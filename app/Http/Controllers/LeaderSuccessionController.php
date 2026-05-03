<?php

namespace App\Http\Controllers;

use App\Services\LeaderSuccessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LeaderSuccessionController extends Controller
{
    public function index(Request $request, LeaderSuccessionService $service)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $currentRoleId = (int) session('authenticated_user.roleid');
        if (!in_array($currentRoleId, [1, 2], true)) {
            return redirect('/')->with('error', __('leader_succession.only_leader_admin_or_superadmin_can_manage'));
        }

        $currentUserId = (int) session('authenticated_user.userid');
        $currentRoleId = (int) session('authenticated_user.roleid');
        $setting = $service->getSettingForOwner($currentUserId);
        $currentHeir = null;
        if ($setting && !empty($setting->heir_memberid)) {
            $currentHeir = DB::table('family_member')
                ->where('memberid', (int) $setting->heir_memberid)
                ->first();
        }

        $candidates = $service->getCandidateMembers($currentUserId);
        $systemSettings = $this->getSystemSettings();

        return view('all.leader-succession', [
            'systemSettings' => $systemSettings,
            'setting' => $setting,
            'currentHeir' => $currentHeir,
            'candidates' => $candidates,
            'hasPin' => !empty($setting) && !empty($setting->pin_hash),
            'currentRoleId' => $currentRoleId,
        ]);
    }

    public function storeHeir(Request $request, LeaderSuccessionService $service)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $currentRoleId = (int) session('authenticated_user.roleid');
        if (!in_array($currentRoleId, [1, 2], true)) {
            return redirect('/')->with('error', __('leader_succession.only_leader_admin_or_superadmin_can_manage'));
        }

        $currentUserId = (int) session('authenticated_user.userid');
        $setting = $service->getSettingForOwner($currentUserId);
        $hasPin = !empty($setting) && !empty($setting->pin_hash);

        $rules = [
            'heir_memberid' => ['required', 'integer', 'exists:family_member,memberid'],
        ];

        if ($hasPin) {
            $rules['current_pin'] = ['required', 'digits:4'];
        } else {
            $rules['new_pin'] = ['required', 'digits:4', 'confirmed'];
        }

        $validated = $request->validate($rules, [
            'heir_memberid.required' => __('leader_succession.choose_successor'),
            'heir_memberid.exists' => __('leader_succession.selected_successor_invalid'),
            'current_pin.required' => __('leader_succession.current_pin_required'),
            'current_pin.digits' => __('leader_succession.current_pin_must_be_4_digits'),
            'new_pin.required' => __('leader_succession.create_new_pin_first'),
            'new_pin.digits' => __('leader_succession.new_pin_must_be_4_digits'),
            'new_pin.confirmed' => __('leader_succession.pin_confirmation_does_not_match'),
        ]);

        try {
            $service->saveHeir(
                $currentUserId,
                (int) $validated['heir_memberid'],
                (string) ($validated['current_pin'] ?? ''),
                (string) ($validated['new_pin'] ?? null)
            );
        } catch (ValidationException $e) {
            throw $e;
        }

        return redirect('/leader-succession')->with('success', __('leader_succession.successor_saved'));
    }

    public function updatePin(Request $request, LeaderSuccessionService $service)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $currentRoleId = (int) session('authenticated_user.roleid');
        if (!in_array($currentRoleId, [1, 2], true)) {
            return redirect('/')->with('error', __('leader_succession.only_leader_admin_or_superadmin_can_manage'));
        }

        $validated = $request->validate([
            'current_pin' => ['required', 'digits:4'],
            'new_pin' => ['required', 'digits:4', 'confirmed'],
        ], [
            'current_pin.required' => __('leader_succession.current_pin_required'),
            'current_pin.digits' => __('leader_succession.current_pin_must_be_4_digits'),
            'new_pin.required' => __('leader_succession.new_pin_required'),
            'new_pin.digits' => __('leader_succession.new_pin_must_be_4_digits'),
            'new_pin.confirmed' => __('leader_succession.pin_confirmation_does_not_match'),
        ]);

        $service->updatePin(
            (int) session('authenticated_user.userid'),
            (string) $validated['current_pin'],
            (string) $validated['new_pin']
        );

        return redirect('/leader-succession')->with('success', __('leader_succession.pin_updated'));
    }
}

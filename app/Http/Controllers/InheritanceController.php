<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class InheritanceController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Correct Superadmin check based on roleid
        $roleId = DB::table('employer')->where('userid', $user->userid)->value('roleid')
               ?? DB::table('family_member')->where('userid', $user->userid)->value('roleid');
        $isSuperadmin = ($roleId == 1);

        // Get global succession setting
        $setting = DB::table('leader_succession_settings')->first();

        // Get heir details if exists
        $heir = null;
        if ($setting) {
            $heir = DB::table('family_member')
                ->where('memberid', $setting->heir_memberid)
                ->first();
        }

        // Get all potential family members to be heir (excluding menantu/partners)
        $menantuIds = DB::table('relationship')
            ->where('relationtype', 'partner')
            ->pluck('relatedmemberid');

        $members = DB::table('family_member')
            ->whereNull('deleted_at')
            ->whereNotIn('memberid', $menantuIds)
            ->select('memberid', 'name', 'picture')
            ->get();

        // Get history globally with pagination
        $history = DB::table('leader_succession_histories')
            ->latest()
            ->paginate(5)
            ->withQueryString();

        return Inertia::render('Inheritance/Index', [
            'currentHeir' => $heir,
            'members' => $members,
            'history' => $history,
            'hasPinSet' => ! empty($setting->pin_hash),
            'isSuperadmin' => $isSuperadmin,
            'translations' => trans('inheritance'),
        ]);
    }

    public function setHeir(Request $request)
    {
        $user = $request->user();

        $roleId = DB::table('employer')->where('userid', $user->userid)->value('roleid')
               ?? DB::table('family_member')->where('userid', $user->userid)->value('roleid');
        $isSuperadmin = ($roleId == 1);

        $rules = [
            'heir_memberid' => 'required|exists:family_member,memberid',
        ];

        if (! $isSuperadmin) {
            $rules['pin'] = 'required|string|digits:4';
        }

        $data = $request->validate($rules);

        $userId = $user->userid;
        $setting = DB::table('leader_succession_settings')->first();

        // Security: If PIN is already set and user is not superadmin, verify it.
        if (! $isSuperadmin && $setting && $setting->pin_hash) {
            if (! Hash::check($data['pin'], $setting->pin_hash)) {
                return back()->withErrors(['pin' => trans('inheritance.error_pin')]);       
            }
        }

        DB::transaction(function () use ($userId, $data, $setting, $isSuperadmin) {
            // Update or Insert Succession Settings (The "Will" / Testament)
            // This ONLY sets who will inherit later, does NOT grant role now.
            if ($setting) {
                $updateData = [
                    'heir_memberid' => $data['heir_memberid'],
                    'updated_at' => now(),
                ];
                if (! $isSuperadmin && isset($data['pin']) && ! $setting->pin_hash) {
                    $updateData['pin_hash'] = Hash::make($data['pin']);
                }
                DB::table('leader_succession_settings')->where('id', $setting->id)->update($updateData);
            } else {
                DB::table('leader_succession_settings')->insert([
                    'owner_userid' => $userId,
                    'heir_memberid' => $data['heir_memberid'],
                    'pin_hash' => $isSuperadmin ? null : Hash::make($data['pin']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return back()->with('success', trans('inheritance.success_heir'));
    }

    public function resetPin(Request $request)
    {
        $user = $request->user();

        $roleId = DB::table('employer')->where('userid', $user->userid)->value('roleid')
               ?? DB::table('family_member')->where('userid', $user->userid)->value('roleid');
        $isSuperadmin = ($roleId == 1);

        if (! $isSuperadmin) {
            return back()->with('error', trans('inheritance.error_unauthorized'));
        }

        DB::table('leader_succession_settings')->update(['pin_hash' => null]);

        return back()->with('success', trans('inheritance.success_reset'));
    }
}

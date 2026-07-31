<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $employer = DB::table('employer')->where('userid', $user->userid)->first();
        $familyMember = DB::table('family_member')->where('userid', $user->userid)->first();

        // Try to parse address if it's JSON
        $addressData = ['country' => 'Indonesia', 'province' => '', 'city' => '', 'detail' => ''];
        if ($familyMember && $familyMember->address) {
            $decoded = json_decode($familyMember->address, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $addressData = array_merge($addressData, $decoded);
            } else {
                $addressData['detail'] = $familyMember->address;
            }
        }

        $socialMediaOptions = DB::table('socialmedia')->whereNull('deleted_at')->get();
        $ownSocial = $familyMember ? DB::table('ownsocial')
            ->join('socialmedia', 'ownsocial.socialid', '=', 'socialmedia.socialid')
            ->where('ownsocial.memberid', $familyMember->memberid)
            ->select('ownsocial.socialid', 'ownsocial.link', 'socialmedia.socialname')
            ->get() : [];

        return Inertia::render('Profile/Index', [
            'translations' => trans('profile'),
            'user' => [
                'userid' => $user->userid,
                'username' => $user->username,
            ],
            'employer' => $employer ? [
                'name' => $employer->name,
                'email' => $employer->email,
                'phonenumber' => $employer->phonenumber,
            ] : null,
            'familyMember' => $familyMember ? [
                'memberid' => $familyMember->memberid,
                'name' => $familyMember->name,
                'email' => $familyMember->email,
                'phonenumber' => $familyMember->phonenumber,
                'gender' => $familyMember->gender,
                'birthdate' => $familyMember->birthdate ? date('Y-m-d', strtotime($familyMember->birthdate)) : null,
                'birthplace' => $familyMember->birthplace,
                'bloodtype' => $familyMember->bloodtype,
                'education_status' => $familyMember->education_status,
                'address' => $addressData,
                'job' => $familyMember->job,
                'social_media' => $ownSocial,
            ] : null,
            'socialMediaOptions' => $socialMediaOptions,
        ]);
    }

    public function updateEmployer(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'username' => 'required|string|max:255|unique:user,username,'.$user->userid.',userid',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:employer,email,'.$user->userid.',userid',
            'phonenumber' => 'nullable|string|max:20',
        ]);

        DB::transaction(function () use ($data, $user) {
            DB::table('user')->where('userid', $user->userid)->update([
                'username' => $data['username'],
            ]);

            DB::table('employer')->where('userid', $user->userid)->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'phonenumber' => $data['phonenumber'],
            ]);
        });

        return back()->with('success', 'Employer profile updated successfully.');
    }

    public function updateFamilyMember(Request $request)
    {
        $user = $request->user();
        $familyMember = DB::table('family_member')->where('userid', $user->userid)->first();

        if (! $familyMember) {
            return back()->with('error', 'Family member profile not found.');
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phonenumber' => 'nullable|string|max:20',
            'gender' => 'required|string|in:male,female',
            'birthdate' => 'nullable|date',
            'birthplace' => 'nullable|string|max:255',
            'bloodtype' => 'nullable|string|max:11',
            'education_status' => 'nullable|string|max:255',
            'address_country' => 'nullable|string|max:255',
            'address_province' => 'nullable|string|max:255',
            'address_city' => 'nullable|string|max:255',
            'address_detail' => 'nullable|string|max:1000',
            'job' => 'nullable|string|max:255',
            'social_media' => 'nullable|array',
            'social_media.*.socialid' => 'required|exists:socialmedia,socialid',
            'social_media.*.link' => 'required|string|max:255',
            'photo' => 'nullable|image|max:2048',
        ]);

        DB::transaction(function () use ($data, $familyMember) {
            $memberData = $data;
            unset($memberData['social_media'], $memberData['photo']);

            // Handle Photo Upload
            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                $fileName = 'profile_' . $familyMember->memberid . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->move(base_path('../uploads/profile-pictures'), $fileName);
                $memberData['picture'] = asset('uploads/profile-pictures/' . $fileName);
            }

            // Structured Address Logic
            $address = [
                'country' => $data['address_country'] ?? 'Indonesia',
                'province' => $data['address_province'] ?? '',
                'city' => $data['address_city'] ?? '',
                'detail' => $data['address_detail'] ?? '',
            ];
            $memberData['address'] = json_encode($address);
            unset($memberData['address_country'], $memberData['address_province'], $memberData['address_city'], $memberData['address_detail']);

            DB::table('family_member')->where('memberid', $familyMember->memberid)->update($memberData);

            // Sync Social Media
            DB::table('ownsocial')->where('memberid', $familyMember->memberid)->delete();
            if (! empty($data['social_media'])) {
                foreach ($data['social_media'] as $social) {
                    DB::table('ownsocial')->insert([
                        'memberid' => $familyMember->memberid,
                        'socialid' => $social['socialid'],
                        'link' => $social['link'],
                    ]);
                }
            }
        });

        return back()->with('success', 'Family member profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        DB::table('user')->where('userid', $request->user()->userid)->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        return back()->with('success', 'Password updated successfully.');
    }
}

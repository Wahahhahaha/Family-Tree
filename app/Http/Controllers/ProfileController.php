<?php

namespace App\Http\Controllers;

use App\Mail\ChangeEmailVerification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function account(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $systemSettings = $this->getSystemSettings();
        $currentUserId = (int) Auth::id();
        $currentLevelId = (int) session('authenticated_user.levelid');
        $currentRoleId = (int) session('authenticated_user.roleid');
        $isEditingOwnFamilyProfile = true;

        $currentFamilyProfile = DB::table('family_member')
            ->where('userid', $currentUserId)
            ->first();

        $currentEmployerProfile = DB::table('employer')
            ->where('userid', $currentUserId)
            ->first();

        $canEditOwnProfile = $currentLevelId === 2 && !empty($currentFamilyProfile);
        $canEditAdminProfile = in_array($currentRoleId, [1, 2], true) && !empty($currentEmployerProfile);
        $canEditMinorContactFields = $isEditingOwnFamilyProfile;
        $profileEditTargetMemberId = (int) ($currentFamilyProfile->memberid ?? 0);
        $accountFamilyUsername = (string) session('authenticated_user.username');

        $socialMediaOptions = collect();
        $selectedSocialMediaIds = [];
        $selectedSocialMediaLinks = [];
        $maxSocialMediaPerMember = 3;

        if ($canEditOwnProfile) {
            $socialMediaOptions = DB::table('socialmedia')
                ->orderBy('socialname')
                ->get();

            $selectedOwnSocialRows = DB::table('ownsocial')
                ->where('memberid', $profileEditTargetMemberId)
                ->get();

            foreach ($selectedOwnSocialRows as $row) {
                $selectedSocialMediaIds[] = $row->socialid;
                $selectedSocialMediaLinks[$row->socialid] = $row->link;
            }
        }

        return view("all.account", [
            'pageClass' => 'page-family-tree',
            'systemSettings' => $systemSettings,
            'currentFamilyProfile' => $currentFamilyProfile,
            'currentEmployerProfile' => $currentEmployerProfile,
            'currentLevelId' => $currentLevelId,
            'currentRoleId' => $currentRoleId,
            'canEditOwnProfile' => $canEditOwnProfile,
            'canEditAdminProfile' => $canEditAdminProfile,
            'canEditMinorContactFields' => $canEditMinorContactFields,
            'profileEditTargetMemberId' => $profileEditTargetMemberId,
            'accountFamilyUsername' => $accountFamilyUsername,
            'isEditingOwnFamilyProfile' => $isEditingOwnFamilyProfile,
            'socialMediaOptions' => $socialMediaOptions,
            'selectedSocialMediaIds' => $selectedSocialMediaIds,
            'selectedSocialMediaLinks' => $selectedSocialMediaLinks,
            'maxSocialMediaPerMember' => $maxSocialMediaPerMember,
            'socialMediaOptionRowsJson' => json_encode($socialMediaOptions)
        ]);
    }

    public function updateEmployerProfile(Request $request)
    {
        $requestedValues = [
            'name' => trim((string) $request->input('name', '')),
            'email' => strtolower(trim((string) $request->input('email', ''))),
            'phonenumber' => trim((string) $request->input('phonenumber', '')),
        ];

        if (!$request->session()->has('authenticated_user')) {
            $this->notifyDiscordEditFailure(
                $request,
                'account.update_admin_profile',
                'Unauthenticated request.',
                [],
                $requestedValues
            );

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect('/login');
        }

        $currentUserId = (int) session('authenticated_user.userid');
        $currentRoleId = (int) session('authenticated_user.roleid');

        if (!in_array($currentRoleId, [1, 2], true)) {
            $this->notifyDiscordEditFailure(
                $request,
                'account.update_admin_profile',
                'Unauthorized role. Only admin can update this profile.',
                [],
                $requestedValues
            );

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['message' => 'Only admin can update this profile.'], 403);
            }

            return redirect('/account')->with('error', 'Only admin can update this profile.');
        }

        $employer = DB::table('employer')
            ->where('userid', $currentUserId)
            ->first();

        if (!$employer) {
            $this->notifyDiscordEditFailure(
                $request,
                'account.update_admin_profile',
                'Admin profile not found.',
                [],
                $requestedValues
            );

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['message' => 'Admin profile not found.'], 404);
            }

            return redirect('/account')->with('error', 'Admin profile not found.');
        }

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phonenumber' => ['required', 'string', 'max:255'],
        ], [
            'name.required' => 'Name is required.',
            'email.required' => 'Email is required.',
            'email.email' => 'Email format is invalid.',
            'phonenumber.required' => 'Phone number is required.',
        ]);

        $oldValues = [
            'name' => trim((string) ($employer->name ?? '')),
            'email' => strtolower(trim((string) ($employer->email ?? ''))),
            'phonenumber' => trim((string) ($employer->phonenumber ?? '')),
        ];

        if ($validator->fails()) {
            $this->notifyDiscordEditFailure(
                $request,
                'account.update_admin_profile',
                'Validation failed.',
                $oldValues,
                $requestedValues,
                $validator->errors()->toArray()
            );

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            return redirect('/account')
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();
        $oldName = $oldValues['name'];
        $oldEmail = $oldValues['email'];
        $pendingEmail = strtolower(trim((string) ($employer->pending_email ?? '')));
        $oldPhoneNumber = $oldValues['phonenumber'];
        $pendingPhoneNumber = trim((string) ($employer->pending_phonenumber ?? ''));
        $newName = trim((string) $validated['name']);
        $newEmail = strtolower(trim((string) $validated['email']));
        $newPhoneNumber = trim((string) $validated['phonenumber']);
        $newValues = [
            'name' => $newName,
            'email' => $newEmail,
            'phonenumber' => $newPhoneNumber,
        ];
        $normalizedOldPhoneNumber = $this->normalizePhoneNumber($oldPhoneNumber);
        $normalizedPendingPhoneNumber = $this->normalizePhoneNumber($pendingPhoneNumber);
        $normalizedNewPhoneNumber = $this->normalizePhoneNumber($newPhoneNumber);
        $isTryingToChangeEmail = $newEmail !== $oldEmail;
        $isTryingToChangePhone = (
            $newPhoneNumber !== ''
            && $normalizedNewPhoneNumber !== ''
            && $normalizedNewPhoneNumber !== $normalizedOldPhoneNumber
        );

        if (($isTryingToChangeEmail || $isTryingToChangePhone) && !$this->employerContactVerificationColumnsReady()) {
            $migrationMessage = 'Contact verification columns for employer are not ready. Please run database migration first.';
            $this->notifyDiscordEditFailure(
                $request,
                'account.update_admin_profile',
                $migrationMessage,
                $oldValues,
                $newValues
            );

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'message' => $migrationMessage,
                ], 500);
            }

            return redirect('/account')
                ->withErrors([
                    'email' => $migrationMessage,
                    'phonenumber' => $migrationMessage,
                ])
                ->withInput();
        }

        if ($newEmail !== $oldEmail) {
            $emailChangeMessage = 'Please use the email verification flow to change your email address.';

            if ($pendingEmail !== '' && $pendingEmail === $newEmail) {
                $emailChangeMessage = 'Email change is pending. Open the verification link sent to your new email address first.';
            }
            $this->notifyDiscordEditFailure(
                $request,
                'account.update_admin_profile',
                $emailChangeMessage,
                $oldValues,
                $newValues
            );

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'message' => $emailChangeMessage,
                    'email_requires_verification' => true,
                ], 422);
            }

            return redirect('/account')
                ->withErrors(['email' => $emailChangeMessage])
                ->withInput();
        }

        if ($newPhoneNumber !== '' && $normalizedNewPhoneNumber === '') {
            $invalidPhoneMessage = 'Please enter a valid phone number.';
            $this->notifyDiscordEditFailure(
                $request,
                'account.update_admin_profile',
                $invalidPhoneMessage,
                $oldValues,
                $newValues
            );

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'message' => $invalidPhoneMessage,
                ], 422);
            }

            return redirect('/account')
                ->withErrors(['phonenumber' => $invalidPhoneMessage])
                ->withInput();
        }

        if (
            $newPhoneNumber !== ''
            && $normalizedNewPhoneNumber !== ''
            && $normalizedNewPhoneNumber !== $normalizedOldPhoneNumber
        ) {
            $phoneChangeMessage = 'Please use the WhatsApp OTP verification flow to change your phone number.';

            if ($normalizedPendingPhoneNumber !== '' && $normalizedPendingPhoneNumber === $normalizedNewPhoneNumber) {
                $phoneChangeMessage = 'Phone number change is pending. Verify the OTP code from WhatsApp first.';
            }
            $this->notifyDiscordEditFailure(
                $request,
                'account.update_admin_profile',
                $phoneChangeMessage,
                $oldValues,
                $newValues
            );

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'message' => $phoneChangeMessage,
                    'phone_requires_verification' => true,
                ], 422);
            }

            return redirect('/account')
                ->withErrors(['phonenumber' => $phoneChangeMessage])
                ->withInput();
        }

        try {
            DB::table('employer')
                ->where('userid', $currentUserId)
                ->update([
                    'name' => $newName,
                    'email' => $newEmail,
                    'phonenumber' => $newPhoneNumber,
                ]);
        } catch (\Throwable $e) {
            $this->notifyDiscordEditFailure(
                $request,
                'account.update_admin_profile',
                'Database update failed.',
                $oldValues,
                $newValues,
                ['exception' => $e->getMessage()]
            );

            throw $e;
        }

        $activityChanges = [];

        if ($oldName !== $newName) {
            $activityChanges[] = [
                'field' => 'name',
                'old' => $oldName,
                'new' => $newName,
            ];
        }

        if ($oldEmail !== $newEmail) {
            $activityChanges[] = [
                'field' => 'email',
                'old' => $oldEmail,
                'new' => $newEmail,
            ];
        }

        if ($oldPhoneNumber !== $newPhoneNumber) {
            $activityChanges[] = [
                'field' => 'phone number',
                'old' => $oldPhoneNumber,
                'new' => $newPhoneNumber,
            ];
        }

        if (count($activityChanges) > 0) {
            $this->logActivity($request, 'account.update_admin_profile', [
                'userid' => $currentUserId,
                'changes' => $activityChanges,
            ]);
        }

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'message' => 'Profile updated successfully.',
                'profile' => [
                    'name' => $newName,
                    'email' => $newEmail,
                    'phonenumber' => $newPhoneNumber,
                ],
            ]);
        }

        return redirect('/account')->with('success', 'Profile updated successfully.');
    }

    public function changeAccountPassword(Request $request)
    {
        $expectsJson = $request->ajax() || $request->expectsJson();

        if (!$request->session()->has('authenticated_user')) {
            if ($expectsJson) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            return redirect('/login');
        }

        $currentUserId = (int) session('authenticated_user.userid');

        $user = DB::table('user')
            ->where('userid', $currentUserId)
            ->first();

        if (!$user) {
            if ($expectsJson) {
                return response()->json([
                    'message' => 'User account not found.',
                ], 404);
            }

            return redirect('/account');
        }

        $validator = Validator::make($request->all(), [
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'max:255', 'different:current_password', 'confirmed'],
        ], [
            'current_password.required' => 'Current password is required.',
            'new_password.required' => 'New password is required.',
            'new_password.min' => 'New password must be at least 8 characters.',
            'new_password.max' => 'New password may not be greater than 255 characters.',
            'new_password.different' => 'New password must be different from current password.',
            'new_password.confirmed' => 'New password confirmation does not match.',
        ]);

        if ($validator->fails()) {
            if ($expectsJson) {
                $errors = $validator->errors();
                return response()->json([
                    'message' => (string) ($errors->first() ?: 'Validation failed.'),
                    'errors' => $errors->toArray(),
                ], 422);
            }

            return redirect('/account');
        }

        $validated = $validator->validated();
        $currentPassword = (string) ($validated['current_password'] ?? '');
        $newPassword = (string) ($validated['new_password'] ?? '');
        $storedPassword = trim((string) ($user->password ?? ''));

        if ($storedPassword === '' || !Hash::check($currentPassword, $storedPassword)) {
            if ($expectsJson) {
                return response()->json([
                    'message' => 'Current password is incorrect.',
                ], 422);
            }

            return redirect('/account');
        }

        DB::table('user')
            ->where('userid', $currentUserId)
            ->update([
                'password' => Hash::make($newPassword),
            ]);

        $this->logActivity($request, 'account.change_password', [
            'userid' => $currentUserId,
        ]);

        if ($expectsJson) {
            return response()->json([
                'message' => 'Password updated successfully.',
            ]);
        }

        return redirect('/account');
    }

    public function requestEmployerChangeEmail(Request $request)
    {
        if (!$this->employerContactVerificationColumnsReady()) {
            return response()->json([
                'message' => 'Contact verification columns for employer are not ready. Please run database migration first.',
            ], 500);
        }

        if (!$request->session()->has('authenticated_user')) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $currentUserId = (int) session('authenticated_user.userid');
        $currentRoleId = (int) session('authenticated_user.roleid');

        if (!in_array($currentRoleId, [1, 2], true)) {
            return response()->json(['message' => 'Only admin can change email.'], 403);
        }

        $validated = $request->validate([
            'new_email' => ['required', 'email', 'max:255'],
        ]);

        $newEmail = strtolower(trim((string) $validated['new_email']));

        $employer = DB::table('employer')
            ->where('userid', $currentUserId)
            ->first();

        if (!$employer) {
            return response()->json(['message' => 'Admin profile not found.'], 404);
        }

        $currentEmail = strtolower(trim((string) ($employer->email ?? '')));
        $pendingEmail = strtolower(trim((string) ($employer->pending_email ?? '')));

        if ($currentEmail === $newEmail) {
            return response()->json(['message' => 'New email is the same as current email.'], 400);
        }

        if ($pendingEmail !== '' && $pendingEmail === $newEmail) {
            return response()->json([
                'message' => 'Verification link has already been sent to this email.',
                'old_email' => $currentEmail,
                'new_email' => $newEmail,
                'already_sent' => true,
            ]);
        }

        $existingFamilyEmail = DB::table('family_member')
            ->where(function ($query) use ($newEmail) {
                $query->whereRaw('LOWER(email) = ?', [$newEmail])
                    ->orWhereRaw('LOWER(pending_email) = ?', [$newEmail]);
            })
            ->exists();

        $existingEmployerEmail = DB::table('employer')
            ->where('userid', '!=', $currentUserId)
            ->where(function ($query) use ($newEmail) {
                $query->whereRaw('LOWER(email) = ?', [$newEmail])
                    ->orWhereRaw('LOWER(pending_email) = ?', [$newEmail]);
            })
            ->exists();

        if ($existingFamilyEmail || $existingEmployerEmail) {
            return response()->json(['message' => 'This email is already in use.'], 400);
        }

        $token = Str::random(64);
        $expiresAt = now()->addMinutes(10);
        $verificationUrl = url('/employer/verify-email/' . $token);
        $adminName = trim((string) ($employer->name ?? ''));

        DB::table('employer')
            ->where('userid', $currentUserId)
            ->update([
                'pending_email' => $newEmail,
                'email_verification_token' => $token,
                'email_verification_token_expires_at' => $expiresAt,
            ]);

        $this->logActivity($request, 'employer.request_email_change', [
            'userid' => $currentUserId,
            'old_email' => $currentEmail,
            'new_email' => $newEmail,
        ]);

        dispatch(function () use ($adminName, $currentEmail, $newEmail, $verificationUrl) {
            try {
                Mail::to($newEmail)->send(new ChangeEmailVerification(
                    $adminName,
                    $currentEmail,
                    $newEmail,
                    $verificationUrl
                ));
            } catch (\Throwable $e) {
                \Log::error('Failed to send employer email change verification: ' . $e->getMessage());
            }
        })->afterResponse();

        return response()->json([
            'message' => 'We have sent an email to your new address for confirmation.',
            'old_email' => $currentEmail,
            'new_email' => $newEmail,
            'pending_email' => $newEmail,
        ]);
    }

    public function cancelEmployerPendingEmailChange(Request $request)
    {
        if (!$this->employerContactVerificationColumnsReady()) {
            return response()->json([
                'message' => 'Contact verification columns for employer are not ready. Please run database migration first.',
            ], 500);
        }

        if (!$request->session()->has('authenticated_user')) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $currentUserId = (int) session('authenticated_user.userid');
        $currentRoleId = (int) session('authenticated_user.roleid');

        if (!in_array($currentRoleId, [1, 2], true)) {
            return response()->json(['message' => 'Only admin can cancel email changes.'], 403);
        }

        $employer = DB::table('employer')
            ->where('userid', $currentUserId)
            ->first();

        if (!$employer) {
            return response()->json(['message' => 'Admin profile not found.'], 404);
        }

        $currentEmail = strtolower(trim((string) ($employer->email ?? '')));
        $pendingEmail = strtolower(trim((string) ($employer->pending_email ?? '')));

        if ($pendingEmail === '') {
            return response()->json([
                'message' => 'There is no pending email change request.',
                'current_email' => $currentEmail,
            ]);
        }

        DB::table('employer')
            ->where('userid', $currentUserId)
            ->update([
                'pending_email' => null,
                'email_verification_token' => null,
                'email_verification_token_expires_at' => null,
            ]);

        $this->logActivity($request, 'employer.cancel_email_change', [
            'userid' => $currentUserId,
            'old_email' => $currentEmail,
            'pending_email' => $pendingEmail,
        ]);

        return response()->json([
            'message' => 'Email change request has been canceled.',
            'current_email' => $currentEmail,
        ]);
    }

    public function verifyEmployerEmailChange(Request $request, $token)
    {
        if (!$this->employerContactVerificationColumnsReady()) {
            return redirect('/account')->with('error', 'Contact verification columns for employer are not ready. Please run database migration first.');
        }

        $employer = DB::table('employer')
            ->where('email_verification_token', $token)
            ->where('email_verification_token_expires_at', '>', now())
            ->first();

        if (!$employer) {
            return redirect('/account')->with('error', 'Invalid or expired verification link.');
        }

        $oldEmail = strtolower(trim((string) ($employer->email ?? '')));
        $newEmail = strtolower(trim((string) ($employer->pending_email ?? '')));

        if ($newEmail === '') {
            return redirect('/account')->with('error', 'No pending email change request was found.');
        }

        $existingFamilyEmail = DB::table('family_member')
            ->where(function ($query) use ($newEmail) {
                $query->whereRaw('LOWER(email) = ?', [$newEmail])
                    ->orWhereRaw('LOWER(pending_email) = ?', [$newEmail]);
            })
            ->exists();

        $existingEmployerEmail = DB::table('employer')
            ->where('userid', '!=', (int) $employer->userid)
            ->where(function ($query) use ($newEmail) {
                $query->whereRaw('LOWER(email) = ?', [$newEmail])
                    ->orWhereRaw('LOWER(pending_email) = ?', [$newEmail]);
            })
            ->exists();

        if ($existingFamilyEmail || $existingEmployerEmail) {
            return redirect('/account')->with('error', 'This email is already in use. Please request a different email.');
        }

        DB::table('employer')
            ->where('employerid', $employer->employerid)
            ->update([
                'email' => $newEmail,
                'pending_email' => null,
                'email_verification_token' => null,
                'email_verification_token_expires_at' => null,
            ]);

        $this->logActivity($request, 'employer.verify_email_change', [
            'userid' => $employer->userid,
            'old_email' => $oldEmail,
            'new_email' => $newEmail,
        ]);

        $systemSettings = $this->getSystemSettings();
        $redirectTo = '/account';

        echo view('all.header', [
            'pageTitle' => 'Email Updated | ' . $systemSettings['website_name'],
            'pageClass' => 'page-login',
            'systemSettings' => $systemSettings
        ]);
        echo view('all.email-change-success', compact('systemSettings', 'oldEmail', 'newEmail', 'redirectTo'));
        echo view('all.footer');
    }

    public function requestEmployerPhoneChangeOtp(Request $request)
    {
        if (!$this->employerContactVerificationColumnsReady()) {
            return response()->json([
                'message' => 'Contact verification columns for employer are not ready. Please run database migration first.',
            ], 500);
        }

        if (!$request->session()->has('authenticated_user')) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $currentUserId = (int) session('authenticated_user.userid');
        $currentRoleId = (int) session('authenticated_user.roleid');

        if (!in_array($currentRoleId, [1, 2], true)) {
            return response()->json(['message' => 'Only admin can change phone numbers.'], 403);
        }

        $validated = $request->validate([
            'new_phone' => ['required', 'string', 'max:255'],
        ], [
            'new_phone.required' => 'Phone number is required.',
            'new_phone.max' => 'Phone number max length is 255 characters.',
        ]);

        $newPhoneRaw = trim((string) $validated['new_phone']);
        $normalizedNewPhone = $this->normalizePhoneNumber($newPhoneRaw);

        if ($normalizedNewPhone === '') {
            return response()->json(['message' => 'Please enter a valid phone number.'], 422);
        }

        $employer = DB::table('employer')
            ->where('userid', $currentUserId)
            ->first();

        if (!$employer) {
            return response()->json(['message' => 'Admin profile not found.'], 404);
        }

        $currentPhone = trim((string) ($employer->phonenumber ?? ''));
        $normalizedCurrentPhone = $this->normalizePhoneNumber($currentPhone);

        if ($normalizedCurrentPhone !== '' && $normalizedCurrentPhone === $normalizedNewPhone) {
            return response()->json(['message' => 'New phone number is the same as current phone number.'], 400);
        }

        $existingAccount = $this->findAccountByPhoneNumber($newPhoneRaw);
        if ($existingAccount && (int) ($existingAccount->userid ?? 0) !== $currentUserId) {
            return response()->json(['message' => 'This phone number is already in use.'], 400);
        }

        $otp = (string) random_int(100000, 999999);
        $otpExpiresAt = Carbon::now()->addMinutes(5);

        if (!$this->sendWhatsappOtpViaFonnte($normalizedNewPhone, $otp, $otpExpiresAt, 'phone_change')) {
            return response()->json([
                'message' => 'Failed to send OTP to WhatsApp. Please try again later.',
            ], 500);
        }

        DB::table('employer')
            ->where('userid', $currentUserId)
            ->update([
                'pending_phonenumber' => $newPhoneRaw,
                'phone_verification_otp_hash' => Hash::make($otp),
                'phone_verification_otp_expires_at' => $otpExpiresAt,
            ]);

        $this->logActivity($request, 'employer.request_phone_change_otp', [
            'userid' => $currentUserId,
            'old_phone' => $currentPhone,
            'new_phone' => $newPhoneRaw,
        ]);

        return response()->json([
            'message' => 'OTP has been sent to your WhatsApp number.',
            'old_phone' => $currentPhone,
            'new_phone' => $newPhoneRaw,
            'pending_phone' => $newPhoneRaw,
            'otp_expires_at' => $otpExpiresAt->toDateTimeString(),
        ]);
    }

    public function verifyEmployerPhoneChangeOtp(Request $request)
    {
        if (!$this->employerContactVerificationColumnsReady()) {
            return response()->json([
                'message' => 'Contact verification columns for employer are not ready. Please run database migration first.',
            ], 500);
        }

        if (!$request->session()->has('authenticated_user')) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $currentUserId = (int) session('authenticated_user.userid');
        $currentRoleId = (int) session('authenticated_user.roleid');

        if (!in_array($currentRoleId, [1, 2], true)) {
            return response()->json(['message' => 'Only admin can verify phone changes.'], 403);
        }

        $validated = $request->validate([
            'otp' => ['required', 'digits:6'],
        ], [
            'otp.required' => 'OTP is required.',
            'otp.digits' => 'OTP must contain 6 digits.',
        ]);

        $employer = DB::table('employer')
            ->where('userid', $currentUserId)
            ->first();

        if (!$employer) {
            return response()->json(['message' => 'Admin profile not found.'], 404);
        }

        $pendingPhone = trim((string) ($employer->pending_phonenumber ?? ''));
        $otpHash = (string) ($employer->phone_verification_otp_hash ?? '');
        $otpExpiresAtRaw = (string) ($employer->phone_verification_otp_expires_at ?? '');
        $otpExpiresAt = $otpExpiresAtRaw !== '' ? Carbon::parse($otpExpiresAtRaw) : null;

        if ($pendingPhone === '' || $otpHash === '' || !$otpExpiresAt) {
            return response()->json([
                'message' => 'There is no active phone change verification request.',
            ], 400);
        }

        if ($otpExpiresAt->isPast()) {
            return response()->json([
                'message' => 'OTP has expired. Please cancel and request a new OTP.',
            ], 422);
        }

        if (!Hash::check((string) $validated['otp'], $otpHash)) {
            return response()->json(['message' => 'Invalid OTP code.'], 422);
        }

        $existingAccount = $this->findAccountByPhoneNumber($pendingPhone);
        if ($existingAccount && (int) ($existingAccount->userid ?? 0) !== $currentUserId) {
            return response()->json(['message' => 'This phone number is already in use.'], 400);
        }

        $oldPhone = trim((string) ($employer->phonenumber ?? ''));

        DB::table('employer')
            ->where('userid', $currentUserId)
            ->update([
                'phonenumber' => $pendingPhone,
                'pending_phonenumber' => null,
                'phone_verification_otp_hash' => null,
                'phone_verification_otp_expires_at' => null,
            ]);

        $this->logActivity($request, 'employer.verify_phone_change_otp', [
            'userid' => $currentUserId,
            'old_phone' => $oldPhone,
            'new_phone' => $pendingPhone,
        ]);

        return response()->json([
            'message' => 'Phone number has been updated successfully.',
            'phone_number' => $pendingPhone,
        ]);
    }

    public function cancelEmployerPendingPhoneChange(Request $request)
    {
        if (!$this->employerContactVerificationColumnsReady()) {
            return response()->json([
                'message' => 'Contact verification columns for employer are not ready. Please run database migration first.',
            ], 500);
        }

        if (!$request->session()->has('authenticated_user')) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $currentUserId = (int) session('authenticated_user.userid');
        $currentRoleId = (int) session('authenticated_user.roleid');

        if (!in_array($currentRoleId, [1, 2], true)) {
            return response()->json(['message' => 'Only admin can cancel phone changes.'], 403);
        }

        $employer = DB::table('employer')
            ->where('userid', $currentUserId)
            ->first();

        if (!$employer) {
            return response()->json(['message' => 'Admin profile not found.'], 404);
        }

        $currentPhone = trim((string) ($employer->phonenumber ?? ''));
        $pendingPhone = trim((string) ($employer->pending_phonenumber ?? ''));

        if ($pendingPhone === '') {
            return response()->json([
                'message' => 'There is no pending phone change request.',
                'current_phone' => $currentPhone,
            ]);
        }

        DB::table('employer')
            ->where('userid', $currentUserId)
            ->update([
                'pending_phonenumber' => null,
                'phone_verification_otp_hash' => null,
                'phone_verification_otp_expires_at' => null,
            ]);

        $this->logActivity($request, 'employer.cancel_phone_change', [
            'userid' => $currentUserId,
            'old_phone' => $currentPhone,
            'pending_phone' => $pendingPhone,
        ]);

        return response()->json([
            'message' => 'Phone change request has been canceled.',
            'current_phone' => $currentPhone,
        ]);
    }
}

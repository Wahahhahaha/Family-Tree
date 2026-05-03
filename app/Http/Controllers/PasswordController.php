<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordController extends Controller
{
    public function forgotPassword(Request $request)
    {
        $requestedMethod = (string) $request->query('method', 'email');
        $activeMethod = in_array($requestedMethod, ['email', 'phone'], true) ? $requestedMethod : 'email';
        
        // Defaults for UI state
        $showPhoneOtpForm = (bool) session('show_forgot_phone_otp_form', false);
        $phoneDisplayNumber = (string) session('forgot_phone_display', '');
        $phoneInputValue = (string) session('forgot_phone_input', '');
        $showOtpModal = (bool) session('show_forgot_otp_modal', false);
        $hasPhoneErrors = session('errors') ? session('errors')->has('phonenumber') || session('errors')->has('otp') : false;
	$systemSettings = $this->getSystemSettings();
        return view("all.forgot-password", compact(
            'systemSettings', 'activeMethod', 'requestedMethod', 
            'showPhoneOtpForm', 'phoneDisplayNumber', 'phoneInputValue', 
            'showOtpModal', 'hasPhoneErrors'
        ) + ["pageClass" => "page-login", "hideNavbar" => true]);

    }

    public function sendPasswordResetLink(Request $request)
    {
        $validated = $request->validate(['email' => 'required|email']);
        $user = DB::table('user')
            ->leftJoin('family_member', 'family_member.userid', '=', 'user.userid')
            ->where('family_member.email', $validated['email'])
            ->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Email not found.']);
        }

        $token = Str::random(64);
        DB::table('user')->where('userid', $user->userid)->update([
            'reset_password_token' => Hash::make($token),
            'reset_password_token_expired' => Carbon::now()->addMinutes(60),
        ]);

        $resetUrl = url('/reset-password/' . $token) . '?email=' . urlencode($validated['email']);
        
        try {
            Mail::send('emails.password_reset', ['resetUrl' => $resetUrl], function ($message) use ($validated) {
                $message->to($validated['email'])->subject('Password Reset Request');
            });
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to send email.');
        }

        return back()->with('success', 'Reset link sent to your email.');
    }

    public function resetPasswordForm(Request $request, $token)
    {
        $systemSettings = $this->getSystemSettings();
        $email = $request->query('email');
        echo view('all.header', ['pageTitle' => 'Reset Password', 'systemSettings' => $systemSettings]);
        echo view('all.reset-password', compact('systemSettings', 'email', 'token'));
        echo view('all.footer');
    }

    public function updatePassword(Request $request, $token)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = DB::table('user')
            ->leftJoin('family_member', 'family_member.userid', '=', 'user.userid')
            ->where('family_member.email', $validated['email'])
            ->first();

        if (!$user || !Hash::check($token, $user->reset_password_token)) {
            return redirect('/forgot-password')->with('error', 'Invalid token.');
        }

        DB::table('user')->where('userid', $user->userid)->update([
            'password' => Hash::make($validated['password']),
            'reset_password_token' => null,
            'reset_password_token_expired' => null,
        ]);

        return redirect('/login')->with('success', 'Password updated successfully.');
    }
}

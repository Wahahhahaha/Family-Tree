<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;

class OtpController extends Controller
{
    public function send(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('--- OTP SEND START ---', [
            'email' => $request->email,
            'ip' => $request->ip()
        ]);

        try {
            $request->validate(['email' => 'required|email']);
            \Illuminate\Support\Facades\Log::info('Basic email format validation passed.');

            // Check existence in family_member
            $existsInFamily = \Illuminate\Support\Facades\DB::table('family_member')
                ->where('email', $request->email)
                ->exists();
            
            // Check existence in employer
            $existsInEmployer = \Illuminate\Support\Facades\DB::table('employer')
                ->where('email', $request->email)
                ->exists();

            if (!$existsInFamily && !$existsInEmployer) {
                \Illuminate\Support\Facades\Log::warning('OTP Send Failed: Email not found in family_member or employer tables.', ['email' => $request->email]);
                return back()->withErrors(['email_not_found' => 'Email address not registered in our records.']);
            }

            \Illuminate\Support\Facades\Log::info('Email exists in records.', [
                'in_family' => $existsInFamily,
                'in_employer' => $existsInEmployer
            ]);

            $otp = rand(100000, 999999);
            \Illuminate\Support\Facades\Cache::put('otp_' . $request->email, $otp, now()->addMinutes(5));
            \Illuminate\Support\Facades\Log::info('OTP generated and cached.', ['otp' => $otp]);

            // Actual Mail Send
            try {
                \Illuminate\Support\Facades\Mail::to($request->email)->send(new \App\Mail\OtpMail((string)$otp));
                \Illuminate\Support\Facades\Log::info('OTP email successfully sent to ' . $request->email);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('SMTP Mail Send Failed:', ['error' => $e->getMessage()]);
                // Still proceed because the code is in cache and can be retrieved from logs if needed
            }

            \Illuminate\Support\Facades\Log::info('Redirecting to login.otp with flash data...');

            return redirect()->route('login.otp')->with([
                'success' => 'OTP code has been sent to your email.',
                'otp_sent' => true,
                'email' => $request->email
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Illuminate\Support\Facades\Log::error('OTP Validation Exception:', $e->errors());
            throw $e;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('OTP Unexpected Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['email' => 'An unexpected error occurred. Please try again.']);
        }
    }

    public function verify(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('--- OTP VERIFY START ---', [
            'email' => $request->email,
            'otp_entered' => $request->otp
        ]);

        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|digits:6',
        ]);

        $cachedOtp = Cache::get('otp_' . $request->email);
        \Illuminate\Support\Facades\Log::info('Checking cached OTP...', ['cached' => $cachedOtp]);

        if ($cachedOtp && $cachedOtp == $request->otp) {
            \Illuminate\Support\Facades\Log::info('OTP match successful.');

            // Try to find userid in family_member
            $userid = \Illuminate\Support\Facades\DB::table('family_member')
                ->where('email', $request->email)
                ->value('userid');

            // If not found, try employer
            if (!$userid) {
                $userid = \Illuminate\Support\Facades\DB::table('employer')
                    ->where('email', $request->email)
                    ->value('userid');
                \Illuminate\Support\Facades\Log::info('Userid found in employer table.', ['userid' => $userid]);
            } else {
                \Illuminate\Support\Facades\Log::info('Userid found in family_member table.', ['userid' => $userid]);
            }

            if ($userid) {
                $user = User::where('userid', $userid)->first();
                if ($user) {
                    \Illuminate\Support\Facades\Log::info('Logging in user...', ['username' => $user->username]);
                    Auth::login($user);
                    Cache::forget('otp_' . $request->email);
                    return redirect('/');
                } else {
                    \Illuminate\Support\Facades\Log::error('User model not found for userid.', ['userid' => $userid]);
                }
            } else {
                \Illuminate\Support\Facades\Log::error('No userid linked to this email after OTP match.', ['email' => $request->email]);
            }
        } else {
            \Illuminate\Support\Facades\Log::warning('OTP mismatch or expired.', [
                'entered' => $request->otp,
                'cached' => $cachedOtp
            ]);
        }

        return back()->withErrors(['otp' => 'Invalid or expired OTP.']);
    }
}

<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        if (session()->has('authenticated_user')) {
            return redirect('/');
        }

        if ($request->boolean('reset_login_otp')) {
            $request->session()->forget('login_phone_otp');
            $request->session()->forget('login_email_otp');
        }

        $systemSettings = $this->getSystemSettings();
        $showRecaptcha = $this->isLoginCaptchaRequired($request);
        $onlineCaptchaConfigured = $this->isOnlineLoginCaptchaConfigured();
        $recaptchaSiteKey = $onlineCaptchaConfigured ? trim((string) config('services.recaptcha.site_key', '')) : '';
        $offlineCaptchaQuestion = '';

        if ($showRecaptcha) {
            $offlineCaptchaQuestion = $this->refreshOfflineLoginCaptchaChallenge($request);
        } else {
            $request->session()->forget('login_offline_captcha_answer');
        }

        return view('all.login', [
            'pageTitle' => __('auth.login_title') . ' | ' . $systemSettings['website_name'],
            'pageClass' => 'page-login',
            'hideNavbar' => true,
            'systemSettings' => $systemSettings,
            'showRecaptcha' => $showRecaptcha,
            'recaptchaSiteKey' => $recaptchaSiteKey,
            'offlineCaptchaQuestion' => $offlineCaptchaQuestion,
        ]);
    }

    public function authenticate(Request $request)
    {
        $captchaRequired = $this->isLoginCaptchaRequired($request);
        $rules = [
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
        
        $credentials = $request->validate($rules);

        if ($captchaRequired) {
            $captchaMode = (string) ($request->input('captcha_mode', 'offline'));
            if ($captchaMode === 'online') {
                if (!$this->verifyRecaptchaResponse($request->input('g-recaptcha-response'), $request->ip())) {
                    return back()->with('error', __('auth.recaptcha_failed'));
                }
            } else {
                if (!$this->verifyOfflineLoginCaptchaAnswer($request, $request->input('offline_captcha_answer'))) {
                    return back()->with('error', __('auth.offline_captcha_failed'));
                }
            }
        }

        $user = DB::table('user')->where('username', trim($credentials['username']))->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            $this->incrementLoginFailedAttempts($request);
            return back()->with('error', __('auth.invalid_credentials'));
        }

        // Ambil data pendukung (Level, Employer, FamilyMember) agar sinkron dengan session
        $level = DB::table('level')->where('levelid', $user->levelid)->first();
        
        $employer = DB::table('employer as e')
            ->leftJoin('role as r', 'r.roleid', '=', 'e.roleid')
            ->where('e.userid', $user->userid)
            ->select('e.*', 'r.rolename')
            ->first();

        $familyMember = DB::table('family_member')->where('userid', $user->userid)->first();

        if ($familyMember && strtolower($familyMember->life_status) === 'deceased') {
            return back()->with('error', __('auth.account_deceased'));
        }

        // Kirim semua objek ke helper storeAuthenticatedSession
        $this->storeAuthenticatedSession($request, $user, $level, $employer, $familyMember, 'password');
        $this->resetLoginFailedAttempts($request);

        return redirect('/');
    }

    public function logout(Request $request)
    {
        $this->logActivity($request, 'auth.logout', [
            'userid' => (int) (session('authenticated_user.userid') ?? 0),
            'username' => (string) (session('authenticated_user.username') ?? ''),
        ]);

        Auth::logout();
        $request->session()->forget('authenticated_user');
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    public function redirectToGoogleLogin(Request $request)
    {
        if (session()->has('authenticated_user')) {
            return redirect('/');
        }

        $clientId = trim((string) config('services.google.client_id', ''));
        $redirectUri = trim((string) config('services.google.redirect', ''));
        $authUrl = trim((string) config('services.google.auth_url', 'https://accounts.google.com/o/oauth2/v2/auth'));

        if ($clientId === '') {
            return redirect('/login')->with('error', __('auth.google_not_configured'));
        }

        if ($redirectUri === '') {
            $redirectUri = url('/login/google/callback');
        }

        $state = Str::random(40);
        $request->session()->put('google_oauth_state', $state);
        $request->session()->put('google_oauth_redirect', url()->previous() ?: url('/login'));

        $query = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'access_type' => 'offline',
            'prompt' => 'select_account',
        ]);

        return redirect($authUrl . '?' . $query);
    }

    public function handleGoogleLoginCallback(Request $request)
    {
        if (session()->has('authenticated_user')) {
            return redirect('/');
        }

        $expectedState = (string) $request->session()->pull('google_oauth_state', '');
        $incomingState = (string) $request->query('state', '');
        if ($expectedState === '' || $incomingState === '' || !hash_equals($expectedState, $incomingState)) {
            return redirect('/login')->with('error', __('auth.google_session_expired'));
        }

        $authCode = trim((string) $request->query('code', ''));
        if ($authCode === '') {
            return redirect('/login')->with('error', __('auth.google_login_failed_or_cancelled'));
        }

        $clientId = trim((string) config('services.google.client_id', ''));
        $clientSecret = trim((string) config('services.google.client_secret', ''));
        $redirectUri = trim((string) config('services.google.redirect', ''));
        $tokenUrl = trim((string) config('services.google.token_url', 'https://oauth2.googleapis.com/token'));
        $userinfoUrl = trim((string) config('services.google.userinfo_url', 'https://openidconnect.googleapis.com/v1/userinfo'));

        if ($clientId === '' || $clientSecret === '') {
            return redirect('/login')->with('error', __('auth.google_not_configured'));
        }

        if ($redirectUri === '') {
            $redirectUri = url('/login/google/callback');
        }

        try {
            $tokenResponse = Http::asForm()->post($tokenUrl, [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri' => $redirectUri,
                'grant_type' => 'authorization_code',
                'code' => $authCode,
            ]);

            if (!$tokenResponse->successful()) {
                return redirect('/login')->with('error', __('auth.failed_connect_google'));
            }

            $accessToken = trim((string) $tokenResponse->json('access_token', ''));
            if ($accessToken === '') {
                return redirect('/login')->with('error', __('auth.google_response_invalid'));
            }

            $profileResponse = Http::withToken($accessToken)->get($userinfoUrl);
            if (!$profileResponse->successful()) {
                return redirect('/login')->with('error', __('auth.failed_google_profile'));
            }

            $googleEmail = trim((string) $profileResponse->json('email', ''));
            $googleName = trim((string) $profileResponse->json('name', ''));
            if ($googleEmail === '') {
                return redirect('/login')->with('error', __('auth.google_email_missing'));
            }

            $emailLower = Str::lower($googleEmail);
            $user = DB::table('user as u')
                ->leftJoin('employer as e', 'e.userid', '=', 'u.userid')
                ->leftJoin('family_member as fm', 'fm.userid', '=', 'u.userid')
                ->leftJoin('level as l', 'l.levelid', '=', 'u.levelid')
                ->whereNull('u.deleted_at')
                ->where(function ($query) use ($emailLower) {
                    $query->whereRaw('LOWER(COALESCE(e.email, \'\')) = ?', [$emailLower])
                        ->orWhereRaw('LOWER(COALESCE(fm.email, \'\')) = ?', [$emailLower])
                        ->orWhereRaw('LOWER(COALESCE(u.username, \'\')) = ?', [$emailLower]);
                })
                ->select(
                    'u.userid',
                    'u.username',
                    'u.levelid',
                    'l.levelname',
                    'e.roleid',
                    'e.rolename',
                    'e.name as employer_name',
                    'fm.memberid as family_memberid',
                    'fm.name as family_name',
                    'fm.life_status as family_life_status'
                )
                ->first();

            if (!$user) {
                return redirect('/login')->with('error', __('auth.google_email_not_registered'));
            }

            if (strtolower((string) ($user->family_life_status ?? '')) === 'deceased') {
                return redirect('/login')->with('error', __('auth.account_deceased'));
            }

            $level = (object) [
                'levelname' => $user->levelname ?? null,
            ];

            $employer = null;
            if (!empty($user->roleid) || !empty($user->employer_name)) {
                $employer = (object) [
                    'roleid' => $user->roleid ?? null,
                    'rolename' => $user->rolename ?? null,
                    'name' => $user->employer_name ?? '',
                    'email' => $googleEmail,
                ];
            }

            $familyMember = null;
            if (!empty($user->family_memberid)) {
                $familyMember = DB::table('family_member')
                    ->where('memberid', (int) $user->family_memberid)
                    ->first();
            }

            $sessionUser = (object) [
                'userid' => $user->userid,
                'username' => $user->username ?? $googleEmail,
                'levelid' => $user->levelid,
            ];

            $this->storeAuthenticatedSession($request, $sessionUser, $level, $employer, $familyMember, 'google');

            $displayName = $googleName !== '' ? $googleName : $googleEmail;
            if (!empty($user->family_name)) {
                $displayName = (string) $user->family_name;
            } elseif (!empty($user->employer_name)) {
                $displayName = (string) $user->employer_name;
            }
            $request->session()->put('authenticated_user.name', $displayName);

            $this->resetLoginFailedAttempts($request);
            $redirectTo = (string) $request->session()->pull('google_oauth_redirect', '/');

            return redirect($redirectTo !== '' ? $redirectTo : '/');
        } catch (\Throwable $e) {
            return redirect('/login')->with('error', __('auth.google_login_failed'));
        }
    }

    public function sendLoginOtp(Request $request)
    {
        // ... pindahkan logic sendLoginOtp dari Ctrl.php ke sini ...
    }

    public function verifyLoginOtp(Request $request)
    {
        // ... pindahkan logic verifyLoginOtp dari Ctrl.php ke sini ...
    }
}

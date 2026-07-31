<?php

namespace App\Http\Middleware;

use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $system = DB::table('system')->first();

        return [
            ...parent::share($request),
            'locale' => \Illuminate\Support\Facades\App::getLocale(),
            'menu_translations' => trans('menu'),
            'chatbot_translations' => trans('chatbot'),
            'systemname' => $system?->systemname ?? 'Family Trees',
            'systemlogo' => $system?->systemlogo ?? null,
            'flash' => [
                'status' => $request->session()->get('status'),
                'error' => $request->session()->get('error'),
                'success' => $request->session()->get('success'),
                'otp_sent' => $request->session()->get('otp_sent'),
                'email' => $request->session()->get('email'),
            ],
            'auth' => [
                'user' => $request->user() ? [
                    'userid' => $request->user()->userid,
                    'username' => $request->user()->username,
                    'picture' => DB::table('family_member')->where('userid', $request->user()->userid)->value('picture'),
                ] : null,
                'roleid' => $request->user() ? (
                    DB::table('employer')->where('userid', $request->user()->userid)->value('roleid')
                    ?? DB::table('family_member')->where('userid', $request->user()->userid)->value('roleid')
                ) : null,
                'is_family_member' => $request->user() ? DB::table('family_member')->where('userid', $request->user()->userid)->exists() : false,
                'permissions' => $request->user() ? PermissionService::getEffectivePermissions($request->user()->userid) : [],
            ],
        ];
    }
}

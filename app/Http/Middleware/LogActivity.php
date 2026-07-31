<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Log only if it's a state-changing request (POST, PUT, PATCH, DELETE)
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $user = Auth::user();

            // Define the action name based on route name or URL
            $action = $request->route() ? $request->route()->getName() : $request->path();
            if (! $action) {
                $action = $request->method().' '.$request->path();
            }

            // Prepare context (exclude sensitive data)
            $context = $request->except(['password', 'password_confirmation', 'current_password', 'pin', 'photo', 'picture']);

            try {
                DB::table('activity_log')->insert([
                    'action' => strtoupper($request->method()).': '.$action,
                    'user_id' => $user ? $user->userid : 0,
                    'context' => json_encode($context),
                    'ip_adress' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Exception $e) {
                // Silently fail logging if database error occurs to not break the app
                Log::error('Activity logging failed: '.$e->getMessage());
            }
        }

        return $response;
    }
}

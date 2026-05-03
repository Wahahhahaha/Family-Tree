<?php

namespace App\Http\Middleware;

use Closure;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class LogActivityRequests
{
    public function handle(Request $request, Closure $next)
    {
        $beforeUser = $this->snapshotUser($request->session()->get('authenticated_user'));

        $response = $next($request);

        $afterUser = $this->snapshotUser($request->session()->get('authenticated_user'));
        $path = ltrim((string) $request->path(), '/');
        $method = strtoupper($request->method());

        if (!$this->shouldLog($method, $path, $beforeUser, $afterUser)) {
            return $response;
        }

        $action = $this->resolveAction($method, $path, $request->route()?->uri() ?? '');
        $actor = $afterUser ?? $beforeUser;

        if ($actor === null) {
            return $response;
        }

        $record = [
            'actor' => $actor,
            'action' => $action,
            'context' => [
                'method' => $method,
                'path' => $path,
                'route' => $request->route()?->uri(),
                'status' => method_exists($response, 'getStatusCode') ? $response->getStatusCode() : null,
                'authenticated_before' => $beforeUser !== null,
                'authenticated_after' => $afterUser !== null,
            ],
            'ip_address' => $request->ip(),
            'latitude' => $this->resolveCoordinate(
                $request->input('client_latitude')
                ?? $request->input('latitude')
                ?? $request->session()->get('client_latitude')
            ),
            'longitude' => $this->resolveCoordinate(
                $request->input('client_longitude')
                ?? $request->input('longitude')
                ?? $request->session()->get('client_longitude')
            ),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
            'created_at' => Carbon::now()->toDateTimeString(),
        ];

        try {
            if (DB::getSchemaBuilder()->hasTable('activity_log')) {
                $payload = [
                    'action' => $action,
                    'context' => json_encode($record['context'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                    'user_agent' => substr((string) $request->userAgent(), 0, 255),
                    'created_at' => Carbon::now(),
                ];

                if (DB::getSchemaBuilder()->hasColumn('activity_log', 'userid')) {
                    $payload['userid'] = (int) ($actor['userid'] ?? 0);
                }

                if (DB::getSchemaBuilder()->hasColumn('activity_log', 'user_id')) {
                    $payload['user_id'] = (int) ($actor['userid'] ?? 0);
                }

                if (DB::getSchemaBuilder()->hasColumn('activity_log', 'latitude')) {
                    $payload['latitude'] = $record['latitude'];
                }

                if (DB::getSchemaBuilder()->hasColumn('activity_log', 'longitude')) {
                    $payload['longitude'] = $record['longitude'];
                }

                if (DB::getSchemaBuilder()->hasColumn('activity_log', 'ip_address')) {
                    $payload['ip_address'] = $request->ip();
                }

                if (DB::getSchemaBuilder()->hasColumn('activity_log', 'ip_adress')) {
                    $payload['ip_adress'] = $request->ip();
                }

                DB::table('activity_log')->insert($payload);
            }

            File::append(
                storage_path('app/activity_log.jsonl'),
                json_encode($record, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL
            );
        } catch (\Throwable $e) {
            // Ignore logging errors so business requests still complete.
        }

        return $response;
    }

    private function shouldLog(string $method, string $path, ?array $beforeUser, ?array $afterUser): bool
    {
        if (Str::startsWith($path, ['live-location'])) {
            return false;
        }

        if (in_array($path, ['login', 'logout', 'login/google/callback'], true)) {
            return false;
        }

        if ($path === 'setting') {
            return false;
        }

        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return $beforeUser !== null || $afterUser !== null;
        }

        if ($method !== 'GET') {
            return false;
        }

        return $this->isStateChangingGet($path) && ($beforeUser !== null || $afterUser !== null);
    }

    private function isStateChangingGet(string $path): bool
    {
        return Str::is([
            'login/google/callback',
            'login/otp/resend',
            'employer/verify-email/*',
            'family/verify-email/*',
        ], $path);
    }

    private function resolveAction(string $method, string $path, string $routeUri): string
    {
        if ($method === 'GET' && Str::startsWith($path, 'employer/verify-email/')) {
            return 'account.verify_email_change';
        }

        if ($method === 'GET' && Str::startsWith($path, 'family/verify-email/')) {
            return 'family.verify_email_change';
        }

        $base = $routeUri !== '' ? $routeUri : $path;
        $normalized = Str::of($base)
            ->trim('/')
            ->replace(['/', '{', '}', ' '], '.')
            ->replaceMatches('/[^A-Za-z0-9_.]+/', '.')
            ->replaceMatches('/\.+/', '.')
            ->trim('.');

        return strtolower($method) . '.' . ($normalized !== '' ? $normalized : 'request');
    }

    private function snapshotUser($sessionUser): ?array
    {
        if (!is_array($sessionUser)) {
            return null;
        }

        return [
            'userid' => (int) ($sessionUser['userid'] ?? 0),
            'username' => (string) ($sessionUser['username'] ?? ''),
            'name' => (string) ($sessionUser['name'] ?? ''),
            'roleid' => isset($sessionUser['roleid']) ? (int) $sessionUser['roleid'] : null,
            'rolename' => (string) ($sessionUser['rolename'] ?? ''),
            'levelid' => isset($sessionUser['levelid']) ? (int) $sessionUser['levelid'] : null,
            'levelname' => (string) ($sessionUser['levelname'] ?? ''),
        ];
    }

    private function resolveCoordinate($value): ?float
    {
        if ($value === null) {
            return null;
        }

        $stringValue = trim((string) $value);
        if ($stringValue === '' || !is_numeric($stringValue)) {
            return null;
        }

        return (float) $stringValue;
    }
}

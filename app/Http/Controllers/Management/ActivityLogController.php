<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $currentRoleId = (int) session('authenticated_user.roleid');
        if (!in_array($currentRoleId, [1, 2], true)) {
            return redirect('/')->with('error', 'You do not have permission to view activity logs.');
        }

        $activityLogs = $this->loadActivityLogs($request);

        return view('admin.activity-log', [
            'pageClass' => 'page-family-tree page-management-activity-log',
            'systemSettings' => $this->getSystemSettings(),
            'activityLogs' => $activityLogs,
        ]);
    }

    private function loadActivityLogs(Request $request): LengthAwarePaginator
    {
        $perPage = max(10, min(100, (int) $request->query('per_page', 20)));
        $search = trim((string) $request->query('search', ''));
        $logs = $this->readActivityLogRecords();

        if ($search !== '') {
            $needle = mb_strtolower($search);
            $logs = $logs->filter(function (array $log) use ($needle): bool {
                $actor = (array) ($log['actor'] ?? []);
                $haystack = mb_strtolower(implode(' ', [
                    (string) ($log['action'] ?? ''),
                    (string) ($log['ip_address'] ?? ''),
                    (string) ($log['created_at'] ?? ''),
                    (string) ($actor['username'] ?? ''),
                    (string) ($actor['name'] ?? ''),
                    json_encode($log['context'] ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                ]));

                return str_contains($haystack, $needle);
            });
        }

        $sorted = $logs->sortByDesc(function (array $log): string {
            return (string) ($log['created_at'] ?? '');
        })->values();

        $page = (int) $request->query('page', 1);
        $items = $sorted->forPage($page, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $sorted->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }

    private function readActivityLogRecords(): Collection
    {
        $records = collect();

        if (DB::getSchemaBuilder()->hasTable('activity_log')) {
            $tableRecords = DB::table('activity_log')
                ->orderByDesc('created_at')
                ->limit(500)
                ->get()
                ->map(function ($row): array {
                    $context = $row->context ?? [];
                    if (is_string($context)) {
                        $decoded = json_decode($context, true);
                        $context = json_last_error() === JSON_ERROR_NONE ? $decoded : $context;
                    }

                    $userId = (int) ($row->user_id ?? $row->userid ?? 0);
                    $actor = [
                        'userid' => $userId,
                        'username' => '',
                        'name' => '',
                        'roleid' => null,
                        'rolename' => '',
                        'levelid' => null,
                        'levelname' => '',
                    ];

                    if ($userId > 0 && DB::getSchemaBuilder()->hasTable('user')) {
                        $user = DB::table('user')
                            ->where('userid', $userId)
                            ->select('userid', 'username')
                            ->first();

                        if ($user) {
                            $actor['username'] = (string) ($user->username ?? '');
                            $actor['name'] = (string) ($user->username ?? '');
                        }
                    }

                    return [
                        'actor' => $actor,
                        'action' => (string) ($row->action ?? ''),
                        'context' => $context,
                        'ip_address' => (string) ($row->ip_address ?? $row->ip_adress ?? ''),
                        'latitude' => $row->latitude ?? null,
                        'longitude' => $row->longitude ?? null,
                        'user_agent' => (string) ($row->user_agent ?? ''),
                        'created_at' => $this->normalizeTimestamp($row->created_at ?? null),
                    ];
                });

            $records = $records->merge($tableRecords);
        }

        $path = storage_path('app/activity_log.jsonl');
        if (File::exists($path)) {
            $lines = preg_split("/\r\n|\n|\r/", (string) File::get($path)) ?: [];
            $jsonRecords = collect($lines)
                ->filter(fn ($line) => trim((string) $line) !== '')
                ->map(function (string $line): ?array {
                    $decoded = json_decode($line, true);
                    if (!is_array($decoded)) {
                        return null;
                    }

                    $decoded['created_at'] = $this->normalizeTimestamp($decoded['created_at'] ?? null);
                    return $decoded;
                })
                ->filter()
                ->values();

            $records = $records->merge($jsonRecords);
        }

        return $records
            ->sortByDesc(fn (array $log) => (string) ($log['created_at'] ?? ''))
            ->values();
    }

    private function normalizeTimestamp($value): string
    {
        if ($value instanceof Carbon) {
            return $value->toDateTimeString();
        }

        $stringValue = trim((string) $value);
        if ($stringValue === '') {
            return '';
        }

        try {
            return Carbon::parse($stringValue)->toDateTimeString();
        } catch (\Throwable) {
            return $stringValue;
        }
    }
}

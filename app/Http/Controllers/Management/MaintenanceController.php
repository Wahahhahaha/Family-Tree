<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Repositories\FamilyMemberRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Output\BufferedOutput;

class MaintenanceController extends Controller
{
    public function __construct(FamilyMemberRepository $memberRepo)
    {
        $this->memberRepo = $memberRepo;
    }

    public function recycleBin(Request $request)
    {
        $activeTab = $request->query('tab', 'users');

        $data = [
            'pageClass' => 'page-family-tree',
            'systemSettings' => $this->getSystemSettings(),
            'activeTab' => $activeTab,
        ];

        if ($activeTab === 'users') {
            $data['deletedUsers'] = $this->memberRepo->usersQuery()
                ->whereNotNull('u.deleted_at')
                ->paginate(20, ['*'], 'users_page')
                ->withQueryString();
        } elseif ($activeTab === 'social') {
            $data['deletedSocials'] = $this->deletedRecordsPaginator($request, 'socialmedia', 'social_page');
        } elseif ($activeTab === 'levels') {
            $data['deletedLevels'] = $this->deletedRecordsPaginator($request, 'level', 'level_page');
        } elseif ($activeTab === 'roles') {
            $data['deletedRoles'] = $this->deletedRecordsPaginator($request, 'role', 'role_page');
        }

        return view('superadmin.recycle-bin', $data);
    }

    public function backupDatabase(Request $request)
    {
        return view('superadmin.backup-database', [
            'pageClass' => 'page-family-tree',
            'systemSettings' => $this->getSystemSettings(),
        ]);
    }

    public function console(Request $request)
    {
        if ((int) session('authenticated_user.roleid') !== 1) {
            return redirect('/')->with('error', 'Only superadmin can access console.');
        }

        return view('superadmin.console', [
            'pageClass' => 'page-family-tree',
            'systemSettings' => $this->getSystemSettings(),
        ]);
    }

    public function runConsole(Request $request)
    {
        if ((int) session('authenticated_user.roleid') !== 1) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'command' => ['required', 'string', 'min:1'],
            'tool' => ['nullable', 'string', 'in:artisan,tinker,php,sql'],
            'type' => ['nullable', 'string', 'in:artisan,tinker,php,sql'],
        ]);

        $tool = $this->normalizeConsoleTool((string) ($validated['tool'] ?? $validated['type'] ?? 'artisan'));
        $command = trim((string) $validated['command']);
        $startedAt = microtime(true);

        try {
            if ($tool === 'artisan') {
                $output = $this->runArtisanCommand($command);
            } elseif ($tool === 'sql') {
                $output = $this->runSqlCommand($command);
            } else {
                $output = $this->runPhpCommand($command);
            }

            return response()->json([
                'success' => true,
                'tool' => $tool,
                'command' => $command,
                'output' => $output,
                'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
                'executed_at' => now()->toDateTimeString(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'tool' => $tool,
                'command' => $command,
                'message' => $e->getMessage(),
                'output' => 'Error: ' . $e->getMessage(),
                'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
                'executed_at' => now()->toDateTimeString(),
            ], 500);
        }
    }

    private function runArtisanCommand(string $command): string
    {
        $buffer = new BufferedOutput();

        try {
            $exitCode = Artisan::call($command, [], $buffer);
            $output = trim((string) $buffer->fetch());

            if ($output === '') {
                return 'Command completed with exit code ' . $exitCode . '.';
            }

            return $output . PHP_EOL . PHP_EOL . 'Exit code: ' . $exitCode;
        } catch (\Throwable $e) {
            $parts = preg_split('/\s+/', trim($command)) ?: [];
            $cmd = array_shift($parts);
            if ($cmd === null || $cmd === '') {
                throw $e;
            }

            $params = [];
            foreach ($parts as $part) {
                if (str_starts_with($part, '--')) {
                    $option = explode('=', ltrim($part, '-'), 2);
                    $params['--' . $option[0]] = $option[1] ?? true;
                    continue;
                }

                $params[] = $part;
            }

            $fallbackBuffer = new BufferedOutput();
            $exitCode = Artisan::call($cmd, $params, $fallbackBuffer);
            $output = trim((string) $fallbackBuffer->fetch());

            if ($output === '') {
                return 'Command completed with exit code ' . $exitCode . '.';
            }

            return $output . PHP_EOL . PHP_EOL . 'Exit code: ' . $exitCode;
        }
    }

    private function runPhpCommand(string $command): string
    {
        ob_start();

        try {
            $code = trim($command);
            if (str_starts_with($code, '<?php')) {
                $code = preg_replace('/^<\\?php\\s*/', '', $code) ?? $code;
            }

            $result = eval($code . ';');
            if ($result !== null) {
                echo $this->formatConsoleOutput($result);
            }
        } catch (\Throwable $e) {
            echo 'Error: ' . $e->getMessage();
        }

        $output = trim((string) ob_get_clean());
        return $output !== '' ? $output : '[No output returned]';
    }

    private function runSqlCommand(string $command): string
    {
        $trimmed = ltrim($command);
        if ($trimmed === '') {
            return '[No output returned]';
        }

        if (preg_match('/^(select|show|describe|desc|explain|with)\b/i', $trimmed) === 1) {
            $rows = DB::select($command);
            return $this->formatConsoleOutput($rows);
        }

        $success = DB::statement($command);
        return $success ? 'Statement executed successfully.' : 'Statement returned false.';
    }

    private function normalizeConsoleTool(string $tool): string
    {
        $tool = strtolower(trim($tool));

        if ($tool === 'php') {
            return 'tinker';
        }

        if (!in_array($tool, ['artisan', 'tinker', 'sql'], true)) {
            return 'artisan';
        }

        return $tool;
    }

    private function formatConsoleOutput($value): string
    {
        if ($value === null) {
            return '[No output returned]';
        }

        if ($value instanceof \Illuminate\Contracts\Support\Arrayable) {
            $value = $value->toArray();
        }

        if ($value instanceof \Illuminate\Support\Collection) {
            $value = $value->toArray();
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_int($value) || is_float($value) || is_string($value)) {
            $text = trim((string) $value);
            return $text !== '' ? $text : '[No output returned]';
        }

        if (is_array($value)) {
            $json = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            if ($json !== false) {
                return $json;
            }

            return trim((string) print_r($value, true));
        }

        if (is_object($value)) {
            if (method_exists($value, '__toString')) {
                try {
                    $stringValue = trim((string) $value);
                    if ($stringValue !== '') {
                        return $stringValue;
                    }
                } catch (\Throwable $e) {
                    // Ignore string cast failures and fall through to JSON/print_r.
                }
            }

            $json = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            if ($json !== false) {
                return $json;
            }

            return trim((string) print_r($value, true));
        }

        $text = trim((string) print_r($value, true));
        return $text !== '' ? $text : '[No output returned]';
    }

    private function deletedRecordsPaginator(Request $request, string $table, string $pageName, int $perPage = 20): LengthAwarePaginator
    {
        $currentPage = max(1, (int) $request->query($pageName, 1));

        if (!Schema::hasColumn($table, 'deleted_at')) {
            return (new LengthAwarePaginator([], 0, $perPage, $currentPage, [
                'path' => $request->url(),
                'pageName' => $pageName,
            ]))->appends($request->query());
        }

        return DB::table($table)
            ->whereNotNull('deleted_at')
            ->paginate($perPage, ['*'], $pageName)
            ->withQueryString();
    }
}

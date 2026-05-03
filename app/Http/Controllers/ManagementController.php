<?php

namespace App\Http\Controllers;

use App\Exports\UsersExport;
use App\Imports\UsersImport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\FamilyMember;
use App\Models\User;
use App\Models\Employer;
use App\Models\Role;
use App\Models\Level;
use App\Models\ActivityLog;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class ManagementController extends Controller
{

    public function userManagement(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $currentRoleId = (int) session('authenticated_user.roleid');
        $allowedRoles = [1, 2, 3];
        $isFamilyHead = $currentRoleId === 3;

        if (!in_array($currentRoleId, $allowedRoles, true)) {
            return redirect('/')->with('error', 'You do not have permission to access user management.');
        }

        $levels = DB::table('level')
            ->selectRaw('MIN(levelid) as levelid, levelname')
            ->groupBy('levelname')
            ->orderBy('levelname')
            ->get();

        $roles = DB::table('role')
            ->orderBy('roleid')
            ->get();

        if ($isFamilyHead) {
            $levels = $levels->filter(function ($level) {
                return in_array((int) $level->levelid, [2, 4], true);
            })->values();

            $roles = $roles->filter(function ($role) {
                return in_array((int) $role->roleid, [2, 3, 4], true);
            })->values();
        }

        $userSearchKeyword = trim((string) $request->query('search', ''));
        $selectedUserRoleFilter = strtolower(trim((string) $request->query('role', '')));
        $allowedUserRoleFilters = ['superadmin', 'admin', 'familymember'];
        if ($selectedUserRoleFilter !== '' && !in_array($selectedUserRoleFilter, $allowedUserRoleFilters, true)) {
            $selectedUserRoleFilter = '';
        }

        $usersQuery = $this->usersQuery()->whereNull('u.deleted_at');
        if ($isFamilyHead) {
            $usersQuery->where('u.levelid', 2);
        }

        if ($userSearchKeyword !== '') {
            $usersQuery->where(function ($query) use ($userSearchKeyword) {
                $keyword = '%' . $userSearchKeyword . '%';
                $query
                    ->where('u.username', 'like', $keyword)
                    ->orWhere('e.name', 'like', $keyword)
                    ->orWhere('fm.name', 'like', $keyword)
                    ->orWhere('e.email', 'like', $keyword)
                    ->orWhere('fm.email', 'like', $keyword)
                    ->orWhere('e.phonenumber', 'like', $keyword)
                    ->orWhere('fm.phonenumber', 'like', $keyword)
                    ->orWhere('l.levelname', 'like', $keyword)
                    ->orWhere('r.rolename', 'like', $keyword);
            });
        }

        if ($selectedUserRoleFilter === 'superadmin') {
            $usersQuery->where('e.roleid', 1);
        } elseif ($selectedUserRoleFilter === 'admin') {
            $usersQuery->where('e.roleid', 2);
        } elseif ($selectedUserRoleFilter === 'familymember') {
            $usersQuery->where(function ($query) {
                $query
                    ->whereNotNull('fm.memberid')
                    ->orWhereIn('u.levelid', [2, 4]);
            });
        }

        $perPage = 20;
        $users = $usersQuery->paginate($perPage)->withQueryString();

        $systemSettings = $this->getSystemSettings();

        if ($request->ajax() || $request->expectsJson() || $request->query('ajax') === '1') {
            return response()->json([
                'rows_html' => view('admin.partials.user-table-rows', ['users' => $users])->render(),
                'pagination_html' => view('admin.partials.user-pagination', ['users' => $users])->render(),
                'total' => $users->total(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
            ]);
        }

        return view('admin.user-management', [
            'pageTitle' => 'User Management',
            'pageClass' => 'page-family-tree',
            'systemSettings' => $systemSettings,
            'users' => $users,
            'levels' => $levels,
            'roles' => $roles,
            'userSearchKeyword' => $userSearchKeyword,
            'selectedUserRoleFilter' => $selectedUserRoleFilter,
        ]);
    }

    public function exportUsers(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $currentRoleId = (int) session('authenticated_user.roleid');
        $allowedRoles = [1, 2, 3];
        $isFamilyHead = $currentRoleId === 3;

        if (!in_array($currentRoleId, $allowedRoles, true)) {
            return redirect('/')->with('error', 'You do not have permission to export users.');
        }

        $this->logActivity($request, 'management.export_users', [
            'is_family_head' => $isFamilyHead,
        ]);

        $fileName = 'users-' . now()->format('Ymd-His') . '.xlsx';
        return Excel::download(new UsersExport($isFamilyHead), $fileName);
    }

    public function importUsers(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $currentRoleId = (int) session('authenticated_user.roleid');
        $allowedRoles = [1, 2, 3];

        if (!in_array($currentRoleId, $allowedRoles, true)) {
            return redirect('/')->with('error', 'You do not have permission to import users.');
        }

        $validator = Validator::make($request->all(), [
            'import_file' => ['required', 'file', 'mimes:xlsx', 'max:5120'],
        ], [
            'import_file.required' => 'Please choose an Excel file first.',
            'import_file.file' => 'Uploaded file is invalid.',
            'import_file.mimes' => 'File must be .xlsx format.',
            'import_file.max' => 'Maximum file size is 5MB.',
        ]);

        if ($validator->fails()) {
            return redirect('/management/users')
                ->withErrors($validator, 'userImport')
                ->with('openImportModal', true);
        }

        try {
            $import = new UsersImport();
            Excel::import($import, $request->file('import_file'));

            $message = 'Import completed. Added ' . $import->getImportedCount() . ' user(s)';
            if ($import->getSkippedCount() > 0) {
                $message .= ', skipped ' . $import->getSkippedCount() . ' row(s).';
            } else {
                $message .= '.';
            }

            $this->logActivity($request, 'management.import_users', [
                'imported_count' => (int) $import->getImportedCount(),
                'skipped_count' => (int) $import->getSkippedCount(),
                'file_name' => (string) $request->file('import_file')->getClientOriginalName(),
            ]);

            return redirect('/management/users')->with('success', $message);
        } catch (\Throwable $e) {
            return redirect('/management/users')
                ->with('error', 'Failed to import users. Please check file format and template.');
        }
    }

    public function activityLog(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $currentRoleId = (int) session('authenticated_user.roleid');
        if (!in_array($currentRoleId, [1, 2], true)) {
            return redirect('/management/users')->with('error', 'Only admin and superadmin can access activity log.');
        }

        $systemSettings = $this->getSystemSettings();
        $activityLogViewMode = $currentRoleId === 1 ? 'all users' : 'non-superadmin users';
        $perPage = 20;
        $allActivityLogs = collect($this->filterActivityLogsForRole($this->readActivityLogs(null), $currentRoleId))->values();
        $currentPage = max(1, (int) $request->query('page', 1));
        $currentPageItems = $allActivityLogs->forPage($currentPage, $perPage)->values();

        $activityLogs = new LengthAwarePaginator(
            $currentPageItems,
            $allActivityLogs->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        if ($request->ajax() || $request->expectsJson() || $request->query('ajax') === '1') {
            return response()->json([
                'rows_html' => view('admin.partials.activity-log-table-rows', ['activityLogs' => $activityLogs])->render(),
                'pagination_html' => view('admin.partials.activity-log-pagination', ['activityLogs' => $activityLogs])->render(),
                'total' => $activityLogs->total(),
                'current_page' => $activityLogs->currentPage(),
                'last_page' => $activityLogs->lastPage(),
            ]);
        }

        return view('admin.activity-log', [
            'pageTitle' => 'Activity Log',
            'pageClass' => 'page-family-tree',
            'systemSettings' => $systemSettings,
            'activityLogs' => $activityLogs,
            'activityLogViewMode' => $activityLogViewMode,
        ]);
    }

    public function recycleBin(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $currentRoleId = (int) session('authenticated_user.roleid');
        if (!in_array($currentRoleId, [1, 2], true)) {
            return redirect('/management/users')->with('error', 'Only admin and superadmin can access recycle bin.');
        }

        $deletedUsersQuery = $this->usersQuery()->whereNotNull('u.deleted_at');
        $perPage = 20;
        $deletedUsers = $deletedUsersQuery->paginate($perPage)->withQueryString();
        $systemSettings = $this->getSystemSettings();

        if ($request->ajax() || $request->expectsJson() || $request->query('ajax') === '1') {
            return response()->json([
                'rows_html' => view('superadmin.partials.recycle-bin-table-rows', ['users' => $deletedUsers])->render(),
                'pagination_html' => view('admin.partials.user-pagination', ['users' => $deletedUsers])->render(),
                'total' => $deletedUsers->total(),
                'current_page' => $deletedUsers->currentPage(),
                'last_page' => $deletedUsers->lastPage(),
            ]);
        }

        return view('superadmin.recycle-bin', [
            'pageTitle' => 'Recycle Bin',
            'pageClass' => 'page-family-tree',
            'systemSettings' => $systemSettings,
            'deletedUsers' => $deletedUsers,
        ]);
    }

    public function backupDatabase(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        if ((int) session('authenticated_user.roleid') !== 1) {
            return redirect('/management/users')->with('error', 'Only superadmin can access backup database.');
        }

        $systemSettings = $this->getSystemSettings();

        return view('superadmin.backup-database', [
            'pageTitle' => 'Backup Database',
            'pageClass' => 'page-family-tree',
            'systemSettings' => $systemSettings,
        ]);
    }

    public function exportDatabaseBackup(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        if ((int) session('authenticated_user.roleid') !== 1) {
            return redirect('/management/users')->with('error', 'Only superadmin can export database backup.');
        }

        $defaultConnection = (string) config('database.default');
        $driver = (string) config("database.connections.{$defaultConnection}.driver");
        $databaseName = (string) config("database.connections.{$defaultConnection}.database");

        if ($driver !== 'mysql') {
            return redirect('/management/backup-database')->with('error', 'Database export is currently available only for MySQL.');
        }

        if ($databaseName === '') {
            return redirect('/management/backup-database')->with('error', 'Database name is not configured.');
        }

        try {
            $tables = $this->listMysqlTables($databaseName);
        } catch (\Throwable $e) {
            return redirect('/management/backup-database')->with('error', 'Failed to read database tables for export.');
        }

        if (count($tables) === 0) {
            return redirect('/management/backup-database')->with('error', 'No tables found to export.');
        }

        $this->logActivity($request, 'superadmin.export_database_backup', [
            'database' => $databaseName,
            'tables_count' => count($tables),
        ]);

        $fileName = 'backup_' . $databaseName . '_' . now()->format('Ymd_His') . '.sql';

        return response()->streamDownload(function () use ($tables) {
            $pdo = DB::connection()->getPdo();

            echo "-- Family Tree Database Backup\n";
            echo '-- Generated at: ' . now()->toDateTimeString() . "\n";
            echo "SET FOREIGN_KEY_CHECKS=0;\n\n";

            foreach ($tables as $tableName) {
                $escapedTable = $this->escapeMysqlIdentifier($tableName);
                $createRows = DB::select('SHOW CREATE TABLE ' . $escapedTable);
                if (count($createRows) === 0) {
                    continue;
                }

                $createData = (array) $createRows[0];
                $createSql = (string) ($createData['Create Table'] ?? '');

                echo "-- ----------------------------\n";
                echo "-- Table structure for " . $tableName . "\n";
                echo "-- ----------------------------\n";
                echo 'DROP TABLE IF EXISTS ' . $escapedTable . ";\n";
                echo $createSql . ";\n\n";

                $rows = DB::table($tableName)->get();
                if ($rows->isEmpty()) {
                    continue;
                }

                $columns = array_keys((array) $rows->first());
                $escapedColumns = array_map(fn ($column) => $this->escapeMysqlIdentifier((string) $column), $columns);

                echo "-- ----------------------------\n";
                echo "-- Records of " . $tableName . "\n";
                echo "-- ----------------------------\n";

                foreach ($rows->chunk(200) as $chunk) {
                    $valuesSql = [];
                    foreach ($chunk as $row) {
                        $rowData = (array) $row;
                        $rowValues = [];
                        foreach ($columns as $column) {
                            $rowValues[] = $this->toMysqlSqlValue($rowData[$column] ?? null, $pdo);
                        }
                        $valuesSql[] = '(' . implode(', ', $rowValues) . ')';
                    }

                    echo 'INSERT INTO ' . $escapedTable
                        . ' (' . implode(', ', $escapedColumns) . ') VALUES '
                        . implode(",\n", $valuesSql) . ";\n";
                }

                echo "\n";
            }

            echo "SET FOREIGN_KEY_CHECKS=1;\n";
        }, $fileName, [
            'Content-Type' => 'application/sql',
        ]);
    }

    public function importDatabaseBackup(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        if ((int) session('authenticated_user.roleid') !== 1) {
            return redirect('/management/users')->with('error', 'Only superadmin can import database backup.');
        }

        $request->validate([
            'sql_file' => ['required', 'file', 'max:51200'],
        ], [
            'sql_file.required' => 'Please choose an SQL file to import.',
            'sql_file.file' => 'Invalid upload file.',
            'sql_file.max' => 'SQL file max size is 50MB.',
        ]);

        $file = $request->file('sql_file');
        $extension = strtolower((string) $file->getClientOriginalExtension());
        if ($extension !== 'sql') {
            throw ValidationException::withMessages([
                'sql_file' => 'File must use .sql extension.',
            ]);
        }

        $sqlContent = trim((string) File::get($file->getRealPath()));
        if ($sqlContent === '') {
            throw ValidationException::withMessages([
                'sql_file' => 'SQL file is empty.',
            ]);
        }

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::unprepared($sqlContent);
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        } catch (\Throwable $e) {
            try {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            } catch (\Throwable $inner) {
                // Ignore nested failure when restoring FK checks.
            }

            return redirect('/management/backup-database')->with('error', 'Failed to import SQL backup. Please verify SQL file format.');
        }

        $this->logActivity($request, 'superadmin.import_database_backup', [
            'file_name' => (string) $file->getClientOriginalName(),
            'file_size' => (int) $file->getSize(),
        ]);

        return redirect('/management/backup-database')->with('success', 'Database backup imported successfully.');
    }

    public function permissionSetting(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        if ((int) session('authenticated_user.roleid') !== 1) {
            return redirect('/management/users')->with('error', 'Only superadmin can access permission settings.');
        }

        $systemSettings = $this->getSystemSettings();
        $permissionMenus = $this->getPermissionMenuOptions();
        $permissionSettings = $this->getRolePermissionSettings();

        return view('superadmin.permission', [
            'pageTitle' => 'Permission Settings',
            'pageClass' => 'page-family-tree',
            'systemSettings' => $systemSettings,
            'permissionSettings' => $permissionSettings,
            'permissionMenus' => $permissionMenus,
        ]);
    }

    public function updatePermissionSetting(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        if ((int) session('authenticated_user.roleid') !== 1) {
            return redirect('/management/users')->with('error', 'Only superadmin can update permission settings.');
        }

        $menuOptions = $this->getPermissionMenuOptions();
        $menuKeys = array_keys($menuOptions);
        $roleKeys = ['superadmin', 'admin', 'family_member'];
        $inputPermissions = (array) $request->input('permissions', []);

        $newPermissions = [];
        foreach ($roleKeys as $roleKey) {
            $newPermissions[$roleKey] = [];
            foreach ($menuKeys as $menuKey) {
                $newPermissions[$roleKey][$menuKey] = isset($inputPermissions[$menuKey][$roleKey]);
            }
        }

        $oldPermissions = $this->getRolePermissionSettings();
        $this->saveRolePermissionSettings($newPermissions);

        if ($oldPermissions !== $newPermissions) {
            $this->logActivity($request, 'superadmin.update_permission_setting', [
                'permissions_old' => $oldPermissions,
                'permissions_new' => $newPermissions,
            ]);
        }

        return redirect('/management/permission')->with('success', 'Permission settings updated successfully.');
    }

    public function updateSystemSetting(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        if ((int) session('authenticated_user.roleid') !== 1) {
            return redirect('/management/users')->with('error', 'Only superadmin can update settings.');
        }

        $validated = $request->validate([
            'website_name' => ['nullable', 'string', 'max:255'],
            'system_contact' => ['nullable', 'string', 'max:255'],
            'system_manager' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
        ], [
            'website_name.max' => 'Website name max 255 characters.',
            'system_contact.max' => 'System contact max 255 characters.',
            'system_manager.max' => 'System manager max 255 characters.',
            'logo.image' => 'Logo must be an image file.',
            'logo.mimes' => 'Logo must be jpg, jpeg, png, webp, or svg.',
            'logo.max' => 'Logo max size is 2MB.',
        ]);

        $settings = $this->getSystemSettings();
        $oldWebsiteName = (string) ($settings['website_name'] ?? '');
        $oldLogoPath = (string) ($settings['logo_path'] ?? '');
        $oldSystemContact = (string) ($settings['systemcontact'] ?? $settings['system_contact'] ?? '');
        $oldSystemManager = (string) ($settings['systemmanager'] ?? $settings['system_manager'] ?? '');

        $settings['website_name'] = array_key_exists('website_name', $validated)
            ? trim((string) $validated['website_name'])
            : $oldWebsiteName;
        if ($settings['website_name'] === '') {
            $settings['website_name'] = $oldWebsiteName !== '' ? $oldWebsiteName : 'Family Tree System';
        }

        $settings['system_contact'] = array_key_exists('system_contact', $validated)
            ? trim((string) $validated['system_contact'])
            : $oldSystemContact;
        $settings['system_manager'] = array_key_exists('system_manager', $validated)
            ? trim((string) $validated['system_manager'])
            : $oldSystemManager;

        if ($request->hasFile('logo')) {
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/system';
            File::ensureDirectoryExists($uploadDir);

            $this->deleteStoredSystemLogoFile($oldLogoPath);

            $ext = $request->file('logo')->getClientOriginalExtension();
            $fileName = 'system_logo_' . time() . '.' . $ext;
            $request->file('logo')->move($uploadDir, $fileName);
            $settings['logo_path'] = '/uploads/system/' . $fileName;
        } else {
            $settings['logo_path'] = $oldLogoPath;
        }

        $this->saveSystemSettings($settings);

        $activityChanges = [];

        if ($oldWebsiteName !== (string) $settings['website_name']) {
            $activityChanges[] = 'Changed website name from "' . ($oldWebsiteName !== '' ? $oldWebsiteName : 'empty') . '" to "' . ((string) $settings['website_name'] !== '' ? (string) $settings['website_name'] : 'empty') . '"';
        }

        if ($oldLogoPath !== (string) ($settings['logo_path'] ?? '')) {
            $activityChanges[] = empty($oldLogoPath)
                ? 'Added website logo'
                : 'Changed website logo';
        }

        if ($oldSystemContact !== (string) ($settings['system_contact'] ?? '')) {
            $activityChanges[] = 'Changed system contact';
        }

        if ($oldSystemManager !== (string) ($settings['system_manager'] ?? '')) {
            $activityChanges[] = 'Changed system manager';
        }

        if (!empty($activityChanges)) {
            $this->logActivity($request, 'superadmin.update_setting', [
                'website_name_old' => $oldWebsiteName,
                'website_name_new' => (string) $settings['website_name'],
                'logo_path_old' => $oldLogoPath,
                'logo_path_new' => (string) ($settings['logo_path'] ?? ''),
                'system_contact_old' => $oldSystemContact,
                'system_contact_new' => (string) ($settings['system_contact'] ?? ''),
                'system_manager_old' => $oldSystemManager,
                'system_manager_new' => (string) ($settings['system_manager'] ?? ''),
                'changes' => $activityChanges,
            ]);
        }

        return redirect()->route('management.setting')->with('success', 'System settings updated successfully.');
    }

    public function dataMaster(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $currentRoleId = (int) session('authenticated_user.roleid');
        if (!in_array($currentRoleId, [1, 2], true)) {
            return redirect('/')->with('error', 'You do not have permission to access data master.');
        }

        $systemSettings = $this->getSystemSettings();

        return view('superadmin.data-master', [
            'pageTitle' => 'Data Master',
            'pageClass' => 'page-family-tree page-management-data-master',
            'systemSettings' => $systemSettings,
        ]);
    }

    private function saveSystemSettings(array $settings): void
    {
        $normalizedSettings = [
            'website_name' => (string) ($settings['website_name'] ?? 'Family Tree System'),
            'logo_path' => (string) ($settings['logo_path'] ?? ''),
            'system_contact' => (string) ($settings['system_contact'] ?? ''),
            'system_manager' => (string) ($settings['system_manager'] ?? ''),
        ];

        $currentSystem = DB::table('system')
            ->orderBy('systemid')
            ->first();

        $payload = [
            'systemname' => $normalizedSettings['website_name'],
            'systemlogo' => $normalizedSettings['logo_path'],
            'systemcontact' => $normalizedSettings['system_contact'],
            'systemmanager' => $normalizedSettings['system_manager'],
        ];

        if ($currentSystem) {
            DB::table('system')
                ->where('systemid', $currentSystem->systemid)
                ->update($payload);
        } else {
            DB::table('system')->insert($payload);
        }

        $settingsPath = storage_path('app/system_settings.json');
        File::put($settingsPath, json_encode($normalizedSettings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function deleteStoredSystemLogoFile(string $path): void
    {
        $path = trim(str_replace('\\', '/', $path));
        if ($path === '') {
            return;
        }

        $candidatePaths = [];

        if (str_starts_with($path, '/uploads/system/')) {
            $candidatePaths[] = public_path(ltrim($path, '/'));
        }

        if (str_starts_with($path, '/storage/uploads/system/')) {
            $candidatePaths[] = storage_path('app/public/' . ltrim(substr($path, strlen('/storage/')), '/'));
        }

        if (str_starts_with($path, str_replace('\\', '/', public_path()))) {
            $candidatePaths[] = $path;
        }

        if (str_starts_with($path, str_replace('\\', '/', storage_path('app/public')))) {
            $candidatePaths[] = $path;
        }

        foreach (array_unique($candidatePaths) as $candidatePath) {
            if (File::exists($candidatePath)) {
                File::delete($candidatePath);
                break;
            }
        }
    }

    public function storeUser(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $currentRoleId = (int) session('authenticated_user.roleid');
        $allowedRoles = [1, 2, 3];
        $isFamilyHead = $currentRoleId === 3;

        if (!in_array($currentRoleId, $allowedRoles, true)) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'message' => 'You do not have permission to add users.',
                ], 403);
            }

            return redirect('/')->with('error', 'You do not have permission to add users.');
        }

        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string', 'max:255', 'unique:user,username'],
            'levelid' => ['required', 'integer', 'exists:level,levelid'],
            'roleid' => ['nullable', 'integer', 'exists:role,roleid'],
            'email' => ['nullable', 'email', 'max:255'],
            'phonenumber' => ['nullable', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'string', 'in:male,female'],
            'address' => ['nullable', 'string', 'max:255'],
            'marital_status' => ['nullable', 'string', 'max:255'],
            'birthdate' => ['nullable', 'date', 'before_or_equal:today'],
            'birthplace' => ['nullable', 'string', 'max:255'],
        ], [
            'username.required' => 'Username is required.',
            'username.unique' => 'Username already exists.',
            'levelid.required' => 'Level is required.',
            'levelid.exists' => 'Selected level is invalid.',
            'roleid.exists' => 'Selected role is invalid.',
            'email.email' => 'Email format is invalid.',
            'gender.in' => 'Gender must be male or female.',
            'birthdate.before_or_equal' => 'Birthdate must be today or earlier.',
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            return redirect('/management/users')
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();

        $selectedLevel = DB::table('level')
            ->where('levelid', (int) $validated['levelid'])
            ->first();

        $selectedLevelId = (int) ($selectedLevel->levelid ?? 0);
        if (!$isFamilyLevel && empty($validated['roleid'])) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => ['roleid' => ['Role is required.']],
                ], 422);
            }

            return redirect('/management/users')
                ->withErrors(['roleid' => 'Role is required.'])
                ->withInput();
        }

        $isFamilyLevel = $selectedLevel && in_array($selectedLevelId, [2, 4], true);
        $allowedRoleIds = $isFamilyLevel ? [2, 3, 4] : [1, 2];
        $isEmployerLevel = !$isFamilyLevel;

        if ($isFamilyHead) {
            if (!$isFamilyLevel) {
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'message' => 'Family head can only add family users.',
                    ], 422);
                }

                return redirect('/management/users')->with('error', 'Family head can only add family users.');
            }

            if (!empty($validated['roleid']) && !in_array((int) $validated['roleid'], [2, 3, 4], true)) {
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'message' => 'Family head can only assign family roles.',
                    ], 422);
                }

                return redirect('/management/users')->with('error', 'Family head can only assign family roles.');
            }
        }

        if (!empty($validated['roleid']) && !in_array((int) $validated['roleid'], $allowedRoleIds, true)) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => ['roleid' => ['Role does not match the selected level.']],
                ], 422);
            }

            return redirect('/management/users')->with('error', 'Role does not match the selected level.');
        }

        if ($isEmployerLevel && (empty($validated['email']) || empty($validated['phonenumber']))) {
            $errors = [];
            if (empty($validated['name'])) {
                $errors['name'] = 'Name is required for Employer level.';
            }
            if (empty($validated['email'])) {
                $errors['email'] = 'Email is required for Employer level.';
            }
            if (empty($validated['phonenumber'])) {
                $errors['phonenumber'] = 'Phone number is required for Employer level.';
            }

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => $errors,
                ], 422);
            }

            return redirect('/management/users')
                ->withErrors($errors)
                ->withInput();
        }

        if ($isFamilyLevel) {
            $errors = [];
            $requiredFields = [
                'name' => 'Name is required for Family level.',
                'email' => 'Email is required for Family level.',
                'phonenumber' => 'Phone number is required for Family level.',
                'gender' => 'Gender is required for Family level.',
                'address' => 'Address is required for Family level.',
                'marital_status' => 'Marital status is required for Family level.',
                'birthdate' => 'Birthdate is required for Family level.',
                'birthplace' => 'Birthplace is required for Family level.',
            ];

            foreach ($requiredFields as $field => $message) {
                if (empty($validated[$field])) {
                    $errors[$field] = $message;
                }
            }

            if (!empty($errors)) {
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'message' => 'Validation failed.',
                        'errors' => $errors,
                    ], 422);
                }

                return redirect('/management/users')
                    ->withErrors($errors)
                    ->withInput();
            }
        }

        DB::transaction(function () use ($validated, $isFamilyLevel) {
            $userId = User::query()->insertGetId([
                'username' => $validated['username'],
                'password' => Hash::make($validated['username']),
                'levelid' => (int) $validated['levelid'],
            ]);

            if ($isFamilyLevel) {
                $birthdate = Carbon::parse($validated['birthdate']);
                $picture = $validated['gender'] === 'male'
                    ? '/images/avatar-male.svg'
                    : '/images/avatar-female.svg';

                FamilyMember::insert([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'phonenumber' => $validated['phonenumber'],
                    'gender' => $validated['gender'],
                    'birthdate' => $birthdate->toDateString(),
                    'birthplace' => $validated['birthplace'],
                    'address' => $validated['address'],
                    'job' => null,
                    'education_status' => null,
                    'life_status' => 'alive',
                    'marital_status' => $validated['marital_status'],
                    'deaddate' => null,
                    'picture' => $picture,
                    'userid' => $userId,
                ]);

                Employer::updateOrInsert(
                    ['userid' => $userId],
                    [
                        'name' => $validated['name'],
                        'email' => $validated['email'] ?? '',
                        'phonenumber' => $validated['phonenumber'] ?? '',
                        'roleid' => !empty($validated['roleid']) ? (int) $validated['roleid'] : null,
                    ]
                );
            } else {
                Employer::insert([
                    'name' => $validated['name'],
                    'email' => $validated['email'] ?? '',
                    'phonenumber' => $validated['phonenumber'] ?? '',
                    'roleid' => (int) $validated['roleid'],
                    'userid' => $userId,
                ]);
            }
        });

        $this->logActivity($request, 'management.create_user', [
            'username' => (string) $validated['username'],
            'levelid' => (int) $validated['levelid'],
            'roleid' => isset($validated['roleid']) ? (int) $validated['roleid'] : null,
        ]);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'message' => 'New user has been added.',
            ]);
        }

        return redirect('/management/users')->with('success', 'New user has been added.');
    }

    public function updateUser(Request $request, $userid)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $currentRoleId = (int) session('authenticated_user.roleid');
        $allowedRoles = [1, 2, 3];
        $isFamilyHead = $currentRoleId === 3;
        if (!in_array($currentRoleId, $allowedRoles, true)) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'message' => 'You do not have permission to update users.',
                ], 403);
            }

            return redirect('/')->with('error', 'You do not have permission to update users.');
        }

        $targetUserId = (int) $userid;
        $targetUser = User::query()
            ->where('userid', $targetUserId)
            ->whereNull('deleted_at')
            ->first();
        if (!$targetUser) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'message' => 'User not found.',
                ], 404);
            }

            return redirect('/management/users')->with('error', 'User not found.');
        }

        $isFamilyMember = (int) ($targetUser->levelid ?? 0) === 2;
        $currentEmployer = Employer::query()
            ->where('userid', $targetUserId)
            ->select('roleid')
            ->first();
        $currentLevelId = (int) ($targetUser->levelid ?? 0);
        $currentRoleIdValue = $currentEmployer ? (int) ($currentEmployer->roleid ?? 0) : null;
        $isInlineProfileEdit = $request->boolean('_from_home')
            || (!$request->has('levelid') && !$request->has('roleid'));
        if ($isFamilyHead && !$isFamilyMember) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Family head can only edit family users.',
                ], 422);
            }

            return redirect('/management/users')->with('error', 'Family head can only edit family users.');
        }

        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string', 'max:255', 'unique:user,username,' . $targetUserId . ',userid'],
            'levelid' => [$isInlineProfileEdit ? 'nullable' : 'required', 'integer', 'exists:level,levelid'],
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phonenumber' => ['nullable', 'string', 'max:255'],
            'roleid' => ['nullable', 'integer', 'exists:role,roleid'],
            'bloodtype' => ['nullable', 'string', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'job' => ['nullable', 'string', 'max:255'],
            'education_status' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'string', 'in:male,female'],
            'birthdate' => ['nullable', 'date', 'before_or_equal:today'],
            'birthplace' => ['nullable', 'string', 'max:255'],
            'marital_status' => ['nullable', 'string', 'max:255'],
            'life_status' => ['nullable', 'string', 'in:alive,deceased'],
            'deaddate' => ['nullable', 'date'],
            'grave_location_url' => ['nullable', 'url', 'max:2048'],
            'child_parenting_mode' => ['nullable', 'string', 'in:with_current_partner,single_parent'],
            'picture' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'username.required' => 'Username is required.',
            'username.unique' => 'Username already exists.',
            'levelid.exists' => 'Selected level is invalid.',
            'email.email' => 'Email format is invalid.',
            'bloodtype.in' => 'Blood type must be one of: A+, A-, B+, B-, AB+, AB-, O+, or O-.',
            'gender.in' => 'Gender must be male or female.',
            'birthdate.before_or_equal' => 'Birthdate must be today or earlier.',
            'life_status.in' => 'Life status must be alive or deceased.',
            'deaddate.date' => 'Death date must be a valid date.',
            'grave_location_url.url' => 'Grave location must be a valid URL.',
            'child_parenting_mode.in' => 'Child status must be single parent or with current partner.',
            'picture.image' => 'Profile picture must be an image file.',
            'picture.mimes' => 'Profile picture must be a JPG, JPEG, PNG, or WebP file.',
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            return redirect('/management/users')->withErrors($validator)->withInput();
        }
        $validated = $validator->validated();
        $selectedLevelId = (int) ($validated['levelid'] ?? $currentLevelId);
        $selectedLevel = DB::table('level')
            ->where('levelid', $selectedLevelId)
            ->first();
        $isFamilyLevel = $selectedLevel && in_array($selectedLevelId, [2, 4], true);
        $selectedRoleId = array_key_exists('roleid', $validated)
            ? (int) $validated['roleid']
            : $currentRoleIdValue;

        $allowedRoleIds = $isFamilyLevel ? [2, 3, 4] : [1, 2];
        if (!$isInlineProfileEdit && !$isFamilyLevel && empty($validated['roleid'])) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => ['roleid' => ['Role is required.']],
                ], 422);
            }

            return redirect('/management/users')
                ->withErrors(['roleid' => 'Role is required.'])
                ->withInput();
        }

        if ($selectedRoleId !== null && !in_array($selectedRoleId, $allowedRoleIds, true)) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => ['roleid' => ['Role does not match the selected level.']],
                ], 422);
            }

            return redirect('/management/users')->with('error', 'Role does not match the selected level.');
        }

        $newEmail = strtolower(trim((string) ($validated['email'] ?? '')));
        $newPhone = trim((string) ($validated['phonenumber'] ?? ''));
        $uploadedPicturePath = null;

        if ($request->hasFile('picture') && $request->file('picture')->isValid()) {
            $pictureFile = $request->file('picture');
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/family-member';
            if (!File::isDirectory($uploadDir)) {
                File::makeDirectory($uploadDir, 0755, true, true);
            }

            $extension = strtolower((string) ($pictureFile->getClientOriginalExtension() ?: 'jpg'));
            $fileName = 'family-' . $targetUserId . '-' . Str::uuid()->toString() . '.' . $extension;
            $pictureFile->move($uploadDir, $fileName);
            $uploadedPicturePath = '/uploads/family-member/' . $fileName;
        }

        try {
            DB::transaction(function () use ($validated, $selectedLevelId, $selectedRoleId, $targetUserId, $isFamilyLevel, $newEmail, $newPhone, $request, $uploadedPicturePath, $currentRoleIdValue) {
            User::query()
                ->where('userid', $targetUserId)
                ->update([
                    'username' => trim((string) $validated['username']),
                    'levelid' => $selectedLevelId,
                ]);

            if ($isFamilyLevel) {
                $familyMember = FamilyMember::query()->where('userid', $targetUserId)->first();
                if (!$familyMember) {
                    throw new \RuntimeException('Family member data not found.');
                }

                if ($newEmail !== '') {
                    $emailExistsInFamily = FamilyMember::query()
                        ->where('userid', '!=', $targetUserId)
                        ->whereRaw('LOWER(email) = ?', [$newEmail])
                        ->exists();
                    $emailExistsInEmployer = Employer::query()
                        ->whereRaw('LOWER(email) = ?', [$newEmail])
                        ->exists();
                    if ($emailExistsInFamily || $emailExistsInEmployer) {
                        throw ValidationException::withMessages([
                            'email' => 'This email is already in use.',
                        ]);
                    }
                }

                if ($newPhone !== '') {
                    $existingPhoneAccount = $this->findAccountByPhoneNumber($newPhone);
                    if ($existingPhoneAccount && (int) ($existingPhoneAccount->userid ?? 0) !== $targetUserId) {
                        throw ValidationException::withMessages([
                            'phonenumber' => 'This phone number is already in use.',
                        ]);
                    }
                }

                $lifeStatus = strtolower(trim((string) ($validated['life_status'] ?? '')));
                $childParentingMode = strtolower(trim((string) ($validated['child_parenting_mode'] ?? '')));
                $submittedDeadDate = trim((string) ($validated['deaddate'] ?? ''));
                $submittedGraveLocationUrl = trim((string) ($validated['grave_location_url'] ?? ''));

                $familyMember = FamilyMember::query()
                    ->where('userid', $targetUserId)
                    ->first();
                if (!$familyMember) {
                    throw new \RuntimeException('Family member data not found.');
                }

                $familyPayload = [
                    'name' => trim((string) ($validated['name'] ?? '')) ?: null,
                    'email' => $newEmail !== '' ? $newEmail : null,
                    'phonenumber' => $newPhone !== '' ? $newPhone : null,
                    'bloodtype' => strtoupper(trim((string) ($validated['bloodtype'] ?? ''))) ?: null,
                    'gender' => trim((string) ($validated['gender'] ?? '')) ?: null,
                    'birthdate' => !empty($validated['birthdate']) ? (string) $validated['birthdate'] : null,
                    'birthplace' => trim((string) ($validated['birthplace'] ?? '')) ?: null,
                    'marital_status' => trim((string) ($validated['marital_status'] ?? '')) ?: null,
                    'job' => trim((string) ($validated['job'] ?? '')) ?: null,
                    'education_status' => trim((string) ($validated['education_status'] ?? '')) ?: null,
                    'address' => trim((string) ($validated['address'] ?? '')) ?: null,
                ];

                if ($uploadedPicturePath !== null) {
                    $familyPayload['picture'] = $uploadedPicturePath;
                }

                if ($lifeStatus !== '') {
                    $familyPayload['life_status'] = $lifeStatus;
                }

                if (array_key_exists('deaddate', $validated)) {
                    $familyPayload['deaddate'] = $lifeStatus === 'deceased' && $submittedDeadDate !== ''
                        ? Carbon::parse($submittedDeadDate)->toDateString()
                        : null;
                }

                if (array_key_exists('grave_location_url', $validated)) {
                    $familyPayload['grave_location_url'] = $lifeStatus === 'deceased' && $submittedGraveLocationUrl !== ''
                        ? $submittedGraveLocationUrl
                        : null;
                }

                FamilyMember::query()
                    ->where('userid', $targetUserId)
                    ->update($familyPayload);

                $existingEmployer = Employer::query()
                    ->where('userid', $targetUserId)
                    ->first();

                if ($existingEmployer) {
                    $resolvedFamilyEmployerRoleId = $currentRoleIdValue !== null
                        ? $currentRoleIdValue
                        : ((int) ($existingEmployer->roleid ?? 0) ?: null);

                    $familyEmployerPayload = [
                        'name' => trim((string) ($validated['name'] ?? '')) ?: null,
                        'email' => $newEmail !== '' ? $newEmail : null,
                        'phonenumber' => $newPhone !== '' ? $newPhone : null,
                        'roleid' => $resolvedFamilyEmployerRoleId,
                    ];

                    Employer::query()
                        ->where('userid', $targetUserId)
                        ->update($familyEmployerPayload);
                }

                if ($lifeStatus === 'deceased') {
                    $partnerIds = DB::table('relationship')
                        ->where('relationtype', 'partner')
                        ->where(function ($query) use ($familyMember) {
                            $query->where('memberid', (int) ($familyMember->memberid ?? 0))
                                ->orWhere('relatedmemberid', (int) ($familyMember->memberid ?? 0));
                        })
                        ->get()
                        ->map(function ($row) use ($familyMember) {
                            return (int) ((int) $row->memberid === (int) ($familyMember->memberid ?? 0)
                                ? $row->relatedmemberid
                                : $row->memberid);
                        })
                        ->filter(function ($id) {
                            return (int) $id > 0;
                        })
                        ->unique()
                        ->values()
                        ->all();

                    if (!empty($partnerIds)) {
                        DB::table('family_member')
                            ->whereIn('memberid', $partnerIds)
                            ->update(['marital_status' => 'widowed']);
                    }
                }

                if ($childParentingMode !== '') {
                    $hasChildRelation = DB::table('relationship')
                        ->where('relationtype', 'child')
                        ->where('relatedmemberid', (int) ($familyMember->memberid ?? 0))
                        ->exists();

                    if ($hasChildRelation) {
                        $this->updateChildParentingModeForManagement((int) ($familyMember->memberid ?? 0), $childParentingMode);
                    }
                }
            } else {
                $roleId = !empty($validated['roleid']) ? (int) $validated['roleid'] : null;
                Employer::query()
                    ->where('userid', $targetUserId)
                    ->update([
                        'name' => trim((string) ($validated['name'] ?? '')) ?: null,
                        'email' => $newEmail !== '' ? $newEmail : null,
                        'phonenumber' => $newPhone !== '' ? $newPhone : null,
                        'roleid' => $roleId,
                    ]);
            }
            });
        } catch (ValidationException $e) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => $e->errors(),
                ], 422);
            }

            return redirect('/management/users')->withErrors($e->errors())->withInput();
        } catch (\Throwable $e) {
            report($e);

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Failed to update member details.',
                    'errors' => [
                        'general' => [$e->getMessage()],
                    ],
                ], 500);
            }

            return redirect('/management/users')->with('error', 'Failed to update member details.');
        }

        $updatedFamilyMember = null;
        if ($isFamilyMember) {
            $updatedFamilyMember = FamilyMember::query()
                ->where('userid', $targetUserId)
                ->first();
        }

        $this->logActivity($request, 'management.update_user', [
            'target_userid' => $targetUserId,
            'target_username' => (string) ($validated['username'] ?? ''),
            'levelid' => $selectedLevelId,
            'roleid' => $selectedRoleId,
            'is_family_member' => $isFamilyLevel,
        ]);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'message' => 'User has been updated.',
                'family_member' => $updatedFamilyMember,
            ]);
        }

        return redirect('/management/users')->with('success', 'User has been updated.');
    }

    private function updateChildParentingModeForManagement(int $childMemberId, string $requestedMode): void
    {
        if ($childMemberId <= 0) {
            throw ValidationException::withMessages([
                'child_parenting_mode' => 'Selected member is not found.',
            ]);
        }

        $requestedMode = strtolower(trim($requestedMode));
        if (!in_array($requestedMode, ['with_current_partner', 'single_parent'], true)) {
            throw ValidationException::withMessages([
                'child_parenting_mode' => 'Child status must be single parent or with current partner.',
            ]);
        }

        $childRelations = DB::table('relationship')
            ->where('relationtype', 'child')
            ->where('relatedmemberid', $childMemberId)
            ->select('memberid', 'child_parenting_mode')
            ->get();

        if ($childRelations->isEmpty()) {
            // Legacy or incomplete data can still edit the child status from the
            // detail panel. Keep the request successful even when no child row
            // exists yet, and let the UI reflect the selected value.
            return;
        }

        $currentMode = 'single_parent';
        foreach ($childRelations as $childRelation) {
            if (strtolower(trim((string) ($childRelation->child_parenting_mode ?? ''))) === 'with_current_partner') {
                $currentMode = 'with_current_partner';
                break;
            }
        }

        $parentIds = $childRelations
            ->pluck('memberid')
            ->map(function ($id) {
                return (int) $id;
            })
            ->filter(function ($id) {
                return $id > 0;
            })
            ->unique()
            ->values()
            ->all();

        $anchorParentId = 0;
        $partnerMemberId = 0;

        if ($requestedMode === 'with_current_partner') {
            foreach ($parentIds as $parentId) {
                $resolvedPartnerId = $this->resolveActivePartnerMemberId((int) $parentId, [$childMemberId]);
                if ($resolvedPartnerId > 0) {
                    $anchorParentId = (int) $parentId;
                    $partnerMemberId = (int) $resolvedPartnerId;
                    break;
                }
            }

            if ($partnerMemberId <= 0) {
                throw ValidationException::withMessages([
                    'child_parenting_mode' => 'No active partner was found for the selected child parent.',
                ]);
            }

            DB::transaction(function () use ($childMemberId, $partnerMemberId, $anchorParentId) {
                DB::table('relationship')
                    ->where('relationtype', 'child')
                    ->where('relatedmemberid', $childMemberId)
                    ->update(['child_parenting_mode' => 'with_current_partner']);

                $existingPartnerChildRelation = DB::table('relationship')
                    ->where('memberid', $partnerMemberId)
                    ->where('relatedmemberid', $childMemberId)
                    ->where('relationtype', 'child')
                    ->exists();

                if (!$existingPartnerChildRelation) {
                    DB::table('relationship')->insert([
                        'memberid' => $partnerMemberId,
                        'relatedmemberid' => $childMemberId,
                        'relationtype' => 'child',
                        'child_parenting_mode' => 'with_current_partner',
                    ]);
                }

                DB::table('family_member')
                    ->whereIn('memberid', array_values(array_unique(array_filter([
                        $anchorParentId,
                        $partnerMemberId,
                    ], function ($memberId) {
                        return (int) $memberId > 0;
                    }))))
                    ->update(['marital_status' => 'married']);
            });
        } else {
            $parentIds = array_values(array_unique(array_filter($parentIds, function ($memberId) {
                return (int) $memberId > 0;
            })));
            sort($parentIds);

            if (!empty($parentIds)) {
                $anchorParentId = (int) $parentIds[0];
            }

            if ($currentMode === 'with_current_partner' && $anchorParentId <= 0) {
                foreach ($parentIds as $parentId) {
                    $resolvedPartnerId = $this->resolveActivePartnerMemberId((int) $parentId, [$childMemberId]);
                    if ($resolvedPartnerId > 0) {
                        $anchorParentId = (int) $parentId;
                        break;
                    }
                }
            }

            if ($anchorParentId <= 0) {
                throw ValidationException::withMessages([
                    'child_parenting_mode' => 'Unable to determine which parent should be kept for single parent mode.',
                ]);
            }

            DB::transaction(function () use ($childMemberId, $anchorParentId) {
                DB::table('relationship')
                    ->where('relationtype', 'child')
                    ->where('relatedmemberid', $childMemberId)
                    ->update(['child_parenting_mode' => 'single_parent']);

                DB::table('relationship')
                    ->where('relationtype', 'child')
                    ->where('relatedmemberid', $childMemberId)
                    ->where('memberid', '<>', $anchorParentId)
                    ->delete();
            });
        }

        Cache::store('file')->forget('family_tree:relationships:v1');
        Cache::store('file')->forget('family_tree:family_members:v1');
        Cache::store('file')->put('family_tree:render_version:v1', (string) now()->timestamp, now()->addDay());
    }

    private function resolveActivePartnerMemberId(int $memberId, array $excludedMemberIds = []): int
    {
        if ($memberId <= 0) {
            return 0;
        }

        $excludedMemberSet = [];
        foreach ($excludedMemberIds as $excludedMemberId) {
            $excludedMemberId = (int) $excludedMemberId;
            if ($excludedMemberId > 0) {
                $excludedMemberSet[$excludedMemberId] = true;
            }
        }

        $partnerIds = DB::table('relationship')
            ->where('relationtype', 'partner')
            ->where(function ($query) use ($memberId) {
                $query->where('memberid', $memberId)
                    ->orWhere('relatedmemberid', $memberId);
            })
            ->get()
            ->map(function ($row) use ($memberId, $excludedMemberSet) {
                $partnerId = (int) ((int) $row->memberid === $memberId ? $row->relatedmemberid : $row->memberid);
                if ($partnerId <= 0 || isset($excludedMemberSet[$partnerId])) {
                    return 0;
                }

                return $partnerId;
            })
            ->filter(function ($partnerId) {
                return $partnerId > 0;
            })
            ->unique()
            ->values()
            ->all();

        if (empty($partnerIds)) {
            return 0;
        }

        return (int) DB::table('family_member')
            ->whereIn('memberid', $partnerIds)
            ->where(function ($query) {
                $query->whereNull('life_status')
                    ->orWhereRaw('LOWER(life_status) <> ?', ['deceased']);
            })
            ->orderBy('memberid')
            ->value('memberid');
    }

    public function resetUserPassword(Request $request, $userid)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $user = User::query()
            ->where('userid', (int) $userid)
            ->first();

        if (!$user) {
            return redirect('/management/users')->with('error', 'User not found.');
        }

        User::query()
            ->where('userid', (int) $userid)
            ->update([
                'password' => Hash::make($user->username),
            ]);

        $this->logActivity($request, 'management.reset_password', [
            'target_userid' => (int) $userid,
            'target_username' => (string) $user->username,
        ]);

        $message = 'Password reset successfully';

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'userid' => (int) $userid,
                'username' => (string) $user->username,
            ]);
        }

        return redirect('/management/users')->with('success', $message);
    }

    public function bulkDeleteUsers(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $currentRoleId = (int) session('authenticated_user.roleid');
        $allowedRoles = [1, 2, 3];
        $isFamilyHead = $currentRoleId === 3;

        if (!in_array($currentRoleId, $allowedRoles, true)) {
            return redirect('/')->with('error', 'You do not have permission to delete users.');
        }

        $validator = Validator::make($request->all(), [
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['required', 'integer', 'distinct'],
        ], [
            'user_ids.required' => 'Please select at least one user.',
            'user_ids.array' => 'Selected users data is invalid.',
            'user_ids.min' => 'Please select at least one user.',
            'user_ids.*.integer' => 'Selected user ID is invalid.',
        ]);

        if ($validator->fails()) {
            return redirect('/management/users')->with('error', $validator->errors()->first());
        }

        $currentUserId = (int) session('authenticated_user.userid');
        $targetUserIds = collect($validator->validated()['user_ids'] ?? [])
            ->map(function ($id) {
                return (int) $id;
            })
            ->filter(function ($id) {
                return $id > 0;
            })
            ->unique()
            ->values()
            ->all();

        if (empty($targetUserIds)) {
            return redirect('/management/users')->with('error', 'Please select at least one valid user.');
        }

        $users = User::query()
            ->whereIn('userid', $targetUserIds)
            ->get(['userid', 'username', 'levelid', 'deleted_at']);

        $usersById = [];
        foreach ($users as $user) {
            $usersById[(int) $user->userid] = $user;
        }

        $deletedCount = 0;
        $skippedCount = 0;
        $skippedOwnAccount = false;
        $skippedByRoleRestriction = false;
        $deletedAt = now()->toDateTimeString();
        $userIdsToDelete = [];
        $targetUserLogs = [];

        foreach ($targetUserIds as $targetUserId) {
            $targetUserId = (int) $targetUserId;

            if ($targetUserId === $currentUserId) {
                $skippedCount++;
                $skippedOwnAccount = true;
                continue;
            }

            $user = $usersById[$targetUserId] ?? null;
            if (!$user || $user->deleted_at !== null) {
                $skippedCount++;
                continue;
            }

            if ($isFamilyHead && (int) ($user->levelid ?? 0) !== 2) {
                $skippedCount++;
                $skippedByRoleRestriction = true;
                continue;
            }

            $cascadeUserIds = $this->resolveCascadeDeleteUserIdsFromManagementUserId($targetUserId, $currentUserId);
            if (empty($cascadeUserIds)) {
                $cascadeUserIds = [$targetUserId];
            }

            $cascadeUserIds = array_values(array_unique(array_filter(array_map(function ($id) {
                return (int) $id;
            }, $cascadeUserIds), function ($id) use ($currentUserId) {
                return $id > 0 && $id !== $currentUserId;
            })));

            if (empty($cascadeUserIds)) {
                $skippedCount++;
                continue;
            }

            foreach ($cascadeUserIds as $cascadeUserId) {
                $userIdsToDelete[$cascadeUserId] = true;
            }

            $targetUserLogs[] = [
                'target_userid' => $targetUserId,
                'target_username' => (string) ($user->username ?? ''),
                'cascade_user_ids' => $cascadeUserIds,
            ];
        }

        $userIdsToDelete = array_keys($userIdsToDelete);
        if (empty($userIdsToDelete)) {
            $errorMessage = 'No users were moved to Recycle Bin.';
            if ($skippedOwnAccount) {
                $errorMessage .= ' Your own account cannot be deleted.';
            }
            if ($skippedByRoleRestriction) {
                $errorMessage .= ' You can only delete family users.';
            }

            return redirect('/management/users')->with('error', $errorMessage);
        }

        $affectedRows = User::query()
            ->whereIn('userid', $userIdsToDelete)
            ->whereNull('deleted_at')
            ->update(['deleted_at' => $deletedAt]);

        if ($affectedRows === 0) {
            return redirect('/management/users')->with('error', 'No users were moved to Recycle Bin.');
        }

        foreach ($targetUserLogs as $targetUserLog) {
            $this->logActivity($request, 'management.delete_user', [
                'target_userid' => (int) ($targetUserLog['target_userid'] ?? 0),
                'target_username' => (string) ($targetUserLog['target_username'] ?? ''),
                'cascade_user_ids' => array_values(array_map('intval', (array) ($targetUserLog['cascade_user_ids'] ?? []))),
            ]);
        }

        $deletedCount = $affectedRows;
        $successMessage = $deletedCount . ' user(s) moved to Recycle Bin.';
        if ($skippedCount > 0) {
            $successMessage .= ' Skipped ' . $skippedCount . ' user(s).';
        }
        if ($skippedOwnAccount) {
            $successMessage .= ' Your own account was skipped.';
        }
        if ($skippedByRoleRestriction) {
            $successMessage .= ' Non-family users were skipped.';
        }

        return redirect('/management/users')->with('bulkDeleteSuccessMessage', $successMessage);
    }

    public function deleteUser(Request $request, $userid)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $currentRoleId = (int) session('authenticated_user.roleid');
        if (!in_array($currentRoleId, [1, 2], true)) {
            return redirect('/management/users')->with('error', 'Only admin and superadmin can delete users.');
        }

        $targetUserId = (int) $userid;
        $currentUserId = (int) session('authenticated_user.userid');

        if ($targetUserId === $currentUserId) {
            return redirect('/management/users')->with('error', 'You cannot delete your own account.');
        }

        $wantsJson = $request->ajax() || $request->expectsJson();

        $user = User::query()
            ->where('userid', $targetUserId)
            ->first();

        if (!$user) {
            if ($wantsJson) {
                return response()->json([
                    'message' => 'User not found.',
                ], 404);
            }

            return redirect('/management/users')->with('error', 'User not found.');
        }

        if ($user->deleted_at !== null) {
            if ($wantsJson) {
                return response()->json([
                    'message' => 'User is already in the recycle bin.',
                ], 422);
            }

            return redirect('/management/recycle-bin')->with('error', 'User is already in the recycle bin.');
        }

        $deletedAt = now()->toDateTimeString();
        $deleteUserIds = $this->resolveCascadeDeleteUserIdsFromManagementUserId($targetUserId, $currentUserId);
        if (empty($deleteUserIds)) {
            $deleteUserIds = [$targetUserId];
        }

        $deleteUserIds = array_values(array_unique(array_filter(array_map(function ($id) {
            return (int) $id;
        }, $deleteUserIds), function ($id) use ($currentUserId) {
            return $id > 0 && $id !== $currentUserId;
        })));

        if (empty($deleteUserIds)) {
            if ($wantsJson) {
                return response()->json([
                    'message' => 'Failed to move user to Recycle Bin.',
                ], 422);
            }

            return redirect('/management/users')->with('error', 'Failed to move user to Recycle Bin.');
        }

        $affectedRows = User::query()
            ->whereIn('userid', $deleteUserIds)
            ->whereNull('deleted_at')
            ->update(['deleted_at' => $deletedAt]);

        if ($affectedRows === 0) {
            if ($wantsJson) {
                return response()->json([
                    'message' => 'Failed to move user to Recycle Bin.',
                ], 422);
            }

            return redirect('/management/users')->with('error', 'Failed to move user to Recycle Bin.');
        }

        $this->logActivity($request, 'management.delete_user', [
            'target_userid' => $targetUserId,
            'target_username' => (string) ($user->username ?? ''),
            'cascade_user_ids' => $deleteUserIds,
        ]);

        $message = count($deleteUserIds) > 1
            ? 'User and related family members have been moved to Recycle Bin.'
            : 'User has been moved to Recycle Bin.';

        if ($wantsJson) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    public function restoreUser(Request $request, $userid)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        if ((int) session('authenticated_user.roleid') !== 1) {
            return redirect('/management/users')->with('error', 'Only superadmin can restore users.');
        }

        $targetUserId = (int) $userid;
        $user = User::query()
            ->where('userid', $targetUserId)
            ->first();

        if (!$user || $user->deleted_at === null) {
            return redirect('/management/recycle-bin')->with('error', 'User not found in recycle bin.');
        }

        User::query()
            ->where('userid', $targetUserId)
            ->update(['deleted_at' => null]);

        $this->logActivity($request, 'management.restore_user', [
            'target_userid' => $targetUserId,
            'target_username' => (string) ($user->username ?? ''),
        ]);

        return redirect('/management/recycle-bin')->with('success', 'User has been restored from Recycle Bin.');
    }

    public function forceDeleteUser(Request $request, $userid)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        if ((int) session('authenticated_user.roleid') !== 1) {
            return redirect('/management/users')->with('error', 'Only superadmin can permanently delete users.');
        }

        $targetUserId = (int) $userid;
        $user = User::query()
            ->where('userid', $targetUserId)
            ->first();

        if (!$user || $user->deleted_at === null) {
            return redirect('/management/recycle-bin')->with('error', 'User not found in recycle bin.');
        }

        $targetLevelId = (int) ($user->levelid ?? 0);
        DB::transaction(function () use ($targetUserId, $targetLevelId) {
            $this->deleteUserDataByLevel($targetUserId, $targetLevelId);
        });

        $this->logActivity($request, 'management.force_delete_user', [
            'target_userid' => $targetUserId,
            'target_username' => (string) ($user->username ?? ''),
        ]);

        return redirect('/management/recycle-bin')->with('success', 'User has been permanently deleted.');
    }

    public function bulkForceDeleteUsers(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        if ((int) session('authenticated_user.roleid') !== 1) {
            return redirect('/management/users')->with('error', 'Only superadmin can permanently delete users.');
        }

        $validator = Validator::make($request->all(), [
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['required', 'integer', 'distinct'],
        ], [
            'user_ids.required' => 'Please select at least one user.',
            'user_ids.array' => 'Selected users data is invalid.',
            'user_ids.min' => 'Please select at least one user.',
            'user_ids.*.integer' => 'Selected user ID is invalid.',
        ]);

        if ($validator->fails()) {
            return redirect('/management/recycle-bin')->with('error', $validator->errors()->first());
        }

        $targetUserIds = collect($validator->validated()['user_ids'] ?? [])
            ->map(function ($id) {
                return (int) $id;
            })
            ->filter(function ($id) {
                return $id > 0;
            })
            ->unique()
            ->values()
            ->all();

        if (empty($targetUserIds)) {
            return redirect('/management/recycle-bin')->with('error', 'Please select at least one valid user.');
        }

        $users = User::query()
            ->whereIn('userid', $targetUserIds)
            ->get(['userid', 'username', 'levelid', 'deleted_at']);

        $usersById = [];
        foreach ($users as $user) {
            $usersById[(int) $user->userid] = $user;
        }

        $deletedCount = 0;
        $skippedCount = 0;

        foreach ($targetUserIds as $targetUserId) {
            $targetUserId = (int) $targetUserId;
            $user = $usersById[$targetUserId] ?? null;

            if (!$user || $user->deleted_at === null) {
                $skippedCount++;
                continue;
            }

            $targetLevelId = (int) ($user->levelid ?? 0);
            DB::transaction(function () use ($targetUserId, $targetLevelId) {
                $this->deleteUserDataByLevel($targetUserId, $targetLevelId);
            });

            $deletedCount++;
            $this->logActivity($request, 'management.force_delete_user', [
                'target_userid' => $targetUserId,
                'target_username' => (string) ($user->username ?? ''),
            ]);
        }

        if ($deletedCount === 0) {
            return redirect('/management/recycle-bin')->with('error', 'No users were permanently deleted.');
        }

        $successMessage = $deletedCount . ' user(s) permanently deleted.';
        if ($skippedCount > 0) {
            $successMessage .= ' Skipped ' . $skippedCount . ' user(s).';
        }

        return redirect('/management/recycle-bin')->with('success', $successMessage);
    }

    private function deleteUserDataByLevel(int $targetUserId, int $targetLevelId): void
    {
        if ($targetLevelId === 1) {
            Employer::query()
                ->where('userid', $targetUserId)
                ->delete();

            User::query()
                ->where('userid', $targetUserId)
                ->delete();

            return;
        }

        if ($targetLevelId === 2 || $targetLevelId === 4) {
            $familyDeleteUserIds = $this->resolveCascadeDeleteUserIdsFromManagementUserId($targetUserId);
            if (empty($familyDeleteUserIds)) {
                $familyDeleteUserIds = [$targetUserId];
            }

            $this->deleteFamilyMemberDataByUserIds($familyDeleteUserIds);

            User::query()
                ->whereIn('userid', $familyDeleteUserIds)
                ->delete();

            return;
        }

        if (FamilyMember::query()->where('userid', $targetUserId)->exists()) {
            $familyDeleteUserIds = $this->resolveCascadeDeleteUserIdsFromManagementUserId($targetUserId);
            if (empty($familyDeleteUserIds)) {
                $familyDeleteUserIds = [$targetUserId];
            }

            $this->deleteFamilyMemberDataByUserIds($familyDeleteUserIds);
        } else {
            Employer::query()
                ->where('userid', $targetUserId)
                ->delete();
        }

        User::query()
            ->where('userid', $targetUserId)
            ->delete();
    }

    private function deleteFamilyMemberDataByUserIds(array $targetUserIds): void
    {
        $normalizedUserIds = collect($targetUserIds)
            ->map(function ($id) {
                return (int) $id;
            })
            ->filter(function ($id) {
                return $id > 0;
            })
            ->unique()
            ->values()
            ->all();

        if (empty($normalizedUserIds)) {
            return;
        }

        $familyMemberIds = DB::table('family_member')
            ->whereIn('userid', $normalizedUserIds)
            ->pluck('memberid')
            ->map(function ($id) {
                return (int) $id;
            })
            ->filter(function ($id) {
                return $id > 0;
            })
            ->unique()
            ->values()
            ->all();

        if (!empty($familyMemberIds)) {
            DB::table('relationship')
                ->whereIn('memberid', $familyMemberIds)
                ->orWhereIn('relatedmemberid', $familyMemberIds)
                ->delete();

            DB::table('ownsocial')
                ->whereIn('memberid', $familyMemberIds)
                ->delete();
        }

        DB::table('employer')
            ->whereIn('userid', $normalizedUserIds)
            ->delete();

        DB::table('family_member')
            ->whereIn('userid', $normalizedUserIds)
            ->delete();
    }

    private function resolveCascadeDeleteMemberIdsFromChild(int $rootChildMemberId): array
    {
        if ($rootChildMemberId <= 0) {
            return [];
        }

        $relations = DB::table('relationship')
            ->whereIn('relationtype', ['child', 'partner'])
            ->select('memberid', 'relatedmemberid', 'relationtype')
            ->get();

        $childrenMap = [];
        $partnerMap = [];

        foreach ($relations as $relation) {
            $from = (int) ($relation->memberid ?? 0);
            $to = (int) ($relation->relatedmemberid ?? 0);
            if ($from <= 0 || $to <= 0 || $from === $to) {
                continue;
            }

            $type = strtolower(trim((string) ($relation->relationtype ?? '')));
            if ($type === 'child') {
                $childrenMap[$from] = $childrenMap[$from] ?? [];
                $childrenMap[$from][$to] = true;
                continue;
            }

            if ($type === 'partner') {
                $partnerMap[$from] = $partnerMap[$from] ?? [];
                $partnerMap[$to] = $partnerMap[$to] ?? [];
                $partnerMap[$from][$to] = true;
                $partnerMap[$to][$from] = true;
            }
        }

        $queue = new \SplQueue();
        $queue->enqueue([
            'memberid' => $rootChildMemberId,
            'expand_partners' => true,
        ]);

        $processedWithoutPartners = [];
        $processedWithPartners = [];
        $deleteSet = [];

        while (!$queue->isEmpty()) {
            $current = (array) $queue->dequeue();
            $memberId = (int) ($current['memberid'] ?? 0);
            $expandPartners = (bool) ($current['expand_partners'] ?? false);
            if ($memberId <= 0) {
                continue;
            }

            if ($expandPartners) {
                if (!empty($processedWithPartners[$memberId])) {
                    continue;
                }

                $processedWithPartners[$memberId] = true;
                $processedWithoutPartners[$memberId] = true;
            } else {
                if (!empty($processedWithoutPartners[$memberId])) {
                    continue;
                }

                $processedWithoutPartners[$memberId] = true;
            }

            $deleteSet[$memberId] = true;

            foreach (array_keys($childrenMap[$memberId] ?? []) as $childId) {
                $queue->enqueue([
                    'memberid' => (int) $childId,
                    'expand_partners' => true,
                ]);
            }

            if (!$expandPartners) {
                continue;
            }

            foreach (array_keys($partnerMap[$memberId] ?? []) as $partnerId) {
                $queue->enqueue([
                    'memberid' => (int) $partnerId,
                    'expand_partners' => false,
                ]);
            }
        }

        return array_values(array_map(function ($id) {
            return (int) $id;
        }, array_keys($deleteSet)));
    }

    private function resolveCascadeDeleteUserIdsFromManagementUserId(int $targetUserId, ?int $excludeUserId = null): array
    {
        $rootMemberIds = DB::table('family_member')
            ->where('userid', $targetUserId)
            ->pluck('memberid')
            ->map(function ($id) {
                return (int) $id;
            })
            ->filter(function ($id) {
                return $id > 0;
            })
            ->unique()
            ->values()
            ->all();

        if (empty($rootMemberIds)) {
            $fallbackUserIds = [$targetUserId];
            if ($excludeUserId !== null) {
                $fallbackUserIds = array_values(array_filter($fallbackUserIds, function ($id) use ($excludeUserId) {
                    return (int) $id !== (int) $excludeUserId;
                }));
            }

            return $fallbackUserIds;
        }

        $cascadeMemberIds = [];
        foreach ($rootMemberIds as $rootMemberId) {
            $cascadeMemberIds = array_merge($cascadeMemberIds, $this->resolveCascadeDeleteMemberIdsFromChild((int) $rootMemberId));
        }

        $cascadeMemberIds = array_values(array_unique(array_filter(array_map(function ($id) {
            return (int) $id;
        }, $cascadeMemberIds), function ($id) {
            return $id > 0;
        })));

        if (empty($cascadeMemberIds)) {
            $fallbackUserIds = [$targetUserId];
            if ($excludeUserId !== null) {
                $fallbackUserIds = array_values(array_filter($fallbackUserIds, function ($id) use ($excludeUserId) {
                    return (int) $id !== (int) $excludeUserId;
                }));
            }

            return $fallbackUserIds;
        }

        $cascadeUserIds = DB::table('family_member')
            ->whereIn('memberid', $cascadeMemberIds)
            ->pluck('userid')
            ->map(function ($id) {
                return (int) $id;
            })
            ->filter(function ($id) {
                return $id > 0;
            })
            ->unique()
            ->values()
            ->all();

        if ($excludeUserId !== null) {
            $cascadeUserIds = array_values(array_filter($cascadeUserIds, function ($id) use ($excludeUserId) {
                return (int) $id !== (int) $excludeUserId;
            }));
        }

        return $cascadeUserIds;
    }

    private function listMysqlTables(string $databaseName): array
    {
        $rows = DB::select(
            'SHOW FULL TABLES FROM '
            . $this->escapeMysqlIdentifier($databaseName)
            . " WHERE Table_type = 'BASE TABLE'"
        );
        $tableKey = 'Tables_in_' . $databaseName;

        return collect($rows)
            ->map(function ($row) use ($tableKey) {
                $data = (array) $row;
                return (string) ($data[$tableKey] ?? '');
            })
            ->filter(fn ($tableName) => $tableName !== '')
            ->values()
            ->all();
    }

    private function escapeMysqlIdentifier(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }

    private function toMysqlSqlValue($value, \PDO $pdo): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return (string) $pdo->quote((string) $value);
    }

    private function readActivityLogs(?int $limit = 200): array
    {
        $path = storage_path('app/activity_log.jsonl');
        if (!File::exists($path)) {
            return [];
        }

        $raw = (string) File::get($path);
        if ($raw === '') {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', $raw) ?: [];
        $logs = [];

        foreach (array_reverse($lines) as $line) {
            $line = trim((string) $line);
            if ($line === '') {
                continue;
            }

            $decoded = json_decode($line, true);
            if (!is_array($decoded)) {
                continue;
            }

            $logs[] = $decoded;
            if (is_int($limit) && $limit > 0 && count($logs) >= $limit) {
                break;
            }
        }

        return $logs;
    }

    private function filterActivityLogsForRole(array $logs, int $roleId): array
    {
        if ($roleId === 1) {
            return $logs;
        }

        if ($roleId === 2) {
            return array_values(array_filter($logs, static function ($log) {
                $actorRoleId = (int) ($log['actor']['roleid'] ?? 0);
                return $actorRoleId !== 1;
            }));
        }

        return [];
    }

    private function getRolePermissionSettings(): array
    {
        $defaults = $this->getDefaultRolePermissionSettings();

        $path = storage_path('app/role_permissions.json');
        if (!File::exists($path)) {
            return $defaults;
        }

        $data = json_decode((string) File::get($path), true);
        if (!is_array($data)) {
            return $defaults;
        }

        if (isset($data['roles']) && is_array($data['roles'])) {
            return $this->normalizeRolePermissionMatrix($data['roles']);
        }

        return $defaults;
    }

    private function saveRolePermissionSettings(array $settings): void
    {
        $normalized = $this->normalizeRolePermissionMatrix($settings);
        $payload = [
            'version' => 2,
            'roles' => $normalized,
        ];

        $path = storage_path('app/role_permissions.json');
        File::put($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function getPermissionMenuOptions(): array
    {
        return [
            'data_master' => 'Data Master',
            'user_management' => 'User Management',
            'activity_log' => 'Activity Log',
            'recycle_bin' => 'Recycle Bin',
            'backup_database' => 'Backup Database',
            'permission' => 'Permission',
            'setting' => 'Setting',
        ];
    }

    private function getDefaultRolePermissionSettings(): array
    {
        return [
            'superadmin' => [
                'data_master' => true,
                'user_management' => true,
                'activity_log' => true,
                'recycle_bin' => true,
                'backup_database' => true,
                'permission' => true,
                'setting' => true,
            ],
            'admin' => [
                'data_master' => true,
                'user_management' => true,
                'activity_log' => true,
                'recycle_bin' => false,
                'backup_database' => false,
                'permission' => false,
                'setting' => false,
            ],
            'family_member' => [
                'data_master' => false,
                'user_management' => true,
                'activity_log' => false,
                'recycle_bin' => false,
                'backup_database' => false,
                'permission' => false,
                'setting' => false,
            ],
        ];
    }

    private function normalizeRolePermissionMatrix(array $settings): array
    {
        $defaults = $this->getDefaultRolePermissionSettings();
        $menuOptions = $this->getPermissionMenuOptions();
        $menuKeys = array_keys($menuOptions);
        $roleKeys = ['superadmin', 'admin', 'family_member'];
        $normalized = [];

        foreach ($roleKeys as $roleKey) {
            $normalized[$roleKey] = [];
            $roleSettings = isset($settings[$roleKey]) && is_array($settings[$roleKey])
                ? $settings[$roleKey]
                : [];

            foreach ($menuKeys as $menuKey) {
                if (array_key_exists($menuKey, $roleSettings)) {
                    $normalized[$roleKey][$menuKey] = filter_var(
                        $roleSettings[$menuKey],
                        FILTER_VALIDATE_BOOLEAN
                    );
                } else {
                    $normalized[$roleKey][$menuKey] = (bool) ($defaults[$roleKey][$menuKey] ?? false);
                }
            }
        }

        return $normalized;
    }

    private function usersQuery()
    {
        return DB::table('user as u')
            ->leftJoin('level as l', 'l.levelid', '=', 'u.levelid')
            ->leftJoin('employer as e', 'e.userid', '=', 'u.userid')
            ->leftJoin('role as r', 'r.roleid', '=', 'e.roleid')
            ->leftJoin('family_member as fm', 'fm.userid', '=', 'u.userid')
            ->select(
                'u.userid',
                'u.username',
                'u.levelid',
                'u.deleted_at',
                'l.levelname',
                'e.roleid',
                'r.rolename',
                'fm.bloodtype',
                'fm.job',
                'fm.education_status',
                'fm.address',
                'fm.gender',
                'fm.birthdate',
                'fm.birthplace',
                'fm.marital_status',
                'fm.life_status',
                DB::raw("COALESCE(e.name, fm.name, '-') as fullname"),
                DB::raw("COALESCE(e.email, fm.email, '-') as email"),
                DB::raw("COALESCE(e.phonenumber, fm.phonenumber, '-') as phone"),
                DB::raw("CASE
                    WHEN e.employerid IS NOT NULL THEN 'Employer'
                    WHEN fm.memberid IS NOT NULL THEN 'Family Member'
                    ELSE 'User'
                END as source")
            )
            ->orderBy('u.userid');
    }



    public function updateLifeStatus(Request $request)
    {
       $validated = $request->validate([
               'memberid' => 'required|integer',
                'life_status' => 'required|string|in:Alive,Deceased,alive,deceased',
           ]);
    
           \Illuminate\Support\Facades\FamilyMember
              ->where('memberid', $validated['memberid'])
           ->update(['life_status' => ucfirst(strtolower($validated['life_status']))]);

       $this->logActivity($request, 'management.update_life_status', [
           'memberid' => (int) $validated['memberid'],
           'life_status' => ucfirst(strtolower($validated['life_status'])),
       ]);
   
       return response()->json(['success' => true]);
    }

    public function testLog()
    {
        $logs = \Illuminate\Support\Facades\DB::table('activity_log')->get();
        
        echo "<h1>Test Activity Log Table</h1>";
        echo "<p>Total logs found: " . count($logs) . "</p>";
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<thead><tr><th>ID</th><th>User ID</th><th>Action</th><th>Context</th><th>IP</th><th>Longitude</th><th>Latitude</th><th>Created At</th></tr></thead><tbody>";
        
        foreach ($logs as $log) {
            echo "<tr>";
            echo "<td>" . ($log->id ?? '-') . "</td>";
            echo "<td>" . ($log->userid ?? '-') . "</td>";
            echo "<td>" . ($log->action ?? '-') . "</td>";
            echo "<td><pre>" . ($log->context ?? '-') . "</pre></td>";
            echo "<td>" . ($log->ip_address ?? '-') . "</td>";
            echo "<td>" . ($log->longitude ?? '-') . "</td>";
            echo "<td>" . ($log->latitude ?? '-') . "</td>";
            echo "<td>" . ($log->created_at ?? '-') . "</td>";
            echo "</tr>";
        }
        
        echo "</tbody></table>";
    }
}

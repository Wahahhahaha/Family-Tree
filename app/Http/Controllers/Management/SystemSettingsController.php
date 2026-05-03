<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SystemSettingsController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $currentRoleId = (int) session('authenticated_user.roleid');
        if (!in_array($currentRoleId, [1, 2], true)) {
            return redirect('/')->with('error', 'You do not have permission to access settings.');
        }

        $systemSettings = $this->getSystemSettings();
        $landingPageSettings = $this->getLandingPageSettings();
        $logoPreviewUrl = trim((string) ($systemSettings['logo_url'] ?? ''));
        if ($logoPreviewUrl === '') {
            $storedLogoPath = trim((string) ($systemSettings['logo_path'] ?? ''));
            if ($storedLogoPath !== '') {
                $logoPreviewUrl = (preg_match('#^https?://#i', $storedLogoPath) || str_starts_with($storedLogoPath, 'data:'))
                    ? $storedLogoPath
                : asset(ltrim($storedLogoPath, '/'));
            }
        }

        $activeTab = strtolower(trim((string) $request->query('tab', 'website')));
        if (!in_array($activeTab, ['website', 'landing'], true)) {
            $activeTab = 'website';
        }

        if ($currentRoleId === 2) {
            $activeTab = 'landing';
        }

        return view("superadmin.settings", [
            "pageClass" => "page-family-tree page-management-setting",
            "systemSettings" => $systemSettings,
            "logoPreviewUrl" => $logoPreviewUrl,
            "landingPageSettings" => $landingPageSettings,
            "activeTab" => $activeTab,
            "canAccessWebsiteSettings" => $currentRoleId === 1,
            "canAccessLandingSettings" => true,
        ]);
    }

    public function updateLandingPageSettings(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $currentRoleId = (int) session('authenticated_user.roleid');
        if (!in_array($currentRoleId, [1, 2], true)) {
            return redirect('/')->with('error', 'You do not have permission to update landing page settings.');
        }

        $validated = $request->validate([
            'family_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'head_of_family_name' => ['required', 'string', 'max:255'],
            'head_of_family_message' => ['nullable', 'string', 'max:5000'],
            'created_by_name' => ['required', 'string', 'max:255'],
            'designed_by_name' => ['required', 'string', 'max:255'],
            'approved_by_name' => ['required', 'string', 'max:255'],
            'acknowledged_by_name' => ['required', 'string', 'max:255'],
            'head_of_family_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'created_by_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'designed_by_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'approved_by_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'acknowledged_by_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'family_name.required' => 'Family name is required.',
            'family_name.max' => 'Family name max 255 characters.',
            'description.max' => 'Description max 5000 characters.',
            'head_of_family_name.required' => 'Head of family name is required.',
            'head_of_family_name.max' => 'Head of family name max 255 characters.',
            'head_of_family_message.max' => 'Head of family message max 5000 characters.',
            'created_by_name.required' => 'Created by name is required.',
            'designed_by_name.required' => 'Designed by name is required.',
            'approved_by_name.required' => 'Approved by name is required.',
            'acknowledged_by_name.required' => 'Acknowledged by name is required.',
            'head_of_family_photo.image' => 'Head of family photo must be an image file.',
            'created_by_photo.image' => 'Created by photo must be an image file.',
            'designed_by_photo.image' => 'Designed by photo must be an image file.',
            'approved_by_photo.image' => 'Approved by photo must be an image file.',
            'acknowledged_by_photo.image' => 'Acknowledged by photo must be an image file.',
        ]);

        $currentSettings = $this->getLandingPageSettings();
        $storageRoot = rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), DIRECTORY_SEPARATOR) . '/uploads/landing-page';
        File::ensureDirectoryExists($storageRoot);

        $photoFields = [
            'head_of_family_photo',
            'created_by_photo',
            'designed_by_photo',
            'approved_by_photo',
            'acknowledged_by_photo',
        ];

        foreach ($photoFields as $photoField) {
            if (!$request->hasFile($photoField)) {
                $validated[$photoField] = (string) ($currentSettings[$photoField] ?? '');
                continue;
            }

            $oldPhotoPath = trim((string) ($currentSettings[$photoField] ?? ''));
            if ($oldPhotoPath !== '' && !preg_match('#^(?:https?:|data:|blob:)#i', $oldPhotoPath)) {
                $oldFile = public_path(ltrim($oldPhotoPath, '/'));
                if (is_file($oldFile)) {
                    @unlink($oldFile);
                }
            }

            $extension = strtolower((string) $request->file($photoField)->getClientOriginalExtension());
            $fileName = $photoField . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
            $request->file($photoField)->move($storageRoot, $fileName);
            $validated[$photoField] = '/uploads/landing-page/' . $fileName;
        }

        $payload = [
            'family_name' => trim((string) $validated['family_name']),
            'description' => trim((string) ($validated['description'] ?? '')),
            'head_of_family_name' => trim((string) $validated['head_of_family_name']),
            'head_of_family_message' => trim((string) ($validated['head_of_family_message'] ?? '')),
            'head_of_family_photo' => trim((string) ($validated['head_of_family_photo'] ?? '')),
            'created_by_name' => trim((string) $validated['created_by_name']),
            'created_by_photo' => trim((string) ($validated['created_by_photo'] ?? '')),
            'designed_by_name' => trim((string) $validated['designed_by_name']),
            'designed_by_photo' => trim((string) ($validated['designed_by_photo'] ?? '')),
            'approved_by_name' => trim((string) $validated['approved_by_name']),
            'approved_by_photo' => trim((string) ($validated['approved_by_photo'] ?? '')),
            'acknowledged_by_name' => trim((string) $validated['acknowledged_by_name']),
            'acknowledged_by_photo' => trim((string) ($validated['acknowledged_by_photo'] ?? '')),
        ];

        $this->saveLandingPageSettings($payload);

        return redirect()
            ->route('management.setting', ['tab' => 'landing'])
            ->with('success', 'Landing Page Settings updated successfully.');
    }

    public function permission(Request $request)
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

        return view("superadmin.permission", compact('systemSettings', 'permissionSettings', 'permissionMenus') + ["pageClass" => "page-family-tree page-management-permission"]);
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
            'setting' => 'Settings',
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
                'setting' => true,
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
}

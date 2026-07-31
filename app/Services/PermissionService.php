<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class PermissionService
{
    protected static $filePath = 'permissions.json';

    public static function getEffectivePermissions($userId)
    {
        if (! $userId) {
            return [];
        }

        $user = DB::table('user')->where('userid', $userId)->first();
        if (! $user) {
            return [];
        }

        // 1. Get Level & Role
        $levelId = $user->levelid;
        $roleId = null;

        // User could be an employer or a family member regardless of levelid (e.g. Superadmin might be level 1 but exists in family_member)
        $employer = DB::table('employer')->where('userid', $userId)->first();
        $familyMember = DB::table('family_member')->where('userid', $userId)->first();

        if ($employer) {
            $roleId = $employer->roleid;
        } elseif ($familyMember) {
            $roleId = $familyMember->roleid;
        }

        // 2. Ensure Vault Exists & Load
        $data = self::ensureVaultExists();

        $allPermissions = collect($data['permissions']);
        $rolePerms = collect($data['role_permissions']);
        $levelPerms = collect($data['level_permissions']);

        // 3. Collect Permission IDs
        $assignedPermIds = [];

        // Add role-based permissions
        if ($roleId) {
            $rolePermIds = $rolePerms->where('role_id', (int) $roleId)->pluck('permission_id')->toArray();
            $assignedPermIds = array_merge($assignedPermIds, $rolePermIds);
        }

        // Add level-based permissions
        if ($levelId) {
            $levelPermIds = $levelPerms->where('level_id', (int) $levelId)->pluck('permission_id')->toArray();
            $assignedPermIds = array_merge($assignedPermIds, $levelPermIds);
        }

        return $allPermissions
            ->whereIn('id', array_unique($assignedPermIds))
            ->pluck('slug')
            ->toArray();
    }

    public static function ensureVaultExists()
    {
        $fullPath = storage_path('app/'.self::$filePath);

        if (! file_exists($fullPath)) {
            $defaults = [
                'permissions' => [
                    ['id' => 1, 'name' => 'Menu: Tree View', 'slug' => 'menu_tree', 'group' => 'Navigation'],
                    ['id' => 2, 'name' => 'Menu: Family Wiki', 'slug' => 'menu_wiki', 'group' => 'Navigation'],
                    ['id' => 3, 'name' => 'Menu: Gallery', 'slug' => 'menu_gallery', 'group' => 'Navigation'],
                    ['id' => 4, 'name' => 'Menu: Letters', 'slug' => 'menu_letters', 'group' => 'Navigation'],
                    ['id' => 5, 'name' => 'Menu: Live Location', 'slug' => 'menu_location', 'group' => 'Navigation'],
                    ['id' => 6, 'name' => 'Menu: Calendar', 'slug' => 'menu_calendar', 'group' => 'Navigation'],
                    ['id' => 7, 'name' => 'Menu: Events', 'slug' => 'menu_events', 'group' => 'Navigation'],
                    ['id' => 8, 'name' => 'Menu: Inheritance', 'slug' => 'menu_inheritance', 'group' => 'Navigation'],
                    ['id' => 9, 'name' => 'Menu: Validation', 'slug' => 'menu_validation', 'group' => 'Navigation'],
                    ['id' => 10, 'name' => 'Menu: Activity Log', 'slug' => 'menu_activity', 'group' => 'Management'],
                    ['id' => 11, 'name' => 'Menu: Backup', 'slug' => 'menu_backup', 'group' => 'Management'],
                    ['id' => 12, 'name' => 'Menu: Master Data', 'slug' => 'menu_master', 'group' => 'Management'],
                    ['id' => 13, 'name' => 'Menu: Permissions', 'slug' => 'menu_permissions', 'group' => 'Management'],
                    ['id' => 14, 'name' => 'Menu: System Settings', 'slug' => 'menu_system', 'group' => 'Management'],
                    ['id' => 15, 'name' => 'Menu: User Data', 'slug' => 'menu_users', 'group' => 'Management'],
                    ['id' => 16, 'name' => 'Menu: Recycle Bin', 'slug' => 'menu_recycle', 'group' => 'Management'],
                    ['id' => 22, 'name' => 'Menu: Home', 'slug' => 'menu_home', 'group' => 'Navigation'],
                    ['id' => 17, 'name' => 'Action: Edit Tree', 'slug' => 'action_edit_tree', 'group' => 'Actions'],
                    ['id' => 18, 'name' => 'Action: Upload Photos', 'slug' => 'action_upload', 'group' => 'Actions'],
                    ['id' => 19, 'name' => 'Action: Manage Events', 'slug' => 'action_manage_events', 'group' => 'Actions'],
                    ['id' => 20, 'name' => 'Action: Send Letters', 'slug' => 'action_send_letters', 'group' => 'Actions'],
                    ['id' => 21, 'name' => 'Action: Direct Delete', 'slug' => 'action_direct_delete', 'group' => 'Actions'],
                ],
                'role_permissions' => [],
                'level_permissions' => [],
            ];

            // Give Superadmin (Role 1) and Admin (Role 2) all permissions
            foreach ($defaults['permissions'] as $p) {
                $defaults['role_permissions'][] = ['role_id' => 1, 'permission_id' => $p['id']];
                $defaults['role_permissions'][] = ['role_id' => 2, 'permission_id' => $p['id']];
            }

            // Give Family Member (Level 2) basic menu access
            $familyBasic = [1, 2, 3, 4, 5, 6, 7, 20, 22];
            foreach ($familyBasic as $pid) {
                $defaults['level_permissions'][] = ['level_id' => 2, 'permission_id' => $pid];
            }

            file_put_contents($fullPath, json_encode($defaults, JSON_PRETTY_PRINT));
        }

        return json_decode(file_get_contents($fullPath), true);
    }

    public static function can($userId, $slug)
    {
        $perms = self::getEffectivePermissions($userId);

        return in_array($slug, $perms);
    }
}

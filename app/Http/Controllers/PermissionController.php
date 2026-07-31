<?php

namespace App\Http\Controllers;

use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PermissionController extends Controller
{
    protected $filePath = 'permissions.json';

    private function getStorage()
    {
        return PermissionService::ensureVaultExists();
    }

    public function index()
    {
        $data = $this->getStorage();
        $roles = DB::table('role')->get();
        $levels = DB::table('level')->get();

        return Inertia::render('Permission/Index', [
            'permissions' => $data['permissions'],
            'roles' => $roles,
            'levels' => $levels,
            'rolePermissions' => $data['role_permissions'],
            'levelPermissions' => $data['level_permissions'],
            'translations' => trans('permission'),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:role,level',
            'id' => 'required|integer',
            'permission_id' => 'required|integer',
            'value' => 'required|boolean',
        ]);

        $data = $this->getStorage();
        $key = ($validated['type'] === 'role') ? 'role_permissions' : 'level_permissions';
        $idKey = ($validated['type'] === 'role') ? 'role_id' : 'level_id';

        if ($validated['value']) {
            // Add if not exists
            $exists = false;
            foreach ($data[$key] as $entry) {
                if ($entry[$idKey] == $validated['id'] && $entry['permission_id'] == $validated['permission_id']) {
                    $exists = true;
                    break;
                }
            }
            if (! $exists) {
                $data[$key][] = [
                    $idKey => $validated['id'],
                    'permission_id' => $validated['permission_id'],
                ];
            }
        } else {
            // Remove
            $data[$key] = array_values(array_filter($data[$key], function ($entry) use ($validated, $idKey) {
                return ! ($entry[$idKey] == $validated['id'] && $entry['permission_id'] == $validated['permission_id']);
            }));
        }

        file_put_contents(storage_path('app/'.$this->filePath), json_encode($data, JSON_PRETTY_PRINT));

        return back()->with('success', trans('permission.success_update'));
    }
}

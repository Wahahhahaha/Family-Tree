<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class SocialMediaController extends Controller
{
    public function index(Request $request)
    {
        $currentRoleId = (int) session('authenticated_user.roleid');
        if ($currentRoleId !== 1) {
            return redirect('/')->with('error', 'Unauthorized.');
        }

        $keyword = trim((string) $request->query('keyword', ''));
        $activeTab = $request->query('tab', 'social');

        $data = [
            'pageClass' => 'page-family-tree',
            'systemSettings' => $this->getSystemSettings(),
            'activeTab' => $activeTab,
            'keyword' => $keyword,
        ];

        if ($activeTab === 'social') {
            $query = DB::table('socialmedia');
            $this->applyActiveFilter($query, 'socialmedia');

            if ($keyword !== '') {
                $query->where(function ($q) use ($keyword) {
                    $q->where('socialname', 'like', '%' . $keyword . '%')
                      ->orWhere('socialicon', 'like', '%' . $keyword . '%');
                });
            }

            $data['socials'] = $query->paginate(10, ['*'], 'social_page')->withQueryString();
        } elseif ($activeTab === 'levels') {
            $query = DB::table('level');
            $this->applyActiveFilter($query, 'level');

            if ($keyword !== '') {
                $query->where('levelname', 'like', '%' . $keyword . '%');
            }

            $data['levels'] = $query->paginate(10, ['*'], 'level_page')->withQueryString();
        } elseif ($activeTab === 'roles') {
            $query = DB::table('role');
            $this->applyActiveFilter($query, 'role');

            if ($keyword !== '') {
                $query->where('rolename', 'like', '%' . $keyword . '%');
            }

            $data['roles'] = $query->paginate(10, ['*'], 'role_page')->withQueryString();
        } elseif ($activeTab === 'fields') {
            $query = DB::table('custom_fields');
            $this->applyActiveFilter($query, 'custom_fields');

            if ($keyword !== '') {
                $query->where('field_name', 'like', '%' . $keyword . '%');
            }

            $data['fields'] = $query->paginate(10, ['*'], 'field_page')->withQueryString();
        }

        return view('superadmin.data-master', $data);
    }

    public function storeSocial(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'socialname' => 'required|string|max:255',
            'socialicon' => 'nullable|string|max:100',
        ]);
        if ($validator->fails()) return back()->withErrors($validator)->withInput();

        DB::table('socialmedia')->insert(['socialname' => $request->socialname, 'socialicon' => $request->socialicon]);

        return redirect('/management/data-master?tab=social')->with('success', 'Social Media added.');
    }

    public function updateSocial(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'socialname' => 'required|string|max:255',
            'socialicon' => 'nullable|string|max:100',
        ]);
        if ($validator->fails()) return back()->withErrors($validator)->withInput();

        DB::table('socialmedia')->where('socialid', $id)->update(['socialname' => $request->socialname, 'socialicon' => $request->socialicon]);

        return redirect('/management/data-master?tab=social')->with('success', 'Social Media updated.');
    }

    public function destroySocial($id)
    {
        $this->softDeleteOrHardDelete('socialmedia', 'socialid', $id);
        return redirect('/management/data-master?tab=social')->with('success', 'Social Media moved to Recycle Bin.');
    }

    public function storeLevel(Request $request)
    {
        $validator = Validator::make($request->all(), ['levelname' => 'required|string|max:255']);
        if ($validator->fails()) return back()->withErrors($validator)->withInput();

        DB::table('level')->insert(['levelname' => $request->levelname]);
        return redirect('/management/data-master?tab=levels')->with('success', 'Level added.');
    }

    public function updateLevel(Request $request, $id)
    {
        $validator = Validator::make($request->all(), ['levelname' => 'required|string|max:255']);
        if ($validator->fails()) return back()->withErrors($validator)->withInput();

        DB::table('level')->where('levelid', $id)->update(['levelname' => $request->levelname]);
        return redirect('/management/data-master?tab=levels')->with('success', 'Level updated.');
    }

    public function destroyLevel($id)
    {
        $this->softDeleteOrHardDelete('level', 'levelid', $id);
        return redirect('/management/data-master?tab=levels')->with('success', 'Level moved to Recycle Bin.');
    }

    public function storeRole(Request $request)
    {
        $validator = Validator::make($request->all(), ['rolename' => 'required|string|max:255']);
        if ($validator->fails()) return back()->withErrors($validator)->withInput();

        DB::table('role')->insert(['rolename' => $request->rolename]);
        return redirect('/management/data-master?tab=roles')->with('success', 'Role added.');
    }

    public function updateRole(Request $request, $id)
    {
        $validator = Validator::make($request->all(), ['rolename' => 'required|string|max:255']);
        if ($validator->fails()) return back()->withErrors($validator)->withInput();

        DB::table('role')->where('roleid', $id)->update(['rolename' => $request->rolename]);
        return redirect('/management/data-master?tab=roles')->with('success', 'Role updated.');
    }

    public function destroyRole($id)
    {
        $this->softDeleteOrHardDelete('role', 'roleid', $id);
        return redirect('/management/data-master?tab=roles')->with('success', 'Role moved to Recycle Bin.');
    }

    public function storeField(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'field_name' => 'required|string|max:255',
            'field_type' => 'required|string|in:text,number,date,select,textarea',
            'field_options' => 'nullable|string',
            'is_required' => 'nullable|boolean'
        ]);

        if ($validator->fails()) return back()->withErrors($validator)->withInput();

        DB::table('custom_fields')->insert([
            'field_name' => $request->field_name,
            'field_type' => $request->field_type,
            'field_options' => $request->field_options,
            'is_required' => $request->is_required ?? 0,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return redirect('/management/data-master?tab=fields')->with('success', 'Custom Field added.');
    }

    public function updateField(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'field_name' => 'required|string|max:255',
            'field_type' => 'required|string|in:text,number,date,select,textarea',
            'field_options' => 'nullable|string',
            'is_required' => 'nullable|boolean'
        ]);

        if ($validator->fails()) return back()->withErrors($validator)->withInput();

        DB::table('custom_fields')->where('id', $id)->update([
            'field_name' => $request->field_name,
            'field_type' => $request->field_type,
            'field_options' => $request->field_options,
            'is_required' => $request->is_required ?? 0,
            'updated_at' => Carbon::now(),
        ]);

        return redirect('/management/data-master?tab=fields')->with('success', 'Custom Field updated.');
    }

    public function destroyField($id)
    {
        $this->softDeleteOrHardDelete('custom_fields', 'id', $id);
        return redirect('/management/data-master?tab=fields')->with('success', 'Custom Field moved to Recycle Bin.');
    }

    public function restoreMaster(Request $request, $type, $id)
    {
        $map = [
            'social' => ['table' => 'socialmedia', 'pk' => 'socialid'],
            'level'  => ['table' => 'level', 'pk' => 'levelid'],
            'role'   => ['table' => 'role', 'pk' => 'roleid'],
            'field'  => ['table' => 'custom_fields', 'pk' => 'id'],
        ];

        if (isset($map[$type]) && Schema::hasColumn($map[$type]['table'], 'deleted_at')) {
            DB::table($map[$type]['table'])->where($map[$type]['pk'], $id)->update(['deleted_at' => null]);
            return back()->with('success', 'Data restored successfully.');
        }

        return back()->with('error', 'Invalid type or table does not support restore.');
    }

    public function forceDeleteMaster(Request $request, $type, $id)
    {
        $map = [
            'social' => ['table' => 'socialmedia', 'pk' => 'socialid'],
            'level'  => ['table' => 'level', 'pk' => 'levelid'],
            'role'   => ['table' => 'role', 'pk' => 'roleid'],
            'field'  => ['table' => 'custom_fields', 'pk' => 'id'],
        ];

        if (isset($map[$type])) {
            DB::table($map[$type]['table'])->where($map[$type]['pk'], $id)->delete();
            return back()->with('success', 'Data permanently deleted.');
        }

        return back()->with('error', 'Invalid type.');
    }

    private function applyActiveFilter($query, string $table): void
    {
        if (Schema::hasColumn($table, 'deleted_at')) {
            $query->whereNull('deleted_at');
        }
    }

    private function softDeleteOrHardDelete(string $table, string $key, $id): void
    {
        if (Schema::hasColumn($table, 'deleted_at')) {
            DB::table($table)->where($key, $id)->update(['deleted_at' => Carbon::now()]);
            return;
        }

        DB::table($table)->where($key, $id)->delete();
    }
}

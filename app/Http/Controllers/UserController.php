<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        $users = DB::table('user')
            ->leftJoin('level', 'user.levelid', '=', 'level.levelid')
            ->leftJoin('employer', 'user.userid', '=', 'employer.userid')
            ->leftJoin('role', 'employer.roleid', '=', 'role.roleid')
            ->select(
                'user.userid', 
                'user.username', 
                'user.levelid', 
                'level.levelname', 
                'employer.name', 
                'employer.email', 
                'employer.phonenumber', 
                'employer.roleid', 
                'role.rolename'
            )
            ->whereNull('user.deleted_at')
            ->get();

        $levels = DB::table('level')->get();
        $roles = DB::table('role')->get();
        $hasSuperadmin = DB::table('employer')->where('roleid', 1)->exists();

        return Inertia::render('User/Index', [
            'users' => $users,
            'levels' => $levels,
            'roles' => $roles,
            'hasSuperadmin' => $hasSuperadmin,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|string|max:255|unique:user,username',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:employer,email',
            'phonenumber' => 'required|string|max:20',
            'roleid' => 'required|integer|exists:role,roleid',
        ]);

        // Strict Rule: Only one Superadmin allowed
        if ($data['roleid'] == 1) {
            $existingSuperadmin = DB::table('employer')->where('roleid', 1)->exists();
            if ($existingSuperadmin) {
                return back()->withErrors(['roleid' => 'System can only have one Superadmin.']);
            }
        }

        DB::transaction(function () use ($data) {
            $userId = DB::table('user')->insertGetId([
                'username' => $data['username'],
                'password' => bcrypt('password'),
                'levelid' => 1, // Employer level
                'created_at' => now(),
            ]);

            DB::table('employer')->insert([
                'name' => $data['name'],
                'email' => $data['email'],
                'phonenumber' => $data['phonenumber'],
                'roleid' => $data['roleid'],
                'userid' => $userId,
                'created_at' => now(),
            ]);
        });

        return back()->with('success', 'Employer account created successfully.');
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'username' => 'required|string|max:255|unique:user,username,' . $id . ',userid',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:employer,email,' . $id . ',userid',
            'phonenumber' => 'required|string|max:20',
            'roleid' => 'required|integer|exists:role,roleid',
        ]);

        // If changing TO Superadmin, check quota
        if ($data['roleid'] == 1) {
            $existingSuperadmin = DB::table('employer')
                ->where('roleid', 1)
                ->where('userid', '!=', $id)
                ->exists();
            
            if ($existingSuperadmin) {
                return back()->withErrors(['roleid' => 'System can only have one Superadmin.']);
            }
        }

        DB::transaction(function () use ($data, $id) {
            DB::table('user')->where('userid', $id)->update([
                'username' => $data['username'],
                'updated_at' => now(),
            ]);

            DB::table('employer')->where('userid', $id)->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'phonenumber' => $data['phonenumber'],
                'roleid' => $data['roleid'],
                'updated_at' => now(),
            ]);
        });

        return back()->with('success', 'User updated successfully.');
    }

    public function resetPassword($id)
    {
        DB::table('user')->where('userid', $id)->update([
            'password' => bcrypt('password'),
        ]);

        return back()->with('success', 'Password reset to default successfully.');
    }

    public function destroy($id)
    {
        DB::table('user')->where('userid', $id)->update([
            'deleted_at' => now(),
        ]);

        return back()->with('success', 'User deleted successfully.');
    }
}

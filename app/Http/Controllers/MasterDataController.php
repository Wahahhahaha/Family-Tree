<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class MasterDataController extends Controller
{
    public function index()
    {
        return Inertia::render('MasterData/Index', [
            'socialMedia' => DB::table('socialmedia')->whereNull('deleted_at')->get(),
            'levels' => DB::table('level')->get(),
            'roles' => DB::table('role')->get(),
            'translations' => trans('master'),
        ]);
    }

    public function storeSocialMedia(Request $request)
    {
        $data = $request->validate([
            'socialname' => 'required|string|max:255|unique:socialmedia,socialname',
            'prefix' => 'nullable|string|max:255',
            'socialicon' => 'nullable|string|max:255',
        ]);

        DB::table('socialmedia')->insert(array_merge($data, [
            'created_at' => now(),
            'updated_at' => now(),
        ]));

        return back()->with('success', trans('master.success_add'));
    }

    public function updateSocialMedia(Request $request, $id)
    {
        $data = $request->validate([
            'socialname' => 'required|string|max:255|unique:socialmedia,socialname,' . $id . ',socialid',
            'prefix' => 'nullable|string|max:255',
            'socialicon' => 'nullable|string|max:255',
        ]);

        DB::table('socialmedia')->where('socialid', $id)->update(array_merge($data, [
            'updated_at' => now(),
        ]));

        return back()->with('success', trans('master.success_update'));
    }

    public function destroySocialMedia($id)
    {
        DB::table('socialmedia')->where('socialid', $id)->update([
            'deleted_at' => now(),
        ]);

        return back()->with('success', trans('master.success_delete'));
    }
}

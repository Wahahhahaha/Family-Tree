<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SystemController extends Controller
{
    public function index()
    {
        return Inertia::render('System/Index', [
            'system' => DB::table('system')->first(),
            'landing' => DB::table('landing_page_settings')->first(),
            'translations' => trans('system_page'),
        ]);
    }

    public function updateGlobal(Request $request)
    {
        $data = $request->validate([
            'systemname' => 'required|string|max:255',
            'systemcontact' => 'nullable|string|max:255',
            'systemmanager' => 'nullable|string|max:255',
            'systemaddress' => 'nullable|string',
            'systemlogo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('systemlogo')) {
            $file = $request->file('systemlogo');

            // Capture metadata before moving (WAMP safety)
            $storageName = time().'_'.$file->getClientOriginalName();
            $file->move(base_path('../uploads/system'), $storageName);
            $data['systemlogo'] = asset('uploads/system/'.$storageName);
        } else {
            unset($data['systemlogo']);
        }

        DB::table('system')->where('systemid', 1)->update($data);

        return back()->with('success', trans('system_page.success_update'));
    }

    public function updateLanding(Request $request)
    {
        $data = $request->validate([
            'family_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'head_of_family_name' => 'nullable|string|max:255',
            'head_of_family_message' => 'nullable|string',
            'head_of_family_photo' => 'nullable|image|max:2048',
            'created_by_name' => 'nullable|string|max:255',
            'created_by_photo' => 'nullable|image|max:2048',
            'designed_by_name' => 'nullable|string|max:255',
            'designed_by_photo' => 'nullable|image|max:2048',
            'approved_by_name' => 'nullable|string|max:255',
            'approved_by_photo' => 'nullable|image|max:2048',
            'acknowledged_by_name' => 'nullable|string|max:255',
            'acknowledged_by_photo' => 'nullable|image|max:2048',
        ]);

        $photoFields = [
            'head_of_family_photo', 'created_by_photo',
            'designed_by_photo', 'approved_by_photo',
            'acknowledged_by_photo',
        ];

        foreach ($photoFields as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);

                // Capture metadata before moving (WAMP safety)
                $storageName = time().'_'.$field.'_'.$file->getClientOriginalName();
                $file->move(base_path('../uploads/system'), $storageName);
                $data[$field] = asset('uploads/system/'.$storageName);
            } else {
                // Important: remove from data array so it doesn't overwrite existing with null
                unset($data[$field]);
            }
        }

        DB::table('landing_page_settings')->where('id', 1)->update($data);

        return back()->with('success', trans('system_page.success_landing'));
    }
}

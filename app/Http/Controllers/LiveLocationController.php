<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class LiveLocationController extends Controller
{
    public function index()
    {
        $locations = DB::table('live_locations')
            ->join('user', 'live_locations.userid', '=', 'user.userid')
            ->join('family_member', 'user.userid', '=', 'family_member.userid')
            ->select(
                'live_locations.latitude',
                'live_locations.longitude',
                'live_locations.updated_at',
                'family_member.name',
                'family_member.picture',
                'family_member.memberid'
            )
            // ->where('live_locations.updated_at', '>=', now()->subHours(24)) // Disabled for demo
            ->get();

        return Inertia::render('LiveLocation/Index', [
            'locations' => $locations,
            'translations' => trans('live_location'),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'accuracy' => 'nullable|numeric',
        ]);

        // VERIFY: Only Family Members can share location, not Employers
        $isFamilyMember = \Illuminate\Support\Facades\DB::table('family_member')
            ->where('userid', auth()->user()->userid)
            ->exists();

        if (!$isFamilyMember) {
            return response()->json([
                'error' => trans('live_location.unauthorized_error')
            ], 403);
        }

        DB::table('live_locations')->updateOrInsert(
            ['userid' => auth()->user()->userid],
            array_merge($data, ['updated_at' => now()])
        );

        return response()->json(['success' => true]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = DB::table('activity_log')
            ->leftJoin('user', 'activity_log.user_id', '=', 'user.userid')
            ->select(
                'activity_log.*',
                'user.username'
            )
            ->latest()
            ->paginate(20);

        return Inertia::render('Management/ActivityLog', [
            'logs' => $logs,
            'translations' => trans('activity'),
        ]);
    }
}

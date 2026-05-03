<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
             $systemSettings = $this->getSystemSettings();
             $hideNavbar = true;
             return view('all.landing', compact('systemSettings', 'hideNavbar'));
        }

        $today = Carbon::today();
        $systemSettings = $this->getSystemSettings();

        // 1. Ulang Tahun Hari Ini
        $birthdays = DB::table('family_member')
            ->whereMonth('birthdate', $today->month)
            ->whereDay('birthdate', $today->day)
            ->select('name', 'picture', 'birthdate')
            ->get();

        // 2. Event Mendatang (7 hari ke depan)
        $upcomingEvents = DB::table('events')
            ->where('event_date', '>=', $today)
            ->whereNull('deleted_at')
            ->orderBy('event_date', 'asc')
            ->limit(5)
            ->get();

        // 3. Statistik Singkat
        $totalMembers = DB::table('family_member')->count();

        return view('all.dashboard', compact('systemSettings', 'birthdays', 'upcomingEvents', 'totalMembers'));
    }
}

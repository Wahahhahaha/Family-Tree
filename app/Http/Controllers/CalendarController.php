<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function index(Request $request): View|\Illuminate\Http\RedirectResponse
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $systemSettings = $this->getSystemSettings();
        $events = [];

        if (Schema::hasTable('events')) {
            $publicEvents = DB::table('events')
                ->whereNull('deleted_at')
                ->orderBy('event_date', 'asc')
                ->get();

            foreach ($publicEvents as $event) {
                $eventDate = trim((string) ($event->event_date ?? ''));
                if ($eventDate === '') {
                    continue;
                }

                $events[] = [
                    'type' => 'event',
                    'title' => trim((string) ($event->title ?? '')) ?: __('calendar.family_event'),
                    'date' => $eventDate,
                ];
            }
        }

        if (Schema::hasTable('family_member')) {
            $members = DB::table('family_member')
                ->select('memberid', 'name', 'birthdate')
                ->orderBy('name')
                ->get();

            foreach ($members as $member) {
                $birthdate = trim((string) ($member->birthdate ?? ''));
                if ($birthdate === '') {
                    continue;
                }

                $events[] = [
                    'type' => 'birthday',
                    'title' => trim((string) ($member->name ?? '')) ?: __('calendar.family_member'),
                    'date' => Carbon::parse($birthdate)->toDateString(),
                ];
            }
        }

        return view('all.calendar', [
            'events' => $events,
            'systemSettings' => $systemSettings,
        ]);
    }
}

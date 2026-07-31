<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CalendarController extends Controller
{
    public function index()
    {
        $events = DB::table('events')
            ->whereNull('deleted_at')
            ->select('id', 'title', 'event_date', 'location', 'status')
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'date' => date('Y-m-d', strtotime($event->event_date)),
                    'time' => date('H:i', strtotime($event->event_date)),
                    'location' => $event->location,
                    'status' => $event->status,
                    'type' => 'event',
                ];
            });

        $birthdays = DB::table('family_member')
            ->whereNull('deleted_at')
            ->whereNotNull('birthdate')
            ->select('memberid', 'name', 'birthdate', 'picture')
            ->get()
            ->map(function ($member) {
                return [
                    'id' => $member->memberid,
                    'name' => $member->name,
                    'month_day' => date('m-d', strtotime($member->birthdate)),
                    'original_date' => $member->birthdate,
                    'picture' => $member->picture,
                    'type' => 'birthday',
                ];
            });

        return Inertia::render('Calendar/Index', [
            'events' => $events,
            'birthdays' => $birthdays,
            'translations' => trans('calendar'),
        ]);
    }
}

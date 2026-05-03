<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EventController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $currentUserId = (int) session('authenticated_user.userid');
        $member = DB::table('family_member')->where('userid', $currentUserId)->first();
        $memberId = $member ? $member->memberid : 0;
        $today = now();

        $allEvents = DB::table('events')
            ->whereNull('deleted_at')
            ->orderBy('event_date', 'asc')
            ->get()
            ->map(function ($event) use ($memberId) {
                $responses = DB::table('event_responses')
                    ->join('family_member', 'family_member.memberid', '=', 'event_responses.member_id')
                    ->where('event_id', $event->id)
                    ->select('family_member.name', 'event_responses.status')
                    ->get()
                    ->groupBy('status');

                $event->my_response = DB::table('event_responses')
                    ->where('event_id', $event->id)
                    ->where('member_id', $memberId)
                    ->value('status');

                $event->going_names = $responses->get('going', collect())->pluck('name')->toArray();
                $event->maybe_names = $responses->get('maybe', collect())->pluck('name')->toArray();
                $event->not_going_names = $responses->get('not_going', collect())->pluck('name')->toArray();

                return $event;
            });

        $upcomingEvents = $allEvents->filter(fn ($e) => Carbon::parse($e->event_date)->gte($today));
        $pastEvents = $allEvents->filter(fn ($e) => Carbon::parse($e->event_date)->lt($today))->sortByDesc('event_date');

        $systemSettings = $this->getSystemSettings();
        return view('all.events', compact('upcomingEvents', 'pastEvents', 'systemSettings'));
    }

    public function rsvp(Request $request)
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'status' => 'required|in:going,not_going,maybe',
        ]);

        $currentUserId = (int) session('authenticated_user.userid');
        $member = DB::table('family_member')->where('userid', $currentUserId)->first();

        if (!$member) {
            return response()->json(['success' => false, 'message' => __('events.member_not_found')]);
        }

        DB::table('event_responses')->updateOrInsert(
            ['event_id' => $validated['event_id'], 'member_id' => $member->memberid],
            ['status' => $validated['status'], 'updated_at' => now(), 'created_at' => now()]
        );

        return response()->json(['success' => true]);
    }

    public function store(Request $request)
    {
        if ((int) session('authenticated_user.roleid') > 2) {
            return abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'event_date' => 'required|date|after_or_equal:today',
            'location' => 'nullable|string',
            'description' => 'nullable|string',
        ], [
            'event_date.after_or_equal' => __('events.event_date_cannot_be_in_past'),
        ]);

        DB::table('events')->insert([
            'title' => $validated['title'],
            'event_date' => $validated['event_date'],
            'location' => $validated['location'],
            'description' => $validated['description'],
            'created_by' => session('authenticated_user.userid'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect('/events')->with('success', __('events.event_created_successfully'));
    }
}

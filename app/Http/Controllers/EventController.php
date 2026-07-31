<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Event;
use Inertia\Inertia;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::orderBy('event_date', 'desc')->paginate(10);

        return Inertia::render('Events/Index', [
            'events' => $events,
            'translations' => trans('events'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255|unique:events,title',
            'event_date' => 'required|date',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $member = \Illuminate\Support\Facades\DB::table('family_member')
            ->where('userid', auth()->user()->userid)
            ->first();

        if (!$member) {
            return back()->with('error', 'You must be linked to a family member to propose events.');
        }

        Event::create(array_merge($data, [
            'status' => 'proposed',
            'created_by' => $member->memberid,
        ]));

        return back()->with('success', 'New event proposed successfully.');
    }

    public function show(Event $event)
    {
        $event->load(['responses.member']);
        
        $myResponse = null;
        if (auth()->check()) {
            $member = \Illuminate\Support\Facades\DB::table('family_member')
                ->where('userid', auth()->user()->userid)
                ->first();
                
            if ($member) {
                $myResponse = \App\Models\EventResponse::where('event_id', $event->id)
                    ->where('member_id', $member->memberid)
                    ->first();
            }
        }

        return Inertia::render('Events/Show', [
            'event' => $event,
            'myResponse' => $myResponse,
            'translations' => trans('events'),
        ]);
    }

    public function respond(Request $request, Event $event)
    {
        $data = $request->validate([
            'status' => 'required|in:going,not_going,maybe',
        ]);

        $member = \Illuminate\Support\Facades\DB::table('family_member')
            ->where('userid', auth()->user()->userid)
            ->first();

        if (!$member) {
            return back()->with('error', 'You must be linked to a family member to respond.');
        }

        \App\Models\EventResponse::updateOrCreate(
            ['event_id' => $event->id, 'member_id' => $member->memberid],
            ['status' => $data['status']]
        );

        return back()->with('success', 'RSVP updated successfully.');
    }
}

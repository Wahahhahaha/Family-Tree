<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class LetterController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $member = DB::table('family_member')->where('userid', $user->userid)->first();
        
        if (!$member) {
            return Inertia::render('Letters/Index', [
                'error' => trans('letters.error_not_linked'),
                'inbox' => [],
                'sent' => [],
                'members' => [],
                'translations' => trans('letters'),
            ]);
        }

        $tab = $request->input('tab', 'inbox');

        $query = DB::table('letters')
            ->join('family_member as senders', 'letters.sender_id', '=', 'senders.memberid')
            ->join('family_member as receivers', 'letters.receiver_id', '=', 'receivers.memberid')
            ->select(
                'letters.*', 
                'senders.name as sender_name', 
                'senders.picture as sender_picture', 
                'receivers.name as receiver_name', 
                'receivers.picture as receiver_picture',
                'receivers.birthdate as receiver_birthdate'
            );

        if ($tab === 'sent') {
            $letters = $query->where('sender_id', $member->memberid)->latest()->paginate(10);
        } else {
            $letters = $query->where('receiver_id', $member->memberid)->latest()->paginate(10);
        }

        $allMembers = DB::table('family_member')
            ->where('memberid', '!=', $member->memberid)
            ->whereNull('deleted_at')
            ->select('memberid', 'name', 'picture', 'birthdate')
            ->get();

        return Inertia::render('Letters/Index', [
            'letters' => $letters,
            'members' => $allMembers,
            'currentTab' => $tab,
            'myMemberId' => $member->memberid,
            'translations' => trans('letters'),
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $member = DB::table('family_member')->where('userid', $user->userid)->first();

        if (!$member) {
            return back()->with('error', trans('letters.error_unauthorized'));
        }

        $data = $request->validate([
            'receiver_id' => 'required|exists:family_member,memberid',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'unlock_type' => 'required|in:immediate,date,age',
            'unlock_at' => 'nullable|required_if:unlock_type,date|date',
            'unlock_value' => 'nullable|required_if:unlock_type,age|integer|min:1|max:150',
        ]);

        DB::table('letters')->insert([
            'sender_id' => $member->memberid,
            'receiver_id' => $data['receiver_id'],
            'subject' => $data['subject'],
            'content' => $data['content'],
            'unlock_type' => $data['unlock_type'],
            'unlock_at' => $data['unlock_type'] === 'date' ? $data['unlock_at'] : null,
            'unlock_value' => $data['unlock_type'] === 'age' ? $data['unlock_value'] : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', trans('letters.success_sent'));
    }

    public function markAsRead($id)
    {
        $user = auth()->user();
        $member = DB::table('family_member')->where('userid', $user->userid)->first();

        DB::table('letters')
            ->where('id', $id)
            ->where('receiver_id', $member->memberid)
            ->update(['read_at' => now()]);

        return back();
    }
}

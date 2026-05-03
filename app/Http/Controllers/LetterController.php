<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LetterController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) return redirect('/login');
        
        $currentUserId = (int) session('authenticated_user.userid');
        $systemSettings = $this->getSystemSettings();

        $inbox = DB::table('letters')
            ->join('user', 'user.userid', '=', 'letters.sender_id')
            ->where('receiver_id', $currentUserId)
            ->select('letters.*', 'user.username as sender_name')
            ->orderBy('created_at', 'desc')
            ->get();

        $sent = DB::table('letters')
            ->join('user', 'user.userid', '=', 'letters.receiver_id')
            ->where('sender_id', $currentUserId)
            ->select('letters.*', 'user.username as receiver_name')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('all.letters.index', compact('inbox', 'sent', 'systemSettings'));
    }

    public function create()
    {
        if (!session('authenticated_user')) return redirect('/login');
        $systemSettings = $this->getSystemSettings();
        $users = DB::table('user')
            ->where('userid', '!=', session('authenticated_user.userid'))
            ->whereNull('deleted_at')
            ->get();
        return view('all.letters.create', compact('users', 'systemSettings'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'receiver_id' => 'required|exists:user,userid',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'unlock_type' => 'required|in:immediate,age,years',
            'unlock_value' => 'nullable|integer|min:1|max:120',
        ]);

        $unlockType = (string) $validated['unlock_type'];
        $unlockValue = in_array($unlockType, ['age', 'years'], true)
            ? (int) ($validated['unlock_value'] ?? 0)
            : null;
        $unlockAt = $unlockType === 'years' && $unlockValue !== null
            ? now()->addYears($unlockValue)
            : null;

        DB::table('letters')->insert([
            'sender_id' => session('authenticated_user.userid'),
            'receiver_id' => $validated['receiver_id'],
            'subject' => $validated['subject'],
            'content' => $validated['content'],
            'unlock_type' => $unlockType,
            'unlock_value' => $unlockValue,
            'unlock_at' => $unlockAt,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return redirect('/letters')->with('success', __('letters.letter_sent_successfully'));
    }

    public function show($id)
    {
        $currentUserId = (int) session('authenticated_user.userid');
        $letter = DB::table('letters')
            ->join('user', 'user.userid', '=', 'letters.sender_id')
            ->where('id', $id)
            ->where(function($q) use ($currentUserId) {
                $q->where('receiver_id', $currentUserId)->orWhere('sender_id', $currentUserId);
            })
            ->select('letters.*', 'user.username as sender_name')
            ->first();

        if (!$letter) abort(404);

        $unlockState = $this->resolveLetterUnlockState($letter);
        $canReadContent = !$unlockState['locked'] || $letter->sender_id == $currentUserId;

        if ($letter->receiver_id == $currentUserId && !$unlockState['locked'] && !$letter->read_at) {
            DB::table('letters')->where('id', $id)->update(['read_at' => now()]);
        }

        $systemSettings = $this->getSystemSettings();
        return view('all.letters.show', compact('letter', 'systemSettings', 'unlockState', 'canReadContent'));
    }

    private function resolveLetterUnlockState(object $letter): array
    {
        $unlockType = strtolower(trim((string) ($letter->unlock_type ?? 'immediate')));
        $unlockValue = (int) ($letter->unlock_value ?? 0);
        $unlockAt = null;
        $label = __('letters.immediate');
        $description = __('letters.this_letter_can_be_opened_right_away');

        if ($unlockType === 'age' && $unlockValue > 0) {
            $label = __('letters.unlock_at_age', ['age' => $unlockValue]);
            $receiverBirthdate = DB::table('family_member')
                ->where('userid', (int) $letter->receiver_id)
                ->value('birthdate');

            if (!empty($receiverBirthdate)) {
                try {
                    $unlockAt = Carbon::parse((string) $receiverBirthdate)->addYears($unlockValue);
                    $description = __('letters.open_on', ['date' => $unlockAt->format('d M Y')]);
                } catch (\Throwable $e) {
                    $description = __('letters.recipient_birthdate_could_not_be_parsed');
                }
            } else {
                $description = __('letters.recipient_birthdate_not_available');
            }
        } elseif ($unlockType === 'years' && $unlockValue > 0) {
            $label = __('letters.unlock_after_years', ['years' => $unlockValue]);
            try {
                $baseDate = !empty($letter->unlock_at)
                    ? Carbon::parse((string) $letter->unlock_at)
                    : Carbon::parse((string) ($letter->created_at ?? now()))->addYears($unlockValue);
                $unlockAt = $baseDate;
                $description = __('letters.open_on', ['date' => $unlockAt->format('d M Y')]);
            } catch (\Throwable $e) {
                $description = __('letters.unlock_date_could_not_be_calculated');
            }
        }

        $locked = $unlockAt instanceof Carbon ? now()->lt($unlockAt) : false;

        return [
            'type' => $unlockType,
            'value' => $unlockValue > 0 ? $unlockValue : null,
            'label' => $label,
            'description' => $description,
            'unlock_at' => $unlockAt,
            'locked' => $locked,
        ];
    }
}

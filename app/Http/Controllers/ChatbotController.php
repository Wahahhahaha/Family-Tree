<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ChatbotController extends Controller
{
    public function respond(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'history' => 'nullable|array',
        ]);

        // Temporarily hardcoded because Laravel cache is persistent on the user's machine
        $apiKey = env('GROQ_API_KEY');

        // Fetch Database Context
        $membersData = DB::table('family_member')
            ->select('memberid', 'name', 'gender', 'birthdate', 'life_status')
            ->whereNull('deleted_at')
            ->get();

        $relationsData = DB::table('relationship')
            ->join('family_member as m1', 'relationship.memberid', '=', 'm1.memberid')
            ->join('family_member as m2', 'relationship.relatedmemberid', '=', 'm2.memberid')
            ->select('m1.name as member1', 'relationship.relationtype', 'm2.name as member2')
            ->whereNull('relationship.deleted_at')
            ->get();

        $dbContext = "DATABASE RECORD:\n[Family Members]\n";
        foreach($membersData as $m) {
            $dbContext .= "- {$m->name} (Gender: {$m->gender}, Born: {$m->birthdate}, Status: {$m->life_status})\n";
        }
        $dbContext .= "\n[Relationships]\n";
        foreach($relationsData as $r) {
            if ($r->relationtype === 'child') {
                $dbContext .= "- {$r->member2} is the child of {$r->member1}\n";
            } elseif ($r->relationtype === 'partner') {
                $dbContext .= "- {$r->member1} and {$r->member2} are partners/spouses\n";
            } else {
                $dbContext .= "- {$r->member1} is related to {$r->member2} ({$r->relationtype})\n";
            }
        }

        $langInstruction = trans('chatbot.system_instruction_lang');

        $systemPrompt = [
            'role' => 'system',
            'content' => "You are an exclusive digital assistant for a family heritage and genealogy application. Your tone must be highly polite, formal, and respectful.\n\n" .
                         "$langInstruction\n\n" .
                         "CRITICAL RULE: You are strictly limited to discussing topics related to family trees, genealogy, heritage, family relationships, and the application's features (such as ancestral tree, time-capsule letters, live location, family wiki, inheritance, and galleries). If the user asks about ANYTHING unrelated to these topics (e.g., general knowledge, programming, weather, news, math, recipes), you MUST refuse to answer. Reply politely stating that you are specifically designed ONLY for family heritage matters and cannot assist with outside topics.\n\n" .
                         "DATABASE INSTRUCTION:\n" .
                         "1. Use the DATABASE RECORD below to answer questions about the family.\n" .
                         "2. If a user asks 'Who is [Name]?', find them in the record and explain who they are, their gender, birthdate, and their specific relationships (child of, partner of, etc.).\n" .
                         "3. AMBIGUITY HANDLING: If there are multiple people with the same or similar name (e.g., two people named 'Budi'), you MUST NOT guess. Instead, list the possibilities found in the database and ask the user to clarify which one they mean by mentioning their birthdate or partner's name.\n" .
                         "4. CONTINUITY: Always prioritize the context of the current conversation history. If the user asks 'What about his children?' after discussing Budi, you must know 'his' refers to Budi based on history.\n" .
                         "5. Keep responses concise and suitable for a small chat window.\n\n" .
                         "DATABASE RECORD:\n" .
                         $dbContext
        ];

        $messages = [$systemPrompt];

        if ($request->has('history') && is_array($request->history)) {
            foreach ($request->history as $msg) {
                if (isset($msg['role']) && isset($msg['content']) && in_array($msg['role'], ['user', 'assistant'])) {
                    $messages[] = [
                        'role' => $msg['role'],
                        'content' => $msg['content']
                    ];
                }
            }
        }

        $messages[] = ['role' => 'user', 'content' => $request->message];

        try {
            $response = Http::withToken($apiKey)
                ->withoutVerifying()
                ->timeout(15)
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => 'llama-3.1-8b-instant',
                    'messages' => $messages,
                ]);

            if ($response->successful()) {
                return response()->json(['reply' => $response->json('choices.0.message.content')]);
            } else {
                $error = $response->json();
                Log::error('Groq API Error Detail: ' . json_encode($error));
                return response()->json([
                    'error' => 'AI Provider error: ' . ($error['error']['message'] ?? 'Unknown error'),
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Chatbot Exception: ' . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred.'], 500);
        }
    }
}

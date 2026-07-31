<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class WikiController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('family_member')->whereNull('deleted_at');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('birthplace', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('job', 'like', "%{$search}%");
            });
        }

        $members = $query->select('memberid', 'name', 'picture', 'life_status', 'address', 'birthplace', 'job')
            ->orderBy('name')
            ->paginate(15);

        if ($request->wantsJson()) {
            return response()->json($members);
        }

        return Inertia::render('Wiki/Index', [
            'initialMembers' => $members,
            'filters' => $request->only(['search']),
            'translations' => trans('wiki')
        ]);
    }

    public function show($id)
    {
        $member = DB::table('family_member')
            ->where('memberid', $id)
            ->first();

        if (!$member) {
            abort(404);
        }

        // Add more detail logic here if needed (e.g. bio from another table)
        return Inertia::render('Wiki/Show', [
            'member' => $member,
            'translations' => trans('wiki')
        ]);
    }
}

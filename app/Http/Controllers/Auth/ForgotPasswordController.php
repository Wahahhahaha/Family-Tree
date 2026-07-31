<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ForgotPasswordController extends Controller
{
    public function create()
    {
        return Inertia::render('Auth/ForgotPassword', [
            'translations' => trans('login')
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Logic for sending reset link would go here.
        // For now, we'll just redirect back with a status.

        return back()->with('status', 'We have emailed your password reset link!');
    }
}

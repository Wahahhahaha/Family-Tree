<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function switch(Request $request, string $locale): RedirectResponse
    {
        $locale = $this->setCurrentLocale($locale);
        $next = trim((string) $request->query('next', '/'));

        if ($next === '' || !str_starts_with($next, '/')) {
            $next = '/';
        }

        return redirect($next)->withCookie(cookie('family_tree_locale', $locale, 60 * 24 * 365));
    }
}

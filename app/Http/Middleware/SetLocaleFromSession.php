<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleFromSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale');
        if (!is_string($locale) || trim($locale) === '') {
            $locale = $request->cookie('family_tree_locale', 'en');
        }

        $locale = strtolower(trim((string) $locale));
        if (!in_array($locale, ['en', 'id'], true)) {
            $locale = 'en';
        }

        if ($request->session()->get('locale') !== $locale) {
            $request->session()->put('locale', $locale);
        }

        if ($request->query('lang') !== null) {
            $queryLocale = strtolower(trim((string) $request->query('lang')));
            if (in_array($queryLocale, ['en', 'id'], true)) {
                $locale = $queryLocale;
                $request->session()->put('locale', $locale);
            }
        }

        app()->setLocale($locale);

        return $next($request);
    }
}

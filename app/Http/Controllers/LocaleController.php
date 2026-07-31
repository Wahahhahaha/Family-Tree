<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LocaleController extends Controller
{
    public function switch(Request $request, $locale)
    {
        if (in_array($locale, ['en', 'id'])) {
            Session::put('locale', $locale);
        }

        return back();
    }
}

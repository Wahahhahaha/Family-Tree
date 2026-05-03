<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GraveyardController extends Controller
{
    public function index()
    {
        $systemSettings = $this->getSystemSettings();
        $deceasedMembers = DB::table('family_member')
            ->where('life_status', 'deceased')
            ->whereNotNull('burial_latitude')
            ->whereNotNull('burial_longitude')
            ->get();

        return view('all.graveyard', compact('systemSettings', 'deceasedMembers'));
    }
}

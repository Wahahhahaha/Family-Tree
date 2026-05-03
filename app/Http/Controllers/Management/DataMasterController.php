<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\Level;
use App\Models\Role;
use App\Models\SocialMedia;
use Illuminate\Http\Request;

class DataMasterController extends Controller
{
    public function index(Request $request)
    {
        return view("superadmin.data-master", [
            "pageClass" => "page-family-tree",
            "systemSettings" => $this->getSystemSettings(),
            "levels" => Level::all(),
            "roles" => Role::all(),
            "socialMedia" => [] // SocialMedia::all() jika sudah ada modelnya
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\FamilyTreeService;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index(FamilyTreeService $treeService)
    {
        if (!auth()->check()) {
            $landingSettings = \Illuminate\Support\Facades\DB::table('landing_page_settings')->first();

            return Inertia::render('Landing', [
                'settings' => $landingSettings,
                'translations' => trans('landing')
            ]);
        }

        $treeData = $treeService->getTreeData();
        $socialPlatforms = \Illuminate\Support\Facades\DB::table('socialmedia')
            ->whereNull('deleted_at')
            ->get();

        return Inertia::render('Home', [
            'treeData' => $treeData,
            'socialPlatforms' => $socialPlatforms,
            'translations' => trans('home')
        ]);
    }
}

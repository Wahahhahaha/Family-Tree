<?php

namespace App\Http\Controllers;

use App\Services\LiveLocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LiveLocationController extends Controller
{
    public function __construct(
        protected LiveLocationService $liveLocationService
    ) {
    }

    public function index(Request $request): View|RedirectResponse
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $currentUserId = (int) ($request->session()->get('authenticated_user.userid') ?? 0);
        $pageData = $this->liveLocationService->buildPageData($currentUserId);

        return view('all.live-location', [
            'pageClass' => 'page-live-location',
            'pageData' => $pageData,
            'systemSettings' => $this->getSystemSettings(),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        if (!$request->session()->has('authenticated_user')) {
            return response()->json([
                'success' => false,
                'message' => __('live_location.unauthorized'),
            ], 401);
        }

        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'min:0'],
        ]);

        $currentUserId = (int) ($request->session()->get('authenticated_user.userid') ?? 0);
        $result = $this->liveLocationService->storeLocation($currentUserId, $validated);

        return response()->json($result);
    }
}

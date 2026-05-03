@php
    $pageClass = 'page-live-location';
    $liveLocationPageData = $pageData ?? [
        'current_user_id' => 0,
        'current_member_id' => 0,
        'current_member_name' => '',
        'markers' => [],
        'center' => [0, 0],
        'zoom' => 2,
    ];
@endphp
@extends('layouts.app')

@section('title', __('live_location.title'))

@section('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
    <style>
        body.page-live-location {
            --bg: #f5f8fb;
            --surface: #ffffff;
            --text: #102133;
            --muted: #678196;
            --line: #d8e5ef;
            --tree-link: #8cbddc;
            --tree-link-width: 2px;
            --tree-link-overlap: 3px;
            --tree-link-fuse: 2px;
            --tree-link-height: 38px;
            --partner-link-y: 92px;
            --tree-level-gap: 40px;
            --partner-gap: 20px;
            --member-card-width: 170px;
            --tree-display-base-zoom: 0.64;
            --primary: #1f9ad6;
            --primary-soft: #ddf1fa;
            --accent: #17b67f;
            --accent-soft: #dcf7ee;
            --shadow: 0 16px 40px rgba(17, 56, 82, 0.08);
            --shadow-hover: 0 22px 48px rgba(17, 56, 82, 0.16);
            --radius-xl: 24px;
            --radius-md: 16px;
            margin: 0;
            font-family: "Manrope", sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at 90% 10%, #e4f8f1 0%, transparent 35%),
                radial-gradient(circle at 0% 0%, #e5f2fb 0%, transparent 40%),
                var(--bg);
        }

        body.page-live-location main {
            width: 100%;
        }

        body.page-live-location .page-navbar-shell {
            width: 100%;
            max-width: none;
            margin: 0;
            padding: 12px 15px 0;
            box-sizing: border-box;
        }

        body.page-live-location .topbar {
            background: var(--surface);
            border: 1px solid #edf3f8;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow);
            width: 100%;
            margin: 0;
            box-sizing: border-box;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 16px 20px;
            position: sticky;
            top: 12px;
            z-index: 10;
        }

        body.page-live-location .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        body.page-live-location .brand-mark {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            background: linear-gradient(140deg, var(--primary), var(--accent));
            color: #fff;
            display: grid;
            place-items: center;
            font-size: 20px;
            font-weight: 800;
            font-family: "Sora", sans-serif;
            overflow: hidden;
        }

        body.page-live-location .brand-mark.has-logo {
            background: transparent;
            width: auto;
            height: auto;
            overflow: visible;
        }

        body.page-live-location .brand-mark a {
            display: grid;
            place-items: center;
            width: 100%;
            height: 100%;
            color: inherit;
            text-decoration: none;
        }

        body.page-live-location .brand-mark.has-logo a {
            display: flex;
            align-items: center;
        }

        body.page-live-location .brand-logo {
            width: auto;
            height: 42px;
            max-width: 180px;
            border-radius: 0;
            object-fit: contain;
            display: block;
        }

        body.page-live-location .brand h1 {
            margin: 0;
            font-size: 18px;
            font-family: "Sora", sans-serif;
            font-weight: 700;
        }

        body.page-live-location .brand p {
            margin: 2px 0 0;
            color: var(--muted);
            font-size: 12px;
            font-weight: 600;
        }

        body.page-live-location .actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        body.page-live-location .menu-dropdown {
            position: relative;
        }

        body.page-live-location .dropdown-toggle {
            min-width: 130px;
        }

        body.page-live-location .dropdown-menu {
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            min-width: 190px;
            padding: 8px;
            border-radius: 12px;
            border: 1px solid #d8e8f3;
            background: #ffffff;
            box-shadow: 0 14px 30px rgba(16, 58, 84, 0.12);
            display: none;
            z-index: 20;
        }

        body.page-live-location .dropdown-menu-right {
            left: auto;
            right: 0;
        }

        body.page-live-location .menu-dropdown.open .dropdown-menu {
            display: block;
        }

        body.page-live-location .menu-dropdown:focus-within .dropdown-menu {
            display: block;
        }

        body.page-live-location .dropdown-item {
            display: block;
            padding: 9px 10px;
            border-radius: 9px;
            color: #1b4a65;
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
        }

        body.page-live-location .dropdown-item:hover {
            background: #eaf4fb;
        }

        body.page-live-location .dropdown-form {
            margin: 0;
        }

        body.page-live-location .dropdown-submit {
            width: 100%;
            border: 0;
            background: transparent;
            text-align: left;
            font-family: inherit;
            cursor: pointer;
        }

        body.page-live-location .btn {
            border: 0;
            height: 40px;
            border-radius: 12px;
            padding: 0 14px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            font-family: inherit;
        }

        body.page-live-location .btn-primary {
            color: #fff;
            background: linear-gradient(120deg, var(--primary), #35b0df);
        }

        body.page-live-location .btn-soft {
            color: #0f5f7b;
            background: var(--primary-soft);
        }

        body.page-live-location .btn-ghost {
            color: #1f4a63;
            background: #eaf2f8;
        }

        @media (max-width: 760px) {
            body.page-live-location .page-navbar-shell {
                padding: 8px 8px 0;
            }

            body.page-live-location .topbar {
                width: 100%;
                margin: 0;
                flex-direction: column;
                align-items: stretch;
            }

            body.page-live-location .actions {
                flex-wrap: wrap;
            }
        }

        .live-location-page {
            width: min(1440px, calc(100% - 24px));
            margin: 0 auto;
            padding: 14px 0 24px;
            box-sizing: border-box;
        }

        .live-location-hero {
            display: grid;
            grid-template-columns: 1.5fr 0.9fr;
            gap: 16px;
            align-items: stretch;
            margin-bottom: 16px;
        }

        .live-location-panel,
        .live-location-side-card {
            background: #ffffff;
            border: 1px solid #dbe7f3;
            border-radius: 24px;
            box-shadow: 0 18px 48px rgba(28, 56, 86, 0.10);
        }

        .live-location-panel {
            padding: 20px;
        }

        .live-location-kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: #edf5ff;
            color: #1f4b8f;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .live-location-title {
            margin: 16px 0 8px;
            font-family: 'Sora', sans-serif;
            font-size: clamp(30px, 4vw, 48px);
            line-height: 1.05;
            color: #0f172a;
        }

        .live-location-copy {
            margin: 0;
            max-width: 68ch;
            color: #47607a;
            font-size: 15px;
            line-height: 1.75;
        }

        .live-location-status {
            margin-top: 16px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .live-location-status span {
            padding: 10px 14px;
            border-radius: 14px;
            background: #f5f9ff;
            border: 1px solid #e3edf7;
            color: #27445f;
            font-size: 13px;
            font-weight: 600;
        }

        .live-location-side-card {
            padding: 20px;
            display: grid;
            gap: 16px;
            align-content: start;
        }

        .live-location-stat {
            padding: 16px;
            border-radius: 18px;
            background: linear-gradient(180deg, #f7fbff 0%, #eef6ff 100%);
            border: 1px solid #dde9f5;
        }

        .live-location-stat small {
            display: block;
            margin-bottom: 6px;
            color: #5d748a;
            text-transform: uppercase;
            letter-spacing: .08em;
            font-size: 11px;
            font-weight: 800;
        }

        .live-location-stat strong {
            display: block;
            color: #0f172a;
            font-family: 'Sora', sans-serif;
            font-size: 28px;
        }

        .live-location-stat p {
            margin: 8px 0 0;
            color: #52697f;
            font-size: 13px;
            line-height: 1.65;
        }

        .live-location-map-shell {
            background: #ffffff;
            border: 1px solid #dbe7f3;
            border-radius: 28px;
            box-shadow: 0 18px 48px rgba(28, 56, 86, 0.10);
            overflow: hidden;
        }

        .live-location-map-header {
            padding: 18px 20px 0;
        }

        .live-location-map-header h2 {
            margin: 0;
            font-family: 'Sora', sans-serif;
            font-size: 22px;
            color: #0f172a;
        }

        .live-location-map-header p {
            margin: 8px 0 0;
            color: #54687c;
            font-size: 14px;
        }

        #liveLocationMap {
            width: 100%;
            height: min(72vh, 720px);
            min-height: 560px;
            margin-top: 16px;
        }

        .live-location-marker {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            border: 3px solid #ffffff;
            box-shadow: 0 10px 22px rgba(15, 23, 42, 0.20);
            overflow: hidden;
            background: #dce8f6;
        }

        .live-location-marker img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .live-location-tooltip.leaflet-tooltip {
            background: transparent;
            border: 0;
            box-shadow: none;
            padding: 0;
            margin-top: 0;
        }

        .live-location-tooltip.leaflet-tooltip::before {
            border-top-color: transparent;
        }

        .live-location-tooltip-card {
            width: 220px;
            box-shadow: 0 16px 40px rgba(15, 23, 42, 0.18);
        }

        .live-location-tooltip-card {
            padding: 14px 14px 12px;
            background: #ffffff;
            border-radius: 16px;
        }

        .live-location-tooltip-card h3 {
            margin: 0 0 4px;
            color: #0f172a;
            font-family: 'Sora', sans-serif;
            font-size: 16px;
        }

        .live-location-tooltip-card .relation {
            margin: 0 0 8px;
            color: #1f4b8f;
            font-weight: 800;
            font-size: 12px;
            letter-spacing: .02em;
            text-transform: uppercase;
        }

        .live-location-tooltip-card .updated-at {
            margin: 0;
            color: #566b80;
            font-size: 12px;
            line-height: 1.6;
        }

        .live-location-tooltip-card .updated-at strong {
            color: #0f172a;
        }

        @media (max-width: 980px) {
            .live-location-hero {
                grid-template-columns: 1fr;
            }

            .live-location-page {
                width: min(100%, calc(100% - 16px));
                padding-top: 8px;
            }

            #liveLocationMap {
                min-height: 440px;
            }
        }
    </style>
@endsection

@section('content')
    <div class="live-location-page">
        <section class="live-location-hero">
            <div class="live-location-panel">
                <span class="live-location-kicker"><i data-lucide="map-pin" style="width:16px;height:16px;"></i> {{ __('live_location.kicker') }}</span>
                <h1 class="live-location-title">{{ __('live_location.hero_title') }}</h1>
                <p class="live-location-copy">{{ __('live_location.hero_copy') }}</p>
                <div class="live-location-status">
                    <span>{{ __('live_location.chip_interface') }}</span>
                    <span>{{ __('live_location.chip_hover') }}</span>
                    <span>{{ __('live_location.chip_auto') }}</span>
                </div>
                <p id="liveLocationStatusMessage" class="live-location-copy" style="margin-top: 14px; color: #27445f; font-weight: 600;"></p>
            </div>

            <aside class="live-location-side-card">
                <div class="live-location-stat">
                    <small>{{ __('live_location.tracked_members') }}</small>
                    <strong id="trackedMemberCount">{{ count($liveLocationPageData['markers']) }}</strong>
                    <p>{{ __('live_location.tracked_members_copy') }}</p>
                </div>
                <div class="live-location-stat">
                    <small>{{ __('live_location.current_user') }}</small>
                    <strong style="font-size: 22px;">{{ $liveLocationPageData['current_member_name'] ?: __('live_location.unknown') }}</strong>
                    <p>{{ __('live_location.current_user_copy') }}</p>
                </div>
                <div class="live-location-stat">
                    <small>{{ __('live_location.map_layer') }}</small>
                    <strong style="font-size: 22px;">OpenStreetMap</strong>
                    <p>{{ __('live_location.map_layer_copy') }}</p>
                </div>
            </aside>
        </section>

        <section class="live-location-map-shell">
            <div class="live-location-map-header">
                <h2>{{ __('live_location.map_title') }}</h2>
                <p>{{ __('live_location.map_copy') }}</p>
            </div>
            <div id="liveLocationMap" aria-label="{{ __('live_location.map_title') }}"></div>
        </section>
    </div>

    <script>
        window.liveLocationPageData = {!! json_encode($liveLocationPageData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!};
        window.liveLocationTranslations = {!! json_encode([
            'member_photo' => __('live_location.member_photo'),
            'unknown_member' => __('live_location.unknown_member'),
            'other_family_member' => __('live_location.other_family_member'),
            'last_updated' => __('live_location.last_updated'),
            'live_markers_ready' => __('live_location.live_markers_ready'),
            'no_tracked_locations' => __('live_location.no_tracked_locations'),
            'updating_live_location' => __('live_location.updating_live_location'),
            'location_shared_updated' => __('live_location.location_shared_updated'),
            'unable_update_live_location' => __('live_location.unable_update_live_location'),
            'location_access_denied' => __('live_location.location_access_denied'),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!};
    </script>
@endsection

@section('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
@endsection

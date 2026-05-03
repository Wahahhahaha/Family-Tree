<?php $pageClass = 'page-family-tree page-wiki'; ?>
@extends('layouts.app')

@section('title', __('wiki.title'))

@section('styles')
<style>
    body.page-wiki {
        --bg: #f4f8fb;
        --surface: rgba(255, 255, 255, 0.96);
        --surface-strong: #ffffff;
        --text: #102133;
        --muted: #688196;
        --line: #dce7ef;
        --primary: #1f9ad6;
        --primary-soft: #dff2fb;
        --accent: #17b67f;
        --accent-soft: #dcf7ee;
        --shadow: 0 16px 40px rgba(17, 56, 82, 0.08);
        background:
            radial-gradient(circle at 90% 10%, rgba(23, 182, 127, 0.10) 0%, transparent 30%),
            radial-gradient(circle at 0% 0%, rgba(31, 154, 214, 0.10) 0%, transparent 35%),
            var(--bg);
        color: var(--text);
        font-family: "Manrope", sans-serif;
    }

    body.page-wiki main {
        width: 100%;
    }

    .wiki-shell {
        width: 100%;
        max-width: none;
        margin: 0;
        padding: 12px 15px 56px;
        box-sizing: border-box;
    }

    .wiki-hero,
    .wiki-panel,
    .wiki-member-card {
        background: var(--surface);
        border: 1px solid rgba(220, 231, 239, 0.92);
        box-shadow: var(--shadow);
        border-radius: 26px;
        backdrop-filter: blur(18px);
    }

    .wiki-hero {
        padding: 24px;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 18px;
        margin-bottom: 22px;
    }

    .wiki-hero h1 {
        margin: 10px 0 10px;
        font-family: "Sora", sans-serif;
        font-size: clamp(24px, 2.8vw, 36px);
        line-height: 1.02;
    }

    .eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        background: var(--primary-soft);
        color: #156e9f;
    }

    .wiki-hero p {
        margin: 0;
        color: var(--muted);
        max-width: 700px;
    }

    .wiki-search {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 12px;
        margin-top: 18px;
    }

    .wiki-search input {
        height: 52px;
        border-radius: 16px;
        border: 1px solid var(--line);
        padding: 0 16px;
        font-size: 15px;
        outline: none;
        background: var(--surface-strong);
    }

    .wiki-search input:focus {
        border-color: rgba(31, 154, 214, 0.55);
        box-shadow: 0 0 0 4px rgba(31, 154, 214, 0.12);
    }

    .wiki-help {
        margin-top: 10px;
        font-size: 13px;
        color: var(--muted);
    }

    .wiki-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin: 0 0 16px;
    }

    .wiki-toolbar h2 {
        margin: 0;
        font-family: "Sora", sans-serif;
        font-size: 22px;
    }

    .wiki-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }

    .wiki-member-card {
        display: flex;
        flex-direction: column;
        overflow: hidden;
        transition: transform .2s ease, box-shadow .2s ease;
        text-decoration: none;
        color: inherit;
        min-height: 100%;
    }

    .wiki-member-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 44px rgba(17, 56, 82, 0.12);
    }

    .wiki-member-photo {
        aspect-ratio: 4 / 3;
        background: linear-gradient(135deg, rgba(31,154,214,.08), rgba(23,182,127,.08));
        position: relative;
    }

    .wiki-member-photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .wiki-member-photo .placeholder {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #7b95a8;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .wiki-member-body {
        padding: 18px;
    }

    .wiki-member-body h3 {
        margin: 0 0 8px;
        font-family: "Sora", sans-serif;
        font-size: 18px;
    }

    .wiki-member-body p {
        margin: 0;
        color: var(--muted);
        font-size: 14px;
    }

    .wiki-member-meta {
        margin-top: 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        font-size: 12px;
        color: #6d8395;
    }

    .wiki-empty {
        padding: 36px;
        background: var(--surface);
        border: 1px dashed var(--line);
        border-radius: 24px;
        text-align: center;
        color: var(--muted);
    }

    @media (max-width: 1180px) {
        .wiki-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }

    @media (max-width: 900px) {
        .wiki-hero { flex-direction: column; }
        .wiki-search { grid-template-columns: 1fr; }
        .wiki-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    @media (max-width: 640px) {
        .wiki-grid { grid-template-columns: 1fr; }
    }
</style>
@endsection

@section('content')
<div class="wiki-shell">
    <section class="wiki-hero">
        <div>
            <span class="eyebrow">{{ __('wiki.title') }}</span>
            <h1>{{ __('wiki.member_card_title') }}</h1>
            <p>{{ __('wiki.member_card_copy') }}</p>

            <form class="wiki-search" method="GET" action="/wiki">
                <input type="text" name="q" value="{{ $searchQuery }}" placeholder="{{ __('wiki.search_placeholder') }}">
                <button class="btn btn-primary" type="submit">{{ __('wiki.search_button') }}</button>
            </form>
            <div class="wiki-help">{{ __('wiki.search_hint') }}</div>
        </div>

    </section>

    <div class="wiki-toolbar">
        <h2>{{ __('wiki.search_title') }}</h2>
        @if ($searchQuery !== '')
            <div style="color: var(--muted); font-size: 14px;">
                {{ $members->count() }} {{ __('wiki.view_details') }}
            </div>
        @endif
    </div>

    @if ($searchQuery === '')
        <div class="wiki-empty">
            <div style="font-size: 18px; font-weight: 800; color: var(--text); margin-bottom: 8px;">{{ __('wiki.search_hint') }}</div>
            <div>{{ __('wiki.search_placeholder') }}</div>
        </div>
    @elseif ($members->count() > 0)
        <div class="wiki-grid">
            @foreach ($members as $member)
                @php
                    $memberPicture = trim((string) ($member->picture ?? ''));
                    $memberPictureUrl = '';
                    if ($memberPicture !== '') {
                        $memberPictureUrl = preg_match('#^https?://#i', $memberPicture) || str_starts_with($memberPicture, 'data:')
                            ? $memberPicture
                            : asset(ltrim($memberPicture, '/'));
                    }
                @endphp
                <a class="wiki-member-card" href="/member/{{ (int) $member->memberid }}/wiki">
                    <div class="wiki-member-photo">
                        @if ($memberPictureUrl !== '')
                            <img src="{{ $memberPictureUrl }}" alt="{{ $member->name }}">
                        @else
                            <div class="placeholder">{{ $member->name }}</div>
                        @endif
                    </div>
                    <div class="wiki-member-body">
                        <h3>{{ $member->name }}</h3>
                        <p>{{ __('wiki.member_card_label') }} #{{ (int) $member->memberid }}</p>
                        <div class="wiki-member-meta">
                            <span>{{ __('wiki.view_details') }}</span>
                            <i data-lucide="arrow-right" style="width: 16px; height: 16px;"></i>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @else
        <div class="wiki-empty">
            <div style="font-size: 18px; font-weight: 800; color: var(--text); margin-bottom: 8px;">{{ __('wiki.no_results') }}</div>
            <div>{{ __('wiki.search_hint') }}</div>
        </div>
    @endif
</div>
@endsection

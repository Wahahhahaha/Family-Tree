<?php $pageClass = 'page-family-tree page-wiki'; ?>
@extends('layouts.app')

@section('title', ($member->name ?? 'Member') . ' - ' . __('wiki.title'))

@section('styles')
<style>
    body.page-wiki {
        --bg: #f5f8fb;
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
    .wiki-card,
    .wiki-section,
    .timeline-form-card,
    .timeline-filter-card,
    .timeline-entry,
    .vault-card {
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
        font-size: clamp(32px, 4vw, 50px);
        line-height: 1.02;
    }

    .wiki-hero p {
        margin: 0;
        color: var(--muted);
        max-width: 760px;
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

    .wiki-header-actions {
        display: flex;
        flex-direction: column;
        gap: 12px;
        align-items: flex-end;
        min-width: 260px;
    }

    .wiki-header-actions .btn {
        min-height: 42px;
        padding: 10px 16px;
        border-radius: 14px;
        font-size: 14px;
        font-weight: 800;
    }

    .wiki-header-actions .btn.btn-soft {
        background: rgba(255, 255, 255, 0.86);
        border: 1px solid rgba(31, 154, 214, 0.18);
        color: #1b4f6b;
    }

    .wiki-header-actions .btn.btn-primary {
        box-shadow: 0 12px 24px rgba(31, 154, 214, 0.16);
    }

    .wiki-search-mini {
        width: 100%;
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 10px;
        margin-top: 18px;
    }

    .wiki-search-mini .btn {
        min-height: 48px;
        padding: 0 18px;
        border-radius: 14px;
        font-size: 14px;
        font-weight: 800;
    }

    .wiki-search-mini input,
    .wiki-search input {
        height: 48px;
        border-radius: 14px;
        border: 1px solid var(--line);
        padding: 0 14px;
        font-size: 14px;
        outline: none;
        background: var(--surface-strong);
    }

    .wiki-search-mini input:focus,
    .wiki-search input:focus {
        border-color: rgba(31, 154, 214, 0.55);
        box-shadow: 0 0 0 4px rgba(31, 154, 214, 0.12);
    }

    .wiki-search {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 12px;
        margin-top: 18px;
    }

    .wiki-layout {
        display: grid;
        grid-template-columns: 320px minmax(0, 1fr);
        gap: 22px;
        align-items: start;
    }

    .wiki-sidebar {
        padding: 22px;
        position: sticky;
        top: 18px;
    }

    .wiki-main {
        min-width: 0;
        display: grid;
        gap: 18px;
    }

    .wiki-portrait {
        aspect-ratio: 4 / 4.5;
        border-radius: 24px;
        overflow: hidden;
        background: linear-gradient(135deg, rgba(31,154,214,.08), rgba(23,182,127,.10));
        position: relative;
    }

    .wiki-portrait img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .wiki-portrait .placeholder {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #7990a3;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        text-align: center;
        padding: 20px;
    }

    .member-name {
        margin: 18px 0 8px;
        font-family: "Sora", sans-serif;
        font-size: 28px;
        line-height: 1.05;
    }

    .member-badge-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 14px;
    }

    .member-badge,
    .timeline-badge,
    .timeline-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        line-height: 1;
    }

    .member-badge {
        background: var(--primary-soft);
        color: #156e9f;
    }

    .member-stat-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        margin-top: 18px;
    }

    .member-stat {
        padding: 14px;
        border-radius: 18px;
        border: 1px solid var(--line);
        background: rgba(255, 255, 255, 0.88);
    }

    .member-stat span,
    .info-row span,
    .timeline-meta-box span {
        display: block;
        color: var(--muted);
        font-size: 12px;
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: .05em;
        font-weight: 800;
    }

    .member-stat strong,
    .info-row strong,
    .timeline-meta-box strong {
        display: block;
        font-size: 14px;
        line-height: 1.5;
    }

    .info-list {
        margin: 16px 0 0;
        display: grid;
        gap: 10px;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 14px;
        border: 1px solid var(--line);
        border-radius: 16px;
        background: rgba(255,255,255,.8);
    }

    .wiki-section {
        padding: 20px;
    }

    .wiki-section-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 16px;
    }

    .wiki-section-header h2 {
        margin: 0;
        font-family: "Sora", sans-serif;
        font-size: 22px;
    }

    .wiki-section-header p {
        margin: 8px 0 0;
        color: var(--muted);
    }

    .bio-copy {
        line-height: 1.8;
        color: #274155;
        white-space: pre-line;
    }

    .relation-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .relation-card {
        border: 1px solid var(--line);
        border-radius: 18px;
        padding: 14px;
        background: rgba(255,255,255,.86);
    }

    .relation-card h4 {
        margin: 0 0 8px;
        font-size: 15px;
    }

    .relation-card ul {
        margin: 0;
        padding-left: 18px;
        color: var(--muted);
    }

    .timeline-tool-grid {
        display: grid;
        grid-template-columns: 280px minmax(0, 1fr);
        gap: 14px;
    }

    .timeline-filter-card,
    .timeline-form-card {
        padding: 18px;
    }

    .timeline-form-card {
        border: 1px solid rgba(31,154,214,.16);
    }

    .timeline-form-card h3,
    .timeline-filter-card h3 {
        margin: 0 0 8px;
        font-family: "Sora", sans-serif;
        font-size: 18px;
    }

    .timeline-form-card p,
    .timeline-filter-card p {
        margin: 0 0 14px;
        color: var(--muted);
        font-size: 14px;
    }

    .timeline-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .timeline-field.full {
        grid-column: 1 / -1;
    }

    .timeline-field label {
        display: block;
        margin-bottom: 8px;
        font-size: 12px;
        font-weight: 800;
        color: #51697c;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .timeline-field input,
    .timeline-field select,
    .timeline-field textarea {
        width: 100%;
        min-height: 46px;
        border-radius: 14px;
        border: 1px solid var(--line);
        background: rgba(255,255,255,.95);
        padding: 12px 14px;
        font: inherit;
        color: var(--text);
        outline: none;
    }

    .timeline-field textarea {
        min-height: 120px;
        resize: vertical;
    }

    .timeline-field input:focus,
    .timeline-field select:focus,
    .timeline-field textarea:focus {
        border-color: rgba(31,154,214,.55);
        box-shadow: 0 0 0 4px rgba(31,154,214,.10);
    }

    .timeline-hint {
        margin-top: 8px;
        font-size: 12px;
        color: var(--muted);
    }

    .timeline-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 14px;
    }

    .timeline-list {
        position: relative;
        margin-top: 18px;
        padding-left: 30px;
    }

    .timeline-list::before {
        content: '';
        position: absolute;
        left: 11px;
        top: 4px;
        bottom: 4px;
        width: 2px;
        background: linear-gradient(180deg, rgba(31,154,214,.55), rgba(23,182,127,.40));
    }

    .timeline-item {
        position: relative;
        padding-bottom: 18px;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: -24px;
        top: 22px;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: linear-gradient(135deg, #1f9ad6, #17b67f);
        box-shadow: 0 0 0 4px rgba(31,154,214,.12);
    }

    .timeline-entry {
        padding: 18px;
    }

    .timeline-entry-head {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        align-items: flex-start;
        margin-bottom: 10px;
    }

    .timeline-entry-head h3 {
        margin: 8px 0 0;
        font-family: "Sora", sans-serif;
        font-size: 19px;
    }

    .timeline-meta-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
    }

    .timeline-badge {
        background: var(--primary-soft);
        color: #156e9f;
    }

    .timeline-chip {
        background: rgba(23, 182, 127, 0.12);
        color: #137f57;
    }

    .timeline-chip.private {
        background: rgba(214, 69, 69, 0.10);
        color: #a63d3d;
    }

    .timeline-desc {
        margin: 14px 0 0;
        color: #274155;
        line-height: 1.75;
        white-space: pre-line;
    }

    .timeline-meta-grid {
        margin-top: 14px;
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }

    .timeline-meta-box {
        padding: 12px 14px;
        border-radius: 16px;
        background: rgba(255,255,255,.84);
        border: 1px solid var(--line);
    }

    .timeline-footer {
        margin-top: 14px;
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
    }

    .timeline-footer .btn {
        padding: 9px 14px;
        min-height: 40px;
    }

    .timeline-attachment {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--primary);
        font-weight: 800;
        text-decoration: none;
    }

    .timeline-empty,
    .vault-empty {
        padding: 26px;
        border: 1px dashed var(--line);
        border-radius: 20px;
        text-align: center;
        color: var(--muted);
        background: rgba(255,255,255,.78);
    }

    .vault-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .vault-card {
        padding: 18px;
    }

    .vault-card h4 {
        margin: 0 0 6px;
        font-size: 16px;
        font-family: "Sora", sans-serif;
    }

    .vault-card p {
        margin: 0;
        color: var(--muted);
        line-height: 1.7;
    }

    .wiki-alert {
        padding: 14px 16px;
        border-radius: 18px;
        margin-bottom: 18px;
        border: 1px solid transparent;
    }

    .wiki-alert.success {
        background: rgba(23,182,127,.12);
        border-color: rgba(23,182,127,.24);
        color: #106b49;
    }

    .wiki-alert.error {
        background: rgba(214,69,69,.10);
        border-color: rgba(214,69,69,.20);
        color: #9e3333;
    }

    @media (max-width: 1180px) {
        .wiki-layout { grid-template-columns: 1fr; }
        .wiki-sidebar { position: static; }
        .wiki-tool-grid, .timeline-tool-grid { grid-template-columns: 1fr; }
    }

    @media (max-width: 900px) {
        .wiki-hero { flex-direction: column; }
        .wiki-header-actions { min-width: 100%; align-items: stretch; }
        .wiki-search-mini { grid-template-columns: 1fr; }
        .relation-grid,
        .timeline-grid,
        .timeline-meta-grid,
        .vault-grid { grid-template-columns: 1fr; }
    }
</style>
@endsection

@section('content')
@php
    $memberPicture = trim((string) ($member->picture ?? ''));
    $memberPictureUrl = '';
    if ($memberPicture !== '') {
        $memberPictureUrl = preg_match('#^https?://#i', $memberPicture) || str_starts_with($memberPicture, 'data:')
            ? $memberPicture
            : asset(ltrim($memberPicture, '/'));
    }
    $currentRoleId = (int) ($currentRoleId ?? 0);
    $canEditBiography = (bool) ($isOwner || in_array($currentRoleId, [1, 2], true));
    $timelineBaseUrl = url('/member/' . (int) $member->memberid . '/wiki');
    $timelineReturnUrl = (string) ($timelineReturnUrl ?? $timelineBaseUrl);
    $activeTab = (string) ($timelineActiveTab ?? 'biography');
    $timelineFormValues = (array) ($timelineFormValues ?? []);
    $timelineShareMemberIds = array_map('intval', (array) ($timelineFormValues['shared_with'] ?? []));
@endphp

<div class="wiki-shell">
    <section class="wiki-hero">
        <div>
            <span class="eyebrow">{{ __('wiki.title') }}</span>
            <h1>{{ $member->name }}</h1>
            <p>{{ __('wiki.search_hint') }}</p>

            <form class="wiki-search-mini" method="GET" action="/wiki">
                <input type="text" name="q" placeholder="{{ __('wiki.search_placeholder') }}">
                <button class="btn btn-primary" type="submit">{{ __('wiki.search_button') }}</button>
            </form>
        </div>

        <div class="wiki-header-actions">
            <a class="btn btn-soft" href="/wiki">{{ __('wiki.clear_search') }}</a>
            @if ($canEditBiography)
                <a class="btn btn-primary" href="/member/{{ (int) $member->memberid }}/wiki/edit">{{ __('wiki.edit') }} Biography</a>
            @endif
        </div>
    </section>

    @if (session('success'))
        <div class="wiki-alert success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="wiki-alert error">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="wiki-alert error">{{ $errors->first() }}</div>
    @endif

    <div class="wiki-layout">
        <aside class="wiki-sidebar">
            <div class="wiki-portrait">
                @if ($memberPictureUrl !== '')
                    <img src="{{ $memberPictureUrl }}" alt="{{ $member->name }}">
                @else
                    <div class="placeholder">{{ $member->name }}</div>
                @endif
            </div>

            <h2 class="member-name">{{ $member->name }}</h2>
            <div class="member-badge-row">
                <span class="member-badge">{{ __('wiki.member_card_label') }}</span>
                <span class="member-badge">ID #{{ (int) $member->memberid }}</span>
            </div>

            <div class="member-stat-grid">
                <div class="member-stat">
                    <span>{{ __('wiki.last_updated') }}</span>
                    <strong>{{ data_get($article, 'updated_at') ?? data_get($article, 'created_at') ?? '—' }}</strong>
                </div>
                <div class="member-stat">
                    <span>{{ __('wiki.life_timeline') }}</span>
                    <strong>{{ $timelineEntries->total() }} Events</strong>
                </div>
            </div>

            <div class="info-list">
                <div class="info-row">
                    <span>Children</span>
                    <strong>{{ $children->count() }}</strong>
                </div>
                <div class="info-row">
                    <span>Partners</span>
                    <strong>{{ $partners->count() }}</strong>
                </div>
                <div class="info-row">
                    <span>Documents</span>
                    <strong>{{ $canSeeDocs ? $documents->count() : 0 }}</strong>
                </div>
            </div>
        </aside>

        <main class="wiki-main">
            <section class="wiki-section">
                <div class="wiki-section-header">
                    <div>
                        <h2>{{ __('wiki.biography') }}</h2>
                        <p>{{ __('wiki.member_card_copy') }}</p>
                    </div>
                </div>

                <div class="bio-copy">
                    {{ trim((string) data_get($article, 'biography', '')) !== '' ? data_get($article, 'biography') : 'No biography has been written yet.' }}
                </div>
            </section>

            <section class="wiki-section">
                <div class="wiki-section-header">
                    <div>
                        <h2>Family Relations</h2>
                        <p>Children and partners associated with this member.</p>
                    </div>
                </div>

                <div class="relation-grid">
                    <div class="relation-card">
                        <h4>Children</h4>
                        @if ($children->count() > 0)
                            <ul>
                                @foreach ($children as $child)
                                    <li>{{ $child->name }}</li>
                                @endforeach
                            </ul>
                        @else
                            <div style="color: var(--muted);">No children recorded.</div>
                        @endif
                    </div>
                    <div class="relation-card">
                        <h4>Partners</h4>
                        @if ($partners->count() > 0)
                            <ul>
                                @foreach ($partners as $partner)
                                    <li>{{ $partner->name }}</li>
                                @endforeach
                            </ul>
                        @else
                            <div style="color: var(--muted);">No partners recorded.</div>
                        @endif
                    </div>
                </div>
            </section>

            <section class="wiki-section">
                <div class="wiki-section-header">
                    <div>
                        <h2>{{ __('wiki.family_vault') }}</h2>
                        <p>Private documents, notes, and attachments linked to this member.</p>
                    </div>
                </div>

                @if ($canSeeDocs)
                    <div class="vault-grid">
                        @forelse ($documents as $doc)
                            <div class="vault-card">
                                <div class="eyebrow" style="margin-bottom: 12px;">{{ $doc->doc_type }}</div>
                                <h4>{{ basename((string) ($doc->file_path ?? 'Document')) }}</h4>
                                <p>{{ $doc->file_path }}</p>
                                @if (trim((string) ($doc->file_path ?? '')) !== '')
                                    <div style="margin-top: 12px;">
                                        <a class="btn btn-soft" href="{{ $doc->file_path }}" target="_blank" rel="noopener noreferrer">{{ __('wiki.view_document') }}</a>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="vault-empty" style="grid-column: 1 / -1;">
                                {{ __('wiki.private_vault_empty') }}
                            </div>
                        @endforelse
                    </div>
                @else
                    <div class="vault-empty">
                        {{ __('wiki.document_upload_locked') }}
                    </div>
                @endif
            </section>

            <section class="wiki-section" id="life-timeline">
                <div class="wiki-section-header">
                    <div>
                        <h2>{{ __('wiki.life_timeline') }}</h2>
                        <p>Chronological life events for this family member.</p>
                    </div>
                </div>

                <div class="timeline-tool-grid">
                    <div class="timeline-filter-card">
                        <h3>Filters</h3>
                        <p>Filter this member timeline by event category and year.</p>
                        <form method="GET" action="{{ $timelineBaseUrl }}">
                            <input type="hidden" name="tab" value="timeline">
                            <div class="timeline-grid">
                                <div class="timeline-field full">
                                    <label for="timelineCategoryFilter">{{ __('wiki.filter_category') }}</label>
                                    <select id="timelineCategoryFilter" name="timeline_category">
                                        <option value="">All categories</option>
                                        @foreach ($timelineCategories as $categoryKey => $categoryLabel)
                                            <option value="{{ $categoryKey }}" @selected((string) ($timelineFilters['category'] ?? '') === $categoryKey)>{{ $categoryLabel }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="timeline-field full">
                                    <label for="timelineYearFilter">{{ __('wiki.filter_year') }}</label>
                                    <input id="timelineYearFilter" type="number" name="timeline_year" min="1900" max="{{ date('Y') }}" value="{{ (int) ($timelineFilters['year'] ?? 0) ?: '' }}" placeholder="Optional">
                                </div>
                            </div>
                            <div class="timeline-actions">
                                <button class="btn btn-primary" type="submit">{{ __('wiki.apply_filters') }}</button>
                                <a class="btn btn-soft" href="{{ $timelineBaseUrl }}#life-timeline">{{ __('wiki.reset_filters') }}</a>
                            </div>
                        </form>
                    </div>

                    <div class="timeline-form-card">
                        <h3>{{ __('wiki.add_timeline_event') }}</h3>
                        <p>Record milestones such as school, work, marriage, health, moves, achievements, and family moments.</p>

                        @if ($timelineCanManage)
                            <form id="timelineForm" method="POST" action="{{ $timelineFormAction }}" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" id="timelineIdInput" name="timeline_id" value="{{ (int) old('timeline_id', $timelineEditId ?? 0) > 0 ? (int) old('timeline_id', $timelineEditId ?? 0) : '' }}">
                                <input type="hidden" name="family_member_id" value="{{ (int) $timelineTargetMemberId }}">
                                <input type="hidden" name="redirect_to" value="{{ $timelineReturnUrl }}#life-timeline">

                                <div class="timeline-grid">
                                    <div class="timeline-field full">
                                        <label for="timelineTitle">{{ __('wiki.event_title') }}</label>
                                        <input id="timelineTitle" name="title" type="text" maxlength="255" value="{{ old('title', $timelineFormValues['title'] ?? '') }}" placeholder="Enter event title" required>
                                    </div>

                                    <div class="timeline-field full">
                                        <label for="timelineDescription">{{ __('wiki.event_description') }}</label>
                                        <textarea id="timelineDescription" name="description" maxlength="3000" placeholder="Write a short life story note...">{{ old('description', $timelineFormValues['description'] ?? '') }}</textarea>
                                    </div>

                                    <div class="timeline-field">
                                        <label for="timelineEventDate">{{ __('wiki.event_date') }}</label>
                                        <input id="timelineEventDate" name="event_date" type="date" value="{{ old('event_date', $timelineFormValues['event_date'] ?? '') }}">
                                    </div>

                                    <div class="timeline-field">
                                        <label for="timelineEventYear">{{ __('wiki.event_year') }}</label>
                                        <input id="timelineEventYear" name="event_year" type="number" min="1900" max="{{ date('Y') }}" value="{{ old('event_year', $timelineFormValues['event_year'] ?? '') }}" placeholder="Optional">
                                    </div>

                                    <div class="timeline-field">
                                        <label for="timelineCategory">{{ __('wiki.category') }}</label>
                                        <select id="timelineCategory" name="category" required>
                                            <option value="">Select category</option>
                                            @foreach ($timelineCategories as $categoryKey => $categoryLabel)
                                                <option value="{{ $categoryKey }}" @selected((string) old('category', $timelineFormValues['category'] ?? '') === $categoryKey)>{{ $categoryLabel }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="timeline-field">
                                        <label for="timelineLocation">{{ __('wiki.location') }}</label>
                                        <input id="timelineLocation" name="location" type="text" maxlength="255" value="{{ old('location', $timelineFormValues['location'] ?? '') }}" placeholder="Optional location">
                                    </div>

                                    <div class="timeline-field">
                                        <label for="timelineVisibility">{{ __('wiki.visibility') }}</label>
                                        <select id="timelineVisibility" name="visibility">
                                            @foreach ($timelineVisibilityOptions as $visibilityKey => $visibilityLabel)
                                                <option value="{{ $visibilityKey }}" @selected((string) old('visibility', $timelineFormValues['visibility'] ?? 'public_family') === $visibilityKey)>{{ $visibilityLabel }}</option>
                                            @endforeach
                                        </select>
                                        <div class="timeline-hint">{{ __('wiki.public_family_help') }} {{ __('wiki.private_shared_help') }}</div>
                                    </div>

                                    <div class="timeline-field">
                                        <label for="timelineSharedWith">{{ __('wiki.shared_with') }}</label>
                                        <select id="timelineSharedWith" name="shared_with[]" multiple size="5">
                                            @foreach ($timelineShareMembers as $shareMember)
                                                <option value="{{ (int) $shareMember->userid }}" @selected(in_array((int) $shareMember->userid, $timelineShareMemberIds, true))>{{ $shareMember->display_name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="timeline-hint">{{ __('wiki.shared_with_hint') }}</div>
                                    </div>

                                    <div class="timeline-field full">
                                        <label for="timelineAttachment">{{ __('wiki.attachment_photo') }}</label>
                                        <input id="timelineAttachment" name="attachment" type="file" accept="image/jpeg,image/png,image/webp">
                                    </div>
                                </div>

                                <div class="timeline-actions">
                                    <button id="timelineSubmitBtn" class="btn btn-primary" type="submit">{{ (int) old('timeline_id', $timelineEditId ?? 0) > 0 ? __('wiki.update_timeline') : __('wiki.save_timeline') }}</button>
                                    <button id="timelineCancelBtn" class="btn btn-soft" type="button" style="{{ (int) old('timeline_id', $timelineEditId ?? 0) > 0 ? '' : 'display:none;' }}">{{ __('wiki.cancel_edit') }}</button>
                                </div>
                            </form>
                        @else
                            <div class="timeline-empty" style="margin-top: 14px;">
                                Only the owner or administrators can add timeline events for this member.
                            </div>
                        @endif
                    </div>
                </div>

                @if ($timelineEntries->count() > 0)
                    <div class="timeline-list">
                        @foreach ($timelineEntries as $entry)
                            @php
                                $entryData = [
                                    'id' => (int) $entry->id,
                                    'family_member_id' => (int) ($entry->family_member_id ?? 0),
                                    'title' => (string) ($entry->title ?? ''),
                                    'description' => (string) ($entry->description ?? ''),
                                    'event_date' => (string) ($entry->event_date ?? ''),
                                    'event_year' => (string) ($entry->event_year ?? ''),
                                    'category' => (string) ($entry->category ?? ''),
                                    'location' => (string) ($entry->location ?? ''),
                                    'visibility' => (string) ($entry->visibility ?? 'public_family'),
                                    'shared_with_ids' => array_map('intval', (array) ($entry->shared_with_ids ?? [])),
                                ];
                                $lastUpdated = trim((string) ($entry->updated_at ?? $entry->created_at ?? ''));
                            @endphp
                            <article class="timeline-item">
                                <div class="timeline-entry">
                                    <div class="timeline-entry-head">
                                        <div>
                                            <div class="timeline-meta-row">
                                                <span class="timeline-badge">{{ $entry->display_date }}</span>
                                                <span class="timeline-chip">{{ $entry->category_label }}</span>
                                                <span class="timeline-chip {{ ($entry->visibility ?? 'public_family') === 'private_shared' ? 'private' : '' }}">{{ $entry->visibility_label }}</span>
                                            </div>
                                            <h3>{{ $entry->title }}</h3>
                                        </div>
                                        @if (($entry->can_manage ?? false))
                                            <div class="timeline-actions" style="margin-top:0;">
                                                <button type="button" class="btn btn-soft timeline-edit-btn" data-timeline='@json($entryData)'>{{ __('wiki.edit') }}</button>
                                                <form method="POST" action="/timeline/{{ (int) $entry->id }}/delete" onsubmit="return confirm('{{ __('wiki.delete_confirm') }}');">
                                                    @csrf
                                                    <input type="hidden" name="redirect_to" value="{{ $timelineReturnUrl }}#life-timeline">
                                                    <button class="btn btn-soft" type="submit" style="border-color: rgba(214,69,69,.25); color: var(--danger);">{{ __('wiki.delete') }}</button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>

                                    @if (trim((string) ($entry->description ?? '')) !== '')
                                        <div class="timeline-desc">{{ $entry->description }}</div>
                                    @endif

                                    <div class="timeline-meta-grid">
                                        <div class="timeline-meta-box">
                                            <span>{{ __('wiki.timeline_meta_date') }}</span>
                                            <strong>{{ $entry->display_date }}</strong>
                                        </div>
                                        <div class="timeline-meta-box">
                                            <span>{{ __('wiki.timeline_meta_visibility') }}</span>
                                            <strong>{{ $entry->visibility_label }}</strong>
                                        </div>
                                        <div class="timeline-meta-box">
                                            <span>{{ __('wiki.last_updated') }}</span>
                                            <strong>{{ $lastUpdated !== '' ? $lastUpdated : '—' }}</strong>
                                        </div>
                                    </div>

                                    <div class="timeline-footer">
                                        <div style="display:flex;flex-wrap:wrap;gap:8px;">
                                            @if (trim((string) ($entry->location ?? '')) !== '')
                                                <span class="timeline-chip">{{ __('wiki.timeline_meta_location') }}: {{ $entry->location }}</span>
                                            @endif
                                            @if (($entry->visibility ?? 'public_family') === 'private_shared')
                                                <span class="timeline-chip private">
                                                    {{ __('wiki.timeline_meta_shared_with') }}:
                                                    {{ !empty($entry->shared_with_names) ? implode(', ', $entry->shared_with_names) : '—' }}
                                                </span>
                                            @endif
                                        </div>

                                        @if (trim((string) ($entry->attachment_url ?? '')) !== '')
                                            <a class="timeline-attachment" href="{{ $entry->attachment_url }}" target="_blank" rel="noopener noreferrer">
                                                <i data-lucide="image" style="width: 16px; height: 16px;"></i>
                                                {{ __('wiki.view_document') }}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div style="margin-top: 18px;">
                        {{ $timelineEntries->links() }}
                    </div>
                @else
                    <div class="timeline-empty" style="margin-top: 8px;">
                        <div style="font-size: 18px; font-weight: 800; color: var(--text); margin-bottom: 8px;">{{ __('wiki.timeline_empty') }}</div>
                        <div>{{ __('wiki.timeline_empty_hint') }}</div>
                    </div>
                @endif
            </section>

            <section class="wiki-section" id="medical-history">
                <div class="wiki-section-header">
                    <div>
                        <h2>{{ __('wiki.medical_history') }}</h2>
                        <p>Shared family medical records for this member.</p>
                    </div>
                </div>

                <div class="timeline-tool-grid">
                    <div class="timeline-filter-card">
                        <h3>{{ __('wiki.filters') }}</h3>
                        <p>Filter this member medical history by category and date.</p>
                        <form method="GET" action="{{ $timelineBaseUrl }}">
                            <input type="hidden" name="medical_tab" value="medical-history">
                            <div class="timeline-grid">
                                <div class="timeline-field full">
                                    <label for="medicalCategoryFilter">{{ __('wiki.filter_category') }}</label>
                                    <select id="medicalCategoryFilter" name="medical_category">
                                        <option value="">{{ __('wiki.all_categories') }}</option>
                                        @foreach ($medicalCategories as $categoryKey => $categoryLabel)
                                            <option value="{{ $categoryKey }}" @selected((string) ($medicalFilters['category'] ?? '') === $categoryKey)>{{ $categoryLabel }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="timeline-field">
                                    <label for="medicalYearFilter">{{ __('wiki.filter_year') }}</label>
                                    <input id="medicalYearFilter" type="number" name="medical_year" min="1900" max="{{ date('Y') }}" value="{{ (int) ($medicalFilters['year'] ?? 0) ?: '' }}" placeholder="{{ __('wiki.optional') }}">
                                </div>
                                <div class="timeline-field">
                                    <label for="medicalDateFilter">{{ __('wiki.filter_date') }}</label>
                                    <input id="medicalDateFilter" type="date" name="medical_date" value="{{ old('medical_date', $medicalFilters['date'] ?? '') }}">
                                </div>
                            </div>
                            <div class="timeline-actions">
                                <button class="btn btn-primary" type="submit">{{ __('wiki.apply_filters') }}</button>
                                <a class="btn btn-soft" href="{{ $timelineBaseUrl }}#medical-history">{{ __('wiki.reset_filters') }}</a>
                            </div>
                        </form>
                    </div>

                    <div class="timeline-form-card">
                        <h3>{{ __('wiki.add_medical_history') }}</h3>
                        <p>Track allergies, diseases, medications, surgeries, hospital stays, vaccinations, and checkups.</p>

                        @if ($medicalCanManage)
                            <form id="medicalHistoryForm" method="POST" action="{{ $medicalFormAction }}">
                                @csrf
                                <input type="hidden" id="medicalHistoryIdInput" name="medical_history_id" value="{{ (int) old('medical_history_id', $medicalEditId ?? 0) > 0 ? (int) old('medical_history_id', $medicalEditId ?? 0) : '' }}">
                                <input type="hidden" name="family_member_id" value="{{ (int) $medicalTargetMemberId }}">
                                <input type="hidden" name="redirect_to" value="{{ $medicalReturnUrl }}#medical-history">

                                <div class="timeline-grid">
                                    <div class="timeline-field full">
                                        <label for="medicalTitle">{{ __('wiki.medical_title') }}</label>
                                        <input id="medicalTitle" name="title" type="text" maxlength="255" value="{{ old('medical_title', $medicalFormValues['title'] ?? '') }}" placeholder="Enter condition or disease name" required>
                                    </div>

                                    <div class="timeline-field">
                                        <label for="medicalAllergyName">{{ __('wiki.allergy_name') }}</label>
                                        <input id="medicalAllergyName" name="allergy_name" type="text" maxlength="255" value="{{ old('allergy_name', $medicalFormValues['allergy_name'] ?? '') }}" placeholder="Optional allergy name">
                                    </div>

                                    <div class="timeline-field">
                                        <label for="medicalDate">{{ __('wiki.medical_date') }}</label>
                                        <input id="medicalDate" name="medical_date" type="date" value="{{ old('medical_date', $medicalFormValues['medical_date'] ?? '') }}" required>
                                    </div>

                                    <div class="timeline-field">
                                        <label for="medicalCategory">{{ __('wiki.category') }}</label>
                                        <select id="medicalCategory" name="medical_category" required>
                                            <option value="">{{ __('wiki.select_category') }}</option>
                                            @foreach ($medicalCategories as $categoryKey => $categoryLabel)
                                                <option value="{{ $categoryKey }}" @selected((string) old('medical_category', $medicalFormValues['category'] ?? '') === $categoryKey)>{{ $categoryLabel }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="timeline-field full">
                                        <label for="medicalNotes">{{ __('wiki.medical_notes') }}</label>
                                        <textarea id="medicalNotes" name="notes" maxlength="3000" placeholder="Add notes, prescriptions, or doctor remarks...">{{ old('medical_notes', $medicalFormValues['notes'] ?? '') }}</textarea>
                                    </div>
                                </div>

                                <div class="timeline-actions">
                                    <button id="medicalSubmitBtn" class="btn btn-primary" type="submit">{{ (int) old('medical_history_id', $medicalEditId ?? 0) > 0 ? __('wiki.update_medical_history') : __('wiki.save_medical_history') }}</button>
                                    <button id="medicalCancelBtn" class="btn btn-soft" type="button" style="{{ (int) old('medical_history_id', $medicalEditId ?? 0) > 0 ? '' : 'display:none;' }}">{{ __('wiki.cancel_edit') }}</button>
                                </div>
                            </form>
                        @else
                            <div class="timeline-empty" style="margin-top: 14px;">
                                Only the owner or administrators can add medical history for this member.
                            </div>
                        @endif
                    </div>
                </div>

                @if ($medicalEntries->count() > 0)
                    <div class="timeline-list">
                        @foreach ($medicalEntries as $entry)
                            @php
                                $entryData = [
                                    'id' => (int) $entry->id,
                                    'family_member_id' => (int) ($entry->family_member_id ?? 0),
                                    'title' => (string) ($entry->title ?? ''),
                                    'allergy_name' => (string) ($entry->allergy_name ?? ''),
                                    'medical_date' => (string) ($entry->medical_date ?? ''),
                                    'category' => (string) ($entry->category ?? ''),
                                    'notes' => (string) ($entry->notes ?? ''),
                                ];
                                $lastUpdated = trim((string) ($entry->updated_at ?? $entry->created_at ?? ''));
                            @endphp
                            <article class="timeline-item">
                                <div class="timeline-entry">
                                    <div class="timeline-entry-head">
                                        <div>
                                            <div class="timeline-meta-row">
                                                <span class="timeline-badge">{{ $entry->display_date }}</span>
                                                <span class="timeline-chip">{{ $entry->category_label }}</span>
                                                @if (trim((string) ($entry->allergy_name ?? '')) !== '')
                                                    <span class="timeline-chip private">{{ $entry->allergy_name }}</span>
                                                @endif
                                            </div>
                                            <h3>{{ $entry->title }}</h3>
                                        </div>
                                        @if (($entry->can_manage ?? false))
                                            <div class="timeline-actions" style="margin-top:0;">
                                                <button type="button" class="btn btn-soft medical-edit-btn" data-medical='@json($entryData)'>{{ __('wiki.edit') }}</button>
                                                <form method="POST" action="/medical-history/{{ (int) $entry->id }}/delete" onsubmit="return confirm('{{ __('wiki.medical_delete_confirm') }}');">
                                                    @csrf
                                                    <input type="hidden" name="redirect_to" value="{{ $medicalReturnUrl }}#medical-history">
                                                    <button class="btn btn-soft" type="submit" style="border-color: rgba(214,69,69,.25); color: var(--danger);">{{ __('wiki.delete') }}</button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>

                                    @if (trim((string) ($entry->notes ?? '')) !== '')
                                        <div class="timeline-desc">{{ $entry->notes }}</div>
                                    @endif

                                    <div class="timeline-meta-grid">
                                        <div class="timeline-meta-box">
                                            <span>{{ __('wiki.medical_meta_date') }}</span>
                                            <strong>{{ $entry->display_date }}</strong>
                                        </div>
                                        <div class="timeline-meta-box">
                                            <span>{{ __('wiki.medical_meta_last_updated') }}</span>
                                            <strong>{{ $lastUpdated !== '' ? $lastUpdated : '—' }}</strong>
                                        </div>
                                        <div class="timeline-meta-box">
                                            <span>{{ __('wiki.medical_meta_category') }}</span>
                                            <strong>{{ $entry->category_label }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div style="margin-top: 18px;">
                        {{ $medicalEntries->links() }}
                    </div>
                @else
                    <div class="timeline-empty" style="margin-top: 8px;">
                        <div style="font-size: 18px; font-weight: 800; color: var(--text); margin-bottom: 8px;">{{ __('wiki.medical_history_empty') }}</div>
                        <div>{{ __('wiki.medical_history_empty_hint') }}</div>
                    </div>
                @endif
            </section>
        </main>
    </div>
</div>

<script>
    (function () {
        var timelineForm = document.getElementById('timelineForm');
        var timelineIdInput = document.getElementById('timelineIdInput');
        var timelineSubmitBtn = document.getElementById('timelineSubmitBtn');
        var timelineCancelBtn = document.getElementById('timelineCancelBtn');
        var timelineVisibility = document.getElementById('timelineVisibility');
        var timelineSharedWith = document.getElementById('timelineSharedWith');
        var timelineTitle = document.getElementById('timelineTitle');
        var timelineDescription = document.getElementById('timelineDescription');
        var timelineEventDate = document.getElementById('timelineEventDate');
        var timelineEventYear = document.getElementById('timelineEventYear');
        var timelineCategory = document.getElementById('timelineCategory');
        var timelineLocation = document.getElementById('timelineLocation');
        var defaultAction = '{{ $timelineFormAction }}';
        var defaultTitle = '{{ __('wiki.save_timeline') }}';
        var updateTitle = '{{ __('wiki.update_timeline') }}';

        function toggleSharedVisibility() {
            if (!timelineVisibility || !timelineSharedWith) {
                return;
            }
            var isPrivate = timelineVisibility.value === 'private_shared';
            var wrapper = timelineSharedWith.closest('.timeline-field');
            if (wrapper) {
                wrapper.style.display = isPrivate ? '' : 'none';
            }
        }

        function populateSharedWith(ids) {
            if (!timelineSharedWith) {
                return;
            }
            var lookup = {};
            (ids || []).forEach(function (id) {
                lookup[String(id)] = true;
            });
            Array.prototype.slice.call(timelineSharedWith.options).forEach(function (option) {
                option.selected = !!lookup[String(option.value)];
            });
        }

        function resetForm() {
            if (!timelineForm) {
                return;
            }

            timelineForm.action = defaultAction;
            if (timelineIdInput) timelineIdInput.value = '';
            if (timelineTitle) timelineTitle.value = '';
            if (timelineDescription) timelineDescription.value = '';
            if (timelineEventDate) timelineEventDate.value = '';
            if (timelineEventYear) timelineEventYear.value = '';
            if (timelineCategory) timelineCategory.value = '';
            if (timelineLocation) timelineLocation.value = '';
            if (timelineVisibility) timelineVisibility.value = 'public_family';
            if (timelineSharedWith) {
                Array.prototype.slice.call(timelineSharedWith.options).forEach(function (option) {
                    option.selected = false;
                });
            }
            if (timelineSubmitBtn) timelineSubmitBtn.textContent = defaultTitle;
            if (timelineCancelBtn) timelineCancelBtn.style.display = 'none';
            toggleSharedVisibility();
        }

        function fillTimelineForm(entry) {
            if (!timelineForm) {
                return;
            }

            timelineForm.action = '/timeline/' + encodeURIComponent(entry.id) + '/update';
            if (timelineIdInput) timelineIdInput.value = entry.id || '';
            if (timelineTitle) timelineTitle.value = entry.title || '';
            if (timelineDescription) timelineDescription.value = entry.description || '';
            if (timelineEventDate) timelineEventDate.value = entry.event_date || '';
            if (timelineEventYear) timelineEventYear.value = entry.event_year || '';
            if (timelineCategory) timelineCategory.value = entry.category || '';
            if (timelineLocation) timelineLocation.value = entry.location || '';
            if (timelineVisibility) timelineVisibility.value = entry.visibility || 'public_family';
            populateSharedWith(entry.shared_with_ids || []);
            toggleSharedVisibility();
            if (timelineSubmitBtn) timelineSubmitBtn.textContent = updateTitle;
            if (timelineCancelBtn) timelineCancelBtn.style.display = '';
            document.getElementById('life-timeline').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        if (timelineVisibility) {
            timelineVisibility.addEventListener('change', toggleSharedVisibility);
        }

        if (timelineCancelBtn) {
            timelineCancelBtn.addEventListener('click', resetForm);
        }

        document.querySelectorAll('.timeline-edit-btn').forEach(function (button) {
            button.addEventListener('click', function () {
                var raw = button.getAttribute('data-timeline') || '{}';
                try {
                    fillTimelineForm(JSON.parse(raw));
                } catch (error) {
                    console.error(error);
                }
            });
        });

        var medicalHistoryForm = document.getElementById('medicalHistoryForm');
        var medicalHistoryIdInput = document.getElementById('medicalHistoryIdInput');
        var medicalSubmitBtn = document.getElementById('medicalSubmitBtn');
        var medicalCancelBtn = document.getElementById('medicalCancelBtn');
        var medicalTitle = document.getElementById('medicalTitle');
        var medicalAllergyName = document.getElementById('medicalAllergyName');
        var medicalDate = document.getElementById('medicalDate');
        var medicalCategory = document.getElementById('medicalCategory');
        var medicalNotes = document.getElementById('medicalNotes');
        var defaultMedicalAction = '{{ $medicalFormAction }}';
        var defaultMedicalTitle = '{{ __('wiki.save_medical_history') }}';
        var updateMedicalTitle = '{{ __('wiki.update_medical_history') }}';

        function resetMedicalForm() {
            if (!medicalHistoryForm) {
                return;
            }

            medicalHistoryForm.action = defaultMedicalAction;
            if (medicalHistoryIdInput) medicalHistoryIdInput.value = '';
            if (medicalTitle) medicalTitle.value = '';
            if (medicalAllergyName) medicalAllergyName.value = '';
            if (medicalDate) medicalDate.value = '';
            if (medicalCategory) medicalCategory.value = '';
            if (medicalNotes) medicalNotes.value = '';
            if (medicalSubmitBtn) medicalSubmitBtn.textContent = defaultMedicalTitle;
            if (medicalCancelBtn) medicalCancelBtn.style.display = 'none';
        }

        function fillMedicalForm(entry) {
            if (!medicalHistoryForm) {
                return;
            }

            medicalHistoryForm.action = '/medical-history/' + encodeURIComponent(entry.id) + '/update';
            if (medicalHistoryIdInput) medicalHistoryIdInput.value = entry.id || '';
            if (medicalTitle) medicalTitle.value = entry.title || '';
            if (medicalAllergyName) medicalAllergyName.value = entry.allergy_name || '';
            if (medicalDate) medicalDate.value = entry.medical_date || '';
            if (medicalCategory) medicalCategory.value = entry.category || '';
            if (medicalNotes) medicalNotes.value = entry.notes || '';
            if (medicalSubmitBtn) medicalSubmitBtn.textContent = updateMedicalTitle;
            if (medicalCancelBtn) medicalCancelBtn.style.display = '';
            document.getElementById('medical-history').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        if (medicalCancelBtn) {
            medicalCancelBtn.addEventListener('click', resetMedicalForm);
        }

        document.querySelectorAll('.medical-edit-btn').forEach(function (button) {
            button.addEventListener('click', function () {
                var raw = button.getAttribute('data-medical') || '{}';
                try {
                    fillMedicalForm(JSON.parse(raw));
                } catch (error) {
                    console.error(error);
                }
            });
        });

        toggleSharedVisibility();
    })();
</script>
@endsection

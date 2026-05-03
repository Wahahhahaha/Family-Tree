@extends('layouts.app')

@section('title', __('management_validation.title'))

<?php $pageClass = $pageClass ?? 'page-family-tree page-management-validation'; ?>

@section('styles')
<style>
    .validation-shell {
        width: 100%;
        max-width: none;
        margin: 0;
        padding: 12px 15px 40px;
        box-sizing: border-box;
    }

    .validation-head {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 18px;
        margin-bottom: 18px;
        flex-wrap: wrap;
    }

    .validation-title h1 {
        margin: 0;
        font-family: "Sora", sans-serif;
        font-size: clamp(26px, 3vw, 40px);
        color: #17384f;
    }

    .validation-title p {
        margin: 8px 0 0;
        color: #6a8092;
        line-height: 1.6;
        max-width: 760px;
    }

    .validation-metrics {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 18px;
    }

    .validation-metric {
        background: #fff;
        border: 1px solid #d8e5ef;
        border-radius: 20px;
        padding: 16px 18px;
        box-shadow: 0 14px 34px rgba(16, 58, 84, 0.08);
    }

    .validation-metric span {
        display: block;
        color: #6a8092;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .08em;
        margin-bottom: 8px;
    }

    .validation-metric strong {
        font-family: "Sora", sans-serif;
        font-size: 28px;
        color: #17384f;
    }

    .validation-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.3fr) minmax(320px, 0.9fr);
        gap: 18px;
        align-items: start;
    }

    .validation-panel {
        background: #fff;
        border: 1px solid #d8e5ef;
        border-radius: 22px;
        box-shadow: 0 16px 36px rgba(16, 58, 84, 0.08);
        padding: 18px;
        box-sizing: border-box;
    }

    .validation-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
        margin-bottom: 16px;
    }

    .validation-search {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }

    .validation-search input,
    .validation-search select {
        height: 42px;
        border-radius: 12px;
        border: 1px solid #cfe0ea;
        padding: 0 12px;
        font: inherit;
        background: #fff;
        outline: none;
    }

    .validation-search input {
        min-width: 260px;
    }

    .validation-search input:focus,
    .validation-search select:focus {
        border-color: #7db4d8;
        box-shadow: 0 0 0 4px rgba(125, 180, 216, 0.18);
    }

    .validation-table-wrap {
        overflow-x: auto;
    }

    .validation-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 980px;
    }

    .validation-table th,
    .validation-table td {
        padding: 14px 12px;
        text-align: left;
        border-bottom: 1px solid #edf2f7;
        vertical-align: top;
    }

    .validation-table th {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #6a8092;
        background: #f8fbfd;
    }

    .validation-table tr.is-selected td {
        background: rgba(31, 154, 214, 0.06);
    }

    .validation-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        line-height: 1;
        background: #eff6fb;
        color: #18567a;
    }

    .validation-chip.is-pending {
        background: #fff6df;
        color: #946400;
    }

    .validation-chip.is-approved {
        background: #dcf7ee;
        color: #116a4d;
    }

    .validation-chip.is-rejected {
        background: #fde4e4;
        color: #9b2d2d;
    }

    .validation-empty {
        text-align: center;
        padding: 30px 16px;
        color: #6a8092;
        font-weight: 700;
    }

    .validation-detail {
        position: sticky;
        top: 18px;
    }

    .validation-detail-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 14px;
    }

    .validation-detail-head h2 {
        margin: 0;
        font-family: "Sora", sans-serif;
        font-size: 24px;
        color: #17384f;
    }

    .validation-detail-head p {
        margin: 6px 0 0;
        color: #6a8092;
        line-height: 1.6;
    }

    .validation-detail-list {
        display: grid;
        gap: 12px;
        margin-top: 14px;
    }

    .validation-detail-row {
        border: 1px solid #e5edf4;
        border-radius: 16px;
        padding: 12px 14px;
        background: #fbfdff;
    }

    .validation-detail-row span {
        display: block;
        font-size: 12px;
        color: #6a8092;
        text-transform: uppercase;
        letter-spacing: .05em;
        font-weight: 800;
        margin-bottom: 6px;
    }

    .validation-detail-row strong,
    .validation-detail-row p {
        margin: 0;
        color: #17384f;
        line-height: 1.6;
    }

    .validation-note-form {
        display: grid;
        gap: 10px;
        margin-top: 16px;
    }

    .validation-note-form textarea {
        width: 100%;
        min-height: 110px;
        resize: vertical;
        border-radius: 14px;
        border: 1px solid #cfe0ea;
        padding: 12px 14px;
        font: inherit;
        outline: none;
    }

    .validation-note-form textarea:focus {
        border-color: #7db4d8;
        box-shadow: 0 0 0 4px rgba(125, 180, 216, 0.18);
    }

    .validation-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        flex-wrap: wrap;
    }

    .validation-detail-empty {
        text-align: center;
        color: #6a8092;
        padding: 22px 10px;
    }

    .validation-meta {
        color: #6a8092;
        font-size: 13px;
        font-weight: 700;
    }

    @media (max-width: 1080px) {
        .validation-grid {
            grid-template-columns: 1fr;
        }

        .validation-detail {
            position: static;
        }
    }

    @media (max-width: 768px) {
        .validation-shell {
            padding: 12px 14px 32px;
        }

        .validation-metrics {
            grid-template-columns: 1fr;
        }

        .validation-search input {
            min-width: 100%;
        }
    }
</style>
@endsection

@section('content')
<div class="validation-shell">
    <div class="validation-head">
        <div class="validation-title">
            <h1>{{ __('management_validation.heading') }}</h1>
            <p>{{ __('management_validation.description') }}</p>
        </div>
    </div>

    <div class="validation-metrics">
        <div class="validation-metric">
            <span>{{ __('management_validation.summary_pending') }}</span>
            <strong>{{ (int) ($counts['pending'] ?? 0) }}</strong>
        </div>
        <div class="validation-metric">
            <span>{{ __('management_validation.summary_approved') }}</span>
            <strong>{{ (int) ($counts['approved'] ?? 0) }}</strong>
        </div>
        <div class="validation-metric">
            <span>{{ __('management_validation.summary_rejected') }}</span>
            <strong>{{ (int) ($counts['rejected'] ?? 0) }}</strong>
        </div>
    </div>

    @if (session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert-error">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert-error">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="validation-grid">
        <div class="validation-panel">
            <div class="validation-toolbar">
                <form class="validation-search" method="GET" action="/management/validation">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search', $searchKeyword ?? '') }}"
                        placeholder="{{ __('management_validation.search_placeholder') }}"
                    >
                    <select name="status" aria-label="{{ __('management_validation.status') }}">
                        <option value="all" @selected(($statusFilter ?? 'pending') === 'all')>{{ __('management_validation.status_all') }}</option>
                        <option value="pending" @selected(($statusFilter ?? 'pending') === 'pending')>{{ __('management_validation.pending') }}</option>
                        <option value="approved" @selected(($statusFilter ?? '') === 'approved')>{{ __('management_validation.approved') }}</option>
                        <option value="rejected" @selected(($statusFilter ?? '') === 'rejected')>{{ __('management_validation.rejected') }}</option>
                    </select>
                    <button class="btn btn-soft" type="submit">{{ __('management_validation.search') }}</button>
                </form>
                <div class="validation-meta">
                    {{ __('management_activity_log.showing', ['current' => $validations->count(), 'total' => $validations->total()]) }}
                </div>
            </div>

            <div class="validation-table-wrap">
                <table class="validation-table">
                    <thead>
                        <tr>
                            <th>{{ __('management_validation.submitted') }}</th>
                            <th>{{ __('management_validation.requester') }}</th>
                            <th>{{ __('management_validation.action') }}</th>
                            <th>{{ __('management_validation.target_member') }}</th>
                            <th>{{ __('management_validation.status') }}</th>
                            <th>{{ __('management_validation.document') }}</th>
                            <th>{{ __('management_validation.view_details') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($validations as $validation)
                            @php
                                $selectedRow = (int) ($selectedValidation->id ?? 0) === (int) $validation->id;
                            @endphp
                            <tr class="{{ $selectedRow ? 'is-selected' : '' }}">
                                <td>{{ $validation->submitted_at }}</td>
                                <td>{{ $validation->requester_label }}</td>
                                <td><span class="validation-chip">{{ $validation->action_label }}</span></td>
                                <td>{{ $validation->target_label }}</td>
                                <td>
                                    <span class="validation-chip {{ $validation->status_class }}">
                                        {{ $validation->status_label }}
                                    </span>
                                </td>
                                <td>
                                    <a class="btn btn-soft" href="{{ $validation->document_url }}" target="_blank" rel="noopener noreferrer" style="padding:8px 12px;min-height:auto;">
                                        {{ __('management_validation.open_document') }}
                                    </a>
                                </td>
                                <td>
                                    <a class="btn btn-primary" href="/management/validation?selected={{ (int) $validation->id }}&status={{ urlencode($statusFilter ?? 'pending') }}&search={{ urlencode($searchKeyword ?? '') }}" style="padding:8px 12px;min-height:auto;">
                                        {{ __('management_validation.view_details') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="validation-empty">{{ __('management_validation.no_requests') }}</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 18px;">
                {{ $validations->links() }}
            </div>
        </div>

        <aside class="validation-panel validation-detail">
            @if ($selectedValidation)
                <div class="validation-detail-head">
                    <div>
                        <h2>{{ __('management_validation.selected_detail') }}</h2>
                        <p>{{ ($selectedValidation->status ?? 'pending') === 'pending' ? __('management_validation.pending_detail') : 'This request has already been processed.' }}</p>
                    </div>
                    <span class="validation-chip {{ $selectedValidation->status_class }}">{{ $selectedValidation->status_label }}</span>
                </div>

                <div class="validation-detail-list">
                    <div class="validation-detail-row">
                        <span>{{ __('management_validation.requester') }}</span>
                        <strong>{{ $selectedValidation->requester_label }}</strong>
                    </div>
                    <div class="validation-detail-row">
                        <span>{{ __('management_validation.action') }}</span>
                        <strong>{{ $selectedValidation->action_label }}</strong>
                    </div>
                    <div class="validation-detail-row">
                        <span>{{ __('management_validation.target_member') }}</span>
                        <strong>{{ $selectedValidation->target_label }}</strong>
                    </div>
                    <div class="validation-detail-row">
                        <span>{{ __('management_validation.reason') }}</span>
                        <p>{{ $selectedValidation->reason }}</p>
                    </div>
                    <div class="validation-detail-row">
                        <span>{{ __('management_validation.document') }}</span>
                        <a class="btn btn-soft" href="{{ $selectedValidation->document_url }}" target="_blank" rel="noopener noreferrer" style="padding:8px 12px;min-height:auto;display:inline-flex;">
                            {{ __('management_validation.open_document') }}
                        </a>
                    </div>
                    <div class="validation-detail-row">
                        <span>{{ __('management_validation.submitted') }}</span>
                        <strong>{{ $selectedValidation->submitted_at }}</strong>
                    </div>
                    <div class="validation-detail-row">
                        <span>{{ __('management_validation.verified_by') }}</span>
                        <strong>{{ $selectedValidation->verified_label }}</strong>
                    </div>
                    <div class="validation-detail-row">
                        <span>{{ __('management_validation.verified_at') }}</span>
                        <strong>{{ $selectedValidation->verified_at_label !== '-' ? $selectedValidation->verified_at_label : '—' }}</strong>
                    </div>
                    <div class="validation-detail-row">
                        <span>{{ __('management_validation.last_updated') }}</span>
                        <strong>{{ $selectedValidation->last_updated_label }}</strong>
                    </div>
                    @if (trim((string) ($selectedValidation->admin_notes ?? '')) !== '')
                        <div class="validation-detail-row">
                            <span>{{ __('management_validation.admin_notes') }}</span>
                            <p>{{ $selectedValidation->admin_notes }}</p>
                        </div>
                    @endif
                </div>

                @if (($selectedValidation->status ?? 'pending') === 'pending')
                    <form class="validation-note-form" method="POST" action="/management/validation/{{ (int) $selectedValidation->id }}/approve">
                        @csrf
                        <div class="validation-detail-row" style="padding:0;border:none;background:transparent;">
                            <span style="margin-bottom:8px;">{{ __('management_validation.admin_notes') }}</span>
                            <textarea name="admin_notes" placeholder="{{ __('management_validation.admin_note_placeholder') }}">{{ old('admin_notes') }}</textarea>
                            <small style="display:block;margin-top:6px;color:#6a8092;">{{ __('management_validation.reject_note_hint') }}</small>
                        </div>

                        <div class="validation-actions">
                            <button type="submit" formaction="/management/validation/{{ (int) $selectedValidation->id }}/reject" class="btn btn-danger-soft">{{ __('management_validation.reject') }}</button>
                            <button type="submit" class="btn btn-primary">{{ __('management_validation.approve') }}</button>
                        </div>
                    </form>
                @else
                    <div class="validation-detail-empty">
                        This request has already been processed.
                    </div>
                @endif
            @else
                <div class="validation-detail-empty">
                    {{ __('management_validation.no_requests') }}
                </div>
            @endif
        </aside>
    </div>
</div>
@endsection

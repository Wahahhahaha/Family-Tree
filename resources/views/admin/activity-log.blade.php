@extends('layouts.app')

@section('title', __('management_activity_log.title'))

<?php $pageClass = $pageClass ?? 'page-family-tree page-management-activity-log'; ?>

@section('styles')
<style>
    .activity-log-shell {
        width: 100%;
        max-width: none;
        margin: 0;
        padding: 12px 15px 40px;
        box-sizing: border-box;
    }

    .activity-log-head {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 18px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .activity-log-title h1 {
        margin: 0;
        font-family: "Sora", sans-serif;
        font-size: 30px;
        font-weight: 800;
        color: #17384f;
    }

    .activity-log-title p {
        margin: 6px 0 0;
        color: #6a8092;
        line-height: 1.6;
    }

    .activity-log-panel {
        width: 100%;
        background: #fff;
        border: 1px solid #d8e5ef;
        border-radius: 22px;
        box-shadow: 0 16px 36px rgba(16, 58, 84, 0.08);
        padding: 18px;
        box-sizing: border-box;
    }

    .activity-log-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
        margin-bottom: 16px;
    }

    .activity-log-search {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }

    .activity-log-search input {
        min-width: 280px;
        height: 42px;
        border: 1px solid #cfe0ea;
        border-radius: 12px;
        padding: 0 14px;
        outline: none;
        font: inherit;
    }

    .activity-log-search input:focus {
        border-color: #7db4d8;
        box-shadow: 0 0 0 4px rgba(125, 180, 216, 0.18);
    }

    .activity-log-meta {
        color: #6a8092;
        font-size: 13px;
        font-weight: 700;
    }

    .activity-log-table-wrap {
        overflow-x: auto;
    }

    .activity-log-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 980px;
    }

    .activity-log-table th,
    .activity-log-table td {
        padding: 14px 12px;
        text-align: left;
        border-bottom: 1px solid #edf2f7;
        vertical-align: top;
    }

    .activity-log-table th {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #6a8092;
        background: #f8fbfd;
    }

    .activity-log-empty {
        text-align: center;
        padding: 28px 16px;
        color: #6a8092;
        font-weight: 700;
    }

    .activity-log-pagination {
        margin-top: 18px;
    }

    @media (max-width: 768px) {
        .activity-log-shell {
            padding: 12px 14px 32px;
        }

        .activity-log-search input {
            min-width: 100%;
        }
    }
</style>
@endsection

@section('content')
<div class="activity-log-shell">
    <div class="activity-log-head">
        <div class="activity-log-title">
            <h1>{{ __('management_activity_log.heading') }}</h1>
            <p>{{ __('management_activity_log.description') }}</p>
        </div>
    </div>

    <div class="activity-log-panel">
        <div class="activity-log-toolbar">
            <form class="activity-log-search" method="GET" action="/management/activity-log">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="{{ __('management_activity_log.search_placeholder') }}"
                >
                <button class="btn btn-soft" type="submit">{{ __('management_activity_log.search') }}</button>
            </form>
            <div class="activity-log-meta">
                {{ __('management_activity_log.showing', ['current' => $activityLogs->count(), 'total' => $activityLogs->total()]) }}
            </div>
        </div>

        <div class="activity-log-table-wrap">
            <table class="activity-log-table">
                <thead>
                    <tr>
                        <th>{{ __('management_activity_log.time') }}</th>
                        <th>{{ __('management_activity_log.user') }}</th>
                        <th>{{ __('management_activity_log.action') }}</th>
                        <th>{{ __('management_activity_log.ip_address') }}</th>
                        <th>{{ __('management_activity_log.longitude') }}</th>
                        <th>{{ __('management_activity_log.latitude') }}</th>
                        <th>{{ __('management_activity_log.details') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @include('admin.partials.activity-log-table-rows', ['activityLogs' => $activityLogs])
                </tbody>
            </table>
        </div>

        <div class="activity-log-pagination">
            @include('admin.partials.activity-log-pagination', ['activityLogs' => $activityLogs])
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', __('management_recycle_bin.title'))
@section('body-class', 'page-family-tree')

@section('content')
<div class="wrapper">
    <section class="management-card">
        <div class="management-head">
            <div>
                <h2>{{ __('management_recycle_bin.title') }}</h2>
                <p>{{ __('management_recycle_bin.description') }}</p>
            </div>
            <div class="management-tools management-tools-user">
                <div class="management-controls">
                    <span id="userTableCount" class="table-count">{{ __('management_recycle_bin.total_users', ['count' => $deletedUsers->total()]) }}</span>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert-error">{{ session('error') }}</div>
        @endif

        <div class="table-wrap">
            <table id="userDataTable" class="data-table" data-bulk-long-press-ms="550">
                <thead>
                    <tr>
                        <th class="bulk-select-col" aria-label="{{ __('management_recycle_bin.select_deleted_users') }}"></th>
                        <th>{{ __('management_recycle_bin.id') }}</th>
                        <th>{{ __('management_recycle_bin.username') }}</th>
                        <th>{{ __('management_recycle_bin.name') }}</th>
                        <th>{{ __('management_recycle_bin.level') }}</th>
                        <th>{{ __('management_recycle_bin.role') }}</th>
                        <th>{{ __('management_recycle_bin.email') }}</th>
                        <th>{{ __('management_recycle_bin.phone') }}</th>
                        <th>{{ __('management_recycle_bin.deleted_at') }}</th>
                        <th>{{ __('management_recycle_bin.action') }}</th>
                    </tr>
                </thead>
                <tbody id="userTableBody">
                    @include('superadmin.partials.recycle-bin-table-rows', ['users' => $deletedUsers])
                </tbody>
            </table>
        </div>

        <div id="userPagination">
            @include('admin.partials.user-pagination', ['users' => $deletedUsers])
        </div>
    </section>

    <div id="bulkUserActions" class="bulk-user-actions-card hidden" aria-live="polite">
        <label class="bulk-select-all-control" for="bulkSelectAllUsers">
            <input id="bulkSelectAllUsers" type="checkbox">
            <span>{{ __('management_recycle_bin.select_all') }}</span>
        </label>
        <span id="bulkSelectedCount" class="table-count">{{ __('management_recycle_bin.selected_count', ['count' => 0]) }}</span>
        <div class="bulk-user-actions-right">
            <form
                id="bulkDeleteForm"
                method="POST"
                action="/management/users/bulk-force-delete"
                class="bulk-delete-form"
                data-confirm-message="{{ __('management_recycle_bin.delete_permanently_confirm') }}"
            >
                @csrf
                <div id="bulkDeleteHiddenInputs"></div>
                <button id="bulkDeleteBtn" type="submit" class="btn btn-danger-soft" disabled>{{ __('management_recycle_bin.delete_permanently') }}</button>
            </form>
            <button id="cancelBulkDeleteBtn" type="button" class="btn btn-ghost">{{ __('management_recycle_bin.cancel') }}</button>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', __('management_backup_database.title'))
@section('body-class', 'page-family-tree')

@section('content')
<div class="wrapper">
    <section class="management-card backup-db-page">
        <div class="management-head">
            <div>
                <h2>{{ __('management_backup_database.title') }}</h2>
                <p>{{ __('management_backup_database.description') }}</p>
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

        <div class="backup-db-layout">
            <article class="backup-db-panel backup-db-panel-export">
                <div class="backup-db-panel-head">
                    <h3>{{ __('management_backup_database.export_database') }}</h3>
                </div>
                <p class="backup-db-panel-desc">{{ __('management_backup_database.export_description') }}</p>
                <form method="POST" action="/management/backup-database/export" class="backup-db-form-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary">{{ __('management_backup_database.export_sql') }}</button>
                </form>
            </article>

            <article class="backup-db-panel backup-db-panel-import">
                <div class="backup-db-panel-head">
                    <h3>{{ __('management_backup_database.import_database') }}</h3>
                </div>
                <p class="backup-db-panel-desc">{{ __('management_backup_database.import_description') }}</p>
                <form method="POST" action="/management/backup-database/import" enctype="multipart/form-data" class="backup-db-form-stack">
                    @csrf
                    <div class="backup-db-file-field">
                        <label for="sqlBackupFile">{{ __('management_backup_database.sql_file') }}</label>
                        <div class="backup-db-file-inline">
                            <input id="sqlBackupFile" type="file" name="sql_file" accept=".sql" required>
                            <button type="submit" class="btn btn-ghost backup-db-import-btn" onclick="return confirm('{{ __('management_backup_database.import_confirm') }}');">{{ __('management_backup_database.import_sql') }}</button>
                        </div>
                        <small>{{ __('management_backup_database.valid_sql_file') }}</small>
                    </div>
                </form>
            </article>
        </div>
    </section>
</div>
@endsection

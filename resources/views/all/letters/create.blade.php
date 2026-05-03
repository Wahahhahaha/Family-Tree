@extends('layouts.app')

@section('title', __('letters.create_title'))

<?php $pageClass = 'page-family-tree page-letters'; ?>

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.css">
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<style>
    .editor-container { max-width: 800px; margin: 0 auto; background: white; padding: 40px; border-radius: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
    main { padding-top: 20px !important; }
    .form-group { margin-bottom: 25px; }
    .form-group label { display: block; font-weight: 700; margin-bottom: 8px; color: #2d3748; }
    .form-control { width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 12px; font-size: 1rem; }
    .letter-setting-box {
        margin-top: 10px;
        padding: 18px;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        background: #f8fafc;
    }
    .letter-setting-box small { color: #718096; display: block; margin-top: 8px; line-height: 1.5; }
    /* Tom Select Customization */
    .ts-control { border-radius: 12px !important; padding: 12px !important; border: 1px solid #e2e8f0 !important; }
</style>
@endsection

@section('content')
<div class="editor-container">
    <h1 style="font-family: 'Sora', sans-serif; font-weight: 800; color: #1a365d; margin-bottom: 30px;">{{ __('letters.create_title') }}</h1>
    
    <form action="/letters/store" method="POST">
        @csrf
        <div class="form-group">
            <label>{{ __('letters.recipient') }}</label>
            <select id="user-select" name="receiver_id" placeholder="{{ __('letters.search_family_member') }}" required autocomplete="off">
                <option value="">{{ __('letters.search_family_member') }}</option>
                @foreach($users as $u)
                    <option value="{{ $u->userid }}">{{ $u->username }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>{{ __('letters.subject') }}</label>
            <input type="text" name="subject" class="form-control" placeholder="{{ __('letters.subject_placeholder') }}" required>
        </div>

        <div class="form-group">
            <label>{{ __('letters.content') }}</label>
            <textarea id="md-editor" name="content"></textarea>
        </div>

        <div class="form-group">
            <label>{{ __('letters.letter_access') }}</label>
            <div class="letter-setting-box">
                <select id="unlock-type" name="unlock_type" class="form-control">
                    <option value="immediate" data-default-value="">{{ __('letters.open_immediately') }}</option>
                    <option value="age" data-default-value="18">{{ __('letters.open_at_age') }}</option>
                    <option value="years" data-default-value="5">{{ __('letters.open_after_years') }}</option>
                </select>

                <div id="unlock-value-wrap" style="margin-top: 15px; display: none;">
                    <label id="unlock-value-label" style="margin-bottom: 8px;">{{ __('letters.unlock_value') }}</label>
                    <input
                        id="unlock-value"
                        type="number"
                        name="unlock_value"
                        class="form-control"
                        min="1"
                        max="120"
                        value="18"
                    >
                        <small id="unlock-help">
                        {{ __('letters.unlock_help_immediate') }}
                    </small>
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 15px; margin-top: 30px;">
            <button type="submit" class="btn btn-primary" style="flex: 1; padding: 15px;">{{ __('letters.send_letter') }}</button>
            <a href="/letters" style="text-decoration: none; flex: 1;"><button type="button" class="btn btn-ghost" style="width: 100%; padding: 15px;">{{ __('letters.cancel') }}</button></a>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script>
    var simplemde = new SimpleMDE({ element: document.getElementById("md-editor") });
    new TomSelect("#user-select",{
        create: false,
        sortField: { field: "text", direction: "asc"}
    });

    const unlockType = document.getElementById('unlock-type');
    const unlockValueWrap = document.getElementById('unlock-value-wrap');
    const unlockValue = document.getElementById('unlock-value');
    const unlockValueLabel = document.getElementById('unlock-value-label');
    const unlockHelp = document.getElementById('unlock-help');

    function syncUnlockFields() {
        const value = unlockType.value;
        const showValue = value === 'age' || value === 'years';
        unlockValueWrap.style.display = showValue ? 'block' : 'none';

        if (value === 'age') {
            unlockValueLabel.textContent = '{{ __('letters.recipient_age') }}';
            unlockValue.value = unlockType.selectedOptions[0].dataset.defaultValue || 18;
            unlockHelp.textContent = '{{ __('letters.unlock_help_age') }}';
        } else if (value === 'years') {
            unlockValueLabel.textContent = '{{ __('letters.number_of_years') }}';
            unlockValue.value = unlockType.selectedOptions[0].dataset.defaultValue || 5;
            unlockHelp.textContent = '{{ __('letters.unlock_help_years') }}';
        } else {
            unlockValue.value = '';
            unlockHelp.textContent = '{{ __('letters.unlock_help_immediate') }}';
        }
    }

    unlockType.addEventListener('change', syncUnlockFields);
    syncUnlockFields();
</script>
@endsection

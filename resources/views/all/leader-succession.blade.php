@extends('layouts.app')

@section('title', __('leader_succession.title'))

<?php $pageClass = 'page-family-tree page-leader-succession'; ?>

@section('styles')
<style>
    .succession-shell {
        max-width: 1100px;
        margin: 40px auto;
        padding: 0 20px;
    }
    .succession-hero {
        margin-bottom: 24px;
    }
    .succession-grid {
        display: grid;
        grid-template-columns: repeat(12, 1fr);
        gap: 20px;
    }
    .succession-card {
        grid-column: span 12;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
        padding: 24px;
    }
    .succession-card h2 {
        margin: 0 0 12px;
        font-size: 1.2rem;
        font-weight: 800;
        color: #1a365d;
    }
    .succession-note {
        color: #718096;
        line-height: 1.7;
        margin: 0;
    }
    .succession-tag {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 999px;
        background: #ebf8ff;
        color: #2b6cb0;
        font-weight: 700;
        font-size: 0.85rem;
    }
    .succession-tag.is-locked {
        background: #fff5f5;
        color: #c53030;
    }
    .succession-form {
        display: grid;
        gap: 14px;
        margin-top: 18px;
    }
    .succession-form label {
        display: block;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 6px;
    }
    .succession-form input,
    .succession-form select {
        width: 100%;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 12px 14px;
        font-size: 1rem;
        background: #fff;
    }
    .succession-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }
    .succession-actions .btn {
        min-width: 180px;
    }
    .succession-list {
        margin: 0;
        padding-left: 18px;
        color: #4a5568;
        line-height: 1.7;
    }
    .succession-heir-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 16px;
    }
    .succession-heir-box strong {
        color: #1a365d;
    }
    @media (min-width: 900px) {
        .succession-card.card-wide {
            grid-column: span 8;
        }
        .succession-card.card-side {
            grid-column: span 4;
        }
    }
</style>
@endsection

@section('content')
<div class="succession-shell">
    <div class="succession-hero">
        <h1 style="font-family: 'Sora', sans-serif; font-weight: 800; color: #1a365d; margin-bottom: 8px;">
            {{ __('leader_succession.heading') }}
        </h1>
        <p class="succession-note">
            {{ __('leader_succession.description') }}
        </p>
    </div>

    <div class="succession-grid">
        <section class="succession-card card-wide">
            <h2>{{ __('leader_succession.current_succession') }}</h2>
            @if($setting && $currentHeir)
                <div class="succession-heir-box">
                    <div class="succession-tag">
                        <i data-lucide="crown" style="width: 14px; height: 14px;"></i>
                        {{ __('leader_succession.active_successor') }}
                    </div>
                    <p style="margin: 12px 0 0;">
                        <strong>{{ $currentHeir->name }}</strong>
                        <span style="color: #718096;">(member #{{ $currentHeir->memberid }})</span>
                    </p>
                </div>
            @elseif($setting && empty($setting->heir_memberid))
                <p class="succession-note">{{ __('leader_succession.no_successor_yet') }}</p>
            @else
                <p class="succession-note">{{ __('leader_succession.no_setting_yet') }}</p>
            @endif

            <ul class="succession-list" style="margin-top: 16px;">
                <li>{{ __('leader_succession.notes_one') }}</li>
                <li>{{ __('leader_succession.notes_two') }}</li>
                <li>{{ __('leader_succession.notes_three') }}</li>
            </ul>
        </section>

        <section class="succession-card card-side">
            <h2>{{ $hasPin ? __('leader_succession.change_successor') : __('leader_succession.create_successor_pin') }}</h2>

            <form class="succession-form" action="/leader-succession/heir" method="POST">
                @csrf

                <div>
                    <label for="heir_memberid">{{ __('leader_succession.select_successor') }}</label>
                    <select id="heir_memberid" name="heir_memberid" required>
                        <option value="">{{ __('leader_succession.choose_family_member') }}</option>
                        @foreach($candidates as $candidate)
                            <option value="{{ $candidate->memberid }}">
                                {{ $candidate->name }} @if(!empty($candidate->username)) ({{ $candidate->username }}) @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                @if($hasPin)
                    <div>
                        <label for="current_pin">{{ __('leader_succession.current_pin') }}</label>
                        <input id="current_pin" name="current_pin" type="password" inputmode="numeric" maxlength="4" pattern="\d{4}" placeholder="1234" required>
                    </div>
                @else
                    <div>
                        <label for="new_pin">{{ __('leader_succession.create_pin') }}</label>
                        <input id="new_pin" name="new_pin" type="password" inputmode="numeric" maxlength="4" pattern="\d{4}" placeholder="1234" required>
                    </div>
                    <div>
                        <label for="new_pin_confirmation">{{ __('leader_succession.confirm_pin') }}</label>
                        <input id="new_pin_confirmation" name="new_pin_confirmation" type="password" inputmode="numeric" maxlength="4" pattern="\d{4}" placeholder="1234" required>
                    </div>
                @endif

                <div class="succession-actions">
                    <button type="submit" class="btn btn-primary">
                        {{ $hasPin ? __('leader_succession.save_new_successor') : __('leader_succession.save_successor_and_pin') }}
                    </button>
                </div>
            </form>
        </section>

        <section class="succession-card card-side">
            <h2>{{ __('leader_succession.change_pin') }}</h2>
            <form class="succession-form" action="/leader-succession/pin" method="POST">
                @csrf

                <div>
                    <label for="pin_current_pin">{{ __('leader_succession.current_pin') }}</label>
                    <input id="pin_current_pin" name="current_pin" type="password" inputmode="numeric" maxlength="4" pattern="\d{4}" placeholder="1234" required>
                </div>

                <div>
                    <label for="pin_new_pin">{{ __('leader_succession.new_pin') }}</label>
                    <input id="pin_new_pin" name="new_pin" type="password" inputmode="numeric" maxlength="4" pattern="\d{4}" placeholder="1234" required>
                </div>

                <div>
                    <label for="pin_new_pin_confirmation">{{ __('leader_succession.confirm_new_pin') }}</label>
                    <input id="pin_new_pin_confirmation" name="new_pin_confirmation" type="password" inputmode="numeric" maxlength="4" pattern="\d{4}" placeholder="1234" required>
                </div>

                <div class="succession-actions">
                    <button type="submit" class="btn btn-ghost">{{ __('leader_succession.update_pin') }}</button>
                </div>
            </form>
        </section>
    </div>
</div>
@endsection

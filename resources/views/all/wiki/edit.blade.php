<?php $pageClass = 'page-family-tree page-wiki'; ?>
@extends('layouts.app')

@section('title', __('wiki.biography') . ' - ' . ($member->name ?? __('wiki.title')))

@section('styles')
<style>
    body.page-wiki main { width: 100%; }
    .wiki-edit-shell {
        width: min(960px, calc(100% - 30px));
        margin: 0 auto;
        padding: 34px 0 56px;
    }
    .wiki-edit-card {
        background: rgba(255,255,255,.96);
        border: 1px solid rgba(220,231,239,.92);
        border-radius: 26px;
        box-shadow: 0 16px 40px rgba(17, 56, 82, 0.08);
        padding: 24px;
    }
    .wiki-edit-card h1 {
        margin: 0 0 10px;
        font-family: "Sora", sans-serif;
    }
    .wiki-edit-card textarea {
        width: 100%;
        min-height: 420px;
        border-radius: 18px;
        border: 1px solid #dce7ef;
        padding: 16px;
        font: inherit;
        line-height: 1.8;
        outline: none;
    }
    .wiki-edit-card textarea:focus {
        border-color: rgba(31,154,214,.55);
        box-shadow: 0 0 0 4px rgba(31,154,214,.12);
    }
    .wiki-edit-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 16px;
    }
</style>
@endsection

@section('content')
<div class="wiki-edit-shell">
    <div class="wiki-edit-card">
        <span class="eyebrow">Wiki</span>
        <h1>{{ $member->name }}</h1>
        <p style="color:#688196; margin: 0 0 18px;">Edit the biography text for this member.</p>

        <form method="POST" action="/member/{{ (int) $member->memberid }}/wiki">
            @csrf
            <textarea name="biography" placeholder="Write biography...">{{ old('biography', $article->biography ?? '') }}</textarea>
            <div class="wiki-edit-actions">
                <button class="btn btn-primary" type="submit">Save Biography</button>
                <a class="btn btn-soft" href="/member/{{ (int) $member->memberid }}/wiki">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

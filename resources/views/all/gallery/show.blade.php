<?php $pageClass = 'page-family-tree page-gallery page-gallery-show'; ?>
@extends('layouts.app')

@section('title', $photo->title . ' - ' . __('gallery.title'))

@section('styles')
<style>
    body.page-gallery {
        --bg: #f5f8fb;
        --surface: rgba(255, 255, 255, 0.94);
        --surface-strong: #ffffff;
        --text: #102133;
        --muted: #678196;
        --line: #d8e5ef;
        --primary: #1f9ad6;
        --primary-soft: #ddf1fa;
        --accent: #17b67f;
        --accent-soft: #dcf7ee;
        --shadow: 0 16px 40px rgba(17, 56, 82, 0.08);
        --shadow-hover: 0 22px 48px rgba(17, 56, 82, 0.16);
        background:
            radial-gradient(circle at 90% 10%, #e4f8f1 0%, transparent 35%),
            radial-gradient(circle at 0% 0%, #e5f2fb 0%, transparent 40%),
            var(--bg);
        color: var(--text);
        font-family: "Manrope", sans-serif;
    }

    body.page-gallery main {
        width: 100%;
    }

    body.page-gallery .detail-shell {
        width: min(1280px, calc(100% - 30px));
        margin: 0 auto;
        padding: 24px 0 52px;
    }

    body.page-gallery .detail-card {
        background: var(--surface);
        backdrop-filter: blur(18px);
        border: 1px solid rgba(216, 229, 239, 0.95);
        box-shadow: var(--shadow);
        border-radius: 26px;
        overflow: hidden;
    }

    body.page-gallery .detail-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.35fr) minmax(320px, 0.65fr);
    }

    body.page-gallery .photo-stage {
        background: linear-gradient(135deg, rgba(31, 154, 214, 0.16), rgba(23, 182, 127, 0.16));
        min-height: 540px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    body.page-gallery .photo-stage img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        display: block;
    }

    body.page-gallery .detail-body {
        padding: 24px;
        display: grid;
        gap: 16px;
        align-content: start;
    }

    body.page-gallery .badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        background: #f4efe8;
        color: #6f5a48;
    }

    body.page-gallery .badge--public {
        background: var(--accent-soft);
        color: #20574e;
    }

    body.page-gallery .badge--private {
        background: var(--primary-soft);
        color: #156e9f;
    }

    body.page-gallery label {
        font-size: 13px;
        font-weight: 700;
        color: var(--text);
        display: block;
        margin-bottom: 6px;
    }

    body.page-gallery input[type="text"],
    body.page-gallery textarea,
    body.page-gallery select {
        width: 100%;
        box-sizing: border-box;
        border: 1px solid var(--line);
        border-radius: 14px;
        padding: 12px 14px;
        font: inherit;
        background: rgba(255, 255, 255, 0.96);
        color: var(--text);
    }

    body.page-gallery .meta-list {
        display: grid;
        gap: 12px;
        padding: 18px;
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.82);
        border: 1px solid var(--line);
    }

    body.page-gallery .meta-list strong {
        display: block;
        color: var(--text);
        margin-top: 2px;
    }

    body.page-gallery .viewer-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    body.page-gallery .viewer-chip {
        padding: 10px 12px;
        border-radius: 14px;
        background: #fff;
        border: 1px solid var(--line);
        font-size: 13px;
    }

    @media (max-width: 960px) {
        body.page-gallery .detail-grid {
            grid-template-columns: 1fr;
        }

        body.page-gallery .photo-stage {
            min-height: 360px;
        }

        body.page-gallery .viewer-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
<div class="detail-shell">
    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 16px;">{{ session('success') }}</div>
    @endif

    <a href="/gallery" class="btn btn-soft" style="display:inline-flex; align-items:center; gap:8px; margin-bottom: 16px;">
        <i data-lucide="arrow-left"></i> {{ __('gallery.back_to_gallery') }}
    </a>

    <div class="detail-card">
        <div class="detail-grid">
            <div class="photo-stage">
                <img src="{{ $photo->file_url }}" alt="{{ $photo->title }}">
            </div>
            <div class="detail-body">
                <div class="badge-row" style="display:flex; flex-wrap:wrap; gap:8px;">
                    <span class="badge {{ $photo->privacy_status === 'private_shared' ? 'badge--private' : 'badge--public' }}">{{ $photo->privacy_label }}</span>
                    <span class="badge">{{ $photo->album_title }}</span>
                </div>

                <div>
                    <h1 style="font-size: 30px;">{{ $photo->title }}</h1>
                    @if(!empty($photo->caption))
                        <p style="margin-top: 8px; color: var(--muted);">{{ $photo->caption }}</p>
                    @endif
                </div>

                <div class="meta-list">
                    <div>{{ __('gallery.uploader') }}<strong>{{ $photo->display_uploader !== '' ? $photo->display_uploader : __('gallery.unknown') }}</strong></div>
                    <div>{{ __('gallery.album') }}<strong>{{ $photo->album_title }}</strong></div>
                    <div>{{ __('gallery.uploaded') }}<strong>{{ \Illuminate\Support\Carbon::parse($photo->uploaded_at)->format('d M Y, H:i') }}</strong></div>
                    <div>{{ __('gallery.privacy_label') }}<strong>{{ $photo->privacy_label }}</strong></div>
                </div>

                @if($photo->privacy_status === 'private_shared')
                    <div>
                        <h3 style="font-size: 18px; margin-bottom: 10px;">{{ __('gallery.allowed_viewers') }}</h3>
                        <div class="viewer-grid">
                            @forelse($selectedViewers as $viewerId)
                                @php
                                    $viewer = $familyMembers->firstWhere('userid', (int) $viewerId);
                                @endphp
                                <div class="viewer-chip">
                                    {{ $viewer->display_name ?? ('User #' . $viewerId) }}
                                </div>
                            @empty
                                <div class="viewer-chip">{{ __('gallery.no_viewers_configured') }}</div>
                            @endforelse
                        </div>
                    </div>
                @endif

                @if($photo->can_manage)
                    <details open style="margin-top: 4px;">
                        <summary style="cursor: pointer; font-weight: 800;">{{ __('gallery.edit_photo') }}</summary>
                        <form method="POST" action="/gallery/photos/{{ $photo->id }}/update" enctype="multipart/form-data" style="display:grid; gap:14px; margin-top: 14px;">
                            @csrf
                            <div>
                                <label>{{ __('gallery.title_label') }}</label>
                                <input type="text" name="title" value="{{ $photo->title }}" required>
                            </div>
                            <div>
                                <label>{{ __('gallery.caption_label') }}</label>
                                <textarea name="caption">{{ $photo->caption }}</textarea>
                            </div>
                            <div>
                                <label>{{ __('gallery.album') }}</label>
                                <select name="album_id" required>
                                    @foreach($albums as $album)
                                        <option value="{{ $album->id }}" @selected((int) $photo->album_id === (int) $album->id)>{{ $album->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label>{{ __('gallery.replace_photo') }}</label>
                                <input type="file" name="photo_file" accept="image/jpeg,image/png,image/webp" capture="environment">
                            </div>
                            <div>
                                <label>{{ __('gallery.privacy') }}</label>
                                <select name="privacy_status" data-gallery-detail-privacy>
                                    <option value="public_family" @selected($photo->privacy_status === 'public_family')>{{ __('gallery.public_family') }}</option>
                                    <option value="private_shared" @selected($photo->privacy_status === 'private_shared')>{{ __('gallery.private_shared') }}</option>
                                </select>
                            </div>
                            <div data-gallery-detail-viewers>
                                <label>{{ __('gallery.viewers') }}</label>
                                <div class="viewer-grid">
                                    @foreach($familyMembers as $member)
                                        <label class="viewer-chip" style="display:flex; gap:10px; align-items:center;">
                                            <input type="checkbox" name="viewers[]" value="{{ $member->userid }}" @checked(in_array((int) $member->userid, $selectedViewers, true))>
                                            <span>{{ $member->display_name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                                <button class="btn btn-primary" type="submit">{{ __('gallery.save_changes') }}</button>
                            </div>
                        </form>
                        <form method="POST" action="/gallery/photos/{{ $photo->id }}/delete" onsubmit="return confirm('{{ __('gallery.delete_photo_confirm') }}');" style="margin-top: 10px;">
                            @csrf
                            <button class="btn btn-danger" type="submit">{{ __('gallery.delete_photo') }}</button>
                        </form>
                    </details>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const privacySelect = document.querySelector('[data-gallery-detail-privacy]');
        const viewersWrapper = document.querySelector('[data-gallery-detail-viewers]');
        if (!privacySelect || !viewersWrapper) {
            return;
        }

        const refreshVisibility = function () {
            viewersWrapper.style.display = privacySelect.value === 'private_shared' ? 'block' : 'none';
        };

        privacySelect.addEventListener('change', refreshVisibility);
        refreshVisibility();
    });
</script>
@endsection

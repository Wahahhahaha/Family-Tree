<?php $pageClass = 'page-family-tree page-gallery'; ?>
@extends('layouts.app')

@section('title', __('gallery.title'))

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

    body.page-gallery .gallery-shell {
        width: min(1400px, calc(100% - 30px));
        margin: 0 auto;
        padding: 24px 0 52px;
        display: grid;
        grid-template-columns: 360px 1fr;
        gap: 22px;
    }

    body.page-gallery .gallery-panel,
    body.page-gallery .gallery-main,
    body.page-gallery .gallery-hero,
    body.page-gallery .gallery-photo-card,
    body.page-gallery .album-card {
        background: var(--surface);
        backdrop-filter: blur(18px);
        border: 1px solid rgba(216, 229, 239, 0.95);
        box-shadow: var(--shadow);
        border-radius: 24px;
    }

    body.page-gallery .gallery-panel {
        padding: 22px;
        align-self: start;
        position: sticky;
        top: 18px;
    }

    body.page-gallery .gallery-main {
        padding: 22px;
        min-width: 0;
    }

    body.page-gallery .gallery-hero {
        padding: 24px;
        margin-bottom: 18px;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        background:
            linear-gradient(135deg, rgba(31, 154, 214, 0.12), rgba(23, 182, 127, 0.12)),
            var(--surface);
    }

    body.page-gallery .eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 7px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #156e9f;
        background: var(--primary-soft);
    }

    body.page-gallery h1,
    body.page-gallery h2,
    body.page-gallery h3,
    body.page-gallery h4 {
        font-family: "Sora", sans-serif;
        margin: 0;
    }

    body.page-gallery .gallery-hero h1 {
        margin-top: 12px;
        font-size: clamp(30px, 3vw, 46px);
        line-height: 1.04;
    }

    body.page-gallery .gallery-hero p,
    body.page-gallery .section-copy,
    body.page-gallery .album-copy,
    body.page-gallery .photo-caption,
    body.page-gallery .meta {
        color: var(--muted);
    }

    body.page-gallery .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-top: 18px;
    }

    body.page-gallery .stat-card {
        padding: 16px;
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.78);
        border: 1px solid var(--line);
    }

    body.page-gallery .stat-card strong {
        display: block;
        font-size: 24px;
        color: var(--text);
        margin-bottom: 6px;
    }

    body.page-gallery .section-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 14px;
    }

    body.page-gallery .toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 18px;
    }

    body.page-gallery .filter-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        border-radius: 999px;
        border: 1px solid var(--line);
        text-decoration: none;
        color: var(--text);
        font-weight: 700;
        background: rgba(255, 255, 255, 0.8);
    }

    body.page-gallery .filter-link.active {
        background: var(--text);
        color: #fff;
        border-color: transparent;
    }

    body.page-gallery .album-list {
        display: grid;
        gap: 14px;
    }

    body.page-gallery .album-card {
        padding: 16px;
    }

    body.page-gallery .album-card header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 12px;
    }

    body.page-gallery .album-card form {
        display: grid;
        gap: 10px;
        margin-top: 12px;
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

    body.page-gallery .photo-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
    }

    body.page-gallery .photo-card {
        overflow: hidden;
        transition: transform .2s ease, box-shadow .2s ease;
        text-decoration: none;
        color: inherit;
    }

    body.page-gallery .photo-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-hover);
    }

    body.page-gallery .photo-cover {
        aspect-ratio: 4 / 3;
        background: linear-gradient(135deg, rgba(31, 154, 214, 0.16), rgba(23, 182, 127, 0.16));
        overflow: hidden;
    }

    body.page-gallery .photo-cover img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    body.page-gallery .photo-body {
        padding: 16px;
    }

    body.page-gallery .badge-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 10px;
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

    body.page-gallery .photo-title {
        font-size: 18px;
        margin-bottom: 6px;
    }

    body.page-gallery .empty-state {
        padding: 42px 18px;
        text-align: center;
        border: 1px dashed var(--line);
        border-radius: 22px;
        background: rgba(255, 255, 255, 0.62);
        color: var(--muted);
    }

    @media (max-width: 1200px) {
        body.page-gallery .photo-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        body.page-gallery .gallery-shell {
            grid-template-columns: 1fr;
        }

        body.page-gallery .gallery-panel {
            position: static;
        }
    }

    @media (max-width: 720px) {
        body.page-gallery .gallery-shell {
            width: min(100% - 20px, 100%);
            padding-top: 16px;
        }

        body.page-gallery .stats-grid,
        body.page-gallery .photo-grid {
            grid-template-columns: 1fr;
        }

        body.page-gallery .gallery-hero {
            padding: 18px;
            flex-direction: column;
        }
    }
</style>
@endsection

@section('content')
<div class="gallery-shell">
    <aside class="gallery-panel">
        <span class="eyebrow">{{ __('gallery.archive_eyebrow') }}</span>
        <h2 style="margin-top: 10px;">{{ __('gallery.archive_heading') }}</h2>
        <p class="section-copy">{{ __('gallery.archive_description') }}</p>

        @if(session('success'))
            <div class="alert alert-success" style="margin: 16px 0 0;">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger" style="margin: 16px 0 0;">{{ session('error') }}</div>
        @endif

        <div style="margin-top: 20px;">
            <h3 style="font-size: 18px; margin-bottom: 10px;">{{ __('gallery.create_album') }}</h3>
            <form method="POST" action="/gallery/albums">
                @csrf
                <div>
                    <label for="album_title">{{ __('gallery.album_title') }}</label>
                    <input id="album_title" type="text" name="title" placeholder="{{ __('gallery.album_placeholder') }}" required>
                </div>
                <div>
                    <label for="album_description">{{ __('gallery.album_description') }}</label>
                    <textarea id="album_description" name="description" placeholder="{{ __('gallery.album_note_placeholder') }}"></textarea>
                </div>
                <button class="btn btn-primary" type="submit">{{ __('gallery.save_album') }}</button>
            </form>
        </div>

        <div style="margin-top: 22px;">
            <h3 style="font-size: 18px; margin-bottom: 10px;">{{ __('gallery.albums') }}</h3>
            <div class="album-list">
                @forelse($albums as $album)
                    <article class="album-card">
                        <header>
                            <div>
                                <h4 style="font-size: 16px;">{{ $album->title }}</h4>
                                <p class="album-copy" style="margin: 4px 0 0;">{{ __('gallery.photo_count', ['count' => $album->photo_count]) }}</p>
                            </div>
                            <span class="badge">{{ $album->display_creator !== '' ? $album->display_creator : __('gallery.creator') }}</span>
                        </header>
                        @if(!empty($album->description))
                            <p class="album-copy">{{ $album->description }}</p>
                        @endif

                        <details style="margin-top: 12px;">
                            <summary style="cursor: pointer; font-weight: 800;">{{ __('gallery.edit_album') }}</summary>
                            <form method="POST" action="/gallery/albums/{{ $album->id }}/update" style="display:grid; gap:10px; margin-top: 12px;">
                                @csrf
                                <div>
                                    <label>{{ __('gallery.title_label') }}</label>
                                    <input type="text" name="title" value="{{ $album->title }}" required>
                                </div>
                                <div>
                                    <label>{{ __('gallery.album_description') }}</label>
                                    <textarea name="description">{{ $album->description }}</textarea>
                                </div>
                                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                                    <button class="btn btn-soft" type="submit">{{ __('gallery.update_album') }}</button>
                                </div>
                            </form>
                            @if($album->is_editable)
                                <form method="POST" action="/gallery/albums/{{ $album->id }}/delete" onsubmit="return confirm('{{ __('gallery.delete_album_confirm') }}');" style="margin-top: 10px;">
                                    @csrf
                                    <button class="btn btn-danger" type="submit">{{ __('gallery.delete') }}</button>
                                </form>
                            @endif
                        </details>
                    </article>
                @empty
                    <div class="empty-state">{{ __('gallery.no_albums_yet') }}</div>
                @endforelse
            </div>
        </div>
    </aside>

    <section class="gallery-main">
        <div class="gallery-hero">
            <div>
                <span class="eyebrow">{{ __('gallery.nostalgia_vault') }}</span>
                <h1>{{ __('gallery.hero_title') }}</h1>
                <p>{{ __('gallery.hero_description') }}</p>
            </div>
            <div style="min-width: 280px;">
                <div class="stats-grid">
                    <div class="stat-card"><strong>{{ $stats['total'] }}</strong><span>{{ __('gallery.total_photos') }}</span></div>
                    <div class="stat-card"><strong>{{ $stats['albums'] }}</strong><span>{{ __('gallery.albums') }}</span></div>
                    <div class="stat-card"><strong>{{ $stats['public'] }}</strong><span>{{ __('gallery.public') }}</span></div>
                    <div class="stat-card"><strong>{{ $stats['private'] }}</strong><span>{{ __('gallery.private') }}</span></div>
                </div>
                <div class="stat-card" style="margin-top: 12px; text-align: left;">
                    <strong style="font-size: 18px;">{{ __('gallery.latest_upload') }}</strong>
                    <span>{{ $stats['latest'] }}</span>
                </div>
            </div>
        </div>

        <div class="section-title">
            <div>
                <h2 style="font-size: 22px;">{{ __('gallery.upload_new_photo') }}</h2>
                <p class="section-copy">{{ __('gallery.upload_new_photo_description') }}</p>
            </div>
        </div>

        <form method="POST" action="/gallery/photos" enctype="multipart/form-data" style="display: grid; gap: 16px; padding: 18px; border: 1px solid var(--line); border-radius: 22px; background: rgba(255,255,255,0.75); margin-bottom: 22px;">
            @csrf
            <div class="photo-grid" style="grid-template-columns: repeat(2, minmax(0, 1fr));">
                <div>
                    <label for="photo_title">{{ __('gallery.photo_title') }}</label>
                    <input id="photo_title" type="text" name="title" placeholder="{{ __('gallery.photo_title_placeholder') }}" required>
                </div>
                <div>
                    <label for="photo_album">{{ __('gallery.album') }}</label>
                    <select id="photo_album" name="album_id" required>
                        <option value="">{{ __('gallery.choose_album') }}</option>
                        @foreach($albums as $album)
                            <option value="{{ $album->id }}">{{ $album->title }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                    <label for="photo_caption">{{ __('gallery.caption') }}</label>
                    <textarea id="photo_caption" name="caption" placeholder="{{ __('gallery.optional_caption_placeholder') }}"></textarea>
            </div>

            <div class="photo-grid" style="grid-template-columns: repeat(2, minmax(0, 1fr));">
                <div>
                    <label for="photo_file">{{ __('gallery.upload_or_take_photo') }}</label>
                    <input id="photo_file" type="file" name="photo_file" accept="image/jpeg,image/png,image/webp" capture="environment" required>
                </div>
                <div>
                    <label for="privacy_status">{{ __('gallery.privacy') }}</label>
                    <select id="privacy_status" name="privacy_status" data-gallery-privacy-select>
                        <option value="public_family">{{ __('gallery.public_family') }}</option>
                        <option value="private_shared">{{ __('gallery.private_shared') }}</option>
                    </select>
                </div>
            </div>

            <div data-gallery-viewers-wrapper>
                <label>{{ __('gallery.who_can_view') }}</label>
                <div class="photo-grid" style="grid-template-columns: repeat(3, minmax(0, 1fr));">
                    @foreach($familyMembers as $member)
                        <label style="display:flex; gap:10px; align-items:center; padding:10px 12px; border-radius:14px; border:1px solid var(--line); background:#fff;">
                            <input type="checkbox" name="viewers[]" value="{{ $member->userid }}">
                            <span>{{ $member->display_name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <button class="btn btn-primary" type="submit">{{ __('gallery.upload_photo') }}</button>
        </form>

        <div class="section-title">
            <div>
                <h2 style="font-size: 22px;">{{ __('gallery.gallery_grid') }}</h2>
                <p class="section-copy">{{ __('gallery.gallery_grid_description') }}</p>
            </div>
        </div>

        <div class="toolbar">
            <a class="filter-link {{ $selectedAlbumId === 0 ? 'active' : '' }}" href="/gallery">{{ __('gallery.all_photos') }}</a>
            @foreach($albums as $album)
                <a class="filter-link {{ $selectedAlbumId === (int) $album->id ? 'active' : '' }}" href="/gallery?album_id={{ $album->id }}">{{ $album->title }}</a>
            @endforeach
        </div>

        @if($photos->isEmpty())
            <div class="empty-state">{{ __('gallery.no_photos_yet') }}</div>
        @else
            <div class="photo-grid">
                @foreach($photos as $photo)
                    <a class="gallery-photo-card photo-card" href="/gallery/photos/{{ $photo->id }}">
                        <div class="photo-cover">
                            <img src="{{ $photo->file_url }}" alt="{{ $photo->title }}">
                        </div>
                        <div class="photo-body">
                            <div class="badge-row">
                                <span class="badge {{ $photo->privacy_status === 'private_shared' ? 'badge--private' : 'badge--public' }}">{{ $photo->privacy_label }}</span>
                            <span class="badge">{{ $photo->album_title }}</span>
                            </div>
                            <h3 class="photo-title">{{ $photo->title }}</h3>
                            @if(!empty($photo->caption))
                                <p class="photo-caption">{{ $photo->caption }}</p>
                            @endif
                            <div class="meta" style="display:flex; justify-content:space-between; gap:12px; margin-top: 10px;">
                                <span>{{ $photo->display_uploader !== '' ? $photo->display_uploader : __('gallery.unknown') }}</span>
                                <span>{{ \Illuminate\Support\Carbon::parse($photo->uploaded_at)->format('d M Y') }}</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const privacySelect = document.querySelector('[data-gallery-privacy-select]');
        const viewersWrapper = document.querySelector('[data-gallery-viewers-wrapper]');

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

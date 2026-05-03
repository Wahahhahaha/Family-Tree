@extends('layouts.app')

@section('title', $album->title . ' - Family Gallery')

<?php $pageClass = 'page-family-tree page-gallery'; ?>

@section('styles')
<style>
    .gallery-container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
    .photo-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; margin-top: 30px; }
    .photo-card { 
        position: relative; border-radius: 12px; overflow: hidden; 
        aspect-ratio: 1; cursor: pointer; border: 1px solid #edf2f7;
        transition: transform 0.2s;
    }
    .photo-card:hover { transform: scale(1.02); z-index: 2; box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
    .photo-card img { width: 100%; height: 100%; object-fit: cover; }
    
    .album-header { margin-bottom: 40px; border-bottom: 2px solid #f7fafc; padding-bottom: 30px; }
    main { padding-top: 20px !important; }

    /* Modal Styling */
    .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.85); z-index: 9999; display: none; align-items: center; justify-content: center; backdrop-filter: blur(5px); }
    .modal-content { background: white; border-radius: 24px; width: 95%; max-width: 1000px; max-height: 90vh; overflow-y: auto; display: flex; flex-direction: row; }
    
    .viewer-image-side { flex: 1; background: #000; display: flex; align-items: center; justify-content: center; min-height: 500px; }
    .viewer-image-side img { max-width: 100%; max-height: 80vh; object-fit: contain; }
    .viewer-info-side { width: 350px; padding: 40px; border-left: 1px solid #edf2f7; }

    @media (max-width: 800px) {
        .modal-content { flex-direction: column; }
        .viewer-info-side { width: 100%; border-left: none; border-top: 1px solid #edf2f7; }
    }
</style>
@endsection

@section('content')
<div class="gallery-container">
    <div class="album-header">
        <a href="/gallery" style="text-decoration: none; color: #718096; display: flex; align-items: center; gap: 8px; margin-bottom: 20px;">
            <i data-lucide="arrow-left" style="width: 16px;"></i> Back to Albums
        </a>
        <div style="display: flex; justify-content: space-between; align-items: flex-end;">
            <div>
                <h1 style="font-family: 'Sora', sans-serif; font-weight: 800; color: #1a365d; margin: 0;">{{ $album->title }}</h1>
                <p style="color: #718096; margin-top: 5px;">{{ $album->description ?: 'No description for this album.' }}</p>
            </div>
            <button onclick="document.getElementById('uploadModal').style.display='flex'" class="btn btn-primary">+ Add Photos</button>
        </div>
    </div>

    <div class="photo-grid">
        @forelse($photos as $photo)
            <div class="photo-card" 
                 onclick="openPhotoViewer({
                     id: {{ $photo->id }},
                     url: '{{ $photo->file_url }}',
                     title: '{{ addslashes($photo->title) }}',
                     caption: '{{ addslashes($photo->caption) }}',
                     privacy: '{{ $photo->privacy_status }}'
                 })">
                <img src="{{ $photo->file_url }}" alt="{{ $photo->title }}">
            </div>
        @empty
            <div style="grid-column: 1/-1; text-align: center; padding: 100px 0; color: #a0aec0;">
                <p>This album is empty. Upload some memories!</p>
            </div>
        @endforelse
    </div>
</div>

<!-- Upload Photo Modal -->
<div id="uploadModal" class="modal-overlay" style="background: rgba(0,0,0,0.5);">
    <div class="modal-content" style="max-width: 500px; display: block; padding: 40px;">
        <h2 style="font-weight: 800; color: #1a365d; margin-bottom: 25px;">Upload to {{ $album->title }}</h2>
        <form action="/gallery/photos" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="album_id" value="{{ $album->id }}">
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 700; margin-bottom: 8px;">Photo Title</label>
                <input type="text" name="title" required style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid #e2e8f0;">
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 700; margin-bottom: 8px;">Caption</label>
                <textarea name="caption" style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid #e2e8f0; min-height: 80px;"></textarea>
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 700; margin-bottom: 8px;">Privacy</label>
                <select name="privacy_status" style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid #e2e8f0;">
                    <option value="public_family">Public</option>
                    <option value="private_shared">Private</option>
                </select>
            </div>
            <div style="margin-bottom: 25px;">
                <label style="display: block; font-weight: 700; margin-bottom: 8px;">Select Image</label>
                <input type="file" name="photo_file" required accept="image/*" style="width: 100%;">
            </div>
            <div style="display: flex; gap: 15px;">
                <button type="submit" class="btn btn-primary" style="flex: 1; padding: 12px;">Upload</button>
                <button type="button" onclick="document.getElementById('uploadModal').style.display='none'" class="btn btn-ghost" style="flex: 1; padding: 12px;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Photo Viewer Modal -->
<div id="viewerModal" class="modal-overlay">
    <div class="modal-content">
        <div class="viewer-image-side">
            <img id="viewerImg" src="" alt="">
        </div>
        <div class="viewer-info-side">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <h2 style="font-weight: 800; color: #1a365d; margin: 0;">Edit Photo</h2>
                <button onclick="closePhotoViewer()" style="background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
            </div>

            <form id="editPhotoForm" method="POST">
                @csrf
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 700; margin-bottom: 8px;">Title</label>
                    <input type="text" name="title" id="editTitle" required style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid #e2e8f0;">
                </div>
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 700; margin-bottom: 8px;">Caption</label>
                    <textarea name="caption" id="editCaption" style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid #e2e8f0; min-height: 100px;"></textarea>
                </div>
                <div style="margin-bottom: 30px;">
                    <label style="display: block; font-weight: 700; margin-bottom: 8px;">Privacy</label>
                    <select name="privacy_status" id="editPrivacy" style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid #e2e8f0;">
                        <option value="public_family">Public</option>
                        <option value="private_shared">Private</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; margin-bottom: 15px;">Save Changes</button>
            </form>

            <form id="deletePhotoForm" method="POST" onsubmit="return confirm('Are you sure you want to delete this memory forever?')">
                @csrf
                <button type="submit" class="btn btn-ghost" style="width: 100%; padding: 12px; color: #e53e3e; border: 1px solid #fed7d7;">Delete Photo</button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function openPhotoViewer(data) {
    const modal = document.getElementById('viewerModal');
    const img = document.getElementById('viewerImg');
    const editForm = document.getElementById('editPhotoForm');
    const deleteForm = document.getElementById('deletePhotoForm');

    img.src = data.url;
    document.getElementById('editTitle').value = data.title;
    document.getElementById('editCaption').value = data.caption === 'null' ? '' : data.caption;
    document.getElementById('editPrivacy').value = data.privacy;

    editForm.action = '/gallery/photos/' + data.id + '/update';
    deleteForm.action = '/gallery/photos/' + data.id + '/delete';

    modal.style.display = 'flex';
}

function closePhotoViewer() {
    document.getElementById('viewerModal').style.display = 'none';
}
</script>
@endsection

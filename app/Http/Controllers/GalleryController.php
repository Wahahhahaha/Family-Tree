<?php

namespace App\Http\Controllers;

use App\Models\FamilyGalleryAlbum;
use App\Models\FamilyGalleryPhoto;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GalleryController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->input('tab', 'albums');

        if ($tab === 'photos') {
            $data = FamilyGalleryPhoto::latest()->paginate(15);
        } else {
            $data = FamilyGalleryAlbum::withCount('photos')
                ->with(['photos' => function ($query) {
                    $query->latest()->limit(1);
                }])
                ->latest()
                ->paginate(15);
        }

        if ($request->wantsJson()) {
            return response()->json($data);
        }

        return Inertia::render('Gallery/Index', [
            'galleryData' => $data,
            'allAlbums' => FamilyGalleryAlbum::select('id', 'title')->latest()->get(),
            'currentTab' => $tab,
            'translations' => trans('gallery'),
        ]);
    }

    public function show($id)
    {
        $album = FamilyGalleryAlbum::with(['photos' => function ($query) {
            $query->latest();
        }])->findOrFail($id);

        return Inertia::render('Gallery/Show', [
            'album' => $album,
            'translations' => trans('gallery'),
        ]);
    }

    public function storeAlbum(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:120',
            'description' => 'nullable|string|max:1000',
        ]);

        FamilyGalleryAlbum::create(array_merge($data, [
            'created_by_userid' => auth()->user()->userid,
        ]));

        return back()->with('success', 'Album created successfully.');
    }

    public function uploadPhoto(Request $request, $album)
    {
        $request->validate([
            'photo' => 'required|image|max:2048',
            'title' => 'required|string|max:255',
            'caption' => 'nullable|string',
        ]);

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');

            // Capture metadata BEFORE moving the file
            $fileName = $file->getClientOriginalName();
            $mimeType = $file->getMimeType();
            $fileSize = $file->getSize();
            $storageName = time() . '_' . $fileName;

            // Move the file to the destination
            $file->move(base_path('../uploads/gallery'), $storageName);

            // Create record using captured metadata
            FamilyGalleryPhoto::create([
                'album_id' => $album,
                'uploader_userid' => auth()->user()->userid,
                'title' => $request->title,
                'caption' => $request->caption,
                'file_path' => asset('uploads/gallery/' . $storageName),
                'file_name' => $fileName,
                'mime_type' => $mimeType,
                'file_size' => $fileSize,
                'uploaded_at' => now(),
            ]);
        }

        return back()->with('success', 'Memory preserved successfully.');
    }
}

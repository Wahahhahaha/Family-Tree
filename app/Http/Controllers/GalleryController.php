<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $currentUserId = (int) session('authenticated_user.userid');
        $currentRoleId = (int) session('authenticated_user.roleid');
        $familyId = $this->resolveCurrentFamilyId($currentUserId);

        if ($familyId === null) {
            return redirect('/')->with('error', __('gallery.gallery_scope_unresolved'));
        }

        $familyMembers = $this->getFamilyMembersForSelection($familyId);
        $albums = $this->getAlbumsForFamily($familyId);
        $albumPhotoCounts = $this->getAlbumPhotoCounts($familyId);
        $selectedAlbumId = (int) $request->query('album_id', 0);

        if ($selectedAlbumId > 0 && !$this->albumBelongsToFamily($selectedAlbumId, $familyId)) {
            $selectedAlbumId = 0;
        }

        $photos = $this->getPhotosForFamily($familyId, $selectedAlbumId);
        $totalPhotos = $photos->count();
        $privatePhotos = $photos->where('privacy_status', 'private_shared')->count();
        $publicPhotos = $photos->where('privacy_status', 'public_family')->count();
        $latestUpload = $photos->first()?->uploaded_at ?? null;
        $canManageAllAlbums = in_array($currentRoleId, [1, 2], true);

        $albums = $albums->map(function (object $album) use ($albumPhotoCounts, $currentUserId, $currentRoleId, $canManageAllAlbums) {
            $album->photo_count = (int) ($albumPhotoCounts[$album->id] ?? 0);
            $album->can_manage = $canManageAllAlbums || (int) ($album->created_by_userid ?? 0) === $currentUserId;
            $album->display_creator = trim((string) ($album->creator_username ?? $album->created_by_userid ?? ''));
            $album->is_editable = $album->can_manage;

            return $album;
        });

        $photos = $photos->map(function (object $photo) use ($currentUserId, $currentRoleId) {
            $photo->file_url = $this->resolveGalleryFileUrl((int) ($photo->id ?? 0));
            $photo->privacy_label = $photo->privacy_status === 'private_shared'
                ? __('gallery.private_shared_label')
                : __('gallery.public_family_label');
            $photo->can_manage = $this->canManagePhoto($photo, $currentUserId, $currentRoleId);
            $photo->display_uploader = trim((string) ($photo->uploader_username ?? $photo->uploader_name ?? ''));

            return $photo;
        });

        return view('all.gallery.index', [
            'pageClass' => 'page-gallery',
            'systemSettings' => $this->getSystemSettings(),
            'familyMembers' => $familyMembers,
            'albums' => $albums,
            'photos' => $photos,
            'selectedAlbumId' => $selectedAlbumId,
            'stats' => [
                'total' => $totalPhotos,
                'public' => $publicPhotos,
                'private' => $privatePhotos,
                'albums' => $albums->count(),
                'latest' => $this->formatGalleryTimestamp($latestUpload),
            ],
        ]);
    }

    public function storeAlbum(Request $request): RedirectResponse
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $currentUserId = (int) session('authenticated_user.userid');
        $familyId = $this->resolveCurrentFamilyId($currentUserId);
        if ($familyId === null) {
            return redirect('/gallery')->with('error', __('gallery.gallery_scope_unresolved'));
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
        ], [
            'title.required' => __('gallery.album_title_required'),
            'title.max' => __('gallery.album_title_max'),
            'description.max' => __('gallery.album_description_max'),
        ]);

        DB::table('family_gallery_albums')->insert([
            'family_id' => $familyId,
            'title' => trim((string) $validated['title']),
            'description' => trim((string) ($validated['description'] ?? '')),
            'created_by_userid' => $currentUserId,
            'updated_by_userid' => $currentUserId,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return redirect('/gallery')->with('success', __('gallery.album_created'));
    }

    public function updateAlbum(Request $request, int $albumId): RedirectResponse
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $currentUserId = (int) session('authenticated_user.userid');
        $currentRoleId = (int) session('authenticated_user.roleid');
        $familyId = $this->resolveCurrentFamilyId($currentUserId);
        $album = $this->findAlbum($albumId, $familyId);

        if (!$album || !$this->canManageAlbum($album, $currentUserId, $currentRoleId)) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::table('family_gallery_albums')
            ->where('id', $albumId)
            ->update([
                'title' => trim((string) $validated['title']),
                'description' => trim((string) ($validated['description'] ?? '')),
                'updated_by_userid' => $currentUserId,
                'updated_at' => Carbon::now(),
            ]);

        return redirect('/gallery')->with('success', __('gallery.album_updated'));
    }

    public function destroyAlbum(Request $request, int $albumId): RedirectResponse
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $currentUserId = (int) session('authenticated_user.userid');
        $currentRoleId = (int) session('authenticated_user.roleid');
        $familyId = $this->resolveCurrentFamilyId($currentUserId);
        $album = $this->findAlbum($albumId, $familyId);

        if (!$album || !$this->canManageAlbum($album, $currentUserId, $currentRoleId)) {
            abort(404);
        }

        $photos = DB::table('family_gallery_photos')
            ->where('album_id', $albumId)
            ->get();

        foreach ($photos as $photo) {
            $this->deletePhotoFile((string) ($photo->file_path ?? ''));
        }

        $photoIds = DB::table('family_gallery_photos')
            ->where('album_id', $albumId)
            ->pluck('id')
            ->map(fn ($value) => (int) $value)
            ->filter(fn ($value) => $value > 0)
            ->values()
            ->all();

        DB::transaction(function () use ($albumId, $photoIds) {
            if (!empty($photoIds)) {
                DB::table('family_gallery_photo_viewers')->whereIn('photo_id', $photoIds)->delete();
                DB::table('family_gallery_photos')->whereIn('id', $photoIds)->delete();
            }

            DB::table('family_gallery_albums')->where('id', $albumId)->delete();
        });

        return redirect('/gallery')->with('success', __('gallery.album_deleted'));
    }

    public function storePhoto(Request $request): RedirectResponse
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $currentUserId = (int) session('authenticated_user.userid');
        $currentRoleId = (int) session('authenticated_user.roleid');
        $familyId = $this->resolveCurrentFamilyId($currentUserId);
        if ($familyId === null) {
            return redirect('/gallery')->with('error', __('gallery.gallery_scope_unresolved'));
        }

        $allowedViewerIds = $this->getAllowedViewerIds($familyId);
        $privacyStatus = $this->normalizePrivacyStatus((string) $request->input('privacy_status', 'public_family'));
        $albumId = (int) $request->input('album_id', 0);

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:2000'],
            'album_id' => ['required', 'integer'],
            'privacy_status' => ['required', Rule::in(['public_family', 'private_shared'])],
            'photo_file' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];

        if ($privacyStatus === 'private_shared') {
            $rules['viewers'] = ['required', 'array', 'min:1'];
            $rules['viewers.*'] = ['integer', Rule::in($allowedViewerIds)];
        } else {
            $rules['viewers'] = ['nullable', 'array'];
            $rules['viewers.*'] = ['integer', Rule::in($allowedViewerIds)];
        }

        $validated = $request->validate($rules, [
            'title.required' => __('gallery.photo_title_required'),
            'album_id.required' => __('gallery.choose_album'),
            'album_id.integer' => __('gallery.album_selection_invalid'),
            'privacy_status.required' => __('gallery.privacy'),
            'photo_file.required' => __('gallery.upload_or_take_photo'),
            'photo_file.image' => __('gallery.photo_must_be_image'),
            'photo_file.mimes' => __('gallery.photo_must_be_image'),
            'viewers.required' => __('gallery.private_viewers_label'),
            'viewers.min' => __('gallery.private_viewers_label'),
        ]);

        $album = $this->findAlbum($albumId, $familyId);
        if (!$album) {
            throw ValidationException::withMessages([
                'album_id' => [__('gallery.album_selection_invalid')],
            ]);
        }

        $storageDir = $this->galleryStorageDirectory($familyId);
        File::ensureDirectoryExists($storageDir);

        $file = $request->file('photo_file');
        $extension = strtolower((string) $file->getClientOriginalExtension());
        $fileName = 'gallery_' . Str::uuid()->toString() . '.' . $extension;
        $file->move($storageDir, $fileName);

        $relativePath = 'private/families/family-' . $familyId . '/gallery/' . $fileName;

        $photoId = DB::table('family_gallery_photos')->insertGetId([
            'family_id' => $familyId,
            'album_id' => $albumId,
            'uploader_userid' => $currentUserId,
            'title' => trim((string) $validated['title']),
            'caption' => trim((string) ($validated['caption'] ?? '')),
            'privacy_status' => $privacyStatus,
            'file_path' => $relativePath,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => (string) $file->getClientMimeType(),
            'file_size' => (int) $file->getSize(),
            'uploaded_at' => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $viewerIds = array_values(array_unique(array_map('intval', (array) ($validated['viewers'] ?? []))));
        if ($privacyStatus === 'private_shared' && !empty($viewerIds)) {
            $this->syncPhotoViewers($photoId, $familyId, $viewerIds);
        }

        return redirect('/gallery/photos/' . $photoId)->with('success', __('gallery.photo_uploaded'));
    }

    public function showPhoto(Request $request, int $photoId): View|RedirectResponse
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $currentUserId = (int) session('authenticated_user.userid');
        $currentRoleId = (int) session('authenticated_user.roleid');
        $familyId = $this->resolveCurrentFamilyId($currentUserId);
        if ($familyId === null) {
            abort(404);
        }

        $photo = $this->findPhoto($photoId, $familyId);
        if (!$photo || !$this->canViewPhoto($photo, $currentUserId, $currentRoleId)) {
            abort(404);
        }

        $albums = $this->getAlbumsForFamily($familyId);
        $familyMembers = $this->getFamilyMembersForSelection($familyId);
        $selectedViewers = $photo->privacy_status === 'private_shared'
            ? $this->getPhotoViewerIds($photoId)
            : [];

        $photo->file_url = $this->resolveGalleryFileUrl((int) ($photo->id ?? 0));
            $photo->privacy_label = $photo->privacy_status === 'private_shared'
                ? __('gallery.private_shared_label')
                : __('gallery.public_family_label');
        $photo->can_manage = $this->canManagePhoto($photo, $currentUserId, $currentRoleId);
        $photo->display_uploader = trim((string) ($photo->uploader_username ?? $photo->uploader_name ?? ''));

        return view('all.gallery.show', [
            'pageClass' => 'page-gallery page-gallery-show',
            'systemSettings' => $this->getSystemSettings(),
            'photo' => $photo,
            'albums' => $albums,
            'familyMembers' => $familyMembers,
            'selectedViewers' => $selectedViewers,
            'currentUserId' => $currentUserId,
            'currentRoleId' => $currentRoleId,
        ]);
    }

    public function updatePhoto(Request $request, int $photoId): RedirectResponse
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $currentUserId = (int) session('authenticated_user.userid');
        $currentRoleId = (int) session('authenticated_user.roleid');
        $familyId = $this->resolveCurrentFamilyId($currentUserId);
        $photo = $this->findPhoto($photoId, $familyId);

        if (!$photo || !$this->canManagePhoto($photo, $currentUserId, $currentRoleId)) {
            abort(404);
        }

        $allowedViewerIds = $this->getAllowedViewerIds($familyId ?? 0);
        $privacyStatus = $this->normalizePrivacyStatus((string) $request->input('privacy_status', (string) $photo->privacy_status));

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:2000'],
            'album_id' => ['required', 'integer'],
            'privacy_status' => ['required', Rule::in(['public_family', 'private_shared'])],
            'photo_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];

        if ($privacyStatus === 'private_shared') {
            $rules['viewers'] = ['required', 'array', 'min:1'];
            $rules['viewers.*'] = ['integer', Rule::in($allowedViewerIds)];
        } else {
            $rules['viewers'] = ['nullable', 'array'];
            $rules['viewers.*'] = ['integer', Rule::in($allowedViewerIds)];
        }

        $validated = $request->validate($rules, [
            'title.required' => __('gallery.photo_title_required'),
            'album_id.required' => __('gallery.choose_album'),
            'photo_file.image' => __('gallery.photo_must_be_image'),
            'photo_file.mimes' => __('gallery.photo_must_be_image'),
            'viewers.required' => __('gallery.private_viewers_label'),
            'viewers.min' => __('gallery.private_viewers_label'),
        ]);

        $albumId = (int) $validated['album_id'];
        $album = $this->findAlbum($albumId, (int) $familyId);
        if (!$album) {
            throw ValidationException::withMessages([
            'album_id' => [__('gallery.album_selection_invalid')],
            ]);
        }

        $newFilePath = (string) $photo->file_path;
        if ($request->hasFile('photo_file')) {
            $storageDir = $this->galleryStorageDirectory((int) $familyId);
            File::ensureDirectoryExists($storageDir);

            $file = $request->file('photo_file');
            $extension = strtolower((string) $file->getClientOriginalExtension());
            $fileName = 'gallery_' . Str::uuid()->toString() . '.' . $extension;
            $file->move($storageDir, $fileName);
            $this->deletePhotoFile((string) $photo->file_path);
            $newFilePath = 'private/families/family-' . $familyId . '/gallery/' . $fileName;
        }

        DB::table('family_gallery_photos')
            ->where('id', $photoId)
            ->update([
                'album_id' => $albumId,
                'title' => trim((string) $validated['title']),
                'caption' => trim((string) ($validated['caption'] ?? '')),
                'privacy_status' => $privacyStatus,
                'file_path' => $newFilePath,
                'file_name' => $request->hasFile('photo_file')
                    ? (string) $request->file('photo_file')->getClientOriginalName()
                    : (string) ($photo->file_name ?? ''),
                'mime_type' => $request->hasFile('photo_file')
                    ? (string) $request->file('photo_file')->getClientMimeType()
                    : (string) ($photo->mime_type ?? ''),
                'file_size' => $request->hasFile('photo_file')
                    ? (int) $request->file('photo_file')->getSize()
                    : (int) ($photo->file_size ?? 0),
                'updated_at' => Carbon::now(),
            ]);

        DB::table('family_gallery_photo_viewers')->where('photo_id', $photoId)->delete();

        if ($privacyStatus === 'private_shared') {
            $viewerIds = array_values(array_unique(array_map('intval', (array) ($validated['viewers'] ?? []))));
            if (!empty($viewerIds)) {
                $this->syncPhotoViewers($photoId, (int) $familyId, $viewerIds);
            }
        }

        return redirect('/gallery/photos/' . $photoId)->with('success', __('gallery.photo_updated'));
    }

    public function destroyPhoto(Request $request, int $photoId): RedirectResponse
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $currentUserId = (int) session('authenticated_user.userid');
        $currentRoleId = (int) session('authenticated_user.roleid');
        $familyId = $this->resolveCurrentFamilyId($currentUserId);
        $photo = $this->findPhoto($photoId, $familyId);

        if (!$photo || !$this->canManagePhoto($photo, $currentUserId, $currentRoleId)) {
            abort(404);
        }

        $this->deletePhotoFile((string) $photo->file_path);
        DB::table('family_gallery_photo_viewers')->where('photo_id', $photoId)->delete();
        DB::table('family_gallery_photos')->where('id', $photoId)->delete();

        return redirect('/gallery')->with('success', __('gallery.photo_deleted'));
    }

    public function servePhotoFile(Request $request, int $photoId)
    {
        if (!$request->session()->has('authenticated_user')) {
            abort(404);
        }

        $currentUserId = (int) session('authenticated_user.userid');
        $currentRoleId = (int) session('authenticated_user.roleid');
        $familyId = $this->resolveCurrentFamilyId($currentUserId);
        if ($familyId === null) {
            abort(404);
        }

        $photo = $this->findPhoto($photoId, $familyId);
        if (!$photo || !$this->canViewPhoto($photo, $currentUserId, $currentRoleId)) {
            abort(404);
        }

        $relativePath = trim((string) ($photo->file_path ?? ''));
        if ($relativePath === '') {
            abort(404);
        }

        $fullPath = $this->resolveGalleryFilePath($relativePath);
        if (!is_file($fullPath)) {
            abort(404);
        }

        return response()->file($fullPath);
    }

    private function getAlbumsForFamily(int $familyId)
    {
        return DB::table('family_gallery_albums as a')
            ->leftJoin('user as u', 'u.userid', '=', 'a.created_by_userid')
            ->where('a.family_id', $familyId)
            ->select('a.*', 'u.username as creator_username')
            ->orderByDesc('a.updated_at')
            ->get();
    }

    private function albumBelongsToFamily(int $albumId, int $familyId): bool
    {
        return $this->findAlbum($albumId, $familyId) !== null;
    }

    private function getPhotosForFamily(int $familyId, int $albumId = 0)
    {
        $query = DB::table('family_gallery_photos as p')
            ->join('family_gallery_albums as a', 'a.id', '=', 'p.album_id')
            ->leftJoin('user as u', 'u.userid', '=', 'p.uploader_userid')
            ->where('p.family_id', $familyId)
            ->select(
                'p.*',
                'a.title as album_title',
                'a.description as album_description',
                'u.username as uploader_username'
            )
            ->orderByDesc('p.uploaded_at');

        if ($albumId > 0) {
            $query->where('p.album_id', $albumId);
        }

        return $query->get();
    }

    private function getAlbumPhotoCounts(int $familyId): array
    {
        if (!Schema::hasTable('family_gallery_photos')) {
            return [];
        }

        return DB::table('family_gallery_photos')
            ->where('family_id', $familyId)
            ->select('album_id', DB::raw('COUNT(*) as photo_count'))
            ->groupBy('album_id')
            ->pluck('photo_count', 'album_id')
            ->map(fn ($value) => (int) $value)
            ->all();
    }

    private function getFamilyMembersForSelection(int $familyId)
    {
        $query = DB::table('user as u')->whereNull('u.deleted_at');

        $memberColumns = [];
        if (Schema::hasTable('family_member')) {
            $query->leftJoin('family_member as fm', 'fm.userid', '=', 'u.userid');
            foreach (['family_id', 'familyid'] as $column) {
                if (Schema::hasColumn('family_member', $column)) {
                    $memberColumns[] = 'fm.' . $column;
                }
            }
        }

        if (Schema::hasTable('employer')) {
            $query->leftJoin('employer as e', 'e.userid', '=', 'u.userid');
            foreach (['family_id', 'familyid'] as $column) {
                if (Schema::hasColumn('employer', $column)) {
                    $memberColumns[] = 'e.' . $column;
                }
            }
        }

        if (Schema::hasColumn('user', 'family_id')) {
            $memberColumns[] = 'u.family_id';
        }
        if (Schema::hasColumn('user', 'familyid')) {
            $memberColumns[] = 'u.familyid';
        }

        if (!empty($memberColumns)) {
            $query->where(function ($scope) use ($memberColumns, $familyId) {
                foreach ($memberColumns as $index => $column) {
                    if ($index === 0) {
                        $scope->where($column, $familyId);
                    } else {
                        $scope->orWhere($column, $familyId);
                    }
                }
            });
        }

        $select = ['u.userid', 'u.username'];
        if (Schema::hasTable('family_member') && Schema::hasColumn('family_member', 'name')) {
            $select[] = 'fm.name as family_name';
        }
        if (Schema::hasTable('employer') && Schema::hasColumn('employer', 'name')) {
            $select[] = 'e.name as employer_name';
        }
        if (Schema::hasTable('family_member') && Schema::hasColumn('family_member', 'memberid')) {
            $select[] = 'fm.memberid as memberid';
        }

        return $query
            ->select($select)
            ->distinct()
            ->orderBy('u.username')
            ->get()
            ->map(function (object $user) {
                $displayName = trim((string) ($user->family_name ?? $user->employer_name ?? $user->username ?? ''));
                $user->display_name = $displayName !== '' ? $displayName : (string) ($user->username ?? 'Member');
                return $user;
            });
    }

    private function getAllowedViewerIds(int $familyId): array
    {
        return $this->getFamilyMembersForSelection($familyId)
            ->pluck('userid')
            ->map(fn ($value) => (int) $value)
            ->filter(fn ($value) => $value > 0)
            ->values()
            ->all();
    }

    protected function resolveCurrentFamilyId(int $userId): ?int
    {
        foreach (['user' => 'userid', 'family_member' => 'userid', 'employer' => 'userid'] as $table => $userColumn) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            foreach (['family_id', 'familyid'] as $familyColumn) {
                if (!Schema::hasColumn($table, $familyColumn)) {
                    continue;
                }

                $value = DB::table($table)->where($userColumn, $userId)->value($familyColumn);
                if ($value !== null && (int) $value > 0) {
                    return (int) $value;
                }
            }
        }

        foreach (['families', 'family'] as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            foreach (['id', 'familyid', 'family_id'] as $familyColumn) {
                if (Schema::hasColumn($table, $familyColumn)) {
                    $value = DB::table($table)->orderBy($familyColumn)->value($familyColumn);
                    if ($value !== null && (int) $value > 0) {
                        return (int) $value;
                    }
                }
            }
        }

        return 1;
    }

    private function findAlbum(int $albumId, int $familyId): ?object
    {
        if ($albumId <= 0 || !Schema::hasTable('family_gallery_albums')) {
            return null;
        }

        return DB::table('family_gallery_albums as a')
            ->leftJoin('user as u', 'u.userid', '=', 'a.created_by_userid')
            ->where('a.id', $albumId)
            ->where('a.family_id', $familyId)
            ->select('a.*', 'u.username as creator_username')
            ->first();
    }

    private function findPhoto(int $photoId, int $familyId): ?object
    {
        if ($photoId <= 0 || !Schema::hasTable('family_gallery_photos')) {
            return null;
        }

        return DB::table('family_gallery_photos as p')
            ->join('family_gallery_albums as a', 'a.id', '=', 'p.album_id')
            ->leftJoin('user as u', 'u.userid', '=', 'p.uploader_userid')
            ->where('p.id', $photoId)
            ->where('p.family_id', $familyId)
            ->select(
                'p.*',
                'a.title as album_title',
                'a.description as album_description',
                'u.username as uploader_username'
            )
            ->first();
    }

    private function getPhotoViewerIds(int $photoId): array
    {
        if ($photoId <= 0 || !Schema::hasTable('family_gallery_photo_viewers')) {
            return [];
        }

        return DB::table('family_gallery_photo_viewers')
            ->where('photo_id', $photoId)
            ->pluck('user_id')
            ->map(fn ($value) => (int) $value)
            ->filter(fn ($value) => $value > 0)
            ->values()
            ->all();
    }

    private function syncPhotoViewers(int $photoId, int $familyId, array $viewerIds): void
    {
        if (!Schema::hasTable('family_gallery_photo_viewers')) {
            return;
        }

        $rows = [];
        $viewerIds = array_values(array_unique(array_filter(array_map('intval', $viewerIds), fn ($value) => $value > 0)));

        foreach ($viewerIds as $viewerId) {
            $rows[] = [
                'family_id' => $familyId,
                'photo_id' => $photoId,
                'user_id' => $viewerId,
                'created_at' => Carbon::now(),
            ];
        }

        if (!empty($rows)) {
            DB::table('family_gallery_photo_viewers')->insert($rows);
        }
    }

    private function normalizePrivacyStatus(string $status): string
    {
        $status = strtolower(trim($status));
        if (!in_array($status, ['public_family', 'private_shared'], true)) {
            return 'public_family';
        }

        return $status;
    }

    private function canManageAlbum(object $album, int $currentUserId, int $currentRoleId): bool
    {
        return in_array($currentRoleId, [1, 2], true) || (int) ($album->created_by_userid ?? 0) === $currentUserId;
    }

    private function canManagePhoto(object $photo, int $currentUserId, int $currentRoleId): bool
    {
        return in_array($currentRoleId, [1, 2], true) || (int) ($photo->uploader_userid ?? 0) === $currentUserId;
    }

    private function canViewPhoto(object $photo, int $currentUserId, int $currentRoleId): bool
    {
        if (in_array($currentRoleId, [1, 2], true)) {
            return true;
        }

        if ((int) ($photo->uploader_userid ?? 0) === $currentUserId) {
            return true;
        }

        if (($photo->privacy_status ?? 'public_family') !== 'private_shared') {
            return true;
        }

        return in_array($currentUserId, $this->getPhotoViewerIds((int) ($photo->id ?? 0)), true);
    }

    private function deletePhotoFile(string $relativePath): void
    {
        $relativePath = trim($relativePath);
        if ($relativePath === '' || preg_match('#^(?:https?:|data:|blob:)#i', $relativePath)) {
            return;
        }

        $fullPath = $this->resolveGalleryFilePath($relativePath);
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }

    private function galleryStorageDirectory(int $familyId): string
    {
        return storage_path('app/private/families/family-' . $familyId . '/gallery');
    }

    private function resolveGalleryFileUrl(int $photoId): string
    {
        return '/gallery/photos/' . $photoId . '/file';
    }

    private function resolveGalleryFilePath(string $relativePath): string
    {
        $relativePath = ltrim(trim($relativePath), '/');

        $storagePath = storage_path('app/' . $relativePath);
        if (is_file($storagePath)) {
            return $storagePath;
        }

        $publicPath = public_path($relativePath);
        if (is_file($publicPath)) {
            return $publicPath;
        }

        return $storagePath;
    }

    private function formatGalleryTimestamp(mixed $value): string
    {
        if (empty($value)) {
            return '-';
        }

        try {
            return Carbon::parse((string) $value)->format('d M Y, H:i');
        } catch (\Throwable $e) {
            return '-';
        }
    }
}

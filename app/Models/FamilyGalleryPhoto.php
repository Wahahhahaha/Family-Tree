<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FamilyGalleryPhoto extends Model
{
    use HasFactory;

    protected $table = 'family_gallery_photos';

    protected $fillable = [
        'album_id',
        'uploader_userid',
        'title',
        'caption',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'uploaded_at',
    ];

    public function album(): BelongsTo
    {
        return $this->belongsTo(FamilyGalleryAlbum::class, 'album_id', 'id');
    }
}

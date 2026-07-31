<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FamilyGalleryAlbum extends Model
{
    use HasFactory;

    protected $table = 'family_gallery_albums';

    protected $fillable = [
        'title',
        'description',
        'created_by_userid',
        'updated_by_userid',
    ];

    public function photos(): HasMany
    {
        return $this->hasMany(FamilyGalleryPhoto::class, 'album_id', 'id');
    }
}

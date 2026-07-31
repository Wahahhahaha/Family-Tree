<?php

namespace Database\Factories;

use App\Models\FamilyGalleryAlbum;
use App\Models\FamilyGalleryPhoto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FamilyGalleryPhoto>
 */
class FamilyGalleryPhotoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'album_id' => FamilyGalleryAlbum::factory(),
            'uploader_userid' => 1,
            'title' => $this->faker->sentence(3),
            'caption' => $this->faker->paragraph(),
            'file_path' => $this->faker->imageUrl(),
            'file_name' => 'photo.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 1024,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\FamilyGalleryAlbum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FamilyGalleryAlbum>
 */
class FamilyGalleryAlbumFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'created_by_userid' => 1,
        ];
    }
}

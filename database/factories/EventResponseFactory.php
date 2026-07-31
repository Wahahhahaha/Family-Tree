<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\FamilyMember;
use App\Models\EventResponse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventResponse>
 */
class EventResponseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'member_id' => FamilyMember::factory(),
            'status' => $this->faker->randomElement(['going', 'not_going', 'maybe']),
        ];
    }
}

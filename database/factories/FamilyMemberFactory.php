<?php

namespace Database\Factories;

use App\Models\FamilyMember;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FamilyMember>
 */
class FamilyMemberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'gender' => $this->faker->randomElement(['male', 'female']),
            'birthdate' => $this->faker->date(),
            'birthplace' => $this->faker->city(),
            'address' => $this->faker->address(),
            'bloodtype' => $this->faker->randomElement(['A', 'B', 'AB', 'O']),
            'job' => $this->faker->jobTitle(),
            'education_status' => $this->faker->randomElement(['Elementary', 'High School', 'University']),
            'life_status' => $this->faker->randomElement(['alive', 'deceased']),
            'marital_status' => $this->faker->randomElement(['single', 'married', 'divorced']),
            'userid' => 0, // Mock userid
        ];
    }
}

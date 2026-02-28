<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

class HoleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'number' => fake()->numberBetween(1, 18),
            'par' => fake()->randomElement([3, 4, 5]),
            'distance_feet' => fake()->numberBetween(200, 600),
            'notes' => null,
        ];
    }
}

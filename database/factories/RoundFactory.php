<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoundFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'course_id' => Course::factory(),
            'played_at' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'notes' => null,
        ];
    }
}

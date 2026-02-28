<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true) . ' Disc Golf Course',
            'description' => fake()->sentence(),
            'city' => fake()->city(),
            'state' => fake()->stateAbbr(),
            'country' => 'US',
            'created_by' => User::factory(),
        ];
    }
}

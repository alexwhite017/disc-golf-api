<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DiscFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'brand' => fake()->randomElement(['Innova', 'Discraft', 'Dynamic Discs', 'Latitude 64', 'MVP']),
            'name' => fake()->word(),
            'type' => fake()->randomElement(['driver', 'fairway_driver', 'mid_range', 'putter']),
            'weight_grams' => fake()->randomFloat(1, 150, 175),
            'color' => fake()->colorName(),
            'notes' => null,
            'is_in_bag' => true,
        ];
    }
}

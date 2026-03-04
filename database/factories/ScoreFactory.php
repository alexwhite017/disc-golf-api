<?php

namespace Database\Factories;

use App\Models\Hole;
use App\Models\Round;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScoreFactory extends Factory
{
    public function definition(): array
    {
        return [
            'round_id' => Round::factory(),
            'hole_id' => Hole::factory(),
            'user_id' => User::factory(),
            'strokes' => fake()->numberBetween(1, 8),
        ];
    }
}

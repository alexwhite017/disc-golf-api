<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Round;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

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

    public function configure(): static
    {
        return $this->afterCreating(function (Round $round) {
            DB::table('round_players')->insertOrIgnore([
                'round_id' => $round->id,
                'user_id' => $round->user_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }
}

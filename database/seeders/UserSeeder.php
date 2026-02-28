<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Round;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $courses = Course::with('holes')->get();

        // Demo users
        $alex = User::create([
            'name' => 'Alex Johnson',
            'email' => 'alex@demo.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
        ]);

        $jordan = User::create([
            'name' => 'Jordan Smith',
            'email' => 'jordan@demo.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
        ]);

        // Discs for Alex
        $alexDiscs = [
            ['brand' => 'Innova', 'name' => 'Destroyer', 'type' => 'driver', 'weight_grams' => 175.0, 'color' => 'Red', 'is_in_bag' => true],
            ['brand' => 'Innova', 'name' => 'Boss', 'type' => 'driver', 'weight_grams' => 173.0, 'color' => 'Blue', 'is_in_bag' => true],
            ['brand' => 'Discraft', 'name' => 'Zone', 'type' => 'putter', 'weight_grams' => 174.0, 'color' => 'Yellow', 'is_in_bag' => true],
            ['brand' => 'Dynamic Discs', 'name' => 'Escape', 'type' => 'fairway_driver', 'weight_grams' => 172.0, 'color' => 'Green', 'is_in_bag' => true],
            ['brand' => 'Discraft', 'name' => 'Buzzz', 'type' => 'mid_range', 'weight_grams' => 177.0, 'color' => 'Orange', 'is_in_bag' => true],
            ['brand' => 'Latitude 64', 'name' => 'Pure', 'type' => 'putter', 'weight_grams' => 175.0, 'color' => 'White', 'is_in_bag' => true],
            ['brand' => 'Innova', 'name' => 'Leopard', 'type' => 'fairway_driver', 'weight_grams' => 168.0, 'color' => 'Purple', 'is_in_bag' => false],
        ];

        foreach ($alexDiscs as $disc) {
            $alex->discs()->create($disc);
        }

        // Discs for Jordan
        $jordanDiscs = [
            ['brand' => 'MVP', 'name' => 'Octane', 'type' => 'driver', 'weight_grams' => 174.0, 'color' => 'Black', 'is_in_bag' => true],
            ['brand' => 'Discraft', 'name' => 'Buzzz', 'type' => 'mid_range', 'weight_grams' => 175.0, 'color' => 'Pink', 'is_in_bag' => true],
            ['brand' => 'Dynamic Discs', 'name' => 'Judge', 'type' => 'putter', 'weight_grams' => 176.0, 'color' => 'Blue', 'is_in_bag' => true],
            ['brand' => 'Innova', 'name' => 'Thunderbird', 'type' => 'fairway_driver', 'weight_grams' => 170.0, 'color' => 'Green', 'is_in_bag' => true],
        ];

        foreach ($jordanDiscs as $disc) {
            $jordan->discs()->create($disc);
        }

        // Realistic stroke offsets per hole par (slightly above par = beginner/intermediate)
        $alexOffsets  = [-1, 0, 0, 1, 0, 0, 1, 0, -1, 0, 1, 0, 0, 1, 0, -1, 0, 1]; // avg ~+2 per round
        $jordanOffsets = [0, 1, 0, 1, 1, 0, 1, 0, 0, 1, 1, 0, 1, 0, 1, 0, 1, 1];     // avg ~+9 per round

        $playedDates = collect(range(0, 11))->map(
            fn ($i) => Carbon::now()->subWeeks($i * 2)->format('Y-m-d')
        );

        foreach ($courses as $course) {
            $holes = $course->holes->sortBy('number')->values();

            if ($holes->isEmpty()) {
                continue;
            }

            // Alex plays each course 4 times
            foreach ($playedDates->take(4) as $i => $date) {
                $round = Round::create([
                    'user_id' => $alex->id,
                    'course_id' => $course->id,
                    'played_at' => $date,
                ]);

                foreach ($holes as $j => $hole) {
                    $offset = $alexOffsets[$j % count($alexOffsets)];
                    // Small variation per round
                    $variation = rand(-1, 1);
                    $strokes = max(1, $hole->par + $offset + ($i % 2 === 0 ? $variation : 0));
                    $round->scores()->create(['hole_id' => $hole->id, 'strokes' => $strokes]);
                }
            }

            // Jordan plays each course 2 times
            foreach ($playedDates->take(2) as $i => $date) {
                $round = Round::create([
                    'user_id' => $jordan->id,
                    'course_id' => $course->id,
                    'played_at' => Carbon::parse($date)->addDay()->format('Y-m-d'),
                ]);

                foreach ($holes as $j => $hole) {
                    $offset = $jordanOffsets[$j % count($jordanOffsets)];
                    $strokes = max(1, $hole->par + $offset);
                    $round->scores()->create(['hole_id' => $hole->id, 'strokes' => $strokes]);
                }
            }
        }
    }
}

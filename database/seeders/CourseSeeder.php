<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('is_admin', true)->first();

        $courses = [
            [
                'name' => 'Maple Hill',
                'description' => 'One of the premier disc golf courses in the US, known for its challenging wooded terrain and elevation changes.',
                'city' => 'Leicester',
                'state' => 'MA',
                'country' => 'US',
                'holes' => [
                    ['number' => 1,  'par' => 3, 'distance_feet' => 285],
                    ['number' => 2,  'par' => 3, 'distance_feet' => 310],
                    ['number' => 3,  'par' => 4, 'distance_feet' => 480],
                    ['number' => 4,  'par' => 3, 'distance_feet' => 260],
                    ['number' => 5,  'par' => 3, 'distance_feet' => 340],
                    ['number' => 6,  'par' => 4, 'distance_feet' => 510],
                    ['number' => 7,  'par' => 3, 'distance_feet' => 275],
                    ['number' => 8,  'par' => 3, 'distance_feet' => 390],
                    ['number' => 9,  'par' => 3, 'distance_feet' => 320],
                    ['number' => 10, 'par' => 3, 'distance_feet' => 295],
                    ['number' => 11, 'par' => 4, 'distance_feet' => 500],
                    ['number' => 12, 'par' => 3, 'distance_feet' => 270],
                    ['number' => 13, 'par' => 3, 'distance_feet' => 355],
                    ['number' => 14, 'par' => 4, 'distance_feet' => 490],
                    ['number' => 15, 'par' => 3, 'distance_feet' => 310],
                    ['number' => 16, 'par' => 3, 'distance_feet' => 280],
                    ['number' => 17, 'par' => 4, 'distance_feet' => 525],
                    ['number' => 18, 'par' => 3, 'distance_feet' => 340],
                ],
            ],
            [
                'name' => 'DeLaveaga Disc Golf Course',
                'description' => 'A legendary course set in the redwood hills of Santa Cruz with tight wooded fairways and dramatic elevation.',
                'city' => 'Santa Cruz',
                'state' => 'CA',
                'country' => 'US',
                'holes' => [
                    ['number' => 1,  'par' => 3, 'distance_feet' => 260],
                    ['number' => 2,  'par' => 3, 'distance_feet' => 300],
                    ['number' => 3,  'par' => 3, 'distance_feet' => 330],
                    ['number' => 4,  'par' => 4, 'distance_feet' => 470],
                    ['number' => 5,  'par' => 3, 'distance_feet' => 285],
                    ['number' => 6,  'par' => 3, 'distance_feet' => 315],
                    ['number' => 7,  'par' => 4, 'distance_feet' => 500],
                    ['number' => 8,  'par' => 3, 'distance_feet' => 275],
                    ['number' => 9,  'par' => 3, 'distance_feet' => 360],
                    ['number' => 10, 'par' => 3, 'distance_feet' => 290],
                    ['number' => 11, 'par' => 3, 'distance_feet' => 345],
                    ['number' => 12, 'par' => 4, 'distance_feet' => 515],
                    ['number' => 13, 'par' => 3, 'distance_feet' => 265],
                    ['number' => 14, 'par' => 3, 'distance_feet' => 310],
                    ['number' => 15, 'par' => 3, 'distance_feet' => 380],
                    ['number' => 16, 'par' => 4, 'distance_feet' => 490],
                    ['number' => 17, 'par' => 3, 'distance_feet' => 295],
                    ['number' => 18, 'par' => 4, 'distance_feet' => 540],
                ],
            ],
            [
                'name' => 'Idlewild Disc Golf Course',
                'description' => 'A beautifully wooded course in Burlington, KY that hosts elite PDGA events and features dramatic changes in terrain.',
                'city' => 'Burlington',
                'state' => 'KY',
                'country' => 'US',
                'holes' => [
                    ['number' => 1,  'par' => 3, 'distance_feet' => 305],
                    ['number' => 2,  'par' => 4, 'distance_feet' => 455],
                    ['number' => 3,  'par' => 3, 'distance_feet' => 280],
                    ['number' => 4,  'par' => 3, 'distance_feet' => 320],
                    ['number' => 5,  'par' => 3, 'distance_feet' => 270],
                    ['number' => 6,  'par' => 4, 'distance_feet' => 530],
                    ['number' => 7,  'par' => 3, 'distance_feet' => 295],
                    ['number' => 8,  'par' => 3, 'distance_feet' => 350],
                    ['number' => 9,  'par' => 4, 'distance_feet' => 480],
                    ['number' => 10, 'par' => 3, 'distance_feet' => 275],
                    ['number' => 11, 'par' => 3, 'distance_feet' => 330],
                    ['number' => 12, 'par' => 3, 'distance_feet' => 290],
                    ['number' => 13, 'par' => 4, 'distance_feet' => 510],
                    ['number' => 14, 'par' => 3, 'distance_feet' => 265],
                    ['number' => 15, 'par' => 3, 'distance_feet' => 340],
                    ['number' => 16, 'par' => 4, 'distance_feet' => 495],
                    ['number' => 17, 'par' => 3, 'distance_feet' => 315],
                    ['number' => 18, 'par' => 3, 'distance_feet' => 360],
                ],
            ],
        ];

        foreach ($courses as $courseData) {
            $holes = $courseData['holes'];
            unset($courseData['holes']);

            $course = Course::create([
                ...$courseData,
                'created_by' => $admin->id,
            ]);

            $course->holes()->createMany($holes);
        }
    }
}

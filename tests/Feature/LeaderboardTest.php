<?php

use App\Models\Course;
use App\Models\Hole;
use App\Models\Round;
use App\Models\User;

describe('GET /api/leaderboard', function () {
    it('returns leaderboard for unauthenticated users', function () {
        $this->getJson('/api/leaderboard')->assertOk();
    });

    it('returns users ordered by avg score vs par ascending', function () {
        $course = Course::factory()->create();
        $hole = Hole::factory()->create(['course_id' => $course->id, 'number' => 1, 'par' => 3]);

        $better = User::factory()->create(['name' => 'Better Player']);
        $worse = User::factory()->create(['name' => 'Worse Player']);

        // Better player: 2 strokes on par 3 = -1
        $round1 = Round::factory()->create(['user_id' => $better->id, 'course_id' => $course->id]);
        $round1->scores()->create(['hole_id' => $hole->id, 'strokes' => 2]);

        // Worse player: 5 strokes on par 3 = +2
        $round2 = Round::factory()->create(['user_id' => $worse->id, 'course_id' => $course->id]);
        $round2->scores()->create(['hole_id' => $hole->id, 'strokes' => 5]);

        $response = $this->getJson('/api/leaderboard')->assertOk();

        expect($response->json('data.0.name'))->toBe('Better Player')
            ->and($response->json('data.1.name'))->toBe('Worse Player');
    });

    it('excludes users with no rounds', function () {
        User::factory()->create(); // no rounds

        $this->getJson('/api/leaderboard')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    });
});

describe('GET /api/leaderboard/{course}', function () {
    it('returns leaderboard for a specific course', function () {
        $course = Course::factory()->create();
        $otherCourse = Course::factory()->create();
        $hole = Hole::factory()->create(['course_id' => $course->id, 'number' => 1, 'par' => 3]);

        $user = User::factory()->create();
        $round = Round::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);
        $round->scores()->create(['hole_id' => $hole->id, 'strokes' => 3]);

        // Round on other course — should not appear
        Round::factory()->create(['user_id' => $user->id, 'course_id' => $otherCourse->id]);

        $response = $this->getJson("/api/leaderboard/{$course->id}")
            ->assertOk()
            ->assertJsonPath('course.id', $course->id);

        expect($response->json('data'))->toHaveCount(1);
    });

    it('returns 404 for a missing course', function () {
        $this->getJson('/api/leaderboard/999')->assertNotFound();
    });
});

<?php

use App\Models\Course;
use App\Models\Disc;
use App\Models\Hole;
use App\Models\Round;
use App\Models\User;

describe('GET /api/me/stats', function () {
    it('requires authentication', function () {
        $this->getJson('/api/me/stats')->assertUnauthorized();
    });

    it('returns zeroed stats for a user with no rounds', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/me/stats')->assertOk();

        expect($response->json('rounds_played'))->toBe(0)
            ->and($response->json('holes_played'))->toBe(0)
            ->and($response->json('avg_score_vs_par'))->toBeNull()
            ->and($response->json('best_round'))->toBeNull()
            ->and($response->json('favorite_course'))->toBeNull()
            ->and($response->json('discs_in_bag'))->toBe(0);
    });

    it('returns correct round and score stats', function () {
        $user = User::factory()->create();
        $course = Course::factory()->create();

        $hole1 = Hole::factory()->create(['course_id' => $course->id, 'number' => 1, 'par' => 3]);
        $hole2 = Hole::factory()->create(['course_id' => $course->id, 'number' => 2, 'par' => 4]);

        // Round 1: 4 + 5 = 9 strokes, par 7, vs par = +2
        $round1 = Round::factory()->create(['user_id' => $user->id, 'course_id' => $course->id, 'played_at' => '2024-05-01']);
        $round1->scores()->create(['hole_id' => $hole1->id, 'strokes' => 4]);
        $round1->scores()->create(['hole_id' => $hole2->id, 'strokes' => 5]);

        // Round 2: 3 + 4 = 7 strokes, par 7, vs par = 0
        $round2 = Round::factory()->create(['user_id' => $user->id, 'course_id' => $course->id, 'played_at' => '2024-06-01']);
        $round2->scores()->create(['hole_id' => $hole1->id, 'strokes' => 3]);
        $round2->scores()->create(['hole_id' => $hole2->id, 'strokes' => 4]);

        $response = $this->actingAs($user)->getJson('/api/me/stats')->assertOk();

        expect($response->json('rounds_played'))->toBe(2)
            ->and($response->json('holes_played'))->toBe(4)
            ->and($response->json('avg_score_vs_par'))->toBe(1) // (+2 + 0) / 2 = 1.0, JSON decodes as int
            ->and($response->json('best_round.id'))->toBe($round2->id)
            ->and($response->json('best_round.score_vs_par'))->toBe(0);
    });

    it('returns correct score distribution', function () {
        $user = User::factory()->create();
        $course = Course::factory()->create();

        $hole = Hole::factory()->create(['course_id' => $course->id, 'number' => 1, 'par' => 3]);

        $round = Round::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

        // Eagle (par - 2 = 1 stroke on par 3)
        $hole2 = Hole::factory()->create(['course_id' => $course->id, 'number' => 2, 'par' => 3]);
        $hole3 = Hole::factory()->create(['course_id' => $course->id, 'number' => 3, 'par' => 3]);
        $hole4 = Hole::factory()->create(['course_id' => $course->id, 'number' => 4, 'par' => 3]);
        $hole5 = Hole::factory()->create(['course_id' => $course->id, 'number' => 5, 'par' => 3]);

        $round->scores()->create(['hole_id' => $hole->id, 'strokes' => 1]);   // eagle
        $round->scores()->create(['hole_id' => $hole2->id, 'strokes' => 2]);  // birdie
        $round->scores()->create(['hole_id' => $hole3->id, 'strokes' => 3]);  // par
        $round->scores()->create(['hole_id' => $hole4->id, 'strokes' => 4]);  // bogey
        $round->scores()->create(['hole_id' => $hole5->id, 'strokes' => 5]);  // double bogey

        $response = $this->actingAs($user)->getJson('/api/me/stats')->assertOk();

        expect($response->json('score_distribution.eagles_or_better'))->toBe(1)
            ->and($response->json('score_distribution.birdies'))->toBe(1)
            ->and($response->json('score_distribution.pars'))->toBe(1)
            ->and($response->json('score_distribution.bogeys'))->toBe(1)
            ->and($response->json('score_distribution.double_bogeys_or_worse'))->toBe(1);
    });

    it('returns correct discs_in_bag count', function () {
        $user = User::factory()->create();
        Disc::factory()->count(4)->create(['user_id' => $user->id, 'is_in_bag' => true]);
        Disc::factory()->count(2)->create(['user_id' => $user->id, 'is_in_bag' => false]);

        $response = $this->actingAs($user)->getJson('/api/me/stats')->assertOk();

        expect($response->json('discs_in_bag'))->toBe(4);
    });

    it('returns correct favorite course', function () {
        $user = User::factory()->create();
        $course1 = Course::factory()->create(['name' => 'Maple Hill']);
        $course2 = Course::factory()->create(['name' => 'DeLaveaga']);

        Round::factory()->count(5)->create(['user_id' => $user->id, 'course_id' => $course1->id]);
        Round::factory()->count(2)->create(['user_id' => $user->id, 'course_id' => $course2->id]);

        // Need at least one score for rounds to appear in stats
        $hole1 = Hole::factory()->create(['course_id' => $course1->id, 'number' => 1, 'par' => 3]);
        $hole2 = Hole::factory()->create(['course_id' => $course2->id, 'number' => 1, 'par' => 3]);

        Round::where('course_id', $course1->id)->get()
            ->each(fn ($r) => $r->scores()->create(['hole_id' => $hole1->id, 'strokes' => 3]));
        Round::where('course_id', $course2->id)->get()
            ->each(fn ($r) => $r->scores()->create(['hole_id' => $hole2->id, 'strokes' => 3]));

        $response = $this->actingAs($user)->getJson('/api/me/stats')->assertOk();

        expect($response->json('favorite_course.name'))->toBe('Maple Hill')
            ->and($response->json('favorite_course.rounds_played'))->toBe(5);
    });

    it('only returns stats for the authenticated user', function () {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $course = Course::factory()->create();
        $hole = Hole::factory()->create(['course_id' => $course->id, 'number' => 1, 'par' => 3]);

        // Other user has 10 rounds
        $rounds = Round::factory()->count(10)->create(['user_id' => $other->id, 'course_id' => $course->id]);
        foreach ($rounds as $round) {
            $round->scores()->create(['hole_id' => $hole->id, 'strokes' => 3]);
        }

        $response = $this->actingAs($user)->getJson('/api/me/stats')->assertOk();

        expect($response->json('rounds_played'))->toBe(0);
    });
});

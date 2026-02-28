<?php

use App\Models\Course;
use App\Models\Hole;
use App\Models\Round;
use App\Models\Score;
use App\Models\User;

describe('POST /api/rounds/{round}/scores', function () {
    it('requires authentication', function () {
        $round = Round::factory()->create();
        $hole = Hole::factory()->create(['course_id' => $round->course_id, 'number' => 1]);

        $this->postJson("/api/rounds/{$round->id}/scores", [
            'hole_id' => $hole->id,
            'strokes' => 3,
        ])->assertUnauthorized();
    });

    it('forbids non-owners from adding scores', function () {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $round = Round::factory()->create(['user_id' => $other->id]);
        $hole = Hole::factory()->create(['course_id' => $round->course_id, 'number' => 1]);

        $this->actingAs($user)
            ->postJson("/api/rounds/{$round->id}/scores", [
                'hole_id' => $hole->id,
                'strokes' => 3,
            ])->assertForbidden();
    });

    it('creates a score for the round owner', function () {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $round = Round::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);
        $hole = Hole::factory()->create(['course_id' => $course->id, 'number' => 1]);

        $this->actingAs($user)
            ->postJson("/api/rounds/{$round->id}/scores", [
                'hole_id' => $hole->id,
                'strokes' => 4,
            ])
            ->assertCreated()
            ->assertJsonPath('data.strokes', 4)
            ->assertJsonPath('data.hole_id', $hole->id);

        $this->assertDatabaseHas('scores', [
            'round_id' => $round->id,
            'hole_id' => $hole->id,
            'strokes' => 4,
        ]);
    });

    it('upserts when scoring the same hole twice', function () {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $round = Round::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);
        $hole = Hole::factory()->create(['course_id' => $course->id, 'number' => 1]);

        $this->actingAs($user)
            ->postJson("/api/rounds/{$round->id}/scores", ['hole_id' => $hole->id, 'strokes' => 4]);

        $this->actingAs($user)
            ->postJson("/api/rounds/{$round->id}/scores", ['hole_id' => $hole->id, 'strokes' => 3])
            ->assertCreated()
            ->assertJsonPath('data.strokes', 3);

        $this->assertDatabaseCount('scores', 1);
        $this->assertDatabaseHas('scores', ['round_id' => $round->id, 'hole_id' => $hole->id, 'strokes' => 3]);
    });

    it('validates strokes is required and positive', function () {
        $user = User::factory()->create();
        $round = Round::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->postJson("/api/rounds/{$round->id}/scores", ['hole_id' => 1])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['strokes']);
    });
});

describe('PUT /api/rounds/{round}/scores/{score}', function () {
    it('allows the round owner to update a score', function () {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $round = Round::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);
        $hole = Hole::factory()->create(['course_id' => $course->id, 'number' => 1]);
        $score = Score::factory()->create(['round_id' => $round->id, 'hole_id' => $hole->id, 'strokes' => 5]);

        $this->actingAs($user)
            ->putJson("/api/rounds/{$round->id}/scores/{$score->id}", ['strokes' => 3])
            ->assertOk()
            ->assertJsonPath('data.strokes', 3);
    });

    it('forbids another user from updating the score', function () {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $course = Course::factory()->create();
        $round = Round::factory()->create(['user_id' => $other->id, 'course_id' => $course->id]);
        $hole = Hole::factory()->create(['course_id' => $course->id, 'number' => 1]);
        $score = Score::factory()->create(['round_id' => $round->id, 'hole_id' => $hole->id, 'strokes' => 5]);

        $this->actingAs($user)
            ->putJson("/api/rounds/{$round->id}/scores/{$score->id}", ['strokes' => 3])
            ->assertForbidden();
    });

    it('returns 404 when score does not belong to round', function () {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $round = Round::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);
        $otherRound = Round::factory()->create(['user_id' => $user->id]);
        $hole = Hole::factory()->create(['course_id' => $course->id, 'number' => 1]);
        $score = Score::factory()->create(['round_id' => $otherRound->id, 'hole_id' => $hole->id, 'strokes' => 5]);

        $this->actingAs($user)
            ->putJson("/api/rounds/{$round->id}/scores/{$score->id}", ['strokes' => 3])
            ->assertNotFound();
    });
});

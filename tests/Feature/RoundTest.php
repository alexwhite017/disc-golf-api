<?php

use App\Models\Course;
use App\Models\Round;
use App\Models\User;

describe('GET /api/rounds', function () {
    it('requires authentication', function () {
        $this->getJson('/api/rounds')->assertUnauthorized();
    });

    it('returns only the authenticated user\'s rounds', function () {
        $user = User::factory()->create();
        $other = User::factory()->create();

        Round::factory()->count(2)->create(['user_id' => $user->id]);
        Round::factory()->count(3)->create(['user_id' => $other->id]);

        $this->actingAs($user)
            ->getJson('/api/rounds')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    });
});

describe('POST /api/rounds', function () {
    it('requires authentication', function () {
        $course = Course::factory()->create();

        $this->postJson('/api/rounds', ['course_id' => $course->id, 'played_at' => '2024-06-01'])
            ->assertUnauthorized();
    });

    it('creates a round for the authenticated user', function () {
        $user = User::factory()->create();
        $course = Course::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/rounds', [
                'course_id' => $course->id,
                'played_at' => '2024-06-01',
                'notes' => 'Great round!',
            ])
            ->assertCreated()
            ->assertJsonPath('data.user_id', $user->id)
            ->assertJsonPath('data.course_id', $course->id);

        $this->assertDatabaseHas('rounds', ['user_id' => $user->id, 'course_id' => $course->id]);
    });

    it('validates required fields', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/rounds', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['course_id', 'played_at']);
    });

    it('validates course must exist', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/rounds', ['course_id' => 999, 'played_at' => '2024-06-01'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['course_id']);
    });
});

describe('GET /api/rounds/{round}', function () {
    it('requires authentication', function () {
        $round = Round::factory()->create();

        $this->getJson("/api/rounds/{$round->id}")->assertUnauthorized();
    });

    it('returns the round for its owner', function () {
        $user = User::factory()->create();
        $round = Round::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->getJson("/api/rounds/{$round->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $round->id);
    });

    it('forbids access to another user\'s round', function () {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $round = Round::factory()->create(['user_id' => $other->id]);

        $this->actingAs($user)
            ->getJson("/api/rounds/{$round->id}")
            ->assertForbidden();
    });

    it('includes computed score fields when scores are loaded', function () {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $round = Round::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

        $hole = $course->holes()->create(['number' => 1, 'par' => 3]);
        $round->scores()->create(['hole_id' => $hole->id, 'strokes' => 4]);

        $response = $this->actingAs($user)
            ->getJson("/api/rounds/{$round->id}")
            ->assertOk();

        expect($response->json('data.total_score'))->toBe(4)
            ->and($response->json('data.score_vs_par'))->toBe(1); // 4 strokes - par 3 = +1
    });
});

describe('PUT /api/rounds/{round}', function () {
    it('allows the owner to update their round', function () {
        $user = User::factory()->create();
        $round = Round::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->putJson("/api/rounds/{$round->id}", ['notes' => 'Updated notes'])
            ->assertOk()
            ->assertJsonPath('data.notes', 'Updated notes');
    });

    it('forbids another user from updating the round', function () {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $round = Round::factory()->create(['user_id' => $other->id]);

        $this->actingAs($user)
            ->putJson("/api/rounds/{$round->id}", ['notes' => 'Hacked'])
            ->assertForbidden();
    });
});

describe('DELETE /api/rounds/{round}', function () {
    it('allows the owner to delete their round', function () {
        $user = User::factory()->create();
        $round = Round::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->deleteJson("/api/rounds/{$round->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('rounds', ['id' => $round->id]);
    });

    it('forbids another user from deleting the round', function () {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $round = Round::factory()->create(['user_id' => $other->id]);

        $this->actingAs($user)
            ->deleteJson("/api/rounds/{$round->id}")
            ->assertForbidden();
    });
});

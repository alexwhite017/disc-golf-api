<?php

use App\Models\Course;
use App\Models\Hole;
use App\Models\User;

describe('GET /api/courses/{course}/holes', function () {
    it('returns holes for unauthenticated users', function () {
        $course = Course::factory()->create();
        Hole::factory()->create(['course_id' => $course->id, 'number' => 1, 'par' => 3]);
        Hole::factory()->create(['course_id' => $course->id, 'number' => 2, 'par' => 3]);
        Hole::factory()->create(['course_id' => $course->id, 'number' => 3, 'par' => 3]);

        $this->getJson("/api/courses/{$course->id}/holes")
            ->assertOk()
            ->assertJsonCount(3, 'data');
    });

    it('returns holes ordered by number', function () {
        $course = Course::factory()->create();
        Hole::factory()->create(['course_id' => $course->id, 'number' => 3, 'par' => 3]);
        Hole::factory()->create(['course_id' => $course->id, 'number' => 1, 'par' => 4]);
        Hole::factory()->create(['course_id' => $course->id, 'number' => 2, 'par' => 5]);

        $response = $this->getJson("/api/courses/{$course->id}/holes")->assertOk();

        expect($response->json('data.0.number'))->toBe(1)
            ->and($response->json('data.1.number'))->toBe(2)
            ->and($response->json('data.2.number'))->toBe(3);
    });
});

describe('GET /api/courses/{course}/holes/{hole}', function () {
    it('returns a hole for unauthenticated users', function () {
        $course = Course::factory()->create();
        $hole = Hole::factory()->create(['course_id' => $course->id, 'number' => 1]);

        $this->getJson("/api/courses/{$course->id}/holes/{$hole->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $hole->id);
    });

    it('returns 404 when hole does not belong to course', function () {
        $course = Course::factory()->create();
        $otherHole = Hole::factory()->create(['number' => 1]);

        $this->getJson("/api/courses/{$course->id}/holes/{$otherHole->id}")
            ->assertNotFound();
    });
});

describe('POST /api/courses/{course}/holes', function () {
    it('requires authentication', function () {
        $course = Course::factory()->create();

        $this->postJson("/api/courses/{$course->id}/holes", ['number' => 1, 'par' => 3])
            ->assertUnauthorized();
    });

    it('forbids non-admin users', function () {
        $user = User::factory()->create(['is_admin' => false]);
        $course = Course::factory()->create();

        $this->actingAs($user)
            ->postJson("/api/courses/{$course->id}/holes", ['number' => 1, 'par' => 3])
            ->assertForbidden();
    });

    it('allows admin users to create a hole', function () {
        $admin = User::factory()->create(['is_admin' => true]);
        $course = Course::factory()->create();

        $this->actingAs($admin)
            ->postJson("/api/courses/{$course->id}/holes", [
                'number' => 1,
                'par' => 3,
                'distance_feet' => 280,
            ])
            ->assertCreated()
            ->assertJsonPath('data.number', 1)
            ->assertJsonPath('data.par', 3)
            ->assertJsonPath('data.distance_feet', 280);
    });

    it('validates par must be 3, 4, or 5', function () {
        $admin = User::factory()->create(['is_admin' => true]);
        $course = Course::factory()->create();

        $this->actingAs($admin)
            ->postJson("/api/courses/{$course->id}/holes", ['number' => 1, 'par' => 6])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['par']);
    });
});

describe('DELETE /api/courses/{course}/holes/{hole}', function () {
    it('allows admin users to delete a hole', function () {
        $admin = User::factory()->create(['is_admin' => true]);
        $course = Course::factory()->create();
        $hole = Hole::factory()->create(['course_id' => $course->id, 'number' => 1]);

        $this->actingAs($admin)
            ->deleteJson("/api/courses/{$course->id}/holes/{$hole->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('holes', ['id' => $hole->id]);
    });
});

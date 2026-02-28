<?php

use App\Models\Course;
use App\Models\User;

describe('GET /api/courses', function () {
    it('returns courses for unauthenticated users', function () {
        Course::factory()->count(3)->create();

        $this->getJson('/api/courses')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    });

    it('includes holes when fetching a single course', function () {
        $course = Course::factory()->create();
        $course->holes()->createMany([
            ['number' => 1, 'par' => 3],
            ['number' => 2, 'par' => 4],
        ]);

        $this->getJson("/api/courses/{$course->id}")
            ->assertOk()
            ->assertJsonPath('data.holes.0.number', 1)
            ->assertJsonPath('data.holes.1.number', 2);
    });
});

describe('GET /api/courses/{course}', function () {
    it('returns a course for unauthenticated users', function () {
        $course = Course::factory()->create();

        $this->getJson("/api/courses/{$course->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $course->id)
            ->assertJsonPath('data.name', $course->name);
    });

    it('returns 404 for a missing course', function () {
        $this->getJson('/api/courses/999')->assertNotFound();
    });
});

describe('POST /api/courses', function () {
    it('requires authentication', function () {
        $this->postJson('/api/courses', ['name' => 'Test Course'])
            ->assertUnauthorized();
    });

    it('forbids non-admin users', function () {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->postJson('/api/courses', ['name' => 'Test Course'])
            ->assertForbidden();
    });

    it('allows admin users to create a course', function () {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->postJson('/api/courses', [
                'name' => 'Maple Hill',
                'city' => 'Leicester',
                'state' => 'MA',
            ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Maple Hill')
            ->assertJsonPath('data.city', 'Leicester');

        $this->assertDatabaseHas('courses', ['name' => 'Maple Hill', 'created_by' => $admin->id]);
    });

    it('validates required fields', function () {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->postJson('/api/courses', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    });
});

describe('PUT /api/courses/{course}', function () {
    it('requires authentication', function () {
        $course = Course::factory()->create();

        $this->putJson("/api/courses/{$course->id}", ['name' => 'New Name'])
            ->assertUnauthorized();
    });

    it('forbids non-admin users', function () {
        $user = User::factory()->create(['is_admin' => false]);
        $course = Course::factory()->create();

        $this->actingAs($user)
            ->putJson("/api/courses/{$course->id}", ['name' => 'New Name'])
            ->assertForbidden();
    });

    it('allows admin users to update a course', function () {
        $admin = User::factory()->create(['is_admin' => true]);
        $course = Course::factory()->create();

        $this->actingAs($admin)
            ->putJson("/api/courses/{$course->id}", ['name' => 'Updated Name'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated Name');
    });
});

describe('DELETE /api/courses/{course}', function () {
    it('requires authentication', function () {
        $course = Course::factory()->create();

        $this->deleteJson("/api/courses/{$course->id}")->assertUnauthorized();
    });

    it('forbids non-admin users', function () {
        $user = User::factory()->create(['is_admin' => false]);
        $course = Course::factory()->create();

        $this->actingAs($user)
            ->deleteJson("/api/courses/{$course->id}")
            ->assertForbidden();
    });

    it('allows admin users to delete a course', function () {
        $admin = User::factory()->create(['is_admin' => true]);
        $course = Course::factory()->create();

        $this->actingAs($admin)
            ->deleteJson("/api/courses/{$course->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
    });
});

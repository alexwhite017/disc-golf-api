<?php

use App\Models\Course;
use App\Models\Disc;
use App\Models\Round;
use App\Models\User;

describe('Course pagination and filtering', function () {
    it('paginates courses', function () {
        Course::factory()->count(20)->create();

        $response = $this->getJson('/api/courses')->assertOk();

        expect($response->json('data'))->toHaveCount(15)
            ->and($response->json('meta.total'))->toBe(20)
            ->and($response->json('meta.per_page'))->toBe(15);
    });

    it('respects per_page parameter', function () {
        Course::factory()->count(10)->create();

        $response = $this->getJson('/api/courses?per_page=5')->assertOk();

        expect($response->json('data'))->toHaveCount(5)
            ->and($response->json('meta.total'))->toBe(10);
    });

    it('filters courses by name search', function () {
        Course::factory()->create(['name' => 'Maple Hill']);
        Course::factory()->create(['name' => 'DeLaveaga']);

        $response = $this->getJson('/api/courses?search=maple')->assertOk();

        expect($response->json('data'))->toHaveCount(1)
            ->and($response->json('data.0.name'))->toBe('Maple Hill');
    });

    it('filters courses by state', function () {
        Course::factory()->create(['state' => 'MA']);
        Course::factory()->create(['state' => 'CA']);
        Course::factory()->create(['state' => 'MA']);

        $response = $this->getJson('/api/courses?state=MA')->assertOk();

        expect($response->json('data'))->toHaveCount(2);
    });
});

describe('Disc filtering', function () {
    it('filters discs by is_in_bag', function () {
        $user = User::factory()->create();
        Disc::factory()->count(3)->create(['user_id' => $user->id, 'is_in_bag' => true]);
        Disc::factory()->count(2)->create(['user_id' => $user->id, 'is_in_bag' => false]);

        $response = $this->actingAs($user)->getJson('/api/discs?is_in_bag=1')->assertOk();
        expect($response->json('data'))->toHaveCount(3);

        $response = $this->actingAs($user)->getJson('/api/discs?is_in_bag=0')->assertOk();
        expect($response->json('data'))->toHaveCount(2);
    });

    it('filters discs by type', function () {
        $user = User::factory()->create();
        Disc::factory()->count(2)->create(['user_id' => $user->id, 'type' => 'driver']);
        Disc::factory()->count(3)->create(['user_id' => $user->id, 'type' => 'putter']);

        $response = $this->actingAs($user)->getJson('/api/discs?type=putter')->assertOk();
        expect($response->json('data'))->toHaveCount(3);
    });

    it('paginates discs', function () {
        $user = User::factory()->create();
        Disc::factory()->count(20)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->getJson('/api/discs')->assertOk();
        expect($response->json('data'))->toHaveCount(15)
            ->and($response->json('meta.total'))->toBe(20);
    });
});

describe('Round filtering', function () {
    it('filters rounds by course_id', function () {
        $user = User::factory()->create();
        $course1 = Course::factory()->create();
        $course2 = Course::factory()->create();

        Round::factory()->count(3)->create(['user_id' => $user->id, 'course_id' => $course1->id]);
        Round::factory()->count(2)->create(['user_id' => $user->id, 'course_id' => $course2->id]);

        $response = $this->actingAs($user)
            ->getJson("/api/rounds?course_id={$course1->id}")
            ->assertOk();

        expect($response->json('data'))->toHaveCount(3);
    });

    it('filters rounds by date range', function () {
        $user = User::factory()->create();
        $course = Course::factory()->create();

        Round::factory()->create(['user_id' => $user->id, 'course_id' => $course->id, 'played_at' => '2024-01-15']);
        Round::factory()->create(['user_id' => $user->id, 'course_id' => $course->id, 'played_at' => '2024-06-01']);
        Round::factory()->create(['user_id' => $user->id, 'course_id' => $course->id, 'played_at' => '2024-12-01']);

        $response = $this->actingAs($user)
            ->getJson('/api/rounds?from=2024-02-01&to=2024-11-01')
            ->assertOk();

        expect($response->json('data'))->toHaveCount(1)
            ->and($response->json('data.0.played_at'))->toStartWith('2024-06-01');
    });

    it('paginates rounds', function () {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        Round::factory()->count(20)->create(['user_id' => $user->id, 'course_id' => $course->id]);

        $response = $this->actingAs($user)->getJson('/api/rounds')->assertOk();
        expect($response->json('data'))->toHaveCount(15)
            ->and($response->json('meta.total'))->toBe(20);
    });
});

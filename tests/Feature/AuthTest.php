<?php

use App\Models\User;

describe('POST /api/register', function () {
    it('registers a new user and returns a token', function () {
        $response = $this->postJson('/api/register', [
            'name' => 'Alex Johnson',
            'email' => 'alex@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertCreated();

        expect($response->json('token'))->not->toBeNull()
            ->and($response->json('user.email'))->toBe('alex@example.com')
            ->and($response->json('user.is_admin'))->toBeFalse();

        $this->assertDatabaseHas('users', ['email' => 'alex@example.com']);
    });

    it('does not expose sensitive fields', function () {
        $response = $this->postJson('/api/register', [
            'name' => 'Alex Johnson',
            'email' => 'alex@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertCreated();

        expect($response->json('user'))->not->toHaveKey('password')
            ->and($response->json('user'))->not->toHaveKey('remember_token');
    });

    it('requires password confirmation', function () {
        $this->postJson('/api/register', [
            'name' => 'Alex Johnson',
            'email' => 'alex@example.com',
            'password' => 'password123',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    });

    it('rejects mismatched password confirmation', function () {
        $this->postJson('/api/register', [
            'name' => 'Alex Johnson',
            'email' => 'alex@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    });

    it('rejects passwords shorter than 8 characters', function () {
        $this->postJson('/api/register', [
            'name' => 'Alex Johnson',
            'email' => 'alex@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    });

    it('rejects duplicate emails', function () {
        User::factory()->create(['email' => 'alex@example.com']);

        $this->postJson('/api/register', [
            'name' => 'Alex Johnson',
            'email' => 'alex@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    });
});

describe('POST /api/login', function () {
    it('returns a token for valid credentials', function () {
        $user = User::factory()->create(['email' => 'alex@example.com']);

        $response = $this->postJson('/api/login', [
            'email' => 'alex@example.com',
            'password' => 'password',
        ])->assertOk();

        expect($response->json('token'))->not->toBeNull()
            ->and($response->json('user.id'))->toBe($user->id)
            ->and($response->json('user.is_admin'))->toBeFalse();
    });

    it('rejects invalid credentials', function () {
        User::factory()->create(['email' => 'alex@example.com']);

        $this->postJson('/api/login', [
            'email' => 'alex@example.com',
            'password' => 'wrong-password',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    });

    it('rejects unknown email', function () {
        $this->postJson('/api/login', [
            'email' => 'nobody@example.com',
            'password' => 'password',
        ])->assertUnprocessable();
    });
});

describe('POST /api/logout', function () {
    it('requires authentication', function () {
        $this->postJson('/api/logout')->assertUnauthorized();
    });

    it('invalidates the current token', function () {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;

        $this->withToken($token)->postJson('/api/logout')->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    });
});

describe('GET /api/user', function () {
    it('requires authentication', function () {
        $this->getJson('/api/user')->assertUnauthorized();
    });

    it('returns the authenticated user', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/user')->assertOk();

        expect($response->json('id'))->toBe($user->id)
            ->and($response->json('email'))->toBe($user->email)
            ->and($response->json('is_admin'))->toBeFalse();
    });

    it('does not expose sensitive fields', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/user')->assertOk();

        expect($response->json())->not->toHaveKey('password')
            ->and($response->json())->not->toHaveKey('remember_token');
    });
});

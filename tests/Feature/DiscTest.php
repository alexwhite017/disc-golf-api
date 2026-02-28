<?php

use App\Models\Disc;
use App\Models\User;

describe('GET /api/discs', function () {
    it('requires authentication', function () {
        $this->getJson('/api/discs')->assertUnauthorized();
    });

    it('returns only the authenticated user\'s discs', function () {
        $user = User::factory()->create();
        $other = User::factory()->create();

        Disc::factory()->count(2)->create(['user_id' => $user->id]);
        Disc::factory()->count(3)->create(['user_id' => $other->id]);

        $this->actingAs($user)
            ->getJson('/api/discs')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    });
});

describe('POST /api/discs', function () {
    it('requires authentication', function () {
        $this->postJson('/api/discs', [])->assertUnauthorized();
    });

    it('creates a disc for the authenticated user', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/discs', [
                'brand' => 'Innova',
                'name' => 'Destroyer',
                'type' => 'driver',
                'weight_grams' => 175.0,
                'color' => 'red',
                'is_in_bag' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('data.brand', 'Innova')
            ->assertJsonPath('data.name', 'Destroyer')
            ->assertJsonPath('data.type', 'driver')
            ->assertJsonPath('data.user_id', $user->id);

        $this->assertDatabaseHas('discs', ['user_id' => $user->id, 'name' => 'Destroyer']);
    });

    it('validates type must be a valid enum value', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/discs', [
                'brand' => 'Innova',
                'name' => 'Destroyer',
                'type' => 'frisbee',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['type']);
    });

    it('validates required fields', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/discs', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['brand', 'name', 'type']);
    });
});

describe('GET /api/discs/{disc}', function () {
    it('returns the disc for its owner', function () {
        $user = User::factory()->create();
        $disc = Disc::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->getJson("/api/discs/{$disc->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $disc->id);
    });

    it('forbids access to another user\'s disc', function () {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $disc = Disc::factory()->create(['user_id' => $other->id]);

        $this->actingAs($user)
            ->getJson("/api/discs/{$disc->id}")
            ->assertForbidden();
    });
});

describe('PUT /api/discs/{disc}', function () {
    it('allows the owner to update their disc', function () {
        $user = User::factory()->create();
        $disc = Disc::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->putJson("/api/discs/{$disc->id}", ['is_in_bag' => false])
            ->assertOk()
            ->assertJsonPath('data.is_in_bag', false);
    });

    it('forbids another user from updating the disc', function () {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $disc = Disc::factory()->create(['user_id' => $other->id]);

        $this->actingAs($user)
            ->putJson("/api/discs/{$disc->id}", ['color' => 'blue'])
            ->assertForbidden();
    });
});

describe('DELETE /api/discs/{disc}', function () {
    it('allows the owner to delete their disc', function () {
        $user = User::factory()->create();
        $disc = Disc::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->deleteJson("/api/discs/{$disc->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('discs', ['id' => $disc->id]);
    });

    it('forbids another user from deleting the disc', function () {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $disc = Disc::factory()->create(['user_id' => $other->id]);

        $this->actingAs($user)
            ->deleteJson("/api/discs/{$disc->id}")
            ->assertForbidden();
    });
});

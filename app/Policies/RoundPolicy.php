<?php

namespace App\Policies;

use App\Models\Round;
use App\Models\User;

class RoundPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Round $round): bool
    {
        if ($user->id === $round->user_id) {
            return true;
        }

        // Use loaded relation if available to avoid N+1
        if ($round->relationLoaded('players')) {
            return $round->players->contains('id', $user->id);
        }

        return $round->players()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Round $round): bool
    {
        return $user->id === $round->user_id;
    }

    public function delete(User $user, Round $round): bool
    {
        return $user->id === $round->user_id;
    }

    public function managePlayers(User $user, Round $round): bool
    {
        return $user->id === $round->user_id;
    }
}

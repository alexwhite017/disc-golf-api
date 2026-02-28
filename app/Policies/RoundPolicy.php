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
        return $user->id === $round->user_id;
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
}

<?php

namespace App\Policies;

use App\Models\Round;
use App\Models\Score;
use App\Models\User;

class ScorePolicy
{
    public function create(User $user, Round $round): bool
    {
        return $round->players()->where('user_id', $user->id)->exists();
    }

    public function update(User $user, Score $score): bool
    {
        return $user->id === $score->user_id;
    }

    public function delete(User $user, Score $score): bool
    {
        return $user->id === $score->user_id;
    }
}

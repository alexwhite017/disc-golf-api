<?php

namespace App\Policies;

use App\Models\Round;
use App\Models\Score;
use App\Models\User;

class ScorePolicy
{
    public function create(User $user, Round $round): bool
    {
        return $user->id === $round->user_id;
    }

    public function update(User $user, Score $score): bool
    {
        return $user->id === $score->round->user_id;
    }

    public function delete(User $user, Score $score): bool
    {
        return $user->id === $score->round->user_id;
    }
}

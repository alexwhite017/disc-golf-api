<?php

namespace App\Policies;

use App\Models\Disc;
use App\Models\User;

class DiscPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Disc $disc): bool
    {
        return $user->id === $disc->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Disc $disc): bool
    {
        return $user->id === $disc->user_id;
    }

    public function delete(User $user, Disc $disc): bool
    {
        return $user->id === $disc->user_id;
    }
}

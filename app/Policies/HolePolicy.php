<?php

namespace App\Policies;

use App\Models\Hole;
use App\Models\User;

class HolePolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Hole $hole): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Hole $hole): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Hole $hole): bool
    {
        return $user->isAdmin();
    }
}

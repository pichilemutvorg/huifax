<?php

namespace App\Policies;

use App\Models\User;

/**
 * The slide rotation is managed by administrators only.
 */
class SlidePolicy
{
    public function before(User $user): bool
    {
        return $user->isAdmin();
    }

    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }
}

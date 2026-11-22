<?php

namespace App\Policies;

use App\Models\User;

/**
 * Background photos are managed by administrators only.
 */
class BackgroundPolicy
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

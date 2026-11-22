<?php

namespace App\Policies;

use App\Models\User;

/**
 * Users are managed by administrators. Nobody can delete their own account,
 * so there is always at least one administrator left.
 */
class UserPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? null : false;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, User $model): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, User $model): bool
    {
        return true;
    }

    public function delete(User $user, User $model): bool
    {
        return $user->isNot($model);
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}

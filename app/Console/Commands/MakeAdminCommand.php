<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('huifax:admin {email : Email of an existing user}')]
#[Description('Give an existing user the administrator role')]
class MakeAdminCommand extends Command
{
    public function handle(): int
    {
        $user = User::query()->where('email', $this->argument('email'))->first();

        if ($user === null) {
            $this->components->error('No user with that email. Create one first with `php artisan make:filament-user`.');

            return self::FAILURE;
        }

        $user->update(['role' => UserRole::Admin]);
        $this->components->info("{$user->email} is now an administrator.");

        return self::SUCCESS;
    }
}

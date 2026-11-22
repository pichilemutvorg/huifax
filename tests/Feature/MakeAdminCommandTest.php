<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Notice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MakeAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_users_are_editors_by_default(): void
    {
        $user = User::query()->create(['name' => 'Ana', 'email' => 'ana@example.org', 'password' => 'secret-password']);

        $this->assertSame(UserRole::Editor, $user->fresh()->role);
    }

    public function test_it_promotes_an_existing_user(): void
    {
        $user = User::factory()->create(['email' => 'ana@example.org']);

        $this->artisan('huifax:admin', ['email' => 'ana@example.org'])->assertSuccessful();

        $this->assertSame(UserRole::Admin, $user->fresh()->role);
    }

    public function test_it_fails_for_an_unknown_email(): void
    {
        $this->artisan('huifax:admin', ['email' => 'nobody@example.org'])->assertFailed();
    }

    public function test_new_records_are_appended_to_the_display_order(): void
    {
        Notice::factory()->create(['sort_order' => 7]);

        $notice = Notice::query()->create(['title' => 'Nuevo']);

        $this->assertSame(8, $notice->sort_order);
    }
}

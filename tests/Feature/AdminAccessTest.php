<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, array{string}> */
    public static function contentPages(): array
    {
        return [
            'notices' => ['/admin/notices'],
            'featured programs' => ['/admin/featured-programs'],
            'emergency contacts' => ['/admin/emergency-contacts'],
            'social links' => ['/admin/social-links'],
        ];
    }

    /** @return array<string, array{string}> */
    public static function adminPages(): array
    {
        return [
            'slides' => ['/admin/slides'],
            'backgrounds' => ['/admin/backgrounds'],
            'users' => ['/admin/users'],
            'branding settings' => ['/admin/manage-branding'],
            'display settings' => ['/admin/manage-display'],
            'audio settings' => ['/admin/manage-audio'],
        ];
    }

    public function test_guests_are_sent_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    #[DataProvider('contentPages')]
    public function test_editors_can_manage_content(string $url): void
    {
        $this->actingAs(User::factory()->create())->get($url)->assertOk();
    }

    #[DataProvider('adminPages')]
    public function test_editors_cannot_reach_admin_pages(string $url): void
    {
        $this->actingAs(User::factory()->create())->get($url)->assertForbidden();
    }

    #[DataProvider('contentPages')]
    #[DataProvider('adminPages')]
    public function test_admins_can_reach_every_page(string $url): void
    {
        $this->actingAs(User::factory()->admin()->create())->get($url)->assertOk();
    }

    public function test_admins_cannot_delete_themselves(): void
    {
        $admin = User::factory()->admin()->create();
        $other = User::factory()->create();

        $this->assertFalse($admin->can('delete', $admin));
        $this->assertTrue($admin->can('delete', $other));
        $this->assertFalse($other->can('delete', $admin));
    }
}

<?php

namespace Tests\Feature;

use App\Display\DisplayHeartbeat;
use App\Filament\Widgets\SystemStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class SystemStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_display_requests_are_recorded(): void
    {
        $this->assertNull(app(DisplayHeartbeat::class)->lastSeen());

        Carbon::setTestNow('2026-11-23 10:00:00');
        $this->getJson('/api/display')->assertOk();

        $this->assertSame('2026-11-23 10:00:00', app(DisplayHeartbeat::class)->lastSeen()->format('Y-m-d H:i:s'));
    }

    public function test_the_dashboard_shows_whether_the_display_is_connected(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(SystemStatus::class)->assertSee(__('admin.status.disconnected'));

        app(DisplayHeartbeat::class)->record();

        Livewire::test(SystemStatus::class)->assertSee(__('admin.status.connected'));
    }

    public function test_a_display_silent_for_several_polls_shows_as_disconnected(): void
    {
        $this->actingAs(User::factory()->create());
        Carbon::setTestNow('2026-11-23 10:00:00');
        app(DisplayHeartbeat::class)->record();

        Carbon::setTestNow('2026-11-23 10:10:00');

        Livewire::test(SystemStatus::class)->assertSee(__('admin.status.disconnected'));
    }
}

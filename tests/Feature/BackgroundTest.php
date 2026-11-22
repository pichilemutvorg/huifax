<?php

namespace Tests\Feature;

use App\Models\Background;
use App\Models\Notice;
use App\Models\Slide;
use App\Settings\DisplaySettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BackgroundTest extends TestCase
{
    use RefreshDatabase;

    public function test_enabled_backgrounds_rotate_in_order(): void
    {
        Storage::fake(Background::DISK);
        $second = Background::factory()->create(['sort_order' => 2]);
        $first = Background::factory()->create(['sort_order' => 1]);
        Background::factory()->disabled()->create();

        $this->getJson(route('api.display'))
            ->assertJsonCount(2, 'backgrounds')
            ->assertJsonPath('backgrounds.0.id', $first->id)
            ->assertJsonPath('backgrounds.0.url', $first->url())
            ->assertJsonPath('backgrounds.1.id', $second->id);
    }

    public function test_a_slide_can_use_its_own_background_even_if_not_in_rotation(): void
    {
        Storage::fake(Background::DISK);
        $background = Background::factory()->disabled()->create();
        Slide::factory()->ofType('notices')->create(['background_id' => $background->id]);
        Notice::factory()->create();

        $this->getJson(route('api.display'))
            ->assertJsonCount(0, 'backgrounds')
            ->assertJsonPath('slides.0.background', $background->url());
    }

    public function test_deleting_a_background_unassigns_it_and_removes_the_file(): void
    {
        Storage::fake(Background::DISK);
        Storage::disk(Background::DISK)->put('backgrounds/a.jpg', 'image');
        $background = Background::factory()->create(['path' => 'backgrounds/a.jpg']);
        $slide = Slide::factory()->create(['background_id' => $background->id]);

        $background->delete();

        Storage::disk(Background::DISK)->assertMissing('backgrounds/a.jpg');
        $this->assertNull($slide->fresh()->background_id);
    }

    public function test_replacing_the_image_removes_the_previous_file(): void
    {
        Storage::fake(Background::DISK);
        Storage::disk(Background::DISK)->put('backgrounds/old.jpg', 'old');
        Storage::disk(Background::DISK)->put('backgrounds/new.jpg', 'new');
        $background = Background::factory()->create(['path' => 'backgrounds/old.jpg']);

        $background->update(['path' => 'backgrounds/new.jpg']);

        Storage::disk(Background::DISK)->assertMissing('backgrounds/old.jpg');
        Storage::disk(Background::DISK)->assertExists('backgrounds/new.jpg');
    }

    public function test_display_settings_reach_the_payload(): void
    {
        DisplaySettings::fake(['poll_interval' => 45, 'reload_at' => null, 'background_interval' => 90]);

        $this->getJson(route('api.display'))
            ->assertJsonPath('settings.poll_interval', 45)
            ->assertJsonPath('settings.reload_at', null)
            ->assertJsonPath('settings.background_interval', 90);
    }
}

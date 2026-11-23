<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageDisplay;
use App\Filament\Resources\Backgrounds\Pages\ManageBackgrounds;
use App\Filament\Resources\Notices\Pages\ManageNotices;
use App\Filament\Resources\Slides\Pages\ManageSlides;
use App\Models\Background;
use App\Models\Notice;
use App\Models\Slide;
use App\Models\User;
use App\Settings\DisplaySettings;
use Filament\Actions\CreateAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AdminResourcesTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_editor_can_create_a_notice_at_the_end_of_the_list(): void
    {
        Notice::factory()->create(['sort_order' => 4]);
        $this->actingAs(User::factory()->create());

        Livewire::test(ManageNotices::class)
            ->callAction(CreateAction::class, data: [
                'title' => 'Feria costumbrista',
                'body' => 'Sábado y domingo en la plaza.',
                'is_enabled' => true,
            ])
            ->assertHasNoActionErrors();

        $this->assertSame(5, Notice::query()->where('title', 'Feria costumbrista')->value('sort_order'));
    }

    public function test_a_notice_cannot_end_before_it_starts(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(ManageNotices::class)
            ->callAction(CreateAction::class, data: [
                'title' => 'Aviso',
                'starts_at' => '2026-12-10 10:00',
                'ends_at' => '2026-12-09 10:00',
            ])
            ->assertHasActionErrors(['ends_at']);
    }

    public function test_an_admin_can_create_a_slide_with_type_settings(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(ManageSlides::class)
            ->callAction(CreateAction::class, data: [
                'type' => 'emergency_contacts',
                'settings' => ['heading' => 'Emergencias', 'per_page' => 3],
                'is_enabled' => true,
            ])
            ->assertHasNoActionErrors();

        $slide = Slide::query()->sole();
        $this->assertSame('emergency_contacts', $slide->type);
        $this->assertSame('Emergencias', $slide->setting('heading'));
        $this->assertEquals(3, $slide->setting('per_page'));
    }

    public function test_an_admin_can_upload_a_background(): void
    {
        Storage::fake(Background::DISK);
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(ManageBackgrounds::class)
            ->callAction(CreateAction::class, data: [
                'name' => 'Punta de Lobos',
                'path' => UploadedFile::fake()->image('lobos.jpg', 2400, 1350),
                'is_enabled' => true,
            ])
            ->assertHasNoActionErrors();

        // Stored as an optimized WebP, not the original upload.
        $background = Background::query()->sole();
        $this->assertStringEndsWith('.webp', $background->path);
        $this->assertSame('image/webp', getimagesizefromstring(Storage::disk(Background::DISK)->get($background->path))['mime']);
    }

    public function test_a_background_larger_than_the_upload_limit_is_rejected(): void
    {
        Storage::fake(Background::DISK);
        config(['huifax.uploads.max_size' => 100]);
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(ManageBackgrounds::class)
            ->callAction(CreateAction::class, data: [
                'name' => 'Demasiado grande',
                'path' => UploadedFile::fake()->image('big.jpg')->size(200),
            ])
            ->assertHasActionErrors(['path']);
    }

    public function test_display_settings_are_saved(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(ManageDisplay::class)
            ->fillForm(['poll_interval' => 45, 'background_interval' => 90, 'reload_at' => ''])
            ->call('save')
            ->assertHasNoFormErrors();

        $settings = app(DisplaySettings::class)->refresh();
        $this->assertSame(45, $settings->poll_interval);
        $this->assertSame(90, $settings->background_interval);
        $this->assertNull($settings->reload_at);
    }

    public function test_reload_time_must_be_hh_mm(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(ManageDisplay::class)
            ->fillForm(['reload_at' => '5am'])
            ->call('save')
            ->assertHasFormErrors(['reload_at']);
    }
}

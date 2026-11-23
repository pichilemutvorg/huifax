<?php

namespace Tests\Feature;

use App\Filament\Pages\DisplayPreview;
use App\Filament\Pages\ManageDisplay;
use App\Models\Notice;
use App\Models\Slide;
use App\Models\User;
use App\Settings\AudioSettings;
use App\Settings\DisplaySettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ThemesTest extends TestCase
{
    use RefreshDatabase;

    private function setTheme(string $theme): void
    {
        $settings = app(DisplaySettings::class);
        $settings->theme = $theme;
        $settings->save();
    }

    public function test_every_configured_theme_has_a_stylesheet(): void
    {
        foreach (config('huifax.themes') as $theme) {
            $file = resource_path("css/themes/{$theme}.css");
            $file = $theme === 'huifax' ? resource_path('css/themes/base.css') : $file;

            $this->assertFileExists($file);
            $this->assertStringContainsString("name: '{$theme}';", file_get_contents($file), "Theme {$theme} must define its daisyUI theme.");
        }
    }

    public function test_the_display_uses_the_configured_theme(): void
    {
        $this->setTheme('metro');

        $this->getJson('/api/display')->assertJsonPath('settings.theme', 'metro');
        $this->get('/')->assertSee('data-theme="metro"', false);
    }

    public function test_a_theme_that_no_longer_exists_falls_back_to_the_default(): void
    {
        $this->setTheme('removed-theme');

        $this->getJson('/api/display')->assertJsonPath('settings.theme', 'huifax');
    }

    public function test_the_display_url_can_force_a_known_theme_only(): void
    {
        $this->get('/?theme=bbc')
            ->assertSee('data-theme="bbc"', false)
            ->assertSee('data-theme-override="bbc"', false);

        $this->get('/?theme=../../evil')
            ->assertSee('data-theme="huifax"', false)
            ->assertSee('data-theme-override=""', false);
    }

    public function test_an_admin_can_change_the_theme(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(ManageDisplay::class)
            ->fillForm(['theme' => 'anthropic'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('anthropic', app(DisplaySettings::class)->refresh()->theme);
    }

    public function test_the_preview_requires_login(): void
    {
        $this->get('/admin/preview/frame')->assertRedirect('/admin/login');
    }

    public function test_the_preview_never_plays_the_radio(): void
    {
        $audio = app(AudioSettings::class);
        $audio->enabled = true;
        $audio->stream_url = 'https://radio.example/stream';
        $audio->save();
        $this->actingAs(User::factory()->create());

        $html = $this->get('/admin/preview/frame')->assertOk()->getContent();
        preg_match('/<script type="application\/json" id="preview-data">(.*?)<\/script>/s', $html, $match);

        $this->assertFalse(json_decode($match[1], true)['payload']['settings']['audio']['enabled']);
    }

    public function test_editors_see_the_rotation_preview_in_any_theme(): void
    {
        $this->actingAs(User::factory()->create());
        Slide::factory()->ofType('notices')->create(['name' => 'Avisos de la portada']);
        Notice::factory()->create(['title' => 'Corte de agua']);

        Livewire::test(DisplayPreview::class)->assertOk();

        $this->get('/admin/preview/frame?theme=material')
            ->assertOk()
            ->assertSee('data-theme="material"', false)
            ->assertSee('Avisos de la portada')
            ->assertSee('Corte de agua');
    }
}

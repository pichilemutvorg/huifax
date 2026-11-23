<?php

namespace Tests\Feature;

use App\Display\SlideTypes\EmergencyContactsSlide;
use App\Enums\SocialPlatform;
use App\Models\EmergencyContact;
use App\Models\Notice;
use App\Models\Slide;
use App\Models\SocialLink;
use App\Settings\AudioSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisplayApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_settings_strings_and_slides(): void
    {
        config([
            'huifax.display.locale' => 'es-CL',
            'app.timezone' => 'America/Santiago',
        ]);

        $this->getJson(route('api.display'))
            ->assertOk()
            ->assertJsonPath('settings.locale', 'es-CL')
            ->assertJsonPath('settings.timezone', 'America/Santiago')
            ->assertJsonPath('settings.poll_interval', 30)
            ->assertJsonStructure(['settings', 'strings' => ['page_of'], 'slides']);
    }

    public function test_it_answers_not_modified_when_the_etag_matches(): void
    {
        Slide::factory()->ofType('notices')->create();
        Notice::factory()->create();

        $etag = $this->getJson(route('api.display'))->assertOk()->headers->get('ETag');

        $this->assertNotEmpty($etag);

        $this->getJson(route('api.display'), ['If-None-Match' => $etag])
            ->assertStatus(304)
            ->assertNoContent(304);
    }

    public function test_the_etag_changes_when_content_changes(): void
    {
        Slide::factory()->ofType('notices')->create();
        $notice = Notice::factory()->create(['title' => 'Before']);

        $before = $this->getJson(route('api.display'))->headers->get('ETag');
        $notice->update(['title' => 'After']);
        $after = $this->getJson(route('api.display'))->headers->get('ETag');

        $this->assertNotSame($before, $after);
    }

    public function test_disabled_slides_and_unknown_types_are_skipped(): void
    {
        Notice::factory()->create();
        Slide::factory()->ofType('notices')->disabled()->create();
        Slide::factory()->ofType('does_not_exist')->create();

        $this->getJson(route('api.display'))->assertJsonCount(0, 'slides');
    }

    public function test_slides_follow_sort_order(): void
    {
        Notice::factory()->create();
        EmergencyContact::factory()->create();
        Slide::factory()->ofType('emergency_contacts')->create(['sort_order' => 2]);
        Slide::factory()->ofType('notices')->create(['sort_order' => 1]);

        $this->getJson(route('api.display'))
            ->assertJsonPath('slides.0.type', 'notices')
            ->assertJsonPath('slides.1.type', 'emergency_contacts');
    }

    public function test_each_active_notice_is_its_own_page(): void
    {
        $this->travelTo(now()->setDate(2026, 11, 22)->setTime(12, 0));

        $slide = Slide::factory()->ofType('notices')->create();
        $first = Notice::factory()->create(['sort_order' => 1, 'duration' => 30]);
        $second = Notice::factory()->create(['sort_order' => 2]);
        Notice::factory()->disabled()->create();
        Notice::factory()->create(['starts_at' => now()->addDay()]);
        Notice::factory()->create(['ends_at' => now()->subMinute()]);

        $this->getJson(route('api.display'))
            ->assertJsonCount(2, 'slides')
            ->assertJsonPath('slides.0.id', "{$slide->id}:notice-{$first->id}")
            ->assertJsonPath('slides.0.duration', 30)
            ->assertJsonPath('slides.0.data.title', $first->title)
            ->assertJsonPath('slides.1.id', "{$slide->id}:notice-{$second->id}")
            ->assertJsonPath('slides.1.duration', 20);
    }

    public function test_a_slide_without_content_is_skipped(): void
    {
        Slide::factory()->ofType('emergency_contacts')->create();
        EmergencyContact::factory()->disabled()->create();

        $this->getJson(route('api.display'))->assertJsonCount(0, 'slides');
    }

    public function test_long_lists_are_split_into_pages(): void
    {
        Slide::factory()->ofType('emergency_contacts')->create(['duration' => 12]);
        EmergencyContact::factory()->count(EmergencyContactsSlide::DEFAULT_PER_PAGE + 1)->create();

        $this->getJson(route('api.display'))
            ->assertJsonCount(2, 'slides')
            ->assertJsonCount(EmergencyContactsSlide::DEFAULT_PER_PAGE, 'slides.0.data.contacts')
            ->assertJsonCount(1, 'slides.1.data.contacts')
            ->assertJsonPath('slides.0.duration', 12)
            ->assertJsonPath('slides.0.data.heading', __('display.slides.emergency_contacts.heading'));
    }

    public function test_slide_settings_override_heading_and_page_size(): void
    {
        Slide::factory()->ofType('social_links')->create(['settings' => ['heading' => 'Redes', 'per_page' => 1]]);
        SocialLink::factory()->create(['platform' => SocialPlatform::Instagram, 'handle' => '@a']);
        SocialLink::factory()->create(['platform' => SocialPlatform::Website, 'handle' => 'a.example']);

        $this->getJson(route('api.display'))
            ->assertJsonCount(2, 'slides')
            ->assertJsonPath('slides.0.data.heading', 'Redes')
            ->assertJsonPath('slides.0.data.links.0.platform', 'instagram')
            ->assertJsonPath('slides.1.data.links.0.platform_label', __('display.social_platforms.website'));
    }

    public function test_the_api_does_not_start_a_session(): void
    {
        $this->getJson(route('api.display'))->assertCookieMissing(config('session.cookie'));
    }

    public function test_the_payload_carries_the_audio_settings(): void
    {
        $audio = app(AudioSettings::class);
        $audio->enabled = true;
        $audio->stream_url = 'https://radio.example/stream';
        $audio->volume = 70;
        $audio->save();

        $this->getJson('/api/display')
            ->assertOk()
            ->assertJsonPath('settings.audio', ['enabled' => true, 'stream_url' => 'https://radio.example/stream', 'volume' => 70]);
    }
}

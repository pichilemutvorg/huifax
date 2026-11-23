<?php

namespace Tests\Feature\Schedule;

use App\Display\DisplayPayload;
use App\Display\SlideTypes\DailyScheduleSlide;
use App\Display\SlideTypes\UpcomingProgramsSlide;
use App\Enums\BroadcastTag;
use App\Models\Program;
use App\Models\ScheduleSlot;
use App\Models\Slide;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleSlidesTest extends TestCase
{
    use RefreshDatabase;

    private function at(string $moment): CarbonImmutable
    {
        return CarbonImmutable::parse($moment, config('app.timezone'));
    }

    public function test_upcoming_programs_sends_spare_airings_for_the_display_to_trim(): void
    {
        ScheduleSlot::factory()->on([1, 2, 3, 4, 5, 6, 7], '21:00')
            ->for(Program::factory()->state(['name' => 'News', 'description' => 'Daily news']))
            ->create(['tag' => BroadcastTag::Live]);
        $slide = Slide::factory()->create(['type' => 'upcoming_programs', 'settings' => ['count' => 2]]);

        [$page] = app(UpcomingProgramsSlide::class)->pages($slide, $this->at('2026-11-23 12:00'));

        $this->assertSame(2, $page->data['count']);
        $this->assertCount(2 + UpcomingProgramsSlide::SPARE, $page->data['airings']);
        $this->assertSame([
            'program' => 'News',
            'description' => 'Daily news',
            'starts_at' => '2026-11-23T21:00:00-03:00',
            'ends_at' => '2026-11-23T22:00:00-03:00',
            'tag' => 'live',
        ], $page->data['airings'][0]);
    }

    public function test_schedule_slides_are_skipped_when_nothing_airs(): void
    {
        $now = $this->at('2026-11-23 12:00');

        $this->assertSame([], app(UpcomingProgramsSlide::class)->pages(Slide::factory()->make(['type' => 'upcoming_programs']), $now));
        $this->assertSame([], app(DailyScheduleSlide::class)->pages(Slide::factory()->make(['type' => 'daily_schedule']), $now));
    }

    public function test_daily_schedule_shows_the_rest_of_today(): void
    {
        ScheduleSlot::factory()->on([1], '10:00')->create();
        ScheduleSlot::factory()->on([1], '21:00')->create();
        $slide = Slide::factory()->create(['type' => 'daily_schedule']);

        [$page] = app(DailyScheduleSlide::class)->pages($slide, $this->at('2026-11-23 12:00'));

        $this->assertSame('2026-11-23', $page->data['date']);
        $this->assertCount(1, $page->data['airings']);
    }

    public function test_daily_schedule_moves_to_tomorrow_once_today_is_over_and_paginates(): void
    {
        ScheduleSlot::factory()->on([1], '10:00')->create();
        foreach (['08:00', '09:00', '10:00'] as $time) {
            ScheduleSlot::factory()->on([2], $time)->create();
        }
        $slide = Slide::factory()->create(['type' => 'daily_schedule', 'settings' => ['per_page' => 2]]);

        $pages = app(DailyScheduleSlide::class)->pages($slide, $this->at('2026-11-23 22:00'));

        $this->assertCount(2, $pages);
        $this->assertSame('2026-11-24', $pages[0]->data['date']);
        $this->assertSame([2, 2], [$pages[0]->data['pages'], $pages[1]->data['page']]);
        $this->assertCount(1, $pages[1]->data['airings']);
    }

    public function test_schedule_slides_appear_in_the_display_payload(): void
    {
        ScheduleSlot::factory()->on([1, 2, 3, 4, 5, 6, 7], '21:00')->create();
        Slide::factory()->create(['type' => 'upcoming_programs']);
        Slide::factory()->create(['type' => 'daily_schedule']);

        $slides = app(DisplayPayload::class)->build($this->at('2026-11-23 12:00'))['slides'];

        $this->assertSame(['upcoming_programs', 'daily_schedule'], array_column($slides, 'type'));
    }
}

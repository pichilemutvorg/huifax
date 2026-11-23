<?php

namespace Tests\Feature\Schedule;

use App\Enums\BroadcastTag;
use App\Models\Program;
use App\Models\ScheduleException;
use App\Models\ScheduleSlot;
use App\Models\SpecialBroadcast;
use App\Schedule\Airing;
use App\Schedule\ScheduleResolver;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleResolverTest extends TestCase
{
    use RefreshDatabase;

    private function at(string $moment): CarbonImmutable
    {
        return CarbonImmutable::parse($moment, config('app.timezone'));
    }

    /**
     * @return list<string>
     */
    private function airings(string $from, string $to): array
    {
        return array_map(
            fn (Airing $airing) => "{$airing->startsAt->format('D H:i')} {$airing->program}",
            app(ScheduleResolver::class)->between($this->at($from), $this->at($to)),
        );
    }

    public function test_weekly_slots_air_on_their_weekdays_in_order(): void
    {
        // 2026-11-23 is a Monday.
        ScheduleSlot::factory()->on([1, 3], '21:00')->for(Program::factory()->state(['name' => 'News']))->create();
        ScheduleSlot::factory()->on([3], '18:30')->for(Program::factory()->state(['name' => 'Kids']))->create();

        $this->assertSame(
            ['Mon 21:00 News', 'Wed 18:30 Kids', 'Wed 21:00 News'],
            $this->airings('2026-11-23 00:00', '2026-11-30 00:00'),
        );
    }

    public function test_the_period_is_half_open_on_exact_times(): void
    {
        ScheduleSlot::factory()->on([1], '21:00')->for(Program::factory()->state(['name' => 'News']))->create();

        $this->assertSame(['Mon 21:00 News'], $this->airings('2026-11-23 21:00', '2026-11-23 22:00'));
        $this->assertSame([], $this->airings('2026-11-23 21:01', '2026-11-30 21:00'));
    }

    public function test_slots_only_air_within_their_season(): void
    {
        ScheduleSlot::factory()->on([1, 2, 3, 4, 5], '21:00')
            ->for(Program::factory()->state(['name' => 'News']))
            ->create(['valid_from' => '2026-11-24', 'valid_until' => '2026-11-25']);

        $this->assertSame(
            ['Tue 21:00 News', 'Wed 21:00 News'],
            $this->airings('2026-11-23 00:00', '2026-11-30 00:00'),
        );
    }

    public function test_exceptions_cancel_or_move_single_airings(): void
    {
        $slot = ScheduleSlot::factory()->on([1, 2, 3], '21:00')
            ->for(Program::factory()->state(['name' => 'News']))
            ->create(['tag' => BroadcastTag::Live]);

        ScheduleException::factory()->for($slot, 'slot')->create(['date' => '2026-11-23']);
        ScheduleException::factory()->for($slot, 'slot')->create(['date' => '2026-11-24', 'rescheduled_to' => '2026-11-24 23:15']);

        $airings = app(ScheduleResolver::class)->between($this->at('2026-11-23 00:00'), $this->at('2026-11-26 00:00'));

        $this->assertSame(['Tue 23:15', 'Wed 21:00'], array_map(fn (Airing $a) => $a->startsAt->format('D H:i'), $airings));
        $this->assertSame('2026-11-25 00:15', $airings[0]->endsAt->format('Y-m-d H:i'));
        $this->assertSame(BroadcastTag::Live, $airings[0]->tag);
    }

    public function test_an_airing_moved_into_the_period_from_outside_it_is_included(): void
    {
        $slot = ScheduleSlot::factory()->on([1], '21:00')->for(Program::factory()->state(['name' => 'News']))->create();
        ScheduleException::factory()->for($slot, 'slot')->create(['date' => '2026-11-23', 'rescheduled_to' => '2026-11-28 12:00']);

        $this->assertSame(['Sat 12:00 News'], $this->airings('2026-11-28 00:00', '2026-11-29 00:00'));
    }

    public function test_special_broadcasts_are_merged_in(): void
    {
        ScheduleSlot::factory()->on([6], '21:00')->for(Program::factory()->state(['name' => 'News']))->create();
        SpecialBroadcast::factory()->for(Program::factory()->state(['name' => 'Festival']))->create(['starts_at' => '2026-11-28 19:00']);

        $this->assertSame(
            ['Sat 19:00 Festival', 'Sat 21:00 News'],
            $this->airings('2026-11-28 00:00', '2026-11-29 00:00'),
        );
    }

    public function test_disabled_programs_and_slots_never_air(): void
    {
        $hidden = Program::factory()->disabled()->create();
        ScheduleSlot::factory()->on([6], '10:00')->for($hidden)->create();
        SpecialBroadcast::factory()->for($hidden)->create(['starts_at' => '2026-11-28 11:00']);
        ScheduleSlot::factory()->on([6], '12:00')->create(['is_enabled' => false]);

        $this->assertSame([], $this->airings('2026-11-28 00:00', '2026-11-29 00:00'));
    }

    public function test_local_times_hold_across_daylight_saving_changes(): void
    {
        // Chile moves clocks forward at midnight between 2026-09-05 and 2026-09-06.
        ScheduleSlot::factory()->on([6, 7], '21:00')->for(Program::factory()->state(['name' => 'News']))->create();

        $airings = app(ScheduleResolver::class)->between($this->at('2026-09-05 00:00'), $this->at('2026-09-07 00:00'));

        $this->assertSame(['21:00', '21:00'], array_map(fn (Airing $a) => $a->startsAt->format('H:i'), $airings));
        $this->assertNotSame($airings[0]->startsAt->getOffset(), $airings[1]->startsAt->getOffset());
    }

    public function test_upcoming_returns_the_next_airings_up_to_the_limit(): void
    {
        ScheduleSlot::factory()->on([1, 2, 3, 4, 5, 6, 7], '21:00')->create();

        $airings = app(ScheduleResolver::class)->upcoming($this->at('2026-11-23 21:30'), 3);

        $this->assertSame(
            ['2026-11-24', '2026-11-25', '2026-11-26'],
            array_map(fn (Airing $a) => $a->startsAt->toDateString(), $airings),
        );
    }
}

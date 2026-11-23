<?php

namespace App\Schedule;

use App\Models\ScheduleException;
use App\Models\ScheduleSlot;
use App\Models\SpecialBroadcast;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

/**
 * Turns the weekly grid, its exceptions and the special broadcasts into the
 * concrete airings of a period.
 */
class ScheduleResolver
{
    /**
     * Airings starting in [$from, $to), in chronological order. Disabled
     * programs and slots are left out.
     *
     * @return list<Airing>
     */
    public function between(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $timezone = config('app.timezone');
        $from = $from->setTimezone($timezone);
        $to = $to->setTimezone($timezone);

        $airings = [
            ...$this->regularAirings($from, $to),
            ...$this->rescheduledAirings($from, $to),
            ...$this->specialAirings($from, $to),
        ];

        usort($airings, fn (Airing $a, Airing $b) => [$a->startsAt, $a->program] <=> [$b->startsAt, $b->program]);

        return $airings;
    }

    /**
     * The next airings starting at or after $from, looking at most $days ahead.
     *
     * @return list<Airing>
     */
    public function upcoming(CarbonImmutable $from, int $limit, int $days = 7): array
    {
        return array_slice($this->between($from, $from->addDays($days)), 0, $limit);
    }

    /**
     * @return list<Airing>
     */
    private function regularAirings(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $slots = ScheduleSlot::query()
            ->enabled()
            ->whereHas('program', fn (Builder $query) => $query->enabled())
            ->where(fn (Builder $query) => $query->whereNull('valid_from')->orWhere('valid_from', '<=', $to->toDateString()))
            ->where(fn (Builder $query) => $query->whereNull('valid_until')->orWhere('valid_until', '>=', $from->toDateString()))
            ->with('program')
            ->get();

        // Regular airings that have been cancelled or moved elsewhere.
        $changed = ScheduleException::query()
            ->whereIn('schedule_slot_id', $slots->modelKeys())
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->get()
            ->map(fn (ScheduleException $exception) => "{$exception->schedule_slot_id}@{$exception->date->toDateString()}")
            ->flip();

        $airings = [];

        // Walk calendar dates, not 24-hour steps: days around DST changes are not 24 hours long.
        for ($date = $from->startOfDay(); $date->lt($to); $date = $date->addDay()->startOfDay()) {
            foreach ($slots as $slot) {
                if (! $slot->airsOn($date) || $changed->has("{$slot->id}@{$date->toDateString()}")) {
                    continue;
                }

                $startsAt = $slot->startOn($date);

                if ($startsAt->lt($from) || $startsAt->gte($to)) {
                    continue;
                }

                $airings[] = $this->airing($slot, $startsAt);
            }
        }

        return $airings;
    }

    /**
     * Regular airings moved into the period, wherever they came from.
     *
     * @return list<Airing>
     */
    private function rescheduledAirings(CarbonImmutable $from, CarbonImmutable $to): array
    {
        return ScheduleException::query()
            ->whereNotNull('rescheduled_to')
            ->where('rescheduled_to', '>=', $from)
            ->where('rescheduled_to', '<', $to)
            ->whereHas('slot', fn (Builder $query) => $query
                ->enabled()
                ->whereHas('program', fn (Builder $query) => $query->enabled()))
            ->with('slot.program')
            ->get()
            ->map(fn (ScheduleException $exception) => $this->airing($exception->slot, $exception->rescheduled_to))
            ->all();
    }

    /**
     * @return list<Airing>
     */
    private function specialAirings(CarbonImmutable $from, CarbonImmutable $to): array
    {
        return SpecialBroadcast::query()
            ->where('starts_at', '>=', $from)
            ->where('starts_at', '<', $to)
            ->whereHas('program', fn (Builder $query) => $query->enabled())
            ->with('program')
            ->get()
            ->map(fn (SpecialBroadcast $broadcast) => new Airing(
                program: $broadcast->program->name,
                description: $broadcast->program->description,
                startsAt: $broadcast->starts_at,
                endsAt: $broadcast->starts_at->addMinutes($broadcast->duration_minutes),
                tag: $broadcast->tag,
            ))
            ->all();
    }

    private function airing(ScheduleSlot $slot, CarbonImmutable $startsAt): Airing
    {
        return new Airing(
            program: $slot->program->name,
            description: $slot->program->description,
            startsAt: $startsAt,
            endsAt: $startsAt->addMinutes($slot->duration_minutes),
            tag: $slot->tag,
        );
    }
}

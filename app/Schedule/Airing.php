<?php

namespace App\Schedule;

use App\Enums\BroadcastTag;
use Carbon\CarbonImmutable;

/**
 * One concrete airing of a program, resolved from the weekly grid, its
 * exceptions and the special broadcasts.
 */
final readonly class Airing
{
    public function __construct(
        public string $program,
        public ?string $description,
        public CarbonImmutable $startsAt,
        public CarbonImmutable $endsAt,
        public ?BroadcastTag $tag,
    ) {}

    /**
     * Shape sent to the display. Times carry their UTC offset so the display
     * can compare them with its own clock and format them in the instance
     * timezone.
     *
     * @return array{program: string, description: string|null, starts_at: string, ends_at: string, tag: string|null}
     */
    public function toDisplay(): array
    {
        return [
            'program' => $this->program,
            'description' => $this->description,
            'starts_at' => $this->startsAt->toIso8601String(),
            'ends_at' => $this->endsAt->toIso8601String(),
            'tag' => $this->tag?->value,
        ];
    }
}

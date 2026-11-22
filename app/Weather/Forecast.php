<?php

namespace App\Weather;

use Carbon\CarbonImmutable;

/**
 * Provider-independent forecast for one location.
 *
 * Each day has up to four periods (dawn, morning, afternoon, night). A day
 * may instead carry only a `summary`, e.g. "rest of today" forecasts.
 * Icons are provider condition codes, resolved to URLs by the provider.
 */
final readonly class Forecast
{
    public const array PERIODS = ['dawn', 'morning', 'afternoon', 'night'];

    /**
     * @param  list<array{date: string, min: ?int, max: ?int, summary: ?array{icon: ?string, text: string}, periods: list<array{period: string, icon: ?string, text: string}>}>  $days
     */
    public function __construct(
        public string $location,
        public string $name,
        public ?CarbonImmutable $issuedAt,
        public array $days,
    ) {}

    /**
     * Days from the given date onwards, so a stale forecast never shows the past.
     *
     * @return list<array{date: string, min: ?int, max: ?int, summary: ?array{icon: ?string, text: string}, periods: list<array{period: string, icon: ?string, text: string}>}>
     */
    public function daysFrom(string $date): array
    {
        return array_values(array_filter($this->days, fn (array $day) => $day['date'] >= $date));
    }

    /**
     * Condition codes used anywhere in the forecast.
     *
     * @return list<string>
     */
    public function icons(): array
    {
        $icons = [];

        foreach ($this->days as $day) {
            $icons[] = $day['summary']['icon'] ?? null;

            foreach ($day['periods'] as $period) {
                $icons[] = $period['icon'];
            }
        }

        return array_values(array_unique(array_filter($icons)));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'location' => $this->location,
            'name' => $this->name,
            'issued_at' => $this->issuedAt?->toIso8601String(),
            'days' => $this->days,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            location: $data['location'],
            name: $data['name'],
            issuedAt: isset($data['issued_at']) ? CarbonImmutable::parse($data['issued_at']) : null,
            days: $data['days'] ?? [],
        );
    }
}

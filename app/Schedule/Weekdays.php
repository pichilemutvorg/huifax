<?php

namespace App\Schedule;

use Carbon\CarbonImmutable;

/**
 * Localized weekday names keyed by ISO-8601 number (1 = Monday … 7 = Sunday).
 */
final class Weekdays
{
    /**
     * @return array<int, string>
     */
    public static function names(bool $short = false): array
    {
        $monday = CarbonImmutable::now()->locale(app()->getLocale())->startOfWeek(CarbonImmutable::MONDAY);
        $names = [];

        foreach (range(1, 7) as $day) {
            $names[$day] = $monday->addDays($day - 1)->isoFormat($short ? 'ddd' : 'dddd');
        }

        return $names;
    }

    /**
     * Compact list such as "lun., mié., vie." in week order.
     *
     * @param  list<int|string>  $days
     */
    public static function summary(array $days): string
    {
        $names = self::names(short: true);
        $days = array_map('intval', $days);
        sort($days);

        return implode(', ', array_map(fn (int $day) => $names[$day] ?? (string) $day, $days));
    }
}

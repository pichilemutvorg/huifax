<?php

namespace App\Display;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;

/**
 * Remembers when a display last asked for content, so the admin panel can
 * tell whether the screen is still connected.
 */
class DisplayHeartbeat
{
    private const string KEY = 'huifax.display.last_seen';

    /** Writes at most this often, to keep polling cheap. */
    private const int RESOLUTION_SECONDS = 15;

    public function record(): void
    {
        $last = $this->lastSeen();

        if ($last === null || $last->diffInSeconds(CarbonImmutable::now()) >= self::RESOLUTION_SECONDS) {
            Cache::forever(self::KEY, CarbonImmutable::now()->getTimestamp());
        }
    }

    public function lastSeen(): ?CarbonImmutable
    {
        $timestamp = Cache::get(self::KEY);

        return is_int($timestamp) ? CarbonImmutable::createFromTimestamp($timestamp, config('app.timezone')) : null;
    }
}

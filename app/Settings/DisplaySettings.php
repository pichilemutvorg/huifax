<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class DisplaySettings extends Settings
{
    /** Seconds between checks for new content. */
    public int $poll_interval;

    /** Daily safety reload as HH:MM in app.timezone, or null to disable it. */
    public ?string $reload_at;

    /** Seconds each background stays on screen in the general rotation. */
    public int $background_interval;

    public static function group(): string
    {
        return 'display';
    }
}

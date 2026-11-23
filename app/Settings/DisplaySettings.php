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

    /** Visual theme of the display, one of huifax.themes. */
    public string $theme;

    /**
     * The configured theme, or the default one if it is no longer available.
     */
    public function resolvedTheme(): string
    {
        $themes = config('huifax.themes', []);

        return in_array($this->theme, $themes, true) ? $this->theme : ($themes[0] ?? 'huifax');
    }

    public static function group(): string
    {
        return 'display';
    }
}

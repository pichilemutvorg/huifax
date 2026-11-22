<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class AudioSettings extends Settings
{
    /** Play the stream from the page. Off by default: the playout system usually handles audio. */
    public bool $enabled;

    public ?string $stream_url;

    /** 0–100. */
    public int $volume;

    public static function group(): string
    {
        return 'audio';
    }
}

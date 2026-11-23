<?php

use App\Display\SlideTypes\EmergencyContactsSlide;
use App\Display\SlideTypes\FeaturedProgramsSlide;
use App\Display\SlideTypes\NoticesSlide;
use App\Display\SlideTypes\SocialLinksSlide;
use App\Display\SlideTypes\WeatherSlide;
use App\Weather\Providers\MeteochileProvider;

return [

    /*
    |--------------------------------------------------------------------------
    | Display
    |--------------------------------------------------------------------------
    |
    | Deployment-level settings for the on-air display. `poll_interval` and
    | `reload_at` (and the branding below) are only initial values: once the
    | database is migrated they are edited from the admin panel.
    |
    */

    'display' => [
        // BCP 47 tag used by the display for <html lang> and Intl formatting.
        // The timezone comes from app.timezone (APP_TIMEZONE).
        'locale' => env('HUIFAX_DISPLAY_LOCALE', 'es-CL'),

        // Seconds between checks for new content.
        'poll_interval' => (int) env('HUIFAX_POLL_INTERVAL', 30),

        // Daily safety reload of the display page, as HH:MM in app.timezone.
        // Set to an empty value to disable it.
        'reload_at' => env('HUIFAX_RELOAD_AT', '05:00') ?: null,

        // Initial visual theme, one of `themes` below.
        'theme' => env('HUIFAX_THEME', 'huifax'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Themes
    |--------------------------------------------------------------------------
    |
    | Visual themes the display can use; the first one is the default. Each
    | key must match a stylesheet in resources/css/themes (data-theme).
    |
    */

    'themes' => ['huifax', 'metro', 'apple', 'material', 'vercel', 'shadcn', 'bbc', 'anthropic'],

    /*
    |--------------------------------------------------------------------------
    | Uploads
    |--------------------------------------------------------------------------
    |
    | `max_size` (KB) caps every upload from the admin panel. PHP must accept
    | at least as much: see upload_max_filesize and post_max_size in
    | public/.user.ini (and the web server's request body limit).
    |
    | Photos are re-encoded as WebP on upload: scaled down until they just
    | cover `photo_width` × `photo_height` (the display stage) and stripped of
    | metadata such as GPS location.
    |
    */

    'uploads' => [
        'max_size' => (int) env('HUIFAX_UPLOAD_MAX_SIZE', 20480),
        'photo_width' => 1920,
        'photo_height' => 1080,
        'photo_quality' => (int) env('HUIFAX_PHOTO_QUALITY', 82),
    ],

    /*
    |--------------------------------------------------------------------------
    | Slide types
    |--------------------------------------------------------------------------
    |
    | Slide types available in this installation. Each one needs a matching
    | Vue component registered in resources/js/display/slides/index.js.
    |
    */

    'slide_types' => [
        EmergencyContactsSlide::class,
        SocialLinksSlide::class,
        NoticesSlide::class,
        WeatherSlide::class,
        FeaturedProgramsSlide::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Weather
    |--------------------------------------------------------------------------
    |
    | Source for weather slides (a class implementing App\Weather\WeatherProvider).
    | Forecasts are fetched by `php artisan huifax:weather`, scheduled every
    | 30 minutes.
    |
    */

    'weather' => [
        'provider' => MeteochileProvider::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Source code
    |--------------------------------------------------------------------------
    |
    | HUIFAX is licensed under the AGPL-3.0-or-later. Section 13 requires that
    | users interacting with a modified version over a network can get its
    | source. If you run a modified version, point this to your own repository.
    |
    */

    'source_url' => env('HUIFAX_SOURCE_URL', 'https://github.com/pichilemutvorg/huifax'),

    // Initial branding, copied into the panel settings on first migration.
    'branding' => [
        'title' => env('HUIFAX_TITLE', 'HUIFAX'),
        'tagline' => env('HUIFAX_TAGLINE'),
    ],

];

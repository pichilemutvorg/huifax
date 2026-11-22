<?php

namespace App\Weather;

use App\Display\SlideTypes\WeatherSlide;
use App\Models\Slide;
use App\Models\WeatherForecast;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Fetches forecasts for the locations used by weather slides and keeps the
 * last valid one per location, plus a local copy of the condition icons
 * (the display never loads anything from the provider directly).
 */
class WeatherUpdater
{
    public const string ICON_DISK = 'public';

    private const int MAX_ICON_BYTES = 512 * 1024;

    public function __construct(private WeatherProvider $provider) {}

    /**
     * @return int Number of locations updated.
     *
     * @throws WeatherUnavailable
     */
    public function update(): int
    {
        $locations = $this->locations();

        if ($locations === []) {
            return 0;
        }

        $forecasts = $this->provider->forecasts($locations);

        foreach ($forecasts as $location => $forecast) {
            WeatherForecast::query()->updateOrCreate(
                ['provider' => $this->provider->key(), 'location' => $location],
                ['data' => $forecast->toArray(), 'fetched_at' => now()],
            );

            $this->storeIcons($forecast);
        }

        return count($forecasts);
    }

    /**
     * Like update(), but reports failures instead of throwing them.
     */
    public function updateQuietly(): void
    {
        try {
            $this->update();
        } catch (Throwable $e) {
            report($e);
        }
    }

    public function forecastFor(string $location): ?WeatherForecast
    {
        return WeatherForecast::query()
            ->where('provider', $this->provider->key())
            ->where('location', $location)
            ->first();
    }

    /**
     * Public URL of the local copy of an icon, or null if it is not stored.
     */
    public function iconUrl(?string $icon): ?string
    {
        if ($icon === null) {
            return null;
        }

        $path = $this->iconPath($icon);

        return Storage::disk(self::ICON_DISK)->exists($path) ? Storage::disk(self::ICON_DISK)->url($path) : null;
    }

    public function attribution(): string
    {
        return $this->provider->attribution();
    }

    /**
     * Locations configured in weather slides.
     *
     * @return list<string>
     */
    private function locations(): array
    {
        return Slide::query()
            ->where('type', WeatherSlide::KEY)
            ->get()
            ->map(fn (Slide $slide) => $slide->setting('location'))
            ->filter(fn ($location) => is_string($location) && $location !== '')
            ->unique()
            ->values()
            ->all();
    }

    private function storeIcons(Forecast $forecast): void
    {
        $disk = Storage::disk(self::ICON_DISK);

        foreach ($forecast->icons() as $icon) {
            $path = $this->iconPath($icon);

            if ($disk->exists($path)) {
                continue;
            }

            try {
                $response = Http::timeout(10)->get($this->provider->iconUrl($icon));
            } catch (Throwable $e) {
                report($e);

                continue;
            }

            $body = $response->body();

            if ($response->successful() && str_starts_with((string) $response->header('Content-Type'), 'image/')
                && strlen($body) <= self::MAX_ICON_BYTES) {
                $disk->put($path, $body);
            }
        }
    }

    private function iconPath(string $icon): string
    {
        return 'weather-icons/'.$this->provider->key().'/'.basename($icon);
    }
}

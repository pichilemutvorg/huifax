<?php

namespace App\Display\SlideTypes;

use App\Display\SlidePage;
use App\Models\Slide;
use App\Weather\WeatherProvider;
use App\Weather\WeatherUpdater;
use Carbon\CarbonImmutable;
use Filament\Forms\Components\Select;
use Throwable;

/**
 * Forecast for one location: today and the following days.
 */
class WeatherSlide extends SlideType
{
    public const string KEY = 'weather';

    /** Days shown: today plus the next four. */
    public const int DAYS = 5;

    /** Forecasts older than this are not shown. */
    public const int MAX_AGE_HOURS = 72;

    public function __construct(
        private WeatherUpdater $weather,
        private WeatherProvider $provider,
    ) {}

    public function key(): string
    {
        return self::KEY;
    }

    public function defaultDuration(): int
    {
        return 20;
    }

    public function settingsFields(): array
    {
        return [
            Select::make('settings.location')
                ->label(__('admin.slides.fields.location'))
                ->helperText(__('admin.slides.help.location'))
                ->options(fn (?string $state): array => $this->locationOptions($state))
                ->searchable()
                ->required(),
            ...parent::settingsFields(),
        ];
    }

    public function pages(Slide $slide, CarbonImmutable $now): array
    {
        $location = $slide->setting('location');
        $stored = is_string($location) ? $this->weather->forecastFor($location) : null;

        if ($stored === null || $stored->fetched_at->lt($now->subHours(self::MAX_AGE_HOURS))) {
            return [];
        }

        $forecast = $stored->forecast();
        $days = array_slice($forecast->daysFrom($now->toDateString()), 0, self::DAYS);

        if ($days === []) {
            return [];
        }

        return [new SlidePage(
            key: 'forecast',
            data: [
                'heading' => $slide->setting('heading') ?: __('display.slides.weather.heading'),
                'location' => $forecast->name,
                'days' => array_map(fn (array $day) => $this->withIconUrls($day), $days),
                'attribution' => $this->weather->attribution(),
            ],
        )];
    }

    /**
     * @param  array<string, mixed>  $day
     * @return array<string, mixed>
     */
    private function withIconUrls(array $day): array
    {
        if ($day['summary'] !== null) {
            $day['summary']['icon'] = $this->weather->iconUrl($day['summary']['icon']);
        }

        $day['periods'] = array_map(
            fn (array $period) => [...$period, 'icon' => $this->weather->iconUrl($period['icon'])],
            $day['periods'],
        );

        return $day;
    }

    /**
     * @return array<string, string>
     */
    private function locationOptions(?string $current): array
    {
        try {
            return $this->provider->locations();
        } catch (Throwable $e) {
            report($e);

            // Source down: keep the current value selectable.
            return $current ? [$current => $current] : [];
        }
    }
}

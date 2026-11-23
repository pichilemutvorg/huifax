<?php

namespace App\Filament\Widgets;

use App\Display\DisplayHeartbeat;
use App\Settings\DisplaySettings;
use App\Weather\WeatherUpdater;
use Carbon\CarbonImmutable;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Dashboard health summary: is the display connected, and are the external
 * data sources up to date.
 */
class SystemStatus extends StatsOverviewWidget
{
    /** Forecasts older than this are flagged: fetches run every 30 minutes. */
    private const int WEATHER_STALE_HOURS = 2;

    protected static ?int $sort = -1;

    protected ?string $pollingInterval = '30s';

    protected function getHeading(): ?string
    {
        return __('admin.status.heading');
    }

    /**
     * @return list<Stat>
     */
    protected function getStats(): array
    {
        return [$this->displayStat(), ...$this->weatherStats()];
    }

    private function displayStat(): Stat
    {
        $lastSeen = app(DisplayHeartbeat::class)->lastSeen();
        // Three missed polls in a row means the display is not reaching the server.
        $connected = $lastSeen !== null
            && $lastSeen->diffInSeconds(CarbonImmutable::now()) <= 3 * app(DisplaySettings::class)->poll_interval + 15;

        return Stat::make(__('admin.status.display'), $connected ? __('admin.status.connected') : __('admin.status.disconnected'))
            ->description($lastSeen
                ? __('admin.status.last_seen', ['time' => $lastSeen->diffForHumans()])
                : __('admin.status.never_seen'))
            ->descriptionIcon(Heroicon::OutlinedTv)
            ->color($connected ? 'success' : 'danger');
    }

    /**
     * @return list<Stat>
     */
    private function weatherStats(): array
    {
        $weather = app(WeatherUpdater::class);

        return array_map(function (string $location) use ($weather): Stat {
            $forecast = $weather->forecastFor($location);
            $age = $forecast?->fetched_at->diffInHours(CarbonImmutable::now());

            return Stat::make(
                __('admin.status.weather', ['location' => $forecast?->forecast()->name ?? $location]),
                $forecast ? __('admin.status.updated', ['time' => $forecast->fetched_at->diffForHumans()]) : __('admin.status.no_data'),
            )
                ->descriptionIcon(Heroicon::OutlinedCloud)
                ->description(__('admin.status.weather_help'))
                ->color(match (true) {
                    $forecast === null => 'danger',
                    $age >= self::WEATHER_STALE_HOURS => 'warning',
                    default => 'success',
                });
        }, $weather->locations());
    }
}

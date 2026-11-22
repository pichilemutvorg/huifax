<?php

namespace App\Console\Commands;

use App\Weather\WeatherUnavailable;
use App\Weather\WeatherUpdater;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('huifax:weather')]
#[Description('Fetch weather forecasts for the locations used by weather slides')]
class FetchWeatherCommand extends Command
{
    public function handle(WeatherUpdater $weather): int
    {
        try {
            $count = $weather->update();
        } catch (WeatherUnavailable $e) {
            // The last valid forecast stays on screen; try again on the next run.
            $this->components->warn($e->getMessage());

            return self::FAILURE;
        }

        $this->components->info("Updated {$count} location(s).");

        return self::SUCCESS;
    }
}

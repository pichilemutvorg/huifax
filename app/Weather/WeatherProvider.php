<?php

namespace App\Weather;

/**
 * A weather data source. Instances outside Chile can add their own provider
 * and select it in config/huifax.php.
 */
interface WeatherProvider
{
    /**
     * Identifier stored with each forecast.
     */
    public function key(): string;

    /**
     * Locations available from this provider, as [key => display name].
     *
     * @return array<string, string>
     *
     * @throws WeatherUnavailable
     */
    public function locations(): array;

    /**
     * Fetch forecasts for the given locations. Locations missing from the
     * source are left out of the result.
     *
     * @param  list<string>  $locations
     * @return array<string, Forecast>
     *
     * @throws WeatherUnavailable When the source cannot be reached or parsed.
     */
    public function forecasts(array $locations): array;

    /**
     * Remote URL of a condition icon.
     */
    public function iconUrl(string $icon): string;

    /**
     * Attribution shown on screen with the forecast.
     */
    public function attribution(): string;
}

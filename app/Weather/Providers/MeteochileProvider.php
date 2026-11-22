<?php

namespace App\Weather\Providers;

use App\Weather\Forecast;
use App\Weather\WeatherProvider;
use App\Weather\WeatherUnavailable;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Public forecast feed of the Dirección Meteorológica de Chile.
 *
 * The feed lists ~100 locations. For each one, `fechasql` is the date of the
 * first day, days are consecutive, `temperatura` holds "min/max" and `icono` /
 * `texto` hold four periods per day (dawn, morning, afternoon, night). The
 * optional "resto del día" fields describe the rest of the day before
 * `fechasql`. Texts contain HTML entities.
 */
class MeteochileProvider implements WeatherProvider
{
    public const string FEED_URL = 'https://archivos.meteochile.gob.cl/portaldmc/meteochile/js/pronostico.json';

    public const string ICON_BASE_URL = 'https://archivos.meteochile.gob.cl/portaldmc/localJS/img/clima_dark/';

    /** Only plain file names are accepted as icon codes. */
    private const string ICON_PATTERN = '/^[a-z0-9_]+\.png$/';

    public function key(): string
    {
        return 'meteochile';
    }

    public function locations(): array
    {
        return Cache::remember('weather:meteochile:locations', now()->addDay(), function (): array {
            $locations = [];

            foreach ($this->feed() as $entry) {
                if (isset($entry['indice'], $entry['ciudad'])) {
                    $locations[(string) $entry['indice']] = $this->text($entry['ciudad']);
                }
            }

            asort($locations);

            return $locations;
        });
    }

    public function forecasts(array $locations): array
    {
        $forecasts = [];

        foreach ($this->feed() as $entry) {
            $key = $entry['indice'] ?? null;

            if (! in_array($key, $locations, true)) {
                continue;
            }

            $forecast = $this->parse($entry);

            if ($forecast !== null) {
                $forecasts[$key] = $forecast;
            }
        }

        return $forecasts;
    }

    public function iconUrl(string $icon): string
    {
        return self::ICON_BASE_URL.$icon;
    }

    public function attribution(): string
    {
        return __('display.weather.attribution.meteochile');
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function feed(): array
    {
        try {
            $response = Http::timeout(15)->retry(2, 1000, throw: false)->acceptJson()->get(self::FEED_URL);
        } catch (ConnectionException $e) {
            throw new WeatherUnavailable('Meteochile is unreachable.', previous: $e);
        }

        if (! $response->successful()) {
            throw new WeatherUnavailable("Meteochile answered HTTP {$response->status()}.");
        }

        $entries = $response->json('Pronostico');

        if (! is_array($entries) || ! array_is_list($entries) || $entries === []) {
            throw new WeatherUnavailable('Meteochile returned an unexpected payload.');
        }

        return array_values(array_filter($entries, 'is_array'));
    }

    /**
     * @param  array<string, mixed>  $entry
     */
    private function parse(array $entry): ?Forecast
    {
        try {
            $first = CarbonImmutable::createFromFormat('!Y-m-d', (string) ($entry['fechasql'] ?? ''));
        } catch (\Throwable) {
            return null;
        }

        if ($first === null) {
            return null;
        }

        $days = [];

        $restText = $this->text($entry['texto_resto_dia'] ?? '');
        if ($restText !== '') {
            $days[] = [
                'date' => $first->subDay()->toDateString(),
                'min' => null,
                'max' => null,
                'summary' => ['icon' => $this->icon($entry['icono_resto_dia'] ?? null), 'text' => $restText],
                'periods' => [],
            ];
        }

        $count = min((int) ($entry['tope'] ?? 0), count($entry['icono'] ?? []), count($entry['texto'] ?? []));

        for ($i = 0; $i < $count; $i++) {
            $periods = [];

            foreach (Forecast::PERIODS as $index => $period) {
                $text = $this->text($entry['texto'][$i][$index] ?? '');
                $icon = $this->icon($entry['icono'][$i][$index] ?? null);

                if ($text !== '' || $icon !== null) {
                    $periods[] = ['period' => $period, 'icon' => $icon, 'text' => $text];
                }
            }

            if ($periods === []) {
                continue;
            }

            [$min, $max] = $this->temperatures($entry['temperatura'][$i] ?? null);

            $days[] = [
                'date' => $first->addDays($i)->toDateString(),
                'min' => $min,
                'max' => $max,
                'summary' => null,
                'periods' => $periods,
            ];
        }

        if ($days === []) {
            return null;
        }

        return new Forecast(
            location: (string) $entry['indice'],
            name: $this->text($entry['ciudad'] ?? $entry['indice']),
            issuedAt: $this->issuedAt($entry['fechasqlredaccion'] ?? null),
            days: $days,
        );
    }

    /**
     * @return array{0: ?int, 1: ?int}
     */
    private function temperatures(mixed $value): array
    {
        if (! is_string($value) || ! preg_match('/^\s*(-?\d+)\s*\/\s*(-?\d+)\s*$/', $value, $matches)) {
            return [null, null];
        }

        return [(int) $matches[1], (int) $matches[2]];
    }

    private function icon(mixed $value): ?string
    {
        return is_string($value) && preg_match(self::ICON_PATTERN, $value) ? $value : null;
    }

    private function text(mixed $value): string
    {
        return is_string($value) ? trim(html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8')) : '';
    }

    private function issuedAt(mixed $value): ?CarbonImmutable
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            return CarbonImmutable::createFromFormat('Y-m-d H:i:s', $value, 'America/Santiago');
        } catch (\Throwable) {
            return null;
        }
    }
}

<?php

namespace Tests\Feature\Weather;

use App\Weather\Providers\MeteochileProvider;
use App\Weather\WeatherUnavailable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MeteochileProviderTest extends TestCase
{
    private function fakeFeed(mixed $body, int $status = 200): void
    {
        Http::fake([MeteochileProvider::FEED_URL => Http::response($body, $status)]);
    }

    private function fixture(): string
    {
        return file_get_contents(base_path('tests/Fixtures/meteochile/pronostico.json'));
    }

    public function test_it_parses_a_forecast(): void
    {
        $this->fakeFeed($this->fixture());

        $forecast = (new MeteochileProvider)->forecasts(['pichilemu'])['pichilemu'];

        $this->assertSame('Pichilemu', $forecast->name);
        $this->assertSame('2026-11-21 16:56', $forecast->issuedAt->format('Y-m-d H:i'));

        // Rest of the day before `fechasql`, with entities decoded.
        $rest = $forecast->days[0];
        $this->assertSame('2026-11-21', $rest['date']);
        $this->assertSame('Despejado con viento entre 25 y 40 km/h', $rest['summary']['text']);
        $this->assertSame('viento3.png', $rest['summary']['icon']);
        $this->assertSame([], $rest['periods']);

        $first = $forecast->days[1];
        $this->assertSame('2026-11-22', $first['date']);
        $this->assertSame(12, $first['min']);
        $this->assertSame(20, $first['max']);
        $this->assertSame(['dawn', 'morning', 'afternoon', 'night'], array_column($first['periods'], 'period'));
        $this->assertSame('despejadonoche.png', $first['periods'][0]['icon']);

        $this->assertCount(6, $forecast->days);
        $this->assertSame('2026-11-26', $forecast->days[5]['date']);
    }

    public function test_it_handles_seven_day_forecasts_and_skips_empty_days(): void
    {
        $this->fakeFeed($this->fixture());

        $forecast = (new MeteochileProvider)->forecasts(['valpo'])['valpo'];
        $withPeriods = array_filter($forecast->days, fn ($day) => $day['periods'] !== []);

        $this->assertGreaterThanOrEqual(5, count($withPeriods));
        foreach ($forecast->days as $day) {
            $this->assertTrue($day['periods'] !== [] || $day['summary'] !== null);
        }
    }

    public function test_unknown_locations_are_left_out(): void
    {
        $this->fakeFeed($this->fixture());

        $this->assertSame([], (new MeteochileProvider)->forecasts(['atlantis']));
    }

    public function test_it_lists_locations_sorted_by_name(): void
    {
        $this->fakeFeed($this->fixture());

        $locations = (new MeteochileProvider)->locations();

        $this->assertSame('Pichilemu', $locations['pichilemu']);
        $this->assertSame(array_values($locations), collect($locations)->sort()->values()->all());
    }

    public function test_malformed_entries_are_ignored(): void
    {
        $this->fakeFeed(['Pronostico' => [
            ['indice' => 'broken', 'ciudad' => 'Rota', 'fechasql' => 'not-a-date', 'tope' => 1],
            ['indice' => 'empty', 'ciudad' => 'Vacía', 'fechasql' => '2026-11-22', 'tope' => 1, 'icono' => [['']], 'texto' => [['']]],
            ['indice' => 'evil', 'ciudad' => 'Mala', 'fechasql' => '2026-11-22', 'tope' => 1,
                'icono' => [['../../etc/passwd', '', '', '']], 'texto' => [['Despejado', '', '', '']], 'temperatura' => ['x']],
        ]]);

        $forecasts = (new MeteochileProvider)->forecasts(['broken', 'empty', 'evil']);

        $this->assertSame(['evil'], array_keys($forecasts));
        $day = $forecasts['evil']->days[0];
        $this->assertNull($day['periods'][0]['icon']);
        $this->assertNull($day['min']);
    }

    /** @return array<string, array{mixed, int}> */
    public static function unusableResponses(): array
    {
        return [
            'server error' => ['', 500],
            'not json' => ['<html>maintenance</html>', 200],
            'empty list' => [['Pronostico' => []], 200],
            'wrong shape' => [['Pronostico' => ['a' => 1]], 200],
        ];
    }

    #[DataProvider('unusableResponses')]
    public function test_unusable_responses_throw(mixed $body, int $status): void
    {
        $this->fakeFeed($body, $status);

        $this->expectException(WeatherUnavailable::class);

        (new MeteochileProvider)->forecasts(['pichilemu']);
    }

    public function test_network_errors_throw(): void
    {
        Http::fake(fn () => throw new ConnectionException('timeout'));

        $this->expectException(WeatherUnavailable::class);

        (new MeteochileProvider)->forecasts(['pichilemu']);
    }
}

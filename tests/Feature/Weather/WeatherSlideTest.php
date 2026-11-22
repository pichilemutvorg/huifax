<?php

namespace Tests\Feature\Weather;

use App\Models\Slide;
use App\Models\WeatherForecast;
use App\Weather\Providers\MeteochileProvider;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WeatherSlideTest extends TestCase
{
    use RefreshDatabase;

    /** Response the fake feed returns; null serves the fixture. */
    private ?PromiseInterface $feedResponse = null;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Http::fake(function (Request $request) {
            if (str_starts_with($request->url(), MeteochileProvider::ICON_BASE_URL)) {
                return Http::response('png-bytes', 200, ['Content-Type' => 'image/png']);
            }

            return $this->feedResponse
                ?? Http::response(file_get_contents(base_path('tests/Fixtures/meteochile/pronostico.json')));
        });
    }

    private function weatherSlide(string $location = 'pichilemu'): Slide
    {
        return Slide::factory()->ofType('weather')->create(['settings' => ['location' => $location]]);
    }

    public function test_the_command_stores_forecasts_and_icons_for_configured_locations(): void
    {
        $this->weatherSlide('pichilemu');
        $this->weatherSlide('arica');

        $this->artisan('huifax:weather')->assertSuccessful();

        $this->assertSame(['arica', 'pichilemu'], WeatherForecast::query()->orderBy('location')->pluck('location')->all());
        Storage::disk('public')->assertExists('weather-icons/meteochile/despejado.png');
    }

    public function test_a_failed_fetch_keeps_the_last_valid_forecast(): void
    {
        $this->weatherSlide();
        $this->artisan('huifax:weather')->assertSuccessful();
        $before = WeatherForecast::query()->sole()->data;

        $this->feedResponse = Http::response('', 503);
        $this->artisan('huifax:weather')->assertFailed();

        $this->assertSame($before, WeatherForecast::query()->sole()->data);
    }

    public function test_the_display_shows_today_onwards(): void
    {
        $this->travelTo(now()->setDate(2026, 11, 22)->setTime(9, 0));
        $slide = $this->weatherSlide();
        $this->artisan('huifax:weather');

        $this->getJson(route('api.display'))
            ->assertJsonPath('slides.0.id', "{$slide->id}:forecast")
            ->assertJsonPath('slides.0.type', 'weather')
            ->assertJsonPath('slides.0.data.location', 'Pichilemu')
            ->assertJsonPath('slides.0.data.days.0.date', '2026-11-22')
            ->assertJsonCount(5, 'slides.0.data.days')
            ->assertJsonPath('slides.0.data.days.0.periods.1.icon', Storage::disk('public')->url('weather-icons/meteochile/despejado.png'))
            ->assertJsonPath('slides.0.data.attribution', __('display.weather.attribution.meteochile'));
    }

    public function test_an_outdated_forecast_is_not_shown(): void
    {
        $this->travelTo(now()->setDate(2026, 11, 22)->setTime(9, 0));
        $this->weatherSlide();
        $this->artisan('huifax:weather');

        // All forecast days are in the past.
        $this->travelTo(now()->setDate(2026, 12, 1));
        $this->getJson(route('api.display'))->assertJsonCount(0, 'slides');
    }

    public function test_a_forecast_fetched_too_long_ago_is_not_shown(): void
    {
        $this->travelTo(now()->setDate(2026, 11, 22)->setTime(9, 0));
        $this->weatherSlide();
        $this->artisan('huifax:weather');

        WeatherForecast::query()->update(['fetched_at' => now()->subDays(4)]);

        $this->getJson(route('api.display'))->assertJsonCount(0, 'slides');
    }

    public function test_a_slide_without_forecast_is_skipped(): void
    {
        $this->weatherSlide('atlantis');

        $this->getJson(route('api.display'))->assertJsonCount(0, 'slides');
    }

    public function test_icons_are_not_downloaded_twice(): void
    {
        $this->weatherSlide();
        $this->artisan('huifax:weather');
        $this->artisan('huifax:weather');

        $iconRequests = collect(Http::recorded())
            ->filter(fn ($pair) => str_ends_with($pair[0]->url(), '/despejado.png'));

        $this->assertCount(1, $iconRequests);
    }
}

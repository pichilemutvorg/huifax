<?php

namespace App\Providers;

use App\Display\SlideTypeRegistry;
use App\Display\SlideTypes\WeatherSlide;
use App\Media\PhotoOptimizer;
use App\Models\Slide;
use App\Weather\WeatherProvider;
use App\Weather\WeatherUpdater;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SlideTypeRegistry::class, fn ($app) => new SlideTypeRegistry(
            array_map(fn (string $class) => $app->make($class), config('huifax.slide_types', [])),
        ));

        $this->app->bind(PhotoOptimizer::class, fn () => new PhotoOptimizer(
            width: config('huifax.uploads.photo_width'),
            height: config('huifax.uploads.photo_height'),
            quality: config('huifax.uploads.photo_quality'),
        ));

        $this->app->bind(WeatherProvider::class, fn ($app) => $app->make(config('huifax.weather.provider')));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Livewire validates temporary uploads on its own (12 MB by default);
        // keep it in line with the upload size allowed by the panel.
        config(['livewire.temporary_file_upload.rules' => [
            'required', 'file', 'max:'.config('huifax.uploads.max_size'),
        ]]);

        // A new or edited weather slide gets its forecast right away instead
        // of waiting for the next scheduled fetch.
        Slide::saved(function (Slide $slide): void {
            if ($slide->type === WeatherSlide::KEY) {
                dispatch(fn () => app(WeatherUpdater::class)->updateQuietly())->afterResponse();
            }
        });
    }
}

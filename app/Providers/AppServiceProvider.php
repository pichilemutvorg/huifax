<?php

namespace App\Providers;

use App\Display\SlideTypeRegistry;
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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

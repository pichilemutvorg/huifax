<?php

use Illuminate\Support\Facades\Schedule;

// Requires the scheduler: `* * * * * php artisan schedule:run` in production.
Schedule::command('huifax:weather')->everyThirtyMinutes()->withoutOverlapping();

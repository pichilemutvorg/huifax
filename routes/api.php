<?php

use App\Http\Controllers\Api\DisplayController;
use Illuminate\Support\Facades\Route;

Route::get('/display', DisplayController::class)->name('api.display');

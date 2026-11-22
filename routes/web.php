<?php

use App\Http\Controllers\DisplayPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', DisplayPageController::class)->name('display');

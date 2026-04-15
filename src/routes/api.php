<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WeatherLogController;

Route::get('/weather/current', [WeatherLogController::class, 'current']);

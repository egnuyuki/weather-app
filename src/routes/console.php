<?php

use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\WeatherFetchCommand;

Schedule::command(WeatherFetchCommand::class)->dailyAt('06:00');

<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('dispute:resolve')
    ->daily()
    ->runInBackground()
    ->withoutOverlapping();

Schedule::command('demo:refresh')
    ->dailyAt('00:01')
    ->runInBackground()
    ->withoutOverlapping();

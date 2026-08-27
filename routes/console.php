<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto-cancel overdue payments (daily at midnight)
Schedule::command('rentals:cancel-overdue')->dailyAt('00:00');

// Auto-activate confirmed rentals (daily at 00:01)
Schedule::command('rentals:activate')->dailyAt('00:01');

// Auto-complete active rentals (daily at 00:02)
Schedule::command('rentals:complete')->dailyAt('00:02');

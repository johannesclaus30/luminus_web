<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\ArchiveExpiredEvents;
use App\Console\Commands\ArchiveExpiredPerks;

// Existing inspire command
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Archive expired events - runs daily at midnight
Schedule::command(ArchiveExpiredEvents::class)->dailyAt('00:00');

// Archive expired perks - runs daily at 1:00 AM
Schedule::command(ArchiveExpiredPerks::class)->dailyAt('01:00');
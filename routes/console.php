<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

Schedule::command('alertas:whatsapp')->dailyAt('08:00');
Schedule::command('sunat:sync')->dailyAt('23:50')->appendOutputTo(storage_path('logs/sunat-cron.log'));

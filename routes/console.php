<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
|
| Here you may define all of your scheduled tasks. These tasks will
| be run by Laravel's scheduler when the cron job is set up on
| the server: * * * * * cd /path-to-project && php artisan schedule:run
|
*/

// Send WhatsApp notifications for late documents every hour
Schedule::command('notifications:send-late-documents')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/late-document-notifications.log'));

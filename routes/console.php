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

// Auto-forward dokumen ke Pembayaran ketika status_pembayaran = sudah_dibayar
// oleh project eksternal. Jalan setiap menit, tidak boleh overlap.
Schedule::command('dokumen:process-auto-forward')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/auto-forward-dokumen.log'));

// Sinkronisasi data perubahan Agenda Online -> Cash Bank (fallback pola observer)
// Berjalan setiap menit untuk mencari data yang gagal tersync via observer
Schedule::command('dokumen:sync-cashbank --since="5 minutes ago"')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/sync-cashbank.log'));

// Jaring pengaman arah sebaliknya: tanggal transaksi Cash Bank -> tanggal_dibayar
// Agenda. Jalur input Cash Bank sudah menyinkronkan sendiri saat menyimpan;
// backfill ini menutup sisa celah (jalur input baru, atau sinkron yang gagal
// karena database kedua sedang tak terjangkau). Hanya mengisi yang kosong,
// jadi aman diulang.
Schedule::command('dokumen:backfill-tanggal-bayar')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/backfill-tanggal-bayar.log'));

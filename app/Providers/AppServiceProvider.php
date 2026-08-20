<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Dokumen;
use App\Observers\DokumenObserver;
use App\Services\WelcomeMessageService;
use App\View\Composers\WelcomeMessageComposer;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(WelcomeMessageService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS on production to prevent redirect loops
        // DINONAKTIFKAN KARENA SERVER MENGGUNAKAN IP ADDRESS (HTTP)
        // if ($this->app->environment('production')) {
        //     \URL::forceScheme('https');
        // }

        // Register welcome message composer for all views
        View::composer('*', WelcomeMessageComposer::class);

        // Register Dokumen observer - detects status_pembayaran changes via Eloquent
        // (DB trigger handles raw query changes from external project)
        Dokumen::observe(DokumenObserver::class);

        $this->daftarkanInvalidasiCacheBarisDokumen();
    }

    /**
     * Naikkan versi cache baris dokumen setiap kali data penyusunnya berubah.
     *
     * Model anak TIDAK punya $touches (diperiksa 2026-08-20), sehingga perubahan
     * status/deadline/PO/penerima tidak mengubah `dokumens.updated_at` — sidik jari
     * di kunci cache tak akan bergerak sendiri. Karena itu perubahan mereka harus
     * menaikkan versi global secara eksplisit. Bagian ikut didaftarkan karena peta
     * bagian menyusun `handler_options` di setiap baris.
     *
     * Hanya menangkap penulisan lewat Eloquent. Penulisan mentah
     * (DB::table()->update() tanpa updated_at) dibatasi oleh TTL — lihat docblock
     * App\Support\DocumentRowCache.
     */
    private function daftarkanInvalidasiCacheBarisDokumen(): void
    {
        $model = [
            Dokumen::class,
            \App\Models\DokumenStatus::class,
            \App\Models\DokumenRoleData::class,
            \App\Models\DokumenPO::class,
            \App\Models\DibayarKepada::class,
            \App\Models\Bagian::class,
        ];

        foreach ($model as $kelas) {
            if (! class_exists($kelas)) {
                continue;
            }

            $kelas::saved(static fn () => \App\Support\DocumentRowCache::naikkanVersi());
            $kelas::deleted(static fn () => \App\Support\DocumentRowCache::naikkanVersi());
        }
    }
}






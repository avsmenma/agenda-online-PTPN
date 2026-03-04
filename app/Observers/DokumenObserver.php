<?php

namespace App\Observers;

use App\Models\Dokumen;
use App\Services\AutoForwardDokumenService;
use Illuminate\Support\Facades\Log;

/**
 * DokumenObserver
 *
 * Mendeteksi perubahan field status_pembayaran pada model Dokumen
 * melalui Eloquent dan memicu auto-forward ke Pembayaran.
 *
 * PENTING: Observer ini hanya aktif untuk Eloquent updates.
 * Raw DB::table()->update() dari project eksternal TIDAK memicu observer.
 * Untuk raw query, gunakan MySQL trigger + Scheduler Command.
 */
class DokumenObserver
{
    public function __construct(
        private readonly AutoForwardDokumenService $autoForwardService
    ) {}

    /**
     * Handle model updated event.
     * Dipicu setiap kali Dokumen di-update melalui Eloquent.
     */
    public function updated(Dokumen $dokumen): void
    {
        // Deteksi: status_pembayaran baru saja berubah menjadi 'sudah_dibayar'
        if (
            $dokumen->wasChanged('status_pembayaran') &&
            $dokumen->status_pembayaran === 'sudah_dibayar'
        ) {
            // Skip jika sudah pernah di-auto-forward (guard clause)
            if ($dokumen->auto_forwarded_at !== null) {
                return;
            }

            Log::info('DokumenObserver: Mendeteksi status_pembayaran = sudah_dibayar via Eloquent', [
                'dokumen_id'   => $dokumen->id,
                'nomor_agenda' => $dokumen->nomor_agenda,
            ]);

            try {
                $this->autoForwardService->forwardToPembayaran($dokumen);
            } catch (\Throwable $e) {
                // Log error tapi jangan throw — jangan interrupt alur utama
                Log::error('DokumenObserver: AutoForward gagal', [
                    'dokumen_id' => $dokumen->id,
                    'error'      => $e->getMessage(),
                    'trace'      => $e->getTraceAsString(),
                ]);
            }
        }
    }
}

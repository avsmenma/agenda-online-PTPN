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
        // 0. Auto-reset urgency when current_handler changes (document forwarded to next role)
        if (
            $dokumen->wasChanged('current_handler') &&
            $dokumen->urgency_active
        ) {
            // Silently reset urgency — the previous handler has finished processing
            $dokumen->withoutEvents(function () use ($dokumen) {
                $dokumen->updateQuietly([
                    'urgency_active'  => false,
                    'urgency_sent_at' => null,
                    'urgency_sent_by' => null,
                ]);
            });

            Log::debug('DokumenObserver: Urgency auto-reset karena current_handler berubah', [
                'dokumen_id'      => $dokumen->id,
                'old_handler'     => $dokumen->getOriginal('current_handler'),
                'new_handler'     => $dokumen->current_handler,
            ]);
        }

        // 1. Deteksi: status_pembayaran baru saja berubah menjadi 'sudah_dibayar'
        if (
            $dokumen->wasChanged('status_pembayaran') &&
            $dokumen->status_pembayaran === 'sudah_dibayar'
        ) {
            // Skip jika sudah pernah di-auto-forward (guard clause)
            if ($dokumen->auto_forwarded_at === null) {
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

        // 2. Deteksi perubahan untuk Sync ke Cash Bank
        // Hanya sync jika proses update BUKAN dipicu oleh sync dari Cash Bank itu sendiri
        if (! ($dokumen->_syncing ?? false)) {
            $changedFields = array_keys($dokumen->getChanges());
            
            // Cek apakah ada field yang changed termasuk di SYNCABLE_FIELDS
            $syncableFields = \App\Services\DokumenSyncService::SYNCABLE_FIELDS;
            $needsSync = count(array_intersect($changedFields, $syncableFields)) > 0;

            if ($needsSync) {
                // Dispatch job ke background untuk Sync ke CB agar tidak memblokir response
                \App\Jobs\SyncDokumenToCashBankJob::dispatch($dokumen);
                
                // [FIX LOG BLOATING] Dispatch log → debug (muncul setiap Dokumen update)
                Log::debug('DokumenObserver: Dispatch job SyncDokumenToCashBankJob', [
                    'dokumen_id'   => $dokumen->id,
                    'changed'      => array_intersect($changedFields, $syncableFields),
                ]);
            }
        }
    }
}

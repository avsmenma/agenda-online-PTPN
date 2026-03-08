<?php

namespace App\Jobs;

use App\Models\Dokumen;
use App\Services\DokumenSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncDokumenToCashBankJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * [FIX LOG BLOATING] Maksimal 3x retry jika gagal.
     * Tanpa ini, job akan retry tanpa batas dan menulis log error
     * + stack trace setiap kali gagal → penyebab utama log membengkak.
     */
    public $tries = 3;

    /**
     * [FIX LOG BLOATING] Jika sudah 2 exception, langsung gagalkan job.
     */
    public $maxExceptions = 2;

    /**
     * [FIX LOG BLOATING] Backoff bertahap: 30 detik, 60 detik, 120 detik.
     * Memberi waktu koneksi Cash Bank pulih sebelum retry berikutnya.
     *
     * @var array<int>
     */
    public $backoff = [30, 60, 120];

    /**
     * @var \App\Models\Dokumen
     */
    protected $dokumen;

    /**
     * Create a new job instance.
     */
    public function __construct(Dokumen $dokumen)
    {
        $this->dokumen = $dokumen;
    }

    /**
     * Execute the job.
     */
    public function handle(DokumenSyncService $syncService): void
    {
        // Panggil service untuk melakukan push data ke Cash Bank
        $syncService->pushToCashBank($this->dokumen);
    }

    /**
     * [FIX LOG BLOATING] Handle job yang sudah gagal setelah semua retry habis.
     * Hanya log sekali saat benar-benar gagal final, bukan setiap retry.
     */
    public function failed(?\Throwable $exception): void
    {
        Log::error('[SyncDokumenToCashBankJob] Job gagal final setelah semua retry habis.', [
            'dokumen_id' => $this->dokumen->id ?? null,
            'nomor_agenda' => $this->dokumen->nomor_agenda ?? null,
            'error' => $exception?->getMessage(),
        ]);
    }
}

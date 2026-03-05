<?php

namespace App\Jobs;

use App\Models\Dokumen;
use App\Services\DokumenSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncDokumenToCashBankJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
}

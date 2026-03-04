<?php

namespace App\Console\Commands;

use App\Models\Dokumen;
use App\Services\AutoForwardDokumenService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ProcessAutoForwardQueue
 *
 * Command yang dijalankan setiap menit oleh Laravel Scheduler.
 * Memproses antrian dokumen yang perlu di-auto-forward ke Pembayaran.
 *
 * Sumber dokumen:
 * 1. Tabel dokumen_auto_forward_queue (diisi oleh MySQL trigger)
 * 2. Polling fallback: dokumen yang lolos dari trigger
 *
 * Usage: php artisan dokumen:process-auto-forward
 */
class ProcessAutoForwardQueue extends Command
{
    protected $signature = 'dokumen:process-auto-forward
                            {--dry-run : Jalankan tanpa memproses (hanya tampilkan dokumen yang akan diproses)}';

    protected $description = 'Proses antrian auto-forward dokumen ke role Pembayaran saat status_pembayaran = sudah_dibayar';

    public function handle(AutoForwardDokumenService $service): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('>>> DRY RUN MODE — tidak ada perubahan yang disimpan <<<');
        }

        $processed = 0;
        $skipped   = 0;
        $failed    = 0;

        // === SUMBER 1: Queue dari MySQL Trigger ===
        $queueItems = DB::table('dokumen_auto_forward_queue')
            ->where('status', 'pending')
            ->orderBy('triggered_at')
            ->get();

        if ($queueItems->isNotEmpty()) {
            $this->info("Memproses {$queueItems->count()} item dari trigger queue...");

            foreach ($queueItems as $item) {
                $result = $this->processItem($item->dokumen_id, $service, $dryRun);

                match ($result) {
                    'processed' => $processed++,
                    'skipped'   => $skipped++,
                    'failed'    => $failed++,
                    default     => null,
                };
            }
        }

        // === SUMBER 2: Polling Fallback ===
        // Cari dokumen yang statusnya sudah_dibayar tapi belum di-auto-forward
        // dan belum ada di queue (trigger mungkin gagal / tidak terpasang)
        $fallbackIds = Dokumen::where('status_pembayaran', 'sudah_dibayar')
            ->whereNull('auto_forwarded_at')
            ->where('current_handler', '!=', 'pembayaran')
            ->whereNotIn('id', function ($query) {
                $query->select('dokumen_id')
                      ->from('dokumen_auto_forward_queue')
                      ->whereIn('status', ['pending', 'processing', 'done']);
            })
            ->pluck('id');

        if ($fallbackIds->isNotEmpty()) {
            $this->info("Polling fallback: menemukan {$fallbackIds->count()} dokumen yang perlu di-auto-forward...");

            foreach ($fallbackIds as $dokumenId) {
                $result = $this->processItem($dokumenId, $service, $dryRun);

                match ($result) {
                    'processed' => $processed++,
                    'skipped'   => $skipped++,
                    'failed'    => $failed++,
                    default     => null,
                };
            }
        }

        if ($processed === 0 && $skipped === 0 && $failed === 0) {
            $this->line('Tidak ada dokumen yang perlu di-auto-forward.');
            return Command::SUCCESS;
        }

        $this->info("Selesai. Processed: {$processed} | Skipped: {$skipped} | Failed: {$failed}");

        return $failed > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    /**
     * Proses satu dokumen dari queue atau fallback.
     *
     * @return string 'processed'|'skipped'|'failed'
     */
    private function processItem(int $dokumenId, AutoForwardDokumenService $service, bool $dryRun): string
    {
        $dokumen = Dokumen::find($dokumenId);

        if (!$dokumen) {
            $this->warn("  [SKIP] Dokumen ID {$dokumenId} tidak ditemukan.");

            // Tandai queue sebagai failed jika ada
            $this->updateQueueStatus($dokumenId, 'failed', 'Dokumen tidak ditemukan di database');

            return 'skipped';
        }

        if ($dryRun) {
            $this->line("  [DRY-RUN] Akan forward: {$dokumen->nomor_agenda} (current_handler: {$dokumen->current_handler})");
            return 'processed';
        }

        // Tandai sebagai processing untuk mencegah concurrent runs
        $this->updateQueueStatus($dokumenId, 'processing');

        try {
            $result = $service->forwardToPembayaran($dokumen);

            if ($result) {
                $this->info("  [OK] Dokumen {$dokumen->nomor_agenda} berhasil di-auto-forward ke pembayaran.");
                return 'processed';
            } else {
                $this->line("  [SKIP] Dokumen {$dokumen->nomor_agenda} diskip (sudah di pembayaran atau tidak memenuhi syarat).");
                // Tandai queue sebagai done juga (dokumen sudah benar)
                $this->updateQueueStatus($dokumenId, 'done');
                return 'skipped';
            }

        } catch (\Throwable $e) {
            $errorMsg = $e->getMessage();
            $this->error("  [FAIL] Dokumen {$dokumen->nomor_agenda}: {$errorMsg}");

            Log::error('AutoForward command failed untuk dokumen', [
                'dokumen_id'   => $dokumenId,
                'nomor_agenda' => $dokumen->nomor_agenda,
                'error'        => $errorMsg,
                'trace'        => $e->getTraceAsString(),
            ]);

            $this->updateQueueStatus($dokumenId, 'failed', $errorMsg);
            return 'failed';
        }
    }

    /**
     * Update status pada dokumen_auto_forward_queue.
     * Aman dipanggil meski record tidak ada (tidak akan error).
     */
    private function updateQueueStatus(int $dokumenId, string $status, ?string $errorMessage = null): void
    {
        $updateData = [
            'status'     => $status,
            'updated_at' => now(),
        ];

        if (in_array($status, ['done', 'failed'])) {
            $updateData['processed_at'] = now();
        }

        if ($errorMessage !== null) {
            $updateData['error_message'] = $errorMessage;
        }

        DB::table('dokumen_auto_forward_queue')
            ->where('dokumen_id', $dokumenId)
            ->update($updateData);
    }
}

<?php

namespace App\Console\Commands;

use App\Services\DokumenSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncCashBankCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dokumen:sync-cashbank {--since=5 minutes ago : Waktu ke belakang untuk mencari data yang berubah}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi data dokumen terbaru ke Cash Bank, bertindak sebagai fallback polling jika observer terlewat.';

    /**
     * Execute the console command.
     */
    public function handle(DokumenSyncService $syncService)
    {
        $since = $this->option('since');
        
        $this->info("Memulai sinkronisasi Agenda Online -> Cash Bank untuk data yang berubah sejak: {$since}");
        // [FIX LOG BLOATING] Dihapus: Log::info duplikat (output console sudah cukup)

        try {
            $count = $syncService->syncAllChanged($since);
            $this->info("Sinkronisasi selesai. {$count} dokumen berhasil di proses.");
            // [FIX LOG BLOATING] Hanya log jika ada yang diproses, tanpa trace
            if ($count > 0) {
                Log::info("[DokumenSyncCommand] Selesai", ['count' => $count]);
            }
        } catch (\Throwable $e) {
            $this->error("Terjadi kesalahan saat sinkronisasi: " . $e->getMessage());
            // [FIX LOG BLOATING] Hapus trace dari log error
            Log::error("[DokumenSyncCommand] Error: " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

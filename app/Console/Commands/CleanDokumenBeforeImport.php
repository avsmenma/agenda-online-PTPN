<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Dokumen;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class CleanDokumenBeforeImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dokumen:clean-before-import 
                            {--type=all : Type of cleanup (all, csv_import, pembayaran, confirm)}
                            {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hapus data dokumen sebelum import CSV (all, csv_import, pembayaran)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->option('type');
        $force = $this->option('force');

        $this->info("=== CLEANUP DATA DOKUMEN SEBELUM IMPORT CSV ===");
        $this->newLine();

        // Show current statistics
        $totalDokumen = Dokumen::count();
        $csvImportDokumen = Dokumen::where('created_by', 'csv_import')->count();
        $pembayaranDokumen = Dokumen::where(function($query) {
            $query->where('current_handler', 'pembayaran')
                  ->orWhere('status', 'sent_to_pembayaran')
                  ->orWhereNotNull('status_pembayaran');
        })->count();

        $this->info("📊 Statistik Data Saat Ini:");
        $this->line("   Total Dokumen: " . number_format($totalDokumen));
        $this->line("   Dokumen CSV Import: " . number_format($csvImportDokumen));
        $this->line("   Dokumen Pembayaran: " . number_format($pembayaranDokumen));
        $this->newLine();

        // Determine what to delete
        $query = null;
        $description = '';

        switch ($type) {
            case 'csv_import':
                $query = Dokumen::where('created_by', 'csv_import');
                $description = 'dokumen yang dOperatort oleh CSV import';
                break;

            case 'pembayaran':
                $query = Dokumen::where(function($q) {
                    $q->where('current_handler', 'pembayaran')
                      ->orWhere('status', 'sent_to_pembayaran')
                      ->orWhereNotNull('status_pembayaran');
                });
                $description = 'dokumen yang terkait dengan pembayaran';
                break;

            case 'all':
            default:
                $query = Dokumen::query();
                $description = 'SEMUA dokumen';
                break;
        }

        $countToDelete = $query->count();

        if ($countToDelete === 0) {
            $this->warn("⚠️  Tidak ada data yang akan dihapus untuk tipe: {$type}");
            return 0;
        }

        $this->warn("⚠️  PERINGATAN: Akan menghapus {$countToDelete} {$description}!");
        $this->newLine();

        // Confirmation
        if (!$force) {
            if (!$this->confirm("Apakah Anda yakin ingin melanjutkan? (yes/no)", false)) {
                $this->info("❌ Operasi dibatalkan.");
                return 0;
            }
        }

        // Start transaction
        DB::beginTransaction();

        try {
            $this->info("🗑️  Menghapus data...");

            // Delete main records (related records will be cascade deleted automatically)
            // dokumen_pos, dokumen_prs sudah cascade delete di migration
            $deleted = $query->delete();
            
            DB::commit();

            $this->newLine();
            $this->info("✅ Berhasil menghapus {$deleted} dokumen!");
            $this->info("💾 Database telah dibersihkan dan siap untuk import CSV.");

            Log::info('Dokumen cleanup before CSV import', [
                'type' => $type,
                'deleted_count' => $deleted,
                'user' => auth()->user()->name ?? 'system'
            ]);

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->error("❌ Error saat menghapus data: " . $e->getMessage());
            Log::error('Dokumen cleanup error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return 1;
        }
    }
}






<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CleanAllData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:clean {--force : Force execution without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hapus semua data dari tabel-tabel terkait dokumen tanpa menghapus struktur tabel';

    /**
     * List of tables to clean in order (respecting foreign key constraints)
     *
     * @var array
     */
    private $tablesToClean = [
        // Child tables first (due to foreign key constraints)
        'dibayar_kepadas',
        'dokumen_pos',
        'dokumen_prs',

        // Parent tables
        'dokumens',
        'bidangs',

        // User table (optional - only if you want to reset users)
        // 'users',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (!$this->option('force')) {
            if ($this->confirm('⚠️  PERINGATAN: Ini akan menghapus SEMUA data dokumen dari database. Lanjutkan?')) {
                $this->info('✓ User confirmed the operation.');
            } else {
                $this->info('❌ Operasi dibatalkan.');
                return Command::SUCCESS;
            }
        }

        $this->info('🧹 Mulai membersihkan semua data...');
        $this->newLine();

        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $totalDeleted = 0;

        foreach ($this->tablesToClean as $tableName) {
            if (!Schema::hasTable($tableName)) {
                $this->warn("⚠️  Tabel '{$tableName}' tidak ditemukan, dilewati.");
                continue;
            }

            $this->line("🗑️  Membersihkan tabel: {$tableName}");

            try {
                $count = DB::table($tableName)->count();

                if ($count > 0) {
                    DB::table($tableName)->delete();
                    $this->info("   ✓ {$count} record dihapus dari {$tableName}");
                    $totalDeleted += $count;
                } else {
                    $this->comment("   - Tabel {$tableName} sudah kosong");
                }

                // Reset auto-increment
                $driver = DB::getDriverName();
                if ($driver === 'mysql') {
                    DB::statement("ALTER TABLE {$tableName} AUTO_INCREMENT = 1");
                } elseif ($driver === 'sqlite') {
                    DB::statement("DELETE FROM sqlite_sequence WHERE name = '{$tableName}'");
                }

            } catch (\Exception $e) {
                $this->error("   ❌ Gagal membersihkan {$tableName}: " . $e->getMessage());
            }
        }

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->newLine();
        $this->info('✅ Selesai! Total ' . number_format($totalDeleted) . ' record dihapus.');
        $this->newLine();

        $this->info('📊 Status Database:');
        foreach ($this->tablesToClean as $tableName) {
            if (Schema::hasTable($tableName)) {
                $count = DB::table($tableName)->count();
                $this->line("   • {$tableName}: {$count} record");
            }
        }

        $this->newLine();
        $this->info('🔄 Database sudah bersih dan siap untuk data baru!');
        $this->warn('⚠️  Pastikan untuk menjalankan ulang aplikasi atau refresh browser untuk melihat perubahan.');

        return Command::SUCCESS;
    }
}

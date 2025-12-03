<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportTuTkData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tu-tk:import {file=tu_tk_2023.sql : Path to SQL file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data from tu_tk_2023.sql file (skips CREATE TABLE statements)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $filePath = $this->argument('file');
        
        // Check if file exists
        if (!File::exists($filePath)) {
            $this->error("❌ File tidak ditemukan: {$filePath}");
            return Command::FAILURE;
        }

        $this->info("📂 Membaca file: {$filePath}");
        
        // Read file content
        $content = File::get($filePath);
        
        // Split by semicolon to get individual statements
        $statements = array_filter(
            array_map('trim', explode(';', $content)),
            fn($stmt) => !empty($stmt) && !preg_match('/^(--|SET|START|COMMIT|BEGIN|USE|\/\*|\*\/)/i', $stmt)
        );

        $this->info("📊 Menemukan " . count($statements) . " statements dalam file");

        // Filter out CREATE TABLE statements and keep only INSERT statements
        $insertStatements = [];
        foreach ($statements as $statement) {
            $statement = trim($statement);
            
            // Skip empty or comment lines
            if (empty($statement) || str_starts_with($statement, '--')) {
                continue;
            }
            
            // Skip CREATE TABLE statements
            if (preg_match('/^CREATE\s+TABLE/i', $statement)) {
                $this->warn("⚠️  Melewati CREATE TABLE statement (tabel sudah ada)");
                continue;
            }
            
            // Only process INSERT statements
            if (preg_match('/^INSERT\s+INTO/i', $statement)) {
                $insertStatements[] = $statement;
            }
        }

        if (empty($insertStatements)) {
            $this->warn("⚠️  Tidak ada INSERT statements yang ditemukan dalam file");
            return Command::FAILURE;
        }

        $this->info("✅ Menemukan " . count($insertStatements) . " INSERT statements");
        $this->newLine();

        // Count existing records
        $existingCount = DB::table('tu_tk_2023')->count();
        $this->info("📈 Data existing dalam tabel: {$existingCount} records");

        // Ask for confirmation if table is not empty
        if ($existingCount > 0) {
            if (!$this->confirm("⚠️  Tabel sudah berisi data. Apakah Anda ingin melanjutkan? (Data akan ditambahkan, tidak akan dihapus)", false)) {
                $this->info("❌ Import dibatalkan.");
                return Command::FAILURE;
            }
        }

        $this->info("🚀 Mulai mengimport data...");
        $this->newLine();

        $successCount = 0;
        $errorCount = 0;
        $progressBar = $this->output->createProgressBar(count($insertStatements));
        $progressBar->start();

        // Disable foreign key checks for faster import
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::statement('SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO"');
        
        // Process each INSERT statement
        foreach ($insertStatements as $index => $statement) {
            try {
                // Ensure statement ends with semicolon
                if (!str_ends_with(trim($statement), ';')) {
                    $statement .= ';';
                }
                
                DB::unprepared($statement);
                $successCount++;
                
            } catch (\Exception $e) {
                $errorCount++;
                if ($this->option('verbose')) {
                    $this->newLine();
                    $this->error("❌ Error pada statement " . ($index + 1) . ": " . $e->getMessage());
                }
            }
            
            $progressBar->advance();
        }

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $progressBar->finish();
        $this->newLine(2);

        // Show results
        $finalCount = DB::table('tu_tk_2023')->count();
        $importedCount = $finalCount - $existingCount;

        $this->info("✅ Import selesai!");
        $this->table(
            ['Metrik', 'Jumlah'],
            [
                ['Statements berhasil', $successCount],
                ['Statements error', $errorCount],
                ['Data existing (sebelum)', $existingCount],
                ['Data baru diimport', $importedCount],
                ['Total data (setelah)', $finalCount],
            ]
        );

        if ($errorCount > 0) {
            $this->warn("⚠️  Terjadi {$errorCount} error. Gunakan --verbose untuk melihat detail error.");
        }

        return Command::SUCCESS;
    }
}

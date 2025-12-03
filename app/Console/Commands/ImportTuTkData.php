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
        
        // Detect table name from file (tu_tk_2023 or tu_tk_pupuk_2023)
        $tableName = 'tu_tk_2023'; // default
        if (preg_match('/CREATE\s+TABLE\s+[`"]?(\w+)[`"]?/i', $content, $matches)) {
            $tableName = $matches[1];
        } elseif (preg_match('/INSERT\s+INTO\s+[`"]?(\w+)[`"]?/i', $content, $matches)) {
            $tableName = $matches[1];
        }
        
        $this->info("📋 Tabel target: {$tableName}");
        
        // Remove comments and clean content
        $content = preg_replace('/--.*$/m', '', $content); // Remove line comments
        $content = preg_replace('/\/\*.*?\*\//s', '', $content); // Remove block comments
        
        // Find INSERT statement - handle multi-line format
        // Some SQL files have INSERT with VALUES spanning multiple lines
        $insertStatements = [];
        
        // Split by line and reconstruct INSERT statement
        $lines = explode("\n", $content);
        $currentStatement = '';
        $inInsert = false;
        $foundValues = false;
        
        foreach ($lines as $line) {
            $originalLine = $line;
            $line = trim($line);
            
            // Skip comments and empty lines
            if (empty($line) || str_starts_with($line, '--') || preg_match('/^\/\*/', $line)) {
                continue;
            }
            
            // Skip CREATE TABLE
            if (preg_match('/^CREATE\s+TABLE/i', $line)) {
                $this->warn("⚠️  Melewati CREATE TABLE statement (tabel sudah ada)");
                continue;
            }
            
            // Skip COMMIT, SET, etc
            if (preg_match('/^(COMMIT|SET\s+SQL_MODE|SET\s+time_zone|START\s+TRANSACTION|BEGIN|USE)/i', $line)) {
                continue;
            }
            
            // Detect start of INSERT
            if (preg_match('/^INSERT\s+INTO/i', $line)) {
                $inInsert = true;
                $foundValues = false;
                $currentStatement = $originalLine; // Keep original line to preserve formatting
                // Check if VALUES is on same line
                if (preg_match('/\bVALUES\b/i', $line)) {
                    $foundValues = true;
                }
                continue;
            }
            
            // Continue building INSERT statement
            if ($inInsert) {
                $currentStatement .= "\n" . $originalLine; // Keep original line
                
                // Check if we found VALUES keyword
                if (preg_match('/\bVALUES\b/i', $line)) {
                    $foundValues = true;
                }
                
                // Check if statement ends (has semicolon at end of line)
                if (preg_match('/;\s*$/', $line)) {
                    if ($foundValues && preg_match('/INSERT\s+INTO/i', $currentStatement)) {
                        $insertStatements[] = trim($currentStatement);
                    }
                    $currentStatement = '';
                    $inInsert = false;
                    $foundValues = false;
                }
            }
        }
        
        // Handle case where last statement doesn't end with semicolon
        if ($inInsert && !empty($currentStatement) && $foundValues) {
            $currentStatement = trim($currentStatement);
            if (!str_ends_with($currentStatement, ';')) {
                // Remove trailing comma if exists
                $currentStatement = rtrim($currentStatement, ',') . ';';
            }
            if (preg_match('/INSERT\s+INTO/i', $currentStatement)) {
                $insertStatements[] = $currentStatement;
            }
        }

        $this->info("📊 Menemukan " . count($insertStatements) . " INSERT statements dalam file");

        if (empty($insertStatements)) {
            $this->warn("⚠️  Tidak ada INSERT statements yang ditemukan dalam file");
            return Command::FAILURE;
        }

        $this->info("✅ Menemukan " . count($insertStatements) . " INSERT statements");
        $this->newLine();

        // Count existing records
        $existingCount = DB::table($tableName)->count();
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
        $finalCount = DB::table($tableName)->count();
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

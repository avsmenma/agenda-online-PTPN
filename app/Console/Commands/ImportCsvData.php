<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Dokumen;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ImportCsvData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:csv {--path= : Path to CSV file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data dari CSV file ke database dokumens';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $csvPath = $this->option('path') ?: public_path('DATA 12.csv');

        if (!file_exists($csvPath)) {
            $this->error("File CSV tidak ditemukan: {$csvPath}");
            return 1;
        }

        $this->info("Memulai import dari: {$csvPath}");

        // Baca file CSV
        $handle = fopen($csvPath, 'r');
        if (!$handle) {
            $this->error("Tidak bisa membuka file CSV");
            return 1;
        }

        // Skip baris kosong dan header
        $lineNumber = 0;
        $importedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($handle, 0, ';')) !== false) {
                $lineNumber++;

                // Skip baris kosong dan header
                if ($lineNumber <= 3 || empty($row[0])) {
                    continue;
                }

                try {
                    // Mapping CSV columns to database fields
                    $data = [
                        'nomor_agenda' => $this->generateUniqueAgendaNumber(),
                        'nomor_spp' => $this->cleanValue($row[0]),
                        'nama_kebuns' => $this->cleanValue($row[1]),
                        'dibayar_kepada' => $this->cleanValue($row[2]),
                        'tanggal_spp' => $this->parseDate($row[3]),
                        'no_spk' => $this->cleanValue($row[4]),
                        'tanggal_spk' => $this->parseDate($row[5]),
                        'tanggal_berakhir_spk' => $this->parseDate($row[6]),
                        'no_berita_acara' => $this->cleanValue($row[7]),
                        'no_ba' => $this->cleanValue($row[7]), // Also map to no_ba field
                        'tanggal_berita_acara' => $this->parseDate($row[8]),
                        'tanggal_faktur' => $this->parseDate($row[9]),
                        'uraian_spp' => $this->cleanValue($row[10]),
                        'nilai_rupiah' => $this->parseCurrency($row[11]),
                        'tanggal_masuk' => $this->parseDate($row[12]),
                        'status_pembayaran' => $this->mapStatusPembayaran($this->cleanValue($row[13])),
                        'DIBAYAR' => $this->parseCurrency($row[14]),
                        'BELUM_DIBAYAR' => $this->parseCurrency($row[15]),
                        'kategori' => $this->cleanValue($row[16]), // Use kategori from CSV column 16
                        'jenis_dokumen' => $this->cleanValue($row[17]),
                        // KATEGORI from row[18] - skip to avoid duplicate column error
                        // MySQL treats 'kategori' and 'KATEGORI' as same column (case-insensitive)
                        'NO_PO' => $this->cleanValue($row[19]),
                        'NO_MIRO_SES' => $this->cleanValue($row[20]),
                        'status' => 'sedang diproses', // Set status awal
                        'created_by' => 'csv_import'
                    ];

                    // Handle nilai default untuk field yang kosong
                    if (empty($data['nomor_spp'])) {
                        // Generate nomor_spp otomatis jika kosong
                        $data['nomor_spp'] = 'SPP-AUTO-' . str_pad($lineNumber, 6, '0', STR_PAD_LEFT);
                        $this->warn("Baris {$lineNumber}: nomor_spp kosong, menggunakan nilai default: {$data['nomor_spp']}");
                    }
                    
                    if (empty($data['nilai_rupiah']) || $data['nilai_rupiah'] == 0) {
                        // Gunakan 0 sebagai default jika nilai_rupiah kosong
                        $data['nilai_rupiah'] = 0;
                        $this->warn("Baris {$lineNumber}: nilai_rupiah kosong, menggunakan nilai default: 0");
                    }

                    // Handle tanggal_masuk yang null - gunakan tanggal_spp sebagai fallback
                    if (empty($data['tanggal_masuk'])) {
                        if (!empty($data['tanggal_spp'])) {
                            $data['tanggal_masuk'] = $data['tanggal_spp'];
                            $this->warn("Baris {$lineNumber}: tanggal_masuk tidak valid, menggunakan tanggal_spp sebagai fallback");
                        } else {
                            // Jika tanggal_spp juga null, gunakan tanggal sekarang
                            $data['tanggal_masuk'] = Carbon::now();
                            $this->warn("Baris {$lineNumber}: tanggal_masuk dan tanggal_spp tidak valid, menggunakan tanggal sekarang");
                        }
                    }

                    // Set bulan dan tahun dari tanggal_masuk yang sudah dipastikan tidak null
                    $data['bulan'] = $this->extractMonth($data['tanggal_masuk']);
                    $data['tahun'] = $this->extractYear($data['tanggal_masuk']);

                    // Cek duplikasi
                    $existing = Dokumen::where('nomor_spp', $data['nomor_spp'])->first();
                    if ($existing) {
                        $this->warn("Baris {$lineNumber}: Data dengan SPP {$data['nomor_spp']} sudah ada, update...");
                        $existing->update($data);
                    } else {
                        Dokumen::create($data);
                    }

                    $importedCount++;

                    if ($importedCount % 100 == 0) {
                        $this->info("Sudah import {$importedCount} data...");
                    }

                } catch (\Exception $e) {
                    $this->error("Baris {$lineNumber}: Error - " . $e->getMessage());
                    $errorCount++;
                }
            }

            DB::commit();

            fclose($handle);

            $this->info("\n=== IMPORT SELESAI ===");
            $this->info("Total data diimport: {$importedCount}");
            $this->info("Total data dilewati: {$skippedCount}");
            $this->info("Total error: {$errorCount}");

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);

            $this->error("Import gagal: " . $e->getMessage());
            Log::error('CSV Import Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return 1;
        }
    }

    /**
     * Generate unique agenda number
     */
    private function generateUniqueAgendaNumber()
    {
        do {
            $year = date('Y');
            $sequence = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $agendaNumber = "AGENDA/{$year}/{$sequence}";
        } while (Dokumen::where('nomor_agenda', $agendaNumber)->exists());

        return $agendaNumber;
    }

    /**
     * Clean value from CSV
     */
    private function cleanValue($value)
    {
        if (empty($value)) return null;
        return trim(strip_tags($value));
    }

    /**
     * Parse date from CSV
     */
    private function parseDate($value)
    {
        if (empty($value)) return null;

        // Handle Indonesian month abbreviations
        $indonesianMonths = [
            'Jan' => 'Jan', 'Feb' => 'Feb', 'Mar' => 'Mar', 'Apr' => 'Apr',
            'Mei' => 'May', 'Jun' => 'Jun', 'Jul' => 'Jul', 'Agu' => 'Aug',
            'Sep' => 'Sep', 'Okt' => 'Oct', 'Nov' => 'Nov', 'Des' => 'Dec'
        ];

        // Replace Indonesian month abbreviations
        $value = str_ireplace(array_keys($indonesianMonths), array_values($indonesianMonths), $value);

        // Handle various date formats
        $formats = [
            'd-M-y',    // 20-Jan-23, 3-Aug-23
            'd-M-Y',    // 20-Jan-2023
            'd/m/Y',     // 20/01/2023
            'Y-m-d',     // 2023-01-20
            'd/m/y',      // 20/01/23
            'd M Y',      // 3 Aug 2023 (after replacement)
        ];

        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, trim($value));
                // Validate reasonable date range (1900-2100)
                if ($date->year >= 1900 && $date->year <= 2100) {
                    return $date;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        $this->warn("Format tanggal tidak dikenali: {$value}");
        return null;
    }

    /**
     * Parse currency from CSV
     */
    private function parseCurrency($value)
    {
        if (empty($value)) return 0;

        // Remove formatting and convert to decimal
        $cleanValue = str_replace(['.', ','], ['', ''], $value);
        $cleanValue = str_replace(['Rp', ' '], ['', ''], $cleanValue);

        return is_numeric($cleanValue) ? (float) $cleanValue : 0;
    }

    /**
     * Extract month from date
     */
    private function extractMonth($date)
    {
        if (!$date) return null;
        return $date->format('F');
    }

    /**
     * Extract year from date
     */
    private function extractYear($date)
    {
        if (!$date) return null;
        return (int) $date->format('Y');
    }

    /**
     * Map status pembayaran from CSV to standardized format
     */
    private function mapStatusPembayaran($value)
    {
        if (empty($value)) return null;
        
        $value = strtoupper(trim($value));
        
        // Map various CSV values to standardized format
        $mapping = [
            'SUDAH DIBAYAR' => 'SUDAH DIBAYAR',
            'BELUM SIAP DIBAYAR' => 'BELUM SIAP DIBAYAR',
            'BELUM SIAPDIBAYAR' => 'BELUM SIAP DIBAYAR',
            'SIAP DIBAYAR' => 'SIAP DIBAYAR',
            'BELUM DIBAYAR' => 'BELUM DIBAYAR',
        ];
        
        // Check exact match first
        if (isset($mapping[$value])) {
            return $mapping[$value];
        }
        
        // Check partial matches
        if (strpos($value, 'SUDAH') !== false && strpos($value, 'DIBAYAR') !== false) {
            return 'SUDAH DIBAYAR';
        }
        
        if (strpos($value, 'BELUM') !== false && strpos($value, 'SIAP') !== false) {
            return 'BELUM SIAP DIBAYAR';
        }
        
        if (strpos($value, 'SIAP') !== false && strpos($value, 'DIBAYAR') !== false) {
            return 'SIAP DIBAYAR';
        }
        
        // Return original value if no mapping found
        return $value;
    }
}



<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use App\Models\Dokumen;
use Carbon\Carbon;

class CsvImportController extends Controller
{
    public function index()
    {
        return view('pembayaranNEW.import.index', [
            'title' => 'Import Data CSV - Pembayaran',
            'module' => 'pembayaran'
        ]);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240', // 10MB max
        ]);

        try {
            $file = $request->file('csv_file');

            // Sanitize filename - remove spaces and special characters
            $originalName = $file->getClientOriginalName();
            $sanitizedName = preg_replace('/[^A-Za-z0-9._-]/', '_', $originalName);
            $filename = time() . '_' . $sanitizedName;

            // Ensure directory exists with proper permissions
            $storageBasePath = storage_path('app');
            $csvImportsPath = $storageBasePath . '/csv_imports';

            if (!is_dir($csvImportsPath)) {
                mkdir($csvImportsPath, 0755, true);
                Log::info('Created csv_imports directory', ['path' => $csvImportsPath]);
            }

            // Store file directly using move()
            $destinationPath = $csvImportsPath . '/' . $filename;
            $file->move($csvImportsPath, $filename);

            Log::info('File moved to destination', [
                'destination' => $destinationPath,
                'filename' => $filename,
                'exists' => file_exists($destinationPath)
            ]);

            // Verify file exists
            if (!file_exists($destinationPath)) {
                Log::error('File not found after move', [
                    'destination' => $destinationPath,
                    'csv_imports_exists' => is_dir($csvImportsPath),
                    'csv_imports_writable' => is_writable($csvImportsPath)
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'File upload gagal: File tidak dapat disimpan'
                ], 500);
            }

            // Parse CSV untuk preview
            $previewData = $this->parseAndPreviewCsv($destinationPath);

            // Return relative path for later use
            $relativePath = 'csv_imports/' . $filename;

            return response()->json([
                'success' => true,
                'message' => 'File berhasil di-upload',
                'file_path' => $relativePath,
                'filename' => $filename,
                'preview' => $previewData,
            ]);

        } catch (\Exception $e) {
            Log::error('CSV Upload Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    private function parseAndPreviewCsv($filePath)
    {
        // Check if file exists
        if (!file_exists($filePath)) {
            Log::error('CSV file not found', ['path' => $filePath]);
            throw new \Exception("File tidak ditemukan: {$filePath}");
        }

        // Detect encoding
        $content = file_get_contents($filePath);
        $encoding = mb_detect_encoding($content, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);

        if ($encoding !== 'UTF-8') {
            $content = mb_convert_encoding($content, 'UTF-8', $encoding);
            file_put_contents($filePath, $content);
        }

        $rows = [];
        $handle = fopen($filePath, 'r');

        // Skip 3 summary rows, use row 4 as headers
        for ($i = 0; $i < 3; $i++) {
            fgetcsv($handle);
        }

        $headers = fgetcsv($handle);

        // Get first 10 data rows for preview
        $rowCount = 0;
        while (($data = fgetcsv($handle)) !== false && $rowCount < 10) {
            if (!empty(array_filter($data))) { // Skip empty rows
                $rows[] = $data;
                $rowCount++;
            }
        }

        fclose($handle);

        // Get total rows
        $totalRows = count(file($filePath)) - 4; // Minus headers

        return [
            'headers' => $this->cleanHeaders($headers),
            'rows' => $rows,
            'total_rows' => $totalRows,
        ];
    }

    private function cleanHeaders($headers)
    {
        return array_map(function ($header) {
            return trim($header);
        }, $headers);
    }

    public function preview(Request $request)
    {
        $request->validate([
            'file_path' => 'required|string'
        ]);

        try {
            // Build full path directly
            $fullPath = storage_path('app/' . $request->file_path);

            if (!file_exists($fullPath)) {
                Log::error('CSV file not found for preview', ['path' => $fullPath]);
                return response()->json([
                    'success' => false,
                    'message' => 'File tidak ditemukan'
                ], 404);
            }

            $validatedData = $this->validateAllRows($fullPath);

            return response()->json([
                'success' => true,
                'validation' => $validatedData
            ]);

        } catch (\Exception $e) {
            Log::error('CSV Preview Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    private function validateAllRows($filePath)
    {
        $handle = fopen($filePath, 'r');

        // Skip 3 summary rows
        for ($i = 0; $i < 3; $i++) {
            fgetcsv($handle);
        }

        $headers = fgetcsv($handle);
        $headers = $this->cleanHeaders($headers);

        $validCount = 0;
        $errorCount = 0;
        $warningCount = 0;
        $errors = [];
        $warnings = [];

        $rowNumber = 5; // Start from row 5 (after headers)

        while (($data = fgetcsv($handle)) !== false) {
            if (empty(array_filter($data))) {
                continue; // Skip empty rows
            }

            $row = array_combine($headers, $data);
            $validation = $this->validateRow($row, $rowNumber);

            if (!empty($validation['errors'])) {
                $errorCount++;
                $errors[] = [
                    'row' => $rowNumber,
                    'nomor_agenda' => $row['AGENDA'] ?? '-',
                    'errors' => $validation['errors']
                ];
            } elseif (!empty($validation['warnings'])) {
                $warningCount++;
                $warnings[] = [
                    'row' => $rowNumber,
                    'nomor_agenda' => $row['AGENDA'] ?? '-',
                    'warnings' => $validation['warnings']
                ];
                $validCount++;
            } else {
                $validCount++;
            }

            $rowNumber++;
        }

        fclose($handle);

        return [
            'total' => $rowNumber - 5,
            'valid' => $validCount,
            'errors' => $errorCount,
            'warnings' => $warningCount,
            'error_details' => array_slice($errors, 0, 10), // Show first 10 errors
            'warning_details' => array_slice($warnings, 0, 10), // Show first 10 warnings
        ];
    }

    private function validateRow($row, $rowNumber)
    {
        $errors = [];
        $warnings = [];

        // Required fields
        if (empty($row['AGENDA'])) {
            $errors[] = 'Nomor Agenda wajib diisi';
        }

        if (empty($row['NO SPP'])) {
            $errors[] = 'Nomor SPP wajib diisi';
        }

        if (empty($row['NILAI'])) {
            $errors[] = 'Nilai Rupiah wajib diisi';
        }

        // Check duplicate
        if (!empty($row['AGENDA'])) {
            if (Dokumen::where('nomor_agenda', $row['AGENDA'])->exists()) {
                $warnings[] = "Nomor Agenda '{$row['AGENDA']}' sudah ada di database";
            }
        }

        return [
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    public function import(Request $request)
    {
        $request->validate([
            'file_path' => 'required|string',
            'skip_duplicates' => 'boolean',
        ]);

        try {
            // Build full path directly
            $fullPath = storage_path('app/' . $request->file_path);

            if (!file_exists($fullPath)) {
                Log::error('CSV file not found for import', ['path' => $fullPath]);
                return response()->json([
                    'success' => false,
                    'message' => 'File tidak ditemukan'
                ], 404);
            }

            $skipDuplicates = $request->skip_duplicates ?? true;
            $result = $this->executeImport($fullPath, $skipDuplicates);

            return response()->json([
                'success' => true,
                'message' => 'Import berhasil!',
                'result' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('CSV Import Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    private function executeImport($filePath, $skipDuplicates)
    {
        $handle = fopen($filePath, 'r');

        // Skip 3 summary rows
        for ($i = 0; $i < 3; $i++) {
            fgetcsv($handle);
        }

        $headers = fgetcsv($handle);
        $headers = $this->cleanHeaders($headers);

        $imported = 0;
        $skipped = 0;
        $failed = 0;

        $batchId = 'CSV_' . now()->format('YmdHis');

        // Set flag to indicate we're in CSV import mode
        // This will suppress notifications to prevent lag/crash during bulk imports
        config(['app.csv_import_mode' => true]);

        while (($data = fgetcsv($handle)) !== false) {
            if (empty(array_filter($data))) {
                continue;
            }

            $row = array_combine($headers, $data);

            // Skip rows without required fields
            if (empty(trim($row['AGENDA'] ?? '')) || empty(trim($row['NO SPP'] ?? ''))) {
                $failed++;
                Log::warning('Skipping row without required fields', [
                    'row_data' => $row
                ]);
                continue;
            }

            // Check duplicate
            if ($skipDuplicates && !empty($row['AGENDA'])) {
                if (Dokumen::where('nomor_agenda', trim($row['AGENDA']))->exists()) {
                    $skipped++;
                    continue;
                }
            }

            try {
                DB::beginTransaction();

                $dokumenData = $this->transformRow($row);

                // Ensure required fields are not empty
                if (empty($dokumenData['nomor_agenda']) || empty($dokumenData['nomor_spp'])) {
                    throw new \Exception('Nomor Agenda atau Nomor SPP tidak boleh kosong');
                }

                // Ensure kategori and jenis_dokumen are set
                if (empty($dokumenData['kategori'])) {
                    $dokumenData['kategori'] = 'CAPEX';
                }
                if (empty($dokumenData['jenis_dokumen'])) {
                    $dokumenData['jenis_dokumen'] = 'Lainnya';
                }

                // Only set CSV import tracking fields if columns exist
                if (\Schema::hasColumn('dokumens', 'imported_from_csv')) {
                    $dokumenData['imported_from_csv'] = true;
                }
                if (\Schema::hasColumn('dokumens', 'csv_import_batch_id')) {
                    $dokumenData['csv_import_batch_id'] = $batchId;
                }
                if (\Schema::hasColumn('dokumens', 'csv_imported_at')) {
                    $dokumenData['csv_imported_at'] = now();
                }

                $dokumen = Dokumen::create($dokumenData);

                // Create vendor relationship if exists
                if (!empty($dokumenData['_vendor'])) {
                    // This would be handled by DibayarKepada model relationship
                    // For now, we skip this
                }

                DB::commit();
                $imported++;

            } catch (\Exception $e) {
                DB::rollBack();

                // Get detailed error info
                $errorMessage = $e->getMessage();
                $errorCode = $e->getCode();

                Log::error('Import row failed', [
                    'error' => $errorMessage,
                    'code' => $errorCode,
                    'nomor_agenda' => $row['AGENDA'] ?? 'N/A',
                    'row_data' => $row,
                    'transformed_data' => $dokumenData ?? [],
                    'trace' => $e->getTraceAsString()
                ]);

                $failed++;
            }
        }

        fclose($handle);

        // Reset CSV import mode flag
        config(['app.csv_import_mode' => false]);

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'failed' => $failed,
            'batch_id' => $batchId,
        ];
    }

    private function transformRow($row)
    {
        // Parse tanggal_masuk first for use as default
        $tanggalMasuk = $this->parseDate($row['TGL SPP'] ?? $row['TANGGAL MASUK DOKUMEN'] ?? null) ?? now()->format('Y-m-d H:i:s');

        // Ensure we have Carbon instance for bulan/tahun extraction
        $carbonDate = Carbon::parse($tanggalMasuk);

        // Extract kategori and jenis_dokumen from CSV
        // KATEGORI from CSV maps to kategori field (required)
        $kategori = trim($row['KATEGORI'] ?? '');
        if (empty($kategori)) {
            $kategori = 'CAPEX'; // Default value if empty
        }

        // Sub Pekerjaan from CSV maps to jenis_dokumen (required)
        $jenisDokumen = trim($row['Sub Pekerjaan'] ?? '');
        if (empty($jenisDokumen)) {
            $jenisDokumen = 'Lainnya'; // Default value if empty
        }

        return [
            // Core required fields
            'nomor_agenda' => trim($row['AGENDA'] ?? ''),
            'nomor_spp' => trim($row['NO SPP'] ?? ''),
            'uraian_spp' => trim($row['HAL'] ?? ''),
            'nilai_rupiah' => $this->cleanNumeric($row['NILAI'] ?? 0),

            // Required: bulan and tahun (extracted from tanggal_masuk)
            'bulan' => $carbonDate->format('n'), // 1-12
            'tahun' => $carbonDate->format('Y'), // YYYY

            // Required: kategori and jenis_dokumen
            'kategori' => $kategori,
            'jenis_dokumen' => $jenisDokumen,
            'jenis_sub_pekerjaan' => trim($row['Sub Pekerjaan'] ?? null), // Optional, can be null

            // Dates - tanggal_spp cannot be null, use tanggal_masuk as default
            'tanggal_masuk' => $tanggalMasuk,
            'tanggal_spp' => $this->parseDate($row['TGL SPP'] ?? null) ?? $tanggalMasuk,

            // Contract info
            'no_kontrak' => trim($row['NO KONTRAK'] ?? null),
            'tanggal_kontrak' => $this->parseDate($row['TGL. KONTRAK'] ?? null),

            // Berita Acara - handle multiple possible header formats
            'no_berita_acara' => trim($row['NO. BERITA ACARA'] ?? $row['NO BERITA ACARA'] ?? null),
            'tanggal_berita_acara' => $this->parseDate($row['TGL. BERITA ACARA'] ?? $row['TGL BERITA ACARA'] ?? null),

            // SPK info (if available in CSV) - handle multiple possible header formats
            'no_spk' => trim($row['NO SPK'] ?? $row['NO. SPK'] ?? null),
            'tanggal_spk' => $this->parseDate($row['TGL. SPK'] ?? $row['TGL SPK'] ?? null),
            'tanggal_berakhir_spk' => $this->parseDate($row['TGL. BERAKHIR KONTRAK'] ?? $row['TGL BERAKHIR KONTRAK'] ?? null),

            // Status
            'status' => 'sent_to_pembayaran',
            'status_pembayaran' => 'belum_dibayar',
            'current_handler' => 'pembayaran',

            // Payment info
            'tanggal_dibayar' => $this->getLastPaymentDate($row),

            // Kebun/Vendor info
            'kebun' => trim($row['KEBUN'] ?? null),
            'nama_kebuns' => trim($row['KEBUN'] ?? null), // Keep for compatibility
            'dibayar_kepada' => trim($row['VENDOR'] ?? null),

            // CSV Import metadata (will be added later in executeImport)
            '_vendor' => trim($row['VENDOR'] ?? null),
        ];
    }

    private function getLastPaymentDate($row)
    {
        $paymentDateKeys = [
            'TANGGAL BAYAR RAMPUNG',
            'TANGGAL BAYAR VI',
            'TANGGAL BAYAR V',
            'TANGGAL BAYAR IV',
            'TANGGAL BAYAR III',
            'TANGGAL BAYAR II',
            'TANGGAL BAYAR I',
        ];

        foreach ($paymentDateKeys as $key) {
            if (!empty($row[$key])) {
                return $this->parseDate($row[$key]);
            }
        }

        return null;
    }

    private function parseDate($date)
    {
        if (empty($date)) {
            return null;
        }

        $dateStr = trim($date);
        if (empty($dateStr) || $dateStr === '-' || $dateStr === '0') {
            return null;
        }

        // Try multiple date formats commonly used in CSV
        $formats = [
            'd/m/Y',           // 20/01/2023
            'd-m-Y',           // 20-01-2023
            'Y-m-d',           // 2023-01-20
            'd M Y',           // 20 Jan 2023
            'd F Y',           // 20 January 2023
            'd/m/Y H:i:s',     // 20/01/2023 10:30:00
            'd-m-Y H:i:s',     // 20-01-2023 10:30:00
            'Y-m-d H:i:s',     // 2023-01-20 10:30:00
        ];

        foreach ($formats as $format) {
            try {
                $d = Carbon::createFromFormat($format, $dateStr);
                if ($d && $d->year > 1900 && $d->year < 2100) { // Sanity check
                    return $d->format('Y-m-d H:i:s');
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        // Last resort: try Carbon's flexible parser
        try {
            $d = Carbon::parse($dateStr);
            if ($d && $d->year > 1900 && $d->year < 2100) {
                return $d->format('Y-m-d H:i:s');
            }
        } catch (\Exception $e) {
            // Ignore
        }

        Log::warning('Failed to parse date', ['date' => $dateStr]);
        return null;
    }

    private function cleanNumeric($value)
    {
        if (empty($value)) {
            return 0;
        }

        // Remove all non-numeric characters except minus and comma (for decimal)
        $cleaned = preg_replace('/[^0-9,.-]/', '', trim($value));

        // Handle Indonesian number format: dots are thousand separators, comma is decimal separator
        // Example: "159.094.195" or "17.659.007" should become 159094195 or 17659007
        // Example: "1.234,56" should become 1234.56

        // Check if comma exists (decimal separator in Indonesian format)
        if (strpos($cleaned, ',') !== false) {
            // Replace dots (thousand separators) with nothing
            $cleaned = str_replace('.', '', $cleaned);
            // Replace comma (decimal separator) with dot
            $cleaned = str_replace(',', '.', $cleaned);
        } else {
            // No comma, so dots are thousand separators - remove them all
            $cleaned = str_replace('.', '', $cleaned);
        }

        // Remove any remaining non-numeric characters except minus and dot
        $cleaned = preg_replace('/[^0-9.-]/', '', $cleaned);

        // Convert to float
        $result = (float) $cleaned;

        // Ensure non-negative (unless explicitly negative)
        if ($result < 0 && strpos($value, '-') === false) {
            $result = abs($result);
        }

        return $result;
    }
}

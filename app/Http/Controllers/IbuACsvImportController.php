<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Dokumen;
use Carbon\Carbon;

class IbuACsvImportController extends Controller
{
    /**
     * Show import page for IbuA role
     */
    public function index()
    {
        return view('bagian.dokumens.importCsv', [
            'title' => 'Import Data CSV - Dokumen',
            'module' => 'ibua',
            'menuDokumen' => 'active'
        ]);
    }

    /**
     * Upload and parse CSV file
     */
    public function upload(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240', // 10MB max
        ]);

        try {
            $file = $request->file('csv_file');

            // Sanitize filename
            $originalName = $file->getClientOriginalName();
            $sanitizedName = preg_replace('/[^A-Za-z0-9._-]/', '_', $originalName);
            $filename = time() . '_' . $sanitizedName;

            // Ensure directory exists
            $csvImportsPath = storage_path('app/csv_imports');
            if (!is_dir($csvImportsPath)) {
                mkdir($csvImportsPath, 0755, true);
            }

            // Store file
            $destinationPath = $csvImportsPath . '/' . $filename;
            $file->move($csvImportsPath, $filename);

            if (!file_exists($destinationPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File upload gagal: File tidak dapat disimpan'
                ], 500);
            }

            // Parse CSV for preview
            $previewData = $this->parseAndPreviewCsv($destinationPath);

            return response()->json([
                'success' => true,
                'message' => 'File berhasil di-upload',
                'file_path' => 'csv_imports/' . $filename,
                'filename' => $filename,
                'preview' => $previewData,
            ]);

        } catch (\Exception $e) {
            Log::error('IbuA CSV Upload Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Parse CSV and get preview data
     */
    private function parseAndPreviewCsv($filePath)
    {
        // Check encoding and convert if needed
        $content = file_get_contents($filePath);
        $encoding = mb_detect_encoding($content, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);

        if ($encoding !== 'UTF-8') {
            $content = mb_convert_encoding($content, 'UTF-8', $encoding);
            file_put_contents($filePath, $content);
        }

        $rows = [];
        $handle = fopen($filePath, 'r');

        // First row is headers
        $headers = fgetcsv($handle);
        $headers = $this->cleanHeaders($headers);

        // Get first 10 data rows for preview
        $rowCount = 0;
        while (($data = fgetcsv($handle)) !== false && $rowCount < 10) {
            if (!empty(array_filter($data))) {
                $rows[] = $data;
                $rowCount++;
            }
        }

        fclose($handle);

        // Get total rows
        $totalRows = count(file($filePath)) - 1; // Minus header

        return [
            'headers' => $headers,
            'rows' => $rows,
            'total_rows' => $totalRows,
        ];
    }

    /**
     * Clean header names
     */
    private function cleanHeaders($headers)
    {
        return array_map(function ($header) {
            return trim($header);
        }, $headers);
    }

    /**
     * Preview and validate all rows
     */
    public function preview(Request $request)
    {
        $request->validate([
            'file_path' => 'required|string'
        ]);

        try {
            $fullPath = storage_path('app/' . $request->file_path);

            if (!file_exists($fullPath)) {
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
            Log::error('IbuA CSV Preview Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate all rows in CSV
     */
    private function validateAllRows($filePath)
    {
        $handle = fopen($filePath, 'r');

        $headers = fgetcsv($handle);
        $headers = $this->cleanHeaders($headers);

        $validCount = 0;
        $errorCount = 0;
        $warningCount = 0;
        $errors = [];
        $warnings = [];

        $rowNumber = 2; // Start from row 2 (after header)

        while (($data = fgetcsv($handle)) !== false) {
            if (empty(array_filter($data))) {
                continue;
            }

            // Ensure data array matches headers count
            while (count($data) < count($headers)) {
                $data[] = '';
            }

            $row = array_combine($headers, $data);
            $validation = $this->validateRow($row, $rowNumber);

            if (!empty($validation['errors'])) {
                $errorCount++;
                $errors[] = [
                    'row' => $rowNumber,
                    'nomor_agenda' => $row['Agenda'] ?? '-',
                    'errors' => $validation['errors']
                ];
            } elseif (!empty($validation['warnings'])) {
                $warningCount++;
                $warnings[] = [
                    'row' => $rowNumber,
                    'nomor_agenda' => $row['Agenda'] ?? '-',
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
            'total' => $rowNumber - 2,
            'valid' => $validCount,
            'errors' => $errorCount,
            'warnings' => $warningCount,
            'error_details' => array_slice($errors, 0, 10),
            'warning_details' => array_slice($warnings, 0, 10),
        ];
    }

    /**
     * Validate single row
     */
    private function validateRow($row, $rowNumber)
    {
        $errors = [];
        $warnings = [];

        // Required fields
        if (empty($row['Agenda'])) {
            $errors[] = 'Nomor Agenda wajib diisi';
        }

        if (empty($row['No SPP'])) {
            $errors[] = 'Nomor SPP wajib diisi';
        }

        if (empty($row['Nilai'])) {
            $errors[] = 'Nilai wajib diisi';
        }

        // Check duplicate based on nomor_agenda
        if (!empty($row['Agenda'])) {
            $existingDoc = Dokumen::where('nomor_agenda', trim($row['Agenda']))->first();
            if ($existingDoc) {
                $warnings[] = "Nomor Agenda '{$row['Agenda']}' sudah ada di database (akan di-skip)";
            }
        }

        return [
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Execute import
     */
    public function import(Request $request)
    {
        $request->validate([
            'file_path' => 'required|string',
            'skip_duplicates' => 'boolean',
        ]);

        try {
            $fullPath = storage_path('app/' . $request->file_path);

            if (!file_exists($fullPath)) {
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
            Log::error('IbuA CSV Import Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Execute the actual import
     */
    private function executeImport($filePath, $skipDuplicates)
    {
        $handle = fopen($filePath, 'r');

        $headers = fgetcsv($handle);
        $headers = $this->cleanHeaders($headers);

        $imported = 0;
        $skipped = 0;
        $failed = 0;

        $batchId = 'IBUA_CSV_' . now()->format('YmdHis');

        while (($data = fgetcsv($handle)) !== false) {
            if (empty(array_filter($data))) {
                continue;
            }

            // Ensure data array matches headers count
            while (count($data) < count($headers)) {
                $data[] = '';
            }

            $row = array_combine($headers, $data);

            // Skip rows without required fields
            if (empty(trim($row['Agenda'] ?? '')) || empty(trim($row['No SPP'] ?? ''))) {
                $failed++;
                continue;
            }

            // Check duplicate
            if ($skipDuplicates && !empty($row['Agenda'])) {
                if (Dokumen::where('nomor_agenda', trim($row['Agenda']))->exists()) {
                    $skipped++;
                    continue;
                }
            }

            try {
                DB::beginTransaction();

                $dokumenData = $this->transformRow($row);

                // Set created_by to IbuA
                $dokumenData['created_by'] = 'ibuA';
                $dokumenData['status'] = 'belum_dikirim';

                // CSV import tracking
                if (\Schema::hasColumn('dokumens', 'imported_from_csv')) {
                    $dokumenData['imported_from_csv'] = true;
                }
                if (\Schema::hasColumn('dokumens', 'csv_import_batch_id')) {
                    $dokumenData['csv_import_batch_id'] = $batchId;
                }
                if (\Schema::hasColumn('dokumens', 'csv_imported_at')) {
                    $dokumenData['csv_imported_at'] = now();
                }

                Dokumen::create($dokumenData);

                DB::commit();
                $imported++;

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Import row failed', [
                    'error' => $e->getMessage(),
                    'nomor_agenda' => $row['Agenda'] ?? 'N/A',
                ]);
                $failed++;
            }
        }

        fclose($handle);

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'failed' => $failed,
            'batch_id' => $batchId,
        ];
    }

    /**
     * Transform CSV row to database fields
     * 
     * CSV Format: Agenda, Bulan, Tahun, Kriteria, No SPP, Tanggal SPP, Tanggal Masuk, Dibayarkan Kepada, Uraian SPP, Nilai
     */
    private function transformRow($row)
    {
        // Parse bulan from text to number
        $bulanMap = [
            'januari' => 1,
            'februari' => 2,
            'maret' => 3,
            'april' => 4,
            'mei' => 5,
            'juni' => 6,
            'juli' => 7,
            'agustus' => 8,
            'september' => 9,
            'oktober' => 10,
            'november' => 11,
            'desember' => 12,
        ];
        $bulanText = strtolower(trim($row['Bulan'] ?? ''));
        $bulan = $bulanMap[$bulanText] ?? (int) $bulanText ?: (int) date('n');

        // Parse tahun
        $tahun = trim($row['Tahun'] ?? '') ?: date('Y');

        // Parse tanggal
        $tanggalMasuk = $this->parseDate($row['Tanggal Masuk'] ?? null) ?? now()->format('Y-m-d H:i:s');
        $tanggalSpp = $this->parseDate($row['Tanggal SPP'] ?? null) ?? $tanggalMasuk;

        // Parse kriteria - the format is like "5SKH/SPP/01/I/2026"
        // We'll store it as jenis_dokumen or kategori based on pattern
        $kriteria = trim($row['Kriteria'] ?? '');

        return [
            'nomor_agenda' => trim($row['Agenda'] ?? ''),
            'bulan' => $bulan,
            'tahun' => $tahun,
            'nomor_spp' => trim($row['No SPP'] ?? $kriteria), // Use Kriteria as No SPP if not provided
            'tanggal_spp' => $tanggalSpp,
            'tanggal_masuk' => $tanggalMasuk,
            'dibayar_kepada' => trim($row['Dibayarkan Kepada'] ?? ''),
            'uraian_spp' => trim($row['Uraian SPP'] ?? ''),
            'nilai_rupiah' => $this->cleanNumeric($row['Nilai'] ?? 0),
            'kategori' => 'CAPEX', // Default kategori
            'jenis_dokumen' => 'Lainnya', // Default jenis
        ];
    }

    /**
     * Parse date from various formats
     */
    private function parseDate($date)
    {
        if (empty($date)) {
            return null;
        }

        $dateStr = trim($date);
        if (empty($dateStr) || $dateStr === '-' || $dateStr === '0') {
            return null;
        }

        $formats = [
            'd/m/Y',
            'd-m-Y',
            'Y-m-d',
            'd/m/Y H:i:s',
            'd-m-Y H:i:s',
            'Y-m-d H:i:s',
        ];

        foreach ($formats as $format) {
            try {
                $d = Carbon::createFromFormat($format, $dateStr);
                if ($d && $d->year > 1900 && $d->year < 2100) {
                    return $d->format('Y-m-d H:i:s');
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        // Try Carbon's flexible parser
        try {
            $d = Carbon::parse($dateStr);
            if ($d && $d->year > 1900 && $d->year < 2100) {
                return $d->format('Y-m-d H:i:s');
            }
        } catch (\Exception $e) {
            // Ignore
        }

        return null;
    }

    /**
     * Clean numeric value (handle Indonesian format)
     */
    private function cleanNumeric($value)
    {
        if (empty($value)) {
            return 0;
        }

        $cleaned = preg_replace('/[^0-9,.-]/', '', trim($value));

        // Handle Indonesian format (dots = thousands, comma = decimal)
        if (strpos($cleaned, ',') !== false) {
            $cleaned = str_replace('.', '', $cleaned);
            $cleaned = str_replace(',', '.', $cleaned);
        } else {
            $cleaned = str_replace('.', '', $cleaned);
        }

        return (float) $cleaned;
    }
}

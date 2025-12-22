<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Dokumen;
use Carbon\Carbon;

class CsvImportController extends Controller
{
    public function index()
    {
        return view('pembayaranNEW.import.index', [
            'title' => 'Import Data CSV - Pembayaran'
        ]);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240', // 10MB max
        ]);

        try {
            $file = $request->file('csv_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('csv_imports', $filename);

            // Parse CSV untuk preview
            $fullPath = storage_path('app/' . $path);
            $previewData = $this->parseAndPreviewCsv($fullPath);

            return response()->json([
                'success' => true,
                'message' => 'File berhasil di-upload',
                'file_path' => $path,
                'filename' => $filename,
                'preview' => $previewData,
            ]);

        } catch (\Exception $e) {
            Log::error('CSV Upload Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    private function parseAndPreviewCsv($filePath)
    {
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

        while (($data = fgetcsv($handle)) !== false) {
            if (empty(array_filter($data))) {
                continue;
            }

            $row = array_combine($headers, $data);

            // Check duplicate
            if ($skipDuplicates && !empty($row['AGENDA'])) {
                if (Dokumen::where('nomor_agenda', $row['AGENDA'])->exists()) {
                    $skipped++;
                    continue;
                }
            }

            try {
                DB::beginTransaction();

                $dokumenData = $this->transformRow($row);
                $dokumenData['imported_from_csv'] = true;
                $dokumenData['csv_import_batch_id'] = $batchId;
                $dokumenData['csv_imported_at'] = now();

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
                Log::error('Import row error: ' . $e->getMessage(), ['row' => $row]);
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

    private function transformRow($row)
    {
        return [
            'nomor_agenda' => $row['AGENDA'] ?? null,
            'nomor_spp' => $row['NO SPP'] ?? null,
            'uraian_spp' => $row['HAL'] ?? null,
            'nilai_rupiah' => $this->cleanNumeric($row['NILAI'] ?? 0),
            'tanggal_masuk' => $this->parseDate($row['TGL SPP'] ?? $row['TANGGAL MASUK DOKUMEN'] ?? null),
            'no_kontrak' => $row['NO KONTRAK'] ?? null,
            'tanggal_kontrak' => $this->parseDate($row['TGL. KONTRAK'] ?? null),
            'no_berita_acara' => $row['NO BERITA ACARA'] ?? null,
            'tanggal_berita_acara' => $this->parseDate($row['TGL. BERITA ACARA'] ?? null),
            'status' => 'sent_to_pembayaran',
            'tanggal_dibayar' => $this->getLastPaymentDate($row),
            '_vendor' => $row['VENDOR'] ?? null,
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

        $formats = ['d/m/Y', 'd-m-Y', 'Y-m-d', 'd M Y', 'd F Y'];

        foreach ($formats as $format) {
            try {
                $d = Carbon::createFromFormat($format, trim($date));
                if ($d) {
                    return $d->format('Y-m-d H:i:s');
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return null;
    }

    private function cleanNumeric($value)
    {
        if (empty($value)) {
            return 0;
        }
        $cleaned = preg_replace('/[^0-9.-]/', '', $value);
        return (float) $cleaned;
    }
}

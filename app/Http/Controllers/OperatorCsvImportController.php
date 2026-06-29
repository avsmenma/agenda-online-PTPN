<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Dokumen;
use Carbon\Carbon;
use App\Traits\LogsProgrammerActivity;

class OperatorCsvImportController extends Controller
{
    use LogsProgrammerActivity;
    /**
     * Show import page for Operator role
     */
    public function index()
    {
        return view('bagian.dokumens.importCsv', [
            'title' => 'Import Data CSV - Dokumen',
            'module' => 'operator',
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
            Log::error('Operator CSV Upload Error: ' . $e->getMessage(), [
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
        $rawHeaders = fgetcsv($handle);
        $headerData = $this->cleanHeaders($rawHeaders);
        $headers = $headerData['headers'];
        $skipFirstColumn = $headerData['skip_first'];
        $validIndices = $headerData['valid_indices'];

        // Get first 10 data rows for preview
        $rowCount = 0;
        while (($data = fgetcsv($handle)) !== false && $rowCount < 10) {
            if (!empty(array_filter($data))) {
                // Skip first column if needed
                if ($skipFirstColumn && count($data) > 0) {
                    array_shift($data);
                }

                // Only take columns at valid indices
                $filteredData = [];
                foreach ($validIndices as $index) {
                    $filteredData[] = $data[$index] ?? '';
                }

                $rows[] = $filteredData;
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
     * Clean header names and handle empty first column.
     *
     * Header names di spreadsheet bisa berubah-ubah (mis. istilah "SPP" diganti
     * "PP"/"Permintaan Pembayaran", beda kapitalisasi, ada spasi/baris baru, atau
     * imbuhan seperti "(Rp)"/"(BA)"/"(PP)"). Maka pencocokan dilakukan secara
     * tahan-banting: tiap header dinormalisasi (lowercase, rapikan spasi, buang
     * imbuhan) lalu dicocokkan ke daftar alias menuju nama kolom kanonik yang
     * dipakai transformRow()/validateRow().
     *
     * Kolom yang tidak dikenali (mis. "Kontrol", "LINK", kolom pajak) dilewati.
     */
    private function cleanHeaders($headers)
    {
        // Check if first column is empty (common issue with some CSV exports)
        $skipFirstColumn = false;
        if (count($headers) > 0 && empty(trim($headers[0]))) {
            // Remove empty first column
            array_shift($headers);
            $skipFirstColumn = true;
        }

        $aliasMap = $this->headerAliasMap();

        // Clean headers and track which indices to keep
        $filteredHeaders = [];
        $validIndices = [];
        $usedCanonical = [];

        foreach ($headers as $index => $header) {
            $canonical = $aliasMap[$this->normalizeHeader($header)] ?? null;

            // Lewati kolom tak dikenal & hindari kolom kanonik ganda (jaga
            // array_combine tetap valid di pemanggil).
            if ($canonical === null || isset($usedCanonical[$canonical])) {
                continue;
            }

            $usedCanonical[$canonical] = true;
            $filteredHeaders[] = $canonical;
            $validIndices[] = $index;
        }

        return [
            'headers' => $filteredHeaders,
            'skip_first' => $skipFirstColumn,
            'valid_indices' => $validIndices,
        ];
    }

    /**
     * Normalisasi satu nama header untuk pencocokan alias.
     * - rapikan spasi/baris baru jadi satu spasi
     * - buang imbuhan satuan/jenis: (Rp) (BA) (PP) (SPP)
     * - lowercase + trim tanda baca/dot di ujung
     */
    private function normalizeHeader($header)
    {
        $normalized = strtolower((string) $header);
        $normalized = preg_replace('/\s+/', ' ', $normalized);        // newline & spasi ganda -> 1 spasi
        $normalized = str_replace(['(rp)', '(ba)', '(pp)', '(spp)'], '', $normalized);
        $normalized = preg_replace('/[\s.]+$/', '', trim($normalized)); // buang dot/spasi di ujung
        return trim($normalized);
    }

    /**
     * Peta alias header (sudah dinormalisasi) -> nama kolom kanonik.
     * Mendukung istilah lama "SPP" maupun baru "PP/Permintaan Pembayaran".
     */
    private function headerAliasMap()
    {
        return [
            'agenda' => 'Agenda',
            'bulan' => 'Bulan',
            'tahun' => 'Tahun',
            'kriteria' => 'Kriteria',

            // Nomor SPP / PP
            'no spp' => 'No SPP', 'no pp' => 'No SPP',
            'nomor spp' => 'No SPP', 'nomor pp' => 'No SPP',

            // Tanggal SPP / PP
            'tanggal spp' => 'Tanggal SPP', 'tanggal pp' => 'Tanggal SPP',
            'tgl spp' => 'Tanggal SPP', 'tgl pp' => 'Tanggal SPP',

            'tanggal masuk' => 'Tanggal Masuk', 'tgl masuk' => 'Tanggal Masuk',

            'dibayarkan kepada' => 'Dibayarkan Kepada',
            'dibayar kepada' => 'Dibayarkan Kepada',
            'dibayarkan kpd' => 'Dibayarkan Kepada',

            // Uraian SPP / PP / Permintaan Pembayaran
            'uraian spp' => 'Uraian SPP', 'uraian pp' => 'Uraian SPP',
            'uraian permintaan pembayaran' => 'Uraian SPP', 'uraian' => 'Uraian SPP',

            'nilai' => 'Nilai', // "Nilai (Rp)" -> "nilai" setelah normalisasi

            // SPK
            'no spk' => 'No SPK',
            'tanggal spk' => 'Tanggal SPK',
            'tgl akhir spk' => 'Tgl. Akhir SPK', 'tgl. akhir spk' => 'Tgl. Akhir SPK',

            // Berita Acara
            'no berita acara' => 'No Berita Acara',
            'tanggal berita acara' => 'Tanggal Berita Acara',

            // PO & MIRO
            'no po' => 'No. PO', 'no. po' => 'No. PO',
            'no miro/ses' => 'No. Miro/SES', 'no. miro/ses' => 'No. Miro/SES',

            // Paraf & proses
            'tanggal paraf' => 'Tanggal Paraf',
            'pemaraf' => 'Pemaraf',
            'tgl selesai diproses' => 'Tgl selesai diproses',
            'kepala sub bagian' => 'Kepala Sub Bagian',
            'status dokumen' => 'Status Dokumen',
            'tanggal bayar' => 'Tanggal Bayar',
        ];
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
            Log::error('Operator CSV Preview Error: ' . $e->getMessage());
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

        $rawHeaders = fgetcsv($handle);
        $headerData = $this->cleanHeaders($rawHeaders);
        $headers = $headerData['headers'];
        $skipFirstColumn = $headerData['skip_first'];
        $validIndices = $headerData['valid_indices'];

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

            // Skip first column if needed
            if ($skipFirstColumn && count($data) > 0) {
                array_shift($data);
            }

            // Only take columns at valid indices
            $filteredData = [];
            foreach ($validIndices as $index) {
                $filteredData[] = $data[$index] ?? '';
            }

            $row = array_combine($headers, $filteredData);
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

            // Catat ke audit trail - operasi import CSV
            $this->logActivity('bulk_import_csv', null,
                "Import CSV: {$result['imported']} berhasil, {$result['skipped']} dilewati, {$result['failed']} gagal (Batch ID: {$result['batch_id']})"
            );

            return response()->json([
                'success' => true,
                'message' => 'Import berhasil!',
                'result' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Operator CSV Import Error: ' . $e->getMessage());
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
        @set_time_limit(0);

        $handle = fopen($filePath, 'r');

        $rawHeaders = fgetcsv($handle);
        $headerData = $this->cleanHeaders($rawHeaders);
        $headers = $headerData['headers'];
        $skipFirstColumn = $headerData['skip_first'];
        $validIndices = $headerData['valid_indices'];

        $imported = 0;
        $skipped = 0;
        $failed = 0;

        $batchId = 'OPERATOR_CSV_' . now()->format('YmdHis');
        $now = now()->format('Y-m-d H:i:s');
        $dokumenColumns = array_flip(\Schema::getColumnListing('dokumens'));
        $candidateRows = [];
        $seenAgendaInFile = [];

        while (($data = fgetcsv($handle)) !== false) {
            if (empty(array_filter($data))) {
                continue;
            }

            // Skip first column if needed
            if ($skipFirstColumn && count($data) > 0) {
                array_shift($data);
            }

            // Only take columns at valid indices
            $filteredData = [];
            foreach ($validIndices as $index) {
                $filteredData[] = $data[$index] ?? '';
            }

            $row = array_combine($headers, $filteredData);
            if ($row === false || empty($row)) {
                $failed++;
                continue;
            }

            // Skip rows without required fields
            if (empty(trim($row['Agenda'] ?? '')) || empty(trim($row['No SPP'] ?? ''))) {
                $failed++;
                continue;
            }

            $dokumenData = $this->transformRow($row);

            // Set created_by to Operator and correct status
            $dokumenData['created_by'] = 'operator';
            $dokumenData['status'] = 'draft';
            $dokumenData['current_handler'] = 'operator';
            $dokumenData['created_at'] = $now;
            $dokumenData['updated_at'] = $now;

            // CSV import tracking
            if (isset($dokumenColumns['imported_from_csv'])) {
                $dokumenData['imported_from_csv'] = true;
            }
            if (isset($dokumenColumns['csv_import_batch_id'])) {
                $dokumenData['csv_import_batch_id'] = $batchId;
            }
            if (isset($dokumenColumns['csv_imported_at'])) {
                $dokumenData['csv_imported_at'] = $now;
            }

            $dokumenData = array_intersect_key($dokumenData, $dokumenColumns);
            $nomorAgenda = $dokumenData['nomor_agenda'] ?? null;

            if ($skipDuplicates && $nomorAgenda && isset($seenAgendaInFile[$nomorAgenda])) {
                $skipped++;
                continue;
            }

            if ($nomorAgenda) {
                $seenAgendaInFile[$nomorAgenda] = true;
            }

            $candidateRows[] = $dokumenData;
        }

        fclose($handle);

        $existingAgenda = [];
        if ($skipDuplicates && !empty($candidateRows)) {
            $agendaNumbers = array_values(array_filter(array_unique(array_column($candidateRows, 'nomor_agenda'))));
            foreach (array_chunk($agendaNumbers, 500) as $agendaChunk) {
                $existing = DB::table('dokumens')
                    ->whereIn('nomor_agenda', $agendaChunk)
                    ->pluck('nomor_agenda')
                    ->all();

                foreach ($existing as $agenda) {
                    $existingAgenda[$agenda] = true;
                }
            }
        }

        $rowsToInsert = [];
        foreach ($candidateRows as $dokumenData) {
            $nomorAgenda = $dokumenData['nomor_agenda'] ?? null;
            if ($skipDuplicates && $nomorAgenda && isset($existingAgenda[$nomorAgenda])) {
                $skipped++;
                continue;
            }

            $rowsToInsert[] = $dokumenData;
        }

        foreach (array_chunk($rowsToInsert, 500) as $chunk) {
            try {
                DB::table('dokumens')->insert($chunk);
                $imported += count($chunk);
            } catch (\Throwable $e) {
                Log::warning('Operator CSV chunk insert failed, retrying per row', [
                    'error' => $e->getMessage(),
                    'rows' => count($chunk),
                ]);

                foreach ($chunk as $dokumenData) {
                    try {
                        DB::table('dokumens')->insert($dokumenData);
                        $imported++;
                    } catch (\Throwable $rowException) {
                        Log::error('Import row failed', [
                            'error' => $rowException->getMessage(),
                            'nomor_agenda' => $dokumenData['nomor_agenda'] ?? 'N/A',
                        ]);
                        $failed++;
                    }
                }
            }
        }

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
        // Parse bulan from text - store as month name (e.g., "Januari", "Februari")
        $bulanNameMap = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
        $bulanValidNames = array_map('strtolower', $bulanNameMap);
        $bulanText = strtolower(trim($row['Bulan'] ?? ''));
        // Clean prefix like "a. ", "b. ", etc. from format like "a. Januari", "b. Februari"
        $bulanText = preg_replace('/^[a-z]\.\s*/i', '', $bulanText);
        // If it's a recognized month name, capitalize it; if numeric, convert to name
        $bulanKey = array_search($bulanText, $bulanValidNames);
        if ($bulanKey !== false) {
            $bulan = $bulanNameMap[$bulanKey];
        } elseif (is_numeric($bulanText) && isset($bulanNameMap[(int) $bulanText])) {
            $bulan = $bulanNameMap[(int) $bulanText];
        } else {
            $bulan = $bulanNameMap[(int) date('n')];
        }

        // Parse tahun
        $tahun = trim($row['Tahun'] ?? '') ?: date('Y');

        // Parse tanggal
        $tanggalMasuk = $this->parseDate($row['Tanggal Masuk'] ?? null) ?? now()->format('Y-m-d H:i:s');
        $tanggalSpp = $this->parseDate($row['Tanggal SPP'] ?? null) ?? $tanggalMasuk;

        // Parse kriteria - the format is like "5SKH/SPP/01/I/2026"
        // We'll store it as jenis_dokumen or kategori based on pattern
        $kriteria = trim($row['Kriteria'] ?? '');

        // Get nomor agenda and add _2026 suffix
        $nomorAgenda = trim($row['Agenda'] ?? '');
        if (!empty($nomorAgenda) && !str_contains($nomorAgenda, '_2026')) {
            $nomorAgenda .= '_2026';
        }

        // Extract bagian from No SPP
        $nomorSpp = trim($row['No SPP'] ?? $kriteria);
        $bagian = $this->extractBagianFromSpp($nomorSpp);

        return [
            'nomor_agenda' => $nomorAgenda,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'nomor_spp' => $nomorSpp,
            'tanggal_spp' => $tanggalSpp,
            'tanggal_masuk' => $tanggalMasuk,
            'dibayar_kepada' => trim($row['Dibayarkan Kepada'] ?? ''),
            'uraian_spp' => trim($row['Uraian SPP'] ?? ''),
            'nilai_rupiah' => $this->cleanNumeric($row['Nilai'] ?? 0),
            'kategori' => 'CAPEX', // Default kategori
            'jenis_dokumen' => 'Lainnya', // Default jenis
            'bagian' => $bagian, // Auto-extracted from No SPP
            // SPK fields
            'no_spk' => trim($row['No SPK'] ?? '') ?: null,
            'tanggal_spk' => $this->parseDate($row['Tanggal SPK'] ?? null),
            'tanggal_berakhir_spk' => $this->parseDate($row['Tgl. Akhir SPK'] ?? null),
            // BA fields
            'no_berita_acara' => trim($row['No Berita Acara'] ?? '') ?: null,
            'tanggal_berita_acara' => $this->parseDate($row['Tanggal Berita Acara'] ?? null),
            // PO and MIRO
            'NO_PO' => trim($row['No. PO'] ?? '') ?: null,
            'NO_MIRO_SES' => trim($row['No. Miro/SES'] ?? '') ?: null,
            // Paraf & processing fields
            'tanggal_paraf' => $this->parseDate($row['Tanggal Paraf'] ?? null),
            'pemaraf' => trim($row['Pemaraf'] ?? '') ?: null,
            'tanggal_selesai_diproses' => $this->parseDate($row['Tgl selesai diproses'] ?? null),
            'kepala_sub_bagian' => trim($row['Kepala Sub Bagian'] ?? '') ?: null,
            'status_dokumen_csv' => trim($row['Status Dokumen'] ?? '') ?: null,
            'tanggal_dibayar' => $this->parseDate($row['Tanggal Bayar'] ?? null),
        ];
    }

    /**
     * Extract bagian code from No SPP
     * Format: {nomor}{BAGIAN}/SPP/{nomor}/{bulan_romawi}/{tahun}
     * Example: 5TAN/SPP/131/I/2026 -> "Tanaman"
     *          5SKH/SPP/01/I/2026 -> "Sekretariat & Hukum"
     *          5DPM/5AKN/SPP/... -> "Distrik Petani Mitra"
     */
    private function extractBagianFromSpp($nomorSpp)
    {
        if (empty($nomorSpp)) {
            return null;
        }

        // Get the first part before "/" (e.g., "5TAN", "5DPM/5AKN")
        $parts = explode('/', $nomorSpp);
        if (empty($parts)) {
            return null;
        }

        $firstPart = strtoupper(trim($parts[0]));

        // Extract bagian code (letters after leading digits)
        // e.g., "5TAN" -> "TAN", "5SKH" -> "SKH"
        preg_match('/^\d*([A-Za-z]+)/', $firstPart, $matches);
        $bagianCode = $matches[1] ?? '';

        // Map bagian codes - return the code itself (matches dropdown values)
        // Short codes like SDM, TAN, TEP are used in the system
        $bagianCodes = [
            'TAN',
            'SKH',
            'SDM',
            'TEP',
            'AKN',
            'PTI',
            'DPM',
            'PMO',
            'KPL',
            // Unit kebun codes
            'KGM',
            'KRB',
            'KNG',
            'KBY',
            'KPR',
            'KGS',
            'KDS',
            'KBL',
            'KPM',
            'KLK',
            'KPD',
            'KTB',
            'KTJ',
            'KSD',
        ];

        // Return the code if it's a valid bagian code
        return in_array($bagianCode, $bagianCodes) ? $bagianCode : null;
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






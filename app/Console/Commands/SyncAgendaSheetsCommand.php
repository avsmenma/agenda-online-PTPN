<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

class SyncAgendaSheetsCommand extends Command
{
    protected $signature = 'agenda:sync-sheets
                            {--url= : Google Sheets published CSV URL}
                            {--dry-run : Only simulate changes without writing to database}';

    protected $description = 'Automated synchronization of Agenda Online documents directly from Google Sheets CSV';

    private const DEFAULT_URL = 'https://docs.google.com/spreadsheets/d/e/2PACX-1vTUjWVBmfxb4K7qGOAc89If_hoYGKOO_T2kr2-WkE4Y25xubioHiIDzDqku3LzIqYgCUl-aiQ32Hiv4/pub?gid=1156295533&single=true&output=csv';

    public function handle()
    {
        @set_time_limit(1800);
        ini_set('memory_limit', '2048M');

        $isDryRun = $this->option('dry-run');
        $url = $this->option('url') ?: env('GOOGLE_SHEETS_AGENDA_URL', self::DEFAULT_URL);

        $this->info("==================================================================");
        $this->info("MEMULAI AUTO-SYNC DOKUMEN AGENDA ONLINE DARI GOOGLE SHEETS");
        $this->info("URL: {$url}");
        if ($isDryRun) {
            $this->warn("MODE: DRY RUN (SIMULASI). Data TIDAK akan diubah di database.");
        }
        $this->info("==================================================================");

        $tempPath = storage_path('app/temp_agenda_sync_' . uniqid() . '.csv');

        try {
            // 1. Download CSV
            $this->line("Mengunduh data CSV dari Google Sheets...");
            $ch = curl_init($url);
            $fp = fopen($tempPath, 'wb');
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 180);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            fclose($fp);

            if ($httpCode !== 200 || !file_exists($tempPath) || filesize($tempPath) < 100) {
                $this->error("Gagal mengunduh CSV dari Google Sheets (HTTP Status: {$httpCode})");
                return Command::FAILURE;
            }

            $this->info("Unduhan selesai. Ukuran file: " . number_format(filesize($tempPath) / 1024, 2) . " KB.");

            // 2. Parse & Process
            $this->processCsv($tempPath, $isDryRun);

            return Command::SUCCESS;

        } catch (\Throwable $e) {
            $this->error("Terjadi kesalahan saat sinkronisasi: " . $e->getMessage());
            Log::error("[AgendaSync] Gagal: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return Command::FAILURE;
        } finally {
            // 3. SELALU HAPUS FILE TEMPORARY AGAR VPS TIDAK PENUH
            if (file_exists($tempPath)) {
                @unlink($tempPath);
                $this->line("File temporary unduhan telah dibersihkan dari server.");
            }
        }
    }

    private function processCsv(string $filePath, bool $isDryRun)
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \Exception("Tidak dapat membuka file CSV.");
        }

        $rawHeaders = fgetcsv($handle);
        $headerData = $this->cleanHeaders($rawHeaders);
        $headers = $headerData['headers'];
        $skipFirstColumn = $headerData['skip_first'];
        $validIndices = $headerData['valid_indices'];

        $dokumenColumns = array_flip(Schema::getColumnListing('dokumens'));
        $now = now()->format('Y-m-d H:i:s');
        $batchId = 'AUTO_SHEETS_' . now()->format('YmdHis');

        $candidateRows = [];
        $seenAgenda = [];
        $totalCsvRows = 0;

        while (($data = fgetcsv($handle)) !== false) {
            if (empty(array_filter($data))) continue;
            $totalCsvRows++;

            if ($skipFirstColumn && count($data) > 0) {
                array_shift($data);
            }

            $filteredData = [];
            foreach ($validIndices as $index) {
                $filteredData[] = $data[$index] ?? '';
            }

            $row = array_combine($headers, $filteredData);
            if ($row === false || empty($row)) continue;

            if (empty(trim($row['Agenda'] ?? '')) || empty(trim($row['No SPP'] ?? ''))) {
                continue;
            }

            $dokumenData = $this->transformRow($row);
            $nomorAgenda = $dokumenData['nomor_agenda'] ?? null;

            if (!$nomorAgenda || isset($seenAgenda[$nomorAgenda])) {
                continue;
            }
            $seenAgenda[$nomorAgenda] = true;

            $dokumenData['created_by'] = 'operator';
            $dokumenData['status'] = 'draft';
            $dokumenData['current_handler'] = 'operator';
            $dokumenData['created_at'] = $now;
            $dokumenData['updated_at'] = $now;

            if ($this->isMarkedPaid($dokumenData)) {
                $dokumenData['status_pembayaran'] = 'sudah_dibayar';
            }

            if (isset($dokumenColumns['imported_from_csv'])) {
                $dokumenData['imported_from_csv'] = true;
            }
            if (isset($dokumenColumns['csv_import_batch_id'])) {
                $dokumenData['csv_import_batch_id'] = $batchId;
            }
            if (isset($dokumenColumns['csv_imported_at'])) {
                $dokumenData['csv_imported_at'] = $now;
            }

            $candidateRows[] = array_intersect_key($dokumenData, $dokumenColumns);
        }
        fclose($handle);

        $this->info("Total baris valid di Google Sheets: " . count($candidateRows) . " (dari {$totalCsvRows} baris CSV).");

        // Cek dokumen yang sudah ada di database
        $existingAgendaMap = [];
        $allAgendas = array_column($candidateRows, 'nomor_agenda');

        foreach (array_chunk($allAgendas, 500) as $chunk) {
            $existing = DB::table('dokumens')
                ->whereIn('nomor_agenda', $chunk)
                ->get(['id', 'nomor_agenda', 'nilai_rupiah', 'uraian_spp', 'dibayar_kepada', 'tanggal_dibayar', 'status_pembayaran'])
                ->keyBy('nomor_agenda');

            foreach ($existing as $agenda => $doc) {
                $existingAgendaMap[$agenda] = $doc;
            }
        }

        $rowsToInsert = [];
        $updatesCount = 0;

        foreach ($candidateRows as $cRow) {
            $agenda = $cRow['nomor_agenda'];
            if (!isset($existingAgendaMap[$agenda])) {
                $rowsToInsert[] = $cRow;
            } else {
                // Periksa apakah ada perubahan field penting di Google Sheets
                $existing = $existingAgendaMap[$agenda];
                $diffs = [];

                if (abs((float)$existing->nilai_rupiah - (float)($cRow['nilai_rupiah'] ?? 0)) > 0.01) {
                    $diffs['nilai_rupiah'] = $cRow['nilai_rupiah'];
                }
                if (!empty($cRow['uraian_spp']) && trim($existing->uraian_spp) !== trim($cRow['uraian_spp'])) {
                    $diffs['uraian_spp'] = $cRow['uraian_spp'];
                }
                if (!empty($cRow['dibayar_kepada']) && trim($existing->dibayar_kepada) !== trim($cRow['dibayar_kepada'])) {
                    $diffs['dibayar_kepada'] = $cRow['dibayar_kepada'];
                }
                if (!empty($cRow['tanggal_dibayar']) && empty($existing->tanggal_dibayar)) {
                    $diffs['tanggal_dibayar'] = $cRow['tanggal_dibayar'];
                    $diffs['status_pembayaran'] = 'sudah_dibayar';
                }

                if (!empty($diffs)) {
                    $diffs['updated_at'] = $now;
                    $updatesCount++;
                    if (!$isDryRun) {
                        DB::table('dokumens')->where('id', $existing->id)->update($diffs);
                    }
                }
            }
        }

        $this->info("Dokumen baru (akan di-insert): " . count($rowsToInsert) . " baris.");
        $this->info("Dokumen yang diperbarui (update): " . $updatesCount . " baris.");

        if (!$isDryRun && !empty($rowsToInsert)) {
            $inserted = 0;
            foreach (array_chunk($rowsToInsert, 500) as $chunk) {
                DB::table('dokumens')->insert($chunk);
                $inserted += count($chunk);
            }
            $this->info("Berhasil meng-insert {$inserted} dokumen baru ke Agenda Online!");
        }

        if (!$isDryRun) {
            // Jalankan sinkronisasi lanjutan
            try {
                $this->line("Menjalankan sinkronisasi status ke Cash Bank...");
                Artisan::call('dokumen:sync-cashbank');
                Artisan::call('dokumen:process-auto-forward');
            } catch (\Throwable $e) {
                $this->warn("Info sync lanjutan: " . $e->getMessage());
            }
        }

        $this->info("Sinkronisasi Google Sheets Agenda Online selesai dengan sukses.");
    }

    private function cleanHeaders($headers)
    {
        $skipFirstColumn = false;
        if (count($headers) > 0 && empty(trim($headers[0]))) {
            array_shift($headers);
            $skipFirstColumn = true;
        }

        $aliasMap = $this->headerAliasMap();
        $filteredHeaders = [];
        $validIndices = [];
        $usedCanonical = [];

        foreach ($headers as $index => $header) {
            $canonical = $aliasMap[$this->normalizeHeader($header)] ?? null;
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

    private function normalizeHeader($header)
    {
        $normalized = strtolower((string) $header);
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        $normalized = str_replace(['(rp)', '(ba)', '(pp)', '(spp)'], '', $normalized);
        $normalized = preg_replace('/[\s.]+$/', '', trim($normalized));
        return trim($normalized);
    }

    private function headerAliasMap()
    {
        return [
            'agenda' => 'Agenda',
            'bulan' => 'Bulan',
            'tahun' => 'Tahun',
            'kriteria' => 'Kriteria',
            'no spp' => 'No SPP', 'no pp' => 'No SPP',
            'nomor spp' => 'No SPP', 'nomor pp' => 'No SPP',
            'tanggal spp' => 'Tanggal SPP', 'tanggal pp' => 'Tanggal SPP',
            'tgl spp' => 'Tanggal SPP', 'tgl pp' => 'Tanggal SPP',
            'tanggal masuk' => 'Tanggal Masuk', 'tgl masuk' => 'Tanggal Masuk',
            'dibayarkan kepada' => 'Dibayarkan Kepada',
            'dibayar kepada' => 'Dibayarkan Kepada',
            'dibayarkan kpd' => 'Dibayarkan Kepada',
            'uraian spp' => 'Uraian SPP', 'uraian pp' => 'Uraian SPP',
            'uraian permintaan pembayaran' => 'Uraian SPP', 'uraian' => 'Uraian SPP',
            'nilai' => 'Nilai',
            'no spk' => 'No SPK',
            'tanggal spk' => 'Tanggal SPK',
            'tgl akhir spk' => 'Tgl. Akhir SPK', 'tgl. akhir spk' => 'Tgl. Akhir SPK',
            'no berita acara' => 'No Berita Acara',
            'tanggal berita acara' => 'Tanggal Berita Acara',
            'no po' => 'No. PO', 'no. po' => 'No. PO',
            'no miro/ses' => 'No. Miro/SES', 'no. miro/ses' => 'No. Miro/SES',
            'tanggal paraf' => 'Tanggal Paraf',
            'pemaraf' => 'Pemaraf',
            'tgl selesai diproses' => 'Tgl selesai diproses',
            'kepala sub bagian' => 'Kepala Sub Bagian',
            'status dokumen' => 'Status Dokumen',
            'tanggal bayar' => 'Tanggal Bayar',
        ];
    }

    private function transformRow($row)
    {
        $bulanNameMap = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        $bulanValidNames = array_map('strtolower', $bulanNameMap);
        $bulanText = strtolower(trim($row['Bulan'] ?? ''));
        $bulanText = preg_replace('/^[a-z]\.\s*/i', '', $bulanText);

        $bulanKey = array_search($bulanText, $bulanValidNames);
        if ($bulanKey !== false) {
            $bulan = $bulanNameMap[$bulanKey];
        } elseif (is_numeric($bulanText) && isset($bulanNameMap[(int) $bulanText])) {
            $bulan = $bulanNameMap[(int) $bulanText];
        } else {
            $bulan = $bulanNameMap[(int) date('n')];
        }

        $tahun = trim($row['Tahun'] ?? '') ?: date('Y');
        $tanggalMasuk = $this->parseDate($row['Tanggal Masuk'] ?? null) ?? now()->format('Y-m-d H:i:s');
        $tanggalSpp = $this->parseDate($row['Tanggal SPP'] ?? null) ?? $tanggalMasuk;
        $kriteria = trim($row['Kriteria'] ?? '');

        $nomorAgenda = trim($row['Agenda'] ?? '');
        if (!empty($nomorAgenda) && !str_contains($nomorAgenda, '_2026')) {
            $nomorAgenda .= '_2026';
        }

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
            'kategori' => 'CAPEX',
            'jenis_dokumen' => 'Lainnya',
            'bagian' => $bagian,
            'no_spk' => trim($row['No SPK'] ?? '') ?: null,
            'tanggal_spk' => $this->parseDate($row['Tanggal SPK'] ?? null),
            'tanggal_berakhir_spk' => $this->parseDate($row['Tgl. Akhir SPK'] ?? null),
            'no_berita_acara' => trim($row['No Berita Acara'] ?? '') ?: null,
            'tanggal_berita_acara' => $this->parseDate($row['Tanggal Berita Acara'] ?? null),
            'NO_PO' => trim($row['No. PO'] ?? '') ?: null,
            'NO_MIRO_SES' => trim($row['No. Miro/SES'] ?? '') ?: null,
            'tanggal_paraf' => $this->parseDate($row['Tanggal Paraf'] ?? null),
            'pemaraf' => trim($row['Pemaraf'] ?? '') ?: null,
            'tanggal_selesai_diproses' => $this->parseDate($row['Tgl selesai diproses'] ?? null),
            'kepala_sub_bagian' => trim($row['Kepala Sub Bagian'] ?? '') ?: null,
            'status_dokumen_csv' => trim($row['Status Dokumen'] ?? '') ?: null,
            'tanggal_dibayar' => $this->parseDate($row['Tanggal Bayar'] ?? null),
        ];
    }

    private function isMarkedPaid(array $data): bool
    {
        if (!empty($data['tanggal_dibayar'])) {
            return true;
        }

        $status = strtolower(trim($data['status_dokumen_csv'] ?? ''));
        if ($status === '' || str_contains($status, 'belum')) {
            return false;
        }

        return str_contains($status, 'dibayar')
            || str_contains($status, 'lunas')
            || str_contains($status, 'paid');
    }

    private function extractBagianFromSpp($nomorSpp)
    {
        if (empty($nomorSpp)) return null;

        $parts = explode('/', $nomorSpp);
        if (empty($parts)) return null;

        $firstPart = strtoupper(trim($parts[0]));
        preg_match('/^\d*([A-Za-z]+)/', $firstPart, $matches);
        $bagianCode = $matches[1] ?? '';

        $bagianCodes = [
            'TAN', 'SKH', 'SDM', 'TEP', 'AKN', 'PTI', 'DPM', 'PMO', 'KPL',
            'KGM', 'KRB', 'KNG', 'KBY', 'KPR', 'KGS', 'KDS', 'KBL', 'KPM', 'KLK', 'KPD', 'KTB', 'KTJ', 'KSD'
        ];

        return in_array($bagianCode, $bagianCodes) ? $bagianCode : null;
    }

    private function parseDate($date)
    {
        if (empty($date)) return null;
        $dateStr = trim($date);
        if (empty($dateStr) || $dateStr === '-' || $dateStr === '0') return null;

        $formats = ['d/m/Y', 'd-m-Y', 'Y-m-d', 'd/m/Y H:i:s', 'd-m-Y H:i:s', 'Y-m-d H:i:s'];
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

        try {
            $d = Carbon::parse($dateStr);
            if ($d && $d->year > 1900 && $d->year < 2100) {
                return $d->format('Y-m-d H:i:s');
            }
        } catch (\Exception $e) {
            // ignore
        }

        return null;
    }

    private function cleanNumeric($value)
    {
        if (empty($value)) return 0;
        $cleaned = preg_replace('/[^0-9,.-]/', '', trim((string) $value));
        if (strpos($cleaned, ',') !== false) {
            $cleaned = str_replace('.', '', $cleaned);
            $cleaned = str_replace(',', '.', $cleaned);
        } else {
            $cleaned = str_replace('.', '', $cleaned);
        }
        return (float) $cleaned;
    }
}

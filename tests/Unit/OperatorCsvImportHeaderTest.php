<?php

namespace Tests\Unit;

use App\Http\Controllers\OperatorCsvImportController;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Regression test untuk pemetaan header importer dokumen.
 *
 * Spreadsheet sumber sempat berganti istilah "SPP" -> "PP / Permintaan
 * Pembayaran", sehingga seluruh baris gagal validasi dengan pesan
 * "Nomor SPP wajib diisi". cleanHeaders() kini harus mengenali kedua istilah
 * (lama & baru) beserta variasi kapitalisasi, spasi, baris baru, dan imbuhan
 * "(Rp)"/"(BA)". Test ini DB-free: hanya menguji normalisasi header lewat
 * refleksi method privat.
 */
class OperatorCsvImportHeaderTest extends TestCase
{
    private function cleanHeaders(array $headers): array
    {
        $controller = (new ReflectionClass(OperatorCsvImportController::class))
            ->newInstanceWithoutConstructor();

        $method = new \ReflectionMethod($controller, 'cleanHeaders');
        $method->setAccessible(true);

        return $method->invoke($controller, $headers);
    }

    /**
     * Petakan baris data CSV ke array asosiatif berkunci nama kolom kanonik,
     * meniru alur validateAllRows()/executeImport().
     */
    private function mapRow(array $rawHeaders, array $rawData): array
    {
        $hd = $this->cleanHeaders($rawHeaders);
        $data = $rawData;
        if ($hd['skip_first']) {
            array_shift($data);
        }
        $filtered = [];
        foreach ($hd['valid_indices'] as $index) {
            $filtered[] = $data[$index] ?? '';
        }
        return array_combine($hd['headers'], $filtered);
    }

    public function test_header_baru_pp_dipetakan_ke_kolom_kanonik(): void
    {
        // Subset header gaya spreadsheet baru (kolom kosong di depan + istilah PP).
        $rawHeaders = ['', 'Agenda', 'Bulan', 'Tahun', 'Kriteria', 'No PP', "Tanggal\nPP", 'Tanggal Masuk', ' Kontrol ', 'Dibayarkan kepada', 'Uraian Permintaan Pembayaran (PP)', "Nilai\n(Rp)"];

        $hd = $this->cleanHeaders($rawHeaders);

        $this->assertTrue($hd['skip_first'], 'Kolom kosong pertama harus di-skip');
        $this->assertContains('No SPP', $hd['headers'], '"No PP" harus dipetakan ke "No SPP"');
        $this->assertContains('Tanggal SPP', $hd['headers'], '"Tanggal PP" (dengan newline) harus dipetakan');
        $this->assertContains('Dibayarkan Kepada', $hd['headers'], 'huruf kecil "kepada" harus tetap cocok');
        $this->assertContains('Uraian SPP', $hd['headers'], '"Uraian Permintaan Pembayaran" harus dipetakan');
        $this->assertContains('Nilai', $hd['headers'], '"Nilai (Rp)" harus dipetakan ke "Nilai"');
        $this->assertNotContains('Kontrol', $hd['headers'], 'Kolom "Kontrol" harus dilewati');
    }

    public function test_header_lama_spp_tetap_didukung(): void
    {
        $rawHeaders = ['', 'Agenda', 'Bulan', 'Tahun', 'Kriteria', 'No SPP', 'Tanggal SPP', 'Tanggal Masuk', 'Dibayarkan Kepada', 'Uraian SPP', 'Nilai (Rp)'];

        $hd = $this->cleanHeaders($rawHeaders);

        foreach (['No SPP', 'Tanggal SPP', 'Dibayarkan Kepada', 'Uraian SPP', 'Nilai'] as $expected) {
            $this->assertContains($expected, $hd['headers'], "Header lama '{$expected}' harus tetap dikenali");
        }
    }

    public function test_baris_data_pp_terisi_dan_lolos_field_wajib(): void
    {
        $rawHeaders = ['', 'Agenda', 'Bulan', 'Tahun', 'Kriteria', 'No PP', "Tanggal\nPP", 'Tanggal Masuk', ' Kontrol ', 'Dibayarkan kepada', 'Uraian Permintaan Pembayaran (PP)', "Nilai\n(Rp)"];
        $rawData = ['', '0001', 'a. Januari', '2026', '', '5SKH/SPP/01/UMUM/I/2026', '02/01/2026', '02/01/2026 9:38:35', '1', 'Fauzan Chairil A', 'Konsumsi Senam', '620.000'];

        $row = $this->mapRow($rawHeaders, $rawData);

        // Field wajib (Agenda, No SPP, Nilai) harus terisi -> tidak lagi error.
        $this->assertSame('0001', $row['Agenda']);
        $this->assertNotEmpty($row['No SPP'], 'No SPP harus terisi (akar bug sebelumnya)');
        $this->assertSame('5SKH/SPP/01/UMUM/I/2026', $row['No SPP']);
        $this->assertNotEmpty($row['Nilai']);
        $this->assertSame('Fauzan Chairil A', $row['Dibayarkan Kepada']);
    }
}

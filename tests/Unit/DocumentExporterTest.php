<?php

namespace Tests\Unit;

use App\Support\DocumentExporter;
use Tests\TestCase;

/**
 * Unit test untuk App\Support\DocumentExporter — penulis XML Spreadsheet 2003
 * (.xls) tanpa dependensi composer, dipakai bersama oleh fitur export dokumen
 * lintas-role. Meniru struktur XML yang sudah terbukti di
 * OwnerDashboardController::exportRekapanKeterlambatan(), tapi digeneralisasi
 * jadi data-driven ($columns + $rows).
 */
class DocumentExporterTest extends TestCase
{
    public function test_toxlsx_menghasilkan_xml_valid_dengan_header_dan_baris(): void
    {
        $cols = [['key' => 'nomor_agenda', 'label' => 'Nomor Agenda'], ['key' => 'nilai_rupiah', 'label' => 'Nilai']];
        $rows = [['nomor_agenda' => '5377_2026', 'nilai_rupiah' => 1000, 'nilai_rupiah_formatted' => 'Rp 1.000']];
        $xml = DocumentExporter::toXlsx($cols, $rows, ['title' => 'Uji', 'total_key' => 'nilai_rupiah']);

        $this->assertStringContainsString('<?mso-application progid="Excel.Sheet"?>', $xml);
        $this->assertStringContainsString('Nomor Agenda', $xml);
        $this->assertStringContainsString('5377_2026', $xml);
        $this->assertStringContainsString('Rp 1.000', $xml);
        // XML well-formed
        $this->assertNotFalse(simplexml_load_string($xml));
    }

    public function test_escape_xml_aman(): void
    {
        $xml = DocumentExporter::toXlsx([['key' => 'x', 'label' => 'X']], [['x' => 'A & B < C > "D"']]);

        $this->assertStringContainsString('A &amp; B &lt; C &gt; &quot;D&quot;', $xml);
        $this->assertNotFalse(simplexml_load_string($xml));
    }

    public function test_multi_sheet_pembayaran(): void
    {
        $xml = DocumentExporter::toXlsx([['key' => 'x', 'label' => 'X']], [], ['sheets' => [
            ['name' => 'Vendor A', 'rows' => [['x' => '1']]],
            ['name' => 'Vendor B', 'rows' => [['x' => '2']]],
        ]]);

        $this->assertSame(2, substr_count($xml, '<Worksheet'));
        $this->assertNotFalse(simplexml_load_string($xml));
    }

    public function test_baris_kosong_tetap_menghasilkan_xml_valid(): void
    {
        $xml = DocumentExporter::toXlsx([['key' => 'x', 'label' => 'X']], []);

        $this->assertNotFalse(simplexml_load_string($xml));
    }

    public function test_total_key_menjumlahkan_baris_sebagai_number(): void
    {
        $cols = [['key' => 'nilai_rupiah', 'label' => 'Nilai']];
        $rows = [
            ['nilai_rupiah' => 1000],
            ['nilai_rupiah' => 2500],
        ];
        $xml = DocumentExporter::toXlsx($cols, $rows, ['total_key' => 'nilai_rupiah']);

        $this->assertStringContainsString('TOTAL', $xml);
        $this->assertStringContainsString('<Data ss:Type="Number">3500</Data>', $xml);
        $this->assertNotFalse(simplexml_load_string($xml));
    }

    public function test_cell_value_utamakan_kunci_formatted(): void
    {
        $row = ['nilai_rupiah' => 1000, 'nilai_rupiah_formatted' => 'Rp 1.000'];

        $this->assertSame('Rp 1.000', DocumentExporter::cellValue($row, 'nilai_rupiah'));
    }

    public function test_cell_value_ambil_dari_dates_untuk_kolom_tanggal(): void
    {
        $row = ['dates' => ['tanggal_spp' => '01-01-2026']];

        $this->assertSame('01-01-2026', DocumentExporter::cellValue($row, 'tanggal_spp'));
    }

    public function test_cell_value_fallback_ke_kolom_mentah_dan_null_jadi_strip(): void
    {
        $row = ['bagian' => 'Keuangan', 'kosong' => null];

        $this->assertSame('Keuangan', DocumentExporter::cellValue($row, 'bagian'));
        $this->assertSame('-', DocumentExporter::cellValue($row, 'kosong'));
        $this->assertSame('-', DocumentExporter::cellValue($row, 'tidak_ada'));
    }

    public function test_nama_sheet_disanitasi_maksimal_31_karakter(): void
    {
        $panjang = str_repeat('A', 50);
        $xml = DocumentExporter::toXlsx([['key' => 'x', 'label' => 'X']], [], ['sheets' => [
            ['name' => $panjang, 'rows' => [['x' => '1']]],
        ]]);

        $simple = simplexml_load_string($xml);
        $this->assertNotFalse($simple);

        $namespaces = $simple->getNamespaces(true);
        $worksheet = $simple->children($namespaces[''])->Worksheet;
        $attrs = $worksheet->attributes($namespaces['ss']);
        $this->assertLessThanOrEqual(31, strlen((string) $attrs['Name']));
    }
}

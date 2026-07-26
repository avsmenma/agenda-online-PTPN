<?php

namespace Tests\Feature;

use App\Models\Dokumen;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Menguji endpoint `GET documents/pembayaran/export` (name
 * `documents.pembayaran.export`, method DashboardPembayaranController::
 * exportDocuments — Task 3 fitur export bersama). Menggantikan
 * exportToExcel()/exportToExcelByVendor() lama yang FATAL (PhpSpreadsheet
 * tak terpasang di composer.json) dengan App\Support\DocumentExporter
 * (dependency-free) lewat trait ExportsDocuments.
 *
 * Paritas dengan PembayaranDatatableTest: pembayaran adalah SATU-SATUNYA
 * role yang menyertakan dokumen hasil import CSV di query-nya (buildPembayaranQuery
 * = buildPembayaranDashboardQuery, tanpa pengecualian CSV) — ditegaskan lagi
 * di sini utk jalur export.
 */
class PembayaranExportTest extends TestCase
{
    use RefreshDatabase;

    private function pembayaran(): User
    {
        return User::factory()->create(['role' => 'pembayaran']);
    }

    private function akutansi(): User
    {
        return User::factory()->create(['role' => 'akutansi']);
    }

    private function buatDokumen(string $nomorAgenda, array $overrides = []): Dokumen
    {
        return Dokumen::create(array_merge([
            'nomor_agenda'    => $nomorAgenda,
            'bulan'           => 'Juli',
            'tahun'           => 2026,
            'tanggal_masuk'   => '2026-07-01',
            'status'          => 'sent_to_pembayaran',
            'created_by'      => 'operator',
            'current_handler' => 'pembayaran',
            'dibayar_kepada'  => 'PT Vendor Satu',
            'nilai_rupiah'    => 1000000,
        ], $overrides));
    }

    public function test_export_excel_mengembalikan_unduhan_xls_berisi_header_dan_nomor_agenda(): void
    {
        $this->buatDokumen('5001/2026');

        $response = $this->actingAs($this->pembayaran())
            ->get(route('documents.pembayaran.export', ['format' => 'excel']));

        $response->assertOk();
        $this->assertStringContainsString('ms-excel', $response->headers->get('Content-Type'));

        $content = $response->getContent();
        $this->assertStringContainsString('Nomor Agenda', $content); // label katalog
        $this->assertStringContainsString('5001/2026', $content);
    }

    public function test_export_pdf_mengembalikan_view_cetak_dengan_judul_rekapan_pembayaran(): void
    {
        $this->buatDokumen('5002/2026');

        $response = $this->actingAs($this->pembayaran())
            ->get(route('documents.pembayaran.export', ['format' => 'pdf']));

        $response->assertOk();
        $this->assertStringContainsString('Rekapan Pembayaran', $response->getContent());
    }

    public function test_dokumen_hasil_import_csv_ikut_ter_export(): void
    {
        $this->buatDokumen('5003-CSV', [
            'current_handler'   => 'operator',
            'status'            => 'draft',
            'imported_from_csv' => true,
        ]);

        $response = $this->actingAs($this->pembayaran())
            ->get(route('documents.pembayaran.export', ['format' => 'excel']));

        $response->assertOk();
        $this->assertStringContainsString('5003-CSV', $response->getContent());
    }

    public function test_role_selain_pembayaran_ditolak_403(): void
    {
        $this->actingAs($this->akutansi())
            ->getJson(route('documents.pembayaran.export', ['format' => 'excel']))
            ->assertForbidden();
    }

    public function test_tamu_tidak_bisa_akses(): void
    {
        $this->getJson(route('documents.pembayaran.export', ['format' => 'excel']))
            ->assertUnauthorized();
    }
}

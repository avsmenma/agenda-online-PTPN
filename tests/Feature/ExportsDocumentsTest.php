<?php

namespace Tests\Feature;

use App\Http\Controllers\Concerns\ExportsDocuments;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Test ringan untuk trait ExportsDocuments (Task 2 fitur export bersama).
 * Trait ini baru benar-benar dipakai end-to-end oleh controller role di
 * Task 3-4 — di sini cukup dibuktikan: dispatch format excel/pdf berjalan,
 * nama berkas tersanitasi, dan view exports.document-print BENAR-BENAR
 * mengompilasi (bukan cuma php -l) lewat render sungguhan.
 */
class ExportsDocumentsTest extends TestCase
{
    /** Controller dummy ber-trait, method wrapper public agar bisa dipanggil test. */
    private function controller(): object
    {
        return new class
        {
            use ExportsDocuments;

            public function respond(Request $request, iterable $rows, array $columns, array $options = [])
            {
                return $this->respondDocumentExport($request, $rows, $columns, $options);
            }
        };
    }

    public function test_format_excel_menghasilkan_unduhan_xls_berisi_data(): void
    {
        $request = Request::create('/export', 'GET', ['format' => 'excel']);
        $columns = [['key' => 'nomor_agenda', 'label' => 'Nomor Agenda']];
        $rows = [['nomor_agenda' => '001/2026']];

        $response = $this->controller()->respond($request, $rows, $columns, ['title' => 'Dokumen Akutansi']);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/vnd.ms-excel', $response->headers->get('Content-Type'));
        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('attachment; filename="Dokumen-Akutansi-', $disposition);
        $this->assertStringEndsWith('.xls"', $disposition);
        $this->assertStringContainsString('001/2026', $response->getContent());
    }

    public function test_format_pdf_merender_view_cetak_sungguhan(): void
    {
        $request = Request::create('/export', 'GET', ['format' => 'pdf']);
        $columns = [['key' => 'nomor_agenda', 'label' => 'Nomor Agenda']];
        $rows = collect([['nomor_agenda' => '002/2026']]);

        $response = $this->controller()->respond($request, $rows, $columns, ['title' => 'Dokumen Perpajakan']);

        $this->assertSame(200, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertStringContainsString('Dokumen Perpajakan', $content);
        $this->assertStringContainsString('002/2026', $content);
        $this->assertStringContainsString('PTPN IV Regional V', $content);
        $this->assertStringContainsString('window.print()', $content);
    }

    public function test_default_format_tanpa_query_adalah_excel(): void
    {
        $request = Request::create('/export', 'GET');
        $response = $this->controller()->respond($request, [], [['key' => 'x', 'label' => 'X']]);

        $this->assertSame('application/vnd.ms-excel', $response->headers->get('Content-Type'));
    }

    public function test_tanpa_title_jatuh_ke_default_export(): void
    {
        $request = Request::create('/export', 'GET', ['format' => 'excel']);
        $response = $this->controller()->respond($request, [], [['key' => 'x', 'label' => 'X']]);

        $this->assertStringContainsString('attachment; filename="Export-', $response->headers->get('Content-Disposition'));
    }

    public function test_title_string_kosong_disanitasi_jadi_export(): void
    {
        $request = Request::create('/export', 'GET', ['format' => 'excel']);
        $response = $this->controller()->respond($request, [], [['key' => 'x', 'label' => 'X']], ['title' => '   ']);

        $this->assertStringContainsString('attachment; filename="export-', $response->headers->get('Content-Disposition'));
    }

    public function test_baris_kosong_pada_pdf_menampilkan_pesan_tidak_ada_data(): void
    {
        $request = Request::create('/export', 'GET', ['format' => 'pdf']);
        $response = $this->controller()->respond($request, [], [['key' => 'x', 'label' => 'X']], ['title' => 'Kosong']);

        $this->assertStringContainsString('Tidak ada data.', $response->getContent());
    }
}

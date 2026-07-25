<?php

namespace Tests\Feature;

use App\Models\Dokumen;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Menguji endpoint export (Excel/PDF) 4 role sisa (Task 4 fitur export bersama):
 * operator, akutansi, perpajakan, team_verifikasi — semuanya lewat trait
 * ExportsDocuments::respondDocumentExport() (App\Support\DocumentExporter),
 * paralel dengan PembayaranExportTest (Task 3, pembayaran = templat).
 *
 * Parametrik atas roleMap(): tiap entri route export + role "asing" dipakai
 * utk uji gating 403. Query per role (buildOperatorQuery/buildAkutansiQuery/
 * buildPerpajakanQuery/buildVerifikasiQuery) TIDAK diduplikasi di sini — export
 * memakai ulang persis method yang sama dgn endpoint datatable() role itu.
 */
class DocumentExportRolesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // build<Role>Query() tiap role mengurutkan nomor_agenda via fungsi MySQL
        // (REGEXP, SUBSTRING_INDEX, LPAD) yang tak tersedia di SQLite in-memory
        // (env test, phpunit.xml). Polyfill sama seperti Operator/Akutansi/
        // Perpajakan/VerifikasiDatatableTest.
        if (DB::connection()->getDriverName() === 'sqlite') {
            $pdo = DB::connection()->getPdo();
            $pdo->sqliteCreateFunction('REGEXP', fn ($p, $v) => preg_match('/'.$p.'/u', (string) $v) ? 1 : 0, 2);
            $pdo->sqliteCreateFunction('SUBSTRING_INDEX', fn ($s, $d, $c) => implode($d, array_slice(explode($d, (string) $s), 0, (int) $c)), 3);
            $pdo->sqliteCreateFunction('LPAD', fn ($s, $l, $p) => str_pad((string) $s, (int) $l, (string) $p, STR_PAD_LEFT), 3);
        }
    }

    /**
     * Peta 4 role: dbRole => [route export, role asing utk gating 403].
     * Role asing sengaja dipilih dari daftar role operasional lain (bukan admin,
     * yang lolos di semua grup) supaya gating benar-benar teruji.
     */
    private function roleMap(): array
    {
        return [
            'operator'        => ['route' => 'documents.export',            'foreign' => 'pembayaran'],
            'akutansi'        => ['route' => 'documents.akutansi.export',   'foreign' => 'operator'],
            'perpajakan'      => ['route' => 'documents.perpajakan.export', 'foreign' => 'akutansi'],
            'team_verifikasi' => ['route' => 'documents.verifikasi.export', 'foreign' => 'perpajakan'],
        ];
    }

    private function user(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    private function buatDokumen(string $role, string $nomorAgenda): Dokumen
    {
        return Dokumen::create([
            'nomor_agenda'    => $nomorAgenda,
            'bulan'           => 'Juli',
            'tahun'           => 2026,
            'tanggal_masuk'   => '2026-07-01',
            'status'          => 'draft',
            'created_by'      => 'operator',
            'current_handler' => $role,
            'nilai_rupiah'    => 1000000,
        ]);
    }

    public function test_export_excel_per_role_mengembalikan_unduhan_xls_dgn_header_katalog(): void
    {
        foreach ($this->roleMap() as $role => $cfg) {
            $dok = $this->buatDokumen($role, '9001-'.$role);

            $response = $this->actingAs($this->user($role))
                ->get(route($cfg['route'], ['format' => 'excel']));

            $response->assertOk();
            $this->assertStringContainsString('ms-excel', $response->headers->get('Content-Type'), "role={$role}");

            $content = $response->getContent();
            $this->assertStringContainsString('Nomor Agenda', $content, "role={$role}: header katalog hilang");
            $this->assertStringContainsString($dok->nomor_agenda, $content, "role={$role}: baris nomor agenda hilang");
        }
    }

    public function test_export_pdf_per_role_mengembalikan_view_cetak(): void
    {
        foreach ($this->roleMap() as $role => $cfg) {
            $this->buatDokumen($role, '9002-'.$role);

            $response = $this->actingAs($this->user($role))
                ->get(route($cfg['route'], ['format' => 'pdf']));

            $response->assertOk();
            $this->assertStringContainsString('Nomor Agenda', $response->getContent(), "role={$role}: header PDF hilang");
        }
    }

    public function test_role_lain_ditolak_403(): void
    {
        foreach ($this->roleMap() as $role => $cfg) {
            $foreign = $this->user($cfg['foreign']);

            $this->actingAs($foreign)
                ->getJson(route($cfg['route'], ['format' => 'excel']))
                ->assertForbidden();
        }
    }

    public function test_tamu_tidak_bisa_akses(): void
    {
        foreach ($this->roleMap() as $role => $cfg) {
            $this->getJson(route($cfg['route'], ['format' => 'excel']))
                ->assertUnauthorized();
        }
    }
}

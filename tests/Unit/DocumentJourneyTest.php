<?php

namespace Tests\Unit;

use App\Models\Dokumen;
use App\Support\DocumentJourney;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Menguji App\Support\DocumentJourney — penentu posisi dokumen dalam alur keuangan
 * untuk role Bagian.
 *
 * Aturan (spec 2026-08-06):
 *   sekarang         : indeks == indeks current_handler
 *   selesai          : indeks < sekarang DAN ada jejak
 *   dilewati         : indeks < sekarang TANPA jejak
 *   belum            : indeks > sekarang
 *   perlu_diperbaiki : status returned_to_bidang -> simpul Bagian
 */
class DocumentJourneyTest extends TestCase
{
    use RefreshDatabase;

    private function dokumen(array $atribut = []): Dokumen
    {
        // DocumentJourney adalah kelas murni (nol query DB) — dokumen di sini sengaja
        // TIDAK disimpan (bukan Dokumen::create()). Kolom current_handler bertipe
        // NOT NULL di skema (default 'ibuA', lihat migrasi
        // 2025_11_16_161322_add_workflow_fields_to_dokumens_table.php), jadi
        // Dokumen::create() dengan current_handler eksplisit null akan gagal di
        // level DB (constraint violation) sebelum sempat menguji DocumentJourney
        // sama sekali. Instance yang tak disimpan menghindarinya sekaligus lebih
        // konsisten dengan sifat "murni" kelas yang diuji.
        return new Dokumen(array_merge([
            'nomor_agenda'    => '1_2026',
            'bulan'           => 'Agustus',
            'tahun'           => 2026,
            'tanggal_masuk'   => '2026-08-01',
            'status'          => 'sedang diproses',
            'created_by'      => 'operator',
            'current_handler' => 'team_verifikasi',
            'bagian'          => 'AKN',
        ], $atribut));
    }

    /** @return array<string,string> kunci tahap => state */
    private function petaState(array $hasil): array
    {
        $peta = [];
        foreach ($hasil['stages'] as $t) {
            $peta[$t['key']] = $t['state'];
        }

        return $peta;
    }

    public function test_dokumen_di_tengah_alur(): void
    {
        $hasil = DocumentJourney::forDokumen($this->dokumen(), ['team_verifikasi']);
        $peta  = $this->petaState($hasil);

        $this->assertSame('selesai', $peta['operator']);
        $this->assertSame('sekarang', $peta['verifikasi']);
        $this->assertSame('belum', $peta['perpajakan']);
        $this->assertSame('belum', $peta['akutansi']);
        $this->assertSame('belum', $peta['pembayaran']);
        $this->assertFalse($hasil['needs_action']);
        $this->assertSame('Verifikasi', $hasil['current_label']);
    }

    public function test_dokumen_di_ujung_alur(): void
    {
        $hasil = DocumentJourney::forDokumen(
            $this->dokumen(['current_handler' => 'pembayaran']),
            ['team_verifikasi', 'perpajakan', 'akutansi', 'pembayaran']
        );
        $peta = $this->petaState($hasil);

        $this->assertSame('selesai', $peta['operator']);
        $this->assertSame('selesai', $peta['verifikasi']);
        $this->assertSame('selesai', $peta['perpajakan']);
        $this->assertSame('selesai', $peta['akutansi']);
        $this->assertSame('sekarang', $peta['pembayaran']);
    }

    public function test_dokumen_dikembalikan_menyalakan_simpul_bagian(): void
    {
        $hasil = DocumentJourney::forDokumen(
            $this->dokumen(['status' => 'returned_to_bidang']),
            ['team_verifikasi']
        );
        $peta = $this->petaState($hasil);

        $this->assertSame('perlu_diperbaiki', $peta['bagian']);
        $this->assertSame('netral', $peta['operator']);
        $this->assertSame('netral', $peta['verifikasi']);
        $this->assertSame('netral', $peta['pembayaran']);
        $this->assertTrue($hasil['needs_action']);
        $this->assertSame('Bagian (AKN)', $hasil['stages'][0]['label']);
    }

    public function test_tahap_tanpa_jejak_ditandai_dilewati(): void
    {
        // Dokumen melompat langsung ke Pembayaran: perpajakan & akutansi tak pernah
        // punya baris received_at.
        $hasil = DocumentJourney::forDokumen(
            $this->dokumen(['current_handler' => 'pembayaran']),
            ['team_verifikasi']
        );
        $peta = $this->petaState($hasil);

        $this->assertSame('selesai', $peta['verifikasi']);
        $this->assertSame('dilewati', $peta['perpajakan']);
        $this->assertSame('dilewati', $peta['akutansi']);
    }

    public function test_dokumen_baru_di_operator_tidak_dianggap_dilewati(): void
    {
        // Operator MEMBUAT dokumen, bukan menerima — dokumen_role_data miliknya
        // ber-received_at NULL. Aturan naif akan menandainya 'dilewati'.
        $hasil = DocumentJourney::forDokumen(
            $this->dokumen(['current_handler' => 'operator']),
            []
        );
        $peta = $this->petaState($hasil);

        $this->assertSame('sekarang', $peta['operator']);
        $this->assertSame('belum', $peta['verifikasi']);
        $this->assertSame('Operator', $hasil['current_label']);
    }

    public function test_current_handler_tak_dikenal_jatuh_ke_operator(): void
    {
        foreach ([null, '', 'entah_apa'] as $nilai) {
            $hasil = DocumentJourney::forDokumen(
                $this->dokumen(['current_handler' => $nilai]),
                []
            );

            $this->assertSame(
                'sekarang',
                $this->petaState($hasil)['operator'],
                'Gagal untuk current_handler: ' . var_export($nilai, true)
            );
        }
    }
}

<?php

namespace Tests\Unit;

use App\Models\Bagian;
use App\Models\Dokumen;
use App\Support\HandlerOptions;
use App\Support\VerifikasiDocumentRow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Menguji DocumentRow::handlerUntukTampilan() lewat DTO nyata.
 *
 * Aturan yang dijaga: saat dokumen sedang dikembalikan ke Bagian, dropdown Pengurus
 * Dokumen harus MENAMPILKAN bagian tujuan (cocok dengan badge "Dikembalikan ke X"),
 * TETAPI current_handler di database tetap role pengembali supaya can_change_handler
 * tetap true dan dokumen masih bisa ditarik kembali.
 *
 * Dilaporkan user dari QA produksi 2026-08-06: badge berkata "Dikembalikan ke AKN"
 * sementara dropdown berkata "Tim Verifikasi" — dua pesan bertentangan di satu baris.
 */
class HandlerTampilanPengembalianTest extends TestCase
{
    use RefreshDatabase;

    private function dokumen(array $atribut = []): Dokumen
    {
        return Dokumen::create(array_merge([
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

    private function baris(Dokumen $dokumen): array
    {
        $dokumen->load(['roleStatuses', 'dibayarKepadas', 'dokumenPos']);

        return VerifikasiDocumentRow::fromDokumen(
            $dokumen,
            HandlerOptions::forDokumen($dokumen->bagian, HandlerOptions::bagianMap()),
            'team_verifikasi'
        );
    }

    public function test_dokumen_dikembalikan_menampilkan_bagian_tujuan(): void
    {
        Bagian::create(['kode' => 'AKN', 'nama' => 'AKN']);

        $dokumen = $this->dokumen([
            'status'        => 'returned_to_bidang',
            'return_source' => 'AKN',
        ]);

        $baris = $this->baris($dokumen);

        $this->assertSame('bagian_akn', $baris['handler']);
    }

    public function test_dropdown_tetap_bisa_diubah_agar_dokumen_dapat_ditarik_kembali(): void
    {
        Bagian::create(['kode' => 'AKN', 'nama' => 'AKN']);

        $dokumen = $this->dokumen([
            'status'        => 'returned_to_bidang',
            'return_source' => 'AKN',
        ]);

        $baris = $this->baris($dokumen);

        $this->assertTrue(
            $baris['can_change_handler'],
            'Dropdown WAJIB tetap aktif — kalau mati, dokumen tak bisa ditarik kembali.'
        );
        $this->assertSame(
            'team_verifikasi',
            $dokumen->fresh()->current_handler,
            'current_handler di database TIDAK BOLEH berubah; perbaikan ini murni tampilan.'
        );
    }

    public function test_dokumen_biasa_tetap_menampilkan_current_handler(): void
    {
        Bagian::create(['kode' => 'AKN', 'nama' => 'AKN']);

        $baris = $this->baris($this->dokumen());

        $this->assertSame('team_verifikasi', $baris['handler']);
    }

    public function test_return_source_basi_diabaikan_bila_status_bukan_dikembalikan(): void
    {
        // Dokumen yang SUDAH ditarik kembali / berpindah, tapi kolom return_source
        // masih menyimpan sisa nilai lama. Tanpa penjaga status, dropdown akan
        // keliru menampilkan bagian padahal dokumennya tidak sedang dikembalikan.
        Bagian::create(['kode' => 'AKN', 'nama' => 'AKN']);

        $dokumen = $this->dokumen([
            'status'        => 'sedang diproses',
            'return_source' => 'AKN',
        ]);

        $baris = $this->baris($dokumen);

        $this->assertSame('team_verifikasi', $baris['handler']);
    }

    public function test_return_source_tanpa_opsi_padanan_kembali_ke_current_handler(): void
    {
        // Data lama: return_source berbeda dari kolom bagian, sehingga opsi
        // 'bagian_lama' tidak pernah ada di dropdown. Menampilkannya akan membuat
        // browser memilih opsi pertama ("Operator") — lebih menyesatkan.
        Bagian::create(['kode' => 'AKN', 'nama' => 'AKN']);

        $dokumen = $this->dokumen([
            'status'        => 'returned_to_bidang',
            'return_source' => 'LAMA',
        ]);

        $baris = $this->baris($dokumen);

        $this->assertSame('team_verifikasi', $baris['handler']);
    }

    public function test_return_source_kosong_kembali_ke_current_handler(): void
    {
        Bagian::create(['kode' => 'AKN', 'nama' => 'AKN']);

        $dokumen = $this->dokumen([
            'status'        => 'returned_to_bidang',
            'return_source' => null,
        ]);

        $baris = $this->baris($dokumen);

        $this->assertSame('team_verifikasi', $baris['handler']);
    }

    public function test_handler_tampilan_mentah_tak_butuh_daftar_opsi(): void
    {
        // Nilai yang sama dengan yang dipakai handlerUntukTampilan(), tapi dihitung
        // tanpa daftar opsi — inilah yang dioper ke HandlerOptions::forDokumen()
        // supaya opsi tersebut tidak ikut terpangkas.
        Bagian::create(['kode' => 'AKN', 'nama' => 'AKN']);

        $dikembalikan = $this->dokumen([
            'nomor_agenda'  => '11_2026',
            'status'        => 'returned_to_bidang',
            'return_source' => 'AKN',
        ]);
        $this->assertSame('bagian_akn', VerifikasiDocumentRow::handlerTampilanMentah($dikembalikan));

        $biasa = $this->dokumen(['nomor_agenda' => '12_2026']);
        $this->assertSame('team_verifikasi', VerifikasiDocumentRow::handlerTampilanMentah($biasa));

        $tanpaSumber = $this->dokumen([
            'nomor_agenda'  => '13_2026',
            'status'        => 'returned_to_bidang',
            'return_source' => null,
        ]);
        $this->assertSame('team_verifikasi', VerifikasiDocumentRow::handlerTampilanMentah($tanpaSumber));

        $sumberBasi = $this->dokumen([
            'nomor_agenda'  => '14_2026',
            'status'        => 'sedang diproses',
            'return_source' => 'AKN',
        ]);
        $this->assertSame('team_verifikasi', VerifikasiDocumentRow::handlerTampilanMentah($sumberBasi));
    }
}

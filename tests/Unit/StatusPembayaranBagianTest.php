<?php

namespace Tests\Unit;

use App\Models\Dokumen;
use App\Models\DokumenRoleData;
use App\Support\StatusPembayaranBagian;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Menguji aturan status pembayaran 3-state milik role Bagian.
 *
 * Harness: Feature-style (Tests\TestCase + RefreshDatabase), BUKAN model
 * in-memory murni seperti draf awal. Alasan: mengisi atribut ber-cast 'date'
 * (tanggal_dibayar) memanggil getDateFormat() -> getConnection(), dan
 * getDataForRole('pembayaran') menjalankan query relasi roleData() sungguhan
 * — keduanya menuntut connection resolver Eloquent yang nyata, yang tidak
 * dipasang oleh PHPUnit\Framework\TestCase telanjang. Preseden persis di
 * AkutansiDocumentRowTest (lihat docblock kelasnya): draf "model in-memory"
 * gagal dengan alasan sejenis dan beralih ke harness yang sama ini. Pola
 * helper (buatDokumen/buatRoleData) meniru AkutansiDocumentRowTest.
 */
class StatusPembayaranBagianTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 0;

    /** Membuat & MENYIMPAN dokumen minimal + override. */
    private function buatDokumen(array $overrides = []): Dokumen
    {
        $this->seq++;

        return Dokumen::create(array_merge([
            'nomor_agenda'    => 'AG-' . $this->seq,
            'bulan'           => 'Agustus',
            'tahun'           => '2026',
            'tanggal_masuk'   => now(),
            'status'          => 'draft',
            'created_by'      => 'operator',
            'current_handler' => 'operator',
        ], $overrides));
    }

    /** Menambahkan satu record dokumen_role_data (received_at/processed_at/dll). */
    private function buatRoleData(Dokumen $dokumen, string $roleCode, array $attrs = []): DokumenRoleData
    {
        return DokumenRoleData::create(array_merge([
            'dokumen_id' => $dokumen->id,
            'role_code'  => $roleCode,
        ], $attrs));
    }

    public function test_sudah_dibayar_saat_status_pembayaran_final(): void
    {
        $doc = new Dokumen();
        $doc->status_pembayaran = 'sudah_dibayar';
        $doc->tanggal_dibayar = '2026-08-11 10:00:00';

        $hasil = StatusPembayaranBagian::untuk($doc);

        $this->assertSame('sudah-dibayar', $hasil['kelas']);
        $this->assertSame('Sudah Dibayar', $hasil['teks']);
        $this->assertSame('fa-check-circle', $hasil['ikon']);
        // 'tanggal_dibayar' ber-cast 'date' (bukan 'datetime') di Dokumen model —
        // SELALU dikembalikan sebagai Carbon dengan jam terpangkas ke tengah malam.
        // Yang perlu dijamin di sini: tanggalnya berasal dari tanggal_dibayar,
        // bukan format penyimpanan mentahnya.
        $this->assertSame('2026-08-11', $hasil['tanggal']->format('Y-m-d'));
    }

    public function test_sudah_dibayar_saat_hanya_tanggal_dibayar_terisi(): void
    {
        // Cabang OR: tanggal_dibayar terisi TAPI status_pembayaran belum final.
        // Mudah pecah saat diekstrak kalau OR keliru jadi AND.
        $doc = new Dokumen();
        $doc->status_pembayaran = null;
        $doc->tanggal_dibayar = '2026-08-12 08:30:00';
        $doc->current_handler = 'team_verifikasi';

        $hasil = StatusPembayaranBagian::untuk($doc);

        $this->assertSame('sudah-dibayar', $hasil['kelas']);
        $this->assertSame('Sudah Dibayar', $hasil['teks']);
    }

    public function test_belum_siap_dibayar_saat_masih_di_role_lain(): void
    {
        $doc = new Dokumen();
        $doc->status_pembayaran = null;
        $doc->tanggal_dibayar = null;
        $doc->current_handler = 'team_verifikasi';
        $doc->sent_at = '2026-08-01 09:00:00';

        $hasil = StatusPembayaranBagian::untuk($doc);

        $this->assertSame('belum-dibayar', $hasil['kelas']);
        $this->assertSame('Belum Siap Dibayar', $hasil['teks']);
        $this->assertSame('fa-clock', $hasil['ikon']);
        $this->assertSame('2026-08-01 09:00:00', $hasil['tanggal']);
    }

    public function test_handler_pembayaran_dikenali_case_insensitive(): void
    {
        // Kode lama memakai str_contains(strtolower(...), 'pembayaran').
        // Handler produksi pernah ditulis 'Team Pembayaran' berkapital.
        // Cabang ini memanggil getDataForRole('pembayaran') -> query relasi
        // roleData() sungguhan, jadi dokumen WAJIB tersimpan + punya baris
        // dokumen_role_data nyata (bukan model in-memory tanpa id).
        $doc = $this->buatDokumen([
            'status_pembayaran' => null,
            'tanggal_dibayar'   => null,
            'current_handler'   => 'Team Pembayaran',
        ]);
        $this->buatRoleData($doc, 'pembayaran', [
            'received_at' => now(),
        ]);

        $hasil = StatusPembayaranBagian::untuk($doc);

        $this->assertSame('siap-dibayar', $hasil['kelas']);
        $this->assertSame('Siap Dibayar', $hasil['teks']);
        $this->assertSame('fa-money-bill-wave', $hasil['ikon']);
    }

    public function test_current_handler_null_tidak_meledak(): void
    {
        $doc = new Dokumen();
        $doc->status_pembayaran = null;
        $doc->tanggal_dibayar = null;
        $doc->current_handler = null;
        $doc->sent_at = null;

        $hasil = StatusPembayaranBagian::untuk($doc);

        $this->assertSame('belum-dibayar', $hasil['kelas']);
    }
}

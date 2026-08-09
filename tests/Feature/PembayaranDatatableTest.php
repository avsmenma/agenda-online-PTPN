<?php

namespace Tests\Feature;

use App\Models\Dokumen;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Menguji endpoint JSON `GET documents/pembayaran/data` (name
 * `documents.pembayaran.data`) yang dipakai Tabulator pembayaran untuk
 * progressive load. Endpoint memakai ulang buildPembayaranQuery() (basis dari
 * buildPembayaranDashboardQuery, TANPA pengecualian CSV) + PembayaranDocumentRow
 * dan membalas struktur {last_page, total, data:[...]}.
 *
 * Pembayaran adalah SATU-SATUNYA role yang menyertakan dokumen hasil import CSV
 * (imported_from_csv=true) di tabelnya — beda dari akutansi/perpajakan/verifikasi
 * yang mengecualikannya secara default. Test kedua di bawah menegaskan itu.
 */
class PembayaranDatatableTest extends TestCase
{
    use RefreshDatabase;

    private function pembayaran(): User
    {
        return User::factory()->create(['role' => 'pembayaran']);
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
        ], $overrides));
    }

    public function test_endpoint_data_pembayaran_mengembalikan_bentuk_json_datatable(): void
    {
        $this->buatDokumen('1');

        $response = $this->actingAs($this->pembayaran())->getJson(route('documents.pembayaran.data'));

        $response->assertOk()
            ->assertJsonStructure([
                'last_page',
                'total',
                'data' => [['id', 'status_badge', 'can_edit']],
            ]);
    }

    public function test_dokumen_hasil_import_csv_tetap_muncul_di_tabel_pembayaran(): void
    {
        $this->buatDokumen('2-CSV', [
            'current_handler'   => 'operator',
            'status'            => 'draft',
            'imported_from_csv' => true,
        ]);

        $response = $this->actingAs($this->pembayaran())->getJson(route('documents.pembayaran.data'));

        $response->assertOk();
        $nomorAgendaList = collect($response->json('data'))->pluck('nomor_agenda');
        $this->assertTrue($nomorAgendaList->contains('2-CSV'));
    }

    public function test_endpoint_data_menolak_tamu(): void
    {
        $this->getJson(route('documents.pembayaran.data'))->assertUnauthorized();
    }

    /**
     * Kolom "Pengurus Dokumen" dihidupkan untuk Pembayaran 2026-08-06. Sebelumnya
     * handler_options selalu dikirim kosong sehingga formatter merender '-'. Sejak
     * 2026-08-09 pembayaran tak lagi menerima opsi Operator maupun optgroup Bagian
     * — lihat App\Support\HandlerOptions::bolehMenunjuk().
     */
    public function test_baris_membawa_opsi_pengurus_dokumen(): void
    {
        \App\Models\Bagian::create(['kode' => 'KEU', 'nama' => 'Keuangan']);
        // current_handler tidak dioper => tetap default buatDokumen() = 'pembayaran',
        // jadi handler yang DITAMPILKAN untuk baris ini adalah 'pembayaran' sendiri —
        // bukan 'operator' atau 'bagian_keu' — sehingga keduanya terpangkas TOTAL
        // (dibuang, bukan dipertahankan ber-disabled).
        $this->buatDokumen('3', ['bagian' => 'KEU']);

        $response = $this->actingAs($this->pembayaran())->getJson(route('documents.pembayaran.data'));

        $response->assertOk();

        $opsi = $response->json('data.0.handler_options');

        $this->assertNotEmpty($opsi, 'handler_options kosong => dropdown akan merender "-".');
        $this->assertFalse(
            collect($opsi)->contains(fn ($o) => ($o['value'] ?? null) === 'operator'),
            'Opsi Operator harus terpangkas total untuk pembayaran (HandlerOptions::bolehMenunjuk).'
        );
        $this->assertFalse(
            collect($opsi)->contains(fn ($o) => ($o['optgroup'] ?? null) === 'Bagian'),
            'Optgroup Bagian harus terpangkas total untuk pembayaran (HandlerOptions::bolehMenunjuk).'
        );
        $this->assertSame(
            ['team_verifikasi', 'perpajakan', 'akutansi', 'pembayaran'],
            array_column($opsi, 'value'),
            'Sisa opsi harus persis 4 peran maju, berurutan (larangan jalur mundur 2026-08-09).'
        );
    }

    public function test_dropdown_aktif_untuk_dokumen_di_pembayaran_dan_mati_untuk_tahap_lain(): void
    {
        $this->buatDokumen('4');                                    // current_handler = pembayaran
        $this->buatDokumen('5', ['current_handler' => 'perpajakan']);

        $response = $this->actingAs($this->pembayaran())->getJson(route('documents.pembayaran.data'));

        $response->assertOk();

        $baris = collect($response->json('data'))->keyBy('nomor_agenda');

        $this->assertTrue(
            $baris['4']['can_change_handler'],
            'Dokumen yang sedang di Pembayaran harus bisa dipindahkan.'
        );
        $this->assertFalse(
            $baris['5']['can_change_handler'],
            'Dokumen di tahap lain hanya boleh DITAMPILKAN posisinya, tidak bisa diubah.'
        );
        $this->assertSame('perpajakan', $baris['5']['handler']);
    }
}

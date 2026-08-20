<?php

namespace Tests\Feature;

use App\Models\Dokumen;
use App\Models\DokumenStatus;
use App\Support\DocumentRowCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Menguji App\Support\DocumentRowCache — cache PER-BARIS untuk DTO tabel dokumen.
 *
 * Yang dijaga di sini bukan kecepatannya (itu diukur di produksi), melainkan
 * KEBENARAN invalidasinya. Tiga lapis yang harus tetap hidup:
 *   1. sidik jari `updated_at` di kunci
 *   2. versi global yang dinaikkan event model (termasuk model ANAK, yang tidak
 *      punya $touches sehingga tak menggerakkan dokumens.updated_at)
 *   3. TTL sebagai jaring terakhir
 * Ditambah satu sifat keamanan: filter/pencarian TIDAK pernah menjadi bagian kunci.
 */
class DocumentRowCacheTest extends TestCase
{
    use RefreshDatabase;

    private function buatDokumen(string $agenda = '1_2026'): Dokumen
    {
        return Dokumen::create([
            'nomor_agenda'    => $agenda,
            'bulan'           => 'Juli',
            'tahun'           => 2026,
            'tanggal_masuk'   => '2026-07-01',
            'status'          => 'draft',
            'created_by'      => 'operator',
            'current_handler' => 'operator',
        ]);
    }

    /** Pemeta uji: menandai tiap pemanggilan supaya cache hit/miss terlihat. */
    private function petakan(array $dokumens, string $penanda, int &$hitungan): array
    {
        return DocumentRowCache::petakan($dokumens, $penanda, function (Dokumen $d) use (&$hitungan) {
            $hitungan++;

            return ['id' => $d->id, 'nomor' => $d->nomor_agenda, 'panggilan_ke' => $hitungan];
        });
    }

    public function test_pemanggilan_kedua_dilayani_cache_tanpa_menghitung_ulang(): void
    {
        $d = $this->buatDokumen();
        $n = 0;

        $a = $this->petakan([$d], 'uji', $n);
        $b = $this->petakan([$d], 'uji', $n);

        $this->assertSame(1, $n, 'Baris seharusnya dihitung SEKALI saja; panggilan kedua wajib dari cache.');
        $this->assertSame($a, $b);
    }

    public function test_perubahan_updated_at_membuat_kunci_baru(): void
    {
        $d = $this->buatDokumen();
        $n = 0;
        $this->petakan([$d], 'uji', $n);

        // Tulis MENTAH: tidak menyalakan event model, hanya menggerakkan updated_at.
        DB::table('dokumens')->where('id', $d->id)->update(['updated_at' => now()->addMinute()]);
        $segar = Dokumen::find($d->id);

        $this->petakan([$segar], 'uji', $n);

        $this->assertSame(2, $n, 'updated_at berubah => sidik jari kunci berubah => wajib hitung ulang.');
    }

    public function test_versi_global_naik_saat_model_anak_berubah(): void
    {
        $d = $this->buatDokumen();
        $n = 0;
        $this->petakan([$d], 'uji', $n);

        $sebelum = DocumentRowCache::versi();

        // DokumenStatus TIDAK punya $touches — dokumens.updated_at tak bergerak.
        // Tanpa kenaikan versi, badge status akan tersaji basi.
        DokumenStatus::create([
            'dokumen_id'        => $d->id,
            'role_code'         => 'team_verifikasi',
            'status'            => 'pending',
            'status_changed_at' => now(),
        ]);

        $this->assertGreaterThan($sebelum, DocumentRowCache::versi(), 'Simpan model anak wajib menaikkan versi.');

        $this->petakan([$d->fresh()], 'uji', $n);
        $this->assertSame(2, $n, 'Versi naik => seluruh kunci lama tak terjangkau => wajib hitung ulang.');
    }

    public function test_penanda_berbeda_tidak_saling_pakai_baris(): void
    {
        $d = $this->buatDokumen();
        $n = 0;

        $this->petakan([$d], 'operator|operator', $n);
        $this->petakan([$d], 'pembayaran|pembayaran', $n);

        $this->assertSame(2, $n, 'Penanda (kelas DTO + peran) berbeda wajib punya entri sendiri, bukan berbagi.');
    }

    public function test_dimatikan_lewat_config_selalu_menghitung_ulang(): void
    {
        config(['document_columns.cache.enabled' => false]);

        $d = $this->buatDokumen();
        $n = 0;

        $this->petakan([$d], 'uji', $n);
        $this->petakan([$d], 'uji', $n);

        $this->assertSame(2, $n, 'Sakelar mati wajib melewati cache sepenuhnya.');
        $this->assertFalse(DocumentRowCache::aktif());
    }

    public function test_urutan_hasil_mengikuti_urutan_masukan_saat_sebagian_dari_cache(): void
    {
        $a = $this->buatDokumen('1_2026');
        $b = $this->buatDokumen('2_2026');
        $n = 0;

        // Hangatkan HANYA dokumen b, supaya panggilan berikutnya bercampur hit & miss.
        $this->petakan([$b], 'uji', $n);

        $hasil = $this->petakan([$a, $b], 'uji', $n);

        $this->assertCount(2, $hasil);
        $this->assertSame($a->id, $hasil[0]['id'], 'Baris hasil hitung-baru harus tetap di posisi pertama.');
        $this->assertSame($b->id, $hasil[1]['id'], 'Baris dari cache harus tetap di posisi kedua.');
    }

    public function test_ttl_dipakai_saat_menyimpan(): void
    {
        config(['document_columns.cache.ttl' => 123]);

        // Dokumen dibuat SEBELUM Cache dipalsukan: menyimpannya menyalakan event model
        // yang memanggil Cache::increment, dan mock akan menolaknya.
        $d = $this->buatDokumen();

        $ttlTerpakai = null;
        Cache::shouldReceive('get')->andReturn(5);
        Cache::shouldReceive('many')->andReturn([]);
        Cache::shouldReceive('putMany')->once()->andReturnUsing(function ($isi, $ttl) use (&$ttlTerpakai) {
            $ttlTerpakai = $ttl;

            return true;
        });

        $n = 0;
        $this->petakan([$d], 'uji', $n);

        $this->assertSame(123, $ttlTerpakai, 'TTL wajib diambil dari config, bukan angka mati di kode.');
    }
}

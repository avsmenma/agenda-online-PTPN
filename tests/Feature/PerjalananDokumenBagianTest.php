<?php

namespace Tests\Feature;

use App\Models\Dokumen;
use App\Models\DokumenRoleData;
use App\Models\DokumenStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Menguji tampilan perjalanan dokumen di halaman role Bagian.
 */
class PerjalananDokumenBagianTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Query daftar dokumen Bagian mengurutkan pakai SUBSTRING_INDEX (fungsi MySQL)
        // yang tak ada di SQLite. Polyfill sama dengan OperatorDatatableTest.
        $pdo = DB::connection()->getPdo();
        if ($pdo->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $pdo->sqliteCreateFunction('substring_index', function ($str, $delim, $count) {
                $parts = explode($delim, (string) $str);

                return implode($delim, array_slice($parts, 0, (int) $count));
            });
        }
    }

    private function userBagian(string $kode = 'AKN'): User
    {
        // CheckBagianRole menuntut role BERAWALAN 'bagian_' DAN bagian_code terisi.
        return User::factory()->create([
            'role'        => 'bagian_' . strtolower($kode),
            'bagian_code' => $kode,
        ]);
    }

    private function dokumen(string $nomor, array $atribut = []): Dokumen
    {
        return Dokumen::create(array_merge([
            'nomor_agenda'    => $nomor . '_2026',
            'bulan'           => 'Agustus',
            'tahun'           => 2026,
            'tanggal_masuk'   => '2026-08-01',
            'status'          => 'sedang diproses',
            'created_by'      => 'operator',
            'current_handler' => 'team_verifikasi',
            'bagian'          => 'AKN',
        ], $atribut));
    }

    public function test_halaman_merender_rangkaian_tahap(): void
    {
        $this->dokumen('1');

        // Dipersempit (fix round 1): assertSee('dj-node', false) polos cocok dengan
        // selector CSS telanjang ".dj-node { ... }" di blok <style> — hampa terlepas dari
        // apakah markup betulan dirender. assertSee('Verifikasi', false) polos cocok
        // dengan teks console.log statis di layouts/app.blade.php ("Team Verifikasi",
        // ~baris 4023-4715) — tak berkaitan dengan fitur ini. Dibuktikan reviewer: ubah
        // @if($jalan) jadi @if(false) di view (seluruh markup dj-cell/dj-track/dj-node/
        // dj-label tak dirender, jatuh ke fallback '-') → test ini TETAP PASS dengan
        // assertion lama. '<span class="dj-track">' hanya muncul di markup yang benar-benar
        // dirender (bukan CSS, bukan JS lain); '>Verifikasi<' menyasar teks node HTML yang
        // terlihat (bukan console.log string di file lain).
        $this->actingAs($this->userBagian())
            ->get(route('bagian.documents.index'))
            ->assertOk()
            ->assertSee('<span class="dj-track">', false)
            ->assertSee('>Verifikasi<', false);
    }

    public function test_dokumen_dikembalikan_ditandai_perlu_diperbaiki(): void
    {
        $this->dokumen('2', ['status' => 'returned_to_bidang']);

        // Dipersempit dari assertSee polos ('Perlu diperbaiki' / 'dj-node--perlu_diperbaiki'
        // saja): blok <style> mendefinisikan SEMUA selector .dj-node--<state>, dan atribut
        // data-perjalanan="{{ json_encode($jalan) }}" ikut membawa current_label serta nama
        // state mentah — keduanya membuat assertSee polos SELALU lolos apa pun isi <span>
        // yang sesungguhnya dirender (dibuktikan lewat mutation test Step 7). '>Perlu
        // diperbaiki<' menyasar teks node HTML yang benar-benar terlihat (bukan nilai JSON),
        // 'dj-node dj-node--perlu_diperbaiki' menyasar atribut class simpul (bukan selector
        // CSS terpisah ".dj-node {" + ".dj-node--perlu_diperbaiki {").
        $this->actingAs($this->userBagian())
            ->get(route('bagian.documents.index'))
            ->assertOk()
            ->assertSee('>Perlu diperbaiki<', false)
            ->assertSee('dj-node dj-node--perlu_diperbaiki', false);
    }

    public function test_jejak_role_terbaca_dari_dokumen_role_data(): void
    {
        $dokumen = $this->dokumen('3', ['current_handler' => 'pembayaran']);

        DokumenRoleData::create([
            'dokumen_id'  => $dokumen->id,
            'role_code'   => 'team_verifikasi',
            'received_at' => now(),
        ]);

        $this->actingAs($this->userBagian())
            ->get(route('bagian.documents.index'))
            ->assertOk()
            // perpajakan & akutansi tak punya received_at => dilewati.
            // Dipersempit ke 'dj-node dj-node--dilewati' — alasan sama seperti test di atas
            // (selector CSS ".dj-node--dilewati {" membuat assertSee polos hampa).
            ->assertSee('dj-node dj-node--dilewati', false);
    }

    public function test_tidak_ada_n_plus_1_saat_dokumen_bertambah(): void
    {
        $user = $this->userBagian();

        $this->dokumen('10');
        DB::enableQueryLog();
        $this->actingAs($user)->get(route('bagian.documents.index', ['per_page' => 100]))->assertOk();
        $satuDokumen = count(DB::getQueryLog());

        DB::disableQueryLog();
        for ($i = 11; $i <= 25; $i++) {
            $this->dokumen((string) $i);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAs($user)->get(route('bagian.documents.index', ['per_page' => 100]))->assertOk();
        $limaBelasDokumen = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertLessThanOrEqual(
            $satuDokumen + 2,
            $limaBelasDokumen,
            "Jumlah query tumbuh dari {$satuDokumen} (1 dokumen) ke {$limaBelasDokumen} "
            . '(16 dokumen) — indikasi N+1 pada perjalanan dokumen.'
        );
    }

    public function test_modal_perjalanan_js_menyaring_simpul_bagian_kecuali_needs_action(): void
    {
        $this->dokumen('5');

        $response = $this->actingAs($this->userBagian())
            ->get(route('bagian.documents.index'))
            ->assertOk();

        $html = $response->getContent();

        // Ambil badan fungsi tampilkanPerjalanan saja (bukan seluruh halaman) supaya
        // assertion tak bisa lolos oleh kecocokan kebetulan di tempat lain pada file.
        $awal = strpos($html, 'window.tampilkanPerjalanan = function');
        $this->assertNotFalse($awal, 'Fungsi tampilkanPerjalanan tidak ditemukan di halaman.');
        $akhir = strpos($html, 'window.showRejectionModal = function', $awal);
        $this->assertNotFalse($akhir, 'Penanda akhir fungsi tampilkanPerjalanan tidak ditemukan.');
        $badanFungsi = substr($html, $awal, $akhir - $awal);

        // Dipersempit ke potongan kode filter yang khas — bukan string pendek seperti
        // 'needs_action' saja, yang JUGA muncul di atribut
        // data-perjalanan="{{ json_encode($jalan) }}" pada sel tabel (Task 3). Sebuah
        // assertSee pendek akan selalu lolos lewat JSON itu, terlepas apakah modal
        // betulan menyaring simpul Bagian atau tidak — persis pola kegagalan yang
        // sudah tiga kali terjadi di berkas ini (lihat komentar test-test di atas).
        $this->assertStringContainsString(
            'const tahapTampil = (jalan.stages || []).filter(function (tahap, i) {',
            $badanFungsi,
            'Modal harus membangun daftar tahap terfilter (tahapTampil), bukan memakai jalan.stages mentah.'
        );
        $this->assertStringContainsString(
            'return i > 0 || jalan.needs_action;',
            $badanFungsi,
            'Aturan filter modal harus sama persis dengan sel tabel: lewati stages[0] kecuali needs_action.'
        );

        // Modal WAJIB me-render dan mengisi teks dari tahapTampil yang SAMA (bukan
        // jalan.stages mentah lagi) — kalau tidak, pemasangan indeks <li> dengan
        // tahap akan meleset begitu simpul pertama disaring.
        $this->assertStringContainsString('tahapTampil.map(function (tahap) {', $badanFungsi);
        $this->assertStringContainsString('const tahap = tahapTampil[i];', $badanFungsi);
    }

    public function test_tahap_pending_dirender_menunggu_diterima(): void
    {
        $dokumen = $this->dokumen('6', ['current_handler' => 'team_verifikasi']);

        // Baris dokumen_statuses pending untuk tahap tujuan = "sudah dikirim, belum
        // diterima" (sendToRoleInbox tidak memajukan current_handler — lihat
        // DocumentJourney::indeksMenunggu()). status_changed_at wajib diisi: NOT NULL
        // di skema (2025_12_15_100001_create_dokumen_statuses_table), tidak ada
        // ->nullable() di migrasi.
        DokumenStatus::create([
            'dokumen_id'        => $dokumen->id,
            'role_code'         => 'perpajakan',
            'status'            => DokumenStatus::STATUS_PENDING,
            'status_changed_at' => now(),
        ]);

        // Dipersempit ke 'dj-node dj-node--menunggu_diterima' (bukan sekadar
        // 'menunggu_diterima' atau 'menunggu diterima' polos): blok <style>
        // mendefinisikan selector ".dj-node--menunggu_diterima { ... }" dan atribut
        // data-perjalanan="{{ json_encode($jalan) }}" membawa nama state mentah
        // 'menunggu_diterima' juga — keduanya membuat assertSee pendek lolos apa pun
        // markup <span> yang sesungguhnya dirender (pola kegagalan yang sudah empat
        // kali terjadi di berkas ini).
        $this->actingAs($this->userBagian())
            ->get(route('bagian.documents.index'))
            ->assertOk()
            ->assertSee('dj-node dj-node--menunggu_diterima', false);
    }

    public function test_dokumen_lunas_tidak_ada_titik_sekarang(): void
    {
        // tanggal_dibayar terisi = definisi kanonik lunas (sama dgn kartu "Sudah
        // Dibayar" di BagianDokumenController). current_handler sengaja BUKAN
        // 'pembayaran' (mis. data lama yang belum sempat diupdate) untuk
        // membuktikan $lunas benar-benar MENANG, bukan kebetulan current_handler
        // ada di ujung alur.
        $this->dokumen('7', [
            'current_handler' => 'perpajakan',
            'tanggal_dibayar' => now(),
        ]);

        $response = $this->actingAs($this->userBagian())
            ->get(route('bagian.documents.index'))
            ->assertOk();

        // Buktikan markup perjalanan memang dirender (bukan assertDontSee hampa
        // krn sel tak pernah tampil sama sekali) — lihat pola pengujian di
        // test-test lain berkas ini.
        $response->assertSee('dj-node dj-node--selesai', false);
        $response->assertDontSee('dj-node dj-node--sekarang', false);
    }

    public function test_markup_modal_perjalanan_ikut_dirender(): void
    {
        $this->dokumen('4');

        // Dipersempit (dibuktikan mutasi): assertSee('tampilkanPerjalanan', false) polos
        // HAMPA terhadap mutasi "ganti nama fungsi jadi tampilkanPerjalananX" — nama baru
        // itu tetap MENGANDUNG substring 'tampilkanPerjalanan', jadi assertSee polos lolos
        // apa pun nama fungsinya. 'window.tampilkanPerjalanan = function' menyasar deklarasi
        // fungsi persis (bukan onclick sel dari Task 2 yang juga memuat substring sama).
        $this->actingAs($this->userBagian())
            ->get(route('bagian.documents.index'))
            ->assertOk()
            ->assertSee('id="perjalananModal"', false)
            ->assertSee('window.tampilkanPerjalanan = function', false)
            ->assertSee('data-perjalanan', false);
    }

    public function test_kartu_informasi_bagian_empat_kartu_dan_reaktif_terhadap_filter(): void
    {
        // 2 dokumen lunas di 2026
        $this->dokumen('101', [
            'tahun'            => 2026,
            'bulan'            => 'Agustus',
            'nilai_rupiah'     => 5000000,
            'tanggal_dibayar'  => '2026-08-10',
            'status_pembayaran'=> 'sudah_dibayar',
            'dibayar_kepada'   => 'Vendor A',
        ]);
        $this->dokumen('102', [
            'tahun'            => 2026,
            'bulan'            => 'Agustus',
            'nilai_rupiah'     => 3000000,
            'tanggal_dibayar'  => '2026-08-11',
            'status_pembayaran'=> 'sudah_dibayar',
            'dibayar_kepada'   => 'Vendor B',
        ]);

        // 1 dokumen belum bayar di 2026
        $this->dokumen('103', [
            'tahun'            => 2026,
            'bulan'            => 'Agustus',
            'nilai_rupiah'     => 2000000,
            'tanggal_dibayar'  => null,
            'status_pembayaran'=> null,
            'dibayar_kepada'   => 'Vendor A',
        ]);

        // 1 dokumen di tahun 2025
        $this->dokumen('104', [
            'tahun'            => 2025,
            'bulan'            => 'Juli',
            'nilai_rupiah'     => 1000000,
            'tanggal_dibayar'  => null,
            'status_pembayaran'=> null,
            'dibayar_kepada'   => 'Vendor A',
        ]);

        $user = $this->userBagian('AKN');

        // 1. Tanpa filter: total 4 dokumen (2 sudah bayar, 2 belum bayar, total nilai 11.000.000)
        $resAll = $this->actingAs($user)->get(route('bagian.documents.index'))->assertOk();
        $resAll->assertSee('Total Dokumen AKN', false);
        $resAll->assertSee('Belum Dibayar', false);
        $resAll->assertSee('Sudah Dibayar', false);
        $resAll->assertSee('Nilai Dokumen', false);
        $resAll->assertSee('Rp 11.000.000', false);

        // 2. Filter tahun 2026: total 3 dokumen (2 sudah bayar, 1 belum bayar, total nilai 10.000.000)
        $res2026 = $this->actingAs($user)->get(route('bagian.documents.index', ['tahun' => 2026]))->assertOk();
        $res2026->assertSee('Rp 10.000.000', false);

        // 3. Filter tahun 2026 + status belum_dibayar: 1 dokumen, total nilai 2.000.000
        $resUnpaid2026 = $this->actingAs($user)->get(route('bagian.documents.index', [
            'tahun'  => 2026,
            'status' => 'belum_dibayar',
        ]))->assertOk();
        $resUnpaid2026->assertSee('Rp 2.000.000', false);
        $resUnpaid2026->assertSee('nilai belum dibayar', false);

        // 4. Filter vendor A: 3 dokumen (1 sudah bayar 5jt, 1 belum bayar 2jt, 1 2025 belum bayar 1jt = 8.000.000)
        $resVendorA = $this->actingAs($user)->get(route('bagian.documents.index', ['vendor' => 'Vendor A']))->assertOk();
        $resVendorA->assertSee('Rp 8.000.000', false);
    }
}


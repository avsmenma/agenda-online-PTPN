<?php

namespace Tests\Feature;

use App\Models\Dokumen;
use App\Models\DokumenRoleData;
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

        $this->actingAs($this->userBagian())
            ->get(route('bagian.documents.index'))
            ->assertOk()
            ->assertSee('dj-node', false)
            ->assertSee('Verifikasi', false);
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
}

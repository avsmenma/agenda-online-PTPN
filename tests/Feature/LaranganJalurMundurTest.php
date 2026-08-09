<?php

namespace Tests\Feature;

use App\Models\Bagian;
use App\Models\Dokumen;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Menguji larangan jalur mundur ke Operator & Bagian untuk perpajakan, akutansi,
 * dan pembayaran.
 *
 * Pemangkasan dropdown saja tidak cukup: opsi ditanam di data baris yang dikirim
 * ke klien, jadi bisa diakali lewat devtools. Penegakan sesungguhnya ada di
 * DocumentHandlerController::update().
 */
class LaranganJalurMundurTest extends TestCase
{
    use RefreshDatabase;

    private function dokumenDi(string $handler): Dokumen
    {
        return Dokumen::create([
            'nomor_agenda'    => '1_2026',
            'bulan'           => 'Agustus',
            'tahun'           => 2026,
            'tanggal_masuk'   => '2026-08-01',
            'status'          => 'sedang diproses',
            'created_by'      => 'operator',
            'current_handler' => $handler,
            'bagian'          => 'KEU',
        ]);
    }

    public function test_perpajakan_tak_bisa_mengembalikan_ke_operator(): void
    {
        Bagian::create(['kode' => 'KEU', 'nama' => 'Keuangan']);
        $dokumen = $this->dokumenDi('perpajakan');

        $this->actingAs(User::factory()->create(['role' => 'perpajakan']))
            ->patchJson(route('documents.handler.update', $dokumen), [
                'target_handler' => 'operator',
            ])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        $this->assertSame('perpajakan', $dokumen->fresh()->current_handler);
    }

    public function test_pembayaran_tak_bisa_mengembalikan_ke_operator(): void
    {
        Bagian::create(['kode' => 'KEU', 'nama' => 'Keuangan']);
        $dokumen = $this->dokumenDi('pembayaran');

        $this->actingAs(User::factory()->create(['role' => 'pembayaran']))
            ->patchJson(route('documents.handler.update', $dokumen), [
                'target_handler' => 'operator',
            ])
            ->assertStatus(403);

        $this->assertSame('pembayaran', $dokumen->fresh()->current_handler);
    }

    public function test_akutansi_tak_bisa_mengembalikan_ke_bagian_dan_nol_notifikasi(): void
    {
        Bagian::create(['kode' => 'KEU', 'nama' => 'Keuangan']);
        // Penerima nyata WAJIB ada: cabang in-app DocumentReturnNotifier selalu
        // jalan bila ada user ber-bagian_code. Tanpa user ini assertion notifikasi
        // akan hijau meski larangan dicabut — hampa.
        User::factory()->create(['role' => 'bagian_keu', 'bagian_code' => 'KEU']);
        Notification::fake();

        $dokumen = $this->dokumenDi('akutansi');

        $this->actingAs(User::factory()->create(['role' => 'akutansi']))
            ->patchJson(route('documents.handler.update', $dokumen), [
                'target_handler' => 'bagian_keu',
                'return_reason'  => 'Lampiran faktur belum lengkap sama sekali.',
            ])
            ->assertStatus(403);

        $this->assertSame('sedang diproses', $dokumen->fresh()->status);
        $this->assertNull($dokumen->fresh()->return_reason);
        Notification::assertNothingSent();
    }

    public function test_perpajakan_tetap_bisa_meneruskan_ke_akutansi(): void
    {
        // Gerbang tidak boleh melebar ke target yang sah.
        Bagian::create(['kode' => 'KEU', 'nama' => 'Keuangan']);
        $dokumen = $this->dokumenDi('perpajakan');

        $this->actingAs(User::factory()->create(['role' => 'perpajakan']))
            ->patchJson(route('documents.handler.update', $dokumen), [
                'target_handler' => 'akutansi',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);
    }

    public function test_verifikasi_tetap_bisa_mengembalikan_ke_operator(): void
    {
        // Role di luar daftar tidak boleh ikut terkena.
        Bagian::create(['kode' => 'KEU', 'nama' => 'Keuangan']);
        $dokumen = $this->dokumenDi('team_verifikasi');

        $this->actingAs(User::factory()->create(['role' => 'team_verifikasi']))
            ->patchJson(route('documents.handler.update', $dokumen), [
                'target_handler' => 'operator',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSame('operator', $dokumen->fresh()->current_handler);
    }

    public function test_operator_tetap_bisa_mengembalikan_ke_bagian(): void
    {
        // Jalur operator utuh — ia hulu alur dan memang berwenang.
        Bagian::create(['kode' => 'KEU', 'nama' => 'Keuangan']);
        $dokumen = $this->dokumenDi('operator');

        $this->actingAs(User::factory()->create(['role' => 'operator']))
            ->patchJson(route('documents.handler.update', $dokumen), [
                'target_handler' => 'bagian_keu',
                'return_reason'  => 'Nomor SPP belum dicantumkan pada berkas.',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSame('returned_to_bidang', $dokumen->fresh()->status);
    }

    public function test_akun_beralias_akuntansi_ikut_ditolak(): void
    {
        // Kolom role produksi bisa berisi alias. Akun ini tertangkap oleh
        // normalisasi berlapis: DocumentHandlerController::update() (baris 33)
        // sudah memanggil Role::normalize() sebelum guard baru ini dievaluasi,
        // dibackstop independen oleh Role::normalize() di dalam bolehMenunjuk()
        // sendiri. Test INI tidak membuktikan lapisan kedua itu — pembuktiannya
        // ada di HandlerOptionsTest::test_alias_peran_dan_target_ikut_dinormalisasi,
        // yang memanggil bolehMenunjuk() langsung dengan string alias mentah.
        Bagian::create(['kode' => 'KEU', 'nama' => 'Keuangan']);
        $dokumen = $this->dokumenDi('akutansi');

        $this->actingAs(User::factory()->create(['role' => 'akuntansi']))
            ->patchJson(route('documents.handler.update', $dokumen), [
                'target_handler' => 'operator',
            ])
            ->assertStatus(403);

        $this->assertSame('akutansi', $dokumen->fresh()->current_handler);
    }

    public function test_formatter_handler_merender_opsi_nonaktif(): void
    {
        $js = file_get_contents(public_path('js/document-tabulator.js'));

        // Assertion dipersempit ke BADAN fungsi: kata 'disabled' sudah muncul di
        // banyak tempat lain di berkas ini (atribut select, judul tooltip), jadi
        // pencarian ke seluruh berkas akan hampa.
        $this->assertSame(
            1,
            substr_count($js, 'function optionHtml(o) {'),
            'optionHtml tidak lagi tunggal — persempit ulang assertion ini'
        );

        $mulai = strpos($js, 'function optionHtml(o) {');
        $badan = substr($js, $mulai, 500);

        $this->assertStringContainsString(
            "(o.disabled ? ' disabled' : '')",
            $badan,
            'optionHtml tidak merender atribut disabled'
        );
    }
}

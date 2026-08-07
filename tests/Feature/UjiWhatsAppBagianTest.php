<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\DocumentReturnNotifier;
use App\Services\FonnteWhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * SEMENTARA — menguji tombol uji kiriman WhatsApp di halaman role Bagian.
 * Hapus seluruh berkas ini saat fitur uji coba dicabut (lihat daftar pencabutan
 * di docblock App\Http\Controllers\UjiWhatsAppBagianController).
 */
class UjiWhatsAppBagianTest extends TestCase
{
    use RefreshDatabase;

    public function test_pesan_uji_memakai_template_yang_sama_dengan_pesan_sungguhan(): void
    {
        // Pesan uji yang MENYIMPANG dari pesan sungguhan akan menipu responden
        // tanpa seorang pun menyadarinya. Karena itu bukan cuma "mengandung kata
        // yang mirip" — badannya wajib byte-per-byte hasil susunPesan().
        $tautan = 'http://contoh.test/bagian/documents';

        $uji = DocumentReturnNotifier::pesanUjiCoba('Tanaman', $tautan);

        $susunPesan = new \ReflectionMethod(DocumentReturnNotifier::class, 'susunPesan');
        $susunPesan->setAccessible(true);
        $badan = $susunPesan->invoke(
            null,
            '9999_2026',
            'Tanaman',
            'Lampiran faktur belum lengkap. (contoh)',
            $tautan
        );

        $this->assertStringEndsWith(
            $badan,
            $uji,
            'pesanUjiCoba() tidak berakhir dengan hasil susunPesan() — templatenya tersalin, bukan dipakai bersama.'
        );

        $this->assertStringStartsWith(
            '🧪',
            $uji,
            'Penanda uji coba hilang — responden bisa mengira ini pengembalian sungguhan.'
        );

        $this->assertStringContainsString('[UJI COBA', $uji);
    }

    private function userBagian(string $kode = 'TAN'): User
    {
        // CheckBagianRole menuntut role BERAWALAN 'bagian_' DAN bagian_code terisi.
        return User::factory()->create([
            'role'        => 'bagian_' . strtolower($kode),
            'bagian_code' => $kode,
        ]);
    }

    public function test_role_selain_bagian_ditolak(): void
    {
        $this->mock(FonnteWhatsAppService::class, function (MockInterface $m) {
            $m->shouldNotReceive('sendMessage');
        });

        $this->actingAs(User::factory()->create(['role' => 'team_verifikasi']))
            ->postJson(route('bagian.uji-whatsapp'), ['nomor_hp' => '081234567890'])
            ->assertForbidden();
    }

    public function test_nomor_tidak_sah_ditolak_dan_tidak_memanggil_gateway(): void
    {
        // Validasi harus menggigit SEBELUM kuota Fonnte terpakai.
        $this->mock(FonnteWhatsAppService::class, function (MockInterface $m) {
            $m->shouldNotReceive('sendMessage');
        });

        $this->actingAs($this->userBagian())
            ->postJson(route('bagian.uji-whatsapp'), ['nomor_hp' => '12345'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('nomor_hp');
    }

    public function test_nomor_sah_mengirim_pesan_berpenanda_uji_coba(): void
    {
        $this->mock(FonnteWhatsAppService::class, function (MockInterface $m) {
            $m->shouldReceive('sendMessage')
                ->once()
                ->withArgs(function (string $nomor, string $pesan) {
                    return $nomor === '081234567890'
                        && str_contains($pesan, '[UJI COBA')
                        && str_contains($pesan, '9999_2026')
                        // Nama bagian diambil dari akun yang login, bukan karangan.
                        && str_contains($pesan, 'Tanaman');
                })
                ->andReturn(['success' => true]);

            $m->shouldReceive('formatPhoneNumber')->andReturn('6281234567890');
        });

        \App\Models\Bagian::create(['kode' => 'TAN', 'nama' => 'Tanaman']);

        $this->actingAs($this->userBagian('TAN'))
            ->postJson(route('bagian.uji-whatsapp'), ['nomor_hp' => '081234567890'])
            ->assertOk()
            ->assertJson(['ok' => true]);
    }

    public function test_kegagalan_gateway_diteruskan_apa_adanya(): void
    {
        // Kalau tombol selalu bilang "terkirim", seluruh gunanya hilang — yang
        // sedang diuji justru apakah kiriman benar-benar sampai dari server.
        $this->mock(FonnteWhatsAppService::class, function (MockInterface $m) {
            $m->shouldReceive('sendMessage')->once()->andReturn([
                'success' => false,
                'reason'  => 'no_token',
                'message' => 'Fonnte API token not configured',
            ]);
        });

        \App\Models\Bagian::create(['kode' => 'TAN', 'nama' => 'Tanaman']);

        $response = $this->actingAs($this->userBagian('TAN'))
            ->postJson(route('bagian.uji-whatsapp'), ['nomor_hp' => '081234567890'])
            ->assertOk()
            ->assertJson(['ok' => false]);

        $this->assertStringContainsString(
            'Token',
            $response->json('pesan'),
            'Alasan gagal diseragamkan jadi pesan generik — user tak akan tahu apa yang harus diperbaiki.'
        );
    }
}

<?php

namespace Tests\Feature;

use App\Models\TwoFactorResetRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pengajuan reset 2FA dari halaman /2fa/verify (pengaju BELUM login).
 *
 * Latar: sebelum ini satu-satunya tombol pengajuan reset 2FA ada di
 * /profile/account yang ber-middleware 'auth'. User yang kehilangan
 * authenticator TIDAK bisa login, sehingga tak pernah bisa mengajukan —
 * sementara programmer menolak mereset tanpa request pending. Lingkaran
 * itu membuat akun terkunci permanen (nyata: akun `input` 2026-07-30).
 *
 * Identitas pengaju diambil dari session('2fa_user_id') yang HANYA terisi
 * setelah password diverifikasi benar di LoginController.
 */
class TwoFactorResetRequestFromVerifyTest extends TestCase
{
    use RefreshDatabase;

    private function userDengan2faAktif(): User
    {
        return User::factory()->create([
            'two_factor_enabled' => true,
            'two_factor_secret' => 'RAHASIABASE32',
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => encrypt(json_encode(['ABCDEFGHIJ'])),
        ]);
    }

    public function test_pengajuan_dari_halaman_verify_membuat_request_pending(): void
    {
        $user = $this->userDengan2faAktif();

        $response = $this->withSession(['2fa_user_id' => $user->id])
            ->post(route('2fa.reset-request'), [
                'reason' => 'Ganti HP dan authenticator hilang, recovery code tidak tersimpan.',
            ]);

        $response->assertRedirect(route('2fa.verify'));

        $this->assertDatabaseHas('two_factor_reset_requests', [
            'requester_id' => $user->id,
            'status' => 'pending',
        ]);
    }

    public function test_pengajuan_tanpa_sesi_2fa_tidak_membuat_baris(): void
    {
        $this->userDengan2faAktif();

        $response = $this->post(route('2fa.reset-request'), [
            'reason' => 'Mencoba mengajukan tanpa pernah memasukkan password.',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseCount('two_factor_reset_requests', 0);
    }

    /**
     * Gerbang keamanan inti: endpoint ini HANYA membuat permintaan.
     * Ia tidak boleh menonaktifkan 2FA — itu tetap wewenang programmer.
     */
    public function test_pengajuan_tidak_menonaktifkan_2fa_milik_user(): void
    {
        $user = $this->userDengan2faAktif();

        $this->withSession(['2fa_user_id' => $user->id])
            ->post(route('2fa.reset-request'), [
                'reason' => 'Authenticator terhapus saat reset pabrik ponsel.',
            ]);

        $user->refresh();

        $this->assertTrue($user->two_factor_enabled);
        $this->assertNotNull($user->two_factor_secret);
        $this->assertNotNull($user->two_factor_confirmed_at);
        $this->assertTrue($user->hasTwoFactorEnabled());
    }

    public function test_pengajuan_kedua_ditolak_saat_masih_ada_pending(): void
    {
        $user = $this->userDengan2faAktif();

        TwoFactorResetRequest::create([
            'requester_id' => $user->id,
            'reason' => 'Pengajuan pertama yang belum ditangani programmer.',
            'status' => 'pending',
        ]);

        $this->withSession(['2fa_user_id' => $user->id])
            ->post(route('2fa.reset-request'), [
                'reason' => 'Mencoba mengajukan untuk kedua kalinya.',
            ])
            ->assertSessionHasErrors('reason');

        $this->assertDatabaseCount('two_factor_reset_requests', 1);
    }

    public function test_alasan_kurang_dari_sepuluh_karakter_ditolak(): void
    {
        $user = $this->userDengan2faAktif();

        $this->withSession(['2fa_user_id' => $user->id])
            ->post(route('2fa.reset-request'), ['reason' => 'hilang'])
            ->assertSessionHasErrors('reason');

        $this->assertDatabaseCount('two_factor_reset_requests', 0);
    }

    public function test_user_tanpa_2fa_aktif_tidak_bisa_mengajukan(): void
    {
        $user = User::factory()->create([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
        ]);

        $this->withSession(['2fa_user_id' => $user->id])
            ->post(route('2fa.reset-request'), [
                'reason' => 'Mengajukan padahal 2FA belum pernah diaktifkan.',
            ])
            ->assertSessionHasErrors('reason');

        $this->assertDatabaseCount('two_factor_reset_requests', 0);
    }

    public function test_halaman_verify_memuat_form_pengajuan_reset(): void
    {
        $user = $this->userDengan2faAktif();

        $this->withSession(['2fa_user_id' => $user->id])
            ->get(route('2fa.verify'))
            ->assertStatus(200)
            ->assertSee(route('2fa.reset-request'), false)
            ->assertSee('name="reason"', false);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Dokumen;
use App\Models\DokumenStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Characterization test (golden-master) untuk OTORISASI INBOX approve/reject.
 *
 * Ini JARING PENGAMAN sebelum normalisasi penamaan role (Slice C).
 * InboxController masih memakai kosakata campur-case ("inbox enum"):
 *   getUserRole() mengembalikan 'Perpajakan'/'Akutansi'/'Pembayaran' (kapital)
 *   tapi 'operator'/'team_verifikasi' (huruf kecil), lalu di-strtolower lagi
 *   untuk cek isPendingForRole(). allowedRoles gate ikut bergantung pada nilai
 *   kapital itu. Test ini MENGUNCI siapa yang boleh/tidak approve & reject apa,
 *   supaya saat literal itu dinormalkan, regresi otorisasi langsung MERAH.
 *
 * Aturan efektif yang dikunci (bukan menilai benar/salah desain):
 *   Otorisasi approve/reject = DUA lapis —
 *     (1) middleware route `role:operator,team_verifikasi,verifikasi,
 *         perpajakan,akutansi,pembayaran,admin` (case-insensitive), lalu
 *     (2) controller: dokumen harus `isPendingForRole()` untuk role user.
 *   Backstop sebenarnya adalah cek "pending untuk role saya" — bukan daftar role.
 *
 * Tidak ada seed role manual — sama seperti AutoForwardDokumenTest, mengandalkan
 * rantai migrasi (RefreshDatabase) yang membuat roles operator/team_verifikasi/
 * perpajakan/akutansi/pembayaran/system.
 */
class InboxApprovalAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake(); // cegah job sync (mis. SyncDokumenToCashBankJob) berjalan
    }

    private function buatUser(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    /** Dokumen dengan status PENDING untuk satu role tertentu (state inbox). */
    private function buatDokumenPendingUntuk(string $roleCode, string $nomorAgenda): Dokumen
    {
        $dokumen = Dokumen::create([
            'nomor_agenda'    => $nomorAgenda,
            'created_by'      => 'operator',
            'current_handler' => $roleCode,
            'status'          => 'draft',
            'tanggal_masuk'   => now(),
            'bulan'           => now()->translatedFormat('F'),
            'tahun'           => (string) now()->year,
        ]);

        // Bikin state "pending untuk role X" langsung lewat model method resmi.
        $dokumen->setStatusForRole($roleCode, DokumenStatus::STATUS_PENDING);

        return $dokumen->fresh();
    }

    // =====================================================================
    // APPROVE
    // =====================================================================

    /** Role approval boleh menyetujui dokumen yang pending untuk dirinya. */
    public function test_role_dapat_approve_dokumen_yang_pending_untuknya(): void
    {
        $roles = ['team_verifikasi', 'perpajakan', 'akutansi', 'pembayaran'];

        foreach ($roles as $i => $role) {
            $dokumen = $this->buatDokumenPendingUntuk($role, "AG-APV-{$i}");
            $user = $this->buatUser($role);

            $this->assertTrue(
                $dokumen->isPendingForRole($role),
                "Prasyarat: dokumen harus pending untuk {$role}"
            );

            $response = $this->actingAs($user)
                ->postJson(route('inbox.approve', $dokumen));

            $response->assertOk();
            $response->assertJson(['success' => true]);

            $dokumen->refresh();
            $this->assertFalse(
                $dokumen->isPendingForRole($role),
                "Setelah approve, {$role} tidak lagi pending"
            );
            $this->assertSame(
                DokumenStatus::STATUS_APPROVED,
                $dokumen->getStatusForRole($role)->status,
                "Status {$role} harus APPROVED"
            );
        }
    }

    /** Role TIDAK boleh approve dokumen yang pending untuk role lain. */
    public function test_role_tidak_dapat_approve_dokumen_role_lain(): void
    {
        // Dokumen pending untuk perpajakan, tapi yang mencoba adalah akutansi.
        $dokumen = $this->buatDokumenPendingUntuk('perpajakan', 'AG-XROLE');
        $penyusup = $this->buatUser('akutansi');

        $response = $this->actingAs($penyusup)
            ->postJson(route('inbox.approve', $dokumen));

        // Lolos middleware (akutansi role valid) tapi ditolak controller (bukan pending untuknya).
        $response->assertStatus(422);
        $response->assertJson(['success' => false]);

        $dokumen->refresh();
        $this->assertTrue(
            $dokumen->isPendingForRole('perpajakan'),
            'Dokumen harus tetap pending untuk perpajakan (tidak berubah)'
        );
        $this->assertNull(
            $dokumen->getStatusForRole('akutansi'),
            'Tidak boleh ada status akutansi yang terbentuk'
        );
    }

    /** Role di luar daftar inbox ditolak MIDDLEWARE sebelum menyentuh controller. */
    public function test_role_non_inbox_ditolak_middleware_saat_approve(): void
    {
        $dokumen = $this->buatDokumenPendingUntuk('perpajakan', 'AG-MW');

        foreach (['bagian_sdm', 'owner', 'programmer'] as $role) {
            $user = $this->buatUser($role);

            $response = $this->actingAs($user)
                ->postJson(route('inbox.approve', $dokumen));

            $response->assertStatus(403); // CheckRole -> 403 untuk request JSON

            $dokumen->refresh();
            $this->assertTrue(
                $dokumen->isPendingForRole('perpajakan'),
                "Dokumen tetap pending; {$role} tidak boleh mengubah apa pun"
            );
        }
    }

    /**
     * Route universal-approval approve/reject (menunjuk InboxController, hanya
     * di-guard `auth` tanpa role gate) adalah duplikat mati tanpa pemanggil di
     * frontend — DIHAPUS (PL-3, 2026-07-05). Approve/reject resmi hanya lewat
     * `/inbox/*` yang bergerbang role + cek pending. Test ini mengunci bahwa
     * jalur lama itu tidak diam-diam hidup lagi.
     */
    public function test_route_universal_approval_approve_reject_sudah_dihapus(): void
    {
        $dokumen = $this->buatDokumenPendingUntuk('perpajakan', 'AG-UNI');
        $user = $this->buatUser('perpajakan');

        $this->actingAs($user)
            ->postJson("/universal-approval/{$dokumen->id}/approve")
            ->assertNotFound();

        $this->actingAs($user)
            ->postJson("/universal-approval/{$dokumen->id}/reject", ['reason' => 'x'])
            ->assertNotFound();
    }

    // =====================================================================
    // REJECT
    // =====================================================================

    /** Reject mengembalikan dokumen ke handler sebelumnya sesuai alur. */
    public function test_reject_mengembalikan_ke_handler_sebelumnya(): void
    {
        // roleCode penolak => current_handler yang diharapkan setelah reject
        $rutePengembalian = [
            'team_verifikasi' => 'operator',
            'perpajakan'      => 'team_verifikasi',
            'akutansi'        => 'perpajakan',
            'pembayaran'      => 'akutansi',
        ];

        foreach ($rutePengembalian as $role => $handlerTujuan) {
            $dokumen = $this->buatDokumenPendingUntuk($role, "AG-REJ-{$role}");
            $user = $this->buatUser($role);

            $response = $this->actingAs($user)->postJson(
                route('inbox.reject', $dokumen),
                ['reason' => "ditolak oleh {$role}"]
            );

            $response->assertOk();
            $response->assertJson(['success' => true]);

            $dokumen->refresh();
            $this->assertSame(
                DokumenStatus::STATUS_REJECTED,
                $dokumen->getStatusForRole($role)->status,
                "Status {$role} harus REJECTED"
            );
            $this->assertSame(
                $handlerTujuan,
                $dokumen->current_handler,
                "Reject oleh {$role} harus mengembalikan ke {$handlerTujuan}"
            );
        }
    }

    /** Reject WAJIB menyertakan alasan. */
    public function test_reject_wajib_alasan(): void
    {
        $dokumen = $this->buatDokumenPendingUntuk('perpajakan', 'AG-REJ-NOREASON');
        $user = $this->buatUser('perpajakan');

        $response = $this->actingAs($user)
            ->postJson(route('inbox.reject', $dokumen), []); // tanpa reason

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);

        $dokumen->refresh();
        $this->assertTrue(
            $dokumen->isPendingForRole('perpajakan'),
            'Dokumen tetap pending karena reject gagal validasi'
        );
    }

    // =====================================================================
    // AKSES HALAMAN INBOX (index)
    // =====================================================================

    /** Hanya role kerja hilir yang punya inbox (operator/admin TIDAK, per PL-1/PL-2). */
    public function test_inbox_index_dapat_diakses_role_workflow(): void
    {
        foreach (['team_verifikasi', 'perpajakan', 'akutansi', 'pembayaran'] as $role) {
            $user = $this->buatUser($role);

            $this->actingAs($user)
                ->get(route('inbox.index'))
                ->assertOk();
        }
    }

    /**
     * Role tanpa inbox ditolak middleware (redirect ke /login).
     * - operator: bagian tak lagi kirim dokumen → operator tak butuh inbox (PL-2).
     * - admin: KABAG god-view, bukan pekerja → tak punya inbox (PL-1).
     * - bagian/owner/programmer: memang di luar alur inbox.
     */
    public function test_inbox_index_ditolak_untuk_role_tanpa_inbox(): void
    {
        foreach (['operator', 'admin', 'bagian_sdm', 'owner', 'programmer'] as $role) {
            $user = $this->buatUser($role);

            $this->actingAs($user)
                ->get(route('inbox.index'))
                ->assertRedirect('/login');
        }
    }
}

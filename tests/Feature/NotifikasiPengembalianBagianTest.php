<?php

namespace Tests\Feature;

use App\Models\Bagian;
use App\Models\Dokumen;
use App\Models\User;
use App\Notifications\DokumenDikembalikanNotification;
use App\Services\DocumentReturnNotifier;
use App\Services\FonnteWhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Menguji App\Services\DocumentReturnNotifier — sumber tunggal pemberitahuan
 * "dokumen dikembalikan ke Bagian", dipakai bersama oleh jalur dropdown Pengurus
 * Dokumen dan jalur returnToBidang.
 *
 * Aturan yang dijaga: notifikasi in-app SELALU ditulis, bukan hanya cadangan saat
 * user tak punya nomor HP. Pemeriksaan produksi 2026-08-06 menemukan 0 dari 8 user
 * Bagian punya phone_number — dengan perilaku lama, tidak seorang pun pernah
 * diberi tahu.
 */
class NotifikasiPengembalianBagianTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Query daftar dokumen Bagian mengurutkan pakai SUBSTRING_INDEX (fungsi
        // MySQL) yang tak ada di SQLite. Polyfill yang sama dipakai
        // OperatorDatatableTest — bukan perilaku yang sedang diuji di sini.
        $pdo = \DB::connection()->getPdo();
        if ($pdo->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $pdo->sqliteCreateFunction('substring_index', function ($str, $delim, $count) {
                $parts = explode($delim, (string) $str);

                return implode($delim, array_slice($parts, 0, (int) $count));
            });
        }
    }

    private function userBagian(string $kode): User
    {
        // Middleware CheckBagianRole menuntut role BERAWALAN 'bagian_' (bukan
        // 'bagian' polos) DAN bagian_code terisi.
        return User::factory()->create([
            'role'        => 'bagian_' . strtolower($kode),
            'bagian_code' => $kode,
        ]);
    }

    private function dokumen(string $bagian): Dokumen
    {
        return Dokumen::create([
            'nomor_agenda'    => '1_2026',
            'bulan'           => 'Juli',
            'tahun'           => 2026,
            'tanggal_masuk'   => '2026-07-01',
            'status'          => 'returned_to_bidang',
            'created_by'      => 'operator',
            'current_handler' => 'team_verifikasi',
            'bagian'          => $bagian,
        ]);
    }

    public function test_notifikasi_in_app_ditulis_meski_tanpa_nomor_hp(): void
    {
        Bagian::create(['kode' => 'KEU', 'nama' => 'Keuangan']);
        $user = $this->userBagian('KEU');
        $user->update(['phone_number' => null]);

        DocumentReturnNotifier::kirim($this->dokumen('KEU'), 'KEU', 'Lampiran faktur belum lengkap.');

        $this->assertSame(1, $user->fresh()->notifications()->count());
    }

    public function test_isi_notifikasi_memuat_alasan_dan_nomor_agenda(): void
    {
        Bagian::create(['kode' => 'KEU', 'nama' => 'Keuangan']);
        $user = $this->userBagian('KEU');
        $alasan = 'Nilai rupiah tidak cocok dengan lampiran.';

        DocumentReturnNotifier::kirim($this->dokumen('KEU'), 'KEU', $alasan);

        $data = $user->fresh()->notifications()->first()->data;

        $this->assertSame($alasan, $data['alasan']);
        $this->assertSame('1_2026', $data['nomor_agenda']);
        $this->assertSame('Keuangan', $data['bidang_name']);
    }

    public function test_hanya_user_bagian_tujuan_yang_diberi_tahu(): void
    {
        Bagian::create(['kode' => 'KEU', 'nama' => 'Keuangan']);
        Bagian::create(['kode' => 'SDM', 'nama' => 'Sumber Daya Manusia']);
        $tujuan = $this->userBagian('KEU');
        $lain   = $this->userBagian('SDM');

        DocumentReturnNotifier::kirim($this->dokumen('KEU'), 'KEU', 'Perlu perbaikan lampiran SPP.');

        $this->assertSame(1, $tujuan->fresh()->notifications()->count());
        $this->assertSame(0, $lain->fresh()->notifications()->count());
    }

    public function test_pengembalian_lewat_dropdown_memicu_notifikasi(): void
    {
        Bagian::create(['kode' => 'KEU', 'nama' => 'Keuangan']);
        $userBagian = $this->userBagian('KEU');

        $dokumen = Dokumen::create([
            'nomor_agenda'    => '2_2026',
            'bulan'           => 'Juli',
            'tahun'           => 2026,
            'tanggal_masuk'   => '2026-07-01',
            'status'          => 'sedang diproses',
            'created_by'      => 'operator',
            'current_handler' => 'team_verifikasi',
            'bagian'          => 'KEU',
        ]);

        $this->actingAs(User::factory()->create(['role' => 'team_verifikasi']))
            ->patchJson(route('documents.handler.update', $dokumen), [
                'target_handler' => 'bagian_keu',
                'return_reason'  => 'Tanda tangan pejabat berwenang belum ada.',
            ])
            ->assertOk();

        $this->assertSame(1, $userBagian->fresh()->notifications()->count());
    }

    public function test_panel_notifikasi_tampil_di_halaman_bagian(): void
    {
        Bagian::create(['kode' => 'KEU', 'nama' => 'Keuangan']);
        $user = $this->userBagian('KEU');
        $dokumen = $this->dokumen('KEU');

        $user->notify(new DokumenDikembalikanNotification($dokumen, 'Uraian SPP tidak sesuai kontrak.', 'Keuangan'));

        $this->actingAs($user)
            ->get(route('bagian.documents.index'))
            ->assertOk()
            ->assertSee('dokumen dikembalikan', false)
            ->assertSee('Uraian SPP tidak sesuai kontrak.', false);
    }

    public function test_tandai_dibaca_menghapus_panel(): void
    {
        Bagian::create(['kode' => 'KEU', 'nama' => 'Keuangan']);
        $user = $this->userBagian('KEU');

        $user->notify(new DokumenDikembalikanNotification($this->dokumen('KEU'), 'Alasan pengembalian percobaan.', 'Keuangan'));

        $this->actingAs($user)
            ->post(route('bagian.notifikasi.tandai-dibaca'))
            ->assertRedirect();

        $this->assertSame(0, $user->fresh()->unreadNotifications()->count());
        $this->assertSame(1, $user->fresh()->notifications()->count(), 'Catatannya tetap ada, hanya ditandai terbaca.');
    }

    public function test_kolom_pengembalian_tampil_di_tabel_bagian(): void
    {
        Bagian::create(['kode' => 'KEU', 'nama' => 'Keuangan']);
        $user = $this->userBagian('KEU');
        $this->dokumen('KEU');

        $this->actingAs($user)
            ->get(route('bagian.documents.index'))
            ->assertOk()
            // Teks title ini HANYA ada di sel kolom pengembalian. Sengaja bukan
            // 'showRejectionModal': fungsi JS itu ada di halaman terlepas dari
            // kolomnya, jadi assertion-nya akan hampa.
            ->assertSee('Klik untuk melihat alasan pengembalian', false);
    }

    /**
     * susunPesan() diubah 2026-08-07 dari menerima objek Dokumen menjadi menerima
     * nilai biasa (agenda, nama bagian, alasan, tautan) supaya template pesannya
     * bisa dipakai bersama panel uji coba WhatsApp (lihat UjiWhatsAppBagianTest).
     * Tak satu pun test lain di berkas ini mengisi phone_number dengan nilai asli,
     * sehingga cabang WhatsApp di kirim() (satu-satunya pemanggil susunPesan() di
     * jalur produksi) sebelumnya TIDAK PERNAH tereksekusi oleh suite ini — kalau
     * argumen tertukar di kirim(), tak ada test yang merah dan pesan WhatsApp
     * produksi bisa berbunyi "dikembalikan ke Bagian Nilai rupiah tidak cocok."
     * tanpa ada yang tahu sampai user melihatnya. Test ini menutup celah itu.
     */
    public function test_pesan_whatsapp_menyusun_argumen_pada_posisi_yang_benar(): void
    {
        Bagian::create(['kode' => 'KEU', 'nama' => 'Keuangan']);
        $user = $this->userBagian('KEU');
        $user->update(['phone_number' => '081234567890']);

        $dokumen = $this->dokumen('KEU');
        $alasan  = 'Nilai rupiah tidak cocok.';

        $pesanTerkirim = null;

        $this->mock(FonnteWhatsAppService::class, function ($mock) use (&$pesanTerkirim) {
            $mock->shouldReceive('sendMessage')
                ->once()
                ->andReturnUsing(function ($nomor, $pesan) use (&$pesanTerkirim) {
                    $pesanTerkirim = $pesan;

                    return ['status' => true];
                });
        });

        DocumentReturnNotifier::kirim($dokumen, 'KEU', $alasan);

        $this->assertNotNull($pesanTerkirim, 'sendMessage() tidak pernah dipanggil.');

        // Nama bagian wajib TEPAT setelah "ke Bagian " — bukan cuma "ada di suatu
        // tempat di string". Assertion "mengandung nama bagian" saja tidak cukup:
        // nama bagian tetap ada di string meski tertukar posisi dengan alasan.
        $this->assertStringContainsString('ke Bagian Keuangan.', $pesanTerkirim);

        // Alasan wajib TEPAT setelah label "Alasan Pengembalian:*\n".
        $this->assertStringContainsString("Alasan Pengembalian:*\n" . $alasan, $pesanTerkirim);

        // Nomor agenda dokumen wajib muncul di antara "agenda *" dan "*".
        $this->assertStringContainsString('agenda *' . $dokumen->nomor_agenda . '*', $pesanTerkirim);

        // Tautan wajib URL dokumen yang benar, bukan string kosong.
        $tautanDiharapkan = url(route('inbox.show', $dokumen->id, false));
        $this->assertStringContainsString('Lihat dokumen: ' . $tautanDiharapkan, $pesanTerkirim);
    }
}

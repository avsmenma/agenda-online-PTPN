<?php

namespace Tests\Feature;

use App\Models\Dokumen;
use App\Models\DokumenStatus;
use App\Models\User;
use App\Models\WhatsAppNotificationLog;
use App\Services\FonnteWhatsAppService;
use App\Services\PendingApprovalReminderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Menguji App\Services\PendingApprovalReminderService — pengingat WhatsApp untuk
 * dokumen yang sudah dikirim ke suatu peran tapi belum di-approve.
 *
 * Alasan fitur ini ada (saran penguji sidang 2026-08-05): mekanisme deadline yang
 * sudah ada menyaring `dokumen_role_data.received_at IS NOT NULL`, sedangkan
 * received_at SENGAJA kosong sampai dokumen di-approve dari inbox. Jadi justru
 * jendela "menunggu approval" tidak terpantau sama sekali.
 */
class PengingatApprovalMenggantungTest extends TestCase
{
    use RefreshDatabase;

    /** @var array<int,string> nomor tujuan yang "terkirim" selama test */
    private array $terkirim = [];

    protected function setUp(): void
    {
        parent::setUp();

        config(['fonnte.pending_approval.hours' => 6]);
        config(['fonnte.pending_approval.cooldown_hours' => 24]);

        // Gateway WhatsApp dipalsukan — test TIDAK boleh memanggil API sungguhan.
        $palsu = new class extends FonnteWhatsAppService
        {
            public array $dikirim = [];

            public function __construct() {}

            public function sendMessage(string $phoneNumber, string $message): array
            {
                $this->dikirim[] = ['ke' => $phoneNumber, 'pesan' => $message];

                return ['status' => true];
            }
        };

        $this->app->instance(FonnteWhatsAppService::class, $palsu);
    }

    private function gateway(): FonnteWhatsAppService
    {
        return $this->app->make(FonnteWhatsAppService::class);
    }

    private function layanan(): PendingApprovalReminderService
    {
        return $this->app->make(PendingApprovalReminderService::class);
    }

    private function dokumenPending(string $peran, int $jamLalu): Dokumen
    {
        $dokumen = Dokumen::create([
            'nomor_agenda'    => '1_2026',
            'bulan'           => 'Agustus',
            'tahun'           => 2026,
            'tanggal_masuk'   => '2026-08-01',
            'uraian_spp'      => 'Pembayaran jasa pemeliharaan',
            'status'          => 'sedang diproses',
            'created_by'      => 'operator',
            'current_handler' => $peran,
        ]);

        DokumenStatus::create([
            'dokumen_id'        => $dokumen->id,
            'role_code'         => $peran,
            'status'            => DokumenStatus::STATUS_PENDING,
            'status_changed_at' => now()->subHours($jamLalu),
            'changed_by'        => 'operator',
        ]);

        return $dokumen;
    }

    private function penerima(string $peran, ?string $hp = '081234567890'): User
    {
        return User::factory()->create(['role' => $peran, 'phone_number' => $hp]);
    }

    public function test_dokumen_menggantung_lebih_dari_ambang_memicu_pengingat(): void
    {
        $this->penerima('perpajakan');
        $this->dokumenPending('perpajakan', 7);

        $hasil = $this->layanan()->kirimPengingat();

        $this->assertSame(1, $hasil['terkirim']);
        $this->assertCount(1, $this->gateway()->dikirim);
        $this->assertStringContainsString('1_2026', $this->gateway()->dikirim[0]['pesan']);
    }

    public function test_dokumen_belum_melewati_ambang_tidak_diingatkan(): void
    {
        $this->penerima('perpajakan');
        $this->dokumenPending('perpajakan', 5);

        $hasil = $this->layanan()->kirimPengingat();

        $this->assertSame(0, $hasil['diperiksa']);
        $this->assertSame(0, $hasil['terkirim']);
        $this->assertCount(0, $this->gateway()->dikirim);
    }

    public function test_dokumen_yang_sudah_diapprove_tidak_diingatkan(): void
    {
        $this->penerima('perpajakan');
        $dokumen = $this->dokumenPending('perpajakan', 10);

        DokumenStatus::where('dokumen_id', $dokumen->id)
            ->update(['status' => DokumenStatus::STATUS_APPROVED]);

        $hasil = $this->layanan()->kirimPengingat();

        $this->assertSame(0, $hasil['diperiksa']);
        $this->assertCount(0, $this->gateway()->dikirim);
    }

    public function test_pengingat_ditujukan_ke_peran_penerima_bukan_peran_lain(): void
    {
        $penerima = $this->penerima('perpajakan', '081111111111');
        $this->penerima('akutansi', '082222222222');
        $this->dokumenPending('perpajakan', 8);

        $this->layanan()->kirimPengingat();

        $this->assertCount(1, $this->gateway()->dikirim);
        $this->assertSame($penerima->phone_number, $this->gateway()->dikirim[0]['ke']);
    }

    public function test_masa_tenang_mencegah_pengingat_beruntun(): void
    {
        $this->penerima('perpajakan');
        $this->dokumenPending('perpajakan', 8);

        $this->layanan()->kirimPengingat();
        $hasilKedua = $this->layanan()->kirimPengingat();

        $this->assertSame(0, $hasilKedua['terkirim']);
        $this->assertSame(1, $hasilKedua['dilewati']);
        $this->assertCount(1, $this->gateway()->dikirim, 'Hanya satu pesan meski dijalankan dua kali.');
    }

    public function test_peran_tanpa_nomor_hp_dilewati_bukan_dianggap_gagal(): void
    {
        $this->penerima('akutansi', null);
        $this->dokumenPending('akutansi', 9);

        $hasil = $this->layanan()->kirimPengingat();

        $this->assertSame(1, $hasil['diperiksa']);
        $this->assertSame(0, $hasil['terkirim']);
        $this->assertSame(1, $hasil['dilewati']);
        $this->assertSame(0, $hasil['gagal']);
    }

    public function test_dry_run_tidak_mengirim_apa_pun(): void
    {
        $this->penerima('perpajakan');
        $this->dokumenPending('perpajakan', 8);

        $hasil = $this->layanan()->kirimPengingat(true);

        $this->assertSame(1, $hasil['diperiksa']);
        $this->assertSame(0, $hasil['terkirim']);
        $this->assertCount(0, $this->gateway()->dikirim);
        $this->assertSame(0, WhatsAppNotificationLog::count());
    }

    public function test_pengiriman_dicatat_agar_bisa_diaudit(): void
    {
        $this->penerima('perpajakan');
        $dokumen = $this->dokumenPending('perpajakan', 8);

        $this->layanan()->kirimPengingat();

        $log = WhatsAppNotificationLog::where('dokumen_id', $dokumen->id)->first();

        $this->assertNotNull($log);
        $this->assertSame('pending_approval', $log->message_type);
        $this->assertSame('success', $log->status);
    }

    public function test_perintah_artisan_berjalan(): void
    {
        config(['fonnte.enabled' => true, 'fonnte.api_token' => 'token-uji']);

        $this->penerima('perpajakan');
        $this->dokumenPending('perpajakan', 8);

        $this->artisan('notifications:send-pending-approval')
            ->assertExitCode(0);

        $this->assertCount(1, $this->gateway()->dikirim);
    }
}

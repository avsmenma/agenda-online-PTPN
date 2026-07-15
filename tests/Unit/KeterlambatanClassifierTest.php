<?php

namespace Tests\Unit;

use App\Support\KeterlambatanClassifier;
use Carbon\Carbon;
use Tests\TestCase;

class KeterlambatanClassifierTest extends TestCase
{
    private Carbon $now;

    protected function setUp(): void
    {
        parent::setUp();
        $this->now = Carbon::parse('2026-07-15 12:00:00');
    }

    /** Dokumen belum diterima → netral (tidak dihitung). */
    public function test_tanpa_received_at_netral(): void
    {
        $this->assertNull(
            KeterlambatanClassifier::bucket('pembayaran', null, null, null, $this->now)
        );
    }

    /**
     * Inti bug: pembayaran SUDAH DIBAYAR yang diterima 3 hari lalu tapi diproses
     * di hari yang sama → umur beku ~0 jam → AMAN (bukan peringatan).
     */
    public function test_pembayaran_sudah_dibayar_beku_di_processed_at_aman(): void
    {
        $received  = Carbon::parse('2026-07-12 10:00:00'); // 3 hari lalu
        $processed = Carbon::parse('2026-07-12 10:30:00'); // diproses ~sama
        $this->assertSame(
            'aman',
            KeterlambatanClassifier::bucket('pembayaran', $received, $processed, 'sudah_dibayar', $this->now)
        );
    }

    /** Pembayaran belum dibayar, diterima 8 hari lalu (192 jam) → PERINGATAN (168–504). */
    public function test_pembayaran_belum_dibayar_seminggu_lebih_peringatan(): void
    {
        $received = Carbon::parse('2026-07-07 12:00:00'); // 192 jam lalu
        $this->assertSame(
            'peringatan',
            KeterlambatanClassifier::bucket('pembayaran', $received, null, 'belum_dibayar', $this->now)
        );
    }

    /** Pembayaran belum dibayar, diterima > 3 minggu lalu (≥504 jam) → TERLAMBAT. */
    public function test_pembayaran_lebih_tiga_minggu_terlambat(): void
    {
        $received = Carbon::parse('2026-06-20 12:00:00'); // 25 hari = 600 jam
        $this->assertSame(
            'terlambat',
            KeterlambatanClassifier::bucket('pembayaran', $received, null, 'belum_dibayar', $this->now)
        );
    }

    /** Pembayaran sudah_dibayar TANPA processed_at → pakai now (tidak beku). */
    public function test_pembayaran_sudah_dibayar_tanpa_processed_pakai_now(): void
    {
        $received = Carbon::parse('2026-07-07 12:00:00'); // 192 jam vs now
        $this->assertSame(
            'peringatan',
            KeterlambatanClassifier::bucket('pembayaran', $received, null, 'sudah_dibayar', $this->now)
        );
    }

    /** Role non-pembayaran pakai ambang harian 24/72 jam; aktif < 24 jam → AMAN. */
    public function test_role_lain_aktif_kurang_24_jam_aman(): void
    {
        $received = Carbon::parse('2026-07-15 00:00:00'); // 12 jam vs now
        $this->assertSame(
            'aman',
            KeterlambatanClassifier::bucket('perpajakan', $received, null, null, $this->now)
        );
    }

    /** Role non-pembayaran aktif 24–72 jam → PERINGATAN. */
    public function test_role_lain_aktif_30_jam_peringatan(): void
    {
        $received = Carbon::parse('2026-07-14 06:00:00'); // 30 jam vs now
        $this->assertSame(
            'peringatan',
            KeterlambatanClassifier::bucket('akutansi', $received, null, null, $this->now)
        );
    }

    /** Role non-pembayaran yang sudah diproses → umur beku di processed_at. */
    public function test_role_lain_beku_di_processed_at(): void
    {
        $received  = Carbon::parse('2026-07-10 12:00:00'); // 5 hari lalu
        $processed = Carbon::parse('2026-07-10 20:00:00'); // 8 jam setelah diterima
        $this->assertSame(
            'aman',
            KeterlambatanClassifier::bucket('team_verifikasi', $received, $processed, null, $this->now)
        );
    }
}

<?php

namespace Tests\Feature;

use App\Services\BackfillTanggalBayarService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BackfillTanggalBayarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Koneksi cash_bank_new → sqlite in-memory terpisah (mensimulasikan DB kedua).
        config()->set('database.connections.cash_bank_new', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]);
        DB::purge('cash_bank_new');

        Schema::connection('cash_bank_new')->dropIfExists('bank_keluars');
        Schema::connection('cash_bank_new')->create('bank_keluars', function (Blueprint $t) {
            $t->id('id_bank_keluar');
            $t->unsignedBigInteger('dokumen_id')->nullable();
            $t->string('no_agenda')->nullable();
            $t->string('agenda_tahun')->nullable();
            $t->date('tanggal')->nullable();
            $t->timestamps();
        });
    }

    private function buatDokumen(array $override = []): int
    {
        return DB::table('dokumens')->insertGetId(array_merge([
            'nomor_agenda'  => '0001',
            'bulan'         => 'Januari',
            'tahun'         => 2026,
            'tanggal_masuk' => '2026-01-01 00:00:00',
            'nomor_spp'     => 'SPP-1',
            'tanggal_spp'   => '2026-01-01 00:00:00',
            'uraian_spp'    => 'Uji',
            'nilai_rupiah'  => 1000,
            'kategori'      => 'Umum',
            'jenis_dokumen' => 'Invoice',
            'status'        => 'draft',
            'tanggal_dibayar' => null,
            'created_at'    => now(),
            'updated_at'    => now(),
        ], $override));
    }

    private function buatBankKeluar(array $override = []): void
    {
        DB::connection('cash_bank_new')->table('bank_keluars')->insert(array_merge([
            'dokumen_id'   => null,
            'no_agenda'    => null,
            'agenda_tahun' => null,
            'tanggal'      => null,
            'created_at'   => now(),
            'updated_at'   => now(),
        ], $override));
    }

    private function service(): BackfillTanggalBayarService
    {
        return app(BackfillTanggalBayarService::class);
    }

    public function test_mengisi_tanggal_dibayar_yang_kosong(): void
    {
        $id = $this->buatDokumen(['nomor_agenda' => '0001', 'tanggal_dibayar' => null]);
        $this->buatBankKeluar(['dokumen_id' => $id, 'tanggal' => '2026-03-10']);

        $s = $this->service()->run(false);

        $this->assertSame('2026-03-10', substr((string) DB::table('dokumens')->where('id', $id)->value('tanggal_dibayar'), 0, 10));
        $this->assertSame(1, $s['diisi']);
        $this->assertSame(0, $s['konflik']);
    }

    public function test_memakai_tanggal_terawal(): void
    {
        $id = $this->buatDokumen(['nomor_agenda' => '0002', 'tanggal_dibayar' => null]);
        $this->buatBankKeluar(['dokumen_id' => $id, 'tanggal' => '2026-05-01']);
        $this->buatBankKeluar(['dokumen_id' => $id, 'tanggal' => '2026-03-02']);

        $this->service()->run(false);

        $this->assertSame('2026-03-02', substr((string) DB::table('dokumens')->where('id', $id)->value('tanggal_dibayar'), 0, 10));
    }

    public function test_tidak_menimpa_dan_mencatat_konflik(): void
    {
        $id = $this->buatDokumen(['nomor_agenda' => '0003', 'tanggal_dibayar' => '2026-01-01']);
        $this->buatBankKeluar(['dokumen_id' => $id, 'tanggal' => '2026-03-10']);

        $s = $this->service()->run(false);

        $this->assertSame('2026-01-01', substr((string) DB::table('dokumens')->where('id', $id)->value('tanggal_dibayar'), 0, 10));
        $this->assertSame(1, $s['konflik']);
        $this->assertSame(1, DB::table('sync_logs')->where('direction', 'cb_to_ao')->where('status', 'conflict_resolved')->count());
    }

    public function test_cocok_via_nomor_agenda_jika_dokumen_id_kosong(): void
    {
        $id = $this->buatDokumen(['nomor_agenda' => '0009', 'tanggal_dibayar' => null]);
        $this->buatBankKeluar(['dokumen_id' => null, 'agenda_tahun' => '0009', 'tanggal' => '2026-04-04']);

        $s = $this->service()->run(false);

        $this->assertSame('2026-04-04', substr((string) DB::table('dokumens')->where('id', $id)->value('tanggal_dibayar'), 0, 10));
        $this->assertSame(1, $s['diisi']);
    }

    public function test_dry_run_tidak_menulis(): void
    {
        $id = $this->buatDokumen(['nomor_agenda' => '0005', 'tanggal_dibayar' => null]);
        $this->buatBankKeluar(['dokumen_id' => $id, 'tanggal' => '2026-03-10']);

        $s = $this->service()->run(true);

        $this->assertNull(DB::table('dokumens')->where('id', $id)->value('tanggal_dibayar'));
        $this->assertSame(1, $s['diisi']); // "akan diisi"
        $this->assertSame(0, DB::table('sync_logs')->count());
    }
}

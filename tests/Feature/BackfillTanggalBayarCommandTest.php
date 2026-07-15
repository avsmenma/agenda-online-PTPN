<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BackfillTanggalBayarCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

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

    public function test_command_mengisi_dan_exit_sukses(): void
    {
        $id = $this->buatDokumen(['nomor_agenda' => '0001', 'tanggal_dibayar' => null]);
        DB::connection('cash_bank_new')->table('bank_keluars')->insert([
            'dokumen_id' => $id, 'tanggal' => '2026-03-10',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->artisan('dokumen:backfill-tanggal-bayar')->assertExitCode(0);

        $this->assertSame('2026-03-10', substr((string) DB::table('dokumens')->where('id', $id)->value('tanggal_dibayar'), 0, 10));
    }

    public function test_command_dry_run_tidak_menulis(): void
    {
        $id = $this->buatDokumen(['nomor_agenda' => '0002', 'tanggal_dibayar' => null]);
        DB::connection('cash_bank_new')->table('bank_keluars')->insert([
            'dokumen_id' => $id, 'tanggal' => '2026-03-10',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->artisan('dokumen:backfill-tanggal-bayar --dry-run')->assertExitCode(0);

        $this->assertNull(DB::table('dokumens')->where('id', $id)->value('tanggal_dibayar'));
    }
}

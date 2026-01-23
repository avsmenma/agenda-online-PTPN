<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Dokumen;
use App\Models\DokumenRoleData;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DokumenDummySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data first
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DokumenRoleData::truncate();
        Dokumen::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $now = Carbon::now();

        // Create dummy documents for different roles and delay scenarios
        $dokumens = [];

        // ===== IBUA (Ibu Tara) - 3 dokumen dengan berbagai umur =====
        for ($i = 1; $i <= 3; $i++) {
            $dokumen = Dokumen::create([
                'nomor_agenda' => 'AGD-IBUA-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'nomor_spp' => 'SPP-IBUA-' . $i,
                'tanggal_spp' => $now->copy()->subDays(10 + $i)->format('Y-m-d H:i:s'),
                'uraian_spp' => 'Uraian SPP untuk Ibu Tara ' . $i,
                'nilai_rupiah' => 10000000 * $i,
                'bulan' => $now->format('F'),
                'tahun' => $now->year,
                'tanggal_masuk' => $now->copy()->subDays(10 + $i)->format('Y-m-d H:i:s'),
                'bagian' => 'DPM',
                'nama_pengirim' => 'Pengirim Ibu Tara ' . $i,
                'status' => 'sedang diproses',
                'current_handler' => 'operator',
                'kategori' => 'Kategori ' . $i,
                'jenis_dokumen' => 'Jenis Dokumen ' . $i,
                'created_at' => $now->copy()->subDays(10 + $i),
                'updated_at' => $now->copy()->subDays(10 + $i),
            ]);

            // Create role data for ibuA
            DokumenRoleData::create([
                'dokumen_id' => $dokumen->id,
                'role_code' => 'operator',
                'received_at' => $now->copy()->subDays(10 + $i)->format('Y-m-d H:i:s'),
                'processed_at' => null,
                'deadline_at' => null,
                'created_at' => $now->copy()->subDays(10 + $i),
                'updated_at' => $now->copy()->subDays(10 + $i),
            ]);

            $dokumens[] = $dokumen;
        }

        // ===== IBUB (Team Verifikasi) - 5 dokumen dengan berbagai umur =====
        // 1 dokumen umur 1 hari (hijau)
        // 2 dokumen umur 2 hari (kuning)
        // 2 dokumen umur 3+ hari (merah)
        
        // Dokumen 1 hari (hijau)
        $dokumen1 = Dokumen::create([
            'nomor_agenda' => 'AGD-IBUB-0001',
            'nomor_spp' => 'SPP-IBUB-001',
            'tanggal_spp' => $now->copy()->subDay()->format('Y-m-d H:i:s'),
            'uraian_spp' => 'Uraian SPP Team Verifikasi - 1 Hari',
            'nilai_rupiah' => 5000000,
            'bulan' => $now->format('F'),
            'tahun' => $now->year,
            'tanggal_masuk' => $now->copy()->subDays(5)->format('Y-m-d H:i:s'),
            'bagian' => 'SKH',
            'nama_pengirim' => 'Pengirim Team Verifikasi 1',
            'status' => 'sent_to_ibub',
            'current_handler' => 'team_verifikasi',
            'kategori' => 'Kategori Verifikasi',
            'jenis_dokumen' => 'Jenis Dokumen Verifikasi',
            'created_at' => $now->copy()->subDays(5),
            'updated_at' => $now->copy()->subDays(5),
        ]);

        DokumenRoleData::create([
            'dokumen_id' => $dokumen1->id,
            'role_code' => 'operator',
            'received_at' => $now->copy()->subDays(5)->format('Y-m-d H:i:s'),
            'processed_at' => $now->copy()->subDay()->format('Y-m-d H:i:s'),
            'deadline_at' => null,
            'created_at' => $now->copy()->subDays(5),
            'updated_at' => $now->copy()->subDay(),
        ]);

        DokumenRoleData::create([
            'dokumen_id' => $dokumen1->id,
            'role_code' => 'team_verifikasi',
            'received_at' => $now->copy()->subDay()->format('Y-m-d H:i:s'),
            'processed_at' => null,
            'deadline_at' => null,
            'created_at' => $now->copy()->subDay(),
            'updated_at' => $now->copy()->subDay(),
        ]);

        // Dokumen 2 hari (kuning) - 2 dokumen
        for ($i = 2; $i <= 3; $i++) {
            $dokumen = Dokumen::create([
                'nomor_agenda' => 'AGD-IBUB-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'nomor_spp' => 'SPP-IBUB-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'tanggal_spp' => $now->copy()->subDays(2)->format('Y-m-d H:i:s'),
                'uraian_spp' => 'Uraian SPP Team Verifikasi - 2 Hari ' . $i,
                'nilai_rupiah' => 7500000 * $i,
                'bulan' => $now->format('F'),
                'tahun' => $now->year,
                'tanggal_masuk' => $now->copy()->subDays(5)->format('Y-m-d H:i:s'),
                'bagian' => 'SDM',
                'nama_pengirim' => 'Pengirim Team Verifikasi ' . $i,
                'status' => 'sent_to_ibub',
                'current_handler' => 'team_verifikasi',
                'kategori' => 'Kategori Verifikasi ' . $i,
                'jenis_dokumen' => 'Jenis Dokumen Verifikasi ' . $i,
                'created_at' => $now->copy()->subDays(5),
                'updated_at' => $now->copy()->subDays(5),
            ]);

            DokumenRoleData::create([
                'dokumen_id' => $dokumen->id,
                'role_code' => 'operator',
                'received_at' => $now->copy()->subDays(5)->format('Y-m-d H:i:s'),
                'processed_at' => $now->copy()->subDays(2)->format('Y-m-d H:i:s'),
                'deadline_at' => null,
                'created_at' => $now->copy()->subDays(5),
                'updated_at' => $now->copy()->subDays(2),
            ]);

            DokumenRoleData::create([
                'dokumen_id' => $dokumen->id,
                'role_code' => 'team_verifikasi',
                'received_at' => $now->copy()->subDays(2)->format('Y-m-d H:i:s'),
                'processed_at' => null,
                'deadline_at' => null,
                'created_at' => $now->copy()->subDays(2),
                'updated_at' => $now->copy()->subDays(2),
            ]);
        }

        // Dokumen 3+ hari (merah) - 2 dokumen
        for ($i = 4; $i <= 5; $i++) {
            $daysAgo = $i + 1; // 5 hari dan 6 hari yang lalu
            $dokumen = Dokumen::create([
                'nomor_agenda' => 'AGD-IBUB-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'nomor_spp' => 'SPP-IBUB-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'tanggal_spp' => $now->copy()->subDays($daysAgo)->format('Y-m-d H:i:s'),
                'uraian_spp' => 'Uraian SPP Team Verifikasi - 3+ Hari ' . $i,
                'nilai_rupiah' => 10000000 * $i,
                'bulan' => $now->format('F'),
                'tahun' => $now->year,
                'tanggal_masuk' => $now->copy()->subDays(10)->format('Y-m-d H:i:s'),
                'bagian' => 'TEP',
                'nama_pengirim' => 'Pengirim Team Verifikasi ' . $i,
                'status' => 'sent_to_ibub',
                'current_handler' => 'team_verifikasi',
                'kategori' => 'Kategori Verifikasi ' . $i,
                'jenis_dokumen' => 'Jenis Dokumen Verifikasi ' . $i,
                'created_at' => $now->copy()->subDays(10),
                'updated_at' => $now->copy()->subDays(10),
            ]);

            DokumenRoleData::create([
                'dokumen_id' => $dokumen->id,
                'role_code' => 'operator',
                'received_at' => $now->copy()->subDays(10)->format('Y-m-d H:i:s'),
                'processed_at' => $now->copy()->subDays($daysAgo)->format('Y-m-d H:i:s'),
                'deadline_at' => null,
                'created_at' => $now->copy()->subDays(10),
                'updated_at' => $now->copy()->subDays($daysAgo),
            ]);

            DokumenRoleData::create([
                'dokumen_id' => $dokumen->id,
                'role_code' => 'team_verifikasi',
                'received_at' => $now->copy()->subDays($daysAgo)->format('Y-m-d H:i:s'),
                'processed_at' => null,
                'deadline_at' => null,
                'created_at' => $now->copy()->subDays($daysAgo),
                'updated_at' => $now->copy()->subDays($daysAgo),
            ]);
        }

        // ===== PERPAJAKAN - 4 dokumen dengan berbagai umur =====
        // 1 dokumen umur 1 hari
        // 1 dokumen umur 2 hari
        // 2 dokumen umur 3+ hari
        
        $perpajakanDocs = [
            ['days' => 1, 'nomor' => '001'],
            ['days' => 2, 'nomor' => '002'],
            ['days' => 4, 'nomor' => '003'],
            ['days' => 5, 'nomor' => '004'],
        ];

        foreach ($perpajakanDocs as $index => $doc) {
            $dokumen = Dokumen::create([
                'nomor_agenda' => 'AGD-PAJAK-' . str_pad($doc['nomor'], 4, '0', STR_PAD_LEFT),
                'nomor_spp' => 'SPP-PAJAK-' . $doc['nomor'],
                'tanggal_spp' => $now->copy()->subDays($doc['days'])->format('Y-m-d H:i:s'),
                'uraian_spp' => 'Uraian SPP Team Perpajakan - ' . $doc['days'] . ' Hari',
                'nilai_rupiah' => 15000000 * ($index + 1),
                'bulan' => $now->format('F'),
                'tahun' => $now->year,
                'tanggal_masuk' => $now->copy()->subDays(10)->format('Y-m-d H:i:s'),
                'bagian' => 'KPL',
                'nama_pengirim' => 'Pengirim Team Perpajakan ' . $doc['nomor'],
                'status' => 'sent_to_perpajakan',
                'current_handler' => 'perpajakan',
                'kategori' => 'Kategori Perpajakan ' . $doc['nomor'],
                'jenis_dokumen' => 'Jenis Dokumen Perpajakan ' . $doc['nomor'],
                'created_at' => $now->copy()->subDays(10),
                'updated_at' => $now->copy()->subDays(10),
            ]);

            DokumenRoleData::create([
                'dokumen_id' => $dokumen->id,
                'role_code' => 'operator',
                'received_at' => $now->copy()->subDays(10)->format('Y-m-d H:i:s'),
                'processed_at' => $now->copy()->subDays(8)->format('Y-m-d H:i:s'),
                'deadline_at' => null,
                'created_at' => $now->copy()->subDays(10),
                'updated_at' => $now->copy()->subDays(8),
            ]);

            DokumenRoleData::create([
                'dokumen_id' => $dokumen->id,
                'role_code' => 'team_verifikasi',
                'received_at' => $now->copy()->subDays(8)->format('Y-m-d H:i:s'),
                'processed_at' => $now->copy()->subDays($doc['days'])->format('Y-m-d H:i:s'),
                'deadline_at' => null,
                'created_at' => $now->copy()->subDays(8),
                'updated_at' => $now->copy()->subDays($doc['days']),
            ]);

            DokumenRoleData::create([
                'dokumen_id' => $dokumen->id,
                'role_code' => 'perpajakan',
                'received_at' => $now->copy()->subDays($doc['days'])->format('Y-m-d H:i:s'),
                'processed_at' => null,
                'deadline_at' => null,
                'created_at' => $now->copy()->subDays($doc['days']),
                'updated_at' => $now->copy()->subDays($doc['days']),
            ]);
        }

        // ===== AKUTANSI - 3 dokumen dengan berbagai umur =====
        // 1 dokumen umur 1 hari
        // 1 dokumen umur 2 hari
        // 1 dokumen umur 3+ hari
        
        $akutansiDocs = [
            ['days' => 1, 'nomor' => '001'],
            ['days' => 2, 'nomor' => '002'],
            ['days' => 4, 'nomor' => '003'],
        ];

        foreach ($akutansiDocs as $index => $doc) {
            $dokumen = Dokumen::create([
                'nomor_agenda' => 'AGD-AKUN-' . str_pad($doc['nomor'], 4, '0', STR_PAD_LEFT),
                'nomor_spp' => 'SPP-AKUN-' . $doc['nomor'],
                'tanggal_spp' => $now->copy()->subDays($doc['days'])->format('Y-m-d H:i:s'),
                'uraian_spp' => 'Uraian SPP Team Akutansi - ' . $doc['days'] . ' Hari',
                'nilai_rupiah' => 20000000 * ($index + 1),
                'bulan' => $now->format('F'),
                'tahun' => $now->year,
                'tanggal_masuk' => $now->copy()->subDays(10)->format('Y-m-d H:i:s'),
                'bagian' => 'AKN',
                'nama_pengirim' => 'Pengirim Team Akutansi ' . $doc['nomor'],
                'status' => 'sent_to_akutansi',
                'current_handler' => 'akutansi',
                'kategori' => 'Kategori Akutansi ' . $doc['nomor'],
                'jenis_dokumen' => 'Jenis Dokumen Akutansi ' . $doc['nomor'],
                'created_at' => $now->copy()->subDays(10),
                'updated_at' => $now->copy()->subDays(10),
            ]);

            DokumenRoleData::create([
                'dokumen_id' => $dokumen->id,
                'role_code' => 'operator',
                'received_at' => $now->copy()->subDays(10)->format('Y-m-d H:i:s'),
                'processed_at' => $now->copy()->subDays(8)->format('Y-m-d H:i:s'),
                'deadline_at' => null,
                'created_at' => $now->copy()->subDays(10),
                'updated_at' => $now->copy()->subDays(8),
            ]);

            DokumenRoleData::create([
                'dokumen_id' => $dokumen->id,
                'role_code' => 'team_verifikasi',
                'received_at' => $now->copy()->subDays(8)->format('Y-m-d H:i:s'),
                'processed_at' => $now->copy()->subDays(6)->format('Y-m-d H:i:s'),
                'deadline_at' => null,
                'created_at' => $now->copy()->subDays(8),
                'updated_at' => $now->copy()->subDays(6),
            ]);

            DokumenRoleData::create([
                'dokumen_id' => $dokumen->id,
                'role_code' => 'perpajakan',
                'received_at' => $now->copy()->subDays(6)->format('Y-m-d H:i:s'),
                'processed_at' => $now->copy()->subDays($doc['days'])->format('Y-m-d H:i:s'),
                'deadline_at' => null,
                'created_at' => $now->copy()->subDays(6),
                'updated_at' => $now->copy()->subDays($doc['days']),
            ]);

            DokumenRoleData::create([
                'dokumen_id' => $dokumen->id,
                'role_code' => 'akutansi',
                'received_at' => $now->copy()->subDays($doc['days'])->format('Y-m-d H:i:s'),
                'processed_at' => null,
                'deadline_at' => null,
                'created_at' => $now->copy()->subDays($doc['days']),
                'updated_at' => $now->copy()->subDays($doc['days']),
            ]);
        }

        // ===== PEMBAYARAN - 2 dokumen =====
        for ($i = 1; $i <= 2; $i++) {
            $dokumen = Dokumen::create([
                'nomor_agenda' => 'AGD-BAYAR-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'nomor_spp' => 'SPP-BAYAR-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'tanggal_spp' => $now->copy()->subDays($i)->format('Y-m-d H:i:s'),
                'uraian_spp' => 'Uraian SPP Pembayaran ' . $i,
                'nilai_rupiah' => 25000000 * $i,
                'bulan' => $now->format('F'),
                'tahun' => $now->year,
                'tanggal_masuk' => $now->copy()->subDays(10)->format('Y-m-d H:i:s'),
                'bagian' => 'TAN',
                'nama_pengirim' => 'Pengirim Pembayaran ' . $i,
                'status' => 'sent_to_pembayaran',
                'current_handler' => 'pembayaran',
                'status_pembayaran' => 'siap_dibayar',
                'kategori' => 'Kategori Pembayaran ' . $i,
                'jenis_dokumen' => 'Jenis Dokumen Pembayaran ' . $i,
                'sent_to_pembayaran_at' => $now->copy()->subDays($i)->format('Y-m-d H:i:s'),
                'created_at' => $now->copy()->subDays(10),
                'updated_at' => $now->copy()->subDays($i),
            ]);

            DokumenRoleData::create([
                'dokumen_id' => $dokumen->id,
                'role_code' => 'pembayaran',
                'received_at' => $now->copy()->subDays($i)->format('Y-m-d H:i:s'),
                'processed_at' => null,
                'deadline_at' => null,
                'created_at' => $now->copy()->subDays($i),
                'updated_at' => $now->copy()->subDays($i),
            ]);
        }

        $this->command->info('Dummy dokumen berhasil dibuat!');
        $this->command->info('Total dokumen: ' . Dokumen::count());
        $this->command->info('Total role data: ' . DokumenRoleData::count());
    }
}


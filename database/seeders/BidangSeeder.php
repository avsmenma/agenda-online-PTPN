<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BidangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bidangs = [
            ['kode_bidang' => 'DPM', 'nama_bidang' => 'Divisi Produksi dan Manufaktur', 'deskripsi' => 'Bidang produksi dan manufaktur'],
            ['kode_bidang' => 'SKH', 'nama_bidang' => 'Sub Kontrak Hutan', 'deskripsi' => 'Bidang sub kontrak hutan'],
            ['kode_bidang' => 'SDM', 'nama_bidang' => 'Sumber Daya Manusia', 'deskripsi' => 'Bidang sumber daya manusia'],
            ['kode_bidang' => 'TEP', 'nama_bidang' => 'Teknik dan Perencanaan', 'deskripsi' => 'Bidang teknik dan perencanaan'],
            ['kode_bidang' => 'KPL', 'nama_bidang' => 'Keuangan dan Pelaporan', 'deskripsi' => 'Bidang keuangan dan pelaporan'],
            ['kode_bidang' => 'AKN', 'nama_bidang' => 'Akuntansi', 'deskripsi' => 'Bidang akuntansi'],
            ['kode_bidang' => 'TAN', 'nama_bidang' => 'Tanaman dan Perkebunan', 'deskripsi' => 'Bidang tanaman dan perkebunan'],
        ];

        foreach ($bidangs as $bidang) {
            \App\Models\Bidang::updateOrCreate(
                ['kode_bidang' => $bidang['kode_bidang']],
                $bidang
            );
        }
    }
}

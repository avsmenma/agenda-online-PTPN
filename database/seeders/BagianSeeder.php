<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bagian;

class BagianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bagians = [
            ['kode' => 'DPM', 'nama' => 'DPM', 'deskripsi' => 'DPM', 'is_active' => true],
            ['kode' => 'SKH', 'nama' => 'SKH', 'deskripsi' => 'SKH', 'is_active' => true],
            ['kode' => 'SDM', 'nama' => 'SDM', 'deskripsi' => 'SDM', 'is_active' => true],
            ['kode' => 'TEP', 'nama' => 'TEP', 'deskripsi' => 'TEP', 'is_active' => true],
            ['kode' => 'KPL', 'nama' => 'KPL', 'deskripsi' => 'KPL', 'is_active' => true],
            ['kode' => 'AKN', 'nama' => 'AKN', 'deskripsi' => 'AKN', 'is_active' => true],
            ['kode' => 'TAN', 'nama' => 'TAN', 'deskripsi' => 'TAN', 'is_active' => true],
            ['kode' => 'PMO', 'nama' => 'PMO', 'deskripsi' => 'PMO', 'is_active' => true],
            ['kode' => 'PTI', 'nama' => 'PTI', 'deskripsi' => 'PTI', 'is_active' => true],
        ];

        foreach ($bagians as $bagian) {
            Bagian::updateOrCreate(
                ['kode' => $bagian['kode']],
                $bagian
            );
        }
    }
}


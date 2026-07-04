<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Sumber kebenaran idempoten untuk tabel `roles`.
 *
 * Aman dijalankan di SEMUA environment (termasuk produksi) karena hanya
 * updateOrInsert — tidak pernah menghapus atau menimpa data lain. Berguna
 * untuk memulihkan tabel roles yang drift, atau menyiapkan DB baru.
 *
 * Termasuk 'system': aktor internal yang dipakai auto-forward dan dirujuk
 * oleh dokumen_role_data.role_code (FK ke roles.code). Lihat migrasi
 * 2026_07_04_000001_add_system_role_to_roles_table.
 *
 * Cara pakai di produksi (tanpa menjalankan seeder lain):
 *   php artisan db:seed --class=RoleSeeder --force
 */
class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['code' => 'operator',        'name' => 'Operator',        'sequence' => 1],
            ['code' => 'team_verifikasi', 'name' => 'Team Verifikasi', 'sequence' => 2],
            ['code' => 'perpajakan',      'name' => 'Perpajakan',      'sequence' => 3],
            ['code' => 'akutansi',        'name' => 'Akutansi',        'sequence' => 4],
            ['code' => 'pembayaran',      'name' => 'Pembayaran',      'sequence' => 5],
            ['code' => 'system',          'name' => 'System',          'sequence' => 99],
        ];

        foreach ($roles as $role) {
            $exists = DB::table('roles')->where('code', $role['code'])->exists();

            DB::table('roles')->updateOrInsert(
                ['code' => $role['code']],
                array_merge(
                    ['name' => $role['name'], 'sequence' => $role['sequence'], 'updated_at' => now()],
                    $exists ? [] : ['created_at' => now()]
                )
            );
        }
    }
}

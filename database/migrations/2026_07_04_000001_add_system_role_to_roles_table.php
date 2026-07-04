<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan role 'system' ke tabel `roles`.
 *
 * ALASAN: AutoForwardDokumenService meneruskan dokumen sebagai aktor internal
 * 'system'. Dokumen::sendToRoleInbox() mencatat display_status pengirim ke
 * `dokumen_role_data` dengan role_code = 'system'. Kolom itu ber-FK ke
 * roles.code, sedangkan TIDAK ADA migrasi/seeder sebelumnya yang membuat role
 * 'system'. Akibatnya di DB yang dibangun dari nol (fresh install / CI), auto-
 * forward gagal "FOREIGN KEY constraint failed". Migrasi ini menutup gap itu
 * secara reproducible tanpa mengubah migrasi lama.
 *
 * Idempoten: updateOrInsert; created_at hanya diisi saat baris belum ada.
 */
return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('roles')) {
            return;
        }

        $exists = DB::table('roles')->where('code', 'system')->exists();

        DB::table('roles')->updateOrInsert(
            ['code' => 'system'],
            array_merge(
                ['name' => 'System', 'sequence' => 99, 'updated_at' => now()],
                $exists ? [] : ['created_at' => now()]
            )
        );
    }

    public function down(): void
    {
        if (! Schema::hasTable('roles')) {
            return;
        }

        // Bisa gagal FK bila masih ada dokumen_role_data yang mereferensikan
        // 'system'. Rollback bersifat best-effort (hanya relevan di dev).
        try {
            DB::table('roles')->where('code', 'system')->delete();
        } catch (\Throwable $e) {
            // Biarkan: role 'system' masih dirujuk data workflow.
        }
    }
};

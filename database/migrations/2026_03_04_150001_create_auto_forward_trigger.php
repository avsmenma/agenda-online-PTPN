<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates MySQL trigger that detects when an external project
     * updates status_pembayaran = 'sudah_dibayar' via raw DB query.
     * The trigger inserts a record into dokumen_auto_forward_queue
     * which is then processed by the Laravel Scheduler every minute.
     *
     * NOTE: If trigger creation fails (e.g. MySQL SUPER privilege restriction
     * with binary logging enabled), the migration will NOT fail — instead it
     * logs a warning. The Scheduler Command 'dokumen:process-auto-forward'
     * has a built-in polling fallback that works WITHOUT the trigger.
     *
     * To enable triggers on MySQL with binary logging, run as MySQL root:
     *   SET GLOBAL log_bin_trust_function_creators = 1;
     * Or add to /etc/mysql/mysql.conf.d/mysqld.cnf:
     *   log_bin_trust_function_creators = 1
     */
    public function up(): void
    {
        // Drop existing trigger if exists (safe re-run)
        try {
            DB::unprepared('DROP TRIGGER IF EXISTS after_dokumen_status_sudah_dibayar');
        } catch (\Throwable $e) {
            Log::warning('AutoForward trigger: Could not drop existing trigger: ' . $e->getMessage());
        }

        try {
            DB::unprepared("
CREATE TRIGGER after_dokumen_status_sudah_dibayar
AFTER UPDATE ON dokumens
FOR EACH ROW
BEGIN
    -- Only fire when status_pembayaran changes FROM something else TO 'sudah_dibayar'
    IF NEW.status_pembayaran = 'sudah_dibayar'
       AND (OLD.status_pembayaran IS NULL OR OLD.status_pembayaran != 'sudah_dibayar')
    THEN
        -- Insert into queue or reset to pending if already exists (re-trigger scenario)
        INSERT INTO dokumen_auto_forward_queue
            (dokumen_id, triggered_at, status, created_at, updated_at)
        VALUES
            (NEW.id, NOW(), 'pending', NOW(), NOW())
        ON DUPLICATE KEY UPDATE
            triggered_at = NOW(),
            status = 'pending',
            processed_at = NULL,
            error_message = NULL,
            updated_at = NOW();
    END IF;
END
            ");

            echo "  MySQL trigger 'after_dokumen_status_sudah_dibayar' berhasil dibuat.\n";

        } catch (\Throwable $e) {
            // Trigger gagal dibuat (kemungkinan karena MySQL SUPER privilege restriction).
            // Ini BUKAN error fatal — Scheduler Command sudah punya polling fallback
            // yang berjalan setiap menit tanpa bergantung pada trigger.
            $hint = "Untuk mengaktifkan trigger, jalankan di MySQL sebagai root:\n"
                  . "  SET GLOBAL log_bin_trust_function_creators = 1;\n"
                  . "Lalu: php artisan migrate:rollback --step=1 && php artisan migrate";

            echo "\n  PERINGATAN: MySQL trigger tidak bisa dibuat (bukan error fatal).\n";
            echo "  Alasan: " . $e->getMessage() . "\n";
            echo "  {$hint}\n";
            echo "  Scheduler polling fallback tetap aktif dan akan bekerja setiap menit.\n\n";

            Log::warning('AutoForward trigger creation failed — polling fallback aktif sebagai pengganti', [
                'error' => $e->getMessage(),
                'hint'  => $hint,
            ]);

            // TIDAK throw — biarkan migration selesai dengan sukses
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            DB::unprepared('DROP TRIGGER IF EXISTS after_dokumen_status_sudah_dibayar');
        } catch (\Throwable $e) {
            Log::warning('AutoForward trigger: Could not drop trigger on rollback: ' . $e->getMessage());
        }
    }
};

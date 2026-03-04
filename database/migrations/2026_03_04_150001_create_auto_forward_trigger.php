<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

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
     * NOTE: MySQL triggers are the ONLY reliable way to detect raw
     * DB::table()->update() changes from external projects, since
     * Eloquent Observers do NOT fire for raw queries.
     */
    public function up(): void
    {
        // Drop existing trigger if exists (safe re-run)
        DB::unprepared('DROP TRIGGER IF EXISTS after_dokumen_status_sudah_dibayar');

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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS after_dokumen_status_sudah_dibayar');
    }
};

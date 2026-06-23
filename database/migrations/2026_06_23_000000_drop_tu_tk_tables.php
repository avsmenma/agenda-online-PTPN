<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Eliminasi total fitur TU/TK.
 *
 * Membuang seluruh tabel milik fitur TU/TK yang sudah tidak dipakai:
 *  - tu_tk_2023, tu_tk_pupuk_2023, tu_tk_vd_2023, tu_tk_tan_2023, tu_tk_dokumens
 *  - payment_logs, document_position_trackings (eksklusif melayani TU/TK)
 *
 * PERHATIAN: Tabel kemungkinan berisi data. BACKUP DULU sebelum menjalankan
 * migrasi ini di server. Operasi drop bersifat IRREVERSIBLE (down() no-op).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('payment_logs');
        Schema::dropIfExists('document_position_trackings');
        Schema::dropIfExists('tu_tk_dokumens');
        Schema::dropIfExists('tu_tk_tan_2023');
        Schema::dropIfExists('tu_tk_vd_2023');
        Schema::dropIfExists('tu_tk_pupuk_2023');
        Schema::dropIfExists('tu_tk_2023');
    }

    public function down(): void
    {
        // Sengaja dibiarkan kosong: eliminasi TU/TK bersifat permanen.
        // Struktur & data tabel tidak dipulihkan oleh rollback.
    }
};

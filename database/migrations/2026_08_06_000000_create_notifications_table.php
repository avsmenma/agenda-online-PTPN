<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel `notifications` bawaan Laravel (notifikasi in-app / database channel).
 *
 * Tabel ini SUDAH ADA di produksi tetapi TIDAK PERNAH punya migrasi di repo —
 * drift skema yang baru ketahuan 2026-08-06. Akibatnya nyata: App\Notifications\
 * DokumenDikembalikanNotification selalu melempar "no such table: notifications"
 * di lingkungan mana pun selain produksi, dan karena pemanggilnya dibungkus
 * try/catch, kegagalan itu tertelan tanpa suara.
 *
 * Definisi di bawah dicocokkan dengan SHOW CREATE TABLE produksi (id char(36)
 * primary, morphs notifiable, data text, read_at nullable, timestamps) sehingga
 * tidak ada perbedaan bentuk antara produksi dan lingkungan baru.
 *
 * Idempoten (aturan 6 CLAUDE.md): dijaga hasTable, jadi di produksi ini no-op.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('notifications')) {
            return;
        }

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // SENGAJA tidak menurunkan apa pun. Tabel ini sudah berisi/akan berisi
        // notifikasi user sungguhan di produksi; rollback otomatis yang menghapusnya
        // adalah kehilangan data, bukan pembatalan perubahan.
    }
};

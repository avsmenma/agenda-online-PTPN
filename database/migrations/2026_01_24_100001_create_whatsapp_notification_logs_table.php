<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('whatsapp_notification_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dokumen_id');
            $table->string('role_code', 50);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('phone_number', 20);
            $table->string('message_type', 20); // 'warning', 'danger', 'overdue'
            $table->text('message')->nullable();
            $table->string('status', 20)->default('pending'); // 'pending', 'success', 'failed'
            $table->text('response')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            // Indexes for faster lookups
            $table->index('dokumen_id');
            $table->index('role_code');
            $table->index('user_id');
            $table->index(['dokumen_id', 'role_code', 'message_type'], 'wa_notif_doc_role_type_idx');
            $table->index('sent_at');

            // Foreign keys
            $table->foreign('dokumen_id')->references('id')->on('dokumens')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_notification_logs');
    }
};

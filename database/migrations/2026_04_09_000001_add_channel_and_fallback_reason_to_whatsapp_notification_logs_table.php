<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('whatsapp_notification_logs', function (Blueprint $table) {
            $table->enum('channel', ['whatsapp', 'email'])->default('whatsapp')->after('status');
            $table->text('fallback_reason')->nullable()->after('response');
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_notification_logs', function (Blueprint $table) {
            $table->dropColumn(['channel', 'fallback_reason']);
        });
    }
};


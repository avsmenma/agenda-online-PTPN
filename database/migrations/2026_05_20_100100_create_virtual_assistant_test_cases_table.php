<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('virtual_assistant_test_cases', function (Blueprint $table) {
            $table->id();
            $table->string('name', 160);
            $table->text('question');
            $table->string('expected_intent', 100)->nullable();
            $table->json('expected_params')->nullable();
            $table->string('expected_result_type', 60)->default('any');
            $table->boolean('is_active')->default(true)->index();
            $table->string('last_status', 30)->nullable()->index();
            $table->text('last_notes')->nullable();
            $table->timestamp('last_run_at')->nullable();
            $table->timestamps();
        });

        DB::table('virtual_assistant_test_cases')->insert([
            [
                'name' => 'Total dokumen',
                'question' => 'berapa total dokumen?',
                'expected_intent' => 'document_summary',
                'expected_params' => json_encode([]),
                'expected_result_type' => 'summary',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Dokumen siap dibayar',
                'question' => 'dokumen siap dibayar apa saja?',
                'expected_intent' => 'ready_to_pay_documents',
                'expected_params' => json_encode(['payment_statuses' => ['siap_dibayar']]),
                'expected_result_type' => 'list',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Belum dibayar di atas 100 juta',
                'question' => 'dokumen belum dibayar di atas 100 juta',
                'expected_intent' => 'unpaid_documents',
                'expected_params' => json_encode(['payment_statuses' => ['belum_dibayar'], 'amount_min' => 100000000]),
                'expected_result_type' => 'list',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Dokumen masuk tanggal spesifik',
                'question' => 'dokumen masuk tanggal 5 Mei 2026',
                'expected_intent' => 'documents_by_date',
                'expected_params' => json_encode(['date_range.type' => 'date']),
                'expected_result_type' => 'list_or_empty',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Top bagian jumlah dokumen',
                'question' => 'bagian mana paling banyak mengirim dokumen?',
                'expected_intent' => 'top_departments_by_count',
                'expected_params' => json_encode([]),
                'expected_result_type' => 'list',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Total nilai sudah dibayar bulan ini',
                'question' => 'berapa total nilai dokumen sudah dibayar bulan ini?',
                'expected_intent' => 'payment_summary',
                'expected_params' => json_encode(['payment_statuses' => ['sudah_dibayar'], 'date_range.type' => 'month']),
                'expected_result_type' => 'summary',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Dokumen SKH belum dibayar',
                'question' => 'dokumen SKH yang belum dibayar',
                'expected_intent' => 'unpaid_documents',
                'expected_params' => json_encode(['payment_statuses' => ['belum_dibayar'], 'department' => 'SKH']),
                'expected_result_type' => 'list',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Dokumen paling lama diproses',
                'question' => 'dokumen apa yang paling lama diproses?',
                'expected_intent' => 'oldest_documents',
                'expected_params' => json_encode([]),
                'expected_result_type' => 'list',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('virtual_assistant_test_cases');
    }
};

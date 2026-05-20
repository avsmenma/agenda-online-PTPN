<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('virtual_assistant_test_cases')) {
            return;
        }

        $now = now();
        $cases = [
            [
                'name' => 'Posisi dokumen tanpa kutip',
                'question' => 'dokumen 3060_2026 ada di mana',
                'expected_intent' => 'specific_document_position',
                'expected_params' => ['keyword' => '3060_2026'],
                'expected_result_type' => 'list_or_empty',
            ],
            [
                'name' => 'Status pembayaran dokumen spesifik',
                'question' => 'status pembayaran dokumen 3060_2026',
                'expected_intent' => 'specific_document_payment_status',
                'expected_params' => ['keyword' => '3060_2026'],
                'expected_result_type' => 'list_or_empty',
            ],
            [
                'name' => 'Top bagian bulan ini',
                'question' => 'bagian mana paling banyak mengirim dokumen bulan ini?',
                'expected_intent' => 'top_departments_by_count',
                'expected_params' => ['date_range.type' => 'month'],
                'expected_result_type' => 'list_or_empty',
            ],
            [
                'name' => 'Top bagian hari ini natural',
                'question' => 'dokumen dari bagian apa yang paling banyak masuk hari ini?',
                'expected_intent' => 'top_departments_by_count',
                'expected_params' => ['date_range.type' => 'date'],
                'expected_result_type' => 'list_or_empty',
            ],
            [
                'name' => 'Total nilai dokumen sudah dibayar',
                'question' => 'berapa total nilai dokumen sudah dibayar?',
                'expected_intent' => 'payment_summary',
                'expected_params' => ['payment_statuses' => ['sudah_dibayar']],
                'expected_result_type' => 'summary',
            ],
        ];

        foreach ($cases as $case) {
            DB::table('virtual_assistant_test_cases')->updateOrInsert(
                ['question' => $case['question']],
                [
                    'name' => $case['name'],
                    'expected_intent' => $case['expected_intent'],
                    'expected_params' => json_encode($case['expected_params']),
                    'expected_result_type' => $case['expected_result_type'],
                    'is_active' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('virtual_assistant_test_cases')) {
            return;
        }

        DB::table('virtual_assistant_test_cases')
            ->whereIn('question', [
                'dokumen 3060_2026 ada di mana',
                'status pembayaran dokumen 3060_2026',
                'bagian mana paling banyak mengirim dokumen bulan ini?',
                'dokumen dari bagian apa yang paling banyak masuk hari ini?',
                'berapa total nilai dokumen sudah dibayar?',
            ])
            ->delete();
    }
};

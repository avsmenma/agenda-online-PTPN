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
                'name' => 'Posisi dokumen berdasarkan nilai titik',
                'question' => 'dimn posisi dokumen dengan nilai 14.625.601',
                'expected_intent' => 'documents_by_exact_amount',
                'expected_params' => ['amount_exact' => 14625601],
                'expected_result_type' => 'list_or_empty',
            ],
            [
                'name' => 'Posisi dokumen berdasarkan nilai tanpa titik',
                'question' => 'dimn posisi dokumen dengan nilai14625601',
                'expected_intent' => 'documents_by_exact_amount',
                'expected_params' => ['amount_exact' => 14625601],
                'expected_result_type' => 'list_or_empty',
            ],
            [
                'name' => 'Dokumen berdasarkan nilai persis',
                'question' => 'tampilkan dokumen dengan nominal 14.625.601',
                'expected_intent' => 'documents_by_exact_amount',
                'expected_params' => ['amount_exact' => 14625601],
                'expected_result_type' => 'list_or_empty',
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
                'dimn posisi dokumen dengan nilai 14.625.601',
                'dimn posisi dokumen dengan nilai14625601',
                'tampilkan dokumen dengan nominal 14.625.601',
            ])
            ->delete();
    }
};

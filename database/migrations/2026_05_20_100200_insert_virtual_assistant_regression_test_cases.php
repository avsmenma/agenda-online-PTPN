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
                'name' => 'Rata-rata durasi pembayaran',
                'question' => 'berapa lama rata rata dokumen selesai oleh tim pembayaran',
                'expected_intent' => 'role_average_duration',
                'expected_params' => ['handler' => 'pembayaran'],
                'expected_result_type' => 'summary',
            ],
            [
                'name' => 'Rata-rata durasi verifikasi',
                'question' => 'berapa lama rata rata dokumen selesai oleh tim verifikasi',
                'expected_intent' => 'role_average_duration',
                'expected_params' => ['handler' => 'team_verifikasi'],
                'expected_result_type' => 'summary',
            ],
            [
                'name' => 'Posisi dokumen nomor agenda',
                'question' => 'sudah di posisi mana dokumen dengan nomor agenda "3060_2026"',
                'expected_intent' => 'specific_document_position',
                'expected_params' => ['keyword' => '3060_2026'],
                'expected_result_type' => 'list_or_empty',
            ],
            [
                'name' => 'Cari nomor agenda dari uraian',
                'question' => 'berapa nomor agenda dari dokumen dengan uraian "Pembayaran honorarium konsultan pertanian periode Q4 2024"',
                'expected_intent' => 'documents_by_keyword',
                'expected_params' => ['keyword' => 'Pembayaran honorarium konsultan pertanian periode Q4 2024'],
                'expected_result_type' => 'list_or_empty',
            ],
            [
                'name' => 'Umur dokumen spesifik',
                'question' => 'berapa umur dokumen dengan nomor agenda 3075_2026?',
                'expected_intent' => 'specific_document_age',
                'expected_params' => ['keyword' => '3075_2026'],
                'expected_result_type' => 'list_or_empty',
            ],
            [
                'name' => 'Dokumen umur satu tahun',
                'question' => 'apakah ada dokumen dengan umur 1 tahun?',
                'expected_intent' => 'documents_by_age',
                'expected_params' => ['age_days_min' => 365],
                'expected_result_type' => 'list_or_empty',
            ],
            [
                'name' => 'Top bagian masuk hari ini',
                'question' => 'dokumen dari bagian apa yang paling banyak masuk hari ini?',
                'expected_intent' => 'top_departments_by_count',
                'expected_params' => ['date_range.type' => 'date'],
                'expected_result_type' => 'list_or_empty',
            ],
            [
                'name' => 'Tanggal dokumen masuk',
                'question' => 'jadi, tanggal berapa saja dokumen yang masuk?',
                'expected_intent' => 'document_entry_dates_summary',
                'expected_params' => [],
                'expected_result_type' => 'list',
            ],
            [
                'name' => 'Dokumen akhir-akhir ini',
                'question' => 'sebutkan dokumen yang akhir-akhir ini masuk',
                'expected_intent' => 'recent_documents',
                'expected_params' => [],
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
                'berapa lama rata rata dokumen selesai oleh tim pembayaran',
                'berapa lama rata rata dokumen selesai oleh tim verifikasi',
                'sudah di posisi mana dokumen dengan nomor agenda "3060_2026"',
                'berapa nomor agenda dari dokumen dengan uraian "Pembayaran honorarium konsultan pertanian periode Q4 2024"',
                'berapa umur dokumen dengan nomor agenda 3075_2026?',
                'apakah ada dokumen dengan umur 1 tahun?',
                'dokumen dari bagian apa yang paling banyak masuk hari ini?',
                'jadi, tanggal berapa saja dokumen yang masuk?',
                'sebutkan dokumen yang akhir-akhir ini masuk',
            ])
            ->delete();
    }
};

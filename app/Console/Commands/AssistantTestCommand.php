<?php

namespace App\Console\Commands;

use App\Models\VirtualAssistantTestCase;
use App\Services\VirtualAssistantQueryService;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;

class AssistantTestCommand extends Command
{
    protected $signature = 'assistant:test {--json : Tampilkan laporan dalam JSON}';

    protected $description = 'Menjalankan batch test intent dan parameter Asisten Virtual secara read-only.';

    public function handle(VirtualAssistantQueryService $assistant): int
    {
        if (!Schema::hasTable('virtual_assistant_test_cases')) {
            $this->error('Tabel virtual_assistant_test_cases belum tersedia. Jalankan php artisan migrate terlebih dahulu.');
            return self::FAILURE;
        }

        $cases = VirtualAssistantTestCase::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        if ($cases->isEmpty()) {
            $this->warn('Belum ada test case aktif untuk Asisten Virtual.');
            return self::SUCCESS;
        }

        $rows = [];
        $passed = 0;

        foreach ($cases as $case) {
            $result = $assistant->answer($case->question, []);
            $evaluation = $this->evaluateCase($case, $result);

            $case->update([
                'last_status' => $evaluation['passed'] ? 'pass' : 'fail',
                'last_notes' => $evaluation['notes'],
                'last_run_at' => now(),
            ]);

            if ($evaluation['passed']) {
                $passed++;
            }

            $rows[] = [
                'id' => $case->id,
                'name' => $case->name,
                'question' => $case->question,
                'expected_intent' => $case->expected_intent,
                'actual_intent' => $result['intent'] ?? null,
                'status' => $evaluation['passed'] ? 'PASS' : 'FAIL',
                'notes' => $evaluation['notes'],
            ];
        }

        $report = [
            'total' => $cases->count(),
            'passed' => $passed,
            'failed' => $cases->count() - $passed,
            'rows' => $rows,
            'recommendations' => $this->recommendations($rows),
        ];

        if ($this->option('json')) {
            $this->line(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            return $report['failed'] === 0 ? self::SUCCESS : self::FAILURE;
        }

        $this->info("Assistant test selesai: {$report['passed']} pass, {$report['failed']} fail dari {$report['total']} test case.");
        $this->table(['ID', 'Nama', 'Expected', 'Actual', 'Status', 'Catatan'], array_map(fn ($row) => [
            $row['id'],
            $row['name'],
            $row['expected_intent'] ?: '-',
            $row['actual_intent'] ?: '-',
            $row['status'],
            $row['notes'],
        ], $rows));

        if ($report['failed'] > 0) {
            $this->newLine();
            $this->warn('Rekomendasi perbaikan:');
            foreach ($report['recommendations'] as $recommendation) {
                $this->line('- ' . $recommendation);
            }
        }

        return $report['failed'] === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function evaluateCase(VirtualAssistantTestCase $case, array $result): array
    {
        $notes = [];
        $passed = true;

        if ($case->expected_intent && ($result['intent'] ?? null) !== $case->expected_intent) {
            $passed = false;
            $notes[] = "Intent aktual '{$result['intent']}' bukan '{$case->expected_intent}'";
        }

        foreach (($case->expected_params ?? []) as $key => $expectedValue) {
            $actualValue = data_get($result, "meta.params.{$key}");
            if (!$this->valuesMatch($expectedValue, $actualValue)) {
                $passed = false;
                $notes[] = "Param {$key} tidak cocok";
            }
        }

        $typeCheck = $this->checkResultType($case->expected_result_type, $result);
        if (!$typeCheck['passed']) {
            $passed = false;
            $notes[] = $typeCheck['note'];
        }

        $qualityCheck = $this->checkAnswerQuality((string) ($result['answer'] ?? ''));
        if (!$qualityCheck['passed']) {
            $passed = false;
            $notes[] = $qualityCheck['note'];
        }

        return [
            'passed' => $passed,
            'notes' => $notes ? implode('; ', $notes) : 'Sesuai ekspektasi',
        ];
    }

    private function valuesMatch(mixed $expected, mixed $actual): bool
    {
        if (is_array($expected) || is_array($actual)) {
            return json_encode($expected) === json_encode($actual);
        }

        if (is_numeric($expected) && is_numeric($actual)) {
            return (float) $expected === (float) $actual;
        }

        return (string) $expected === (string) $actual;
    }

    private function checkResultType(string $expectedType, array $result): array
    {
        $data = $result['data'] ?? null;
        $service = data_get($result, 'meta.service');

        return match ($expectedType) {
            'summary' => [
                'passed' => is_array($data) && Arr::isAssoc($data),
                'note' => 'Hasil bukan ringkasan/object',
            ],
            'list' => [
                'passed' => is_array($data),
                'note' => 'Hasil bukan daftar',
            ],
            'list_or_empty' => [
                'passed' => is_array($data),
                'note' => 'Hasil bukan daftar atau kosong',
            ],
            'no_data' => [
                'passed' => $service === 'empty_result',
                'note' => 'Hasil bukan empty_result',
            ],
            'clarification' => [
                'passed' => ($result['intent'] ?? null) === 'clarification',
                'note' => 'Hasil bukan klarifikasi',
            ],
            default => [
                'passed' => true,
                'note' => 'Tidak ada validasi tipe khusus',
            ],
        };
    }

    private function checkAnswerQuality(string $answer): array
    {
        $forbiddenPhrases = [
            'pengurus Pembayaran dengan status Pembayaran dan status pembayaran',
            'pengurus Perpajakan dengan status Perpajakan dan status pembayaran',
            'pengurus Akuntansi dengan status Akuntansi dan status pembayaran',
            'data bagian yang bisa diringkas dengan tanggal masuk',
            'intent',
            'query',
            'database',
            'JSON',
        ];

        foreach ($forbiddenPhrases as $phrase) {
            if (stripos($answer, $phrase) !== false) {
                return [
                    'passed' => false,
                    'note' => "Jawaban masih mengandung frasa teknis/kaku: {$phrase}",
                ];
            }
        }

        return [
            'passed' => true,
            'note' => 'Bahasa jawaban aman',
        ];
    }

    private function recommendations(array $rows): array
    {
        $failed = array_values(array_filter($rows, fn ($row) => $row['status'] === 'FAIL'));
        if ($failed === []) {
            return ['Semua test case aktif lolos. Pertahankan test ini sebagai regression suite.'];
        }

        $recommendations = [];
        foreach ($failed as $row) {
            if (($row['actual_intent'] ?? null) !== ($row['expected_intent'] ?? null)) {
                $recommendations[] = "Periksa mapping sinonim/intent untuk pertanyaan '{$row['question']}'.";
            } else {
                $recommendations[] = "Periksa parser parameter atau query service untuk intent '{$row['actual_intent']}'.";
            }
        }

        return array_values(array_unique($recommendations));
    }
}

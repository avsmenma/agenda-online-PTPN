<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class VirtualAssistantService
{
    public function __construct(
        private readonly VirtualAssistantQueryService $queryService,
        private readonly VirtualAssistantAiProvider $aiProvider,
    ) {
    }

    public function respond(string $message): array
    {
        $result = $this->queryService->answer($message);
        $configuredProvider = (string) config('asisten_virtual.provider', 'local');
        $aiAnswer = $this->shouldUseAiRefinement($result)
            ? $this->aiProvider->refineAnswer($message, $result)
            : null;

        if ($aiAnswer) {
            $result['answer'] = trim($aiAnswer);
            $result['meta']['ai_provider'] = $configuredProvider;
            $result['meta']['ai_called'] = true;
        } else {
            $result['meta']['ai_provider'] = 'local';
            $result['meta']['ai_called'] = false;
            $result['meta']['ai_skipped_reason'] = $this->shouldUseAiRefinement($result)
                ? null
                : 'authoritative_structured_data';
        }

        Log::info('Virtual Assistant response completed', [
            'question' => (string) str($message)->limit(300),
            'intent' => $result['intent'] ?? null,
            'configured_provider' => $configuredProvider,
            'answer_provider' => $result['meta']['ai_provider'] ?? null,
            'ai_called' => $result['meta']['ai_called'] ?? false,
            'result_count' => is_array($result['data'] ?? null) ? count($result['data']) : 0,
            'total' => data_get($result, 'meta.total'),
        ]);

        return $result;
    }

    private function shouldUseAiRefinement(array $result): bool
    {
        $intent = (string) ($result['intent'] ?? '');

        if (in_array($intent, ['clarification', 'greeting'], true)) {
            return true;
        }

        $data = $result['data'] ?? [];
        $firstRow = is_array($data) ? reset($data) : null;

        // Keep database-backed lists and summaries authoritative so AI cannot
        // accidentally change counts, totals, or which rows are shown.
        if (is_array($firstRow) && (array_key_exists('nomor_agenda', $firstRow) || array_key_exists('total_nilai', $firstRow))) {
            return false;
        }

        return false;
    }
}

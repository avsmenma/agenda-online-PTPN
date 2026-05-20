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
        $aiAnswer = $this->aiProvider->refineAnswer($message, $result);

        if ($aiAnswer) {
            $result['answer'] = trim($aiAnswer);
            $result['meta']['ai_provider'] = $configuredProvider;
            $result['meta']['ai_called'] = true;
        } else {
            $result['meta']['ai_provider'] = 'local';
            $result['meta']['ai_called'] = false;
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
}

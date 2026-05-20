<?php

namespace App\Services;

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
        $aiAnswer = $this->aiProvider->refineAnswer($message, $result);

        if ($aiAnswer) {
            $result['answer'] = trim($aiAnswer);
            $result['meta']['ai_provider'] = config('asisten_virtual.provider');
        } else {
            $result['meta']['ai_provider'] = 'local';
        }

        return $result;
    }
}

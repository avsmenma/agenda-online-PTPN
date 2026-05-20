<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VirtualAssistantAiProvider
{
    public function refineAnswer(string $question, array $queryResult): ?string
    {
        return match (config('asisten_virtual.provider')) {
            'openai' => $this->refineWithOpenAi($question, $queryResult),
            'gemini' => $this->refineWithGemini($question, $queryResult),
            default => null,
        };
    }

    private function refineWithOpenAi(string $question, array $queryResult): ?string
    {
        $apiKey = (string) config('asisten_virtual.openai.api_key');
        if ($apiKey === '') {
            return null;
        }

        try {
            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->timeout((int) config('asisten_virtual.openai.timeout', 12))
                ->post(config('asisten_virtual.openai.endpoint'), [
                    'model' => config('asisten_virtual.openai.model'),
                    'temperature' => 0.2,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $this->systemInstruction(),
                        ],
                        [
                            'role' => 'user',
                            'content' => $this->buildSafePrompt($question, $queryResult),
                        ],
                    ],
                ]);

            if (!$response->successful()) {
                Log::warning('Virtual Assistant AI provider failed', [
                    'status' => $response->status(),
                    'body' => str($response->body())->limit(500)->toString(),
                ]);

                return null;
            }

            return data_get($response->json(), 'choices.0.message.content');
        } catch (\Throwable $exception) {
            Log::warning('Virtual Assistant AI provider exception', [
                'provider' => 'openai',
                'model' => config('asisten_virtual.openai.model'),
                'message' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    private function refineWithGemini(string $question, array $queryResult): ?string
    {
        $apiKey = (string) config('asisten_virtual.gemini.api_key');
        if ($apiKey === '') {
            return null;
        }

        $model = (string) config('asisten_virtual.gemini.model', 'gemini-2.5-flash');
        $endpoint = rtrim((string) config('asisten_virtual.gemini.endpoint'), '/')
            . '/' . rawurlencode($model) . ':generateContent';

        try {
            $response = Http::withHeaders([
                'x-goog-api-key' => $apiKey,
            ])
                ->acceptJson()
                ->timeout((int) config('asisten_virtual.gemini.timeout', 12))
                ->post($endpoint, [
                    'systemInstruction' => [
                        'parts' => [
                            ['text' => $this->systemInstruction()],
                        ],
                    ],
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => $this->buildSafePrompt($question, $queryResult)],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.2,
                    ],
                ]);

            if (!$response->successful()) {
                Log::warning('Virtual Assistant AI provider failed', [
                    'provider' => 'gemini',
                    'model' => $model,
                    'status' => $response->status(),
                    'body' => (string) str($response->body())->limit(500),
                ]);

                return null;
            }

            return data_get($response->json(), 'candidates.0.content.parts.0.text');
        } catch (\Throwable $exception) {
            Log::warning('Virtual Assistant AI provider exception', [
                'provider' => 'gemini',
                'model' => $model,
                'message' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    private function systemInstruction(): string
    {
        return implode("\n", [
            'Anda adalah Asisten Virtual untuk aplikasi Agenda Online PTPN.',
            'Jawab dalam Bahasa Indonesia yang ringkas, rapi, dan faktual.',
            'Gunakan hanya data JSON yang diberikan aplikasi.',
            'Jangan mengarang data, jangan menyebut query SQL, dan jangan memberi instruksi teknis internal.',
            'Jika data kosong, jelaskan bahwa data tidak ditemukan.',
        ]);
    }

    private function buildSafePrompt(string $question, array $queryResult): string
    {
        return json_encode([
            'pertanyaan' => $question,
            'hasil_query_aman' => $queryResult,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}

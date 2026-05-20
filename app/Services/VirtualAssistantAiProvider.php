<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VirtualAssistantAiProvider
{
    public function refineAnswer(string $question, array $queryResult): ?string
    {
        if (config('asisten_virtual.provider') !== 'openai') {
            return null;
        }

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
                            'content' => implode("\n", [
                                'Anda adalah Asisten Virtual untuk aplikasi Agenda Online PTPN.',
                                'Jawab dalam Bahasa Indonesia yang ringkas, rapi, dan faktual.',
                                'Gunakan hanya data JSON yang diberikan aplikasi.',
                                'Jangan mengarang data, jangan menyebut query SQL, dan jangan memberi instruksi teknis internal.',
                                'Jika data kosong, jelaskan bahwa data tidak ditemukan.',
                            ]),
                        ],
                        [
                            'role' => 'user',
                            'content' => json_encode([
                                'pertanyaan' => $question,
                                'hasil_query_aman' => $queryResult,
                            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
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
                'message' => $exception->getMessage(),
            ]);

            return null;
        }
    }
}

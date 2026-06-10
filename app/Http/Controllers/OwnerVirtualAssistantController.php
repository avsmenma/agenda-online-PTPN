<?php

namespace App\Http\Controllers;

use App\Models\VirtualAssistantInteraction;
use App\Services\VirtualAssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OwnerVirtualAssistantController extends Controller
{
    public function index(): View
    {
        return view('owner.asisten-virtual', [
            'module' => 'owner',
            'menuAsistenVirtual' => 'active',
            'assistantContext' => 'owner',
            'assistantSubtitle' => 'Tanyakan data dokumen, pembayaran, status, dan laporan kepada asisten AI',
            'assistantPlaceholder' => 'Contoh: Tampilkan dokumen yang belum dibayar di atas 100 juta',
            'assistantEmptyTitle' => 'Mulai tanya data Agenda Online',
            'assistantEmptyText' => 'Asisten hanya membaca data melalui query aman yang sudah dibatasi aplikasi.',
            'assistantChatUrl' => route('owner.asisten-virtual.chat'),
            'assistantFeedbackUrlTemplate' => route('owner.asisten-virtual.feedback', ['interaction' => '__ID__']),
            'assistantQuickPrompts' => [],
            'assistantTypewriterExamples' => [
                'Tampilkan dokumen yang belum dibayar di atas 100 juta',
                'Berapa total nilai dokumen yang sudah dibayar?',
                'Dokumen apa saja yang dikembalikan ke bidang?',
                'Tampilkan dokumen terlambat lebih dari 3 hari',
                'Berapa total dokumen masuk bulan ini?',
            ],
        ]);
    }

    public function pembayaranIndex(): View
    {
        return view('owner.asisten-virtual', [
            'module' => 'pembayaran',
            'menuAsistenVirtual' => 'active',
            'assistantContext' => 'pembayaran',
            'assistantSubtitle' => 'Tanyakan data pembayaran, status dokumen, dan laporan pembayaran kepada asisten AI',
            'assistantPlaceholder' => 'Contoh: Tampilkan dokumen siap dibayar di atas 100 juta',
            'assistantEmptyTitle' => 'Mulai tanya data pembayaran',
            'assistantEmptyText' => 'Asisten Pembayaran membaca data dokumen melalui query aman yang dibatasi aplikasi.',
            'assistantChatUrl' => route('pembayaran.asisten-virtual.chat'),
            'assistantFeedbackUrlTemplate' => route('pembayaran.asisten-virtual.feedback', ['interaction' => '__ID__']),
            'assistantQuickPrompts' => [
                'Dokumen siap dibayar',
                'Total belum siap bayar bulan ini',
                'Dokumen sudah dibayar hari ini',
                'Dokumen pembayaran terlambat',
                'Total nilai sudah dibayar',
            ],
            'assistantTypewriterExamples' => [
                'Tampilkan dokumen siap dibayar di atas 100 juta',
                'Berapa total nilai yang sudah dibayar?',
                'Dokumen pembayaran yang terlambat',
                'Total dokumen sudah dibayar hari ini',
                'Berapa total belum siap bayar bulan ini?',
            ],
        ]);
    }

    public function chat(Request $request, VirtualAssistantService $assistant): JsonResponse
    {
        $startedAt = microtime(true);

        $validated = $request->validate([
            'message' => [
                'required',
                'string',
                'min:3',
                'max:' . (int) config('asisten_virtual.limits.max_message_length', 800),
            ],
            'context' => ['nullable', 'array'],
            'context.selected_document' => ['nullable', 'array'],
            'context.selected_document.id' => ['nullable', 'integer'],
            'context.selected_document.nomor_agenda' => ['nullable', 'string', 'max:80'],
            'context.selected_document.nomor_spp' => ['nullable', 'string', 'max:160'],
        ], [
            'message.required' => 'Pertanyaan wajib diisi.',
            'message.min' => 'Pertanyaan terlalu pendek.',
            'message.max' => 'Pertanyaan terlalu panjang. Ringkas pertanyaan agar lebih mudah dianalisis.',
        ]);

        try {
            $context = $validated['context'] ?? [];
            $context['assistant_scope'] = $this->assistantContext($request);

            $reply = $assistant->respond($validated['message'], $context);
            try {
                $log = $this->storeInteraction($request, $validated['message'], $reply, (int) round((microtime(true) - $startedAt) * 1000));
                $reply['interaction_id'] = $log->id;
            } catch (\Throwable $loggingException) {
                report($loggingException);
            }

            return response()->json([
                'success' => true,
                'reply' => $reply,
            ]);
        } catch (\Throwable $exception) {
            report($exception);
            try {
                $this->storeErrorInteraction($request, $validated['message'] ?? '', $exception, (int) round((microtime(true) - $startedAt) * 1000));
            } catch (\Throwable $loggingException) {
                report($loggingException);
            }

            return response()->json([
                'success' => false,
                'message' => 'Maaf, Asisten Virtual belum bisa memproses pertanyaan ini. Coba persempit pertanyaan atau ulangi beberapa saat lagi.',
            ], 500);
        }
    }

    public function feedback(Request $request, VirtualAssistantInteraction $interaction): JsonResponse
    {
        abort_unless((int) $interaction->user_id === (int) $request->user()->id, 403);

        $validated = $request->validate([
            'feedback' => ['required', 'in:helpful,not_helpful,wrong_answer'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $interaction->update([
            'feedback' => $validated['feedback'],
            'feedback_reason' => isset($validated['reason']) ? Str::limit($validated['reason'], 1000, '') : null,
            'feedback_at' => now(),
            'result_status' => $validated['feedback'] === 'helpful'
                ? $interaction->result_status
                : VirtualAssistantInteraction::STATUS_NEEDS_REVIEW,
            'failure_category' => $validated['feedback'] === 'helpful'
                ? $interaction->failure_category
                : ($interaction->failure_category ?: 'response_ai_kurang_bagus'),
            'failure_reason' => $validated['feedback'] === 'helpful'
                ? $interaction->failure_reason
                : ($validated['reason'] ?? 'User menandai jawaban tidak sesuai.'),
        ]);

        return response()->json([
            'success' => true,
            'message' => $validated['feedback'] === 'helpful'
                ? 'Terima kasih, feedback membantu sudah disimpan.'
                : 'Terima kasih, jawaban ini masuk daftar review programmer.',
        ]);
    }

    private function storeInteraction(Request $request, string $question, array $reply, int $latencyMs): VirtualAssistantInteraction
    {
        $meta = $reply['meta'] ?? [];
        $status = $this->classifyStatus($reply);
        $failure = $this->classifyFailure($reply, $status);

        return VirtualAssistantInteraction::create([
            'user_id' => $request->user()?->id,
            'user_role' => $request->user()?->role,
            'question' => Str::limit($question, 2000, ''),
            'normalized_question' => Str::limit((string) str($question)->lower()->replaceMatches('/\s+/', ' ')->trim(), 2000, ''),
            'intent' => $reply['intent'] ?? null,
            'confidence' => data_get($meta, 'confidence'),
            'detected_params' => data_get($meta, 'params'),
            'internal_service' => data_get($meta, 'service'),
            'result_count' => is_array($reply['data'] ?? null) ? count($reply['data']) : 0,
            'result_total' => data_get($meta, 'total'),
            'answer' => Str::limit((string) ($reply['answer'] ?? ''), 4000, ''),
            'result_status' => $status,
            'failure_category' => $failure['category'],
            'failure_reason' => $failure['reason'],
            'ai_provider' => data_get($meta, 'ai_provider', config('asisten_virtual.provider')),
            'ai_model' => $this->aiModelName((string) data_get($meta, 'ai_provider', config('asisten_virtual.provider'))),
            'ai_called' => (bool) data_get($meta, 'ai_called', false),
            'ai_skipped_reason' => data_get($meta, 'ai_skipped_reason'),
            'latency_ms' => $latencyMs,
            'source_context' => $this->assistantContext($request),
        ]);
    }

    private function storeErrorInteraction(Request $request, string $question, \Throwable $exception, int $latencyMs): void
    {
        VirtualAssistantInteraction::create([
            'user_id' => $request->user()?->id,
            'user_role' => $request->user()?->role,
            'question' => Str::limit($question, 2000, ''),
            'normalized_question' => Str::limit((string) str($question)->lower()->replaceMatches('/\s+/', ' ')->trim(), 2000, ''),
            'intent' => 'error',
            'internal_service' => 'chat_controller',
            'result_status' => VirtualAssistantInteraction::STATUS_ERROR,
            'failure_category' => 'error_teknis',
            'failure_reason' => Str::limit($exception->getMessage(), 1000, ''),
            'ai_provider' => config('asisten_virtual.provider'),
            'ai_model' => $this->aiModelName((string) config('asisten_virtual.provider')),
            'ai_called' => false,
            'latency_ms' => $latencyMs,
            'source_context' => $this->assistantContext($request),
        ]);
    }

    private function assistantContext(Request $request): string
    {
        return str_starts_with((string) $request->route()?->getName(), 'pembayaran.')
            ? 'pembayaran'
            : 'owner';
    }

    private function classifyStatus(array $reply): string
    {
        $intent = (string) ($reply['intent'] ?? '');
        $service = (string) data_get($reply, 'meta.service', '');
        $confidence = (string) data_get($reply, 'meta.confidence', '');

        if ($intent === 'clarification') {
            return VirtualAssistantInteraction::STATUS_AMBIGUOUS;
        }

        if ($confidence === 'low') {
            return VirtualAssistantInteraction::STATUS_LOW_CONFIDENCE;
        }

        if ($service === 'empty_result') {
            return VirtualAssistantInteraction::STATUS_NO_DATA;
        }

        if ($intent === '' || $intent === 'unsupported') {
            return VirtualAssistantInteraction::STATUS_UNSUPPORTED;
        }

        return VirtualAssistantInteraction::STATUS_ANSWERED;
    }

    private function classifyFailure(array $reply, string $status): array
    {
        if ($status === VirtualAssistantInteraction::STATUS_ANSWERED) {
            return ['category' => null, 'reason' => null];
        }

        $answer = (string) ($reply['answer'] ?? '');
        $params = data_get($reply, 'meta.params', []);

        if ($status === VirtualAssistantInteraction::STATUS_NO_DATA) {
            return ['category' => 'query_database_belum_tersedia_atau_data_kosong', 'reason' => Str::limit($answer, 1000, '')];
        }

        if ($status === VirtualAssistantInteraction::STATUS_UNSUPPORTED) {
            return ['category' => 'intent_belum_tersedia', 'reason' => Str::limit($answer, 1000, '')];
        }

        if ($status === VirtualAssistantInteraction::STATUS_LOW_CONFIDENCE) {
            return ['category' => 'sinonim_belum_dikenali', 'reason' => Str::limit($answer, 1000, '')];
        }

        if (($params['date_range'] ?? null) === null && str_contains(strtolower($answer), 'tanggal')) {
            return ['category' => 'format_tanggal_tidak_terbaca', 'reason' => Str::limit($answer, 1000, '')];
        }

        if (($params['payment_statuses'] ?? []) === [] && str_contains(strtolower($answer), 'bayar')) {
            return ['category' => 'status_pembayaran_tidak_termapping', 'reason' => Str::limit($answer, 1000, '')];
        }

        return ['category' => 'pertanyaan_terlalu_umum', 'reason' => Str::limit($answer, 1000, '')];
    }

    private function aiModelName(string $provider): ?string
    {
        return match ($provider) {
            'gemini' => config('asisten_virtual.gemini.model'),
            'openai' => config('asisten_virtual.openai.model'),
            default => null,
        };
    }
}

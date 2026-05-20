<?php

namespace App\Http\Controllers;

use App\Models\VirtualAssistantInteraction;
use App\Models\VirtualAssistantTestCase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProgrammerAssistantEvaluationController extends Controller
{
    private const REVIEW_STATUSES = [
        VirtualAssistantInteraction::STATUS_AMBIGUOUS,
        VirtualAssistantInteraction::STATUS_UNSUPPORTED,
        VirtualAssistantInteraction::STATUS_NO_DATA,
        VirtualAssistantInteraction::STATUS_ERROR,
        VirtualAssistantInteraction::STATUS_LOW_CONFIDENCE,
        VirtualAssistantInteraction::STATUS_NEEDS_REVIEW,
    ];

    public function index(Request $request): View
    {
        $baseQuery = $this->filteredQuery($request);
        $interactions = (clone $baseQuery)
            ->with(['user', 'fixedBy'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => VirtualAssistantInteraction::count(),
            'needs_review' => VirtualAssistantInteraction::query()
                ->whereNull('fixed_at')
                ->where(function (Builder $query) {
                    $query->whereIn('result_status', self::REVIEW_STATUSES)
                        ->orWhereIn('feedback', ['not_helpful', 'wrong_answer']);
                })
                ->count(),
            'ambiguous' => VirtualAssistantInteraction::where('result_status', VirtualAssistantInteraction::STATUS_AMBIGUOUS)->count(),
            'error' => VirtualAssistantInteraction::where('result_status', VirtualAssistantInteraction::STATUS_ERROR)->count(),
            'fixed' => VirtualAssistantInteraction::whereNotNull('fixed_at')->count(),
        ];

        $recommendations = $this->buildRecommendations();
        $testCases = VirtualAssistantTestCase::orderByDesc('is_active')
            ->orderBy('id')
            ->get();

        return view('programmer.assistant-evaluation', [
            'interactions' => $interactions,
            'stats' => $stats,
            'recommendations' => $recommendations,
            'testCases' => $testCases,
            'statuses' => $this->statusOptions(),
            'categories' => $this->categoryOptions(),
        ]);
    }

    public function markFixed(Request $request, VirtualAssistantInteraction $interaction): RedirectResponse
    {
        $validated = $request->validate([
            'fix_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $interaction->update([
            'fixed_at' => now(),
            'fixed_by' => $request->user()?->id,
            'fix_note' => $validated['fix_note'] ?? 'Ditandai sudah diperbaiki.',
        ]);

        return back()->with('success', 'Log Asisten Virtual sudah ditandai diperbaiki.');
    }

    public function storeTestCase(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'question' => ['required', 'string', 'max:1000'],
            'expected_intent' => ['nullable', 'string', 'max:100'],
            'expected_params_json' => ['nullable', 'string', 'max:2000'],
            'expected_result_type' => ['required', 'in:any,summary,list,list_or_empty,no_data,clarification'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $expectedParams = [];
        if (!empty($validated['expected_params_json'])) {
            $decoded = json_decode($validated['expected_params_json'], true);
            if (!is_array($decoded)) {
                return back()
                    ->withInput()
                    ->with('error', 'Expected parameter harus berupa JSON object yang valid.');
            }
            $expectedParams = $decoded;
        }

        VirtualAssistantTestCase::create([
            'name' => $validated['name'],
            'question' => $validated['question'],
            'expected_intent' => $validated['expected_intent'] ?: null,
            'expected_params' => $expectedParams,
            'expected_result_type' => $validated['expected_result_type'],
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        return back()->with('success', 'Test case Asisten Virtual berhasil ditambahkan.');
    }

    public function export(Request $request): StreamedResponse
    {
        $fileName = 'evaluasi-asisten-virtual-' . now()->format('Ymd-His') . '.csv';
        $query = $this->filteredQuery($request)->with(['user', 'fixedBy'])->latest();

        return Response::streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Waktu',
                'User',
                'Role',
                'Pertanyaan',
                'Intent',
                'Status',
                'Kategori Gagal',
                'Alasan',
                'Service',
                'Jumlah Hasil',
                'Total Hasil',
                'Feedback',
                'Alasan Feedback',
                'Provider',
                'Model',
                'AI Dipanggil',
                'Alasan AI Skip',
                'Latency MS',
                'Jawaban',
                'Diperbaiki Pada',
                'Catatan Perbaikan',
            ]);

            $query->chunk(200, function ($rows) use ($handle) {
                foreach ($rows as $row) {
                    fputcsv($handle, [
                        optional($row->created_at)->format('Y-m-d H:i:s'),
                        $row->user?->name,
                        $row->user_role,
                        $row->question,
                        $row->intent,
                        $row->result_status,
                        $row->failure_category,
                        $row->failure_reason,
                        $row->internal_service,
                        $row->result_count,
                        $row->result_total,
                        $row->feedback,
                        $row->feedback_reason,
                        $row->ai_provider,
                        $row->ai_model,
                        $row->ai_called ? 'ya' : 'tidak',
                        $row->ai_skipped_reason,
                        $row->latency_ms,
                        $row->answer,
                        optional($row->fixed_at)->format('Y-m-d H:i:s'),
                        $row->fix_note,
                    ]);
                }
            });

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function filteredQuery(Request $request): Builder
    {
        $query = VirtualAssistantInteraction::query();

        if ($request->input('scope', 'review') === 'review') {
            $query->whereNull('fixed_at')
                ->where(function (Builder $subQuery) {
                    $subQuery->whereIn('result_status', self::REVIEW_STATUSES)
                        ->orWhereIn('feedback', ['not_helpful', 'wrong_answer']);
                });
        }

        if ($request->filled('status')) {
            $query->where('result_status', $request->string('status'));
        }

        if ($request->filled('intent')) {
            $query->where('intent', $request->string('intent'));
        }

        if ($request->filled('category')) {
            $query->where('failure_category', $request->string('category'));
        }

        if ($request->filled('feedback')) {
            $query->where('feedback', $request->string('feedback'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date('date_to'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function (Builder $subQuery) use ($search) {
                $subQuery->where('question', 'like', "%{$search}%")
                    ->orWhere('answer', 'like', "%{$search}%")
                    ->orWhere('intent', 'like', "%{$search}%")
                    ->orWhere('failure_reason', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    private function buildRecommendations(): array
    {
        $failedQuery = VirtualAssistantInteraction::query()
            ->whereIn('result_status', self::REVIEW_STATUSES)
            ->orWhereIn('feedback', ['not_helpful', 'wrong_answer']);

        return [
            'by_intent' => (clone $failedQuery)
                ->selectRaw("COALESCE(intent, 'tidak_terdeteksi') as label, COUNT(*) as total")
                ->groupBy('label')
                ->orderByDesc('total')
                ->limit(8)
                ->pluck('total', 'label')
                ->all(),
            'by_category' => (clone $failedQuery)
                ->selectRaw("COALESCE(failure_category, 'belum_dikategorikan') as label, COUNT(*) as total")
                ->groupBy('label')
                ->orderByDesc('total')
                ->limit(8)
                ->pluck('total', 'label')
                ->all(),
            'frequent_questions' => (clone $failedQuery)
                ->whereNotNull('normalized_question')
                ->selectRaw('normalized_question, COUNT(*) as total')
                ->groupBy('normalized_question')
                ->having('total', '>=', 1)
                ->orderByDesc('total')
                ->limit(8)
                ->get()
                ->map(fn ($row) => [
                    'question' => Str::limit($row->normalized_question, 120),
                    'total' => (int) $row->total,
                ])
                ->all(),
        ];
    }

    private function statusOptions(): array
    {
        return [
            VirtualAssistantInteraction::STATUS_ANSWERED => 'Terjawab',
            VirtualAssistantInteraction::STATUS_AMBIGUOUS => 'Ambigu',
            VirtualAssistantInteraction::STATUS_UNSUPPORTED => 'Intent belum didukung',
            VirtualAssistantInteraction::STATUS_NO_DATA => 'Data tidak ditemukan',
            VirtualAssistantInteraction::STATUS_ERROR => 'Error',
            VirtualAssistantInteraction::STATUS_LOW_CONFIDENCE => 'Confidence rendah',
            VirtualAssistantInteraction::STATUS_NEEDS_REVIEW => 'Perlu review',
        ];
    }

    private function categoryOptions(): array
    {
        return [
            'intent_belum_tersedia' => 'Intent belum tersedia',
            'sinonim_belum_dikenali' => 'Sinonim belum dikenali',
            'format_tanggal_tidak_terbaca' => 'Format tanggal tidak terbaca',
            'status_pembayaran_tidak_termapping' => 'Status pembayaran tidak termapping',
            'nama_bagian_tidak_dikenali' => 'Nama bagian/unit tidak dikenali',
            'pertanyaan_terlalu_umum' => 'Pertanyaan terlalu umum',
            'query_database_belum_tersedia_atau_data_kosong' => 'Query/data belum tersedia',
            'hasil_data_terlalu_banyak' => 'Hasil data terlalu banyak',
            'response_ai_kurang_bagus' => 'Response AI kurang bagus',
            'error_teknis' => 'Error teknis/API/database',
        ];
    }
}

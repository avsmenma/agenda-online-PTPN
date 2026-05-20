<?php

namespace App\Services;

use App\Models\Dokumen;
use App\Models\DokumenRoleData;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class VirtualAssistantQueryService
{
    private const MONTHS = [
        'januari' => 1, 'jan' => 1,
        'februari' => 2, 'feb' => 2,
        'maret' => 3, 'mar' => 3,
        'april' => 4, 'apr' => 4,
        'mei' => 5, 'may' => 5,
        'juni' => 6, 'jun' => 6,
        'juli' => 7, 'jul' => 7,
        'agustus' => 8, 'agu' => 8, 'ags' => 8, 'aug' => 8,
        'september' => 9, 'sep' => 9,
        'oktober' => 10, 'okt' => 10, 'oct' => 10,
        'november' => 11, 'nov' => 11,
        'desember' => 12, 'des' => 12, 'dec' => 12,
    ];

    private const HANDLER_LABELS = [
        'operator' => 'Operator',
        'bagian' => 'Bagian',
        'team_verifikasi' => 'Tim Verifikasi',
        'verifikasi' => 'Tim Verifikasi',
        'perpajakan' => 'Perpajakan',
        'akutansi' => 'Akuntansi',
        'akuntansi' => 'Akuntansi',
        'pembayaran' => 'Pembayaran',
    ];

    private const WORKFLOW_STATUS_ALIASES = [
        'draft' => ['draft'],
        'pending' => ['pending', 'menunggu', 'approval', 'approve'],
        'processing' => ['proses', 'diproses', 'sedang diproses'],
        'sent_to_verification' => ['verifikasi', 'sent_to_team_verifikasi', 'reviewer'],
        'sent_to_tax' => ['perpajakan', 'pajak', 'sent_to_perpajakan'],
        'sent_to_accounting' => ['akuntansi', 'akutansi', 'accounting', 'sent_to_akutansi'],
        'sent_to_payment' => ['pembayaran', 'sent_to_pembayaran'],
        'completed' => ['selesai', 'completed', 'tuntas'],
        'returned' => ['dikembalikan', 'return', 'returned', 'ditolak'],
    ];

    public function answer(string $message, array $context = []): array
    {
        $normalized = $this->normalize($message);
        $limit = min((int) config('asisten_virtual.limits.default_result_limit', 15), 20);
        $params = $this->parseParameters($message, $normalized);
        $params = $this->applyConversationContext($params, $normalized, $context);
        $intent = $this->resolveIntent($normalized, $params);

        $result = match ($intent) {
            'greeting' => $this->greeting(),
            'cashbank_summary' => $this->cashbankSummary($params),
            'document_summary' => $this->documentSummary($params),
            'document_entry_dates_summary' => $this->documentEntryDatesSummary($params, $limit),
            'specific_document_age' => $this->specificDocumentAge($params),
            'specific_document_position' => $this->specificDocumentPosition($params),
            'role_average_duration' => $this->roleAverageDuration($params),
            'documents_by_age' => $this->documentsByAge($params, $limit),
            'recent_documents' => $this->recentDocuments($params, $limit),
            'top_departments_by_value' => $this->topDepartments('value', $limit, $params),
            'top_departments_by_count' => $this->topDepartments('count', $limit, $params),
            'late_documents' => $this->lateDocuments($params, $limit),
            'oldest_documents' => $this->oldestDocuments($params, $limit),
            'payment_summary' => $this->paymentSummary($params),
            'specific_document_payment_status' => $this->specificDocumentPaymentStatus($params),
            'ready_to_pay_documents',
            'unpaid_documents',
            'paid_documents',
            'documents_by_payment_status',
            'documents_by_workflow_status',
            'documents_by_department',
            'documents_by_current_handler',
            'documents_by_amount_range',
            'documents_by_keyword',
            'documents_by_month',
            'documents_by_date',
            'documents_filtered' => $this->documentsByFilters($intent, $params, $limit),
            default => $this->clarification($message, $params),
        };

        $this->logDecision($message, $intent, $params, $result);

        return $result;
    }

    private function resolveIntent(string $text, array $params): string
    {
        if ($this->isGreeting($text)) {
            return 'greeting';
        }

        if ($this->containsAny($text, ['cash bank', 'cashbank', 'saldo bank', 'saldo rekening', 'dropping', 'penerimaan cash'])) {
            return 'cashbank_summary';
        }

        if ($params['asks_average_duration']) {
            return 'role_average_duration';
        }

        if ($params['context_document_id'] && $params['asks_age']) {
            return 'specific_document_age';
        }

        if ($params['keyword'] && $params['asks_position']) {
            return 'specific_document_position';
        }

        if ($params['keyword'] && $this->containsAny($text, ['status pembayaran', 'sudah dibayar', 'belum dibayar', 'siap dibayar', 'bayar', 'pembayaran', 'lunas'])) {
            return 'specific_document_payment_status';
        }

        if ($params['keyword'] && $params['asks_age']) {
            return 'specific_document_age';
        }

        if ($params['asks_entry_dates']) {
            return 'document_entry_dates_summary';
        }

        if ($params['asks_recent']) {
            return 'recent_documents';
        }

        if ($params['age_days_min'] !== null || $params['age_days_max'] !== null) {
            return 'documents_by_age';
        }

        if ($params['keyword'] && $this->containsAny($text, ['uraian', 'nomor agenda dari dokumen', 'nomor agenda dokumen'])) {
            return 'documents_by_keyword';
        }

        if ($this->containsAny($text, ['top bagian', 'bagian mana', 'bagian apa', 'paling banyak mengirim', 'paling banyak masuk'])) {
            return 'top_departments_by_count';
        }

        if ($this->containsAny($text, ['nilai dokumen terbesar', 'nilai terbesar', 'berdasarkan nilai', 'total nilai terbesar', 'bagian terbesar'])) {
            return 'top_departments_by_value';
        }

        if ($this->containsAny($text, ['paling lama', 'terlama', 'lama diproses', 'umur dokumen paling'])) {
            return 'oldest_documents';
        }

        if ($this->containsAny($text, ['tertahan', 'terlambat', 'terlalu lama', 'telat'])) {
            return 'late_documents';
        }

        if ($params['context_document_id'] && $this->containsAny($text, ['bayar', 'dibayar', 'pembayaran', 'lunas'])) {
            return 'specific_document_payment_status';
        }

        if ($params['payment_statuses'] !== []) {
            if ($params['wants_total']) {
                return 'payment_summary';
            }

            if ($params['payment_statuses'] === ['siap_dibayar']) {
                return 'ready_to_pay_documents';
            }

            if ($params['payment_statuses'] === ['belum_dibayar']) {
                return 'unpaid_documents';
            }

            if ($params['payment_statuses'] === ['sudah_dibayar']) {
                return 'paid_documents';
            }

            return 'documents_by_payment_status';
        }

        if ($params['wants_total'] && !$this->hasDocumentFilters($params)) {
            return 'document_summary';
        }

        if ($params['date_range']) {
            return $params['date_range']['type'] === 'month' ? 'documents_by_month' : 'documents_by_date';
        }

        if ($params['workflow_status']) {
            return 'documents_by_workflow_status';
        }

        if ($params['department']) {
            return 'documents_by_department';
        }

        if ($params['handler']) {
            return 'documents_by_current_handler';
        }

        if ($params['amount_min'] !== null || $params['amount_max'] !== null) {
            return 'documents_by_amount_range';
        }

        if ($params['keyword']) {
            return 'documents_by_keyword';
        }

        return 'clarification';
    }

    private function parseParameters(string $message, string $text): array
    {
        return [
            'date_range' => $this->extractDateRange($text),
            'payment_statuses' => $this->extractPaymentStatuses($text),
            'workflow_status' => $this->extractWorkflowStatus($text),
            'department' => $this->extractDepartment($message),
            'handler' => $this->extractHandler($text),
            'amount_min' => $this->extractAmountBound($text, 'min'),
            'amount_max' => $this->extractAmountBound($text, 'max'),
            'keyword' => $this->extractKeyword($message, $text),
            'keyword_focus' => $this->isKeywordFocused($message, $text),
            'age_days_min' => $this->extractAgeDays($text, 'min'),
            'age_days_max' => $this->extractAgeDays($text, 'max'),
            'context_document_id' => null,
            'context_document_label' => null,
            'wants_total' => $this->containsAny($text, ['berapa', 'total', 'jumlah', 'ringkasan', 'rekap']),
            'wants_list' => $this->containsAny($text, ['dokumen', 'berikan', 'tampilkan', 'apa saja', 'daftar', 'cari']),
            'asks_average_duration' => $this->containsAny($text, ['rata rata', 'rata-rata', 'rerata', 'average']) && $this->containsAny($text, ['lama', 'durasi', 'waktu']),
            'asks_age' => $this->containsAny($text, ['umur dokumen', 'berapa umur', 'usia dokumen']),
            'asks_position' => $this->containsAny($text, ['posisi mana', 'posisi dokumen', 'sedang dimana', 'di mana', 'dimana', 'pengurus siapa', 'tahap mana']),
            'asks_entry_dates' => $this->containsAny($text, ['tanggal berapa saja', 'tanggal apa saja']) && $this->containsAny($text, ['masuk', 'dokumen']),
            'asks_recent' => $this->containsAny($text, ['akhir-akhir ini', 'akhir akhir ini', 'terbaru', 'baru masuk', 'recent']),
        ];
    }

    private function applyConversationContext(array $params, string $text, array $context): array
    {
        $selected = data_get($context, 'selected_document');
        if (!is_array($selected) || !$this->containsAny($text, ['tersebut', 'itu', 'tadi', 'dokumen ini', 'dokumen tadi'])) {
            return $params;
        }

        $documentId = data_get($selected, 'id');
        $label = data_get($selected, 'nomor_agenda') ?: data_get($selected, 'nomor_spp');

        if ($documentId) {
            $params['context_document_id'] = (int) $documentId;
            $params['context_document_label'] = $label;
            $params['keyword'] = null;
        }

        return $params;
    }

    private function greeting(): array
    {
        return [
            'intent' => 'greeting',
            'answer' => 'Halo. Saya siap membantu membaca data Agenda Online, misalnya total dokumen, dokumen siap dibayar, dokumen belum dibayar, dokumen masuk tanggal tertentu, dokumen terlambat, atau top bagian berdasarkan nilai dokumen.',
            'data' => [
                'contoh_pertanyaan' => [
                    'Berapa total dokumen?',
                    'Berikan saya dokumen siap dibayar',
                    'Dokumen belum dibayar di atas 100 juta',
                    'Dokumen yang masuk tanggal 5 Mei 2026',
                    'Bagian mana dengan nilai dokumen terbesar?',
                ],
            ],
            'link' => route('owner.dokumen'),
            'meta' => ['confidence' => 'high', 'service' => 'greeting'],
        ];
    }

    private function documentSummary(array $params = []): array
    {
        $query = $this->baseQuery();
        $this->applyFilters($query, $params, false);

        $totalDocuments = (clone $query)->count();
        $totalValue = (clone $query)->sum('nilai_rupiah');
        $paidQuery = $this->baseQuery();
        $unpaidQuery = $this->baseQuery();
        $readyQuery = $this->baseQuery();
        $this->applyFilters($paidQuery, $params, false);
        $this->applyFilters($unpaidQuery, $params, false);
        $this->applyFilters($readyQuery, $params, false);
        $this->applyPaymentStatuses($paidQuery, ['sudah_dibayar']);
        $this->applyPaymentStatuses($unpaidQuery, ['belum_dibayar']);
        $this->applyPaymentStatuses($readyQuery, ['siap_dibayar']);

        return [
            'intent' => 'document_summary',
            'answer' => "Total dokumen{$this->filterLabel($params)} adalah {$totalDocuments} dokumen dengan nilai {$this->formatMoney($totalValue)}.",
            'data' => [
                'total_dokumen' => $totalDocuments,
                'total_nilai' => $this->formatMoney($totalValue),
                'siap_dibayar' => [
                    'jumlah_dokumen' => (clone $readyQuery)->count(),
                    'total_nilai' => $this->formatMoney((clone $readyQuery)->sum('nilai_rupiah')),
                ],
                'sudah_dibayar' => [
                    'jumlah_dokumen' => (clone $paidQuery)->count(),
                    'total_nilai' => $this->formatMoney((clone $paidQuery)->sum('nilai_rupiah')),
                ],
                'belum_dibayar' => [
                    'jumlah_dokumen' => (clone $unpaidQuery)->count(),
                    'total_nilai' => $this->formatMoney((clone $unpaidQuery)->sum('nilai_rupiah')),
                ],
            ],
            'link' => route('owner.dokumen', $this->linkParams($params)),
            'meta' => ['confidence' => 'high', 'service' => 'document_summary', 'params' => $params],
        ];
    }

    private function paymentSummary(array $params): array
    {
        $query = $this->baseQuery();
        $this->applyFilters($query, $params);

        $count = (clone $query)->count();
        $total = (clone $query)->sum('nilai_rupiah');
        $statusLabel = $this->paymentStatusListLabel($params['payment_statuses']);
        $breakdown = [];

        foreach ($params['payment_statuses'] as $status) {
            $statusQuery = $this->baseQuery();
            $filteredParams = $params;
            $filteredParams['payment_statuses'] = [$status];
            $this->applyFilters($statusQuery, $filteredParams);
            $breakdown[$status] = [
                'status' => $this->paymentStatusListLabel([$status]),
                'jumlah_dokumen' => (clone $statusQuery)->count(),
                'total_nilai' => $this->formatMoney((clone $statusQuery)->sum('nilai_rupiah')),
            ];
        }

        return [
            'intent' => 'payment_summary',
            'answer' => "Total dokumen {$statusLabel}{$this->filterLabel($params, false)} adalah {$count} dokumen dengan nilai {$this->formatMoney($total)}.",
            'data' => [
                'status_pembayaran' => $statusLabel,
                'jumlah_dokumen' => $count,
                'total_nilai' => $this->formatMoney($total),
                'breakdown' => implode(' | ', array_map(
                    fn ($item) => "{$item['status']}: {$item['jumlah_dokumen']} dokumen ({$item['total_nilai']})",
                    $breakdown
                )),
            ],
            'link' => route('owner.dokumen', $this->linkParams($params)),
            'meta' => ['confidence' => 'high', 'service' => 'payment_summary', 'params' => $params],
        ];
    }

    private function specificDocumentPaymentStatus(array $params): array
    {
        $doc = $this->findSpecificDocument($params);

        if (!$doc) {
            return $this->emptyResult($this->documentNotFoundAnswer($params, true), $params, 'specific_document_payment_status', false);
        }

        $paymentStatus = $this->paymentLabel($doc->status_pembayaran, $doc->tanggal_dibayar);
        $paidDate = $doc->tanggal_dibayar ? Carbon::parse($doc->tanggal_dibayar)->format('d/m/Y') : null;
        $paidText = $paidDate ? " pada {$paidDate}" : '';
        $answer = "Dokumen {$doc->nomor_agenda} berstatus pembayaran {$paymentStatus}{$paidText}.";

        return [
            'intent' => 'specific_document_payment_status',
            'answer' => $answer,
            'data' => $this->formatDocuments(collect([$doc])),
            'link' => route('owner.dokumen', ['status' => 'all', 'search' => $doc->nomor_agenda]),
            'meta' => [
                'service' => 'specific_document_payment_status',
                'context_document_id' => $doc->id,
                'params' => $params,
            ],
        ];
    }

    private function specificDocumentAge(array $params): array
    {
        $doc = $this->findSpecificDocument($params);

        if (!$doc || !$doc->tanggal_masuk) {
            return $this->emptyResult($this->documentNotFoundAnswer($params), $params, 'specific_document_age', false);
        }

        $tanggalMasuk = Carbon::parse($doc->tanggal_masuk);
        $age = $tanggalMasuk->diffForHumans(null, true);

        return [
            'intent' => 'specific_document_age',
            'answer' => "Umur dokumen {$doc->nomor_agenda} adalah {$age} sejak tanggal masuk {$tanggalMasuk->format('d M Y H:i')}.",
            'data' => $this->formatDocuments(collect([$doc]), true),
            'link' => route('owner.dokumen', ['status' => 'all', 'search' => $doc->nomor_agenda]),
            'meta' => ['confidence' => 'high', 'service' => 'specific_document_age', 'params' => $params],
        ];
    }

    private function specificDocumentPosition(array $params): array
    {
        $doc = $this->findSpecificDocument($params);

        if (!$doc) {
            return $this->emptyResult($this->documentNotFoundAnswer($params), $params, 'specific_document_position', false);
        }

        $answer = $this->formatDocumentPositionAnswer($doc);

        return [
            'intent' => 'specific_document_position',
            'answer' => $answer,
            'data' => $this->formatDocuments(collect([$doc]), true),
            'link' => route('owner.dokumen', ['status' => 'all', 'search' => $doc->nomor_agenda]),
            'meta' => ['confidence' => 'high', 'service' => 'specific_document_position', 'params' => $params],
        ];
    }

    private function roleAverageDuration(array $params): array
    {
        $role = $this->canonicalHandler($params['handler'] ?: $this->extractRoleFromWorkflow($params['workflow_status'] ?? null) ?: 'pembayaran');
        $roleAliases = $role === 'team_verifikasi' ? ['team_verifikasi', 'verifikasi'] : [$role];

        $rows = DokumenRoleData::query()
            ->whereIn('role_code', $roleAliases)
            ->whereNotNull('received_at')
            ->whereNotNull('processed_at')
            ->get(['dokumen_id', 'role_code', 'received_at', 'processed_at']);

        if ($rows->isEmpty()) {
            return $this->emptyResult("Belum ada data durasi selesai untuk {$this->handlerLabel($role)}.", $params, 'role_average_duration');
        }

        $minutes = $rows->map(fn ($row) => Carbon::parse($row->received_at)->diffInMinutes(Carbon::parse($row->processed_at)));
        $averageMinutes = (int) round($minutes->avg());
        $fastest = (int) $minutes->min();
        $slowest = (int) $minutes->max();

        return [
            'intent' => 'role_average_duration',
            'answer' => "Rata-rata durasi dokumen selesai oleh {$this->handlerLabel($role)} adalah {$this->formatDuration($averageMinutes)} dari {$rows->count()} dokumen yang memiliki timestamp lengkap.",
            'data' => [
                'role' => $this->handlerLabel($role),
                'jumlah_dokumen_terhitung' => $rows->count(),
                'rata_rata' => $this->formatDuration($averageMinutes),
                'tercepat' => $this->formatDuration($fastest),
                'terlama' => $this->formatDuration($slowest),
            ],
            'link' => route('owner.dokumen', ['filter_pengurus' => $role]),
            'meta' => ['confidence' => 'high', 'service' => 'role_average_duration', 'params' => $params],
        ];
    }

    private function documentsByFilters(string $intent, array $params, int $limit): array
    {
        $query = $this->baseQuery();
        if ($intent === 'documents_by_keyword' && ($params['keyword_focus'] ?? false)) {
            $params['handler'] = null;
            $params['workflow_status'] = null;
        }
        $this->applyFilters($query, $params);

        $total = (clone $query)->count();
        $docs = $query
            ->orderByDesc('tanggal_masuk')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        if ($docs->isEmpty()) {
            return $this->emptyResult($this->formatNoDataAnswer($intent, $params), $params, $intent);
        }

        $answer = $this->formatDocumentListAnswer($total, $docs->count(), $params);

        return [
            'intent' => $intent,
            'answer' => $answer,
            'data' => $this->formatDocuments($docs, $intent === 'late_documents'),
            'link' => route('owner.dokumen', $this->linkParams($params)),
            'meta' => [
                'limited' => $total > $docs->count(),
                'total' => $total,
                'shown' => $docs->count(),
                'service' => 'documents_by_filters',
                'params' => $params,
            ],
        ];
    }

    private function documentsByAge(array $params, int $limit): array
    {
        $query = $this->baseQuery();
        $this->applyFilters($query, $params);

        if ($params['age_days_min'] !== null) {
            $query->where('tanggal_masuk', '<=', now()->subDays((int) $params['age_days_min']));
        }

        if ($params['age_days_max'] !== null) {
            $query->where('tanggal_masuk', '>=', now()->subDays((int) $params['age_days_max']));
        }

        $total = (clone $query)->count();
        $docs = $query->oldest('tanggal_masuk')->limit($limit)->get();

        if ($docs->isEmpty()) {
            return $this->emptyResult('Tidak ditemukan dokumen dengan umur yang dimaksud.', $params, 'documents_by_age');
        }

        return [
            'intent' => 'documents_by_age',
            'answer' => "Ditemukan {$total} dokumen dengan umur yang dimaksud. Berikut {$docs->count()} teratas.",
            'data' => $this->formatDocuments($docs, true),
            'link' => route('owner.dokumen', $this->linkParams($params)),
            'meta' => ['total' => $total, 'shown' => $docs->count(), 'service' => 'documents_by_age', 'params' => $params],
        ];
    }

    private function recentDocuments(array $params, int $limit): array
    {
        $recentParams = $params;
        $recentParams['date_range'] ??= [
            'type' => 'recent',
            'start' => now()->subDays(7)->startOfDay()->toDateTimeString(),
            'end' => now()->endOfDay()->toDateTimeString(),
        ];

        $query = $this->baseQuery();
        $this->applyFilters($query, $recentParams);
        $total = (clone $query)->count();
        $docs = $query->orderByDesc('tanggal_masuk')->limit($limit)->get();

        if ($docs->isEmpty()) {
            return $this->emptyResult('Tidak ditemukan dokumen yang masuk dalam 7 hari terakhir.', $recentParams, 'recent_documents');
        }

        return [
            'intent' => 'recent_documents',
            'answer' => "Ditemukan {$total} dokumen yang masuk akhir-akhir ini, berikut {$docs->count()} terbaru.",
            'data' => $this->formatDocuments($docs),
            'link' => route('owner.dokumen', $this->linkParams($recentParams)),
            'meta' => ['total' => $total, 'shown' => $docs->count(), 'service' => 'recent_documents', 'params' => $recentParams],
        ];
    }

    private function documentEntryDatesSummary(array $params, int $limit): array
    {
        $query = Dokumen::query();
        $this->applyFilters($query, $params);

        $rows = $query
            ->selectRaw('DATE(tanggal_masuk) as tanggal')
            ->selectRaw('COUNT(*) as total_dokumen')
            ->groupBy('tanggal')
            ->orderByDesc('tanggal')
            ->limit(min($limit, 20))
            ->get();

        if ($rows->isEmpty()) {
            return $this->emptyResult('Belum ada tanggal masuk dokumen yang bisa diringkas.', $params, 'document_entry_dates_summary');
        }

        return [
            'intent' => 'document_entry_dates_summary',
            'answer' => "Berikut tanggal masuk dokumen terbaru, dibatasi {$rows->count()} tanggal.",
            'data' => $rows->map(fn ($row) => [
                'tanggal_masuk' => Carbon::parse($row->tanggal)->format('d M Y'),
                'jumlah_dokumen' => (int) $row->total_dokumen,
            ])->values()->all(),
            'link' => route('owner.dokumen', $this->linkParams($params)),
            'meta' => ['service' => 'document_entry_dates_summary', 'params' => $params],
        ];
    }

    private function oldestDocuments(array $params, int $limit): array
    {
        $query = $this->baseQuery();
        $params['payment_statuses'] = $params['payment_statuses'] ?: ['belum_dibayar'];
        $this->applyFilters($query, $params);

        $docs = $query->oldest('tanggal_masuk')->limit($limit)->get();

        if ($docs->isEmpty()) {
            return $this->emptyResult('Tidak ditemukan dokumen aktif yang belum selesai/dibayar.', $params, 'oldest_documents');
        }

        return [
            'intent' => 'oldest_documents',
            'answer' => "Berikut dokumen yang paling lama diproses. Hasil dibatasi {$docs->count()} dokumen teratas.",
            'data' => $this->formatDocuments($docs, true),
            'link' => route('owner.dokumen', $this->linkParams($params)),
            'meta' => ['limited' => true, 'service' => 'oldest_documents', 'params' => $params],
        ];
    }

    private function lateDocuments(array $params, int $limit): array
    {
        $query = $this->baseQuery();
        $params['payment_statuses'] = $params['payment_statuses'] ?: ['belum_dibayar'];
        $this->applyFilters($query, $params);
        $query->where('tanggal_masuk', '<=', now()->subDays(3));

        $total = (clone $query)->count();
        $docs = $query->oldest('tanggal_masuk')->limit($limit)->get();

        if ($docs->isEmpty()) {
            return $this->emptyResult("Tidak ditemukan dokumen terlambat{$this->filterLabel($params)}.", $params, 'late_documents');
        }

        return [
            'intent' => 'late_documents',
            'answer' => "Ada {$total} dokumen yang tertahan lebih dari 3 hari{$this->filterLabel($params)}. Berikut {$docs->count()} teratas.",
            'data' => $this->formatDocuments($docs, true),
            'link' => route('owner.dokumen', array_merge($this->linkParams($params), ['status' => 'urgent'])),
            'meta' => ['total' => $total, 'shown' => $docs->count(), 'service' => 'late_documents', 'params' => $params],
        ];
    }

    private function topDepartments(string $mode, int $limit, array $params = []): array
    {
        $query = Dokumen::query();
        $this->applyFilters($query, $params);

        $rows = $query
            ->selectRaw("COALESCE(NULLIF(bagian, ''), NULLIF(nama_pengirim, ''), NULLIF(created_by, ''), 'Tidak diketahui') as bagian_label")
            ->selectRaw('COUNT(*) as total_dokumen')
            ->selectRaw('COALESCE(SUM(nilai_rupiah), 0) as total_nilai')
            ->groupBy('bagian_label')
            ->orderByDesc($mode === 'value' ? 'total_nilai' : 'total_dokumen')
            ->limit(min($limit, 10))
            ->get();

        if ($rows->isEmpty()) {
            return $this->emptyResult(
                $this->formatTopDepartmentsEmptyAnswer($mode, $params),
                $params,
                $mode === 'value' ? 'top_departments_by_value' : 'top_departments_by_count'
            );
        }

        return [
            'intent' => $mode === 'value' ? 'top_departments_by_value' : 'top_departments_by_count',
            'answer' => $this->formatTopDepartmentsAnswer($rows, $mode, $params),
            'data' => $rows->map(fn ($row) => [
                'bagian' => $row->bagian_label,
                'jumlah_dokumen' => (int) $row->total_dokumen,
                'total_nilai' => $this->formatMoney($row->total_nilai),
            ])->values()->all(),
            'link' => route('owner.dokumen', $this->linkParams($params)),
            'meta' => ['mode' => $mode, 'service' => 'top_departments', 'params' => $params],
        ];
    }

    private function cashbankSummary(array $params): array
    {
        $range = $params['date_range'] ?? null;
        $year = $range['year'] ?? now()->year;
        $monthFrom = $range['month'] ?? 1;
        $monthTo = $range['month'] ?? 12;

        $summary = app(CashBankReportService::class)->getRingkasanUtama((int) $year, (int) $monthFrom, (int) $monthTo);

        return [
            'intent' => 'cashbank_summary',
            'answer' => "Ringkasan Cash Bank {$year}: saldo {$this->formatMoney($summary['total_saldo'] ?? 0)}, penerimaan {$this->formatMoney($summary['total_penerimaan'] ?? 0)}, permintaan {$this->formatMoney($summary['total_permintaan'] ?? 0)}, dropping {$this->formatMoney($summary['total_dropping'] ?? 0)}.",
            'data' => [
                'tahun' => $year,
                'bulan_dari' => $monthFrom,
                'bulan_sampai' => $monthTo,
                'total_saldo' => $this->formatMoney($summary['total_saldo'] ?? 0),
                'total_penerimaan' => $this->formatMoney($summary['total_penerimaan'] ?? 0),
                'total_permintaan' => $this->formatMoney($summary['total_permintaan'] ?? 0),
                'total_dropping' => $this->formatMoney($summary['total_dropping'] ?? 0),
                'realisasi_pct' => $summary['realisasi_pct'] ?? 0,
            ],
            'link' => url('/owner/laporan-cash-bank'),
            'meta' => ['service' => 'cashbank_summary', 'params' => $params],
        ];
    }

    private function formatDocumentPositionAnswer(Dokumen $doc): string
    {
        $position = $this->handlerLabel($doc->current_handler);
        $paymentStatus = $this->paymentLabel($doc->status_pembayaran, $doc->tanggal_dibayar);

        return "Dokumen {$doc->nomor_agenda} saat ini berada di {$position} dengan status pembayaran {$paymentStatus}.";
    }

    private function formatDocumentListAnswer(int $total, int $shown, array $params): string
    {
        $criteria = $this->filterLabel($params);

        if ($total > $shown) {
            $remaining = max($total - $shown, 0);

            return "Ditemukan {$total} dokumen{$criteria}. Berikut {$shown} dokumen teratas. Masih ada {$remaining} dokumen lainnya yang bisa dilihat melalui daftar dokumen terkait.";
        }

        return "Ditemukan {$total} dokumen{$criteria}.";
    }

    private function formatNoDataAnswer(string $intent, array $params): string
    {
        if (($params['keyword'] ?? null) && in_array($intent, ['documents_by_keyword', 'specific_document_position', 'specific_document_payment_status'], true)) {
            return $this->documentNotFoundAnswer($params);
        }

        if ($params['date_range'] ?? null) {
            return 'Belum ada dokumen yang masuk' . $this->periodSentenceLabel($params) . '.';
        }

        if ($params['payment_statuses'] ?? []) {
            return 'Tidak ditemukan dokumen dengan status pembayaran ' . $this->paymentStatusListLabel($params['payment_statuses']) . $this->filterLabelWithout(['payment_statuses'], $params) . '.';
        }

        if ($params['department'] ?? null) {
            return 'Tidak ditemukan dokumen dari bagian ' . $params['department'] . $this->filterLabelWithout(['department'], $params) . '.';
        }

        if ($params['handler'] ?? null) {
            return 'Tidak ditemukan dokumen yang sedang ditangani oleh ' . $this->handlerLabel($params['handler']) . $this->filterLabelWithout(['handler'], $params) . '.';
        }

        if (($params['amount_min'] ?? null) || ($params['amount_max'] ?? null)) {
            return 'Tidak ada dokumen dengan kriteria nilai tersebut.';
        }

        return 'Tidak ditemukan dokumen yang sesuai dengan kriteria tersebut.';
    }

    private function formatTopDepartmentsAnswer(Collection $rows, string $mode, array $params): string
    {
        $top = $rows->first();
        $period = $this->periodSentenceLabel($params);
        $lines = $rows->take(5)->values()->map(function ($row, int $index) use ($mode) {
            $rank = $index + 1;
            $value = $mode === 'value'
                ? $this->formatMoney($row->total_nilai)
                : ((int) $row->total_dokumen) . ' dokumen';

            return "{$rank}. {$row->bagian_label} - {$value}";
        })->implode("\n");

        if ($mode === 'value') {
            $headline = "Bagian dengan total nilai dokumen terbesar{$period} adalah {$top->bagian_label} dengan nilai {$this->formatMoney($top->total_nilai)}.";
        } else {
            $headline = "Bagian yang paling banyak mengirim dokumen{$period} adalah {$top->bagian_label} dengan {$top->total_dokumen} dokumen.";
        }

        return "{$headline}\n\nBerikut ringkasannya:\n{$lines}";
    }

    private function formatTopDepartmentsEmptyAnswer(string $mode, array $params): string
    {
        $period = $this->periodSentenceLabel($params);

        if ($params['date_range'] ?? null) {
            $reason = $mode === 'value'
                ? 'sehingga belum ada nilai dokumen bagian yang bisa dirangking'
                : 'sehingga belum ada bagian yang bisa dirangking';

            return "Belum ada dokumen yang masuk{$period}, {$reason}.";
        }

        return $mode === 'value'
            ? 'Belum ada data nilai dokumen per bagian yang bisa dirangking.'
            : 'Belum ada data dokumen per bagian yang bisa dirangking.';
    }

    private function documentNotFoundAnswer(array $params, bool $fromContext = false): string
    {
        if ($fromContext && empty($params['keyword'])) {
            return 'Saya tidak menemukan lagi dokumen yang dimaksud dari konteks chat sebelumnya.';
        }

        if ($params['keyword'] ?? null) {
            return 'Saya tidak menemukan dokumen dengan nomor atau kata kunci "' . $params['keyword'] . '". Coba periksa kembali nomor agenda atau nomor SPP.';
        }

        return 'Saya tidak menemukan dokumen yang dimaksud. Coba sertakan nomor agenda atau nomor SPP agar pencarian lebih tepat.';
    }

    private function periodSentenceLabel(array $params): string
    {
        $range = $params['date_range'] ?? null;
        if (!$range) {
            return '';
        }

        if (($range['type'] ?? null) === 'date') {
            return ' pada tanggal ' . $this->formatIndonesianDate(Carbon::parse($range['date']));
        }

        if (($range['type'] ?? null) === 'month') {
            return ' pada bulan ' . $this->formatIndonesianMonth((int) $range['month']) . ' ' . $range['year'];
        }

        if (($range['type'] ?? null) === 'year') {
            return ' pada tahun ' . $range['year'];
        }

        return ' pada periode tersebut';
    }

    private function filterLabelWithout(array $keys, array $params): string
    {
        foreach ($keys as $key) {
            $params[$key] = null;
        }

        return $this->filterLabel($params, !in_array('payment_statuses', $keys, true));
    }

    private function applyFilters(Builder $query, array $params, bool $includePayment = true): void
    {
        if ($includePayment && $params['payment_statuses'] !== []) {
            $this->applyPaymentStatuses($query, $params['payment_statuses']);
        }

        if ($params['date_range']) {
            $this->applyDateRange($query, $params['date_range']);
        }

        if ($params['department']) {
            $department = $params['department'];
            $query->where(function (Builder $subQuery) use ($department) {
                $subQuery->where('bagian', 'like', "%{$department}%")
                    ->orWhere('nama_pengirim', 'like', "%{$department}%")
                    ->orWhere('created_by', 'like', "%{$department}%");
            });
        }

        if ($params['handler']) {
            $handler = $this->canonicalHandler($params['handler']);
            $handlerAliases = array_unique([$handler, $params['handler']]);
            if ($handler === 'team_verifikasi') {
                $handlerAliases[] = 'verifikasi';
            }
            if ($handler === 'akutansi') {
                $handlerAliases[] = 'akuntansi';
            }
            $query->whereIn('current_handler', $handlerAliases);
        }

        if ($params['workflow_status']) {
            $this->applyWorkflowStatus($query, $params['workflow_status']);
        }

        if ($params['amount_min'] !== null) {
            $query->where('nilai_rupiah', '>=', $params['amount_min']);
        }

        if ($params['amount_max'] !== null) {
            $query->where('nilai_rupiah', '<=', $params['amount_max']);
        }

        if ($params['keyword']) {
            $keyword = $params['keyword'];
            $query->where(function (Builder $subQuery) use ($keyword) {
                $subQuery->where('nomor_agenda', 'like', "%{$keyword}%")
                    ->orWhere('nomor_spp', 'like', "%{$keyword}%")
                    ->orWhere('uraian_spp', 'like', "%{$keyword}%")
                    ->orWhere('dibayar_kepada', 'like', "%{$keyword}%")
                    ->orWhere('nama_pengirim', 'like', "%{$keyword}%")
                    ->orWhere('bagian', 'like', "%{$keyword}%")
                    ->orWhere('keterangan', 'like', "%{$keyword}%");
            });
        }
    }

    private function applyPaymentStatuses(Builder $query, array $statuses): void
    {
        $statuses = array_values(array_unique($statuses));

        $query->where(function (Builder $paymentQuery) use ($statuses) {
            foreach ($statuses as $index => $status) {
                $method = $index === 0 ? 'where' : 'orWhere';
                $paymentQuery->{$method}(function (Builder $statusQuery) use ($status) {
                    match ($status) {
                        'sudah_dibayar' => $this->applyPaidCondition($statusQuery),
                        'siap_dibayar' => $this->applyReadyToPayCondition($statusQuery),
                        'belum_dibayar' => $this->applyUnpaidCondition($statusQuery),
                        default => null,
                    };
                });
            }
        });
    }

    private function applyPaidCondition(Builder $query): void
    {
        $query->where(function (Builder $subQuery) {
            $subQuery->whereNotNull('tanggal_dibayar')
                ->orWhereIn('status_pembayaran', ['sudah_dibayar', 'SUDAH_DIBAYAR', 'SUDAH DIBAYAR'])
                ->orWhereIn('status', ['completed', 'selesai']);
        });
    }

    private function applyUnpaidCondition(Builder $query): void
    {
        $query->where(function (Builder $subQuery) {
            $subQuery->whereNull('tanggal_dibayar')
                ->where(function (Builder $paymentSubQuery) {
                    $paymentSubQuery->whereNull('status_pembayaran')
                        ->orWhereNotIn('status_pembayaran', ['sudah_dibayar', 'SUDAH_DIBAYAR', 'SUDAH DIBAYAR']);
                });
        });
    }

    private function applyReadyToPayCondition(Builder $query): void
    {
        $query->where(function (Builder $subQuery) {
            $this->applyUnpaidCondition($subQuery);
            $subQuery->where(function (Builder $readyQuery) {
                $readyQuery->whereIn('status_pembayaran', ['siap_dibayar', 'siap_bayar', 'SIAP_DIBAYAR', 'SIAP DIBAYAR', 'pending', 'Pending'])
                    ->orWhere('current_handler', 'pembayaran')
                    ->orWhere('status', 'sent_to_pembayaran');
            });
        });
    }

    private function applyDateRange(Builder $query, array $range): void
    {
        if (($range['type'] ?? null) === 'date') {
            $query->whereDate('tanggal_masuk', $range['date']);
            return;
        }

        if (($range['type'] ?? null) === 'year') {
            $query->whereYear('tanggal_masuk', $range['year']);
            return;
        }

        if (isset($range['start'], $range['end'])) {
            $query->whereBetween('tanggal_masuk', [$range['start'], $range['end']]);
        }
    }

    private function applyWorkflowStatus(Builder $query, string $workflowStatus): void
    {
        $statusValues = match ($workflowStatus) {
            'pending' => ['waiting_reviewer_approval', 'pending_approval_perpajakan', 'pending_approval_akutansi', 'pending_approval_pembayaran', 'menunggu_di_approve'],
            'processing' => ['sedang diproses'],
            'sent_to_verification' => ['sent_to_team_verifikasi', 'waiting_reviewer_approval'],
            'sent_to_tax' => ['sent_to_perpajakan', 'pending_approval_perpajakan'],
            'sent_to_accounting' => ['sent_to_akutansi', 'pending_approval_akutansi'],
            'sent_to_payment' => ['sent_to_pembayaran', 'pending_approval_pembayaran'],
            'completed' => ['completed', 'selesai'],
            'returned' => ['returned_to_operator', 'returned_to_verifikasi', 'returned_to_team_verifikasi', 'returned_to_department'],
            default => [$workflowStatus],
        };

        $query->whereIn('status', $statusValues);
    }

    private function baseQuery(): Builder
    {
        return Dokumen::query()->select([
            'id',
            'nomor_agenda',
            'nomor_spp',
            'uraian_spp',
            'nilai_rupiah',
            'bagian',
            'nama_pengirim',
            'created_by',
            'status',
            'status_pembayaran',
            'current_handler',
            'tanggal_masuk',
            'tanggal_dibayar',
            'dibayar_kepada',
            'keterangan',
        ]);
    }

    private function findSpecificDocument(array $params): ?Dokumen
    {
        if ($params['context_document_id'] ?? null) {
            return $this->baseQuery()->where('id', $params['context_document_id'])->first();
        }

        if (!($params['keyword'] ?? null)) {
            return null;
        }

        $keyword = $params['keyword'];

        return $this->baseQuery()
            ->where(function (Builder $query) use ($keyword) {
                $query->where('nomor_agenda', 'like', "%{$keyword}%")
                    ->orWhere('nomor_spp', 'like', "%{$keyword}%")
                    ->orWhere('uraian_spp', 'like', "%{$keyword}%");
            })
            ->orderByDesc('tanggal_masuk')
            ->first();
    }

    private function extractRoleFromWorkflow(?string $workflowStatus): ?string
    {
        return match ($workflowStatus) {
            'sent_to_verification' => 'team_verifikasi',
            'sent_to_tax' => 'perpajakan',
            'sent_to_accounting' => 'akutansi',
            'sent_to_payment' => 'pembayaran',
            default => null,
        };
    }

    private function extractDateRange(string $text): ?array
    {
        if (str_contains($text, 'hari ini')) {
            return $this->dateRange(today(), 'date');
        }

        if (str_contains($text, 'kemarin')) {
            return $this->dateRange(today()->subDay(), 'date');
        }

        if (str_contains($text, 'bulan ini')) {
            $date = now();
            return [
                'type' => 'month',
                'month' => $date->month,
                'year' => $date->year,
                'start' => $date->copy()->startOfMonth()->toDateTimeString(),
                'end' => $date->copy()->endOfMonth()->toDateTimeString(),
            ];
        }

        if (str_contains($text, 'tahun ini')) {
            return ['type' => 'year', 'year' => now()->year];
        }

        if (preg_match('/\b(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})\b/', $text, $matches)) {
            return $this->dateRange(Carbon::createFromDate((int) $matches[3], (int) $matches[2], (int) $matches[1]), 'date');
        }

        if (preg_match('/\b(\d{1,2})\s+([a-z]+)\s+(\d{4})\b/u', $text, $matches)) {
            $month = self::MONTHS[$matches[2]] ?? null;
            if ($month) {
                return $this->dateRange(Carbon::createFromDate((int) $matches[3], $month, (int) $matches[1]), 'date');
            }
        }

        if (preg_match('/\b(?:bulan\s+)?([a-z]+)\s+(\d{4})\b/u', $text, $matches)) {
            $month = self::MONTHS[$matches[1]] ?? null;
            if ($month) {
                $date = Carbon::createFromDate((int) $matches[2], $month, 1);
                return [
                    'type' => 'month',
                    'month' => $month,
                    'year' => (int) $matches[2],
                    'start' => $date->copy()->startOfMonth()->toDateTimeString(),
                    'end' => $date->copy()->endOfMonth()->toDateTimeString(),
                ];
            }
        }

        return null;
    }

    private function dateRange(Carbon $date, string $type): array
    {
        return [
            'type' => $type,
            'date' => $date->toDateString(),
            'start' => $date->copy()->startOfDay()->toDateTimeString(),
            'end' => $date->copy()->endOfDay()->toDateTimeString(),
        ];
    }

    private function extractPaymentStatuses(string $text): array
    {
        $statuses = [];

        if ($this->containsAny($text, ['siap dibayar', 'siap bayar', 'pending pembayaran', 'menunggu pembayaran'])) {
            $statuses[] = 'siap_dibayar';
        }

        if ($this->containsAny($text, ['sudah dibayar', 'sudah bayar', 'telah dibayar', 'lunas', 'terbayar'])) {
            $statuses[] = 'sudah_dibayar';
        }

        if ($this->containsAny($text, ['belum dibayar', 'belum bayar', 'belum lunas', 'tidak dibayar'])) {
            $statuses[] = 'belum_dibayar';
        }

        if (preg_match('/sudah\s+(?:dan|atau)\s+belum\s+dibayar/u', $text) || preg_match('/belum\s+(?:dan|atau)\s+sudah\s+dibayar/u', $text)) {
            $statuses[] = 'sudah_dibayar';
            $statuses[] = 'belum_dibayar';
        }

        return array_values(array_unique($statuses));
    }

    private function extractWorkflowStatus(string $text): ?string
    {
        foreach (self::WORKFLOW_STATUS_ALIASES as $status => $aliases) {
            if ($this->containsAny($text, $aliases)) {
                return $status;
            }
        }

        return null;
    }

    private function extractDepartment(string $text): ?string
    {
        if (preg_match('/\b(SKH|TAN|DPM|SDM|TEP|AKN|KPL|PMO|PTI)\b/i', $text, $matches)) {
            return strtoupper($matches[1]);
        }

        if (preg_match('/bagian\s+(?:apa|mana)\b/i', $text)) {
            return null;
        }

        if (preg_match('/bagian\s+([a-z0-9\s\-]{2,40})/i', $text, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    private function extractHandler(string $text): ?string
    {
        foreach (array_keys(self::HANDLER_LABELS) as $handler) {
            if (str_contains($text, $handler)) {
                if ($handler === 'bagian' && !$this->containsAny($text, ['pengurus bagian', 'handler bagian', 'di bagian operator'])) {
                    continue;
                }

                return $this->canonicalHandler($handler);
            }
        }

        return null;
    }

    private function extractAgeDays(string $text, string $bound): ?int
    {
        if (!preg_match('/umur\s+(?:dokumen\s+)?(?:di\s*atas|lebih dari|>=|minimal|min)?\s*(\d+)\s*(hari|minggu|bulan|tahun)/u', $text, $matches)
            && !preg_match('/(\d+)\s*(hari|minggu|bulan|tahun)\s+(?:terakhir|lamanya|usianya|umur)/u', $text, $matches)) {
            return null;
        }

        $days = match ($matches[2]) {
            'tahun' => (int) $matches[1] * 365,
            'bulan' => (int) $matches[1] * 30,
            'minggu' => (int) $matches[1] * 7,
            default => (int) $matches[1],
        };

        return $bound === 'min' ? $days : null;
    }

    private function extractAmountBound(string $text, string $bound): ?float
    {
        if (preg_match('/antara\s+(?:rp\.?\s*)?([\d\.\,]+)\s*(juta|miliar|ribu)?\s+(?:dan|sampai|-)\s+(?:rp\.?\s*)?([\d\.\,]+)\s*(juta|miliar|ribu)?/u', $text, $matches)) {
            return $bound === 'min'
                ? $this->parseAmount($matches[1], $matches[2] ?? '')
                : $this->parseAmount($matches[3], $matches[4] ?? ($matches[2] ?? ''));
        }

        $patterns = $bound === 'min'
            ? ['/(?:di\s*atas|lebih dari|>=|minimal|min|lebih besar dari)\s*(?:rp\.?\s*)?([\d\.\,]+)\s*(juta|miliar|ribu)?/u']
            : ['/(?:di\s*bawah|kurang dari|<=|maksimal|max|lebih kecil dari)\s*(?:rp\.?\s*)?([\d\.\,]+)\s*(juta|miliar|ribu)?/u'];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return $this->parseAmount($matches[1], $matches[2] ?? '');
            }
        }

        return null;
    }

    private function parseAmount(string $rawNumber, string $unit = ''): float
    {
        $number = (float) str_replace([',', '.'], ['', ''], $rawNumber);

        return match ($unit) {
            'miliar' => $number * 1_000_000_000,
            'juta' => $number * 1_000_000,
            'ribu' => $number * 1_000,
            default => $number,
        };
    }

    private function extractKeyword(string $message, string $text): ?string
    {
        if (preg_match('/["“”](.{3,160})["“”]/u', $message, $matches)) {
            return $this->cleanKeyword($matches[1]);
        }

        if (preg_match('/nomor\s+spp\s+(.{3,80})$/iu', $message, $matches)) {
            return $this->cleanKeyword($matches[1]);
        }

        if (preg_match('/nomor\s+agenda\s+(.{3,40})$/iu', $message, $matches)) {
            return $this->cleanKeyword($matches[1]);
        }

        if (preg_match('/\b\d{2,6}[_\/\-]\d{4}\b/u', $message, $matches)) {
            return $matches[0];
        }

        if (preg_match('/(?:cari|search|kata kunci|mengandung|berisi)\s+(.{3,80})$/iu', $message, $matches)) {
            return $this->cleanKeyword($matches[1]);
        }

        return null;
    }

    private function isKeywordFocused(string $message, string $text): bool
    {
        return (bool) preg_match('/["“”].{3,160}["“”]/u', $message)
            || $this->containsAny($text, ['uraian', 'kata kunci', 'mengandung', 'berisi', 'nomor agenda dari dokumen']);
    }

    private function cleanKeyword(string $keyword): ?string
    {
        $keyword = preg_replace('/\s+(apakah|apa|yang|statusnya|status|sudah|belum|siap)\b.*$/iu', '', $keyword) ?: $keyword;
        $keyword = trim($keyword, " \t\n\r\0\x0B*?.,:;\"'“”");

        return strlen($keyword) >= 3 ? $keyword : null;
    }

    private function formatDocuments(Collection $docs, bool $includeAge = false): array
    {
        return $docs->map(function (Dokumen $doc) use ($includeAge) {
            $tanggalMasuk = $doc->tanggal_masuk ? Carbon::parse($doc->tanggal_masuk) : null;

            return array_filter([
                'id' => $doc->id,
                'nomor_agenda' => $doc->nomor_agenda ?: '-',
                'nomor_spp' => $doc->nomor_spp ?: '-',
                'uraian' => (string) str($doc->uraian_spp ?: '-')->limit(130),
                'nilai' => $this->formatMoney($doc->nilai_rupiah),
                'bagian' => $doc->bagian ?: ($doc->nama_pengirim ?: ($doc->created_by ?: '-')),
                'status' => $this->workflowLabel($doc->status),
                'status_pembayaran' => $this->paymentLabel($doc->status_pembayaran, $doc->tanggal_dibayar),
                'pengurus' => $this->handlerLabel($doc->current_handler),
                'tanggal_masuk' => $tanggalMasuk ? $tanggalMasuk->format('d M Y H:i') : '-',
                'umur' => $includeAge && $tanggalMasuk ? $tanggalMasuk->diffForHumans(null, true) : null,
            ], fn ($value) => $value !== null);
        })->values()->all();
    }

    private function clarification(string $message, array $params): array
    {
        $normalized = $this->normalize($message);
        $suggestion = "Saya belum bisa memastikan maksud pertanyaan. Apakah yang Anda maksud:\n"
            . "1. Dokumen berdasarkan status pembayaran?\n"
            . "2. Dokumen berdasarkan tanggal masuk?\n"
            . "3. Dokumen berdasarkan bagian/unit?\n"
            . "4. Dokumen berdasarkan nilai minimum/maksimum?\n"
            . "5. Dokumen berdasarkan nomor SPP atau nomor agenda?";

        if (str_contains($normalized, 'bayar')) {
            $suggestion = "Saya belum bisa memastikan status pembayaran yang Anda maksud. Apakah yang Anda maksud:\n"
                . "1. Dokumen siap dibayar?\n"
                . "2. Dokumen belum dibayar?\n"
                . "3. Dokumen sudah dibayar?";
        } elseif ($this->containsAny($normalized, ['besar', 'terbesar', 'nilai'])) {
            $suggestion = "Saya belum bisa memastikan batas nilai yang Anda maksud. Apakah yang Anda maksud:\n"
                . "1. Dokumen dengan nilai terbesar?\n"
                . "2. Dokumen di atas nominal tertentu, misalnya di atas 100 juta?\n"
                . "3. Top bagian berdasarkan total nilai dokumen?";
        } elseif ($this->containsAny($normalized, ['tanggal', 'masuk', 'bulan', 'hari'])) {
            $suggestion = "Saya belum bisa memastikan periode tanggal yang Anda maksud. Apakah yang Anda maksud:\n"
                . "1. Dokumen masuk hari ini?\n"
                . "2. Dokumen masuk tanggal tertentu, misalnya 5 Mei 2026?\n"
                . "3. Dokumen masuk bulan tertentu, misalnya Mei 2026?";
        }

        return [
            'intent' => 'clarification',
            'answer' => $suggestion . "\n\nContoh pertanyaan yang bisa langsung dijawab: \"berikan saya dokumen siap dibayar\" atau \"dokumen belum dibayar di atas 100 juta\".",
            'data' => [],
            'link' => route('owner.dokumen'),
            'meta' => ['confidence' => 'low', 'service' => 'clarification', 'params' => $params],
        ];
    }

    private function emptyResult(string $answer, array $params = [], string $intent = 'empty', bool $includeLink = false): array
    {
        $result = [
            'intent' => $intent,
            'answer' => $answer,
            'data' => [],
            'meta' => ['service' => 'empty_result', 'params' => $params],
        ];

        if ($includeLink) {
            $result['link'] = route('owner.dokumen', $this->linkParams($params));
        }

        return $result;
    }

    private function linkParams(array $params): array
    {
        $query = ['status' => 'all'];

        if (count($params['payment_statuses'] ?? []) === 1) {
            $query['filter_status_pembayaran'] = $params['payment_statuses'][0];
        }

        if (($params['date_range']['type'] ?? null) === 'date') {
            $query['filter_tanggal_masuk'] = $params['date_range']['date'];
        }

        if ($params['department'] ?? null) {
            $query['filter_bagian'] = $params['department'];
        }

        if ($params['handler'] ?? null) {
            $query['filter_pengurus'] = $this->canonicalHandler($params['handler']);
        }

        if ($params['amount_min'] ?? null) {
            $query['filter_nilai_min'] = (int) $params['amount_min'];
        }

        if ($params['amount_max'] ?? null) {
            $query['filter_nilai_max'] = (int) $params['amount_max'];
        }

        if ($params['keyword'] ?? null) {
            $query['search'] = $params['keyword'];
        }

        return array_filter($query, fn ($value) => $value !== null && $value !== '');
    }

    private function filterLabel(array $params, bool $includeStatus = true): string
    {
        $parts = [];

        if ($includeStatus && ($params['payment_statuses'] ?? [])) {
            $parts[] = 'status ' . $this->paymentStatusListLabel($params['payment_statuses']);
        }

        if ($params['date_range'] ?? null) {
            $parts[] = $this->dateRangeLabel($params['date_range']);
        }

        if ($params['department'] ?? null) {
            $parts[] = 'bagian ' . $params['department'];
        }

        if ($params['handler'] ?? null) {
            $parts[] = 'pengurus ' . $this->handlerLabel($params['handler']);
        }

        if ($params['amount_min'] ?? null) {
            $parts[] = 'nilai minimal ' . $this->formatMoney($params['amount_min']);
        }

        if ($params['amount_max'] ?? null) {
            $parts[] = 'nilai maksimal ' . $this->formatMoney($params['amount_max']);
        }

        if ($params['keyword'] ?? null) {
            $parts[] = 'kata kunci "' . $params['keyword'] . '"';
        }

        return $parts ? ' dengan ' . implode(', ', $parts) : '';
    }

    private function paymentStatusListLabel(array $statuses): string
    {
        return implode(' dan ', array_map(fn ($status) => match ($status) {
            'siap_dibayar' => 'Siap Dibayar',
            'sudah_dibayar' => 'Sudah Dibayar',
            'belum_dibayar' => 'Belum Dibayar',
            default => (string) str($status)->replace('_', ' ')->title(),
        }, $statuses));
    }

    private function dateRangeLabel(array $range): string
    {
        if (($range['type'] ?? null) === 'date') {
            return 'tanggal masuk ' . $this->formatIndonesianDate(Carbon::parse($range['date']));
        }

        if (($range['type'] ?? null) === 'month') {
            return 'bulan ' . $this->formatIndonesianMonth((int) $range['month']) . ' ' . $range['year'];
        }

        if (($range['type'] ?? null) === 'year') {
            return 'tahun ' . $range['year'];
        }

        return 'periode tertentu';
    }

    private function paymentLabel(?string $status, $tanggalDibayar): string
    {
        if ($tanggalDibayar || in_array((string) $status, ['sudah_dibayar', 'SUDAH_DIBAYAR', 'SUDAH DIBAYAR'], true)) {
            return 'Sudah Dibayar';
        }

        if (in_array((string) $status, ['siap_dibayar', 'siap_bayar', 'SIAP_DIBAYAR', 'SIAP DIBAYAR', 'pending', 'Pending'], true)) {
            return 'Siap Dibayar';
        }

        return 'Belum Dibayar';
    }

    private function workflowLabel(?string $status): string
    {
        return match ($status) {
            'sent_to_team_verifikasi', 'waiting_reviewer_approval' => 'Menunggu Verifikasi',
            'sent_to_perpajakan', 'pending_approval_perpajakan' => 'Perpajakan',
            'sent_to_akutansi', 'pending_approval_akutansi' => 'Akuntansi',
            'sent_to_pembayaran', 'pending_approval_pembayaran' => 'Pembayaran',
            'completed', 'selesai' => 'Selesai',
            'returned_to_operator', 'returned_to_verifikasi', 'returned_to_team_verifikasi', 'returned_to_department' => 'Dikembalikan',
            default => $status ?: '-',
        };
    }

    private function handlerLabel(?string $handler): string
    {
        $key = strtolower((string) $handler);
        return self::HANDLER_LABELS[$key] ?? ($handler ? (string) str($handler)->replace('_', ' ')->title() : '-');
    }

    private function canonicalHandler(string $handler): string
    {
        $handler = strtolower($handler);

        return match ($handler) {
            'verifikasi' => 'team_verifikasi',
            'akuntansi' => 'akutansi',
            'bagian' => 'operator',
            default => $handler,
        };
    }

    private function hasDocumentFilters(array $params): bool
    {
        return (bool) (
            ($params['date_range'] ?? null)
            || ($params['payment_statuses'] ?? [])
            || ($params['workflow_status'] ?? null)
            || ($params['department'] ?? null)
            || ($params['handler'] ?? null)
            || ($params['amount_min'] ?? null)
            || ($params['amount_max'] ?? null)
            || ($params['age_days_min'] ?? null)
            || ($params['age_days_max'] ?? null)
            || ($params['keyword'] ?? null)
        );
    }

    private function containsAny(string $text, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($text, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function isGreeting(string $text): bool
    {
        return in_array($text, [
            'halo',
            'hallo',
            'hello',
            'hai',
            'hi',
            'pagi',
            'siang',
            'sore',
            'malam',
            'assalamualaikum',
            'asalamualaikum',
        ], true);
    }

    private function normalize(string $text): string
    {
        return (string) str($text)->lower()->replaceMatches('/\s+/', ' ')->trim();
    }

    private function formatMoney($value): string
    {
        return 'Rp ' . number_format((float) $value, 0, ',', '.');
    }

    private function formatDuration(int $minutes): string
    {
        if ($minutes < 60) {
            return "{$minutes} menit";
        }

        $days = intdiv($minutes, 1440);
        $hours = intdiv($minutes % 1440, 60);
        $remainingMinutes = $minutes % 60;
        $parts = [];

        if ($days > 0) {
            $parts[] = "{$days} hari";
        }

        if ($hours > 0) {
            $parts[] = "{$hours} jam";
        }

        if ($remainingMinutes > 0 && $days === 0) {
            $parts[] = "{$remainingMinutes} menit";
        }

        return implode(' ', $parts) ?: '0 menit';
    }

    private function formatIndonesianDate(Carbon $date): string
    {
        return $date->format('d') . ' ' . $this->formatIndonesianMonth((int) $date->format('n')) . ' ' . $date->format('Y');
    }

    private function formatIndonesianMonth(int $month): string
    {
        return [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ][$month] ?? (string) $month;
    }

    private function logDecision(string $question, string $intent, array $params, array $result): void
    {
        Log::info('Virtual Assistant intent resolved', [
            'question' => (string) str($question)->limit(300),
            'intent' => $intent,
            'params' => $params,
            'service' => data_get($result, 'meta.service'),
            'result_count' => is_array($result['data'] ?? null) ? count($result['data']) : 0,
            'total' => data_get($result, 'meta.total'),
            'ai_provider_configured' => config('asisten_virtual.provider'),
        ]);
    }
}

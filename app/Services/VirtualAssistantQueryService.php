<?php

namespace App\Services;

use App\Models\Dokumen;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

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
        'team_verifikasi' => 'Tim Verifikasi',
        'verifikasi' => 'Tim Verifikasi',
        'perpajakan' => 'Perpajakan',
        'akutansi' => 'Akuntansi',
        'akuntansi' => 'Akuntansi',
        'pembayaran' => 'Pembayaran',
        'bagian' => 'Bagian',
    ];

    public function answer(string $message): array
    {
        $normalized = $this->normalize($message);
        $limit = (int) config('asisten_virtual.limits.default_result_limit', 15);

        if ($this->isGreeting($normalized)) {
            return $this->greeting();
        }

        if ($date = $this->extractDate($normalized)) {
            if ($this->containsAny($normalized, ['masuk', 'tanggal masuk', 'dokumen apa', 'dokumen yang masuk', 'hari ini'])) {
                return $this->documentsByDate($date, $limit);
            }
        }

        if ($this->containsAny($normalized, ['top bagian', 'bagian mana', 'paling banyak mengirim', 'berdasarkan nilai', 'bagian terbesar'])) {
            return $this->topDepartments($this->containsAny($normalized, ['nilai', 'rupiah']) ? 'value' : 'count', $limit);
        }

        if ($this->containsAny($normalized, ['total nilai']) && $this->containsAny($normalized, ['sudah dibayar', 'dibayar'])) {
            return $this->paidSummary($this->extractMonthYear($normalized));
        }

        if ($this->containsAny($normalized, ['belum dibayar', 'pending pembayaran', 'belum bayar'])) {
            $amount = $this->extractAmount($normalized);
            if ($this->containsAny($normalized, ['total', 'berapa'])) {
                return $this->unpaidSummary($this->extractMonthYear($normalized), $amount);
            }

            return $this->unpaidDocuments($this->extractMonthYear($normalized), $amount, $limit);
        }

        if ($this->containsAny($normalized, ['total dokumen', 'jumlah dokumen', 'berapa dokumen', 'berapa total dokumen', 'seluruh dokumen'])) {
            return $this->documentSummary();
        }

        if ($this->containsAny($normalized, ['paling lama', 'terlama', 'lama diproses', 'umur dokumen'])) {
            return $this->oldestDocuments($limit);
        }

        if ($this->containsAny($normalized, ['tertahan', 'terlambat', 'terlalu lama'])) {
            $handler = $this->extractHandler($normalized) ?? ($this->containsAny($normalized, ['operator']) ? 'operator' : null);
            return $this->lateDocuments($handler, $limit);
        }

        if (($department = $this->extractDepartment($message)) && $this->containsAny($normalized, ['belum selesai', 'belum tuntas', 'pending', 'belum dibayar'])) {
            return $this->unfinishedDepartmentDocuments($department, $limit);
        }

        return [
            'intent' => 'clarification',
            'answer' => 'Saya belum bisa memastikan maksud pertanyaan tersebut. Coba sertakan kriteria yang lebih spesifik, misalnya tanggal masuk, status pembayaran, bagian, nilai minimum, atau pengurus dokumen.',
            'data' => [],
            'link' => route('owner.dokumen'),
            'meta' => ['confidence' => 'low'],
        ];
    }

    private function greeting(): array
    {
        return [
            'intent' => 'greeting',
            'answer' => 'Halo. Saya siap membantu membaca data Agenda Online, misalnya total dokumen, dokumen belum dibayar, dokumen masuk tanggal tertentu, dokumen terlambat, atau top bagian berdasarkan nilai dokumen.',
            'data' => [
                'contoh_pertanyaan' => [
                    'Berapa total dokumen?',
                    'Total belum dibayar bulan ini',
                    'Dokumen apa saja yang masuk hari ini?',
                    'Dokumen yang belum dibayar di atas 100 juta',
                    'Bagian mana yang paling banyak mengirim dokumen?',
                ],
            ],
            'link' => route('owner.dokumen'),
            'meta' => ['confidence' => 'high'],
        ];
    }

    private function documentSummary(): array
    {
        $totalDocuments = Dokumen::query()->count();
        $totalValue = Dokumen::query()->sum('nilai_rupiah');
        $paidCount = (clone $this->paidQuery())->count();
        $paidValue = (clone $this->paidQuery())->sum('nilai_rupiah');
        $unpaidCount = (clone $this->unpaidQuery())->count();
        $unpaidValue = (clone $this->unpaidQuery())->sum('nilai_rupiah');

        return [
            'intent' => 'document_summary',
            'answer' => "Total seluruh dokumen saat ini: {$totalDocuments} dokumen dengan nilai {$this->formatMoney($totalValue)}.",
            'data' => [
                'total_dokumen' => $totalDocuments,
                'total_nilai' => $this->formatMoney($totalValue),
                'sudah_dibayar' => [
                    'jumlah_dokumen' => $paidCount,
                    'total_nilai' => $this->formatMoney($paidValue),
                ],
                'belum_dibayar' => [
                    'jumlah_dokumen' => $unpaidCount,
                    'total_nilai' => $this->formatMoney($unpaidValue),
                ],
            ],
            'link' => route('owner.dokumen'),
            'meta' => ['confidence' => 'high'],
        ];
    }

    private function documentsByDate(Carbon $date, int $limit): array
    {
        $docs = $this->baseQuery()
            ->whereDate('tanggal_masuk', $date->toDateString())
            ->latest('tanggal_masuk')
            ->limit($limit)
            ->get();

        if ($docs->isEmpty()) {
            return $this->emptyResult("Tidak ditemukan dokumen yang masuk pada {$this->formatDate($date)}.", [
                'filter_tanggal_masuk' => $date->toDateString(),
            ]);
        }

        return [
            'intent' => 'documents_by_date',
            'answer' => "Ditemukan {$docs->count()} dokumen yang masuk pada {$this->formatDate($date)}.",
            'data' => $this->formatDocuments($docs),
            'link' => route('owner.dokumen', ['status' => 'all', 'filter_tanggal_masuk' => $date->toDateString()]),
            'meta' => ['date' => $date->toDateString(), 'limited' => $docs->count() >= $limit],
        ];
    }

    private function unpaidSummary(?array $monthYear, ?float $minimumAmount): array
    {
        $query = $this->unpaidQuery();
        $this->applyMonthYear($query, $monthYear);
        if ($minimumAmount !== null) {
            $query->where('nilai_rupiah', '>=', $minimumAmount);
        }

        $count = (clone $query)->count();
        $total = (clone $query)->sum('nilai_rupiah');
        $period = $this->periodLabel($monthYear);

        return [
            'intent' => 'unpaid_summary',
            'answer' => "Total dokumen belum dibayar{$period}: {$count} dokumen dengan nilai {$this->formatMoney($total)}.",
            'data' => [
                'jumlah_dokumen' => $count,
                'total_nilai' => $this->formatMoney($total),
                'nilai_minimum' => $minimumAmount ? $this->formatMoney($minimumAmount) : null,
            ],
            'link' => route('owner.dokumen', array_filter([
                'status' => 'all',
                'filter_status_pembayaran' => 'belum_dibayar',
                'filter_nilai_min' => $minimumAmount ? (int) $minimumAmount : null,
            ])),
            'meta' => ['period' => $monthYear],
        ];
    }

    private function unpaidDocuments(?array $monthYear, ?float $minimumAmount, int $limit): array
    {
        $query = $this->unpaidQuery();
        $this->applyMonthYear($query, $monthYear);
        if ($minimumAmount !== null) {
            $query->where('nilai_rupiah', '>=', $minimumAmount);
        }

        $docs = $query->orderByDesc('nilai_rupiah')->limit($limit)->get();

        if ($docs->isEmpty()) {
            return $this->emptyResult('Tidak ditemukan dokumen belum dibayar dengan kriteria tersebut.', [
                'filter_status_pembayaran' => 'belum_dibayar',
            ]);
        }

        $period = $this->periodLabel($monthYear);
        $minText = $minimumAmount ? ' di atas ' . $this->formatMoney($minimumAmount) : '';

        return [
            'intent' => 'unpaid_documents',
            'answer' => "Berikut {$docs->count()} dokumen belum dibayar{$minText}{$period}.",
            'data' => $this->formatDocuments($docs),
            'link' => route('owner.dokumen', array_filter([
                'status' => 'all',
                'filter_status_pembayaran' => 'belum_dibayar',
                'filter_nilai_min' => $minimumAmount ? (int) $minimumAmount : null,
            ])),
            'meta' => ['limited' => $docs->count() >= $limit],
        ];
    }

    private function paidSummary(?array $monthYear): array
    {
        $query = $this->paidQuery();
        $this->applyMonthYear($query, $monthYear, 'tanggal_dibayar');

        $count = (clone $query)->count();
        $total = (clone $query)->sum('nilai_rupiah');

        return [
            'intent' => 'paid_summary',
            'answer' => "Total dokumen sudah dibayar{$this->periodLabel($monthYear)}: {$count} dokumen dengan nilai {$this->formatMoney($total)}.",
            'data' => [
                'jumlah_dokumen' => $count,
                'total_nilai' => $this->formatMoney($total),
            ],
            'link' => route('owner.dokumen', ['status' => 'all', 'filter_status_pembayaran' => 'sudah_dibayar']),
            'meta' => ['period' => $monthYear],
        ];
    }

    private function topDepartments(string $mode, int $limit): array
    {
        $rows = Dokumen::query()
            ->selectRaw("COALESCE(NULLIF(bagian, ''), NULLIF(nama_pengirim, ''), NULLIF(created_by, ''), 'Tidak diketahui') as bagian_label")
            ->selectRaw('COUNT(*) as total_dokumen')
            ->selectRaw('COALESCE(SUM(nilai_rupiah), 0) as total_nilai')
            ->groupBy('bagian_label')
            ->orderByDesc($mode === 'value' ? 'total_nilai' : 'total_dokumen')
            ->limit(min($limit, 10))
            ->get();

        if ($rows->isEmpty()) {
            return $this->emptyResult('Belum ada data bagian yang bisa diringkas.');
        }

        return [
            'intent' => 'top_departments',
            'answer' => $mode === 'value'
                ? 'Berikut bagian teratas berdasarkan total nilai dokumen.'
                : 'Berikut bagian teratas berdasarkan jumlah dokumen.',
            'data' => $rows->map(fn ($row) => [
                'bagian' => $row->bagian_label,
                'jumlah_dokumen' => (int) $row->total_dokumen,
                'total_nilai' => $this->formatMoney($row->total_nilai),
            ])->values()->all(),
            'link' => route('owner.dokumen'),
            'meta' => ['mode' => $mode],
        ];
    }

    private function oldestDocuments(int $limit): array
    {
        $docs = $this->unpaidQuery()
            ->oldest('tanggal_masuk')
            ->limit($limit)
            ->get();

        if ($docs->isEmpty()) {
            return $this->emptyResult('Tidak ditemukan dokumen aktif yang belum selesai/dibayar.');
        }

        return [
            'intent' => 'oldest_documents',
            'answer' => "Berikut dokumen yang paling lama diproses. Hasil dibatasi {$docs->count()} dokumen teratas.",
            'data' => $this->formatDocuments($docs, true),
            'link' => route('owner.dokumen', ['status' => 'pending']),
            'meta' => ['limited' => true],
        ];
    }

    private function lateDocuments(?string $handler, int $limit): array
    {
        $query = $this->unpaidQuery()
            ->where('tanggal_masuk', '<=', now()->subDays(3));

        if ($handler) {
            $query->whereRaw('LOWER(current_handler) = ?', [strtolower($handler)]);
        }

        $docs = $query->oldest('tanggal_masuk')->limit($limit)->get();
        $handlerText = $handler ? ' di ' . ($this->handlerLabel($handler)) : '';

        if ($docs->isEmpty()) {
            return $this->emptyResult("Tidak ditemukan dokumen yang tertahan lama{$handlerText}.");
        }

        return [
            'intent' => 'late_documents',
            'answer' => "Ada {$docs->count()} dokumen yang tertahan lebih dari 3 hari{$handlerText}.",
            'data' => $this->formatDocuments($docs, true),
            'link' => route('owner.dokumen', ['status' => 'urgent']),
            'meta' => ['handler' => $handler],
        ];
    }

    private function unfinishedDepartmentDocuments(string $department, int $limit): array
    {
        $docs = $this->unpaidQuery()
            ->where(function (Builder $query) use ($department) {
                $query->where('bagian', 'like', "%{$department}%")
                    ->orWhere('nama_pengirim', 'like', "%{$department}%")
                    ->orWhere('created_by', 'like', "%{$department}%");
            })
            ->latest('tanggal_masuk')
            ->limit($limit)
            ->get();

        if ($docs->isEmpty()) {
            return $this->emptyResult("Tidak ditemukan dokumen dari bagian {$department} yang belum selesai.");
        }

        return [
            'intent' => 'unfinished_department',
            'answer' => "Berikut dokumen dari bagian {$department} yang belum selesai/dibayar.",
            'data' => $this->formatDocuments($docs),
            'link' => route('owner.dokumen', ['status' => 'pending', 'filter_bagian' => $department]),
            'meta' => ['department' => $department],
        ];
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
        ]);
    }

    private function unpaidQuery(): Builder
    {
        return $this->baseQuery()
            ->where(function (Builder $query) {
                $query->whereNull('tanggal_dibayar')
                    ->where(function (Builder $subQuery) {
                        $subQuery->whereNull('status_pembayaran')
                            ->orWhereRaw('LOWER(status_pembayaran) != ?', ['sudah_dibayar']);
                    });
            });
    }

    private function paidQuery(): Builder
    {
        return $this->baseQuery()
            ->where(function (Builder $query) {
                $query->whereNotNull('tanggal_dibayar')
                    ->orWhereRaw('LOWER(status_pembayaran) = ?', ['sudah_dibayar'])
                    ->orWhere('status', 'completed');
            });
    }

    private function applyMonthYear(Builder $query, ?array $monthYear, string $column = 'tanggal_masuk'): void
    {
        if (!$monthYear) {
            return;
        }

        $query->whereMonth($column, $monthYear['month'])
            ->whereYear($column, $monthYear['year']);
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
                'status' => $doc->status ?: '-',
                'status_pembayaran' => $this->paymentLabel($doc->status_pembayaran, $doc->tanggal_dibayar),
                'pengurus' => $this->handlerLabel($doc->current_handler),
                'tanggal_masuk' => $tanggalMasuk ? $tanggalMasuk->format('d M Y H:i') : '-',
                'umur' => $includeAge && $tanggalMasuk ? $tanggalMasuk->diffForHumans(null, true) : null,
            ], fn ($value) => $value !== null);
        })->values()->all();
    }

    private function paymentLabel(?string $status, $tanggalDibayar): string
    {
        if ($tanggalDibayar || strtolower((string) $status) === 'sudah_dibayar') {
            return 'Sudah Dibayar';
        }

        if (in_array(strtolower((string) $status), ['siap_dibayar', 'siap_bayar'], true)) {
            return 'Siap Dibayar';
        }

        return 'Belum Dibayar';
    }

    private function handlerLabel(?string $handler): string
    {
        $key = strtolower((string) $handler);
        return self::HANDLER_LABELS[$key] ?? ($handler ? (string) str($handler)->replace('_', ' ')->title() : '-');
    }

    private function extractDate(string $text): ?Carbon
    {
        if (str_contains($text, 'hari ini')) {
            return today();
        }

        if (str_contains($text, 'kemarin')) {
            return today()->subDay();
        }

        if (preg_match('/\b(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})\b/', $text, $matches)) {
            return Carbon::createFromDate((int) $matches[3], (int) $matches[2], (int) $matches[1])->startOfDay();
        }

        if (preg_match('/\b(\d{1,2})\s+([a-z]+)\s+(\d{4})\b/u', $text, $matches)) {
            $month = self::MONTHS[$matches[2]] ?? null;
            if ($month) {
                return Carbon::createFromDate((int) $matches[3], $month, (int) $matches[1])->startOfDay();
            }
        }

        return null;
    }

    private function extractMonthYear(string $text): ?array
    {
        if (str_contains($text, 'bulan ini')) {
            return ['month' => now()->month, 'year' => now()->year];
        }

        if (preg_match('/\b([a-z]+)\s+(\d{4})\b/u', $text, $matches)) {
            $month = self::MONTHS[$matches[1]] ?? null;
            if ($month) {
                return ['month' => $month, 'year' => (int) $matches[2]];
            }
        }

        return null;
    }

    private function extractAmount(string $text): ?float
    {
        if (!preg_match('/(?:di atas|lebih dari|>=|minimal|min)\s*rp?\s*([\d\.\,]+)\s*(juta|miliar|ribu)?/u', $text, $matches)) {
            return null;
        }

        $number = (float) str_replace([',', '.'], ['', ''], $matches[1]);
        $unit = $matches[2] ?? '';

        return match ($unit) {
            'miliar' => $number * 1_000_000_000,
            'juta' => $number * 1_000_000,
            'ribu' => $number * 1_000,
            default => $number,
        };
    }

    private function extractDepartment(string $text): ?string
    {
        if (preg_match('/\b(SKH|TAN|DPM|SDM|TEP|AKN|KPL|PMO|PTI)\b/i', $text, $matches)) {
            return strtoupper($matches[1]);
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
                return $handler;
            }
        }

        return null;
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

    private function emptyResult(string $answer, array $query = []): array
    {
        return [
            'intent' => 'empty',
            'answer' => $answer,
            'data' => [],
            'link' => route('owner.dokumen', array_merge(['status' => 'all'], $query)),
            'meta' => [],
        ];
    }

    private function formatMoney($value): string
    {
        return 'Rp ' . number_format((float) $value, 0, ',', '.');
    }

    private function formatDate(Carbon $date): string
    {
        return $date->translatedFormat('d F Y');
    }

    private function periodLabel(?array $monthYear): string
    {
        if (!$monthYear) {
            return '';
        }

        $date = Carbon::createFromDate($monthYear['year'], $monthYear['month'], 1);
        return ' pada ' . $date->translatedFormat('F Y');
    }
}

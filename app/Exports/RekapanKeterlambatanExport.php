<?php

namespace App\Exports;

use App\Models\Dokumen;
use App\Models\DokumenRoleData;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class RekapanKeterlambatanExport implements WithMultipleSheets
{
    protected $year;
    protected $month;

    public function __construct($year = null, $month = null)
    {
        $this->year = $year;
        $this->month = $month;
    }

    public function sheets(): array
    {
        return [
            new RingkasanPerRoleSheet($this->year, $this->month),
            new DetailDokumenSheet($this->year, $this->month),
            new DokumenTerlambatSheet($this->year, $this->month),
        ];
    }
}

/**
 * Sheet 1: Ringkasan Per Role
 */
class RingkasanPerRoleSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    protected $year;
    protected $month;

    public function __construct($year = null, $month = null)
    {
        $this->year = $year;
        $this->month = $month;
    }

    public function title(): string
    {
        return 'Ringkasan Per Role';
    }

    public function headings(): array
    {
        return [
            'Role',
            'Total Dokumen',
            'Aman',
            'Peringatan',
            'Terlambat'
        ];
    }

    public function collection()
    {
        $roles = [
            'team_verifikasi' => 'Team Verifikasi',
            'perpajakan' => 'Team Perpajakan',
            'akutansi' => 'Team Akutansi',
            'pembayaran' => 'Pembayaran'
        ];

        $data = collect();
        $totals = ['aman' => 0, 'peringatan' => 0, 'terlambat' => 0, 'total' => 0];

        foreach ($roles as $roleCode => $roleName) {
            $stats = $this->getStatsForRole($roleCode);
            $data->push([
                'role' => $roleName,
                'total' => $stats['total'],
                'aman' => $stats['aman'],
                'peringatan' => $stats['peringatan'],
                'terlambat' => $stats['terlambat']
            ]);

            $totals['total'] += $stats['total'];
            $totals['aman'] += $stats['aman'];
            $totals['peringatan'] += $stats['peringatan'];
            $totals['terlambat'] += $stats['terlambat'];
        }

        // Add total row
        $data->push([
            'role' => 'TOTAL',
            'total' => $totals['total'],
            'aman' => $totals['aman'],
            'peringatan' => $totals['peringatan'],
            'terlambat' => $totals['terlambat']
        ]);

        return $data;
    }

    private function getStatsForRole($roleCode)
    {
        $now = Carbon::now();

        // Get threshold based on role
        $isPembayaran = $roleCode === 'pembayaran';

        $query = DokumenRoleData::where('role_code', $roleCode)
            ->whereNotNull('received_at');

        if ($this->year) {
            $query->whereYear('received_at', $this->year);
        }
        if ($this->month) {
            $query->whereMonth('received_at', $this->month);
        }

        $roleData = $query->get();

        $aman = 0;
        $peringatan = 0;
        $terlambat = 0;

        foreach ($roleData as $rd) {
            $receivedAt = Carbon::parse($rd->received_at);

            if ($isPembayaran) {
                // Weekly thresholds for pembayaran
                $diffInWeeks = $receivedAt->diffInWeeks($now);
                if ($diffInWeeks < 1) {
                    $aman++;
                } elseif ($diffInWeeks <= 3) {
                    $peringatan++;
                } else {
                    $terlambat++;
                }
            } else {
                // Daily thresholds for other roles
                $diffInDays = $receivedAt->diffInDays($now);
                if ($diffInDays < 1) {
                    $aman++;
                } elseif ($diffInDays <= 3) {
                    $peringatan++;
                } else {
                    $terlambat++;
                }
            }
        }

        return [
            'total' => $aman + $peringatan + $terlambat,
            'aman' => $aman,
            'peringatan' => $peringatan,
            'terlambat' => $terlambat
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();

        return [
            // Header row bold
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1a4d3e']], 'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true]],
            // Total row bold
            $lastRow => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E8F5E9']]],
        ];
    }
}

/**
 * Sheet 2: Detail Dokumen Per Role
 */
class DetailDokumenSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    protected $year;
    protected $month;

    public function __construct($year = null, $month = null)
    {
        $this->year = $year;
        $this->month = $month;
    }

    public function title(): string
    {
        return 'Detail Dokumen Per Role';
    }

    public function headings(): array
    {
        return [
            'No',
            'Role',
            'No Agenda',
            'SPP',
            'Uraian',
            'Nilai (Rp)',
            'Tanggal Masuk',
            'Waktu Proses',
            'Status Deadline',
            'Status Dokumen',
            'Dibayar Kepada'
        ];
    }

    public function collection()
    {
        $roles = ['team_verifikasi', 'perpajakan', 'akutansi', 'pembayaran'];
        $roleNames = [
            'team_verifikasi' => 'Team Verifikasi',
            'perpajakan' => 'Team Perpajakan',
            'akutansi' => 'Team Akutansi',
            'pembayaran' => 'Pembayaran'
        ];

        $now = Carbon::now();
        $data = collect();
        $no = 1;

        foreach ($roles as $roleCode) {
            $isPembayaran = $roleCode === 'pembayaran';

            $query = Dokumen::with(['dokumenPos', 'dokumenPrs', 'dibayarKepadas', 'roleData'])
                ->join('dokumen_role_data', 'dokumens.id', '=', 'dokumen_role_data.dokumen_id')
                ->where('dokumen_role_data.role_code', $roleCode)
                ->whereNotNull('dokumen_role_data.received_at');

            if ($this->year) {
                $query->whereYear('dokumen_role_data.received_at', $this->year);
            }
            if ($this->month) {
                $query->whereMonth('dokumen_role_data.received_at', $this->month);
            }

            $documents = $query->select(
                'dokumens.*',
                'dokumen_role_data.received_at as role_received_at',
                'dokumen_role_data.processed_at as role_processed_at'
            )->get();

            foreach ($documents as $doc) {
                $receivedAt = Carbon::parse($doc->role_received_at);

                // Calculate deadline status
                if ($isPembayaran) {
                    $diffInWeeks = $receivedAt->diffInWeeks($now);
                    if ($diffInWeeks < 1) {
                        $deadlineStatus = 'AMAN';
                    } elseif ($diffInWeeks <= 3) {
                        $deadlineStatus = 'PERINGATAN';
                    } else {
                        $deadlineStatus = 'TERLAMBAT';
                    }
                } else {
                    $diffInDays = $receivedAt->diffInDays($now);
                    if ($diffInDays < 1) {
                        $deadlineStatus = 'AMAN';
                    } elseif ($diffInDays <= 3) {
                        $deadlineStatus = 'PERINGATAN';
                    } else {
                        $deadlineStatus = 'TERLAMBAT';
                    }
                }

                // Calculate waktu proses
                $waktuProses = $this->formatWaktuProses($receivedAt, $now);

                // Get status dokumen
                $statusDokumen = $doc->role_processed_at ? 'Sudah Dikirim' : 'Sedang Diproses';

                // Get dibayar kepada
                $dibayarKepada = $doc->dibayarKepadas->pluck('nama')->implode(', ') ?: '-';

                $data->push([
                    'no' => $no++,
                    'role' => $roleNames[$roleCode],
                    'no_agenda' => $doc->no_agenda ?? '-',
                    'spp' => $doc->dokumenPos->first()->spp ?? ($doc->dokumenPrs->first()->spp ?? '-'),
                    'uraian' => \Illuminate\Support\Str::limit($doc->uraian ?? '-', 50),
                    'nilai' => number_format($doc->nilai_ajuan ?? 0, 0, ',', '.'),
                    'tanggal_masuk' => $receivedAt->format('d M Y H:i'),
                    'waktu_proses' => $waktuProses,
                    'status_deadline' => $deadlineStatus,
                    'status_dokumen' => $statusDokumen,
                    'dibayar_kepada' => $dibayarKepada
                ]);
            }
        }

        return $data;
    }

    private function formatWaktuProses($receivedAt, $now)
    {
        $diff = $receivedAt->diff($now);
        $parts = [];

        if ($diff->d > 0) {
            $parts[] = $diff->d . ' hari';
        }
        if ($diff->h > 0) {
            $parts[] = $diff->h . ' jam';
        }

        return empty($parts) ? 'Baru masuk' : implode(' ', $parts);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1a4d3e']], 'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true]],
        ];
    }
}

/**
 * Sheet 3: Dokumen Terlambat (Prioritas)
 */
class DokumenTerlambatSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    protected $year;
    protected $month;

    public function __construct($year = null, $month = null)
    {
        $this->year = $year;
        $this->month = $month;
    }

    public function title(): string
    {
        return 'Dokumen Terlambat';
    }

    public function headings(): array
    {
        return [
            'No',
            'Role',
            'No Agenda',
            'SPP',
            'Uraian',
            'Nilai (Rp)',
            'Tanggal Masuk',
            'Lama Terlambat',
            'Dibayar Kepada'
        ];
    }

    public function collection()
    {
        $roles = ['team_verifikasi', 'perpajakan', 'akutansi', 'pembayaran'];
        $roleNames = [
            'team_verifikasi' => 'Team Verifikasi',
            'perpajakan' => 'Team Perpajakan',
            'akutansi' => 'Team Akutansi',
            'pembayaran' => 'Pembayaran'
        ];

        $now = Carbon::now();
        $data = collect();
        $no = 1;

        foreach ($roles as $roleCode) {
            $isPembayaran = $roleCode === 'pembayaran';

            $query = Dokumen::with(['dokumenPos', 'dokumenPrs', 'dibayarKepadas'])
                ->join('dokumen_role_data', 'dokumens.id', '=', 'dokumen_role_data.dokumen_id')
                ->where('dokumen_role_data.role_code', $roleCode)
                ->whereNotNull('dokumen_role_data.received_at');

            if ($this->year) {
                $query->whereYear('dokumen_role_data.received_at', $this->year);
            }
            if ($this->month) {
                $query->whereMonth('dokumen_role_data.received_at', $this->month);
            }

            $documents = $query->select(
                'dokumens.*',
                'dokumen_role_data.received_at as role_received_at'
            )->get();

            foreach ($documents as $doc) {
                $receivedAt = Carbon::parse($doc->role_received_at);

                // Check if terlambat
                $isTerlambat = false;
                $lamaTerlambat = '';

                if ($isPembayaran) {
                    $diffInWeeks = $receivedAt->diffInWeeks($now);
                    if ($diffInWeeks > 3) {
                        $isTerlambat = true;
                        $lamaTerlambat = ($diffInWeeks - 3) . ' minggu';
                    }
                } else {
                    $diffInDays = $receivedAt->diffInDays($now);
                    if ($diffInDays > 3) {
                        $isTerlambat = true;
                        $lamaTerlambat = ($diffInDays - 3) . ' hari';
                    }
                }

                if (!$isTerlambat) {
                    continue;
                }

                // Get dibayar kepada
                $dibayarKepada = $doc->dibayarKepadas->pluck('nama')->implode(', ') ?: '-';

                $data->push([
                    'no' => $no++,
                    'role' => $roleNames[$roleCode],
                    'no_agenda' => $doc->no_agenda ?? '-',
                    'spp' => $doc->dokumenPos->first()->spp ?? ($doc->dokumenPrs->first()->spp ?? '-'),
                    'uraian' => \Illuminate\Support\Str::limit($doc->uraian ?? '-', 50),
                    'nilai' => number_format($doc->nilai_ajuan ?? 0, 0, ',', '.'),
                    'tanggal_masuk' => $receivedAt->format('d M Y H:i'),
                    'lama_terlambat' => $lamaTerlambat,
                    'dibayar_kepada' => $dibayarKepada
                ]);
            }
        }

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'dc3545']], 'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true]],
        ];
    }
}

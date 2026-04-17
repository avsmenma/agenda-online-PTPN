<?php

namespace App\Exports;

use App\Models\Dokumen;
use App\Models\DokumenRoleData;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class RekapanKeterlambatanExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $roleCode;
    protected $year;
    protected $month;

    /**
     * Role names mapping
     */
    protected $roleNames = [
        'team_verifikasi' => 'Team Verifikasi',
        'perpajakan' => 'Perpajakan',
        'akutansi' => 'Akutansi',
        'pembayaran' => 'Pembayaran',
    ];

    /**
     * Deadline thresholds per role (in days)
     */
    protected $deadlineThresholds = [
        'team_verifikasi' => [1, 3],     // Aman: <1 hari, Peringatan: 1-3 hari, Terlambat: >3 hari
        'perpajakan' => [1, 3],          // Aman: <1 hari, Peringatan: 1-3 hari, Terlambat: >3 hari
        'akutansi' => [1, 3],            // Aman: <1 hari, Peringatan: 1-3 hari, Terlambat: >3 hari
        'pembayaran' => [7, 21],         // Aman: <1 minggu, Peringatan: 1-3 minggu, Terlambat: >3 minggu
    ];

    public function __construct($roleCode, $year = null, $month = null)
    {
        $this->roleCode = $roleCode;
        $this->year = $year;
        $this->month = $month;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $now = Carbon::now();
        $data = collect();

        // Get role-specific data
        $query = DokumenRoleData::where('role_code', $this->roleCode)
            ->whereNotNull('received_at')
            ->with(['dokumen']);

        // Optional date filtering
        if ($this->year) {
            $query->whereYear('received_at', $this->year);
        }
        if ($this->month) {
            $query->whereMonth('received_at', $this->month);
        }

        $roleDataList = $query->orderBy('received_at', 'asc')->get();
        $thresholds = $this->deadlineThresholds[$this->roleCode] ?? [1, 3];
        $isWeekly = $this->roleCode === 'pembayaran';

        $no = 1;
        foreach ($roleDataList as $roleData) {
            $dokumen = $roleData->dokumen;
            if (!$dokumen)
                continue;

            // Calculate days/weeks since received
            $receivedAt = Carbon::parse($roleData->received_at);
            $daysDiff = $receivedAt->diffInDays($now);

            // Determine status
            if ($isWeekly) {
                $weeksDiff = floor($daysDiff / 7);
                if ($daysDiff < $thresholds[0]) {
                    $status = 'AMAN';
                    $statusDetail = "< 1 minggu";
                } elseif ($daysDiff <= $thresholds[1]) {
                    $status = 'PERINGATAN';
                    $statusDetail = "1-3 minggu";
                } else {
                    $status = 'TERLAMBAT';
                    $statusDetail = "> 3 minggu";
                }
                $duration = $weeksDiff . " minggu";
            } else {
                if ($daysDiff < $thresholds[0]) {
                    $status = 'AMAN';
                    $statusDetail = "< 1 hari";
                } elseif ($daysDiff <= $thresholds[1]) {
                    $status = 'PERINGATAN';
                    $statusDetail = "1-3 hari";
                } else {
                    $status = 'TERLAMBAT';
                    $statusDetail = "> 3 hari";
                }
                $duration = $daysDiff . " hari";
            }

            // Check if completed
            $completedAt = $roleData->processed_at;
            $isCompleted = !is_null($completedAt);

            $data->push([
                'no' => $no++,
                'nomor_agenda' => $dokumen->nomor_agenda ?? '-',
                'nomor_spp' => $dokumen->nomor_spp ?? '-',
                'uraian' => \Illuminate\Support\Str::limit($dokumen->uraian_spp ?? '-', 50),
                'nilai_rupiah' => $dokumen->nilai_rupiah ? 'Rp ' . number_format($dokumen->nilai_rupiah, 0, ',', '.') : '-',
                'tanggal_terima' => $receivedAt->format('d/m/Y H:i'),
                'durasi' => $duration,
                'status_deadline' => $status,
                'status_proses' => $isCompleted ? 'Selesai' : 'Sedang Diproses',
                'tanggal_selesai' => $isCompleted ? Carbon::parse($completedAt)->format('d/m/Y H:i') : '-',
            ]);
        }

        return $data;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        $roleName = $this->roleNames[$this->roleCode] ?? $this->roleCode;

        return [
            'No',
            'No. Agenda',
            'No. SPP',
            'Uraian',
            'Nilai (Rupiah)',
            'Tanggal Terima di ' . $roleName,
            'Durasi',
            'Status Deadline',
            'Status Proses',
            'Tanggal Selesai',
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet): array
    {
        $roleName = $this->roleNames[$this->roleCode] ?? $this->roleCode;

        // Set title
        $sheet->setTitle('Rekapan ' . $roleName);

        return [
            // Style header row
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1a4d3e']
                ],
            ],
        ];
    }
}

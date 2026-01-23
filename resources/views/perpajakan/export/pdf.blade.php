<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Perpajakan - {{ date('d/m/Y') }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0.5cm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 7px;
            margin: 0;
            padding: 5px;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 2px solid #083E40;
            padding-bottom: 5px;
        }

        .header h1 {
            margin: 0;
            color: #083E40;
            font-size: 14px;
        }

        .header p {
            margin: 3px 0;
            color: #666;
            font-size: 8px;
        }

        .filter-info {
            margin-bottom: 8px;
            font-size: 7px;
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            table-layout: auto;
        }

        th {
            background-color: #083E40;
            color: white;
            padding: 4px 3px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #ddd;
            font-size: 7px;
            word-wrap: break-word;
            white-space: normal;
            line-height: 1.2;
        }

        td {
            padding: 3px 2px;
            border: 1px solid #ddd;
            font-size: 7px;
            word-wrap: break-word;
            white-space: normal;
            line-height: 1.2;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .footer {
            margin-top: 10px;
            text-align: center;
            font-size: 7px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }

        @media print {
            body {
                margin: 0;
            }

            .no-print {
                display: none;
            }
        }

        /* Ensure table fits within page width */
        table {
            max-width: 100%;
            overflow: hidden;
        }

        /* Make text in cells more compact - allow wrapping for long text */
        td {
            max-width: 150px;
            overflow: hidden;
        }

        /* Special handling for long text columns like uraian_spp */
        td:first-child+td+td+td+td+td+td+td {
            max-width: 200px;
            white-space: normal;
            word-break: break-word;
        }
    </style>
    <script>
        window.onload = function () {
            window.print();
        };
    </script>
</head>

<body>
    <div class="header">
        <h1>EXPORT DATA PERPAJAKAN</h1>
        <p>Tanggal Export: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <div class="filter-info">
        @if($year ?? null)
            <strong>Tahun:</strong> {{ $year }} |
        @endif
        @if($month ?? null)
            <strong>Bulan:</strong>
            {{ ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'][$month - 1] }}
            |
        @endif
        @if($search ?? null)
            <strong>Pencarian:</strong> {{ $search }} |
        @endif
        <strong>Total Dokumen:</strong> {{ $dokumens->count() }}
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 25px; min-width: 25px;">No</th>
                @foreach($columns as $col)
                    <th>
                        @if($col === 'sent_to_perpajakan_at')
                            Tgl Masuk Perpajakan
                        @elseif($col === 'processed_perpajakan_at')
                            Tgl Diproses Perpajakan
                        @elseif($col === 'deadline_perpajakan_at')
                            Deadline Perpajakan
                        @else
                            {{ $availableColumns[$col] ?? ucfirst(str_replace('_', ' ', $col)) }}
                        @endif
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @php
                $dateFields = $dateFields ?? ['tanggal_spp', 'tanggal_berita_acara', 'tanggal_spk', 'tanggal_berakhir_spk', 'tanggal_faktur', 'tanggal_selesai_verifikasi_pajak', 'tanggal_invoice', 'tanggal_pengajuan_pajak', 'created_at', 'sent_to_perpajakan_at', 'processed_perpajakan_at', 'deadline_at', 'deadline_perpajakan_at'];
                $currencyFields = $currencyFields ?? ['nilai_rupiah', 'dpp_pph', 'ppn_terhutang', 'dpp_invoice', 'ppn_invoice', 'dpp_ppn_invoice', 'dpp_faktur', 'ppn_faktur', 'selisih_pajak', 'penggantian_pajak', 'dpp_penggantian', 'ppn_penggantian', 'selisih_ppn'];
            @endphp

            @forelse($dokumens as $index => $doc)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    @foreach($columns as $col)
                        <td>
                            @php
                                $value = '';

                                // Handle special columns that come from relationships
                                if ($col === 'dibayar_kepada') {
                                    if ($doc->dibayarKepadas && $doc->dibayarKepadas->count() > 0) {
                                        $value = $doc->dibayarKepadas->pluck('nama_penerima')->join(', ');
                                    } else {
                                        $value = $doc->dibayar_kepada ?? '-';
                                    }
                                } elseif ($col === 'nomor_po' || $col === 'no_po') {
                                    if ($doc->dokumenPos && $doc->dokumenPos->count() > 0) {
                                        $value = $doc->dokumenPos->pluck('nomor_po')->join(', ');
                                    } else {
                                        $value = '-';
                                    }
                                } elseif ($col === 'nomor_pr' || $col === 'no_pr') {
                                    if ($doc->dokumenPrs && $doc->dokumenPrs->count() > 0) {
                                        $value = $doc->dokumenPrs->pluck('nomor_pr')->join(', ');
                                    } else {
                                        $value = '-';
                                    }
                                } elseif ($col === 'status') {
                                    $statusMap = [
                                        'selesai' => 'Selesai',
                                        'terkunci' => 'Terkunci',
                                        'sedang diproses' => 'Sedang Diproses',
                                        'sent_to_perpajakan' => 'Terkirim ke Team Perpajakan',
                                        'sent_to_akutansi' => 'Terkirim ke Team Akutansi',
                                        'returned_to_department' => 'Dikembalikan ke Department',
                                        'returned_from_akutansi' => 'Dikembalikan dari Akutansi',
                                        'returned_from_perpajakan' => 'Dikembalikan dari Perpajakan',
                                        'proses_perpajakan' => 'Diproses Team Perpajakan',
                                        'proses_akutansi' => 'Diproses Team Akutansi',
                                        'sent_to_Team Verifikasi' => 'Terkirim ke Team Verifikasi',
                                        'proses_Team Verifikasi' => 'Diproses Team Verifikasi',
                                        'pending_approval_Team Verifikasi' => 'Menunggu Persetujuan Team Verifikasi',
                                        'menunggu_verifikasi' => 'Menunggu Verifikasi',
                                        'draft' => 'Draft',
                                    ];
                                    $value = $statusMap[$doc->status] ?? ucwords(str_replace('_', ' ', $doc->status));
                                } elseif (in_array($col, $dateFields)) {
                                    try {
                                        $fieldValue = $doc->getAttribute($col);
                                        if ($fieldValue) {
                                            if ($fieldValue instanceof \Carbon\Carbon || $fieldValue instanceof \DateTime) {
                                                $value = $fieldValue->format('d/m/Y');
                                            } elseif (is_string($fieldValue)) {
                                                $value = \Carbon\Carbon::parse($fieldValue)->format('d/m/Y');
                                            } else {
                                                $value = '-';
                                            }
                                        } else {
                                            $value = '-';
                                        }
                                    } catch (\Exception $e) {
                                        $value = '-';
                                    }
                                } elseif (in_array($col, $currencyFields)) {
                                    try {
                                        $fieldValue = $doc->getAttribute($col);
                                        if ($fieldValue && is_numeric($fieldValue)) {
                                            $value = 'Rp ' . number_format((float) $fieldValue, 0, ',', '.');
                                        } else {
                                            $value = '-';
                                        }
                                    } catch (\Exception $e) {
                                        $value = '-';
                                    }
                                } else {
                                    try {
                                        $fieldValue = $doc->getAttribute($col);
                                        $value = $fieldValue ?? '-';
                                    } catch (\Exception $e) {
                                        $value = '-';
                                    }
                                }
                            @endphp
                            @if(in_array($col, $currencyFields) && $value !== '-')
                                <span class="text-right">{{ $value }}</span>
                            @else
                                {{ $value }}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($columns) + 1 }}" class="text-center">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ date('d/m/Y H:i:s') }}</p>
    </div>
</body>

</html>



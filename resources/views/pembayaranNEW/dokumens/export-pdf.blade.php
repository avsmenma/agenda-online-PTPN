<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekapan Pembayaran - {{ date('d/m/Y') }}</title>
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
        .subtotal-row {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        .subtotal-row td {
            border-top: 2px solid #dee2e6;
            border-bottom: 2px solid #dee2e6;
        }
        .grand-total-row {
            background-color: #e8f5e9;
            font-weight: 700;
        }
        .grand-total-row td {
            border-top: 3px solid #28a745;
            padding: 5px 3px;
            font-size: 8px;
        }
        .footer {
            margin-top: 10px;
            text-align: center;
            font-size: 7px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 5px;
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
        td:first-child + td + td + td + td + td + td + td {
            max-width: 200px;
            white-space: normal;
            word-break: break-word;
        }
        @media print {
            body {
                margin: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</head>
<body>
    <div class="header">
        <h1>REKAPAN DOKUMEN PEMBAYARAN</h1>
        <p>Tanggal Export: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <div class="filter-info">
        @if($statusFilter)
            <strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $statusFilter)) }} | 
        @endif
        @if($year)
            <strong>Tahun:</strong> {{ $year }} | 
        @endif
        @if($month)
            <strong>Bulan:</strong> {{ ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'][$month - 1] }} | 
        @endif
        @if($search)
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
                        @if($col === 'sent_to_pembayaran_at')
                            Tgl Diterima
                        @elseif($col === 'computed_status')
                            Status
                        @elseif($col === 'tanggal_dibayar')
                            Tgl Dibayar
                        @else
                            {{ $availableColumns[$col] ?? ucfirst(str_replace('_', ' ', $col)) }}
                        @endif
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @php
                $globalNo = 0;
                $valueColumns = ['nilai_rupiah', 'nilai_belum_siap_bayar', 'nilai_siap_bayar', 'nilai_sudah_dibayar'];
            @endphp

            @if(isset($mode) && $mode === 'rekapan_table' && $rekapanByVendor)
                @foreach($rekapanByVendor as $vendorData)
                    @foreach($vendorData['documents'] as $doc)
                        @php $globalNo++; @endphp
                        <tr>
                            <td class="text-center">{{ $globalNo }}</td>
                            @foreach($columns as $col)
                                <td>
                                    @php
                                        $value = '';
                                        switch ($col) {
                                            case 'nomor_agenda':
                                                $value = $doc->nomor_agenda ?? '-';
                                                break;
                                            case 'nomor_spp':
                                                $value = $doc->nomor_spp ?? '-';
                                                break;
                                            case 'sent_to_pembayaran_at':
                                                $value = $doc->sent_to_pembayaran_at ? $doc->sent_to_pembayaran_at->format('d/m/Y') : '-';
                                                break;
                                            case 'dibayar_kepada':
                                                if ($doc->dibayarKepadas && $doc->dibayarKepadas->count() > 0) {
                                                    $value = $doc->dibayarKepadas->pluck('nama_penerima')->join(', ');
                                                } else {
                                                    $value = $doc->dibayar_kepada ?? '-';
                                                }
                                                break;
                                            case 'nilai_rupiah':
                                                $value = 'Rp ' . number_format($doc->nilai_rupiah ?? 0, 0, ',', '.');
                                                break;
                                            case 'computed_status':
                                                $status = $doc->computed_status ?? 'belum_siap_dibayar';
                                                if ($status === 'sudah_dibayar') $value = 'Sudah Dibayar';
                                                elseif ($status === 'siap_dibayar') $value = 'Siap Dibayar';
                                                else $value = 'Belum Siap Dibayar';
                                                break;
                                            case 'tanggal_dibayar':
                                                $value = $doc->tanggal_dibayar ? $doc->tanggal_dibayar->format('d/m/Y') : '-';
                                                break;
                                            case 'jenis_pembayaran':
                                                $value = $doc->jenis_pembayaran ?? '-';
                                                break;
                                            case 'jenis_sub_pekerjaan':
                                                $value = $doc->jenis_sub_pekerjaan ?? '-';
                                                break;
                                            case 'nomor_miro':
                                                $value = $doc->nomor_miro ?? '-';
                                                break;
                                            case 'uraian_spp':
                                                $value = $doc->uraian_spp ?? '-';
                                                break;
                                            case 'tanggal_spp':
                                                $value = $doc->tanggal_spp ? $doc->tanggal_spp->format('d/m/Y') : '-';
                                                break;
                                            case 'tanggal_berita_acara':
                                                $value = $doc->tanggal_berita_acara ? $doc->tanggal_berita_acara->format('d/m/Y') : '-';
                                                break;
                                            case 'no_berita_acara':
                                                $value = $doc->no_berita_acara ?? '-';
                                                break;
                                            case 'tanggal_berakhir_ba':
                                                $value = $doc->tanggal_berakhir_ba ? $doc->tanggal_berakhir_ba->format('d/m/Y') : '-';
                                                break;
                                            case 'no_spk':
                                                $value = $doc->no_spk ?? '-';
                                                break;
                                            case 'tanggal_spk':
                                                $value = $doc->tanggal_spk ? $doc->tanggal_spk->format('d/m/Y') : '-';
                                                break;
                                            case 'tanggal_berakhir_spk':
                                                $value = $doc->tanggal_berakhir_spk ? $doc->tanggal_berakhir_spk->format('d/m/Y') : '-';
                                                break;
                                            case 'kebun':
                                                $value = $doc->kebun ?? $doc->nama_kebuns ?? '-';
                                                break;
                                            case 'umur_dokumen_tanggal_masuk':
                                                // Jika sudah dibayar, tampilkan 0
                                                if (isset($doc->computed_status) && $doc->computed_status === 'sudah_dibayar') {
                                                    $value = '0 HARI';
                                                } elseif ($doc->tanggal_masuk) {
                                                    $tanggalMasuk = \Carbon\Carbon::parse($doc->tanggal_masuk)->startOfDay();
                                                    $hariIni = \Carbon\Carbon::now()->startOfDay();
                                                    $days = $tanggalMasuk->lte($hariIni) ? (int) $tanggalMasuk->diffInDays($hariIni) : 0;
                                                    $value = $days . ' HARI';
                                                } else {
                                                    $value = '-';
                                                }
                                                break;
                                            case 'umur_dokumen_tanggal_spp':
                                                // Jika sudah dibayar, tampilkan 0
                                                if (isset($doc->computed_status) && $doc->computed_status === 'sudah_dibayar') {
                                                    $value = '0 HARI';
                                                } elseif ($doc->tanggal_spp) {
                                                    $tanggalSpp = \Carbon\Carbon::parse($doc->tanggal_spp)->startOfDay();
                                                    $hariIni = \Carbon\Carbon::now()->startOfDay();
                                                    $days = $tanggalSpp->lte($hariIni) ? (int) $tanggalSpp->diffInDays($hariIni) : 0;
                                                    $value = $days . ' HARI';
                                                } else {
                                                    $value = '-';
                                                }
                                                break;
                                            case 'umur_dokumen_tanggal_ba':
                                                // Jika sudah dibayar, tampilkan 0
                                                if (isset($doc->computed_status) && $doc->computed_status === 'sudah_dibayar') {
                                                    $value = '0 HARI';
                                                } elseif ($doc->tanggal_berita_acara) {
                                                    $tanggalBa = \Carbon\Carbon::parse($doc->tanggal_berita_acara)->startOfDay();
                                                    $hariIni = \Carbon\Carbon::now()->startOfDay();
                                                    $days = $tanggalBa->lte($hariIni) ? (int) $tanggalBa->diffInDays($hariIni) : 0;
                                                    $value = $days . ' HARI';
                                                } else {
                                                    $value = '-';
                                                }
                                                break;
                                            case 'nilai_belum_siap_bayar':
                                                $value = $doc->computed_status === 'belum_siap_dibayar' 
                                                    ? 'Rp ' . number_format($doc->nilai_rupiah ?? 0, 0, ',', '.')
                                                    : '-';
                                                break;
                                            case 'nilai_siap_bayar':
                                                $value = $doc->computed_status === 'siap_dibayar' 
                                                    ? 'Rp ' . number_format($doc->nilai_rupiah ?? 0, 0, ',', '.')
                                                    : '-';
                                                break;
                                            case 'nilai_sudah_dibayar':
                                                $value = $doc->computed_status === 'sudah_dibayar' 
                                                    ? 'Rp ' . number_format($doc->nilai_rupiah ?? 0, 0, ',', '.')
                                                    : '-';
                                                break;
                                            default:
                                                $value = $doc->$col ?? '-';
                                        }
                                    @endphp
                                    {{ $value }}
                                </td>
                            @endforeach
                        </tr>
                    @endforeach

                    <!-- Subtotal Row -->
                    <tr class="subtotal-row">
                        <td colspan="{{ $colspanCount }}" class="text-right" style="border-top: 2px solid #dee2e6; border-bottom: 2px solid #dee2e6; font-size: 7px; padding: 4px 3px;">
                            <strong>Subtotal {{ Str::limit($vendorData['vendor'], 30) }}:</strong>
                        </td>
                        @foreach($columns as $idx => $col)
                            @if($firstValueIndex !== null && $idx >= $firstValueIndex)
                                @if($col == 'nilai_rupiah')
                                    <td style="border-top: 2px solid #dee2e6; border-bottom: 2px solid #dee2e6;"><strong>Rp {{ number_format($vendorData['total_nilai'], 0, ',', '.') }}</strong></td>
                                @elseif($col == 'nilai_belum_siap_bayar')
                                    <td style="border-top: 2px solid #dee2e6; border-bottom: 2px solid #dee2e6;"><strong>Rp {{ number_format($vendorData['total_belum_dibayar'], 0, ',', '.') }}</strong></td>
                                @elseif($col == 'nilai_siap_bayar')
                                    <td style="border-top: 2px solid #dee2e6; border-bottom: 2px solid #dee2e6;"><strong>Rp {{ number_format($vendorData['total_siap_dibayar'], 0, ',', '.') }}</strong></td>
                                @elseif($col == 'nilai_sudah_dibayar')
                                    <td style="border-top: 2px solid #dee2e6; border-bottom: 2px solid #dee2e6;"><strong>Rp {{ number_format($vendorData['total_sudah_dibayar'], 0, ',', '.') }}</strong></td>
                                @elseif(in_array($col, ['umur_dokumen_tanggal_masuk', 'umur_dokumen_tanggal_spp', 'umur_dokumen_tanggal_ba']))
                                    <td style="border-top: 2px solid #dee2e6; border-bottom: 2px solid #dee2e6;">-</td>
                                @else
                                    <td style="border-top: 2px solid #dee2e6; border-bottom: 2px solid #dee2e6;"></td>
                                @endif
                            @endif
                        @endforeach
                    </tr>
                @endforeach

                <!-- Grand Total Row -->
                <tr class="grand-total-row">
                    <td colspan="{{ $colspanCount }}" class="text-right" style="border-top: 3px solid #28a745; padding: 5px 3px;">
                        <strong style="font-size: 8px;">GRAND TOTAL:</strong>
                    </td>
                    @foreach($columns as $idx => $col)
                        @if($firstValueIndex !== null && $idx >= $firstValueIndex)
                            @if($col == 'nilai_rupiah')
                                <td style="border-top: 3px solid #28a745; padding: 5px 3px;"><strong style="font-size: 8px;">Rp {{ number_format($grandTotalNilai ?? 0, 0, ',', '.') }}</strong></td>
                            @elseif($col == 'nilai_belum_siap_bayar')
                                <td style="border-top: 3px solid #28a745; padding: 5px 3px;"><strong style="font-size: 8px;">Rp {{ number_format($grandTotalBelum ?? 0, 0, ',', '.') }}</strong></td>
                            @elseif($col == 'nilai_siap_bayar')
                                <td style="border-top: 3px solid #28a745; padding: 5px 3px;"><strong style="font-size: 8px;">Rp {{ number_format($grandTotalSiap ?? 0, 0, ',', '.') }}</strong></td>
                            @elseif($col == 'nilai_sudah_dibayar')
                                <td style="border-top: 3px solid #28a745; padding: 5px 3px;"><strong style="font-size: 8px;">Rp {{ number_format($grandTotalSudah ?? 0, 0, ',', '.') }}</strong></td>
                            @elseif(in_array($col, ['umur_dokumen_tanggal_masuk', 'umur_dokumen_tanggal_spp', 'umur_dokumen_tanggal_ba']))
                                <td style="border-top: 3px solid #28a745; padding: 10px 6px;">-</td>
                            @else
                                <td style="border-top: 3px solid #28a745; padding: 10px 6px;"></td>
                            @endif
                        @endif
                    @endforeach
                </tr>
            @else
                @forelse($dokumens as $index => $dokumen)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        @foreach($columns as $col)
                            <td>
                                @php
                                    $value = '';
                                    switch ($col) {
                                        case 'nomor_agenda':
                                            $value = $dokumen->nomor_agenda ?? '-';
                                            break;
                                        case 'nomor_spp':
                                            $value = $dokumen->nomor_spp ?? '-';
                                            break;
                                        case 'sent_to_pembayaran_at':
                                            $value = $dokumen->sent_to_pembayaran_at ? $dokumen->sent_to_pembayaran_at->format('d/m/Y') : '-';
                                            break;
                                        case 'dibayar_kepada':
                                            if ($dokumen->dibayarKepadas && $dokumen->dibayarKepadas->count() > 0) {
                                                $value = $dokumen->dibayarKepadas->pluck('nama_penerima')->join(', ');
                                            } else {
                                                $value = $dokumen->dibayar_kepada ?? '-';
                                            }
                                            break;
                                        case 'nilai_rupiah':
                                            $value = 'Rp ' . number_format($dokumen->nilai_rupiah ?? 0, 0, ',', '.');
                                            break;
                                        case 'computed_status':
                                            $status = $dokumen->computed_status ?? 'belum_siap_dibayar';
                                            if ($status === 'sudah_dibayar') $value = 'Sudah Dibayar';
                                            elseif ($status === 'siap_dibayar') $value = 'Siap Dibayar';
                                            else $value = 'Belum Siap Dibayar';
                                            break;
                                        case 'tanggal_dibayar':
                                            $value = $dokumen->tanggal_dibayar ? $dokumen->tanggal_dibayar->format('d/m/Y') : '-';
                                            break;
                                        case 'jenis_pembayaran':
                                            $value = $dokumen->jenis_pembayaran ?? '-';
                                            break;
                                        case 'jenis_sub_pekerjaan':
                                            $value = $dokumen->jenis_sub_pekerjaan ?? '-';
                                            break;
                                        case 'nomor_miro':
                                            $value = $dokumen->nomor_miro ?? '-';
                                            break;
                                        case 'uraian_spp':
                                            $value = $dokumen->uraian_spp ?? '-';
                                            break;
                                        case 'tanggal_spp':
                                            $value = $dokumen->tanggal_spp ? $dokumen->tanggal_spp->format('d/m/Y') : '-';
                                            break;
                                        case 'tanggal_berita_acara':
                                            $value = $dokumen->tanggal_berita_acara ? $dokumen->tanggal_berita_acara->format('d/m/Y') : '-';
                                            break;
                                        case 'no_berita_acara':
                                            $value = $dokumen->no_berita_acara ?? '-';
                                            break;
                                        case 'tanggal_berakhir_ba':
                                            $value = $dokumen->tanggal_berakhir_ba ? $dokumen->tanggal_berakhir_ba->format('d/m/Y') : '-';
                                            break;
                                        case 'no_spk':
                                            $value = $dokumen->no_spk ?? '-';
                                            break;
                                        case 'tanggal_spk':
                                            $value = $dokumen->tanggal_spk ? $dokumen->tanggal_spk->format('d/m/Y') : '-';
                                            break;
                                        case 'tanggal_berakhir_spk':
                                            $value = $dokumen->tanggal_berakhir_spk ? $dokumen->tanggal_berakhir_spk->format('d/m/Y') : '-';
                                            break;
                                        case 'kebun':
                                            $value = $dokumen->kebun ?? $dokumen->nama_kebuns ?? '-';
                                            break;
                                        case 'umur_dokumen_tanggal_masuk':
                                            // Jika sudah dibayar, tampilkan 0
                                            if (isset($dokumen->computed_status) && $dokumen->computed_status === 'sudah_dibayar') {
                                                $value = '0 HARI';
                                            } elseif ($dokumen->tanggal_masuk) {
                                                $tanggalMasuk = \Carbon\Carbon::parse($dokumen->tanggal_masuk)->startOfDay();
                                                $hariIni = \Carbon\Carbon::now()->startOfDay();
                                                $days = $tanggalMasuk->lte($hariIni) ? (int) $tanggalMasuk->diffInDays($hariIni) : 0;
                                                $value = $days . ' HARI';
                                            } else {
                                                $value = '-';
                                            }
                                            break;
                                        case 'umur_dokumen_tanggal_spp':
                                            // Jika sudah dibayar, tampilkan 0
                                            if (isset($dokumen->computed_status) && $dokumen->computed_status === 'sudah_dibayar') {
                                                $value = '0 HARI';
                                            } elseif ($dokumen->tanggal_spp) {
                                                $tanggalSpp = \Carbon\Carbon::parse($dokumen->tanggal_spp)->startOfDay();
                                                $hariIni = \Carbon\Carbon::now()->startOfDay();
                                                $days = $tanggalSpp->lte($hariIni) ? (int) $tanggalSpp->diffInDays($hariIni) : 0;
                                                $value = $days . ' HARI';
                                            } else {
                                                $value = '-';
                                            }
                                            break;
                                        case 'umur_dokumen_tanggal_ba':
                                            // Jika sudah dibayar, tampilkan 0
                                            if (isset($dokumen->computed_status) && $dokumen->computed_status === 'sudah_dibayar') {
                                                $value = '0 HARI';
                                            } elseif ($dokumen->tanggal_berita_acara) {
                                                $tanggalBa = \Carbon\Carbon::parse($dokumen->tanggal_berita_acara)->startOfDay();
                                                $hariIni = \Carbon\Carbon::now()->startOfDay();
                                                $days = $tanggalBa->lte($hariIni) ? (int) $tanggalBa->diffInDays($hariIni) : 0;
                                                $value = $days . ' HARI';
                                            } else {
                                                $value = '-';
                                            }
                                            break;
                                        case 'nilai_belum_siap_bayar':
                                            $value = $dokumen->computed_status === 'belum_siap_dibayar' 
                                                ? 'Rp ' . number_format($dokumen->nilai_rupiah ?? 0, 0, ',', '.')
                                                : '-';
                                            break;
                                        case 'nilai_siap_bayar':
                                            $value = $dokumen->computed_status === 'siap_dibayar' 
                                                ? 'Rp ' . number_format($dokumen->nilai_rupiah ?? 0, 0, ',', '.')
                                                : '-';
                                            break;
                                        case 'nilai_sudah_dibayar':
                                            $value = $dokumen->computed_status === 'sudah_dibayar' 
                                                ? 'Rp ' . number_format($dokumen->nilai_rupiah ?? 0, 0, ',', '.')
                                                : '-';
                                            break;
                                        default:
                                            $value = $dokumen->$col ?? '-';
                                    }
                                @endphp
                                {{ $value }}
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($columns) + 1 }}" class="text-center">Tidak ada data</td>
                    </tr>
                @endforelse

                @if($dokumens->count() > 0)
                    <!-- Grand Total Row for normal mode -->
                    <tr class="grand-total-row">
                        <td colspan="{{ $colspanCount ?? count($columns) + 1 }}" class="text-right" style="border-top: 3px solid #28a745; padding: 5px 3px;">
                            <strong style="font-size: 8px;">GRAND TOTAL:</strong>
                        </td>
                        @foreach($columns as $idx => $col)
                            @if(isset($firstValueIndex) && $firstValueIndex !== null && $idx >= $firstValueIndex)
                                @if($col == 'nilai_rupiah')
                                    <td style="border-top: 3px solid #28a745; padding: 5px 3px;"><strong style="font-size: 8px;">Rp {{ number_format($grandTotalNilai ?? 0, 0, ',', '.') }}</strong></td>
                                @elseif($col == 'nilai_belum_siap_bayar')
                                    <td style="border-top: 3px solid #28a745; padding: 5px 3px;"><strong style="font-size: 8px;">Rp {{ number_format($grandTotalBelum ?? 0, 0, ',', '.') }}</strong></td>
                                @elseif($col == 'nilai_siap_bayar')
                                    <td style="border-top: 3px solid #28a745; padding: 5px 3px;"><strong style="font-size: 8px;">Rp {{ number_format($grandTotalSiap ?? 0, 0, ',', '.') }}</strong></td>
                                @elseif($col == 'nilai_sudah_dibayar')
                                    <td style="border-top: 3px solid #28a745; padding: 5px 3px;"><strong style="font-size: 8px;">Rp {{ number_format($grandTotalSudah ?? 0, 0, ',', '.') }}</strong></td>
                                @elseif(in_array($col, ['umur_dokumen_tanggal_masuk', 'umur_dokumen_tanggal_spp', 'umur_dokumen_tanggal_ba']))
                                    <td style="border-top: 3px solid #28a745; padding: 10px 6px;">-</td>
                                @else
                                    <td style="border-top: 3px solid #28a745; padding: 10px 6px;"></td>
                                @endif
                            @endif
                        @endforeach
                    </tr>
                @endif
            @endif
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ date('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>





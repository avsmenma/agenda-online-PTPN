<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekapan Pembayaran - {{ date('d/m/Y') }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 1cm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #083E40;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #083E40;
            font-size: 18px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
            font-size: 11px;
        }
        .filter-info {
            margin-bottom: 15px;
            font-size: 9px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #083E40;
            color: white;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #ddd;
            font-size: 9px;
        }
        td {
            padding: 6px;
            border: 1px solid #ddd;
            font-size: 9px;
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
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
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
                <th style="width: 30px;">No</th>
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
                                    case 'umur_dokumen_tanggal_masuk':
                                        if ($dokumen->tanggal_masuk) {
                                            $days = now()->diffInDays($dokumen->tanggal_masuk);
                                            $value = $days . ' hari';
                                        } else {
                                            $value = '-';
                                        }
                                        break;
                                    case 'umur_dokumen_tanggal_spp':
                                        if ($dokumen->tanggal_spp) {
                                            $days = now()->diffInDays($dokumen->tanggal_spp);
                                            $value = $days . ' hari';
                                        } else {
                                            $value = '-';
                                        }
                                        break;
                                    case 'umur_dokumen_tanggal_ba':
                                        if ($dokumen->tanggal_berita_acara) {
                                            $days = now()->diffInDays($dokumen->tanggal_berita_acara);
                                            $value = $days . ' hari';
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
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ date('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>





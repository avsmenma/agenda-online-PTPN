<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $title }} - {{ $exportDate }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 20px;
        }
        h1 {
            color: #083E40;
            text-align: center;
            margin-bottom: 10px;
        }
        .header-info {
            text-align: center;
            color: #6c757d;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
            color: white;
            padding: 10px;
            text-align: left;
            border: 1px solid #083E40;
        }
        td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .text-right {
            text-align: right;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #6c757d;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <div class="header-info">
        <p>Tanggal Export: {{ $exportDate }}</p>
        <p>Total Dokumen: {{ $dokumens->count() }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Agenda</th>
                <th>No. SPP</th>
                <th>Tgl SPP</th>
                <th>Vendor</th>
                <th>Kategori</th>
                <th class="text-right">Nilai</th>
                <th class="text-right">Dibayar</th>
                <th class="text-right">Belum Dibayar</th>
                <th>Status</th>
                <th>Persentase</th>
                <th>Umur Hutang</th>
                <th>Posisi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dokumens as $index => $dokumen)
            @php
                $status = $dokumen->status_pembayaran ?? 'belum_lunas';
                $persentase = $dokumen->persentase_pembayaran ?? 0;
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $dokumen->AGENDA ?? '-' }}</td>
                <td>{{ $dokumen->NO_SPP ?? '-' }}</td>
                <td>{{ $dokumen->TGL_SPP ?? '-' }}</td>
                <td>{{ Str::limit($dokumen->VENDOR ?? '-', 30) }}</td>
                <td>{{ $dokumen->KATEGORI ?? '-' }}</td>
                <td class="text-right">{{ number_format($dokumen->NILAI ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($dokumen->JUMLAH_DIBAYAR ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($dokumen->BELUM_DIBAYAR ?? 0, 0, ',', '.') }}</td>
                <td>
                    @if($status == 'lunas')
                        Lunas
                    @elseif($status == 'parsial')
                        Parsial
                    @else
                        Belum Lunas
                    @endif
                </td>
                <td>{{ number_format($persentase, 1) }}%</td>
                <td>{{ $dokumen->UMUR_HUTANG_HARI ?? 0 }} Hari</td>
                <td>{{ $dokumen->POSISI_DOKUMEN ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="13" style="text-align: center; padding: 20px;">Tidak ada data</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>© {{ date('Y') }} Agenda Online - Generated on {{ $exportDate }}</p>
    </div>
</body>
</html>


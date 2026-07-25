<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} — Cetak</title>
    <style>
        {{--
            Dependency-free, self-contained (tanpa Bootstrap/CDN) — view ini dibuka
            langsung sebagai tab baru (respondDocumentExport format=pdf), bukan
            di-embed di layout aplikasi. Warna brand (#083E40) mengikuti tombol
            fullscreen document-role-filter-toolbar.blade.php agar konsisten.
        --}}
        @page { size: A4 landscape; margin: 1cm; }

        * { box-sizing: border-box; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #1f2937;
            margin: 0;
            padding: 16px;
        }

        .print-header {
            text-align: center;
            border-bottom: 2px solid #083E40;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }
        .print-header .instansi {
            font-size: 15px;
            font-weight: 700;
            color: #083E40;
            letter-spacing: .02em;
        }
        .print-header .aplikasi {
            font-size: 11px;
            color: #6b7280;
            margin-top: 2px;
        }
        .print-header .judul {
            font-size: 14px;
            font-weight: 700;
            color: #1f2937;
            margin-top: 10px;
        }
        .print-header .tanggal-cetak {
            font-size: 10px;
            color: #6b7280;
            margin-top: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
        }
        thead th {
            background: #083E40;
            color: #ffffff;
            border: 1px solid #083E40;
            padding: 6px 8px;
            text-align: left;
            font-size: 10px;
            font-weight: 700;
            white-space: nowrap;
        }
        tbody td {
            border: 1px solid #d1d5db;
            padding: 5px 8px;
            font-size: 10px;
            vertical-align: top;
            word-break: break-word;
        }
        tbody tr:nth-child(even) { background: #f8fafc; }
        .col-nomor {
            width: 34px;
            text-align: center;
            white-space: nowrap;
        }
        .empty-row td {
            text-align: center;
            padding: 16px;
            color: #6b7280;
            font-style: italic;
        }

        .print-footer {
            margin-top: 12px;
            font-size: 9px;
            color: #9ca3af;
            text-align: right;
        }

        @media print {
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="print-header">
        <div class="instansi">PTPN IV Regional V</div>
        <div class="aplikasi">Agenda Online — Sistem Persetujuan Dokumen Pembayaran</div>
        <div class="judul">{{ $title }}</div>
        <div class="tanggal-cetak">Dicetak pada: {{ now()->format('d-m-Y H:i') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="col-nomor">No</th>
                @foreach ($columns as $column)
                    <th>{{ $column['label'] ?? $column['key'] ?? '' }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $index => $row)
                <tr>
                    <td class="col-nomor">{{ $index + 1 }}</td>
                    @foreach ($columns as $column)
                        <td>{{ \App\Support\DocumentExporter::cellValue($row, $column['key'] ?? '') }}</td>
                    @endforeach
                </tr>
            @empty
                <tr class="empty-row">
                    <td colspan="{{ count($columns) + 1 }}">Tidak ada data.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="print-footer">Total baris: {{ count($rows) }}</div>

    <script>window.onload = () => window.print();</script>
</body>
</html>

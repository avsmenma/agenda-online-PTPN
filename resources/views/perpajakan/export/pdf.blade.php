<!DOCTYPE html>
<html>

<head>
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10pt;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .meta {
            margin-bottom: 15px;
            font-size: 9pt;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>{{ $title }}</h2>
        <p>Tanggal Export: {{ $date }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="30">No</th>
                @foreach($columns as $col)
                    <th>{{ $availableColumns[$col] ?? $col }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($dokumens as $index => $doc)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    @foreach($columns as $col)
                        <td>
                            @if($col == 'nilai_rupiah')
                                <div class="text-right">Rp {{ number_format($doc->nilai_rupiah, 0, ',', '.') }}</div>
                            @elseif($col == 'status')
                                {{ ucwords(str_replace('_', ' ', $doc->status)) }}
                            @elseif($col == 'tanggal_spp')
                                {{ $doc->tanggal_spp ? $doc->tanggal_spp->format('d/m/Y') : '-' }}
                            @elseif($col == 'deadline_at')
                                {{ $doc->deadline_at ? $doc->deadline_at->format('d/m/Y') : '-' }}
                            @elseif($col == 'created_at')
                                {{ $doc->created_at ? $doc->created_at->format('d/m/Y') : '-' }}
                            @else
                                {{ $doc->$col ?? '-' }}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
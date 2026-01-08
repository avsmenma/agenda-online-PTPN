@extends('layouts/app')
@section('content')

    <style>
        h2 {
            background: linear-gradient(135deg, #083E40 0%, #889717 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .search-box {
            background: #ffffff;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border: 1px solid #e9ecef;
        }

        .search-filter-form {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .search-input-group {
            flex: 1;
            min-width: 250px;
        }

        .search-box .input-group-text {
            background: white;
            border: 1px solid #dee2e6;
            border-right: none;
            border-radius: 8px 0 0 8px;
            padding: 10px 14px;
        }

        .search-box .form-control {
            border: 1px solid #dee2e6;
            border-left: none;
            border-radius: 0 8px 8px 0;
            padding: 10px 14px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .search-box .form-control:focus {
            outline: none;
            border-color: #889717;
            box-shadow: 0 0 0 3px rgba(136, 151, 23, 0.1);
        }

        .year-dropdown-wrapper {
            position: relative;
        }

        .btn-year-select {
            padding: 10px 16px;
            background: white;
            color: #495057;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            min-height: 44px;
            white-space: nowrap;
        }

        .btn-year-select:hover {
            border-color: #889717;
            background: #f8f9fa;
        }

        .status-dropdown-wrapper {
            position: relative;
        }

        .btn-status-select {
            padding: 10px 16px;
            background: white;
            color: #495057;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            min-height: 44px;
            white-space: nowrap;
        }

        .btn-status-select:hover {
            border-color: #889717;
            background: #f8f9fa;
        }

        .btn-filter {
            padding: 10px 20px;
            background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 2px 6px rgba(8, 62, 64, 0.2);
            min-height: 44px;
        }

        .btn-filter:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(8, 62, 64, 0.3);
        }

        .table-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table thead {
            background: linear-gradient(135deg, #083E40 0%, #0a5f52 100%);
        }

        .data-table th {
            padding: 16px;
            color: white;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        .data-table td {
            padding: 14px 16px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f3f5;
            font-size: 14px;
        }

        .data-table tbody tr {
            transition: all 0.2s ease;
        }

        .data-table tbody tr:hover {
            background: #f8f9fa;
        }

        .badge-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-draft {
            background: linear-gradient(135deg, #6c757d 0%, #868e96 100%);
            color: white;
        }

        .badge-terkirim {
            background: linear-gradient(135deg, #28a745 0%, #34c759 100%);
            color: white;
        }

        .badge-selesai {
            background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
            color: white;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            text-decoration: none;
        }

        .btn-edit {
            background: #fff3cd;
            color: #856404;
        }

        .btn-edit:hover {
            background: #ffc107;
            color: #212529;
        }

        .btn-send {
            background: #d4edda;
            color: #155724;
        }

        .btn-send:hover {
            background: #28a745;
            color: white;
        }

        .btn-send:disabled {
            background: #e9ecef;
            color: #6c757d;
            cursor: not-allowed;
        }

        .btn-tracking {
            background: #d1ecf1;
            color: #0c5460;
        }

        .btn-tracking:hover {
            background: #17a2b8;
            color: white;
        }

        .btn-delete {
            background: #f8d7da;
            color: #721c24;
        }

        .btn-delete:hover {
            background: #dc3545;
            color: white;
        }

        .btn-create {
            padding: 12px 24px;
            background: linear-gradient(135deg, #083E40 0%, #0a5f52 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(8, 62, 64, 0.25);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-create:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(8, 62, 64, 0.35);
            color: white;
        }

        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            border-top: 1px solid #e9ecef;
        }

        .per-page-select {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .per-page-select label {
            font-size: 14px;
            color: #495057;
        }

        .per-page-select select {
            padding: 6px 12px;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            font-size: 14px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state i {
            font-size: 80px;
            color: #dee2e6;
            margin-bottom: 20px;
        }

        .empty-state h4 {
            color: #6c757d;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #adb5bd;
            margin-bottom: 20px;
        }
    </style>

    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>
                    <i class="fa-solid fa-file-lines me-2"></i>
                    Daftar Dokumen Bagian {{ $bagianCode }}
                </h2>
                <p class="text-muted mb-0">{{ $bagianName }}</p>
            </div>
            <a href="{{ route('bagian.documents.create') }}" class="btn-create">
                <i class="fa-solid fa-plus"></i>
                Buat Dokumen
            </a>
        </div>

        <!-- Alert Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Search & Filter -->
        <div class="search-box">
            <form action="{{ route('bagian.documents.index') }}" method="GET" class="search-filter-form">
                <div class="search-input-group">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fa-solid fa-search text-muted"></i>
                        </span>
                        <input type="text" name="search" class="form-control"
                            placeholder="Cari nomor agenda, SPP, atau uraian..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="year-dropdown-wrapper">
                    <select name="tahun" class="btn-year-select">
                        <option value="">Semua Tahun</option>
                        @php
                            $currentYear = date('Y');
                            for ($y = $currentYear; $y >= $currentYear - 5; $y--) {
                                $selected = request('tahun') == $y ? 'selected' : '';
                                echo "<option value=\"{$y}\" {$selected}>{$y}</option>";
                            }
                        @endphp
                    </select>
                </div>

                <div class="status-dropdown-wrapper">
                    <select name="status" class="btn-status-select">
                        <option value="">Semua Status</option>
                        <option value="belum dikirim" {{ request('status') == 'belum dikirim' ? 'selected' : '' }}>Belum
                            Dikirim
                        </option>
                        <option value="sent_to_ibub" {{ request('status') == 'sent_to_ibub' ? 'selected' : '' }}>Menunggu
                            Verifikasi
                        </option>
                        <option value="sudah dibayar" {{ request('status') == 'sudah dibayar' ? 'selected' : '' }}>Sudah
                            Dibayar
                        </option>
                    </select>
                </div>

                <button type="submit" class="btn-filter">
                    <i class="fa-solid fa-filter me-1"></i>Filter
                </button>
            </form>
        </div>

        <!-- Document Table -->
        <div class="table-container">
            @if($dokumens->count() > 0)
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nomor Agenda</th>
                                <th>Nomor SPP</th>
                                <th>Tanggal Masuk</th>
                                <th>Nilai Rupiah</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dokumens as $index => $doc)
                                <tr>
                                    <td>{{ $dokumens->firstItem() + $index }}</td>
                                    <td>
                                        <strong style="color: #083E40;">{{ $doc->nomor_agenda }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $doc->bulan ?? '' }} {{ $doc->tahun ?? '' }}</small>
                                    </td>
                                    <td>{{ $doc->nomor_spp }}</td>
                                    <td>{{ $doc->tanggal_masuk ? $doc->tanggal_masuk->format('d/m/Y H:i') : '-' }}</td>
                                    <td>
                                        <strong style="color: #28a745;">Rp.
                                            {{ number_format($doc->nilai_rupiah, 0, ',', '.') }}</strong>
                                    </td>
                                    <td>
                                        @php
                                            $statusLower = strtolower($doc->status ?? '');
                                        @endphp
                                        @if($statusLower == 'belum dikirim')
                                            <span class="badge-status badge-draft">
                                                <i class="fa-solid fa-file-lines"></i>
                                                <span>Belum Dikirim</span>
                                            </span>
                                        @elseif(in_array($statusLower, ['sent_to_ibub', 'pending_approval_ibub']))
                                            <span class="badge-status badge-terkirim">
                                                <i class="fa-solid fa-check"></i>
                                                <span>Terkirim</span>
                                            </span>
                                        @elseif($statusLower == 'sudah dibayar')
                                            <span class="badge-status badge-selesai">
                                                <i class="fa-solid fa-check-double"></i>
                                                <span>Selesai</span>
                                            </span>
                                        @else
                                            <span class="badge-status badge-terkirim">
                                                <i class="fa-solid fa-spinner"></i>
                                                <span>{{ ucwords(str_replace('_', ' ', $doc->status)) }}</span>
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            @if($statusLower == 'belum dikirim')
                                                <a href="{{ route('bagian.documents.edit', $doc) }}" class="btn-action btn-edit"
                                                    title="Edit">
                                                    <i class="fa-solid fa-pen"></i>
                                                    <span>Edit</span>
                                                </a>
                                                <form action="{{ route('bagian.documents.send-to-verifikasi', $doc) }}" method="POST"
                                                    class="d-inline" onsubmit="return confirm('Kirim dokumen ini ke Team Verifikasi?')">
                                                    @csrf
                                                    <button type="submit" class="btn-action btn-send" title="Kirim">
                                                        <i class="fa-solid fa-paper-plane"></i>
                                                        <span>Kirim</span>
                                                    </button>
                                                </form>
                                                <form action="{{ route('bagian.documents.destroy', $doc) }}" method="POST"
                                                    class="d-inline" onsubmit="return confirm('Hapus dokumen ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn-action btn-delete" title="Hapus">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <a href="{{ route('owner.workflow', $doc->id) }}" class="btn-action btn-tracking"
                                                    title="Tracking">
                                                    <i class="fa-solid fa-route"></i>
                                                    <span>Tracking</span>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="pagination-container">
                    <div class="per-page-select">
                        <label>Baris per halaman:</label>
                        <select onchange="changePerPage(this.value)">
                            <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                        <span class="text-muted">
                            Menampilkan {{ $dokumens->firstItem() }} - {{ $dokumens->lastItem() }} dari {{ $dokumens->total() }}
                            hasil
                        </span>
                    </div>
                    <div>
                        {{ $dokumens->appends(request()->query())->links() }}
                    </div>
                </div>
            @else
                <div class="empty-state">
                    <i class="fa-solid fa-folder-open"></i>
                    <h4>Belum ada dokumen</h4>
                    <p>Buat dokumen pertama Anda sekarang</p>
                    <a href="{{ route('bagian.documents.create') }}" class="btn-create">
                        <i class="fa-solid fa-plus"></i>
                        Buat Dokumen
                    </a>
                </div>
            @endif
        </div>
    </div>

    <script>
        function changePerPage(value) {
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', value);
            url.searchParams.delete('page'); // Reset to page 1
            window.location.href = url.toString();
        }
    </script>

@endsection
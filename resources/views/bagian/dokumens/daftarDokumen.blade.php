@extends('layouts.app')

@section('title', 'Daftar Dokumen Bagian ' . $bagianCode)

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 style="color: #083E40; font-weight: 700;">
                    <i class="fa-solid fa-file-lines me-2"></i>Daftar Dokumen Bagian {{ $bagianCode }}
                </h2>
                <p class="text-muted mb-0">{{ $bagianName }}</p>
            </div>
            <a href="{{ route('bagian.documents.create') }}" class="btn btn-primary"
                style="background: linear-gradient(135deg, #083E40 0%, #0a5f52 100%); border: none;">
                <i class="fa-solid fa-plus me-2"></i>Buat Dokumen
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
        <div class="card mb-4" style="border: none; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.06);">
            <div class="card-body">
                <form action="{{ route('bagian.documents.index') }}" method="GET" class="row g-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text" style="background: #f8f9fa; border-right: none;">
                                <i class="fa-solid fa-search text-muted"></i>
                            </span>
                            <input type="text" name="search" class="form-control"
                                placeholder="Cari nomor agenda, SPP, atau uraian..." value="{{ request('search') }}"
                                style="border-left: none;">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="belum dikirim" {{ request('status') == 'belum dikirim' ? 'selected' : '' }}>Belum
                                Dikirim</option>
                            <option value="sent_to_ibub" {{ request('status') == 'sent_to_ibub' ? 'selected' : '' }}>Menunggu
                                Verifikasi</option>
                            <option value="sudah dibayar" {{ request('status') == 'sudah dibayar' ? 'selected' : '' }}>Sudah
                                Dibayar</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100" style="background: #083E40; border: none;">
                            <i class="fa-solid fa-filter me-1"></i>Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Document Table -->
        <div class="card" style="border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
            <div class="card-body p-0">
                @if($dokumens->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background: linear-gradient(135deg, #083E40 0%, #0a5f52 100%);">
                                <tr>
                                    <th style="padding: 16px; color: white; font-size: 12px; font-weight: 600;">NO</th>
                                    <th style="padding: 16px; color: white; font-size: 12px; font-weight: 600;">NO. AGENDA</th>
                                    <th style="padding: 16px; color: white; font-size: 12px; font-weight: 600;">NO. SPP</th>
                                    <th style="padding: 16px; color: white; font-size: 12px; font-weight: 600;">TANGGAL</th>
                                    <th style="padding: 16px; color: white; font-size: 12px; font-weight: 600;">NILAI</th>
                                    <th style="padding: 16px; color: white; font-size: 12px; font-weight: 600;">STATUS</th>
                                    <th style="padding: 16px; color: white; font-size: 12px; font-weight: 600;">AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dokumens as $index => $doc)
                                    <tr>
                                        <td style="padding: 16px; vertical-align: middle;">{{ $dokumens->firstItem() + $index }}
                                        </td>
                                        <td style="padding: 16px; vertical-align: middle;">
                                            <strong style="color: #083E40;">{{ $doc->nomor_agenda }}</strong>
                                        </td>
                                        <td style="padding: 16px; vertical-align: middle;">{{ $doc->nomor_spp }}</td>
                                        <td style="padding: 16px; vertical-align: middle;">
                                            {{ $doc->tanggal_spp ? $doc->tanggal_spp->format('d/m/Y') : '-' }}
                                        </td>
                                        <td style="padding: 16px; vertical-align: middle;">
                                            <strong style="color: #28a745;">Rp.
                                                {{ number_format($doc->nilai_rupiah, 0, ',', '.') }}</strong>
                                        </td>
                                        <td style="padding: 16px; vertical-align: middle;">
                                            @php
                                                $statusColor = match ($doc->status) {
                                                    'belum dikirim' => '#ffc107',
                                                    'sent_to_ibub' => '#17a2b8',
                                                    'sudah dibayar' => '#28a745',
                                                    default => '#6c757d'
                                                };
                                                $statusText = match ($doc->status) {
                                                    'belum dikirim' => 'Belum Dikirim',
                                                    'sent_to_ibub' => 'Menunggu Verifikasi',
                                                    'sudah dibayar' => 'Selesai',
                                                    default => ucwords(str_replace('_', ' ', $doc->status))
                                                };
                                            @endphp
                                            <span class="badge"
                                                style="background: {{ $statusColor }}; padding: 6px 12px; border-radius: 20px;">
                                                {{ $statusText }}
                                            </span>
                                        </td>
                                        <td style="padding: 16px; vertical-align: middle;">
                                            <div class="d-flex gap-2">
                                                @if($doc->status == 'belum dikirim')
                                                    <a href="{{ route('bagian.documents.edit', $doc) }}"
                                                        class="btn btn-sm btn-outline-primary" title="Edit">
                                                        <i class="fa-solid fa-pen"></i>
                                                    </a>
                                                    <form action="{{ route('bagian.documents.send-to-verifikasi', $doc) }}"
                                                        method="POST" class="d-inline"
                                                        onsubmit="return confirm('Kirim dokumen ini ke Team Verifikasi?')">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success" title="Kirim">
                                                            <i class="fa-solid fa-paper-plane"></i>
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('bagian.documents.destroy', $doc) }}" method="POST"
                                                        class="d-inline" onsubmit="return confirm('Hapus dokumen ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <a href="{{ route('owner.workflow', $doc->id) }}"
                                                        class="btn btn-sm btn-outline-info" title="Tracking">
                                                        <i class="fa-solid fa-route"></i>
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
                    <div class="d-flex justify-content-between align-items-center p-3" style="border-top: 1px solid #e9ecef;">
                        <div class="text-muted" style="font-size: 13px;">
                            Menampilkan {{ $dokumens->firstItem() }} - {{ $dokumens->lastItem() }} dari {{ $dokumens->total() }}
                            dokumen
                        </div>
                        {{ $dokumens->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fa-solid fa-folder-open" style="font-size: 64px; color: #dee2e6; margin-bottom: 20px;"></i>
                        <h5 class="text-muted">Belum ada dokumen</h5>
                        <p class="text-muted mb-4">Buat dokumen pertama Anda sekarang</p>
                        <a href="{{ route('bagian.documents.create') }}" class="btn btn-primary"
                            style="background: #083E40; border: none;">
                            <i class="fa-solid fa-plus me-2"></i>Buat Dokumen
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
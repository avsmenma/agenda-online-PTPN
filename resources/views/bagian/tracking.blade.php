@extends('layouts.app')

@section('title', 'Tracking Dokumen - Bagian ' . $bagianCode)

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 style="color: #083E40; font-weight: 700;">
                    <i class="fa-solid fa-route me-2"></i>Tracking Dokumen
                </h2>
                <p class="text-muted mb-0">Lacak status dokumen Bagian {{ $bagianName }}</p>
            </div>
            <a href="{{ route('bagian.dashboard') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-2"></i>Dashboard
            </a>
        </div>

        <!-- Search -->
        <div class="card mb-4" style="border: none; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.06);">
            <div class="card-body">
                <form action="{{ route('bagian.tracking') }}" method="GET" class="row g-3">
                    <div class="col-md-10">
                        <div class="input-group">
                            <span class="input-group-text" style="background: #f8f9fa; border-right: none;">
                                <i class="fa-solid fa-search text-muted"></i>
                            </span>
                            <input type="text" name="search" class="form-control"
                                placeholder="Cari nomor agenda atau nomor SPP..." value="{{ request('search') }}"
                                style="border-left: none;">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100" style="background: #083E40; border: none;">
                            <i class="fa-solid fa-search me-1"></i>Cari
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tracking Table -->
        <div class="card" style="border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
            <div class="card-body p-0">
                @if($dokumens->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background: linear-gradient(135deg, #083E40 0%, #0a5f52 100%);">
                                <tr>
                                    <th style="padding: 16px; color: white; font-size: 12px; font-weight: 600;">NO. AGENDA</th>
                                    <th style="padding: 16px; color: white; font-size: 12px; font-weight: 600;">NO. SPP</th>
                                    <th style="padding: 16px; color: white; font-size: 12px; font-weight: 600;">NILAI</th>
                                    <th style="padding: 16px; color: white; font-size: 12px; font-weight: 600;">STATUS</th>
                                    <th style="padding: 16px; color: white; font-size: 12px; font-weight: 600;">POSISI SAAT INI
                                    </th>
                                    <th style="padding: 16px; color: white; font-size: 12px; font-weight: 600;">AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dokumens as $doc)
                                    <tr>
                                        <td style="padding: 16px; vertical-align: middle;">
                                            <strong style="color: #083E40;">{{ $doc->nomor_agenda }}</strong>
                                        </td>
                                        <td style="padding: 16px; vertical-align: middle;">{{ $doc->nomor_spp }}</td>
                                        <td style="padding: 16px; vertical-align: middle;">
                                            <strong style="color: #28a745;">Rp.
                                                {{ number_format($doc->nilai_rupiah, 0, ',', '.') }}</strong>
                                        </td>
                                        <td style="padding: 16px; vertical-align: middle;">
                                            @php
                                                $statusConfig = [
                                                    'belum dikirim' => ['color' => '#ffc107', 'icon' => 'fa-clock', 'text' => 'Belum Dikirim'],
                                                    'sent_to_team_verifikasi' => ['color' => '#17a2b8', 'icon' => 'fa-paper-plane', 'text' => 'Verifikasi'],
                                                    'sent_to_perpajakan' => ['color' => '#6f42c1', 'icon' => 'fa-calculator', 'text' => 'Perpajakan'],
                                                    'sent_to_akutansi' => ['color' => '#fd7e14', 'icon' => 'fa-book', 'text' => 'Akutansi'],
                                                    'sent_to_pembayaran' => ['color' => '#20c997', 'icon' => 'fa-money-bill', 'text' => 'Pembayaran'],
                                                    'sudah dibayar' => ['color' => '#28a745', 'icon' => 'fa-check-circle', 'text' => 'Selesai'],
                                                ];
                                                $config = $statusConfig[$doc->status] ?? ['color' => '#6c757d', 'icon' => 'fa-question', 'text' => ucwords(str_replace('_', ' ', $doc->status))];
                                            @endphp
                                            <span class="badge d-flex align-items-center gap-1"
                                                style="background: {{ $config['color'] }}; padding: 8px 12px; border-radius: 20px; width: fit-content;">
                                                <i class="fa-solid {{ $config['icon'] }}"></i>
                                                {{ $config['text'] }}
                                            </span>
                                        </td>
                                        <td style="padding: 16px; vertical-align: middle;">
                                            @php
                                                $handlerNames = [
                                                    'bagian_' . strtolower($bagianCode) => 'Bagian ' . $bagianCode,
                                                    'team_verifikasi' => 'Team Verifikasi',
                                                    'perpajakan' => 'Perpajakan',
                                                    'akutansi' => 'Akutansi',
                                                    'pembayaran' => 'Pembayaran',
                                                ];
                                            @endphp
                                            <span style="color: #6c757d;">
                                                <i class="fa-solid fa-location-dot me-1"></i>
                                                {{ $handlerNames[$doc->current_handler] ?? ucwords(str_replace('_', ' ', $doc->current_handler)) }}
                                            </span>
                                        </td>
                                        <td style="padding: 16px; vertical-align: middle;">
                                            <a href="{{ route('owner.workflow', $doc->id) }}" class="btn btn-sm"
                                                style="background: linear-gradient(135deg, #083E40 0%, #0a5f52 100%); color: white; border-radius: 8px;">
                                                <i class="fa-solid fa-eye me-1"></i>Lihat Detail
                                            </a>
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
                        <i class="fa-solid fa-route" style="font-size: 64px; color: #dee2e6; margin-bottom: 20px;"></i>
                        <h5 class="text-muted">Tidak ada dokumen untuk dilacak</h5>
                        <p class="text-muted mb-4">Buat dokumen terlebih dahulu</p>
                        <a href="{{ route('bagian.documents.create') }}" class="btn btn-primary"
                            style="background: #083E40; border: none;">
                            <i class="fa-solid fa-plus me-2"></i>Buat Dokumen
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Legend -->
        <div class="card mt-4" style="border: none; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.06);">
            <div class="card-body">
                <h6 style="color: #083E40; font-weight: 600; margin-bottom: 12px;">
                    <i class="fa-solid fa-info-circle me-2"></i>Legenda Status
                </h6>
                <div class="d-flex flex-wrap gap-3">
                    <span class="badge" style="background: #ffc107; padding: 6px 12px;"><i
                            class="fa-solid fa-clock me-1"></i>Belum Dikirim</span>
                    <span class="badge" style="background: #17a2b8; padding: 6px 12px;"><i
                            class="fa-solid fa-paper-plane me-1"></i>Verifikasi</span>
                    <span class="badge" style="background: #6f42c1; padding: 6px 12px;"><i
                            class="fa-solid fa-calculator me-1"></i>Perpajakan</span>
                    <span class="badge" style="background: #fd7e14; padding: 6px 12px;"><i
                            class="fa-solid fa-book me-1"></i>Akutansi</span>
                    <span class="badge" style="background: #20c997; padding: 6px 12px;"><i
                            class="fa-solid fa-money-bill me-1"></i>Pembayaran</span>
                    <span class="badge" style="background: #28a745; padding: 6px 12px;"><i
                            class="fa-solid fa-check-circle me-1"></i>Selesai</span>
                </div>
            </div>
        </div>
    </div>
@endsection




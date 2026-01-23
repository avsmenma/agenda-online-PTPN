@extends('layouts.app')

@section('title', 'Dashboard Bagian ' . $bagianCode)

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 style="color: #083E40; font-weight: 700;">
                    <i class="fa-solid fa-building me-2"></i>Dashboard Bagian {{ $bagianCode }}
                </h2>
                <p class="text-muted mb-0">Selamat datang, {{ $bagianName }}</p>
            </div>
            <a href="{{ route('bagian.documents.create') }}" class="btn btn-primary"
                style="background: linear-gradient(135deg, #083E40 0%, #0a5f52 100%); border: none;">
                <i class="fa-solid fa-plus me-2"></i>Buat Dokumen Baru
            </a>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card h-100" style="border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div
                                style="width: 50px; height: 50px; background: linear-gradient(135deg, #083E40 0%, #0a5f52 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                <i class="fa-solid fa-file-lines text-white" style="font-size: 20px;"></i>
                            </div>
                            <div class="ms-3">
                                <h3 class="mb-0" style="color: #083E40; font-weight: 700;">{{ $totalDokumen }}</h3>
                                <p class="mb-0 text-muted" style="font-size: 13px;">Total Dokumen</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100" style="border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div
                                style="width: 50px; height: 50px; background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                <i class="fa-solid fa-clock text-white" style="font-size: 20px;"></i>
                            </div>
                            <div class="ms-3">
                                <h3 class="mb-0" style="color: #e0a800; font-weight: 700;">{{ $dokumenBelumDikirim }}</h3>
                                <p class="mb-0 text-muted" style="font-size: 13px;">Belum Dikirim</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100" style="border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div
                                style="width: 50px; height: 50px; background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                <i class="fa-solid fa-paper-plane text-white" style="font-size: 20px;"></i>
                            </div>
                            <div class="ms-3">
                                <h3 class="mb-0" style="color: #138496; font-weight: 700;">{{ $dokumenTerkirim }}</h3>
                                <p class="mb-0 text-muted" style="font-size: 13px;">Terkirim</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100" style="border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div
                                style="width: 50px; height: 50px; background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                <i class="fa-solid fa-check-circle text-white" style="font-size: 20px;"></i>
                            </div>
                            <div class="ms-3">
                                <h3 class="mb-0" style="color: #1e7e34; font-weight: 700;">{{ $dokumenSelesai }}</h3>
                                <p class="mb-0 text-muted" style="font-size: 13px;">Selesai</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <a href="{{ route('bagian.documents.index') }}" class="card text-decoration-none h-100"
                    style="border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); transition: transform 0.2s;">
                    <div class="card-body text-center py-4">
                        <i class="fa-solid fa-list" style="font-size: 32px; color: #083E40; margin-bottom: 12px;"></i>
                        <h5 style="color: #083E40; font-weight: 600;">Daftar Dokumen</h5>
                        <p class="text-muted mb-0" style="font-size: 13px;">Lihat semua dokumen {{ $bagianCode }}</p>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="{{ route('bagian.documents.create') }}" class="card text-decoration-none h-100"
                    style="border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); transition: transform 0.2s;">
                    <div class="card-body text-center py-4">
                        <i class="fa-solid fa-plus-circle"
                            style="font-size: 32px; color: #28a745; margin-bottom: 12px;"></i>
                        <h5 style="color: #28a745; font-weight: 600;">Buat Dokumen</h5>
                        <p class="text-muted mb-0" style="font-size: 13px;">Buat dokumen baru</p>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="{{ route('bagian.tracking') }}" class="card text-decoration-none h-100"
                    style="border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); transition: transform 0.2s;">
                    <div class="card-body text-center py-4">
                        <i class="fa-solid fa-route" style="font-size: 32px; color: #17a2b8; margin-bottom: 12px;"></i>
                        <h5 style="color: #17a2b8; font-weight: 600;">Tracking</h5>
                        <p class="text-muted mb-0" style="font-size: 13px;">Lacak status dokumen</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Recent Documents -->
        <div class="card" style="border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
            <div class="card-header" style="background: transparent; border-bottom: 1px solid #e9ecef; padding: 20px;">
                <h5 style="color: #083E40; font-weight: 600; margin: 0;">
                    <i class="fa-solid fa-clock-rotate-left me-2"></i>Dokumen Terbaru
                </h5>
            </div>
            <div class="card-body p-0">
                @if($recentDokumens->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background: #f8f9fa;">
                                <tr>
                                    <th style="padding: 16px; font-size: 12px; font-weight: 600; color: #6c757d;">NO. AGENDA
                                    </th>
                                    <th style="padding: 16px; font-size: 12px; font-weight: 600; color: #6c757d;">NO. SPP</th>
                                    <th style="padding: 16px; font-size: 12px; font-weight: 600; color: #6c757d;">URAIAN</th>
                                    <th style="padding: 16px; font-size: 12px; font-weight: 600; color: #6c757d;">NILAI</th>
                                    <th style="padding: 16px; font-size: 12px; font-weight: 600; color: #6c757d;">STATUS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentDokumens as $doc)
                                    <tr>
                                        <td style="padding: 16px; vertical-align: middle;">
                                            <strong style="color: #083E40;">{{ $doc->nomor_agenda }}</strong>
                                        </td>
                                        <td style="padding: 16px; vertical-align: middle;">{{ $doc->nomor_spp }}</td>
                                        <td style="padding: 16px; vertical-align: middle;">
                                            <span
                                                style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                                {{ Str::limit($doc->uraian_spp, 50) }}
                                            </span>
                                        </td>
                                        <td style="padding: 16px; vertical-align: middle;">
                                            <strong style="color: #28a745;">Rp.
                                                {{ number_format($doc->nilai_rupiah, 0, ',', '.') }}</strong>
                                        </td>
                                        <td style="padding: 16px; vertical-align: middle;">
                                            @php
                                                $statusColor = match ($doc->status) {
                                                    'belum dikirim' => '#ffc107',
                                                    'sent_to_Team Verifikasi' => '#17a2b8',
                                                    'sudah dibayar' => '#28a745',
                                                    default => '#6c757d'
                                                };
                                            @endphp
                                            <span class="badge"
                                                style="background: {{ $statusColor }}; padding: 6px 12px; border-radius: 20px;">
                                                {{ ucwords(str_replace('_', ' ', $doc->status)) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fa-solid fa-inbox" style="font-size: 48px; color: #dee2e6; margin-bottom: 16px;"></i>
                        <p class="text-muted">Belum ada dokumen</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection



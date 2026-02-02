@extends('layouts.app')

@section('title', 'Programmer Dashboard')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <h2>Programmer Dashboard</h2>
                <p class="text-muted">Special operations panel for developer/programmer</p>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">Total Dokumen</h6>
                        <h3 class="mb-0 text-primary">{{ number_format($stats['total_dokumens']) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">Operator</h6>
                        <h3 class="mb-0 text-info">{{ number_format($stats['dokumens_operator']) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">Verifikasi</h6>
                        <h3 class="mb-0 text-warning">{{ number_format($stats['dokumens_verifikasi']) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">Perpajakan</h6>
                        <h3 class="mb-0 text-secondary">{{ number_format($stats['dokumens_perpajakan']) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">Akutansi</h6>
                        <h3 class="mb-0 text-dark">{{ number_format($stats['dokumens_akutansi']) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">Pembayaran</h6>
                        <h3 class="mb-0 text-success">{{ number_format($stats['dokumens_pembayaran']) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Special Operations</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <a href="{{ route('programmer.bulk-to-payment.form') }}"
                                class="list-group-item list-group-item-action d-flex align-items-center">
                                <div class="me-3">
                                    <i class="fas fa-fast-forward fa-2x text-warning"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Bulk Direct to Payment</h6>
                                    <small class="text-muted">Kirim dokumen langsung ke Pembayaran (skip workflow)</small>
                                </div>
                                <i class="fas fa-chevron-right ms-auto"></i>
                            </a>
                            <a href="{{ route('programmer.document-tools') }}"
                                class="list-group-item list-group-item-action d-flex align-items-center">
                                <div class="me-3">
                                    <i class="fas fa-tools fa-2x text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Document Tools</h6>
                                    <small class="text-muted">Lihat ID dokumen dan edit timestamp role</small>
                                </div>
                                <i class="fas fa-chevron-right ms-auto"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-2"><strong>Login sebagai:</strong> {{ Auth::user()->name ?? 'Programmer' }}</p>
                        <p class="mb-2"><strong>Role:</strong> <span
                                class="badge bg-primary">{{ Auth::user()->role ?? 'programmer' }}</span></p>
                        <p class="mb-0"><strong>Waktu Server:</strong> {{ now()->format('d M Y, H:i:s') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
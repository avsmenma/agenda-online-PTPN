@extends('layouts.app')
@section('content')

<div class="container-fluid py-4">
    <h2 class="text-center mb-4">
        <i class="fas fa-file-import me-2"></i>
        Import Data CSV
    </h2>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Import Form Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-upload me-2"></i>
                        Upload File CSV
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Success/Error Messages -->
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- Import Form -->
                    <form action="{{ route('dashboard-pembayaran.import-csv') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="csv_file" class="form-label fw-bold">
                                        <i class="fas fa-file-csv me-1"></i>
                                        Pilih File CSV
                                    </label>
                                    <input type="file"
                                           name="csv_file"
                                           id="csv_file"
                                           class="form-control"
                                           accept=".csv,.txt"
                                           required>
                                    <div class="form-text text-muted">
                                        Format: CSV (Comma/Semicolon separated) - Maksimal: 10MB
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-success btn-lg">
                                            <i class="fas fa-upload me-2"></i>
                                            Import CSV
                                        </button>

                                        <a href="{{ route('dashboard-pembayaran.index') }}"
                                           class="btn btn-outline-secondary btn-lg">
                                            <i class="fas fa-arrow-left me-2"></i>
                                            Kembali
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Instructions -->
                    <div class="mt-4">
                        <div class="card border-info">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Petunjuk Import CSV
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="fw-bold text-info">
                                            <i class="fas fa-download me-1"></i>
                                            1. Download Template
                                        </h6>
                                        <p class="mb-2">
                                            Download template CSV untuk memastikan format yang benar.
                                        </p>
                                        <div class="d-grid gap-2">
                                            <a href="{{ route('dashboard-pembayaran.download-csv-template') }}"
                                               class="btn btn-info">
                                                <i class="fas fa-download me-2"></i>
                                                Download Template
                                            </a>

                                            <button type="button"
                                                    class="btn btn-outline-info"
                                                    onclick="window.open('{{ asset('DATA 12.csv') }}', '_blank')">
                                                <i class="fas fa-eye me-2"></i>
                                                Lihat Contoh
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="fw-bold text-warning">
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            2. Format Data
                                        </h6>
                                        <ul class="list-unstyled">
                                            <li class="mb-2">
                                                <strong>Kolom Wajib:</strong>
                                                <ul class="mt-1 ms-3">
                                                    <li>no_spp (Nomor SPP)</li>
                                                    <li>dibayar_kepada (Vendor)</li>
                                                    <li>nilai_rupiah (Jumlah)</li>
                                                </ul>
                                            </li>
                                            <li class="mb-2">
                                                <strong>Penting:</strong>
                                                <ul class="mt-1 ms-3">
                                                    <li>Gunakan format sesuai template</li>
                                                    <li>Data duplikat akan di-update</li>
                                                    <li>Maksimal file: 10MB</li>
                                                    <li>Import akan memproses 800+ baris data</li>
                                                </ul>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Indicator -->
                    <div class="mt-3" id="importProgress" style="display: none;">
                        <div class="alert alert-info">
                            <div class="d-flex align-items-center">
                                <div class="spinner-border spinner-border-sm me-3" role="status"></div>
                                <span>
                                    <strong>Sedang Import:</strong> Mohon tunggu, proses ini mungkin memerlukan beberapa saat...
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const progressDiv = document.getElementById('importProgress');
            const submitButton = form.querySelector('button[type="submit"]');

            form.addEventListener('submit', function(e) {
                // Show progress
                progressDiv.style.display = 'block';
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Processing...';

                // Hide form during processing
                form.style.opacity = '0.5';
                form.style.pointerEvents = 'none';
            });

            // File validation
            const fileInput = document.getElementById('csv_file');
            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                const maxSize = 10 * 1024 * 1024; // 10MB

                if (file) {
                    if (file.size > maxSize) {
                        alert('File terlalu besar! Maksimal 10MB.');
                        fileInput.value = '';
                    }

                    const validTypes = ['text/csv', 'text/plain', 'application/csv'];
                    if (!validTypes.includes(file.type) && !file.name.match(/\.(csv|txt)$/i)) {
                        alert('Format file tidak valid! Hanya file CSV atau TXT yang diperbolehkan.');
                        fileInput.value = '';
                    }
                }
            });
        });
    </script>
</div>

<style>
    .card {
        border: none;
        box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1);
        border-radius: 16px;
    }

    .card-header {
        background: linear-gradient(135deg, #083E40 0%, #889717 100%);
        color: white;
        border: none;
        border-radius: 16px 16px 0 0 !important;
    }

    .btn-success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border: none;
        color: white;
    }

    .btn-success:hover {
        background: linear-gradient(135deg, #20c997 0%, #28a745 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(40, 167, 69, 0.3);
    }

    .alert-info {
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        border: 1px solid #3182ce;
        color: #0c5460;
    }

    .list-unstyled ul {
        list-style: none;
        padding-left: 0;
    }

    .list-unstyled li {
        margin-bottom: 0.5rem;
    }

    .spinner-border-sm {
        width: 1rem;
        height: 1rem;
    }
</style>
@endsection




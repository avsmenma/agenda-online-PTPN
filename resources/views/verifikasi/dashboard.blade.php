@extends('layouts.app')

@section('title', 'Dashboard Verifikasi - Agenda Online PTPN')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard Verifikasi</h1>
        <div class="d-flex align-items-center">
            <span class="badge bg-primary fs-6 me-3">
                <i class="fas fa-user-shield me-1"></i> Verifikasi
            </span>
            <a href="{{ route('logout') }}" class="btn btn-outline-danger btn-sm" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fas fa-sign-out-alt me-1"></i> Keluar
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </div>
    </div>

    <!-- Welcome Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-left-primary shadow h-100 py-3">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Selamat Datang
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ Auth::user()->name }}
                            </div>
                            <div class="text-muted">
                                <small>Role: Verifikasi</small>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-shield fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <!-- Total Dokumen Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Total Dokumen
                            </div>
                            <div class="row no-gutters align-items-center">
                                <div class="col-auto">
                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800" id="totalDocuments">
                                        0
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="progress progress-sm mr-2">
                                        <div class="progress-bar bg-info" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Menunggu Verifikasi Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Menunggu Verifikasi
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="pendingVerification">
                                0
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Terverifikasi Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Terverifikasi
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="verifiedDocuments">
                                0
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ditolak Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Ditolak
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="rejectedDocuments">
                                0
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Documents Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list me-2"></i>Dokumen Menunggu Verifikasi
                    </h6>
                    <div>
                        <button class="btn btn-primary btn-sm" onclick="refreshData()">
                            <i class="fas fa-sync-alt me-1"></i> Refresh
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered" id="documentsTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>No. Agenda</th>
                                    <th>Perihal</th>
                                    <th>Pengirim</th>
                                    <th>Tanggal Masuk</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="6" class="text-center">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                        <p class="mt-2">Memuat data...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-refresh data every 30 seconds
let refreshInterval;

function loadStatistics() {
    fetch('/api/verification/statistics')
        .then(response => response.json())
        .then(data => {
            document.getElementById('totalDocuments').textContent = data.total || 0;
            document.getElementById('pendingVerification').textContent = data.pending || 0;
            document.getElementById('verifiedDocuments').textContent = data.verified || 0;
            document.getElementById('rejectedDocuments').textContent = data.rejected || 0;
        })
        .catch(error => {
            console.error('Error loading statistics:', error);
        });
}

function loadDocuments() {
    fetch('/api/verification/pending-documents')
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector('#documentsTable tbody');

            if (data.documents && data.documents.length > 0) {
                tbody.innerHTML = data.documents.map((doc, index) => `
                    <tr>
                        <td>${doc.no_agenda || '-'}</td>
                        <td>${doc.perihal || '-'}</td>
                        <td>${doc.pengirim || '-'}</td>
                        <td>${formatDate(doc.tanggal_masuk)}</td>
                        <td>
                            <span class="badge bg-warning">
                                <i class="fas fa-clock me-1"></i>Menunggu Verifikasi
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="/verification/${doc.id}/review" class="btn btn-primary">
                                    <i class="fas fa-eye"></i> Review
                                </a>
                                <button class="btn btn-success" onclick="approveDocument(${doc.id})">
                                    <i class="fas fa-check"></i> Setujui
                                </button>
                                <button class="btn btn-danger" onclick="rejectDocument(${doc.id})">
                                    <i class="fas fa-times"></i> Tolak
                                </button>
                            </div>
                        </td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>Tidak ada dokumen yang menunggu verifikasi</p>
                        </td>
                    </tr>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading documents:', error);
            const tbody = document.querySelector('#documentsTable tbody');
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-danger">
                        <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                        <p>Gagal memuat data. Silakan refresh halaman.</p>
                    </td>
                </tr>
            `;
        });
}

function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function refreshData() {
    loadStatistics();
    loadDocuments();

    // Show loading state
    const refreshBtn = document.querySelector('button[onclick="refreshData()"]');
    const originalHTML = refreshBtn.innerHTML;
    refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Loading...';
    refreshBtn.disabled = true;

    setTimeout(() => {
        refreshBtn.innerHTML = originalHTML;
        refreshBtn.disabled = false;
    }, 1000);
}

function approveDocument(docId) {
    if (confirm('Apakah Anda yakin ingin menyetujui dokumen ini?')) {
        fetch(`/verification/${docId}/approve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Dokumen berhasil disetujui!');
                refreshData();
            } else {
                alert('Gagal menyetujui dokumen: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error approving document:', error);
            alert('Terjadi kesalahan. Silakan coba lagi.');
        });
    }
}

function rejectDocument(docId) {
    const reason = prompt('Alasan penolakan:');
    if (reason) {
        fetch(`/verification/${docId}/reject`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ reason: reason })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Dokumen berhasil ditolak!');
                refreshData();
            } else {
                alert('Gagal menolak dokumen: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error rejecting document:', error);
            alert('Terjadi kesalahan. Silakan coba lagi.');
        });
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    loadStatistics();
    loadDocuments();

    // Set up auto-refresh
    refreshInterval = setInterval(() => {
        loadStatistics();
        loadDocuments();
    }, 30000); // Refresh every 30 seconds
});

// Clean up interval when page is hidden
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        clearInterval(refreshInterval);
    } else {
        refreshInterval = setInterval(() => {
            loadStatistics();
            loadDocuments();
        }, 30000);
    }
});
</script>
@endsection




@extends('layouts.app')

@section('title', 'Database Tools')

@section('content')
    <style>
        .db-tools-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }

        .db-tools-header {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            border-radius: 12px 12px 0 0;
        }

        .db-export-header {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
            border-radius: 12px 12px 0 0;
        }

        .table-count {
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
        }

        .table-count.has-data {
            background: #fee2e2;
            color: #dc2626;
        }

        .table-count.empty {
            background: #d1fae5;
            color: #047857;
        }

        .danger-zone {
            border: 2px dashed #ef4444;
            border-radius: 12px;
            padding: 24px;
            background: #fef2f2;
        }

        .btn-danger-action {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border: none;
            padding: 12px 24px;
            font-weight: 600;
        }

        .btn-danger-action:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        }

        .confirmation-input {
            border: 2px solid #ef4444;
            font-size: 16px;
            text-align: center;
            font-weight: bold;
            letter-spacing: 2px;
        }

        .confirmation-input:focus {
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2);
        }

        .export-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(37, 99, 235, 0.12);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .export-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(37, 99, 235, 0.18);
        }

        .btn-export {
            border: none;
            padding: 16px 24px;
            font-weight: 600;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
        }

        .btn-export-agenda {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            color: white;
        }

        .btn-export-agenda:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e3a8a 100%);
            color: white;
        }

        .btn-export-cashbank {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: white;
        }

        .btn-export-cashbank:hover {
            background: linear-gradient(135deg, #047857 0%, #065f46 100%);
            color: white;
        }

        .btn-export:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .export-db-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .export-db-icon.agenda {
            background: rgba(37, 99, 235, 0.1);
            color: #2563eb;
        }

        .export-db-icon.cashbank {
            background: rgba(5, 150, 105, 0.1);
            color: #059669;
        }

        .export-info {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 10px;
            padding: 14px 18px;
        }

        .dark .export-info {
            background: #1e293b;
            border-color: #334155;
        }
    </style>

    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('programmer.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Database Tools</li>
                    </ol>
                </nav>
                <h2><i class="fas fa-database text-primary me-2"></i>Database Tools</h2>
                <p class="text-muted">Backup, export, dan kelola database</p>
            </div>
        </div>

        {{-- Database Export/Backup Section --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card export-card">
                    <div class="card-header db-export-header">
                        <h5 class="mb-0"><i class="fas fa-download me-2"></i>Database Backup / Export</h5>
                    </div>
                    <div class="card-body">
                        <div class="export-info mb-4">
                            <i class="fas fa-info-circle text-primary me-2"></i>
                            Download file <code>.sql</code> backup database langsung dari server.
                            File dapat digunakan untuk restore database menggunakan <code>mysql</code> command atau phpMyAdmin.
                        </div>

                        <div class="row g-4">
                            {{-- Agenda PTPN Database --}}
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="export-db-icon agenda me-3">
                                        <i class="fas fa-book"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Agenda PTPN</h6>
                                        <small class="text-muted">Database: {{ config('database.connections.mysql.database') }}</small>
                                    </div>
                                </div>
                                <a href="{{ route('programmer.database-tools.export', 'agenda') }}"
                                   class="btn btn-export btn-export-agenda"
                                   id="btn-export-agenda"
                                   onclick="handleExportClick(this, 'Agenda PTPN')">
                                    <i class="fas fa-download"></i>
                                    <span>Download Backup SQL</span>
                                </a>
                            </div>

                            {{-- Cash Bank Database --}}
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="export-db-icon cashbank me-3">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Cash Bank</h6>
                                        <small class="text-muted">Database: {{ config('database.connections.cash_bank_new.database') }}</small>
                                    </div>
                                </div>
                                <a href="{{ route('programmer.database-tools.export', 'cashbank') }}"
                                   class="btn btn-export btn-export-cashbank"
                                   id="btn-export-cashbank"
                                   onclick="handleExportClick(this, 'Cash Bank')">
                                    <i class="fas fa-download"></i>
                                    <span>Download Backup SQL</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Preview Section -->
            <div class="col-lg-6">
                <div class="card db-tools-card mb-4">
                    <div class="card-header db-tools-header">
                        <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Database Preview</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">Jumlah records di setiap tabel dokumen:</p>

                        <div id="preview-loading" class="text-center py-4">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2 text-muted">Memuat data...</p>
                        </div>

                        <div id="preview-result" style="display: none;">
                            <table class="table table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th>Tabel</th>
                                        <th class="text-end">Jumlah Records</th>
                                    </tr>
                                </thead>
                                <tbody id="preview-body">
                                </tbody>
                                <tfoot>
                                    <tr class="table-secondary">
                                        <th>Total</th>
                                        <th class="text-end" id="total-count">0</th>
                                    </tr>
                                </tfoot>
                            </table>

                            <button class="btn btn-outline-primary w-100 mt-3" onclick="loadPreview()">
                                <i class="fas fa-sync me-2"></i>Refresh Data
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cleanup Section -->
            <div class="col-lg-6">
                <div class="card db-tools-card">
                    <div class="card-header db-tools-header">
                        <h5 class="mb-0"><i class="fas fa-trash-alt me-2"></i>Database Cleanup</h5>
                    </div>
                    <div class="card-body">
                        <div class="danger-zone">
                            <div class="text-center mb-4">
                                <i class="fas fa-exclamation-triangle fa-4x text-danger"></i>
                                <h4 class="text-danger mt-3">DANGER ZONE</h4>
                                <p class="text-muted">Operasi ini akan menghapus <strong>SEMUA</strong> data dokumen secara
                                    permanen!</p>
                            </div>

                            <div class="alert alert-danger">
                                <strong>Data yang akan dihapus:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>Semua dokumen (dokumens)</li>
                                    <li>Data PO dan PR (dokumen_pos, dokumen_prs)</li>
                                    <li>Data role dan status (dokumen_role_data, dokumen_statuses)</li>
                                    <li>Activity logs (dokumen_activity_logs)</li>
                                    <li>Data penerima pembayaran (dibayar_kepadas)</li>
                                </ul>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">
                                    <strong>Konfirmasi:</strong> Ketik <code>HAPUS SEMUA</code> untuk melanjutkan
                                </label>
                                <input type="text" class="form-control confirmation-input" id="confirmation-input"
                                    placeholder="HAPUS SEMUA" autocomplete="off">
                            </div>

                            <button class="btn btn-danger btn-danger-action w-100" id="btn-cleanup"
                                onclick="performCleanup()" disabled>
                                <i class="fas fa-trash-alt me-2"></i>HAPUS SEMUA DATA DOKUMEN
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            loadPreview();

            // Enable/disable cleanup button based on confirmation input
            $('#confirmation-input').on('input', function () {
                const value = $(this).val().trim();
                $('#btn-cleanup').prop('disabled', value !== 'HAPUS SEMUA');
            });
        });

        function handleExportClick(btn, dbName) {
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Mengunduh ' + dbName + '...</span>';

            // Re-enable after 30 seconds (the download will have started by then)
            setTimeout(function () {
                btn.innerHTML = originalHtml;
            }, 30000);
        }

        function loadPreview() {
            $('#preview-loading').show();
            $('#preview-result').hide();

            $.ajax({
                url: '{{ route('programmer.database-tools.preview') }}',
                method: 'GET',
                success: function (response) {
                    if (response.success) {
                        let html = '';
                        const counts = response.counts;
                        const tableNames = {
                            'dokumens': 'Dokumens',
                            'dokumen_pos': 'Dokumen POs',
                            'dokumen_prs': 'Dokumen PRs',
                            'dokumen_role_data': 'Dokumen Role Data',
                            'dokumen_statuses': 'Dokumen Statuses',
                            'dokumen_activity_logs': 'Activity Logs',
                            'dibayar_kepadas': 'Dibayar Kepada'
                        };

                        for (const [key, count] of Object.entries(counts)) {
                            const className = count > 0 ? 'has-data' : 'empty';
                            html += `<tr>
                                    <td>${tableNames[key] || key}</td>
                                    <td class="text-end">
                                        <span class="table-count ${className}">${count.toLocaleString()}</span>
                                    </td>
                                </tr>`;
                        }

                        $('#preview-body').html(html);
                        $('#total-count').text(response.total.toLocaleString());
                    }

                    $('#preview-loading').hide();
                    $('#preview-result').show();
                },
                error: function () {
                    $('#preview-loading').hide();
                    $('#preview-result').html(
                        '<div class="alert alert-danger">Gagal memuat preview</div>').show();
                }
            });
        }

        function performCleanup() {
            const confirmation = $('#confirmation-input').val().trim();

            if (confirmation !== 'HAPUS SEMUA') {
                alert('Konfirmasi tidak valid!');
                return;
            }

            if (!confirm('PERINGATAN TERAKHIR!\n\nAnda akan menghapus SEMUA data dokumen.\nOperasi ini TIDAK BISA dibatalkan!\n\nYakin ingin melanjutkan?')) {
                return;
            }

            $('#btn-cleanup').prop('disabled', true).html(
                '<i class="fas fa-spinner fa-spin me-2"></i>Menghapus...');

            $.ajax({
                url: '{{ route('programmer.database-tools.cleanup') }}',
                method: 'POST',
                data: {
                    confirmation: confirmation,
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    if (response.success) {
                        alert('SUCCESS!\n\n' + response.message);
                        $('#confirmation-input').val('');
                        loadPreview();
                    } else {
                        alert('GAGAL: ' + response.message);
                    }
                    $('#btn-cleanup').prop('disabled', true).html(
                        '<i class="fas fa-trash-alt me-2"></i>HAPUS SEMUA DATA DOKUMEN');
                },
                error: function (xhr) {
                    alert('ERROR: ' + (xhr.responseJSON?.message || 'Terjadi kesalahan'));
                    $('#btn-cleanup').prop('disabled', true).html(
                        '<i class="fas fa-trash-alt me-2"></i>HAPUS SEMUA DATA DOKUMEN');
                }
            });
        }
    </script>
@endsection

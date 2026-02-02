@extends('layouts.app')

@section('title', 'Document Tools')

@section('content')
    <style>
        .doc-tools-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }

        .doc-tools-header {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
            border-radius: 12px 12px 0 0;
        }

        .doc-tools-table th {
            background: #f8fafc;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
        }

        .doc-tools-table td {
            vertical-align: middle;
        }

        .doc-id-badge {
            background: linear-gradient(135deg, #083E40 0%, #0f766e 100%);
            color: white;
            font-weight: 700;
            font-family: monospace;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 13px;
        }

        .role-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .role-badge.operator {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .role-badge.team_verifikasi {
            background: #fef3c7;
            color: #b45309;
        }

        .role-badge.perpajakan {
            background: #e0e7ff;
            color: #4338ca;
        }

        .role-badge.akutansi {
            background: #d1fae5;
            color: #047857;
        }

        .role-badge.pembayaran {
            background: #fce7f3;
            color: #be185d;
        }

        .timestamp-form {
            background: #f8fafc;
            border-radius: 12px;
            padding: 24px;
        }

        .timestamp-form .form-label {
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
        }

        .timestamp-form .form-control {
            border-radius: 8px;
            border: 2px solid #e2e8f0;
        }

        .timestamp-form .form-control:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .current-value {
            font-size: 12px;
            color: #64748b;
            margin-top: 4px;
        }

        .btn-update {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            border: none;
            padding: 12px 24px;
            font-weight: 600;
        }

        .btn-update:hover {
            background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
        }

        .search-box {
            position: relative;
        }

        .search-box i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        .search-box input {
            padding-left: 40px;
        }
    </style>

    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('programmer.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Document Tools</li>
                    </ol>
                </nav>
                <h2><i class="fas fa-tools text-primary me-2"></i>Document Tools</h2>
                <p class="text-muted">Lihat ID dokumen dan edit timestamp role</p>
            </div>
        </div>

        <div class="row">
            <!-- Document Lookup -->
            <div class="col-lg-7">
                <div class="card doc-tools-card mb-4">
                    <div class="card-header doc-tools-header">
                        <h5 class="mb-0"><i class="fas fa-search me-2"></i>Document Lookup</h5>
                    </div>
                    <div class="card-body">
                        <!-- Search Box -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="search-box">
                                    <i class="fas fa-search"></i>
                                    <input type="text" class="form-control" id="search-input"
                                        placeholder="Cari nomor agenda atau SPP...">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <select class="form-select" id="filter-role">
                                    <option value="">Semua Role</option>
                                    <option value="operator">Operator</option>
                                    <option value="team_verifikasi">Team Verifikasi</option>
                                    <option value="perpajakan">Perpajakan</option>
                                    <option value="akutansi">Akutansi</option>
                                    <option value="pembayaran">Pembayaran</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-primary w-100" id="btn-search">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Results Table -->
                        <div id="search-loading" class="text-center py-4" style="display: none;">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2 text-muted">Mencari dokumen...</p>
                        </div>

                        <div class="table-responsive" id="results-container">
                            <table class="table table-hover doc-tools-table mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nomor Agenda</th>
                                        <th>Nomor SPP</th>
                                        <th>Current Handler</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="results-body">
                                    @forelse($documents ?? [] as $doc)
                                        <tr>
                                            <td><span class="doc-id-badge">{{ $doc->id }}</span></td>
                                            <td><strong>{{ $doc->nomor_agenda }}</strong></td>
                                            <td>{{ $doc->nomor_spp ?? '-' }}</td>
                                            <td>
                                                <span class="role-badge {{ $doc->current_handler ?? 'operator' }}">
                                                    {{ ucwords(str_replace('_', ' ', $doc->current_handler ?? 'operator')) }}
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary btn-select-doc"
                                                    data-id="{{ $doc->id }}" data-agenda="{{ $doc->nomor_agenda }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">
                                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                                <p class="mb-0">Gunakan search untuk mencari dokumen</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if(isset($documents) && $documents instanceof \Illuminate\Pagination\LengthAwarePaginator)
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <small class="text-muted">Showing {{ $documents->firstItem() ?? 0 }} -
                                    {{ $documents->lastItem() ?? 0 }} of {{ $documents->total() }}</small>
                                {{ $documents->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Timestamp Editor -->
            <div class="col-lg-5">
                <div class="card doc-tools-card">
                    <div class="card-header doc-tools-header">
                        <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Role Timestamp Editor</h5>
                    </div>
                    <div class="card-body">
                        <div class="timestamp-form">
                            <form id="timestamp-form">
                                @csrf
                                <!-- Document Selection -->
                                <div class="mb-3">
                                    <label class="form-label">Dokumen ID atau Nomor Agenda</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-file-alt"></i></span>
                                        <input type="text" class="form-control" id="input-doc-id"
                                            placeholder="ID atau Nomor Agenda">
                                    </div>
                                    <div class="current-value" id="selected-doc-info"></div>
                                </div>

                                <!-- Role Selection -->
                                <div class="mb-3">
                                    <label class="form-label">Role</label>
                                    <select class="form-select" id="input-role" required>
                                        <option value="">Pilih Role</option>
                                        <option value="team_verifikasi">Team Verifikasi</option>
                                        <option value="perpajakan">Perpajakan</option>
                                        <option value="akutansi">Akutansi</option>
                                        <option value="pembayaran">Pembayaran</option>
                                    </select>
                                </div>

                                <!-- Load Current Data Button -->
                                <div class="mb-3">
                                    <button type="button" class="btn btn-outline-primary w-100" id="btn-load-data">
                                        <i class="fas fa-download me-2"></i>Load Current Data
                                    </button>
                                </div>

                                <hr>

                                <!-- Timestamp Fields -->
                                <div id="timestamp-fields" style="display: none;">
                                    <div class="mb-3">
                                        <label class="form-label">Received At</label>
                                        <input type="datetime-local" class="form-control" id="input-received-at">
                                        <div class="current-value" id="current-received-at"></div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Processed At</label>
                                        <input type="datetime-local" class="form-control" id="input-processed-at">
                                        <div class="current-value" id="current-processed-at"></div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Deadline At</label>
                                        <input type="datetime-local" class="form-control" id="input-deadline-at">
                                        <div class="current-value" id="current-deadline-at"></div>
                                    </div>

                                    <div class="alert alert-warning mb-3">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <strong>Perhatian:</strong> Perubahan akan langsung tersimpan ke database!
                                    </div>

                                    <button type="submit" class="btn btn-update text-white w-100" id="btn-update">
                                        <i class="fas fa-save me-2"></i>Update Timestamps
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Result Message -->
                        <div id="update-result" class="mt-3" style="display: none;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            // Search functionality
            $('#btn-search').on('click', function () {
                performSearch();
            });

            $('#search-input').on('keypress', function (e) {
                if (e.which === 13) {
                    performSearch();
                }
            });

            function performSearch() {
                const search = $('#search-input').val().trim();
                const role = $('#filter-role').val();

                if (!search && !role) {
                    return;
                }

                $('#search-loading').show();
                $('#results-container').hide();

                $.ajax({
                    url: '{{ route("programmer.document-tools.search") }}',
                    method: 'GET',
                    data: { search: search, role: role },
                    success: function (response) {
                        $('#search-loading').hide();
                        $('#results-container').show();

                        let html = '';
                        if (response.documents && response.documents.length > 0) {
                            response.documents.forEach(function (doc) {
                                html += '<tr>' +
                                    '<td><span class="doc-id-badge">' + doc.id + '</span></td>' +
                                    '<td><strong>' + doc.nomor_agenda + '</strong></td>' +
                                    '<td>' + (doc.nomor_spp || '-') + '</td>' +
                                    '<td><span class="role-badge ' + (doc.current_handler || 'operator') + '">' +
                                    (doc.current_handler || 'operator').replace('_', ' ') + '</span></td>' +
                                    '<td><button class="btn btn-sm btn-outline-primary btn-select-doc" ' +
                                    'data-id="' + doc.id + '" data-agenda="' + doc.nomor_agenda + '">' +
                                    '<i class="fas fa-edit"></i></button></td>' +
                                    '</tr>';
                            });
                        } else {
                            html = '<tr><td colspan="5" class="text-center text-muted py-4">' +
                                '<i class="fas fa-search fa-2x mb-2"></i>' +
                                '<p class="mb-0">Tidak ditemukan</p></td></tr>';
                        }
                        $('#results-body').html(html);
                    },
                    error: function () {
                        $('#search-loading').hide();
                        $('#results-container').show();
                        alert('Gagal mencari dokumen');
                    }
                });
            }

            // Select document for editing
            $(document).on('click', '.btn-select-doc', function () {
                const id = $(this).data('id');
                const agenda = $(this).data('agenda');
                $('#input-doc-id').val(id);
                $('#selected-doc-info').html('<i class="fas fa-check-circle text-success"></i> ' + agenda);
            });

            // Load current data
            $('#btn-load-data').on('click', function () {
                const docId = $('#input-doc-id').val().trim();
                const role = $('#input-role').val();

                if (!docId || !role) {
                    alert('Masukkan Dokumen ID dan pilih Role');
                    return;
                }

                $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Loading...');

                $.ajax({
                    url: '{{ route("programmer.document-tools.get-role-data") }}',
                    method: 'POST',
                    data: {
                        doc_id: docId,
                        role: role,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        $('#btn-load-data').prop('disabled', false).html('<i class="fas fa-download me-2"></i>Load Current Data');

                        if (response.success) {
                            const data = response.data;

                            // Format datetime for input (using local timezone)
                            function formatDatetimeLocal(dateStr) {
                                if (!dateStr) return '';
                                const d = new Date(dateStr);
                                // Use local timezone format
                                const year = d.getFullYear();
                                const month = String(d.getMonth() + 1).padStart(2, '0');
                                const day = String(d.getDate()).padStart(2, '0');
                                const hours = String(d.getHours()).padStart(2, '0');
                                const minutes = String(d.getMinutes()).padStart(2, '0');
                                return `${year}-${month}-${day}T${hours}:${minutes}`;
                            }

                            $('#input-received-at').val(formatDatetimeLocal(data.received_at));
                            $('#input-processed-at').val(formatDatetimeLocal(data.processed_at));
                            $('#input-deadline-at').val(formatDatetimeLocal(data.deadline_at));

                            // Show current values
                            $('#current-received-at').html('Current: ' + (data.received_at || 'Not set'));
                            $('#current-processed-at').html('Current: ' + (data.processed_at || 'Not set'));
                            $('#current-deadline-at').html('Current: ' + (data.deadline_at || 'Not set'));

                            $('#selected-doc-info').html('<i class="fas fa-check-circle text-success"></i> ID: ' +
                                response.dokumen_id + ' | ' + response.nomor_agenda);

                            $('#timestamp-fields').slideDown();
                        } else {
                            alert(response.message || 'Data tidak ditemukan');
                        }
                    },
                    error: function (xhr) {
                        $('#btn-load-data').prop('disabled', false).html('<i class="fas fa-download me-2"></i>Load Current Data');
                        alert('Error: ' + (xhr.responseJSON?.message || 'Gagal memuat data'));
                    }
                });
            });

            // Update timestamps
            $('#timestamp-form').on('submit', function (e) {
                e.preventDefault();

                const docId = $('#input-doc-id').val().trim();
                const role = $('#input-role').val();

                if (!confirm('Apakah Anda yakin ingin update timestamps?\n\nDokumen: ' + docId + '\nRole: ' + role)) {
                    return;
                }

                $('#btn-update').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Updating...');

                $.ajax({
                    url: '{{ route("programmer.document-tools.update-timestamps") }}',
                    method: 'POST',
                    data: {
                        doc_id: docId,
                        role: role,
                        received_at: $('#input-received-at').val(),
                        processed_at: $('#input-processed-at').val(),
                        deadline_at: $('#input-deadline-at').val(),
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        $('#btn-update').prop('disabled', false).html('<i class="fas fa-save me-2"></i>Update Timestamps');

                        if (response.success) {
                            $('#update-result').html(
                                '<div class="alert alert-success">' +
                                '<i class="fas fa-check-circle me-2"></i>' + response.message +
                                '</div>'
                            ).show();

                            // Reload data
                            $('#btn-load-data').click();
                        } else {
                            $('#update-result').html(
                                '<div class="alert alert-danger">' +
                                '<i class="fas fa-times-circle me-2"></i>' + response.message +
                                '</div>'
                            ).show();
                        }
                    },
                    error: function (xhr) {
                        $('#btn-update').prop('disabled', false).html('<i class="fas fa-save me-2"></i>Update Timestamps');
                        $('#update-result').html(
                            '<div class="alert alert-danger">' +
                            '<i class="fas fa-times-circle me-2"></i>Error: ' + (xhr.responseJSON?.message || 'Gagal update') +
                            '</div>'
                        ).show();
                    }
                });
            });
        });
    </script>
@endsection
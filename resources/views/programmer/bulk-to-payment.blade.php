@extends('layouts.app')

@section('title', 'Bulk Direct to Payment')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('programmer.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Bulk Direct to Payment</li>
                    </ol>
                </nav>
                <h2><i class="fas fa-fast-forward text-warning me-2"></i>Bulk Direct to Payment</h2>
                <p class="text-muted">Kirim dokumen langsung ke Pembayaran tanpa melalui workflow normal</p>
            </div>
        </div>

        <div class="row">
            <!-- Input Form -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-list-alt me-2"></i>Input Nomor Agenda</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Peringatan:</strong> Fitur ini akan mengirim dokumen langsung ke Pembayaran tanpa
                            melalui Verifikasi, Perpajakan, dan Akutansi. Gunakan dengan hati-hati!
                        </div>

                        <div class="mb-3">
                            <label for="nomor_agendas" class="form-label">Daftar Nomor Agenda</label>
                            <textarea class="form-control" id="nomor_agendas" rows="10"
                                placeholder="Masukkan nomor agenda (satu per baris atau dipisahkan koma)&#10;Contoh:&#10;0003_2026&#10;0004_2026&#10;0005_2026"></textarea>
                            <small class="text-muted">Format: 0003_2026, 0004_2026 atau satu per baris</small>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-primary btn-lg" id="btn-preview">
                                <i class="fas fa-search me-2"></i>Preview Dokumen
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preview & Execute -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-eye me-2"></i>Preview Hasil</h5>
                    </div>
                    <div class="card-body">
                        <div id="preview-loading" class="text-center py-4" style="display: none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Mencari dokumen...</p>
                        </div>

                        <div id="preview-result" style="display: none;">
                            <!-- Stats -->
                            <div class="row mb-3">
                                <div class="col-6">
                                    <div class="bg-success bg-opacity-10 p-3 rounded">
                                        <h4 id="count-found" class="text-success mb-0">0</h4>
                                        <small class="text-muted">Ditemukan</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-danger bg-opacity-10 p-3 rounded">
                                        <h4 id="count-not-found" class="text-danger mb-0">0</h4>
                                        <small class="text-muted">Tidak Ditemukan</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Not Found List -->
                            <div id="not-found-list" class="mb-3" style="display: none;">
                                <h6 class="text-danger"><i class="fas fa-times-circle me-1"></i>Tidak Ditemukan:</h6>
                                <div id="not-found-items" class="small text-muted"></div>
                            </div>

                            <!-- Found Table -->
                            <div id="found-table-container" style="max-height: 300px; overflow-y: auto;">
                                <table class="table table-sm table-hover">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th>No. Agenda</th>
                                            <th>No. SPP</th>
                                            <th>Nilai</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="found-table-body">
                                    </tbody>
                                </table>
                            </div>

                            <hr>

                            <!-- Execute Button -->
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-danger btn-lg" id="btn-execute" disabled>
                                    <i class="fas fa-rocket me-2"></i>Kirim ke Pembayaran
                                </button>
                            </div>
                        </div>

                        <div id="preview-empty" class="text-center text-muted py-4">
                            <i class="fas fa-clipboard fa-3x mb-3"></i>
                            <p>Preview akan muncul di sini setelah klik "Preview Dokumen"</p>
                        </div>
                    </div>
                </div>

                <!-- Execution Result -->
                <div id="execution-result" class="card border-0 shadow-sm mt-3" style="display: none;">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>Hasil Eksekusi</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <h4 id="exec-processed" class="text-success">0</h4>
                                <small>Berhasil</small>
                            </div>
                            <div class="col-6">
                                <h4 id="exec-failed" class="text-danger">0</h4>
                                <small>Gagal</small>
                            </div>
                        </div>
                        <div id="exec-errors" class="mt-3" style="display: none;">
                            <h6 class="text-danger">Error:</h6>
                            <ul id="exec-error-list" class="small text-danger mb-0"></ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            let previewData = null;

            console.log('Programmer bulk-to-payment JS loaded');

            // Preview button click
            $('#btn-preview').on('click', function() {
                console.log('Preview button clicked');
                const nomorAgendas = $('#nomor_agendas').val().trim();

                if (!nomorAgendas) {
                    alert('Silakan masukkan daftar nomor agenda');
                    return;
                }

                console.log('Sending preview request...');

                // Show loading
                $('#preview-empty').hide();
                $('#preview-result').hide();
                $('#preview-loading').show();
                $('#execution-result').hide();
                $('#btn-execute').prop('disabled', true);

                $.ajax({
                    url: '{{ route("programmer.bulk-to-payment.preview") }}',
                    method: 'POST',
                    data: {
                        nomor_agendas: nomorAgendas,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        console.log('Preview response:', response);
                        $('#preview-loading').hide();

                        if (response.success) {
                            previewData = response;

                            // Update stats
                            $('#count-found').text(response.total_found);
                            $('#count-not-found').text(response.total_not_found);

                            // Show not found list
                            if (response.not_found.length > 0) {
                                $('#not-found-items').text(response.not_found.join(', '));
                                $('#not-found-list').show();
                            } else {
                                $('#not-found-list').hide();
                            }

                            // Populate found table
                            let tableHtml = '';
                            response.found.forEach(function(doc) {
                                tableHtml += '<tr>' +
                                    '<td><strong>' + doc.nomor_agenda + '</strong></td>' +
                                    '<td>' + (doc.nomor_spp || '-') + '</td>' +
                                    '<td>Rp ' + doc.nilai_rupiah + '</td>' +
                                    '<td><span class="badge bg-secondary">' + doc.current_handler + '</span></td>' +
                                    '</tr>';
                            });
                            $('#found-table-body').html(tableHtml);

                            // Enable execute button if there are documents to process
                            if (response.total_found > 0) {
                                $('#btn-execute').prop('disabled', false);
                            }

                            $('#preview-result').show();
                        }
                    },
                    error: function(xhr) {
                        console.log('Preview error:', xhr);
                        $('#preview-loading').hide();
                        $('#preview-empty').show();
                        alert('Error: ' + (xhr.responseJSON?.message || 'Gagal memuat preview'));
                    }
                });
            });

            // Execute button click
            $('#btn-execute').on('click', function() {
                if (!confirm('Apakah Anda yakin ingin mengirim ' + previewData.total_found +
                        ' dokumen langsung ke Pembayaran?\n\nProses ini TIDAK DAPAT dibatalkan!')) {
                    return;
                }

                const btn = $(this);
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Memproses...');

                $.ajax({
                    url: '{{ route("programmer.bulk-to-payment.execute") }}',
                    method: 'POST',
                    data: {
                        nomor_agendas: $('#nomor_agendas').val().trim(),
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        btn.html('<i class="fas fa-rocket me-2"></i>Kirim ke Pembayaran');

                        // Show execution result
                        $('#exec-processed').text(response.processed);
                        $('#exec-failed').text(response.failed);

                        if (response.errors && response.errors.length > 0) {
                            let errorHtml = '';
                            response.errors.forEach(function(err) {
                                errorHtml += '<li>' + err + '</li>';
                            });
                            $('#exec-error-list').html(errorHtml);
                            $('#exec-errors').show();
                        } else {
                            $('#exec-errors').hide();
                        }

                        $('#execution-result').show();

                        if (response.success) {
                            alert(response.message);
                            // Clear form
                            $('#nomor_agendas').val('');
                            $('#preview-result').hide();
                            $('#preview-empty').show();
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html(
                            '<i class="fas fa-rocket me-2"></i>Kirim ke Pembayaran');
                        alert('Error: ' + (xhr.responseJSON?.message || 'Gagal mengeksekusi'));
                    }
                });
            });
        });
    </script>
@endsection

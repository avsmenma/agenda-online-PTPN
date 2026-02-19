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

                            <!-- Progress Bar (hidden by default) -->
                            <div id="batch-progress" class="mt-3" style="display: none;">
                                <div class="d-flex justify-content-between mb-1">
                                    <small class="text-muted" id="batch-progress-label">Memproses batch...</small>
                                    <small class="text-muted" id="batch-progress-count">0/0</small>
                                </div>
                                <div class="progress" style="height: 25px;">
                                    <div id="batch-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-info"
                                         role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                        0%
                                    </div>
                                </div>
                                <small class="text-muted mt-1 d-block" id="batch-progress-detail"></small>
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
        $(document).ready(function () {
            let previewData = null;
            const BATCH_SIZE = 50; // Documents per batch request

            console.log('Programmer bulk-to-payment JS loaded');

            /**
             * Split an array into chunks of given size
             */
            function chunkArray(arr, size) {
                const chunks = [];
                for (let i = 0; i < arr.length; i += size) {
                    chunks.push(arr.slice(i, i + size));
                }
                return chunks;
            }

            /**
             * Parse nomor agenda text into array (same logic as backend)
             */
            function parseNomorAgendas(text) {
                return text.replace(/[,;\t]/g, '\n')
                    .split('\n')
                    .map(s => s.trim())
                    .filter(s => s.length > 0)
                    .filter((v, i, a) => a.indexOf(v) === i); // unique
            }

            /**
             * Update progress bar UI
             */
            function updateProgress(current, total, processed, failed) {
                const pct = Math.round((current / total) * 100);
                $('#batch-progress-bar').css('width', pct + '%').text(pct + '%').attr('aria-valuenow', pct);
                $('#batch-progress-count').text('Batch ' + current + '/' + total);
                $('#batch-progress-detail').text('Berhasil: ' + processed + ' | Gagal: ' + failed);
            }

            /**
             * Send a single batch via AJAX - returns a Promise
             */
            function sendBatch(nomorAgendasStr) {
                return new Promise(function (resolve, reject) {
                    $.ajax({
                        url: '{{ route("programmer.bulk-to-payment.execute") }}',
                        method: 'POST',
                        timeout: 120000, // 2 min per batch (50 docs should be fast)
                        data: {
                            nomor_agendas: nomorAgendasStr,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function (response) {
                            resolve(response);
                        },
                        error: function (xhr) {
                            reject(xhr);
                        }
                    });
                });
            }

            /**
             * Process all batches sequentially
             */
            async function processAllBatches(chunks) {
                let totalProcessed = 0;
                let totalFailed = 0;
                let allErrors = [];

                $('#batch-progress').show();
                updateProgress(0, chunks.length, 0, 0);

                for (let i = 0; i < chunks.length; i++) {
                    const chunk = chunks[i];
                    const batchStr = chunk.join('\n');

                    $('#batch-progress-label').text('Memproses batch ' + (i + 1) + ' dari ' + chunks.length + ' (' + chunk.length + ' dokumen)...');

                    try {
                        const response = await sendBatch(batchStr);
                        totalProcessed += response.processed || 0;
                        totalFailed += response.failed || 0;
                        if (response.errors && response.errors.length > 0) {
                            allErrors = allErrors.concat(response.errors);
                        }
                    } catch (xhr) {
                        // Batch failed entirely - count all docs as failed
                        totalFailed += chunk.length;
                        allErrors.push('Batch ' + (i + 1) + ' gagal: ' + (xhr.statusText || 'Server error'));
                    }

                    updateProgress(i + 1, chunks.length, totalProcessed, totalFailed);
                }

                // Mark progress bar as complete
                $('#batch-progress-bar')
                    .removeClass('progress-bar-animated bg-info')
                    .addClass('bg-success');
                $('#batch-progress-label').text('Selesai!');

                return {
                    processed: totalProcessed,
                    failed: totalFailed,
                    errors: allErrors
                };
            }

            // Preview button click
            $('#btn-preview').on('click', function () {
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
                $('#batch-progress').hide();
                $('#btn-execute').prop('disabled', true);

                $.ajax({
                    url: '{{ route("programmer.bulk-to-payment.preview") }}',
                    method: 'POST',
                    data: {
                        nomor_agendas: nomorAgendas,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
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
                            response.found.forEach(function (doc) {
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
                    error: function (xhr) {
                        console.log('Preview error:', xhr);
                        $('#preview-loading').hide();
                        $('#preview-empty').show();
                        alert('Error: ' + (xhr.responseJSON?.message || 'Gagal memuat preview'));
                    }
                });
            });

            // Execute button click - uses frontend batching
            $('#btn-execute').on('click', async function () {
                const allNomors = parseNomorAgendas($('#nomor_agendas').val().trim());
                const totalDocs = allNomors.length;
                const totalBatches = Math.ceil(totalDocs / BATCH_SIZE);

                if (!confirm('Apakah Anda yakin ingin mengirim ' + totalDocs +
                    ' dokumen langsung ke Pembayaran?\n\n' +
                    'Akan diproses dalam ' + totalBatches + ' batch (@' + BATCH_SIZE + ' dokumen).\n' +
                    'Proses ini TIDAK DAPAT dibatalkan!')) {
                    return;
                }

                const btn = $(this);
                btn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin me-2"></i>Memproses... (jangan tutup halaman)');
                $('#execution-result').hide();

                // Reset progress bar
                $('#batch-progress-bar')
                    .removeClass('bg-success')
                    .addClass('progress-bar-animated bg-info');

                // Split into chunks and process sequentially
                const chunks = chunkArray(allNomors, BATCH_SIZE);
                const result = await processAllBatches(chunks);

                // Show final results
                btn.prop('disabled', false).html('<i class="fas fa-rocket me-2"></i>Kirim ke Pembayaran');
                $('#exec-processed').text(result.processed);
                $('#exec-failed').text(result.failed);

                if (result.errors.length > 0) {
                    let errorHtml = '';
                    result.errors.forEach(function (err) {
                        errorHtml += '<li>' + err + '</li>';
                    });
                    $('#exec-error-list').html(errorHtml);
                    $('#exec-errors').show();
                } else {
                    $('#exec-errors').hide();
                }

                $('#execution-result').show();

                alert('Selesai! Berhasil: ' + result.processed + ', Gagal: ' + result.failed);

                if (result.processed > 0) {
                    $('#nomor_agendas').val('');
                    $('#preview-result').hide();
                    $('#preview-empty').show();
                }
            });
        });
    </script>
@endsection
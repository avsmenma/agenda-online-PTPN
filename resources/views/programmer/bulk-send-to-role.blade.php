@extends('layouts.programmer')

@section('title', 'Bulk Send to Role')
@section('page_title', 'Bulk Send to Role')
@section('page_breadcrumb', 'Programmer → Bulk Send to Role')

@section('content')
    <div class="container-fluid">
        <div class="prog-page-header">
            <div class="prog-page-header-left">
                <a href="{{ route('programmer.dashboard') }}" class="btn-back-prog mb-2">
                    <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                </a>
                <h2><i class="fas fa-share-square text-primary me-2"></i>Bulk Send to Role</h2>
                <p>Kirim dokumen secara massal ke role tertentu (Verifikasi, Perpajakan, atau Akutansi)</p>
            </div>
        </div>

        <div class="row g-4">
            <!-- Input Form -->
            <div class="col-md-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-list-alt me-2"></i>Konfigurasi Pengiriman</h5>
                    </div>
                    <div class="card-body">
                        <!-- Target Role Selector -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Target Role Tujuan</label>
                            <div class="d-flex flex-column gap-2" id="role-option-group">
                                @php
                                    $roleOptions = [
                                        'team_verifikasi' => ['label' => 'Team Verifikasi', 'icon' => 'fa-check-double', 'color' => '#2563eb', 'bg' => '#dbeafe'],
                                        'perpajakan'      => ['label' => 'Perpajakan',       'icon' => 'fa-file-invoice-dollar', 'color' => '#7c3aed', 'bg' => '#ede9fe'],
                                        'akutansi'        => ['label' => 'Akutansi',          'icon' => 'fa-calculator',         'color' => '#059669', 'bg' => '#d1fae5'],
                                    ];
                                @endphp
                                @foreach($roleOptions as $roleKey => $roleInfo)
                                    <label class="role-option-card d-flex align-items-center gap-3 p-3 rounded border cursor-pointer"
                                           style="cursor:pointer; transition: all .15s;" id="card-{{ $roleKey }}">
                                        <input type="radio" name="target_role" value="{{ $roleKey }}"
                                               class="d-none role-radio" id="radio-{{ $roleKey }}">
                                        <div class="d-flex align-items-center justify-content-center rounded-circle"
                                             style="width:40px;height:40px;background:{{ $roleInfo['bg'] }};flex-shrink:0;">
                                            <i class="fas {{ $roleInfo['icon'] }}" style="color:{{ $roleInfo['color'] }};font-size:16px;"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold" style="color:{{ $roleInfo['color'] }}">{{ $roleInfo['label'] }}</div>
                                            <div class="small text-muted">Kirim dokumen ke antrian {{ $roleInfo['label'] }}</div>
                                        </div>
                                        <div class="ms-auto">
                                            <div class="role-check-icon" style="display:none;">
                                                <i class="fas fa-check-circle" style="color:{{ $roleInfo['color'] }};font-size:20px;"></i>
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            <div class="invalid-feedback d-block text-danger small mt-1" id="role-error" style="display:none!important;"></div>
                        </div>

                        <div class="alert alert-info py-2 small">
                            <i class="fas fa-info-circle me-1"></i>
                            Dokumen akan melalui <strong>semua role antara</strong> posisi saat ini dan target.
                            Contoh: dokumen di Operator ke target Perpajakan → melewati Verifikasi → Perpajakan.
                        </div>

                        <!-- Nomor Agenda Input -->
                        <div class="mb-3">
                            <label for="nomor_agendas" class="form-label fw-semibold">Daftar Nomor Agenda</label>
                            <textarea class="form-control font-monospace" id="nomor_agendas" rows="10"
                                placeholder="Masukkan nomor agenda (satu per baris atau dipisahkan koma)&#10;Contoh:&#10;0003_2026&#10;0004_2026&#10;0005_2026"></textarea>
                            <small class="text-muted">Format: satu per baris, atau dipisahkan koma/titik koma</small>
                        </div>

                        <div class="d-grid">
                            <button type="button" class="btn btn-primary btn-lg" id="btn-preview">
                                <i class="fas fa-search me-2"></i>Preview Dokumen
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preview & Execute -->
            <div class="col-md-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-eye me-2"></i>Preview Hasil</h5>
                    </div>
                    <div class="card-body">
                        <div id="preview-loading" class="text-center py-4" style="display: none;">
                            <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
                            <p class="mt-2">Mencari dokumen...</p>
                        </div>

                        <div id="preview-result" style="display: none;">
                            <!-- Stats -->
                            <div class="row mb-3">
                                <div class="col-6">
                                    <div class="bg-success bg-opacity-10 p-3 rounded text-center">
                                        <h4 id="count-found" class="text-success fw-bold mb-0">0</h4>
                                        <small class="text-muted">Ditemukan</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-danger bg-opacity-10 p-3 rounded text-center">
                                        <h4 id="count-not-found" class="text-danger fw-bold mb-0">0</h4>
                                        <small class="text-muted">Tidak Ditemukan</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Not Found List -->
                            <div id="not-found-list" class="mb-3" style="display: none;">
                                <h6 class="text-danger"><i class="fas fa-times-circle me-1"></i>Tidak Ditemukan:</h6>
                                <div id="not-found-items" class="small text-muted font-monospace"></div>
                            </div>

                            <!-- Skipped (cannot move backward) -->
                            <div id="skip-list" class="mb-3" style="display: none;">
                                <h6 class="text-warning"><i class="fas fa-exclamation-triangle me-1"></i>Dilewati (sudah melewati target):</h6>
                                <div id="skip-items" class="small text-muted font-monospace"></div>
                            </div>

                            <!-- Found Table -->
                            <div id="found-table-container" style="max-height: 300px; overflow-y: auto;">
                                <table class="table table-sm table-hover align-middle">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th>No. Agenda</th>
                                            <th>No. SPP</th>
                                            <th>Nilai</th>
                                            <th>Posisi Saat Ini</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="found-table-body"></tbody>
                                </table>
                            </div>

                            <hr>

                            <!-- Execute Button -->
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-danger btn-lg" id="btn-execute" disabled>
                                    <i class="fas fa-paper-plane me-2"></i>
                                    Kirim ke <span id="btn-execute-label">Role Terpilih</span>
                                </button>
                            </div>

                            <!-- Progress Bar -->
                            <div id="batch-progress" class="mt-3" style="display: none;">
                                <div class="d-flex justify-content-between mb-1">
                                    <small class="text-muted" id="batch-progress-label">Memproses batch...</small>
                                    <small class="text-muted" id="batch-progress-count">0/0</small>
                                </div>
                                <div class="progress" style="height: 22px;">
                                    <div id="batch-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                         role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                                </div>
                                <small class="text-muted mt-1 d-block" id="batch-progress-detail"></small>
                            </div>
                        </div>

                        <div id="preview-empty" class="text-center text-muted py-5">
                            <i class="fas fa-share-square fa-3x mb-3 opacity-25"></i>
                            <p>Pilih role tujuan dan masukkan nomor agenda, lalu klik "Preview Dokumen"</p>
                        </div>
                    </div>
                </div>

                <!-- Execution Result -->
                <div id="execution-result" class="card border-0 shadow-sm mt-3" style="display: none;">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>Hasil Eksekusi</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-4">
                                <h3 id="exec-processed" class="text-success fw-bold">0</h3>
                                <small class="text-muted">Berhasil</small>
                            </div>
                            <div class="col-4">
                                <h3 id="exec-skipped" class="text-warning fw-bold">0</h3>
                                <small class="text-muted">Dilewati</small>
                            </div>
                            <div class="col-4">
                                <h3 id="exec-failed" class="text-danger fw-bold">0</h3>
                                <small class="text-muted">Gagal</small>
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

    <style>
        .role-option-card { background: #f8fafc; border-color: #e2e8f0 !important; }
        .role-option-card:hover { background: #f1f5f9; border-color: #94a3b8 !important; }
        .role-option-card.selected { border-width: 2px !important; background: #fff; }
        .font-monospace { font-family: 'Courier New', Courier, monospace; font-size: 13px; }
    </style>

    <script>
    $(document).ready(function () {
        let previewData = null;
        let selectedRole = null;
        const BATCH_SIZE = 50;

        const roleColors = {
            'team_verifikasi': '#2563eb',
            'perpajakan':      '#7c3aed',
            'akutansi':        '#059669',
        };
        const roleLabels = {
            'team_verifikasi': 'Team Verifikasi',
            'perpajakan':      'Perpajakan',
            'akutansi':        'Akutansi',
        };

        // Role card selection logic
        $('.role-option-card').on('click', function () {
            const card = $(this);
            const roleValue = card.find('.role-radio').val();

            // Deselect all
            $('.role-option-card').removeClass('selected').css('border-color', '');
            $('.role-check-icon').hide();

            // Select this
            card.addClass('selected').css('border-color', roleColors[roleValue] + ' !important');
            card.find('.role-check-icon').show();
            card.find('.role-radio').prop('checked', true);
            selectedRole = roleValue;

            $('#btn-execute-label').text(roleLabels[roleValue] || roleValue);

            // Reset preview when role changes
            if (previewData) {
                previewData = null;
                $('#preview-result').hide();
                $('#preview-empty').show();
                $('#btn-execute').prop('disabled', true);
                $('#execution-result').hide();
            }
        });

        function chunkArray(arr, size) {
            const chunks = [];
            for (let i = 0; i < arr.length; i += size) {
                chunks.push(arr.slice(i, i + size));
            }
            return chunks;
        }

        function parseNomorAgendas(text) {
            return text.replace(/[,;\t]/g, '\n')
                .split('\n')
                .map(s => s.trim())
                .filter(s => s.length > 0)
                .filter((v, i, a) => a.indexOf(v) === i);
        }

        function updateProgress(current, total, processed, failed) {
            const pct = Math.round((current / total) * 100);
            $('#batch-progress-bar').css('width', pct + '%').text(pct + '%').attr('aria-valuenow', pct);
            $('#batch-progress-count').text('Batch ' + current + '/' + total);
            $('#batch-progress-detail').text('Berhasil: ' + processed + ' | Gagal: ' + failed);
        }

        function sendBatch(nomorAgendasStr, targetRole) {
            return new Promise(function (resolve, reject) {
                $.ajax({
                    url: '{{ route("programmer.bulk-send-to-role.execute") }}',
                    method: 'POST',
                    timeout: 120000,
                    data: {
                        nomor_agendas: nomorAgendasStr,
                        target_role: targetRole,
                        _token: '{{ csrf_token() }}'
                    },
                    success: resolve,
                    error: reject
                });
            });
        }

        async function processAllBatches(chunks, targetRole) {
            let totalProcessed = 0;
            let totalFailed = 0;
            let totalSkipped = 0;
            let allErrors = [];

            $('#batch-progress').show();
            updateProgress(0, chunks.length, 0, 0);

            for (let i = 0; i < chunks.length; i++) {
                const chunk = chunks[i];
                const batchStr = chunk.join('\n');
                $('#batch-progress-label').text('Memproses batch ' + (i + 1) + ' dari ' + chunks.length + ' (' + chunk.length + ' dokumen)...');

                try {
                    const response = await sendBatch(batchStr, targetRole);
                    totalProcessed += response.processed || 0;
                    totalFailed    += response.failed    || 0;
                    totalSkipped   += response.skipped   || 0;
                    if (response.errors && response.errors.length > 0) {
                        allErrors = allErrors.concat(response.errors);
                    }
                } catch (xhr) {
                    totalFailed += chunk.length;
                    allErrors.push('Batch ' + (i + 1) + ' gagal: ' + (xhr.statusText || 'Server error'));
                }

                updateProgress(i + 1, chunks.length, totalProcessed, totalFailed);
            }

            $('#batch-progress-bar').removeClass('progress-bar-animated bg-primary').addClass('bg-success');
            $('#batch-progress-label').text('Selesai!');

            return { processed: totalProcessed, failed: totalFailed, skipped: totalSkipped, errors: allErrors };
        }

        // ── Preview ──────────────────────────────────────────────────
        $('#btn-preview').on('click', function () {
            if (!selectedRole) {
                $('#role-error').text('Pilih role tujuan terlebih dahulu.').show();
                return;
            }
            $('#role-error').hide();

            const nomorAgendas = $('#nomor_agendas').val().trim();
            if (!nomorAgendas) {
                alert('Silakan masukkan daftar nomor agenda terlebih dahulu.');
                return;
            }

            $('#preview-empty').hide();
            $('#preview-result').hide();
            $('#preview-loading').show();
            $('#execution-result').hide();
            $('#batch-progress').hide();
            $('#btn-execute').prop('disabled', true);

            $.ajax({
                url: '{{ route("programmer.bulk-send-to-role.preview") }}',
                method: 'POST',
                data: {
                    nomor_agendas: nomorAgendas,
                    target_role: selectedRole,
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    $('#preview-loading').hide();
                    if (!response.success) { alert(response.message || 'Gagal memuat preview'); return; }

                    previewData = response;
                    $('#count-found').text(response.total_actionable);
                    $('#count-not-found').text(response.total_not_found);

                    // Not found
                    if (response.not_found && response.not_found.length > 0) {
                        $('#not-found-items').text(response.not_found.join(', '));
                        $('#not-found-list').show();
                    } else { $('#not-found-list').hide(); }

                    // Skipped (already past target)
                    if (response.skipped && response.skipped.length > 0) {
                        $('#skip-items').text(response.skipped.map(s => s.nomor_agenda + ' (posisi: ' + s.current_handler + ')').join(', '));
                        $('#skip-list').show();
                    } else { $('#skip-list').hide(); }

                    // Build table
                    let tableHtml = '';
                    (response.actionable || []).forEach(function (doc) {
                        tableHtml += '<tr>' +
                            '<td><strong>' + doc.nomor_agenda + '</strong></td>' +
                            '<td class="small">' + (doc.nomor_spp || '-') + '</td>' +
                            '<td class="small">Rp ' + doc.nilai_rupiah + '</td>' +
                            '<td><span class="badge bg-secondary">' + (doc.current_handler || 'operator') + '</span></td>' +
                            '<td><span class="badge bg-primary">' + (roleLabels[selectedRole] || selectedRole) + '</span></td>' +
                            '</tr>';
                    });
                    $('#found-table-body').html(tableHtml);

                    if (response.total_actionable > 0) {
                        $('#btn-execute').prop('disabled', false);
                    }

                    $('#preview-result').show();
                },
                error: function (xhr) {
                    $('#preview-loading').hide();
                    $('#preview-empty').show();
                    alert('Error: ' + (xhr.responseJSON?.message || 'Gagal memuat preview'));
                }
            });
        });

        // ── Execute ───────────────────────────────────────────────────
        $('#btn-execute').on('click', async function () {
            if (!selectedRole) { alert('Pilih role tujuan terlebih dahulu.'); return; }

            const allNomors = parseNomorAgendas($('#nomor_agendas').val().trim());
            const totalDocs  = allNomors.length;
            const totalBatches = Math.ceil(totalDocs / BATCH_SIZE);
            const roleName = roleLabels[selectedRole] || selectedRole;

            if (!confirm('Anda akan mengirim ' + totalDocs + ' dokumen ke ' + roleName + '.\n' +
                         'Diproses dalam ' + totalBatches + ' batch (@' + BATCH_SIZE + ' dokumen).\n\n' +
                         'Proses ini TIDAK DAPAT dibatalkan!\nLanjutkan?')) return;

            const btn = $(this);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Memproses... (jangan tutup halaman)');
            $('#execution-result').hide();

            $('#batch-progress-bar').removeClass('bg-success').addClass('progress-bar-animated bg-primary');

            const chunks = chunkArray(allNomors, BATCH_SIZE);
            const result = await processAllBatches(chunks, selectedRole);

            btn.prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i>Kirim ke <span id="btn-execute-label">' + roleName + '</span>');
            $('#exec-processed').text(result.processed);
            $('#exec-skipped').text(result.skipped);
            $('#exec-failed').text(result.failed);

            if (result.errors.length > 0) {
                let errorHtml = '';
                result.errors.forEach(e => { errorHtml += '<li>' + e + '</li>'; });
                $('#exec-error-list').html(errorHtml);
                $('#exec-errors').show();
            } else { $('#exec-errors').hide(); }

            $('#execution-result').show();

            if (result.processed > 0) {
                $('#nomor_agendas').val('');
                $('#preview-result').hide();
                $('#preview-empty').show();
            }
        });
    });
    </script>
@endsection

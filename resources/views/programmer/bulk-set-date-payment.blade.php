@extends('layouts.programmer')

@section('title', 'Bulk Set Date Payment')
@section('page_title', 'Bulk Set Date Payment')
@section('page_breadcrumb', 'Programmer → Bulk Set Date Payment')

@section('content')
    <div class="container-fluid">
        <div class="prog-page-header">
            <div class="prog-page-header-left">
                <a href="{{ route('programmer.dashboard') }}" class="btn-back-prog mb-2">
                    <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                </a>
                <h2><i class="fas fa-calendar-check me-2 text-info"></i>Bulk Set Date Payment</h2>
                <p>Set tanggal pembayaran massal untuk dokumen yang sudah siap bayar di Pembayaran</p>
            </div>
        </div>

        <div class="row">
            <!-- Input Form -->
            <div class="col-md-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Input Data</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <label class="form-label fw-bold">Nomor Agenda <span class="text-danger">*</span></label>
                                <textarea id="nomorAgendaInput" class="form-control font-monospace" rows="15"
                                    placeholder="Masukkan nomor agenda&#10;Satu per baris&#10;Contoh:&#10;001&#10;002&#10;003"></textarea>
                                <small class="text-muted mt-1 d-block">Jumlah baris: <span id="agendaCount"
                                        class="fw-bold">0</span></small>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold">Tanggal Pembayaran <span
                                        class="text-danger">*</span></label>
                                <textarea id="tanggalInput" class="form-control font-monospace" rows="15"
                                    placeholder="Masukkan tanggal&#10;Format: dd/mm/yyyy&#10;Contoh:&#10;10/01/2026&#10;12/01/2026&#10;15/01/2026"></textarea>
                                <small class="text-muted mt-1 d-block">Jumlah baris: <span id="tanggalCount"
                                        class="fw-bold">0</span></small>
                            </div>
                        </div>

                        <!-- Count Match Indicator -->
                        <div id="countMatchIndicator" class="alert alert-secondary mt-3 mb-0 d-none">
                            <i class="fas fa-info-circle me-2"></i><span id="countMatchText"></span>
                        </div>

                        <div class="d-grid gap-2 mt-3">
                            <button id="btnPreview" class="btn btn-info btn-lg text-white" disabled>
                                <i class="fas fa-search me-2"></i>Preview
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preview Results -->
            <div class="col-md-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Preview Hasil</h5>
                    </div>
                    <div class="card-body">
                        <!-- Stats Cards -->
                        <div id="statsArea" class="d-none">
                            <div class="row mb-3">
                                <div class="col-4">
                                    <div class="card bg-light text-center">
                                        <div class="card-body py-2">
                                            <small class="text-muted d-block">Total</small>
                                            <h4 class="mb-0" id="statTotal">0</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="card bg-success bg-opacity-10 text-center">
                                        <div class="card-body py-2">
                                            <small class="text-success d-block">Valid</small>
                                            <h4 class="mb-0 text-success" id="statValid">0</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="card bg-danger bg-opacity-10 text-center">
                                        <div class="card-body py-2">
                                            <small class="text-danger d-block">Invalid</small>
                                            <h4 class="mb-0 text-danger" id="statInvalid">0</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Preview Table -->
                        <div id="previewTableArea" class="d-none">
                            <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                                <table class="table table-sm table-bordered table-hover">
                                    <thead class="table-dark sticky-top">
                                        <tr>
                                            <th style="width:40px">No</th>
                                            <th>No. Agenda</th>
                                            <th>Tanggal</th>
                                            <th>No. SPP</th>
                                            <th>Nilai (Rp)</th>
                                            <th style="width:50px">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="previewTableBody">
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Execute Button -->
                        <div id="executeArea" class="d-none mt-3">
                            <div class="d-grid">
                                <button id="btnExecute" class="btn btn-success btn-lg">
                                    <i class="fas fa-check-circle me-2"></i>Execute — Set Tanggal Pembayaran
                                </button>
                            </div>
                        </div>

                        <!-- Result Area -->
                        <div id="resultArea" class="d-none mt-3"></div>

                        <!-- Empty State -->
                        <div id="emptyState" class="text-center py-5 text-muted">
                            <i class="fas fa-clipboard-list fa-3x mb-3 opacity-25"></i>
                            <p>Masukkan nomor agenda dan tanggal, lalu klik <strong>Preview</strong></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const nomorInput = document.getElementById('nomorAgendaInput');
            const tanggalInput = document.getElementById('tanggalInput');
            const agendaCount = document.getElementById('agendaCount');
            const tanggalCount = document.getElementById('tanggalCount');
            const countIndicator = document.getElementById('countMatchIndicator');
            const countText = document.getElementById('countMatchText');
            const btnPreview = document.getElementById('btnPreview');
            const btnExecute = document.getElementById('btnExecute');

            let previewData = null;

            // Count lines helper
            function countNonEmptyLines(text) {
                return text.split('\n').filter(l => l.trim() !== '').length;
            }

            // Update counts on input
            function updateCounts() {
                const ac = countNonEmptyLines(nomorInput.value);
                const tc = countNonEmptyLines(tanggalInput.value);
                agendaCount.textContent = ac;
                tanggalCount.textContent = tc;

                if (ac === 0 && tc === 0) {
                    countIndicator.classList.add('d-none');
                    btnPreview.disabled = true;
                    return;
                }

                countIndicator.classList.remove('d-none');

                if (ac === tc && ac > 0) {
                    countIndicator.className = 'alert alert-success mt-3 mb-0';
                    countText.innerHTML = `<i class="fas fa-check-circle me-1"></i> Jumlah cocok: <strong>${ac}</strong> pasangan`;
                    btnPreview.disabled = false;
                } else {
                    countIndicator.className = 'alert alert-danger mt-3 mb-0';
                    countText.innerHTML = `<i class="fas fa-times-circle me-1"></i> Jumlah tidak cocok! Agenda: <strong>${ac}</strong>, Tanggal: <strong>${tc}</strong>`;
                    btnPreview.disabled = true;
                }
            }

            nomorInput.addEventListener('input', updateCounts);
            tanggalInput.addEventListener('input', updateCounts);

            // Preview
            btnPreview.addEventListener('click', function () {
                btnPreview.disabled = true;
                btnPreview.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Loading...';

                fetch("{{ route('programmer.bulk-set-date-payment.preview') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        nomor_agenda_list: nomorInput.value,
                        tanggal_list: tanggalInput.value,
                    })
                })
                    .then(r => r.json())
                    .then(data => {
                        if (!data.success) {
                            alert(data.message || 'Error occurred');
                            return;
                        }

                        previewData = data;
                        renderPreview(data);
                    })
                    .catch(err => {
                        alert('Error: ' + err.message);
                    })
                    .finally(() => {
                        btnPreview.disabled = false;
                        btnPreview.innerHTML = '<i class="fas fa-search me-2"></i>Preview';
                        updateCounts();
                    });
            });

            function renderPreview(data) {
                document.getElementById('emptyState').classList.add('d-none');
                document.getElementById('statsArea').classList.remove('d-none');
                document.getElementById('previewTableArea').classList.remove('d-none');
                document.getElementById('resultArea').classList.add('d-none');

                document.getElementById('statTotal').textContent = data.total;
                document.getElementById('statValid').textContent = data.total_valid;
                document.getElementById('statInvalid').textContent = data.total_invalid;

                const tbody = document.getElementById('previewTableBody');
                tbody.innerHTML = '';

                data.pairs.forEach(pair => {
                    const tr = document.createElement('tr');
                    tr.className = pair.valid ? '' : 'table-warning';

                    let statusBadge;
                    if (pair.valid) {
                        statusBadge = '<span class="badge bg-success"><i class="fas fa-check"></i></span>';
                    } else {
                        statusBadge = `<span class="badge bg-danger" title="${pair.reason}"><i class="fas fa-times"></i></span>`;
                    }

                    tr.innerHTML = `
                            <td class="text-center">${pair.index}</td>
                            <td><strong>${pair.nomor_agenda}</strong></td>
                            <td>${pair.tanggal_input}</td>
                            <td>${pair.nomor_spp || '<span class="text-muted">-</span>'}</td>
                            <td class="text-end">${pair.nilai_rupiah || '-'}</td>
                            <td class="text-center" title="${pair.reason || 'Valid'}">${statusBadge}</td>
                        `;
                    tbody.appendChild(tr);
                });

                // Show execute button only if there are valid items
                if (data.total_valid > 0) {
                    document.getElementById('executeArea').classList.remove('d-none');
                    btnExecute.innerHTML = `<i class="fas fa-check-circle me-2"></i>Execute — Set ${data.total_valid} Dokumen`;
                } else {
                    document.getElementById('executeArea').classList.add('d-none');
                }
            }

            // Execute
            btnExecute.addEventListener('click', function () {
                if (!previewData || previewData.total_valid === 0) return;

                if (!confirm(`Apakah Anda yakin ingin set tanggal pembayaran untuk ${previewData.total_valid} dokumen? Aksi ini tidak bisa dibatalkan.`)) {
                    return;
                }

                btnExecute.disabled = true;
                btnExecute.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';

                // Build valid pairs only
                const validPairs = previewData.pairs
                    .filter(p => p.valid)
                    .map(p => ({
                        nomor_agenda: p.nomor_agenda,
                        tanggal: p.tanggal_input
                    }));

                fetch("{{ route('programmer.bulk-set-date-payment.execute') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ pairs: validPairs })
                })
                    .then(r => r.json())
                    .then(data => {
                        const resultArea = document.getElementById('resultArea');
                        resultArea.classList.remove('d-none');

                        if (data.success) {
                            let html = `<div class="alert alert-success">
                                <h6 class="alert-heading"><i class="fas fa-check-circle me-2"></i>Berhasil!</h6>
                                <p class="mb-1">${data.message}</p>`;

                            if (data.errors && data.errors.length > 0) {
                                html += `<hr><small class="d-block mb-1"><strong>Errors:</strong></small>
                                    <ul class="mb-0 small">`;
                                data.errors.forEach(e => {
                                    html += `<li>${e}</li>`;
                                });
                                html += `</ul>`;
                            }

                            html += `</div>`;
                            resultArea.innerHTML = html;

                            // Hide execute button
                            document.getElementById('executeArea').classList.add('d-none');
                        } else {
                            resultArea.innerHTML = `<div class="alert alert-danger">
                                <h6 class="alert-heading"><i class="fas fa-times-circle me-2"></i>Gagal!</h6>
                                <p class="mb-0">${data.message}</p>
                            </div>`;
                        }
                    })
                    .catch(err => {
                        alert('Error: ' + err.message);
                    })
                    .finally(() => {
                        btnExecute.disabled = false;
                        btnExecute.innerHTML = '<i class="fas fa-check-circle me-2"></i>Execute';
                    });
            });
        });
    </script>
@endsection
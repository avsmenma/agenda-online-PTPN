@extends('layouts/app')
@section('content')

    <style>
        h2 {
            background: linear-gradient(135deg, #083E40 0%, #889717 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 24px;
        }

        .import-container {
            background: white;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            margin-bottom: 24px;
        }

        .upload-area {
            border: 3px dashed #d1d5db;
            border-radius: 12px;
            padding: 48px;
            text-align: center;
            background: #f9fafb;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .upload-area.dragover {
            border-color: #083E40;
            background: #e0f2fe;
        }

        .upload-area:hover {
            border-color: #889717;
            background: #f0fdf4;
        }

        .upload-icon {
            font-size: 64px;
            color: #9ca3af;
            margin-bottom: 16px;
        }

        .upload-area.dragover .upload-icon {
            color: #083E40;
        }

        .btn-upload {
            background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
            color: white;
            border: none;
            padding: 12px 32px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(8, 62, 64, 0.3);
        }

        .btn-upload:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(8, 62, 64, 0.4);
        }

        .preview-table {
            margin-top: 24px;
            font-size: 13px;
        }

        .preview-table th {
            background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
            color: white;
            padding: 12px;
            font-weight: 600;
        }

        .preview-table td {
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
        }

        .stats-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            text-align: center;
        }

        .stats-card .number {
            font-size: 32px;
            font-weight: 700;
            margin: 8px 0;
        }

        .stats-card.success .number {
            color: #10b981;
        }

        .stats-card.warning .number {
            color: #f59e0b;
        }

        .stats-card.error .number {
            color: #ef4444;
        }

        .progress-bar-custom {
            height: 8px;
            background: #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
            margin: 16px 0;
        }

        .progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #10b981 0%, #059669 100%);
            transition: width 0.3s ease;
        }

        .alert-custom {
            padding: 16px;
            border-radius: 12px;
            margin: 12px 0;
            display: flex;
            align-items: start;
            gap: 12px;
        }

        .alert-custom.info {
            background: #dbeafe;
            border-left: 4px solid #3b82f6;
            color: #1e40af;
        }

        .alert-custom.warning {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            color: #92400e;
        }

        .alert-custom.error {
            background: #fee2e2;
            border-left: 4px solid #ef4444;
            color: #991b1b;
        }

        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 32px;
        }

        .step {
            flex: 1;
            text-align: center;
            position: relative;
        }

        .step::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 50%;
            width: 100%;
            height: 3px;
            background: #e5e7eb;
            z-index: -1;
        }

        .step:first-child::before {
            display: none;
        }

        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e5e7eb;
            color: #9ca3af;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .step.active .step-circle {
            background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(8, 62, 64, 0.3);
        }

        .step.completed .step-circle {
            background: #10b981;
            color: white;
        }

        .step-title {
            font-size: 14px;
            font-weight: 600;
            color: #6b7280;
        }

        .step.active .step-title {
            color: #083E40;
        }
    </style>

    <h2><i class="fa-solid fa-file-import me-2"></i>{{ $title }}</h2>

    <!-- Step Indicator -->
    <div class="step-indicator">
        <div class="step active" id="step1">
            <div class="step-circle">1</div>
            <div class="step-title">Upload File</div>
        </div>
        <div class="step" id="step2">
            <div class="step-circle">2</div>
            <div class="step-title">Preview & Validasi</div>
        </div>
        <div class="step" id="step3">
            <div class="step-circle">3</div>
            <div class="step-title">Import Data</div>
        </div>
    </div>

    <!-- Step 1: Upload -->
    <div class="import-container" id="uploadSection">
        <h4 class="mb-4"><i class="fa-solid fa-cloud-upload-alt me-2"></i>Upload File CSV</h4>

        <div class="alert-custom info">
            <i class="fa-solid fa-info-circle" style="font-size: 20px;"></i>
            <div>
                <strong>Format File CSV:</strong><br>
                • File harus dalam format .csv<br>
                • Maximum size: 10MB<br>
                • Header harus sesuai dengan template (AGENDA, NO SPP, NILAI, dll)<br>
                • Skip 3 baris pertama (summary rows)
            </div>
        </div>

        <div class="upload-area" id="uploadArea">
            <div class="upload-icon">
                <i class="fa-solid fa-file-csv"></i>
            </div>
            <h5 class="mb-2">Drag & Drop File CSV Disini</h5>
            <p class="text-muted mb-3">atau klik untuk memilih file</p>
            <input type="file" id="csvFile" accept=".csv" style="display: none;">
            <button type="button" class="btn btn-upload" onclick="document.getElementById('csvFile').click()">
                <i class="fa-solid fa-folder-open me-2"></i>Pilih File
            </button>
        </div>

        <div id="fileInfo" class="mt-3" style="display: none;">
            <div class="alert-custom info">
                <i class="fa-solid fa-file-check"></i>
                <div>
                    <strong id="fileName"></strong><br>
                    <small id="fileSize"></small>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 2: Preview -->
    <div class="import-container" id="previewSection" style="display: none;">
        <h4 class="mb-4"><i class="fa-solid fa-eye me-2"></i>Preview & Validasi Data</h4>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card">
                    <i class="fa-solid fa-file-lines" style="font-size: 24px; color: #6b7280;"></i>
                    <div class="number" id="totalRows">0</div>
                    <div class="text-muted">Total Rows</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card success">
                    <i class="fa-solid fa-check-circle" style="font-size: 24px;"></i>
                    <div class="number" id="validRows">0</div>
                    <div class="text-muted">Valid</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card warning">
                    <i class="fa-solid fa-exclamation-triangle" style="font-size: 24px;"></i>
                    <div class="number" id="warningRows">0</div>
                    <div class="text-muted">Warnings</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card error">
                    <i class="fa-solid fa-times-circle" style="font-size: 24px;"></i>
                    <div class="number" id="errorRows">0</div>
                    <div class="text-muted">Errors</div>
                </div>
            </div>
        </div>

        <div id="errorsList" style="display: none;">
            <h5 class="mb-3">Errors (akan di-skip saat import):</h5>
            <div id="errorsContent"></div>
        </div>

        <div id="warningsList" style="display: none;">
            <h5 class="mb-3 mt-4">Warnings (akan tetap di-import):</h5>
            <div id="warningsContent"></div>
        </div>

        <h5 class="mb-3 mt-4">Preview Data (10 rows pertama):</h5>
        <div class="table-responsive">
            <table class="table preview-table" id="previewTable">
                <thead id="previewHeaders"></thead>
                <tbody id="previewBody"></tbody>
            </table>
        </div>

        <div class="mt-4">
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="skipDuplicates" checked>
                <label class="form-check-label" for="skipDuplicates">
                    Skip dokumen yang sudah ada (berdasarkan Nomor Agenda)
                </label>
            </div>

            <button type="button" class="btn btn-upload" id="btnImport">
                <i class="fa-solid fa-upload me-2"></i>Mulai Import
            </button>
            <button type="button" class="btn btn-secondary ms-2" onclick="location.reload()">
                <i class="fa-solid fa-arrow-left me-2"></i>Batal
            </button>
        </div>
    </div>

    <!-- Step 3: Import Progress -->
    <div class="import-container" id="importSection" style="display: none;">
        <h4 class="mb-4"><i class="fa-solid fa-spinner fa-spin me-2"></i>Importing Data...</h4>

        <div class="progress-bar-custom">
            <div class="progress-bar-fill" id="importProgress" style="width: 0%"></div>
        </div>

        <div class="text-center mt-3">
            <p id="importStatus">Memproses data...</p>
        </div>
    </div>

    <!-- Step 4: Result -->
    <div class="import-container" id="resultSection" style="display: none;">
        <h4 class="mb-4"><i class="fa-solid fa-check-circle me-2" style="color: #10b981;"></i>Import Selesai!</h4>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stats-card success">
                    <i class="fa-solid fa-check-circle" style="font-size: 24px;"></i>
                    <div class="number" id="importedCount">0</div>
                    <div class="text-muted">Berhasil Di-import</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card warning">
                    <i class="fa-solid fa-ban" style="font-size: 24px;"></i>
                    <div class="number" id="skippedCount">0</div>
                    <div class="text-muted">Di-skip (Duplicate)</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card error">
                    <i class="fa-solid fa-exclamation-circle" style="font-size: 24px;"></i>
                    <div class="number" id="failedCount">0</div>
                    <div class="text-muted">Gagal</div>
                </div>
            </div>
        </div>

        <div class="alert-custom info">
            <i class="fa-solid fa-info-circle" style="font-size: 20px;"></i>
            <div>
                <strong>Batch ID:</strong> <span id="batchId"></span><br>
                <small>Gunakan Batch ID ini untuk tracking dokumen yang di-import dari CSV</small>
            </div>
        </div>

        <div class="mt-4">
            <a href="/documents/pembayaran" class="btn btn-upload">
                <i class="fa-solid fa-list me-2"></i>Lihat Daftar Dokumen
            </a>
            <button type="button" class="btn btn-secondary ms-2" onclick="location.reload()">
                <i class="fa-solid fa-redo me-2"></i>Import Lagi
            </button>
        </div>
    </div>

    <script>
        let uploadedFilePath = '';

        // Drag and drop
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('csvFile');

        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                handleFileUpload();
            }
        });

        uploadArea.addEventListener('click', () => {
            fileInput.click();
        });

        fileInput.addEventListener('change', handleFileUpload);

        function handleFileUpload() {
            const file = fileInput.files[0];

            if (!file) {
                return;
            }

            if (!file.name.endsWith('.csv')) {
                alert('File harus dalam format .csv');
                return;
            }

            if (file.size > 10 * 1024 * 1024) { // 10MB
                alert('File terlalu besar! Maximum 10MB');
                return;
            }

            // Show file info
            document.getElementById('fileName').textContent = file.name;
            document.getElementById('fileSize').textContent = formatFileSize(file.size);
            document.getElementById('fileInfo').style.display = 'block';

            // Upload file
            uploadFile(file);
        }

        function uploadFile(file) {
            const formData = new FormData();
            formData.append('csv_file', file);

            fetch('/csv-import/upload', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        uploadedFilePath = data.file_path;
                        showPreviewSection(data.preview);
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Upload error:', error);
                    alert('Error uploading file');
                });
        }

        function showPreviewSection(preview) {
            // Update steps
            document.getElementById('step1').classList.add('completed');
            document.getElementById('step1').classList.remove('active');
            document.getElementById('step2').classList.add('active');

            // Hide upload, show preview
            document.getElementById('uploadSection').style.display = 'none';
            document.getElementById('previewSection').style.display = 'block';

            // Show preview data
            displayPreviewTable(preview.headers, preview.rows);

            // Validate all rows
            validateAllRows();
        }

        function displayPreviewTable(headers, rows) {
            const headerRow = document.getElementById('previewHeaders');
            const tbody = document.getElementById('previewBody');

            // Clear existing
            headerRow.innerHTML = '';
            tbody.innerHTML = '';

            // Build headers (show first 8 columns)
            const displayHeaders = headers.slice(0, 8);
            headerRow.innerHTML = '<tr>' + displayHeaders.map(h => `<th>${h}</th>`).join('') + '</tr>';

            // Build rows
            rows.forEach(row => {
                const displayRow = row.slice(0, 8);
                tbody.innerHTML += '<tr>' + displayRow.map(cell => `<td>${cell || '-'}</td>`).join('') + '</tr>';
            });
        }

        function validateAllRows() {
            document.getElementById('totalRows').textContent = '...';
            document.getElementById('validRows').textContent = '...';
            document.getElementById('warningRows').textContent = '...';
            document.getElementById('errorRows').textContent = '...';

            fetch('/csv-import/preview', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ file_path: uploadedFilePath })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const validation = data.validation;
                        document.getElementById('totalRows').textContent = validation.total;
                        document.getElementById('validRows').textContent = validation.valid;
                        document.getElementById('warningRows').textContent = validation.warnings;
                        document.getElementById('errorRows').textContent = validation.errors;

                        // Show errors
                        if (validation.errors > 0) {
                            document.getElementById('errorsList').style.display = 'block';
                            let errorsHtml = '';
                            validation.error_details.forEach(err => {
                                errorsHtml += `
                <div class="alert-custom error">
                  <i class="fa-solid fa-times-circle"></i>
                  <div>
                    <strong>Row ${err.row} (${err.nomor_agenda}):</strong><br>
                    ${err.errors.join(', ')}
                  </div>
                </div>
              `;
                            });
                            document.getElementById('errorsContent').innerHTML = errorsHtml;
                        }

                        // Show warnings
                        if (validation.warnings > 0) {
                            document.getElementById('warningsList').style.display = 'block';
                            let warningsHtml = '';
                            validation.warning_details.forEach(warn => {
                                warningsHtml += `
                <div class="alert-custom warning">
                  <i class="fa-solid fa-exclamation-triangle"></i>
                  <div>
                    <strong>Row ${warn.row} (${warn.nomor_agenda}):</strong><br>
                    ${warn.warnings.join(', ')}
                  </div>
                </div>
              `;
                            });
                            document.getElementById('warningsContent').innerHTML = warningsHtml;
                        }
                    }
                });
        }

        document.getElementById('btnImport').addEventListener('click', () => {
            // Update steps
            document.getElementById('step2').classList.add('completed');
            document.getElementById('step2').classList.remove('active');
            document.getElementById('step3').classList.add('active');

            // Hide preview, show import
            document.getElementById('previewSection').style.display = 'none';
            document.getElementById('importSection').style.display = 'block';

            // Start import
            startImport();
        });

        function startImport() {
            const skipDuplicates = document.getElementById('skipDuplicates').checked;

            // Animate progress
            let progress = 0;
            const progressBar = document.getElementById('importProgress');
            const progressInterval = setInterval(() => {
                progress += 10;
                progressBar.style.width = progress + '%';
                if (progress >= 90) {
                    clearInterval(progressInterval);
                }
            }, 200);

            fetch('/csv-import/import', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    file_path: uploadedFilePath,
                    skip_duplicates: skipDuplicates
                })
            })
                .then(response => response.json())
                .then(data => {
                    clearInterval(progressInterval);
                    progressBar.style.width = '100%';

                    if (data.success) {
                        setTimeout(() => showResultSection(data.result), 500);
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    clearInterval(progressInterval);
                    console.error('Import error:', error);
                    alert('Error importing data');
                });
        }

        function showResultSection(result) {
            // Update steps
            document.getElementById('step3').classList.add('completed');

            // Hide import, show result
            document.getElementById('importSection').style.display = 'none';
            document.getElementById('resultSection').style.display = 'block';

            // Show result
            document.getElementById('importedCount').textContent = result.imported;
            document.getElementById('skippedCount').textContent = result.skipped;
            document.getElementById('failedCount').textContent = result.failed;
            document.getElementById('batchId').textContent = result.batch_id;
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }
    </script>

@endsection


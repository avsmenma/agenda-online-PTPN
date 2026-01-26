{{-- Bulk Operations for INBOX - Full Features (Approve, Reject, Forward) --}}
<style>
    /* Bulk Operations Styles */
    .bulk-action-bar {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
        border-top: 3px solid #083E40;
        box-shadow: 0 -4px 20px rgba(8, 62, 64, 0.2);
        padding: 20px 0;
        z-index: 1000;
        display: none;
        animation: slideUp 0.3s ease;
    }

    @keyframes slideUp {
        from {
            transform: translateY(100%);
        }

        to {
            transform: translateY(0);
        }
    }

    .bulk-action-bar.show {
        display: block;
    }

    .bulk-action-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 15px;
    }

    .selected-info {
        font-size: 16px;
        font-weight: 600;
        color: #083E40;
    }

    .selected-info strong {
        font-size: 20px;
        color: #083E40;
    }

    .bulk-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    #bulkAction {
        min-width: 200px;
        padding: 10px 16px;
        border: 2px solid #083E40;
        border-radius: 10px;
        font-weight: 600;
        background: white;
    }

    #bulkAction:focus {
        outline: none;
        border-color: #889717;
        box-shadow: 0 0 0 3px rgba(136, 151, 23, 0.1);
    }

    .btn-bulk-execute {
        background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
        color: white;
        border: none;
        padding: 10px 24px;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(8, 62, 64, 0.3);
    }

    .btn-bulk-execute:hover {
        background: linear-gradient(135deg, #0a4f52 0%, #083E40 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(8, 62, 64, 0.4);
    }

    .btn-bulk-cancel {
        background: white;
        color: #6c757d;
        border: 2px solid #dee2e6;
        padding: 10px 24px;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-bulk-cancel:hover {
        background: #f8f9fa;
        border-color: #adb5bd;
        color: #495057;
    }

    .document-checkbox {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: #083E40;
    }

    #selectAll {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: #083E40;
    }

    tr.selected-row {
        background: linear-gradient(90deg, rgba(8, 62, 64, 0.1) 0%, transparent 100%) !important;
        border-left: 4px solid #083E40 !important;
    }

    /* Result Modal Styles */
    .result-modal-content {
        border-radius: 16px;
        border: none;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    }

    .result-modal-header {
        border-bottom: none;
        padding: 24px 24px 0;
    }

    .result-modal-body {
        padding: 24px;
    }

    .result-icon {
        font-size: 64px;
        margin-bottom: 16px;
    }

    .result-icon.success {
        color: #10b981;
    }

    .result-icon.error {
        color: #ef4444;
    }

    .result-title {
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 12px;
    }

    .result-message {
        font-size: 16px;
        color: #6b7280;
        margin-bottom: 20px;
    }

    .result-details {
        background: #f9fafb;
        border-radius: 12px;
        padding: 16px;
        margin-top: 16px;
    }

    .result-detail-item {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #e5e7eb;
    }

    .result-detail-item:last-child {
        border-bottom: none;
    }

    .result-detail-label {
        font-weight: 600;
        color: #374151;
    }

    .result-detail-value {
        color: #6b7280;
    }

    .result-detail-value.success {
        color: #10b981;
        font-weight: 600;
    }

    .result-detail-value.error {
        color: #ef4444;
        font-weight: 600;
    }
</style>

{{-- Bulk Action Bar --}}
<div id="bulkActionBar" class="bulk-action-bar">
    <div class="container">
        <div class="bulk-action-content">
            <div class="selected-info">
                <strong><span id="selectedCount">0</span></strong> dokumen dipilih
            </div>

            <div class="bulk-actions">
                <select id="bulkAction" class="form-select">
                    <option value="">Pilih Aksi...</option>
                    <option value="approve">✅ Approve Semua</option>
                    <option value="reject">❌ Reject Semua</option>
                    <option value="forward-perpajakan">➡️ Kirim ke Perpajakan</option>
                    <option value="forward-akuntansi">➡️ Kirim ke Akuntansi</option>
                </select>

                <button id="executeBulk" class="btn btn-bulk-execute">
                    <i class="fas fa-check-circle"></i> Jalankan
                </button>

                <button id="cancelBulk" class="btn btn-bulk-cancel">
                    <i class="fas fa-times"></i> Batal
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Bulk Confirmation Modal --}}
<div class="modal fade" id="bulkConfirmModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Operasi Massal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <strong>Anda akan melakukan:</strong> <span id="actionName"></span><br>
                    <strong>Untuk:</strong> <span id="affectedCount"></span> dokumen
                </div>

                <div id="documentList" class="list-group" style="max-height: 300px; overflow-y: auto;">
                    <!-- Populated by JavaScript -->
                </div>

                <div id="additionalInputs" class="mt-3">
                    <!-- Reject reason input if needed -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" id="confirmBulkAction" class="btn btn-primary">
                    <i class="fas fa-check"></i> Lanjutkan
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Result Modal (Success/Error) --}}
<div class="modal fade" id="resultModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content result-modal-content">
            <div class="result-modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="result-modal-body text-center">
                <div id="resultIcon" class="result-icon"></div>
                <h3 id="resultTitle" class="result-title"></h3>
                <p id="resultMessage" class="result-message"></p>
                <div id="resultDetails" class="result-details" style="display: none;"></div>
                <button type="button" class="btn btn-primary mt-3" data-bs-dismiss="modal" id="resultOkBtn">
                    OK
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Bulk Operations JavaScript (INBOX - Full Features) --}}
<script>
    let selectedDocuments = [];

    $(document).ready(function () {
        // Select all checkbox
        $('#selectAll').on('change', function () {
            $('.document-checkbox').prop('checked', this.checked);
            updateSelection();
        });

        // Individual checkbox
        $('.document-checkbox').on('change', function () {
            updateSelection();
        });

        // Update selection and show/hide bulk action bar
        function updateSelection() {
            selectedDocuments = $('.document-checkbox:checked').map(function () {
                return {
                    id: $(this).val(),
                    nomor: $(this).data('nomor')
                };
            }).get();

            $('#selectedCount').text(selectedDocuments.length);

            // Update UI
            $('.document-row').removeClass('selected-row');
            $('.document-checkbox:checked').closest('tr').addClass('selected-row');

            if (selectedDocuments.length > 0) {
                $('#bulkActionBar').addClass('show');
            } else {
                $('#bulkActionBar').removeClass('show');
            }

            // Update select all checkbox state
            const totalCheckboxes = $('.document-checkbox').length;
            const checkedCheckboxes = $('.document-checkbox:checked').length;
            $('#selectAll').prop('checked', totalCheckboxes > 0 && totalCheckboxes === checkedCheckboxes);
        }

        // Execute bulk action
        $('#executeBulk').on('click', function () {
            const action = $('#bulkAction').val();
            if (!action) {
                showResultModal('error', 'Pilih aksi terlebih dahulu!', '');
                return;
            }

            showConfirmationModal(action);
        });

        // Cancel bulk action
        $('#cancelBulk').on('click', function () {
            $('.document-checkbox').prop('checked', false);
            $('#selectAll').prop('checked', false);
            $('#bulkAction').val('');
            updateSelection();
        });

        // Show confirmation modal
        function showConfirmationModal(action) {
            let actionText = '';
            let needsInput = false;

            switch (action) {
                case 'approve':
                    actionText = 'Approve';
                    break;
                case 'reject':
                    actionText = 'Reject';
                    needsInput = true;
                    break;
                case 'forward-perpajakan':
                    actionText = 'Kirim ke Perpajakan';
                    break;
                case 'forward-akuntansi':
                    actionText = 'Kirim ke Akuntansi';
                    break;
            }

            $('#actionName').text(actionText);
            $('#affectedCount').text(selectedDocuments.length);

            // Populate document list
            let listHtml = '';
            selectedDocuments.forEach((doc, index) => {
                listHtml += `<div class="list-group-item">${index + 1}. ${doc.nomor}</div>`;
            });
            $('#documentList').html(listHtml);

            // Show/hide additional inputs
            if (needsInput) {
                $('#additionalInputs').html(`
          <label for="rejectReason" class="form-label"><strong>Alasan Penolakan:</strong></label>
          <textarea id="rejectReason" class="form-control" rows="3" 
                    placeholder="Masukkan alasan penolakan..." required></textarea>
        `);
            } else {
                $('#additionalInputs').html('');
            }

            $('#bulkConfirmModal').modal('show');
        }

        // Confirm bulk action
        $('#confirmBulkAction').on('click', function () {
            const action = $('#bulkAction').val();
            executeBulkOperation(action);
        });

        // Execute bulk operation via AJAX
        function executeBulkOperation(action) {
            const documentIds = selectedDocuments.map(d => d.id);
            let url = '';
            let data = {
                document_ids: documentIds,
                _token: '{{ csrf_token() }}'
            };

            switch (action) {
                case 'approve':
                    url = '{{ route("team-verifikasi.bulk.approve") }}';
                    break;
                case 'reject':
                    const reason = $('#rejectReason').val();
                    if (!reason || reason.trim() === '') {
                        showResultModal('error', 'Alasan Penolakan Diperlukan', 'Mohon masukkan alasan penolakan sebelum melanjutkan.');
                        return;
                    }
                    url = '{{ route("team-verifikasi.bulk.reject") }}';
                    data.reason = reason;
                    break;
                case 'forward-perpajakan':
                    url = '{{ route("team-verifikasi.bulk.forward") }}';
                    data.target_role = 'perpajakan';
                    break;
                case 'forward-akuntansi':
                    url = '{{ route("team-verifikasi.bulk.forward") }}';
                    data.target_role = 'akuntansi';
                    break;
            }

            // Show loading
            $('#confirmBulkAction').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');

            $.ajax({
                url: url,
                method: 'POST',
                data: data,
                success: function (response) {
                    $('#bulkConfirmModal').modal('hide');

                    if (response.success) {
                        const successMsg = `Berhasil memproses ${response.processed} dokumen!`;
                        const detailsHtml = `
              <div class="result-detail-item">
                <span class="result-detail-label">Berhasil:</span>
                <span class="result-detail-value success">${response.processed} dokumen</span>
              </div>
              ${response.failed > 0 ? `
              <div class="result-detail-item">
                <span class="result-detail-label">Gagal:</span>
                <span class="result-detail-value error">${response.failed} dokumen</span>
              </div>
              ` : ''}
            `;
                        showResultModal('success', 'Operasi Berhasil!', successMsg, detailsHtml, true);
                    } else {
                        showResultModal('error', 'Operasi Gagal', response.message || 'Terjadi kesalahan saat memproses dokumen.');
                    }
                },
                error: function (xhr) {
                    $('#bulkConfirmModal').modal('hide');
                    const errorMsg = xhr.responseJSON?.message || 'Terjadi kesalahan pada server';
                    showResultModal('error', 'Error', errorMsg);
                    $('#confirmBulkAction').prop('disabled', false).html('<i class="fas fa-check"></i> Lanjutkan');
                }
            });
        }

        // Show result modal (Success/Error)
        function showResultModal(type, title, message, detailsHtml = '', reloadOnClose = false) {
            const iconMap = {
                success: '<i class="fas fa-check-circle success"></i>',
                error: '<i class="fas fa-times-circle error"></i>'
            };

            $('#resultIcon').html(iconMap[type] || iconMap.error);
            $('#resultTitle').text(title);
            $('#resultMessage').text(message);

            if (detailsHtml) {
                $('#resultDetails').html(detailsHtml).show();
            } else {
                $('#resultDetails').hide();
            }

            $('#resultModal').modal('show');

            // Reload on close if needed
            if (reloadOnClose) {
                $('#resultModal').off('hidden.bs.modal').on('hidden.bs.modal', function () {
                    location.reload();
                });
                $('#resultOkBtn').off('click').on('click', function () {
                    location.reload();
                });
            } else {
                $('#resultModal').off('hidden.bs.modal');
                $('#resultOkBtn').off('click');
            }
        }

        // Reset modal on close
        $('#bulkConfirmModal').on('hidden.bs.modal', function () {
            $('#confirmBulkAction').prop('disabled', false).html('<i class="fas fa-check"></i> Lanjutkan');
        });
    });
</script>
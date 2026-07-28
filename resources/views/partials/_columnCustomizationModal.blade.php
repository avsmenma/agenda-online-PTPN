{{--
  Modal Kustomisasi Kolom bersama — dipakai 4 view Tabulator role
  (operator/akutansi/perpajakan/verifikasi). Data lewat window.COLUMN_CUSTOMIZATION_CONFIG;
  logika di public/js/column-customization.js. Dark-mode CSS ada global di layouts/app.
  Lihat spec docs/superpowers/specs/2026-07-28-ekstrak-modal-kustomisasi-kolom-design.md.
--}}
<div class="customization-modal" id="columnCustomizationModal">
    <div class="modal-content-custom">
        <div class="modal-header-custom">
            <h3>
                <i class="fa-solid fa-table-columns"></i>
                Kustomisasi Kolom Tabel
            </h3>
        </div>

        <div class="modal-body-custom">
            <div class="customization-grid">
                <div class="selection-panel">
                    <div class="panel-header">
                        <div class="panel-title">
                            <i class="fa-solid fa-check-square"></i>
                            Pilih Kolom
                        </div>
                        <div class="panel-actions">
                            <button type="button" class="btn-select-action btn-select-all" onclick="selectAllColumns()">
                                <i class="fa-solid fa-check-double"></i> Pilih Semua
                            </button>
                            <button type="button" class="btn-select-action btn-remove-all" onclick="removeAllColumns()">
                                <i class="fa-solid fa-times"></i> Hapus Semua
                            </button>
                        </div>
                    </div>
                    <div class="panel-description">
                        Centang kolom yang ingin ditampilkan pada tabel. Urutan akan mengikuti urutan pemilihan Anda.
                    </div>
                    <div class="column-selection-list" id="columnSelectionList">
                        @foreach($availableColumns as $key => $label)
                            <div class="column-item {{ in_array($key, $selectedColumns) ? 'selected' : '' }}" data-column="{{ $key }}"
                                draggable="{{ in_array($key, $selectedColumns) ? 'true' : 'false' }}" onclick="toggleColumn(this)">
                                <div class="drag-handle">
                                    <i class="fa-solid fa-grip-vertical"></i>
                                </div>
                                <input type="checkbox" class="column-item-checkbox" value="{{ $key }}" {{ in_array($key, $selectedColumns) ? 'checked' : '' }} onclick="event.stopPropagation()">
                                <label class="column-item-label">{{ $label }}</label>
                                <span class="column-item-order">
                                    {{ in_array($key, $selectedColumns) ? array_search($key, $selectedColumns) + 1 : '' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="preview-panel">
                    <div class="panel-title">
                        <i class="fa-solid fa-eye"></i>
                        Preview Hasil
                    </div>
                    <div class="panel-description">
                        Preview tabel akan menampilkan kolom yang Anda pilih sesuai urutan.
                    </div>
                    <div class="preview-container">
                        <div id="tablePreview">
                            @if(count($selectedColumns) > 0)
                                <table class="preview-table">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            @foreach($selectedColumns as $col)
                                                <th>{{ $availableColumns[$col] ?? $col }}</th>
                                            @endforeach
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @for($i = 1; $i <= 5; $i++)
                                            <tr>
                                                <td>{{ $i }}</td>
                                                @foreach($selectedColumns as $col)
                                                    <td>Contoh Data {{ $i }}</td>
                                                @endforeach
                                                <td>Edit, Kirim</td>
                                            </tr>
                                        @endfor
                                    </tbody>
                                </table>
                            @else
                                <div class="empty-preview">
                                    <i class="fa-solid fa-table"></i>
                                    <p>Belum ada kolom yang dipilih</p>
                                    <small>Silakan pilih minimal satu kolom untuk melihat preview</small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer-custom">
            <div class="selected-count">
                <strong id="selectedColumnCount">{{ count($selectedColumns) }}</strong> kolom dipilih
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-modal btn-cancel" onclick="closeColumnCustomizationModal()">
                    <i class="fa-solid fa-times"></i>
                    Batal
                </button>
                <button type="button" class="btn-modal btn-save" id="saveCustomizationBtn" onclick="saveColumnCustomization()">
                    <i class="fa-solid fa-save"></i>
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Modal Customization Styles — dipindah dari view role (self-contained, dark-mode global di layouts/app). */
    .customization-modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); z-index: 9999; overflow-y: auto; padding: 20px; box-sizing: border-box; }
    .customization-modal.show { display: flex; align-items: center; justify-content: center; animation: fadeIn 0.3s ease; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    .modal-content-custom { background: white; border-radius: 20px; box-shadow: 0 25px 80px rgba(0, 0, 0, 0.25); max-width: 90%; width: 90%; max-height: 90vh; display: flex; flex-direction: column; overflow: hidden; animation: slideIn 0.3s ease; }
    @keyframes slideIn { from { transform: translateY(-30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    .modal-header-custom { background: #f8f9fa; border-bottom: 1px solid #e9ecef; padding: 24px 40px; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; }
    .modal-header-custom h3 { margin: 0; font-size: 24px; font-weight: 600; color: #212529; display: flex; align-items: center; gap: 12px; }
    .modal-body-custom { padding: 24px 32px; flex: 1; overflow-y: auto; display: flex; flex-direction: column; gap: 24px; }
    .customization-grid { display: flex; flex-direction: column; gap: 24px; flex: 1; min-height: 0; }
    .selection-panel { background: #f8f9fa; border-radius: 12px; padding: 24px; border: 1px solid #e9ecef; display: flex; flex-direction: column; flex-shrink: 0; }
    .panel-title { font-size: 18px; font-weight: 600; color: #212529; margin-bottom: 12px; display: flex; align-items: center; gap: 10px; }
    .panel-description { font-size: 13px; color: #6c757d; margin-bottom: 16px; line-height: 1.6; }
    .panel-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
    .panel-actions { display: flex; gap: 8px; }
    .btn-select-action { padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 500; border: 1px solid #e5e7eb; background: #fff; color: #374151; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 6px; }
    .btn-select-action:hover { border-color: #083E40; color: #083E40; }
    .btn-select-action.btn-select-all:hover { background: rgba(34, 197, 94, 0.1); border-color: #22c55e; color: #22c55e; }
    .btn-select-action.btn-remove-all:hover { background: rgba(239, 68, 68, 0.1); border-color: #ef4444; color: #ef4444; }
    .column-selection-list { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; max-height: 200px; overflow-y: auto; padding: 8px; background: white; border-radius: 8px; border: 1px solid #dee2e6; }
    @media (max-width: 900px) { .column-selection-list { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 600px) { .column-selection-list { grid-template-columns: repeat(2, 1fr); } }
    .column-item { display: flex; align-items: center; padding: 10px 12px; background: #ffffff; border-radius: 8px; border: 2px solid #e9ecef; cursor: move; transition: all 0.2s ease; position: relative; user-select: none; min-height: 44px; gap: 8px; }
    .column-item:hover { border-color: #0066cc; background: #f8f9ff; box-shadow: 0 2px 8px rgba(0, 102, 204, 0.1); }
    .column-item.selected { border-color: #28a745; background: #f0f9f4; box-shadow: 0 2px 8px rgba(40, 167, 69, 0.15); }
    .column-item.dragging { opacity: 0.6; transform: scale(0.98); box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2); z-index: 1000; }
    .column-item.drag-over { border-color: #0066cc; border-style: dashed; background: #e7f3ff; transform: translateX(8px); }
    .drag-handle { width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; color: #6c757d; cursor: grab; flex-shrink: 0; font-size: 12px; }
    .drag-handle:active { cursor: grabbing; }
    .column-item.selected .drag-handle { color: #28a745; }
    .column-item:not(.selected) .drag-handle { opacity: 0.3; cursor: default; }
    .column-item-checkbox { width: 18px; height: 18px; cursor: pointer; flex-shrink: 0; }
    .column-item-label { font-size: 14px; color: #212529; font-weight: 500; flex: 1; cursor: pointer; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .column-item-order { width: 24px; height: 24px; background: #28a745; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 600; opacity: 0; transform: scale(0); transition: all 0.2s ease; flex-shrink: 0; }
    .column-item.selected .column-item-order { opacity: 1; transform: scale(1); }
    .preview-panel { background: #ffffff; border-radius: 12px; padding: 24px; border: 1px solid #e9ecef; display: flex; flex-direction: column; flex: 1; min-height: 0; }
    .preview-container { flex: 1; overflow-x: auto; overflow-y: auto; background: #f8f9fa; border-radius: 8px; padding: 16px; min-height: 400px; width: 100%; }
    .preview-table { width: 100%; min-width: 100%; border-collapse: separate; border-spacing: 0; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); font-size: 13px; table-layout: auto; }
    .preview-table thead { position: sticky; top: 0; z-index: 10; }
    .preview-table th { background: #212529; color: white; padding: 14px 12px; text-align: center; font-weight: 600; font-size: 12px; border-right: 1px solid rgba(255, 255, 255, 0.1); white-space: nowrap; }
    .preview-table th:last-child { border-right: none; }
    .preview-table tbody tr { border-bottom: 1px solid #e9ecef; transition: background 0.2s ease; }
    .preview-table tbody tr:hover { background: #f8f9fa; }
    .preview-table tbody tr:last-child { border-bottom: none; }
    .preview-table td { padding: 12px; text-align: center; border-right: 1px solid #e9ecef; color: #495057; font-size: 13px; }
    .preview-table td:last-child { border-right: none; }
    .empty-preview { text-align: center; padding: 60px 20px; color: #6c757d; }
    .empty-preview i { font-size: 48px; color: #adb5bd; margin-bottom: 16px; }
    .empty-preview p { font-size: 16px; font-weight: 500; margin-bottom: 8px; }
    .empty-preview small { font-size: 14px; color: #868e96; }
    .modal-footer-custom { padding: 20px 40px; border-top: 1px solid #e9ecef; background: #ffffff; display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-shrink: 0; position: sticky; bottom: 0; z-index: 100; box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.05); }
    .selected-count { font-size: 15px; color: #495057; font-weight: 500; }
    .selected-count strong { color: #28a745; font-size: 18px; }
    .modal-actions { display: flex; gap: 12px; }
    .btn-modal { padding: 12px 32px; border: none; border-radius: 10px; font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.2s ease; min-height: 48px; display: inline-flex; align-items: center; gap: 8px; }
    .btn-cancel { background: #6c757d; color: white; }
    .btn-cancel:hover { background: #5a6268; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3); }
    .btn-save { background: #28a745; color: white; }
    .btn-save:hover { background: #218838; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3); }
    .btn-save:disabled { background: #adb5bd; cursor: not-allowed; transform: none; box-shadow: none; }
    @media (max-width: 768px) {
        .customization-modal { padding: 10px; }
        .modal-content-custom { max-height: 95vh; }
        .modal-header-custom, .modal-body-custom, .modal-footer-custom { padding: 20px; }
        .modal-header-custom h3 { font-size: 20px; }
        .modal-footer-custom { flex-direction: column; align-items: stretch; }
        .selected-count { text-align: center; margin-bottom: 12px; }
        .modal-actions { justify-content: stretch; }
        .btn-modal { flex: 1; justify-content: center; }
    }
</style>

<script>
    // Jembatan data Blade→JS (pola window.DOCUMENT_TABULATOR_CONFIG). Dibaca column-customization.js.
    window.COLUMN_CUSTOMIZATION_CONFIG = {
        availableColumns: @json($availableColumns),
        selected: @json(array_values($selectedColumns)),
    };
</script>

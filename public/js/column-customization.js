// ==== Kustomisasi Kolom (diekstrak dari 4 view role ke file bersama) ====
// Diekstrak dari view role (operator/akutansi/perpajakan/verifikasi, masing-masing
// daftar*Tabulator.blade.php) menjadi file bersama dipakai 4 view Tabulator role
// sekaligus. Data lewat window.COLUMN_CUSTOMIZATION_CONFIG (diisi
// partials._columnCustomizationModal), BUKAN output template Blade langsung — file
// ini statis, nol token Blade.
var __CCCFG = window.COLUMN_CUSTOMIZATION_CONFIG || { availableColumns: {}, selected: [] };
let availableColumnsData = __CCCFG.availableColumns || {};
let selectedColumnsOrder = Array.isArray(__CCCFG.selected) ? __CCCFG.selected.slice() : [];

function openColumnCustomizationModal() {
    const modal = document.getElementById('columnCustomizationModal');
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
    initializeModalState();
}
function closeColumnCustomizationModal() {
    const modal = document.getElementById('columnCustomizationModal');
    modal.classList.remove('show');
    document.body.style.overflow = '';
}
function toggleColumn(columnElement) {
    const columnKey = columnElement.dataset.column;
    const checkbox = columnElement.querySelector('.column-item-checkbox');
    const isChecked = checkbox.checked;
    if (!isChecked) {
        if (!selectedColumnsOrder.includes(columnKey)) { selectedColumnsOrder.push(columnKey); }
        checkbox.checked = true;
        columnElement.classList.add('selected');
        columnElement.setAttribute('draggable', 'true');
    } else {
        selectedColumnsOrder = selectedColumnsOrder.filter(key => key !== columnKey);
        checkbox.checked = false;
        columnElement.classList.remove('selected');
        columnElement.setAttribute('draggable', 'false');
    }
    updateColumnOrderBadges();
    updatePreviewTable();
    updateSelectedCount();
    updateDraggableState();
    // Kolom yang BARU dicentang perlu listener drag-nya dipasang sekarang juga.
    // Sebelumnya listener hanya dipasang saat modal dibuka, sehingga kolom baru
    // tampak bisa ditarik (atribut draggable menyala) tapi tak bereaksi sampai
    // modal ditutup lalu dibuka lagi.
    // Wajib dipanggil PALING AKHIR: initializeDragAndDrop() mengganti node
    // #columnSelectionList dengan klonanya, jadi referensi node lama basi.
    initializeDragAndDrop();
}
function selectAllColumns() {
    const allKeys = Object.keys(availableColumnsData);
    selectedColumnsOrder = allKeys;
    document.querySelectorAll('.column-item').forEach(item => {
        item.classList.add('selected');
        item.setAttribute('draggable', 'true');
        item.querySelector('.column-item-checkbox').checked = true;
    });
    updateColumnOrderBadges();
    updatePreviewTable();
    updateSelectedCount();
    updateDraggableState();
}
function removeAllColumns() {
    selectedColumnsOrder = [];
    document.querySelectorAll('.column-item').forEach(item => {
        item.classList.remove('selected');
        item.setAttribute('draggable', 'false');
        item.querySelector('.column-item-checkbox').checked = false;
    });
    updateColumnOrderBadges();
    updatePreviewTable();
    updateSelectedCount();
    updateDraggableState();
}
function updateColumnOrderBadges() {
    document.querySelectorAll('.column-item').forEach(item => {
        const columnKey = item.dataset.column;
        const orderBadge = item.querySelector('.column-item-order');
        const index = selectedColumnsOrder.indexOf(columnKey);
        orderBadge.textContent = index !== -1 ? (index + 1) : '';
    });
}
function updatePreviewTable() {
    const previewContainer = document.getElementById('tablePreview');
    if (selectedColumnsOrder.length === 0) {
        previewContainer.innerHTML = '<div class="empty-preview"><i class="fa-solid fa-table fa-2x mb-2"></i><p>Belum ada kolom yang dipilih</p><small>Silakan pilih minimal satu kolom untuk melihat preview</small></div>';
        return;
    }
    let previewHTML = '<table class="preview-table"><thead><tr><th>No</th>';
    selectedColumnsOrder.forEach(columnKey => {
        const columnLabel = availableColumnsData[columnKey] || columnKey;
        previewHTML += `<th>${columnLabel}</th>`;
    });
    previewHTML += '<th>Aksi</th></tr></thead><tbody>';
    for (let i = 0; i < 5; i++) {
        previewHTML += '<tr><td>' + (i + 1) + '</td>';
        selectedColumnsOrder.forEach(columnKey => {
            const columnLabel = availableColumnsData[columnKey] || columnKey;
            previewHTML += `<td>Contoh ${columnLabel} ${i + 1}</td>`;
        });
        previewHTML += '<td>Edit, Kirim</td></tr>';
    }
    previewHTML += '</tbody></table>';
    previewContainer.innerHTML = previewHTML;
}
function updateSelectedCount() {
    const countElement = document.getElementById('selectedColumnCount');
    countElement.textContent = selectedColumnsOrder.length;
    const saveButton = document.getElementById('saveCustomizationBtn');
    saveButton.disabled = selectedColumnsOrder.length === 0;
}
function saveColumnCustomization() {
    if (selectedColumnsOrder.length === 0) {
        alert('Silakan pilih minimal satu kolom untuk ditampilkan.');
        return;
    }
    // Mulai dari URL berjalan supaya parameter yang tidak diwakili toolbar tetap
    // hidup (mis. mode=rekapan_table & per_page milik pembayaran, page, sort).
    const url = new URL(window.location.href);

    applyToolbarParams(url);

    url.searchParams.delete('columns[]');
    url.searchParams.delete('columns');
    selectedColumnsOrder.forEach(function (columnKey) {
        url.searchParams.append('columns[]', columnKey);
    });

    closeColumnCustomizationModal();
    window.location.href = url.toString();
}

// Nama yang punya jalur simpan sendiri — kontrol toolbar bernama sama tidak
// boleh menimpanya (lihat saveColumnCustomization & applyFrozenParams).
var RESERVED_PARAM_NAMES = ['columns[]', 'columns', 'frozen_config', 'frozen_left[]', 'frozen_right[]'];

// Timpa URL dengan nilai kontrol toolbar yang sedang aktif (generik lintas-role:
// tiap toolbar hanya memuat field-nya sendiri). Nilai kosong MENGHAPUS param —
// itulah semantik "bersihkan filter"; tanpa ini filter yang dikosongkan user
// akan hidup lagi dari URL sebelumnya.
function applyToolbarParams(url) {
    const toolbar = document.querySelector('.tabulator-toolbar');
    if (!toolbar) return;
    const controls = toolbar.querySelectorAll('input[name], select[name], textarea[name]');

    // Kelompokkan dulu per name SEBELUM menulis ke URL. Radio/checkbox lazim berbagi
    // name (satu grup, banyak elemen) — menulis set()/delete() langsung di dalam loop
    // per-elemen SALAH: elemen checked menulis set(name, value), lalu elemen unchecked
    // berikutnya dalam grup yang sama memanggil delete(name) dan menghapus nilai yang
    // baru saja ditulis. Kontrol lain (text/date/select) selalu jadi grup berisi satu
    // elemen, jadi perilakunya tidak berubah dari sebelumnya.
    const groups = new Map();
    controls.forEach(function (el) {
        if (!el.name || RESERVED_PARAM_NAMES.indexOf(el.name) !== -1) return;
        if (!groups.has(el.name)) { groups.set(el.name, []); }
        groups.get(el.name).push(el);
    });

    groups.forEach(function (els, name) {
        const isCheckable = els.some(function (el) { return el.type === 'checkbox' || el.type === 'radio'; });
        if (isCheckable) {
            // Ada yang tercentang → pakai nilainya. Tak ada yang tercentang → hapus param.
            const checked = els.find(function (el) { return (el.type === 'checkbox' || el.type === 'radio') && el.checked; });
            if (checked) { url.searchParams.set(name, checked.value); }
            else { url.searchParams.delete(name); }
            return;
        }
        const el = els[0];
        if (el.value === '' || el.value == null) {
            url.searchParams.delete(name);
            return;
        }
        url.searchParams.set(name, el.value);
    });
}
function initializeModalState() {
    document.querySelectorAll('.column-item').forEach(item => {
        const columnKey = item.dataset.column;
        const checkbox = item.querySelector('.column-item-checkbox');
        if (selectedColumnsOrder.includes(columnKey)) {
            checkbox.checked = true;
            item.classList.add('selected');
            item.setAttribute('draggable', 'true');
        } else {
            checkbox.checked = false;
            item.classList.remove('selected');
            item.setAttribute('draggable', 'false');
        }
    });
    initializeDragAndDrop();
    updateColumnOrderBadges();
    updatePreviewTable();
    updateSelectedCount();
}
function updateDraggableState() {
    document.querySelectorAll('.column-item').forEach(item => {
        const columnKey = item.dataset.column;
        item.setAttribute('draggable', selectedColumnsOrder.includes(columnKey) ? 'true' : 'false');
    });
}

let draggedElement = null;
let draggedIndex = -1;
function initializeDragAndDrop() {
    const columnList = document.getElementById('columnSelectionList');
    if (!columnList) return;
    const newList = columnList.cloneNode(true);
    columnList.parentNode.replaceChild(newList, columnList);
    newList.querySelectorAll('.column-item.selected').forEach(item => {
        item.addEventListener('dragstart', handleDragStart);
        item.addEventListener('dragend', handleDragEnd);
        item.addEventListener('dragover', handleDragOver);
        item.addEventListener('dragenter', handleDragEnter);
        item.addEventListener('dragleave', handleDragLeave);
        item.addEventListener('drop', handleDrop);
    });
}
function handleDragStart(e) {
    draggedElement = this;
    draggedIndex = selectedColumnsOrder.indexOf(this.dataset.column);
    this.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', this.dataset.column);
}
function handleDragEnd(e) {
    this.classList.remove('dragging');
    document.querySelectorAll('.column-item').forEach(el => { el.classList.remove('drag-over'); });
    draggedElement = null;
    draggedIndex = -1;
}
function handleDragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    if (this !== draggedElement && this.classList.contains('selected')) {
        const afterElement = getDragAfterElement(this.parentNode, e.clientY);
        if (afterElement == null) { this.parentNode.appendChild(draggedElement); }
        else { this.parentNode.insertBefore(draggedElement, afterElement); }
    }
    return false;
}
function handleDragEnter(e) {
    e.preventDefault();
    if (this !== draggedElement && this.classList.contains('selected')) { this.classList.add('drag-over'); }
}
function handleDragLeave(e) { this.classList.remove('drag-over'); }
function handleDrop(e) {
    e.preventDefault();
    e.stopPropagation();
    this.classList.remove('drag-over');
    if (this !== draggedElement && this.classList.contains('selected')) {
        const columnList = document.getElementById('columnSelectionList');
        const selectedItems = Array.from(columnList.querySelectorAll('.column-item.selected'));
        const newOrder = selectedItems.map(item => item.dataset.column);
        selectedColumnsOrder = newOrder;
        updateColumnOrderBadges();
        updatePreviewTable();
        setTimeout(() => { initializeDragAndDrop(); }, 50);
    }
    return false;
}
function getDragAfterElement(container, y) {
    const draggableElements = [...container.querySelectorAll('.column-item.selected:not(.dragging)')];
    return draggableElements.reduce((closest, child) => {
        const box = child.getBoundingClientRect();
        const offset = y - box.top - box.height / 2;
        if (offset < 0 && offset > closest.offset) { return { offset: offset, element: child }; }
        return closest;
    }, { offset: Number.NEGATIVE_INFINITY }).element;
}

// Tutup modal kustomisasi: klik luar + Escape + re-init drag saat modal dibuka.
document.addEventListener('click', function (e) {
    const modal = document.getElementById('columnCustomizationModal');
    if (modal && modal.classList.contains('show') && e.target === modal) {
        closeColumnCustomizationModal();
    }
});
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('columnCustomizationModal');
        if (modal && modal.classList.contains('show')) { closeColumnCustomizationModal(); }
    }
});
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('columnCustomizationModal');
    if (modal) {
        const observer = new MutationObserver(function () {
            if (modal.classList.contains('show')) {
                setTimeout(() => { initializeDragAndDrop(); }, 100);
            }
        });
        observer.observe(modal, { attributes: true, attributeFilter: ['class'] });
    }
});

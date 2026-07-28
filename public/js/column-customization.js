// ==== Kustomisasi Kolom (diekstrak dari 5 view role ke file bersama) ====
// Diekstrak dari view role (operator/akutansi/perpajakan/verifikasi/pembayaran,
// masing-masing daftar*Tabulator.blade.php / dashboardPembayaran.blade.php) menjadi
// file bersama dipakai 5 view Tabulator role sekaligus (pembayaran ikut 2026-07-28).
// Data lewat window.COLUMN_CUSTOMIZATION_CONFIG (diisi
// partials._columnCustomizationModal), BUKAN output template Blade langsung — file
// ini statis, nol token Blade.
var __CCCFG = window.COLUMN_CUSTOMIZATION_CONFIG || { availableColumns: {}, selected: [] };
let availableColumnsData = __CCCFG.availableColumns || {};
let selectedColumnsOrder = Array.isArray(__CCCFG.selected) ? __CCCFG.selected.slice() : [];
let frozenLeftOrder = Array.isArray(__CCCFG.frozen && __CCCFG.frozen.left) ? __CCCFG.frozen.left.slice() : [];
let frozenRightOrder = Array.isArray(__CCCFG.frozen && __CCCFG.frozen.right) ? __CCCFG.frozen.right.slice() : [];
// Kolom yang selalu beku kiri (document-tabulator.js membekukannya tanpa syarat).
const PINNED_LEFT_COLUMNS = Array.isArray(__CCCFG.pinned) ? __CCCFG.pinned.slice() : ['nomor_agenda'];
// Perkiraan lebar untuk peringatan "kolom beku memakan layar" (angka dari pembayaran).
const FROZEN_WIDTH_MAP = { nomor_agenda: 210 };
const FROZEN_WIDTH_DEFAULT = 132;
const FROZEN_NO_COLUMN_WIDTH = 88;

function openColumnCustomizationModal() {
    const modal = document.getElementById('columnCustomizationModal');
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
    switchColumnTab('kolom');
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

    url.searchParams.delete('frozen_left[]');
    url.searchParams.delete('frozen_right[]');
    // Penanda WAJIB: tanpa ini "user melepas semua kolom beku" tidak bisa
    // dibedakan dari "request tanpa konfigurasi beku" di sisi server.
    url.searchParams.set('frozen_config', '1');
    // Kolom yang sudah tidak ditampilkan tidak boleh ikut dikirim sebagai beku.
    frozenLeftOrder
        .filter(function (col) { return selectedColumnsOrder.indexOf(col) !== -1; })
        .forEach(function (col) { url.searchParams.append('frozen_left[]', col); });
    frozenRightOrder
        .filter(function (col) { return selectedColumnsOrder.indexOf(col) !== -1; })
        .forEach(function (col) { url.searchParams.append('frozen_right[]', col); });

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

function switchColumnTab(tab) {
    document.querySelectorAll('.column-tab').forEach(function (btn) {
        btn.classList.toggle('active', btn.dataset.tab === tab);
    });
    const panelKolom = document.getElementById('tabPanelKolom');
    const panelBeku = document.getElementById('tabPanelBeku');
    if (panelKolom) panelKolom.style.display = tab === 'kolom' ? '' : 'none';
    if (panelBeku) panelBeku.style.display = tab === 'beku' ? '' : 'none';
    if (tab === 'beku') renderFrozenTab();
}

function getFrozenState(key) {
    if (frozenLeftOrder.indexOf(key) !== -1) return 'left';
    if (frozenRightOrder.indexOf(key) !== -1) return 'right';
    return 'none';
}

function setFrozenState(key, state) {
    if (PINNED_LEFT_COLUMNS.indexOf(key) !== -1) return; // terkunci, abaikan
    frozenLeftOrder = frozenLeftOrder.filter(function (k) { return k !== key; });
    frozenRightOrder = frozenRightOrder.filter(function (k) { return k !== key; });
    if (state === 'left') frozenLeftOrder.push(key);
    if (state === 'right') frozenRightOrder.push(key);
    renderFrozenTab();
}

// Render bersifat NON-DESTRUKTIF: frozenLeftOrder/frozenRightOrder tidak pernah
// ditugaskan ulang di sini. Dulu fungsi ini memangkas kedua array padahal hanya
// jalan saat user membuka tab Beku — akibatnya hasil simpan ikut bergantung pada
// apakah tab itu pernah dibuka. Satu-satunya titik penegakan adalah filter di
// saveColumnCustomization().
function renderFrozenTab() {
    const list = document.getElementById('frozenList');
    if (!list) return;

    list.innerHTML = selectedColumnsOrder.map(function (key) {
        const label = availableColumnsData[key] || key;
        const state = getFrozenState(key);
        const isPinned = PINNED_LEFT_COLUMNS.indexOf(key) !== -1;
        const opt = function (value, text) {
            return '<button type="button" class="frozen-opt' + (state === value ? ' active' : '') + '"' +
                (isPinned ? ' disabled' : ' onclick="setFrozenState(\'' + key + '\', \'' + value + '\')"') +
                '>' + text + '</button>';
        };
        const noteHtml = isPinned
            ? '<span class="frozen-row-note">identitas baris selalu terlihat</span>'
            : '';
        return '<div class="frozen-row"><span>' + label + ' ' + noteHtml + '</span>' +
            '<span class="frozen-options">' + opt('left', 'Kiri') + opt('none', 'Bebas') + opt('right', 'Kanan') +
            '</span></div>';
    }).join('');

    renderFrozenWarning();
}

function renderFrozenWarning() {
    const box = document.getElementById('frozenWarning');
    if (!box) return;

    // Hanya kolom yang benar-benar ditampilkan yang dihitung. Penyaringan memakai
    // variabel lokal, bukan menugaskan ulang state (lihat catatan non-destruktif).
    const visibleFrozen = frozenLeftOrder
        .concat(frozenRightOrder)
        .filter(function (key) { return selectedColumnsOrder.indexOf(key) !== -1; });

    const total = visibleFrozen.reduce(function (sum, key) {
        return sum + (FROZEN_WIDTH_MAP[key] || FROZEN_WIDTH_DEFAULT);
    }, FROZEN_NO_COLUMN_WIDTH);

    if (total > window.innerWidth * 0.5) {
        box.style.display = '';
        box.textContent = 'Kolom beku memakan sekitar ' + Math.round(total) +
            'px dari lebar layar Anda. Area yang bisa digulir jadi sempit — pertimbangkan mengurangi kolom beku.';
    } else {
        box.style.display = 'none';
    }
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

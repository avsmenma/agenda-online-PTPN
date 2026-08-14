{{--
  Shared Inline Edit Engine — include at bottom of any daftar-dokumen view.
  Required variables (passed from controller): $ieKategoriList, $ieSubKriteriaList,
  $ieItemSubKriteriaList, $ieJenisPembayaranList
--}}

<style>
  /* ── Inline Edit Cell Base ── */
  .ie-cell { cursor: pointer; position: relative; }
  .ie-cell:not(.ie-editing):hover::after {
    content: '✏️';
    position: absolute; top: 2px; right: 2px;
    font-size: 10px; opacity: 0.5; pointer-events: none;
  }
  .ie-cell.ie-editing  { background: #fffbeb !important; outline: 2px solid #f59e0b; outline-offset: -2px; padding: 2px !important; }
  .ie-cell.ie-saving   { background: #eff6ff !important; opacity: 0.7; }
  .ie-cell.ie-saved    { background: #f0fdf4 !important; transition: background 0.5s; }
  .ie-cell.ie-error    { background: #fef2f2 !important; outline: 2px solid #ef4444; }
  .ie-cell {
    transition: background 140ms ease, box-shadow 140ms ease, outline-color 140ms ease;
  }
  .ie-cell:not(.ie-editing):hover {
    background: #f0fdfa !important;
    box-shadow: inset 0 0 0 1px rgba(13, 91, 89, 0.24);
  }
  .ie-cell:not(.ie-editing):hover::after {
    content: 'Enter Edit';
    background: #083E40;
    border-radius: 999px;
    color: #ffffff;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: 0;
    line-height: 1;
    opacity: 0.88;
    padding: 4px 6px;
    right: 6px;
    text-transform: uppercase;
    top: 6px;
  }
  .ie-cell.ie-saving {
    box-shadow: inset 0 0 0 1px #93c5fd;
    opacity: 0.82;
  }
  .ie-cell.ie-saved {
    box-shadow: inset 0 0 0 1px #34d399;
  }
  .ie-input {
    width: 100%; box-sizing: border-box;
    border: 1px solid #d1d5db; border-radius: 4px;
    padding: 4px 6px; font-size: 13px; font-family: inherit;
    background: #fff; color: #111;
  }
  .ie-input:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 2px rgba(59,130,246,.2); }
  .ie-select-open {
    min-width: 280px;
    max-width: min(520px, calc(100vw - 32px));
    position: relative;
    z-index: 9997;
  }
  /* Samakan bentuk editor uraian dengan kolom lain (satu baris; bisa di-resize). */
  .ie-textarea { resize: none; min-height: 0; overflow-y: auto; line-height: 1.45; box-sizing: border-box; }
  .ie-spinner {
    display: inline-block; width: 12px; height: 12px; margin-left: 4px;
    border: 2px solid #93c5fd; border-top-color: #3b82f6;
    border-radius: 50%; animation: ie-spin .6s linear infinite; vertical-align: middle;
  }
  @keyframes ie-spin { to { transform: rotate(360deg); } }

  /* ── Floating Textarea Overlay (uraian_spp, dibayar_kepada) ── */
  .ie-overlay-backdrop {
    position: fixed; inset: 0; z-index: 9998;
    background: rgba(0,0,0,0.15);
    animation: ie-backdrop-in .12s ease;
  }
  @keyframes ie-backdrop-in { from { opacity: 0; } to { opacity: 1; } }
  .ie-overlay-popup {
    position: fixed; z-index: 9999;
    width: 480px; max-width: calc(100vw - 32px);
    background: #fff;
    border: 2px solid #f59e0b;
    border-radius: 10px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.18), 0 2px 8px rgba(0,0,0,0.10);
    padding: 12px 14px 10px;
    animation: ie-popup-in .15s cubic-bezier(.2,.8,.3,1);
  }
  @keyframes ie-popup-in {
    from { opacity: 0; transform: translateY(-6px) scale(.98); }
    to   { opacity: 1; transform: translateY(0)    scale(1); }
  }
  .ie-overlay-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 8px;
  }
  .ie-overlay-label {
    font-size: 11px; font-weight: 600; color: #b45309;
    text-transform: uppercase; letter-spacing: .5px;
  }
  .ie-overlay-close {
    background: none; border: none; cursor: pointer; padding: 2px 6px;
    font-size: 16px; color: #6b7280; line-height: 1;
    border-radius: 4px;
  }
  .ie-overlay-close:hover { background: #f3f4f6; color: #111; }
  .ie-overlay-textarea {
    width: 100%; box-sizing: border-box;
    min-height: 160px; max-height: 340px;
    border: 1px solid #d1d5db; border-radius: 6px;
    padding: 8px 10px; font-size: 13px; font-family: inherit;
    background: #fffbeb; color: #111;
    resize: vertical; line-height: 1.6;
    outline: none;
    transition: border-color .15s;
  }
  .ie-overlay-textarea:focus { border-color: #f59e0b; box-shadow: 0 0 0 3px rgba(245,158,11,.15); }
  .ie-overlay-footer {
    display: flex; align-items: center; justify-content: space-between;
    margin-top: 8px; gap: 8px;
  }
  .ie-overlay-charcount { font-size: 11px; color: #9ca3af; }
  .ie-overlay-actions { display: flex; gap: 6px; }
  .ie-overlay-btn {
    padding: 5px 16px; border-radius: 6px; border: none; cursor: pointer;
    font-size: 12px; font-weight: 600; transition: all .15s;
  }
  .ie-overlay-btn-save {
    background: #f59e0b; color: #fff;
  }
  .ie-overlay-btn-save:hover { background: #d97706; }
  .ie-overlay-btn-cancel {
    background: #f3f4f6; color: #374151;
  }
  .ie-overlay-btn-cancel:hover { background: #e5e7eb; }
</style>

<script>
(function () {
  'use strict';

  const IE_KATEGORI    = @json($ieKategoriList ?? []);
  const IE_SUB         = @json($ieSubKriteriaList ?? []);
  const IE_ITEM        = @json($ieItemSubKriteriaList ?? []);
  const IE_JENIS_BAYAR = @json($ieJenisPembayaranList ?? []);

  const BULAN_LIST = ['Januari','Februari','Maret','April','May','Juni','July','Agustus','September','Oktober','November','Desember'];
  // Daftar Bagian (kode + nama) — sumber DB Bagian, dipakai untuk dropdown kolom "bagian".
  const IE_BAGIAN  = @json($ieBagianList ?? \App\Models\Bagian::active()->ordered()->get(['kode','nama']) ?? []);

  const FIELD_TYPE = {
    nomor_agenda        : 'text',
    nomor_spp           : 'text',
    uraian_spp          : 'textarea',
    nilai_rupiah        : 'number',
    tanggal_spp         : 'date',
    tanggal_berita_acara: 'date',
    tanggal_spk         : 'date',
    tanggal_berakhir_spk: 'date',
    tanggal_faktur      : 'date',
    tanggal_paraf       : 'date',
    tanggal_miro        : 'date',
    tanggal_selesai_verifikasi_pajak: 'date',
    tanggal_dibayar     : 'date',
    kebun               : 'text',
    bagian              : 'select_bagian',
    nama_pengirim       : 'text',
    no_berita_acara     : 'text',
    no_spk              : 'text',
    nomor_po            : 'text',
    nomor_miro          : 'text',
    no_faktur           : 'text',
    pemaraf             : 'select_pemaraf',
    jenis_pph           : 'text',
    dpp_pph             : 'number',
    ppn_terhutang       : 'number',
    dibayar_kepada      : 'text',
    kategori            : 'select_kategori',
    jenis_dokumen       : 'select_sub',
    jenis_sub_pekerjaan : 'select_item',
    jenis_pembayaran    : 'select_jenis',
    bulan               : 'select_bulan',
    tahun               : 'text',
    // Perpajakan-specific
    npwp                : 'text',
    link_dokumen_pajak  : 'text',
    link                : 'text',
  };

  // Fields yang menggunakan anchor tag di dalam cell —
  // klik anchor = buka link, klik di luar anchor = edit field
  const LINK_FIRST_CLICK_FIELDS = ['link_dokumen_pajak', 'link'];

  let activeCell     = null;
  let activeInput    = null;
  let activeOverlay  = null; // backdrop + popup for textarea fields
  let activationTime = 0;   // timestamp saat cell diaktifkan (untuk cooldown Enter key)
  const savingCells  = new WeakSet();

  /* ── Floating overlay for long-text fields ──
     uraian_spp DIKELUARKAN dari sini agar diedit inline (textarea di dalam sel),
     bukan via popup — supaya tetap bisa diedit saat mode fullscreen tabel. */
  const OVERLAY_FIELDS = [];
  const OVERLAY_LABELS = {};

  function openOverlay(cell, field, rawValue) {
    closeOverlay(false); // tidy up any existing overlay

    const backdrop = document.createElement('div');
    backdrop.className = 'ie-overlay-backdrop';

    const popup = document.createElement('div');
    popup.className = 'ie-overlay-popup';

    // Position popup near the clicked cell
    const rect = cell.getBoundingClientRect();
    let top = rect.bottom + 6;
    const popupW = Math.min(480, window.innerWidth - 32);
    let left = rect.left;
    if (left + popupW > window.innerWidth - 16) left = window.innerWidth - popupW - 16;
    if (left < 8) left = 8;
    if (top + 300 > window.innerHeight) top = Math.max(8, rect.top - 310);
    popup.style.top  = top  + 'px';
    popup.style.left = left + 'px';
    popup.style.width = popupW + 'px';

    // Header
    const header = document.createElement('div');
    header.className = 'ie-overlay-header';
    const label = document.createElement('span');
    label.className = 'ie-overlay-label';
    label.textContent = OVERLAY_LABELS[field] || 'Edit';
    const closeBtn = document.createElement('button');
    closeBtn.className = 'ie-overlay-close';
    closeBtn.type = 'button';
    closeBtn.textContent = '✕';
    closeBtn.title = 'Tutup (Esc)';
    header.appendChild(label);
    header.appendChild(closeBtn);

    // Textarea
    const ta = document.createElement('textarea');
    ta.className = 'ie-overlay-textarea';
    ta.value = rawValue ?? '';
    ta.rows = 7;
    ta.placeholder = 'Ketik uraian di sini…';

    // Footer
    const footer = document.createElement('div');
    footer.className = 'ie-overlay-footer';
    const charCount = document.createElement('span');
    charCount.className = 'ie-overlay-charcount';
    charCount.textContent = (ta.value.length) + ' karakter';
    ta.addEventListener('input', () => { charCount.textContent = ta.value.length + ' karakter'; });

    const actions = document.createElement('div');
    actions.className = 'ie-overlay-actions';
    const cancelBtn = document.createElement('button');
    cancelBtn.className = 'ie-overlay-btn ie-overlay-btn-cancel';
    cancelBtn.type = 'button'; cancelBtn.textContent = 'Batal';
    const saveBtn = document.createElement('button');
    saveBtn.className = 'ie-overlay-btn ie-overlay-btn-save';
    saveBtn.type = 'button'; saveBtn.textContent = '💾 Simpan';
    actions.appendChild(cancelBtn);
    actions.appendChild(saveBtn);
    footer.appendChild(charCount);
    footer.appendChild(actions);

    popup.appendChild(header);
    popup.appendChild(ta);
    popup.appendChild(footer);
    document.body.appendChild(backdrop);
    document.body.appendChild(popup);

    activeOverlay = { backdrop, popup, ta, cell, field };
    activeInput = ta;
    cell.classList.add('ie-editing');
    cell.dataset.originalRaw  = rawValue ?? '';
    cell.dataset.originalHtml = cell.innerHTML;
    setTimeout(() => { ta.focus(); ta.setSelectionRange(ta.value.length, ta.value.length); }, 20);

    const doSave = () => {
      const newVal = ta.value;
      closeOverlay(false);
      commitOverlayValue(cell, field, newVal, rawValue ?? '');
    };
    const doCancel = () => { closeOverlay(true); };

    saveBtn.addEventListener('click', doSave);
    cancelBtn.addEventListener('click', doCancel);
    closeBtn.addEventListener('click', doCancel);
    backdrop.addEventListener('click', doSave); // click outside = save
    ta.addEventListener('keydown', e => {
      if (e.key === 'Escape') { e.preventDefault(); doCancel(); }
      // Ctrl+Enter = save
      if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) { e.preventDefault(); doSave(); }
    });
  }

  function closeOverlay(restoreHtml) {
    if (!activeOverlay) return;
    const { backdrop, popup, cell } = activeOverlay;
    cell.classList.remove('ie-editing');
    if (restoreHtml) cell.innerHTML = cell.dataset.originalHtml || '';
    backdrop.remove();
    popup.remove();
    activeOverlay = null;
    activeCell  = null;
    activeInput = null;
  }

  function commitOverlayValue(cell, field, newValue, oldRaw) {
    if (newValue === oldRaw) return; // no change
    if (savingCells.has(cell)) return;
    const dokumenId = cell.closest('tr').dataset.dokumenId;
    if (!dokumenId) return;
    savingCells.add(cell);
    cell.classList.add('ie-saving');
    cell.innerHTML = (cell.dataset.originalHtml || newValue) + '<span class="ie-spinner"></span>';
    fetch(`/documents/${dokumenId}/inline-update`, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json',
      },
      body: JSON.stringify({ field, value: newValue }),
    })
    .then(async r => {
      const data = await r.json().catch(() => ({}));
      if (!r.ok) {
        throw new Error(data.message || 'Gagal menyimpan.');
      }
      return data;
    })
    .then(data => {
      cell.classList.remove('ie-saving');
      if (data.success) {
        const display = data.display_value ?? newValue ?? '-';
        cell.dataset.raw = data.raw_value ?? newValue;
        cell.innerHTML   = display || '-';
        syncRowInlineValue(cell, field, data.raw_value ?? newValue);
        cell.classList.add('ie-saved');
        setTimeout(() => cell.classList.remove('ie-saved'), 700);
      } else {
        cell.innerHTML = cell.dataset.originalHtml;
        cell.classList.add('ie-error');
        setTimeout(() => cell.classList.remove('ie-error'), 700);
        showIeToast('error', data.message || 'Gagal menyimpan.');
      }
    })
    .catch((error) => {
      cell.classList.remove('ie-saving');
      cell.innerHTML = cell.dataset.originalHtml;
      cell.classList.add('ie-error');
      setTimeout(() => cell.classList.remove('ie-error'), 700);
      showIeToast('error', error.message || 'Koneksi gagal. Coba lagi.');
    })
    .finally(() => {
      savingCells.delete(cell);
      if (typeof window.acnRefresh === 'function') window.acnRefresh();
    });
  }

  function normalizeInlineValue(value) {
    return String(value ?? '').trim().toLowerCase();
  }

  function getObjectValue(obj, keys) {
    if (!obj) return '';
    for (const key of keys) {
      if (obj[key] !== undefined && obj[key] !== null && String(obj[key]).trim() !== '') {
        return obj[key];
      }
    }
    return '';
  }

  function inlineValuesEqual(left, right) {
    return normalizeInlineValue(left) === normalizeInlineValue(right);
  }

  function findOptionByValue(list, labelKeys, value) {
    const needle = normalizeInlineValue(value);
    if (!needle) return null;
    return list.find(item => labelKeys.some(key => normalizeInlineValue(item[key]) === needle)) || null;
  }

  function getRowFieldValue(cell, fieldName) {
    const row = cell?.closest('tr');
    if (!row) return '';

    const rowDatasetKey = {
      kategori: 'kategori',
      jenis_dokumen: 'jenisDokumen',
      jenis_sub_pekerjaan: 'jenisSubPekerjaan',
    }[fieldName];

    if (rowDatasetKey && row.dataset[rowDatasetKey] !== undefined) {
      return row.dataset[rowDatasetKey] || '';
    }

    const fieldCell = row.querySelector(`td[data-field="${fieldName}"]`);
    if (fieldCell) {
      return fieldCell.dataset.raw ?? fieldCell.textContent.trim();
    }

    return '';
  }

  function filterSubOptions(cell) {
    const kategoriValue = getRowFieldValue(cell, 'kategori');
    if (!normalizeInlineValue(kategoriValue)) return IE_SUB;

    const kategori = findOptionByValue(IE_KATEGORI, ['nama_kriteria', 'id'], kategoriValue);
    const kategoriId = getObjectValue(kategori, ['id', 'id_kategori_kriteria']) || kategoriValue;

    return IE_SUB.filter(sub => {
      const parent = getObjectValue(sub, ['id_kategori_kriteria', 'kategori_id', 'id_kategori', 'kategori']);
      if (!normalizeInlineValue(parent)) return false;
      return inlineValuesEqual(parent, kategoriId) || inlineValuesEqual(parent, kategoriValue);
    });
  }

  function filterItemOptions(cell) {
    const subValue = getRowFieldValue(cell, 'jenis_dokumen');
    if (!normalizeInlineValue(subValue)) return IE_ITEM;

    const scopedSub = filterSubOptions(cell).find(sub =>
      inlineValuesEqual(sub.nama_sub_kriteria, subValue) || inlineValuesEqual(sub.id, subValue)
    );
    const sub = scopedSub || findOptionByValue(IE_SUB, ['nama_sub_kriteria', 'id'], subValue);
    const subId = getObjectValue(sub, ['id', 'id_sub_kriteria']) || subValue;

    return IE_ITEM.filter(item => {
      const parent = getObjectValue(item, ['id_sub_kriteria', 'sub_kriteria_id', 'id_sub']);
      if (!normalizeInlineValue(parent)) return false;
      return inlineValuesEqual(parent, subId) || inlineValuesEqual(parent, subValue);
    });
  }

  function syncRowInlineValue(cell, field, value) {
    const row = cell?.closest('tr');
    if (!row) return;

    if (field === 'kategori') row.dataset.kategori = value ?? '';
    if (field === 'jenis_dokumen') row.dataset.jenisDokumen = value ?? '';
    if (field === 'jenis_sub_pekerjaan') row.dataset.jenisSubPekerjaan = value ?? '';
    if (field === 'tanggal_dibayar') {
      const statusCell = row.querySelector('td[data-field="status_pembayaran"], td.col-status_pembayaran');
      if (statusCell) {
        statusCell.innerHTML = '<span class="status-pill status-pill--paid status-badge sudah-dibayar"><i class="fas fa-circle"></i> Sudah Dibayar</span>';
        statusCell.dataset.raw = 'sudah_dibayar';
      }
    }
  }

  function buildSelect(field, currentVal, cell) {
    const sel = document.createElement('select');
    sel.className = 'ie-input ie-select-input';
    sel.dataset.inlineSelect = 'true';
    let options = [];
    if      (field === 'select_kategori') options = IE_KATEGORI.map(k => ({ value: k.nama_kriteria,        label: k.nama_kriteria }));
    else if (field === 'select_sub')      options = filterSubOptions(cell).map(k => ({ value: k.nama_sub_kriteria, label: k.nama_sub_kriteria }));
    else if (field === 'select_item')     options = filterItemOptions(cell).map(k => ({ value: k.nama_item_sub_kriteria, label: k.nama_item_sub_kriteria }));
    else if (field === 'select_jenis')    options = IE_JENIS_BAYAR.map(k => ({ value: k.nama_jenis_pembayaran, label: k.nama_jenis_pembayaran }));
    else if (field === 'select_bulan')    options = BULAN_LIST.map(b  => ({ value: b, label: b }));
    else if (field === 'select_bagian')   options = IE_BAGIAN.map(b   => ({ value: b.kode, label: (b.nama && b.nama !== b.kode) ? (b.kode + ' — ' + b.nama) : b.kode }));
    else if (field === 'select_pemaraf')  options = [{ value: 'Yuni', label: 'Yuni' }, { value: 'Sekar', label: 'Sekar' }];
    const empty = document.createElement('option');
    empty.value = ''; empty.textContent = '-- Pilih --';
    sel.appendChild(empty);
    options.forEach(opt => {
      const o = document.createElement('option');
      o.value = opt.value; o.textContent = opt.label;
      if (opt.value === currentVal) o.selected = true;
      sel.appendChild(o);
    });
    return sel;
  }

  function openSelectPicker(selectEl) {
    if (!selectEl || selectEl.tagName !== 'SELECT') return;

    selectEl.classList.add('ie-select-open');

    if (typeof selectEl.showPicker === 'function') {
      try {
        selectEl.showPicker();
        return;
      } catch (error) {
        // Fall back to inline listbox mode below.
      }
    }

    const optionCount = selectEl.options ? selectEl.options.length : 0;
    if (optionCount > 0) {
      selectEl.dataset.inlineListbox = 'true';
      selectEl.size = Math.min(Math.max(optionCount, 2), 8);
    }
  }

  function closeSelectPicker(selectEl) {
    if (!selectEl || selectEl.tagName !== 'SELECT') return;
    selectEl.classList.remove('ie-select-open');
    selectEl.removeAttribute('size');
    delete selectEl.dataset.inlineListbox;
  }

  function createInput(fieldType, rawValue, fieldName, cell) {
    let el;
    if (fieldType === 'textarea') {
      el = document.createElement('textarea');
      el.className = 'ie-input ie-textarea'; el.value = rawValue ?? ''; el.rows = 1;
      // Auto-tinggi: pas dengan isi (1 baris untuk teks pendek → tak ada area kosong),
      // tumbuh hingga maks 220px lalu menggulir. Mengatasi kotak uraian yang tinggi sendiri
      // (textarea height:auto di dalam sel tabel ikut meregang mengikuti tinggi baris).
      const _grow = () => { el.style.height = 'auto'; el.style.height = Math.min(el.scrollHeight, 220) + 'px'; };
      el.addEventListener('input', _grow);
      requestAnimationFrame(_grow);
    } else if (fieldType.startsWith('select_')) {
      el = buildSelect(fieldType, rawValue ?? '', cell);
    } else if (fieldType === 'date') {
      el = document.createElement('input');
      el.type = 'date'; el.className = 'ie-input'; el.value = rawValue ?? '';
    } else if (fieldType === 'number') {
      el = document.createElement('input');
      el.type = 'text'; el.className = 'ie-input';
      // Gunakan parseFloat→round agar nilai decimal MySQL (e.g. "4832149.00")
      // menjadi "4832149" — bukan strip naif yang mengubah "4832149.00" → "483214900"
      const num = rawValue
        ? String(Math.round(parseFloat(String(rawValue).replace(/[^0-9.]/g, '')) || 0))
        : '';
      el.value = num;
      el.placeholder = '0';
    } else {
      el = document.createElement('input');
      el.type = 'text'; el.className = 'ie-input'; el.value = rawValue ?? '';
      // Autocomplete untuk dibayar_kepada
      if (fieldName === 'dibayar_kepada') {
        el.setAttribute('list', 'ie-dibayar-kepada-list');
        el.placeholder = 'Nama penerima (pisah koma)';
      }
    }
    return el;
  }

  function activateCell(cell) {
    if (activeOverlay) return; // overlay already open
    if (cell.classList.contains('ie-saving') || savingCells.has(cell)) return;
    if (activeCell && activeCell !== cell) commitCell(activeCell);
    if (activeCell === cell) return;
    const field = cell.dataset.field;
    if (!field) return;

    // Catat waktu aktivasi — digunakan oleh onKeyDown & blur untuk cooldown
    // sehingga tombol Enter/blur yang memicu aktivasi tidak langsung menyimpan data
    activationTime = Date.now();

    // Long-text fields → floating overlay
    if (OVERLAY_FIELDS.includes(field)) {
      activeCell = cell;
      openOverlay(cell, field, cell.dataset.raw ?? '');
      return;
    }

    const fieldType = FIELD_TYPE[field] || 'text';
    const rawValue  = cell.dataset.raw ?? '';
    cell.dataset.originalHtml = cell.innerHTML;
    cell.dataset.originalRaw  = rawValue;
    activeCell  = cell;
    activeInput = createInput(fieldType, rawValue, field, cell);
    cell.classList.add('ie-editing');
    cell.innerHTML = '';
    cell.appendChild(activeInput);
    if (activeInput.tagName === 'SELECT') {
      activeInput.focus({ preventScroll: true });
      openSelectPicker(activeInput);
    }
    setTimeout(() => {
      activeInput.focus();
      if (activeInput.tagName === 'INPUT' && activeInput.type === 'text') activeInput.select();
    }, 20);
    activeInput.addEventListener('keydown', onKeyDown);
    if (activeInput.tagName === 'SELECT') {
      activeInput.addEventListener('change', () => {
        if (activeInput?.dataset.inlineListbox === 'true') return;
        setTimeout(() => {
          if (activeCell === cell) commitCell(cell);
        }, 80);
      });
      activeInput.addEventListener('blur', () => {
        setTimeout(() => {
          if (activeCell === cell) commitCell(cell);
        }, 120);
      });
    } else {
      activeInput.addEventListener('blur', () => {
        // Cooldown: abaikan blur yang terjadi dalam 350ms setelah aktivasi.
        // Ini mencegah phantom-blur (kehilangan fokus sesaat saat input baru dibuat)
        // yang terjadi ketika cell diaktifkan via Enter key dari active cell navigation.
        const msSinceActivation = Date.now() - activationTime;
        if (msSinceActivation < 350) return;
        setTimeout(() => { if (activeCell === cell) commitCell(cell); }, 80);
      });
    }
  }


  function commitCell(cell) {
    if (!cell || !activeInput) return;
    if (savingCells.has(cell)) return;
    const field    = cell.dataset.field;
    const newValue = activeInput.value;
    const oldRaw   = cell.dataset.originalRaw ?? '';

    // Untuk field number, normalisasi oldRaw dengan cara yang sama seperti createInput
    // agar "4832149.00" (decimal MySQL) dibandingkan sebagai "4832149"
    let compareOld = oldRaw;
    const fieldType = FIELD_TYPE[field];
    if (fieldType === 'number') {
      compareOld = oldRaw
        ? String(Math.round(parseFloat(String(oldRaw).replace(/[^0-9.]/g, '')) || 0))
        : '';
    }
    if (newValue === compareOld) { cancelCell(cell); return; }

    const dokumenId = cell.closest('tr').dataset.dokumenId;
    if (!dokumenId) { cancelCell(cell); return; }
    closeSelectPicker(activeInput);
    savingCells.add(cell);
    cell.classList.remove('ie-editing');
    cell.classList.add('ie-saving');
    cell.innerHTML = (cell.dataset.originalHtml || newValue) + '<span class="ie-spinner"></span>';
    activeCell = null; activeInput = null;
    fetch(`/documents/${dokumenId}/inline-update`, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json',
      },
      body: JSON.stringify({ field, value: newValue }),
    })
    .then(async r => {
      const data = await r.json().catch(() => ({}));
      if (!r.ok) {
        throw new Error(data.message || 'Gagal menyimpan.');
      }
      return data;
    })
    .then(data => {
      cell.classList.remove('ie-saving');
      if (data.success) {
        const display = data.display_value ?? newValue ?? '-';
        cell.dataset.raw = data.raw_value ?? newValue;
        cell.innerHTML   = display || '-';
        syncRowInlineValue(cell, field, data.raw_value ?? newValue);
        cell.classList.add('ie-saved');
        setTimeout(() => cell.classList.remove('ie-saved'), 700);
      } else {
        cell.innerHTML = cell.dataset.originalHtml;
        cell.classList.add('ie-error');
        setTimeout(() => cell.classList.remove('ie-error'), 700);
        showIeToast('error', data.message || 'Gagal menyimpan.');
      }
    })
    .catch((error) => {
      cell.classList.remove('ie-saving');
      cell.innerHTML = cell.dataset.originalHtml;
      cell.classList.add('ie-error');
      setTimeout(() => cell.classList.remove('ie-error'), 700);
      showIeToast('error', error.message || 'Koneksi gagal. Coba lagi.');
    })
    .finally(() => {
      savingCells.delete(cell);
      if (typeof window.acnRefresh === 'function') window.acnRefresh();
    });
  }

  function cancelCell(cell) {
    if (!cell) return;
    if (activeInput && activeInput.tagName === 'SELECT') closeSelectPicker(activeInput);
    cell.classList.remove('ie-editing', 'ie-saving');
    cell.innerHTML = cell.dataset.originalHtml || '';
    activeCell = null; activeInput = null;
  }

  function onKeyDown(e) {
    if (!activeCell) return;
    if (e.key === 'Escape') {
      e.preventDefault();
      cancelCell(activeCell);
    } else if (e.key === 'Enter') {
      // Textarea (uraian): Shift+Enter = baris baru; Enter biasa = SIMPAN, sama
      // seperti kolom lain. Field lain: Enter = SIMPAN.
      if (activeInput && activeInput.tagName === 'TEXTAREA' && e.shiftKey) return;
      e.preventDefault();
      // Cooldown: abaikan Enter selama 250ms pertama setelah cell diaktifkan.
      // Ini mencegah tombol Enter yang sama (yang memicu aktivasi via active cell
      // navigation) langsung meng-commit sebelum user sempat mengedit nilai.
      const msSinceActivation = Date.now() - activationTime;
      if (msSinceActivation < 250) return; // terlalu cepat — abaikan
      commitCell(activeCell);
    } else if (e.key === 'ArrowDown' && activeInput && activeInput.tagName === 'SELECT' && e.altKey) {
      e.preventDefault();
      openSelectPicker(activeInput);
    } else if (e.key === 'Tab') {
      e.preventDefault();
      const direction   = e.shiftKey ? -1 : 1;
      const currentCell = activeCell;
      commitCell(activeCell);
      setTimeout(() => navigateCell(currentCell, direction), 100);
    }
  }

  function navigateCell(fromCell, direction) {
    const allCells = Array.from(document.querySelectorAll('tr[data-editable="true"] .ie-cell'));
    const idx = allCells.indexOf(fromCell);
    if (idx === -1) return;
    const next = allCells[idx + direction];
    if (next) activateCell(next);
  }

  function showIeToast(type, message) {
    const existing = document.getElementById('ie-toast');
    if (existing) existing.remove();
    const t = document.createElement('div');
    t.id = 'ie-toast';
    t.style.cssText = `
      position:fixed; bottom:24px; left:50%; transform:translateX(-50%);
      background:${type === 'error' ? '#dc3545' : '#28a745'};
      color:#fff; padding:10px 20px; border-radius:8px;
      font-size:13px; z-index:99999; box-shadow:0 4px 12px rgba(0,0,0,.2);
    `;
    t.textContent = message;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 3500);
  }

  // Expose ke window agar modul lain (_activeCellNav) bisa memanggil inline edit
  window.ieActivateCell = activateCell;
  window.ieCommitCell   = commitCell;
  window.ieCancelCell   = cancelCell;

  document.addEventListener('click', function(e) {
    const cell = e.target.closest('.ie-cell');
    if (cell) {
      // Jika user klik sebuah <a> di dalam cell link → biarkan link terbuka, jangan edit
      if (e.target.tagName === 'A' || e.target.closest('a')) {
        e.stopPropagation();
        return; // buka link, tidak memicu inline edit
      }
      // Klik biasa dipakai untuk membuka Detail Cepat. Inline edit sengaja
      // dibuat eksplisit agar tidak terasa seperti salah klik di tabel.
      if (activeCell && activeCell !== cell) {
        commitCell(activeCell);
      }
      return;
    } else if (activeCell) {
      commitCell(activeCell);
    }
  });

  document.addEventListener('dblclick', function(e) {
    const cell = e.target.closest('.ie-cell');
    if (!cell) return;
    if (e.target.tagName === 'A' || e.target.closest('a')) return;
    e.stopPropagation();
    e.preventDefault();
    activateCell(cell);
  }, true);

})();
</script>

{{-- Datalist autocomplete untuk field Dibayar Kepada (dipakai oleh semua role) --}}
<datalist id="ie-dibayar-kepada-list"></datalist>
<script>
(function() {
  const list = document.getElementById('ie-dibayar-kepada-list');
  if (!list) return;

  // Pre-populate dari nilai yang sudah tampil di kolom tabel
  const seen = new Set();
  document.querySelectorAll('td.col-dibayar_kepada').forEach(function(td) {
    const txt = td.textContent.trim();
    if (txt && txt !== '-') {
      txt.split(',').forEach(function(name) {
        const n = name.trim();
        if (n && !seen.has(n)) {
          seen.add(n);
          const opt = document.createElement('option');
          opt.value = n;
          list.appendChild(opt);
        }
      });
    }
  });

  // Live fetch saat user mengetik 2+ karakter
  let fetchTimer = null;
  document.addEventListener('input', function(e) {
    if (!e.target || e.target.getAttribute('list') !== 'ie-dibayar-kepada-list') return;
    clearTimeout(fetchTimer);
    const q = e.target.value.trim();
    if (q.length < 2) return;
    fetchTimer = setTimeout(function() {
      fetch('/api/autocomplete/payment-recipients?q=' + encodeURIComponent(q), {
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        }
      })
      .then(function(r) { return r.ok ? r.json() : []; })
      .then(function(data) {
        (Array.isArray(data) ? data : []).forEach(function(name) {
          if (typeof name === 'string' && !seen.has(name)) {
            seen.add(name);
            const opt = document.createElement('option');
            opt.value = name;
            list.appendChild(opt);
          }
        });
      })
      .catch(function() {});
    }, 250);
  });
})();
</script>

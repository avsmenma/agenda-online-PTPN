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
  .ie-input {
    width: 100%; box-sizing: border-box;
    border: 1px solid #d1d5db; border-radius: 4px;
    padding: 4px 6px; font-size: 13px; font-family: inherit;
    background: #fff; color: #111;
  }
  .ie-input:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 2px rgba(59,130,246,.2); }
  .ie-textarea { resize: vertical; min-height: 60px; }
  .ie-spinner {
    display: inline-block; width: 12px; height: 12px; margin-left: 4px;
    border: 2px solid #93c5fd; border-top-color: #3b82f6;
    border-radius: 50%; animation: ie-spin .6s linear infinite; vertical-align: middle;
  }
  @keyframes ie-spin { to { transform: rotate(360deg); } }
</style>

<script>
(function () {
  'use strict';

  const IE_KATEGORI    = @json($ieKategoriList ?? []);
  const IE_SUB         = @json($ieSubKriteriaList ?? []);
  const IE_ITEM        = @json($ieItemSubKriteriaList ?? []);
  const IE_JENIS_BAYAR = @json($ieJenisPembayaranList ?? []);

  const BULAN_LIST = ['Januari','Februari','Maret','April','May','Juni','July','Agustus','September','Oktober','November','Desember'];

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
    kebun               : 'text',
    bagian              : 'text',
    nama_pengirim       : 'text',
    no_berita_acara     : 'text',
    no_spk              : 'text',
    nomor_miro          : 'text',
    no_faktur           : 'text',
    pemaraf             : 'text',
    jenis_pph           : 'text',
    dpp_pph             : 'number',
    ppn_terhutang       : 'number',
    dibayar_kepada      : 'textarea',
    kategori            : 'select_kategori',
    jenis_dokumen       : 'select_sub',
    jenis_sub_pekerjaan : 'select_item',
    jenis_pembayaran    : 'select_jenis',
    bulan               : 'select_bulan',
    tahun               : 'text',
  };

  let activeCell  = null;
  let activeInput = null;

  function buildSelect(field, currentVal) {
    const sel = document.createElement('select');
    sel.className = 'ie-input';
    let options = [];
    if      (field === 'select_kategori') options = IE_KATEGORI.map(k => ({ value: k.nama_kriteria,        label: k.nama_kriteria }));
    else if (field === 'select_sub')      options = IE_SUB.map(k      => ({ value: k.nama_sub_kriteria,    label: k.nama_sub_kriteria }));
    else if (field === 'select_item')     options = IE_ITEM.map(k     => ({ value: k.nama_item_sub_kriteria, label: k.nama_item_sub_kriteria }));
    else if (field === 'select_jenis')    options = IE_JENIS_BAYAR.map(k => ({ value: k.nama_jenis_pembayaran, label: k.nama_jenis_pembayaran }));
    else if (field === 'select_bulan')    options = BULAN_LIST.map(b  => ({ value: b, label: b }));
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

  function createInput(fieldType, rawValue) {
    let el;
    if (fieldType === 'textarea') {
      el = document.createElement('textarea');
      el.className = 'ie-input ie-textarea'; el.value = rawValue ?? ''; el.rows = 3;
    } else if (fieldType.startsWith('select_')) {
      el = buildSelect(fieldType, rawValue ?? '');
    } else if (fieldType === 'date') {
      el = document.createElement('input');
      el.type = 'date'; el.className = 'ie-input'; el.value = rawValue ?? '';
    } else if (fieldType === 'number') {
      el = document.createElement('input');
      el.type = 'text'; el.className = 'ie-input';
      el.value = rawValue ? String(rawValue).replace(/[^0-9]/g, '') : '';
      el.placeholder = '0';
    } else {
      el = document.createElement('input');
      el.type = 'text'; el.className = 'ie-input'; el.value = rawValue ?? '';
    }
    return el;
  }

  function activateCell(cell) {
    if (activeCell && activeCell !== cell) commitCell(activeCell);
    if (activeCell === cell) return;
    const field = cell.dataset.field;
    if (!field) return;
    const fieldType = FIELD_TYPE[field] || 'text';
    const rawValue  = cell.dataset.raw ?? '';
    cell.dataset.originalHtml = cell.innerHTML;
    cell.dataset.originalRaw  = rawValue;
    activeCell  = cell;
    activeInput = createInput(fieldType, rawValue);
    cell.classList.add('ie-editing');
    cell.innerHTML = '';
    cell.appendChild(activeInput);
    setTimeout(() => {
      activeInput.focus();
      if (activeInput.tagName === 'INPUT' && activeInput.type === 'text') activeInput.select();
    }, 20);
    activeInput.addEventListener('keydown', onKeyDown);
    if (activeInput.tagName === 'SELECT') {
      activeInput.addEventListener('change', () => commitCell(cell));
    } else {
      activeInput.addEventListener('blur', () => {
        setTimeout(() => { if (activeCell === cell) commitCell(cell); }, 80);
      });
    }
  }

  function commitCell(cell) {
    if (!cell || !activeInput) return;
    const field    = cell.dataset.field;
    const newValue = activeInput.value;
    const oldRaw   = cell.dataset.originalRaw ?? '';
    if (newValue === oldRaw) { cancelCell(cell); return; }
    const dokumenId = cell.closest('tr').dataset.dokumenId;
    if (!dokumenId) { cancelCell(cell); return; }
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
    .then(r => r.json())
    .then(data => {
      cell.classList.remove('ie-saving');
      if (data.success) {
        const display = data.display_value ?? newValue ?? '-';
        cell.dataset.raw = data.raw_value ?? newValue;
        cell.innerHTML   = display || '-';
        cell.classList.add('ie-saved');
        setTimeout(() => cell.classList.remove('ie-saved'), 700);
      } else {
        cell.innerHTML = cell.dataset.originalHtml;
        cell.classList.add('ie-error');
        setTimeout(() => cell.classList.remove('ie-error'), 700);
        showIeToast('error', data.message || 'Gagal menyimpan.');
      }
    })
    .catch(() => {
      cell.classList.remove('ie-saving');
      cell.innerHTML = cell.dataset.originalHtml;
      cell.classList.add('ie-error');
      setTimeout(() => cell.classList.remove('ie-error'), 700);
      showIeToast('error', 'Koneksi gagal. Coba lagi.');
    });
  }

  function cancelCell(cell) {
    if (!cell) return;
    cell.classList.remove('ie-editing', 'ie-saving');
    cell.innerHTML = cell.dataset.originalHtml || '';
    activeCell = null; activeInput = null;
  }

  function onKeyDown(e) {
    if (!activeCell) return;
    if (e.key === 'Escape') { e.preventDefault(); cancelCell(activeCell); }
    else if (e.key === 'Enter' && activeInput && activeInput.tagName !== 'TEXTAREA') {
      e.preventDefault(); commitCell(activeCell);
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

  document.addEventListener('click', function(e) {
    const cell = e.target.closest('.ie-cell');
    if (cell) { e.stopPropagation(); activateCell(cell); }
    else if (activeCell) commitCell(activeCell);
  });

  document.addEventListener('dblclick', function(e) {
    if (e.target.closest('.ie-cell')) { e.stopPropagation(); e.preventDefault(); }
  }, true);

})();
</script>

/*
 * operator-tabulator.js — Init Tabulator untuk Daftar Dokumen Operator (pilot).
 *
 * Tugas 5: formatter paritas PENUH. Setiap kolom dirender byte-faithful terhadap
 * tabel lama `_tableRowsAjax.blade.php` (badge status, rupiah, tanggal, link,
 * pemaraf, nomor_agenda dua baris). Editor & interaksi menyusul di Tugas 6-7.
 * Membaca konfigurasi dari window.OPERATOR_TABULATOR_CONFIG yang di-inject oleh
 * daftarDokumenTabulator.blade.php.
 *
 * PENTING (escaping): formatter fungsi yang mengembalikan string HTML TIDAK
 * di-escape otomatis oleh Tabulator (berbeda dari formatter teks default). Maka
 * setiap nilai asal-user yang disuntikkan ke HTML WAJIB melewati esc().
 */
(function () {
  const CFG = window.OPERATOR_TABULATOR_CONFIG;
  if (!CFG || typeof Tabulator === 'undefined') return;

  // === Helper: HTML-escape untuk semua nilai user-asal di dalam formatter HTML. ===
  function esc(value) {
    if (value === null || value === undefined) return '';
    return String(value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  // Kolom tanggal — nilainya SUDAH diformat sisi-server (row.dates[key]); klien
  // tidak boleh mem-parse tanggal (peta format hidup di OperatorDocumentRow).
  const DATE_COLS = new Set([
    'tanggal_spp', 'tanggal_berita_acara', 'tanggal_spk', 'tanggal_berakhir_spk',
    'tanggal_masuk', 'tanggal_paraf', 'tanggal_selesai_diproses',
    'tanggal_kembali_ke_bagian', 'tanggal_hasil_koreksi_bagian',
    'tanggal_dibayar', 'tanggal_faktur', 'tanggal_selesai_verifikasi_pajak',
  ]);

  // === Formatter per kolom (rujukan baris ada di komentar tiap fungsi). ===

  // _tableRowsAjax.blade.php:99-101 — identitas dua baris, kolom beku.
  function fmtNomorAgenda(cell) {
    const d = cell.getRow().getData();
    return '<strong>' + esc(d.nomor_agenda) + '</strong><br>' +
      '<small class="text-muted">' + esc(d.bulan) + ' ' + esc(d.tahun) + '</small>';
  }

  // _tableRowsAjax.blade.php:134-150 — badge status dari display_status.variant.
  function fmtStatus(cell) {
    const ds = cell.getRow().getData().display_status || {};
    const variant = ds.variant;

    if (variant === 'draft') {
      return '<span class="badge-status badge-belum-dikirim">' +
        '<i class="fa-solid fa-file-pen me-1"></i><span>Belum Dikirim</span></span>';
    }
    if (variant === 'ditolak_verifikasi' || variant === 'dikembalikan') {
      const label = variant === 'dikembalikan' ? 'Dikembalikan' : 'Dokumen Ditolak';
      return '<span class="badge-status badge-ditolak" style="background:linear-gradient(135deg,#dc3545,#b02a37);color:white;">' +
        '<i class="fa-solid fa-rotate-left me-1"></i><span>' + label + '</span></span>';
    }
    if (variant === 'menunggu_approval_verifikasi') {
      return '<span class="badge-status" style="background:linear-gradient(135deg,#ffc107,#ff8c00);color:white;">' +
        '<i class="fa-solid fa-clock me-1"></i><span>Menunggu Approve Team Verifikasi</span></span>';
    }
    // else — terkirim.
    return '<span class="badge-status badge-terkirim">' +
      '<i class="fa-solid fa-check me-1"></i><span>' + esc(ds.label) + '</span></span>';
  }

  // _tableRowsAjax.blade.php:107 — nilai rupiah tebal (sudah terformat server).
  function fmtNilaiRupiah(cell) {
    return '<strong>' + esc(cell.getRow().getData().nilai_rupiah_formatted) + '</strong>';
  }

  // _tableRowsAjax.blade.php:225 — DPP PPh (server: 'Rp x.xxx' atau '-').
  function fmtDppPph(cell) {
    return esc(cell.getRow().getData().dpp_pph_formatted);
  }

  // _tableRowsAjax.blade.php:227 — PPN terhutang (server: 'Rp x.xxx' atau '-').
  function fmtPpnTerhutang(cell) {
    return esc(cell.getRow().getData().ppn_terhutang_formatted);
  }

  // _tableRowsAjax.blade.php:173-178 — join nama penerima (fallback kolom flat).
  function fmtDibayarKepada(cell) {
    return esc(cell.getRow().getData().dibayar_kepada) || '-';
  }

  // _tableRowsAjax.blade.php:219 — join nomor PO (server fallback ke NO_PO/'-').
  function fmtNomorPo(cell) {
    return esc(cell.getRow().getData().nomor_po) || '-';
  }

  // _tableRowsAjax.blade.php:164 — nomor_miro_display > nomor_miro > '-'.
  function fmtNomorMiro(cell) {
    const d = cell.getRow().getData();
    return esc(d.nomor_miro_display || d.nomor_miro || '-');
  }

  // _tableRowsAjax.blade.php:207-213 — badge hijau inline bila ada pemaraf.
  function fmtPemaraf(cell) {
    const value = cell.getValue();
    if (!value) return '-';
    return '<span style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;' +
      'background:linear-gradient(135deg,#22c55e,#16a34a);color:white;border-radius:6px;' +
      'font-size:11px;font-weight:600;"><i class="fa-solid fa-check-circle"></i> ' +
      esc(value) + '</span>';
  }

  // _tableRowsAjax.blade.php:237-243 — link ter-sanitasi server (link_safe).
  function fmtLink(cell) {
    const safe = cell.getRow().getData().link_safe;
    if (!safe) return '-';
    return '<a href="' + esc(safe) + '" target="_blank" rel="noopener noreferrer" ' +
      'onclick="event.stopPropagation()"><i class="fa-solid fa-link fa-sm"></i> Lihat</a>';
  }

  // _tableRowsAjax.blade.php:230-236 — link dokumen pajak (link_dokumen_pajak_safe).
  function fmtLinkPajak(cell) {
    const safe = cell.getRow().getData().link_dokumen_pajak_safe;
    if (!safe) return '-';
    return '<a href="' + esc(safe) + '" target="_blank" rel="noopener noreferrer" ' +
      'onclick="event.stopPropagation()"><i class="fa-solid fa-link fa-sm"></i> Lihat Dokumen</a>';
  }

  // Kolom tanggal — ambil string terformat dari row.dates[field]; kosong → '-'.
  function fmtDate(cell) {
    const dates = cell.getRow().getData().dates || {};
    const value = dates[cell.getColumn().getField()];
    return (value === null || value === undefined || value === '') ? '-' : esc(value);
  }

  // Kolom lain — teks mentah ter-escape; kosong → '-'.
  function fmtPlain(cell) {
    const value = cell.getValue();
    if (value === null || value === undefined || value === '') return '-';
    return esc(value);
  }

  const FORMATTERS = {
    nomor_agenda: fmtNomorAgenda,
    status: fmtStatus,
    nilai_rupiah: fmtNilaiRupiah,
    dpp_pph: fmtDppPph,
    ppn_terhutang: fmtPpnTerhutang,
    dibayar_kepada: fmtDibayarKepada,
    nomor_po: fmtNomorPo,
    nomor_miro: fmtNomorMiro,
    pemaraf: fmtPemaraf,
    link: fmtLink,
    link_dokumen_pajak: fmtLinkPajak,
  };

  // Pilih formatter untuk sebuah kolom: khusus → tanggal → teks polos.
  function getFormatter(key) {
    if (FORMATTERS[key]) return FORMATTERS[key];
    if (DATE_COLS.has(key)) return fmtDate;
    return fmtPlain;
  }

  // Bangun definisi kolom: nomor baris beku kiri, lalu kolom terpilih dengan
  // formatter paritas. nomor_agenda dibekukan agar identitas selalu terlihat.
  function buildColumns(cfg) {
    const cols = [{ formatter: 'rownum', width: 60, frozen: true, headerSort: false, title: 'No' }];
    (cfg.columns || []).forEach(function (c) {
      const def = { title: c.label, field: c.key, formatter: getFormatter(c.key) };
      if (c.key === 'nomor_agenda') {
        def.frozen = true;
        def.variableHeight = true; // sel dua baris (nomor + bulan/tahun) tak terpotong.
      }
      cols.push(def);
    });
    return cols;
  }

  const table = new Tabulator('#operatorTabulatorTable', {
    ajaxURL: CFG.dataUrl,
    // Progressive load (scroll): Tabulator membaca `last_page` & `data` dari respons
    // endpoint /documents/data ({last_page,total,data}) — nama field default cocok.
    progressiveLoad: 'scroll',
    progressiveLoadDelay: 200,
    paginationSize: 100,
    ajaxResponse: function (url, params, response) { return response; },
    layout: 'fitDataStretch',
    height: '70vh',
    index: 'id',
    columns: buildColumns(CFG),
    placeholder: 'Tidak ada dokumen.',
  });

  window.operatorTable = table;
})();

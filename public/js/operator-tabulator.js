/*
 * operator-tabulator.js — Init Tabulator untuk Daftar Dokumen Operator (pilot).
 *
 * Versi SKELETON (Tugas 4): mount tabel + progressive load + kolom minimal
 * (rownum + judul/field). Formatter paritas penuh menyusul di Tugas 5, editor &
 * interaksi di Tugas 6-7. Membaca konfigurasi dari window.OPERATOR_TABULATOR_CONFIG
 * yang di-inject oleh daftarDokumenTabulator.blade.php.
 */
(function () {
  const CFG = window.OPERATOR_TABULATOR_CONFIG;
  if (!CFG || typeof Tabulator === 'undefined') return;

  // Bangun definisi kolom: kolom nomor baris beku di kiri, lalu kolom terpilih.
  // Kolom nomor_agenda dibekukan agar identitas baris selalu terlihat saat scroll.
  function buildColumns(cfg) {
    const cols = [{ formatter: 'rownum', width: 60, frozen: true, headerSort: false, title: 'No' }];
    (cfg.columns || []).forEach(function (c) {
      cols.push({ title: c.label, field: c.key, frozen: c.key === 'nomor_agenda' });
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

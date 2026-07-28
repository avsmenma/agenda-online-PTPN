/*
 * document-tabulator.js — Init Tabulator untuk Daftar Dokumen Operator (pilot).
 *
 * Tugas 5: formatter paritas PENUH. Setiap kolom dirender byte-faithful terhadap
 * tabel lama `_tableRowsAjax.blade.php` (badge status, rupiah, tanggal, link,
 * pemaraf, nomor_agenda dua baris). Editor & interaksi menyusul di Tugas 6-7.
 * Membaca konfigurasi dari window.DOCUMENT_TABULATOR_CONFIG yang di-inject oleh
 * daftarDokumenTabulator.blade.php.
 *
 * CLAUDE.md §8 — perilaku ala spreadsheet, lengkap:
 *   Tahap A (baca): sel aktif + navigasi panah & auto-scroll, klik-tunggal
 *     memindahkan sel aktif, dobel-klik / Enter mulai edit (Esc batal), seleksi
 *     blok (drag, Shift+Klik, Shift+Panah), Ctrl+C salin sel & blok.
 *   Tahap B (tulis): Delete/Backspace mengosongkan, Ctrl+V tempel ala Excel,
 *     Ctrl+Z membatalkan, Ctrl+Y mengulangi — semuanya ikut tersimpan ke server
 *     lewat satu jalur tulis (applyBulkEdits). Lihat blok Tahap B di bawah.
 *
 * PENTING (escaping): formatter fungsi yang mengembalikan string HTML TIDAK
 * di-escape otomatis oleh Tabulator (berbeda dari formatter teks default). Maka
 * setiap nilai asal-user yang disuntikkan ke HTML WAJIB melewati esc().
 */
(function () {
  const CFG = window.DOCUMENT_TABULATOR_CONFIG;
  if (!CFG || typeof Tabulator === 'undefined') return;

  // Elemen mount tabel. Id datang dari config (CFG.mountId) agar engine tak terikat ke
  // satu role; query internal memakai elemen ini (relatif), bukan selektor '#id ...' global.
  function mountEl() { return document.getElementById(CFG.mountId); }

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

  // === Tugas 6: Inline edit + Tambah Baris + dropdown Pengurus Dokumen ===
  //
  // Peta tipe field per kolom — SALINAN persis dari FIELD_TYPE di
  // resources/views/partials/_inlineEditEngine.blade.php:149-185. Field yang
  // tidak terdaftar → 'text'. Menentukan jenis editor Tabulator per kolom.
  const FIELD_TYPE = {
    nomor_agenda: 'text',
    nomor_spp: 'text',
    uraian_spp: 'textarea',
    nilai_rupiah: 'number',
    tanggal_spp: 'date',
    tanggal_berita_acara: 'date',
    tanggal_spk: 'date',
    tanggal_berakhir_spk: 'date',
    tanggal_faktur: 'date',
    tanggal_paraf: 'date',
    tanggal_miro: 'date',
    tanggal_selesai_verifikasi_pajak: 'date',
    tanggal_dibayar: 'date',
    kebun: 'text',
    bagian: 'select_bagian',
    nama_pengirim: 'text',
    no_berita_acara: 'text',
    no_spk: 'text',
    nomor_miro: 'text',
    no_faktur: 'text',
    pemaraf: 'text',
    jenis_pph: 'text',
    dpp_pph: 'number',
    ppn_terhutang: 'number',
    dibayar_kepada: 'text',
    kategori: 'select_kategori',
    jenis_dokumen: 'select_sub',
    jenis_sub_pekerjaan: 'select_item',
    jenis_pembayaran: 'select_jenis',
    bulan: 'select_bulan',
    tahun: 'text',
    npwp: 'text',
    link_dokumen_pajak: 'text',
    link: 'text',
  };

  // Kolom yang TIDAK boleh diedit (paritas plan §Global Constraints).
  const NON_EDITABLE_FIELDS = ['tanggal_masuk', 'status', 'nomor_mirror', 'keterangan'];

  // Peta format tanggal per kolom — IDENTIK OperatorDocumentRow::formatDates.
  // Dipakai saat menyusun ulang tampilan tanggal setelah edit sukses agar sel
  // yang baru diedit tampil sama persis dengan render awal sisi-server.
  const DATE_FORMATS = {
    tanggal_spp: 'd-m-Y', tanggal_berita_acara: 'd-m-Y', tanggal_spk: 'd-m-Y', tanggal_berakhir_spk: 'd-m-Y',
    tanggal_masuk: 'd-m-Y H:i',
    tanggal_paraf: 'd/m/Y H:i', tanggal_selesai_diproses: 'd/m/Y H:i',
    tanggal_kembali_ke_bagian: 'd/m/Y H:i', tanggal_hasil_koreksi_bagian: 'd/m/Y H:i',
    tanggal_dibayar: 'd/m/Y', tanggal_faktur: 'd/m/Y', tanggal_selesai_verifikasi_pajak: 'd/m/Y',
  };

  // --- Helper jaringan ---
  function jsonHeaders() {
    return {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': CFG.csrf,
      'Accept': 'application/json',
    };
  }
  function inlineUpdateUrl(id) { return CFG.inlineUpdateTpl.replace('{id}', id); }
  function handlerUrl(id) { return CFG.handlerTpl.replace('{id}', id); }

  // Toast mandiri (fixed bottom, hijau/merah) — setara showIeToast lama tanpa
  // bergantung pada fungsi page-local view lama.
  function opToast(type, message) {
    const existing = document.getElementById('op-toast');
    if (existing) existing.remove();
    const t = document.createElement('div');
    t.id = 'op-toast';
    t.style.cssText = 'position:fixed;bottom:24px;left:50%;transform:translateX(-50%);' +
      'background:' + (type === 'error' ? '#dc3545' : '#28a745') + ';color:#fff;' +
      'padding:10px 20px;border-radius:8px;font-size:13px;z-index:99999;' +
      'box-shadow:0 4px 12px rgba(0,0,0,.2);max-width:80vw;';
    t.textContent = message; // textContent = auto-escape.
    document.body.appendChild(t);
    setTimeout(function () { t.remove(); }, 3500);
  }

  // --- Helper format (untuk memperbarui key turunan setelah edit sukses) ---
  // number_format(n,0,',','.') → integer dgn pemisah ribuan '.'.
  function idNumber(n) {
    const cleaned = String(n === null || n === undefined ? '' : n).replace(/[^0-9.\-]/g, '');
    const int = Math.round(parseFloat(cleaned) || 0);
    return String(int).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  }
  // dpp_pph / ppn_terhutang: 'Rp '.number_format atau '-' (paritas OperatorDocumentRow).
  function formatRp(raw) {
    if (raw === null || raw === undefined || raw === '') return '-';
    return 'Rp ' + idNumber(raw);
  }
  // Normalisasi nilai date apa pun (ISO/Carbon/Y-m-d) → 'YYYY-MM-DD' (untuk <input type=date> & banding).
  function toDateInput(v) {
    if (v === null || v === undefined || v === '') return '';
    const m = String(v).match(/^(\d{4})-(\d{2})-(\d{2})/);
    return m ? m[1] + '-' + m[2] + '-' + m[3] : '';
  }
  // Terapkan pola PHP-date (subset d,m,Y,H,i) ke nilai Y-m-d[ H:i] → string tampilan.
  function formatDateForCol(field, raw) {
    if (raw === null || raw === undefined || raw === '') return '-';
    const m = String(raw).match(/^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2}))?/);
    if (!m) return '-';
    const pattern = DATE_FORMATS[field] || 'd-m-Y';
    return pattern
      .replace('d', m[3]).replace('m', m[2]).replace('Y', m[1])
      .replace('H', m[4] || '00').replace('i', m[5] || '00');
  }
  // Normalisasi numerik untuk banding "tak berubah" (samakan '4832149.00' ↔ '4832149').
  function normNum(v) {
    if (v === null || v === undefined || v === '') return '';
    return String(Math.round(parseFloat(String(v).replace(/[^0-9.]/g, '')) || 0));
  }
  // Apakah nilai baru == lama (per tipe) → bila ya, lewati PATCH.
  function sameValue(field, fieldType, a, b) {
    if (fieldType === 'number') return normNum(a) === normNum(b);
    if (fieldType === 'date' || DATE_COLS.has(field)) return toDateInput(a) === toDateInput(b);
    return String(a === null || a === undefined ? '' : a) === String(b === null || b === undefined ? '' : b);
  }

  // --- Chained-select: port PERSIS logika filter _inlineEditEngine.blade.php:362-437,
  //     namun membaca data baris (objek) alih-alih DOM. ---
  function normalizeInline(value) {
    return String(value === null || value === undefined ? '' : value).trim().toLowerCase();
  }
  function getObjectValue(obj, keys) {
    if (!obj) return '';
    for (let i = 0; i < keys.length; i++) {
      const k = keys[i];
      if (obj[k] !== undefined && obj[k] !== null && String(obj[k]).trim() !== '') return obj[k];
    }
    return '';
  }
  function inlineValuesEqual(a, b) { return normalizeInline(a) === normalizeInline(b); }
  function findOptionByValue(list, labelKeys, value) {
    const needle = normalizeInline(value);
    if (!needle) return null;
    return (list || []).find(function (item) {
      return labelKeys.some(function (k) { return normalizeInline(item[k]) === needle; });
    }) || null;
  }
  function filterSubOptions(rowData) {
    const IE = CFG.ie || {};
    const kategoriValue = rowData.kategori || '';
    if (!normalizeInline(kategoriValue)) return IE.sub || [];
    const kategori = findOptionByValue(IE.kategori, ['nama_kriteria', 'id'], kategoriValue);
    const kategoriId = getObjectValue(kategori, ['id', 'id_kategori_kriteria']) || kategoriValue;
    return (IE.sub || []).filter(function (sub) {
      const parent = getObjectValue(sub, ['id_kategori_kriteria', 'kategori_id', 'id_kategori', 'kategori']);
      if (!normalizeInline(parent)) return false;
      return inlineValuesEqual(parent, kategoriId) || inlineValuesEqual(parent, kategoriValue);
    });
  }
  function filterItemOptions(rowData) {
    const IE = CFG.ie || {};
    const subValue = rowData.jenis_dokumen || '';
    if (!normalizeInline(subValue)) return IE.item || [];
    const scopedSub = filterSubOptions(rowData).find(function (sub) {
      return inlineValuesEqual(sub.nama_sub_kriteria, subValue) || inlineValuesEqual(sub.id, subValue);
    });
    const sub = scopedSub || findOptionByValue(IE.sub, ['nama_sub_kriteria', 'id'], subValue);
    const subId = getObjectValue(sub, ['id', 'id_sub_kriteria']) || subValue;
    return (IE.item || []).filter(function (item) {
      const parent = getObjectValue(item, ['id_sub_kriteria', 'sub_kriteria_id', 'id_sub']);
      if (!normalizeInline(parent)) return false;
      return inlineValuesEqual(parent, subId) || inlineValuesEqual(parent, subValue);
    });
  }

  // Bangun daftar opsi {value,label} untuk editor 'list' — mapping value/label
  // IDENTIK buildSelect() lama (:455-476), termasuk opsi kosong '-- Pilih --'.
  function buildSelectValues(type, rowData) {
    const IE = CFG.ie || {};
    let opts = [];
    if (type === 'select_kategori') {
      opts = (IE.kategori || []).map(function (k) { return { value: k.nama_kriteria, label: k.nama_kriteria }; });
    } else if (type === 'select_sub') {
      opts = filterSubOptions(rowData).map(function (k) { return { value: k.nama_sub_kriteria, label: k.nama_sub_kriteria }; });
    } else if (type === 'select_item') {
      opts = filterItemOptions(rowData).map(function (k) { return { value: k.nama_item_sub_kriteria, label: k.nama_item_sub_kriteria }; });
    } else if (type === 'select_jenis') {
      opts = (IE.jenis || []).map(function (k) { return { value: k.nama_jenis_pembayaran, label: k.nama_jenis_pembayaran }; });
    } else if (type === 'select_bulan') {
      opts = (CFG.bulanList || []).map(function (b) { return { value: b, label: b }; });
    } else if (type === 'select_bagian') {
      opts = (IE.bagian || []).map(function (b) {
        return { value: b.kode, label: (b.nama && b.nama !== b.kode) ? (b.kode + ' — ' + b.nama) : b.kode };
      });
    }
    return [{ value: '', label: '-- Pilih --' }].concat(opts);
  }

  // --- Editor kustom (number & date) untuk kontrol penuh nilai kirim ---
  // Number: input teks, pra-isi integer ter-normalisasi (paritas createInput number lama).
  function numberEditor(cell, onRendered, success, cancel) {
    const input = document.createElement('input');
    input.type = 'text';
    input.setAttribute('inputmode', 'numeric');
    input.className = 'op-inline-editor';
    const raw = cell.getValue();
    input.value = (raw === null || raw === undefined || raw === '')
      ? ''
      : String(Math.round(parseFloat(String(raw).replace(/[^0-9.]/g, '')) || 0));
    input.style.width = '100%';
    input.style.boxSizing = 'border-box';
    onRendered(function () { input.focus(); try { input.select(); } catch (e) {} });
    let done = false;
    function commit() { if (done) return; done = true; success(input.value); }
    input.addEventListener('blur', commit);
    input.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') { e.preventDefault(); commit(); }
      else if (e.key === 'Escape') { e.preventDefault(); done = true; cancel(); }
    });
    return input;
  }
  // Date: <input type=date>, nilai pra-isi & kirim = 'YYYY-MM-DD' (yang diminta server).
  function dateEditor(cell, onRendered, success, cancel) {
    const input = document.createElement('input');
    input.type = 'date';
    input.className = 'op-inline-editor';
    input.value = toDateInput(cell.getValue());
    input.style.width = '100%';
    input.style.boxSizing = 'border-box';
    onRendered(function () { input.focus(); });
    let done = false;
    function commit() { if (done) return; done = true; success(input.value); }
    input.addEventListener('change', commit);
    input.addEventListener('blur', commit);
    input.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') { e.preventDefault(); commit(); }
      else if (e.key === 'Escape') { e.preventDefault(); done = true; cancel(); }
    });
    return input;
  }

  // Editor teks panjang (uraian_spp). Editor 'textarea' BAWAAN Tabulator memakai
  // Enter sebagai baris baru, sehingga sel tidak pernah bisa disimpan lewat Enter —
  // bertentangan dengan CLAUDE.md §8 ("tekan Enter lagi untuk menyimpan").
  // Di sini: Enter = SIMPAN, Shift+Enter = baris baru, Esc = batal.
  function textareaEditor(cell, onRendered, success, cancel) {
    const area = document.createElement('textarea');
    area.className = 'op-inline-editor';
    const value = cell.getValue();
    area.value = (value === null || value === undefined) ? '' : value;
    area.style.width = '100%';
    area.style.boxSizing = 'border-box';
    area.style.minHeight = '60px';
    area.style.font = 'inherit';
    area.style.lineHeight = '1.35';
    area.style.resize = 'vertical';

    onRendered(function () {
      area.focus();
      // Tinggi mengikuti isi supaya teks panjang tak perlu digulir saat diedit.
      area.style.height = Math.max(60, area.scrollHeight) + 'px';
      // Kursor di akhir teks, bukan menyeleksi semua — mengetik tidak menghapus isi lama.
      try { area.setSelectionRange(area.value.length, area.value.length); } catch (e) {}
    });

    let done = false;
    function commit() { if (done) return; done = true; success(area.value); }
    area.addEventListener('blur', commit);
    area.addEventListener('keydown', function (e) {
      // stopPropagation wajib: tanpa itu Enter/Esc naik ke penangan tabel dan
      // memindahkan sel aktif atau membuka ulang editor.
      if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); e.stopPropagation(); commit(); }
      else if (e.key === 'Escape') { e.preventDefault(); e.stopPropagation(); done = true; cancel(); }
      // Shift+Enter sengaja dibiarkan default → menyisipkan baris baru.
    });
    return area;
  }

  // Petakan field → definisi editor Tabulator berdasar FIELD_TYPE.
  function editorFor(field) {
    const type = FIELD_TYPE[field] || 'text';
    if (type === 'textarea') return { editor: textareaEditor };
    if (type === 'number') return { editor: numberEditor };
    if (type === 'date') return { editor: dateEditor };
    if (type.indexOf('select_') === 0) {
      return {
        editor: 'list',
        // FUNGSI: dievaluasi saat editor dibuka agar filter berantai membaca
        // nilai parent (kategori/jenis_dokumen) TERKINI dari baris.
        editorParams: function (cell) {
          return { values: buildSelectValues(type, cell.getRow().getData()), autocomplete: false };
        },
      };
    }
    return { editor: 'input' };
  }

  // Gerbang editable per sel (paritas brief): baris can_edit && kolom bukan non-editable.
  function editableGate(cell) {
    const d = cell.getRow().getData();
    const f = cell.getColumn().getField();
    return !!d.can_edit && NON_EDITABLE_FIELDS.indexOf(f) === -1;
  }

  // Setelah edit sukses: susun objek update baris agar FORMATTER Tugas 5 merender
  // ulang dgn benar. Simpan raw_value ke field (untuk editor berikutnya) + key
  // turunan tampilan (nilai_rupiah_formatted / *_formatted / dates[field] / *_safe).
  function buildPostEditPatch(field, fieldType, data, rowData) {
    const raw = (data.raw_value !== undefined) ? data.raw_value
      : (data.display_value !== undefined ? data.display_value : '');
    const patch = {};
    patch[field] = raw;
    if (field === 'nilai_rupiah') {
      patch.nilai_rupiah_formatted = data.display_value || '-'; // server: 'Rp. ...' / '-'
    } else if (field === 'dpp_pph') {
      patch.dpp_pph_formatted = formatRp(raw);
    } else if (field === 'ppn_terhutang') {
      patch.ppn_terhutang_formatted = formatRp(raw);
    } else if (DATE_COLS.has(field)) {
      const dates = {};
      Object.assign(dates, rowData.dates || {});
      dates[field] = formatDateForCol(field, raw);
      patch.dates = dates;
    } else if (field === 'nomor_miro') {
      patch.nomor_miro_display = raw; // formatter fmtNomorMiro membaca nomor_miro_display dulu.
    } else if (field === 'link') {
      patch.link_safe = raw || null;
    } else if (field === 'link_dokumen_pajak') {
      patch.link_dokumen_pajak_safe = raw || null;
    }
    return patch;
  }

  // SATU-SATUNYA pengirim perubahan sel ke server. Dipakai jalur editor
  // (onCellEdited) maupun jalur massal Tahap B (saveCell) — supaya aturan
  // header/CSRF/penanganan balasan tidak pernah bercabang jadi dua versi.
  // Pola fetch mengikuti _inlineEditEngine.blade.php:632-674.
  function patchCell(id, field, value) {
    return fetch(inlineUpdateUrl(id), {
      method: 'PATCH',
      headers: jsonHeaders(),
      body: JSON.stringify({ field: field, value: value }),
    })
      .then(async function (r) {
        const data = await r.json().catch(function () { return {}; });
        return { ok: r.ok && data.success !== false, data: data };
      })
      .catch(function () { return { ok: false, data: {}, offline: true }; });
  }

  // === Masalah 3 (brief 2026-07-22): kategori/jenis_dokumen berubah → anak jadi
  // tak valid ===
  // filterSubOptions()/filterItemOptions() (di atas, ~190-216) SUDAH menyaring
  // opsi anak sesuai induk saat editor DIBUKA, tapi nilai anak yang SUDAH
  // tersimpan tidak pernah dikosongkan saat induk berubah. Peta kolom: kategori =
  // Kriteria CF, jenis_dokumen = Sub Kriteria, jenis_sub_pekerjaan = Item Sub
  // Kriteria.
  // HANYA dipakai jalur editor (onCellEdited di bawah) — SENGAJA TIDAK dipasang
  // di pasteMatrix/applyBulkEdits: saat user menempel satu baris utuh yang berisi
  // kategori+sub+item sekaligus, semua sel ditulis bersamaan dan nilai yang
  // ditempel adalah maksud eksplisit user — reset otomatis di sana akan
  // menghapus sub/item yang baru saja ditempel secara sah.
  const DEPENDENT_FIELDS = {
    kategori: ['jenis_dokumen', 'jenis_sub_pekerjaan'],
    jenis_dokumen: ['jenis_sub_pekerjaan'],
  };

  // Kosongkan (tampilan DAN server via PATCH — pola sama seperti patchCell di
  // atas) anak yang jadi tak valid akibat perubahan `field`. Hanya anak yang
  // MASIH berisi yang di-PATCH; anak yang gagal disimpan dibiarkan apa adanya
  // (tidak dikosongkan di tampilan). Mengembalikan Promise berisi entri
  // {id,field,before,after} untuk anak yang BENAR-BENAR tersimpan — dipakai
  // pemanggil (onCellEdited) untuk digabung ke SATU entri riwayat bersama
  // perubahan induk, supaya satu Ctrl+Z membatalkan induk + anak sekaligus.
  function resetDependentFields(row, field) {
    const children = DEPENDENT_FIELDS[field];
    if (!children) return Promise.resolve([]);
    const toReset = children.filter(function (f) {
      const v = row.getData()[f];
      return v !== null && v !== undefined && String(v) !== '';
    });
    if (toReset.length === 0) return Promise.resolve([]);
    const id = row.getData().id;
    const tasks = toReset.map(function (f) {
      const before = row.getData()[f];
      return patchCell(id, f, '').then(function (res) {
        if (!res.ok) return null; // gagal → anak ini TIDAK dikosongkan.
        row.update(buildPostEditPatch(f, FIELD_TYPE[f] || 'text', res.data, row.getData()));
        return { id: id, field: f, before: before, after: '' };
      });
    });
    return Promise.all(tasks).then(function (results) {
      return results.filter(Boolean);
    });
  }

  // cellEdited → simpan hasil edit lewat editor (dobel-klik / Enter).
  // Beda dari jalur massal: editor SUDAH menuliskan nilai baru ke sel, jadi bila
  // server menolak kita harus mengembalikannya (restoreOldValue), bukan sekadar
  // tidak menampilkan apa-apa.
  function onCellEdited(cell) {
    const row = cell.getRow();
    const field = cell.getColumn().getField();
    const fieldType = FIELD_TYPE[field] || 'text';
    const newValue = cell.getValue();
    const oldValue = cell.getOldValue();

    // Lewati jika tak berubah (mis. beda format tapi setara).
    if (sameValue(field, fieldType, newValue, oldValue)) return;

    patchCell(row.getData().id, field, newValue).then(function (res) {
      if (!res.ok) {
        cell.restoreOldValue();
        opToast('error', res.offline ? 'Koneksi gagal. Coba lagi.' : (res.data.message || 'Gagal menyimpan.'));
        return;
      }
      // row.update() TIDAK memicu cellEdited (hanya edit-oleh-user yang memicu).
      row.update(buildPostEditPatch(field, fieldType, res.data, row.getData()));
      const parentChange = { id: row.getData().id, field: field, before: oldValue, after: newValue };
      // Masalah 3: kategori/jenis_dokumen berubah → kosongkan anak yang tak lagi
      // valid, lalu satukan ke SATU entri riwayat bersama perubahan induk.
      resetDependentFields(row, field).then(function (childChanges) {
        // §8: "setiap perubahan dapat dibatalkan dengan Ctrl+Z" — edit satu sel lewat
        // editor ikut masuk riwayat, bukan hanya operasi massal.
        pushHistory([parentChange].concat(childChanges));
      });
    });
  }

  // Formatter kolom tetap "Pengurus Dokumen" — render <select> dari handler_options
  // (termasuk optgroup Bagian), opsi cocok row.handler = selected. Perubahan
  // ditangani listener change terdelegasi (lihat init). stopPropagation agar klik
  // select tak memicu seleksi/detail baris (paritas _tableRowsAjax.blade.php:250).
  function fmtHandler(cell) {
    const d = cell.getRow().getData();
    const opts = d.handler_options;
    if (!Array.isArray(opts) || opts.length === 0) return '-';
    const current = (d.handler === null || d.handler === undefined) ? '' : String(d.handler);
    const canChange = !!d.can_change_handler;
    function optionHtml(o) {
      const val = (o.value === null || o.value === undefined) ? '' : String(o.value);
      return '<option value="' + esc(val) + '"' + (val === current ? ' selected' : '') + '>' + esc(o.label) + '</option>';
    }
    // Paritas document-handler-select.blade.php:53 — disabled kecuali viewer
    // adalah pengurus dokumen saat ini (dan tak ada status pending).
    let html = '<select class="op-handler-select" data-document-id="' + esc(d.id) +
      '" data-prev="' + esc(current) + '"' + (canChange ? '' : ' disabled') +
      ' title="' + (canChange ? 'Ubah pengurus dokumen' : 'Hanya pengurus dokumen saat ini yang dapat mengubah') + '"' +
      ' onclick="event.stopPropagation()" onmousedown="event.stopPropagation()">';
    opts.forEach(function (opt) {
      if (opt && opt.optgroup) {
        html += '<optgroup label="' + esc(opt.optgroup) + '">';
        (opt.options || []).forEach(function (o) { html += optionHtml(o); });
        html += '</optgroup>';
      } else if (opt) {
        html += optionHtml(opt);
      }
    });
    html += '</select>';
    return html;
  }

  // === Formatter per kolom (rujukan baris ada di komentar tiap fungsi). ===

  // _tableRowsAjax.blade.php:99-101 — identitas dua baris, kolom beku.
  function fmtNomorAgenda(cell) {
    const d = cell.getRow().getData();
    return '<strong>' + esc(d.nomor_agenda) + '</strong><br>' +
      '<small class="text-muted">' + esc(d.bulan) + ' ' + esc(d.tahun) + '</small>';
  }

  // _tableRowsAjax.blade.php:134-150 — badge status dari display_status.variant.
  function fmtStatus(cell) {
    const d = cell.getRow().getData();
    const ds = d.display_status || {};
    const variant = ds.variant;

    if (variant === 'draft') {
      return '<span class="badge-status badge-belum-dikirim">' +
        '<i class="fa-solid fa-file-pen me-1"></i><span>Belum Dikirim</span></span>';
    }
    if (variant === 'ditolak_verifikasi' || variant === 'dikembalikan') {
      const label = variant === 'dikembalikan' ? 'Dikembalikan' : 'Dokumen Ditolak';
      // Tugas 7e: badge penolakan/dikembalikan dapat diklik → modal alasan global.
      // stopPropagation agar klik tak memicu seleksi/detail baris.
      return '<span class="badge-status badge-ditolak op-reject-badge" ' +
        'style="background:linear-gradient(135deg,#dc3545,#b02a37);color:white;cursor:pointer;" ' +
        'title="Klik untuk lihat alasan penolakan" ' +
        'onclick="event.stopPropagation();window.openOperatorRejectReason(' + esc(d.id) + ')">' +
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

  // === Formatter akutansi (Rollout 1) — merender objek server, nol logika bisnis. ===

  // Badge Status akutansi/perpajakan/verifikasi dari row.status_badge
  // {class, icon, text, link, style?, title?}. Port kolom Status
  // _rows.blade.php:459-510 (pohon keputusan sudah di server).
  // Rollout 3 (verifikasi) — perluasan ADDITIF:
  //   - guard direlaksasi dari "!b.class" ke "!b.class && !b.text": cabang #16
  //     verifikasi (Menunggu Approve umum, VerifikasiDocumentRow::buildStatusBadge())
  //     mengemit class KOSONG ('') tapi tetap punya text — legacy-nya memang
  //     <span class="badge-status" ...> tanpa modifier class. Akutansi/perpajakan
  //     TIDAK PERNAH mengemit class kosong (selalu non-empty), jadi guard lama
  //     tak pernah menyala untuk mereka — perilaku mereka tak berubah.
  //   - style/title dirender HANYA bila field ada (opsional, null di akutansi/
  //     perpajakan → tak ada atribut tambahan → HTML byte-identik dgn sebelumnya).
  function fmtAkutansiStatus(cell) {
    const b = cell.getRow().getData().status_badge;
    if (!b || (!b.class && !b.text)) return '-';
    let html = '<span class="badge-status ' + esc(b.class) + '"';
    if (b.style) { html += ' style="' + esc(b.style) + '"'; }
    if (b.title) { html += ' title="' + esc(b.title) + '"'; }
    html += ' onclick="event.stopPropagation()">';
    if (b.icon) { html += '<i class="fa-solid ' + esc(b.icon) + ' me-1"></i>'; }
    html += esc(b.text);
    if (b.link && b.link.href) {
      html += ' <a href="' + esc(b.link.href) + '" class="text-white text-decoration-underline fw-bold" ' +
        'style="color:#fff !important;text-decoration:underline !important;font-weight:600 !important;" ' +
        'onclick="event.stopPropagation()">' + esc(b.link.text) + '</a>';
    }
    html += '</span>';
    return html;
  }

  // Kolom Deadline akutansi dari row.deadline. Port _rows.blade.php:291-413
  // (kartu umur / bypass / belum-diterima) — semua nilai sudah dihitung server.
  function fmtDeadline(cell) {
    const d = cell.getRow().getData().deadline;
    if (!d || d.variant === 'none') {
      return '<div class="no-deadline"><i class="fa-solid fa-clock"></i><span>Belum diterima</span></div>';
    }
    if (d.variant === 'sent_fallback') {
      return '<div class="deadline-card deadline-sent deadline-gray">' +
        '<div class="deadline-label" style="font-size:10px;color:#6b7280;font-weight:600;">' +
        '<i class="fa-solid fa-paper-plane"></i> Terkirim ke Pembayaran</div></div>';
    }
    // variant === 'card'
    let html = '<div class="deadline-card deadline-' + esc(d.type) + ' deadline-' + esc(d.color) + '">';
    html += '<div class="deadline-time"><i class="fa-solid fa-calendar"></i><span>' + esc(d.received_display) + '</span></div>';
    html += '<div class="deadline-indicator deadline-' + esc(d.color) + '"><i class="fa-solid ' + esc(d.indicator_icon) + '"></i>' +
      '<span class="status-text">' + esc(d.indicator_label) + '</span></div>';
    html += '<div class="deadline-age" style="font-size:10px;color:#6b7280;margin-top:4px;">' +
      '<i class="fa-solid fa-hourglass-half"></i><span>' + esc(d.age_text) + '</span></div>';
    if (d.footer) {
      if (d.footer.kind === 'paused') {
        html += '<div class="deadline-paused-label"><i class="fa-solid ' + esc(d.footer.icon) + '"></i> ' + esc(d.footer.text) + '</div>';
      } else {
        html += '<div class="deadline-label" style="font-size:8px;color:#6b7280;margin-top:4px;font-weight:600;">' +
          '<i class="fa-solid ' + esc(d.footer.icon) + '"></i> ' + esc(d.footer.text) + '</div>';
      }
    }
    html += '</div>';
    return html;
  }

  // Badge status pembayaran (Rollout 4) dari row.status_badge {state,class,text} —
  // BENTUK BEDA dari status_badge akutansi/perpajakan/verifikasi di atas
  // ({class,icon,text,link}): PaymentDocumentRow (Task 1) mengemit kontrak lebih
  // sederhana {state,class,text}, tanpa icon/link — karena itu formatter TERPISAH
  // (fmtAkutansiStatus tidak disentuh) alih-alih dicampur ke satu fungsi.
  function fmtPaymentPill(cell) {
    const b = cell.getRow().getData().status_badge;
    if (!b || !b.class) return '-';
    return '<span class="status-pill ' + esc(b.class) + '"><i class="fas fa-circle"></i> ' + esc(b.text) + '</span>';
  }

  // Registry formatter kolom tetap terparameter (CFG.extraColumns[].formatter).
  // 'date' & 'parafBadge' ditambah Rollout 3 (verifikasi, kolom Paraf tetap);
  // 'paymentPill' ditambah Rollout 4 (pembayaran, badge status pill) — ADDITIF,
  // kunci baru, tak mengubah kunci lama ('deadline'/'akutansiStatus'/dst).
  const EXTRA_FORMATTERS = {
    deadline: fmtDeadline,
    akutansiStatus: fmtAkutansiStatus,
    date: fmtDate,
    parafBadge: fmtParafBadge,
    paymentPill: fmtPaymentPill,
  };

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

  // Rollout 3 (verifikasi) — kolom Paraf TETAP (extraColumns), BUKAN kolom
  // kustomisasi. Port _rows.blade.php:225-233: badge kelas CSS `badge-paraf-done`
  // (BEDA tampilan dari fmtPemaraf di atas yang inline-style — dua formatter
  // terpisah utk field 'pemaraf' dgn mekanisme kolom berbeda: fmtPemaraf dipakai
  // bila 'pemaraf' jadi kolom kustomisasi (FORMATTERS registry), fmtParafBadge
  // dipakai extraColumns verifikasi. Read-only, tanpa editor.
  function fmtParafBadge(cell) {
    const value = cell.getValue();
    if (!value) return '-';
    return '<span class="badge-paraf-done"><i class="fa-solid fa-check-circle"></i> ' + esc(value) + '</span>';
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
    const cols = [{ formatter: 'rownum', width: 60, frozen: true, title: 'No' }];
    // Rollout 4 (pembayaran) — CFG.frozen = {left:[keys], right:[keys]} membekukan
    // kolom tambahan di kiri/kanan (modal freeze Kiri/Bebas/Kanan). Bila cfg.frozen
    // tak diset (role lain), kedua daftar kosong → perilaku lama (hanya No +
    // nomor_agenda beku) DIPERTAHANKAN byte-identik. Prasyarat: cfg.columns HARUS
    // sudah terurut kiri→bebas→kanan (FrozenColumnLayout::renderOrder) — posisi
    // frozen Tabulator ditentukan dari URUTAN kolom (kolom frozen di awal daftar
    // membeku ke kiri, di akhir daftar membeku ke kanan), bukan properti sisi
    // eksplisit.
    const fzLeft = (cfg.frozen && cfg.frozen.left) || [];
    const fzRight = (cfg.frozen && cfg.frozen.right) || [];

    // Satu entri cfg.columns → satu definisi kolom Tabulator. Diekstrak jadi
    // fungsi karena dipakai DUA kali sekarang: kelompok kiri/bebas (langsung),
    // dan kelompok beku-kanan (ditunda ke akhir — lihat bawah).
    function buildColumnDef(c) {
      const def = { title: c.label, field: c.key };
      // Fix QA Rollout 4 — c.formatter opsional (mis. status_pembayaran pakai
      // 'paymentPill') menimpa formatter kolom kustomisasi biasa dengan salah satu
      // EXTRA_FORMATTERS (badge/pill server-computed, sama seperti extraColumns).
      // Kolom begini murni tampilan → tanpa editor. Bila nama formatter tak dikenal
      // di registry, jatuh ke cabang biasa (getFormatter). Tanpa c.formatter (semua
      // role lain + kolom pembayaran lainnya) → cabang else, PERSIS perilaku lama.
      if (c.formatter && EXTRA_FORMATTERS[c.formatter]) {
        def.formatter = EXTRA_FORMATTERS[c.formatter];
        def.editable = false;
      } else {
        def.formatter = getFormatter(c.key);
        // Tugas 6: editor + gerbang editable per kolom data. Editor sesuai FIELD_TYPE;
        // gerbang membaca can_edit baris & daftar non-editable saat sel dibuka.
        def.editable = editableGate;
        const ed = editorFor(c.key);
        def.editor = ed.editor;
        if (ed.editorParams) def.editorParams = ed.editorParams;
      }
      if (c.key === 'nomor_agenda') {
        def.frozen = true; // identitas baris selalu terlihat saat digulir horizontal.
        // variableHeight dulu diset per-kolom di sini; sekarang datang dari
        // columnDefaults (konstruktor Tabulator) untuk SEMUA kolom, jadi mubazir.
      }
      if (fzLeft.indexOf(c.key) !== -1 || fzRight.indexOf(c.key) !== -1) { def.frozen = true; }
      return def;
    }

    // Kelompok beku-kanan DITUNDA, bukan langsung di-push: modul FrozenColumns
    // Tabulator menempelkan ke tepi kanan viewport kolom beku mana pun yang
    // posisinya SETELAH kolom non-beku dalam definisi tabel — bukan berdasar
    // properti sisi eksplisit. Sebelum perbaikan ini (review 2026-07-28), kolom
    // beku-kanan ikut terdorong ke posisi ini (masih di tengah cfg.columns),
    // padahal extraColumns/kolom "Pengurus Dokumen" menyusul SETELAHNYA — hasilnya
    // kolom beku-kanan mengambang menutupi Deadline/Status/Paraf/Pengurus Dokumen
    // saat tabel digulir horizontal di 4 dari 5 role (operator/akutansi/
    // perpajakan/verifikasi). Pembayaran kebetulan aman karena satu-satunya yang
    // menyetel extraColumns:[] DAN showHandler:false, jadi cfg.columns memang
    // sudah jadi kelompok terakhir di sana.
    const kelompokBekuKanan = [];
    (cfg.columns || []).forEach(function (c) {
      if (fzRight.indexOf(c.key) !== -1) {
        kelompokBekuKanan.push(c);
        return;
      }
      cols.push(buildColumnDef(c));
    });
    // Kolom tetap terparameter per-role (mis. akutansi: Deadline + Status). Tanpa
    // editor Tabulator; formatter merender objek server. Operator tak mengirim
    // extraColumns → tak ada kolom tambahan (paritas).
    (cfg.extraColumns || []).forEach(function (ec) {
      cols.push({
        title: ec.title,
        field: ec.field,
        formatter: EXTRA_FORMATTERS[ec.formatter] || fmtPlain,
        editable: false,
      });
    });
    // Kolom tetap "Pengurus Dokumen" (di luar kolom kustomisasi, selalu paling
    // kanan dari kolom TETAP — paritas _tableRowsAjax.blade.php:250-252). Tanpa
    // editor Tabulator; <select> menangani perubahan sendiri via listener change
    // terdelegasi. Rollout 4 (pembayaran) — CFG.showHandler=false menyembunyikan
    // kolom ini: pembayaran adalah ujung alur (tak ada "pengurus dokumen"
    // berikutnya untuk di-forward). Role lain tak set showHandler → `!== false`
    // bernilai true → kolom tetap ada (paritas byte-identik).
    if (cfg.showHandler !== false) {
      cols.push({ title: 'Pengurus Dokumen', field: 'handler', formatter: fmtHandler, editable: false });
    }
    // Kelompok beku-kanan ditambahkan PALING TERAKHIR (lihat catatan di atas) —
    // setelah extraColumns maupun "Pengurus Dokumen", supaya benar-benar jadi
    // kelompok terakhir dalam definisi tabel dan menempel tepi kanan viewport
    // dengan tepat, bukan menutupi kolom tetap tersebut.
    kelompokBekuKanan.forEach(function (c) {
      cols.push(buildColumnDef(c));
    });
    return cols;
  }

  // === CLAUDE.md §8 Tahap A: helper "sel aktif" ===
  //
  // Sel aktif & seleksi blok ditangani modul SelectRange bawaan Tabulator 6.3.1
  // (opsi `selectableRange` di konstruktor), BUKAN mesin tulis-tangan — partial
  // lama `_activeCellNav.blade.php` berbasis <td> dan tidak berlaku di sini
  // (baris Tabulator adalah <div>). Helper di bawah hanya MEMBACA range aktif
  // untuk dua keperluan: Enter→mulai edit, dan tombol toolbar "Hapus".

  // Range aktif TERAKHIR (objek RangeComponent bawaan modul SelectRange). Dipakai
  // activeCell() (sel pojok kiri-atas) DAN pasteMatrix() (dimensi blok untuk
  // aturan ubin ala Excel — Masalah 1, brief 2026-07-22).
  function activeRange() {
    let ranges = null;
    try { ranges = table.getRanges(); } catch (e) { return null; }
    if (!Array.isArray(ranges) || ranges.length === 0) return null;
    return ranges[ranges.length - 1] || null;
  }

  // Sel pertama (pojok kiri-atas) dari range terakhir yang aktif.
  // range.getCells() dapat mengembalikan array 2 dimensi [baris][kolom] atau datar
  // tergantung bentuk seleksi — tangani keduanya agar tak bergantung pada detail versi.
  function activeCell() {
    const range = activeRange();
    if (!range) return null;
    let cells = null;
    try { cells = range.getCells(); } catch (e) { return null; }
    if (!Array.isArray(cells) || cells.length === 0) return null;
    const head = cells[0];
    return (Array.isArray(head) ? head[0] : head) || null;
  }

  // Baris yang memuat sel aktif (dipakai tombol toolbar "Hapus").
  function activeRow() {
    const cell = activeCell();
    if (!cell) return null;
    try { return cell.getRow(); } catch (e) { return null; }
  }

  // Lebar kolom tarikan user disimpan di localStorage. 'fitDataStretch' selalu
  // memaksa kolom terakhir memenuhi layar sehingga menimpa lebar simpanan —
  // karena itu begitu user pernah menarik kolom, layout beralih ke 'fitData'
  // agar lebar user dihormati apa adanya (keputusan user 2026-07-22).
  // PENTING: penanda ini BUKAN kunci persistence bawaan Tabulator
  // ('tabulator-<persistenceID>-columns') — kunci itu ditulis Tabulator sendiri
  // pada render PERTAMA (sebelum user menyentuh apa pun), jadi tidak bisa
  // dipakai untuk mendeteksi "user sudah resize". Karena itu dipakai kunci
  // terpisah (lihat USER_RESIZED_FLAG_KEY di bawah) yang HANYA ditulis dari
  // handler 'columnResized' (lihat bawah) — event itu terbukti tidak terpicu
  // oleh resize terprogram (mis. column.setWidth()), hanya oleh drag user.
  //
  // BUG 2026-07-28 (dibuktikan di produksi, bukan dugaan): opsi
  // `persistence: { columns: ['width'] }` di bawah TIDAK berarti "simpan HANYA
  // lebar" — modul persistence Tabulator membangun ulang definisi kolom lewat
  // mergeDefinition(current, persisted), yang MENGITERASI ARRAY TERSIMPAN lalu
  // mendorong kolom yang cocok satu per satu. Hasilnya: URUTAN kolom hasil =
  // urutan yang tersimpan di localStorage, apa pun properti yang disebut di opsi
  // `columns` — persistence Tabulator ikut mengunci urutan (dan efektif,
  // susunan) kolom, bukan cuma lebar. Dibuktikan di produksi: menukar dua kolom
  // HANYA di localStorage lalu reload membuat header tertukar sekalipun
  // konfigurasi dari server tidak berubah — localStorage lama (dari sebelum
  // fitur Kolom Beku ada) selalu menang atas susunan baru dari server.
  //
  // Perbaikan: persistenceID diikat ke SIDIK JARI daftar kunci kolom + susunan
  // freeze (CFG.frozen) yang sedang dikirim server. Begitu salah satu berubah,
  // sidik jari berubah → kunci localStorage yang dipakai pun otomatis berbeda,
  // sehingga simpanan basi (susunan lama) tidak pernah dipakai untuk menimpa
  // susunan baru dari server. Hash djb2 sederhana (bukan kriptografis, cukup
  // stabil & pendek), dijadikan string base36.
  //
  // BUG LANJUTAN 2026-07-28 (ditemukan review, sebelum ada di produksi):
  // CFG.frozen WAJIB ikut di dalam hash, bukan cuma CFG.columns[].key.
  // FrozenColumnLayout::renderOrder() (PHP) mempertahankan urutan RELATIF di
  // dalam tiap kelompok kiri/bebas/kanan — jadi kalau kolom yang dibekukan ke
  // KANAN kebetulan SUDAH berada di ekor daftar 'selected' (kasus lazim: kolom
  // terakhir dibekukan ke kanan), array_merge($left,$middle,$right) di server
  // menghasilkan urutan BYTE-IDENTIK dengan sebelum dibekukan — CFG.columns[].key
  // tidak berubah sama sekali. Padahal buildColumns() di klien MENUNDA
  // kelompokBekuKanan ke paling akhir (lihat komentarnya), jadi urutan definisi
  // TABEL di klien tetap berubah. Tanpa CFG.frozen di hash, sidik jari tidak
  // ikut berubah, localStorage lama (urutan sebelum freeze) menang lewat
  // mergeDefinition, dan kolom beku-kanan mengambang lagi di tengah tabel —
  // persis bug yang commit 9668455 perbaiki, hidup lagi lewat jalur berbeda.
  // (Freeze KIRI tidak kena masalah yang sama: urutan definisi tabel untuk
  // kelompok kiri tidak ditunda seperti kelompok kanan, jadi tidak wajib masuk
  // hash — tapi tetap diikutkan di sini demi kejujuran & kesederhanaan: satu
  // aturan untuk kedua sisi, bukan pengecualian per sisi yang gampang jadi bug
  // baru kalau perilaku PHP berubah.)
  //
  // CAKUPAN HASH — JUJUR, bukan "apa pun yang memengaruhi susunan kolom":
  // hanya CFG.columns[].key + CFG.frozen (kiri/kanan) yang masuk. cfg.extraColumns,
  // cfg.showHandler, dan kolom "No" (rownum, selalu paling kiri) IKUT menentukan
  // urutan definisi tabel di buildColumns() tapi SENGAJA di luar hash — ketiganya
  // literal statis per VIEW Blade (mis. extraColumns akutansi vs verifikasi beda
  // isi, tapi tidak pernah berubah tanpa deploy ulang), bukan sesuatu yang bisa
  // berbeda antar-page-load untuk role yang sama seperti CFG.columns/CFG.frozen
  // (dua-duanya berasal dari preferensi user yang bisa berubah kapan saja lewat
  // modal Kustomisasi Kolom). Konsekuensinya: deploy yang mengubah salah satu
  // dari ketiganya (mis. menambah extraColumns baru) BISA mengulang kelas bug
  // yang sama seperti freeze-kanan di atas — localStorage lama tak otomatis
  // kadaluarsa. Ini bukan bug aktif hari ini, murni batas yang perlu diingat
  // kalau ketiganya disentuh di masa depan.
  function hashSusunanKolom(str) {
    let h = 5381;
    for (let i = 0; i < str.length; i++) {
      h = ((h * 33) ^ str.charCodeAt(i)) >>> 0;
    }
    return h.toString(36);
  }
  // Aman terhadap CFG.frozen yang tak ada / bukan objek / properti bukan array
  // (role lama, konfigurasi belum lengkap) — tidak pernah melempar, jatuh ke [].
  function daftarBekuAman(x) { return Array.isArray(x) ? x : []; }
  const bekuKiriUntukHash = daftarBekuAman(CFG.frozen && CFG.frozen.left);
  const bekuKananUntukHash = daftarBekuAman(CFG.frozen && CFG.frozen.right);
  const sidikJariKolom = hashSusunanKolom(
    (CFG.columns || []).map(function (c) { return c.key; }).join(',') +
    '|L:' + bekuKiriUntukHash.join(',') +
    '|R:' + bekuKananUntukHash.join(',')
  );
  // Penanda role dari CFG.mountId — nilainya beda per role (operatorTabulatorTable,
  // akutansiTabulatorTable, verifikasiTabulatorTable, dst.), jadi cukup dipakai
  // apa adanya sebagai bagian kunci. Cadangan 'documents' bila mountId kosong
  // (seharusnya tak pernah terjadi — CFG divalidasi di baris 24 — tapi
  // persistenceID tidak boleh jatuh ke string kosong kalau itu terjadi).
  const roleTag = CFG.mountId || 'documents';
  // Awalan tetap dipisah supaya bisa dipakai ulang untuk membersihkan kunci basi
  // di bawah (lihat blok pembersihan) tanpa mengulang literal string.
  const PERSIST_ID_PREFIX = 'agenda-documents-' + roleTag + '-';
  const PERSIST_ID = PERSIST_ID_PREFIX + sidikJariKolom;
  // TANPA sidik jari, sengaja: penanda ini menentukan fitData vs fitDataStretch
  // (lihat komentar di atas opsi `layout` di bawah). Kalau ikut disidikjari, ia
  // akan ter-reset setiap kali user mengubah susunan kolom (pilih/freeze kolom
  // lain), dan layout melompat balik ke 'fitDataStretch' — merusak keputusan
  // user 2026-07-22 bahwa lebar tarikan user dihormati apa adanya. Karena itu ia
  // HANYA per-role (via roleTag), murni riwayat "user pernah resize di role
  // ini", terlepas dari susunan kolom yang sedang aktif.
  const USER_RESIZED_FLAG_KEY = 'agenda-documents-' + roleTag + '-user-resized';
  let adaLebarTersimpan = false;
  // try/catch wajib: localStorage bisa melempar di mode privat / kuota penuh,
  // dan tabel TIDAK boleh gagal render karenanya.
  // Review akhir branch (2026-07-28): penanda saja TIDAK cukup. USER_RESIZED_FLAG_KEY
  // sengaja tanpa sidik jari (lihat komentar di atasnya), tapi kunci LEBAR
  // tersimpan Tabulator ('tabulator-' + PERSIST_ID + '-columns') justru IKUT
  // sidik jari. Begitu user mengubah kolom/beku, sidik jari berganti -> kunci
  // lebar lama ikut terhapus oleh blok pembersihan di bawah, sementara
  // penanda "pernah resize" tetap true -> layout: 'fitData' TANPA lebar
  // tersimpan -> tabel tak melebar penuh (ruang kosong di kanan), berulang
  // tiap kali user menyentuh kolom. Kunci lebar AKTIF (milik sidik jari
  // SEKARANG) wajib benar-benar ada, bukan cuma penandanya.
  try {
    const pernahResize = !!localStorage.getItem(USER_RESIZED_FLAG_KEY);
    adaLebarTersimpan = pernahResize && localStorage.getItem('tabulator-' + PERSIST_ID + '-columns') !== null;
  } catch (e) {}

  // Bersihkan kunci persistence BASI milik role ini (sidik jari lama, susunan
  // kolom yang sudah tak dipakai lagi) — tanpa ini localStorage tumbuh tanpa
  // batas karena tiap susunan kolom baru melahirkan kunci baru (kunci lama tak
  // pernah otomatis hilang). Hanya menyentuh kunci berpola PERSIS milik kita
  // sendiri untuk role ini ('tabulator-' + PERSIST_ID_PREFIX + ...) dan BUKAN
  // kunci yang baru saja kita pasang di atas — kunci lain (kunci role lain,
  // USER_RESIZED_FLAG_KEY, atau apa pun di luar pola ini) tidak pernah disentuh.
  try {
    const kunciBaruTabulator = 'tabulator-' + PERSIST_ID + '-columns';
    const awalanKunciKita = 'tabulator-' + PERSIST_ID_PREFIX;
    for (let i = localStorage.length - 1; i >= 0; i--) {
      const k = localStorage.key(i);
      if (k && k.indexOf(awalanKunciKita) === 0 && k !== kunciBaruTabulator) {
        localStorage.removeItem(k);
      }
    }
  } catch (e) {}

  const table = new Tabulator(mountEl(), {
    ajaxURL: CFG.dataUrl,
    // Tugas 7c: parameter filter aktif (search/year/status_filter) dibaca live dari
    // toolbar tiap request — cocok nama dgn buildOperatorQuery. Fungsi hoisted.
    ajaxParams: getFilterParams,
    // Progressive load (scroll): Tabulator membaca `last_page` & `data` dari respons
    // endpoint /documents/data ({last_page,total,data}) — nama field default cocok.
    progressiveLoad: 'scroll',
    progressiveLoadDelay: 200,
    paginationSize: 100,
    ajaxResponse: function (url, params, response) { return response; },
    layout: adaLebarTersimpan ? 'fitData' : 'fitDataStretch',
    // Opsi `columns: ['width']` di sini HANYA menentukan properti yang dibawa
    // Tabulator saat MEMBACA definisi tersimpan — bukan jaminan urutan tak ikut
    // terkunci (lihat penjelasan panjang di atas PERSIST_ID). Karena itu
    // persistenceID sendiri yang mengunci kesegaran: begitu CFG.columns[].key
    // ATAU CFG.frozen (kiri/kanan) berubah, sidik jari berubah dan kunci
    // localStorage ikut berganti — lebar hasil tarikan user tetap bertahan
    // SELAMA sidik jari (susunan kolom + freeze) tidak berubah.
    persistence: { columns: ['width'] },
    persistenceID: PERSIST_ID,
    height: '70vh',
    index: 'id',
    // Sort header dibuang atas keputusan user (spec 2026-07-22 §5): header bersih
    // tanpa segitiga, urutan tabel selalu default server/sesi. Backend masih
    // menerima sort/order untuk pemanggil lain — hanya klien yang berhenti mengirim.
    // variableHeight: kolom yang disempitkan membungkus teks (CSS white-space:
    // normal) alih-alih memotongnya — tanpa ini tinggi baris tetap kaku dan teks
    // yang membungkus terpotong vertikal.
    columnDefaults: { headerSort: false, resizable: true, variableHeight: true },
    columns: buildColumns(CFG),
    placeholder: 'Tidak ada dokumen.',

    // === CLAUDE.md §8 Tahap A — perilaku ala spreadsheet (BACA-SAJA) ===
    // Semua dari modul bawaan Tabulator 6.3.1; nol mesin tulis-tangan (§3 aturan 1).
    //
    // selectableRange memberi sekaligus: sel aktif, pindah sel dgn tombol panah
    // + auto-scroll mengikuti, klik-tunggal memindahkan sel aktif, dan seleksi blok
    // via drag / Shift+Klik / Shift+Panah.
    selectableRange: true,
    // selectableRangeColumns tetap false: data dimuat progresif (5.000+ baris), jadi
    // "pilih seluruh kolom" hanya akan menyeleksi baris yang KEBETULAN sudah termuat
    // saat itu, bukan seluruh kolom sungguhan — menyesatkan, bukan berguna.
    selectableRangeColumns: false,
    selectableRangeRows: false,
    // TETAP false meski Tahap B sudah jalan: pengosongan bawaan Tabulator hanya
    // mengubah TAMPILAN tanpa menyimpan ke server. Delete/Backspace ditangani
    // sendiri di blok Tahap B agar melewati jalur simpan yang sama.
    selectableRangeClearCells: false,
    // Klik-tunggal TIDAK lagi membuka editor — hanya memindahkan sel aktif.
    // Edit dimulai lewat dobel-klik, atau Enter (lihat handler keydown di bawah).
    editTriggerEvent: 'dblclick',
    // Ctrl+C saja. Ctrl+V (clipboardPasteAction) = Tahap B.
    clipboard: 'copy',
    clipboardCopyRowRange: 'range', // salin blok terseleksi, bukan seluruh baris.
    clipboardCopyStyled: false,     // teks polos → tempel bersih ke Excel/Sheets.
    // Tanpa ini Tabulator ikut menyalin baris judul kolom ke clipboard, sehingga
    // menempel balik ke tabel menulis nama kolom ke baris pertama dan menggeser
    // seluruh data turun satu baris (diverifikasi di produksi 2026-07-22).
    clipboardCopyConfig: { columnHeaders: false },

    // rowClick & rowDblClick sengaja TIDAK dipasang:
    //  - klik-tunggal kini milik selectableRange (pindah sel aktif). Panel "Detail
    //    Cepat" dimatikan di operator atas keputusan user 2026-07-21 (rasa Excel murni).
    //  - dobel-klik kini milik editTriggerEvent (mulai edit).
    // Modal Detail Dokumen dihapus atas permintaan user (2026-07-22); kemampuan
    // menghapus dokumen tetap ada lewat tombol toolbar Hapus (baris aktif).
  });

  // Handle instance untuk kode luar (mis. modal). Nama generik karena engine kini bersama;
  // grep membuktikan tak ada pemakai `window.operatorTable` di luar berkas ini.
  window.documentTable = table;

  // === §8 Tahap A: Enter pada sel aktif → mulai edit ===
  // Tabulator hanya menyediakan pemicu edit lewat mouse (editTriggerEvent), jadi
  // jalur keyboard dipasang di sini. Enter/Esc DI DALAM editor sudah ditangani
  // masing-masing editor (numberEditor/dateEditor + editor bawaan) — karena itu
  // handler ini mundur bila fokus sedang berada di elemen input.
  // Fase CAPTURE agar niat §8 (Enter = mulai edit) menang atas binding keyboard
  // bawaan Tabulator pada elemen yang sama.
  (function wireEnterToEdit() {
    const tableEl = mountEl();
    if (!tableEl) return;
    tableEl.addEventListener('keydown', function (e) {
      if (e.key !== 'Enter' || e.shiftKey || e.ctrlKey || e.altKey || e.metaKey) return;
      const t = e.target;
      if (t && t.closest && t.closest('input, textarea, select')) return; // editor terbuka.
      const cell = activeCell();
      if (!cell) return;
      e.preventDefault();
      // cell.edit() TANPA argumen → tetap menghormati gerbang `editable`
      // (editableGate: can_edit baris + kolom bukan NON_EDITABLE_FIELDS).
      try { cell.edit(); } catch (err) { /* sel tak dapat diedit — abaikan. */ }
    }, true);
  })();

  // === §8 Tahap A: tombol toolbar "Hapus" → hapus dokumen pada baris aktif ===
  // Menggantikan tombol Detail lama: modal Detail Dokumen dihapus atas permintaan
  // user (2026-07-22), dan tombol Hapus yang dulu ada di footer modal itu pindah
  // ke sini — pola sambungannya sama persis (baca activeRow(), teruskan datanya).
  (function wireDeleteButton() {
    const deleteBtn = document.getElementById('btnHapusBarisAktif');
    if (!deleteBtn) return;
    deleteBtn.addEventListener('click', function () {
      const row = activeRow();
      if (!row) {
        opToast('error', 'Pilih dulu sel pada baris yang ingin dihapus.');
        return;
      }
      if (typeof window.confirmDeleteDocument === 'function') {
        const data = row.getData();
        window.confirmDeleteDocument(data.id, data.nomor_agenda, data.nomor_spp, data.nilai_rupiah_formatted);
      }
    });
  })();

  // ==========================================================================
  // CLAUDE.md §8 Tahap B — tulisan massal: Delete, Ctrl+V, Ctrl+Z / Ctrl+Y
  // ==========================================================================
  //
  // Keputusan user 2026-07-21: TANPA dialog konfirmasi (rasa spreadsheet murni).
  // Karena tidak ada pengaman di depan, jaminan di belakang dibuat rapat:
  //
  //  1. SATU jalur tulis untuk semua operasi massal (applyBulkEdits) — kosongkan,
  //     tempel, batalkan, dan ulangi semuanya lewat sini.
  //  2. Tampilan TIDAK PERNAH mendahului server: sel baru berubah setelah PATCH
  //     dijawab sukses, jadi sel yang gagal tak pernah sempat menampilkan nilai palsu.
  //  3. Sel terkunci disaring di klien SEBELUM dikirim.
  //  4. Ctrl+Z ikut menyimpan ke server (membatalkan = menulis nilai lama), bukan
  //     sekadar mengembalikan tampilan.
  //
  // Semua tetap lewat PATCH inline-update yang sama dengan edit satu sel. Server
  // menegakkan ulang otorisasinya sendiri: DokumenController.php:1014 (status yang
  // boleh diedit operator) dan :1030 (whitelist field). Penyaringan klien di sini
  // hanya demi pengalaman pemakai, BUKAN batas keamanan.

  const MAX_PARALLEL_SAVES = 5;
  const MAX_HISTORY = 50;

  // Riwayat Ctrl+Z / Ctrl+Y. Tiap entri = satu operasi, berisi HANYA perubahan
  // yang benar-benar tersimpan di server. Baris dicatat sebagai `id` (bukan objek
  // RowComponent) agar tetap sah setelah filter/muat ulang mengganti objek baris.
  const undoStack = [];
  const redoStack = [];

  function resetHistory() {
    undoStack.length = 0;
    redoStack.length = 0;
  }
  // Catat satu operasi ke riwayat. Menekan tombol baru membatalkan jalur "ulangi"
  // yang lama — perilaku standar undo/redo.
  function pushHistory(changes) {
    if (!changes || changes.length === 0) return;
    undoStack.push(changes);
    if (undoStack.length > MAX_HISTORY) undoStack.shift();
    redoStack.length = 0;
  }
  function resolveRow(id) {
    try { return table.getRow(id) || null; } catch (e) { return null; }
  }

  // Jalankan daftar tugas dengan batas paralel — menahan ledakan 500 permintaan
  // serentak saat blok besar ditempel.
  function runWithLimit(tasks, limit) {
    return new Promise(function (resolve) {
      const results = [];
      let next = 0, active = 0, finished = 0;
      if (tasks.length === 0) { resolve(results); return; }
      function launch() {
        while (active < limit && next < tasks.length) {
          const i = next++;
          active++;
          tasks[i]()
            .then(function (r) { results[i] = r; }, function () { results[i] = { ok: false }; })
            .then(function () {
              active--; finished++;
              if (finished === tasks.length) resolve(results);
              else launch();
            });
        }
      }
      launch();
    });
  }

  // Simpan SATU sel pada jalur massal. Tampilan hanya disentuh setelah server
  // menjawab sukses — sel yang gagal tak pernah sempat menampilkan nilai palsu.
  function saveCell(row, field, value) {
    const fieldType = FIELD_TYPE[field] || 'text';
    const before = row.getData()[field];
    if (sameValue(field, fieldType, value, before)) {
      return Promise.resolve({ ok: true, skipped: true });
    }
    return patchCell(row.getData().id, field, value).then(function (res) {
      if (!res.ok) return { ok: false };
      // row.getData() dibaca ULANG di sini (bukan salinan sebelum fetch): dua sel
      // tanggal pada baris yang sama bisa tersimpan bersamaan, dan buildPostEditPatch
      // menyusun ulang peta `dates` dari data baris — salinan basi akan menjatuhkan
      // hasil sel yang lebih dulu selesai.
      row.update(buildPostEditPatch(field, fieldType, res.data, row.getData()));
      return { ok: true, before: before };
    });
  }

  function reportBulkResult(label, saved, failed, skipped) {
    const parts = [];
    if (saved > 0) parts.push(saved + ' sel tersimpan');
    if (failed > 0) parts.push(failed + ' gagal');
    if (skipped > 0) parts.push(skipped + ' dilewati (terkunci)');
    if (parts.length === 0) { opToast('success', label + ': tidak ada perubahan.'); return; }
    opToast(failed > 0 ? 'error' : 'success', label + ': ' + parts.join(', ') + '.');
  }

  // Jalur tulis massal tunggal. edits: [{row, field, value}].
  // options.record=false dipakai undo/redo agar tidak menumpuk riwayat baru.
  function applyBulkEdits(edits, options) {
    const opts = options || {};
    const label = opts.label || 'Perubahan';
    if (!edits || edits.length === 0) {
      // Diam bila memang tak ada apa-apa yang dipilih (mis. Delete tanpa seleksi);
      // hanya bersuara bila ada sel yang sengaja dilewati karena terkunci.
      if (opts.skipped) reportBulkResult(label, 0, 0, opts.skipped);
      return Promise.resolve([]);
    }
    const tasks = edits.map(function (e) {
      return function () { return saveCell(e.row, e.field, e.value); };
    });
    return runWithLimit(tasks, MAX_PARALLEL_SAVES).then(function (results) {
      const applied = [];
      let saved = 0, failed = 0;
      results.forEach(function (r, i) {
        if (!r || !r.ok) { failed++; return; }
        if (r.skipped) return;
        saved++;
        applied.push({
          id: edits[i].row.getData().id,
          field: edits[i].field,
          before: r.before,
          after: edits[i].value,
        });
      });
      if (opts.record !== false) pushHistory(applied);
      reportBulkResult(label, saved, failed, opts.skipped || 0);
      return applied;
    });
  }

  // Sel dari SELURUH range aktif yang benar-benar boleh ditulis.
  // Kolom nomor baris & "Pengurus Dokumen" diabaikan diam-diam (bukan sel data);
  // sel data yang terkunci dihitung sebagai `skipped` agar dilaporkan ke pemakai.
  function selectedWritableCells() {
    let ranges = [];
    try { ranges = table.getRanges() || []; } catch (e) { return { cells: [], skipped: 0 }; }
    const cells = [];
    let skipped = 0;
    ranges.forEach(function (range) {
      let raw = null;
      try { raw = range.getCells(); } catch (e) { return; }
      if (!Array.isArray(raw)) return;
      const flat = Array.isArray(raw[0]) ? raw.reduce(function (a, b) { return a.concat(b); }, []) : raw;
      flat.forEach(function (cell) {
        if (!cell) return;
        let field = null;
        try { field = cell.getColumn().getField(); } catch (e) { return; }
        if (!field || field === 'handler') return;
        if (NON_EDITABLE_FIELDS.indexOf(field) !== -1) { skipped++; return; }
        if (!cell.getRow().getData().can_edit) { skipped++; return; }
        cells.push(cell);
      });
    });
    return { cells: cells, skipped: skipped };
  }

  function editsFromCells(cells, valueFor) {
    return cells.map(function (cell) {
      return {
        row: cell.getRow(),
        field: cell.getColumn().getField(),
        value: valueFor(cell),
      };
    });
  }

  // Saat Delete/Backspace mengosongkan sel KRITERIA (kategori) atau SUB-KRITERIA
  // (jenis_dokumen), anak yang bergantung padanya harus IKUT kosong — paritas
  // dengan jalur dropdown (onCellEdited → resetDependentFields). Tanpa ini,
  // mengosongkan kriteria lewat Delete meninggalkan sub-kriteria & item yatim.
  //
  // Edit anak dihitung DI MUKA dari data baris saat ini (anak masih berisi), lalu
  // digabung ke SATU batch tulis bersama pengosongan langsung → satu laporan toast,
  // satu entri Ctrl+Z (satu undo mengembalikan induk + anak sekaligus).
  function cascadeEditsFor(clearedCells) {
    // "rowId|field" yang MEMANG sudah dikosongkan langsung oleh seleksi user —
    // jangan diantre ulang sebagai anak (hindari PATCH & entri riwayat ganda).
    const clearedKeys = {};
    clearedCells.forEach(function (cell) {
      let field = null, id = null;
      try { field = cell.getColumn().getField(); id = cell.getRow().getData().id; } catch (e) { return; }
      if (field != null && id != null) clearedKeys[id + '|' + field] = true;
    });
    const queued = {}; // dedup antar-induk pada baris yang sama (kategori & jenis_dokumen sama-sama menuju jenis_sub_pekerjaan).
    const edits = [];
    clearedCells.forEach(function (cell) {
      let field = null, row = null, data = null;
      try { field = cell.getColumn().getField(); row = cell.getRow(); data = row.getData(); } catch (e) { return; }
      const children = DEPENDENT_FIELDS[field];
      if (!children) return;
      children.forEach(function (child) {
        const key = data.id + '|' + child;
        if (clearedKeys[key] || queued[key]) return;      // sudah dikosongkan / sudah diantre.
        if (NON_EDITABLE_FIELDS.indexOf(child) !== -1) return;
        const v = data[child];
        if (v === null || v === undefined || String(v) === '') return; // sudah kosong, tak perlu ditulis.
        queued[key] = true;
        edits.push({ row: row, field: child, value: '' });
      });
    });
    return edits;
  }

  // Delete / Backspace → kosongkan sel terpilih tanpa masuk mode edit.
  function clearSelection() {
    const sel = selectedWritableCells();
    const directEdits = editsFromCells(sel.cells, function () { return ''; });
    const cascadeEdits = cascadeEditsFor(sel.cells);
    applyBulkEdits(
      directEdits.concat(cascadeEdits),
      { skipped: sel.skipped, label: 'Kosongkan' }
    );
  }

  // --- Tempel ala spreadsheet ---

  // TAB memisah kolom, baris baru memisah baris (format clipboard Excel/Sheets).
  function parseClipboardMatrix(text) {
    const lines = String(text).replace(/\r\n/g, '\n').replace(/\r/g, '\n').split('\n');
    while (lines.length > 1 && lines[lines.length - 1] === '') lines.pop(); // buang newline penutup.
    return lines.map(function (line) { return line.split('\t'); });
  }

  // Kolom ber-field dalam urutan tampil. "Pengurus Dokumen" IKUT dihitung agar
  // keselarasan kolom tetap benar saat hasil Ctrl+C dari tabel ini ditempel balik —
  // posisinya dikonsumsi, isinya tidak ditulis.
  function gridColumns() {
    let cols = [];
    try { cols = table.getColumns() || []; } catch (e) { return []; }
    return cols.filter(function (c) {
      try { return !!c.getField(); } catch (e) { return false; }
    });
  }

  function pasteMatrix(matrix) {
    const origin = activeCell();
    if (!origin) return;

    // Aturan Excel: satu nilai ditempel ke blok → isi seluruh blok dengan nilai itu.
    if (matrix.length === 1 && matrix[0].length === 1) {
      const sel = selectedWritableCells();
      if (sel.cells.length > 1) {
        const value = matrix[0][0];
        applyBulkEdits(
          editsFromCells(sel.cells, function () { return value; }),
          { skipped: sel.skipped, label: 'Tempel' }
        );
        return;
      }
    }

    let rows = [];
    try { rows = table.getRows() || []; } catch (e) { return; }
    const cols = gridColumns();
    const originId = origin.getRow().getData().id;
    const originField = origin.getColumn().getField();
    const r0 = rows.findIndex(function (r) { return r.getData().id === originId; });
    const c0 = cols.findIndex(function (c) { return c.getField() === originField; });
    if (r0 < 0 || c0 < 0) return;

    // Ubin ala Excel (Masalah 1, brief 2026-07-22) — port ATURANNYA dari
    // CASH_BANK/tableMasuk.blade.php bmPaste():876-887, BUKAN kodenya: di sana
    // batas blok datang dari objek {r1,c1,r2,c2} tulisan tangan; di sini dari
    // range SelectRange bawaan Tabulator (activeRange().getRows()/getColumns() —
    // sudah terurut pojok kiri-atas → kanan-bawah apa pun arah drag-nya, lihat
    // Range.getRows()/getColumns() di tabulator.min.js: slice(top,bottom+1) /
    // slice(left,right+1) dengan top/bottom/left/right = min/max start-end).
    //
    // Aturan: ulangi (tile) data hanya bila ukuran blok kelipatan BULAT ukuran
    // data yang ditempel — dihitung TERPISAH per baris & kolom. Kalau bukan
    // kelipatan, tempel data SEKALI dari pojok kiri-atas blok (sisa blok tidak
    // disentuh sama sekali — bukan dikosongkan). Bila data lebih besar dari blok
    // (atau tak ada blok sungguhan — hanya satu sel aktif, blok 1x1), rumus di
    // bawah otomatis luber melewati batas blok dan berperilaku seperti tempel-grid
    // biasa dari sel aktif (perilaku lama untuk kasus "tanpa blok" tetap sama).
    const range = activeRange();
    let blockRowCount = matrix.length;
    let blockColCount = (matrix[0] || []).length;
    if (range) {
      try {
        const rRows = range.getRows();
        if (Array.isArray(rRows) && rRows.length > 0) blockRowCount = rRows.length;
      } catch (e) {}
      try {
        const rCols = range.getColumns();
        if (Array.isArray(rCols) && rCols.length > 0) blockColCount = rCols.length;
      } catch (e) {}
    }
    const repRows = (blockRowCount % matrix.length === 0) ? blockRowCount : matrix.length;

    const edits = [];
    let skipped = 0;
    for (let i = 0; i < repRows; i++) {
      const row = rows[r0 + i];
      if (!row) break; // melewati baris terakhir yang termuat — tempel dipotong, baris tidak dibuat.
      const rowEditable = !!row.getData().can_edit;
      const srcRow = matrix[i % matrix.length]; // ulangi baris data bila di-tile.
      const repCols = (srcRow.length && blockColCount % srcRow.length === 0) ? blockColCount : srcRow.length;
      for (let j = 0; j < repCols; j++) {
        const col = cols[c0 + j];
        if (!col) break; // melewati kolom terakhir.
        const field = col.getField();
        if (field === 'handler' || NON_EDITABLE_FIELDS.indexOf(field) !== -1 || !rowEditable) {
          skipped++; // posisi tetap dikonsumsi supaya kolom berikutnya tidak bergeser.
          continue;
        }
        edits.push({ row: row, field: field, value: srcRow[j % srcRow.length] });
      }
    }
    applyBulkEdits(edits, { skipped: skipped, label: 'Tempel' });
  }

  // --- Batalkan / Ulangi (keduanya menulis ke server, bukan sekadar tampilan) ---

  function replayHistory(entry, pick, label, onDone) {
    const edits = entry.map(function (c) {
      const row = resolveRow(c.id);
      return row ? { row: row, field: c.field, value: pick(c) } : null;
    }).filter(Boolean);
    if (edits.length === 0) {
      opToast('error', label + ': baris terkait sudah tidak ada di tabel.');
      return;
    }
    applyBulkEdits(edits, { record: false, label: label }).then(function (applied) {
      if (applied && applied.length > 0) onDone();
    });
  }

  function undoLast() {
    const entry = undoStack.pop();
    if (!entry) { opToast('error', 'Tidak ada perubahan untuk dibatalkan.'); return; }
    replayHistory(entry, function (c) { return c.before; }, 'Batalkan', function () {
      redoStack.push(entry);
      if (redoStack.length > MAX_HISTORY) redoStack.shift();
    });
  }

  function redoLast() {
    const entry = redoStack.pop();
    if (!entry) { opToast('error', 'Tidak ada perubahan untuk diulang.'); return; }
    replayHistory(entry, function (c) { return c.after; }, 'Ulangi', function () {
      undoStack.push(entry);
      if (undoStack.length > MAX_HISTORY) undoStack.shift();
    });
  }

  // --- Pemasangan pintasan ---

  (function wireBulkKeys() {
    const tableEl = mountEl();
    if (!tableEl) return;
    tableEl.addEventListener('keydown', function (e) {
      const t = e.target;
      if (t && t.closest && t.closest('input, textarea, select')) return; // editor terbuka.
      const ctrl = e.ctrlKey || e.metaKey;
      if (ctrl && (e.key === 'z' || e.key === 'Z')) { e.preventDefault(); undoLast(); return; }
      if (ctrl && (e.key === 'y' || e.key === 'Y')) { e.preventDefault(); redoLast(); return; }
      if (!ctrl && (e.key === 'Delete' || e.key === 'Backspace')) { e.preventDefault(); clearSelection(); }
    }, true);
  })();

  (function wirePaste() {
    const container = mountEl();
    if (!container) return;
    document.addEventListener('paste', function (e) {
      const t = e.target;
      if (t && t.closest && t.closest('input, textarea, select')) return; // editor / kotak cari.
      // Hanya tangani bila fokus memang di dalam tabel DAN ada sel aktif.
      if (!container.contains(t) && !container.contains(document.activeElement)) return;
      if (!activeCell()) return;
      const cb = e.clipboardData || window.clipboardData;
      const text = cb ? cb.getData('text/plain') : '';
      if (!text) return;
      e.preventDefault();
      pasteMatrix(parseClipboardMatrix(text));
    });
  })();

  // === Tugas 7c: Filter toolbar → Tabulator via AJAX (tanpa reload halaman) ===
  // Baca nilai kontrol toolbar tiap request (fungsi ajaxParams) & saat berubah
  // panggil replaceData() (muat ulang dari halaman 1 dgn param terkini).
  //
  // PENTING (perbaikan review): nama field TIDAK di-hardcode di sini — dibaca
  // langsung dari atribut `name` tiap kontrol berbernama di dalam
  // .tabulator-toolbar. Dengan begitu SATU engine ini melayani semua role
  // sekalipun nama filternya beda per role: operator memakai
  // search/year/status_filter, akutansi memakai search/status/filter_dari —
  // keduanya bekerja karena nama diambil dari DOM toolbar milik role yang aktif,
  // bukan dari daftar tetap di sini (lihat CLAUDE.md §7 soal engine bersama).
  // Semua <input>/<select>/<textarea> berbernama di dalam toolbar — sumber
  // tunggal kebenaran untuk nama field filter (bukan daftar hardcode per-role).
  function toolbarFilterControls() {
    const nodeList = document.querySelectorAll(
      '.tabulator-toolbar input[name], .tabulator-toolbar select[name], .tabulator-toolbar textarea[name]'
    );
    return Array.prototype.filter.call(nodeList, function (el) { return !!el.name; });
  }
  function getFilterParams() {
    // Sort header sudah dimatikan lewat columnDefaults di konstruktor Tabulator,
    // jadi tak ada sorter aktif yang bisa dikirim — server memakai urutan default/sesi.
    const params = {};
    toolbarFilterControls().forEach(function (el) {
      params[el.name] = el.value; // nilai kosong tetap dikirim — server anggap itu "tanpa filter".
    });
    return params;
  }
  (function wireFilters() {
    // resetHistory: setelah filter berubah, isi tabel berganti — nilai "sebelum"
    // di riwayat Ctrl+Z tak lagi mencerminkan keadaan server, jadi jangan disimpan.
    function reload() { clearLoadError(); resetHistory(); table.replaceData(); }
    toolbarFilterControls().forEach(function (el) {
      el.removeAttribute('disabled');
      if (el.tagName === 'SELECT') {
        el.addEventListener('change', reload);
      } else {
        // input teks / textarea (mis. kotak cari): debounce 300ms, sama seperti sebelumnya.
        let deb = null;
        el.addEventListener('input', function () {
          clearTimeout(deb);
          deb = setTimeout(reload, 300); // debounce 300ms.
        });
      }
    });
  })();

  // === Task 2 (fitur export bersama, ADITIF): tombol Export toolbar ===
  // Muncul HANYA bila CFG.exportUrl diisi (dikabelkan per-role). Klik "Export"
  // membuka modal terpadu: pilih format (Excel/PDF) + pilih kolom (checkbox).
  // Menggantikan dropdown Excel/PDF pure-CSS lama (rapuh di layout jQuery+BS5).
  // Modal self-contained: CSS scoped .dxm-* disuntik sekali, toggle kelas .show,
  // TANPA JS dropdown/modal Bootstrap. Berlaku 5 role (engine bersama, aditif).
  (function wireExportButton() {
    if (!CFG.exportUrl) return;
    const toolbar = document.querySelector('.tabulator-toolbar');
    if (!toolbar) return;

    const PDF_SOFT_LIMIT = 9; // ambang catatan A4 (lunak, tak memblokir).

    // Kandidat kolom export = kolom data biasa (WYSIWYG kustomisasi kolom user).
    // Sama seperti visibleColumnFields() lama TAPI tanpa saringan isVisible() —
    // kolom tersembunyi tetap jadi opsi (tak tercentang default). Disingkirkan:
    // kolom nomor baris (field kosong), kolom aksi 'handler', dan kolom tetap
    // per-role (CFG.extraColumns — status_badge/deadline, nilainya objek server
    // computed) yang tak boleh masuk columns[] (di-cast jadi "Array" oleh
    // DocumentExporter::cellValue()). Mengembalikan {field, title, visible}.
    function exportColumnCandidates() {
      const extraFields = new Set((CFG.extraColumns || []).map(function (ec) { return ec.field; }));
      let cols = [];
      try { cols = table.getColumns(); } catch (e) { return []; }
      const out = [];
      cols.forEach(function (c) {
        let field = null;
        try { field = c.getField(); } catch (e) { field = null; }
        if (!field || field === 'handler' || extraFields.has(field)) return;
        let title = field;
        try { const def = c.getDefinition(); if (def && def.title) title = def.title; } catch (e) { /* fallback field */ }
        let visible = true;
        try { visible = c.isVisible(); } catch (e) { visible = true; }
        out.push({ field: field, title: title, visible: visible });
      });
      return out;
    }

    // URL = CFG.exportUrl + filter aktif (getFilterParams()) + columns[]=<field
    // terpilih dari modal> + format. Beda dgn versi lama: field datang dari
    // centang user, bukan otomatis dari kolom terlihat.
    function buildExportUrl(format, fields) {
      const params = new URLSearchParams();
      const filterParams = getFilterParams();
      Object.keys(filterParams).forEach(function (key) { params.append(key, filterParams[key]); });
      (fields || []).forEach(function (field) { params.append('columns[]', field); });
      params.append('format', format);
      return CFG.exportUrl + '?' + params.toString();
    }

    // CSS scoped, disuntik sekali. Prefiks .dxm-* agar identik di 5 role tanpa
    // bergantung pada .customization-modal (didefinisikan per-role di Blade).
    if (!document.getElementById('docExportModalStyle')) {
      const st = document.createElement('style');
      st.id = 'docExportModalStyle';
      st.textContent = [
        '.dxm-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:10000;padding:2rem;overflow-y:auto;}',
        '.dxm-overlay.show{display:flex;align-items:flex-start;justify-content:center;}',
        '.dxm-card{background:#fff;border-radius:16px;width:100%;max-width:560px;max-height:90vh;display:flex;flex-direction:column;box-shadow:0 25px 50px -12px rgba(0,0,0,.25);margin:1rem;}',
        '.dxm-head{padding:1.25rem 1.5rem;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;justify-content:space-between;}',
        '.dxm-head h3{margin:0;font-size:1.15rem;font-weight:600;color:#1f2937;display:flex;align-items:center;gap:.6rem;}',
        '.dxm-head h3 i{color:#083E40;}',
        '.dxm-close{background:none;border:none;font-size:1.35rem;color:#6b7280;cursor:pointer;line-height:1;padding:.25rem;}',
        '.dxm-close:hover{color:#1f2937;}',
        '.dxm-body{padding:1.25rem 1.5rem;overflow-y:auto;flex:1;}',
        '.dxm-formats{display:flex;gap:1.25rem;margin-bottom:1rem;}',
        '.dxm-formats label{display:flex;align-items:center;gap:.4rem;font-weight:600;color:#374151;cursor:pointer;}',
        '.dxm-bar{display:flex;gap:.5rem;margin-bottom:.6rem;align-items:center;}',
        '.dxm-count{margin-left:auto;font-size:.8rem;color:#6b7280;}',
        '.dxm-mini{border:1px solid #d1d5db;background:#fff;color:#374151;border-radius:8px;padding:.3rem .6rem;font-size:.8rem;cursor:pointer;}',
        '.dxm-mini:hover{background:#f4f7fb;}',
        '.dxm-list{border:1px solid #e5e7eb;border-radius:10px;max-height:40vh;overflow-y:auto;}',
        '.dxm-item{display:flex;align-items:center;gap:.6rem;padding:.5rem .75rem;border-bottom:1px solid #f1f5f9;}',
        '.dxm-item:last-child{border-bottom:none;}',
        '.dxm-item:hover{background:#f8fafc;}',
        '.dxm-item label{margin:0;cursor:pointer;flex:1;color:#374151;}',
        '.dxm-note{margin-top:.75rem;font-size:.82rem;color:#6b7280;background:#f8fafc;border:1px solid #e5e7eb;border-radius:8px;padding:.5rem .75rem;display:none;}',
        '.dxm-note.show{display:block;}',
        '.dxm-note.warn{color:#92400e;background:#fffbeb;border-color:#fde68a;}',
        '.dxm-foot{padding:1rem 1.5rem;border-top:1px solid #e5e7eb;display:flex;justify-content:flex-end;gap:.6rem;}',
        '.dxm-btn{border-radius:8px;padding:.5rem 1rem;font-weight:600;cursor:pointer;border:1px solid transparent;}',
        '.dxm-btn-cancel{background:#fff;border-color:#d1d5db;color:#5a6a7b;}',
        '.dxm-btn-cancel:hover{background:#f4f7fb;}',
        '.dxm-btn-go{background:#083E40;color:#fff;}',
        '.dxm-btn-go:hover{filter:brightness(1.1);}',
        '.dxm-btn-go:disabled{opacity:.5;cursor:not-allowed;}'
      ].join('');
      document.head.appendChild(st);
    }

    // Modal dibuat sekali, disuntik ke body; daftar kolom di-refresh tiap buka.
    const overlay = document.createElement('div');
    overlay.className = 'dxm-overlay';
    overlay.innerHTML =
      '<div class="dxm-card" role="dialog" aria-modal="true" aria-label="Export dokumen">' +
        '<div class="dxm-head">' +
          '<h3><i class="fa-solid fa-file-export"></i> Export Dokumen</h3>' +
          '<button type="button" class="dxm-close" aria-label="Tutup"><i class="fa-solid fa-times"></i></button>' +
        '</div>' +
        '<div class="dxm-body">' +
          '<div class="dxm-formats">' +
            '<label><input type="radio" name="dxmFormat" value="excel" checked> <i class="fa-solid fa-file-excel"></i> Excel</label>' +
            '<label><input type="radio" name="dxmFormat" value="pdf"> <i class="fa-solid fa-file-pdf"></i> PDF</label>' +
          '</div>' +
          '<div class="dxm-bar">' +
            '<button type="button" class="dxm-mini" data-dxm-all><i class="fa-solid fa-check-double me-1"></i>Pilih Semua</button>' +
            '<button type="button" class="dxm-mini" data-dxm-none><i class="fa-solid fa-times me-1"></i>Kosongkan</button>' +
            '<span class="dxm-count" data-dxm-count></span>' +
          '</div>' +
          '<div class="dxm-list" data-dxm-list></div>' +
          '<div class="dxm-note" data-dxm-note></div>' +
        '</div>' +
        '<div class="dxm-foot">' +
          '<button type="button" class="dxm-btn dxm-btn-cancel" data-dxm-cancel>Batal</button>' +
          '<button type="button" class="dxm-btn dxm-btn-go" data-dxm-go><i class="fa-solid fa-download me-1"></i>Export</button>' +
        '</div>' +
      '</div>';
    document.body.appendChild(overlay);

    const listEl = overlay.querySelector('[data-dxm-list]');
    const noteEl = overlay.querySelector('[data-dxm-note]');
    const countEl = overlay.querySelector('[data-dxm-count]');
    const goBtn = overlay.querySelector('[data-dxm-go]');

    function selectedFormat() {
      const r = overlay.querySelector('input[name="dxmFormat"]:checked');
      return r ? r.value : 'excel';
    }
    function checkedFields() {
      return Array.prototype.slice.call(listEl.querySelectorAll('input[type="checkbox"]:checked'))
        .map(function (cb) { return cb.value; });
    }
    // Perbarui: tombol Export nonaktif bila 0 kolom; catatan A4 hanya untuk PDF.
    function refreshState() {
      const n = checkedFields().length;
      goBtn.disabled = n === 0;
      countEl.textContent = n + ' kolom dipilih';
      if (selectedFormat() === 'pdf') {
        noteEl.classList.add('show');
        if (n > PDF_SOFT_LIMIT) {
          noteEl.classList.add('warn');
          noteEl.innerHTML = '<i class="fa-solid fa-triangle-exclamation me-1"></i>' + n + ' kolom dipilih. Kertas A4 landscape mungkin tidak muat — disarankan &plusmn; &le; ' + PDF_SOFT_LIMIT + ' kolom agar terbaca.';
        } else {
          noteEl.classList.remove('warn');
          noteEl.innerHTML = '<i class="fa-solid fa-circle-info me-1"></i>Kertas A4 landscape — pilih secukupnya (&plusmn; &le; ' + PDF_SOFT_LIMIT + ' kolom) agar muat &amp; terbaca.';
        }
      } else {
        noteEl.classList.remove('show');
        noteEl.classList.remove('warn');
      }
    }
    // Bangun daftar checkbox via DOM props (bukan innerHTML) — aman dari
    // karakter khusus pada field/label.
    function renderList() {
      const cands = exportColumnCandidates();
      listEl.innerHTML = '';
      if (!cands.length) {
        const empty = document.createElement('div');
        empty.className = 'dxm-item';
        empty.style.cssText = 'color:#6b7280;';
        empty.textContent = 'Tidak ada kolom untuk diexport.';
        listEl.appendChild(empty);
        return;
      }
      cands.forEach(function (c, i) {
        const id = 'dxmCol' + i;
        const row = document.createElement('div');
        row.className = 'dxm-item';
        const cb = document.createElement('input');
        cb.type = 'checkbox';
        cb.id = id;
        cb.value = c.field;
        cb.checked = c.visible;
        const lab = document.createElement('label');
        lab.setAttribute('for', id);
        lab.textContent = c.title;
        row.appendChild(cb);
        row.appendChild(lab);
        listEl.appendChild(row);
      });
    }
    function openModal() {
      renderList();
      const ex = overlay.querySelector('input[name="dxmFormat"][value="excel"]');
      if (ex) ex.checked = true; // default excel tiap buka.
      refreshState();
      overlay.classList.add('show');
    }
    function closeModal() { overlay.classList.remove('show'); }

    overlay.addEventListener('change', function (e) {
      if (e.target && (e.target.name === 'dxmFormat' || e.target.type === 'checkbox')) refreshState();
    });
    overlay.querySelector('[data-dxm-all]').addEventListener('click', function () {
      listEl.querySelectorAll('input[type="checkbox"]').forEach(function (cb) { cb.checked = true; });
      refreshState();
    });
    overlay.querySelector('[data-dxm-none]').addEventListener('click', function () {
      listEl.querySelectorAll('input[type="checkbox"]').forEach(function (cb) { cb.checked = false; });
      refreshState();
    });
    overlay.querySelector('[data-dxm-cancel]').addEventListener('click', closeModal);
    overlay.querySelector('.dxm-close').addEventListener('click', closeModal);
    overlay.addEventListener('click', function (e) { if (e.target === overlay) closeModal(); });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && overlay.classList.contains('show')) closeModal();
    });
    goBtn.addEventListener('click', function () {
      const fields = checkedFields();
      if (!fields.length) return; // tombol seharusnya sudah disabled.
      closeModal();
      window.location = buildExportUrl(selectedFormat(), fields);
    });

    // Tombol Export di toolbar (satu titik masuk, buka modal).
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'btn btn-outline-success';
    btn.innerHTML = '<i class="fa-solid fa-file-export me-1"></i> Export';
    btn.addEventListener('click', openModal);
    toolbar.appendChild(btn);
  })();

  // === Tugas 7f: Penanganan gagal muat data + tombol "Coba lagi" ===
  function clearLoadError() {
    const ex = document.getElementById('opLoadError');
    if (ex) ex.remove();
  }
  function showLoadError() {
    clearLoadError();
    const container = document.getElementById('documentTableContainer');
    if (!container) return;
    const box = document.createElement('div');
    box.id = 'opLoadError';
    box.style.cssText = 'padding:14px 18px;margin-bottom:12px;background:#fff3cd;' +
      'border:1px solid #ffc107;border-radius:10px;display:flex;align-items:center;' +
      'justify-content:space-between;gap:12px;';
    const msg = document.createElement('span');
    msg.style.cssText = 'color:#856404;font-weight:600;';
    msg.innerHTML = '<i class="fa-solid fa-triangle-exclamation me-2"></i>Gagal memuat data.';
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'btn btn-sm btn-warning';
    btn.innerHTML = '<i class="fa-solid fa-rotate-right me-1"></i>Coba lagi';
    btn.addEventListener('click', function () {
      clearLoadError();
      table.setData(CFG.dataUrl, getFilterParams());
    });
    box.appendChild(msg);
    box.appendChild(btn);
    container.insertBefore(box, container.firstChild);
  }
  // Commit inline-update saat sel selesai diedit (Tugas 6).
  //
  // WAJIB lewat table.on(), BUKAN opsi konstruktor `cellEdited: onCellEdited`.
  // Tabulator 6 menghapus callback gaya-opsi dan menggantinya dengan sistem event:
  // opsi `cellEdited` diabaikan DIAM-DIAM — tanpa error, tanpa peringatan — sehingga
  // sejak b1ef9aa (2026-07-21) setiap edit lewat editor tampak berhasil di layar lalu
  // hilang saat halaman dimuat ulang. Dibuktikan di browser 2026-07-22 dengan satu
  // tabel yang memasang KEDUA cara sekaligus: opsi 0 panggilan, table.on() 1 panggilan.
  // Jalur tempel/Delete/undo tidak terpengaruh karena memakai saveCell(), bukan ini.
  table.on('cellEdited', onCellEdited);

  // Popup mungil "✓ Disalin" di dekat sel aktif setelah Ctrl+C — meniru CASH_BANK.
  // Tabulator menembakkan clipboardCopied SETELAH penyalinan bawaan berhasil
  // (clipboard: 'copy'), jadi cukup menumpang event itu; tak perlu menyusun TSV
  // sendiri seperti CASH_BANK yang tabelnya DataTables tanpa clipboard bawaan.
  // Elemen popup dibuat sekali lalu dipakai ulang (posisi: fixed, di luar wadah
  // tabel agar tidak ikut terpotong overflow), gayanya di public/css/tabulator-agenda.css.
  let copyPopEl = null;
  let copyPopTimer = null;
  function copyPop() {
    if (copyPopEl) return copyPopEl;
    copyPopEl = document.createElement('div');
    copyPopEl.className = 'op-copy-pop';
    copyPopEl.setAttribute('aria-hidden', 'true');
    copyPopEl.innerHTML = '<b>✓</b> Disalin';
    document.body.appendChild(copyPopEl);
    return copyPopEl;
  }
  function showCopiedPopup() {
    const pop = copyPop();
    pop.style.display = 'block';
    // Ukur SETELAH tampil agar getBoundingClientRect popup valid untuk clamp tepi.
    const pr = pop.getBoundingClientRect();
    let x = 20, y = 20;
    const cell = activeCell();
    let anchor = null;
    try { anchor = cell && cell.getElement(); } catch (e) { anchor = null; }
    if (anchor) {
      const rc = anchor.getBoundingClientRect();
      x = rc.right + 8;
      y = rc.bottom + 8;
    }
    pop.style.left = Math.min(Math.max(8, x), window.innerWidth - pr.width - 8) + 'px';
    pop.style.top = Math.min(Math.max(8, y), window.innerHeight - pr.height - 8) + 'px';
    if (copyPopTimer) clearTimeout(copyPopTimer);
    copyPopTimer = setTimeout(function () { pop.style.display = 'none'; }, 900);
  }
  table.on('clipboardCopied', showCopiedPopup);

  // Koreksi gulir horizontal saat sel aktif berpindah.
  //
  // Modul SelectRange menggulir sel aktif "masuk viewport", tetapi tidak
  // memperhitungkan kolom beku kiri (No + Nomor Agenda) yang MENIMPA tepi kiri
  // viewport itu. Akibatnya sel bisa dianggap terlihat padahal tertimbun di
  // baliknya. Diverifikasi di produksi 2026-07-22: setelah bergerak ke kanan
  // sampai Uraian lalu kembali ke kiri, sel aktif 'bulan' berada di x261-316
  // sementara kolom beku berakhir di x420 — kolom Bulan & Tahun hilang dari layar.
  //
  // Hanya berjalan saat range berubah (pindah sel), bukan saat user menggulir
  // sendiri, sehingga tidak melawan gulir manual.
  (function wireFrozenScrollFix() {
    const JARAK_AMAN = 8; // sisa ruang agar sel tidak menempel persis di batas kolom beku.

    // Wadah gulir dicari ULANG tiap kali, JANGAN di-cache: Tabulator mengganti
    // elemen .tabulator-tableholder setelah init/redraw, sehingga referensi yang
    // disimpan sekali di awal menjadi basi dan menyetel scrollLeft padanya tidak
    // berefek apa pun (terbukti: logika koreksi jalan saat dipanggil manual dgn
    // query segar, tapi diam saja lewat referensi yang di-cache).
    function wadahGulir() {
      const host = mountEl();
      return host ? host.querySelector('.tabulator-tableholder') : null;
    }

    // Tepi kanan blok kolom beku kiri, dibaca dari DOM header (lebar bisa berubah
    // karena tarikan lebar kolom user, jadi jangan di-cache).
    function tepiKananKolomBeku() {
      const host = mountEl();
      const beku = host ? host.querySelectorAll(
        '.tabulator-header .tabulator-col.tabulator-frozen.tabulator-frozen-left'
      ) : [];
      let tepi = 0;
      beku.forEach(function (el) { tepi = Math.max(tepi, el.getBoundingClientRect().right); });
      return tepi;
    }

    table.on('rangeChanged', function () {
      // rAF: jalankan SETELAH Tabulator selesai menggulir sendiri, kalau tidak
      // koreksi kita langsung ditimpa.
      requestAnimationFrame(function () {
        const holder = wadahGulir();
        if (!holder) return;
        const cell = activeCell();
        if (!cell) return;
        let el = null;
        try { el = cell.getElement(); } catch (e) { return; }
        if (!el) return;
        // Sel yang beku tidak perlu dikoreksi — ia memang selalu terlihat.
        if (el.classList.contains('tabulator-frozen')) return;

        const tepi = tepiKananKolomBeku();
        if (!tepi) return;
        const kotak = el.getBoundingClientRect();
        const tertimbun = tepi - kotak.left;
        if (tertimbun > 0) holder.scrollLeft -= (tertimbun + JARAK_AMAN);
      });
    });

    // Tepi KIRI blok kolom beku KANAN, dibaca dari DOM header (kebalikan dari
    // tepiKananKolomBeku di atas: kolom beku-kanan ditempel Tabulator ke tepi
    // KANAN viewport, jadi tepi KIRI blok itulah yang bisa menimbun sel aktif
    // saat digulir ke kanan). Kosong (frozen.right = [], mayoritas role) -> 0,
    // dianggap nonaktif.
    function tepiKiriKolomBekuKanan() {
      const host = mountEl();
      const beku = host ? host.querySelectorAll(
        '.tabulator-header .tabulator-col.tabulator-frozen.tabulator-frozen-right'
      ) : [];
      let tepi = 0;
      beku.forEach(function (el) {
        const kiri = el.getBoundingClientRect().left;
        tepi = tepi === 0 ? kiri : Math.min(tepi, kiri);
      });
      return tepi;
    }

    // Diverifikasi di produksi 2026-07-28 (pembayaran, frozen.right =
    // ['status_pembayaran']): setelah 40x panah kanan, sel aktif berada di
    // x880-971 sementara tepi kiri blok beku-kanan di x847 — sel aktif
    // tertimbun. Listener TERPISAH (bukan menyisipkan ke blok kiri di atas)
    // supaya perilaku sisi kiri byte-identik & tak tersentuh; kedua listener
    // 'rangeChanged' berjalan independen (Tabulator eventBus mendukung banyak
    // subscriber per event).
    table.on('rangeChanged', function () {
      requestAnimationFrame(function () {
        const holder = wadahGulir();
        if (!holder) return;
        const cell = activeCell();
        if (!cell) return;
        let el = null;
        try { el = cell.getElement(); } catch (e) { return; }
        if (!el) return;
        if (el.classList.contains('tabulator-frozen')) return;

        const tepiKanan = tepiKiriKolomBekuKanan();
        if (!tepiKanan) return;
        const kotak = el.getBoundingClientRect();
        const tertimbun = kotak.right - tepiKanan;
        if (tertimbun <= 0) return;

        // Sel raksasa (lebih lebar dari celah bebas antara blok beku-kiri &
        // blok beku-kanan): koreksi penuh di atas bisa mendorong tepi KIRI sel
        // sampai masuk ke bawah blok beku-kiri (kebalikan dari bug yang
        // diperbaiki listener ini). Untuk teks kiri-ke-kanan tepi kiri jauh
        // lebih berguna — itu awal isi sel — jadi koreksi di-clamp supaya
        // tepi kiri sel tidak pernah melewati tepi kanan blok beku-kiri
        // (+ JARAK_AMAN yang sama). Diverifikasi 2026-07-28 (pembayaran,
        // frozen.right = ['status_pembayaran']): kolom 'Uraian SPP' 1240px
        // vs celah bebas ~395px — tanpa clamp ini tepi kiri kolom tertimbun.
        const tepiKiri = tepiKananKolomBeku();
        let koreksi = tertimbun + JARAK_AMAN;
        if (tepiKiri) koreksi = Math.min(koreksi, kotak.left - (tepiKiri + JARAK_AMAN));
        if (koreksi <= 0) return;
        holder.scrollLeft += koreksi;
      });
    });
  })();

  table.on('dataLoadError', function () { showLoadError(); });
  table.on('dataLoaded', function () { clearLoadError(); });

  // Tandai bahwa user SUNGGUH menarik lebar kolom (bukan sekadar memuat
  // halaman) — dipakai kunjungan berikutnya untuk memilih layout 'fitData'
  // alih-alih 'fitDataStretch'. columnResized tidak terpicu oleh resize
  // terprogram, jadi penanda ini murni aksi user.
  table.on('columnResized', function () {
    try { localStorage.setItem(USER_RESIZED_FLAG_KEY, '1'); } catch (e) {}
  });

  // === Tugas 7e: Modal alasan penolakan global (dipicu klik badge status). ===
  // Baca data baris via index id; isi field modal (Bootstrap) lalu tampilkan.
  window.openOperatorRejectReason = function (id) {
    let rowObj = null;
    try { rowObj = table.getRow(id); } catch (e) { rowObj = null; }
    if (!rowObj) return;
    const d = rowObj.getData();
    const reason = (d.reject_reason !== null && d.reject_reason !== undefined && String(d.reject_reason).trim() !== '')
      ? d.reject_reason : 'Tidak ada alasan yang dicatat';
    const by = (d.rejected_by !== null && d.rejected_by !== undefined && String(d.rejected_by).trim() !== '') ? d.rejected_by : '-';
    const at = (d.rejected_at !== null && d.rejected_at !== undefined && String(d.rejected_at).trim() !== '') ? d.rejected_at : '-';
    const elText = document.getElementById('reject-reason-text');
    const elBy = document.getElementById('reject-reason-by');
    const elAt = document.getElementById('reject-reason-at');
    if (elText) elText.textContent = reason; // textContent = auto-escape.
    if (elBy) elBy.textContent = by;
    if (elAt) elAt.textContent = at;
    const modalEl = document.getElementById('rejectReasonModal');
    if (modalEl && typeof bootstrap !== 'undefined') {
      new bootstrap.Modal(modalEl).show();
    }
  };

  // === Tambah Baris (inline create) ===
  // Aktifkan tombol toolbar yang di-skeleton ditandai `disabled`, lalu wire klik.
  // Alur robust (sesuai brief): minta nomor_agenda → POST inline-create → sisipkan
  // baris server nyata (dgn id + data lengkap) di puncak; tampilkan pesan 422 duplikat.
  const addBtn = document.getElementById('btnTambahBarisTabulator');
  if (addBtn) {
    addBtn.removeAttribute('disabled');
    addBtn.addEventListener('click', function () {
      const nomor = (window.prompt('Masukkan Nomor Agenda untuk baris baru:') || '').trim();
      if (!nomor) return; // wajib nomor_agenda.
      fetch(CFG.inlineCreateUrl, {
        method: 'POST',
        headers: jsonHeaders(),
        body: JSON.stringify({ nomor_agenda: nomor }),
      })
        .then(async function (r) {
          const data = await r.json().catch(function () { return {}; });
          return { ok: r.ok, data: data };
        })
        .then(function (res) {
          const data = res.data || {};
          if (!res.ok || data.success === false || !data.row) {
            // 422 duplikat/validasi: tampilkan pesan server.
            const msg = (data.errors && data.errors.nomor_agenda && data.errors.nomor_agenda[0])
              || data.message || 'Gagal menambah baris.';
            opToast('error', msg);
            return;
          }
          table.addRow(data.row, true).then(function (newRow) {
            try { newRow.scrollTo(); } catch (e) {}
            opToast('success', 'Baris baru ditambahkan.');
          }).catch(function () {});
        })
        .catch(function () { opToast('error', 'Koneksi gagal. Coba lagi.'); });
    });
  }

  // === Dropdown Pengurus Dokumen (PATCH handler) ===
  // Listener change TERDELEGASI (capture) untuk semua <select.op-handler-select>
  // yang dirender formatter — tahan re-render Tabulator. Semantik payload mengikuti
  // document-handler-select.blade.php:183-237 (kunci `target_handler`).
  document.addEventListener('change', function (e) {
    const sel = (e.target && e.target.closest) ? e.target.closest('.op-handler-select') : null;
    if (!sel) return;
    e.stopPropagation();
    // Guard tambahan: select disabled tak seharusnya memicu change, tapi jaga
    // agar aman bila DOM dimanipulasi di luar jalur normal (paritas gate server).
    if (sel.disabled) return;
    const id = sel.getAttribute('data-document-id');
    const prev = sel.getAttribute('data-prev') || '';
    const next = sel.value;
    if (!next || next === prev) { sel.value = prev; return; }
    sel.disabled = true;
    sel.classList.add('is-saving');
    fetch(handlerUrl(id), {
      method: 'PATCH',
      headers: jsonHeaders(),
      body: JSON.stringify({ target_handler: next }),
    })
      .then(async function (r) {
        const data = await r.json().catch(function () { return {}; });
        return { ok: r.ok, data: data };
      })
      .then(function (res) {
        const data = res.data || {};
        if (!res.ok || data.success !== true) {
          sel.value = prev; // balikkan pilihan.
          sel.disabled = false;
          sel.classList.remove('is-saving');
          opToast('error', data.message || 'Gagal mengubah pengurus dokumen.');
          return;
        }
        opToast('success', data.message || 'Pengurus dokumen berhasil diubah.');
        // Muat ulang data via ajax agar dokumen yang berpindah pengurus tercermin.
        // Riwayat Ctrl+Z dibuang: hak edit baris bisa berubah setelah ganti pengurus.
        resetHistory();
        table.replaceData();
      })
      .catch(function () {
        sel.value = prev;
        sel.disabled = false;
        sel.classList.remove('is-saving');
        opToast('error', 'Koneksi gagal. Coba lagi.');
      });
  }, true);
})();

/*
 * operator-tabulator.js — Init Tabulator untuk Daftar Dokumen Operator (pilot).
 *
 * Tugas 5: formatter paritas PENUH. Setiap kolom dirender byte-faithful terhadap
 * tabel lama `_tableRowsAjax.blade.php` (badge status, rupiah, tanggal, link,
 * pemaraf, nomor_agenda dua baris). Editor & interaksi menyusul di Tugas 6-7.
 * Membaca konfigurasi dari window.OPERATOR_TABULATOR_CONFIG yang di-inject oleh
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

  // Petakan field → definisi editor Tabulator berdasar FIELD_TYPE.
  function editorFor(field) {
    const type = FIELD_TYPE[field] || 'text';
    if (type === 'textarea') return { editor: 'textarea' };
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
      // §8: "setiap perubahan dapat dibatalkan dengan Ctrl+Z" — edit satu sel lewat
      // editor ikut masuk riwayat, bukan hanya operasi massal.
      pushHistory([{ id: row.getData().id, field: field, before: oldValue, after: newValue }]);
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
    const cols = [{ formatter: 'rownum', width: 60, frozen: true, title: 'No' }];
    (cfg.columns || []).forEach(function (c) {
      const def = { title: c.label, field: c.key, formatter: getFormatter(c.key) };
      if (c.key === 'nomor_agenda') {
        def.frozen = true; // identitas baris selalu terlihat saat digulir horizontal.
        // variableHeight dulu diset per-kolom di sini; sekarang datang dari
        // columnDefaults (konstruktor Tabulator) untuk SEMUA kolom, jadi mubazir.
      }
      // Tugas 6: editor + gerbang editable per kolom data. Editor sesuai FIELD_TYPE;
      // gerbang membaca can_edit baris & daftar non-editable saat sel dibuka.
      def.editable = editableGate;
      const ed = editorFor(c.key);
      def.editor = ed.editor;
      if (ed.editorParams) def.editorParams = ed.editorParams;
      cols.push(def);
    });
    // Kolom tetap "Pengurus Dokumen" (di luar kolom kustomisasi, selalu paling
    // kanan — paritas _tableRowsAjax.blade.php:250-252). Tanpa editor Tabulator;
    // <select> menangani perubahan sendiri via listener change terdelegasi.
    cols.push({ title: 'Pengurus Dokumen', field: 'handler', formatter: fmtHandler, editable: false });
    return cols;
  }

  // === CLAUDE.md §8 Tahap A: helper "sel aktif" ===
  //
  // Sel aktif & seleksi blok ditangani modul SelectRange bawaan Tabulator 6.3.1
  // (opsi `selectableRange` di konstruktor), BUKAN mesin tulis-tangan — partial
  // lama `_activeCellNav.blade.php` berbasis <td> dan tidak berlaku di sini
  // (baris Tabulator adalah <div>). Helper di bawah hanya MEMBACA range aktif
  // untuk dua keperluan: Enter→mulai edit, dan tombol toolbar "Detail".

  // Sel pertama (pojok kiri-atas) dari range terakhir yang aktif.
  // range.getCells() dapat mengembalikan array 2 dimensi [baris][kolom] atau datar
  // tergantung bentuk seleksi — tangani keduanya agar tak bergantung pada detail versi.
  function activeCell() {
    let ranges = null;
    try { ranges = table.getRanges(); } catch (e) { return null; }
    if (!Array.isArray(ranges) || ranges.length === 0) return null;
    let cells = null;
    try { cells = ranges[ranges.length - 1].getCells(); } catch (e) { return null; }
    if (!Array.isArray(cells) || cells.length === 0) return null;
    const head = cells[0];
    return (Array.isArray(head) ? head[0] : head) || null;
  }

  // Baris yang memuat sel aktif (dipakai tombol "Detail").
  function activeRow() {
    const cell = activeCell();
    if (!cell) return null;
    try { return cell.getRow(); } catch (e) { return null; }
  }

  // Lebar kolom tarikan user disimpan di localStorage. 'fitDataStretch' selalu
  // memaksa kolom terakhir memenuhi layar sehingga menimpa lebar simpanan —
  // karena itu begitu ada simpanan, layout beralih ke 'fitData' agar lebar user
  // dihormati apa adanya (keputusan user 2026-07-22). Tabulator menyimpan di
  // kunci 'tabulator-<persistenceID>-columns'.
  const PERSIST_ID = 'agenda-operator-documents';
  let adaLebarTersimpan = false;
  // try/catch wajib: localStorage bisa melempar di mode privat / kuota penuh,
  // dan tabel TIDAK boleh gagal render karenanya.
  try { adaLebarTersimpan = !!localStorage.getItem('tabulator-' + PERSIST_ID + '-columns'); } catch (e) {}

  const table = new Tabulator('#operatorTabulatorTable', {
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
    // Simpan HANYA lebar kolom (bukan urutan/visibilitas) ke localStorage, supaya
    // penyempitan/pelebaran kolom oleh user bertahan lintas kunjungan.
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
    cellEdited: onCellEdited, // Tugas 6: commit inline-update saat sel diedit.

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
    // Modal Detail Dokumen pindah ke tombol toolbar #btnDetailBarisAktif (baris aktif).
  });

  window.operatorTable = table;

  // === §8 Tahap A: Enter pada sel aktif → mulai edit ===
  // Tabulator hanya menyediakan pemicu edit lewat mouse (editTriggerEvent), jadi
  // jalur keyboard dipasang di sini. Enter/Esc DI DALAM editor sudah ditangani
  // masing-masing editor (numberEditor/dateEditor + editor bawaan) — karena itu
  // handler ini mundur bila fokus sedang berada di elemen input.
  // Fase CAPTURE agar niat §8 (Enter = mulai edit) menang atas binding keyboard
  // bawaan Tabulator pada elemen yang sama.
  (function wireEnterToEdit() {
    const tableEl = document.getElementById('operatorTabulatorTable');
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

  // === §8 Tahap A: tombol toolbar "Detail" → modal Detail Dokumen baris aktif ===
  // Menggantikan jalur dobel-klik lama (dobel-klik kini = mulai edit).
  (function wireDetailButton() {
    const detailBtn = document.getElementById('btnDetailBarisAktif');
    if (!detailBtn) return;
    detailBtn.addEventListener('click', function () {
      const row = activeRow();
      if (!row) {
        opToast('error', 'Pilih dulu sel pada baris yang ingin dilihat.');
        return;
      }
      if (typeof window.openViewDocumentModal === 'function') {
        window.openViewDocumentModal(row.getData().id);
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

  // Delete / Backspace → kosongkan sel terpilih tanpa masuk mode edit.
  function clearSelection() {
    const sel = selectedWritableCells();
    applyBulkEdits(
      editsFromCells(sel.cells, function () { return ''; }),
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

    const edits = [];
    let skipped = 0;
    for (let i = 0; i < matrix.length; i++) {
      const row = rows[r0 + i];
      if (!row) break; // melewati baris terakhir yang termuat — tempel dipotong, baris tidak dibuat.
      const rowEditable = !!row.getData().can_edit;
      for (let j = 0; j < matrix[i].length; j++) {
        const col = cols[c0 + j];
        if (!col) break; // melewati kolom terakhir.
        const field = col.getField();
        if (field === 'handler' || NON_EDITABLE_FIELDS.indexOf(field) !== -1 || !rowEditable) {
          skipped++; // posisi tetap dikonsumsi supaya kolom berikutnya tidak bergeser.
          continue;
        }
        edits.push({ row: row, field: field, value: matrix[i][j] });
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
    const tableEl = document.getElementById('operatorTabulatorTable');
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
    const container = document.getElementById('operatorTabulatorTable');
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
  function toolbarEl(selector) {
    return document.querySelector('.tabulator-toolbar ' + selector);
  }
  function getFilterParams() {
    const s = toolbarEl('input[name="search"]');
    const y = toolbarEl('select[name="year"]');
    const st = toolbarEl('select[name="status_filter"]');
    // Sort header sudah dimatikan lewat columnDefaults di konstruktor Tabulator,
    // jadi tak ada sorter aktif yang bisa dikirim — server memakai urutan default/sesi.
    const params = {
      search: s ? s.value : '',
      year: y ? y.value : '',
      status_filter: st ? st.value : '',
    };
    return params;
  }
  (function wireFilters() {
    const searchEl = toolbarEl('input[name="search"]');
    const yearEl = toolbarEl('select[name="year"]');
    const statusEl = toolbarEl('select[name="status_filter"]');
    // resetHistory: setelah filter berubah, isi tabel berganti — nilai "sebelum"
    // di riwayat Ctrl+Z tak lagi mencerminkan keadaan server, jadi jangan disimpan.
    function reload() { clearLoadError(); resetHistory(); table.replaceData(); }
    if (searchEl) {
      searchEl.removeAttribute('disabled');
      let deb = null;
      searchEl.addEventListener('input', function () {
        clearTimeout(deb);
        deb = setTimeout(reload, 300); // debounce 300ms.
      });
    }
    if (yearEl) { yearEl.removeAttribute('disabled'); yearEl.addEventListener('change', reload); }
    if (statusEl) { statusEl.removeAttribute('disabled'); statusEl.addEventListener('change', reload); }
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
  table.on('dataLoadError', function () { showLoadError(); });
  table.on('dataLoaded', function () { clearLoadError(); });

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

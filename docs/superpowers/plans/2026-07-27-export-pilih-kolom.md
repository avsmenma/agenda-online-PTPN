# Prompt Pemilihan Kolom sebelum Export — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Tombol Export membuka satu modal (pilih format Excel/PDF + pilih kolom via checkbox) sebelum benar-benar export, berlaku 5 role via engine bersama.

**Architecture:** Perubahan terlokalisasi di IIFE `wireExportButton()` dalam `public/js/document-tabulator.js`. Dropdown Excel/PDF pure-CSS lama diganti tombol tunggal + modal self-contained (CSS scoped `.dxm-*`, toggle kelas `.show`, tanpa Bootstrap JS). Nol perubahan server/Blade — controller tiap role sudah menerima `columns[]` + fallback.

**Tech Stack:** Vanilla JS (engine Tabulator), CSS inline scoped, Bootstrap 5 CDN (hanya kelas utilitas `me-1`/`btn-outline-success`), Laravel 12 (backend tak disentuh).

## Global Constraints

- **Nol perubahan server & Blade.** Hanya `public/js/document-tabulator.js` yang berubah. Route `documents.<role>.export`, `exportDocuments()`, trait `ExportsDocuments`, `DocumentExporter`, view `exports/document-print.blade.php` **tidak disentuh**.
- **Perubahan aditif & lintas-role sadar (CLAUDE.md §6).** Engine dipakai 5 role; ubah HANYA `wireExportButton()`. Jangan sentuh formatter/kolom/inline-edit lain.
- **Jangan tambah CSS inline global baru ke Blade** (CLAUDE.md §4). CSS modal disuntik dari JS sebagai `<style id="docExportModalStyle">` sekali, prefiks kelas unik `.dxm-*` (tak bertabrakan dengan `.customization-modal` per-role).
- **`git add` per-file**, pesan commit Bahasa Indonesia (aturan `~/.claude/CLAUDE.md`).
- **Suite hijau sebelum commit:** `php artisan test` (regresi; kontrak `columns[]` & route tak berubah).
- `PDF_SOFT_LIMIT = 9` — peringatan A4 **lunak**, tidak memblokir.
- Default format = **excel**; default centang kolom = kolom yang `isVisible()` saat modal dibuka.

---

### Task 1: Ganti dropdown Export → tombol + modal terpadu (format + kolom)

**Files:**
- Modify: `public/js/document-tabulator.js` — IIFE `wireExportButton()` (saat ini ~baris 1396–1494, termasuk komentar section 1396–1401).

**Interfaces:**
- Consumes (sudah ada di engine, JANGAN diubah): `CFG.exportUrl`, `CFG.extraColumns` (array `{field,...}`), `table.getColumns()` (tiap kolom punya `getField()`, `getDefinition()`, `isVisible()`), `getFilterParams()` (mengembalikan objek param filter aktif), variabel `table`.
- Produces: tidak ada konsumen eksternal. Fungsi lama `visibleColumnFields()` dan seluruh markup dropdown (`data-export-format`, `.dropdown-toggle`/`.dropdown-menu` bikinan IIFE ini) **dihapus** — diganti `exportColumnCandidates()` + modal `.dxm-*` yang sepenuhnya internal IIFE.

- [ ] **Step 1: Pastikan tak ada referensi eksternal ke markup dropdown lama**

Grep-gate sebelum menghapus (CLAUDE.md §3). Yang dicari harus **nol hit di luar** `document-tabulator.js`:

Run:
```
rg -n "data-export-format|visibleColumnFields" public resources app
```
Expected: hit HANYA di `public/js/document-tabulator.js` (definisi internal IIFE). Bila ada hit di file lain → BERHENTI, lapor (berarti markup dropdown dipakai di luar dugaan).

- [ ] **Step 2: Baca blok lama untuk anchor edit**

Run: buka `public/js/document-tabulator.js` baris 1396–1494 dan konfirmasi isinya = komentar section `// === Task 2 (fitur export bersama, ADITIF): tombol Export toolbar ===` diikuti `(function wireExportButton() { ... })();` yang berakhir tepat sebelum `// === Tugas 7f: Penanganan gagal muat data ...`.

- [ ] **Step 3: Ganti seluruh IIFE `wireExportButton()` dengan versi modal**

Replace komentar section + IIFE lama (1396–1494) dengan blok berikut **utuh**:

```js
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
```

- [ ] **Step 4: Verifikasi statis — tak ada sisa referensi dropdown lama & sintaks JS valid**

Run:
```
rg -n "data-export-format|visibleColumnFields|dropdown-toggle" public/js/document-tabulator.js
node --check public/js/document-tabulator.js
```
Expected: grep **nol hit** (semua sisa dropdown lama sudah tergantikan); `node --check` exit 0 (tak ada error sintaks). Bila `node` tak tersedia, lewati cek sintaks dan andalkan review Step 6.

- [ ] **Step 5: Jalankan suite PHP (regresi kontrak export)**

Run: `php artisan test`
Expected: hijau (mis. 245 lulus). Tak ada jalur server baru; ini memastikan route `documents.<role>.export` & kontrak `columns[]` tetap utuh. Bila ada yang merah → BERHENTI, diagnosa (superpowers:systematic-debugging) sebelum lanjut.

- [ ] **Step 6: Review mandiri kode**

Baca ulang blok baru: (a) tombol Export hanya muncul saat `CFG.exportUrl` ada; (b) `exportColumnCandidates()` menyingkirkan `handler` + `CFG.extraColumns` + field kosong; (c) default centang = `isVisible()`; (d) `goBtn.disabled` saat 0 kolom; (e) catatan A4 hanya format PDF, lunak; (f) tutup via Batal/overlay/Esc/×; (g) `buildExportUrl` tetap memakai `getFilterParams()` + `columns[]` + `format`.

- [ ] **Step 7: Commit**

```bash
git add public/js/document-tabulator.js
git commit -m "feat(export): modal pilih kolom + format sebelum export (5 role)"
```

---

### Task 2: Sinkron CLAUDE.md — catat perubahan alur export

**Files:**
- Modify: `CLAUDE.md` (§7 paragraf "Fitur Export Bersama").

**Interfaces:**
- Consumes: hasil Task 1.
- Produces: dokumentasi kanonik agar sesi berikut tak salah kutip perilaku export.

- [ ] **Step 1: Tambahkan catatan alur baru**

Di paragraf "Fitur Export Bersama" CLAUDE.md §7, tambahkan kalimat bahwa tombol Export kini membuka **modal terpadu** (pilih format Excel/PDF + pilih kolom via checkbox; default centang = kolom terlihat; PDF punya catatan A4 lunak ambang 9 kolom; 0 kolom → Export nonaktif), menggantikan dropdown Excel/PDF pure-CSS lama. Sebut satu-satunya berkas berubah: `public/js/document-tabulator.js` (`wireExportButton()`), nol perubahan server/Blade. Rujuk spec `docs/superpowers/specs/2026-07-27-export-pilih-kolom-design.md`.

- [ ] **Step 2: Commit**

```bash
git add CLAUDE.md
git commit -m "docs: catat modal pilih kolom sebelum export di CLAUDE.md"
```

---

## Deploy (setelah kedua task & persetujuan user)

`public/js/*` adalah aset statis — cukup `git pull` di server; `view/route/config clear` tak wajib untuk JS, tapi jalankan tetap agar konsisten alur deploy (CLAUDE.md §5). **Cache browser**: minta user hard-refresh (Ctrl+F5) bila JS lama masih ter-cache.

```bash
git push origin codinggemini
# di server:
git pull
php artisan route:clear && php artisan view:clear && php artisan config:clear
```

## Catatan pengujian (jujur)

- **Tak ada test JS** di repo (konsisten dengan engine sekarang) — verifikasi via `node --check` + review + QA browser user.
- **QA visual = tanggung jawab user** (CLAUDE.md §6): buka role (mis. pembayaran), klik Export → modal muncul; default centang = kolom terlihat; ganti ke PDF → catatan A4 muncul; pilih > 9 kolom → catatan berubah kuning; 0 kolom → Export nonaktif; Esc/overlay/Batal menutup; Excel & PDF menghasilkan kolom sesuai centang. Idealnya cek 1 role lain untuk paritas.

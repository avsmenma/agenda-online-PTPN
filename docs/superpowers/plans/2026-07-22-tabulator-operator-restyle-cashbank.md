# Restyle Tabel Tabulator Operator ala CASH_BANK — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Mengganti gaya tabel Tabulator role Operator agar menyerupai tabel Bank Masuk/Keluar project CASH_BANK — header navy bersih tanpa kontrol sort, font Source Sans Pro, garis antarbaris tegas, sel aktif tebal.

**Architecture:** Murni lapisan tampilan. `public/css/tabulator-agenda.css` ditulis ulang total (hanya dipakai 1 view, aman). `public/js/operator-tabulator.js` disunting tiga tempat untuk mematikan sort header dan membuang kode sort yang jadi mati. Satu baris link webfont ditambahkan di view Tabulator operator. Tema vendor `tabulator.min.css` TIDAK ditukar dan TIDAK diedit.

**Tech Stack:** Laravel 12, Blade, Tabulator.js v6.3.1 (self-hosted), CSS polos (tanpa build step — `@vite` mati di project ini), PHPUnit.

## Global Constraints

- Acuan desain: `docs/superpowers/specs/2026-07-22-tabulator-operator-restyle-cashbank-design.md` (commit `c1f6d3b`).
- Semua aturan CSS WAJIB di-scope `#operatorTabulatorTable` — CLAUDE.md §1: 6 tabel per-role masih terduplikasi, gaya bocor merusak role lain.
- JANGAN menyentuh `resources/views/layouts/app.blade.php` (god-file global, gerbang kritis §6).
- JANGAN menukar/mengedit `public/vendor/tabulator/tabulator.min.css` atau `tabulator.min.js`.
- JANGAN mengubah backend: `DokumenController::buildOperatorQuery` tetap menerima `sort`/`order` (dipakai pemanggil lain).
- JANGAN mengubah logika §8 Tahap A/B (sel aktif, blok, Ctrl+C/V, Delete, undo/redo) selain hilangnya sort header.
- Warna wajib persis: header `#0d3b6e`, border bawah header `#082948`, garis sel/baris `#c3d2e0`, baris genap `#fbfdff`, hover `#f0f5fb`, aksen sel aktif `#1b6fd8`, blok terpilih `#dbeafe`.
- Font wajib: `"Source Sans Pro", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif` pada 12px.
- Commit per-file (`git add <path>`, JANGAN `git add .`), pesan Bahasa Indonesia.
- `php artisan test` harus hijau sebelum tiap commit.

---

## File Structure

| Berkas | Peran | Aksi |
|---|---|---|
| `public/css/tabulator-agenda.css` | Satu-satunya sumber gaya tabel operator | **Tulis ulang total** (152 → ±175 baris) |
| `public/js/operator-tabulator.js` | Konfigurasi & perilaku tabel | Sunting 3 tempat (~1192 → ~1180 baris) |
| `resources/views/operator/dokumens/daftarDokumenTabulator.blade.php` | View tabel operator | Tambah 1 baris `<link>` webfont |
| `tests/Feature/OperatorTabulatorViewTest.php` | Test view | Tambah 1 test |

---

### Task 1: Muat webfont Source Sans Pro di view Tabulator

**Files:**
- Modify: `resources/views/operator/dokumens/daftarDokumenTabulator.blade.php:418-420`
- Test: `tests/Feature/OperatorTabulatorViewTest.php`

**Interfaces:**
- Consumes: tidak ada (task pertama).
- Produces: font `"Source Sans Pro"` tersedia di halaman `/documents`. Task 2 mengandalkan ini — tanpa link ini, `font-family` di CSS jatuh ke fallback Arial.

- [ ] **Step 1: Tulis test yang gagal**

Tambahkan method ini ke `tests/Feature/OperatorTabulatorViewTest.php`, tepat setelah
`test_toolbar_menyediakan_tombol_detail_baris_aktif()` (berakhir di baris 66) dan
sebelum `test_flag_classic_menyajikan_view_lama()` (mulai baris 68).

Berkas itu sudah punya helper privat `operator(): User` (baris 37-40) dan memakai
`route('documents.index')` + `assertOk()` — method baru mengikuti konvensi yang sama,
jadi tidak perlu membuat user sendiri:

```php
    /**
     * Spec 2026-07-22: tema tabel menyetel font-family "Source Sans Pro". Webfont-nya
     * sengaja dimuat di view ini, BUKAN di layouts/app.blade.php, agar tipografi role
     * lain tidak ikut berubah. Tanpa link ini font diam-diam jatuh ke Arial dan
     * restyle terlihat gagal tanpa error apa pun.
     */
    public function test_view_memuat_webfont_source_sans_pro(): void
    {
        $response = $this->actingAs($this->operator())
            ->get(route('documents.index'));

        $response->assertOk();
        $response->assertSee('family=Source+Sans+Pro', false);
    }
```

- [ ] **Step 2: Jalankan test, pastikan GAGAL**

```bash
php artisan test --filter=test_view_memuat_webfont_source_sans_pro
```

Expected: FAIL — `Failed asserting that '...' contains "family=Source+Sans+Pro"`.

- [ ] **Step 3: Tambahkan link webfont**

Di `resources/views/operator/dokumens/daftarDokumenTabulator.blade.php`, blok
`@push('styles')` saat ini berbunyi:

```blade
@push('styles')
    <link rel="stylesheet" href="{{ asset('vendor/tabulator/tabulator.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/tabulator-agenda.css') }}">
```

Ubah menjadi (sisipkan 1 baris di antara `@push` dan link tema vendor):

```blade
@push('styles')
    {{-- Font tabel ala CASH_BANK. Sengaja dimuat di sini, BUKAN di layouts/app.blade.php,
         agar tipografi role lain tidak ikut berubah (CLAUDE.md §6). --}}
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/tabulator/tabulator.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/tabulator-agenda.css') }}">
```

- [ ] **Step 4: Jalankan test, pastikan LULUS**

```bash
php artisan test --filter=OperatorTabulatorViewTest
```

Expected: PASS — 4 test hijau (3 lama + 1 baru).

- [ ] **Step 5: Commit**

```bash
git add resources/views/operator/dokumens/daftarDokumenTabulator.blade.php tests/Feature/OperatorTabulatorViewTest.php
git commit -m "feat(operator-tabulator): muat webfont Source Sans Pro di view tabel"
```

---

### Task 2: Tulis ulang tema CSS tabel operator

**Files:**
- Rewrite: `public/css/tabulator-agenda.css` (seluruh isi diganti)

**Interfaces:**
- Consumes: webfont `Source Sans Pro` dari Task 1.
- Produces: kelas gaya yang dipakai formatter JS tetap ada — `.badge-status`, `.badge-status.badge-terkirim`, `.tabulator-toolbar`, `.tabulator-toolbar-search`. Task 3 tidak bergantung pada berkas ini.

**Konteks penting sebelum menulis:** sel aktif di project ini memakai modul
SelectRange bawaan Tabulator (kelas `.tabulator-range-cell-active`), BUKAN kelas
`.bm-active-cell` tulisan tangan CASH_BANK. Jangan menyalin selektor CASH_BANK
mentah-mentah — kelas itu tidak pernah ada di halaman ini.

- [ ] **Step 1: Catat baseline agar tidak ada kelas yang hilang diam-diam**

```bash
grep -o "badge-status\|badge-terkirim\|tabulator-toolbar-search\|tabulator-toolbar" public/css/tabulator-agenda.css | sort -u
```

Expected: empat nama kelas tercetak. Keempatnya WAJIB masih ada setelah penulisan ulang.

- [ ] **Step 2: Ganti seluruh isi `public/css/tabulator-agenda.css`**

Tulis berkas dengan isi PERSIS berikut:

```css
/*
 * Tema Tabulator — Daftar Dokumen Operator.
 *
 * Meniru tabel Bank Masuk/Keluar project CASH_BANK: header navy rapat tanpa
 * kontrol sort, font Source Sans Pro, garis antarbaris tegas, sel aktif tebal.
 * Acuan: docs/superpowers/specs/2026-07-22-tabulator-operator-restyle-cashbank-design.md
 *
 * Seluruh aturan dikunci di bawah #operatorTabulatorTable agar tidak bocor ke
 * tabel role lain (CLAUDE.md §1 — 6 tabel per-role masih terduplikasi).
 */

/* ------- Wadah tabel ------- */
#operatorTabulatorTable.tabulator {
  background-color: #ffffff;
  border: 1px solid #d0dce8;
  border-radius: 8px;
  font-family: "Source Sans Pro", -apple-system, BlinkMacSystemFont, "Segoe UI",
    Roboto, "Helvetica Neue", Arial, sans-serif;
  font-size: 12px;
  color: #1f2933;
}

/* Tema vendor menyetel font sendiri pada header & sel — paksa ikut wadah. */
#operatorTabulatorTable.tabulator .tabulator-cell,
#operatorTabulatorTable.tabulator .tabulator-header .tabulator-col,
#operatorTabulatorTable.tabulator .tabulator-header .tabulator-col-title {
  font-family: inherit;
}

/* ------- Header kolom ------- */
#operatorTabulatorTable.tabulator .tabulator-header {
  background-color: #0d3b6e;
  border-bottom: 2px solid #082948;
  color: #ffffff;
  font-weight: 600;
}

#operatorTabulatorTable.tabulator .tabulator-header .tabulator-col {
  background-color: #0d3b6e;
  border-right: 1px solid #ffffff;
  color: #ffffff;
}

#operatorTabulatorTable.tabulator .tabulator-header .tabulator-col .tabulator-col-title {
  color: #ffffff;
  font-size: 11.5px;
  font-weight: 600;
  padding: 6px 8px;
  text-align: center;
  white-space: normal;
}

/* Pegangan tarik-lebar kolom: garis putih samar di batas header. */
#operatorTabulatorTable.tabulator .tabulator-header .tabulator-col .tabulator-col-resize-handle {
  width: 6px;
  cursor: col-resize;
}

#operatorTabulatorTable.tabulator .tabulator-header .tabulator-col:not(.tabulator-col-group) > .tabulator-col-resize-handle {
  background: linear-gradient(to bottom, transparent 32%, rgba(255, 255, 255, 0.45) 32%,
    rgba(255, 255, 255, 0.45) 68%, transparent 68%) center / 2px 100% no-repeat;
}

#operatorTabulatorTable.tabulator .tabulator-header .tabulator-col .tabulator-col-resize-handle:hover {
  background: rgba(255, 255, 255, 0.35);
}

/* Pesan tempat scrollbar sejak awal agar header & isi tetap sejajar. */
#operatorTabulatorTable.tabulator .tabulator-tableholder {
  scrollbar-gutter: stable;
}

/* ------- Baris & sel ------- */
#operatorTabulatorTable.tabulator .tabulator-row {
  border-bottom: 1px solid #c3d2e0;
}

#operatorTabulatorTable.tabulator .tabulator-row.tabulator-row-even {
  background-color: #fbfdff;
}

#operatorTabulatorTable.tabulator .tabulator-row .tabulator-cell {
  padding: 6px 8px;
  border-right: 1px solid #c3d2e0;
  vertical-align: middle;
}

#operatorTabulatorTable.tabulator .tabulator-row:hover .tabulator-cell {
  background-color: #f0f5fb;
}

/* ------- Sel aktif & blok seleksi -------
 * Kelas milik modul SelectRange bawaan Tabulator 6.3.1. Nilai bawaan tema vendor
 * (border 2px #2975dd, blok #9abcea) ditebalkan & dilembutkan sesuai acuan CASH_BANK.
 * Aturan blok sengaja ditaruh SETELAH aturan hover agar menang tanpa !important.
 */
#operatorTabulatorTable.tabulator .tabulator-tableholder .tabulator-range-overlay .tabulator-range-cell-active {
  border: 3px solid #1b6fd8;
}

#operatorTabulatorTable.tabulator .tabulator-tableholder .tabulator-range-overlay .tabulator-range {
  border: 1px solid #1b6fd8;
}

#operatorTabulatorTable.tabulator .tabulator-tableholder .tabulator-range-overlay .tabulator-range.tabulator-range-active:after {
  background-color: #1b6fd8;
}

#operatorTabulatorTable.tabulator .tabulator-row .tabulator-cell.tabulator-range-selected:not(.tabulator-range-only-cell-selected):not(.tabulator-range-row-header) {
  background-color: #dbeafe;
}

/* Header kolom yang sedang tersorot range — jangan menabrak latar navy. */
#operatorTabulatorTable.tabulator .tabulator-header .tabulator-col.tabulator-range-highlight {
  background-color: #124a85;
  color: #ffffff;
}

#operatorTabulatorTable.tabulator .tabulator-header .tabulator-col.tabulator-range-selected {
  background-color: #1b6fd8;
  color: #ffffff;
}

/* ------- Kolom beku (frozen) ------- */
#operatorTabulatorTable.tabulator .tabulator-row .tabulator-cell.tabulator-frozen,
#operatorTabulatorTable.tabulator .tabulator-header .tabulator-col.tabulator-frozen {
  box-shadow: 2px 0 4px rgba(13, 59, 110, 0.15);
}

#operatorTabulatorTable.tabulator .tabulator-frozen.tabulator-frozen-left {
  border-right: 1px solid #c3d2e0;
}

/* ------- Placeholder kosong ------- */
#operatorTabulatorTable.tabulator .tabulator-placeholder .tabulator-placeholder-contents {
  color: #6b7c7c;
  font-size: 13px;
}

/* ------- Toolbar filter -------
 * Aturan dasar .tabulator-toolbar ditimpa blok <style> inline di
 * daftarDokumenTabulator.blade.php; dua aturan di bawahnya tetap hidup.
 */
.tabulator-toolbar {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 8px;
  margin-bottom: 14px;
}

.tabulator-toolbar .form-control,
.tabulator-toolbar .form-select {
  height: 38px;
  font-size: 13px;
}

.tabulator-toolbar .tabulator-toolbar-search {
  flex: 1 1 260px;
  min-width: 200px;
}

/* ============================================================
 * Paritas formatter: badge status & subline nomor_agenda.
 * DISKOPKAN dari daftarDokumen.blade.php:1366-1412 — jangan diubah di sini,
 * ini makna status, bukan hiasan tabel.
 * ============================================================ */

#operatorTabulatorTable.tabulator .badge-status {
  padding: 8px 16px;
  border-radius: 25px;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.5px;
  box-shadow: 0 3px 12px rgba(0, 0, 0, 0.15);
  border: 2px solid transparent;
  text-align: center;
  min-width: 100px;
  max-width: 250px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  transition: all 0.3s ease;
  white-space: normal;
  word-wrap: break-word;
  line-height: 1.3;
}

#operatorTabulatorTable.tabulator .badge-status.badge-terkirim {
  background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
  color: white;
  border-color: #083E40;
}

/* Sel status: izinkan tinggi mengikuti badge (badge bisa dua baris). */
#operatorTabulatorTable.tabulator .tabulator-cell[tabulator-field="status"] {
  white-space: normal;
}

/* Sel nomor_agenda dua baris: subline bulan/tahun tetap terbaca & tak terpotong. */
#operatorTabulatorTable.tabulator .tabulator-cell[tabulator-field="nomor_agenda"] {
  white-space: normal;
  line-height: 1.35;
}

#operatorTabulatorTable.tabulator .tabulator-cell[tabulator-field="nomor_agenda"] .text-muted {
  display: block;
  color: #6b7c7c;
  font-size: 11px;
}
```

- [ ] **Step 3: Verifikasi kelas lama tidak hilang & aturan sort sudah lenyap**

```bash
grep -c "badge-status\|badge-terkirim\|tabulator-toolbar-search" public/css/tabulator-agenda.css
grep -c "tabulator-arrow\|SKELETON\|min-height: 38px" public/css/tabulator-agenda.css
```

Expected: perintah pertama mencetak angka **> 0**. Perintah kedua mencetak **0**
(grep keluar dengan status 1 — itu wajar dan benar).

- [ ] **Step 4: Verifikasi kurung kurawal seimbang (CSS tidak rusak)**

```bash
python -c "s=open('public/css/tabulator-agenda.css',encoding='utf-8').read(); print('buka',s.count('{'),'tutup',s.count('}'),'SEIMBANG' if s.count('{')==s.count('}') else 'RUSAK')"
```

Expected: `SEIMBANG`.

- [ ] **Step 5: Jalankan seluruh suite**

```bash
php artisan test
```

Expected: PASS, seluruh suite hijau.

- [ ] **Step 6: Commit**

```bash
git add public/css/tabulator-agenda.css
git commit -m "style(operator-tabulator): tema tabel ala CASH_BANK — header navy, garis tegas, sel aktif tebal"
```

---

### Task 3: Matikan sort header & buang kode sort yang jadi mati

**Files:**
- Modify: `public/js/operator-tabulator.js` — empat titik: `buildColumns()` (~550, ~556, ~569), konstruktor Tabulator (~612-620), `getFilterParams()` (~1018-1030)

**Interfaces:**
- Consumes: tidak ada dari task sebelumnya.
- Produces: tidak ada yang dikonsumsi task lain. `getFilterParams()` tetap mengembalikan objek dengan kunci `search`, `year`, `status_filter` — kunci `sort` dan `order` hilang.

**Konteks:** `sortMode: 'remote'` dan blok `getSorters()` adalah fitur commit `0675c23`.
User memutuskan sadar untuk membuangnya (spec §5). Backend `DokumenController.php:40`
TETAP menerima `sort`/`order` — jangan disentuh, pemanggil lain memakainya.

- [ ] **Step 1: Pasang `headerSort: false` sebagai default semua kolom**

Di konstruktor Tabulator, temukan blok ini (sekitar baris 612-620):

```js
    // Fix review: sort server-side (buildOperatorQuery baca request('sort')/('order'))
    // alih-alih sort lokal yang menyesatkan (hanya menyortir chunk termuat di dataset
    // 5000+ baris progressive-load). getFilterParams() menambah params.sort/order.
    sortMode: 'remote',
    ajaxResponse: function (url, params, response) { return response; },
    layout: 'fitDataStretch',
    height: '70vh',
    index: 'id',
    columns: buildColumns(CFG),
```

Ganti menjadi:

```js
    ajaxResponse: function (url, params, response) { return response; },
    layout: 'fitDataStretch',
    height: '70vh',
    index: 'id',
    // Sort header dibuang atas keputusan user (spec 2026-07-22 §5): header bersih
    // tanpa segitiga, urutan tabel selalu default server/sesi. Backend masih
    // menerima sort/order untuk pemanggil lain — hanya klien yang berhenti mengirim.
    columnDefaults: { headerSort: false, resizable: true },
    columns: buildColumns(CFG),
```

- [ ] **Step 2: Hapus tiga `headerSort: false` per-kolom yang jadi mubazir**

Di `buildColumns()`, ubah baris kolom nomor (sekitar 550):

```js
    const cols = [{ formatter: 'rownum', width: 60, frozen: true, headerSort: false, title: 'No' }];
```

menjadi:

```js
    const cols = [{ formatter: 'rownum', width: 60, frozen: true, title: 'No' }];
```

Lalu di blok `nomor_agenda` (sekitar 553-557), ubah:

```js
      if (c.key === 'nomor_agenda') {
        def.frozen = true;
        def.variableHeight = true; // sel dua baris (nomor + bulan/tahun) tak terpotong.
        def.headerSort = false; // kolom identitas beku — urutan default server (session) yang berlaku.
      }
```

menjadi:

```js
      if (c.key === 'nomor_agenda') {
        def.frozen = true;
        def.variableHeight = true; // sel dua baris (nomor + bulan/tahun) tak terpotong.
      }
```

Lalu kolom Pengurus Dokumen (sekitar 569), ubah:

```js
    cols.push({ title: 'Pengurus Dokumen', field: 'handler', formatter: fmtHandler, headerSort: false, editable: false });
```

menjadi:

```js
    cols.push({ title: 'Pengurus Dokumen', field: 'handler', formatter: fmtHandler, editable: false });
```

- [ ] **Step 3: Buang blok `getSorters()` yang kini tak pernah terisi**

Di `getFilterParams()` (sekitar baris 1009-1032), temukan:

```js
    const params = {
      search: s ? s.value : '',
      year: y ? y.value : '',
      status_filter: st ? st.value : '',
    };
    // sortMode:'remote' — kirim sorter aktif sebagai params.sort/order (dibaca
    // buildOperatorQuery). Dijaga try/catch: pada request pertama window.operatorTable
    // belum tentu ter-assign (assignment terjadi setelah constructor Tabulator selesai,
    // namun ajaxParams bisa terpanggil selama konstruksi).
    try {
      if (window.operatorTable && typeof window.operatorTable.getSorters === 'function') {
        const sorters = window.operatorTable.getSorters();
        if (sorters && sorters.length > 0) {
          params.sort = sorters[0].field;
          params.order = sorters[0].dir;
        }
      }
    } catch (e) { /* biarkan server pakai sort default/sesi. */ }
    return params;
```

Ganti menjadi:

```js
    // Tanpa sort header (columnDefaults.headerSort=false), tak ada sorter aktif yang
    // bisa dikirim — server memakai urutan default/sesi miliknya sendiri.
    const params = {
      search: s ? s.value : '',
      year: y ? y.value : '',
      status_filter: st ? st.value : '',
    };
    return params;
```

- [ ] **Step 4: Verifikasi kode sort benar-benar lenyap dan default terpasang**

```bash
grep -n "headerSort\|sortMode\|getSorters" public/js/operator-tabulator.js
```

Expected: TEPAT SATU baris tercetak — `columnDefaults: { headerSort: false, resizable: true },`.
Jika `sortMode` atau `getSorters` masih muncul, Step 1 atau 3 belum selesai.

- [ ] **Step 5: Verifikasi sintaks JavaScript masih sah**

```bash
node --check public/js/operator-tabulator.js
```

Expected: tidak ada keluaran (exit 0). Node v20.19.0 sudah terverifikasi ada di mesin ini.
Bila ada keluaran, berkas rusak — perbaiki sebelum lanjut, JANGAN commit.

- [ ] **Step 6: Jalankan seluruh suite**

```bash
php artisan test
```

Expected: PASS, seluruh suite hijau.

- [ ] **Step 7: Commit**

```bash
git add public/js/operator-tabulator.js
git commit -m "refactor(operator-tabulator): matikan sort header, buang sortMode remote & getSorters"
```

---

### Task 4: Deploy & serahkan ke QA visual user

**Files:** tidak ada perubahan kode.

**Interfaces:**
- Consumes: Task 1-3 sudah ter-commit.
- Produces: perubahan tayang di server; daftar periksa QA untuk user.

- [ ] **Step 1: Pastikan pohon kerja bersih & suite hijau**

```bash
git status --short
php artisan test
```

Expected: tidak ada berkas ter-modifikasi tersisa dari task 1-3; suite hijau.

- [ ] **Step 2: Push**

```bash
git push origin codinggemini
```

- [ ] **Step 3: Tarik & bersihkan cache di server**

```bash
ssh -i C:\Users\ASUS\.ssh\crypto_bot_vps root@163.61.58.92 "cd /var/www/agenda-online-PTPN && git pull && php artisan route:clear && php artisan view:clear && php artisan config:clear"
```

Expected: `git pull` menampilkan commit baru; tiga perintah clear selesai tanpa error.
Cache clear TIDAK BOLEH dilewat — Blade & route ter-cache membuat perubahan tampak tidak berefek.

- [ ] **Step 4: Serahkan daftar periksa QA ke user**

Agent tidak punya browser — paritas rupa TIDAK BOLEH diklaim selesai dari test backend.
Sampaikan daftar ini ke user untuk diperiksa di `http://163.61.58.92/documents`
(muat ulang dengan Ctrl+Shift+R agar CSS lama tidak dipakai dari cache peramban):

1. Header navy `#0d3b6e`, **tanpa satu pun segitiga sort**; klik header tidak melakukan apa-apa.
2. Pemisah antar-kolom header berupa garis putih yang jelas terlihat.
3. Font tabel berubah — bandingkan langsung dengan tabel bank-masuk CASH_BANK.
4. Sel aktif berbingkai biru **tebal** (3px).
5. Blok seleksi (Shift+Panah / drag) berwarna biru muda, teks tetap terbaca.
6. Garis antarbaris terlihat tegas, tidak lagi pucat.
7. Kolom beku `No` & `Nomor Agenda` masih menempel saat menggulir mendatar, bayangan pemisah masih ada.
8. Badge status & subline bulan/tahun di `Nomor Agenda` tidak berubah.
9. Inline edit (Enter/dobel-klik), Ctrl+C, Ctrl+V, Delete, Ctrl+Z/Ctrl+Y masih berfungsi.

---

## Rollback

Seluruh perubahan terbatas pada tiga berkas presentasi. Jika hasilnya tidak disukai:

```bash
git revert <sha-task-3> <sha-task-2> <sha-task-1>
git push origin codinggemini
```

Lalu ulangi Step 3 Task 4 (pull + clear cache). Tidak ada migrasi, tidak ada perubahan
skema, tidak ada perubahan data — rollback aman sepenuhnya.

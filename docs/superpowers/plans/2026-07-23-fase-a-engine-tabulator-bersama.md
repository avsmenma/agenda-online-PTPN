# Fase A — Engine Tabulator Bersama: Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Mengekstrak engine Tabulator operator jadi komponen bersama yang di-parameter, dengan operator sebagai konsumen tunggal dan NOL perubahan perilaku.

**Architecture:** Refaktor berurutan, tiap langkah menjaga tabel operator identik: (1) rename config global + berkas JS + handle window, (2) mount-id via config + kelas `.doc-tabulator` + query relatif-elemen di JS, (3) re-scope CSS id→kelas, (4) verifikasi + QA. Tiap commit meninggalkan operator berperilaku persis sama.

**Tech Stack:** Laravel 12, Blade, Tabulator.js (self-hosted), PHPUnit (SQLite in-memory), CSS statis + `App\Support\Asset::versioned` untuk cache-busting.

Spec sumber: `docs/superpowers/specs/2026-07-23-fase-a-engine-tabulator-bersama-design.md`.

> **Nomor baris indikatif** (snapshot pra-refaktor). Task menambah/menghapus baris → offset bergeser di task berikutnya. **Jangkar sebenarnya adalah blok kode yang ditampilkan** — cari & ganti berdasarkan isinya.

## Global Constraints

- **NOL perubahan perilaku operator.** Semua jalur §8 CLAUDE.md (sel aktif/panah/gulir, klik pindah, Enter/dblclick edit, Ctrl+C/V, Delete/Backspace, blok drag/Shift, Ctrl+Z/Y, persistensi lebar, kaskade Kriteria, popup Disalin, dropdown Pengurus, Tambah Baris, Hapus) harus identik.
- **Operator TETAP memakai `id="operatorTabulatorTable"`** (instance-id-nya sendiri). Yang digeneralisasi adalah ENGINE (baca id dari `CFG.mountId`), bukan id operator.
- **Nama berkas CSS `tabulator-agenda.css` DIPERTAHANKAN** (hanya selektor yang berubah).
- CSS: di mana sekarang `#operatorTabulatorTable.tabulator`, hasil akhir harus `.doc-tabulator.tabulator` (dua kelas) — jaga spesifisitas atas tema Tabulator.
- `git add` PER-FILE / `git mv` PER-FILE — JANGAN `git add .`. Pesan commit Bahasa Indonesia. Satu commit = satu perubahan logis.
- Komentar Indonesia, identifier English.
- `php artisan test` hijau sebelum tiap commit. `node --check` exit 0.
- JANGAN sentuh partial bersama, endpoint, controller/view role lain, atau `public/vendor/tabulator/*`.
- JANGAN bangun slot `actionColumn`/`editableFields` (di luar lingkup Fase A).
- JANGAN push/deploy sampai QA visual operator oleh user lolos.

---

### Task 1: Rename config global + berkas JS + handle window

**Files:**
- Modify: `resources/views/operator/dokumens/daftarDokumenTabulator.blade.php` (baris 8 komentar, 401 config, 403 Asset JS, 441 pemakaian config)
- Rename: `public/js/operator-tabulator.js` → `public/js/document-tabulator.js` (`git mv`), lalu ubah baris 23 (baca config) & 781 (handle window)
- Test: `tests/Feature/OperatorTabulatorViewTest.php`

**Interfaces:**
- Consumes: —
- Produces: view operator memuat `window.DOCUMENT_TABULATOR_CONFIG` + `js/document-tabulator.js`; engine membaca `window.DOCUMENT_TABULATOR_CONFIG`; instance di `window.documentTable`.

- [ ] **Step 1: Tulis test rename (RED)**

Tambahkan metode ini ke `tests/Feature/OperatorTabulatorViewTest.php` (setelah `test_default_menyajikan_view_tabulator`):

```php
    public function test_view_memakai_config_global_dan_berkas_document_tabulator(): void
    {
        $response = $this->actingAs($this->operator())
            ->get(route('documents.index'));

        $response->assertOk();
        $response->assertSee('window.DOCUMENT_TABULATOR_CONFIG', false);
        $response->assertSee('js/document-tabulator.js', false);
        $response->assertDontSee('OPERATOR_TABULATOR_CONFIG', false);
        $response->assertDontSee('operator-tabulator.js', false);
    }
```

- [ ] **Step 2: Jalankan test → GAGAL**

Run: `php artisan test --filter=test_view_memakai_config_global_dan_berkas_document_tabulator`
Expected: FAIL — view masih memancarkan `OPERATOR_TABULATOR_CONFIG` + `operator-tabulator.js`.

- [ ] **Step 3: Rename berkas JS**

Run:
```bash
git mv public/js/operator-tabulator.js public/js/document-tabulator.js
```

- [ ] **Step 4: Ubah pembacaan config + handle window di JS**

Di `public/js/document-tabulator.js` baris 23:

```js
  const CFG = window.OPERATOR_TABULATOR_CONFIG;
```
→
```js
  const CFG = window.DOCUMENT_TABULATOR_CONFIG;
```

Baris 781:

```js
  window.operatorTable = table;
```
→
```js
  // Handle instance untuk kode luar (mis. modal). Nama generik karena engine kini bersama;
  // grep membuktikan tak ada pemakai `window.operatorTable` di luar berkas ini.
  window.documentTable = table;
```

- [ ] **Step 5: Ubah Blade — nama global, komentar, & referensi berkas JS**

Di `resources/views/operator/dokumens/daftarDokumenTabulator.blade.php`:

Baris 8 (komentar):
```
  Tabel di-mount oleh public/js/operator-tabulator.js membaca window.OPERATOR_TABULATOR_CONFIG.
```
→
```
  Tabel di-mount oleh public/js/document-tabulator.js membaca window.DOCUMENT_TABULATOR_CONFIG.
```

Baris 401:
```blade
    <script>window.OPERATOR_TABULATOR_CONFIG = @json($configArray);</script>
```
→
```blade
    <script>window.DOCUMENT_TABULATOR_CONFIG = @json($configArray);</script>
```

Baris 403:
```blade
    <script src="{{ \App\Support\Asset::versioned('js/operator-tabulator.js') }}"></script>
```
→
```blade
    <script src="{{ \App\Support\Asset::versioned('js/document-tabulator.js') }}"></script>
```

Baris 441:
```js
        form.action = window.OPERATOR_TABULATOR_CONFIG.destroyTpl.replace('{id}', documentIdToDelete);
```
→
```js
        form.action = window.DOCUMENT_TABULATOR_CONFIG.destroyTpl.replace('{id}', documentIdToDelete);
```

- [ ] **Step 6: Cek sintaks JS + jalankan suite → LULUS**

Run: `node --check public/js/document-tabulator.js && echo OK`
Expected: `OK`.
Run: `php artisan test`
Expected: PASS semua (jumlah bertambah 1 dari test baru).

- [ ] **Step 7: Commit (per-file)**

```bash
git add public/js/document-tabulator.js
git add resources/views/operator/dokumens/daftarDokumenTabulator.blade.php
git add tests/Feature/OperatorTabulatorViewTest.php
git commit -m "refactor(tabulator): rename config global & berkas engine jadi DOCUMENT/document-tabulator"
```

> Catatan: `git mv` (Step 3) sudah men-stage rename; `git add public/js/document-tabulator.js` menambahkan perubahan isinya (Step 4).

---

### Task 2: Mount-id via config + kelas `.doc-tabulator` + query relatif-elemen

**Files:**
- Modify: `resources/views/operator/dokumens/daftarDokumenTabulator.blade.php` (`$configArray` baris ~17-35; mount div baris 81)
- Modify: `public/js/document-tabulator.js` (tambah helper `mountEl()`; 6 situs mount-id: 717, 791, 1176, 1189, 1341, 1348)
- Test: `tests/Feature/OperatorTabulatorViewTest.php`

**Interfaces:**
- Consumes: `window.DOCUMENT_TABULATOR_CONFIG` (Task 1).
- Produces: config berisi `mountId`; mount div ber-`class="doc-tabulator"`; engine meresolusi mount via `CFG.mountId` + query relatif.

- [ ] **Step 1: Tulis test mount-id & kelas (RED)**

Tambahkan ke `tests/Feature/OperatorTabulatorViewTest.php`:

```php
    public function test_view_menyetel_mountid_dan_kelas_doc_tabulator(): void
    {
        $response = $this->actingAs($this->operator())
            ->get(route('documents.index'));

        $response->assertOk();
        // Elemen mount membawa kelas bersama (target CSS) + tetap id instance operator.
        $response->assertSee('id="operatorTabulatorTable" class="doc-tabulator"', false);
        // Engine membaca id mount dari config.
        $response->assertSee('mountId', false);
    }
```

- [ ] **Step 2: Jalankan test → GAGAL**

Run: `php artisan test --filter=test_view_menyetel_mountid_dan_kelas_doc_tabulator`
Expected: FAIL — mount div belum ber-kelas & config belum ada `mountId`.

- [ ] **Step 3: Tambah `mountId` ke config + kelas ke mount div (Blade)**

Di `$configArray` (`daftarDokumenTabulator.blade.php` baris ~17), sisipkan sebagai entri pertama:

```php
        $configArray = [
            'mountId'          => 'operatorTabulatorTable',
            'dataUrl'          => route('documents.data'),
```

Mount div (baris 81):
```blade
        <div id="operatorTabulatorTable"></div>
```
→
```blade
        <div id="operatorTabulatorTable" class="doc-tabulator"></div>
```

- [ ] **Step 4: Tambah helper `mountEl()` di JS**

Di `public/js/document-tabulator.js`, tepat setelah guard config (`if (!CFG || typeof Tabulator === 'undefined') return;`, sekitar baris 24), tambahkan:

```js
  // Elemen mount tabel. Id datang dari config (CFG.mountId) agar engine tak terikat ke
  // satu role; query internal memakai elemen ini (relatif), bukan selektor '#id ...' global.
  function mountEl() { return document.getElementById(CFG.mountId); }
```

- [ ] **Step 5: Ganti 6 situs mount-id hardcoded di JS**

Baris 717:
```js
  const table = new Tabulator('#operatorTabulatorTable', {
```
→
```js
  const table = new Tabulator(mountEl(), {
```

Baris 791:
```js
    const tableEl = document.getElementById('operatorTabulatorTable');
```
→
```js
    const tableEl = mountEl();
```

Baris 1176:
```js
    const tableEl = document.getElementById('operatorTabulatorTable');
```
→
```js
    const tableEl = mountEl();
```

Baris 1189:
```js
    const container = document.getElementById('operatorTabulatorTable');
```
→
```js
    const container = mountEl();
```

Baris 1341 (di dalam fungsi `wadahGulir()`):
```js
      return document.querySelector('#operatorTabulatorTable .tabulator-tableholder');
```
→
```js
      const host = mountEl();
      return host ? host.querySelector('.tabulator-tableholder') : null;
```

Baris ~1346-1349 (di dalam fungsi `tepiKananKolomBeku()`) — pakai nama `host`, JANGAN `el`, karena `beku.forEach(function (el) {...})` di bawahnya sudah memakai `el`:
```js
      const beku = document.querySelectorAll(
        '#operatorTabulatorTable .tabulator-header .tabulator-col.tabulator-frozen.tabulator-frozen-left'
      );
```
→
```js
      const host = mountEl();
      const beku = host ? host.querySelectorAll(
        '.tabulator-header .tabulator-col.tabulator-frozen.tabulator-frozen-left'
      ) : [];
```

- [ ] **Step 6: Cek sintaks + grep JS bersih + suite → LULUS**

Run: `node --check public/js/document-tabulator.js && echo OK`
Expected: `OK`.
Run: `grep -n "operatorTabulatorTable" public/js/document-tabulator.js`
Expected: KOSONG (semua mount-id kini via `CFG.mountId`).
Run: `php artisan test`
Expected: PASS semua.

- [ ] **Step 7: Commit (per-file)**

```bash
git add public/js/document-tabulator.js
git add resources/views/operator/dokumens/daftarDokumenTabulator.blade.php
git add tests/Feature/OperatorTabulatorViewTest.php
git commit -m "refactor(tabulator): mount-id via config + kelas .doc-tabulator, engine id-agnostik"
```

---

### Task 3: Re-scope CSS `#operatorTabulatorTable` → `.doc-tabulator`

**Files:**
- Modify: `public/css/tabulator-agenda.css` (39 kemunculan `#operatorTabulatorTable`)

**Interfaces:**
- Consumes: kelas `doc-tabulator` pada mount div (Task 2).
- Produces: seluruh aturan tabel di-scope ke `.doc-tabulator.tabulator ...` (bukan id).

> Tidak ada test PHPUnit untuk langkah ini — CSS berkas statis, tak dieksekusi suite. Gerbangnya adalah grep + **QA visual operator**. `OperatorTabulatorViewTest` tetap hijau karena nama berkas CSS tak berubah.

- [ ] **Step 1: Ganti SEMUA `#operatorTabulatorTable` → `.doc-tabulator`**

Di `public/css/tabulator-agenda.css`, ganti seluruh (39×) string `#operatorTabulatorTable` menjadi `.doc-tabulator`. Karena SETIAP kemunculan berbentuk `#operatorTabulatorTable.tabulator` (id+kelas), hasilnya otomatis `.doc-tabulator.tabulator` (dua kelas) — spesifisitas atas tema Tabulator terjaga. (Gunakan replace-all; komentar di baris 8 yang menyebut id ikut berubah — itu wajar.)

- [ ] **Step 2: Verifikasi grep + struktur CSS**

Run: `grep -c "operatorTabulatorTable" public/css/tabulator-agenda.css`
Expected: `0`.
Run: `grep -c "\.doc-tabulator\.tabulator" public/css/tabulator-agenda.css`
Expected: angka > 0 (mendekati 39; sebagian selektor tak ber-`.tabulator` seperti dark-mode `.dark .doc-tabulator.tabulator` tetap terhitung).
Run (kurung & !important tak berubah):
```bash
node -e "const s=require('fs').readFileSync('public/css/tabulator-agenda.css','utf8');const o=(s.match(/{/g)||[]).length,c=(s.match(/}/g)||[]).length,i=(s.match(/!important/g)||[]).length;console.log('kurung',o,c,'!important',i);"
```
Expected: kurung seimbang (37 37), `!important` tetap 4.

- [ ] **Step 3: Jalankan suite → LULUS**

Run: `php artisan test`
Expected: PASS semua (view test tak terpengaruh; nama berkas CSS sama).

- [ ] **Step 4: Commit**

```bash
git add public/css/tabulator-agenda.css
git commit -m "refactor(tabulator): scope CSS ke kelas .doc-tabulator (dari id operator)"
```

> Regresi gaya (bila spesifisitas jebol) hanya terlihat di browser — dibuktikan di Task 4 QA, bukan di sini.

---

### Task 4: Sapuan verifikasi akhir + serah-terima QA

**Files:** — (gerbang akhir, tanpa perubahan kode)

**Interfaces:**
- Consumes: hasil Task 1-3.
- Produces: bukti verifikasi + checklist QA untuk user.

- [ ] **Step 1: Gerbang grep spec §6 (semua KOSONG)**

Run:
```bash
grep -n "operatorTabulatorTable" public/css/tabulator-agenda.css
grep -n "operatorTabulatorTable" public/js/document-tabulator.js
grep -rn "OPERATOR_TABULATOR_CONFIG\|operator-tabulator\.js" resources/ public/
```
Expected: ketiganya KOSONG. (Id `operatorTabulatorTable` di Blade operator BOLEH tetap — itu instance-id, bukan pelanggaran; perintah di atas tidak menyasar Blade.)

- [ ] **Step 2: Sanity JS + suite penuh**

Run: `node --check public/js/document-tabulator.js && echo OK`
Expected: `OK`.
Run: `php artisan test`
Expected: PASS semua; catat `Tests: N passed`.

- [ ] **Step 3: Serahkan QA visual operator ke user (WAJIB sebelum deploy)**

Laporkan ke user untuk uji di browser pada `/documents` (agent tak punya sesi login). Semua harus **identik** dengan sebelum refaktor:
1. Tabel tampil dengan gaya benar (header navy, garis, font Source Sans Pro) — bukti spesifisitas CSS tak jebol.
2. Sel aktif + panah menggeser + tabel ikut menggulir; klik memindah sel.
3. Enter/dobel-klik mulai edit; Enter simpan; Esc batal.
4. Ctrl+C popup "Disalin"; Ctrl+V tempel; Delete/Backspace kosongkan.
5. Blok (drag/Shift), Ctrl+Z/Y.
6. Lebar kolom yang disesuaikan bertahan setelah reload.
7. Ubah Kriteria → Sub/Item reset; Delete di Kriteria → anak ikut kosong.
8. Dropdown Pengurus → Team Verifikasi memindah dokumen; Tambah Baris; Hapus baris.
9. Kolom beku (No, Nomor Agenda) tetap menempel & outline sel aktif tebal saat digulir.

- [ ] **Step 4: Deploy SETELAH user konfirmasi QA lolos**

```bash
git push origin codinggemini
# di server:
git pull
php artisan route:clear && php artisan view:clear && php artisan config:clear
```

> Clear cache tidak boleh dilewat. `Asset::versioned` mem-bust cache; berkas JS baru `document-tabulator.js` membawa `?v=<mtime>`.

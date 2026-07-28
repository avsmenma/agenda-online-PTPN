# Ekstrak Modal Kustomisasi Kolom ke Partial + JS Bersama — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Satukan modal "Kustomisasi Kolom" yang tersalin verbatim di 4 view Tabulator role (operator/akutansi/perpajakan/verifikasi) menjadi SATU partial `partials/_columnCustomizationModal.blade.php` + SATU file `public/js/column-customization.js`.

**Architecture:** Ekstrak markup+CSS-terang modal ke partial (data lewat jembatan `window.COLUMN_CUSTOMIZATION_CONFIG`, pola `DOCUMENT_TABULATOR_CONFIG`) + ~260 baris JS ke file statis `public/js` (dimuat `Asset::versioned()`, `@vite` mati). `appendActiveFilterInputs()` digeneralisasi (bawa semua `[name]` di `.tabulator-toolbar`) sehingga tak ada lagi variasi per-role. Tiap view tetap punya `#filterForm` (route per-role) + toolbar sendiri.

**Tech Stack:** Laravel 12 Blade, PHPUnit (`Tests\TestCase`), JS statis `public/js` + `App\Support\Asset::versioned()`, QA Playwright MCP.

## Global Constraints

- Bahasa: UI/komentar Indonesia, identifier English. Pesan commit Bahasa Indonesia.
- `git add` per-file — JANGAN `git add .` / `git add -A`.
- Satu commit = satu perubahan logis.
- **Behavior-preserving**: perilaku modal tiap role tak berubah (buka, centang/urut kolom, preview, Simpan→reload GET dgn kolom baru, filter toolbar tetap terbawa).
- **Cakupan 4 role saja**: operator/akutansi/perpajakan/verifikasi. **JANGAN sentuh pembayaran** (`pembayaranNEW/dashboardPembayaran.blade.php`) — modal superset 2-tab/frozen, di luar cakupan.
- **JANGAN sentuh** dark-CSS modal global di `layouts/app.blade.php`, toolbar `.tabulator-toolbar`, `#filterForm`, `window.DOCUMENT_TABULATOR_CONFIG`, controller/route, `document-role-filter-toolbar.blade.php`.
- File JS statis: nol ketergantungan Blade (semua data lewat `window.*`/DOM).
- `php artisan test` wajib hijau sebelum tiap commit.
- Gerbang kritis §6 (lintas-role tabel kerja): ekstraksi wajib diff-verifikasi identitas sebelum menghapus; re-QA per-role pasca-ubah.

**Sumber kebenaran desain:** `docs/superpowers/specs/2026-07-28-ekstrak-modal-kustomisasi-kolom-design.md`.

---

## File Structure

- **Create** `resources/views/partials/_columnCustomizationModal.blade.php` — markup modal `#columnCustomizationModal` + CSS terang modal (inline `<style>`, pola `_infoCards`) + jembatan config `window.COLUMN_CUSTOMIZATION_CONFIG`. Konsumsi `$availableColumns`, `$selectedColumns`.
- **Create** `public/js/column-customization.js` — ~260 baris JS modal (dari view akutansi), dgn 2 modifikasi: baca data dari `window.COLUMN_CUSTOMIZATION_CONFIG`, `appendActiveFilterInputs()` generik.
- **Create** `tests/Feature/ColumnCustomizationSharedTest.php` — render test partial + kehadiran modal/JS di view role, ketiadaan JS inline lama.
- **Modify** `resources/views/akutansi/dokumens/daftarAkutansiTabulator.blade.php` — hapus modal inline (markup+CSS+JS), pakai partial + `<script src>`.
- **Modify** `resources/views/perpajakan/dokumens/daftarPerpajakanTabulator.blade.php` — idem.
- **Modify** `resources/views/team_verifikasi/dokumens/daftarDokumenTabulator.blade.php` — idem.
- **Modify** `resources/views/operator/dokumens/daftarDokumenTabulator.blade.php` — hapus HANYA fungsi modal (sisakan JS operator-only: inline-create, hapus baris), pakai partial + `<script src>`.

**Sumber kanonik ekstraksi = view akutansi** (`daftarAkutansiTabulator.blade.php`) — modal-only, paling bersih. Nomor baris di bawah merujuk file akutansi per 2026-07-28.

---

## Task 1: Partial `_columnCustomizationModal` + file JS `column-customization.js`

**Files:**
- Create: `resources/views/partials/_columnCustomizationModal.blade.php`
- Create: `public/js/column-customization.js`
- Test: `tests/Feature/ColumnCustomizationSharedTest.php`

**Interfaces:**
- Consumes: var view `$availableColumns` (array `key=>label`), `$selectedColumns` (array key terpilih) — sudah dikirim tiap view role.
- Produces:
  - Partial `partials._columnCustomizationModal` (include nol-argumen; baca `$availableColumns`/`$selectedColumns`). Mengeluarkan `<div id="columnCustomizationModal">`, `<style>` CSS terang modal, dan `<script>window.COLUMN_CUSTOMIZATION_CONFIG = {availableColumns, selected}</script>`.
  - File `public/js/column-customization.js` mendefinisikan global: `openColumnCustomizationModal()`, `closeColumnCustomizationModal()`, `toggleColumn()`, `selectAllColumns()`, `removeAllColumns()`, `saveColumnCustomization()`, dan `appendActiveFilterInputs()` (generik). Membaca `window.COLUMN_CUSTOMIZATION_CONFIG`.

- [ ] **Step 1: Tulis test yang gagal**

Buat `tests/Feature/ColumnCustomizationSharedTest.php`:

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Kontrak partial bersama partials._columnCustomizationModal + file JS
 * public/js/column-customization.js (dipakai 4 view Tabulator role).
 */
class ColumnCustomizationSharedTest extends TestCase
{
    public function test_partial_merender_modal_dan_jembatan_config(): void
    {
        $html = view('partials._columnCustomizationModal', [
            'availableColumns' => ['nomor_agenda' => 'Nomor Agenda', 'no_spp' => 'No SPP'],
            'selectedColumns'  => ['nomor_agenda'],
        ])->render();

        // Markup modal ada.
        $this->assertStringContainsString('id="columnCustomizationModal"', $html);
        // Jembatan data (menggantikan @json inline di tiap view).
        $this->assertStringContainsString('window.COLUMN_CUSTOMIZATION_CONFIG', $html);
        // Kolom dari $availableColumns dirender sebagai item.
        $this->assertStringContainsString('Nomor Agenda', $html);
        $this->assertStringContainsString('data-column="no_spp"', $html);
    }

    public function test_file_js_bersama_ada_dan_berisi_fungsi_inti(): void
    {
        $path = public_path('js/column-customization.js');
        $this->assertFileExists($path);
        $js = file_get_contents($path);

        $this->assertStringContainsString('function openColumnCustomizationModal', $js);
        $this->assertStringContainsString('function appendActiveFilterInputs', $js);
        // Baca data dari jembatan window, BUKAN @json Blade (file statis).
        $this->assertStringContainsString('COLUMN_CUSTOMIZATION_CONFIG', $js);
        $this->assertStringNotContainsString('@json', $js);
    }
}
```

- [ ] **Step 2: Jalankan test untuk memastikan gagal**

Run: `php artisan test --filter=ColumnCustomizationSharedTest`
Expected: FAIL — `View [partials._columnCustomizationModal] not found` + file JS tak ada.

- [ ] **Step 3: Buat partial**

Buat `resources/views/partials/_columnCustomizationModal.blade.php`. Isi = **pindahkan verbatim** blok markup modal dari view akutansi (`daftarAkutansiTabulator.blade.php`) baris **79–187** (dari `<div class="customization-modal" id="columnCustomizationModal">` sampai `</div>` penutupnya), LALU tambahkan CSS terang modal (akutansi baris **202–271**, dari komentar `/* Modal Customization Styles ... */` sampai akhir blok `@media (max-width: 768px) { … }`) dalam `<style>`, dan jembatan config. Struktur akhir partial:

```blade
{{--
  Modal Kustomisasi Kolom bersama — dipakai 4 view Tabulator role
  (operator/akutansi/perpajakan/verifikasi). Data lewat window.COLUMN_CUSTOMIZATION_CONFIG;
  logika di public/js/column-customization.js. Dark-mode CSS ada global di layouts/app.
  Lihat spec docs/superpowers/specs/2026-07-28-ekstrak-modal-kustomisasi-kolom-design.md.
--}}
<div class="customization-modal" id="columnCustomizationModal">
    {{-- … PINDAHKAN VERBATIM markup akutansi baris 79–187 (isi <div id="columnCustomizationModal">) … --}}
</div>

<style>
    /* Modal Customization Styles — dipindah dari view role (self-contained, dark-mode global di layouts/app). */
    /* … PINDAHKAN VERBATIM CSS akutansi baris 202–271 (mulai .customization-modal { … } s/d blok @media 768px) … */
</style>

<script>
    // Jembatan data Blade→JS (pola window.DOCUMENT_TABULATOR_CONFIG). Dibaca column-customization.js.
    window.COLUMN_CUSTOMIZATION_CONFIG = {
        availableColumns: @json($availableColumns),
        selected: @json(array_values($selectedColumns)),
    };
</script>
```

CATATAN: markup memakai `@foreach($availableColumns …)`, `@foreach($selectedColumns …)`, `count($selectedColumns)` — semua var view yang sudah ada; pindahkan apa adanya. JANGAN sertakan `<link>` font/CSS Tabulator (itu milik view). JANGAN sertakan `.tabulator-toolbar` CSS (akutansi 198–200) — itu toolbar, tetap di view.

- [ ] **Step 4: Buat file JS bersama**

Buat `public/js/column-customization.js`. Isi = **pindahkan verbatim** SELURUH isi `<script>` blok "Kustomisasi Kolom" dari view akutansi (baris **286 `<script>` s/d 546**, sebelum `</script>` baris 547) — yaitu semua fungsi modal (`openColumnCustomizationModal`, `closeColumnCustomizationModal`, `toggleColumn`, `selectAllColumns`, `removeAllColumns`, `initializeModalState`, `updateColumnOrderBadges`, `updatePreviewTable`, `updateSelectedCount`, `updateDraggableState`, `initializeDragAndDrop`, `saveColumnCustomization`, listener `DOMContentLoaded`/MutationObserver, dll) — DENGAN DUA MODIFIKASI:

**Modifikasi A — sumber data (ganti akutansi baris 288–292):**

```js
// GANTI blok Blade lama:
//   let selectedColumnsOrder = [];
//   let availableColumnsData = @json($availableColumns);
//   @if(count($selectedColumns) > 0) selectedColumnsOrder = @json(array_values($selectedColumns)); @endif
// MENJADI (baca jembatan window, nol Blade):
var __CCCFG = window.COLUMN_CUSTOMIZATION_CONFIG || { availableColumns: {}, selected: [] };
let availableColumnsData = __CCCFG.availableColumns || {};
let selectedColumnsOrder = Array.isArray(__CCCFG.selected) ? __CCCFG.selected.slice() : [];
```

**Modifikasi B — `appendActiveFilterInputs()` generik (ganti akutansi baris 412–427):**

```js
// Bawa SEMUA filter toolbar aktif (generik lintas-role) agar tak hilang saat reload GET.
// Menggantikan versi lama yang hardcode nama field per-role (operator: year/status_filter,
// keuangan: status/filter_dari). Tiap toolbar hanya memuat field-nya sendiri, jadi
// perilaku per-role tetap identik (behavior-preserving).
function appendActiveFilterInputs(filterForm) {
    const toolbar = document.querySelector('.tabulator-toolbar');
    if (!toolbar) return;
    const controls = toolbar.querySelectorAll('input[name], select[name]');
    const names = new Set();
    controls.forEach(el => { if (el.name) names.add(el.name); });
    // Buang input lama bernama sama agar tak dobel saat reload GET.
    names.forEach(name => {
        filterForm.querySelectorAll('input[name="' + name.replace(/"/g, '\\"') + '"]').forEach(i => i.remove());
    });
    controls.forEach(el => {
        if ((el.type === 'checkbox' || el.type === 'radio') && !el.checked) return;
        if (el.value === '' || el.value == null) return;
        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = el.name;
        hidden.value = el.value;
        filterForm.appendChild(hidden);
    });
}
```

Selain dua blok itu, JS dipindah **apa adanya**. Pastikan file TIDAK memuat token Blade (`@json`, `@if`, `{{ }}`) — test Step 1 menegakkan ini.

- [ ] **Step 5: Jalankan test untuk memastikan lulus**

Run: `php artisan test --filter=ColumnCustomizationSharedTest`
Expected: PASS (2 test).

- [ ] **Step 6: Suite penuh hijau + commit**

Run: `php artisan test` → semua hijau (baseline 248).

```bash
git add resources/views/partials/_columnCustomizationModal.blade.php
git add public/js/column-customization.js
git add tests/Feature/ColumnCustomizationSharedTest.php
git commit -m "feat(ui): partial + JS bersama modal kustomisasi kolom (sumber akutansi)"
```

---

## Task 2: Adopsi partial+JS di 3 view keuangan (akutansi/perpajakan/verifikasi)

**Files:**
- Modify: `resources/views/akutansi/dokumens/daftarAkutansiTabulator.blade.php`
- Modify: `resources/views/perpajakan/dokumens/daftarPerpajakanTabulator.blade.php`
- Modify: `resources/views/team_verifikasi/dokumens/daftarDokumenTabulator.blade.php`

**Interfaces:**
- Consumes: partial `partials._columnCustomizationModal` + `public/js/column-customization.js` (Task 1).
- Produces: 3 view keuangan tanpa modal inline; memakai `@include` + `<script src>`.

- [ ] **Step 1: Diff-verifikasi blok modal ketiga view identik dgn sumber**

Sebelum menghapus, pastikan blok modal (markup+CSS+JS) di perpajakan & verifikasi identik dengan akutansi kecuali beda yang diketahui (komentar, nama route filterForm, `appendActiveFilterInputs` hardcoded — semuanya digantikan JS bersama). Jalankan:

Run: `diff resources/views/akutansi/dokumens/daftarAkutansiTabulator.blade.php resources/views/perpajakan/dokumens/daftarPerpajakanTabulator.blade.php`
Run: `diff resources/views/akutansi/dokumens/daftarAkutansiTabulator.blade.php resources/views/team_verifikasi/dokumens/daftarDokumenTabulator.blade.php`
Expected: perbedaan hanya di area config/toolbar/status/comment/appendActiveFilterInputs — BUKAN di fungsi modal inti. Bila ada drift fungsi modal, catat & rekonsiliasi (fungsi bersama sudah dari akutansi; drift berarti view itu perlu perhatian ekstra — laporkan sebagai concern).

- [ ] **Step 2: Ganti markup modal → `@include` (tiap view)**

Di masing-masing 3 view, GANTI seluruh blok `<div class="customization-modal" id="columnCustomizationModal"> … </div>` (beserta komentar `{{-- ==== Modal Kustomisasi Kolom … ==== --}}` di atasnya) dengan:

```blade
    @include('partials._columnCustomizationModal')
```

BIARKAN baris `<form action="{{ route('documents.<role>.index') }}" method="GET" id="filterForm" class="d-none"></form>` (tepat di atas modal) TETAP — itu `#filterForm` per-role.

- [ ] **Step 3: Hapus CSS modal dari `@push('styles')` (tiap view)**

Di blok `<style>` tiap view, HAPUS blok CSS modal: dari komentar `/* Modal Customization Styles … */` sampai akhir blok `@media (max-width: 768px) { … }` (di akutansi = baris 202–271). BIARKAN CSS toolbar (`.tabulator-toolbar { … }`, `.tabulator-toolbar-search { … }`) dan `<link>` font/CSS tetap.

- [ ] **Step 4: Ganti JS modal inline → `<script src>` (tiap view)**

Di tiap view, temukan blok `@push('scripts')` KEDUA (yang berkomentar "Kustomisasi Kolom", berisi `<script>` dgn `openColumnCustomizationModal` dst). GANTI SELURUH isi `<script> … </script>` inline itu dengan pemuatan file bersama:

```blade
@push('scripts')
    {{-- Modal Kustomisasi Kolom — partial partials._columnCustomizationModal + JS bersama.
         window.COLUMN_CUSTOMIZATION_CONFIG diset partial (di atas), jadi dimuat sebelum file ini. --}}
    <script src="{{ \App\Support\Asset::versioned('js/column-customization.js') }}"></script>
@endpush
```

JANGAN sentuh blok `@push('scripts')` PERTAMA (`window.DOCUMENT_TABULATOR_CONFIG` + tabulator.min.js + document-tabulator.js) — itu engine tabel, beda fitur.

- [ ] **Step 5: Tambah assertion adopsi ke test**

Tambahkan ke `tests/Feature/ColumnCustomizationSharedTest.php` sebuah test yang, untuk role akutansi/perpajakan/verifikasi, memuat halaman index-nya dan menegakkan modal via partial + JS bersama hadir, serta JS inline lama HILANG. Ikuti pola `actingAs` + route pada test yang sudah ada (mis. `AkutansiDatatableTest`). Contoh (sesuaikan nama route/role bila perlu):

```php
    public function test_view_keuangan_memakai_modal_bersama(): void
    {
        $cases = [
            ['role' => 'akutansi',        'route' => 'documents.akutansi.index'],
            ['role' => 'perpajakan',      'route' => 'documents.perpajakan.index'],
            ['role' => 'team_verifikasi', 'route' => 'documents.verifikasi.index'],
        ];
        foreach ($cases as $c) {
            $user = \App\Models\User::factory()->create(['role' => $c['role']]);
            $res = $this->actingAs($user)->get(route($c['route']));
            $res->assertOk();
            // Modal via partial + JS bersama hadir.
            $res->assertSee('id="columnCustomizationModal"', false);
            $res->assertSee('js/column-customization.js', false);
            $res->assertSee('window.COLUMN_CUSTOMIZATION_CONFIG', false);
            // JS modal inline lama sudah tidak ada (bukti ekstraksi).
            $res->assertDontSee('let availableColumnsData =', false);
        }
    }
```

> Bila query index butuh polyfill SQLite (REGEXP/SUBSTRING_INDEX) seperti `OperatorTabulatorViewTest::setUp`, salin polyfill `setUp` itu ke test ini agar hijau di SQLite.

- [ ] **Step 6: Jalankan test + suite + commit per-file**

Run: `php artisan view:clear && php artisan test`
Expected: semua hijau, termasuk assertion adopsi baru.

```bash
git add resources/views/akutansi/dokumens/daftarAkutansiTabulator.blade.php
git add resources/views/perpajakan/dokumens/daftarPerpajakanTabulator.blade.php
git add resources/views/team_verifikasi/dokumens/daftarDokumenTabulator.blade.php
git add tests/Feature/ColumnCustomizationSharedTest.php
git commit -m "refactor(keuangan): 3 view Tabulator pakai modal kustomisasi kolom bersama"
```

---

## Task 3: Adopsi di view operator (bedah hati-hati — sisakan JS operator-only)

**Files:**
- Modify: `resources/views/operator/dokumens/daftarDokumenTabulator.blade.php`

**Interfaces:**
- Consumes: partial + JS bersama (Task 1).
- Produces: view operator tanpa fungsi modal inline; JS operator-only (inline-create baris, hapus baris) TETAP.

> **Kenapa terpisah:** view operator mencampur JS modal dengan JS khusus operator (tambah baris inline `btnTambahBarisInline`, hapus baris aktif `btnHapusBarisAktif`, dll). Fungsi modal PINDAH ke JS bersama; fungsi operator-only WAJIB tetap.

- [ ] **Step 1: Petakan blok modal vs operator-only di view operator**

Baca view operator. Identifikasi (a) markup `#columnCustomizationModal`, (b) CSS modal `.customization-modal … @media 768px`, (c) fungsi JS modal (nama SAMA dengan yang kini di `public/js/column-customization.js`: `openColumnCustomizationModal`/`closeColumnCustomizationModal`/`toggleColumn`/`selectAllColumns`/`removeAllColumns`/`saveColumnCustomization`/`initializeModalState`/`updateColumnOrderBadges`/`updatePreviewTable`/`updateSelectedCount`/`updateDraggableState`/`initializeDragAndDrop`/`appendActiveFilterInputs` + listener modal), versus (d) JS operator-only (apa pun yang menyebut `btnTambahBarisInline`/`btnHapusBarisAktif`/inline-create/hapus baris — JANGAN dihapus).

Run: `grep -nE "btnTambahBarisInline|btnHapusBarisAktif|function (openColumnCustomizationModal|saveColumnCustomization|appendActiveFilterInputs|toggleColumn)" resources/views/operator/dokumens/daftarDokumenTabulator.blade.php`

Catat batas fungsi modal (yang akan dihapus) dan pastikan fungsi operator-only berada di luar batas itu.

- [ ] **Step 2: Ganti markup modal → `@include`; hapus CSS modal**

Seperti Task 2 Step 2 & 3: GANTI blok `<div id="columnCustomizationModal"> … </div>` (+ komentar) dgn `@include('partials._columnCustomizationModal')`; BIARKAN `#filterForm` operator. HAPUS blok CSS modal `.customization-modal … @media 768px` dari `<style>` operator (BIARKAN CSS toolbar & operator lain).

- [ ] **Step 3: Hapus HANYA fungsi modal dari JS operator; muat JS bersama**

Di blok script operator: HAPUS definisi fungsi-fungsi modal (daftar di Step 1) beserta init state modal-nya (`let selectedColumnsOrder`/`availableColumnsData` + `@json` init). BIARKAN semua fungsi operator-only. Tambahkan pemuatan JS bersama (sekali):

```blade
    <script src="{{ \App\Support\Asset::versioned('js/column-customization.js') }}"></script>
```

Tempatkan `<script src>` ini SETELAH partial menetapkan `window.COLUMN_CUSTOMIZATION_CONFIG` (partial berada di section content, sebelum `@push('scripts')` yang dirender di akhir body — urutan aman). Pastikan tak ada duplikasi definisi (fungsi modal kini hanya di file bersama).

- [ ] **Step 4: Tambah assertion operator ke test**

Tambah ke `ColumnCustomizationSharedTest` sebuah test operator: index operator memuat modal bersama + JS bersama, JS modal inline hilang, TAPI fitur operator-only tetap ada.

```php
    public function test_view_operator_pakai_modal_bersama_tanpa_hilangkan_fitur_operator(): void
    {
        // Polyfill SQLite bila perlu (lihat OperatorTabulatorViewTest::setUp).
        $user = \App\Models\User::factory()->create(['role' => 'operator']);
        $res = $this->actingAs($user)->get(route('documents.index'));
        $res->assertOk();
        $res->assertSee('id="columnCustomizationModal"', false);
        $res->assertSee('js/column-customization.js', false);
        $res->assertDontSee('let availableColumnsData =', false);
        // Fitur operator-only WAJIB tetap.
        $res->assertSee('id="btnHapusBarisAktif"', false);
    }
```

> Salin polyfill SQLite dari `OperatorTabulatorViewTest::setUp` ke `ColumnCustomizationSharedTest::setUp` (index operator memakai REGEXP/SUBSTRING_INDEX).

- [ ] **Step 5: Jalankan test + suite + commit**

Run: `php artisan view:clear && php artisan test`
Expected: semua hijau; `OperatorTabulatorViewTest` (jaring detail/hapus operator) tetap lolos.

```bash
git add resources/views/operator/dokumens/daftarDokumenTabulator.blade.php
git add tests/Feature/ColumnCustomizationSharedTest.php
git commit -m "refactor(operator): view Tabulator pakai modal kustomisasi kolom bersama (sisakan JS operator-only)"
```

---

## Task 4: Verifikasi integrasi + grep-gate + QA (gerbang sebelum deploy)

**Files:** (tak ada perubahan kode — gerbang verifikasi)

**Interfaces:**
- Consumes: hasil Task 1–3.
- Produces: keputusan lolos/gagal untuk gerbang deploy user (§5/§6).

- [ ] **Step 1: Suite penuh hijau**

Run: `php artisan test`
Expected: semua hijau (termasuk `ColumnCustomizationSharedTest` + `OperatorTabulatorViewTest`).

- [ ] **Step 2: Grep-gate — modal tak lagi didefinisikan inline di 4 view**

Run: `git grep -nE "function openColumnCustomizationModal|let availableColumnsData|Modal Customization Styles" -- resources/views/operator resources/views/akutansi resources/views/perpajakan resources/views/team_verifikasi`
Expected: **nol hasil** (definisi modal hanya di partial + `public/js/column-customization.js`).

Run: `git grep -nl "customization-modal" -- resources/views/pembayaranNEW`
Expected: pembayaran MASIH punya modal sendiri (di luar cakupan — pastikan TIDAK ikut terhapus).

- [ ] **Step 3: QA interaksi (Playwright, produksi pasca-deploy)**

Untuk tiap role yang bisa login (**akutansi** `akutansi`/`akuntansi`, **perpajakan** `perpajakan`/`pajak`, **verifikasi** — pw `12345678`; **operator** bila kredensial diberikan): buka halaman daftar → klik tombol Kustomisasi Kolom → modal terbuka → centang/hapus 1 kolom → Simpan → verifikasi tabel me-reload dgn kolom baru DAN filter toolbar (search/status) TETAP terbawa (tak reset). Nyatakan jujur role mana yang belum bisa di-QA (operator bila tanpa kredensial).

- [ ] **Step 4: Serahkan ke user untuk gerbang deploy**

Laporkan hasil apa adanya. JANGAN deploy tanpa persetujuan user. Setelah disetujui (§5):

```bash
git push origin codinggemini
# server:
git pull
php artisan route:clear && php artisan view:clear && php artisan config:clear
```

---

## Self-Review

**1. Spec coverage:**
- Spec §2.1 cakupan 4 role, pembayaran dilewati → Global Constraints + Task 4 Step 2 (gate pembayaran utuh). ✓
- Spec §2.2 ekstrak (partial + JS) → Task 1. ✓
- Spec §2.3 jembatan `window.COLUMN_CUSTOMIZATION_CONFIG` → Task 1 Step 3/4 (Modifikasi A). ✓
- Spec §2.4 `appendActiveFilterInputs` generik → Task 1 Step 4 (Modifikasi B). ✓
- Spec §2.5 `#filterForm`/toolbar per-role tetap → Task 2 Step 2/4, Task 3 Step 2/3. ✓
- Spec §3.1 partial (markup+CSS+bridge) → Task 1 Step 3. ✓
- Spec §3.2 file JS statis nol-Blade → Task 1 Step 4 + test `assertStringNotContainsString('@json')`. ✓
- Spec §3.3 4 view pakai include+script → Task 2 (keuangan) + Task 3 (operator). ✓
- Spec §4 pembayaran/dark-CSS/toolbar/document-role-filter-toolbar tak disentuh → Global Constraints + Task 4 gate. ✓
- Spec §5 uji (suite + render test + assertSee + QA) → Task 1/2/3 test + Task 4. ✓
- Spec §6 risiko (diff-verifikasi, generik, jembatan) → Task 2 Step 1, Task 3 Step 1. ✓

**2. Placeholder scan:** Kode NEW (config bridge, `appendActiveFilterInputs` generik, include, script src, semua test) ditulis penuh. Bagian bulk (markup/CSS/JS lama) diinstruksikan "pindah verbatim dari baris X–Y berjangkar string unik" — instruksi ekstraksi presisi ke sumber nyata, bukan placeholder. ✓

**3. Type consistency:** Nama partial `partials._columnCustomizationModal`, global `window.COLUMN_CUSTOMIZATION_CONFIG`, file `public/js/column-customization.js`, fungsi `openColumnCustomizationModal`/`appendActiveFilterInputs`/`saveColumnCustomization` konsisten di Task 1–3 dan test. Field bridge `availableColumns`/`selected` sama antara partial (Task 1 Step 3) dan pembaca JS (Task 1 Step 4 Modifikasi A). ✓

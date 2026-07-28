# Desain: Ekstrak Modal Kustomisasi Kolom ke Partial + JS Bersama (4 role)

- **Tanggal:** 2026-07-28
- **Status:** Disetujui user (2026-07-28)
- **Cakupan:** Modal "Kustomisasi Kolom" yang disalin-tempel di 4 view Tabulator role
  (operator/akutansi/perpajakan/verifikasi) disatukan ke SATU partial + SATU file JS bersama.
  Pembayaran SENGAJA di luar cakupan (modal superset 2-tab/frozen di god-file — dikerjakan terpisah).
  Ini "point 1" dari rencana penyatuan lebih besar (point 2 = satukan seluruh view Tabulator).

---

## 1. Masalah

Modal Kustomisasi Kolom = **markup + CSS terang + ~260 baris JS**, disalin **verbatim** di 4 view:
`operator/dokumens/daftarDokumenTabulator`, `akutansi/dokumens/daftarAkutansiTabulator`,
`perpajakan/dokumens/daftarPerpajakanTabulator`, `team_verifikasi/dokumens/daftarDokumenTabulator`.
Komentar kodenya sendiri mengakui utang ini (mis. *"disalin dari daftarDokumenTabulator... (utang
de-dup §7)"*). Bahkan ada sisa copy-paste yang tak sempat di-rename (formatter `akutansiStatus`
dipakai di view perpajakan).

Dampak: mengubah 1 perilaku fitur kustomisasi kolom = mengedit 4 file. Persis "penyakit utama"
duplikasi (§1 CLAUDE.md). Kemiripan antar-view: akutansi vs perpajakan ~92% identik, akutansi vs
verifikasi ~86%.

**Fakta pendukung (hasil audit kode 2026-07-28):**
- Dark-mode CSS modal (`.dark .customization-modal …`) **sudah global** di `layouts/app.blade.php`
  (~baris 1035-1436) — TIDAK ikut diduplikasi, jadi TIDAK disentuh.
- Ke-4 modal role adalah versi **simpel** (hanya seleksi kolom; nol `column-tab`/`frozen-*`).
  Pembayaran punya versi **superset** (2-tab + Kolom Beku, 16 hit) di god-file — beda + berisiko.
- JS modal punya ketergantungan Blade: `let availableColumnsData = @json($availableColumns)` dan
  `selectedColumnsOrder = @json(array_values($selectedColumns))`. Jadi file JS statis wajib membaca
  data lewat jembatan `window.*` (pola sama seperti `window.DOCUMENT_TABULATOR_CONFIG` yang sudah ada).
- Satu-satunya variasi JS per-role: `appendActiveFilterInputs()` — daftar nama field filter yang
  dibawa saat reload GET berbeda (operator: `search`/`year`/`status_filter`; keuangan:
  `search`/`status`/`filter_dari`).

## 2. Keputusan desain (disetujui)

1. **Cakupan: 4 role, pembayaran DILEWATI** (keputusan user 2026-07-28). Pembayaran = program terpisah.
2. **Ekstrak, bukan salin.** Modal → 1 partial `partials/_columnCustomizationModal.blade.php`
   (markup + CSS terang) + 1 file `public/js/column-customization.js` (logika ~260 baris).
3. **Jembatan data `window.COLUMN_CUSTOMIZATION_CONFIG`** (pola `DOCUMENT_TABULATOR_CONFIG`): partial
   mengeluarkan `<script>window.COLUMN_CUSTOMIZATION_CONFIG = { availableColumns: @json(...),
   selected: @json(...) }</script>`; file JS statis membaca dari sana (menggantikan `@json` inline).
4. **`appendActiveFilterInputs()` dibuat GENERIK** — membawa SEMUA `input[name]`/`select[name]` dari
   toolbar aktif (bukan daftar field hardcoded). Ini menghapus satu-satunya variasi per-role; superset
   nama field tetap benar untuk operator maupun keuangan.
5. **Tiap view tetap punya `#filterForm` sendiri** (action route per-role) dan toolbar-nya sendiri —
   keduanya DI LUAR cakupan. Modal JS cukup bergantung pada keberadaan `#filterForm` (kontrak lama).

## 3. Arsitektur

### 3.1 Partial baru `resources/views/partials/_columnCustomizationModal.blade.php`
- Berisi: markup `#columnCustomizationModal` (seleksi kolom, drag-reorder, preview, footer) +
  **CSS terang** modal (kelas `.customization-modal`, `.modal-content-custom`, `.column-item`,
  `.preview-table`, dst) dibungkus **`@once`** (tak dobel walau ke-include >1×).
- Mengonsumsi var yang SUDAH dikirim tiap view: `$availableColumns` (peta key⇒label) & `$selectedColumns`
  (array key terpilih). Loop markup (`@foreach($availableColumns …)`, `@foreach($selectedColumns …)`)
  dipindah apa adanya dari view.
- Mengeluarkan jembatan config sekali:
  `<script>window.COLUMN_CUSTOMIZATION_CONFIG = { availableColumns: @json($availableColumns),
  selected: @json(array_values($selectedColumns)) };</script>`.
- **Kontrak include:** `@include('partials._columnCustomizationModal')` — nol argumen wajib tambahan
  (baca var view yang sudah ada). Dark-CSS TIDAK dibawa (sudah global di layout).

### 3.2 File baru `public/js/column-customization.js`
- Isi ~260 baris JS yang sekarang inline (open/close modal, drag-reorder, tab/preview, render daftar
  kolom, `applyColumnCustomization()` yang submit `#filterForm`, dll), **dipindah apa adanya** kecuali:
  - Sumber data: `availableColumnsData`/`selectedColumnsOrder` dibaca dari
    `window.COLUMN_CUSTOMIZATION_CONFIG` (bukan `@json` inline).
  - `appendActiveFilterInputs(filterForm)` ditulis generik: hapus input filter lama di `#filterForm`,
    lalu untuk SETIAP `.tabulator-toolbar [name]` (input/select) yang punya nilai, tambahkan hidden
    input `name=value` ke `#filterForm`. Tak ada daftar field hardcoded.
- Dimuat via `@push('scripts')` `<script src="{{ \App\Support\Asset::versioned('js/column-customization.js') }}"></script>`
  dibungkus `@once` (satu kali per halaman). Konsisten dengan `document-tabulator.js` + `@vite` mati.
- **Nol ketergantungan Blade** setelah pindah (semua data lewat `window.*`/DOM).

### 3.3 Empat view role — pakai partial + file JS
Di tiap dari 4 view: **HAPUS** blok modal inline (markup + CSS terang + ~260 baris JS) dan **GANTI** dengan:
- `@include('partials._columnCustomizationModal')` di posisi markup modal lama.
- `@push('scripts') <script src="…js/column-customization.js"></script> @endpush` (via `@once` di partial
  JS-loader, atau langsung — implementasi menentukan; intinya dimuat sekali).
- **Tetap:** `@php $configArray = […] @endphp` + `window.DOCUMENT_TABULATOR_CONFIG` (Tabulator, beda fitur),
  `#filterForm` (route per-role), toolbar `.tabulator-toolbar`, tombol pemicu `onclick="openColumnCustomizationModal()"`.

> **Prasyarat implementasi:** sebelum ekstraksi, **diff-verifikasi** blok modal ke-4 view identik
> (markup+CSS+JS). Bila ada drift kecil (mis. komentar salin, formatter salah-rename), rekonsiliasi ke
> satu versi kanonik saat ekstraksi. Ekstraksi wajib **behavior-preserving**: perilaku modal tiap role
> tak berubah.

## 4. Yang TIDAK berubah (jaring pengaman)
- **Pembayaran** (`pembayaranNEW/dashboardPembayaran.blade.php`) — modal superset di god-file, tak disentuh.
- **Dark-CSS global** modal di `layouts/app.blade.php` — sudah menyasar `.customization-modal`, tetap.
- **Toolbar & `#filterForm` per-role**, endpoint Tabulator, `window.DOCUMENT_TABULATOR_CONFIG`, DTO, query.
- **`document-role-filter-toolbar.blade.php`** (fondasi tak-terpakai) — di luar cakupan point 1.
- Controller & route — nol perubahan (partial hanya konsumsi var yang sudah dikirim).

## 5. Rencana pengujian
- **Backend:** `php artisan test` hijau. Test view Tabulator yang ada (mis. `OperatorTabulatorViewTest`)
  tetap lolos. Tambah assertion tiap dari 4 view masih merender modal (via partial) + memuat
  `column-customization.js` (mis. `assertSee('columnCustomizationModal', false)` +
  `assertSee('js/column-customization.js', false)`). Tambah cek berkas `public/js/column-customization.js`
  ada (pola `AssetTest`).
- **QA visual/interaksi (Playwright, produksi pasca-deploy):** buka modal → centang/hapus 1 kolom →
  Terapkan → tabel berubah kolom & filter aktif TETAP terbawa. Kredensial tersedia: **akutansi**
  (`akutansi`/`akuntansi`), **perpajakan** (`perpajakan`/`pajak`), **verifikasi** — pw `12345678`.
  **operator**: kredensial belum ada → dinyatakan jujur bila tak bisa di-QA mandiri (view operator
  paling banyak dipakai; mohon login operator saat QA).
- **Grep-gate:** setelah ekstraksi, blok modal inline (markup+CSS+JS) tak lagi ada di 4 view — hanya
  `@include` + `<script src>`. Pastikan `openColumnCustomizationModal`/`customization-modal` **tak lagi
  didefinisikan** di ke-4 view (hanya dirujuk lewat partial/JS bersama).

## 6. Risiko
- **Lintas-role tabel kerja (4 role, gerbang kritis §6).** Dimitigasi: ekstraksi behavior-preserving
  (JS dipindah apa adanya; hanya sumber-data + `appendActiveFilterInputs` yang digeneralisasi),
  diff-verifikasi identitas sebelum pindah, re-QA per-role.
- **Generalisasi `appendActiveFilterInputs`.** Membawa semua `[name]` toolbar = superset; tetap benar
  untuk operator (`year/status_filter`) & keuangan (`status/filter_dari`). Uji reload GET tiap role
  agar filter tak hilang.
- **Jembatan `window.COLUMN_CUSTOMIZATION_CONFIG`.** Bila ada data Blade lain yang terlewat (selain
  `availableColumns`/`selected`), JS statis bisa `undefined`. Mitigasi: Task pertama grep semua `@json`/
  `{{ }}` di region JS modal tiap view; semua data non-DOM wajib lewat jembatan.
- **`@vite` mati.** File JS di `public/js` + `Asset::versioned()` (URL ber-`?v=mtime`, sinkron cache
  nginx immutable) — konsisten pola `document-tabulator.js`, bukan utang baru.

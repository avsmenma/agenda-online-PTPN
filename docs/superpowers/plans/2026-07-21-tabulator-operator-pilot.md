# Migrasi Tabulator.js — Tabel Dokumen Operator (Pilot) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Ganti mesin tabel dokumen operator (`/documents`) dari `<table>`+virtual-chunk+inline-edit-engine lama menjadi Tabulator.js 6.x remote/AJAX, dengan paritas fitur penuh, di belakang flag fallback sementara.

**Architecture:** View baru **terpisah** (`daftarDokumenTabulator.blade.php`) berdampingan dengan view lama; `index()` bercabang pada flag `?classic=1` (default = Tabulator, `?classic=1` = tabel lama). Data via endpoint JSON baru `GET /documents/data` yang memakai ulang `buildOperatorQuery()` + kelas transform baris tunggal `OperatorDocumentRow`. Endpoint tulis lama (`inline-update`, `handler`, `detail`, `destroy`) TIDAK diubah; hanya `inlineCreate` ditambah objek baris JSON. View lama baru dihapus di tugas cleanup terakhir SETELAH QA visual user lolos.

**Tech Stack:** Laravel 12 / PHP 8.4 / MySQL 8, Blade, Tabulator.js 6.x (self-hosted `public/vendor/tabulator/`), vanilla JS, jQuery 3.7.1 (sudah ada), Bootstrap 5.3.3 (modal), PHPUnit (SQLite in-memory).

## Global Constraints

- **Bahasa:** UI & komentar domain Bahasa Indonesia; identifier/kode English. Nama method test Bahasa Indonesia, variabel lokal English (house style).
- **Git:** commit kecil & sering, pesan Bahasa Indonesia, `git add` per-file (JANGAN `git add .`/`-A`). Akhiri pesan commit dengan `Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>`.
- **Commit message multi-baris via `git commit -F - <<'EOF' ... EOF` (heredoc), BUKAN PowerShell here-string `@'...'@`.**
- **Keamanan:** kerja hanya di lokal/dev. Jangan sentuh server/DB produksi. Tanpa konfirmasi eksplisit: tanpa perintah destruktif DB.
- **Flag fallback:** `?classic=1` menyajikan view lama; default (tanpa flag) menyajikan Tabulator. View lama `daftarDokumen.blade.php` TIDAK diubah selama Tugas 1–7. Cleanup (hapus view lama + flag) HANYA di Tugas 8, dan Tugas 8 TIDAK dieksekusi sampai user menyatakan QA visual lolos.
- **Tanpa runtime CDN/npm untuk Tabulator:** file dist di-commit ke `public/vendor/tabulator/`. Deploy tetap `git pull` saja.
- **Paritas format WAJIB byte-faithful** terhadap `resources/views/operator/dokumens/_tableRowsAjax.blade.php` (rujukan baris disebut per formatter). Termasuk quirk yang ada: `nilai_rupiah` = `'Rp. '` (ada titik) via accessor, `dpp_pph`/`ppn_terhutang` = `'Rp '` (tanpa titik); badge "Belum Dikirim" memakai class `badge-belum-dikirim` yang TAK punya rule CSS (biarkan apa adanya).
- **Session key `dokumens_table_columns` JANGAN diganti nama/repurpose** — dipakai lintas role.
- **Endpoint `inline-update`, `handler`, `detail`, `destroy`: TANPA perubahan backend.** `destroy` tetap redirect (frontend pakai hidden-form submit + reload, bukan AJAX).
- **Perubahan ke partial global `document-workbench-ui.blade.php` HARUS aditif** (menambah fungsi baru), tidak mengubah jalur DOM lama yang dipakai role lain.
- **Kolom non-editable (parity):** `['tanggal_masuk','status','nomor_mirror','keterangan']`.
- Baseline test sebelum mulai: jalankan `php artisan test` dan catat angka lulus di ledger.

---

## File Structure

| File | Aksi | Tanggung jawab |
|---|---|---|
| `app/Support/OperatorDocumentRow.php` | Create | Transform 1 `Dokumen` → array baris JSON (status derivation, format, join, can_edit, reject fields, handler options). Sumber kebenaran tunggal untuk `datatable()` & `inlineCreate()`. |
| `tests/Unit/OperatorDocumentRowTest.php` | Create | Uji transform + derivasi badge (5 varian) + format + can_edit. |
| `app/Http/Controllers/DokumenController.php` | Modify | Tambah `datatable()`; cabang flag di `index()`; `inlineCreate()` tambah `row`; selaraskan column map. |
| `tests/Feature/OperatorDatatableTest.php` | Create | Uji endpoint `/documents/data` (shape, paginate, filter/sort). |
| `tests/Feature/OperatorInlineCreateRowTest.php` | Create | Uji `inlineCreate` balas objek `row`. |
| `routes/web.php` | Modify | Route `GET /documents/data` → `documents.data`. |
| `public/vendor/tabulator/tabulator.min.js` | Create | Dist Tabulator 6.x. |
| `public/vendor/tabulator/tabulator.min.css` | Create | Dist Tabulator 6.x. |
| `public/css/tabulator-agenda.css` | Create | Tema teal + densitas kompak. |
| `public/js/operator-tabulator.js` | Create | Init Tabulator, formatter, editor, event (baca `window.OPERATOR_TABULATOR_CONFIG`). |
| `resources/views/operator/dokumens/daftarDokumenTabulator.blade.php` | Create | View baru: toolbar, `#documentTableContainer`, modal detail/hapus/kustomisasi/alasan, inject config, `@push` aset, include `_inlineEditEngine`? (tidak — editor ditangani Tabulator). |
| `resources/views/partials/document-workbench-ui.blade.php` | Modify (aditif) | Tambah `window.openDocumentQuickViewFromData(data)`. |

---

## Task 1: Kelas transform baris `OperatorDocumentRow` (TDD, PHP murni)

**Files:**
- Create: `app/Support/OperatorDocumentRow.php`
- Test: `tests/Unit/OperatorDocumentRowTest.php`

**Interfaces:**
- Produces: `\App\Support\OperatorDocumentRow::fromDokumen(\App\Models\Dokumen $dokumen, array $handlerOptions): array` — mengembalikan array baris JSON (dipakai Tugas 2 `datatable()` & Tugas 3 `inlineCreate()`).
- Consumes: model `Dokumen` dengan relasi ter-eager-load `roleStatuses`, `dibayarKepadas`, `dokumenPos`.

**Bentuk array keluaran (kunci wajib):**
```php
[
  'id'            => int,
  // semua field mentah dari config('document_columns.base') keys yang relevan,
  // pakai nilai ATTRIBUTE mentah (untuk editor & display formatter di klien):
  'nomor_agenda' => string, 'bulan' => ?string, 'tahun' => ?string, ... (semua 42 key),
  // turunan tampilan:
  'display_status'          => ['code' => string, 'label' => string, 'variant' => string],
  'nilai_rupiah_formatted'  => string,  // 'Rp. 1.234.567' (accessor formatted_nilai_rupiah)
  'dpp_pph_formatted'       => string,  // 'Rp 1.234' atau '-'
  'ppn_terhutang_formatted' => string,  // 'Rp 1.234' atau '-'
  'dibayar_kepada'          => string,  // join nama_penerima ', ' (fallback field lama)
  'nomor_po'                => string,  // join nomor_po, fallback NO_PO, '-'
  'nomor_miro_display'      => ?string,
  'reject_reason'           => ?string, // DokumenStatus.notes (team_verifikasi rejected)
  'rejected_by'             => ?string,
  'rejected_at'             => ?string,
  'can_edit'                => bool,
  'handler'                 => ?string, // current_handler
  'handler_options'         => array,   // [['value'=>..,'label'=>..], ...] termasuk optgroup bagian
]
```

**Aturan derivasi `display_status`** — PINDAHKAN logika dari `_tableRowsAjax.blade.php:9-150` (lihat riset). Ringkas:
- Hitung status team_verifikasi terbaru (`roleStatuses` role_code `team_verifikasi`, urut `status_changed_at` desc): `tvRejected/tvPending/tvApproved` = `status` == `rejected`/`pending`/`approved` (konstanta `DokumenStatus::STATUS_*`).
- `hasOtherRoles` = ada roleStatuses selain operator.
- `currentHandlerOperator` = `strtolower(current_handler) === 'operator'`.
- Pohon keputusan → `code`:
  - `returned_to_operator` → `dikembalikan`
  - elseif `tvRejected` → `ditolak_verifikasi`
  - elseif `tvPending` → `menunggu_approval_verifikasi`
  - elseif `tvApproved || hasOtherRoles` → `terkirim`
  - elseif status `menunggu_approval_keuangan` && currentHandlerOperator → `draft`
  - elseif currentHandlerOperator && status ∈ {draft,returned_to_operator} → `draft`
  - else → currentHandler ∈ {team_verifikasi,verifikasi,perpajakan,akutansi,pembayaran} ? `terkirim` : `draft`
- `label` via match: `draft`→`Belum Dikirim`, `menunggu_approval_verifikasi`→`Menunggu Approve Team Verifikasi`, `ditolak_verifikasi`→`Dokumen Ditolak oleh Team Verifikasi`, `dikembalikan`→`Dikembalikan`, default→`Terkirim`.
- `variant` = `code` itu sendiri (klien memetakan ke class/inline-style di formatter Tugas 5).

**Aturan `can_edit`** — dari `_tableRowsAjax.blade.php:48-50`:
```
currentHandlerOperator && status ∈ {draft,returned_to_operator,belum_dikirim,'belum dikirim',menunggu_approval_keuangan}
  || (isRejected && currentHandlerOperator)
```
di mana `isRejected` = `tvRejected` ATAU (status `returned_to_operator` && ada roleStatus `rejected`).

**`reject_reason`** — `roleStatuses` where role_code ∈ {verifikasi,team_verifikasi} first; jika `status==='rejected'` → `notes`. `rejected_by` = `changed_by`, `rejected_at` = `status_changed_at?->format('d-m-Y H:i')`. Null bila tak rejected.

**`handler_options`** — replika `document-handler-select.blade.php`: base `['operator'=>'Operator','team_verifikasi'=>'Team Verifikasi','perpajakan'=>'Perpajakan','akutansi'=>'Akutansi','pembayaran'=>'Pembayaran']` + (opsional) optgroup bagian dari `$handlerOptions` (dilewatkan pemanggil agar tak query per-baris).

**Format helper (parity):**
- `nilai_rupiah_formatted` = `$dokumen->formatted_nilai_rupiah` (accessor: `'Rp. '.number_format((float)nilai_rupiah,0,',','.')`).
- `dpp_pph_formatted` = `dpp_pph !== null ? 'Rp '.number_format((float)dpp_pph,0,',','.') : '-'`. Sama untuk `ppn_terhutang_formatted`.
- `dibayar_kepada` = `dibayarKepadas->pluck('nama_penerima')->join(', ')` (kosong → '-'... TERAPKAN sesuai partial :175, kosong = string kosong, klien render '-').
- `nomor_po` = `dokumenPos->pluck('nomor_po')->filter()->join(', ') ?: (NO_PO ?? '-')`.
- Field tanggal dikirim MENTAH (ISO/`Y-m-d H:i:s` cast Carbon → string) ATAU null; formatter KLIEN yang memformat per kolom (Tugas 5) agar satu sumber format. Sertakan juga string ter-cast apa adanya; klien tahu peta format per kolom.

- [ ] **Step 1: Tulis test gagal** — `tests/Unit/OperatorDocumentRowTest.php`. Gunakan `RefreshDatabase`. Buat helper bikin `Dokumen` + `DokumenStatus` terkait. Test methods (Bahasa Indonesia):
  - `test_status_dikembalikan_saat_returned_to_operator`
  - `test_status_ditolak_saat_team_verifikasi_rejected`
  - `test_status_menunggu_saat_team_verifikasi_pending`
  - `test_status_terkirim_saat_ada_role_lain`
  - `test_status_belum_dikirim_saat_draft_di_operator`
  - `test_can_edit_true_saat_draft_di_operator`
  - `test_can_edit_false_saat_handler_bukan_operator`
  - `test_format_rupiah_nilai_pakai_titik_dan_dpp_tanpa_titik`
  - `test_reject_reason_dari_notes_team_verifikasi`
  - `test_join_dibayar_kepada_dan_nomor_po`

  Contoh satu test:
```php
public function test_status_ditolak_saat_team_verifikasi_rejected(): void
{
    $dokumen = Dokumen::factory()->create([
        'current_handler' => 'operator', 'status' => 'draft',
    ]);
    DokumenStatus::create([
        'dokumen_id' => $dokumen->id, 'role_code' => 'team_verifikasi',
        'status' => DokumenStatus::STATUS_REJECTED, 'notes' => 'Berkas kurang lengkap',
        'status_changed_at' => now(), 'changed_by' => 'Verifikator A',
    ]);
    $dokumen->load(['roleStatuses', 'dibayarKepadas', 'dokumenPos']);

    $row = OperatorDocumentRow::fromDokumen($dokumen, []);

    $this->assertSame('ditolak_verifikasi', $row['display_status']['code']);
    $this->assertSame('Dokumen Ditolak oleh Team Verifikasi', $row['display_status']['label']);
    $this->assertSame('Berkas kurang lengkap', $row['reject_reason']);
}
```
  (Cek dulu apakah ada `Dokumen::factory()` / `DokumenStatus` fillable yang dibutuhkan; bila factory tak ada, buat via `Dokumen::create([...])` dengan field minimal seperti di `inlineCreate`. Sesuaikan nama kolom `status_changed_at`/`changed_by` dengan skema `dokumen_statuses` yang sebenarnya — verifikasi di migrasi.)

- [ ] **Step 2: Jalankan, pastikan GAGAL** — `php artisan test --filter=OperatorDocumentRowTest`. Expected: FAIL (class belum ada).

- [ ] **Step 3: Implementasi `app/Support/OperatorDocumentRow.php`** — kelas dengan `public static function fromDokumen(Dokumen $dokumen, array $handlerOptions): array`. Terapkan seluruh aturan di atas. Komentar domain Bahasa Indonesia. Tidak ada query di dalam (pakai relasi ter-load); `handler_options` bagian dari argumen.

- [ ] **Step 4: Jalankan, pastikan LULUS** — `php artisan test --filter=OperatorDocumentRowTest`. Expected: PASS semua.

- [ ] **Step 5: Commit**
```bash
git add app/Support/OperatorDocumentRow.php tests/Unit/OperatorDocumentRowTest.php
git commit -F - <<'EOF'
feat(operator): kelas OperatorDocumentRow transform baris + derivasi status

Pindahkan logika badge status, format rupiah/tanggal, join relasi, dan aturan
can_edit dari _tableRowsAjax.blade.php ke satu kelas PHP teruji, sumber kebenaran
tunggal untuk endpoint datatable & inlineCreate.

Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>
EOF
```

---

## Task 2: Endpoint `datatable()` + route `documents.data` (TDD)

**Files:**
- Modify: `app/Http/Controllers/DokumenController.php` (tambah method `datatable`, dekat `index`)
- Modify: `routes/web.php` (grup operator `role:admin,operator`, `routes/web.php:298-325`)
- Test: `tests/Feature/OperatorDatatableTest.php`

**Interfaces:**
- Consumes: `buildOperatorQuery(Request)` (private, `DokumenController.php:27-117`), `OperatorDocumentRow::fromDokumen()` (Tugas 1).
- Produces: `GET /documents/data` (name `documents.data`) balas `{ "last_page": int, "total": int, "data": [row,...] }`.

**Perilaku `datatable(Request $request): JsonResponse`:**
- `$query = $this->buildOperatorQuery($request);` (scope/search/year/status/sort sudah ditangani; Tabulator kirim `search`, `year`, `status_filter`, `sort`, `order` — SAMA seperti index).
- `$size = (int) $request->input('size', 100); $size = $size > 0 && $size <= 200 ? $size : 100;`
- `$page = max(1, (int) $request->input('page', 1));`
- `$paginator = $query->paginate($size, ['*'], 'page', $page);`
- Siapkan `handler_options` bagian SEKALI: `$bagian = \App\Models\Bagian::active()->ordered()->get(['kode','nama']);` → array optgroup.
- `$data = collect($paginator->items())->map(fn ($d) => OperatorDocumentRow::fromDokumen($d, $handlerOptions))->all();`
- Balas `response()->json(['last_page' => $paginator->lastPage(), 'total' => $paginator->total(), 'data' => $data]);`
- Pastikan relasi ter-load (buildOperatorQuery sudah `with([...])`).

- [ ] **Step 1: Tulis test gagal** — `tests/Feature/OperatorDatatableTest.php`, `RefreshDatabase`, login user role operator (`actingAs`). Buat beberapa Dokumen. Test:
  - `test_endpoint_data_balas_struktur_progressive_load`: GET `route('documents.data')` → 200, JSON punya `last_page`, `total`, `data` array; tiap item punya `id`, `nomor_agenda`, `display_status.code`, `can_edit`.
  - `test_filter_tahun_dihormati`: buat dok tahun 2025 & 2026, `?year=2026` → hanya 2026.
  - `test_paginasi_size_dan_page`: buat 150 dok, `?size=100&page=2` → 50 item, `last_page`=2.
  - `test_non_operator_ditolak`: user role `pembayaran` → redirect/403 (sesuai `role:` middleware).
```php
public function test_endpoint_data_balas_struktur_progressive_load(): void
{
    $user = User::factory()->create(['role' => 'operator']);
    Dokumen::create([/* field minimal: nomor_agenda, bulan, tahun, tanggal_masuk, status, created_by, current_handler */]);

    $response = $this->actingAs($user)->getJson(route('documents.data'));

    $response->assertOk()
        ->assertJsonStructure(['last_page', 'total', 'data' => [['id', 'nomor_agenda', 'display_status' => ['code','label','variant'], 'can_edit']]]);
}
```
- [ ] **Step 2: Jalankan, pastikan GAGAL** — `php artisan test --filter=OperatorDatatableTest`. Expected FAIL (route/method belum ada).
- [ ] **Step 3: Implementasi** — tambah `datatable()` di controller; tambah route:
```php
Route::get('/data', [DokumenController::class, 'datatable'])->name('data');
```
  di dalam grup `role:admin,operator` (`routes/web.php` dekat baris 300). Letakkan SEBELUM route `/{dokumen}/...` agar `data` tak tertangkap wildcard (cek: `/data` vs `/{dokumen}` — Laravel cocokkan urut; taruh `/data` sebelum route berparameter untuk aman).
- [ ] **Step 4: Jalankan, pastikan LULUS** — `php artisan test --filter=OperatorDatatableTest`. Expected PASS.
- [ ] **Step 5: Commit**
```bash
git add app/Http/Controllers/DokumenController.php routes/web.php tests/Feature/OperatorDatatableTest.php
git commit -F - <<'EOF'
feat(operator): endpoint GET /documents/data untuk Tabulator (progressive load)

Pakai ulang buildOperatorQuery() + OperatorDocumentRow; balas {last_page,total,data}.
Tanpa logika filter baru.

Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>
EOF
```

---

## Task 3: `inlineCreate()` tambah objek baris JSON + selaraskan column map

**Files:**
- Modify: `app/Http/Controllers/DokumenController.php` (`inlineCreate` :261-313)
- Test: `tests/Feature/OperatorInlineCreateRowTest.php`

**Interfaces:**
- Produces: `POST /documents/inline-create` balasan DITAMBAH kunci `row` (objek dari `OperatorDocumentRow::fromDokumen`), MEMPERTAHANKAN `html` & `id` & `success` (view lama masih pakai `html` selama fallback).

**Perilaku:** setelah membuat `$dokumen`, eager-load `roleStatuses,dibayarKepadas,dokumenPos`, siapkan `handler_options`, dan tambahkan `'row' => OperatorDocumentRow::fromDokumen($dokumen, $handlerOptions)` ke array json. Selaraskan drift: pemetaan kolom di inlineCreate tak memengaruhi `row` (row selalu lengkap dari config), jadi cukup pastikan `html` lama tetap dirender seperti sekarang; TIDAK perlu mengubah `operatorDocumentColumns()` (biarkan untuk hindari risiko), CUKUP tambahkan `row`.

- [ ] **Step 1: Tulis test gagal** — `tests/Feature/OperatorInlineCreateRowTest.php`:
  - `test_inline_create_balas_objek_row`: actingAs operator, POST `route('documents.inline-create')` `{nomor_agenda:'9001_2026'}` → 200 JSON punya `success:true`, `id`, `row.nomor_agenda === '9001_2026'`, `row.display_status.code === 'draft'`, `row.can_edit === true`.
  - `test_inline_create_duplikat_422`: buat dok nomor sama dulu, POST → 422.
- [ ] **Step 2: Jalankan, pastikan GAGAL** — `php artisan test --filter=OperatorInlineCreateRowTest`.
- [ ] **Step 3: Implementasi** — tambah `row` ke balasan `inlineCreate`.
- [ ] **Step 4: Jalankan, pastikan LULUS.**
- [ ] **Step 5: Commit**
```bash
git add app/Http/Controllers/DokumenController.php tests/Feature/OperatorInlineCreateRowTest.php
git commit -F - <<'EOF'
feat(operator): inlineCreate balas objek row JSON untuk Tabulator

Tetap kirim html (view lama) + tambah row (OperatorDocumentRow) sebagai konsumen
tunggal halaman Tabulator operator.

Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>
EOF
```

---

## Task 4: Aset Tabulator + tema + cabang flag `index()` + skeleton view baru

**Files:**
- Create: `public/vendor/tabulator/tabulator.min.js`, `public/vendor/tabulator/tabulator.min.css`
- Create: `public/css/tabulator-agenda.css`
- Create: `public/js/operator-tabulator.js` (versi skeleton: init + progressive load + kolom minimal, formatter penuh menyusul Tugas 5)
- Create: `resources/views/operator/dokumens/daftarDokumenTabulator.blade.php`
- Modify: `app/Http/Controllers/DokumenController.php` (`index()` cabang flag)

**Interfaces:**
- View meng-inject `window.OPERATOR_TABULATOR_CONFIG = { dataUrl, inlineUpdateUrlTemplate, inlineCreateUrl, handlerUrlTemplate, detailUrlTemplate, destroyUrlTemplate, csrf, columns: [{key,label}], availableColumns, selected, ie: {kategori,sub,item,jenis,bagian}, bulanList }` via `@json`.
- `operator-tabulator.js` membaca config itu, init Tabulator di `#operatorTabulatorTable`.

**Cabang `index()`:** di awal `index()`, SEBELUM logika lama:
```php
$useClassic = $request->boolean('classic');
```
Setelah menghitung `$data` (variabel view yang sudah ada), pilih view:
```php
$view = $useClassic ? 'operator.dokumens.daftarDokumen' : 'operator.dokumens.daftarDokumenTabulator';
return view($view, $data);
```
CATATAN: `$data` yang sudah disiapkan `index()` (termasuk `availableColumns,selectedColumns,ieKategoriList,...`) cukup untuk kedua view. Jangan ubah cabang `virtual_chunk` (tetap untuk view lama). Tabulator TIDAK memakai `virtual_chunk`.

**Ambil dist Tabulator 6.x** (Step khusus): unduh & commit. Bila offline → BLOCKED, escalate ke controller.
```bash
mkdir -p public/vendor/tabulator
curl -fsSL https://cdn.jsdelivr.net/npm/tabulator-tables@6.3.1/dist/js/tabulator.min.js  -o public/vendor/tabulator/tabulator.min.js
curl -fsSL https://cdn.jsdelivr.net/npm/tabulator-tables@6.3.1/dist/css/tabulator.min.css -o public/vendor/tabulator/tabulator.min.css
```
Verifikasi ukuran file > 100KB (js) dan > 10KB (css); bila HTML error page terunduh, escalate.

**Skeleton view** `daftarDokumenTabulator.blade.php`:
- `@extends('layouts.app')`, `@section('content')`.
- Toolbar filter: reuse markup toolbar operator (search input `name="search"`, select tahun, select status) — boleh SALIN dari view lama, tapi tanpa `filterForm` full-reload untuk search/status (di Tugas 7 disambung ke Tabulator). Untuk skeleton, cukup input kosong.
- `<div class="table-section table-dokumen" id="documentTableContainer">` (WAJIB id ini untuk workbench-ui) berisi `<div id="operatorTabulatorTable"></div>`.
- `@push('styles')` link `tabulator.min.css` + `tabulator-agenda.css` (pakai `asset()`).
- `@push('scripts')`: `<script>window.OPERATOR_TABULATOR_CONFIG = @json($configArray);</script>` lalu `<script src="{{ asset('vendor/tabulator/tabulator.min.js') }}"></script>` lalu `<script src="{{ asset('js/operator-tabulator.js') }}"></script>`.
  - Bangun `$configArray` di dalam view via `@php` dari variabel controller (`$selectedColumns`, `$availableColumns`, `$ieKategoriList`, dst) + `route()` template URL (pakai placeholder `__ID__` untuk yang berparameter, mis. `str_replace('__ID__','{id}', route('documents.inline-update', ['dokumen'=>'__ID__']))`). Dokumentasikan.

**Skeleton `operator-tabulator.js`:** init Tabulator:
```js
const CFG = window.OPERATOR_TABULATOR_CONFIG;
const table = new Tabulator("#operatorTabulatorTable", {
  ajaxURL: CFG.dataUrl,
  progressiveLoad: "scroll",
  progressiveLoadDelay: 200,
  paginationSize: 100,
  ajaxParams: () => ({ /* filter aktif disambung Tugas 7 */ }),
  ajaxResponse: (url, params, response) => response, // {last_page,total,data}
  layout: "fitDataStretch",
  height: "70vh",
  index: "id",
  columns: buildColumns(CFG),   // Tugas 5 mengisi formatter; skeleton: title+field saja
  placeholder: "Tidak ada dokumen.",
});
window.operatorTable = table;
```
`buildColumns` skeleton: `[{formatter:'rownum', width:60, frozen:true, headerSort:false}, ...CFG.columns.map(c => ({title:c.label, field:c.key, frozen: c.key==='nomor_agenda'}))]`.

- [ ] **Step 1:** Unduh dist Tabulator (perintah curl di atas) + verifikasi ukuran. Commit dua file dist.
- [ ] **Step 2:** Tulis `public/css/tabulator-agenda.css` (header `#083E40` teks putih, baris kompak ~38px, hover, border tipis, kolom frozen bayangan). Commit.
- [ ] **Step 3:** Tulis skeleton `operator-tabulator.js` + `daftarDokumenTabulator.blade.php` (inject config, mount). Commit keduanya.
- [ ] **Step 4:** Tambah cabang flag di `index()`. Commit.
- [ ] **Step 5: Verifikasi render server (tanpa browser):**
```bash
php artisan route:clear
php -r "require 'vendor/autoload.php'; \$app=require 'bootstrap/app.php';" # sanity
```
  Lalu uji ringan via test HTTP: tambah `tests/Feature/OperatorTabulatorViewTest.php`:
  - `test_default_menyajikan_view_tabulator`: actingAs operator, GET `route('documents.index')` → 200, body memuat `operatorTabulatorTable` dan `vendor/tabulator/tabulator.min.js`.
  - `test_flag_classic_menyajikan_view_lama`: GET `route('documents.index', ['classic'=>1])` → 200, body memuat penanda view lama (mis. `id="btnTambahBarisInline"`), TIDAK memuat `operatorTabulatorTable`.
  Jalankan `php artisan test --filter=OperatorTabulatorViewTest` → PASS.
- [ ] **Step 6: Commit test**
```bash
git add tests/Feature/OperatorTabulatorViewTest.php
git commit -F - <<'EOF'
test(operator): view Tabulator default + fallback ?classic=1 ke view lama

Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>
EOF
```

**CATATAN REVIEW:** Tugas ini berhenti pada "tabel Tabulator termuat & terisi data mentah". Format & interaksi di Tugas 5–7. Deliverable teruji: server menyajikan view yang benar per flag + aset ada.

---

## Task 5: Formatter paritas penuh (render, read-only)

**Files:**
- Modify: `public/js/operator-tabulator.js` (`buildColumns` + formatter)
- Modify: `public/css/tabulator-agenda.css` (badge, nomor_agenda subline, pemaraf)

**Interfaces:** Consumes row JSON dari Tugas 1/2 (`display_status`, `*_formatted`, dll). Tidak ada perubahan backend.

**Formatter per kolom (paritas — rujuk `_tableRowsAjax.blade.php`):**
- **`No`**: `formatter:'rownum'`, frozen kiri, width 60, `headerSort:false`.
- **`nomor_agenda`** (`:99-101`): frozen kiri. `<strong>{nomor_agenda}</strong><br><small class="text-muted">{bulan} {tahun}</small>`. `formatter` return HTML (Tabulator `formatter` fungsi; pastikan `variableHeight`).
- **`status`** kolom → badge dari `display_status` (`:134-150`): peta `variant`→markup:
  - `draft` → `<span class="badge-status badge-belum-dikirim"><i class="fa-solid fa-file-pen me-1"></i><span>Belum Dikirim</span></span>`
  - `ditolak_verifikasi`/`dikembalikan` → `<span class="badge-status badge-ditolak" style="background:linear-gradient(135deg,#dc3545,#b02a37);color:white;"><i class="fa-solid fa-rotate-left me-1"></i><span>{variant==='dikembalikan'?'Dikembalikan':'Dokumen Ditolak'}</span></span>`
  - `menunggu_approval_verifikasi` → `<span class="badge-status" style="background:linear-gradient(135deg,#ffc107,#ff8c00);color:white;"><i class="fa-solid fa-clock me-1"></i><span>Menunggu Approve Team Verifikasi</span></span>`
  - else `terkirim` → `<span class="badge-status badge-terkirim"><i class="fa-solid fa-check me-1"></i><span>{label}</span></span>`
  - Salin rule CSS `.badge-status`, `.badge-status.badge-terkirim` dari `daftarDokumen.blade.php:1366-1412` ke `tabulator-agenda.css` (atau ke `<style>` view). `badge-belum-dikirim`/`badge-ditolak` andalkan inline style / base saja (parity).
- **`nilai_rupiah`** → tampilkan `row.nilai_rupiah_formatted` dalam `<strong>`. **`dpp_pph`/`ppn_terhutang`** → `*_formatted`.
- **Tanggal** (peta format, dari riset): `d-m-Y` → `tanggal_spp,tanggal_berita_acara,tanggal_spk,tanggal_berakhir_spk`. `d-m-Y H:i` → `tanggal_masuk`. `d/m/Y H:i` → `tanggal_paraf,tanggal_selesai_diproses,tanggal_kembali_ke_bagian,tanggal_hasil_koreksi_bagian`. `d/m/Y` → `tanggal_dibayar,tanggal_faktur,tanggal_selesai_verifikasi_pajak`. Null/'' → `-`. Tulis helper `fmtDate(iso, pattern)` di JS (parsing aman; kolom kosong → '-').
- **`dibayar_kepada`** → `row.dibayar_kepada || '-'`. **`nomor_po`** → `row.nomor_po`. **`nomor_miro`** → `row.nomor_miro_display || row.nomor_miro || '-'`.
- **`pemaraf`** (`:207-213`) → bila ada: inline green badge (salin markup), else `-`.
- **`link`/`link_dokumen_pajak`** (`:230-243`) → server SUDAH sanitasi? Tidak — sanitasi via `SafeUrl` ada di render lama. Tambahkan di `OperatorDocumentRow`: sertakan `link_safe`/`link_dokumen_pajak_safe` (via `\App\Support\SafeUrl::external`) agar klien cukup pasang `<a href>`. (⚠️ Ini menambah 2 field ke Tugas 1 — bila belum ada, tambahkan di Tugas 5 sebagai perubahan kecil ke `OperatorDocumentRow` + test.) Formatter: bila safe truthy → `<a href="{safe}" target="_blank" rel="noopener noreferrer" onclick="event.stopPropagation()"><i class="fa-solid fa-link fa-sm"></i> Lihat</a>`, else `-`.
- Kolom lain → teks mentah, kosong → `-`.
- **Frozen:** `nomor_agenda` `frozen:true`. `No` frozen. Sisanya normal.
- **Escaping:** field mentah yang dirender sebagai teks harus di-escape (Tabulator default meng-escape string; tapi formatter fungsi yang return HTML TIDAK — pastikan hanya nilai server-terkontrol/ter-escape yang masuk innerHTML; untuk teks bebas user gunakan `document.createElement`/`textContent` atau util escape). Tulis `esc(s)` util dan pakai untuk semua nilai user-asal di dalam HTML formatter.

- [ ] **Step 1:** Implementasi `buildColumns` penuh + formatter + helper `fmtDate`/`esc` di `operator-tabulator.js`.
- [ ] **Step 2:** (bila perlu) tambah `link_safe`/`link_dokumen_pajak_safe` ke `OperatorDocumentRow` + test `test_link_disanitasi_safeurl`. Jalankan unit test → PASS.
- [ ] **Step 3:** Lengkapi `tabulator-agenda.css` (badge, subline, pemaraf, frozen shadow, densitas).
- [ ] **Step 4: Self-review paritas** — bandingkan tiap formatter dengan baris rujukan `_tableRowsAjax.blade.php`. Tidak ada red-green test (frontend); buktikan via tabel paritas di laporan (kolom → rujukan → formatter).
- [ ] **Step 5: Commit** (per-file)
```bash
git add public/js/operator-tabulator.js public/css/tabulator-agenda.css
git commit -F - <<'EOF'
feat(operator): formatter Tabulator paritas penuh (badge, rupiah, tanggal, link)

Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>
EOF
# bila OperatorDocumentRow disentuh:
git add app/Support/OperatorDocumentRow.php tests/Unit/OperatorDocumentRowTest.php
git commit -F - <<'EOF'
feat(operator): OperatorDocumentRow sertakan URL link ter-sanitasi SafeUrl

Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>
EOF
```

**UTANG VERIFIKASI:** render visual hanya bisa dibuktikan user (QA). Review = paritas statis.

---

## Task 6: Inline edit + create + dropdown pengurus (Tabulator editors + AJAX)

**Files:**
- Modify: `public/js/operator-tabulator.js`

**Interfaces:** PATCH `inline-update` `{field,value}` → `{success,display_value,raw_value}` (TANPA perubahan server). POST `inline-create` `{nomor_agenda}` → `{success,id,row}` (Tugas 3). PATCH `handler` `{target_handler}` → `{success,message,handler}` (TANPA perubahan server).

**Editor per field** (peta `FIELD_TYPE` dari `_inlineEditEngine.blade.php:149-185`, dilewatkan via `CFG` atau disalin ke JS):
- `text` → `editor:"input"`; `textarea`(uraian_spp) → `editor:"textarea"`; `number`(nilai_rupiah,dpp_pph,ppn_terhutang) → `editor:"input"` dgn normalisasi numerik pada commit; `date` → `editor:"input"` `editorParams:{type:'date'}` (atau `editor:"date"` Tabulator bila ada) hasil kirim `Y-m-d`.
- `select_kategori/sub/item/jenis/bagian/bulan` → `editor:"list"` dengan `values` dinamis:
  - kategori: `CFG.ie.kategori`; sub: filter `CFG.ie.sub` by baris `kategori`; item: filter `CFG.ie.item` by baris `jenis_dokumen`; jenis: `CFG.ie.jenis`; bagian: `CFG.ie.bagian` (value `kode`, label `kode — nama`); bulan: `CFG.bulanList` (SALIN persis termasuk quirk 'May'/'July' agar paritas — JANGAN "perbaiki" tanpa arahan).
  - Filter berantai: `editorParams` sebagai FUNGSI membaca `cell.getRow().getData().kategori` / `.jenis_dokumen`.
- **Editable gating:** `editable:(cell)=>{ const d=cell.getRow().getData(); const f=cell.getColumn().getField(); return d.can_edit && !['tanggal_masuk','status','nomor_mirror','keterangan'].includes(f); }` untuk SETIAP kolom data.
- **`cellEdited`**: PATCH inline-update; sukses → set `display_value` (tampilkan lewat formatter; simpan `raw_value` ke data row via `cell.getRow().update({[field]:raw_value})` + simpan display); gagal (non-OK / `success:false`) → `cell.restoreOldValue()` + toast error (helper `opToast('error',msg)`). Ikuti pola fetch `_inlineEditEngine.blade.php:632-674`.
  - Setelah sukses field parent (kategori/jenis_dokumen), tak perlu aksi khusus (editor anak baca data row terbaru saat dibuka).
- **Tambah Baris:** tombol toolbar → `table.addRow({__isNew:true}, true)` di puncak; buka editor sel `nomor_agenda`. Pada commit nomor_agenda non-kosong → POST inline-create; sukses → `row.update(response.row)` + tandai bukan-baru; duplikat/gagal → hapus baris (`row.delete()`) + toast. (Alternatif lebih sederhana & robust: tombol buka prompt kecil nomor_agenda → POST → `table.addRow(response.row, true)`. Implementer pilih yang paling andal; WAJIB: nomor_agenda unik divalidasi server, error 422 ditampilkan.)
- **Dropdown Pengurus Dokumen** (kolom `handler`): formatter render `<select class="op-handler-select">` dari `row.handler_options`, value `row.handler`; `cellClick`/`change` → PATCH handler `{target_handler}`; sukses → toast + `table.replaceData()` (muat ulang data via ajax) untuk cerminkan pindah handler; gagal → balikkan pilihan + toast. (Boleh pakai formatter+listener; pastikan `event.stopPropagation()` agar tak trigger row click.)
- **Toast helper** `opToast(type,msg)`: implement mandiri di JS (fixed bottom, hijau/merah) — setara `showIeToast` lama; JANGAN bergantung fungsi page-local view lama.

- [ ] **Step 1:** Tambah editor + `editable` gating + `cellEdited` PATCH + `opToast`.
- [ ] **Step 2:** Select berantai (kategori→sub→item) + bulan/bagian/jenis.
- [ ] **Step 3:** Tambah Baris (POST inline-create → row.update/addRow).
- [ ] **Step 4:** Dropdown pengurus (PATCH handler).
- [ ] **Step 5: Self-review** vs `_inlineEditEngine.blade.php` & `document-handler-select.blade.php` (tabel paritas di laporan).
- [ ] **Step 6: Commit**
```bash
git add public/js/operator-tabulator.js
git commit -F - <<'EOF'
feat(operator): inline edit, tambah baris, & dropdown pengurus di Tabulator

Editor per FIELD_TYPE + select berantai; cellEdited PATCH inline-update (revert+toast
saat gagal); Tambah Baris POST inline-create; dropdown pengurus PATCH handler.

Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>
EOF
```

**UTANG VERIFIKASI:** seluruh interaksi butuh QA user (tak ada harness). Review = paritas statis + kontrak endpoint.

---

## Task 7: Aksi baris, filter, panel Detail Cepat, modal alasan, error handling

**Files:**
- Modify: `public/js/operator-tabulator.js`
- Modify: `resources/views/operator/dokumens/daftarDokumenTabulator.blade.php` (modal detail/hapus/kustomisasi/alasan + toolbar filter fungsional)
- Modify: `resources/views/partials/document-workbench-ui.blade.php` (aditif `openDocumentQuickViewFromData`)

**7a. Modal Detail + Hapus:** SALIN dari `daftarDokumen.blade.php` ke view baru: `#viewDocumentModal` (+ ~30 `#view-*`), `openViewDocumentModal(docId)` (fetch `/documents/{id}/detail`), `#deleteConfirmModal` + `#deleteDocumentForm` (hidden POST DELETE) + `confirmDeleteDocument`/`executeDeleteDocument` + `#deleteSuccessToast`. **Double-click baris Tabulator** (`rowDblClick`) → `openViewDocumentModal(row.getData().id)`. Delete tetap redirect (form submit + reload) — PARITY, tanpa AJAX.

**7b. Panel Detail Cepat (single-click):** tambah di `document-workbench-ui.blade.php` (aditif, JANGAN ubah listener DOM lama):
```js
window.openDocumentQuickViewFromData = function (data) {
  // Bangun peta {label: value} dari data baris Tabulator, lalu render panel
  // memakai jalur render yang sama dengan extractGenericRow (refactor kecil:
  // ekstrak fungsi render internal agar bisa diumpani objek, BUKAN DOM).
};
```
- Refactor internal minimal: pisahkan bagian "render panel dari peta field" dari `extractGenericRow` sehingga bisa dipanggil dua jalur (DOM lama + data baru). Jalur DOM lama TETAP dipakai role lain — jangan diubah perilakunya.
- Di `operator-tabulator.js`: `rowClick` → `window.openDocumentQuickViewFromData(buildQuickViewData(row.getData()))`, hormati ignore-list (klik pada select/link/button jangan buka panel — cek `e.target`).

**7c. Filter tanpa reload:** toolbar search/tahun/status → set state → `table.setData(CFG.dataUrl, params)` atau perbarui `ajaxParams` lalu `table.replaceData()`. Debounce search 300ms. (Nilai dikirim sebagai `search`,`year`,`status_filter` — sama seperti `buildOperatorQuery`.)

**7d. Kustomisasi Kolom:** SALIN modal `#columnCustomizationModal` + `saveColumnCustomization()` dari view lama; submit `columns[]` + `enable_customization=1` ke `route('documents.index')` (full reload) — Tabulator column defs regen server-side. (Paritas §4.2: "modal lama dipakai apa adanya".) Karena reload membawa flag default (tanpa `?classic`), view Tabulator kembali dengan kolom baru. Pastikan form action tak menambah `classic`.

**7e. Modal alasan penolakan (global tunggal):** view baru punya SATU `#rejectReasonModal` (bukan per-baris). Formatter badge `ditolak_verifikasi`/`dikembalikan` diberi `onclick` → isi modal dari `row.reject_reason`,`rejected_by`,`rejected_at` lalu tampilkan (Bootstrap modal). Bila `reject_reason` kosong → teks "Tidak ada alasan yang dicatat".

**7f. Error handling:** `ajaxError` Tabulator → tampilkan area "Gagal memuat data" + tombol "Coba lagi" (`table.setData(...)`). Semua fetch pakai CSRF meta. Interaksi gagal → revert + toast (sudah di Tugas 6).

- [ ] **Step 1 (7a):** modal detail + hapus + dblclick.
- [ ] **Step 2 (7b):** hook `openDocumentQuickViewFromData` (aditif) + rowClick.
- [ ] **Step 3 (7c):** filter search/tahun/status → replaceData.
- [ ] **Step 4 (7d):** modal kustomisasi kolom (full reload).
- [ ] **Step 5 (7e):** modal alasan global + wiring badge.
- [ ] **Step 6 (7f):** error handling load + retry.
- [ ] **Step 7: Regression check partial global** — pastikan perubahan `document-workbench-ui` aditif: grep bahwa listener DOM lama (`#documentTableContainer tbody tr`) tak diubah; jalankan `php artisan view:cache` (compile OK). Uji cepat 4 role lain TIDAK memuat `operator-tabulator.js` (view mereka tak berubah).
- [ ] **Step 8: Commit** (per-file, beberapa commit logis)
```bash
git add resources/views/operator/dokumens/daftarDokumenTabulator.blade.php public/js/operator-tabulator.js
git commit -F - <<'EOF'
feat(operator): aksi baris, filter, modal alasan, & error handling Tabulator

Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>
EOF
git add resources/views/partials/document-workbench-ui.blade.php
git commit -F - <<'EOF'
feat(workbench): hook data-driven openDocumentQuickViewFromData (aditif)

Panel Detail Cepat kini bisa diumpani objek data baris (untuk baris Tabulator yang
berbasis <div>, bukan <tr>). Jalur DOM lama role lain tidak diubah.

Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>
EOF
```

**UTANG VERIFIKASI:** seluruh Tugas 7 butuh QA visual user.

---

## Task 8: Cleanup + finalize — DEFERRED (gated on QA)

> **JANGAN eksekusi Tugas 8 sampai user menyatakan QA visual (Tugas 5–7) LULUS.** Sampai saat itu, view lama tetap ada sebagai fallback `?classic=1`.

**Files (saat dieksekusi):**
- Delete: `resources/views/operator/dokumens/daftarDokumen.blade.php` (view lama), `_chunk.blade.php`, `_tableRowsAjax.blade.php` (jika tak dipakai role lain — VERIFIKASI grep).
- Modify: `index()` — hapus cabang `?classic`/`virtual_chunk` operator (jika `virtual_chunk` hanya untuk view lama operator), selalu view Tabulator.
- Modify: rename `daftarDokumenTabulator.blade.php` → `daftarDokumen.blade.php` (opsional, agar nama kanonik).

**JANGAN hapus** (dipakai role lain): `_inlineEditEngine`, `virtual-document-table`, `_documentTableStickyCells`, `_activeCellNav`, `document-handler-select`, `document-workbench-ui`, endpoint `bulk-send-to-verifikasi`/`send-to-verifikasi`.

- [ ] Verifikasi via grep bahwa file yang akan dihapus TIDAK di-include role lain.
- [ ] Hapus file lama + cabang flag; jalankan seluruh test suite → PASS.
- [ ] Commit; deploy; minta QA akhir user.

---

## Rencana Uji & Rilis (dari spec §6)

1. Backend TDD (Tugas 1–4) lulus lokal.
2. Deploy ke `codinggemini` → server pull → clear route/view/config.
3. Verifikasi server: `/documents` render Tabulator + `/documents/data` balas JSON sehat; `/documents?classic=1` render tabel lama.
4. **QA visual user sebagai operator** (WAJIB sebelum Tugas 8): scroll progresif, sort, 3 filter, edit tiap tipe field, tambah baris (+duplikat), ganti pengurus, modal detail, hapus, kustomisasi kolom, panel Detail Cepat, modal alasan.

## Di Luar Lingkup

- Rollout Tabulator ke role lain (fase berikutnya, pakai pola pilot ini).
- Tombol kirim per baris / bulk send (UI-nya memang sudah tidak ada).
- Penghapusan partial mesin tabel lama shared (baru setelah semua role migrasi).

---

## Self-Review (penulis plan)

**Spec coverage:** §3.1 endpoint→T2; §3.2 row shape→T1; §3.3 inlineCreate→T3; §4.1 aset→T4; §4.2 column defs+modal kustomisasi→T4/T7d; §4.3 formatter→T5; §4.4 inline edit/create→T6; §4.5 aksi baris/panel/filter→T7; §4.6 progressive load→T4; §4.7 cleanup→T8; §5 error handling→T7f; §6 uji→bagian uji. Semua tersinggung.

**Type consistency:** `OperatorDocumentRow::fromDokumen(Dokumen,$handlerOptions):array` dipakai konsisten di T2/T3/T5. `display_status.{code,label,variant}` konsisten T1↔T5. `window.OPERATOR_TABULATOR_CONFIG` & `window.operatorTable` & `window.openDocumentQuickViewFromData` konsisten T4/T6/T7.

**Risiko/utang jujur:** ~70% pekerjaan frontend TANPA harness test → Tugas 5–7 hanya dibuktikan review statis + QA user. Flag `?classic=1` adalah jaring pengaman. Dist Tabulator harus terunduh (bila offline → BLOCKED). Perubahan `document-workbench-ui` harus benar-benar aditif (partial global lintas role).

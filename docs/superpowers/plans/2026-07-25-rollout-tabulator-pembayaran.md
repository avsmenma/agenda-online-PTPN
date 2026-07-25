# Rollout Tabulator — Pembayaran (Rollout 4, outlier) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Migrasikan tabel dokumen utama pembayaran ke engine Tabulator bersama (pill 3-state, edit-anywhere, CSV-include, tanpa forward), reimplement freeze 2-tab di atas frozen-column native Tabulator, lalu hapus renderer bespoke + kode mati (grep-gated).

**Architecture:** DTO `App\Support\PembayaranDocumentRow extends DocumentRow` (status pill server-side, NO deadline object, can_edit=true); endpoint `documents.pembayaran.data` shape `{last_page,total,data}` include CSV; view Tabulator memakai `public/js/document-tabulator.js` dengan 3 perubahan engine ADITIF (formatter `paymentPill`, suppress kolom "Pengurus Dokumen", dukungan `CFG.frozen`).

**Tech Stack:** Laravel 12, PHP ^8.2, Blade, Tabulator 6.3.1 (self-hosted), PHPUnit.

**Spec:** `docs/superpowers/specs/2026-07-25-rollout-tabulator-pembayaran-design.md`

## Global Constraints

- **Commit per-file** (`git add <path>`, JANGAN `git add .`/`-A`). Pesan commit Bahasa Indonesia. Satu commit = satu perubahan logis.
- **UI/komentar Indonesia, identifier English.**
- **Suite hijau** (`php artisan test`) sebelum tiap commit.
- **Nol logika bisnis di JS** — pill dihitung server; klien merender.
- **Perubahan `document-tabulator.js` (engine global) WAJIB ADITIF** — role operator/akutansi/perpajakan/verifikasi tak boleh berubah perilakunya (mereka tak set `showHandler`/`frozen`/`paymentPill`).
- **Edit-anywhere:** `can_edit=true` untuk SEMUA baris pembayaran (tak di-gate `is_at_my_role`). Aturan khas pembayaran (dikonfirmasi user).
- **Query INCLUDE `imported_from_csv`** (pembayaran satu-satunya). JANGAN salin exclusion role lain.
- **Kunci preferensi kolom TAK BERUBAH** (`table_columns_preferences['pembayaran_dashboard']`, `['pembayaran_dashboard_frozen']`, session, sentinel `frozen_config`) — export/rekapan membacanya.
- **DI LUAR SCOPE, JANGAN sentuh:** CsvImportController + import UI, export (Excel/PDF/per-vendor), rekapan-vendor, virtual-assistant, `FrozenColumnLayout` (dipakai lagi).
- **Sebelum hapus kode aksi apa pun (Task 6): grep-gate + surface ke user DULU.**
- **QA visual = tanggung jawab user** (Playwright READ-ONLY, login `pembayaran`/`12345678`).

---

## Referensi template (BACA — artefak nyata yang di-mirror)

| Peran | File |
|---|---|
| DTO basis | `app/Support/DocumentRow.php` (`baseRow`, `formatDates`) |
| DTO template | `app/Support/PerpajakanDocumentRow.php` / `VerifikasiDocumentRow.php` (struktur fromDokumen + buildStatusBadge) |
| Endpoint template | `DashboardPerpajakanController::datatable()` + route `documents.perpajakan.data` (shape `{last_page,total,data}`, size/page) |
| Engine | `public/js/document-tabulator.js` — `buildColumns()` (:726-758), `EXTRA_FORMATTERS` (:598-601), `getFormatter`/`FORMATTERS` (:703-722), `editableGate` (:737) |
| Freeze logic | `app/Support/FrozenColumnLayout.php` (`normalize` "kiri menang", `renderOrder` kiri→bebas→kanan) — DIPERTAHANKAN |

**Sumber pembayaran (BACA — yang diporting/diganti):**
- `app/Http/Controllers/DashboardPembayaranController.php`: `datatable()` (:696-763), `buildPembayaranDashboardQuery()` (:765-856, INCLUDE CSV — hanya `whereNotNull('nomor_agenda')`), `getPembayaranComputedStatus()` (:1108-1125), `renderPembayaranStatusPill()` (:1127-1138), `getPembayaranDashboardAvailableColumns()` (:911-961, katalog 48-kolom), `getPembayaranInlineEditOptions()` (:963-1032), `getPembayaranDateEditableColumns()` (:1093-1106), index() freeze/stat handling (:137-193, :278-333).
- View god-file `resources/views/pembayaranNEW/dashboardPembayaran.blade.php` (3970): renderer bespoke `renderFallbackRows` (:3705-3744)/`loadFallbackRows` (:3746-3785)/`initFallbackTableScroll` (:3787-3801); modal 2-tab (:2747-2865); JS `switchColumnTab`/`renderFrozenTab`/state `frozenLeftOrder`/`frozenRightOrder` (:3027-3113); save freeze (:3289-3306); `editableColumns`/`columnClassMap` (:3557-3608); includes `_inlineEditEngine`(:3968)/`_activeCellNav`(:3969)/`_documentTableStickyCells`(:3857).

---

## Task 1: `PembayaranDocumentRow` DTO + unit test

**Files:**
- Create: `app/Support/PembayaranDocumentRow.php`
- Create: `tests/Unit/PembayaranDocumentRowTest.php`

**Interfaces:**
- Consumes: `DocumentRow::baseRow(Dokumen, array $handlerOptions, ?string $viewerRole): array`.
- Produces: `PembayaranDocumentRow::fromDokumen(Dokumen $dokumen, array $handlerOptions = [], ?string $viewerRole = 'pembayaran'): array` — row berisi kunci base + **semua kolom katalog pembayaran (raw)** + `can_edit` + `status_badge` (pill). **TIDAK ada** key `deadline`.
  - `status_badge` bentuk: `['state'=>string,'class'=>string,'text'=>string]` — `state ∈ {belum_siap_dibayar, siap_dibayar, sudah_dibayar}`; `class ∈ {status-pill--pending, status-pill--ready, status-pill--paid}`; `text ∈ {Belum Siap, Siap Dibayar, Sudah Dibayar}`.

### Kontrak khas pembayaran
- `can_edit = true` (SELALU — edit-anywhere; JANGAN panggil `DokumenHelper::canEditDocument`/gate `is_at_my_role`).
- **Tanpa `deadline`** — jangan keluarkan key `deadline` (pembayaran tak punya kolom deadline per-baris).
- **Tanpa forward** — base menaruh `handler`/`handler_options`/`can_change_handler`; biarkan (tak dipakai view; engine akan suppress kolomnya via `showHandler:false` di Task 3).
- **Kolom katalog:** pastikan row memuat nilai RAW untuk setiap kolom di `getPembayaranDashboardAvailableColumns()` yang tak sudah disediakan `baseRow()`. Base sediakan kolom umum (`config('document_columns.base')`) + helper rupiah/tanggal/join. Tambah kolom pembayaran-spesifik (mis. field pajak, `link_bukti_pembayaran`, `tanggal_dibayar`) sebagai `row[$key] = $dokumen->{$key}` bila belum ada. Klien (engine `getFormatter`) yang memformat.

### `buildStatusBadge(Dokumen): array` — port `getPembayaranComputedStatus` + `renderPembayaranStatusPill`
```php
protected static function buildStatusBadge(Dokumen $dokumen): array
{
    $sp = strtoupper(trim((string) ($dokumen->status_pembayaran ?? '')));
    if ($dokumen->tanggal_dibayar || $dokumen->link_bukti_pembayaran
        || $sp === 'SUDAH_DIBAYAR' || $sp === 'SUDAH DIBAYAR' || $dokumen->status_pembayaran === 'sudah_dibayar') {
        return ['state' => 'sudah_dibayar', 'class' => 'status-pill--paid', 'text' => 'Sudah Dibayar'];
    }
    if ($dokumen->current_handler === 'pembayaran' || $dokumen->status === 'sent_to_pembayaran') {
        return ['state' => 'siap_dibayar', 'class' => 'status-pill--ready', 'text' => 'Siap Dibayar'];
    }
    return ['state' => 'belum_siap_dibayar', 'class' => 'status-pill--pending', 'text' => 'Belum Siap'];
}
```

- [ ] **Step 1: Tulis unit test yang gagal** — `tests/Unit/PembayaranDocumentRowTest.php` (mirror fixture `tests/Unit/VerifikasiDocumentRowTest.php`):
```php
// sudah_dibayar via tanggal_dibayar
$d = $this->makeDokumen(['tanggal_dibayar' => now()]);
$this->assertSame('sudah_dibayar', PembayaranDocumentRow::fromDokumen($d)['status_badge']['state']);
$this->assertSame('status-pill--paid', PembayaranDocumentRow::fromDokumen($d)['status_badge']['class']);
// sudah_dibayar via casing CSV uppercase "SUDAH DIBAYAR"
$d2 = $this->makeDokumen(['status_pembayaran' => 'SUDAH DIBAYAR']);
$this->assertSame('sudah_dibayar', PembayaranDocumentRow::fromDokumen($d2)['status_badge']['state']);
// siap_dibayar: current_handler=pembayaran, belum bayar
$d3 = $this->makeDokumen(['current_handler' => 'pembayaran']);
$this->assertSame('siap_dibayar', PembayaranDocumentRow::fromDokumen($d3)['status_badge']['state']);
// belum_siap: current_handler=akutansi
$d4 = $this->makeDokumen(['current_handler' => 'akutansi']);
$this->assertSame('belum_siap_dibayar', PembayaranDocumentRow::fromDokumen($d4)['status_badge']['state']);
// edit-anywhere: can_edit true walau bukan di pembayaran
$this->assertTrue(PembayaranDocumentRow::fromDokumen($d4)['can_edit']);
// tanpa deadline
$this->assertArrayNotHasKey('deadline', PembayaranDocumentRow::fromDokumen($d4));
```
- [ ] **Step 2: Jalankan — pastikan GAGAL**
Run: `php artisan test --filter=PembayaranDocumentRowTest`
Expected: FAIL (class not found).
- [ ] **Step 3: Implementasi `app/Support/PembayaranDocumentRow.php`** sesuai kontrak. Docblock jelaskan: edit-anywhere, tanpa deadline, tanpa forward, INCLUDE CSV di endpoint pemanggil, prasyarat eager-load (dibayarKepadas + dokumenPos RELASI + roleData pembayaran + roleStatuses).
- [ ] **Step 4: Jalankan — pastikan LULUS**
Run: `php artisan test --filter=PembayaranDocumentRowTest` → PASS.
- [ ] **Step 5: Commit**
```bash
git add app/Support/PembayaranDocumentRow.php
git add tests/Unit/PembayaranDocumentRowTest.php
git commit -m "feat(pembayaran): PembayaranDocumentRow DTO (pill 3-state server, edit-anywhere, tanpa deadline)"
```

---

## Task 2: Endpoint `documents.pembayaran.data` + `buildPembayaranQuery` (include CSV)

**Files:**
- Modify: `app/Http/Controllers/DashboardPembayaranController.php` (tambah `datatableTabulator()` + `buildPembayaranQuery()` atau reshape; JANGAN hapus `datatable()`/`buildPembayaranDashboardQuery` lama dulu — dipakai renderer bespoke sampai Task 6)
- Modify: `routes/web.php` (tambah route `documents.pembayaran.data`)
- Create: `tests/Feature/PembayaranDatatableTest.php`

**Interfaces:**
- Consumes: `PembayaranDocumentRow::fromDokumen(...)` (Task 1).
- Produces: `datatableTabulator(Request): JsonResponse` → `{last_page,total,data}`; `buildPembayaranQuery(Request): Builder`.

- [ ] **Step 1: Tulis feature test yang gagal**:
```php
public function test_endpoint_pembayaran_data_json_shape_dan_include_csv(): void
{
    $user = User::factory()->create(['role' => 'pembayaran']);
    // 1 dokumen normal + 1 dokumen imported_from_csv=true
    $res = $this->actingAs($user)->getJson(route('documents.pembayaran.data'));
    $res->assertOk()->assertJsonStructure(['last_page','total','data' => [['dokumen_id','status_badge','can_edit']]]);
    // dokumen CSV harus IKUT muncul
}
```
- [ ] **Step 2: Jalankan — GAGAL** (route belum ada).
- [ ] **Step 3: `buildPembayaranQuery(Request): Builder`** — dasarnya `buildPembayaranDashboardQuery` (INCLUDE CSV: `Dokumen::whereNotNull('nomor_agenda')` + filter status_pembayaran/year/month/date/vendor/kategori/dll), TAMBAH eager-load relasi yg dibutuhkan DTO: `dibayarKepadas`, `dokumenPos` (RELASI, bukan withCount — pelajaran verifikasi), `roleData`(pembayaran), `roleStatuses`. Sort default `created_at desc, id desc`.
- [ ] **Step 4: `datatableTabulator(Request): JsonResponse`** — mirror `DashboardPerpajakanController::datatable()`: baca `size`/`page`, `->paginate($size)`, map tiap Dokumen → `PembayaranDocumentRow::fromDokumen($d, [], 'pembayaran')`, return `response()->json(['last_page'=>$p->lastPage(),'total'=>$p->total(),'data'=>$rows])`.
- [ ] **Step 5: Route** di grup `documents.pembayaran.` (routes/web.php ~:418-427):
```php
Route::get('/data', [DashboardPembayaranController::class, 'datatableTabulator'])->name('data');
```
- [ ] **Step 6: Jalankan test → LULUS**; lalu suite penuh `php artisan test` → hijau.
- [ ] **Step 7: Commit** (per-file: controller, routes, test).

---

## Task 3: Perubahan engine ADITIF (paymentPill + suppress handler + CFG.frozen)

**Files:**
- Modify: `public/js/document-tabulator.js`

⚠️ **GERBANG KRITIS global.** Semua aditif; role lain (tak set field baru) byte-identik.

**Interfaces:**
- Produces: engine mendukung `CFG.extraColumns[].formatter === 'paymentPill'`; `CFG.showHandler === false` (suppress kolom Pengurus Dokumen); `CFG.frozen = {left:[keys], right:[keys]}`.

- [ ] **Step 1: Formatter `paymentPill`** — tambah ke `EXTRA_FORMATTERS` (:598-601). Render `row.status_badge`:
```js
function fmtPaymentPill(cell) {
  const b = cell.getRow().getData().status_badge;
  if (!b || !b.class) return '-';
  return '<span class="status-pill ' + esc(b.class) + '"><i class="fas fa-circle"></i> ' + esc(b.text) + '</span>';
}
// EXTRA_FORMATTERS: { deadline, akutansiStatus, ..., paymentPill: fmtPaymentPill }
```
- [ ] **Step 2: Suppress kolom "Pengurus Dokumen"** — di `buildColumns` (:757), bungkus push kolom handler:
```js
if (cfg.showHandler !== false) {
  cols.push({ title: 'Pengurus Dokumen', field: 'handler', formatter: fmtHandler, editable: false });
}
```
Role lain tak set `showHandler` → `!== false` true → kolom tetap ada (paritas).
- [ ] **Step 3: Dukungan `CFG.frozen`** — di `buildColumns`, saat menyusun kolom `cfg.columns`, set `frozen:true` untuk key yang ada di `cfg.frozen.left`/`cfg.frozen.right`. **Prasyarat:** view HARUS mengirim `cfg.columns` sudah dalam urutan `FrozenColumnLayout::renderOrder` (kiri→bebas→kanan) agar Tabulator membekukan left ke tepi kiri & right ke tepi kanan (Tabulator membekukan kolom frozen di awal ke kiri, di akhir ke kanan). Bila `cfg.frozen` tak diset (role lain), perilaku lama (hanya No + nomor_agenda frozen) DIPERTAHANKAN.
```js
// dalam forEach cfg.columns:
const fl = (cfg.frozen && cfg.frozen.left) || [];
const fr = (cfg.frozen && cfg.frozen.right) || [];
if (fl.indexOf(c.key) !== -1 || fr.indexOf(c.key) !== -1) { def.frozen = true; }
```
- [ ] **Step 4: VALIDASI feasibility freeze (spike) + SURFACE bila gagal** — verifikasi (dokumentasi Tabulator 6.3.1 + uji lokal ringan) bahwa frozen-column native mendukung **kiri DAN kanan dinamis** dengan lebar variabel. Bila ada batasan yang mengubah UX (mis. right-freeze tak jalan mulus, atau bentrok `selectableRange`), **STOP & laporkan ke controller** (DONE_WITH_CONCERNS) — jangan kompromi UX diam-diam.
- [ ] **Step 5: Regresi role lain** — `php artisan test` hijau + `node --check public/js/document-tabulator.js`. Perilaku operator/akutansi/perpajakan/verifikasi tak berubah (tak set field baru).
- [ ] **Step 6: Commit**
```bash
git add public/js/document-tabulator.js
git commit -m "feat(engine): dukung paymentPill + showHandler=false + CFG.frozen (aditif, utk pembayaran)"
```

---

## Task 4: View Tabulator pembayaran + modal 2-tab (kustomisasi kolom + freeze) + stat cards

**Files:**
- Modify: `resources/views/pembayaranNEW/dashboardPembayaran.blade.php` (ganti renderer bespoke dgn engine Tabulator; port modal 2-tab; wire freeze ke `CFG.frozen`)
- Create (bila perlu CSS pill terpisah): `public/css/pembayaran-pill.css`

**Interfaces:**
- Consumes: endpoint `documents.pembayaran.data` (Task 2); engine `CFG` (Task 3).

- [ ] **Step 1: Bangun `window.DOCUMENT_TABULATOR_CONFIG`** (mirror `daftarPerpajakanTabulator.blade.php`, tapi dgn spesifik pembayaran):
  - `mountId`, `dataUrl: '{{ route("documents.pembayaran.data") }}'`.
  - `columns`: dari katalog 48-kolom (`getPembayaranDashboardAvailableColumns`) yang terpilih user, DIURUTKAN via `FrozenColumnLayout::renderOrder($selected,$left,$right)` (kiri→bebas→kanan). Tiap item `{key,label}`.
  - `extraColumns: [{title:'Status', field:'status_badge', formatter:'paymentPill'}]`.
  - `showHandler: false` (tanpa forward).
  - `frozen: { left: $frozenLeft, right: $frozenRight }` (dari config beku ternormalisasi `FrozenColumnLayout::normalize`).
  - Editable columns: pastikan engine `editableGate` mengizinkan set kolom editable pembayaran (~30, `getPembayaranInlineEditOptions`/`getPembayaranDateEditableColumns`) saat `can_edit=true`. Bila engine butuh daftar non-editable/tipe editor per kolom, sediakan via config yang sudah dipakai role lain.
- [ ] **Step 2: Port modal 2-tab** (dari :2747-2865 + JS :3027-3113, :3289-3306) ke view baru: Tab "Kolom Tabel" (pilih+urut) & Tab "Kolom Beku" (kiri/bebas/kanan). **Persistensi pakai kunci yang SAMA** (`table_columns_preferences['pembayaran_dashboard']` + `['pembayaran_dashboard_frozen']` + sentinel `frozen_config`). On save → set ulang kolom & `frozen` Tabulator (reload config / `table.setColumns`) TANPA reload halaman penuh bila memungkinkan.
- [ ] **Step 3: Pertahankan 3 kartu agregat** Aman/Peringatan/Terlambat (index() :278-333, tak berubah) + toolbar filter (status_pembayaran/year/month/vendor/dll) — nama field DOM generik agar engine `toolbarFilterControls` membacanya.
- [ ] **Step 4: CSS pill** — pastikan `.status-pill`/`--ready`/`--paid`/`--pending` tersedia (port dari god-file bila inline). Muat via `@push`.
- [ ] **Step 5: index() sajikan Tabulator** — `index()` render view dgn config Tabulator sebagai default; simpan renderer bespoke di belakang flag `?classic` SEMENTARA utk banding QA (hapus di Task 6). Pertahankan penyediaan katalog kolom/preferensi/stat.
- [ ] **Step 6: Smoke test** feature: `GET route('documents.pembayaran.index')` (`/documents/pembayaran/daftar`) → 200 + memuat `DOCUMENT_TABULATOR_CONFIG`.
- [ ] **Step 7: Suite hijau + commit** (per-file: view, css, controller).

---

## Task 5: 🚦 QA GATE — Playwright produksi (USER)

**BERHENTI. Gerbang user.** Deploy dulu (push→pull→clear cache), lalu QA READ-ONLY (login `pembayaran`/`12345678`):
- [ ] Tabel muat, 0 error konsol.
- [ ] Pill 3-state (Belum Siap/Siap/Sudah Dibayar) cocok sampel (banding `?classic`), termasuk baris CSV (casing uppercase → Sudah Dibayar).
- [ ] **Freeze 2-tab kiri/bebas/kanan berfungsi + persist** setelah reload. Kustomisasi kolom (pilih+urut) jalan.
- [ ] **Edit-anywhere**: dokumen yang BELUM di pembayaran pun bisa di-inline-edit; `tanggal_dibayar` editable.
- [ ] Copy/paste/multiselect/undo (§8) berfungsi; arrow-nav tetap.
- [ ] Kartu agregat Aman/Peringatan/Terlambat cocok. Baris CSV tampil.
- [ ] Export & CSV import (di luar scope) MASIH jalan.

**Acceptance:** user konfirmasi paritas + freeze OK. Gagal → perbaiki, JANGAN lanjut Task 6.

---

## Task 6: Cleanup grep-gated (surface DULU) + hapus renderer bespoke

- [ ] **Step 1: Grep-gate** kandidat: renderer bespoke (`renderFallbackRows`/`loadFallbackRows`/`initFallbackTableScroll`), endpoint lama `datatable()`+`buildPembayaranDashboardQuery`+route `dashboard.pembayaran.data`, `DOCUMENT_STICKY_CONFIG`+sticky-CSS server, flag `?classic`, DAN kode aksi tampak-mati: `setDeadline`(pembayaran)/`updateStatus`/`uploadBukti`/`getPaymentData`/`getDocumentDetail` + route `documents.pembayaran.{set-deadline,update-status,upload-proof,payment-data,detail}`.
- [ ] **Step 2: 🚦 SURFACE findings ke user** — tabel "simbol → dipakai di mana → hidup/yatim". Default PERTAHANKAN aksi yang ragu (mungkin dipakai VA/rekapan/masa depan). Tunggu keputusan.
- [ ] **Step 3: Hapus hanya yang disetujui.** JANGAN sentuh CsvImportController/import, export, rekapan-vendor, VA, `FrozenColumnLayout`, kunci preferensi. Update test yang mereferensikan route lama.
- [ ] **Step 4: Suite hijau.**
- [ ] **Step 5: Update `CLAUDE.md §7`** — pembayaran Tabulator-only SELESAI (Rollout 4); catat pill/edit-anywhere/freeze-reimplement/CSV-include; catat yang dipertahankan (import/export/rekapan/VA); catat kode aksi yg dihapus/dipertahankan. Update baris "Belum dikerjakan" (kini semua role non-bagian selesai).
- [ ] **Step 6: Commit** (per-file, beberapa commit logis).

---

## Task 7: Deploy

- [ ] **Step 1: Push** `git push origin codinggemini`.
- [ ] **Step 2: Server** (SSH key `-i C:\Users\ASUS\.ssh\crypto_bot_vps root@163.61.58.92`): `cd /var/www/agenda-online-PTPN && git pull && php artisan route:clear && view:clear && config:clear && cache:clear`.
- [ ] **Step 3: Smoke produksi** (user/Playwright READ-ONLY) — tabel muat, route lama dihapus → 404, freeze/pill/edit-anywhere jalan, export/import masih hidup.

---

## Self-Review (diisi penulis plan)

- **Spec coverage:** §3.1 pill→Task1; §3.2 no-deadline→Task1; §3.3 edit-anywhere→Task1; §3.4 no-forward→Task3(showHandler); §3.5 CSV→Task2; §4 freeze/kolom→Task3(CFG.frozen)+Task4(modal); §5 endpoint→Task2; §6 §8→Task3/4(engine); §7 cleanup→Task6; §8 out-of-scope→Global Constraints; §9 testing→tiap task+Task5; §10 gerbang→Task3/5/6. ✅
- **Placeholder scan:** kode pill konkret; test konkret; view/modal via referensi line-range file nyata (bukan placeholder logika). Nama test feature implementer pilih.
- **Type consistency:** `fromDokumen` konsisten Task1↔2; `status_badge {state,class,text}` konsisten Task1↔3(paymentPill); `CFG.frozen{left,right}`/`showHandler` konsisten Task3↔4.

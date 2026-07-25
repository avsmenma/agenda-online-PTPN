# Rollout Tabulator — Verifikasi (Rollout 3) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Migrasikan tabel dokumen role `team_verifikasi` ke engine Tabulator bersama (klon pola akutansi/perpajakan), lalu hapus tabel legacy + kode aksi mati (grep-gated).

**Architecture:** DTO `App\Support\VerifikasiDocumentRow extends DocumentRow` menghitung `status_badge` (~24 cabang, diperluas dgn field opsional `style`+`title`) & `deadline` (count-up) SERVER-side; endpoint JSON `documents.verifikasi.data`; view Tabulator baru pakai `public/js/document-tabulator.js`. Forward tetap via dropdown Pengurus Dokumen. Nol arsitektur baru.

**Tech Stack:** Laravel 12, PHP ^8.2, Blade, Tabulator 6.3.1 (self-hosted), PHPUnit.

**Spec:** `docs/superpowers/specs/2026-07-25-rollout-tabulator-verifikasi-design.md`

## Global Constraints

- **Commit per-file** (`git add <file>`, JANGAN `git add .`/`-A`). Pesan commit Bahasa Indonesia. Satu commit = satu perubahan logis.
- **UI/komentar Indonesia, identifier English.**
- **Suite hijau** (`php artisan test`) sebelum tiap commit.
- **Nol logika bisnis di JS** — klien hanya merender objek `status_badge`/`deadline` dari server.
- **Paritas data byte-identik `dokumens()` legacy:** eager-load `roleData` HANYA `role_code='team_verifikasi'`; `roleStatuses` 4 role `['team_verifikasi','perpajakan','akutansi','pembayaran']`; `dibayarKepadas` (relasi). DTO endpoint TAMBAHAN eager-load relasi `dokumenPos` (base `DocumentRow` pakai `->dokumenPos->pluck('nomor_po')`).
- **Perubahan `document-tabulator.js` (engine global) WAJIB aditif** — field baru opsional; role operator/akutansi/perpajakan tak boleh berubah perilakunya.
- **JANGAN sentuh** `returns.verifikasi.*` / `pengembalianKeBidang` / `returnToBidang` (satu-satunya return hidup).
- **Sebelum menghapus kode aksi apa pun (Task 6): grep-gate + surface findings ke user DULU.**
- **QA visual = tanggung jawab user** (Playwright READ-ONLY, login `verifikasi`/`12345678`). Tidak bisa diklaim selesai dari test backend.

---

## Referensi template (BACA sebelum mulai — ini artefak nyata yang di-mirror)

| Peran | File template (perpajakan, Rollout 2) |
|---|---|
| DTO | `app/Support/PerpajakanDocumentRow.php` (struktur `statusContext`/`buildStatusBadge`/`buildDeadline`) |
| DTO basis | `app/Support/DocumentRow.php` (`baseRow`, `formatDates`, `normalizeRole`) |
| Endpoint | `app/Http/Controllers/DashboardPerpajakanController.php` — `datatable()` (baris 44), `dokumens()` (315), `buildPerpajakanQuery()`, `buildPerpajakanHandlerOptions()` |
| Route | `routes/web.php` — grup `documents.perpajakan.` + route `.data` (`documents.perpajakan.data`) |
| View | `resources/views/perpajakan/dokumens/daftarPerpajakanTabulator.blade.php` (wiring `window.DOCUMENT_TABULATOR_CONFIG`, `extraColumns`) |
| Engine | `public/js/document-tabulator.js` (`EXTRA_FORMATTERS`, `toolbarFilterControls()`) |
| CSS | `public/css/perpajakan-deadline-badge.css` |
| Unit test | `tests/Unit/PerpajakanDocumentRowTest.php` (fixture Dokumen + roleStatuses + display_status) |
| Feature test | test endpoint `documents.perpajakan.data` (cari di `tests/Feature/`) |

**Sumber porting verifikasi (BACA — ini yang diporting, urutan cabang WAJIB dipertahankan):**
- Setup context: `resources/views/team_verifikasi/dokumens/_rows.blade.php:3-119` (+ `:307` `$isReturnedToVerifikasi`).
- Badge cascade: `_rows.blade.php:455-608` (24 cabang).
- Deadline: `_rows.blade.php:300-451` (count-up; identik semantik `AkutansiDocumentRow::buildDeadline`).
- Eager-load & is_at_my_role legacy: `TeamVerifikasiController::dokumens()` `:456-517`.

---

## Task 1: `VerifikasiDocumentRow` DTO + unit test

**Files:**
- Create: `app/Support/VerifikasiDocumentRow.php`
- Create: `tests/Unit/VerifikasiDocumentRowTest.php`

**Interfaces:**
- Consumes: `App\Support\DocumentRow::baseRow(Dokumen, array $handlerOptions, ?string $viewerRole): array`; `Dokumen::getDataForRole(string): ?DokumenRoleData`; `Dokumen::getDisplayStatusForRole(string): ?string`; `Dokumen::getFinalStatusLabel(string): string`; `Dokumen::getDetailedApprovalText(): string`.
- Produces: `VerifikasiDocumentRow::fromDokumen(Dokumen $dokumen, array $handlerOptions, ?string $viewerRole = null): array` — array baris berisi kunci base + `is_at_my_role, is_locked, can_edit, status_pembayaran, status_badge, deadline, pemaraf`.
  - `status_badge` bentuk: `['class'=>string,'icon'=>?string,'text'=>string,'link'=>?string,'style'=>?string,'title'=>?string]` (DUA field baru `style`/`title` vs akutansi/perpajakan; null bila tak dipakai).
  - `deadline` bentuk: identik `AkutansiDocumentRow` (`variant,type,color,received_display,indicator_icon,indicator_label,age_text,footer`).

### Kontrak nilai kunci verifikasi (dari legacy — WAJIB persis)

- `is_locked` = **`false` selalu** (legacy `_rows.blade.php:52`: "Document is NO LONGER locked after approval"). JANGAN pakai `DokumenHelper::isDocumentLocked` di sini — verifikasi override ke false. Cabang badge yg bergantung `$isLocked` (`'sedang diproses' && $isLocked`, `'sent_to_team_verifikasi' && $isLocked`) jadi tak pernah menyala — itu paritas benar.
- `is_at_my_role` (port `dokumens():497-514`):
```php
$isAtMyRole = in_array($dokumen->current_handler, ['team_verifikasi'], true)
    || in_array($dokumen->status, [
        'sent_to_perpajakan','sent_to_akutansi','sent_to_pembayaran',
        'pending_approval_perpajakan','pending_approval_akutansi','pending_approval_pembayaran',
        'menunggu_di_approve','waiting_approval_perpajakan','waiting_approval_akuntansi',
        'waiting_approval_pembayaran','returned_to_verifikasi','sedang diproses','sedang_diproses',
    ], true)
    || (in_array($dokumen->status, ['completed','selesai'], true) && ! empty($dokumen->status_pembayaran))
    || ($dokumen->status === 'returned_to_department'
        && in_array($dokumen->return_source, ['perpajakan','akutansi'], true)
        && $dokumen->current_handler === 'team_verifikasi');
```
- `can_edit` = `DokumenHelper::canEditDocument($dokumen, 'team_verifikasi')`.
- `status_pembayaran` = `$dokumen->status_pembayaran`.
- `pemaraf` = `$dokumen->pemaraf` (mentah; kolom Paraf read-only). `tanggal_paraf` sudah ada via `baseRow()`→`formatDates` (format `d/m/Y H:i`).

### `statusContext(Dokumen): array` — port setup `_rows.blade.php:3-119`

Kembalikan array berisi flag yang dipakai cascade:
```php
[
  'is_returned_to_verifikasi' => $dokumen->status === 'returned_to_verifikasi',       // :307
  'is_rejected_by_team_verifikasi' => /* roleStatuses team_verifikasi status=rejected */,  // :20-23
  'is_rejected_by_other_role' => bool, 'rejected_by_role' => ?string,                  // :25-39 (hanya bila current_handler==='team_verifikasi'; ambil perpajakan|akutansi rejected terbaru by status_changed_at)
  'is_rejected' => is_rejected_by_team_verifikasi || is_rejected_by_other_role,         // :41
  'is_pending' => /* roleStatuses team_verifikasi status=pending */,                     // :44-47
  'display_status' => $dokumen->getDataForRole('team_verifikasi')?->display_status,      // :72
  'display_status_label' => display_status ? Dokumen::getFinalStatusLabel(display_status) : null, // :75
  // Fallback (HANYA saat display_status null) — port :83-118 VERBATIM:
  'is_pending_perpajakan' => bool, 'is_pending_akuntansi' => bool, 'sent_to_team_label' => ?string,
]
```
Fallback penting (`:94`): `$perpajakanRoleData = $dokumen->getDataForRole('perpajakan')` — **selalu null** karena eager-load role-own-only → cabang `$perpajakanRoleData && $perpajakanRoleData->received_at` di `$wasSentToPerpajakan` selalu false. Port apa adanya (jangan "perbaiki" jadi query).

### `buildStatusBadge(Dokumen, array $ctx): array` — port cascade `_rows.blade.php:455-608`

Urutan cabang WAJIB persis. Peta cabang→output (`icon` tanpa prefix `fa-solid`, engine yg menambah):

| # | Kondisi | class | icon | text | style | title |
|---|---|---|---|---|---|---|
| 1 | `is_returned_to_verifikasi` | `badge-proses` | `fa-inbox` | `Kembali dari {returnedByLabel}` | – | `return_reason ? 'Catatan: '.reason : 'Kembali ke Team Verifikasi'` |
| 2a | `is_rejected` & `is_rejected_by_other_role` & `rejected_by_role` | `badge-dikembalikan` | `fa-times-circle` | `Dokumen ditolak` | – | – |
| 2b | `is_rejected` (else) | `badge-proses` | – | `⏳ Draft` | – | – |
| 3 | `status ∈ {selesai, approved_Team Verifikasi}` | `badge-selesai` | – | `✓ {status==='approved_Team Verifikasi'?'Approved':'Selesai'}` | – | – |
| 4 | `status==='rejected_Team Verifikasi'` | `badge-dikembalikan` | – | `Rejected` | – | – |
| 5 | `status==='returned_to_bidang'` | `badge-dikembalikan` | – | `Dikembalikan ke {return_source?strtoupper:'Bagian'}` | – | – |
| 6 | `display_status_label` truthy → sub: | | | | | |
| 6a | `str_starts_with(display_status,'terkirim')` | `badge-sent` | – | `📤 {display_status_label}` | – | – |
| 6b | `str_starts_with(display_status,'menunggu')` | `badge-warning` | `fa-clock` | `{display_status_label}` | – | – |
| 6c | `display_status==='sedang_diproses'` | `badge-proses` | – | `⏳ {display_status_label}` | – | – |
| 6d | `display_status==='terkunci'` | `badge-locked` | – | `🔒 {display_status_label}` | – | – |
| 6e | else | `badge-proses` | – | `{display_status_label}` | – | – |
| 7 | `is_pending_perpajakan` | `badge-warning` | `fa-clock` | `Menunggu Approval dari Team Perpajakan` | `background: linear-gradient(135deg,#ffc107 0%,#ff8c00 100%); color: white;` | – |
| 8 | `is_pending_akuntansi` | `badge-warning` | `fa-clock` | `Menunggu Approval dari Team Akutansi` | (gradient kuning-oranye sama #7) | – |
| 9 | `sent_to_team_label` | `badge-sent` | – | `📤 Terkirim ke {sent_to_team_label}` | – | – |
| 10 | `status==='sent_to_perpajakan'` | `badge-sent` | – | `📤 Terkirim ke Team Perpajakan` | – | – |
| 11 | `status==='sent_to_akutansi'` | `badge-sent` | – | `📤 Terkirim ke Team Akutansi` | – | – |
| 12 | `status==='sent_to_pembayaran'` | `badge-sent` | – | `📤 Terkirim ke Team Pembayaran` | – | – |
| 13 | `status==='waiting_approval_perpajakan'` | `badge-warning` | `fa-clock` | `Menunggu Approve Team Perpajakan` | (gradient kuning-oranye) | – |
| 14 | `status==='waiting_approval_akuntansi'` | `badge-warning` | `fa-clock` | `Menunggu Approve Team Akuntansi` | (gradient kuning-oranye) | – |
| 15 | `status==='waiting_approval_pembayaran'` | `badge-warning` | `fa-clock` | `Menunggu Approve Team Pembayaran` | `background: linear-gradient(135deg,#6610f2 0%,#9b4dca 100%); color: white;` (UNGU) | – |
| 16 | `status ∈ {menunggu_di_approve,waiting_reviewer_approval,pending_approval_perpajakan,pending_approval_akutansi,pending_approval_team_verifikasi}` \|\| `is_pending` | `` (kosong) | `fa-clock` | `{dokumen->getDetailedApprovalText()}` | (gradient kuning-oranye) | – |
| 17 | `status==='sedang diproses' && is_locked` | `badge-locked` | – | `🔒 Terkunci` | – | – |
| 18 | `status==='sedang diproses'` | `badge-proses` | – | `⏳ Sedang Diproses` | – | – |
| 19 | `status==='sent_to_team_verifikasi' && !is_locked` | `badge-proses` | – | `⏳ Diproses` | – | – |
| 20 | `status==='sent_to_team_verifikasi' && is_locked` | `badge-locked` | – | `🔒 Terkunci` | – | – |
| 21 | `status==='returned_to_operator'` | `badge-dikembalikan` | – | `Dikembalikan ke Ibu A` | – | – |
| 22 | `status==='returned_to_department'` → sub `is_workflow_return` (`return_source ∈{perpajakan,akutansi,pembayaran}` & `current_handler==='team_verifikasi'` & `!is_rejected`) | | | | | |
| 22a | is_workflow_return | `badge-proses` | `fa-inbox` | `Kembali dari {workflowReturnLabel}` | – | – |
| 22b | else | `badge-dikembalikan` | – | `Dikembalikan dari {Str::title(return_source??'Bagian Terkait')}` | – | – |
| 23 | `Str::startsWith(status,'returned_from_')` | `badge-dikembalikan` | – | `Dikembalikan dari {sourceLabel}` | – | – |
| 24 | else | `badge-proses` | – | `⏳ {ucfirst(status)}` | – | – |

Label helper (port `:458-463`, `:579-584`, `:599-603`): `returnedByLabel`/`workflowReturnLabel` = `match(return_source){perpajakan→'Team Perpajakan', akutansi→'Team Akutansi', pembayaran→'Team Pembayaran', default→Str::title(return_source??'...')}`. `sourceLabel` (returned_from_) = `match(Str::after(status,'returned_from_')){akutansi→'Team Akutansi', perpajakan→'Team Perpajakan', default→Str::title(str_replace('_',' ',source))}`.

Semua cabang tanpa `link` → `'link'=>null`. Cabang tanpa `style`/`title` → `null`.

### `buildDeadline(Dokumen): array` — port `_rows.blade.php:300-451`

Struktur IDENTIK `AkutansiDocumentRow::buildDeadline()` (baca sebagai template): Path A (received → kartu umur count-up, ambang AMAN<24j/PERINGATAN 24–72j/TERLAMBAT ≥72j, beku saat sent/paused/completed dgn `⏸️`), Path C (belum diterima → `variant:'none'`). `$isReturned = status==='returned_to_verifikasi'`. **BACA `_rows.blade.php:300-451` dan port persis** — bila legacy verifikasi TIDAK punya jalur bypass (Path B), JANGAN tambahkan; bila ada, port. Sumber `roleData = getDataForRole('team_verifikasi')`.

- [ ] **Step 1: Tulis unit test yang gagal** — `tests/Unit/VerifikasiDocumentRowTest.php`. Mirror fixture `tests/Unit/PerpajakanDocumentRowTest.php` (buat Dokumen + `roleStatuses` + `roleData` team_verifikasi + `display_status`). Tulis kasus (assert `status_badge` persis):

```php
// Cabang 1: returned_to_verifikasi
$d = $this->makeDokumen(['status'=>'returned_to_verifikasi','return_source'=>'perpajakan','current_handler'=>'team_verifikasi']);
$row = VerifikasiDocumentRow::fromDokumen($d, [], 'team_verifikasi');
$this->assertSame('badge-proses', $row['status_badge']['class']);
$this->assertSame('fa-inbox', $row['status_badge']['icon']);
$this->assertSame('Kembali dari Team Perpajakan', $row['status_badge']['text']);

// Cabang 2a: ditolak oleh role lain (current_handler team_verifikasi + perpajakan rejected)
// → assert class 'badge-dikembalikan', icon 'fa-times-circle', text 'Dokumen ditolak'

// Cabang 3: selesai → class 'badge-selesai', text '✓ Selesai'
// Cabang 6a: display_status='terkirim_akutansi' → class 'badge-sent', text mulai '📤 '
// Cabang 13: status 'waiting_approval_perpajakan' → class 'badge-warning', style mengandung 'linear-gradient', 'ffc107'
// Cabang 15: status 'waiting_approval_pembayaran' → style mengandung '6610f2' (ungu)
// Cabang 24: status tak dikenal (mis. 'foo_bar') → class 'badge-proses', text '⏳ Foo_bar'

// Deadline Path A AMAN: roleData received_at = now()->subHours(2), status 'sedang diproses'
// → deadline['variant']='card', color='green', indicator_label='AMAN'
// Deadline Path A TERLAMBAT: received_at = now()->subHours(80) → color='red', indicator_label='TERLAMBAT'
// Deadline Path C: tanpa roleData received_at → variant='none'
// Paritas: is_locked selalu false; getDataForRole('perpajakan') null (tak di-eager-load)
```
- [ ] **Step 2: Jalankan test — pastikan GAGAL**
Run: `php artisan test --filter=VerifikasiDocumentRowTest`
Expected: FAIL ("Class VerifikasiDocumentRow not found").
- [ ] **Step 3: Implementasi `app/Support/VerifikasiDocumentRow.php`** — `fromDokumen` + `statusContext` + `buildStatusBadge` + `buildDeadline` sesuai kontrak di atas. Header docblock jelaskan prasyarat eager-load (roleData team_verifikasi-only; roleStatuses 4 role; dokumenPos relasi) & bahwa `getDataForRole('perpajakan'/'akutansi'/'pembayaran')` = null (parity).
- [ ] **Step 4: Jalankan test — pastikan LULUS**
Run: `php artisan test --filter=VerifikasiDocumentRowTest`
Expected: PASS.
- [ ] **Step 5: Commit**
```bash
git add app/Support/VerifikasiDocumentRow.php
git add tests/Unit/VerifikasiDocumentRowTest.php
git commit -m "feat(verifikasi): VerifikasiDocumentRow DTO (badge ~24 cabang + deadline count-up server)"
```

---

## Task 2: Endpoint JSON `documents.verifikasi.data`

**Files:**
- Modify: `app/Http/Controllers/TeamVerifikasiController.php` (ekstrak `buildVerifikasiQuery()`, tambah `datatable()`, `buildVerifikasiHandlerOptions()`)
- Modify: `routes/web.php:363-371` (tambah route `.data`)
- Create/Modify: test feature endpoint (di `tests/Feature/`)

**Interfaces:**
- Consumes: `VerifikasiDocumentRow::fromDokumen(...)` (Task 1); `App\Support\DocumentHandlerOptions` atau pola `buildPerpajakanHandlerOptions()` (mirror).
- Produces: `TeamVerifikasiController::datatable(Request): JsonResponse` → `{data: [...rows]}`; `buildVerifikasiQuery(Request): Builder` (dipakai `dokumens()` & `datatable()`).

- [ ] **Step 1: Tulis feature test yang gagal** — endpoint mengembalikan JSON untuk viewer verifikasi:
```php
public function test_endpoint_data_verifikasi_mengembalikan_json(): void
{
    $user = User::factory()->create(['role' => 'team_verifikasi']); // sesuaikan kolom role app
    $dok = /* buat 1 Dokumen minimal di team_verifikasi */;
    $res = $this->actingAs($user)->getJson(route('documents.verifikasi.data'));
    $res->assertOk()->assertJsonStructure(['data' => [['id','status_badge','deadline','handler']]]);
}
```
- [ ] **Step 2: Jalankan — pastikan GAGAL** (route belum ada).
Run: `php artisan test --filter=<NamaTestFeature>`
Expected: FAIL (route not defined / 404).
- [ ] **Step 3: Ekstrak `buildVerifikasiQuery(Request): Builder`** dari `dokumens()` — pindahkan pembangunan query (select + leftJoin team_verifikasi_data + filter + sort + eager-load `dibayarKepadas`,`roleData`(team_verifikasi),`roleStatuses`(4 role) + `withCount`) TAPI **tambah eager-load relasi `dokumenPos`** (base DTO butuh). `dokumens()` dan `datatable()` sama-sama memanggilnya. JANGAN ubah perilaku `dokumens()` (masih render legacy di Task 2; diubah di Task 4).
- [ ] **Step 4: Tambah `datatable(Request): JsonResponse`** — mirror `DashboardPerpajakanController::datatable()`: ambil `buildVerifikasiQuery`, paginate/get, `$handlerOptions = $this->buildVerifikasiHandlerOptions(...)`, map tiap Dokumen → `VerifikasiDocumentRow::fromDokumen($d, $handlerOptions, 'team_verifikasi')`, set `is_at_my_role` transform bila perlu, return `response()->json(['data'=>$rows])`. Tambah `buildVerifikasiHandlerOptions()` (mirror perpajakan).
- [ ] **Step 5: Daftarkan route** di `routes/web.php` grup `documents.verifikasi.` (:363-371), setelah `.index`:
```php
Route::get('/data', [TeamVerifikasiController::class, 'datatable'])->name('data');
```
- [ ] **Step 6: Jalankan test — pastikan LULUS**
Run: `php artisan test --filter=<NamaTestFeature>`
Expected: PASS.
- [ ] **Step 7: Suite penuh + commit**
Run: `php artisan test`
```bash
git add app/Http/Controllers/TeamVerifikasiController.php
git add routes/web.php
git add tests/Feature/<NamaTestFeature>.php
git commit -m "feat(verifikasi): endpoint documents.verifikasi.data + buildVerifikasiQuery bersama"
```

---

## Task 3: View Tabulator + perluasan formatter (aditif) + CSS

**Files:**
- Create: `resources/views/team_verifikasi/dokumens/daftarDokumenTabulator.blade.php`
- Modify: `public/js/document-tabulator.js` (perluas formatter status aditif: baca `style`/`title` opsional)
- Create: `public/css/verifikasi-deadline-badge.css` (atau reuse + tambah kelas verifikasi: `badge-dikembalikan`, gradients — cek yg sudah ada)

**Interfaces:**
- Consumes: endpoint `documents.verifikasi.data` (Task 2); engine `DOCUMENT_TABULATOR_CONFIG`.
- Produces: view route `documents.verifikasi.index` (dipakai Task 4).

⚠️ **GERBANG KRITIS (partial global `document-tabulator.js`):** perubahan formatter WAJIB aditif. Formatter status render `style`/`title` HANYA bila field ada; role lain (yang tak set field itu) tak berubah. Jangan ubah cabang lama.

- [ ] **Step 1: Perluas formatter status di `document-tabulator.js`** — pada formatter yang merender objek `status_badge` (dipakai akutansi/perpajakan, mis. `EXTRA_FORMATTERS.akutansiStatus`), tambah render opsional:
  - bila `badge.style` truthy → tambahkan ke atribut `style` `<span>`.
  - bila `badge.title` truthy → tambahkan atribut `title`.
  - Perilaku existing (class/icon/text/link) tak berubah. Verifikasi memakai formatter yang sama.
- [ ] **Step 2: Buat view `daftarDokumenTabulator.blade.php`** — mirror `daftarPerpajakanTabulator.blade.php`:
  - `window.DOCUMENT_TABULATOR_CONFIG` dengan `mountId`, `dataUrl: route('documents.verifikasi.data')`, `extraColumns` untuk kolom **Deadline** (`formatter:'deadline'`), **Status** (`formatter:'akutansiStatus'` — sama, kini mendukung style/title), dan **Paraf** read-only (`tanggal_paraf`, `pemaraf`; render `pemaraf` sebagai badge `badge-paraf-done` bila ada — port `_rows.blade.php:225-233`).
  - Toolbar filter: pakai nama field DOM generik (engine baca `.tabulator-toolbar [name]`). Sertakan filter yang sama dgn legacy verifikasi (status, keterlambatan bila ada).
  - Muat CSS deadline+badge via `@push`.
- [ ] **Step 3: CSS** — pastikan kelas badge verifikasi ada (`badge-dikembalikan`, `badge-paraf-done`, `badge-sent`, `badge-warning`, `badge-locked`, `badge-proses`, `badge-selesai`). Reuse CSS akutansi/perpajakan bila kelasnya sama; tambah yang khas verifikasi (`badge-dikembalikan`, `badge-paraf-done`) bila belum ada. Deadline card CSS reuse.
- [ ] **Step 4: Verifikasi engine tak regresi** (operator/akutansi/perpajakan) — jalankan suite + (bila ada) test JS/lint.
Run: `php artisan test`
Expected: PASS (tak ada test PHP yg menyentuh formatter; regresi visual dicek user di Task 5).
- [ ] **Step 5: Commit** (per-file)
```bash
git add public/js/document-tabulator.js
git commit -m "feat(engine): formatter status dukung field opsional style+title (aditif, utk verifikasi)"
git add resources/views/team_verifikasi/dokumens/daftarDokumenTabulator.blade.php
git add public/css/verifikasi-deadline-badge.css
git commit -m "feat(verifikasi): view Tabulator daftarDokumenTabulator + CSS deadline/badge"
```

---

## Task 4: Alihkan `dokumens()` menyajikan view Tabulator

**Files:**
- Modify: `app/Http/Controllers/TeamVerifikasiController.php` (`dokumens()` render view baru; flag transisi `?classic` sementara)

**Interfaces:**
- Consumes: view `daftarDokumenTabulator` (Task 3); `buildVerifikasiQuery` (Task 2).
- Produces: `documents.verifikasi.index` menyajikan Tabulator secara default.

- [ ] **Step 1: Ubah `dokumens()`** — default `return view('team_verifikasi.dokumens.daftarDokumenTabulator', [...])`. Simpan cabang legacy di belakang `$request->boolean('classic')` (view lama `daftarDokumen`) SEMENTARA untuk perbandingan QA. Pertahankan penyediaan `$selectedColumns`, stats, dropdown IE data yang masih dipakai view baru. Cabang `virtual_chunk` dibiarkan sampai Task 6.
- [ ] **Step 2: Smoke test** — tabel merender & endpoint terpanggil:
```php
public function test_halaman_verifikasi_menyajikan_tabulator(): void
{
    $user = /* user team_verifikasi */;
    $res = $this->actingAs($user)->get(route('documents.verifikasi.index'));
    $res->assertOk()->assertSee('DOCUMENT_TABULATOR_CONFIG', false);
}
```
- [ ] **Step 3: Jalankan test + suite**
Run: `php artisan test`
Expected: PASS.
- [ ] **Step 4: Commit**
```bash
git add app/Http/Controllers/TeamVerifikasiController.php
git add tests/Feature/<smoke>.php
git commit -m "feat(verifikasi): dokumens() sajikan view Tabulator (legacy di belakang ?classic sementara)"
```

---

## Task 5: 🚦 QA GATE — Playwright produksi (USER)

**BERHENTI. Ini gerbang user.** Deploy ke server dulu (commit→push→pull→clear cache, lihat Task 7 alur), lalu QA READ-ONLY (login `verifikasi`/`12345678`):

- [ ] Tabel muat, 0 error console.
- [ ] Badge Status byte-cocok sampel (bandingkan `?classic=1` vs Tabulator): cek cabang returned_to_verifikasi, ditolak, terkirim, waiting_approval (gradient kuning & ungu), sedang diproses, draft.
- [ ] Kolom Deadline: AMAN/PERINGATAN/TERLAMBAT + beku (⏸️) cocok.
- [ ] Kolom Paraf (`tanggal_paraf`/`pemaraf`) tampil read-only benar.
- [ ] Filter toolbar aktif (status, keterlambatan).
- [ ] Dropdown Pengurus Dokumen forward berfungsi.
- [ ] `returns.verifikasi.*` (`/returns/verifikasi/bagian`) masih hidup.
- [ ] Inline edit / navigasi sel / copy-paste sesuai CLAUDE.md §8.

**Acceptance:** user konfirmasi paritas visual. Bila gagal → perbaiki, JANGAN lanjut Task 6.

---

## Task 6: Cleanup grep-gated (surface findings DULU) + hapus legacy

**Files (kandidat — hanya setelah grep + persetujuan user):**
- Delete: `resources/views/team_verifikasi/dokumens/daftarDokumen.blade.php` (6046), `_rows.blade.php`, `_chunk.blade.php`
- Modify: `TeamVerifikasiController.php` (hapus cabang `virtual_chunk`, method aksi yatim), `routes/web.php`
- Modify: `CLAUDE.md` §7

- [ ] **Step 1: Grep-gate SEMUA kandidat hapus** (lintas `resources/`+`routes/`+`app/`): `parafDokumen`, `documents.verifikasi.paraf`, `openParafModal`, `sendToNextHandler`, `documents.verifikasi.send-to-next`, `returnToDepartment`, `return-to-department`, `returnToOperator`, `return-to-owner`, `getDocumentDetail`, `documents.verifikasi.detail`, `generateDocumentDetailHtml`, `virtual_chunk`, `?classic`. Cek pemakai tiap simbol.
- [ ] **Step 2: 🚦 SURFACE findings ke user** — laporkan tabel "simbol → dipakai di mana → hidup/yatim". Termasuk temuan terpisah: route `returns.verifikasi.restore-from-bidang`→`restoreFromBidang` menunjuk **method yang tak ada** (broken) — laporkan, JANGAN diam-diam perbaiki/hapus. Tunggu keputusan user tentang apa yg boleh dihapus. **Default PERTAHANKAN** `acceptDocument`/`rejectDocument` (kemungkinan Inbox), `checkRejectedDocuments`/`showRejectedDocument` (polling).
- [ ] **Step 3: Hapus hanya yang disetujui** — file view legacy + `_rows`+`_chunk` (bawa serta modal Paraf/JS/CSS di dalamnya), cabang `virtual_chunk`, flag `?classic` (jadi no-op), method aksi yatim yang di-ACC user + route-nya. Update test yang mereferensikan route lama (flip assertion "ada"→"tiada", mirror `AkutansiHapusAksiMatiTest`).
- [ ] **Step 4: Suite hijau**
Run: `php artisan test`
Expected: PASS.
- [ ] **Step 5: Update `CLAUDE.md` §7** — tandai verifikasi Tabulator-only SELESAI; catat Paraf pensiun (kode dihapus); catat apa yang dipertahankan (accept/reject/inbox); catat temuan `restore-from-bidang` broken; tegaskan `returns.verifikasi.*` tetap hidup.
- [ ] **Step 6: Commit** (per-file, satu commit per perubahan logis)
```bash
git add resources/views/team_verifikasi/dokumens/daftarDokumen.blade.php # (deletion)
git commit -m "refactor(verifikasi): hapus tabel legacy daftarDokumen + _rows + _chunk (Tabulator-only)"
# lalu commit terpisah utk route/method mati, dan CLAUDE.md
```

---

## Task 7: Deploy

- [ ] **Step 1: Push**
```bash
git push origin codinggemini
```
- [ ] **Step 2: Server pull + clear cache** (WAJIB — cache Blade/route menyembunyikan perubahan)
```bash
# di server:
git pull
php artisan route:clear && php artisan view:clear && php artisan config:clear
```
- [ ] **Step 3: Smoke produksi** (user, Playwright READ-ONLY) — tabel muat, route lama yg dihapus → 404/no-op, `returns.verifikasi.*` hidup.

---

## Self-Review (diisi penulis plan)

- **Spec coverage:** §2 arsitektur→Task 1-4; §3.1 badge→Task 1 (peta 24 cabang); §3.2 deadline→Task 1; §3.3 Paraf read-only→Task 1/3, pensiun→Task 6; §3.4 paritas→Global Constraints + Task 2; §4 detail badge→Task 6 grep-gate; §5 cleanup→Task 6; §6 testing→tiap task + Task 5; §7 urutan→Task 1-7; §8 gerbang→Task 3/5/6. ✅
- **Placeholder scan:** tak ada TBD/TODO; kode & test konkret; nama test feature ditandai `<NamaTestFeature>` (implementer pilih nama, bukan placeholder logika).
- **Type consistency:** `fromDokumen(Dokumen,array,?string)` konsisten Task 1↔2; `status_badge` shape (+style/title) konsisten Task 1↔3; `buildVerifikasiQuery`/`datatable`/`buildVerifikasiHandlerOptions` konsisten Task 2↔4.

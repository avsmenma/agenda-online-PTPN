# DEAD_CODE_ELIMINATION_PLAN — FASE A (Read-Only)

Project: **agenda-online-PTPN** (Laravel 12)
Tanggal audit: 2026-06-29
Metode: 6 sub-agent paralel (Opus 4.8) menyapu seluruh project + verifikasi-silang manual.
Status: **FASE A SELESAI (read-only). Belum ada file yang diubah/dihapus. Menunggu persetujuan sebelum FASE B.**

> Setiap kandidat dicek terhadap pola dinamis Laravel (view dinamis, `app()`/`resolve()`, `route()`/`action()`, auto-discovery event/observer/middleware/provider/command terjadwal, endpoint API/webhook, referensi dari JS/Blade). Item "MATI-PASTI" = nol referensi statis **dan** dinamis, bukan entry-point.

---

## 1. RINGKASAN JUMLAH

| Kategori | Jumlah |
|---|---|
| **[MATI-PASTI] — file utuh** (aman dihapus) | **38 file** |
| **[MATI-PASTI] — blok/method** dalam file yang masih hidup | **30 method** (di 8 controller) |
| **[KEMUNGKINAN-MATI]** (butuh keputusan Anda) | ± 18 item |
| **[ENTRY-POINT] / [DINAMIS]** (JANGAN hapus) | banyak (login, 2FA, API, command terjadwal, broadcast, dll) |
| **Broken reference (BUG, bukan dead code)** | 7 item (perlu perbaikan terpisah) |

Rincian file MATI-PASTI: 20 Blade view, 8 model, 4 aset, 2 middleware, 1 controller, 1 service, 1 helper, 1 command kosong.

### Verifikasi runtime `php artisan route:list` (DIJALANKAN — 238 route)
- ✅ **Semua 30 method [MATI-PASTI]** (Bagian 3) terkonfirmasi **TIDAK ter-route** sama sekali.
- ✅ `tambahDokumenController`, `CacheService`, `AutoLoginMiddleware`, `PreventUrlManipulation`, `KebunOptions`, `GenerateDummyDocuments` **tidak muncul** di route mana pun.
- ⚠️ **Broken route terkonfirmasi**: `TeamVerifikasiController@restoreFromBidang` & `OwnerDashboardController@getRealTimeUpdates` **terdaftar di route tapi method-nya tidak ada** → akan error 500 bila dipanggil (Bagian 6).
- Endpoint AJAX [KEMUNGKINAN-MATI] terkonfirmasi route-nya ada: `analytics.data` → `AnalyticsController@getAnalyticsData`, `owner.cashbank.chart` → `CashBankPimpinanController@chartData`, `owner.notification-logs` → `WhatsAppNotificationLogController@index` (method juga dipakai route `programmer.notification-logs`).

---

## 2. [MATI-PASTI] — FILE UTUH (diusulkan dihapus), urut dari paling tak berisiko

### 2.1 Aset frontend (4) — tidak dirujuk `asset()`/`@vite`/`src`/`href`/`url()` di mana pun
1. `public/css/column-width-fix.css`
2. `public/css/owner-dokumen.css`
3. `public/images/landing-bg.png` (login pakai `landing-bg-poster.jpg`, bukan ini)
4. `public/images/landing-hero.png`

### 2.2 Blade view (20) — tidak pernah `view()/@include/@extends/@component` (dicek notasi titik & slash)
5. `resources/views/welcome.blade.php` — route `/` me-`redirect('/login')`, view default tak pernah dirender
6. `resources/views/dev/role-switcher.blade.php` — route dev sudah `abort(403/404)`
7. `resources/views/verifikasi/dashboard.blade.php` — controller verifikasi pakai `dashboard.workflow`
8. `resources/views/operator/rejected-detail.blade.php` — yang dipakai `team_verifikasi.rejected-detail`
9. `resources/views/owner/rekapanKeterlambatanByRole.blade.php` — method render `owner.rekapanKeterlambatan`, bukan ini
10. `resources/views/team_verifikasi/dokumens/pengembalianDokumen.blade.php`
11. `resources/views/team_verifikasi/partials/bulk-operations.blade.php`
12. `resources/views/team_verifikasi/partials/bulk-operations-inbox.blade.php`
13. `resources/views/partials/advanced-search-panel.blade.php`
14. `resources/views/partials/document-detail-modal.blade.php` (hanya disebut di komentar "Usage:" di file itu sendiri)
15. `resources/views/partials/document-preview-modal.blade.php`
16. `resources/views/partials/document-summary-metrics.blade.php`
17. `resources/views/partials/_analyticsTableRows.blade.php`
18. `resources/views/pembayaran/dashboardPembayaran.blade.php` *(folder lama, digantikan `pembayaranNEW/`)*
19. `resources/views/pembayaran/dokumens/daftarPembayaran.blade.php`
20. `resources/views/pembayaran/dokumens/editPembayaran.blade.php`
21. `resources/views/pembayaran/dokumens/export-pdf.blade.php`
22. `resources/views/pembayaran/dokumens/rekapanDokumen.blade.php`
23. `resources/views/pembayaran/dokumens/rekapanKeterlambatan.blade.php`
24. `resources/views/pembayaranNEW/dokumens/rekapanKeterlambatan.blade.php`

> ⚠️ **JANGAN hapus seluruh folder `pembayaran/`** — `pembayaran/dokumens/tambahPembayaran.blade.php` MASIH dipakai (DashboardPembayaranController:1508).

### 2.3 Model PHP (8) — class tak pernah dipakai
25. `app/Models/RoleDeadlineConfig.php` — tabel hanya disentuh migrasi data, tak ada query runtime
26–32. `app/Models/CashBank/` (seluruh folder, 7 file): `BankTujuan`, `Dropping`, `KategoriKriteria`, `Penerima`, `Permintaan`, `SubKriteria`, `SumberDana`
> ⚠️ **Hanya class model-nya yang mati.** Tabel fisik CashBank (`penerimas`, `permintaans`, `droppings`, dll) **AKTIF** dipakai via raw query di `CashBankReportService` & `DokumenSyncService`. **JANGAN sentuh tabel/DB-nya.**

### 2.4 Middleware (2) — tidak terdaftar di `bootstrap/app.php` & tak dipakai route
33. `app/Http/Middleware/AutoLoginMiddleware.php`
34. `app/Http/Middleware/PreventUrlManipulation.php`

### 2.5 Service / Helper / Command / Controller (4)
35. `app/Services/CacheService.php` — nol referensi (terverifikasi manual: tidak ada di app/routes/config)
36. `app/Helpers/KebunOptions.php` — `getOptions/generateSelectOptions` tak pernah dipanggil
37. `app/Console/Commands/GenerateDummyDocuments.php` — `handle()` kosong (stub scaffold), tak terjadwal/dipanggil
38. `app/Http/Controllers/tambahDokumenController.php` — controller yatim, tak ada route/`::class` (terverifikasi manual)

---

## 3. [MATI-PASTI] — BLOK/METHOD dalam file yang MASIH HIDUP

> File-nya tetap dipakai; hanya method berikut yang tak terjangkau (tidak dirouting, tidak dipanggil `$this->`/`action()`/`route()`/`fetch`). Penghapusan **lebih hati-hati**: sebelum hapus, pastikan tidak ada `private` helper yang HANYA dipakai method ini (kalau ada, ikut hapus).

| Controller | Method mati |
|---|---|
| `WelcomeMessageController` | `index`, `update`, `destroy`, `clearCache` |
| `TeamVerifikasiController` | `createDokumen`, `storeDokumen`, `destroyDokumen`, `sendToNextHandlerDirect`, `sendToTargetDepartment`, `sendBackToMainList`, `sendBackToPerpajakan` |
| `DashboardPembayaranController` | `redirectDokumens`, `dokumens`, `createDokumen`, `storeDokumen`, `editDokumen`, `updateDokumen`, `updatePembayaran`, `destroyDokumen`, `rekapan` |
| `OwnerDashboardController` | `rekapan`, `rekapanByHandler`, `rekapanDetail`, `getDocumentDetail` |
| `AnalyticsController` | `index` |
| `DokumenController` | `getDocumentDetailForOperator` |
| `UniversalApprovalController` | `index`, `approve`, `reject` *(class "Legacy"; `getDetail`/`checkNotifications` masih live)* |
| `ProgrammerController` | `isProgrammer` |

**Penghapusan berpasangan (coupled):**
- `OwnerDashboardController::rekapanDetail` ⟶ membuat `resources/views/owner/rekapanDetail.blade.php` ikut mati (hapus view-nya bersamaan).
- `TeamVerifikasiController::sendToTargetDepartment` / `sendBackToMainList` / `sendBackToPerpajakan` ⟶ dipanggil JS ke endpoint **`/dokumensB/*` yang route-nya tidak ada** (terverifikasi: nol `dokumensB` di web.php). Saat hapus method, hapus juga blok `fetch('/dokumensB/...')` di:
  - `resources/views/team_verifikasi/dokumens/pengembalianKeBidang.blade.php` (±baris 1101, 1144)
  - `resources/views/team_verifikasi/dokumens/pengembalianKeBagian.blade.php` (±baris 1434)

---

## 4. [KEMUNGKINAN-MATI] / [ENTRY-POINT] / [DINAMIS] — KEPUTUSAN ANDA

### 4.1 [KEMUNGKINAN-MATI] — perlu konfirmasi (JANGAN hapus tanpa "ya")
- **View** `resources/views/profile/require-2fa.blade.php` — nol referensi, TAPI menyangkut enforcement 2FA (sensitif keamanan). Konfirmasi memang tak dipakai.
- **Events broadcast-only**: `app/Events/DocumentSent.php`, `app/Events/DocumentReturned.php` — hanya di-broadcast dari route test ber-gate `local/development` (web.php:609/639), tanpa listener frontend. Kandidat mati bila route test juga dibuang.
- **Events tanpa konsumen**: `DocumentApprovedInbox`, `DocumentRejectedInbox` — di-dispatch di produksi tapi tak ada listener frontend (`.document.approved.inbox` / `.rejected.inbox`). Efektif no-op di klien — tinjau, bukan hapus otomatis.
- **Command maintenance manual (saling redundan)**: `CleanAllData` (`data:clean`), `ClearDokumenCommand` (`dokumen:clear`), `CleanDokumenBeforeImport` (`dokumen:clean-before-import`) — tak terjadwal/tak dipanggil. Kandidat konsolidasi.
- **Config** `config/document_statuses.php` — tak pernah dipanggil `config('document_statuses.*')` ("single source of truth" yang belum diadopsi; status masih hardcoded).
- **Seeder (manual only, tak ter-wire di `DatabaseSeeder`)**: `UpdateUserCredentialsSeeder`, `BagianSeeder`, `BagianUserSeeder`, `BidangSeeder`, `UserSeeder`, `WelcomeMessageSeeder`, `DokumenDummySeeder`. (Seeder lazim dijalankan manual → bukan MATI-PASTI.)
- **Endpoint AJAX tanpa pemicu UI**: `AnalyticsController::getAnalyticsData` (route `analytics.data`), `CashBankPimpinanController::chartData` (route `owner.cashbank.chart`). Route hidup, tapi tak ada `fetch` di Blade/JS. Bisa dipakai alat eksternal → konfirmasi.
- **Nama route owner** `owner.notification-logs` tak terpaut menu, tapi method `index`-nya tetap hidup via `programmer.notification-logs`. (Hanya nama route yang mungkin tak terpakai.)

### 4.2 [ENTRY-POINT] / [DINAMIS] — TERPAKAI, JANGAN HAPUS
- Auth (`Auth\LoginController`), 2FA (`TwoFactorController`, `TwoFactorReset*`).
- API JSON: `Api\DocumentPreviewController`, `Api\AdvancedSearchController` (dipanggil JS via URL `/api/*`).
- Command terjadwal: `notifications:send-late-documents`, `dokumen:process-auto-forward`, `dokumen:sync-cashbank`; serta `import:csv` (dipanggil `Artisan::call`), `assistant:test` (referensi di UI programmer), `welcome:message` (utility admin).
- Event/Observer/Job aktif: `DocumentActivityChanged`, `DocumentSentToInbox`, `DokumenObserver`, `SyncDokumenToCashBankJob`.
- Middleware aktif: `SecurityHeaders` (global), `CheckRole` (alias `role`), `CheckBagianRole` (alias `bagian`).
- Banyak nama route dalam group berprefix tampak "0 hit" pada pencarian suffix saja, padahal reachable lewat nama lengkap / URL literal di JS — **bukan mati**.

---

## 5. CATATAN KHUSUS MIGRASI & DATABASE (risiko data)

- **Tidak menjalankan `migrate`/drop otomatis.** Untuk membuang tabel mati, dibuat **migrasi `drop` baru** dan Anda yang menjalankan.
- `role_deadline_configs` (model mati + tabel tanpa query runtime): **[KEMUNGKINAN-MATI]**. Berisiko menyimpan data → konfirmasi isi tabel di produksi sebelum buat migrasi drop.
- **Tabel CashBank** (`penerimas`, `permintaans`, `droppings`, `sumber_dana`, `bank_tujuan`, `bank_masuk`, `bank_keluars`, `kategori_kriteria`): **AKTIF via raw query** — JANGAN drop. Hanya class model PHP-nya yang dihapus.
- `dokumen_auto_forward_queue`: **AKTIF** via raw `DB::table` (AutoForward) — bukan mati meski tak punya model.
- Tabel legacy `tu_tk_*`, `payment_logs`, `document_position_trackings`: **sudah** di-drop oleh `2026_06_23_000000_drop_tu_tk_tables` → bersih.
- Pertanyaan untuk Anda: file migrasi lama mau **disimpan sebagai riwayat** atau dirapikan? (default: simpan.)

---

## 6. BROKEN REFERENCE (BUG runtime — BUKAN dead code, perlu perbaikan terpisah)

Bukan untuk dihapus; ini referensi yang menunjuk sesuatu yang hilang → bisa error saat dipanggil:
1. `bootstrap/providers.php` mendaftarkan `App\Providers\BroadcastServiceProvider` **yang filenya tidak ada**.
2. Route → method tak ada: `restoreFromBidang` (web.php:446), `getRealTimeUpdates` (web.php:250).
3. Controller → view tak ada: `akutansi.dokumens.tambahAkutansi`, `universal-approval.index`, `admin.welcome-messages.index`, `test-welcome` (route dev).

---

## 7. URUTAN ELIMINASI YANG DIUSULKAN (FASE B, setelah Anda setujui)

1. **Batch 1 — file utuh tak berisiko** (Bagian 2.1–2.5, 38 file): hapus aset, view mati, model mati, middleware/service/helper/command/controller yatim. Tidak ada yang merujuknya, jadi tidak memutus apa pun. Commit per-grup (mis. `chore: hapus 20 blade view tidak terpakai`).
2. **Batch 2 — method mati + JS pasangannya** (Bagian 3): hapus dulu pemanggil JS `/dokumensB/*` & method `rekapanDetail` + view-nya, lalu method lain. Grep `private` helper unik tiap method sebelum hapus.
3. **Batch 3 — [KEMUNGKINAN-MATI]** (Bagian 4.1): hanya yang Anda setujui satu per satu.
4. **Batch 4 — DB**: buat migrasi `drop` untuk `role_deadline_configs` bila disetujui; Anda jalankan manual. (Tabel CashBank & auto_forward TIDAK disentuh.)
5. **Verifikasi pasca-hapus (WAJIB):** `grep` ulang seluruh project memastikan tak ada referensi yatim (`use`/pemanggilan/`@include`/`view()` ke yang dihapus); `php artisan view:cache` & (dengan izin) `php artisan route:list`/`config:clear` untuk memastikan boot konsisten. Laporkan ringkasan + bukti grep bersih di akhir dokumen ini.

> **Catatan akurasi:** `php artisan route:list` (read-only) **sudah dijalankan** (238 route) dan mengonfirmasi seluruh temuan method/controller MATI-PASTI serta broken route — lihat Bagian 1.

---

## 8. KEPUTUSAN YANG DIBUTUHKAN DARI ANDA

1. Setujui **Batch 1** (38 file MATI-PASTI) untuk dihapus? (ya/sebagian)
2. Setujui **Batch 2** (30 method + JS pasangan)?
3. Untuk tiap item **[KEMUNGKINAN-MATI]** (Bagian 4.1) — hapus / pertahankan?
4. ~~Jalankan `php artisan route:list`?~~ ✅ **SUDAH** — 238 route, semua temuan terkonfirmasi.
5. Tangani **broken reference** (Bagian 6) sekarang atau nanti?
6. Migrasi lama: simpan sebagai riwayat atau rapikan?

**FASE A berhenti di sini. Tidak ada perubahan dilakukan. Menunggu instruksi Anda untuk FASE B.**

---

## 9. FASE B — BATCH 1 (SELESAI) ✅

Disetujui user (2026-06-29): "Q1 — Batch 1 (38 file): YA, semua". Eliminasi dilakukan; commit per kelompok (Bahasa Indonesia).

### Yang dihapus (38 file, 6 commit)
1. `chore: hapus 4 aset frontend tidak terpakai` — 2 CSS + 2 gambar.
2. `chore: hapus 19 blade view tidak terpakai` — 19 dari 20 view.
3. `chore: hapus 8 model Eloquent tidak terpakai` — RoleDeadlineConfig + 7 CashBank.
4. `chore: hapus 2 middleware tidak terdaftar`.
5. `chore: hapus service/helper/command/controller yatim` — CacheService, KebunOptions, GenerateDummyDocuments, tambahDokumenController.
6. `chore: hapus partials/_analyticsTableRows.blade.php` — view ke-20; ada modifikasi lokal belum di-commit → salinan kerja + diff dicadangkan ke scratchpad sebelum `git rm -f`.

### Verifikasi pasca-hapus (WAJIB) — semua LULUS
- ✅ `php artisan view:cache` sukses (tak ada @extends/@include ke view terhapus).
- ✅ `php artisan route:list` = 238 route, boot tanpa error (tak ada provider/class yatim).
- ✅ `php artisan config:clear` sukses.
- ✅ Grep orphan-reference seluruh sumber: BERSIH. Satu-satunya kemunculan `rekapanKeterlambatanByRole` adalah **method hidup** (routed) — bukan view yang dihapus; method merender `owner.rekapanKeterlambatan` yang masih ada.
- ✅ `php artisan test --testsuite=Unit` = 12 passed (68 assertions).
- ℹ️ Komentar dokumentasi di `database/schema/cash_bank_new.sql` masih menyebut nama class `App\Models\CashBank\*` (sengaja dibiarkan — hanya dokumentasi pemetaan tabel; tabel tetap aktif via raw query).

### Catatan tetap menunggu keputusan
- **Batch 2** (30 method + JS `/dokumensB/*`), **Batch 3** ([KEMUNGKINAN-MATI]), **broken reference** (Bagian 6), dan **DB/migrasi** belum dikerjakan — menunggu persetujuan Anda.

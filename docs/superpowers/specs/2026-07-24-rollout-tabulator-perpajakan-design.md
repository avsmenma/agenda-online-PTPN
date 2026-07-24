# Desain: Rollout Tabulator ke role Perpajakan (Rollout 2)

Tanggal: 2026-07-24
Status: disetujui user (brainstorming interaktif)
Sasaran: role perpajakan — `DashboardPerpajakanController`, `routes/web.php`, view baru
`perpajakan/dokumens/daftarPerpajakanTabulator.blade.php`, DTO baru `App\Support\PerpajakanDocumentRow`.

## 1. Latar & Keputusan

Program penyatuan 6 tabel dokumen. **Rollout 1 = akutansi** sudah SELESAI & ter-deploy
(engine bersama `public/js/document-tabulator.js`, basis DTO `App\Support\DocumentRow`,
`extraColumns` untuk kolom tetap, badge/deadline dihitung server). **Rollout 2 = perpajakan** —
role termudah berikutnya (peta scout 2026-07-24): struktur `dokumens()`/`_rows`/`_chunk`,
sumber kolom base config, resep eager-load, dan logika badge/deadline nyaris 1:1 dengan akutansi.

Keputusan yang sudah diambil bersama user:

| Keputusan | Pilihan |
|---|---|
| Row DTO | **`PerpajakanDocumentRow extends App\Support\DocumentRow`** (basis bersama, sudah ada dari Rollout 1). Bukan salinan. |
| Badge Status & Deadline | **Dihitung di SERVER** (di dalam DTO); klien hanya merender objek. Nol logika bisnis di JS. |
| Forward | **Lewat dropdown Pengurus Dokumen** (mekanisme bersama `document-handler-select` → `DocumentHandlerController`). Tombol Kirim/Balik lama **dibuang tuntas** — sudah mati (`$showActionColumn=false`). |
| Gate wajib-isi NPWP/Faktur | **Dibiarkan seperti sekarang (TANPA gate).** Gate lama menempel pada tombol tersembunyi → sudah tak berfungsi di app live. Rollout mempertahankan perilaku live. Menegakkan gate = fitur terpisah, DI LUAR lingkup. |
| Transisi | Pola akutansi: `dokumens()` menyajikan Tabulator; view lama via `?classic=1` untuk QA banding; **hapus view lama + flag setelah QA lolos.** |

> Nomor baris indikatif (peta scout 2026-07-24). Jangkar = nama simbol/blok.

## 2. Kondisi Saat Ini

- Route list: `documents.perpajakan.index` (`web.php:497`) → `DashboardPerpajakanController@dokumens`
  (method **`dokumens`**). Query dibangun inline di `dokumens()` (`:39-538`): cross-role
  visibility, filter search/dari/tanggal/nilai + switch status, per-baris enrich
  (`is_locked`/`can_edit`/`is_at_my_role`/`can_set_deadline`). Eager-load: base
  `dokumenPos`/`dokumenPrs`/`dibayarKepadas` (`:47-50`); pasca-paginate `loadMissing`
  (`:256-264`): `roleData` role_code=`perpajakan` + `roleStatuses` `['team_verifikasi','perpajakan','akutansi','pembayaran']`.
- **`$showActionColumn = false`** — di controller cabang `virtual_chunk` (`:387`) DAN view
  (`daftarPerpajakan.blade.php:2239`). Kolom aksi (tombol Kirim/Balik) **tersembunyi/mati**.
- Kolom: `Arr::except(config('document_columns.base'), ['status'])` (`:325`). Sesi
  `perpajakan_dokumens_table_columns` + DB `table_columns_preferences['perpajakan']`. Default:
  `nomor_agenda, nomor_spp, tanggal_masuk, nilai_rupiah, nomor_miro, link`. Plus 3 kolom tetap:
  **Deadline, Status, Pengurus Dokumen**.
- **Belum ada endpoint JSON** (`@datatable`). Baris server-rendered `_rows`/`_chunk` +
  `virtual-document-table` (global partial).
- Badge Status: `_rows.blade.php:586-632` (~12 cabang) memakai `getDisplayStatusForRole('perpajakan')`
  (`_rows.blade.php:33-118`, display_status-first + fallback legacy).
- Deadline: `_rows.blade.php:353-585` — count-up (umur sejak `received_at` perpajakan), beku
  saat sent/completed/paused, ambang AMAN(<24j)/PERINGATAN(24-72j)/TERLAMBAT(≥72j) + path bypass.
  **Bug laten:** `$isBypassedToPembayaran` dirujuk di `:494` tapi hanya di-set di cabang fallback
  logika status → risiko "undefined variable" pada sebagian baris.
- Acuan Rollout 1: `App\Support\AkutansiDocumentRow`, `DashboardAkutansiController@datatable`+`buildAkutansiQuery`,
  `resources/views/akutansi/dokumens/daftarAkutansiTabulator.blade.php`.

## 3. Arsitektur Target (cermin Rollout 1)

### 3.1 `App\Support\PerpajakanDocumentRow extends DocumentRow`
Basis §3.1 Rollout 1 (`baseRow()`: kolom base, rupiah/dpp/ppn, join dibayar_kepada/nomor_po,
tanggal, handler/handler_options/can_change_handler, link_safe, dates) + bit perpajakan:
- `is_at_my_role` (dokumen sedang di perpajakan?), `is_locked`, `lock_status_message`,
  `lock_status_class`, `can_edit` (`DokumenHelper::canEditDocument(...,'perpajakan')`),
  `can_set_deadline`, `status_pembayaran` (mentah).
- **`status_badge`** `{class, icon?, text, link?}` — PORT pohon `_rows.blade.php:586-632`
  (+ `getDisplayStatusForRole('perpajakan')`): rejected → link "cek disini"
  (`returns.perpajakan.index?search=`); upstream-belum-terima → "Draft"; pending hilir;
  terkirim-dari-perpajakan; sent_to_akutansi / sent_to_pembayaran; locked; returned_to_verifikasi;
  sedang diproses; fallback. Urutan if/elseif DIPERTAHANKAN persis.
- **`deadline`** `{variant, type, color, received_display, indicator_icon, indicator_label, age_text, footer}`
  — PORT `_rows.blade.php:353-585`. **PERBAIKI bug laten:** definisikan
  `$isBypassedToPembayaran` TAK-BERSYARAT di DTO (default false) sebelum dipakai — bukan porting
  verbatim bug-nya. Selain itu path A (kartu umur)/B (bypass)/C (belum diterima) = pola akutansi.
- TIDAK menyertakan `display_status`/`reject_*` operator.

### 3.2 Endpoint JSON `documents.perpajakan.data`
`DashboardPerpajakanController@datatable` — route GET `documents/perpajakan/data` (STATIS,
sebelum route `{dokumen}`) di grup `web.php:496`. Membalas `{last_page, total, data}`, memakai
ulang query & enrich `dokumens()` (**ekstrak `buildPerpajakanQuery(Request): Builder`** — sumber
tunggal dipakai `dokumens()` + `datatable()`), baris via `PerpajakanDocumentRow::fromDokumen(...)`.
`buildPerpajakanHandlerOptions()` (5 peran base + optgroup Bagian) — bentuk identik
`DokumenController::buildHandlerOptions()`.
**Eager-load parity WAJIB:** `roleData` role_code=`perpajakan` saja + `roleStatuses` 4 role —
sama seperti `dokumens()`. Jangan diperluas (jaga byte-parity badge/deadline).

### 3.3 View Blade + formatter klien
`perpajakan/dokumens/daftarPerpajakanTabulator.blade.php` (meniru `daftarAkutansiTabulator`):
emit `DOCUMENT_TABULATOR_CONFIG` (`mountId:'perpajakanTabulatorTable'`, `dataUrl: documents.perpajakan.data`,
`extraColumns` Deadline+Status, `handlerTpl`/`inlineUpdateTpl` bersama, `columns`/`availableColumns`/`selected`
perpajakan, `ie` perpajakan); mount `<div id="perpajakanTabulatorTable" class="doc-tabulator">` di
`#documentTableContainer`. Formatter **deadline**/**akutansiStatus** engine sudah ada (dipakai ulang,
render objek server). Toolbar filter perpajakan (nama field dibaca engine dari DOM — sudah generik).
TANPA tombol Tambah/Hapus (perpajakan tak punya create/destroy). Modal kustomisasi kolom
**diduplikasi self-contained** (utang de-dup §7, sama seperti akutansi) — nama field toolbar perpajakan
di `appendActiveFilterInputs`. CSS Deadline/Badge: **pakai ulang `public/css/akutansi-deadline-badge.css`**
bila kelas identik; bila perpajakan punya kelas badge/warna khusus, port ke
`public/css/perpajakan-deadline-badge.css` (cek inventaris kelas saat plan).

### 3.4 Transisi
`dokumens()`: `?classic` → view lama; selain itu → view Tabulator baru. Setelah QA lolos (§6):
hapus view lama + flag.

## 4. Dipakai ulang / WAJIB tetap
Engine `document-tabulator.js` (TANPA ubah — sudah generik: `extraColumns` + filter dari DOM),
basis `App\Support\DocumentRow`, formatter `fmtDeadline`/`fmtAkutansiStatus`, endpoint
`documents.inline-update` & `documents.handler.update`, `config('document_columns.base')`, partial
bersama (`document-handler-select`, global `compact-document-ui`/`document-workbench-ui`), `SafeUrl`,
`DokumenHelper`. Route `documents.perpajakan.index`/`detail`, halaman Pengembalian
(`returns.perpajakan.index` + `pengembalianPerpajakan.blade.php`) — **tetap**.

## 5. Di luar lingkup
- Menegakkan ulang gate wajib-isi NPWP/Faktur (fitur terpisah).
- Rollout role lain (verifikasi berikutnya, pembayaran menyusul); kustomisasi freeze (pembayaran).
- Legacy PDF export perpajakan (`exportToPDF` + `export/pdf.blade.php`) — jangan disentuh.
- Endpoint `documents.perpajakan.detail` — TETAP (dipakai halaman Pengembalian; verifikasi saat plan).

## 6. Penghapusan setelah QA (fase akhir rollout)
Setelah QA Tabulator perpajakan lolos, dengan **grep gate lintas-role** (partial `_rows`/`_chunk`
role LAIN beda berkas — jangan disentuh):
- Hapus `perpajakan/dokumens/daftarPerpajakan.blade.php` (4.557 baris), `_rows.blade.php` (713),
  `_chunk.blade.php`, cabang `?classic`/`virtual_chunk` di `dokumens()`, flag `$showActionColumn`.
- Hapus **kode aksi mati** (grep-verified yatim, gerbang §6): rute `documents.perpajakan.set-deadline`/
  `send-to-next`/`send-to-akutansi`/`return` (`web.php:499-502`) + method `setDeadline`/`sendToNext`/
  `sendToAkutansi`(deprecated)/`returnDocument`. **Verifikasi dulu** tak dipakai dari view/JS lain
  (mis. halaman Pengembalian) sebelum hapus.
- CSS aksi mati yang ikut terhapus bersama view legacy.

## 7. Verifikasi (dilaporkan verbatim di plan)
1. `php artisan test` hijau di TIAP tahap. Ekstraksi basis dijaga suite Rollout 1
   (`OperatorDocumentRowTest`/`AkutansiDocumentRowTest` tetap hijau) + test baru
   `PerpajakanDocumentRowTest` + `PerpajakanDatatableTest` + `PerpajakanTabulatorSwitchTest`.
2. `node --check public/js/document-tabulator.js` bila disentuh (idealnya TIDAK — engine sudah generik).
3. **QA visual (agent via Playwright, READ-ONLY)** — login `pajak`/`12345678`: `/documents/perpajakan`
   → Tabulator; kolom Deadline & badge Status benar (bandingkan `?classic=1`); filter status/dari jalan
   (engine baca nama dari DOM); dropdown Pengurus jalan; inline-edit jalan; kustomisasi kolom jalan;
   lintas-role "Di {role}" benar; 0 error konsol. **Jalur TULIS diserahkan user** (aturan aman produksi).
4. **Parity operator & akutansi** tak regresi (DTO basis tak berubah; engine tak berubah).

## 8. Deploy
Per tahap disetujui: commit per-file → push → pull server → clear cache. Penghapusan legacy (§6)
hanya setelah QA perpajakan lolos + konfirmasi user.

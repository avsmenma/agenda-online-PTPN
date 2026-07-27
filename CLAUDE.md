# CLAUDE.md — Agenda Online PTPN IV Regional V

Aplikasi pencatatan & alur persetujuan **dokumen pembayaran**, dari Operator sampai
Tim Pembayaran. Laravel 12, PHP `^8.2`, MySQL 8, Blade + Alpine.js + Bootstrap 5 (CDN).
Repo: `avsmenma/agenda-online-PTPN` — branch kerja `codinggemini`.

Aturan universal (gaya commit, `git add` per-file, bahasa) ada di `~/.claude/CLAUDE.md`.
Berkas ini hanya memuat yang **khas project ini**.

---

## 1. Kondisi Kodebase — baca sebelum menyentuh apa pun

Acuan lengkap: **`docs/AUDIT_KESEHATAN_KODEBASE_2026-07-03.md`** (audit 6 domain, ~144k baris, skor rata-rata ≈46/100).

> ### Keputusan final: REFAKTOR BERTAHAP. JANGAN rewrite dari 0.
> Pondasi sehat — skema relasional benar (FK cascade konsisten), middleware RBAC benar,
> nol SQL injection. Yang sakit adalah **duplikasi** dan **ukuran file**. Itu diekstrak,
> bukan dilahirkan ulang. Cakupan test masih rendah, jadi rewrite = menghancurkan ratusan
> aturan bisnis yang ter-enkode diam-diam.

### Penyakit utama — ingat ini setiap kali memperbaiki bug tabel

> **6 tabel dokumen per-role adalah ~73% salinan copy-paste satu sama lain.** Rollout
> bertahap sudah menyatukan operator/akutansi/perpajakan/verifikasi/pembayaran ke satu
> engine Tabulator bersama (`document-tabulator.js` + DTO `DocumentRow`); `bagian` masih
> tabel biasa view-only (tanpa aksi, sengaja tak ikut rollout).
>
> Karena itu **memperbaiki 1 role tidak menyebar ke 5 role lain.** Kalau user melaporkan
> "bug ini sudah pernah diperbaiki tapi muncul lagi di halaman lain" — inilah sebabnya.
> Selalu tanyakan: perbaikan ini perlu disalin ke berapa file?

### Status Juli 2026 (kodebase sudah bergerak sejak audit — jangan kutip audit mentah-mentah)

| | Temuan audit | Status sekarang |
|---|---|---|
| ✅ | 6 lubang keamanan mendesak (§7 audit) | **Sudah ditutup** — autocomplete & `pengembalian-dokumens` kini ber-`auth`+`role`, `channels.php` punya otorisasi peran nyata, `DokumenDummySeeder` punya guard produksi, grup `dashboard-pembayaran` dihapus |
| ✅ | Tanpa CI | Ada `.github/workflows/tests.yml` |
| ✅ | `User::ROLES` kunci duplikat | Sudah dinormalisasi (alias ditangani `App\Support\Role::normalize()`) |
| ✅ | Test 9 file | Naik ke 21 file |
| ❌ | **`@vite` mati total** | Masih mati. `resources/css/app.css` & `resources/js/app.js` tetap dead asset. UI 100% dari CDN + CSS inline |
| ❌ | God-file | `layouts/app.blade.php` 5.968 baris. (`operator/daftarDokumen` 5.227 baris **sudah dihapus 2026-07-23** — operator kini Tabulator-only.) |
| ❌ | Duplikasi 6 tabel role | Belum disatukan |
| ❌ | `pint.json` | Belum ada |
| ❌ | `maatwebsite/excel ^1.1` | Masih rilis era 2015 di atas Laravel 12 |

**Sebelum melaporkan temuan audit sebagai masalah aktif, verifikasi dulu ke kode.**
Audit ini bertanggal 3 Juli 2026 dan sebagian sudah usang.

---

## 2. Peta Berkas

### Partial shared — HARAM dihapus tanpa grep lintas-role

Dipakai banyak role sekaligus. Menghapusnya merusak halaman yang tidak sedang Anda buka:

```
resources/views/partials/
  _inlineEditEngine.blade.php        mesin inline-edit (PATCH /documents/{id}/inline-update)
  _activeCellNav.blade.php           navigasi sel keyboard (dipakai role bagian)
  compact-document-ui.blade.php      dimuat GLOBAL dari layouts/app.blade.php
  document-workbench-ui.blade.php    panel Detail Cepat
```
> Catatan: `virtual-document-table`, `_compactDocumentTable`, `document-handler-select`,
> `auto-refresh-documents` **sudah DIHAPUS** (audit dead-code 2026-07-26) — konsumennya lenyap
> saat rollout Tabulator; forward "Pengurus Dokumen" kini di `document-tabulator.js`.

`compact-document-ui` dan `document-workbench-ui` bersifat **global** — mengubahnya
menyentuh semua role sekaligus. Perubahan di sini wajib aditif (tambah, jangan ubah
jalur lama) kecuali user memutuskan lain.

### God-file — jangan tambah baris, ekstrak keluar

`layouts/app.blade.php` (5.960), `pembayaranNEW/dashboardPembayaran.blade.php` (3.671),
`OwnerDashboardController.php`. (`operator/dokumens/daftarDokumen.blade.php` sudah dihapus
2026-07-23 — operator kini Tabulator-only.) Kalau harus menambah CSS/JS di sini,
pertimbangkan file terpisah di `public/css` atau `public/js` lalu `@push`.

---

## 3. Aturan Kerja Wajib

1. **Jangan tambah salinan ke-7.** Sebelum copy-paste blok dari role lain, cek apakah
   bisa jadi partial shared. Duplikasi baru = memperparah penyakit utama.
2. **Sebelum menghapus apa pun**, grep dulu ke seluruh `resources/` dan `routes/`.
   Banyak partial/endpoint dipakai role yang tidak terlihat dari file yang sedang dibuka.
3. **Test sebelum refaktor.** Cakupan masih rendah; refaktor tanpa jaring pengaman
   pernah melahirkan regresi yang bocor ke produksi (auto-forward, import CSV).
   Jalankan `php artisan test` — suite harus hijau sebelum commit.
4. **Jangan tambah CSS inline baru.** Sudah ada 1.623 `!important`. Perang spesifisitas
   melawan Bootstrap CDN adalah utang, bukan solusi.
5. **Nol tebakan pada aturan bisnis.** Alur dokumen, deadline, dan hak per-role sudah
   diputuskan pemilik. Kalau tidak yakin — tanya, jangan karang.
6. **Jangan biarkan drift skema.** Migrasi baru wajib idempoten (guard kolom **dan**
   index). Contoh yang benar: `2026_01_26_000000_add_performance_indexes` (`indexExists()`).
   Jangan tambah guard `Schema::hasColumn()` baru di kode bisnis — itu membuat fitur
   mati diam-diam, bukan error keras.

---

## 4. Data & Server Produksi

Aplikasi ini **dipakai sungguhan** dan datanya tidak bisa dipulihkan sembarangan.

- **Jangan** jalankan perintah destruktif (`drop`, `rm -rf`, `reset --hard`,
  `truncate`) tanpa konfirmasi eksplisit user.
- **Jangan** jalankan `migrate:fresh` / `migrate:wipe` pada database berisi data.
- **Jangan** jalankan seeder dummy atau command penghapus massal
  (`data:clean`, `dokumen:clean-before-import`, `dokumen:clear`) di luar lokal.
- Sebelum menghapus/menimpa berkas, **baca dulu isinya**.
- Jika memang ingin dijalankan, tanyakan dulu ke user untuk mendapatkan izin

---

## 5. Alur Deploy

Setiap update yang sudah disetujui: **commit → push → pull di server → clear cache.**

```bash
git push origin codinggemini
# di server:
git pull
php artisan route:clear && php artisan view:clear && php artisan config:clear
```

Detail koneksi server ada di `deploy_to_server.bat`. **Clear cache tidak boleh dilewat** —
Blade & route yang ter-cache membuat perubahan tampak tidak berefek, lalu waktu terbuang
mencari bug yang sebenarnya tidak ada.

---

## 6. Gerbang Kritis — berhenti dan minta keputusan user

Untuk rencana yang sudah disetujui, tugas boleh berjalan berurutan tanpa menunggu
"lanjut". Tetapi **berhenti** bila:

- Acceptance sebuah tugas gagal.
- Muncul ambiguitas atau keputusan desain baru.
- Pekerjaan menyentuh **partial global** (`compact-document-ui`, `document-workbench-ui`,
  `layouts/app.blade.php`) — dampaknya lintas-role.
- Pekerjaan menyentuh **skema database**, **RBAC/route middleware**, atau **auto-forward**.
- Akan **menghapus** view/route/partial — perlu bukti grep + persetujuan.

**QA visual adalah tanggung jawab user.** Agent tidak punya browser atau sesi login;
paritas tampilan tidak pernah bisa diklaim selesai dari test backend saja. Nyatakan
dengan jujur apa yang sudah diuji dan apa yang belum.

---

## 7. Pekerjaan Berjalan

**Operator `/documents` sudah Tabulator-only — SELESAI & ter-deploy, QA lolos 2026-07-23.**
Tabel classic operator (`daftarDokumen.blade.php`, `_chunk`, `_tableRowsAjax`), cabang
`virtual_chunk`, flag `?classic`, dan route/method yatim (`getDocumentDetail`+route,
`sendToTeamVerifikasi`+route, method mati `operatorDocumentColumns`) sudah **dihapus permanen
dari kode — bukan dimatikan**. `/documents?classic=1` kini no-op (menyajikan Tabulator).
Forward operator→verifikasi lewat dropdown **Pengurus Dokumen** (`DocumentHandlerController::
moveDirectlyToTeamVerifikasi`, langsung diterima), BUKAN tombol "Kirim" lama (jalur
approval-gated `sendToInbox` sudah dihapus). Guard "Bagian wajib terisi" sebelum forward tetap.

- Pilot: `docs/superpowers/specs/2026-07-17-tabulator-operator-pilot-design.md`,
  `docs/superpowers/plans/2026-07-21-tabulator-operator-pilot.md`
- Pembersihan: `docs/superpowers/specs/2026-07-23-operator-tabulator-only-cleanup-design.md`,
  `docs/superpowers/plans/2026-07-23-operator-tabulator-only-cleanup.md`

**Akutansi `/documents/akutansi` sudah Tabulator-only — SELESAI & ter-deploy, QA lolos 2026-07-24
(Rollout 1).** Engine operator SUDAH diekstrak jadi komponen bersama: `public/js/document-tabulator.js`
(baca `window.DOCUMENT_TABULATOR_CONFIG`, mount via `CFG.mountId`, kolom tetap terparameter via
`CFG.extraColumns`) + basis DTO **`App\Support\DocumentRow`** yang diwarisi `OperatorDocumentRow` &
`AkutansiDocumentRow`. Badge Status & kolom Deadline akutansi **dihitung SERVER** di
`AkutansiDocumentRow` (objek `status_badge`/`deadline`); klien hanya merender (nol logika bisnis di JS).
Endpoint JSON `documents.akutansi.data` (query bersama `buildAkutansiQuery()`). View legacy
(`daftarAkutansi` 3995 baris, `_rows`, `_chunk`), cabang `?classic`/`virtual_chunk` **dihapus permanen**
(~4539 baris). Forward lewat dropdown **Pengurus Dokumen** (sama seperti operator). Filter toolbar
engine kini baca nama field dari DOM (generik lintas-role, bukan hardcode). `?classic=1` no-op (Tabulator).

- Spec/plan: `docs/superpowers/specs/2026-07-23-rollout-tabulator-akutansi-design.md`,
  `docs/superpowers/plans/2026-07-24-rollout-tabulator-akutansi.md`

**Perpajakan `/documents/perpajakan` sudah Tabulator-only — SELESAI & ter-deploy, QA lolos 2026-07-24
(Rollout 2).** Klon pola akutansi: `App\Support\PerpajakanDocumentRow` (badge+deadline server, bug laten
`$isBypassedToPembayaran` diperbaiki), endpoint `documents.perpajakan.data`, view `daftarPerpajakanTabulator`.
Forward via dropdown; gate wajib-isi NPWP/Faktur dibiarkan tanpa gate (perilaku live). View legacy
(`daftarPerpajakan` 4557/`_rows`/`_chunk`) + rute/method aksi mati (`set-deadline`/`send-to-next`/`return`
+ `setDeadline`/`returnDocument`) **dihapus**. `?classic=1` no-op (Tabulator).

- Spec/plan: `docs/superpowers/specs/2026-07-24-rollout-tabulator-perpajakan-design.md`,
  `docs/superpowers/plans/2026-07-24-rollout-tabulator-perpajakan.md`

**Halaman "Pengembalian" per-role DIHAPUS (perpajakan + akutansi, 2026-07-24).** Keputusan user:
tak ada lagi "pengembalian dokumen" **kecuali verifikasi→bagian** (`pengembalianKeBidang`, `returns.verifikasi.*` —
TETAP HIDUP), karena perpindahan dokumen kini via dropdown Pengurus Dokumen. Dihapus tuntas:
`pengembalianPerpajakan`/`pengembalianAkutansi` view + route `returns.{perpajakan,akutansi}.index` +
`documents.{perpajakan,akutansi}.detail` + method `pengembalian()`/`getDocumentDetail()`+helper
(`generateDocumentDetailHtml`/`formatTaxStatus`/`formatTaxDocumentLink`) + perpajakan `sendToAkutansi()`/`sendToNext()`+
rute `send-to-akutansi`. **Badge Status "Dokumen ditolak, cek disini" DIHAPUS** dari `PerpajakanDocumentRow` &
`AkutansiDocumentRow` (link-nya ke halaman yg dihapus; dokumen ditolak kini jatuh ke badge status biasa).
Var mati `$pengembalianUrl` di `layouts/app.blade.php` juga dihapus.
Dua halaman return TERSEMBUNYI lain (nol link/menu) IKUT dihapus 2026-07-24: **operator** `/pengembalian-dokumens`
(`PengembalianDokumenController` + view `operator.dokumens.pengembalianDokumen`, read-only) & **pembayaran**
`/returns/pembayaran` (method `pengembalian()` = redirect saja). **HASIL: nol halaman/route return mati di seluruh
app — hanya `returns.verifikasi.*` (verifikasi→bagian) yang hidup.** Sebelum menyentuh "return/pengembalian"
apa pun ke depan: cek `returns.verifikasi.*`/`pengembalianKeBidang` = JANGAN dihapus (satu-satunya yang hidup).

**Verifikasi `/documents/verifikasi` sudah Tabulator-only — SELESAI & ter-deploy, QA lolos
2026-07-25 (Rollout 3).** Klon pola akutansi/perpajakan: `App\Support\VerifikasiDocumentRow`
(badge+deadline server), endpoint `documents.verifikasi.data` (`buildVerifikasiQuery()`
dipakai bersama `dokumens()`+`datatable()`), view `daftarDokumenTabulator.blade.php`. **Paraf
pensiun** — workflow tanda tangan `tanggal_paraf`/`pemaraf` sudah tak punya UI aktif; method
`parafDokumen` dihapus bersama kodenya. Dihapus tuntas: view legacy (`daftarDokumen` + `_rows`
+ `_chunk`, ~6.8k baris), cabang `virtual_chunk`/`?classic` (`?classic=1` kini no-op — selalu
Tabulator), method aksi mati `getDocumentDetail`+`generateDocumentDetailHtml`/`escapeHtml`/
`getRejectedByDisplayName` (rantai private-nya), `sendToNextHandler`, `returnToDepartment`,
`returnToOperator`, `setDeadline` (**hanya milik `TeamVerifikasiController`** — method
senama di `DashboardPembayaranController`/`DokumenRoleData` beda kelas, tak disentuh), plus
route-nya masing-masing. `acceptDocument`/`rejectDocument` + route `.accept`/`.reject` ikut
dihapus setelah grep-gate final lintas `resources/`+`public/js/`+`app/`+`tests/`+config
menemukan nol caller (Inbox pakai `InboxController::approve`/`reject`, bukan method ini).
Route broken `returns.verifikasi.restore-from-bidang` (method `restoreFromBidang` tak pernah
ada) turut dihapus. **`returns.verifikasi.*` (`pengembalianKeBidang`/`returnToBidang`,
route `.bagian`/`.to-bidang`) TETAP HIDUP** — satu-satunya alur "pengembalian dokumen" yang
masih dipakai di seluruh app (lihat paragraf Pengembalian di atas), TIDAK disentuh.
`checkRejectedDocuments`/`getSearchSuggestions` (dipakai polling notifikasi & `dokumens()`)
dipertahankan apa adanya.

- Spec/plan: `docs/superpowers/specs/2026-07-25-rollout-tabulator-verifikasi-design.md`,
  `docs/superpowers/plans/2026-07-25-rollout-tabulator-verifikasi.md`

**Pembayaran `/documents/pembayaran/daftar` sudah Tabulator-only — SELESAI & ter-deploy, QA
lolos 2026-07-25 (Rollout 4).** Outlier di antara rollout Tabulator: `App\Support\
PembayaranDocumentRow` TANPA deadline (pembayaran tak punya kolom deadline per-baris) dan
`can_edit` SELALU true (edit-anywhere — Team Pembayaran boleh mengedit dokumen kapan pun,
termasuk sebelum sampai di rolenya, aturan bisnis disengaja, bukan lupa gate). Badge Status
kini SATU kolom katalog (`status_pembayaran`, formatter `paymentPill` baca `row.status_badge`
3-state dihitung SERVER) — dulu dobel dengan kolom Status terpisah (fix QA). Freeze 2-tab
(modal Kolom Beku) direimplementasi ke frozen native Tabulator (`CFG.frozen` dari
`$frozenLeft`/`$frozenRight`), menggantikan CSS `position:sticky` classic. Pembayaran juga
SATU-SATUNYA role yang mengikutkan dokumen hasil import CSV di query Tabulator tanpa filter
`current_handler` (paritas `index()` lama). Endpoint JSON `documents.pembayaran.data`
(`buildPembayaranQuery()` → `buildPembayaranDashboardQuery()` dipakai bersama `index()` &
`datatableTabulator()`). **Dihapus permanen**: renderer bespoke (`renderFallbackRows`/
`loadFallbackRows`/`initFallbackTableScroll`, markup `#pembayaranDocumentTable`), cabang
`?classic`, endpoint `datatable()` lama (route `dashboard.pembayaran.data`) + helper
`formatPembayaranDashboardCell()`/`getPembayaranDashboardRawValue()`, 4 endpoint aksi mati
(`setDeadline`/`updateStatus`/`uploadBukti`/`getPaymentData` + rute `set-deadline`/
`update-status`/`upload-proof`/`payment-data`, nol pemakai), dan partial
`_documentTableStickyCells` (grep gate final nol includer pasca-hapus). `?classic=1` kini
no-op (Tabulator). **KEEP**: `buildPembayaranDashboardQuery()`/`applyPembayaranDashboardSearch()`
(dipakai jalur Tabulator baru), `getDocumentDetail()`+helper terkait (tombol mata mode
rekapan-vendor), import CSV (`CsvImportController`), export bersama (`exportDocuments` +
trait `ExportsDocuments` + `App\Support\DocumentExporter`), mode `rekapan_table`,
asisten-virtual (`OwnerVirtualAssistantController`).

- Spec/plan: `docs/superpowers/specs/2026-07-25-rollout-tabulator-pembayaran-design.md`,
  `docs/superpowers/plans/2026-07-25-rollout-tabulator-pembayaran.md`

**Fitur Export Bersama (Excel `.xls` dependency-free + PDF) SELESAI & ter-deploy 2026-07-26.**
Satu jalur export untuk 5 role keuangan: trait `App\Http\Controllers\Concerns\ExportsDocuments`
(`respondDocumentExport`) + service `App\Support\DocumentExporter::toXlsx()` (XML Spreadsheet
2003, NOL library — menggantikan `exportToExcel()` PhpSpreadsheet lama yang FATAL karena
`phpspreadsheet` tak terpasang; hanya `maatwebsite/excel ^1.1`/PHPExcel abandoned yang ada).
Tombol **"Export" (dropdown Excel/PDF)** aditif di engine `document-tabulator.js` (aktif bila
`CFG.exportUrl` diisi per-role). PDF = print-view bersama `resources/views/exports/document-print.blade.php`.
Route `documents.<role>.export` per role (gating `role:`). **Bug "tombol Export tak berfungsi"
(dropdown tak terbuka — data-api Bootstrap mati di layout jQuery+BS5) sudah DIPERBAIKI**:
dropdown digerakkan eksplisit via instance `bootstrap.Dropdown` (commit `2a2c955`) — berdampak
SEMUA role. **Pembayaran: tombol/form/route export LAMA + mode per-vendor DIHAPUS** atas
keputusan user (2026-07-26) — `exportRekapan`, route `reports.pembayaran.export`,
`buildVendorExportSheets`, `#exportForm` + modal vendor + JS `exportDocument`/`doExport`, plus
dead code `exportToPDF`/`getColumnValue`/`getExportCellValue` + view
`pembayaranNEW/dokumens/export-pdf.blade.php`. Export kini flat 1-sheet lewat SATU dropdown
Export. `DocumentExporter` tetap menyimpan kapabilitas multi-sheet generik (tested, tanpa
pemakai aktif).

- Spec/plan: `docs/superpowers/specs/2026-07-26-fitur-export-bersama-design.md`,
  `docs/superpowers/plans/2026-07-26-fitur-export-bersama.md`

**Export: modal pilih kolom SELESAI & ter-deploy 2026-07-27.** Tombol Export kini
membuka **modal terpadu** (pilih format Excel/PDF via radio, default Excel + pilih
kolom via checkbox, default centang = kolom yang sedang terlihat), menggantikan
**dropdown Excel/PDF pure-CSS lama**. PDF punya catatan A4 lunak (non-blocking) di
atas ambang 9 kolom (`PDF_SOFT_LIMIT`); 0 kolom tercentang → tombol Export nonaktif.
Satu-satunya berkas berubah: `public/js/document-tabulator.js` (IIFE
`wireExportButton()`) — **nol perubahan server/Blade**, controller per-role sudah
menerima `columns[]` sejak fitur export bersama di atas. Berlaku otomatis di 5 role
keuangan (engine bersama).

- Spec: `docs/superpowers/specs/2026-07-27-export-pilih-kolom-design.md`

**Audit dead-code 2026-07-26 (Tier 1 + Tier 3, disetujui user).** Dihapus permanen (grep-gated,
suite hijau): class mati `app/Exports/RekapanKeterlambatanExport.php` (satu-satunya konsumen
`maatwebsite/excel` di `app/`), `app/Helpers/TerbilangHelper.php`, `app/Events/DocumentSent.php`,
`app/Events/DocumentReturned.php`, `app/Http/Requests/SetDeadlineRequest.php`; method mati
`DashboardPerpajakanController::exportToPDF()` + view `perpajakan/export/pdf.blade.php`; dua `use`
mati di `TeamVerifikasiController` (`Bidang`, `DocumentReturned`); dua root logo duplikat
(`logoPTPNNew.png`/`logo_ptpn.png` — asli di `public/images/`); output build Vite basi
`public/build/*` (`@vite` tak pernah dipanggil).

**Audit dead-code lanjutan 2026-07-26 (Tier 2 + Tier 4 + composer, disetujui user).** Dihapus:
4 partial mati sisa rollout (`virtual-document-table`, `_compactDocumentTable`,
`document-handler-select`, `auto-refresh-documents` — §2 di atas sudah diperbarui); paket
**`maatwebsite/excel` + `phpoffice/phpexcel` DIHAPUS dari composer** (nol pemakai; `composer remove`,
suite 245 hijau — **deploy: server WAJIB `composer install --no-dev`**, bukan sekadar `git pull`);
folder untracked `Agenda Online PTPN Design System/` (16 MB referensi); dir kosong
`resources/views/tracking/`. `public/favicon.ico` (0-byte) diisi logo 128px. **MASIH ADA (sengaja
disimpan):** config Vite/npm (`vite.config.js`/`package.json`/`resources/{css,js}`) agar pipeline
bisa dihidupkan; partial `document-role-filter-toolbar` (fondasi kolom masa depan §7).

**Rollout Tabulator SELESAI untuk semua role non-`bagian`** (operator/akutansi/perpajakan/
verifikasi/pembayaran — Rollout 1-4 tuntas 2026-07-25). `bagian` sengaja DILEWATI atas
keputusan user — role ini view-only (halamannya `pengembalianKeBidang`, inbox dokumen
dikembalikan verifikasi), bukan pengelola tabel dokumen seperti 5 role di atas. Tersisa
sebagai program terpisah bila diperlukan: kustomisasi kolom masih diduplikasi per-role
(fondasi bersama sudah ada: `config/document_columns.php` + partial
`document-role-filter-toolbar`) — penyatuannya belum dikerjakan, meski pola freeze native
Tabulator (dulu "freeze ala pembayaran, modal 2-tab") kini justru sudah jadi acuan di
pembayaran sendiri.

## 8. Hal Yang harus bisa dilakukan pada tabel tabulator

- Terdapat sel aktif yang dapat digerakkan dengan tombol panah pada keyboard, dan tabel akan otomatis menggulir mengikuti sel aktif. - Sel aktif dapat dipindahkan secara instan cukup dengan mengeklik sel yang diinginkan.
- Tersedia inline edit: arahkan sel aktif ke sel yang ingin diubah, tekan Enter untuk mulai mengedit, lalu tekan Enter lagi untuk menyimpan (atau Esc untuk membatalkan). Dobel-klik juga bisa dipakai untuk mulai mengedit.
- Data dapat disalin tanpa masuk mode edit: arahkan sel aktif ke sel yang ingin disalin, lalu tekan Ctrl+C.
- Data dapat dihapus tanpa masuk mode edit: arahkan sel aktif ke sel yang ingin dikosongkan, lalu tekan Delete atau Backspace.
- Beberapa sel dapat dipilih sekaligus (blok) dengan cara men-drag mouse, Shift+Klik, atau Shift+Panah.
- Seluruh isi blok dapat disalin sekaligus dengan Ctrl+C.
- Data hasil salinan dapat ditempel ke dalam blok dengan Ctrl+V, mengikuti aturan penempelan ala Excel.
- Setiap perubahan dapat dibatalkan dengan Ctrl+Z dan diulang kembali dengan Ctrl+Y.
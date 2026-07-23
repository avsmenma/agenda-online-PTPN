# Desain: Rollout Tabulator ke role Akutansi (Rollout 1)

Tanggal: 2026-07-23
Status: disetujui user (brainstorming interaktif)
Sasaran: role akutansi — `DashboardAkutansiController`, `routes/web.php`, view baru
`akutansi/dokumens/daftarAkutansiTabulator.blade.php`, DTO baru `App\Support\*`.

## 1. Latar & Keputusan

Program penyatuan 6 tabel dokumen. Fase A sudah mengekstrak engine Tabulator jadi
komponen bersama (`public/js/document-tabulator.js`, `window.DOCUMENT_TABULATOR_CONFIG`,
mount via `CFG.mountId`, scope CSS `.doc-tabulator`). **Rollout 1 = akutansi** — role
classic termudah (peta 2026-07-23): sudah memakai `config('document_columns.base')` − status,
partial & endpoint bersama yang sama; gap utamanya hanya **belum punya endpoint JSON** dan
kolom aksinya sudah mati (dihapus 2026-07-23). User memilih rollout ini demi kode bersih
untuk **sidang tugas akhir**: view legacy akutansi + semua kode matinya dihapus utuh.

Keputusan yang sudah diambil bersama user:

| Keputusan | Pilihan |
|---|---|
| Row DTO | **Ekstrak basis bersama `DocumentRow`** — operator & akutansi mewarisi. Bukan salinan. |
| Badge Status & Deadline | **Dihitung di SERVER** (di dalam DTO); klien hanya merender objeknya. Konsisten `display_status` operator; nol logika bisnis di JS. |
| Kolom aksi | **Tidak ada** (sudah mati & dibuang). |
| Transisi | Pola operator: `dokumens()` menyajikan Tabulator; view lama via `?classic=1` untuk QA banding; **hapus view lama utuh + flag setelah QA lolos**. |

> Nomor baris indikatif (peta 2026-07-23). Jangkar = nama simbol/blok.

## 2. Kondisi Saat Ini

- Route list akutansi: `documents.akutansi.index` (`web.php:477`) → `DashboardAkutansiController@dokumens`
  (method bernama **`dokumens`**, bukan `index`). Query dibangun inline di `dokumens()`
  (`:43-192`): cross-role visibility (lihat SEMUA dokumen), `excludeCsvImports`, filter
  search/dari/tanggal/nilai, switch `status` 5 bucket, eager-load `roleData` (akutansi) +
  `roleStatuses` (verifikasi/perpajakan/akutansi/pembayaran) + `dibayarKepadas`/`dokumenPos`/`dokumenPrs`.
  Per-baris enrich (`:239-279`): `is_locked`, `can_edit` (`DokumenHelper::canEditDocument(...,'akutansi')`),
  `can_set_deadline`, `is_at_my_role`.
- Kolom: `Arr::except(config('document_columns.base'), ['status'])` (`:291`). Sesi
  `akutansi_dokumens_table_columns` + DB `table_columns_preferences['akutansi']`. Default:
  `nomor_agenda, nomor_spp, tanggal_masuk, nilai_rupiah, nomor_miro, link`. Plus 3 kolom
  tetap non-kustom: **Deadline, Status, Pengurus Dokumen**.
- **Belum ada endpoint JSON** (tak ada `@datatable` seperti operator). Baris hanya
  server-rendered `_rows`/`_chunk` (+ `virtual-document-table`).
- Acuan operator: `DokumenController@datatable` (`web.php:301`, `:125-148`) → baris via
  `App\Support\OperatorDocumentRow::fromDokumen(Dokumen, array $handlerOptions, ?string $viewerRole)`.

## 3. Arsitektur Target

### 3.1 Basis bersama `App\Support\DocumentRow`
Abstract class (atau trait) berisi keluaran yang KINI ada di `OperatorDocumentRow` dan
dipakai semua role: `id` + loop kolom `config('document_columns.base')`; `nilai_rupiah_formatted`,
`dpp_pph_formatted`, `ppn_terhutang_formatted`; `dibayar_kepada` (join `dibayarKepadas.nama_penerima`);
`nomor_po` (join); `nomor_miro_display`; `link_safe`/`link_dokumen_pajak_safe` (`SafeUrl::external`);
peta `dates`; `handler`/`handler_options`/`can_change_handler`; `status_dokumen_custom` (csv-fallback).
Tanpa query DB (menerima relasi yang sudah di-eager-load).

### 3.2 `OperatorDocumentRow` → mewarisi basis
Refaktor agar memakai basis §3.1 + menambah bit operator: `display_status` (pohon
draft/terkirim), `reject_reason`/`rejected_by`/`rejected_at`, `can_edit` (varian operator).
**Perilaku operator IDENTIK** — keluaran `fromDokumen` sama byte-nya (dijaga suite +
`InlineCreateDokumenTest`/`OperatorInlineCreateRowTest`/`OperatorDatatableTest` + QA operator).

### 3.3 `App\Support\AkutansiDocumentRow` → mewarisi basis
Basis §3.1 + bit akutansi:
- `is_at_my_role` (dokumen sedang di akutansi?).
- `is_locked`, `lock_status_message`, `lock_status_class`, `can_edit`, `can_set_deadline`.
- `status_pembayaran` (mentah).
- **`status_badge`** — objek `{code, label, class, link?}` hasil PORTING pohon keputusan
  status akutansi dari `_rows.blade.php:417-511`: rejected(akutansi/pembayaran)→"Dokumen
  ditolak, cek disini" (link `returns.akutansi.index`); upstream-belum-terima→"Draft";
  pembayaran-pending→"Menunggu Approval dari Pembayaran"; terkirim/bypass→"Terkirim ke
  Pembayaran"; locked/selesai/returned_to_verifikasi/processing fallback.
- **`deadline`** — objek `{received_at, processed_at, deadline_at, umur/label/warna, is_frozen}`
  hasil porting logika kolom Deadline dari `_rows.blade.php:170-411` (termasuk deteksi
  bypass dari roleData pembayaran/verifikasi). DTO menerima roleData akutansi/pembayaran/
  verifikasi yang sudah di-eager-load (tanpa query di dalam DTO).
- TIDAK menyertakan `display_status`/`reject_*` operator.

### 3.4 Endpoint JSON `documents.akutansi.data`
`DashboardAkutansiController@datatable` — route GET `documents/akutansi/data` (STATIS,
sebelum route `{dokumen}`) di grup `web.php:476`. Membalas `{data, last_page, total}`,
memakai ulang query & enrich `dokumens()` (**ekstrak pembangun query jadi method privat
bersama** agar tak menduplikasi), baris via `AkutansiDocumentRow::fromDokumen(...)`.

### 3.5 View Blade + formatter klien
`akutansi/dokumens/daftarAkutansiTabulator.blade.php` (meniru
`operator/dokumens/daftarDokumenTabulator.blade.php`): emit `DOCUMENT_TABULATOR_CONFIG`
(`mountId: 'akutansiTabulatorTable'`, `dataUrl: documents.akutansi.data`, `columns`/`availableColumns`/
`selected` akutansi, `handlerTpl`/`inlineUpdateTpl` bersama, `ie` akutansi); mount
`<div id="akutansiTabulatorTable" class="doc-tabulator">` di `#documentTableContainer`
(dibutuhkan workbench global). Formatter klien untuk kolom **Deadline** & **Status** merender
objek dari server (`status_badge`/`deadline`) — nol pohon keputusan di JS. Dropdown **Pengurus
Dokumen** memakai formatter yang sama seperti operator (partial `document-handler-select`
via handler_options). Bawa filter status akutansi + modal kustomisasi kolom.

### 3.6 Transisi
`dokumens()`: bila `?classic` → view lama; selain itu → view Tabulator baru (kebalikan dari
default sekarang). Setelah QA lolos (fase §6): hapus view lama + flag.

## 4. Dipakai ulang / WAJIB tetap
Engine `document-tabulator.js`, endpoint `documents.inline-update` & `documents.handler.update`,
`config('document_columns.base')`, partial bersama (`_inlineEditEngine`, `_activeCellNav`,
`document-handler-select`, global `compact-document-ui`/`document-workbench-ui`), `SafeUrl`,
`DokumenHelper`, `AkutansiDocumentRow`-eager-loads dari `dokumens()`. Sesi/DB kolom akutansi
(sudah ada). Route `documents.akutansi.index`/`detail`, Inbox, `pengembalian` — tetap.

## 5. Di luar lingkup
- Kolom aksi (mati/dibuang).
- Rollout role lain; kustomisasi freeze (pembayaran).
- Endpoint `documents.akutansi.detail` (fitur detail) — tetap apa adanya.

## 6. Penghapusan setelah QA (fase akhir rollout)
Setelah QA Tabulator akutansi lolos: hapus `akutansi/dokumens/daftarAkutansi.blade.php`,
`_rows.blade.php`, `_chunk.blade.php`, cabang `virtual_chunk` di `dokumens()`, dan flag
`?classic` — membuang seluruh kode legacy/mati akutansi (termasuk ~250 baris CSS mati yang
ditemukan saat QA). Grep lintas-role wajib (partial `_rows`/`_chunk` role lain BEDA berkas).

## 7. Verifikasi (dilaporkan verbatim di plan)
1. `php artisan test` hijau di TIAP tahap. Ekstraksi basis §3.2 dijaga oleh
   `OperatorInlineCreateRowTest`/`InlineCreateDokumenTest`/`OperatorDatatableTest` +
   test baru untuk `AkutansiDocumentRow`/`@datatable`.
2. `node --check public/js/document-tabulator.js` (bila disentuh) exit 0.
3. **QA visual operator** (ekstraksi basis menyentuh backend operator): tabel operator
   identik — nav/edit/copy/paste/kaskade/dropdown/tambah/hapus.
4. **QA visual akutansi**: `/documents/akutansi` → Tabulator; kolom Deadline & badge Status
   benar; dropdown Pengurus (kirim/kembalikan) jalan; inline-edit jalan; kustomisasi kolom
   jalan; pencarian/filter jalan; lintas-role "Di {role}" benar; 0 error konsol. `?classic=1`
   → view lama (banding) sampai fase §6.

## 8. Deploy
Per tahap yang disetujui: commit per-file → push → pull server → clear cache. Penghapusan
view lama (§6) hanya setelah QA akutansi lolos.

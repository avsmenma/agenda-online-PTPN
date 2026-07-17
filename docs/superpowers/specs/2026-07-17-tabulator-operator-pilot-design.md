# Desain: Pilot Tabulator.js — Tabel Dokumen Operator

Tanggal: 2026-07-17
Status: disetujui user (brainstorming interaktif)
Halaman sasaran: `/documents` (route `documents.index`) — role operator (+ admin god-view)

## 1. Latar & Keputusan

User ingin project Agenda Online bermigrasi ke tabel Tabulator.js. Keputusan yang
sudah diambil bersama user:

| Keputusan | Pilihan |
|---|---|
| Role pilot | **Operator** (tabel alur kerja utama) |
| Cakupan | **Paritas penuh langsung** — semua fitur tabel operator yang terlihat sekarang |
| Peluncuran | **Langsung ganti** tabel lama di `/documents` (tanpa versi berdampingan) |
| Arsitektur | **A. Remote/AJAX** — endpoint JSON baru + progressive load |
| Cara muat library | **Self-hosted** di `public/vendor/tabulator/` (tanpa npm/CDN; deploy tetap hanya `git pull`) |

Definisi "paritas": mereplikasi fitur yang *terlihat dan berfungsi* hari ini.
Temuan eksplorasi: tombol kirim-per-baris dan bulk-send **tidak punya UI** saat ini
(endpoint backend ada tapi dead-code di sisi UI) — keduanya **di luar lingkup** pilot.
Pengiriman dokumen tetap lewat dropdown "Pengurus Dokumen" per baris.

## 2. Kondisi Saat Ini (ringkas)

- View: `resources/views/operator/dokumens/daftarDokumen.blade.php` (±5.228 baris;
  CSS ±2.000 baris + JS inline besar).
- Render: `<table>` Blade `@foreach` + `paginate(100)` + infinite scroll `virtual_chunk`
  (partial shared `partials/virtual-document-table.blade.php`).
- Inline edit: partial shared `partials/_inlineEditEngine.blade.php` →
  `PATCH /documents/{id}/inline-update` (payload `{field, value}`).
- Inline create: `POST /documents/inline-create` (payload `{nomor_agenda}`, balasan
  saat ini berisi HTML baris `_tableRowsAjax`).
- Tabulator.js belum ada di project. jQuery via CDN ada di layout.

## 3. Backend

### 3.1 Endpoint data baru

- Route: `GET /documents/data`, nama `documents.data`, middleware `role:admin,operator`.
- Method baru `DokumenController@datatable`, **memakai ulang `buildOperatorQuery()`**
  (scope operator, `search`, `year`, `status_filter`, sort whitelist + natural sort
  `nomor_agenda`). Tidak ada logika filter baru.
- Input dari Tabulator: `page`, `size` (100), plus parameter filter/sort yang sama
  dengan halaman lama.
- Balasan (format progressive load Tabulator):
  `{ "last_page": N, "total": M, "data": [ {row}, ... ] }`.

### 3.2 Bentuk baris JSON

Server menghitung semua nilai turunan agar JS tetap sederhana dan aturan izin tidak
bocor ke frontend. Tiap baris memuat:

- Field mentah sesuai kolom `config('document_columns.base')`.
- `display_status`: `{ code, label, variant }` — derivasi `status` + `roleStatuses`
  (4 varian: `belum_dikirim`, `dikembalikan`, `menunggu_verifikasi`, `terkirim`).
- `nilai_rupiah_formatted`, `dpp_pph_formatted`, `ppn_terhutang_formatted`.
- `dibayar_kepada` (join `dibayarKepadas.nama_penerima`), `nomor_po` (join
  `dokumenPos`), `nomor_miro_display` (accessor).
- `reject_reason` (alasan penolakan/pengembalian, untuk modal alasan global).
- `can_edit` (boolean; aturan sama dengan `data-editable` lama: handler=operator dan
  status ∈ draft/returned_to_operator/belum_dikirim/menunggu_approval_keuangan atau
  ditolak verifikasi).
- `handler` + `handler_options` (untuk dropdown Pengurus Dokumen).

### 3.3 Penyesuaian endpoint tulis

- `inlineCreate`: balasan ditambah objek baris JSON (field yang sama dengan §3.2);
  konsumen satu-satunya adalah halaman operator ini.
- `inline-update`, `handler`, `detail`, `destroy`: **tanpa perubahan**.

## 4. Frontend

### 4.1 Library & asset

- `public/vendor/tabulator/tabulator.min.js` + `tabulator.min.css` (Tabulator 6.x,
  file dist di-commit ke repo).
- Dimuat via `@push('styles')` / `@push('scripts')` di view operator saja.
- Satu file tema kustom `public/css/tabulator-agenda.css`: header teal `#083E40`,
  densitas kompak, tinggi baris, hover, lebar kolom mengikuti `.col-*` lama.

### 4.2 Definisi kolom

- Dibangkitkan server-side di Blade dari `config('document_columns.base')` +
  session `dokumens_table_columns`, dikirim ke JS via `@json`.
- Modal "Kustomisasi Kolom" lama **tetap dipakai apa adanya** (kirim `columns[]` →
  session → reload halaman → definisi kolom Tabulator ikut berubah).
- `nomor_agenda` → `frozen: true` (pengganti sticky lama). Kolom `No` → `rownum`.

### 4.3 Formatter (paritas `_tableRowsAjax`)

- Badge status 4 varian memakai kelas CSS badge lama; klik badge "Dikembalikan" →
  modal alasan penolakan **global tunggal** (data dari `reject_reason` baris),
  menggantikan modal per-baris Blade.
- Rupiah, tanggal `d-m-Y` / `d/m/Y H:i` per kolom, `nomor_agenda` tebal + sub-baris
  `{bulan} {tahun}`, link eksternal (URL sudah disanitasi server), badge hijau
  `pemaraf`.
- Kolom "Pengurus Dokumen": formatter dropdown replika `document-handler-select` →
  `PATCH /documents/{id}/handler`; gagal → kembalikan pilihan + toast.

### 4.4 Inline edit & create

- Editor per field mengikuti peta `FIELD_TYPE` `_inlineEditEngine`: text, textarea,
  number, date, select bulan/bagian/jenis pembayaran, select berantai
  kategori → sub kriteria → item sub kriteria.
- Sel editable ⇔ `can_edit` baris true DAN field ∉ {`tanggal_masuk`, `status`,
  `keterangan`}. Server tetap memvalidasi ulang (whitelist `inlineUpdate`).
- `cellEdited` → PATCH `inline-update`; sukses → tampilkan `display_value` server;
  gagal (403/422/500) → **kembalikan nilai lama** + toast pesan server.
- Tombol "Tambah Baris" → `addRow` di puncak tabel, sel `nomor_agenda` langsung
  terbuka → Enter/blur → POST `inline-create` → isi baris dari JSON balasan.
  Duplikat → baris dibatalkan + toast error validasi.

### 4.5 Aksi baris & integrasi lama

- Double-click baris → modal detail lama (`GET /documents/{id}/detail`); tombol
  hapus tetap di footer modal (`DELETE /documents/{id}`).
- Single-click → panel Detail Cepat global (`document-workbench-ui`) tetap jalan;
  container/id yang dibutuhkannya dipertahankan.
- Toolbar filter (search, tahun, status) tetap, tapi tanpa reload halaman:
  nilai → parameter AJAX → `setData`.

### 4.6 Mode muat data

- `progressiveLoad: "scroll"`, 100 baris per permintaan — pengganti `virtual_chunk`.
- **Penyimpangan paritas yang disetujui:** dropdown `per_page` (10/25/50/100/all)
  dihapus; Tabulator mengatur pemuatan otomatis.

### 4.7 Pembersihan (view operator SAJA)

Dihapus dari `daftarDokumen.blade.php`: CSS tabel lama (±2.000 baris `.data-table`/
`.col-*`), JS `tambahBarisInline`/`na-row`, mesin inline-edit duplikat (baris
±4728–5160, sudah nonaktif via flag), `toggleSort`, `refreshDocumentTable`,
`loadDocumentDetail` (dead), include `virtual-document-table`,
`_documentTableStickyCells`, `_activeCellNav` dari view operator.

**Tidak boleh dihapus** (dipakai role lain): file partial shared
`_inlineEditEngine`, `virtual-document-table`, `_documentTableStickyCells`,
`_activeCellNav`, `_compactDocumentTable`, `document-handler-select`,
`document-workbench-ui`, serta endpoint `bulk-send-to-verifikasi` /
`send-to-verifikasi` (dibiarkan, di luar lingkup).

## 5. Penanganan Error

- Gagal muat data → pesan + tombol "Coba lagi" di area tabel.
- Semua AJAX memakai CSRF meta tag layout yang sudah ada.
- Error edit/create/handler → toast pola lama; tampilan tidak pernah dibiarkan
  berbeda dari keadaan server (selalu revert atau refresh baris).

## 6. Rencana Uji & Rilis

1. Lokal: render halaman, scroll progresif, sort, tiga filter, edit tiap tipe
   field, tambah baris (termasuk duplikat), ganti pengurus, modal detail, hapus,
   kustomisasi kolom, panel Detail Cepat.
2. Commit kecil bertahap (endpoint → view → styling → pembersihan), pesan Bahasa
   Indonesia, `git add` per-file.
3. Deploy: push `codinggemini` → pull di server → clear route/view/config cache.
4. Verifikasi server: tinker HTTP-render halaman `/documents` + cek
   `/documents/data` membalas JSON sehat (`last_page`, `data`).
5. QA visual oleh user sebagai operator sebelum tahap dianggap selesai.

## 7. Di Luar Lingkup (fase berikutnya)

- Rollout Tabulator ke role lain (verifikasi, perpajakan, akutansi, pembayaran,
  bagian, owner, inbox, programmer) — memakai pola endpoint+formatter pilot ini.
- Tombol kirim per baris / bulk send (UI-nya memang sudah tidak ada hari ini).
- Penghapusan file partial shared mesin tabel lama (baru boleh setelah semua role
  bermigrasi).

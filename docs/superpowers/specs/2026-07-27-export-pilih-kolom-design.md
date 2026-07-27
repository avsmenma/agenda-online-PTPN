# Desain: Prompt Pemilihan Kolom sebelum Export (Excel & PDF)

- **Tanggal:** 2026-07-27
- **Status:** Disetujui user (2026-07-27)
- **Cakupan:** Engine Tabulator bersama — berlaku 5 role keuangan
  (operator/akutansi/perpajakan/verifikasi/pembayaran)
- **Lanjutan dari:** `2026-07-26-fitur-export-bersama-design.md`

---

## 1. Masalah

Tombol **Export** (Excel/PDF) sekarang **langsung** mengunduh/mencetak tanpa
menanyakan kolom. Secara teknis ia sudah mengirim `columns[]` = kolom data yang
*sedang tampil* di tabel (WYSIWYG hasil "Kustomisasi Kolom"), tapi user tidak
pernah ditanya. Untuk **Excel** ini tidak masalah (semua kolom boleh ikut).
Untuk **PDF** ini bermasalah: kertas A4 tidak muat bila kolom terlalu banyak,
hasil cetak jadi sempit / tidak terbaca.

Keputusan user: **tampilkan prompt pemilihan kolom sebelum export**, untuk
**kedua format**, berlaku untuk **semua 5 role**.

## 2. Keputusan desain (disetujui)

1. **Modal terpadu**, bukan dropdown. Tombol **"Export"** membuka SATU modal
   berisi (a) pilih format (radio Excel/PDF) dan (b) pilih kolom (checkbox).
   Dropdown Excel/PDF pure-CSS yang lama **dihapus** (rapuh; digantikan modal).
2. **Peringatan A4 lunak** (bukan blokir keras) saat format = PDF.
3. Berlaku 5 role via engine bersama (semua yang mengisi `CFG.exportUrl`).

## 3. Arsitektur

- **Satu-satunya berkas yang berubah: `public/js/document-tabulator.js`**, di dalam
  IIFE `wireExportButton()` (saat ini ~baris 1402–1494). Perubahan **aditif**:
  ganti dropdown + navigasi-langsung menjadi tombol tunggal yang membuka modal.
- **NOL perubahan server.** Controller tiap role (`exportDocuments`) sudah:
  - menerima `columns[]` dari request,
  - meng-*intersect* dengan katalog kolom role (pertahanan field asing),
  - fallback ke kolom tersimpan user → seluruh katalog bila kosong.
  `respondDocumentExport()` / `DocumentExporter::toXlsx()` / view
  `exports.document-print` **tidak disentuh**.
- **NOL perubahan Blade.** Modal dibuat & disuntik oleh engine JS, dengan CSS
  **scoped sendiri** (prefiks kelas `.doc-export-modal*`) agar identik di 5 role
  tanpa bergantung pada CSS `.customization-modal` yang didefinisikan per-role.
  Gaya visual meniru modal "Kustomisasi Kolom" (kartu putih rounded, aksen
  `#083E40`, overlay `rgba(0,0,0,.5)`, toggle kelas `.show`).

## 4. Interaksi & alur data

1. Engine merender tombol **"Export"** (ikon `fa-file-export`) di `.tabulator-toolbar`
   — HANYA bila `CFG.exportUrl` diisi (sama seperti sekarang).
2. Klik "Export" → `openExportModal()`:
   - Bangun daftar kolom dari `table.getColumns()`.
   - **Kandidat kolom** = kolom yang `getField()` bukan kosong, bukan `'handler'`,
     dan **tidak** termasuk `CFG.extraColumns[].field` (mis. `status_badge`,
     `deadline`) — filter IDENTIK dengan `visibleColumnFields()` sekarang, KECUALI
     TANPA saringan `isVisible()` (kolom tersembunyi tetap tampil sebagai opsi).
   - **Label** kolom diambil dari `col.getDefinition().title` (fallback ke field).
   - **Ter-centang default** = kolom yang `isVisible()` saat ini (WYSIWYG user).
   - Format radio default = **Excel**.
3. Kontrol modal:
   - Radio **Excel / PDF**. Saat PDF terpilih → tampilkan catatan A4 (lihat §5).
   - Checkbox per kolom (urutan mengikuti urutan kolom tabel).
   - Tombol **"Pilih Semua"** dan **"Kosongkan"**.
   - Tombol **"Export"** (konfirmasi) + **"Batal"**.
4. Konfirmasi **"Export"** → `buildExportUrl(format, chosenFields)` → `window.location`.
   `buildExportUrl` tetap seperti sekarang: gabungkan `getFilterParams()` +
   `columns[]=<field terpilih>` + `format`. Satu-satunya beda: daftar field kini
   datang dari centang modal, bukan otomatis `visibleColumnFields()`.
5. Modal ditutup oleh: tombol Batal, klik overlay (area gelap), tombol **Esc**,
   atau setelah konfirmasi Export.

## 5. Aturan khusus PDF (inti permintaan)

- Saat radio = **PDF**, tampilkan catatan halus di bawah daftar kolom:
  > "Kertas A4 landscape — pilih secukupnya (± ≤ 9 kolom) agar muat & terbaca."
- **Lunak, bukan blokir.** User tetap boleh memilih lebih dari 9 kolom; catatan
  hanya mengingatkan. (Threshold `PDF_SOFT_LIMIT = 9`, mudah diubah.)
- Saat kolom tercentang melebihi threshold DAN format = PDF, catatan boleh berubah
  gaya (mis. warna kuning) sebagai penegasan — tetap tidak memblokir.

## 6. Edge case

- **0 kolom tercentang** → tombol **"Export" dinonaktifkan** (disabled). Mencegah
  `columns[]` kosong yang akan memicu fallback server ke "seluruh katalog" —
  perilaku yang mengejutkan (user sengaja mengosongkan tapi malah dapat semua).
- **`getColumns()` gagal / kosong** → modal tetap bisa dibuka; bila tak ada
  kandidat kolom, tampilkan pesan "Tidak ada kolom untuk diexport" dan Export
  dinonaktifkan (defensif; tak seharusnya terjadi pada 5 role live).
- **`CFG.extraColumns` tak diisi** (role tanpa kolom tetap) → set kosong, tak ada
  efek — sama seperti perilaku `visibleColumnFields()` sekarang.

## 7. Yang TIDAK berubah (jaring pengaman)

- Route `documents.<role>.export` (kelima) — tetap.
- `exportDocuments()` tiap controller, trait `ExportsDocuments`,
  `DocumentExporter`, view `exports/document-print.blade.php` — tetap.
- Toolbar/filter, modal "Kustomisasi Kolom" per-role (tabel view) — tetap.
- Perilaku ketika `CFG.exportUrl` kosong (role tanpa export) — tetap tanpa tombol.

## 8. Rencana pengujian

- **Backend:** tak ada jalur server baru → suite PHPUnit (245) tetap sebagai
  regresi export (route & kontrak `columns[]` tak berubah). Jalankan
  `php artisan test` — harus hijau sebelum commit.
- **JS klien:** tak ada test JS di repo (konsisten dengan engine sekarang).
  Verifikasi lewat pembacaan kode + QA browser.
- **QA visual (tanggung jawab user, dinyatakan jujur):** buka 1 role (mis.
  pembayaran), klik Export → modal muncul; centang default = kolom terlihat;
  ganti radio ke PDF → catatan A4 muncul; Export Excel & PDF menghasilkan kolom
  sesuai centang; 0 kolom → tombol nonaktif; Esc/overlay menutup modal.
  Idealnya cek juga 1 role lain untuk memastikan paritas lintas-role.

## 9. Risiko

- **Lintas-role (gerbang kritis CLAUDE.md §6):** engine bersama → perubahan
  menyentuh 5 role sekaligus. Dimitigasi: perubahan aditif & terlokalisasi di
  `wireExportButton()`, CSS scoped sendiri, tak ada perubahan server/Blade.
- **Regresi dropdown:** menghapus dropdown pure-CSS lama; digantikan modal. Tombol
  "Export" tetap satu titik masuk, jadi tak ada endpoint yang hilang.

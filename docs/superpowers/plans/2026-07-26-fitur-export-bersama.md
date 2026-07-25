# Fitur Export Bersama — Semua Role Keuangan (Excel + PDF, dependency-free) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Satu fitur export daftar-dokumen bersama untuk 5 role keuangan — Excel (`.xls` XML, dependency-free, rapi) + PDF (print-view) + kolom kustom (WYSIWYG) + gating per-role — menggantikan export PhpSpreadsheet pembayaran yang rusak & menghapus dead export.

**Architecture:** Service bersama `App\Support\DocumentExporter` (penulis XML Spreadsheet 2003, generalisasi penulis owner) + trait `Concerns\ExportsDocuments` + PDF print-view bersama + tombol Export aditif di engine Tabulator. Tiap role reuse `build<Role>Query` + `<Role>DocumentRow` + katalog kolomnya.

**Tech Stack:** Laravel 12, PHP ^8.2, Blade, PHPUnit. **Nol library baru** (tanpa phpspreadsheet/dompdf).

**Spec:** `docs/superpowers/specs/2026-07-26-fitur-export-bersama-design.md`

## Global Constraints

- **Commit per-file**, pesan Indonesia. Satu commit = satu perubahan logis.
- **UI/komentar Indonesia, identifier English.**
- **Suite hijau** (`php artisan test`) sebelum tiap commit.
- **Nol library baru** — Excel via XML Spreadsheet 2003 string; PDF via Blade print-view (`window.print()`). JANGAN `composer require`.
- **Perubahan `document-tabulator.js` (engine global) WAJIB ADITIF** — tombol Export aktif hanya bila `CFG.exportUrl` diset; role tanpa itu tak berubah.
- **Gating per-role WAJIB:** route export tiap role di bawah middleware `role:` sendiri; hanya export data role itu.
- **Nol logika bisnis baru di export** — nilai sel dari `<Role>DocumentRow` (sudah terformat server).
- **Sebelum hapus dead export code (Task 6): grep-gate + surface ke user.**
- **QA visual = tanggung jawab user** (Playwright READ-ONLY, tiap role). Format `.xls` harus terbuka bersih di Excel/LibreOffice.

---

## Referensi (BACA — artefak nyata)

- **Engine XLS template:** `app/Http/Controllers/OwnerDashboardController.php` ~:1996-2240 (`exportRekapanKeterlambatan` — Workbook XML Spreadsheet 2003: `<Styles>` Title/Header/Cell/CellCenter/CellRight + `<Worksheet><Table><Row><Cell><Data>`; multi-worksheet; `deleteFileAfterSend`/string response). GENERALISASI jadi `DocumentExporter`.
- **DTO:** `app/Support/DocumentRow.php` + subclass `OperatorDocumentRow`/`AkutansiDocumentRow`/`PerpajakanDocumentRow`/`VerifikasiDocumentRow`/`PembayaranDocumentRow` (nilai terformat: `nilai_rupiah_formatted`, `dates`, dll.).
- **Controller per-role:** operator=`DokumenController`; akutansi=`DashboardAkutansiController`; perpajakan=`DashboardPerpajakanController`; verifikasi=`TeamVerifikasiController`; pembayaran=`DashboardPembayaranController`. Semua sudah punya `build<Role>Query` + endpoint `documents.<role>.data` (kecuali operator — cek namanya, ekstrak bila perlu).
- **Katalog kolom:** `config/document_columns.php` (operator/akutansi/perpajakan/verifikasi); `getPembayaranDashboardAvailableColumns()` (pembayaran, 48).
- **Export pembayaran lama (diganti):** `DashboardPembayaranController::exportRekapan()` (~:980), `exportToExcel()` (~:1238, RUSAK PhpSpreadsheet), `exportToExcelByVendor()` (~:1425, RUSAK), `exportToPDF()` (~:1718, JALAN, print-view), `getColumnValue`/`getExportCellValue`.
- **Dead export (dihapus Task 6):** `app/Exports/RekapanKeterlambatanExport.php`, `DashboardPerpajakanController::exportToPDF()` (~:713) + view `perpajakan/export/pdf.blade.php`.
- **Engine toolbar:** `public/js/document-tabulator.js` (bagian toolbar/tombol — tempat menambah tombol Export aditif).

---

## Task 1: `App\Support\DocumentExporter` service (XLS XML) + unit test

**Files:**
- Create: `app/Support/DocumentExporter.php`
- Create: `tests/Unit/DocumentExporterTest.php`

**Interfaces:**
- Produces:
  - `DocumentExporter::toXlsx(array $columns, array $rows, array $options = []): string` — `$columns=[['key'=>..,'label'=>..], ...]`; `$rows` = array asosiatif (baris DTO); mengembalikan STRING dokumen XML Spreadsheet 2003. `$options`: `title` (judul sheet), `total_key` (kolom yang dijumlahkan di baris TOTAL, mis. `nilai_rupiah`), `sheets` (untuk multi-sheet pembayaran: `[['name'=>..,'rows'=>[...],'subtotal'=>bool], ...]`).
  - `DocumentExporter::cellValue(array $row, string $key): string` — ambil nilai sel dari baris DTO (utamakan `<key>_formatted` bila ada, mis. `nilai_rupiah`→`nilai_rupiah_formatted`; tanggal via `dates[key]`; fallback `row[key]`), string aman.
  - Helper `xmlEscape(string): string` (escape `& < > "`).

### Kontrak
- Format: **XML Spreadsheet 2003** (`<?mso-application progid="Excel.Sheet"?>` + `<Workbook>`), meniru struktur `OwnerDashboardController` ~:1996 (Styles Title/Header/Cell/CellRight + Worksheet/Table/Row/Cell). Rapi: header berwarna, border, baris TOTAL (Σ `total_key`) bila diset.
- Angka rupiah: sel `total_key` boleh Number; sisanya String. Escape XML semua nilai.
- Multi-sheet: bila `$options['sheets']` diberi → satu `<Worksheet>` per entri (nama sheet disanitasi ≤31 char), subtotal per sheet + grand total bila `subtotal`.

- [ ] **Step 1: Tulis unit test yang gagal** — `tests/Unit/DocumentExporterTest.php`:
```php
public function test_toxlsx_menghasilkan_xml_valid_dengan_header_dan_baris(): void
{
    $cols = [['key'=>'nomor_agenda','label'=>'Nomor Agenda'],['key'=>'nilai_rupiah','label'=>'Nilai']];
    $rows = [['nomor_agenda'=>'5377_2026','nilai_rupiah'=>1000,'nilai_rupiah_formatted'=>'Rp 1.000']];
    $xml = DocumentExporter::toXlsx($cols, $rows, ['title'=>'Uji','total_key'=>'nilai_rupiah']);
    $this->assertStringContainsString('<?mso-application progid="Excel.Sheet"?>', $xml);
    $this->assertStringContainsString('Nomor Agenda', $xml);
    $this->assertStringContainsString('5377_2026', $xml);
    $this->assertStringContainsString('Rp 1.000', $xml);
    // XML well-formed
    $this->assertNotFalse(simplexml_load_string($xml));
}
public function test_escape_xml_aman(): void
{
    $xml = DocumentExporter::toXlsx([['key'=>'x','label'=>'X']], [['x'=>'A & B < C > "D"']]);
    $this->assertStringContainsString('A &amp; B &lt; C &gt; &quot;D&quot;', $xml);
    $this->assertNotFalse(simplexml_load_string($xml));
}
public function test_multi_sheet_pembayaran(): void
{
    $xml = DocumentExporter::toXlsx([['key'=>'x','label'=>'X']], [], ['sheets'=>[
        ['name'=>'Vendor A','rows'=>[['x'=>'1']]],['name'=>'Vendor B','rows'=>[['x'=>'2']]],
    ]]);
    $this->assertSame(2, substr_count($xml, '<Worksheet'));
    $this->assertNotFalse(simplexml_load_string($xml));
}
```
- [ ] **Step 2: Jalankan — GAGAL** (`php artisan test --filter=DocumentExporterTest`).
- [ ] **Step 3: Implementasi `DocumentExporter`** — port struktur XML dari `OwnerDashboardController` ~:1996-2240, generalisasi jadi data-driven (`$columns`+`$rows`). `cellValue` ambil dari DTO. Escape wajib.
- [ ] **Step 4: LULUS** + suite penuh hijau.
- [ ] **Step 5: Commit** (per-file service + test).

---

## Task 2: PDF print-view bersama + trait `ExportsDocuments` + tombol Export engine (aditif)

**Files:**
- Create: `resources/views/exports/document-print.blade.php`
- Create: `app/Http/Controllers/Concerns/ExportsDocuments.php`
- Modify: `public/js/document-tabulator.js` (tombol Export aditif)

**Interfaces:**
- Produces (trait): `protected function respondDocumentExport(Request $request, \Illuminate\Support\Collection|array $rows, array $columns, array $options = []): \Symfony\Component\HttpFoundation\Response` — `format=excel` → `DocumentExporter::toXlsx(...)` sebagai unduhan `.xls` (`Content-Type: application/vnd.ms-excel`, `Content-Disposition attachment; filename="<judul>-<tanggal>.xls"`); `format=pdf` → `view('exports.document-print', [...])`.
- Consumes: `DocumentExporter` (Task 1).
- Produces (engine): `CFG.exportUrl` → tombol/dropdown "Export" (Excel/PDF) di toolbar; menyusun URL `CFG.exportUrl + ?format=excel|pdf + <filter aktif> + columns[]=<kolom terlihat>`.

- [ ] **Step 1: PDF print-view** `resources/views/exports/document-print.blade.php` — terima `$columns`,`$rows`,`$title`; tabel rapi (header instansi PTPN, judul, tabel border, tanggal cetak) + `<script>window.print()</script>`. Nilai sel via `DocumentExporter::cellValue($row,$key)` (atau helper Blade setara). Dependency-free.
- [ ] **Step 2: Trait `ExportsDocuments`** — `respondDocumentExport()` dispatch excel/pdf sesuai `$request->get('format','excel')`. Sanitasi nama file. (Digunakan tiap role controller di Task 3-4.)
- [ ] **Step 3: Tombol Export engine (ADITIF)** — di toolbar `document-tabulator.js`, bila `CFG.exportUrl` truthy, render tombol "Export" (dropdown Excel/PDF). On click: kumpulkan filter aktif (dari `toolbarFilterControls`/`getFilterParams`) + kolom terlihat (visible columns) → `window.location = CFG.exportUrl + '?' + params + '&format=' + fmt`. Role tanpa `CFG.exportUrl` → tak ada tombol (paritas). Jalankan `node --check`.
- [ ] **Step 4: Suite hijau** (tak ada test PHP menyentuh engine) + commit per-file (view, trait, engine).

---

## Task 3: Wire PEMBAYARAN — ganti export PhpSpreadsheet rusak + mode per-vendor

**Files:**
- Modify: `app/Http/Controllers/DashboardPembayaranController.php`
- Modify: `resources/views/pembayaranNEW/dashboardPembayaran.blade.php` (set `CFG.exportUrl` + arahkan tombol/form export ke jalur baru)
- Modify: `routes/web.php`
- Create: `tests/Feature/PembayaranExportTest.php`

- [ ] **Step 1: Tulis feature test gagal** — `GET route('documents.pembayaran.export', ['format'=>'excel'])` (user pembayaran) → 200 + `Content-Type` mengandung `ms-excel` + body mengandung header kolom + salah satu `nomor_agenda`. `format=pdf` → 200 + memuat judul print. Dokumen CSV ikut (query include CSV). Gating: user role lain → 403.
- [ ] **Step 2: Route** `GET documents/pembayaran/export` → `exportDocuments` (nama `documents.pembayaran.export`) di grup `role:admin,pembayaran`.
- [ ] **Step 3: `exportDocuments(Request)`** di controller (pakai trait `ExportsDocuments`): `buildPembayaranQuery($request)` (filter+CSV) → map `PembayaranDocumentRow::fromDokumen` → kolom terpilih (dari `visible_columns`/`getSavedPembayaranDashboardColumns` + katalog `getPembayaranDashboardAvailableColumns`) → `respondDocumentExport(...)`. **Mode per-vendor:** bila `vendor_export_mode ∈ {multi_sheet,single_sheet,single_vendor}` → susun `$options['sheets']` (group per `dibayar_kepada` + subtotal) lalu `toXlsx`. Judul "Rekapan Pembayaran".
- [ ] **Step 4: GANTI method rusak** — `exportToExcel()` & `exportToExcelByVendor()` (PhpSpreadsheet) DIHAPUS; `exportRekapan()` dialihkan memanggil `exportDocuments`/service (atau exportRekapan dijadikan thin wrapper → service). `exportToPDF()` (print-view lama) boleh diganti jalur PDF bersama. Update form/tombol export di view → arahkan ke `documents.pembayaran.export` + set `CFG.exportUrl`.
- [ ] **Step 5: LULUS + suite hijau + commit** (per-file: controller, view, routes, test).

---

## Task 4: Wire 4 role lain (operator, akutansi, perpajakan, verifikasi)

**Files (per role):**
- Modify: controller masing-masing (tambah `exportDocuments` via trait)
- Modify: view Tabulator masing-masing (set `CFG.exportUrl`)
- Modify: `routes/web.php` (route `documents.<role>.export` di grup role-nya)
- Create: `tests/Feature/DocumentExportRolesTest.php` (parametrik 4 role)

Untuk TIAP role (operator/akutansi/perpajakan/verifikasi), pola identik (via trait `ExportsDocuments`):
- [ ] **Step 1: Tulis feature test gagal** — untuk tiap role: `GET route('documents.<role>.export',['format'=>'excel'])` (user role itu) → 200 + ms-excel + header kolom; `format=pdf` → 200; **gating**: user role lain → 403.
- [ ] **Step 2: Route** `GET documents/<role>/export` → `exportDocuments` di grup middleware `role:` role itu.
- [ ] **Step 3: `exportDocuments(Request)`** tiap controller: `build<Role>Query` (filter) → map `<Role>DocumentRow::fromDokumen` → kolom terpilih (config document_columns + preferensi role) → `respondDocumentExport(...)`. Judul = nama role.
  - **operator (`DokumenController`):** konfirmasi nama query builder + sumber kolom terpilih; ekstrak `buildOperatorQuery` bila belum ada (JANGAN ubah perilaku tabel/endpoint data). Bila operator tak punya preferensi kolom → default semua kolom base.
- [ ] **Step 4: `CFG.exportUrl`** di tiap view Tabulator role → `route('documents.<role>.export')`.
- [ ] **Step 5: LULUS (4 role) + suite hijau + commit** (per-file, per-role bila memungkinkan).

---

## Task 5: 🚦 QA GATE — Playwright produksi (USER)

Deploy dulu (push→pull→clear cache), lalu QA READ-ONLY tiap role (login masing-masing):
- [ ] Tombol **Export (Excel/PDF)** muncul di toolbar tiap role.
- [ ] Unduhan **`.xls`** terbuka **rapi** di Excel/LibreOffice (header berwarna, border, TOTAL) — cek minimal pembayaran + 1 role lain.
- [ ] **PDF** print-view rapi.
- [ ] **Kolom mengikuti pilihan** user (WYSIWYG) + **filter dihormati**.
- [ ] **Pembayaran mode per-vendor** (multi/single-sheet + subtotal) jalan.
- [ ] **Gating:** role tak bisa export data role lain (403).
- [ ] Export pembayaran **tak lagi 500**.

**Acceptance:** user konfirmasi. Gagal → fix loop, JANGAN lanjut Task 6.

---

## Task 6: Cleanup dead export (grep-gated, surface DULU)

- [ ] **Step 1: Grep-gate** kandidat: `app/Exports/RekapanKeterlambatanExport.php` (cek nol referensi), `DashboardPerpajakanController::exportToPDF()` (~:713) + view `perpajakan/export/pdf.blade.php` (cek yatim), sisa method pembayaran lama (`getColumnValue`/`getExportCellValue` bila tak lagi dipakai pasca Task 3). Cek `Maatwebsite\Excel` — apakah ADA kode LIVE lain yang memakainya (kalau tidak, catat sebagai kandidat hapus composer TERPISAH).
- [ ] **Step 2: 🚦 SURFACE findings ke user** — tabel simbol→yatim/hidup. Default pertahankan yang ragu. Composer `maatwebsite/excel` = keputusan terpisah (JANGAN ubah composer tanpa izin).
- [ ] **Step 3: Hapus yang disetujui.** Update test yg merujuk kode dihapus.
- [ ] **Step 4: Suite hijau.**
- [ ] **Step 5: Update `CLAUDE.md`** — catat fitur export bersama (5 role, dependency-free), export pembayaran diperbaiki, dead export dihapus.
- [ ] **Step 6: Commit** per-file.

---

## Task 7: Deploy

- [ ] Push `git push origin codinggemini`.
- [ ] Server (SSH key `-i C:\Users\ASUS\.ssh\crypto_bot_vps root@163.61.58.92`): `cd /var/www/agenda-online-PTPN && git pull && php artisan route:clear && view:clear && config:clear && cache:clear`.
- [ ] Smoke produksi (user/Playwright): export tiap role 200 + unduhan valid; pembayaran tak 500; gating 403 lintas-role.

---

## Self-Review (penulis plan)

- **Spec coverage:** §2 service→Task1; PDF+trait+tombol→Task2; §3 wiring→Task3(pembayaran)+Task4(4 role); §4 UI→Task2/3/4; §5 per-vendor→Task3; §6 cleanup→Task6; §7 out-of-scope owner→tak disentuh; §8 testing→tiap task+Task5; §9 gerbang→Task2/5/6. ✅
- **Placeholder scan:** kode service+test konkret; wiring per-role via trait dgn referensi artefak nyata; operator ditandai "konfirmasi/ekstrak query".
- **Type consistency:** `toXlsx(columns,rows,options)` konsisten Task1↔2↔3; `respondDocumentExport(...)` konsisten Task2↔3↔4; `CFG.exportUrl` konsisten Task2↔3↔4.

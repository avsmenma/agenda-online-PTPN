# Fitur Export Bersama — Semua Role Keuangan (Excel + PDF, dependency-free)

**Tanggal:** 2026-07-26
**Status:** Disetujui (siap → writing-plans)
**Role tercakup:** operator, akutansi, perpajakan, team_verifikasi, pembayaran (5 role keuangan)
**Latar:** Export Excel pembayaran RUSAK (`Class "PhpOffice\PhpSpreadsheet\Spreadsheet" not found` — kode pakai PhpSpreadsheet tapi hanya `maatwebsite/excel ^1.1`/PHPExcel abandoned terpasang). Hanya pembayaran yang punya export daftar-dokumen; role lain belum. Keputusan user: bangun SATU fitur export bersama untuk semua role, dependency-free.

---

## 1. Tujuan

Satu fitur export daftar-dokumen bersama untuk 5 role keuangan: **Excel** (`.xls`, dependency-free) + **PDF** (print-view), output **rapi/profesional**, **kolom bisa dikustom** (mengikuti kolom terpilih role), filter dihormati, gating per-role. Menggantikan 2 method PhpSpreadsheet pembayaran yang rusak + menghapus dead code export. **Nol library baru** (tak menambah phpspreadsheet/dompdf).

---

## 2. Arsitektur

- **Service bersama `App\Support\DocumentExporter`** — satu sumber kebenaran.
  - `toXlsx(array $columns, iterable $rows, array $options = []): string` → string dokumen **XML Spreadsheet 2003** (`.xls`, Content-Type `application/vnd.ms-excel`). Dependency-free (generalisasi penulis XML milik `OwnerDashboardController::exportRekapanKeterlambatan()` ~:1996 yang sudah terbukti jalan).
  - Data-driven: `$columns = [{key,label}]`; `$rows` = array baris DTO (`DocumentRow`), tiap sel diambil per `key`.
- **Excel rapi:** baris header berwarna + border tipis + baris **TOTAL** (Σ `nilai_rupiah` bila kolom itu ada) + lebar kolom wajar. Escape XML aman (`&`,`<`,`>`,`"`).
- **PDF:** **Blade print-view bersama** `resources/views/exports/document-print.blade.php` → auto `window.print()`. Tata letak rapi seragam (header instansi, judul role, tabel border, tanggal cetak). Dependency-free (browser print → PDF).
- **Sumber baris:** tiap role memakai `build<Role>Query(Request)` (sudah diekstrak saat rollout Tabulator) + `<Role>DocumentRow` DTO (nilai sudah terformat server) → array baris.
- **Kolom (WYSIWYG + kustom):** kolom yang diexport = **pilihan kolom terkini role** (dari preferensi kolom Tabulator yang sudah ada). Katalog+label per-role: `config('document_columns')` untuk operator/akutansi/perpajakan/verifikasi; `getPembayaranDashboardAvailableColumns()` untuk pembayaran. User mengubah kolom lewat kustomisasi tabel yang sudah ada → export mengikuti.
- **Filter dihormati:** request export membawa filter/pencarian aktif; `build<Role>Query` menerapkannya (sama seperti endpoint `documents.<role>.data`).

---

## 3. Wiring per-role (5 role)

Tiap role controller mendapat method tipis `exportDocuments(Request): Response` (atau via trait bersama `App\Http\Controllers\Concerns\ExportsDocuments`) yang:
1. `build<Role>Query($request)` + terapkan filter (identik jalur data Tabulator).
2. Map tiap Dokumen → `<Role>DocumentRow::fromDokumen(...)`.
3. Ambil kolom terpilih (WYSIWYG) + katalog label role.
4. `format=excel` → `DocumentExporter::toXlsx(...)` → `response()->streamDownload / download` `.xls`. `format=pdf` → `view('exports.document-print', [...])`.

Route baru per-role: `GET documents/<role>/export` → nama `documents.<role>.export`, **di dalam grup middleware `role:` masing-masing** (gating: tiap role hanya export datanya sendiri). Contoh: `documents.pembayaran.export` di grup `role:admin,pembayaran`.

**Peta role → artefak yang sudah ada (dipakai ulang):**
| Role | Controller | DTO | Query | Katalog kolom |
|---|---|---|---|---|
| operator | (controller operator) | `OperatorDocumentRow` | query operator | `config('document_columns')` |
| akutansi | `DashboardAkutansiController` | `AkutansiDocumentRow` | `buildAkutansiQuery` | `config('document_columns')` |
| perpajakan | `DashboardPerpajakanController` | `PerpajakanDocumentRow` | `buildPerpajakanQuery` | `config('document_columns')` |
| verifikasi | `TeamVerifikasiController` | `VerifikasiDocumentRow` | `buildVerifikasiQuery` | `config('document_columns')` |
| pembayaran | `DashboardPembayaranController` | `PembayaranDocumentRow` | `buildPembayaranQuery` | `getPembayaranDashboardAvailableColumns()` (48) |

**Verifikasi saat plan:** nama/lokasi query & katalog kolom operator (role pertama) dikonfirmasi — operator mungkin belum punya `build...Query` bernama sama; ekstrak bila perlu (jangan ubah perilaku tabel).

---

## 4. UI — tombol Export di toolbar (aditif engine)

- Tambah **tombol/dropdown "Export" (Excel / PDF)** ke toolbar Tabulator bersama (`public/js/document-tabulator.js`) secara **ADITIF**: aktif hanya bila `CFG.exportUrl` diset; role yang tak set → tak ada tombol (paritas). Tombol menyusun URL export dengan **filter aktif + kolom terlihat saat ini + format**, lalu navigasi/unduh.
- Semua 5 role kini pakai engine Tabulator → cukup set `CFG.exportUrl` di tiap view. Pembayaran menambah opsi mode per-vendor (§5).

---

## 5. Khas pembayaran — mode group per-vendor (dipertahankan)

- Export bersama default = **flat 1-sheet** untuk semua role.
- Pembayaran mempertahankan opsi lanjutan **group per-vendor**: `multi_sheet` (satu worksheet per vendor + subtotal), `single_sheet` (semua vendor bertumpuk + subtotal per-vendor + GRAND TOTAL) — diimplementasi di atas engine `.xls` bersama (`DocumentExporter` mendukung multi-sheet + baris subtotal via `$options`). Hanya muncul di UI pembayaran (mode `rekapan_table`/vendor).
- Kolom vendor-set khas pembayaran dipertahankan.

---

## 6. Cleanup menyertai (nol dead code)

- **Ganti** `DashboardPembayaranController::exportToExcel()` & `exportToExcelByVendor()` (pakai PhpSpreadsheet, RUSAK) dengan pemanggilan `DocumentExporter`. `exportRekapan()` (orchestrator) tetap tapi dialihkan ke service; `getColumnValue()`/`getExportCellValue()` (switch formatter duplikat) → digantikan nilai `DocumentRow` bila memungkinkan (kurangi duplikasi).
- **Hapus dead code export** (grep-gate + surface dulu): `app/Exports/RekapanKeterlambatanExport.php` (tak pernah dirujuk, akan rusak bila dipakai), `DashboardPerpajakanController::exportToPDF()` (yatim, route sudah dihapus) + view `perpajakan/export/pdf.blade.php` bila yatim.
- **`maatwebsite/excel ^1.1` di composer** = penghapusan TERPISAH (butuh `composer` di server, risiko deploy). Dicatat sebagai follow-up; JANGAN dilakukan di scope ini kecuali user minta. Verifikasi tak ada kode LIVE lain yang memakai `Maatwebsite\Excel` sebelum menyarankan penghapusannya.

---

## 7. DI LUAR SCOPE (tak disentuh)

- Export **"Rekap Keterlambatan" owner** (`OwnerDashboardController::exportRekapanKeterlambatan`) — laporan terpisah, sudah jalan (XML dependency-free). Dibiarkan. (Engine `.xls` bersama bisa dipakai ulang olehnya di kemudian hari — bukan sekarang.)
- Export programmer (SQL dump / CSV) — tak terkait.
- PDF via library (dompdf dll.) — tetap pakai print-view (dependency-free).

---

## 8. Testing & QA

- **Unit `DocumentExporterTest`:** `toXlsx` menghasilkan XML valid (well-formed), escape aman, header+baris+TOTAL benar untuk kolom sampel; multi-sheet + subtotal (mode pembayaran).
- **Feature per-role:** `GET documents/<role>/export?format=excel` → 200 + Content-Type ms-excel + body XML berisi header kolom terpilih; `format=pdf` → 200 + view print. Gating: user role X tak bisa export via route role Y (403). Filter diterapkan (baris terfilter).
- **Suite hijau** sebelum tiap commit.
- **QA produksi (Playwright READ-ONLY, tiap role):** tombol Export muncul; unduh Excel `.xls` terbuka rapi di spreadsheet (header/border/TOTAL); PDF print-view rapi; kolom mengikuti pilihan; filter dihormati; pembayaran mode per-vendor jalan; **export tak lagi 500**. QA visual = tanggung jawab user.

---

## 9. Gerbang kritis

- Menyentuh **engine global** `document-tabulator.js` (tombol Export) — wajib **aditif**, tak mengubah perilaku role tanpa `CFG.exportUrl`.
- Sebelum **hapus** dead export code (§6) — grep-gate + surface findings.
- **Gating per-role** wajib benar (satu role tak boleh export data role lain) — bagian RBAC, uji eksplisit.
- Format `.xls` XML harus dibuka bersih di Excel/LibreOffice (escape + struktur) — QA user.
- Penghapusan dependency composer = keputusan terpisah user.

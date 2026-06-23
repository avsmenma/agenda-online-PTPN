# RENCANA ELIMINASI TU/TK — Agenda Online PTPN

> **Status: FASE A SELESAI (read-only). Menunggu persetujuan sebelum FASE B (eksekusi).**
> Dokumen ini hasil analisa menyeluruh seluruh codebase. Belum ada file/kode yang dihapus.

---

## 1. RINGKASAN

Fitur **TU/TK** membentuk **satu klaster kode yang saling merujuk dan tertutup** (models → controller → tabel pendukung), dan **tidak ada satu pun route, view, atau menu/navigasi yang menjangkaunya**. Semua method TU/TK di controller adalah *dead code* (tidak ter-route). Ini sejalan dengan pernyataan "TU/TK sudah tidak dipakai".

**Temuan penting (menjawab titik paling rawan di prompt):**
Tabel `payment_logs` dan `document_position_trackings` — yang dikhawatirkan "menempel di modul hidup" — ternyata **dipakai EKSKLUSIF oleh kode TU/TK**. Tidak ada modul lain (Dokumen, Pajak, Operator, dll) yang menyentuh kedua model/tabel ini. Bukti: `grep "PaymentLog|DocumentPositionTracking"` di seluruh `app/` hanya muncul di 7 file = 5 model TuTk\* + DashboardPembayaranController (blok mati) + definisi keduanya sendiri. Jadi keduanya **bagian dari TU/TK**, bukan kolom nyangkut di tabel orang lain.

### Hitungan jejak

| Kategori | Jumlah |
|---|---|
| Model TU/TK (`app/Models`) | **7 file** (TuTk, TuTkDokumen, TuTkPupuk, TuTkVd, TuTkTan, PaymentLog, DocumentPositionTracking) |
| Command | **1 file** (`ImportTuTkData` / `tu-tk:import`) |
| View Blade khusus TU/TK | **1 file** (`export-tu-tk-pdf.blade.php`) |
| Migrasi terkait TU/TK | **9 file** (5 create tabel TU/TK + create 2 tabel pendukung + 2 alter kolom) |
| Controller (edit bedah, BUKAN hapus) | **1 file** (`DashboardPembayaranController.php` — blok mati ±10 method + 6 baris `use`) |
| Tabel DB | **7 tabel** (tu_tk_2023, tu_tk_pupuk_2023, tu_tk_vd_2023, tu_tk_tan_2023, tu_tk_dokumens, payment_logs, document_position_trackings) |
| Route / Menu / Navigasi yang memanggil TU/TK | **0 (NOL)** — terbukti tidak terpakai |
| Referensi di Service/Job/Request/Helper/Export/Seeder/Factory/Test/Config | **0 (NOL)** |

---

## 2. BUKTI "BENAR-BENAR TIDAK TERPAKAI"

1. **Tidak ada route.** `routes/web.php`, `api.php`, `console.php` tidak memetakan satu pun method TU/TK. Method ter-route di `DashboardPembayaranController` hanya yang non-TU/TK (`dashboard`, `datatable`, `index`, `dokumens`, `updatePembayaran`, `exportRekapan`, `rekapan`, `getPaymentData`, `getDocumentDetail`, `setDeadline`, `checkUpdates`, `importCsv`, dll). Method TU/TK (`storePaymentInstallment`, `getPaymentLogsByAgenda`, `getPositionTimeline`, `updateDocumentPosition`, `exportRekapanTuTk`, `storePaymentInstallmentBatch`) **tidak ada di routes**. Dikonfirmasi juga oleh `docs_prompting/AUDIT_FINDINGS.md:711` ("tampak tak ter-route").
2. **Tidak ada di view/JS/navigasi.** `grep` endpoint TU/TK (`getPaymentLogsByAgenda`, `position-timeline`, `export-tu-tk`, dll) di seluruh `resources/` → **kosong**. Tidak ada AJAX/menu yang memanggilnya.
3. **Command tidak terjadwal & tidak dipanggil.** `ImportTuTkData` (`tu-tk:import`) tidak ada di schedule (`console.php` / Kernel) maupun dipanggil dari kode mana pun.
4. **Tidak ada konsumen luar.** `PaymentLog` & `DocumentPositionTracking` hanya dirujuk oleh blok mati controller + relasi di model TuTk\*. Tidak ada modul hidup yang memakainya.

---

## 3. DAFTAR FILE YANG AKAN DIHAPUS — kategori [AMAN-HAPUS]

Diurutkan berdasarkan dependensi (yang paling terisolasi dihapus dulu). Semua file di bawah **terbukti tidak dijangkau route/view/menu**.

| Urut | File | Tabel/Identitas | Bukti aman |
|---|---|---|---|
| 1 | `app/Models/TuTkDokumen.php` | tabel `tu_tk_2023` (alias, duplikat logika) | `grep TuTkDokumen` di kode = **nihil** (hanya muncul di docs audit). Tak pernah di-`use`. |
| 2 | `resources/views/pembayaranNEW/dokumens/export-tu-tk-pdf.blade.php` | view export PDF | Hanya dipanggil `exportTuTkToPDF()` (method mati, tak ter-route). |
| 3 | `app/Console/Commands/ImportTuTkData.php` | command `tu-tk:import` | Tak ada schedule/route/pemanggilan. |
| 4 | `app/Models/TuTkPupuk.php` | `tu_tk_pupuk_2023` | Hanya dirujuk blok mati controller + relasi internal. |
| 5 | `app/Models/TuTkVd.php` | `tu_tk_vd_2023` | idem |
| 6 | `app/Models/TuTkTan.php` | `tu_tk_tan_2023` | idem |
| 7 | `app/Models/TuTk.php` | `tu_tk_2023` | Hanya dirujuk blok mati controller + relasi `PaymentLog`/`DocumentPositionTracking` (yang juga ikut dihapus). |
| 8 | `app/Models/PaymentLog.php` | `payment_logs` | Konsumen hanya blok mati controller + relasi TuTk\*. Tidak dipakai modul lain. |
| 9 | `app/Models/DocumentPositionTracking.php` | `document_position_trackings` | idem |

> Urutan hapus definisi (4–9) sebaiknya dilakukan **setelah** blok pemanggil di controller dibersihkan (lihat §4 item A), agar tidak ada momen kode merujuk kelas yang sudah hilang.

---

## 4. ITEM [MENEMPEL] & [RAGU] — BUTUH KEPUTUSANMU

### A. `app/Http/Controllers/DashboardPembayaranController.php` — [MENEMPEL] (file HIDUP, edit bedah)
File ini **TIDAK boleh dihapus** — sebagian besar isinya modul pembayaran yang masih aktif. Namun ia memuat **blok mati khusus TU/TK** yang perlu diangkat:

- **6 baris `use`** (baris 7–12): `TuTk`, `TuTkPupuk`, `TuTkVd`, `TuTkTan`, `PaymentLog`, `DocumentPositionTracking`.
- **10 method TU/TK berurutan** (perkiraan baris **2097–3311**), semuanya *tak ter-route*:
  `parseTanggalBayar` (2097), `sanitizeNominal` (2153), `storePaymentInstallment` (2167), `getPaymentLogsByAgenda` (2352), `getPositionTimeline` (2499), `updateDocumentPosition` (2579), `exportRekapanTuTk` (2651), `exportTuTkToExcel` (2724), `exportTuTkToPDF` (2809), `storePaymentInstallmentBatch` (2834).
- `parseTanggalBayar` & `sanitizeNominal` **hanya** dipakai di dalam blok ini (verified) → ikut diangkat.

**Risiko:** rendah (semua dead code), tapi karena ini file hidup, edit harus presisi — angkat hanya method-method di atas, jangan menyentuh `getRomanNumeral` (3313) dan `rekapan`/`exportRekapan` (modul hidup). **Perlu persetujuanmu** untuk mengedit file hidup ini.

### B. Tabel & data DB `payment_logs`, `document_position_trackings`, `tu_tk_*` — [RAGU/keputusan DB]
Secara kode = aman dihapus. **Tapi**: nama `payment_logs` & `document_position_trackings` generik dan **tabelnya mungkin berisi data** (history pembayaran/posisi). Menghapus model tidak menghapus tabel. Keputusan drop tabel = milikmu (lihat §5).

### C. Referensi di dokumentasi (`docs_prompting/AUDIT_FINDINGS.md`, `REMEDIATION_CHECKLIST.md`) — [RAGU]
Menyebut TuTk/TuTkDokumen sebagai temuan audit. Ini dokumen historis, **bukan kode**. Saran: biarkan apa adanya (riwayat), atau tambahkan catatan "sudah dieliminasi". Tidak memengaruhi aplikasi.

---

## 5. CATATAN MIGRASI & DATA DB (WAJIB DIBACA)

Sesuai aturan prompt & CLAUDE.md — **aku TIDAK akan menjalankan `migrate`, `migrate:fresh`, drop table, atau perintah DB apa pun secara otomatis.**

9 file migrasi terkait:
- `..._create_tu_tk_dokumens_table.php`
- `..._create_tu_tk_2023_table.php`
- `..._create_tu_tk_pupuks_table.php` (tabel `tu_tk_pupuk_2023`)
- `..._create_tu_tk_vd_2023_table.php`
- `..._create_tu_tk_tan_2023_table.php`
- `..._create_payment_logs_table.php`
- `..._create_document_position_trackings_table.php`
- `..._add_data_source_to_payment_logs_and_position_trackings.php`
- `..._add_tu_tk_agenda_to_payment_logs_table.php`

**Yang perlu kamu putuskan:**
1. **Buang tabel dari DB?** Jika ya, aku akan **membuat 1 migrasi `drop` baru** (drop 7 tabel) dan **kamu** yang menjalankannya saat siap. **Tabel mungkin berisi data — konfirmasi dulu sebelum apa pun yang menghapus data.**
2. **File migrasi lama (9 file di atas):** mau **dihapus** dari repo, atau **disimpan sebagai riwayat**? (Menghapus file migrasi tidak mengubah DB yang sudah jalan.)

---

## 6. URUTAN LANGKAH ELIMINASI YANG DIUSULKAN (FASE B — setelah kamu setuju)

Setiap langkah = 1 commit, pesan Bahasa Indonesia, `git add` per-file.

1. **Bersihkan pemanggil dulu** — edit `DashboardPembayaranController.php`: hapus 6 `use` TU/TK + 10 method blok mati (2097–3311). *(item [MENEMPEL] A — butuh approval edit file hidup)*
2. **Hapus view** `export-tu-tk-pdf.blade.php`.
3. **Hapus command** `ImportTuTkData.php`.
4. **Hapus model** berurutan: `TuTkDokumen` → `TuTkPupuk` → `TuTkVd` → `TuTkTan` → `TuTk` → `PaymentLog` → `DocumentPositionTracking`.
5. **(Opsional, atas keputusanmu)** Buat migrasi `drop` baru untuk 7 tabel — **tidak dijalankan otomatis**.
6. **(Opsional, atas keputusanmu)** Hapus 9 file migrasi lama, atau simpan.
7. **Verifikasi akhir** (wajib): `grep` ulang `TuTk`, `tu_tk`, `tutk` di seluruh project → pastikan tidak ada referensi yatim (`use` ke kelas terhapus, pemanggilan command hilang). Laporkan hasil grep bersih sebagai bukti.

---

## 7. KEPUTUSAN YANG AKU TUNGGU DARImu

- [ ] **Q1.** Setuju hapus 9 file [AMAN-HAPUS] di §3? (model + command + view)
- [ ] **Q2.** Setuju edit bedah `DashboardPembayaranController.php` (hapus blok mati TU/TK) di §4.A?
- [ ] **Q3.** Tabel DB: (a) buat migrasi drop & kamu jalankan sendiri, atau (b) biarkan tabel apa adanya? Apakah tabel berisi data yang perlu di-backup dulu?
- [ ] **Q4.** File migrasi lama: hapus dari repo atau simpan sebagai riwayat?

> **Setelah kamu jawab/menyetujui, baru aku mulai FASE B.**

---

## 8. HASIL FASE B — EKSEKUSI (SELESAI)

Disetujui semua (Q1–Q4). Dikerjakan dengan commit per-file, pesan Bahasa Indonesia.
**Tidak ada perintah DB (`migrate`/drop) yang dijalankan** — migrasi drop hanya dibuat sebagai file.

### Yang dihapus / diubah (per commit)
| Commit | Aksi |
|---|---|
| `27573ca` | Edit bedah `DashboardPembayaranController.php`: hapus 6 `use` + 11 method blok mati TU/TK (−1239 baris). Modul pembayaran hidup utuh. |
| `63a9e20` | Hapus view `resources/views/pembayaranNEW/dokumens/export-tu-tk-pdf.blade.php`. |
| `e71c1c2` | Hapus command `app/Console/Commands/ImportTuTkData.php`. |
| `05e8d7f` | Hapus 5 model: `TuTk`, `TuTkDokumen`, `TuTkPupuk`, `TuTkVd`, `TuTkTan`. |
| `a43c105` | Hapus 2 model pendukung: `PaymentLog`, `DocumentPositionTracking`. |
| `c844743` | Tambah migrasi `2026_06_23_000000_drop_tu_tk_tables.php` (drop 7 tabel — **belum dijalankan**). |
| `11d8011` | Hapus 9 file migrasi lama TU/TK dari repo. |

**Total: 1 file diedit, 17 file dihapus, 1 file migrasi drop ditambah.**

### Hasil verifikasi (grep bersih + lint)
- `grep -rniE "tutk|tu_tk|tu-tk"` di `app/ routes/ resources/ config/ tests/` → **NIHIL**.
- `grep` `use` ke kelas terhapus (`TuTk*`, `PaymentLog`, `DocumentPositionTracking`, `ImportTuTkData`) → **NIHIL** (tidak ada import yatim).
- `grep -rniE "TU/TK|input_pupuk|input_vd|input_tan|tu-tk:import"` di kode → **NIHIL**.
- Referensi `tu_tk`/`payment_logs`/`position_tracking` di `database/` → **HANYA** di migrasi drop yang sengaja dibuat.
- `php -l DashboardPembayaranController.php` → **No syntax errors**.
- `composer dump-autoload` → **OK** (tidak ada referensi kelas hilang).

> **Aplikasi konsisten** — tidak ada referensi rusak terhadap kode TU/TK yang dihapus (dibuktikan grep bersih + lint + autoload).

### Sisa tindakan di tanganmu (DB & deploy)
1. **Tabel DB belum dibuang.** Jalankan migrasi drop **setelah backup data**:
   `php artisan migrate` (akan menjalankan `2026_06_23_000000_drop_tu_tk_tables`).
2. **Deploy:** perubahan masih di branch `codinggemini` (lokal). Belum di-push/deploy ke server — menunggu instruksimu.
3. Dokumen historis `docs_prompting/AUDIT_FINDINGS.md` & `REMEDIATION_CHECKLIST.md` masih menyebut TuTk (riwayat audit) — dibiarkan apa adanya.

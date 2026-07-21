# Audit Kesehatan Kodebase — Agenda Online PTPN

**Tanggal:** 3 Juli 2026
**Metode:** 6 agen audit paralel (Claude Opus 4.8), masing-masing membaca-mendalam satu domain dan melaporkan temuan berbukti (`file:line` + kutipan).
**Cakupan:** ~144.000 baris (≈42k PHP + ≈102k Blade), 34 controller, 25 model, 98 migrasi, 92 view, 9 file test.

---

## 0. Ringkasan Eksekutif & Keputusan

### Pertanyaan Anda: "Lebih baik buat ulang kode dari 0, atau tidak?"

> ## ❌ JANGAN buat ulang dari 0. ✅ REFAKTOR BERTAHAP (dengan test dulu).

Keputusan ini **bulat di keenam domain**. Alasannya tegas dan berbasis bukti, bukan opini:

1. **Pondasinya sehat, yang sakit adalah DUPLIKASI dan UKURAN.** Skema relasional benar (FK cascade konsisten, index kolom panas ada, `$fillable` disiplin). Middleware RBAC benar. Tidak ada SQL injection. Masalah utamanya: kode yang sama disalin 4–6 kali, dan file raksasa (view 7.000+ baris). Ini bisa **diekstrak**, bukan harus dilahirkan ulang.

2. **Tim/AI sebelumnya TERBUKTI bisa menulis kode bersih — hanya tidak konsisten.** Ada beberapa pola yang sudah benar dan tinggal disebarluaskan: `Concerns/BuildsRoleDashboard.php` (menyatukan statistik 4 role lewat config), `partials/_documentTableStickyCells.blade.php`, `Support/SafeUrl` (anti-XSS teladan), `SyncDokumenToCashBankJob` (retry/backoff benar). Artinya perbaikan = "terapkan pola bagus yang sudah ada ke sisa kode".

3. **Menulis ulang TANPA test = bunuh diri.** Cakupan test fungsional **< 5%** atas 40k baris. Dua dari enam test yang ada lahir **karena regresi yang sudah bocor ke produksi** (auto-forward gagal, import CSV gagal). Tanpa jaring pengaman, rewrite akan menghancurkan ratusan aturan bisnis yang sudah ter-enkode secara diam-diam. Rewrite butuh ~berbulan-bulan dan menghasilkan bug baru untuk hasil fungsional yang sama.

**Analogi:** rumahnya berdiri kokoh (fondasi & rangka OK), tapi banyak ruangan dibangun tempel copy-paste dan instalasi listriknya belum diuji. Kita rapikan & satukan ruangan-ruangan itu — bukan robohkan rumahnya.

### Skor Kesehatan per Domain

| # | Domain | Skor | Status |
|---|--------|:----:|--------|
| 1 | Controllers, Requests & Middleware | **38** | 🔴 Buruk (duplikasi & god-method) |
| 2 | Models & Database (Skema/Migrasi) | **56** | 🟠 Sedang (pondasi OK, skema tak matang) |
| 3 | **Views Blade & Frontend** | **22** | 🔴 **Terparah** (duplikasi ~73%, god-file) |
| 4 | Services, Observers, Events, Jobs | **68** | 🟢 Terbaik (logika kuat, edge-case rapuh) |
| 5 | Routing, Auth/RBAC & Keamanan | **58** | 🟠 Sedang (ada lubang keamanan nyata) |
| 6 | Tests, Tooling & Hygiene | **34** | 🔴 Buruk (test <5%, tanpa CI) |
| | **Rata-rata** | **≈46/100** | 🟠 **Butuh perawatan serius, bukan pemakaman** |

### Kenapa "perbaiki 1 role tak berlaku ke role lain" (mis. saga frozen-column)

Ini bukan kebetulan — ini **gejala penyakit utama**: 6 tabel dokumen per-role adalah **~73% salinan copy-paste** satu sama lain, memakai **3 teknologi tabel berbeda** (DataTables di pembayaran, Tabulator di operator, tabel biasa + sticky di sisanya). Memperbaiki satu file tidak menyentuh 5 salinan lain. **Ini kandidat #1 untuk disatukan menjadi satu komponen.**

---

## 1. Controllers, Requests & Middleware — Skor 38/100

**Verdict domain: REFAKTOR bertahap.**

- **[KRITIS] Fat controller / pelanggaran SRP.** Method raksasa: `TeamVerifikasiController::dokumens` **769 baris**, `OwnerDashboardController::rekapanKeterlambatanByRole` 669, `DashboardPerpajakanController::dokumens` 545, `DashboardPembayaranController::index` 531. `OwnerDashboardController` = 4.829 baris / 65 method. Base `Controller.php` kosong. Tidak ada layer service/repository.
- **[KRITIS] HTML dirender di dalam PHP.** 197 fragmen markup di 6 controller. `OwnerDashboardController::generateDocumentDetailHtml` bahkan diduplikasi di Owner **dan** Akutansi.
- **[KRITIS] Duplikasi antar-role (copy-paste).** `updateDokumen`, `setDeadline`, `pengembalian`, `sendToNext` hampir identik di perpajakan/akutansi/team_verifikasi/pembayaran, beda hanya string role. Idiom bersihkan-rupiah `preg_replace('/[^0-9]/'…)` disalin **~13×** dalam satu method saja.
- **[TINGGI] Otorisasi ditunda.** `StoreDokumenRequest::authorize()` & `UpdateDokumenRequest::authorize()` = `return true; // adjust as needed`. Cek objek dilakukan inline dengan 3 cara berbeda. Nol Policy/Gate.
- **[TINGGI] Sisa "botched refactor" (bukti dibangun banyak pass AI).** `DokumenController.php:1439` → `$Operatorliases = ['operator','Operator','Operator','tarapul','operator']` (identifier rusak + entri duplikat). Array degenerate `['team_verifikasi','team_verifikasi']` di 6+ tempat; `whereRaw('… IN (?, ?, ?)', ['operator','Operator','operator'])`. Ratusan baris kode ter-comment (327 di Owner, 326 di Pembayaran).
- **Positif:** tidak ada SQL injection (semua pakai binding), middleware auth solid, `BuildsRoleDashboard` membuktikan tim bisa memfaktor dengan baik.

**Top-3 aksi:** (1) ekstrak `WorkflowRoleService`/base controller untuk kuartet CRUD per-role; (2) pindah otorisasi ke Policy/FormRequest; (3) pusatkan parsing input (`CurrencyParser`) & keluarkan HTML ke Blade.

---

## 2. Models & Database — Skor 56/100

**Verdict domain: REFAKTOR + KONSOLIDASI SKEMA (squash migrasi).**

- **[KRITIS] Akar "drift skema" produksi.** `2025_11_30_120000_add_csv_import_fields`: penambahan **kolom** dijaga `if(!hasColumn)` tapi penambahan **index** (baris 65–69) TIDAK dijaga. Di MySQL tiap DDL auto-commit — bila migrasi rerun parsial, kolom di-skip tapi `CREATE INDEX` melempar "duplicate key" → migrasi gagal di tengah, kolom `imported_from_csv`/`csv_import_batch_id`/`csv_imported_at` **tak pernah masuk**. Ini persis gejala "server produksi kekurangan kolom".
- **[TINGGI] Drift kode↔DB tersebar di 9 file (22 pemakaian).** Query bisnis mengecek `Schema::hasColumn(...)` saat runtime (Dokumen, 4 dashboard controller, CsvImport, Inbox, TwoFactor). Fitur diam-diam mati di server yang kekurangan kolom, bukan error keras. **Berbahaya karena tersembunyi.**
- **[TINGGI] Skema tak pernah stabil.** 73 dari 98 migrasi adalah tambalan. Tabel `dokumens` di-ALTER **39×**. Kolom `status` diubah enum **9×** lalu menyerah jadi VARCHAR. `status_miro` ditambah & dibuang di **hari yang sama** (selisih ~2 jam). Ada migrasi duplikat identik & migrasi stub kosong yang menyesatkan.
- **[TINGGI] Bug kunci duplikat.** `User::ROLES` (`User.php:17-43`) punya kunci array berulang (`'operator'` 3×) + tabrakan case (`'Akutansi'` vs `'akutansi'`). Kolom typo warisan `nomor_mirror` MASIH dibaca runtime bersamaan dengan `nomor_miro` & `NO_MIRO_SES` → 3 sumber untuk 1 konsep.
- **[TINGGI] Seeder berbahaya.** `DokumenDummySeeder` men-`truncate` tabel produksi dengan `FOREIGN_KEY_CHECKS=0`, tanpa guard environment. `UpdateUserCredentialsSeeder` memuat kredensial produksi di VCS.
- **Positif:** FK cascade konsisten (15 tabel), index kolom panas ada, `$fillable` & `$casts` disiplin. `2026_01_26_000000_add_performance_indexes` adalah **contoh migrasi idempoten yang benar** (pakai `indexExists()`) — jadikan standar.

**Top-3 aksi:** (1) migrasi "reconcile" idempoten untuk tuntaskan drift kolom CSV, lalu buang 22 guard runtime; (2) squash 98 migrasi jadi baseline bersih; (3) amankan seeder + normalisasi `User::ROLES` & kolom MIRO.

---

## 3. Views Blade & Frontend — Skor 22/100 (TERPARAH)

**Verdict domain: EKSTRAKSI TERKONSOLIDASI — satukan 6 tabel role jadi 1 komponen. Bukan rewrite total, bukan dibiarkan.**

- **[KRITIS] Pipeline build (Vite/Tailwind) MATI TOTAL.** `@vite(...)` tidak muncul di seluruh `resources/`. `resources/css/app.css` (Tailwind) & `resources/js/app.js` adalah **dead asset** — tak pernah di-serve. UI 100% jalan dari **CDN Bootstrap + CSS inline**, dengan **1.623 `!important`** di 51 file (perang spesifisitas menimpa Bootstrap).
- **[KRITIS] Duplikasi ~73% antar 6 tabel role.** perpajakan ∩ akutansi = 1.242 baris identik (~73–77%). Blok CSS `.badge-status.badge-dikembalikan::before` didefinisikan di **5 file**. Tiap role punya `_rows.blade.php` sendiri. **Ini akar langsung keluhan "fix 1 role tak menyebar".**
- **[TINGGI] God-file & inline everything.** `layouts/app.blade.php` = **8.469 baris** (7 blok `<style>` + 23 `<script>` + 40 fungsi), termasuk logika per-role yang diduplikasi di dalam layout. View role mayoritas 50–85% CSS/JS inline (operator/daftarDokumen: 7.235 baris → 2.611 CSS + 3.728 JS inline).
- **[TINGGI] Data dummy hardcoded bocor ke 5 view produksi** (`'Rp. 241.650.650'…`, NPWP palsu, link `<a href="#">`) — sisa scaffolding AI.
- **[TINGGI] 4 CDN tanpa SRI, versi bentrok, sebagian tak dipin.** Bootstrap 5.3.0 vs 5.3.3, FontAwesome 6.4 vs 6.5, Chart.js dari 3 sumber, **Flatpickr `…/npm/flatpickr` selalu "latest"** (rawan breaking di jaringan kantor).
- **[SEDANG] 3 teknologi tabel berbeda** (DataTables/Tabulator/HTML-sticky) = 3 jalur bug terpisah.
- **[SEDANG] Nol Blade component (`<x-…>`).** Reuse hanya `@include`. Tapi ada partial yang SUDAH baik (`_documentTableStickyCells`, `_inlineEditEngine`, `_activeCellNav`, `virtual-document-table`) — pola ini tinggal diperluas.

**Top-3 aksi:** (1) hidupkan `@vite`, pindahkan CSS/JS inline ke bundle Tailwind (menyelesaikan god-file + CDN + `!important` sekaligus); (2) satukan 6 tabel role → satu `<x-document-table :role :columns :actions>`; (3) seragamkan ke SATU teknologi tabel + hapus data dummy.

---

## 4. Services, Observers, Events, Jobs & Console — Skor 68/100 (TERBAIK)

**Verdict domain: REFAKTOR TERARAH pada alur auto-forward. Services lain sehat.**

Alur "dokumen dibayar → teruskan ke Pembayaran" dibangun lewat **4 mekanisme paralel** (observer `saving` + observer `updated` + trigger MySQL + command scheduler). Over-engineered untuk kondisi normal, tapi rapuh di tepi:

- **[TINGGI] Trigger hanya `AFTER UPDATE`, buta terhadap raw INSERT.** Import CSV pakai `DB::table('dokumens')->insert()` → tidak memicu trigger DAN melewati observer. Hanya polling fallback yang menyelamatkan. **Ini sumber regresi import CSV yang pernah muncul.**
- **[TINGGI] Dokumen macet di status `processing`/`failed` tak pernah pulih otomatis.** Bila PHP mati di tengah forward, baris tetap `processing` selamanya (tak dipilih oleh query pending maupun fallback). Dead-letter tanpa pemulihan.
- **[TINGGI] `saving()` menyamakan "ada link bukti" = "sudah dibayar"** → sekadar menempel link bisa melompati verifikasi/pajak/akuntansi & di-approve sebagai `'system'`. Kehadiran link ≠ pembayaran nyata.
- **[SEDANG] Race condition** observer-vs-scheduler tanpa `lockForUpdate` → potensi entri `DokumenActivityLog` ganda. Vokabulari status tidak konsisten (`'sudah_dibayar'` 50× vs `'SUDAH DIBAYAR'` 5× vs `'SUDAH_DIBAYAR'` 5×); `ImportCsvData` memetakan ke `'SUDAH DIBAYAR'` yang **tak akan** memicu forward.
- **[TINGGI] 3 command penghapus data massal** (`data:clean --force`, `dokumen:clean-before-import`, `dokumen:clear`) tumpang tindih, tanpa guard environment.
- **Positif (banyak):** DI konsisten, `SyncDokumenToCashBankJob` retry/backoff benar, `SafeUrl` teladan keamanan, trio Virtual Assistant berlapis rapi (bukan duplikasi), logging sudah dirampingkan, tidak ada listener/mail yatim.

**Top-3 aksi:** (1) tambah trigger `AFTER INSERT` + reset baris stale `processing`/`failed`; (2) jadikan forward **single-writer** (queue-only) dengan `lockForUpdate` untuk hilangkan race; (3) `hasLink` bukan syarat lunas + pusatkan enum status; konsolidasi & amankan command penghapus data.

---

## 5. Routing, Auth/RBAC & Keamanan — Skor 58/100

**Verdict domain: REFAKTOR + PERBAIKAN KEAMANAN MENDESAK.**

Arsitektur routing/RBAC idiomatik dan ~90% rute terproteksi benar, tapi ada **lubang keamanan nyata yang aktif sekarang**:

- **[KEAMANAN TINGGI] Endpoint autocomplete TANPA autentikasi** (`web.php:417-421`): siapa pun tanpa login bisa menarik daftar penerima pembayaran, pengirim dokumen, nomor PO/PR, uraian dokumen. **Kebocoran data vendor & keuangan.**
- **[KEAMANAN TINGGI] `/pengembalian-dokumens` publik** (`web.php:422`) — tanpa `auth`.
- **[KEAMANAN TINGGI] Grup `dashboard-pembayaran` hanya `auth` tanpa role** (`web.php:529`): setiap user login (operator, bagian, dst.) bisa impor CSV pembayaran. **Privilege escalation.**
- **[KEAMANAN TINGGI] Otorisasi broadcasting dimatikan** (`channels.php:17-70`): semua channel privat `return true` → user mana pun bisa subscribe data real-time departemen mana pun. Plus mencetak `csrf_token()` ke log tiap request.
- **[SEDANG] Impor CSV disimpan ke disk publik** dengan nama tertebak (`import_<time>.csv`) → akses via `/storage/imports/...`.
- **[SEDANG] CSP `script-src 'unsafe-inline' 'unsafe-eval'`**, cookie sesi tak dipaksa `secure`, ekspor rekap keterlambatan lintas-departemen tanpa batas role.
- **Positif:** `CheckRole` solid & gagal-aman, rate-limit login + 2FA + CSRF penuh, `SecurityHeaders` global, IDOR umumnya ditutup di controller (`isPendingForRole`, cek `current_handler`), `.env` tidak tracked, upload "bukti" ternyata URL (bukan file) tervalidasi.

**Top-3 aksi (MENDESAK):** (1) bungkus rute yatim (autocomplete, pengembalian, dashboard-pembayaran import) dengan `auth`+`role`; (2) implementasi otorisasi channel sungguhan; (3) pindah impor CSV ke disk privat + kerasin config produksi.

---

## 6. Tests, Tooling & Hygiene Repo — Skor 34/100

**Verdict domain: TEST DULU, baru refaktor. Rewrite tanpa test = KRITIS tidak aman.**

- **[TINGGI] Cakupan fungsional < 5%.** 6 test bermakna untuk 40k baris `app/`. 7 controller inti (~29k baris) = **nol test**. **Alur auto-forward end-to-end, perhitungan status, agregasi dashboard, export, RBAC per-bagian — semua tanpa test.** Dua test yang ada (`OperatorCsvImportPaidDetectionTest`, `…HeaderTest`) lahir karena regresi yang sudah bocor ke produksi.
- **[TINGGI] Tanpa CI** (`.github/` tidak ada), tanpa static analysis (Larastan/PHPStan nihil). **Pint terpasang tapi mati** (tak ada `pint.json`, tak dijalankan).
- **[TINGGI] Dependency usang berbahaya:** `maatwebsite/excel v1.1.5` — rilis **era 2015 (Laravel 4/5)** berjalan di atas Laravel 12/PHP 8.3, tanpa `phpspreadsheet` modern. Bom waktu kompatibilitas & keamanan.
- **[TINGGI] Deploy tooling berisiko:** `deploy_to_server.bat` hardcode `ssh root@163.61.58.92` (login root, IP ter-ekspos). `fix_migration_server.sh` menambal state migrasi manual (`rollback --step=1` tanpa konfirmasi + insert paksa ke tabel `migrations`) — **bukti riwayat migrasi produksi pernah rusak**.
- **[SEDANG] Sampah ter-commit:** file `git` (0 byte), `dashboard-kabag.jsx` (JSX yatim di proyek Blade), `PROJECT_INTELLIGENCE_REPORT.md`/`rekapan-keterlambatan.html` (dihapus tapi belum di-commit), `nul` (untracked). README = boilerplate Laravel, `AGENTS.md` = 0 byte.
- **Positif:** `.gitignore` matang & terverifikasi tanpa kebocoran rahasia, `composer.lock` terkunci, config phpunit bersih (sqlite in-memory), dan test kecil yang ADA menyasar area rawan (RBAC, XSS).

**Top-3 aksi:** (1) **bangun jaring pengaman** — characterization test untuk auto-forward + status + import, lalu CI `composer test`; (2) ganti/audit `maatwebsite/excel`; (3) aktifkan Larastan + `pint.json` + bersihkan sampah & rahasia deploy.

---

## 7. Temuan Keamanan MENDESAK (kerjakan minggu ini, terpisah dari refaktor)

Ini bocor **sekarang** dan tidak perlu menunggu refaktor besar:

| # | Lubang | Lokasi | Dampak |
|---|--------|--------|--------|
| 1 | Autocomplete tanpa auth | `web.php:417-421` | Data vendor/keuangan bocor ke publik |
| 2 | `pengembalian-dokumens` publik | `web.php:422` | Data dokumen bocor tanpa login |
| 3 | Import CSV pembayaran tanpa role | `web.php:529` | Privilege escalation lintas-role |
| 4 | Channel broadcast `return true` | `channels.php:17-70` | Data real-time lintas-departemen |
| 5 | CSV di disk publik, nama tertebak | `DashboardPembayaranController.php:3986` | File keuangan dapat diunduh |
| 6 | Seeder truncate tanpa guard env | `DokumenDummySeeder.php:19` | Risiko wipe data produksi |

---

## 8. Peta Jalan yang Direkomendasikan (urutan penting)

Prinsip: **amankan → jaring pengaman → baru rapikan.** Jangan mulai refaktor besar sebelum ada test.

**Fase 0 — Hotfix keamanan (1–3 hari).** Tutup 6 lubang di §7. Kecil, berisiko rendah, dampak tinggi.

**Fase 1 — Jaring pengaman (1–2 minggu).**
- Characterization/golden-master test untuk 3 alur kritis: auto-forward antar-role, perhitungan status pembayaran, pipeline import CSV end-to-end.
- CI GitHub Actions menjalankan `composer test` (blokir merah) + `pint --test` + Larastan level rendah.

**Fase 2 — Konsolidasi skema (1 minggu).**
- Migrasi "reconcile" idempoten untuk tuntaskan drift kolom CSV; lalu buang 22 guard `Schema::hasColumn` runtime.
- Squash 98 migrasi jadi baseline; amankan seeder berbahaya.

**Fase 3 — De-duplikasi (bertahap, berbulan, dijaga test).**
- **View (prioritas #1):** satukan 6 tabel role → satu komponen parametrik; hidupkan `@vite`; pecah layout 8.469 baris.
- **Controller:** ekstrak `WorkflowRoleService` + Policy untuk kuartet CRUD per-role.
- **Auto-forward:** single-writer queue + trigger `AFTER INSERT` + pemulihan stale.

**Fase 4 — Hygiene berkelanjutan.** Ganti `maatwebsite/excel`, README nyata, bersihkan sampah repo, pusatkan enum status.

---

## 9. Penutup

Kodebase ini **layak diselamatkan**. Skor rata-rata 46/100 mencerminkan proyek yang **fungsional dan berpondasi benar, tetapi tumbuh terlalu cepat lewat copy-paste tanpa test** — khas dibangun iteratif oleh AI/dev yang berbeda-beda tanpa fase konsolidasi. Menulis ulang dari 0 akan membuang aset yang sudah benar (skema, RBAC, pola-pola bagus yang ada), memakan waktu berbulan-bulan, dan — karena tidak ada test — hampir pasti melahirkan regresi baru untuk hasil yang secara fungsional sama.

Jalur yang benar: **tutup lubang keamanan → pasang test pada alur kritis → konsolidasi skema → lalu satukan duplikasi secara bertahap.** Setiap langkah kecil, terukur, dan reversibel. Itu memberi Anda kodebase yang sehat **tanpa** risiko dan biaya rewrite total.

> Frozen-column yang bikin Anda kesal itu adalah versi-mikro dari seluruh masalah: satu perbaikan tak menyebar karena 6 salinan. Menyatukan tabel-tabel itu (Fase 3) menyelesaikan kelas masalah tersebut untuk selamanya — bukan hanya satu kejadian.

---

*Disusun oleh audit multi-agent (6× Claude Opus 4.8). Semua temuan dapat ditelusuri ke `file:line` pada laporan domain masing-masing di atas.*

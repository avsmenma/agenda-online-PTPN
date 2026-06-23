# CHECKLIST USULAN PERBAIKAN — Sistem Agenda Online PTPN

> Diturunkan dari `AUDIT_FINDINGS.md`. **Belum ada kode yang diubah.** Daftar ini untuk DISARING: centang yang benar-benar perlu dikerjakan.
>
> **Legenda:**
> - `⚠️ASUMSI` = perbaikan mengandaikan suatu perilaku sebagai "benar" (mis. "role X seharusnya tidak boleh Y"). **Bisa jadi ini memang fitur yang disengaja** — saring dulu.
> - Awalan **"Konfirmasi/Verifikasi"** = temuan ini di audit berlabel `[PERLU KONFIRMASI]`, belum tentu perlu diubah.
> - `(ref: ...)` = file:baris di kode untuk lacak balik.

## RINGKASAN JUMLAH POIN

| Severity | Jumlah |
|---|---|
| CRITICAL (C) | 14 |
| HIGH (H) | 44 |
| MEDIUM (M) | 74 |
| LOW (L) | 31 |
| INFO (I) | 22 |
| **TOTAL** | **185** |

> Catatan: temuan duplikat `formatTaxDocumentLink` (dilaporkan 2 modul) digabung jadi 1 poin (H35). Ber-tanda `⚠️ASUMSI`: 37 poin.
>
> **Pembaruan (eliminasi TU/TK):** 10 poin yang murni soal modul TU/TK yang sudah dihapus telah dibuang dari checklist ini — **H3, H4, H5, H6, H41, M8, M9, M12, L2, L14** (lihat `TU_TK_ELIMINATION_PLAN.md`). Nomor poin lain **sengaja tidak digeser** agar referensi silang (mis. H35/H46/H47, C6/C15) tetap stabil; karena itu ada lompatan nomor pada daftar di bawah. Poin M4, M32, L3, I10 ikut disunting untuk membuang referensi artefak TU/TK.
>
> **Pembaruan (perbaikan bug) — sudah diperbaiki & dibuang dari checklist (nomor C lain tidak digeser):**
> - **C1** — jalur approve/reject Team Verifikasi (`changeDocumentStatus`) yang menulis kolom milestone tak ada di DB dipensiunkan total (TV memang tidak approve/reject).
> - **C2** — skema referensi koneksi `cash_bank_new` disimpan di `database/schema/cash_bank_new.sql` (DDL 8 tabel dari produksi).
> - **C3** — hardening verifikasi 2FA: rate limit `throttle:5,1`, penghitung gagal per `2fa_user_id` + batalkan sesi setelah 5 gagal, window TOTP `2→1`, anti-replay via kolom baru `users.two_factor_last_used_timestep`.

---

## CRITICAL

- [ ] **C4.** Ganti channel broadcasting `documents.{department}`/`inbox.{role}`/`documents.ibuB` dari `return true` menjadi otorisasi nyata (cocokkan `$user->role` dengan channel). — (ref: channels.php:17–70)
- [ ] **C5.** Tambahkan middleware `role:` pada `/universal-approval/{dokumen}/approve|reject` agar tidak semua user login bisa approve/reject. — (ref: web.php:474–479; InboxController.php:341–555)
- [ ] **C6.** Hapus key duplikat pada `User::ROLES`/`DASHBOARD_ROUTES` dan jadikan satu set role kanonik (pisahkan display-name dari daftar role valid). — (ref: User.php:17–43)
- [ ] **C7.** Batasi `destroy()` dokumen hanya bila `current_handler==='operator'` & status ∈ {draft, returned_to_operator} & belum ada roleStatuses lanjutan; pertimbangkan SoftDeletes. — (ref: DokumenController.php:2064–2095) ⚠️ASUMSI
- [ ] **C8.** Batasi `inlineUpdate()` role `pembayaran`/`verifikasi` hanya pada dokumen yang `current_handler`-nya milik mereka, bukan dokumen apa pun. — (ref: DokumenController.php:1840–1995) ⚠️ASUMSI
- [ ] **C9.** Tambahkan cabang default (throw + rollback) atau bungkus seluruh `reject` dalam satu `DB::transaction` agar role `operator` tidak menghasilkan status setengah-tertulis. — (ref: InboxController.php:474–514)
- [ ] **C10.** Tambahkan cek `current_handler===$role` (atau `isPendingForRole`) per dokumen di `bulkForward` agar tidak bisa forward dokumen yang sudah pindah/selesai. — (ref: BulkOperationController.php:264–333) ⚠️ASUMSI
- [ ] **C11.** Konfirmasi apakah route `acceptDocument`/`rejectDocument`/`pendingApproval` (kolom legacy) masih dipakai; bila tidak hapus, bila ya ubah ke `isPendingForRole('team_verifikasi')` + sinkron `dokumen_statuses`. — (ref: TeamVerifikasiController.php:2863–3009) ⚠️ASUMSI
- [ ] **C12.** Kembalikan validasi state di `sendToPembayaran` Akutansi (status sah, `nomor_miro` wajib, tidak ada roleStatuses pending) sebelum dokumen didorong ke pembayaran. — (ref: DashboardAkutansiController.php:1523–1556) ⚠️ASUMSI
- [ ] **C13.** Ubah matching sync AO→Cash Bank menjadi `dokumen_id` saja (bukan `orWhere('no_agenda')`/`agenda_tahun`) dan pastikan `affected<=1` (alert bila >1). — (ref: DokumenSyncService.php:89–97,180–189)
- [ ] **C14.** Bungkus sync AO→CB dalam transaksi + idempotency key, dan lempar ulang exception (jangan ditelan) agar `$tries=3` job benar-benar retry. — (ref: DokumenSyncService.php:76–212)
- [ ] **C15.** Batasi validasi `role` di `storeUser`/`updateUser` programmer dengan `Rule::in([...])` role operasional saja (kecualikan owner/Admin) agar tidak bisa eskalasi hak. — (ref: ProgrammerController.php:847–961) ⚠️ASUMSI
- [ ] **C16.** Larang programmer mengubah kredensial (password/email/username) & mereset akun owner/admin; notifikasi pemilik saat password diubah. — (ref: ProgrammerController.php:891–904) ⚠️ASUMSI
- [ ] **C17.** Tambahkan cek `current_handler==='team_verifikasi'`+status (Policy `approve`) dan role middleware pada `quickApprove`/`quickReject` agar tidak bisa approve/reject dokumen apa pun by-id. — (ref: Api/DocumentPreviewController.php:75–217; web.php:76–84) ⚠️ASUMSI

---

## HIGH

- [ ] **H1.** Batasi modul CashBank hanya memakai namespace `App\Models\CashBank\*`; hindari `join()`/`has()` lintas-connection (`cash_bank` vs `cash_bank_new`). — (ref: app/Models/CashBank/Dropping.php:24–32; SubKriteria.php:21–29) ⚠️ASUMSI
- [ ] **H2.** Konsolidasikan kelas model duplikat (`KategoriKriteria`, `SubKriteria` di dua namespace) ke satu connection kanonik; deprecate yang lama. — (ref: app/Models/KategoriKriteria.php vs CashBank/KategoriKriteria.php)
- [ ] **H7.** Standardkan nama kolom `dokumen_id` (ganti `document_id` di `document_trackings`). — (ref: migrasi 2025_11_22_034621:16; DocumentTracking.php:13–32)
- [ ] **H8.** Perbaiki relasi `Bidang::dokumens()` agar pakai kolom kunci yang benar (bukan `return_source`→`kode_bidang`) dan tambah indeks `return_source`. — (ref: Bidang.php:23–26; migrasi 2026_02_14_140000:19)
- [ ] **H9.** Tegakkan klaim read-only kolom uang CashBank (override `save()/delete()`) dan agregasi pakai `SUM` DB (bukan decimal-as-string). — (ref: app/Models/CashBank/Dropping.php:15–22; Penerima.php:15–23) ⚠️ASUMSI
- [ ] **H10.** Tambah proteksi replay TOTP (simpan `two_factor_last_used_at`/last step, tolak step ≤ terakhir). — (ref: TwoFactorController.php:196–214)
- [ ] **H11.** Hapus route `GET /logout`; gunakan hanya POST + CSRF, dan perbaiki akar penyebab error 419 (session lifetime). — (ref: web.php:41–48)
- [ ] **H12.** Standarkan kanonikalisasi role (lowercase) di seluruh app; samakan strategi banding `isAdmin()`/`hasRole()` (strict) dengan `CheckRole` (case-insensitive). — (ref: User.php:156–175; CheckRole.php:44–48)
- [ ] **H13.** Persempit route `documents/{dokumen}/handler` dengan whitelist role operasional agar user `bagian_*` tidak bisa memindah handler/melompat alur. — (ref: web.php:397–401; DocumentHandlerController.php:25–143) ⚠️ASUMSI
- [ ] **H14.** Tambah otorisasi (role/kepemilikan) pada `api/documents/{dokumen}/activity*` sebelum kembalikan data, dan batasi field user yang dibocorkan. — (ref: web.php:464–471; InboxController.php:773–949)
- [ ] **H15.** Tambah `role:admin,pembayaran` pada grup `dashboard-pembayaran/*` (termasuk import & cleanup CSV). — (ref: web.php:530–536)
- [ ] **H16.** Scoping data per role/bagian di controller `tracking-dokumen` & `reports/analytics` (atau persempit middleware). — (ref: web.php:264–267,409–412; OwnerDashboardController::trackingDokumen) ⚠️ASUMSI
- [ ] **H17.** Keluarkan field state/workflow (`status`,`current_handler`,`created_by`) dari `$fillable` Dokumen & whitelist inline; set via method workflow. — (ref: Dokumen.php:40,55,56; DokumenController.php:1905–1918)
- [ ] **H18.** Tambah unique index DB pada `nomor_agenda` & generate server-side dalam transaksi + lock/retry on duplicate (1062) untuk cegah race/duplikat. — (ref: DokumenController.php:2543–2572,1188–1215)
- [ ] **H19.** Validasi transisi ketat di `update()`; tolak edit bila ada roleStatuses pending (jangan andalkan hanya `current_handler==operator`). — (ref: DokumenController.php:1426–1484) ⚠️ASUMSI
- [ ] **H20.** Terapkan state-machine terpusat dengan transisi sah (verifikasi status awal valid sebelum set `completed`/map status). — (ref: DokumenController.php:1948–1959; Dokumen.php:1016–1028,1083–1094)
- [ ] **H21.** Buat `approveInbox()` memfilter `role_code` aktor agar tidak mengambil pending pertama milik role lain. — (ref: DokumenController.php:2201–2235,1052–1067) ⚠️ASUMSI
- [ ] **H22.** Catat `sent_by_user_id` dan tolak approve bila approver = pengirim (Separation of Duties). — (ref: Dokumen.php:419–651; InboxController.php:341–408) ⚠️ASUMSI
- [ ] **H23.** Hapus method/controller reject legacy `UniversalApprovalController::reject` (kolom inbox legacy, handler salah) bila tak terpakai; satukan satu jalur reject. — (ref: UniversalApprovalController.php:112–168)
- [ ] **H24.** Ganti broadcast event `DocumentReturned`/`DocumentSent`/`DocumentActivityChanged` dari public `Channel` ke `PrivateChannel` + otorisasi. — (ref: app/Events/DocumentReturned.php:41–69; DocumentSent.php:51)
- [ ] **H25.** Tambah `lockForUpdate()` dalam transaksi (atau update bersyarat atomik + cek affectedRows) pada bulk approve agar tidak double-processing. — (ref: InboxController.php:989–1093; BulkOperationController.php:43–105)
- [ ] **H26.** Bungkus `InboxController::approve` (`approveFromRoleInbox`, 4+ tabel + event) dalam `DB::transaction`. — (ref: InboxController.php:341–408)
- [ ] **H27.** Wajibkan `link_bukti_pembayaran`/nominal & bandingkan terhadap `nilai_rupiah` sebelum set `completed`/`sudah_dibayar`; validasi `tanggal_dibayar` ≤ hari ini; simpan siapa menandai lunas. — (ref: DashboardPembayaranController.php:1802–1999) ⚠️ASUMSI
- [ ] **H28.** Konfirmasi/perbaiki RBAC `import-csv` pembayaran: tambah `role:admin,pembayaran`, simpan file di disk privat, nama file non-tebakan. — (ref: web.php:530–536; DashboardPembayaranController.php:5212–5240)
- [ ] **H29.** Catat `exportDatabase` ke ProgrammerActivityLog, pakai `--defaults-extra-file` (bukan `--password=` di argv), batasi ke owner, maskir tabel kredensial. — (ref: ProgrammerController.php:1486–1586)
- [ ] **H30.** Wajibkan backup otomatis + konfirmasi ganda/persetujuan owner + guard non-produksi sebelum `performCleanup` TRUNCATE. — (ref: ProgrammerController.php:1125–1187)
- [ ] **H31.** Validasi tanggal (format/urutan/batas) di `updateTimestamps`, catat lama→baru di audit, dan tandai dokumen yang dipercepat via bulk workflow. — (ref: ProgrammerController.php:685–781,403–563) ⚠️ASUMSI
- [ ] **H32.** Whitelist `role_code` & hilangkan efek-samping tulis (`create()`/`firstOrCreate`) pada endpoint baca `getRoleData`; validasi `doc_id`. — (ref: ProgrammerController.php:632–714)
- [ ] **H33.** Panggil `DokumenHelper::canEditDocument($dokumen,'perpajakan')` di `updateDokumen` Perpajakan (hormati lock/pending). — (ref: DashboardPerpajakanController.php:676–684) ⚠️ASUMSI
- [ ] **H34.** Perketat otorisasi `getDocumentDetail` Akutansi & Perpajakan ke dokumen di role pemanggil (`getDataForRole($role)->received_at`/`current_handler===$role`); minimalkan field. — (ref: DashboardAkutansiController.php:1271–1282; DashboardPerpajakanController.php:1178–1189) ⚠️ASUMSI
- [ ] **H35.** Gunakan `SafeUrl::external()` di SEMUA varian `formatTaxDocumentLink` (Perpajakan masih hanya `htmlspecialchars`, lolos `javascript:`/`data:`); output dirender `{!! !!}`. — (ref: DashboardPerpajakanController.php:1371–1382; cross-confirm Api/DocumentPreview & Views)
- [ ] **H36.** Hapus PO/PR hanya bila field `nomor_po`/`nomor_pr` dikirim & dimaksudkan diganti (diff, bukan delete-then-recreate semua). — (ref: DashboardPerpajakanController.php:944–1011; Akutansi 922–945)
- [ ] **H37.** Escape semua `$value` (`htmlspecialchars`) atau pakai Blade `{{ }}` di `generateDocumentDetailHtml()` god-view (saat ini `uraian_spp`/`kategori`/dll mentah). — (ref: OwnerDashboardController.php:3167–3212)
- [ ] **H38.** Satukan sumber kepemilikan Bagian (satu kolom untuk visibilitas DAN otorisasi), atau scope edit/update/destroy dengan `created_by` DAN `bagian`. — (ref: BagianDokumenController.php:63,107,473,552,781,1088,1126) ⚠️ASUMSI
- [ ] **H39.** Paksa status awal aman (`draft`) & whitelist kolom pada `Dokumen::create($row)` import agar tidak menyuntik dokumen langsung `sent_to_pembayaran`/`sudah_dibayar`. — (ref: CsvImportController.php:396,517–519; ImportCsvData.php:92–93,131) ⚠️ASUMSI
- [ ] **H40.** Pindahkan file impor `import:csv` dari `public_path` ke `storage/app`, validasi `--path` (realpath whitelist), hapus contoh `DATA 12.csv` dari `public/`. — (ref: ImportCsvData.php:32)
- [ ] **H42.** Netralisasi formula/CSV injection saat import/re-export (prefiks `'` atau strip leading `= + - @`). — (ref: CsvImportController.php:440–529; OperatorCsvImportController.php:554–637)
- [ ] **H43.** Ubah `parseAndPreviewCsv` ke streaming convert + hapus file upload setelah proses; hindari `count(file())` & timpa file sumber (memory DoS). — (ref: CsvImportController.php:107–137; OperatorCsvImportController.php:90–131)
- [ ] **H44.** Tambah `throttle` (mis. 20/menit/user) + circuit-breaker/limit harian pada endpoint chat Asisten Virtual. — (ref: web.php:177–179,237–239; OwnerVirtualAssistantController.php:66–117)
- [ ] **H45.** Tambah Policy/scoping role pada `getPreviewData` (saat ini `findOrFail` tanpa cek) agar detail dokumen tak bocor ke semua user. — (ref: Api/DocumentPreviewController.php:21–66; web.php:79) ⚠️ASUMSI
- [ ] **H46.** Kirim `link_*_safe` (SafeUrl) dari backend / paksa skema `https://` + escape teks anchor pada modal detail Pembayaran (`innerHTML`). — (ref: pembayaran/dokumens/daftarPembayaran.blade.php:1641–1661)
- [ ] **H47.** Validasi skema URL sebelum `href` di `linkFormatter` Operator & link Asisten Virtual (`escapeHtml` tak blok `javascript:`). — (ref: operator/dokumens/daftarDokumen.blade.php:5916–5920; owner/asisten-virtual.blade.php:607)
- [ ] **H48.** Buat cek cooldown + insert log notifikasi WA atomik (`lockForUpdate`/transaksi) atau unique index (`dokumen_id`+role+type+window) agar tak kirim dobel. — (ref: LateDocumentNotificationService.php:138–177,342–362)
- [ ] **H49.** Samakan timezone (Asia/Jakarta) atau normalisasi UTC pada klasifikasi keterlambatan; pakai `diffInHours($now,false)` (bertanda); tolak `received_at` masa depan. — (ref: LateDocumentNotificationService.php:235–245; config/app.php:68)

---

## MEDIUM

- [ ] **M1.** Bersihkan duplikasi index `idx_nomor_agenda`/`idx_nomor_spp`/`status_pembayaran` di banyak migrasi (satu index per kolom). — (ref: migrasi 2025_11_16_180851:23–24; 2026_01_26_000000:31–40)
- [ ] **M2.** Pilih satu index komposit antara `(current_handler,status)` vs `(status,current_handler)`; hapus yang redundan. — (ref: migrasi 2025_11_16_180851; 2026_01_26_000000)
- [ ] **M3.** Ubah kolom `details`/`metadata` dari TEXT ke `json()` (MySQL 8) agar konsisten & bisa `whereJsonContains`. — (ref: migrasi 2025_11_24_075859:21; 2025_11_22_034621:19)
- [ ] **M4.** Konsolidasikan dua audit-log yang konsep tumpang tindih (`document_trackings` & `dokumen_activity_logs`); dokumentasikan peran tiap tabel tracking. — (ref: DocumentTracking.php, DokumenActivityLog.php, DocumentActivity.php)
- [ ] **M5.** Tambah FK `whatsapp_notification_logs.role_code` → `roles.code`. — (ref: migrasi 2026_01_24_100001:18,29)
- [ ] **M6.** Tambah FK `nullOnDelete` + relasi `belongsTo(Dokumen)` pada `sync_logs.dokumen_id`. — (ref: migrasi 2026_03_05_000001:17; SyncLog.php)
- [ ] **M7.** Tambah indeks `dokumens.return_source` (dipakai relasi & filter routing). — (ref: migrasi 2026_02_14_140000:19)
- [ ] **M10.** Pertimbangkan FK/relasi atau validasi referensial `dokumens.bagian` (string) → `bagians.kode`. — (ref: Bagian.php; migrasi 2026_01_07_100000)
- [ ] **M11.** Set `CASH_BANK_NEW_DB_DATABASE` eksplisit per env agar `cash_bank` & `cash_bank_new` tak menunjuk DB fisik sama akibat fallback. — (ref: config/database.php:86–105 (92))
- [ ] **M13.** Keluarkan `role`+kolom 2FA dari `$fillable` User (set eksplisit) & hindari pola `fill($request->all())`. — (ref: User.php:80–94; ProfileController.php:64) ⚠️ASUMSI
- [ ] **M14.** Cek peran di `authorize()` FormRequest reset 2FA & tolak bila `requester_id === approver_id` (self-approval). — (ref: TwoFactorResetController.php:35–121; ApproveTwoFactorResetRequest.php:12–14) ⚠️ASUMSI
- [ ] **M15.** Re-encode gambar saat upload foto profil, set `X-Content-Type-Options: nosniff`, blok SVG (risiko polyglot). — (ref: ProfileController.php:92; User.php:185–192)
- [ ] **M16.** Minta konfirmasi password/TOTP sebelum `regenerateRecoveryCodes()` menampilkan recovery codes baru. — (ref: TwoFactorController.php:334–357)
- [ ] **M17.** Perkuat kebijakan password (`Password::min(8)->mixedCase()->numbers()->uncompromised()`); hapus `min:8` dari validasi login. — (ref: ProfileController.php:177; LoginRequest.php:34)
- [ ] **M18.** Migrasikan CSP ke nonce/hash, hilangkan `unsafe-inline`/`unsafe-eval`; tambah COOP/CORP. — (ref: SecurityHeaders.php:28–38)
- [ ] **M19.** Ubah default `match()` rekapan-keterlambatan dari `team_verifikasi` menjadi `abort(403)`/dashboard role sendiri. — (ref: web.php:278–295) ⚠️ASUMSI
- [ ] **M20.** Verifikasi otorisasi export `rekapan-keterlambatan/{roleCode}` non-owner (idealnya middleware/Policy, bukan hanya cek internal). — (ref: web.php:273–306) ⚠️ASUMSI
- [ ] **M21.** Tambahkan Policy/`authorize()` per-dokumen (scope bagian) pada `inline-update`/`handler`/`inline-create` (jangan hanya guard controller). — (ref: web.php:390–401,376–377) ⚠️ASUMSI
- [ ] **M22.** Aktifkan SoftDeletes & arsipkan dokumen (jangan hard-delete berantai tanpa jejak). — (ref: DokumenController.php:2064–2095; Dokumen.php:11–14)
- [ ] **M23.** Sanitasi URL terpusat via model mutator untuk `link`/`link_dokumen_pajak` + pastikan view selalu `{{ }}` (SafeUrl kini hanya di inlineUpdate). — (ref: DokumenController.php:1960–1964,2031–2042; Dokumen.php:68–69)
- [ ] **M24.** Update sync Cash Bank by primary key/`dokumen_id` saja (bukan `no_agenda`), dalam transaksi/outbox retry, catat ketidaksesuaian. — (ref: DokumenController.php:1731–1807,2004–2017)
- [ ] **M25.** Samakan nama bulan Indonesia di `update()` (`Mei`/`Juli`, bukan `May`/`July`) — ekstrak ke satu konstanta/helper. — (ref: DokumenController.php:1528–1541)
- [ ] **M26.** Tambah `validate()` per-field tanggal/nominal di `inlineUpdate` (date_format, numeric/max); tolak invalid 422. — (ref: DokumenController.php:1527,1937,1957,2026–2028)
- [ ] **M27.** Hitung tiap bucket statistik nilai dari query saling-eksklusif (jangan `total - sudah - siap`); assertion bila hasil <0. — (ref: DashboardPembayaranController.php:178–223)
- [ ] **M28.** Normalisasi tipe kolom uang CashBank & validasi/log baris non-numerik pada perhitungan saldo (kini di-CAST dari string → 0 diam-diam). — (ref: CashBankReportService.php:34–82,215–265)
- [ ] **M29.** Pindahkan penulisan `deadline_at` ke titik transisi status (event/observer), bukan di method baca daftar (GET). — (ref: DashboardPembayaranController.php:1262–1277)
- [ ] **M30.** Pindahkan perhitungan `computed_status` ke SQL (CASE/where) agar paginate/aggregate di DB; pakai `chunk()`/cursor untuk export (hindari muat semua ke memori). — (ref: DashboardPembayaranController.php:426–455,3454–3520,1231–1296)
- [ ] **M31.** Pertahankan whitelist `orderByRaw $sortOrder` + guard agar tak pernah terima nilai non-whitelist. — (ref: DashboardPembayaranController.php:1219–1252)
- [ ] **M32.** Whitelist tabel pada nama tabel hasil interpolasi `DB::statement`/`DB::table` (identifier injection) terhadap `Schema::getAllTables()`; quote identifier. — (ref: CleanAllData.php:88,90)
- [ ] **M33.** Samakan validasi tipe file impor Operator dengan parser (csv,txt) atau tambah reader xlsx; validasi `mimetypes`/magic byte. — (ref: OperatorCsvImportController.php:33,99–138; CsvImportController.php:26)
- [ ] **M34.** Normalisasi panjang baris (`pad/truncate`) sebelum `array_combine($headers,$data)`; skip + catat baris malformed. — (ref: CsvImportController.php:212,344; OperatorCsvImportController.php:285,441)
- [ ] **M35.** Satukan natural key duplicate-detection + unique index DB + `upsert()`/`insertOrIgnore` (cegah double-import TOCTOU). — (ref: CsvImportController.php:269,357; ImportCsvData.php:68,126)
- [ ] **M36.** Pakai null-safe `?->` untuk `auth()->user()->name` di konteks CLI audit log; catat sumber CLI eksplisit. — (ref: CleanDokumenBeforeImport.php:118)
- [ ] **M37.** Keluarkan `Schema::hasColumn` dari loop, preload existing keys, bulk insert + chunk + transaksi per-chunk pada import (kurangi N+1 & lock panjang). — (ref: CsvImportController.php:269,357,386–396; ImportCsvData.php:126,131,180)
- [ ] **M38.** Selaraskan `SYNCABLE_FIELDS` dengan `FIELD_MAP` untuk `link_bukti_pembayaran` (kini sync diam-diam tak lengkap) atau dokumentasikan read-only. — (ref: DokumenSyncService.php:45–70)
- [ ] **M39.** Konfirmasi kebutuhan, lalu terapkan row-level filter di `applyAssistantScope()` (kini no-op → role pembayaran lihat seluruh dataset). — (ref: VirtualAssistantQueryService.php:1074–1080) ⚠️ASUMSI
- [ ] **M40.** Verifikasi/terapkan row-level scope di `findSpecificDocument()` agar `context.selected_document.id` arbitrer tak bisa ambil detail dokumen apa pun (IDOR via konteks). — (ref: OwnerVirtualAssistantController.php:77–81; VirtualAssistantQueryService.php:247–264) ⚠️ASUMSI
- [ ] **M41.** Kurangi verbositas log produksi (pertanyaan & `params` mentah user); batasi retensi/akses tabel interaksi. — (ref: VirtualAssistantQueryService.php:1712–1722; VirtualAssistantService.php:35–44)
- [ ] **M42.** Verifikasi/ganti write-path link Perpajakan store dari `SafeUrl::external()` (inert/mangle) ke `SafeUrl::sanitizeForStorage()` (reject) agar konsisten. — (ref: DashboardPerpajakanController.php:910–911)
- [ ] **M43.** Validasi `tahun` (array integer digits:4) + tambah Validator untuk semua filter (status/bagian/nilai/per_page) pada `search`. — (ref: Api/AdvancedSearchController.php:44–47)
- [ ] **M44.** Clamp `per_page` pada `search` (`min((int)per_page,100)`; validasi integer positif). — (ref: AdvancedSearchController.php:102–103)
- [ ] **M45.** Verifikasi/ganti `$e->getMessage()` mentah ke klien dengan pesan generik (detail hanya di log) pada AdvancedSearch & DocumentPreview. — (ref: AdvancedSearchController.php:120,178,206,248,273,299; DocumentPreviewController.php:63,141,214)
- [ ] **M46.** Verifikasi sumber `actionUrl`/`doc.url`, lalu escape+validasi skema untuk render link `innerHTML`/`onclick` di inbox & notifikasi global (gunakan event listener+dataset). — (ref: inbox/index.blade.php:2247,3033; layouts/app.blade.php:6478)
- [ ] **M47.** Escape tiap sel/header (`textContent`/escapeHtml) pada preview import CSV (`innerHTML` dari sel unggahan). — (ref: bagian/dokumens/importCsv.blade.php:514–518; pembayaranNEW/import/index.blade.php:508–513)
- [ ] **M48.** Tambah guard `current_handler==='akutansi'` eksplisit pada `setDeadline` Akutansi. — (ref: DashboardAkutansiController.php:1006–1046) ⚠️ASUMSI
- [ ] **M49.** Implementasikan `destroyDokumen`/`storeDokumen` Akutansi (kini no-op tapi flash "berhasil") dengan otorisasi+transaksi, atau hapus route. — (ref: DashboardAkutansiController.php:662–666,997–1001; web.php:555)
- [ ] **M50.** Agregasi statistik delay via DB (CASE/SUM)/cache (kini muat seluruh dokumen tiap render). — (ref: DashboardAkutansiController.php:516–589; DashboardPerpajakanController.php:457–523)
- [ ] **M51.** Gabungkan `getSearchSuggestions` (12+ query distinct per-field) jadi satu query / cache. — (ref: DashboardAkutansiController.php:1637–1705; DashboardPerpajakanController.php:1899–1968)
- [ ] **M52.** Validasi kelengkapan & lock pada `sendToNext` Perpajakan (cegah lompat ke pembayaran); dokumentasikan bila lompat akutansi memang diizinkan. — (ref: DashboardPerpajakanController.php:1609–1622) ⚠️ASUMSI
- [ ] **M53.** Sesuaikan aturan validasi `updateDokumen` Akutansi dengan bisnis; jangan tutupi input kosong dengan merge nilai lama. — (ref: DashboardAkutansiController.php:784–809)
- [ ] **M54.** Validasi `last_checked` dari klien & samakan baseline `checkUpdates` antar role; minimalkan field respons. — (ref: DashboardAkutansiController.php:41–125; DashboardPerpajakanController.php:1710–1786)
- [ ] **M55.** Bedakan error fatal vs boleh-diabaikan pada try/catch yang menelan exception lalu lanjut data kosong (resolve kriteria). — (ref: DashboardAkutansiController.php:593–600,867–881; DashboardPerpajakanController.php:527–533,786–801)
- [ ] **M56.** Validasi state (`{id}` integer + tolak `completed`/`sudah_dibayar`) pada `sendUrgency`/`resetUrgency`/`getHistory`/`sendPriorityWhatsApp`. — (ref: OwnerDashboardController.php:4330,4375,4445,4646) ⚠️ASUMSI
- [ ] **M57.** Tambah `throttle` + queue async + dedup (WhatsAppNotificationLog) pada `sendPriorityWhatsApp` (kini loop sinkron 10s/user). — (ref: OwnerDashboardController.php:4445–4556; web.php:341–343)
- [ ] **M58.** Validasi seluruh field Bagian store/update (`kebun` whitelist, `kriteria_cf` exists) & pakai `$validated` untuk payload + sync; verifikasi `id_kategori_*` sebelum tulis ke `bank_keluars`. — (ref: BagianDokumenController.php:388–413,622–642,719–731)
- [ ] **M59.** Agregasi umur via SQL + eager-load roleData pada `index`/`tracking`/`rekapan` Owner (hindari `->get()` seluruh tabel + N+1). — (ref: OwnerDashboardController.php:179–195,2432–2494)
- [ ] **M60.** Pakai selisih detik bertanda untuk klasifikasi waktu & definisikan perlakuan `received_at` null (jangan otomatis "terlambat"). — (ref: OwnerDashboardController.php:2458–2467; BuildsRoleDashboard.php:167–183; AnalyticsController.php:129–145)
- [ ] **M61.** Tambah cek `current_handler===$role`/`isPendingForRole` di `bulkForward` & standarisasi ejaan `akutansi` (key map duplikat → dokumen "hilang"). — (ref: BulkOperationController.php:222–306)
- [ ] **M62.** Hanya pakai `$user->role` resmi untuk otorisasi; hapus fallback `getUserRole()` berbasis `name` ('Ibu B'/'Sekar'). — (ref: InboxController.php:560–668; UniversalApprovalController.php:217–252) ⚠️ASUMSI
- [ ] **M63.** Pastikan `changeDocumentStatus` menghasilkan status enum kanonik & sinkron `dokumen_statuses` (kini tulis `approved_Team Verifikasi` berspasi → dead-state), atau pensiunkan method. — (ref: TeamVerifikasiController.php:2741–2858)
- [ ] **M64.** Bungkus `/pengembalian-dokumens` dengan `role:operator,admin` & batasi query berdasarkan kepemilikan (kini semua user lihat semua returned + statistik global). — (ref: PengembalianDokumenController.php:10–64; web.php:422) ⚠️ASUMSI
- [ ] **M65.** Validasi sumber pemicu `AutoForwardDokumenService` (signature/whitelist) + audit kuat + flag "approved upstream" (kini auto-approve semua tahap atas kolom DB eksternal). — (ref: AutoForwardDokumenService.php:44–149,194–276) ⚠️ASUMSI
- [ ] **M66.** Verifikasi akses status pakai koleksi `roleStatuses` ter-load (firstWhere) di `UniversalApprovalController::index` & inbox listing (N+1). — (ref: UniversalApprovalController.php:29–38; InboxController.php:184–199,720–727)
- [ ] **M67.** Larang `resetUserTwoFactor`/approve 2FA untuk akun owner/admin oleh programmer. — (ref: ProgrammerController.php:1013–1084; TwoFactorResetController.php:35–80) ⚠️ASUMSI
- [ ] **M68.** Larang `destroyUser` menghapus akun owner/admin & owner terakhir; pakai soft-delete. — (ref: ProgrammerController.php:966–1008) ⚠️ASUMSI
- [ ] **M69.** Terapkan validasi tanggal ketat (sama seperti preview, cek `getLastErrors()`, tolak masa depan) di `executeBulkSetDatePayment`. — (ref: ProgrammerController.php:1324–1410)
- [ ] **M70.** Escape `%`/`_` pada LIKE + whitelist role/handler + batasi panjang search pada `searchDocuments`/`activityLogs`. — (ref: ProgrammerController.php:602–627,1435–1477)
- [ ] **M71.** Prefiks `'` untuk sel berkarakter formula / aktifkan escaping maatwebsite pada ekspor Excel rekapan keterlambatan. — (ref: RekapanKeterlambatanExport.php:115–126)
- [ ] **M72.** Pakai cache tags (redis) atau kelola daftar key; ganti `Cache::forget('user_count_*')` wildcard yang tak menghapus apa pun + invalidasi cache per-user. — (ref: CacheService.php:84–98,115,188)
- [ ] **M73.** Validasi nomor telepon E.164 (10–15 digit) sebelum kirim WhatsApp; tolak+log invalid (cegah kirim ke nomor salah). — (ref: FonnteWhatsAppService.php:139–155,28–60)
- [ ] **M74.** Ganti bulk WA `sleep()` blocking dengan queue job + delay antar job + retry/backoff; tangani 429. — (ref: FonnteWhatsAppService.php:106–133)
- [ ] **M75.** Tambah `->connectTimeout(5)->retry(2,200)` pada outbound HTTP Fonnte; klasifikasi error transien vs permanen. — (ref: FonnteWhatsAppService.php:53–60)
- [ ] **M76.** Ringkas output command notifikasi (jangan cetak full stack trace ke output/log); trace via `Log::error` terstruktur. — (ref: SendLateDocumentNotifications.php:72–76; routes/console.php:27)
- [ ] **M77.** Verifikasi/perbaiki polling auto-forward dengan update bersyarat atomik (`->where('status','pending')`) sebagai lock + penanda idempoten dalam transaksi (cegah reproses/race). — (ref: ProcessAutoForwardQueue.php:68–91,126–127)

---

## LOW

- [ ] **L1.** Konsolidasi ALTER enum `status` dokumens (enum→varchar berulang, migrasi enum-only di-skip di SQLite) & validasi status di level aplikasi. — (ref: migrasi 2025_11_14_041402 → 2026_01_08_110000)
- [ ] **L3.** Tambah `useCurrent()` pada kolom NOT NULL tanpa default (`status_changed_at`). — (ref: migrasi 2025_12_15_100001:26)
- [ ] **L4.** Whitelist field model lama cash_bank (FK di `$fillable`) & batasi atribut yang disalin `JenisPembayaran::fromStdClass` (`setRawAttributes`). — (ref: KategoriKriteria.php, SubKriteria.php:15–18; JenisPembayaran.php:16–18,39–49)
- [ ] **L5.** Tambah index `DokumenActivityLog` (performed_by/action) bila ada filter; pertimbangkan unique `(dokumen_id, nomor_po)`. — (ref: migrasi 2025_11_24_075859:25–26; 2025_11_14_041403)
- [ ] **L6.** Kurangi field PII di log (cukup `user_id`); jangan log email lama/baru & error mentah. — (ref: ProfileController.php:67–71,159–164,240–244; LoginController.php:46–80)
- [ ] **L7.** Pertahankan entropi penuh recovery code (hapus `strtoupper(Str::random(10))`) + rate-limit verifikasi recovery. — (ref: TwoFactorController.php:110–112,343–345)
- [ ] **L8.** Hapus logging CSRF token & data sesi di `channels.php` (turunkan ke debug-only). — (ref: channels.php:19–27,44–52)
- [ ] **L9.** Verifikasi controller `check-updates`/`welcome-message` men-scope ke role pemanggil; idealnya tambah role middleware. — (ref: web.php:135–150,535) ⚠️ASUMSI
- [ ] **L10.** Pakai queue retry/alert untuk side-effect kritis pada try/catch yang menelan exception (tandai status "sync_pending"). — (ref: DokumenController.php:229–236,1800–1806,1998–2002; DokumenObserver.php:91–100)
- [ ] **L11.** Eager-load semua relasi accessor & optimalkan/cache suggestion pada `index`/`getAllData`/`getSearchSuggestions`. — (ref: DokumenController.php:411–413,2474–2536)
- [ ] **L12.** Prefiks sel berkarakter formula (`= + - @`) dengan `'` sebelum `fputcsv` pada CSV export Pembayaran. — (ref: DashboardPembayaranController.php:2737–2804,4436–4444)
- [ ] **L13.** Simpan file impor CSV Pembayaran di disk privat (`local`), nama acak, hapus setelah proses (kini disk public + nama `time()`). — (ref: DashboardPembayaranController.php:5220–5227)
- [ ] **L15.** Log exception CashBankReportService & bedakan "0 sah" vs "gagal memuat" di UI (kini saldo 0 tanpa log). — (ref: CashBankReportService.php:56–58,79–81,109–111,181–183,256–264)
- [ ] **L16.** Verifikasi audit trail programmer benar-benar immutable (tabel append-only, revoke UPDATE/DELETE) & tambah `logActivity` untuk `exportDatabase`. — (ref: ProgrammerActivityLog.php:10–45; LogsProgrammerActivity.php:33–48; ProgrammerController.php:1535–1540)
- [ ] **L17.** Ganti `$e->getMessage()` mentah ke pengguna dengan pesan generik (detail hanya di log) pada Akutansi/Perpajakan. — (ref: DashboardAkutansiController.php:993,1161,1808; DashboardPerpajakanController.php:1592)
- [ ] **L18.** Agregasi total `nilai_rupiah` via `SUM` DB (decimal/integer), bukan `preg_replace`+float per-baris. — (ref: DashboardPerpajakanController.php:476–477)
- [ ] **L19.** Beri umpan balik saat filter diabaikan & hindari catch kosong pada sync/dropdown Bagian/Owner. — (ref: BagianDokumenController.php:282–287,514–519,597–620; OwnerDashboardController.php:843–885)
- [ ] **L20.** Normalisasi `nilai_rupiah` sebelum validasi (selaraskan `str_replace` dengan aturan validasi); uji round-trip. — (ref: BagianDokumenController.php:396,629)
- [ ] **L21.** Cap maksimum efektif `per_page` (mis. 1000)/cursor untuk ekspor (kini `per_page=all`→999999 dengan eager-load). — (ref: BagianDokumenController.php:166,1066; OwnerDashboardController.php:28–33,310–315)
- [ ] **L22.** Tambah `ConfirmableTrait`/cek `app()->environment('production')` + backup sebelum TRUNCATE/DELETE pada command destruktif CLI. — (ref: CleanAllData.php:16,47–62; ClearDokumenCommand.php:21,35; CleanDokumenBeforeImport.php:19–20)
- [ ] **L23.** Validasi prefix `csv_imports/` & tolak `..` pada `file_path` preview/import; gunakan UUID + disk privat (cegah path traversal). — (ref: CsvImportController.php:33–48; OperatorCsvImportController.php:40–52)
- [ ] **L24.** Satukan tiga implementasi `cleanNumeric`/`parseCurrency` jadi satu parser integer/decimal + validasi rentang (cegah korup format ambigu). — (ref: CsvImportController.php:600–636; OperatorCsvImportController.php:748–765; ImportCsvData.php:240–249)
- [ ] **L25.** Scrub/batasi body respons provider AI (≤500 char) yang dicatat ke log (cukup status + ringkasan). — (ref: VirtualAssistantAiProvider.php:45–63,102–122)
- [ ] **L26.** Verifikasi & whitelist key `expected_params` pada `storeTestCase` (kini terima JSON arbitrer). — (ref: ProgrammerAssistantEvaluationController.php:78–110)
- [ ] **L27.** Whitelist key `filters` JSON & keluarkan `usage_count`/`last_used_at` dari `$fillable` SearchPreset. — (ref: SearchPreset.php:13–25; AdvancedSearchController.php:214–251)
- [ ] **L28.** Jangan log request/response Fonnte mentah; redaksi token eksplisit pada catch. — (ref: FonnteWhatsAppService.php:54–55,89–99)
- [ ] **L29.** Minimalkan data dokumen sensitif & batasi panjang pesan notifikasi ke kanal eksternal Fonnte. — (ref: LateDocumentNotificationService.php:311–337)
- [ ] **L30.** Strip CR/LF pada subject email (`nomor_agenda`) & validasi panjang field di hulu (header injection minor). — (ref: LateDocumentNotificationService.php:416; DeadlineReminderMail.php:27)
- [ ] **L31.** Verifikasi disk penyimpanan lampiran SPP; bila di disk `public`, pindah ke `local` & sajikan via controller berotorisasi. — (ref: config/filesystems.php:41–48,76–78)
- [ ] **L32.** Buat checklist deploy: `APP_DEBUG=false`, `APP_ENV=production`, `SESSION_SECURE_COOKIE=true` (jangan salin nilai `.env.example`). — (ref: config/app.php:29,42; .env.example:9–11)
- [ ] **L33.** Verifikasi `SetDeadlineRequest` memaksa `integer|min:1` + guard di model (cegah deadline di masa lalu). — (ref: TeamVerifikasiController.php:1937–2031; RoleDeadlineConfig.php:26–33)

---

## INFO

> Sebagian besar INFO adalah catatan kualitas/konsistensi atau **verifikasi positif** (tak perlu diubah). Disertakan agar tidak ada temuan yang hilang.

- [ ] **I1.** Normalisasi casing `users.role`; pertimbangkan FK ke `roles.code`. — (ref: Role.php:42–45; migrasi 2026_01_24_000001)
- [ ] **I2.** Dokumentasikan pemetaan eksplisit / enum-kan `display_status` (tiga vokabulari status paralel tanpa constraint sinkronisasi). — (ref: migrasi 2025_12_15_100001:17–25; 2026_01_14_180000:29)
- [ ] **I3.** Verifikasi `2fa_user_id` hanya ditulis LoginController & beri TTL/expiry sesi 2FA. — (ref: web.php:30–38; TwoFactorController.php:151–225)
- [ ] **I4.** Standarkan satu kode role kanonik `verifikasi` vs `team_verifikasi`; alias normalisasi terpusat di `CheckRole`. — (ref: web.php:777 dll)
- [ ] **I5.** Verifikasi `bulkForward` membatasi tujuan per role (dua jalur forward team_verifikasi). — (ref: web.php:777–794)
- [ ] **I6.** Sentralisasi normalisasi role & hapus key array duplikat ('operator'/'Operator'/'tarapul'). — (ref: DokumenController.php:1439,2109,2374; Dokumen.php:220–229)
- [ ] **I7.** Pastikan `current_handler` selalu cerminkan pemegang aktual; pakai roleStatuses pending sebagai sumber kebenaran guard. — (ref: Dokumen.php:1009–1012,941–1043)
- [ ] **I8.** Sentralisasi normalisasi role ke satu helper & hapus key duplikat pada array mapping (sumber bug senyap). — (ref: InboxController.php:28,66–73,571–630; BulkOperationController.php:300–305)
- [ ] **I9.** Tambah `lockForUpdate` sebelum cek `hasPendingApproval` pada reassignment `DocumentHandlerController` (stale handler). — (ref: DocumentHandlerController.php:59–143,301–318)
- [ ] **I10.** Tandai/hapus method tulis-uang dead code (tanpa route); bila diaktifkan kembali, terapkan otorisasi per-dokumen + validasi nominal. — (ref: DashboardPembayaranController.php:1517,1620,2072,1887)
- [ ] **I11.** Pertimbangkan otorisasi per-dokumen (SoD) untuk pembayaran + audit trail andal di kolom non-nullable. — (ref: DashboardPembayaranController.php:1805,1890,2007,5034; web.php:503–511) ⚠️ASUMSI
- [ ] **I12.** Bersihkan duplikasi `User::ROLES` & sediakan satu daftar role kanonik untuk whitelist validasi (dukung C6/C15). — (ref: CheckRole.php:44–48; User.php:17–43)
- [ ] **I13.** Pastikan setiap endpoint mutasi/detail Akutansi/Perpajakan punya validasi otorisasi server-side (visibilitas listing lintas-role disengaja). — (ref: DashboardAkutansiController.php:127–137; DashboardPerpajakanController.php:39–50)
- [ ] **I14.** Tambah cek idempotensi (tolak bila sudah ada roleStatus pending target)/kunci optimistik pada `returnDocument`/`sendToPembayaran` (double-submit). — (ref: DashboardAkutansiController.php:1711–1763; DashboardPerpajakanController.php:1494–1547)
- [ ] **I15.** Pertimbangkan global scope pada model Dokumen untuk user bagian (`CheckBagianRole` tak mengikat kode bagian). — (ref: CheckBagianRole.php:24–83) ⚠️ASUMSI
- [ ] **I16.** Tangani kasus `false` pada deteksi encoding (`mb_convert_encoding(...,false)`); jangan timpa file sumber. — (ref: CsvImportController.php:108–113; OperatorCsvImportController.php:91–96)
- [ ] **I17.** Ganti `$e->getMessage()` mentah di response JSON import dengan pesan generik (detail di log). — (ref: CsvImportController.php:92–94,311–313; OperatorCsvImportController.php:78–80,393–396)
- [ ] **I18.** (Positif) Pertahankan pola Asisten Virtual yang sudah aman: binding parameter (tidak ada SQLi), output di-escape, feedback IDOR dijaga, API key dari env. — (ref: VirtualAssistantQueryService.php; config/asisten_virtual.php:17–29)
- [ ] **I19.** Pertimbangkan scoping autocomplete per-role (kini kembalikan semua nama lintas dokumen); preset use/delete sudah benar di-scope. — (ref: AutocompleteController.php:26–133; AdvancedSearchController.php:256–302) ⚠️ASUMSI
- [ ] **I20.** (Positif) Terapkan pola `SafeUrl`+`{{ }}` yang sudah konsisten di partial row baru ke sink JS lama yang masih rentan (H35/H46/H47). — (ref: akutansi/perpajakan/team_verifikasi `_rows`; operator `_tableRowsAjax`)
- [ ] **I21.** Pindah ke HTTPS + `SESSION_SECURE_COOKIE=true` (kini cookie sesi bisa via HTTP). — (ref: config/session.php:172,185,202; AppServiceProvider.php:30–33)
- [ ] **I22.** (Positif) Pertahankan penanganan rahasia (`.env` gitignore, `.env.example` placeholder kosong); tambah secret scanning di CI. — (ref: .gitignore:6–8; .env.example)

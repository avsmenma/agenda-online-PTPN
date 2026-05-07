# PROJECT INTELLIGENCE REPORT — Agenda Online PTPN
*Dibuat oleh AI Analyst untuk keperluan rebuild project*

> Metode analisis: static analysis menggunakan perintah shell read-only terhadap route, model, controller, middleware, migration, seeder, config, service, notification, event, helper, JS, dan struktur Blade. Tidak ada migrasi, server, artisan command, test, atau command destruktif yang dijalankan.

## 1. RINGKASAN EKSEKUTIF

Agenda Online PTPN adalah aplikasi workflow dokumen pembayaran/SPP internal. Aplikasi ini mencatat dokumen dari unit/bagian, meneruskan dokumen melalui verifikasi, perpajakan, akuntansi, dan pembayaran, lalu memberi monitoring keterlambatan, audit trail, notifikasi real-time, serta pelaporan status pembayaran.

Pengguna utamanya adalah:

- Bagian/departemen pengaju dokumen: AKN, DPM, KPL, PMO, SDM, SKH, TAN, TEP, dan data master PTI.
- Operator: pintu masuk/pengelola awal dokumen agenda.
- Team Verifikasi: pemeriksa, penerima/reject inbox, paraf, pengatur deadline, dan pengarah ke role berikutnya.
- Perpajakan: melengkapi/validasi data pajak.
- Akutansi/Akuntansi: melengkapi data MIRO dan data akuntansi.
- Pembayaran: mengelola status siap dibayar/sudah dibayar, bukti pembayaran, CSV import, dan rekapan pembayaran.
- Owner/Admin: monitoring lintas role, tracking workflow, rekapan keterlambatan, urgency alert, cash bank report, dan log.
- Programmer: alat operasional khusus untuk bulk repair, user management, database tool, reset 2FA, dan audit trail programmer.

Masalah bisnis yang diselesaikan: dokumen pembayaran yang sebelumnya rawan tersendat antar unit dapat dilacak posisinya, batas waktunya, status approval-nya, dan status pembayarannya. Aplikasi juga menyediakan jalur pengembalian dokumen, notifikasi keterlambatan, serta rekapan manajemen untuk melihat bottleneck.

## 2. DAFTAR ROLE & HAK AKSES

| Role | Deskripsi | Menu yang Bisa Diakses | Aksi yang Bisa Dilakukan |
|---|---|---|---|
| `Admin` / `admin` | Superuser operasional dan shortcut monitoring | Owner dashboard, semua dashboard role tertentu via middleware yang menyertakan admin, monitoring, cash bank, log | Monitoring, melihat dokumen lintas role, akses endpoint admin/owner, urgency alert, beberapa route dokumen role |
| `owner` / `Owner` | Pimpinan/monitoring read-heavy | `owner/home`, `owner/dokumen`, tracking, workflow, rekapan keterlambatan, analytics, cash bank, programmer logs, notification logs | Melihat seluruh dokumen, filter live, timeline, history, trend chart, mengirim/reset urgency alert, export rekapan keterlambatan |
| `programmer` | Role teknis khusus | Programmer dashboard, bulk-to-payment, bulk-send-to-role, bulk-set-date-payment, document tools, user management, database tools, audit trail, 2FA reset requests, notification logs | Bulk direct to payment, bulk forward, ubah timestamp role data, kelola user, reset 2FA user, preview/cleanup database, export DB, audit aktivitas |
| `operator` | Pengelola dokumen agenda awal | Dashboard operator, documents CRUD, import CSV operator, reports operator, rejected check/detail, tracking | Buat/edit/hapus dokumen, import CSV, kirim ke Team Verifikasi, bulk send ke verifikasi, inline edit, lihat progress, approve operator-side |
| `team_verifikasi` | Pemeriksa/verifikator utama | Documents Verifikasi, reports verifikasi, returns verifikasi, pending approval, inbox, bulk operations | Terima/reject inbox, edit data verifikasi, set deadline, paraf, kirim ke perpajakan/akutansi/pembayaran, return ke operator/bagian/role lain, bulk approve/reject/forward |
| `verifikasi` | Alias legacy Team Verifikasi | Sama dengan `team_verifikasi` di beberapa route | Sama dengan Team Verifikasi; kode masih mempertahankan alias untuk backward compatibility |
| `perpajakan` / `Perpajakan` | Tim pajak | Documents Perpajakan, reports/export perpajakan, returns perpajakan, inbox, bulk forward | Terima/reject inbox, edit data pajak, set deadline, kirim ke akutansi atau next, return ke verifikasi, export data pajak |
| `akutansi` / `Akutansi` | Tim akuntansi; nama mengandung typo historis | Documents Akutansi, reports akutansi, returns akutansi, inbox, bulk forward | Terima/reject inbox, edit data akuntansi/MIRO, set deadline, kirim ke pembayaran, return ke verifikasi |
| `pembayaran` / `Pembayaran` | Tim pembayaran | Dashboard pembayaran, documents pembayaran, reports/export/delays/analytics, CSV import, returns pembayaran | Kelola status pembayaran, upload bukti, import CSV, edit tanggal dibayar, set deadline pembayaran, export rekapan |
| `bagian_akn` | Bagian Akuntansi/AKN | Bagian dashboard, bagian documents CRUD, tracking | Buat/edit/hapus dokumen bagian AKN, kirim ke Operator, lihat tracking, lihat return detail |
| `bagian_dpm` | Bagian DPM | Bagian dashboard/documents/tracking | Sama seperti role bagian, scoped ke `bagian_code = DPM` |
| `bagian_kpl` | Bagian Kepatuhan/KPL | Bagian dashboard/documents/tracking | Sama seperti role bagian, scoped ke `bagian_code = KPL` |
| `bagian_pmo` | Bagian PMO | Bagian dashboard/documents/tracking | Sama seperti role bagian, scoped ke `bagian_code = PMO` |
| `bagian_sdm` | Bagian SDM | Bagian dashboard/documents/tracking | Sama seperti role bagian, scoped ke `bagian_code = SDM` |
| `bagian_skh` | Bagian Sekretariat/SKH | Bagian dashboard/documents/tracking | Sama seperti role bagian, scoped ke `bagian_code = SKH` |
| `bagian_tan` | Bagian Tanaman/TAN | Bagian dashboard/documents/tracking | Sama seperti role bagian, scoped ke `bagian_code = TAN` |
| `bagian_tep` | Bagian Teknik & Pengolahan/TEP | Bagian dashboard/documents/tracking | Sama seperti role bagian, scoped ke `bagian_code = TEP` |
| `bagian_pti` | Potensi role bagian PTI | Data master `bagians` berisi PTI, tetapi `User::ROLES` dan seeder user bagian belum memasukkan `bagian_pti` | Jika dibuat manual, middleware `bagian` menerima prefix `bagian_`, tetapi menu/seed belum lengkap |
| Legacy `IbuA`, `IbuB`, `ibutarapul`, `ibuA`, `ibuB` | Role/nama historis dari versi awal | Muncul di migration dan komentar/backfill | Sudah distandarkan ke `operator` dan `team_verifikasi`, tetapi masih meninggalkan alias/komentar/logika kompatibilitas |

## 3. ALUR BISNIS (BUSINESS FLOW)

### State machine utama

Status dokumen aktif yang ditemukan:

- `draft`
- `sent_to_team_verifikasi`
- `sedang diproses`
- `menunggu_di_approve`
- `pending_approval_team_verifikasi`
- `pending_approval_perpajakan`
- `pending_approval_akutansi`
- `pending_approval_pembayaran`
- `waiting_reviewer_approval`
- `waiting_approval_perpajakan`
- `waiting_approval_akuntansi`
- `waiting_approval_pembayaran`
- `sent_to_perpajakan`
- `sent_to_akutansi`
- `sent_to_pembayaran`
- `returned_to_operator`
- `returned_to_department`
- `returned_to_verifikasi`
- `returned_to_bidang`
- `completed`
- `selesai`

Status per role disimpan lebih modern di `dokumen_statuses`:

- `pending`: menunggu di inbox role.
- `received`: diterima.
- `processing`: sedang diproses.
- `approved`: disetujui.
- `rejected`: ditolak.
- `completed`: selesai.
- `returned`: dikembalikan.

Data waktu per role disimpan di `dokumen_role_data`:

- `received_at`
- `processed_at`
- `deadline_at`
- `deadline_days`
- `deadline_note`
- `display_status`
- `role_specific_data`

### Alur normal

```text
[Bagian] → buat dokumen → kirim ke [Operator]
[Operator] → validasi/agenda → kirim ke inbox [Team Verifikasi]
[Team Verifikasi] → approve inbox → set/paraf/proses → kirim ke [Perpajakan] atau [Akutansi] atau langsung [Pembayaran]
[Perpajakan] → approve inbox → isi data pajak → kirim ke [Akutansi]
[Akutansi] → approve inbox → isi data MIRO/akuntansi → kirim ke [Pembayaran]
[Pembayaran] → approve inbox → proses status pembayaran → upload bukti/tanggal dibayar → selesai/sudah dibayar
[Owner/Admin] → monitoring lintas tahap, urgency alert, rekapan keterlambatan, workflow timeline
```

### Alur pengembalian

```text
[Team Verifikasi] → return_to_operator → [Operator] revisi → kirim ulang ke [Team Verifikasi]
[Team Verifikasi] → return_to_bidang → [Bagian terkait] revisi → kirim ulang ke [Operator/Team Verifikasi]
[Perpajakan] → returned_to_department / returned_to_verifikasi → [Team Verifikasi] review/kirim ulang
[Akutansi] → returned_to_department / returned_to_verifikasi → [Team Verifikasi] review/kirim ulang
[Pembayaran] → returned_to_department → [Team Verifikasi] review/kirim ulang
```

### Notifikasi

- Pusher/Laravel Echo:
  - `DocumentSent`: public channel `documents.{role}` untuk dokumen baru.
  - `DocumentReturned`: public channel `documents.Operator`.
  - `DocumentSentToInbox`: public channel `inbox-updates`.
  - `DocumentApprovedInbox`: private channel `inbox.{role}`.
  - `DocumentRejectedInbox`: private channel `inbox.{role}`.
  - `DocumentActivityChanged`: channel `document.{id}` untuk aktivitas view/edit pada detail inbox.
- WhatsApp Fonnte:
  - `LateDocumentNotificationService` mengirim warning/danger ke user role yang dokumennya terlambat.
  - Role target: `team_verifikasi`, `perpajakan`, `akutansi`, `pembayaran`.
  - Threshold default: 24 jam warning dan 72 jam danger untuk verifikasi/perpajakan/akutansi; 168 jam warning dan 504 jam danger untuk pembayaran.
  - Cooldown default 24 jam per dokumen/role/message type.
- Email fallback:
  - Jika WhatsApp gagal dan fallback aktif, sistem mengirim `DeadlineReminderMail`.
  - Reset 2FA mengirim status via mail dan WhatsApp.
- In-app database notification:
  - `DokumenDikembalikanNotification` untuk Bagian ketika dokumen dikembalikan dari Team Verifikasi.

## 4. SKEMA DATABASE LENGKAP

### Ringkasan tabel

Teridentifikasi 35 tabel project dari migration, ditambah tabel eksternal/read-only dari database `cash_bank_new`.

### Tabel project utama

| Tabel | Fungsi bisnis | Kolom utama | Relasi, FK, index |
|---|---|---|---|
| `users` | Akun login dan role RBAC | `id`, `username`, `name`, `email`, `phone_number`, `email_verified_at`, `password`, `role`, `bagian_code`, `table_columns_preferences`, `sort_preferences`, `remember_token`, `two_factor_enabled`, `two_factor_secret`, `two_factor_confirmed_at`, `two_factor_recovery_codes`, timestamps | `email` unique, `username` unique, index `role`; bagian user memakai `bagian_code` tanpa FK eksplisit |
| `password_reset_tokens` | Token reset password Laravel | `email` primary, `token`, `created_at` | Primary `email` |
| `sessions` | Session database Laravel | `id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity` | `user_id` index, `last_activity` index |
| `cache` | Cache database Laravel | `key`, `value`, `expiration` | Primary `key` |
| `cache_locks` | Lock cache database | `key`, `owner`, `expiration` | Primary `key` |
| `jobs` | Queue database Laravel | `id`, `queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at` | Index `queue` |
| `job_batches` | Batch queue Laravel | `id`, `name`, `total_jobs`, `pending_jobs`, `failed_jobs`, `failed_job_ids`, `options`, `cancelled_at`, `created_at`, `finished_at` | Primary `id` |
| `failed_jobs` | Failed queue jobs | `id`, `uuid`, `connection`, `queue`, `payload`, `exception`, `failed_at` | `uuid` unique |
| `dokumens` | Entitas utama dokumen SPP/agenda dan workflow | Core: `id`, `nomor_agenda`, `bulan`, `tahun`, `tanggal_masuk`, `nomor_spp`, `tanggal_spp`, `uraian_spp`, `nilai_rupiah`, `kategori`, `jenis_dokumen`, `jenis_sub_pekerjaan`, `jenis_pembayaran`, `kebun`, `bagian`, `nama_pengirim`, `dibayar_kepada`, `no_berita_acara`, `tanggal_berita_acara`, `no_spk`, `tanggal_spk`, `tanggal_berakhir_spk`, `nomor_miro`, `tanggal_miro`, `status`, `keterangan`, timestamps | `nomor_agenda` awalnya unique, lalu constraint diubah; banyak index performa untuk handler/status/date/search |
| `dokumens` lanjutan | Workflow dan return | `created_by`, `current_handler`, `current_stage`, `last_action_status`, legacy timestamp lama, `return_source`, `return_reason`, `returned_at`, `resent_to_verifikasi_at`, `was_returned_by_verifikasi`, `tanggal_paraf`, `pemaraf`, `tanggal_selesai_diproses`, `kepala_sub_bagian`, `status_dokumen_csv` | Return unified menggantikan beberapa kolom legacy yang kemudian di-drop |
| `dokumens` perpajakan | Data pajak | `npwp`, `status_perpajakan`, `no_faktur`, `tanggal_faktur`, `tanggal_selesai_verifikasi_pajak`, `jenis_pph`, `dpp_pph`, `ppn_terhutang`, `link_dokumen_pajak`, `komoditi_perpajakan`, `alamat_pembeli`, `no_kontrak`, `no_invoice`, `tanggal_invoice`, `dpp_invoice`, `ppn_invoice`, `dpp_ppn_invoice`, `tanggal_pengajuan_pajak`, `dpp_faktur`, `ppn_faktur`, `selisih_pajak`, `keterangan_pajak`, `penggantian_pajak`, `dpp_penggantian`, `ppn_penggantian`, `selisih_ppn`, `perpajakan_return_data`, `pengembalian_awaiting_fix`, `returned_from_perpajakan_fixed_at` | Banyak field nullable setelah migration `make_dokumens_fields_nullable` |
| `dokumens` pembayaran/import | Data pembayaran dan CSV | `status_pembayaran`, `tanggal_dibayar`, `link_bukti_pembayaran`, `nama_kebuns`, `no_ba`, `NO_PO`, `NO_MIRO_SES`, `DIBAYAR`, `BELUM_DIBAYAR`, `KATEGORI`, `imported_from_csv`, `csv_import_batch_id`, `csv_imported_at`, `urgency_active`, `urgency_sent_at`, `urgency_sent_by`, `auto_forwarded_at` | Index `urgency_active`, `auto_forwarded_at`, `created_by`, performance composite indexes |
| `dokumen_pos` | Nomor PO multi-value per dokumen | `id`, `dokumen_id`, `nomor_po`, timestamps | FK `dokumen_id` → `dokumens`, cascade delete |
| `dokumen_prs` | Nomor PR multi-value per dokumen | `id`, `dokumen_id`, `nomor_pr`, timestamps | FK `dokumen_id` → `dokumens`, cascade delete |
| `dibayar_kepadas` | Multi penerima pembayaran per dokumen | `id`, `dokumen_id`, `nama_penerima`, timestamps | `belongsTo dokumen`; FK implisit lewat model/migration |
| `bidangs` | Master bidang/departemen lama | `id`, `kode_bidang`, `nama_bidang`, `deskripsi`, `is_active`, timestamps | `kode_bidang` untuk return/filter |
| `bagians` | Master bagian/departemen baru | `id`, `kode`, `nama`, `deskripsi`, `is_active`, timestamps | `kode` digunakan oleh user `bagian_code` dan dokumen `bagian` |
| `roles` | Master role workflow | `code` primary, `name`, `sequence`, timestamps | FK dari `dokumen_statuses`, `dokumen_role_data`, `role_deadline_configs` |
| `dokumen_statuses` | Status dokumen per role/inbox | `id`, `dokumen_id`, `role_code`, `status`, `status_changed_at`, `changed_by`, `notes`, timestamps | FK `dokumen_id`; FK `role_code`; unique `dokumen_id + role_code`; index `role_code,status` dan `dokumen_id,status` |
| `dokumen_role_data` | Timestamp, deadline, dan data display per role | `id`, `dokumen_id`, `role_code`, `received_at`, `processed_at`, `deadline_at`, `deadline_days`, `deadline_note`, `role_specific_data`, `display_status`, timestamps | FK `dokumen_id`; FK `role_code`; unique `dokumen_id + role_code`; index `role_code` |
| `role_deadline_configs` | Default deadline per role | `id`, `role_code`, `default_deadline_days`, `description`, `is_active`, timestamps | `role_code` unique, FK ke `roles` |
| `document_trackings` | Tracking aksi historis dokumen lama | `id`, `document_id`, `action`, `actor`, `metadata`, `action_at`, timestamps | FK logis `document_id` → `dokumens`; metadata JSON |
| `dokumen_activity_logs` | Audit trail workflow dokumen modern | `id`, `dokumen_id`, `stage`, `action`, `action_description`, `performed_by`, `details`, `action_at`, timestamps | `dokumen_id` ke `dokumens`; details JSON di model |
| `document_activities` | Aktivitas real-time view/edit user | `id`, `dokumen_id`, `user_id`, `activity_type`, `last_activity_at`, timestamps | `dokumen_id`, `user_id`; dipakai event `DocumentActivityChanged` |
| `welcome_messages` | Pesan sambutan per modul | `id`, `module`, `message`, `type`, `is_active`, timestamps | Scope by module/type/active |
| `payment_logs` | Log pembayaran untuk data TU/TK/cash bank | `id`, `tu_tk_kontrol`, `data_source`, payment sequence/tanggal/jumlah/keterangan`, timestamps | Terhubung ke model TU/TK via `KONTROL` dan `data_source` |
| `document_position_trackings` | History perubahan posisi dokumen TU/TK | `id`, `tu_tk_kontrol`, `data_source`, `posisi_lama`, `posisi_baru`, `changed_by`, `keterangan`, `changed_at`, timestamps | Relasi ke model TU/TK via `KONTROL` |
| `tu_tk_dokumens` | Mapping/import dokumen TU/TK | `id` dan kolom mapping dokumen TU/TK sesuai migration | Mendukung laporan pembayaran legacy |
| `tu_tk_2023` | Raw import TU/TK 2023 | Banyak kolom raw uppercase: `KONTROL`, `AGENDA`, `TGL_SPP`, `NO_SPP`, `KATEGORI`, `VENDOR`, `NO_KONTRAK`, tanggal kontrak/BA/faktur, `HAL`, `NILAI`, `POSISI_DOKUMEN`, pembayaran I-VI, umur hutang, saldo, kolom bulan/tahun | Dibuat raw SQL karena nama kolom numerik |
| `tu_tk_pupuk_2023` | Raw import TU/TK pupuk | Kolom raw mirip TU/TK: `AGENDA`, `NO_SPP`, `KEBUN`, `VENDOR`, `NILAI`, `POSISI_DOKUMEN`, pembayaran, umur hutang, bulan/tahun, `EXTRA_COL_*` | Raw SQL; tidak ada PK eksplisit |
| `tu_tk_vd_2023` | Raw import TU/TK VD | `KONTROL`, `AGENDA`, `NO_SPP`, `KATEGORI`, `VENDOR`, `NILAI`, `POSISI_DOKUMEN`, pembayaran, umur hutang, bulan/tahun | Raw SQL; model memakai `KONTROL` sebagai primaryKey walau migration tidak menetapkan PK |
| `tu_tk_tan_2023` | Raw import TU/TK TAN | `KONTROL`, `AGENDA`, `NO_SPP`, `KEBUN`, `VENDOR`, `NILAI`, `POSISI_DOKUMEN`, pembayaran, umur hutang, bulan/tahun | Raw SQL; model memakai `KONTROL` |
| `whatsapp_notification_logs` | Log notifikasi WhatsApp/email | `id`, `dokumen_id`, `role_code`, `user_id`, `phone_number`, `message_type`, `message`, `status`, `channel`, `response`, `fallback_reason`, `sent_at`, timestamps | Relasi ke `dokumens` dan `users`; cooldown query per dokumen/role/type |
| `search_presets` | Preset advanced search per user | `id`, `user_id`, `name`, `filters`, `is_default`, `last_used_at`, timestamps | FK user; filters JSON |
| `dokumen_auto_forward_queue` | Queue auto-forward saat pembayaran dikonfirmasi eksternal | `id`, `dokumen_id` unique, `triggered_at`, `status`, `processed_at`, `error_message`, timestamps | FK `dokumen_id` cascade, index `status` |
| `sync_logs` | Log sinkronisasi Agenda Online ↔ Cash Bank | `id`, `dokumen_id`, `direction`, `status`, `fields_synced`, `conflict_fields`, `source_wins`, `error_message`, `synced_at`, timestamps | `dokumen_id` nullable index; JSON fields |
| `programmer_activity_logs` | Audit trail aktivitas sensitif programmer | `id`, `programmer_id`, `action`, `target_type`, `target_id`, `target_description`, `ip_address`, `user_agent`, `created_at` | FK `programmer_id` → `users`; index `programmer_id,created_at`, `action`, `created_at` |
| `two_factor_reset_requests` | Pengajuan reset 2FA oleh user | `id`, `requester_id`, `programmer_id`, `reason`, `status`, `handled_at`, `notes`, timestamps | FK requester/programmer ke `users`; index `requester_id,status`, `status,created_at` |

### Tabel eksternal `cash_bank_new` yang teridentifikasi

Tabel ini tidak dimigrasikan penuh di project Agenda Online, tetapi dipakai lewat model/service:

- `bank_tujuan`: VA/kebun/unit tujuan pembayaran.
- `droppings`: realisasi dropping dana M1-M4.
- `kategori_kriteria`: kategori transaksi.
- `penerimas`: penerimaan uang masuk, penjualan komoditas.
- `permintaans`: permintaan/rencana anggaran mingguan M1-M4.
- `sub_kriteria`: subkategori kriteria.
- `sumber_dana`: rekening/sumber dana.
- `bank_keluars`: target sinkronisasi dokumen Agenda Online ke Cash Bank.
- `bank_masuk`: sumber agregasi saldo/penerimaan.
- `item_sub_kriteria`: lookup item sub kriteria untuk sinkronisasi kategori.

## 5. FITUR-FITUR YANG ADA SAAT INI

- [x] Login berbasis username/email + password.
- [x] Rate limit login di `LoginRequest`.
- [x] 2FA Google Authenticator.
- [x] Recovery codes 2FA.
- [x] Pengajuan reset 2FA ke Programmer.
- [x] RBAC custom via middleware `role` dan `bagian`.
- [x] Dashboard per role.
- [x] CRUD dokumen operator.
- [x] CRUD dokumen bagian.
- [x] CRUD/edit per role verifikasi/perpajakan/akutansi/pembayaran.
- [x] Multi-value PO/PR.
- [x] Multi penerima pembayaran.
- [x] Inbox approval universal per role.
- [x] Quick approve/reject API.
- [x] Bulk approve/reject/forward.
- [x] Workflow status per role (`dokumen_statuses`).
- [x] Role data/deadline per role (`dokumen_role_data`).
- [x] Display status per role agar setiap role punya perspektif final.
- [x] Return workflow ke operator, team verifikasi, dan bagian.
- [x] Paraf dokumen Team Verifikasi.
- [x] Deadline dan rekapan keterlambatan.
- [x] Urgency alert dari owner/admin.
- [x] Tracking/timeline workflow dokumen.
- [x] Activity log/audit trail dokumen.
- [x] Activity presence/viewing/editing real-time.
- [x] Pusher/Laravel Echo notification.
- [x] WhatsApp Fonnte late-document notification.
- [x] Email fallback untuk notifikasi.
- [x] WhatsApp notification log.
- [x] Advanced search.
- [x] Search presets.
- [x] Autocomplete penerima, pengirim, uraian, PO, PR.
- [x] Inline edit table.
- [x] Virtual scrolling/pagination enhancement untuk tabel besar.
- [x] CSV import operator.
- [x] CSV import pembayaran.
- [x] Export/report pembayaran/perpajakan/keterlambatan.
- [x] Dashboard owner/god view.
- [x] Cash Bank read-only dashboard untuk pimpinan.
- [x] Sinkronisasi dokumen Agenda Online ke Cash Bank.
- [x] Auto-forward ke pembayaran saat status pembayaran dikonfirmasi eksternal.
- [x] Programmer tools untuk bulk repair dan user management.
- [x] Programmer audit trail.
- [x] Security headers middleware.
- [x] Welcome message dinamis per modul.

## 6. INTEGRASI EKSTERNAL

| Layanan | Digunakan untuk | Package/kode | ENV |
|---|---|---|---|
| Pusher | Real-time notification dokumen, inbox, aktivitas edit/view | `pusher/pusher-php-server`, `pusher-js`, `laravel-echo`, config `broadcasting.php`, events `ShouldBroadcast` | `BROADCAST_CONNECTION`, `PUSHER_APP_ID`, `PUSHER_APP_KEY`, `PUSHER_APP_SECRET`, `PUSHER_APP_CLUSTER`, `PUSHER_SCHEME`, optional `PUSHER_HOST`, `PUSHER_PORT`, `VITE_PUSHER_APP_KEY`, `VITE_PUSHER_APP_CLUSTER` |
| Fonnte WhatsApp Gateway | Notifikasi dokumen terlambat dan status reset 2FA | `App\Services\FonnteWhatsAppService`, `LateDocumentNotificationService`, `config/fonnte.php` | `FONNTE_API_TOKEN`, `FONNTE_API_URL`, `FONNTE_COUNTRY_CODE`, `FONNTE_DELAY`, `WHATSAPP_NOTIFICATIONS_ENABLED`, `WHATSAPP_NOTIFICATION_COOLDOWN` |
| Google Authenticator / TOTP | 2FA user | `pragmarx/google2fa`, `TwoFactorController`, encrypted user fields | Tidak ada ENV khusus; memakai `APP_KEY` untuk encrypt secret |
| Mail SMTP | Email fallback deadline dan reset 2FA | Laravel Mail, `DeadlineReminderMail`, `TwoFactorResetStatusMail` | `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_ENCRYPTION`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`, `NOTIFICATION_FALLBACK_EMAIL` |
| Cash Bank database | Laporan pimpinan dan sinkronisasi data pembayaran | Koneksi `cash_bank_new`, model `App\Models\CashBank\*`, `DokumenSyncService`, `CashBankReportService` | `DB_CASHBANK_CONNECTION`, `DB_CASHBANK_HOST`, `DB_CASHBANK_PORT`, `DB_CASHBANK_DATABASE`, `DB_CASHBANK_USERNAME`, `DB_CASHBANK_PASSWORD` |
| Maatwebsite Excel | Export Excel/rekapan | `maatwebsite/excel` di composer; dipakai pada export rekapan | Tidak ada ENV khusus |
| Bootstrap/CDN, FontAwesome, SweetAlert/JS libs | UI Blade legacy | `twbs/bootstrap`, CDN di layout Blade | Tidak ada ENV khusus |

## 7. MASALAH & KELEMAHAN TEKNIS (ANALISIS JUJUR)

### Fat controller ekstrem

- `DashboardPembayaranController.php` sekitar 248 KB.
- `OwnerDashboardController.php` sekitar 224 KB.
- `TeamVerifikasiController.php` sekitar 173 KB.
- `DashboardPerpajakanController.php` sekitar 125 KB.
- `DokumenController.php` sekitar 98 KB.
- `DashboardAkutansiController.php` sekitar 91 KB.

Controller mengandung query kompleks, validasi, business rule, transformasi data, HTML generation, export, status transitions, logging, dan response building sekaligus. Ini membuat perilaku sulit diuji dan rawan regresi.

### Domain model terlalu gemuk

`Dokumen` memuat banyak tanggung jawab: relasi, scope, status mapping, workflow inbox, approval/reject, display status, helper label, dan backward compatibility. Ini membuat model menjadi service tersembunyi dan sulit dipisahkan saat rebuild.

### Status workflow tidak konsisten

- Ada status lama `sent_to_ibub`, `returned_to_ibua`, `approved_ibub`, `rejected_ibub`.
- Ada standar baru `team_verifikasi`, tetapi alias `verifikasi` masih dipertahankan.
- Ada typo domain `akutansi` yang sudah menyebar ke route, table, migration, config, dan UI.
- Ada campuran `sedang diproses` dengan spasi dan `sedang_diproses` dengan underscore.
- Ada `returned_to_department` dan `returned_to_verifikasi` yang maknanya mirip namun berbeda.
- Ada status pembayaran dengan beberapa format: `sudah_dibayar`, `SUDAH_DIBAYAR`, `SUDAH DIBAYAR`.

Rebuild sebaiknya memakai state machine eksplisit, bukan string bebas tersebar.

### RBAC belum matang

- Role disimpan sebagai string di `users.role`, bukan permission model terstruktur.
- Ada table `roles`, tetapi belum menjadi sumber otorisasi tunggal.
- `CheckRole` case-insensitive, tetapi banyak controller/view masih melakukan mapping sendiri.
- Banyak FormRequest `authorize()` mengembalikan `true`, sehingga otorisasi bergeser ke controller atau route middleware.
- Programmer role memiliki endpoint sensitif seperti user management, DB cleanup, export database, bulk status repair; perlu policy, audit, dan approval kuat.

### Query N+1 dan query berat

- Beberapa view dan partial mengakses relasi/model langsung, misalnya handler select memuat options `Bagian`.
- Banyak controller memakai filter collection setelah query/pagination, terutama untuk computed status dan keterlambatan.
- Banyak query `whereHas`, `whereDoesntHave`, `with` conditional, dan transform collection besar di controller.
- Dashboard pembayaran menghitung status dengan closure berulang dan banyak fallback format.
- Owner dashboard dan rekapan berpotensi heavy karena banyak statistik live.

Rebuild perlu query object/read model dan materialized workflow metrics.

### Service layer belum konsisten

Ada service yang baik sebagai awal: `AutoForwardDokumenService`, `DokumenSyncService`, `FonnteWhatsAppService`, `LateDocumentNotificationService`, `CashBankReportService`, `WelcomeMessageService`. Namun workflow utama masih dominan di controller/model. Tidak ada Application Service/Action layer untuk transisi dokumen.

### Validasi tidak konsisten

- Ada `StoreDokumenRequest` dan `UpdateDokumenRequest`, tetapi banyak route update/action memakai validasi inline.
- Banyak field dokumen dibuat nullable dan pesan validasi masih menyebut wajib.
- `SetDeadlineRequest` authorization berisi fallback permisif dan mapping role yang tampak tidak sinkron.
- Validasi pembayaran/perpajakan/akutansi tersebar di controller masing-masing.

### Blade terlalu besar dan mencampur tanggung jawab

- `resources/views/layouts/app.blade.php` sekitar 255 KB, memuat CSS global, sidebar role, topbar, real-time JS, polling, urgency widget, fullscreen, keyboard shortcuts.
- Beberapa daftar dokumen role >250 KB sampai >390 KB.
- Banyak JavaScript inline, fetch, modal, state management, dan styling berada langsung di Blade.
- Ada indikasi bug JS serius: `const isTeam Verifikasi = ...` di layout, nama variabel dengan spasi invalid.
- Layout masih memanggil endpoint lama seperti `/Operator/check-rejected` dan `/Team Verifikasi/check-rejected`, sedangkan route kompatibilitas lama disebut sudah dihapus.

### Security concerns

- Seeder berisi kredensial default hardcoded (`admin123`, `ibua123`, dst.) dan `UpdateUserCredentialsSeeder` menyimpan password baru di repo.
- GET `/logout` tersedia untuk mengatasi 419, tetapi logout via GET rentan CSRF/logout CSRF.
- Beberapa event memakai public channel (`documents.Operator`, `documents.{role}`, `inbox-updates`, `document.{id}`), padahal data dokumen bersifat internal.
- CSP masih mengizinkan `'unsafe-inline'` dan `'unsafe-eval'`.
- Role switch/dev route memang abort/404 di production, tetapi tetap ada kompleksitas attack surface.
- Programmer DB cleanup/user delete/reset 2FA endpoint harus dipastikan semua punya audit, confirmation, dan authorization policy.
- Broadcasting auth sudah diperbaiki dari MD5 custom, tetapi sebagian event masih public.

### Migration debt

- Banyak migration bersifat data backfill/rename dan beberapa memakai raw `DB::statement`.
- Raw table TU/TK dibuat tanpa primary key eksplisit, sementara model memakai `KONTROL`.
- Banyak kolom legacy ditambah lalu di-drop, status enum diubah berkali-kali, lalu status menjadi varchar.
- Rebuild sebaiknya membuat baseline schema bersih dari hasil akhir domain, bukan mengulang semua migration historis.

### Integrasi dan operasional

- `FonnteWhatsAppService::sendBulkMessages` memakai `sleep()`, tidak queue-friendly.
- Notifikasi keterlambatan harus dijalankan scheduler/queue, tetapi report ini tidak menemukan konfigurasi cron yang solid selain service.
- `DokumenSyncService` melakukan update ke database eksternal dan swallowing exception agar alur utama tidak gagal; perlu observability dan retry lebih jelas.
- `AutoForwardDokumenService` mensimulasikan approval lintas role oleh `system`; perlu audit dan state machine resmi.

## 8. REKOMENDASI UNTUK REBUILD

### Tech stack yang disarankan

- Laravel 12 atau versi LTS/stable terbaru pada saat rebuild, PHP 8.3+, MySQL 8.
- Redis untuk cache, queue, lock, dan rate limit; database queue hanya fallback.
- Laravel Reverb atau Pusher untuk broadcast, tetapi semua channel dokumen harus private/presence channel.
- Laravel Notifications untuk WhatsApp/email/in-app dengan queue.
- Tailwind CSS v4 + Alpine.js atau Livewire/Inertia, pilih satu pola frontend yang konsisten.
- Maatwebsite Excel versi yang kompatibel dengan Laravel 12; verifikasi karena `^1.1` terlihat tidak lazim untuk Laravel modern.

### Arsitektur yang disarankan

- Gunakan bounded context:
  - `IdentityAccess`: user, role, 2FA, reset 2FA, policies.
  - `Documents`: dokumen, PO/PR, penerima, validation.
  - `Workflow`: state machine, inbox, approval, return, deadline.
  - `Notifications`: WhatsApp/email/in-app/broadcast.
  - `Reporting`: owner dashboards, keterlambatan, export.
  - `CashBankIntegration`: read-only report dan sync.
  - `ProgrammerTools`: admin repair tools dengan audit.
- Buat `DocumentWorkflowService` atau Action classes:
  - `CreateDocumentAction`
  - `SendDocumentToRoleAction`
  - `ApproveInboxDocumentAction`
  - `RejectInboxDocumentAction`
  - `ReturnDocumentAction`
  - `SetDeadlineAction`
  - `MarkPaymentStatusAction`
  - `AutoForwardPaidDocumentAction`
- Pakai enum PHP untuk:
  - `RoleCode`
  - `DocumentStatus`
  - `RoleWorkflowStatus`
  - `PaymentStatus`
  - `ReturnMode`
- Pisahkan read model/query object untuk dashboard:
  - `OwnerDashboardQuery`
  - `RoleDocumentListQuery`
  - `LateDocumentQuery`
  - `PaymentDashboardQuery`
- Gunakan Policy/Gate untuk otorisasi per aksi dokumen, bukan hanya route middleware.
- Buat event domain:
  - `DocumentCreated`
  - `DocumentSentToInbox`
  - `DocumentApproved`
  - `DocumentRejected`
  - `DocumentReturned`
  - `DeadlineSet`
  - `PaymentMarkedPaid`
- Queue semua notifikasi dan integrasi eksternal.
- Buat baseline migration bersih, lalu data migration terpisah jika perlu import legacy.
- Frontend: pecah layout menjadi component kecil: sidebar, topbar, table toolbar, document table, modal, notification widget. Hindari JS/CSS raksasa di Blade.

### Fitur baru yang disarankan

- Visual workflow builder/configurable routing per jenis dokumen.
- SLA calendar-aware deadline: hari kerja, libur, cut-off time.
- Dashboard bottleneck dengan aging bucket dan owner drill-down.
- Audit log immutable dengan diff field sebelum/sesudah.
- Notification preference per user/role.
- Role/permission management UI yang aman.
- Import validation sandbox dengan preview error granular.
- Reconciliation screen Agenda Online ↔ Cash Bank.
- Attachment/document file management dengan virus scan dan access control.
- Full test suite untuk workflow transition matrix.

### Hal yang sudah bagus dan perlu dipertahankan

- Pemisahan `dokumen_statuses` dan `dokumen_role_data` adalah arah yang benar.
- Unified return fields (`return_source`, `return_reason`, `returned_at`) lebih baik daripada banyak kolom return legacy.
- Audit trail programmer adalah ide penting untuk endpoint sensitif.
- Cash Bank read-only model diberi komentar jelas bahwa tidak boleh write.
- Notifikasi keterlambatan sudah punya threshold per role dan cooldown.
- Advanced search + presets berguna untuk operasional harian.
- Virtual scrolling/pagination menjawab masalah tabel besar, meskipun implementasinya perlu dirapikan.

## 9. DAFTAR LENGKAP ROUTE

Catatan: jumlah deklarasi route statis yang ditemukan di `routes/web.php` adalah sekitar 240, termasuk conditional development/production routes dan route group. Tabel berikut mengelompokkan semua endpoint fungsional berdasarkan prefix agar tetap dapat dibaca oleh agent rebuild.

| Method | URI | Controller@Method | Middleware | Keterangan |
|---|---|---|---|---|
| GET | `/login` | `LoginController@showLoginForm` | `guest` | Form login |
| POST | `/login` | `LoginController@login` | `guest` | Submit login |
| GET | `/2fa/verify` | `TwoFactorController@showVerify` | `guest` | Form verifikasi 2FA |
| POST | `/2fa/verify` | `TwoFactorController@verify` | `guest` | Verifikasi TOTP |
| POST | `/2fa/verify-recovery` | `TwoFactorController@verifyRecoveryCode` | `guest` | Verifikasi recovery code |
| GET | `/logout` | closure | none | Logout via GET |
| POST | `/logout` | `LoginController@logout` | `auth` | Logout via POST |
| GET | `/dashboard` | `LoginController@dashboard` | `auth` | Redirect dashboard berdasarkan role |
| GET | `/2fa/setup` | `TwoFactorController@showSetup` | `auth` | Setup 2FA |
| POST | `/2fa/enable` | `TwoFactorController@enable` | `auth` | Enable 2FA |
| GET | `/2fa/recovery-codes` | `TwoFactorController@showRecoveryCodes` | `auth` | Recovery codes |
| POST | `/2fa/regenerate-recovery-codes` | `TwoFactorController@regenerateRecoveryCodes` | `auth` | Regenerate recovery codes |
| POST | `/2fa/disable` | `TwoFactorController@disable` | `auth` | Disable 2FA |
| GET | `/profile/account` | `ProfileController@showAccount` | `auth` | Account profile |
| POST | `/profile/update-username` | `ProfileController@updateUsername` | `auth` | Update username |
| POST | `/profile/update-email` | `ProfileController@updateEmail` | `auth` | Update email |
| POST | `/profile/update-password` | `ProfileController@updatePassword` | `auth` | Update password |
| POST | `/profile/2fa-reset-requests` | `TwoFactorResetRequestController@store` | `auth` | Ajukan reset 2FA |
| GET | `/api/documents/{id}/preview` | `Api\DocumentPreviewController@getPreviewData` | `auth` | Preview dokumen |
| POST | `/api/documents/{id}/quick-approve` | `Api\DocumentPreviewController@quickApprove` | `auth` | Quick approve |
| POST | `/api/documents/{id}/quick-reject` | `Api\DocumentPreviewController@quickReject` | `auth` | Quick reject |
| POST | `/api/search/documents` | `Api\AdvancedSearchController@search` | `auth` | Advanced search |
| GET | `/api/search/filter-options` | `Api\AdvancedSearchController@getFilterOptions` | `auth` | Filter options |
| GET | `/api/search/presets` | `Api\AdvancedSearchController@loadPresets` | `auth` | Load presets |
| POST | `/api/search/presets` | `Api\AdvancedSearchController@savePreset` | `auth` | Save preset |
| POST | `/api/search/presets/{id}/use` | `Api\AdvancedSearchController@usePreset` | `auth` | Mark preset used |
| DELETE | `/api/search/presets/{id}` | `Api\AdvancedSearchController@deletePreset` | `auth` | Delete preset |
| ANY | `/broadcasting/auth` | Laravel Broadcast routes | `web,auth` | Broadcast auth |
| GET | `/` | closure | none | Redirect login |
| GET | `/test-welcome/{module}` | closure | env conditional + auth/dev | Test welcome message atau 404 production |
| GET | `/simple-test` | closure | env conditional + auth/dev | Test welcome service atau 404 production |
| GET | `/api/welcome-message` | `WelcomeMessageController@getMessage` | `auth` | Welcome message API |
| GET | `/api/documents/verifikasi/check-updates` | `TeamVerifikasiController@checkVerifikasiUpdates` | `auth` | Poll update verifikasi |
| GET | `/api/documents/perpajakan/check-updates` | `DashboardPerpajakanController@checkUpdates` | `auth` | Poll update perpajakan |
| GET | `/api/documents/akutansi/check-updates` | `DashboardAkutansiController@checkUpdates` | `auth` | Poll update akutansi |
| GET | `/api/documents/pembayaran/check-updates` | `DashboardPembayaranController@checkUpdates` | `auth` | Poll update pembayaran |
| GET | `/dashboard` | `DashboardController@index` | `auth, role:admin,operator` | Dashboard operator/admin |
| GET | `/dashboard/verifikasi` | redirect closure | `auth, role:admin,team_verifikasi,verifikasi` | Redirect documents verifikasi |
| GET | `/dashboard/pembayaran` | `DashboardPembayaranController@index` | `auth, role:admin,pembayaran` | Dashboard pembayaran |
| GET | `/dashboard/akutansi` | redirect closure | `auth, role:admin,akutansi` | Redirect documents akutansi |
| GET | `/dashboard/perpajakan` | redirect closure | `auth, role:admin,perpajakan` | Redirect documents perpajakan |
| GET | `/api/documents/rejected/check` | `DashboardController@checkRejectedDocuments` | `auth, role:admin,operator` | Check rejected operator |
| GET | `/api/documents/rejected/{dokumen}` | `DashboardController@showRejectedDocument` | `auth, role:admin,operator,bagian` | Detail rejected operator/bagian |
| GET | `/api/documents/verifikasi/rejected/check` | `TeamVerifikasiController@checkRejectedDocuments` | `auth, role:admin,team_verifikasi` | Check rejected verifikasi |
| GET | `/api/documents/verifikasi/rejected/{dokumen}` | `TeamVerifikasiController@showRejectedDocument` | `auth, role:admin,team_verifikasi` | Detail rejected verifikasi |
| GET | `/owner/home` | `OwnerDashboardController@home` | `auth, role:admin,owner` | Owner home |
| GET | `/owner/dokumen` | `OwnerDashboardController@index` | `auth, role:admin,owner` | Owner dokumen |
| GET | `/owner/dokumen/filter` | `OwnerDashboardController@filterDocuments` | `auth, role:admin,owner` | AJAX filter dokumen |
| GET | `/owner/dashboard` | redirect closure | `auth, role:admin,owner` | Redirect owner home |
| GET | `/owner/api/real-time-updates` | `OwnerDashboardController@getRealTimeUpdates` | `auth, role:admin,owner` | Owner realtime API |
| GET | `/owner/api/recent-documents` | `OwnerDashboardController@getRecentDocuments` | `auth, role:admin,owner` | Recent documents |
| GET | `/owner/api/trend-chart` | `OwnerDashboardController@getTrendChart` | `auth, role:admin,owner` | Trend chart |
| GET | `/tracking-dokumen` | `OwnerDashboardController@trackingDokumen` | `auth` | Tracking semua role |
| GET | `/owner/api/document-timeline/{id}` | `OwnerDashboardController@getDocumentTimeline` | `auth, role:admin,owner` | Timeline API |
| GET | `/owner/workflow/{id}` | `OwnerDashboardController@showWorkflow` | `auth` | Workflow detail |
| GET | `/owner/rekapan-keterlambatan` | `OwnerDashboardController@rekapanKeterlambatan` | `auth, role:admin,owner` | Rekapan delay |
| GET | `/owner/rekapan-keterlambatan/{roleCode}` | `OwnerDashboardController@rekapanKeterlambatanByRole` | `auth` | Delay by role |
| GET | `/owner/rekapan-keterlambatan-export/{roleCode}` | `OwnerDashboardController@exportRekapanKeterlambatan` | `auth, role:admin,owner` | Export delay |
| GET | `/owner/analytics` | redirect closure | `auth, role:admin,owner` | Redirect analytics |
| GET | `/owner/analytics/data` | `AnalyticsController@getAnalyticsData` | `auth, role:admin,owner` | Analytics data |
| GET | `/admin/monitoring` | `OwnerDashboardController@index` | `auth, role:admin` | Admin monitoring |
| POST | `/owner/dokumen/{id}/urgency` | `OwnerDashboardController@sendUrgency` | `auth, role:admin,owner` | Send urgency |
| DELETE | `/owner/dokumen/{id}/urgency` | `OwnerDashboardController@resetUrgency` | `auth, role:admin,owner` | Reset urgency |
| DELETE | `/owner/urgency/reset-all` | `OwnerDashboardController@resetAllUrgencies` | `auth, role:admin,owner` | Reset all urgency |
| GET | `/owner/dokumen/{id}/history` | `OwnerDashboardController@getHistory` | `auth, role:admin,owner` | History API |
| GET | `/api/documents/urgency/active` | `OwnerDashboardController@getActiveUrgencies` | `auth` | Active urgency polling |
| GET | `/documents` | `DokumenController@index` | `auth, role:admin,operator` | Operator documents |
| GET | `/documents/create` | `DokumenController@create` | `auth, role:admin,operator` | Create document |
| POST | `/documents` | `DokumenController@store` | `auth, role:admin,operator` | Store document |
| GET | `/documents/import` | `OperatorCsvImportController@index` | `auth, role:admin,operator` | Operator import |
| POST | `/documents/import/upload` | `OperatorCsvImportController@upload` | `auth, role:admin,operator` | Upload import |
| POST | `/documents/import/preview` | `OperatorCsvImportController@preview` | `auth, role:admin,operator` | Preview import |
| POST | `/documents/import` | `OperatorCsvImportController@import` | `auth, role:admin,operator` | Execute import |
| GET | `/documents/next-nomor-agenda` | `DokumenController@nextNomorAgenda` | `auth, role:admin,operator` | Generate nomor agenda |
| POST | `/documents/bulk-send-to-verifikasi` | `DokumenController@bulkSendToTeamVerifikasi` | `auth, role:admin,operator` | Bulk send |
| GET | `/documents/ajax-rows` | `DokumenController@ajaxRows` | `auth, role:admin,operator` | AJAX rows |
| GET | `/documents/{dokumen}/edit` | `DokumenController@edit` | `auth, role:admin,operator` | Edit document |
| GET | `/documents/{dokumen}/detail` | `DokumenController@getDocumentDetail` | `auth, role:admin,operator` | Detail API |
| GET | `/documents/{dokumen}/progress` | `DokumenController@getDocumentProgressForOperator` | `auth, role:admin,operator` | Progress API |
| PUT | `/documents/{dokumen}` | `DokumenController@update` | `auth, role:admin,operator` | Update document |
| DELETE | `/documents/{dokumen}` | `DokumenController@destroy` | `auth, role:admin,operator` | Delete document |
| POST | `/documents/{dokumen}/send-to-verifikasi` | `DokumenController@sendToTeamVerifikasi` | `auth, role:admin,operator` | Send to verifikasi |
| POST | `/documents/{dokumen}/approve` | `DokumenController@approveDocument` | `auth, role:admin,operator` | Approve document |
| PATCH | `/documents/{dokumen}/inline-update` | `DokumenController@inlineUpdate` | `auth, role:admin,operator,team_verifikasi,verifikasi,perpajakan,akutansi,pembayaran,bagian` | Inline edit |
| PATCH | `/documents/{dokumen}/handler` | `DocumentHandlerController@update` | `auth` | Ubah pengurus dokumen |
| GET | `/reports` | `DokumenRekapanController@index` | `auth, role:admin,operator` | Report operator |
| GET | `/reports/analytics` | `DokumenRekapanController@analytics` | `auth, role:admin,operator` | Analytics operator |
| GET | `/api/autocomplete/payment-recipients` | `AutocompleteController@getPaymentRecipients` | none explicit | Autocomplete penerima |
| GET | `/api/autocomplete/document-senders` | `AutocompleteController@getDocumentSenders` | none explicit | Autocomplete pengirim |
| GET | `/api/autocomplete/document-descriptions` | `AutocompleteController@getDocumentDescriptions` | none explicit | Autocomplete uraian |
| GET | `/api/autocomplete/po-numbers` | `AutocompleteController@getPONumbers` | none explicit | Autocomplete PO |
| GET | `/api/autocomplete/pr-numbers` | `AutocompleteController@getPRNumbers` | none explicit | Autocomplete PR |
| GET | `/pengembalian-dokumens` | `PengembalianDokumenController@index` | none explicit | Legacy return page |
| GET | `/documents/verifikasi` | `TeamVerifikasiController@dokumens` | `auth, role:admin,team_verifikasi,verifikasi` | Daftar verifikasi |
| GET | `/documents/verifikasi/{dokumen}/detail` | `TeamVerifikasiController@getDocumentDetail` | same | Detail verifikasi |
| GET | `/documents/verifikasi/{dokumen}/edit` | `TeamVerifikasiController@editDokumen` | same | Edit verifikasi |
| PUT | `/documents/verifikasi/{dokumen}` | `TeamVerifikasiController@updateDokumen` | same | Update verifikasi |
| POST | `/documents/verifikasi/{dokumen}/return-to-department` | `TeamVerifikasiController@returnToDepartment` | same | Return department |
| POST | `/documents/verifikasi/{dokumen}/send-to-next` | `TeamVerifikasiController@sendToNextHandler` | same | Send next |
| POST | `/documents/verifikasi/{dokumen}/set-deadline` | `TeamVerifikasiController@setDeadline` | same | Set deadline |
| POST | `/documents/verifikasi/{dokumen}/return-to-owner` | `TeamVerifikasiController@returnToOperator` | same | Return operator |
| POST | `/documents/verifikasi/{dokumen}/change-status` | `TeamVerifikasiController@changeDocumentStatus` | same | Change status |
| POST | `/documents/verifikasi/{dokumen}/paraf` | `TeamVerifikasiController@parafDokumen` | same | Paraf |
| GET | `/reports/verifikasi` | `TeamVerifikasiController@rekapan` | same | Rekapan verifikasi |
| GET | `/reports/verifikasi/analytics` | `TeamVerifikasiController@rekapanAnalytics` | same | Analytics verifikasi |
| GET | `/returns/verifikasi` | `TeamVerifikasiController@pengembalian` | same | Return list |
| GET | `/returns/verifikasi/stats` | `TeamVerifikasiController@getPengembalianKeBagianStats` | same | Return stats |
| GET | `/returns/verifikasi/bagian` | `TeamVerifikasiController@pengembalianKeBidang` | same | Return bagian |
| POST | `/returns/verifikasi/{dokumen}/to-bidang` | `TeamVerifikasiController@returnToBidang` | same | Return to bidang |
| POST | `/returns/verifikasi/{dokumen}/restore-from-bidang` | `TeamVerifikasiController@restoreFromBidang` | same | Restore from bidang |
| POST | `/documents/verifikasi/{dokumen}/accept` | `TeamVerifikasiController@acceptDocument` | same | Accept pending |
| POST | `/documents/verifikasi/{dokumen}/reject` | `TeamVerifikasiController@rejectDocument` | same | Reject pending |
| GET | `/documents/verifikasi/pending-approval` | `TeamVerifikasiController@pendingApproval` | same | Pending approval |
| POST | `/api/documents/{dokumen}/activity` | `InboxController@trackActivity` | `auth,web` | Track view/edit |
| GET | `/api/documents/{dokumen}/activities` | `InboxController@getActivities` | `auth,web` | Active activities |
| POST | `/api/documents/{dokumen}/activity/stop` | `InboxController@stopActivity` | `auth,web` | Stop activity |
| POST | `/universal-approval/{dokumen}/approve` | `InboxController@approve` | `auth` | Universal approve |
| POST | `/universal-approval/{dokumen}/reject` | `InboxController@reject` | `auth` | Universal reject |
| GET | `/universal-approval/{dokumen}/detail` | `UniversalApprovalController@getDetail` | `auth` | Approval detail |
| GET | `/universal-approval/notifications` | `UniversalApprovalController@checkNotifications` | `auth` | Approval notifications |
| GET | `/inbox` | `InboxController@index` | `auth, role:operator,team_verifikasi,verifikasi,perpajakan,akutansi,pembayaran,admin` | Inbox |
| GET | `/inbox/check-new` | `InboxController@checkNewDocuments` | same | Inbox polling |
| GET | `/inbox/history` | `InboxController@history` | same | Inbox history |
| POST | `/inbox/bulk-approve` | `InboxController@bulkApprove` | same | Bulk approve inbox |
| GET | `/inbox/{dokumen}` | `InboxController@show` | same | Inbox detail |
| POST | `/inbox/{dokumen}/approve` | `InboxController@approve` | same | Approve inbox |
| POST | `/inbox/{dokumen}/reject` | `InboxController@reject` | same | Reject inbox |
| GET | `/documents/pembayaran` | `DashboardPembayaranController@dokumens` | `auth, role:admin,pembayaran` | Documents pembayaran |
| GET | `/documents/pembayaran/{dokumen}/detail` | `DashboardPembayaranController@getDocumentDetail` | same | Detail pembayaran |
| GET | `/documents/pembayaran/{dokumen}/payment-data` | `DashboardPembayaranController@getPaymentData` | same | Payment data |
| POST | `/documents/pembayaran/{dokumen}/set-deadline` | `DashboardPembayaranController@setDeadline` | same | Set deadline pembayaran |
| POST | `/documents/pembayaran/{dokumen}/update-status` | `DashboardPembayaranController@updateStatus` | same | Update status pembayaran |
| POST | `/documents/pembayaran/{dokumen}/upload-proof` | `DashboardPembayaranController@uploadBukti` | same | Upload bukti |
| GET | `/documents/pembayaran/create` | `DashboardPembayaranController@createDokumen` | same | Create pembayaran |
| POST | `/documents/pembayaran` | `DashboardPembayaranController@storeDokumen` | same | Store pembayaran |
| GET | `/documents/pembayaran/{dokumen}/edit` | `DashboardPembayaranController@editDokumen` | same | Edit pembayaran |
| PUT | `/documents/pembayaran/{dokumen}` | `DashboardPembayaranController@updateDokumen` | same | Update pembayaran |
| DELETE | `/documents/pembayaran/{dokumen}` | `DashboardPembayaranController@destroyDokumen` | same | Delete pembayaran |
| GET | `/reports/pembayaran` | redirect closure | `auth, role:admin,pembayaran` | Report pembayaran index |
| GET/POST | `/reports/pembayaran/export` | `DashboardPembayaranController@exportRekapan` | same | Export pembayaran |
| GET | `/reports/pembayaran/delays` | `DashboardPembayaranController@rekapanKeterlambatan` | same | Delays pembayaran |
| GET | `/reports/pembayaran/analytics` | `DashboardPembayaranController@analytics` | same | Analytics pembayaran |
| GET | `/returns/pembayaran` | `DashboardPembayaranController@pengembalian` | `auth, role:admin,pembayaran` | Returns pembayaran |
| GET | `/dashboard-pembayaran` | `DashboardPembayaranController@index` | `auth` | Legacy dashboard pembayaran |
| GET | `/dashboard-pembayaran/import` | `DashboardPembayaranController@showImportForm` | `auth` | Import form |
| POST | `/dashboard-pembayaran/import-csv` | `DashboardPembayaranController@importCsv` | `auth` | Import CSV |
| GET | `/dashboard-pembayaran/download-csv-template` | `DashboardPembayaranController@downloadCsvTemplate` | `auth` | CSV template |
| POST | `/dashboard-pembayaran/check-updates` | `DashboardPembayaranController@checkUpdates` | `auth` | Legacy update check |
| GET | `/csv-import` | `CsvImportController@index` | `auth, role:admin,pembayaran` | CSV import pembayaran |
| POST | `/csv-import/upload` | `CsvImportController@upload` | same | Upload CSV |
| POST | `/csv-import/preview` | `CsvImportController@preview` | same | Preview CSV |
| POST | `/csv-import/import` | `CsvImportController@import` | same | Execute CSV |
| GET | `/documents/akutansi` | `DashboardAkutansiController@dokumens` | `auth, role:admin,akutansi` | Documents akutansi |
| GET | `/documents/akutansi/create` | `DashboardAkutansiController@createDokumen` | same | Create akutansi |
| POST | `/documents/akutansi` | `DashboardAkutansiController@storeDokumen` | same | Store akutansi |
| GET | `/documents/akutansi/{dokumen}/edit` | `DashboardAkutansiController@editDokumen` | same | Edit akutansi |
| GET | `/documents/akutansi/{dokumen}/detail` | `DashboardAkutansiController@getDocumentDetail` | same | Detail akutansi |
| PUT | `/documents/akutansi/{dokumen}` | `DashboardAkutansiController@updateDokumen` | same | Update akutansi |
| DELETE | `/documents/akutansi/{dokumen}` | `DashboardAkutansiController@destroyDokumen` | same | Delete akutansi |
| POST | `/documents/akutansi/{dokumen}/set-deadline` | `DashboardAkutansiController@setDeadline` | same | Set deadline |
| POST | `/documents/akutansi/{dokumen}/send-to-pembayaran` | `DashboardAkutansiController@sendToPembayaran` | same | Send pembayaran |
| POST | `/documents/akutansi/{dokumen}/return` | `DashboardAkutansiController@returnDocument` | same | Return akutansi |
| GET | `/reports/akutansi` | `DashboardAkutansiController@rekapan` | same | Report akutansi |
| GET | `/returns/akutansi` | `DashboardAkutansiController@pengembalian` | same | Returns akutansi |
| GET | `/documents/perpajakan` | `DashboardPerpajakanController@dokumens` | `auth, role:admin,perpajakan` | Documents perpajakan |
| GET | `/documents/perpajakan/{dokumen}/detail` | `DashboardPerpajakanController@getDocumentDetail` | same | Detail perpajakan |
| GET | `/documents/perpajakan/{dokumen}/edit` | `DashboardPerpajakanController@editDokumen` | same | Edit perpajakan |
| PUT | `/documents/perpajakan/{dokumen}` | `DashboardPerpajakanController@updateDokumen` | same | Update perpajakan |
| POST | `/documents/perpajakan/{dokumen}/set-deadline` | `DashboardPerpajakanController@setDeadline` | same | Set deadline |
| POST | `/documents/perpajakan/{dokumen}/send-to-next` | `DashboardPerpajakanController@sendToNext` | same | Send next |
| POST | `/documents/perpajakan/{dokumen}/send-to-akutansi` | `DashboardPerpajakanController@sendToAkutansi` | same | Send akutansi |
| POST | `/documents/perpajakan/{dokumen}/return` | `DashboardPerpajakanController@returnDocument` | same | Return perpajakan |
| GET | `/reports/perpajakan` | `DashboardPerpajakanController@rekapan` | same | Report perpajakan |
| GET | `/reports/perpajakan/export` | `DashboardPerpajakanController@exportView` | same | Export view |
| GET | `/reports/perpajakan/export/download` | `DashboardPerpajakanController@exportData` | same | Download export |
| GET | `/returns/perpajakan` | `DashboardPerpajakanController@pengembalian` | same | Returns perpajakan |
| GET | `/test-broadcast` | closure | env conditional + `auth, role:admin` in dev; 404 prod | Test broadcast |
| GET | `/test-returned-broadcast` | closure | env conditional + `auth, role:admin` in dev; 404 prod | Test returned broadcast |
| GET | `/test-broadcast-auth` | closure | env conditional + `auth, role:admin` in dev; 404 prod | Test broadcast auth |
| GET | `/test-trigger-notification` | closure | env conditional + `auth, role:admin` in dev; 404 prod | Test notification |
| GET | `/switch-role/{role}` | closure | env conditional + `auth, role:admin` in dev; 404 prod | Disabled role switch |
| GET | `/dev-dashboard/{role?}` | closure | env conditional + `auth, role:admin` in dev; 404 prod | Disabled dev dashboard |
| GET | `/dev-all` | closure | env conditional + `auth, role:admin` in dev; 404 prod | Disabled dev all |
| GET | `/bagian/dashboard` | `BagianDokumenController@dashboard` | `auth, bagian` | Dashboard bagian |
| GET | `/bagian/documents` | `BagianDokumenController@index` | `auth, bagian` | Bagian documents |
| GET | `/bagian/documents/create` | `BagianDokumenController@create` | same | Create bagian |
| POST | `/bagian/documents` | `BagianDokumenController@store` | same | Store bagian |
| GET | `/bagian/documents/{dokumen}/edit` | `BagianDokumenController@edit` | same | Edit bagian |
| GET | `/bagian/documents/{dokumen}/detail` | `BagianDokumenController@getDocumentDetail` | same | Detail bagian |
| PUT | `/bagian/documents/{dokumen}` | `BagianDokumenController@update` | same | Update bagian |
| DELETE | `/bagian/documents/{dokumen}` | `BagianDokumenController@destroy` | same | Delete bagian |
| POST | `/bagian/documents/{dokumen}/send-to-Operator` | `BagianDokumenController@sendToOperator` | same | Send to operator |
| GET | `/bagian/tracking` | `BagianDokumenController@tracking` | same | Tracking bagian |
| GET | `/api/bagian/documents/{dokumen}/return-detail` | `BagianDokumenController@getReturnDetail` | same | Return detail |
| POST | `/team-verifikasi/bulk/approve` | `BulkOperationController@bulkApprove` | `auth, role:team_verifikasi` | Bulk approve TV |
| POST | `/team-verifikasi/bulk/reject` | `BulkOperationController@bulkReject` | same | Bulk reject TV |
| POST | `/team-verifikasi/bulk/forward` | `BulkOperationController@bulkForward` | same | Bulk forward TV |
| POST | `/bulk-operations/forward` | `BulkOperationController@bulkForward` | `auth, role:team_verifikasi,verifikasi,perpajakan,akutansi` | Bulk forward common |
| GET | `/programmer/dashboard` | `ProgrammerController@dashboard` | `auth, role:programmer` | Programmer dashboard |
| GET | `/programmer/bulk-to-payment` | `ProgrammerController@showDirectToPaymentForm` | same | Bulk direct form |
| POST | `/programmer/bulk-to-payment/preview` | `ProgrammerController@previewDocuments` | same | Preview direct |
| POST | `/programmer/bulk-to-payment` | `ProgrammerController@bulkDirectToPayment` | same | Execute direct |
| GET | `/programmer/bulk-send-to-role` | `ProgrammerController@showBulkSendToRoleForm` | same | Bulk send form |
| POST | `/programmer/bulk-send-to-role/preview` | `ProgrammerController@previewBulkSendToRole` | same | Preview bulk send |
| POST | `/programmer/bulk-send-to-role` | `ProgrammerController@bulkSendToRole` | same | Execute bulk send |
| GET | `/programmer/bulk-set-date-payment` | `ProgrammerController@showBulkSetDatePaymentForm` | same | Bulk date form |
| POST | `/programmer/bulk-set-date-payment/preview` | `ProgrammerController@previewBulkSetDatePayment` | same | Preview bulk date |
| POST | `/programmer/bulk-set-date-payment` | `ProgrammerController@executeBulkSetDatePayment` | same | Execute bulk date |
| GET | `/programmer/document-tools` | `ProgrammerController@documentTools` | same | Document tools |
| GET | `/programmer/document-tools/search` | `ProgrammerController@searchDocuments` | same | Search docs |
| POST | `/programmer/document-tools/get-role-data` | `ProgrammerController@getRoleData` | same | Get role data |
| POST | `/programmer/document-tools/update-timestamps` | `ProgrammerController@updateTimestamps` | same | Update timestamps |
| GET | `/programmer/user-management` | `ProgrammerController@userManagement` | same | User management |
| GET | `/programmer/user-management/{id}` | `ProgrammerController@getUserData` | same | Get user |
| POST | `/programmer/user-management/store` | `ProgrammerController@storeUser` | same | Store user |
| POST | `/programmer/user-management/update` | `ProgrammerController@updateUser` | same | Update user |
| DELETE | `/programmer/user-management/{id}` | `ProgrammerController@destroyUser` | same | Delete user |
| POST | `/programmer/user-management/{id}/reset-2fa` | `ProgrammerController@resetUserTwoFactor` | same | Reset user 2FA |
| GET | `/programmer/database-tools` | `ProgrammerController@databaseTools` | same | DB tools |
| GET | `/programmer/database-tools/preview` | `ProgrammerController@previewCleanup` | same | Preview cleanup |
| POST | `/programmer/database-tools/cleanup` | `ProgrammerController@performCleanup` | same | Cleanup DB |
| GET | `/programmer/database-tools/export/{database}` | `ProgrammerController@exportDatabase` | same | Export DB |
| GET | `/programmer/activity-logs` | `ProgrammerController@activityLogs` | same | Activity logs |
| GET | `/programmer/programmer-audit-trail` | `ProgrammerLogController@index` | same | Programmer audit |
| GET | `/programmer/2fa-reset-requests` | `TwoFactorResetController@index` | same | 2FA reset requests |
| POST | `/programmer/2fa-reset-requests/{id}/approve` | `TwoFactorResetController@approve` | same | Approve reset 2FA |
| POST | `/programmer/2fa-reset-requests/{id}/reject` | `TwoFactorResetController@reject` | same | Reject reset 2FA |
| GET | `/programmer/notification-logs` | `WhatsAppNotificationLogController@index` | same | Notification logs |
| GET | `/owner/programmer-logs` | `ProgrammerLogController@ownerIndex` | `auth, role:owner,admin` | Owner programmer logs |
| GET | `/owner/notification-logs` | `WhatsAppNotificationLogController@index` | `auth, role:owner,admin` | Owner notification logs |
| GET | `/owner/cashbank` | `CashBankPimpinanController@index` | `auth, role:owner,admin` | Cash bank report |
| GET | `/owner/cashbank/chart-data` | `CashBankPimpinanController@chartData` | `auth, role:owner,admin` | Cash bank chart data |

## 10. ENVIRONMENT VARIABLES

| Variable | Keterangan | Contoh |
|---|---|---|
| `APP_NAME` | Nama aplikasi | `"Agenda Online PTPN"` |
| `APP_ENV` | Environment Laravel | `production` |
| `APP_KEY` | Encryption key Laravel dan 2FA secret encryption | `base64:...` |
| `APP_DEBUG` | Debug mode | `false` |
| `APP_URL` | URL aplikasi | `https://agenda.example.com` |
| `APP_LOCALE` | Locale utama | `id` |
| `APP_FALLBACK_LOCALE` | Locale fallback | `en` |
| `APP_FAKER_LOCALE` | Faker locale | `id_ID` |
| `APP_MAINTENANCE_DRIVER` | Driver maintenance | `file` |
| `APP_MAINTENANCE_STORE` | Store maintenance optional | `database` |
| `APP_PREVIOUS_KEYS` | Key rotation Laravel optional | `base64:oldkey` |
| `BCRYPT_ROUNDS` | Cost hash password | `12` |
| `LOG_CHANNEL` | Channel log | `daily` |
| `LOG_STACK` | Stack log | `daily` |
| `LOG_DEPRECATIONS_CHANNEL` | Deprecation log | `null` |
| `LOG_LEVEL` | Level log | `error` production |
| `DB_CONNECTION` | Koneksi utama | `mysql` |
| `DB_HOST` | Host DB utama | `127.0.0.1` |
| `DB_PORT` | Port DB utama | `3306` |
| `DB_DATABASE` | Nama DB utama | `agenda_online` |
| `DB_USERNAME` | User DB utama | `agenda_user` |
| `DB_PASSWORD` | Password DB utama | `secret` |
| `DB_CASHBANK_CONNECTION` | Driver DB cash bank | `mysql` |
| `DB_CASHBANK_HOST` | Host DB cash bank | `127.0.0.1` |
| `DB_CASHBANK_PORT` | Port DB cash bank | `3306` |
| `DB_CASHBANK_DATABASE` | Nama DB cash bank | `cash_bank_new` |
| `DB_CASHBANK_USERNAME` | User DB cash bank | `cashbank_readonly` |
| `DB_CASHBANK_PASSWORD` | Password DB cash bank | `secret` |
| `SESSION_DRIVER` | Driver session | `database` |
| `SESSION_LIFETIME` | Lifetime session menit | `120` |
| `SESSION_ENCRYPT` | Encrypt session | `false` |
| `SESSION_PATH` | Path cookie | `/` |
| `SESSION_DOMAIN` | Domain cookie | `null` |
| `CACHE_STORE` | Store cache | `database` atau `redis` |
| `FILESYSTEM_DISK` | Disk default | `local` |
| `QUEUE_CONNECTION` | Queue connection | `database` atau `redis` |
| `BROADCAST_CONNECTION` | Broadcast driver | `pusher` |
| `BROADCAST_DRIVER` | Fallback legacy broadcast driver | `pusher` |
| `PUSHER_APP_ID` | Pusher app id | `123456` |
| `PUSHER_APP_KEY` | Pusher key | `app-key` |
| `PUSHER_APP_SECRET` | Pusher secret | `app-secret` |
| `PUSHER_APP_CLUSTER` | Pusher cluster | `ap1` |
| `PUSHER_SCHEME` | Pusher scheme | `https` |
| `PUSHER_HOST` | Optional custom websocket host | `ws.example.com` |
| `PUSHER_PORT` | Optional websocket port | `443` |
| `VITE_PUSHER_APP_KEY` | Frontend pusher key | `"${PUSHER_APP_KEY}"` |
| `VITE_PUSHER_APP_CLUSTER` | Frontend pusher cluster | `"${PUSHER_APP_CLUSTER}"` |
| `VITE_PUSHER_HOST` | Frontend ws host optional | `ws.example.com` |
| `VITE_PUSHER_PORT` | Frontend ws port optional | `443` |
| `VITE_PUSHER_SCHEME` | Frontend scheme optional | `https` |
| `MAIL_MAILER` | Mail driver | `smtp` |
| `MAIL_HOST` | SMTP host | `smtp.gmail.com` |
| `MAIL_PORT` | SMTP port | `587` |
| `MAIL_USERNAME` | SMTP user | `noreply@example.com` |
| `MAIL_PASSWORD` | SMTP password/app password | `secret` |
| `MAIL_ENCRYPTION` | SMTP encryption | `tls` |
| `MAIL_FROM_ADDRESS` | Sender email | `noreply@example.com` |
| `MAIL_FROM_NAME` | Sender name | `"${APP_NAME}"` |
| `FONNTE_API_TOKEN` | Token Fonnte | `xxxxx` |
| `FONNTE_API_URL` | Endpoint Fonnte | `https://api.fonnte.com/send` |
| `FONNTE_COUNTRY_CODE` | Prefix negara | `62` |
| `FONNTE_DELAY` | Delay bulk WA detik | `5` |
| `WHATSAPP_NOTIFICATIONS_ENABLED` | Enable WA notif | `true` |
| `WHATSAPP_NOTIFICATION_COOLDOWN` | Cooldown jam | `24` |
| `NOTIFICATION_FALLBACK_EMAIL` | Fallback email ketika WA gagal | `true` |
| `VITE_APP_NAME` | Nama app frontend | `"${APP_NAME}"` |
| `AUTH_GUARD` | Default guard optional | `web` |
| `AUTH_PASSWORD_BROKER` | Password broker optional | `users` |
| `AUTH_MODEL` | Model auth optional | `App\Models\User` |
| `AUTH_PASSWORD_RESET_TOKEN_TABLE` | Tabel reset token optional | `password_reset_tokens` |
| `AUTH_PASSWORD_TIMEOUT` | Password confirmation timeout | `10800` |

## 11. SPRINT PLAN UNTUK REBUILD

| Sprint | Tujuan | Deliverable | Kompleksitas |
|---|---|---|---|
| 0 | Discovery dan baseline domain | Finalisasi event storming, state machine, ERD bersih, mapping legacy → canonical | Sedang |
| 1 | Fondasi Laravel | Laravel 12 fresh, auth, role enum, policy, migration baseline, test setup, CI | Sedang |
| 2 | Dokumen core | CRUD dokumen, PO/PR, penerima, bagian, validation, nomor agenda service | Tinggi |
| 3 | Workflow engine | Inbox, send/approve/reject/return, state machine, role data, deadlines | Sangat tinggi |
| 4 | UI operasional | Layout modular, dashboard per role, table reusable, inline edit aman, modal detail | Tinggi |
| 5 | Notification layer | Private broadcast, WhatsApp queued notification, email fallback, in-app notification, logs | Tinggi |
| 6 | Reporting | Owner dashboard, tracking timeline, rekapan keterlambatan, export Excel/PDF | Tinggi |
| 7 | Cash Bank integration | Read-only cash bank report, sync service AO→CB, reconciliation, retry log | Tinggi |
| 8 | Programmer tools | Bulk repair tools, user management, 2FA reset approval, audit trail hardened | Sedang-Tinggi |
| 9 | Migration/import legacy | Data migration script, mapping old statuses, data quality report, rollback plan | Sangat tinggi |
| 10 | Hardening | Security review, performance index, queue worker, observability, full regression tests | Tinggi |

## 12. PROMPT SIAP PAKAI UNTUK MULAI REBUILD

```text
Kamu adalah senior Laravel architect dan coding agent. Bangun ulang project "Agenda Online PTPN" dari nol dengan kualitas produksi yang lebih baik. Gunakan PROJECT_INTELLIGENCE_REPORT.md sebagai referensi utama domain, role, workflow, schema legacy, integrasi, route, dan masalah teknis yang harus dihindari.

Target stack:
- Laravel 12, PHP 8.3+, MySQL 8.
- Redis untuk cache/queue/lock/rate-limit jika tersedia.
- Tailwind CSS v4 + Alpine.js atau Livewire/Inertia dengan komponen UI modular.
- Broadcast private/presence channel menggunakan Pusher atau Laravel Reverb.
- Laravel Notifications queued untuk WhatsApp Fonnte, email fallback, dan in-app notification.
- Maatwebsite Excel versi kompatibel Laravel 12 untuk export.

Prinsip arsitektur:
- Jangan menyalin struktur legacy controller gemuk.
- Buat bounded context: IdentityAccess, Documents, Workflow, Notifications, Reporting, CashBankIntegration, ProgrammerTools.
- Gunakan PHP enum untuk RoleCode, DocumentStatus, RoleWorkflowStatus, PaymentStatus, ReturnMode.
- Gunakan Action/Application Service untuk semua transisi workflow.
- Gunakan Policy/Gate untuk setiap aksi sensitif.
- Gunakan baseline migration bersih, bukan mengulang semua migration historis.
- Semua notifikasi dan integrasi eksternal harus queued, retryable, dan logged.
- Semua channel broadcast dokumen harus private/presence channel.

Sprint pertama yang harus dikerjakan:
1. Scaffold Laravel fresh.
2. Buat enum role/status/payment/return.
3. Buat migration baseline untuk users, roles, bagians, dokumens, dokumen_pos, dokumen_prs, dibayar_kepadas, dokumen_statuses, dokumen_role_data, dokumen_activity_logs, document_activities, role_deadline_configs.
4. Buat auth login username/email + password, rate limit, basic profile.
5. Buat RBAC policy/middleware dengan canonical role codes.
6. Buat seed canonical roles dan bagian.
7. Buat test awal untuk role access dan state transition skeleton.

Jangan implement fitur besar sebelum sprint 1 selesai dan test hijau. Setelah sprint 1, lanjut ke Document CRUD dan Workflow Engine sesuai sprint plan di PROJECT_INTELLIGENCE_REPORT.md.
```

## 13. STATE MACHINE TRANSITION TABLE

Catatan penting: workflow legacy memakai dua sumber status sekaligus: kolom `dokumens.status/current_handler/status_pembayaran` dan tabel `dokumen_statuses` per role. Tabel di bawah menggabungkan state legacy yang benar-benar dipakai controller/model.

| Status Asal | Aksi | Status Tujuan | Role yang Boleh Trigger |
|---|---|---|---|
| Dokumen baru | `DokumenController@store` | `draft`, `current_handler=operator` | Operator |
| `draft`, `returned_to_operator`, `sedang diproses`, `sent_to_team_verifikasi`, atau role status Team Verifikasi `rejected` | `DokumenController@sendToTeamVerifikasi` / bulk send | `waiting_reviewer_approval` dan `dokumen_statuses(team_verifikasi)=pending`; sender display `menunggu_approval_verifikasi` | Operator |
| `waiting_reviewer_approval` / `dokumen_statuses(team_verifikasi)=pending` | `InboxController@approve` atau `DokumenController@approveDocument` | `sedang diproses`, `current_handler=team_verifikasi`, `dokumen_statuses(team_verifikasi)=approved` | Team Verifikasi |
| `waiting_reviewer_approval` / `dokumen_statuses(team_verifikasi)=pending` | `InboxController@reject` | `returned_to_operator`, `current_handler=operator`, `return_source=team_verifikasi` | Team Verifikasi |
| `pending_approval_team_verifikasi` dengan `pending_approval_for=team_verifikasi` | `TeamVerifikasiController@acceptDocument` | `sent_to_team_verifikasi`, `current_handler=team_verifikasi` | Team Verifikasi |
| `pending_approval_team_verifikasi` dengan `pending_approval_for=team_verifikasi` | `TeamVerifikasiController@rejectDocument` | `draft`, `current_handler=operator` | Team Verifikasi |
| `sent_to_team_verifikasi` atau `sedang diproses` | `TeamVerifikasiController@setDeadline` | Status dokumen tetap, deadline Team Verifikasi disimpan di `dokumen_role_data` | Team Verifikasi |
| `sedang diproses` dengan `current_handler=team_verifikasi` | `TeamVerifikasiController@parafDokumen` | Status tetap; `tanggal_paraf` dan `pemaraf` terisi | Team Verifikasi |
| `sedang diproses`, `returned_to_department`, atau `returned_to_verifikasi` | `TeamVerifikasiController@sendToNextHandler(next_handler=perpajakan)` | `pending_approval_perpajakan`, `dokumen_statuses(perpajakan)=pending`; setelah approve menjadi `sent_to_perpajakan` | Team Verifikasi |
| `sedang diproses`, `returned_to_department`, atau `returned_to_verifikasi` | `TeamVerifikasiController@sendToNextHandler(next_handler=akutansi)` | `pending_approval_akutansi`, `dokumen_statuses(akutansi)=pending`; setelah approve menjadi `sent_to_akutansi` | Team Verifikasi |
| `sedang diproses`, `returned_to_department`, atau `returned_to_verifikasi` | `TeamVerifikasiController@sendToNextHandler(next_handler=pembayaran)` | `menunggu_di_approve`, `dokumen_statuses(pembayaran)=pending`; setelah approve menjadi `sent_to_pembayaran` | Team Verifikasi |
| `sedang diproses` dengan `current_handler=team_verifikasi` | `TeamVerifikasiController@returnToOperator` | `returned_to_operator`, `current_handler=operator`, `return_source=team_verifikasi` | Team Verifikasi |
| `sedang diproses` dengan `current_handler=team_verifikasi` | `TeamVerifikasiController@returnToDepartment(return_source=perpajakan/akutansi/pembayaran)` | `returned_to_department`, `current_handler=team_verifikasi`, `return_source` sesuai request | Team Verifikasi |
| `sedang diproses` dengan `current_handler=team_verifikasi` | `TeamVerifikasiController@changeDocumentStatus(status=approved)` | `approved_Team Verifikasi` | Team Verifikasi |
| `sedang diproses` dengan `current_handler=team_verifikasi` | `TeamVerifikasiController@changeDocumentStatus(status=rejected)` | `rejected_Team Verifikasi` | Team Verifikasi |
| `pending_approval_perpajakan` / `dokumen_statuses(perpajakan)=pending` | `InboxController@approve` | `sent_to_perpajakan`, `current_handler=perpajakan`, `dokumen_statuses(perpajakan)=approved` | Perpajakan |
| `pending_approval_perpajakan` / `dokumen_statuses(perpajakan)=pending` | `InboxController@reject` | `returned_to_department`, `current_handler=team_verifikasi`, `return_source=perpajakan` | Perpajakan |
| `sent_to_perpajakan` dengan `current_handler=perpajakan` | `DashboardPerpajakanController@setDeadline` | Status tetap; deadline Perpajakan disimpan di `dokumen_role_data` | Perpajakan |
| `sent_to_perpajakan` dengan `current_handler=perpajakan` | `DashboardPerpajakanController@updateDokumen` | Status tetap; data dasar dan data pajak ter-update | Perpajakan |
| `sent_to_perpajakan` dengan `current_handler=perpajakan` | `DashboardPerpajakanController@sendToNext(next_handler=akutansi)` / `sendToAkutansi` | `pending_approval_akutansi`, lalu setelah approve menjadi `sent_to_akutansi`; `tanggal_selesai_verifikasi_pajak=now()` | Perpajakan |
| `sent_to_perpajakan` dengan `current_handler=perpajakan` | `DashboardPerpajakanController@sendToNext(next_handler=pembayaran)` | `menunggu_di_approve`, lalu setelah approve menjadi `sent_to_pembayaran` | Perpajakan |
| `sent_to_perpajakan` dengan `current_handler=perpajakan` | `DashboardPerpajakanController@returnDocument` | `returned_to_verifikasi`, `current_handler=team_verifikasi`, `return_source=perpajakan`; field pajak utama di-reset | Perpajakan |
| `pending_approval_akutansi` / `dokumen_statuses(akutansi)=pending` | `InboxController@approve` | `sent_to_akutansi`, `current_handler=akutansi`, `dokumen_statuses(akutansi)=approved` | Akutansi |
| `pending_approval_akutansi` / `dokumen_statuses(akutansi)=pending` | `InboxController@reject` | `returned_to_department`, `current_handler=perpajakan`, `return_source=akutansi` | Akutansi |
| `sent_to_akutansi` dengan `current_handler=akutansi` | `DashboardAkutansiController@setDeadline` | Status tetap; deadline Akutansi disimpan di `dokumen_role_data` | Akutansi |
| `sent_to_akutansi` dengan `current_handler=akutansi` | `DashboardAkutansiController@updateDokumen` | Status tetap; `nomor_miro`, `tanggal_miro`, dan data dasar ter-update | Akutansi |
| `sent_to_akutansi` dengan `current_handler=akutansi` | `DashboardAkutansiController@sendToPembayaran` | `menunggu_di_approve`, `dokumen_statuses(pembayaran)=pending`; `current_handler` legacy tetap `akutansi` sampai Pembayaran approve | Akutansi |
| `sent_to_akutansi` dengan `current_handler=akutansi` | `DashboardAkutansiController@returnDocument` | `returned_to_verifikasi`, `current_handler=team_verifikasi`, `return_source=akutansi`; `nomor_miro` di-reset | Akutansi |
| `menunggu_di_approve` / `dokumen_statuses(pembayaran)=pending` | `InboxController@approve` | `sent_to_pembayaran`, `current_handler=pembayaran`, `status_pembayaran=siap_dibayar` jika belum ada status pembayaran | Pembayaran |
| `menunggu_di_approve` / `dokumen_statuses(pembayaran)=pending` | `InboxController@reject` | `returned_to_department`, `current_handler=akutansi`, `return_source=akutansi` | Pembayaran |
| `sent_to_pembayaran` dengan `current_handler=pembayaran` | `DashboardPembayaranController@setDeadline` | `sedang diproses`, `deadline_at=now()+deadline_days`, `processed_at=now()` | Pembayaran |
| `sent_to_pembayaran` / `sedang diproses` dengan `current_handler=pembayaran` | `DashboardPembayaranController@updateStatus(status_pembayaran=siap_dibayar)` | Status dokumen tetap; `status_pembayaran=siap_dibayar`, `deadline_at=now()+3 minggu` | Pembayaran |
| `sent_to_pembayaran` / `sedang diproses` dengan `current_handler=pembayaran` | `DashboardPembayaranController@updateStatus(status_pembayaran=sudah_dibayar)` | `completed`, `current_handler=pembayaran`, `status_pembayaran=sudah_dibayar` | Pembayaran |
| `sent_to_pembayaran` / `sedang diproses` dengan `current_handler=pembayaran` | `DashboardPembayaranController@updatePembayaran` dengan `tanggal_dibayar` atau `link_bukti_pembayaran` terisi | `completed`, `status_pembayaran=sudah_dibayar` | Pembayaran |
| `sent_to_pembayaran` / `sedang diproses` dengan `current_handler=pembayaran` | `DashboardPembayaranController@uploadBukti` | Status dokumen tetap; `link_bukti_pembayaran` terisi | Pembayaran |
| Status apa pun sebelum Pembayaran, `status_pembayaran=sudah_dibayar`, `auto_forwarded_at=null` | `AutoForwardDokumenService@forwardToPembayaran` | `sent_to_pembayaran`, `current_handler=pembayaran`, `dokumen_statuses(pembayaran)=approved`, `auto_forwarded_at=now()` | Sistem |

## 14. BUSINESS RULES TERSEMBUNYI

1. Generate `nomor_agenda`
   - Endpoint `DokumenController@nextNomorAgenda` mengambil `Carbon::now()->year`.
   - Query mencari dokumen dengan `nomor_agenda LIKE '%_YYYY'`.
   - Semua hasil di-load, lalu angka sebelum underscore pertama diekstrak dengan `explode('_', nomor_agenda)`.
   - Nomor berikutnya adalah `max(angka_awal)+1`, format akhirnya `{angka}_{tahun}`, contoh `126_2026`.
   - Risiko rebuild: rule ini rawan race condition karena tidak memakai DB lock/sequence, dan hanya mendukung nomor dengan suffix tahun underscore. Operator CSV juga menambahkan suffix `_2026` secara hardcoded jika belum ada.

2. Kapan dokumen boleh dihapus/diedit
   - Delete di `DokumenController@destroy` tidak memiliki guard status/role di method. Method langsung menghapus tracking, PO, PR, penerima, role data, role statuses, activity logs, lalu row `dokumens`. Pembatasan hanya bergantung pada route/middleware/UI.
   - Operator edit full form: harus `current_handler=operator`; status harus `draft`, `returned_to_operator`, `belum_dikirim`, `belum dikirim`, `menunggu_approval_keuangan`, `sent_to_team_verifikasi`, atau role status Team Verifikasi `rejected`. Dokumen dari Bagian yang berada di Operator juga diloloskan.
   - Inline edit global: user role harus cocok dengan `current_handler`, kecuali Team Verifikasi boleh edit saat status berada di tahap verifikasi, Bagian boleh edit `belum_dikirim/returned_to_bidang`, dan Pembayaran boleh edit ketika handler Pembayaran atau status sudah `sent_to_pembayaran/processed_by_pembayaran/processed_by_akutansi`.
   - Team Verifikasi edit: boleh saat `current_handler` `team_verifikasi/verifikasi` atau status `returned_to_verifikasi`; update dapat mereset dokumen returned menjadi `sedang diproses`.
   - Perpajakan edit: hanya saat `current_handler=perpajakan`.
   - Akutansi edit: memakai `DokumenHelper::canEditDocument($dokumen, 'akutansi')`; default harus tidak pending approval, tidak locked, dan `current_handler=akutansi`.
   - Pembayaran edit: aturan khusus; boleh jika `current_handler=pembayaran`, status `sent_to_pembayaran`, computed status `siap_bayar/siap_dibayar/sudah_dibayar`, `status_pembayaran` termasuk siap/sudah dibayar, atau sudah ada `dokumen_role_data(pembayaran).received_at`.

3. Trigger dan kondisi auto-forward
   - `AutoForwardDokumenService@forwardToPembayaran` aktif saat `status_pembayaran='sudah_dibayar'`, biasanya dari update eksternal/raw DB sync.
   - Service skip bila `status_pembayaran` bukan `sudah_dibayar`, `auto_forwarded_at` sudah terisi, atau dokumen sudah di Pembayaran dengan status role Pembayaran `approved`.
   - Posisi dokumen ditentukan dari `current_handler`; fallback ke status role terbaru `approved/pending`; fallback lagi ke `dokumen_role_data.received_at`.
   - Semua role yang belum dilewati dipass-through otomatis: role data dibuat/diisi, role status sender dibuat `approved`, inbox role berikutnya dibuat `pending`, lalu langsung di-approve sebagai `system`.
   - Tahap akhir selalu mengirim dan approve Pembayaran, mengisi `current_handler=pembayaran`, `status=sent_to_pembayaran`, `status_pembayaran=sudah_dibayar`, `tanggal_dibayar`, dan `auto_forwarded_at`.

4. Threshold keterlambatan per role

| Role | Warning | Danger | Cara Hitung |
|---|---:|---:|---|
| Team Verifikasi | 24 jam | 72 jam | `now - dokumen_role_data.received_at`, hanya jika `processed_at IS NULL` dan `current_handler=team_verifikasi` |
| Perpajakan | 24 jam | 72 jam | `now - dokumen_role_data.received_at`, hanya jika `processed_at IS NULL` dan `current_handler=perpajakan` |
| Akutansi | 24 jam | 72 jam | `now - dokumen_role_data.received_at`, hanya jika `processed_at IS NULL` dan `current_handler=akutansi` |
| Pembayaran | 168 jam | 504 jam | `now - dokumen_role_data.received_at`, hanya jika `processed_at IS NULL`, `current_handler=pembayaran`, dan `status_pembayaran` bukan `sudah_dibayar` |

   Dashboard role juga menghitung bucket keterlambatan dengan pola mirip: `<24 jam`, `24-72 jam`, dan `>72 jam`; Pembayaran memakai batas operasional lebih panjang untuk WA, yaitu 1 minggu dan 3 minggu.

5. Cooldown notifikasi WA
   - Konfigurasi `WHATSAPP_NOTIFICATION_COOLDOWN` default 24 jam.
   - Cooldown dicek oleh `WhatsAppNotificationLog::wasRecentlySent(dokumen_id, role_code, message_type, cooldownHours)`.
   - Notifikasi yang sama akan diskip jika ada log sukses untuk dokumen, role, dan message type yang sama dengan `sent_at >= now() - cooldown`.
   - Cooldown dipisahkan untuk tipe `warning` dan `danger`; dokumen yang naik dari warning ke danger bisa mengirim pesan berbeda.

## 15. FIELD-LEVEL ACCESS PER ROLE

Legenda: `R` = read/lihat, `W` = write/update melalui form/controller, `-` = tidak terlihat atau tidak relevan di role tersebut. Beberapa controller legacy lebih permisif daripada UI; tabel ini memakai gabungan form edit dan update controller.

| Field Dokumen | Operator | TV | Perpajakan | Akutansi | Pembayaran |
|---|---|---|---|---|---|
| `nomor_agenda` | W | W | W | W | W |
| `bulan`, `tahun` | W | W | W | W | W |
| `tanggal_masuk` | R | W | W | W | W |
| `bagian`, `nama_pengirim`, `kebun` | W | W | W | W | W |
| `nomor_spp`, `tanggal_spp` | W | W | W | W | W |
| `uraian_spp`, `nilai_rupiah` | W | W | W | W | W |
| `kategori` / `kriteria_cf` | W | W | W | W | W |
| `jenis_dokumen` / `sub_kriteria` | W | W | W | W | W |
| `jenis_sub_pekerjaan` / `item_sub_kriteria` | W | W | W | W | W |
| `jenis_pembayaran` | W | W | W | W | W |
| `dibayar_kepada` / `dibayar_kepada[]` | W | W | W | W | R/W legacy |
| `nomor_po[]`, `nomor_pr[]` | W | W | W | W | W |
| `no_berita_acara`, `tanggal_berita_acara` | W | W | W | W | W |
| `no_spk`, `tanggal_spk`, `tanggal_berakhir_spk` | W | W | W | W | W |
| `tanggal_paraf`, `pemaraf` | R | W | R | R | R |
| `tanggal_selesai_diproses` | R | W via send | R | R | R |
| `npwp` | R | R | W | R | R |
| `status_perpajakan` | R | R | W/implicit | R | R |
| `no_faktur`, `tanggal_faktur` | R | R | W | R | R |
| `tanggal_selesai_verifikasi_pajak` | R | R | W/auto on send | R | R |
| `jenis_pph`, `dpp_pph`, `ppn_terhutang` | R | R | W | R | R |
| `link_dokumen_pajak` | R | R | W | R | R |
| Perpajakan extended: `komoditi_perpajakan`, `alamat_pembeli`, `no_kontrak`, `no_invoice`, `tanggal_invoice`, `dpp_invoice`, `ppn_invoice`, `dpp_ppn_invoice`, `tanggal_pengajuan_pajak`, `dpp_faktur`, `ppn_faktur`, `selisih_pajak`, `keterangan_pajak`, `penggantian_pajak`, `dpp_penggantian`, `ppn_penggantian`, `selisih_ppn` | - | R | W | R | R |
| `nomor_miro`, `tanggal_miro` | R | W legacy | R | W | R |
| `status_pembayaran` | R | R | R | R | W |
| `tanggal_dibayar` | R/import | R | R | R | W |
| `link_bukti_pembayaran` | R | R | R | R | W |
| `catatan_pembayaran` | - | - | - | - | W |
| `deadline_at`, `deadline_days`, `deadline_note` | R | W untuk TV | W untuk Perpajakan | W untuk Akutansi | W untuk Pembayaran |
| `return_source`, `return_reason`, `returned_at` | R | W | W via return | W via return | W via inbox reject |
| `status`, `current_handler`, `pending_approval_for` | R | W via workflow | W via workflow | W via workflow | W via payment workflow |
| `created_by`, CSV metadata (`imported_from_csv`, `csv_import_batch_id`, `csv_imported_at`) | R/implicit | R | R | R | R/implicit |

Implikasi rebuild: field-level access harus dipindahkan ke Policy/Form Request per role. Legacy update controller membiarkan Perpajakan, Akutansi, dan Pembayaran menulis banyak field dasar dokumen, sehingga rebuild perlu memutuskan apakah ini memang kebutuhan bisnis atau hanya kebocoran akibat shared form.

## 16. FORMAT CSV IMPORT

### Operator CSV (`OperatorCsvImportController`)

- Upload menerima `csv`, `txt`, `xlsx`, `xls` sampai 10 MB, tetapi parser yang dipakai tetap `fgetcsv`.
- Header ada pada baris pertama. Jika kolom pertama kosong, kolom tersebut dibuang.
- Header dibersihkan dengan trim whitespace dan menghapus suffix `(Rp)`, `(RP)`, `(BA)`.
- Hanya kolom dalam allowlist yang diproses; kolom lain seperti `Kontrol` diabaikan.

| Header CSV | Wajib | Tipe/Format | Mapping / Validasi |
|---|---|---|---|
| `Agenda` | Ya | string/angka | Menjadi `nomor_agenda`; jika belum mengandung `_2026`, otomatis ditambah suffix `_2026`; duplicate menjadi warning dan bisa diskip |
| `Bulan` | Tidak | nama bulan atau angka bulan | Menjadi `bulan`; prefix seperti `a. Januari` dibersihkan; fallback bulan sekarang |
| `Tahun` | Tidak | tahun | Menjadi `tahun`; fallback tahun sekarang |
| `Kriteria` | Tidak | string | Dipakai sebagai fallback `No SPP`; tidak benar-benar menjadi kategori karena `kategori` default `CAPEX` |
| `No SPP` | Ya | string | Menjadi `nomor_spp`; juga dipakai mengekstrak kode `bagian` dari awalan SPP |
| `Tanggal SPP` | Tidak | date | Menjadi `tanggal_spp`; format `d/m/Y`, `d-m-Y`, `Y-m-d`, variasi dengan jam, atau Carbon parse; fallback `Tanggal Masuk` |
| `Tanggal Masuk` | Tidak | date | Menjadi `tanggal_masuk`; fallback `now()` |
| `Dibayarkan Kepada` | Tidak | string | Menjadi legacy `dibayar_kepada` |
| `Uraian SPP` | Tidak | string | Menjadi `uraian_spp` |
| `Nilai` / `Nilai (Rp)` | Ya | numeric Indonesia | Menjadi `nilai_rupiah`; titik ribuan dihapus, koma desimal dikonversi |
| `No SPK` | Tidak | string | Menjadi `no_spk` |
| `Tanggal SPK` | Tidak | date | Menjadi `tanggal_spk` |
| `Tgl. Akhir SPK` | Tidak | date | Menjadi `tanggal_berakhir_spk` |
| `No Berita Acara` / `No Berita Acara (BA)` | Tidak | string | Menjadi `no_berita_acara` |
| `Tanggal Berita Acara` / `Tanggal Berita Acara (BA)` | Tidak | date | Menjadi `tanggal_berita_acara` |
| `No. PO` | Tidak | string | Menjadi legacy field `NO_PO` pada `dokumens`, bukan relasi `dokumen_pos` |
| `No. Miro/SES` | Tidak | string | Menjadi legacy field `NO_MIRO_SES` |
| `Tanggal Paraf` | Tidak | date | Menjadi `tanggal_paraf` |
| `Pemaraf` | Tidak | string | Menjadi `pemaraf` |
| `Tgl selesai diproses` | Tidak | date | Menjadi `tanggal_selesai_diproses` |
| `Kepala Sub Bagian` | Tidak | string | Menjadi `kepala_sub_bagian` |
| `Status Dokumen` | Tidak | string | Menjadi `status_dokumen_csv` |
| `Tanggal Bayar` | Tidak | date | Menjadi `tanggal_dibayar` |

Default import Operator: `created_by=operator`, `status=draft`, `current_handler=operator`, `imported_from_csv=true`, `csv_import_batch_id` dan `csv_imported_at` jika kolom tersedia. Contoh baris valid:

```csv
Agenda,Bulan,Tahun,Kriteria,No SPP,Tanggal SPP,Tanggal Masuk,Dibayarkan Kepada,Uraian SPP,Nilai,No SPK,Tanggal SPK,Tgl. Akhir SPK,No Berita Acara,Tanggal Berita Acara,No. PO,No. Miro/SES,Tanggal Paraf,Pemaraf,Tgl selesai diproses,Kepala Sub Bagian,Status Dokumen,Tanggal Bayar
126,Mei,2026,5SKH/SPP/01/V/2026,5SKH/SPP/01/V/2026,04/05/2026,04/05/2026,PT Contoh Vendor,Pembayaran pekerjaan contoh,1.250.000,SPK-001,01/05/2026,31/05/2026,BA-001,03/05/2026,PO-001,MIRO-001,04/05/2026,Ibu Yuni,04/05/2026,SKH,Draft,
```

### Pembayaran CSV (`CsvImportController`)

- Upload menerima `csv`/`txt` sampai 10 MB.
- Parser melewati 3 baris summary, memakai baris ke-4 sebagai header, dan data mulai baris ke-5.
- Header hanya di-trim, tidak dinormalisasi ke snake_case.
- Required hard validation: `AGENDA`, `NO SPP`, `NILAI`.

| Header CSV | Wajib | Tipe/Format | Mapping / Validasi |
|---|---|---|---|
| `AGENDA` | Ya | string/angka | Menjadi `nomor_agenda`; duplicate menjadi warning dan bisa diskip |
| `NO SPP` | Ya | string | Menjadi `nomor_spp` |
| `HAL` | Tidak | string | Menjadi `uraian_spp` |
| `NILAI` | Ya | numeric Indonesia | Menjadi `nilai_rupiah` |
| `TGL SPP` | Tidak | date | Menjadi `tanggal_spp`; juga fallback `tanggal_masuk` |
| `TANGGAL MASUK DOKUMEN` | Tidak | date | Fallback untuk `tanggal_masuk` bila `TGL SPP` kosong |
| `KATEGORI` | Tidak | string | Menjadi `kategori`; fallback `CAPEX` |
| `Sub Pekerjaan` | Tidak | string | Menjadi `jenis_dokumen` dan `jenis_sub_pekerjaan`; fallback `Lainnya` untuk `jenis_dokumen` |
| `NO KONTRAK` | Tidak | string | Menjadi `no_kontrak` |
| `TGL. KONTRAK` | Tidak | date | Menjadi `tanggal_kontrak` |
| `NO. BERITA ACARA` / `NO BERITA ACARA` | Tidak | string | Menjadi `no_berita_acara` |
| `TGL. BERITA ACARA` / `TGL BERITA ACARA` | Tidak | date | Menjadi `tanggal_berita_acara` |
| `NO SPK` / `NO. SPK` | Tidak | string | Menjadi `no_spk` |
| `TGL. SPK` / `TGL SPK` | Tidak | date | Menjadi `tanggal_spk` |
| `TGL. BERAKHIR KONTRAK` / `TGL BERAKHIR KONTRAK` | Tidak | date | Menjadi `tanggal_berakhir_spk` |
| `KEBUN` | Tidak | string | Menjadi `kebun` dan `nama_kebuns` |
| `VENDOR` | Tidak | string | Menjadi legacy `dibayar_kepada` dan `_vendor` metadata |
| `TANGGAL BAYAR RAMPUNG`, `TANGGAL BAYAR VI`, `TANGGAL BAYAR V`, `TANGGAL BAYAR IV`, `TANGGAL BAYAR III`, `TANGGAL BAYAR II`, `TANGGAL BAYAR I` | Tidak | date | First non-empty value menjadi `tanggal_dibayar`; jika ada, `status_pembayaran=sudah_dibayar`; jika tidak, `belum_dibayar` |

Default import Pembayaran: `status=sent_to_pembayaran`, `current_handler=pembayaran`, `created_by=csv_import`, `imported_from_csv=true`, `csv_import_batch_id` dan `csv_imported_at` jika kolom tersedia. Contoh struktur valid dengan 3 baris summary:

```csv
Ringkasan Pembayaran,,,,,,,,,,,
Generated,04/05/2026,,,,,,,,,,
Total,,,,,,,,,,,
AGENDA,NO SPP,TGL SPP,KATEGORI,Sub Pekerjaan,HAL,NILAI,KEBUN,VENDOR,NO KONTRAK,TGL. KONTRAK,NO. BERITA ACARA,TGL. BERITA ACARA,NO SPK,TGL. SPK,TGL. BERAKHIR KONTRAK,TANGGAL BAYAR I
126_2026,5SKH/SPP/01/V/2026,04/05/2026,CAPEX,Jasa,Pembayaran pekerjaan contoh,1.250.000,KGM,PT Contoh Vendor,KTR-001,01/05/2026,BA-001,03/05/2026,SPK-001,01/05/2026,31/05/2026,04/05/2026
```

## 17. CANONICAL NAMING MAP

### Status Dokumen

| Legacy / Variant | Canonical Baru yang Disarankan | Catatan |
|---|---|---|
| `draft`, `belum_dikirim`, `belum dikirim` | `DocumentStatus::DRAFT` | Gunakan satu status awal saja |
| `waiting_reviewer_approval`, `menunggu_di_approve`, `pending_approval_team_verifikasi`, `pending_approval_perpajakan`, `pending_approval_akutansi`, `pending_approval_pembayaran` | `DocumentStatus::PENDING_ROLE_APPROVAL` + `pending_role` | Pisahkan status dokumen dan role tujuan |
| `sent_to_team_verifikasi`, `sedang diproses`, `sedang_diproses` | `DocumentStatus::IN_VERIFICATION` | Hilangkan spasi dan duplikasi bahasa |
| `approved_Team Verifikasi`, `approved_ibub`, `approved_verifikasi` | `RoleWorkflowStatus::APPROVED` untuk `team_verifikasi` | Jangan simpan role dalam string status dokumen |
| `rejected_Team Verifikasi`, `rejected_ibub` | `RoleWorkflowStatus::REJECTED` untuk `team_verifikasi` | Alasan masuk `return_reason` |
| `sent_to_perpajakan` | `DocumentStatus::IN_TAX` | Role canonical `tax`/`perpajakan` |
| `sent_to_akutansi` | `DocumentStatus::IN_ACCOUNTING` | Rebuild harus memakai ejaan `akuntansi` |
| `sent_to_pembayaran` | `DocumentStatus::IN_PAYMENT` | Payment stage |
| `returned_to_operator`, `returned_to_ibua` | `DocumentStatus::RETURNED_TO_OPERATOR` | `return_source` wajib enum |
| `returned_to_department`, `returned_to_verifikasi` | `DocumentStatus::RETURNED_TO_VERIFICATION` | Saat ini maknanya campur: kembali ke Team Verifikasi dari role downstream |
| `returned_to_bidang` | `DocumentStatus::RETURNED_TO_REQUESTING_UNIT` | Untuk dokumen Bagian |
| `completed`, `selesai` | `DocumentStatus::COMPLETED` | Gunakan satu terminal state |
| `pending`, `received`, `processing`, `approved`, `rejected`, `completed`, `returned` pada `dokumen_statuses` | `RoleWorkflowStatus::*` | Status role jangan dicampur dengan status dokumen utama |

### Status Pembayaran

| Legacy / Variant | Canonical Baru yang Disarankan | Catatan |
|---|---|---|
| `pending`, `belum_dibayar`, null | `PaymentStatus::UNPAID` | Hindari `pending` ambigu |
| `siap_bayar`, `siap_dibayar` | `PaymentStatus::READY_TO_PAY` | Satu istilah saja |
| `sudah_dibayar`, `SUDAH_DIBAYAR`, `sudah dibayar` | `PaymentStatus::PAID` | Trigger completion/auto-forward |

### Role Codes

| Alias Legacy | Canonical Baru |
|---|---|
| `operator`, `Operator`, `tarapul`, `Ibu Tarapul`, `ibua`, `IbuA`, `ibu a` | `operator` |
| `team_verifikasi`, `verifikasi`, `Team Verifikasi`, `team verifikasi`, `Ibu B`, `Ibu Yuni`, `ibub` | `team_verifikasi` |
| `perpajakan`, `Perpajakan` | `perpajakan` |
| `akutansi`, `Akutansi` | `akuntansi` |
| `pembayaran`, `Pembayaran` | `pembayaran` |
| `bagian`, kode bagian `DPM/SKH/SDM/TEP/KPL/AKN/TAN/PMO` | `requesting_unit` + `bagian_code` |
| `admin`, `superadmin`, `programmer`, `viewer`, `guest` | Pertahankan sebagai role sistem terpisah jika masih dibutuhkan |

### Nama Tabel

| Nama Legacy | Canonical Baru yang Disarankan | Catatan |
|---|---|---|
| `dokumens` | `documents` | Gunakan bahasa Inggris konsisten atau pilih bahasa Indonesia penuh; rekomendasi rebuild: Inggris |
| `dokumen_pos` | `document_purchase_orders` | Relasi nomor PO |
| `dokumen_prs` | `document_purchase_requisitions` | Relasi nomor PR |
| `dibayar_kepadas` | `document_payees` | Nama legacy tidak natural |
| `dokumen_statuses` | `document_role_statuses` | Status per role, bukan status dokumen utama |
| `dokumen_role_data` | `document_role_timelines` atau `document_role_assignments` | Simpan received/processed/deadline per role |
| `dokumen_activity_logs` | `document_activity_logs` | Audit workflow dokumen |
| `document_activities` dan `document_trackings` | Gabungkan ke `document_activity_logs` | Hindari tiga sumber audit |
| `bagians` | `departments` atau `requesting_units` | Sesuaikan domain final |
| `whatsapp_notification_logs` | `notification_logs` dengan `channel=whatsapp` | Bisa dipakai lintas channel |
| `dokumen_auto_forward_queue` | `document_auto_forward_jobs` | Status queue lebih jelas |

### Nama Field

| Field Legacy / Typo | Canonical Baru yang Disarankan | Catatan |
|---|---|---|
| `akutansi` dalam field/route/status | `akuntansi` | Perbaiki ejaan di seluruh domain |
| `nomor_mirror` | `nomor_miro` | Typo terdeteksi di filter Akutansi |
| `NO_PO` | `nomor_po` atau relasi `document_purchase_orders.nomor_po` | Legacy uppercase dari CSV |
| `NO_MIRO_SES` | `nomor_miro_ses` | Pisahkan dari `nomor_miro` jika memang beda konsep |
| `nama_kebuns` | `kebun_name` atau `kebun` | Hindari plural untuk single value |
| `dibayar_kepada` | `payee_name` atau relasi `document_payees` | Saat ini ada field legacy dan tabel relasi |
| `kriteria_cf`, `kategori` | `category_id` dan `category_name_snapshot` | Hindari campur ID cash_bank dan nama snapshot |
| `sub_kriteria`, `jenis_dokumen` | `document_type_id` dan `document_type_name_snapshot` | Konsisten dengan kategori |
| `item_sub_kriteria`, `jenis_sub_pekerjaan` | `work_item_id` dan `work_item_name_snapshot` | Konsisten dengan kategori |
| `status_dokumen_csv` | `csv_document_status` | Metadata CSV, bukan status workflow canonical |
| `sent_to_ibub_at`, `approved_by_team_verifikasi_at` | `verified_at` atau timeline event | Jangan tambah kolom milestone per role bila ada event log |
| `returned_to_Operator_at`, `returned_from_perpajakan_at`, `returned_from_akutansi_at`, `department_returned_at`, `bidang_returned_at` | `returned_at` + `return_source` + `return_target` | Gunakan model return terpadu |
| `alasan_pengembalian`, `department_return_reason`, `bidang_return_reason`, `return_reason` | `return_reason` | Satu field canonical |
| `target_department`, `target_bidang` | `return_target` | Enum/foreign key target |
| `pending_approval_for` | `pending_role` | Enum role |
| `tanggal_selesai_diproses` | `verification_completed_at` atau role timeline `processed_at` | Hindari duplikasi dengan `dokumen_role_data.processed_at` |
| `tanggal_paraf`, `pemaraf` | `signed_at`, `signed_by` | Lebih generik |
| `tanggal_dibayar` | `paid_at` | Payment domain |
| `link_bukti_pembayaran` | `payment_proof_url` | Validasi URL |
| `catatan_pembayaran` | `payment_note` | Payment domain |

# Desain: Backfill Tanggal Bayar Cash Bank → Agenda Online

- **Tanggal**: 2026-07-15
- **Proyek pemilik command**: Agenda Online (`agenda_2026`)
- **Status**: Disetujui untuk implementasi (menunggu review spec)

## 1. Latar Belakang & Masalah

Pada data bank keluar di proyek **Cash Bank**, kolom `tanggal` sesungguhnya adalah
**tanggal pembayaran** dokumen. Nilai ini seharusnya juga muncul sebagai
`tanggal_dibayar` pada dokumen yang sama di **Agenda Online**.

Ditemukan bahwa **sinkronisasi dua arah live sudah ada dan aktif**, jadi tidak perlu
dibangun ulang:

- **Agenda → Cash Bank**: `App\Observers\DokumenObserver::updated()` mendeteksi
  perubahan `tanggal_dibayar` (termasuk di `DokumenSyncService::SYNCABLE_FIELDS`) →
  men-dispatch `App\Jobs\SyncDokumenToCashBankJob` → `DokumenSyncService::pushToCashBank()`
  memetakan `tanggal_dibayar → tanggal` dan meng-update `bank_keluars` yang cocok.
  Observer terdaftar di `AppServiceProvider` (`Dokumen::observe(DokumenObserver::class)`).
  Ada pula fallback scheduler `dokumen:sync-cashbank --since="5 minutes ago"` tiap menit.
- **Cash Bank → Agenda**: `BankKeluarController` (`store`, `update`, import Excel/CSV,
  dan "tarik otomatis") menulis `tanggal_dibayar` ke Agenda melalui koneksi
  `mysql_agenda_online`.

**Celah yang tersisa (akar masalah nyata):** kedua arah sync itu **hanya menyala saat
ada aksi edit/perubahan**, dan fallback scheduler hanya melihat `updated_at >= 5 menit
lalu`. Baris historis — yang tanggalnya sudah ada di Cash Bank jauh sebelum mesin sync
dibuat dan tak pernah disentuh lagi — tidak akan pernah ikut tersinkron. Akibatnya ada
dokumen Agenda dengan `tanggal_dibayar` kosong padahal `bank_keluars` terkait sudah
punya `tanggal`.

## 2. Tujuan

Menutup celah data historis dengan **satu command rekonsiliasi sekali jalan** yang
mengisi `tanggal_dibayar` dokumen Agenda dari tanggal bank keluar Cash Bank.

## 3. Keputusan (dikonfirmasi pengguna)

1. **Arah tunggal**: hanya **isi Agenda dari Cash Bank**. Tidak ada arah sebaliknya
   dalam backfill ini.
2. **Isi hanya bila kosong**: hanya update dokumen yang `tanggal_dibayar`-nya kosong.
   Tidak pernah menimpa tanggal yang sudah ada di Agenda.
3. **Konflik** (Agenda sudah punya tanggal, Cash Bank beda): **dilewati** dan dicatat
   ke laporan/`sync_logs` untuk ditinjau manual — tidak ditimpa.
4. **Tanggal yang dipakai**: bila satu dokumen punya banyak baris `bank_keluars`
   (mis. pembayaran split) dengan tanggal berbeda, dipakai tanggal **TERAWAL** (`MIN`).
5. **Lingkup field**: `tanggal_dibayar` **dan** `status_pembayaran='sudah_dibayar'`
   (revisi pasca-review, keputusan user). Field lain (nilai, kategori, dll.) tidak
   disentuh. Catatan: perubahan `status_pembayaran` via raw write memicu MySQL
   trigger auto-forward (`ProcessAutoForwardQueue`) di produksi sehingga dokumen
   historis mengalir ke Pembayaran — ini DITERIMA sebagai keputusan bisnis.
6. **Pencocokan komposit** (revisi pasca-review): `bank_keluars.agenda_tahun`
   produksi campuran — polos (`'0004'`) untuk entri manual, komposit (`'0004_2026'`)
   untuk entri impor. `nomor_agenda` di `dokumens` unik komposit `(nomor_agenda,
   created_by)` sehingga bisa berulang. Matcher: komposit dicocokkan ke
   (nomor_agenda + tahun); polos hanya bila unik (bila >1 → ambigu, dilewati).
6. **Eksekusi produksi bertahap**: jalankan `--dry-run` (read-only) dulu untuk
   memperlihatkan dampak, lalu **berhenti minta izin eksplisit** sebelum menulis ke
   DB produksi.

## 4. Rancangan

### 4.1 Lokasi
Command dibangun di **Agenda Online**, yang sudah memiliki koneksi `cash_bank_new` ke
DB Cash Bank, tabel `sync_logs`, model `SyncLog`, dan pola command sync yang serupa
(`dokumen:sync-cashbank`).

### 4.2 Command
```
php artisan dokumen:backfill-tanggal-bayar [--dry-run] [--limit=N]
```

- `--dry-run`: hanya melaporkan apa yang akan berubah, **tidak menulis** apa pun.
- `--limit=N` (opsional): batasi jumlah dokumen yang diproses (untuk uji bertahap).

### 4.3 Sumber data & pencocokan
- Baca dari koneksi `cash_bank_new`, tabel `bank_keluars`, hanya baris `tanggal`
  tidak null/kosong.
- Kunci pencocokan ke dokumen Agenda (urutan prioritas):
  1. `bank_keluars.dokumen_id` → `dokumens.id`
  2. fallback: `bank_keluars.agenda_tahun` **atau** `bank_keluars.no_agenda`
     → `dokumens.nomor_agenda`
- Untuk tiap dokumen target, hitung **tanggal terawal** `MIN(tanggal)` di antara semua
  `bank_keluars` yang cocok dan bertanggal.

### 4.4 Algoritma per dokumen
1. Resolusikan dokumen Agenda dari baris/grup `bank_keluars`.
2. Jika dokumen tidak ditemukan → catat kategori `tidak_ketemu`, lanjut.
3. Jika `dokumens.tanggal_dibayar` **sudah terisi**:
   - jika sama dengan `MIN(tanggal)` CB → `dilewati_sama`;
   - jika beda → `konflik_dilewati` (catat kedua nilai untuk tinjauan).
4. Jika `dokumens.tanggal_dibayar` **kosong** dan ada `MIN(tanggal)`:
   - mode normal: set `tanggal_dibayar = MIN(tanggal)` → `diisi`;
   - mode `--dry-run`: catat `akan_diisi`, tidak menulis.

### 4.5 Cara menulis (hindari sync balik berulang)
Update dokumen memakai **`updateQuietly()`** (atau set flag `_syncing = true`) agar
`DokumenObserver` tidak memicu `SyncDokumenToCashBankJob` yang mendorong nilai yang
sama kembali ke Cash Bank. Nilai sumbernya memang berasal dari Cash Bank, jadi push
balik tidak diperlukan.

> Catatan konsekuensi: karena memakai `updateQuietly()`, hook `DokumenObserver::saving()`
> yang biasanya meng-otomatiskan `status_pembayaran = 'sudah_dibayar'` **tidak** ikut
> jalan. Ini sesuai keputusan lingkup (hanya `tanggal_dibayar`). Bila pengguna kelak
> ingin `status_pembayaran` ikut terisi untuk data historis, itu sub-tugas terpisah.

### 4.6 Pencatatan & keluaran
- Ringkasan di akhir command: total diperiksa, `diisi`/`akan_diisi`, `dilewati_sama`,
  `konflik_dilewati`, `tidak_ketemu`.
- Tulis entri ke `sync_logs` (`direction = 'cb_to_ao_backfill'`) untuk `diisi` dan
  `konflik_dilewati`, memakai model `SyncLog` yang sudah ada.
- Untuk `konflik_dilewati`, tampilkan daftar (nomor_agenda, tanggal Agenda, tanggal CB)
  agar mudah ditinjau.

### 4.7 Kinerja
- Proses `bank_keluars` secara ter-agregasi/`chunk` untuk menghindari memuat ribuan
  baris sekaligus (mengikuti catatan performa yang sudah ada di kode sync).

## 5. Non-Tujuan (di luar lingkup)

- Tidak membangun trigger sync baru — dua arah live sudah ada dan tetap dipakai untuk
  edit dari-sekarang.
- Tidak ada arah backfill sebaliknya (Agenda → Cash Bank).
- Tidak menimpa `tanggal_dibayar` Agenda yang sudah terisi.
- Tidak menyentuh field selain `tanggal_dibayar` dan `status_pembayaran`.
- Tidak menjadwalkan command ini (sekali jalan; boleh diulang kapan pun secara manual
  karena idempoten untuk baris yang sudah terisi).

## 6. Risiko & Mitigasi

| Risiko | Mitigasi |
|---|---|
| Menulis salah ke DB produksi | `--dry-run` wajib dulu; tulis hanya setelah izin eksplisit pengguna. |
| Salah pencocokan via `nomor_agenda` (duplikat/format beda) | Prioritaskan `dokumen_id`; laporkan pencocokan `nomor_agenda` yang menghasilkan >1 dokumen sebagai anomali, jangan diisi. |
| Menimpa data Agenda yang benar | Aturan "isi hanya bila kosong" + konflik dilewati. |
| Push balik berulang ke Cash Bank | Tulis dengan `updateQuietly()`/guard `_syncing`. |

## 7. Kriteria Selesai

- Command `dokumen:backfill-tanggal-bayar` berjalan tanpa error.
- `--dry-run` menghasilkan laporan akurat tanpa perubahan DB.
- Setelah run sungguhan (dengan izin), dokumen Agenda yang tadinya kosong dan punya
  pasangan bank keluar bertanggal kini terisi `tanggal_dibayar` = tanggal terawal CB.
- Dokumen yang sudah punya tanggal tidak berubah; konflik terdaftar di laporan.
- Di-commit dengan pesan Bahasa Indonesia; ringkasan + cara uji ditampilkan.

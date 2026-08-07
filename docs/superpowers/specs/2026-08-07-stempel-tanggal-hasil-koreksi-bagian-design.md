# Stempel Otomatis `tanggal_hasil_koreksi_bagian` — Rancangan

**Tanggal:** 2026-08-07
**Status:** disetujui user

---

## 1. Masalah

Kolom **"Tgl Hasil Koreksi Bagian"** sudah lama terdaftar di katalog
(`config/document_columns.php:51`) sehingga muncul sebagai pilihan kolom di tabel,
**tetapi kolom databasenya tidak pernah dibuat** — nol migrasi. Akibatnya selalu
menampilkan `-` dan mustahil terisi.

Ini kondisi yang persis sama dengan `tanggal_kembali_ke_bagian` sebelum diperbaiki
2026-08-06. Kolom itu kini hidup dan terisi otomatis saat Tim Verifikasi mengembalikan
dokumen ke Bagian. Yang belum tercatat adalah **sisi baliknya**: kapan dokumen hasil
revisi kembali dari Bagian ke Tim Verifikasi.

Tanpa keduanya, lama Bagian mengoreksi dokumen tidak bisa diukur sama sekali.

## 2. Lingkup

**Termasuk:** membuat kolom databasenya, mengisinya otomatis saat dokumen diterima
kembali dari Bagian, dan memastikan nilainya sampai ke tabel Verifikasi.

**Tidak termasuk:** mengubah alur pengembalian, menampilkan selisih waktu / durasi
koreksi, notifikasi, dan pengisian retroaktif (lihat §8).

## 3. Keputusan aturan bisnis

| # | Pertanyaan | Keputusan user |
|---|---|---|
| 1 | Dokumen dikoreksi lebih dari sekali — simpan tanggal yang mana? | **Selalu ditimpa yang terbaru.** Kolom menunjuk siklus koreksi TERAKHIR |

Alasan yang mendasari pilihan itu: kolom kembarannya `tanggal_kembali_ke_bagian` juga
ditimpa tiap pengembalian. Menyimpan "yang pertama" di salah satu kolom saja membuat
kedua kolom menunjuk siklus yang berbeda — terbaca membingungkan di baris yang sama.

## 4. Titik stempel

**Satu baris**, di dalam `$dokumen->update([...])` yang sudah ada pada
`App\Http\Controllers\DocumentHandlerController::receiveBackFromBagian()`:

```php
'tanggal_hasil_koreksi_bagian' => now(),
```

**Nol logika gerbang baru.** Gerbangnya sudah ada di `update()` dan sudah benar —
`$canReceiveReturnedBagian` menuntut **ketiganya sekaligus**:

```php
$canReceiveReturnedBagian = $userRole === 'team_verifikasi'
    && $targetHandler === 'team_verifikasi'
    && $dokumen->status === 'returned_to_bidang';
```

Karena syarat ketiga, **forward biasa operator→verifikasi tidak akan ikut terstempel**:
jalur itu jatuh ke `moveDirectlyToTeamVerifikasi()` yang berbeda. Ini yang membuat
kolomnya bermakna — kalau setiap perpindahan ke Verifikasi ikut mengisi, angkanya tak
menandakan koreksi apa pun.

### 4.1 Hanya ada satu pintu — sudah diverifikasi

Berbeda dari `tanggal_kembali_ke_bagian` yang punya **dua** pintu kirim
(`returnDirectlyToBagian` dan `TeamVerifikasiController::returnToBidang`) sehingga
keduanya harus mengisi, pintu **terima-balik** hanya satu.

Halaman "Pengembalian Ke Bagian" (`returns.verifikasi.*`) hanya punya jalur kirim;
rute `returns.verifikasi.restore-from-bidang` sudah dihapus 2026-07-25 karena
method `restoreFromBidang` tidak pernah ada.

**Konsekuensi untuk pemelihara berikutnya:** bila kelak ditambahkan pintu terima-balik
kedua, stempel ini WAJIB ikut disalin ke sana — atau lebih baik, diekstrak seperti
`DocumentReturnNotifier`. Jangan biarkan satu pintu mengisi dan pintu lain tidak;
kolomnya akan kosong separuh waktu tanpa ada yang tahu sebabnya.

## 5. Yang menyertainya

### 5.1 Migrasi

Berkas baru, meniru `2026_08_06_100000_add_tanggal_kembali_ke_bagian_to_dokumens.php`:
idempoten dengan guard `Schema::hasColumn` (CLAUDE.md aturan 6), tipe `timestamp`
nullable, `->after('tanggal_kembali_ke_bagian')` supaya kedua kolom bersebelahan di
skema.

### 5.2 Model `Dokumen`

Tambahkan `'tanggal_hasil_koreksi_bagian'` ke `$fillable` dan ke `$casts` sebagai
`'datetime'`. Keduanya perlu: tanpa `$fillable`, `update()` mengabaikannya diam-diam;
tanpa cast, `formatDates()` jatuh ke jalur parse defensif alih-alih memformat objek
Carbon.

### 5.3 `buildVerifikasiQuery()`

Tambahkan `'dokumens.tanggal_hasil_koreksi_bagian'` ke daftar select **eksplisit** di
`App\Http\Controllers\TeamVerifikasiController` (tepat setelah
`'dokumens.tanggal_kembali_ke_bagian'`, baris ~227).

Tanpa ini database terisi tapi sel tetap menampilkan `-`. Bukan kekhawatiran teoretis —
persis bug yang terjadi dan diperbaiki 2026-08-06 untuk kolom kembarannya, karena daftar
select eksplisit membuat kolom yang tak disebut sampai ke DTO sebagai `null`.

Query role lain tidak memakai daftar select eksplisit, jadi kolomnya ikut otomatis.

### 5.4 `NON_EDITABLE_FIELDS`

Tambahkan `'tanggal_hasil_koreksi_bagian'` ke konstanta di
`public/js/document-tabulator.js:107`.

Kolom ini diisi otomatis. Bila selnya tampak bisa diedit, user akan mengetik lalu
ditolak server — persis keluhan yang baru diperbaiki di kolom lain.

## 6. Dua test yang akan pecah — sudah diramalkan komentarnya sendiri

`tests/Unit/OperatorDocumentRowTest.php` punya dua test yang memakai
`tanggal_hasil_koreksi_bagian` **justru karena** kolom itu belum ber-cast:

- `test_dates_kolom_non_cast_diparse_defensif`
- `test_dates_kolom_non_cast_tak_terparse_jadi_strip`

Komentarnya menulis: *"Bila kelak tanggal_hasil_koreksi_bagian ikut dibuatkan kolom,
test ini harus pindah lagi ke kolom lain yang benar-benar non-cast."*

**Masalahnya: tidak ada tempat pindah lagi.** Pemeriksaan seluruh peta `formatDates()`
(`App\Support\DocumentRow:144-157`) terhadap `$casts` model menemukan
`tanggal_hasil_koreksi_bagian` adalah **kolom tanggal non-cast yang terakhir tersisa**.

Karena itu kedua test **ditulis ulang** memakai `setRawAttributes()`, yang menyuntik
nilai mentah langsung ke atribut melewati cast. Jalur parse defensif tetap terjaga, dan
tesnya berhenti bergantung pada kebetulan adanya kolom yang lupa di-cast — sesuatu yang
akan hilang lagi pada migrasi berikutnya.

## 7. Pengujian

Berkas baru `tests/Feature/TanggalHasilKoreksiBagianTest.php`, meniru struktur
`TanggalKembaliKeBagianTest` (termasuk polyfill SQLite untuk `regexp`/`substring_index`
yang dipakai `ORDER BY nomor_agenda` di `buildVerifikasiQuery()`).

| # | Test | Yang dijaga |
|---|---|---|
| 1 | Kolom `tanggal_hasil_koreksi_bagian` benar-benar ada di skema | Migrasi jalan, bukan no-op diam-diam |
| 2 | Terima-balik lewat dropdown (status `returned_to_bidang` → target `team_verifikasi`) mengisi stempel | Perilaku inti |
| 3 | Forward biasa operator→verifikasi (status normal) **tidak** mengisi stempel | Gerbangnya menggigit — ini yang membuat kolomnya bermakna |
| 4 | Siklus koreksi kedua **menimpa** stempel pertama | Keputusan §3 |
| 5 | Nilai ikut terkirim di `documents.verifikasi.data` dan terformat `d/m/Y H:i` | Daftar select eksplisit tidak melewatkannya |
| 6 | Kolom terdaftar di `NON_EDITABLE_FIELDS` | Sel tidak tampak bisa diedit |

Tiap assertion dibuktikan menggigit sesuai CLAUDE.md aturan 8.

Test 4 harus memakai stempel awal yang **jelas berbeda** dan memastikan nilainya
benar-benar berubah — bukan sekadar `assertNotNull`, yang akan tetap hijau meski
penimpaan tidak terjadi.

## 8. Yang sengaja TIDAK dibuat

- **Pengisian retroaktif.** Dokumen yang sudah melewati siklus koreksi sebelum migrasi
  ini akan bernilai kosong selamanya. Tidak bisa di-backfill: waktunya memang tak pernah
  tercatat di kolom manapun, dan `activity_logs` hanya terisi untuk 2 dari 5.719 dokumen
  (temuan 2026-08-06) sehingga bukan sumber yang bisa dipercaya. Kolom mulai berisi sejak
  deploy.
- **Kolom durasi koreksi** (selisih `tanggal_kembali_ke_bagian` → kolom baru ini). Bisa
  dihitung dari dua kolom yang sudah ada; menyimpannya sebagai kolom ketiga = data
  turunan yang bisa basi.
- **Notifikasi saat dokumen kembali.** Tidak diminta, dan Tim Verifikasi-lah yang
  menekan tombolnya — mereka sudah tahu.

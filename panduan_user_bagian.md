# Buku Panduan User Bagian
## Sistem Agenda Online PTPN

---

## Daftar Isi

1. [Pendahuluan](#1-pendahuluan)
2. [Login ke Sistem](#2-login-ke-sistem)
3. [Dashboard Bagian](#3-dashboard-bagian)
4. [Mengelola Dokumen](#4-mengelola-dokumen)
5. [Membuat Dokumen Baru](#5-membuat-dokumen-baru)
6. [Mengedit Dokumen](#6-mengedit-dokumen)
7. [Mengirim Dokumen](#7-mengirim-dokumen)
8. [Tracking Dokumen](#8-tracking-dokumen)
9. [Tips dan FAQ](#9-tips-dan-faq)

---

## 1. Pendahuluan

### 1.1 Tentang Sistem

Sistem Agenda Online PTPN adalah aplikasi berbasis web untuk mengelola dokumen pembayaran dari bagian/unit kerja hingga proses pembayaran selesai. Sebagai user **Bagian**, Anda bertanggung jawab untuk:

- Membuat dokumen baru (input data SPP)
- Mengirim dokumen ke Operator Keuangan
- Melacak status dokumen yang telah dikirim

### 1.2 Alur Kerja Dokumen

```
BAGIAN → OPERATOR → TEAM VERIFIKASI → PERPAJAKAN/AKUTANSI → PEMBAYARAN
```

Sebagai user Bagian, dokumen yang Anda buat akan melalui alur di atas hingga selesai dibayar.

---

## 2. Login ke Sistem

### 2.1 Cara Login

1. Buka browser (Chrome/Firefox/Edge)
2. Akses URL sistem: `https://[alamat-server]/login`
3. Masukkan **Username** dan **Password** yang telah diberikan
4. Klik tombol **Login**

### 2.2 Logout

Untuk keluar dari sistem:
1. Klik nama user di pojok kanan atas
2. Pilih **Logout**

---

## 3. Dashboard Bagian

Setelah login, Anda akan melihat halaman Dashboard Bagian.

### 3.1 Statistik Dokumen

Di bagian atas dashboard terdapat 4 kartu statistik:

| Kartu | Warna | Keterangan |
|-------|-------|------------|
| **Total Dokumen** | Hijau Tua | Jumlah seluruh dokumen yang Anda buat |
| **Belum Dikirim** | Kuning | Dokumen yang masih draft/belum dikirim |
| **Terkirim** | Biru | Dokumen yang sudah dikirim ke Operator |
| **Selesai** | Hijau | Dokumen yang sudah selesai dibayar |

### 3.2 Menu Quick Action

Terdapat 3 shortcut menu untuk akses cepat:

| Menu | Fungsi |
|------|--------|
| **Daftar Dokumen** | Melihat semua dokumen Anda |
| **Buat Dokumen** | Membuat dokumen baru |
| **Tracking** | Melacak status dokumen |

### 3.3 Dokumen Terbaru

Tabel yang menampilkan beberapa dokumen terbaru dengan informasi:
- Nomor Agenda
- Nomor SPP
- Uraian
- Nilai
- Status

---

## 4. Mengelola Dokumen

### 4.1 Membuka Daftar Dokumen

1. Dari Dashboard, klik menu **Daftar Dokumen**
2. Atau klik menu **Dokumen** di sidebar

### 4.2 Memahami Tabel Dokumen

Tabel dokumen menampilkan seluruh dokumen yang Anda buat dengan kolom-kolom berikut:

| Kolom | Keterangan |
|-------|------------|
| **No** | Nomor urut |
| **Nomor Agenda** | Nomor agenda dokumen (otomatis) |
| **Nomor SPP** | Nomor Surat Permintaan Pembayaran |
| **Nilai Rupiah** | Nominal pembayaran |
| **Status** | Status pengiriman dokumen |
| **Aksi** | Tombol-tombol aksi |

### 4.3 Filter dan Pencarian

#### Pencarian Teks
- Gunakan kotak pencarian untuk mencari berdasarkan nomor agenda, SPP, atau uraian
- Ketik kata kunci, lalu klik **Filter**

#### Filter Tahun
- Pilih tahun dari dropdown **Semua Tahun**
- Pilih tahun yang diinginkan (2024, 2025, dst)

#### Filter Status
- Pilih status dari dropdown **Semua Status**
- Pilihan status:
  - **Belum Dikirim** - Dokumen draft
  - **Menunggu Approve** - Dokumen menunggu persetujuan Operator
  - **Terkirim** - Dokumen sudah diproses lebih lanjut
  - **Belum Siap Dibayar** - Masih dalam proses verifikasi
  - **Siap Dibayar** - Sudah sampai di bagian Pembayaran
  - **Sudah Dibayar** - Proses selesai

### 4.4 Kustomisasi Kolom Tabel

Anda dapat memilih kolom mana saja yang ditampilkan:

1. Klik tombol **Kustomisasi Kolom Tabel** (warna hijau muda)
2. Centang/uncentang kolom yang ingin ditampilkan
3. Klik **Simpan**

Kolom yang tersedia:
- Nomor Agenda
- Nomor SPP
- Tanggal SPP
- Tanggal Masuk
- Nilai Rupiah
- Uraian SPP
- Kebun
- Nama Pengirim
- Jenis Pembayaran
- Status
- Status Pembayaran
- Umur Dokumen

### 4.5 Tombol Aksi

Setiap baris dokumen memiliki tombol aksi yang berbeda tergantung status:

#### Dokumen Belum Dikirim:
| Tombol | Icon | Fungsi |
|--------|------|--------|
| **Edit** | ✏️ | Mengubah data dokumen |
| **Kirim** | ✈️ | Mengirim ke Operator |
| **Hapus** | 🗑️ | Menghapus dokumen |

#### Dokumen Sudah Dikirim:
| Tombol | Icon | Fungsi |
|--------|------|--------|
| **Tracking** | 🛤️ | Melihat posisi dokumen |

### 4.6 Melihat Detail Dokumen

- Klik baris dokumen manapun untuk membuka popup detail
- Popup menampilkan semua informasi lengkap dokumen

### 4.7 Pagination

Di bagian bawah tabel:
- Pilih jumlah baris per halaman: 10, 25, 50, atau 100
- Gunakan navigasi halaman untuk berpindah halaman

---

## 5. Membuat Dokumen Baru

### 5.1 Membuka Form Tambah Dokumen

1. Klik tombol **Buat Dokumen Baru** di Dashboard, atau
2. Klik tombol **Buat Dokumen** di halaman Daftar Dokumen

### 5.2 Mengisi Form Dokumen

Form dokumen terdiri dari beberapa bagian:

#### Bagian 1: Input Dokumen Baru

| Field | Keterangan | Wajib |
|-------|------------|-------|
| **Bagian** | Terisi otomatis sesuai login Anda | - |
| **Nama Pengirim Dokumen** | Nama orang yang mengirim dokumen fisik | Tidak |
| **Nomor SPP** | Nomor Surat Permintaan Pembayaran | **Ya** |
| **Tanggal SPP** | Tanggal surat SPP dibuat | Tidak |
| **Uraian SPP** | Deskripsi/keperluan pembayaran | Tidak |

> **Catatan:** Format Nomor SPP contoh: `123/M/SPP/13/XII/2025`

#### Bagian 2: Vendor/Dibayar Kepada

| Field | Keterangan |
|-------|------------|
| **Vendor/Dibayar Kepada** | Nama vendor/penerima pembayaran |

- Klik tombol **+** untuk menambah vendor lainnya
- Klik tombol **−** untuk menghapus vendor

#### Bagian 3: Nilai Rupiah

| Field | Keterangan |
|-------|------------|
| **Nilai Rupiah** | Nominal pembayaran (angka saja) |
| **Ejaan Nilai Rupiah** | Terisi otomatis (terbilang) |

> **Tips:** Ketik angka seperti `120000000`, sistem akan otomatis format menjadi `120.000.000` dan mengisi ejaan "seratus dua puluh juta rupiah"

#### Bagian 4: Kriteria dan Jenis Pembayaran

| Field | Keterangan |
|-------|------------|
| **Kriteria CF** | Kategori kriteria (pilih dari dropdown) |
| **Sub Kriteria** | Sub kategori (muncul setelah pilih Kriteria CF) |
| **Item Sub Kriteria** | Detail item (muncul setelah pilih Sub Kriteria) |
| **Jenis Pembayaran** | Jenis/metode pembayaran |

#### Bagian 5: Kebun

| Field | Keterangan |
|-------|------------|
| **Kebun** | Unit/lokasi kebun terkait dokumen |

Pilihan kebun meliputi: Region Office, Gunung Meliau, Sungai Dekan, Rimba Belian, dan lainnya.

#### Bagian 6: SPK (Surat Perjanjian Kontrak)

| Field | Keterangan |
|-------|------------|
| **No SPK** | Nomor surat perjanjian kontrak |
| **Tanggal SPK** | Tanggal SPK dibuat |
| **Tanggal Berakhir SPK** | Tanggal berakhirnya SPK |

#### Bagian 7: BA (Berita Acara)

| Field | Keterangan |
|-------|------------|
| **No Berita Acara** | Nomor berita acara |
| **Tanggal Berita Acara** | Tanggal berita acara |

#### Bagian 8: PO/PR (Purchase Order / Purchase Request)

| Field | Keterangan |
|-------|------------|
| **Nomor PO** | Nomor Purchase Order |
| **Nomor PR** | Nomor Purchase Request |

- Klik tombol **+** untuk menambah nomor PO/PR lainnya
- Klik tombol **−** untuk menghapus

### 5.3 Tombol Aksi Form

| Tombol | Fungsi |
|--------|--------|
| **Auto Isi** | Mengisi form dengan data contoh (untuk testing) |
| **Reset** | Mengosongkan semua field |
| **Simpan dokumen** | Menyimpan dokumen sebagai draft |

### 5.4 Setelah Menyimpan

- Dokumen tersimpan dengan status **Belum Dikirim**
- Anda akan diarahkan ke halaman Daftar Dokumen
- Dokumen dapat diedit atau dihapus selama belum dikirim

---

## 6. Mengedit Dokumen

### 6.1 Syarat Edit Dokumen

- **Hanya dokumen dengan status "Belum Dikirim"** yang dapat diedit
- Dokumen yang sudah dikirim tidak dapat diubah

### 6.2 Cara Mengedit

1. Buka halaman **Daftar Dokumen**
2. Cari dokumen yang ingin diedit
3. Klik tombol **Edit** (ikon pensil) di kolom Aksi
4. Ubah data yang diperlukan
5. Klik **Simpan dokumen**

### 6.3 Catatan Penting

- Perubahan akan langsung tersimpan
- Nomor Agenda tidak dapat diubah (otomatis dari sistem)
- Pastikan data sudah benar sebelum mengirim dokumen

---

## 7. Mengirim Dokumen

### 7.1 Cara Mengirim Dokumen

1. Buka halaman **Daftar Dokumen**
2. Cari dokumen dengan status "Belum Dikirim"
3. Klik tombol **Kirim** (ikon pesawat kertas)
4. Akan muncul popup konfirmasi
5. Klik **Ya, Kirim** untuk mengkonfirmasi

### 7.2 Setelah Dokumen Dikirim

- Status berubah menjadi **Menunggu Approve**
- Dokumen dikirim ke Operator Keuangan untuk diproses
- Tombol Edit dan Hapus **tidak akan muncul lagi**
- Anda hanya dapat melakukan **Tracking**

### 7.3 Peringatan

> ⚠️ **PENTING:** Dokumen yang sudah dikirim **TIDAK DAPAT** dibatalkan atau diedit. Pastikan semua data sudah benar sebelum mengirim!

---

## 8. Tracking Dokumen

Fitur Tracking memungkinkan Anda melacak posisi dan status dokumen yang sudah dikirim.

### 8.1 Membuka Halaman Tracking

1. Dari Dashboard, klik menu **Tracking**, atau
2. Dari Daftar Dokumen, klik tombol **Tracking** pada dokumen

### 8.2 Tampilan Tracking

Halaman tracking menyediakan 2 tampilan:

#### Tampilan Kartu (Card View)
- Setiap dokumen ditampilkan dalam kartu terpisah
- Menampilkan progress bar visual alur kerja
- Cocok untuk melihat gambaran umum posisi dokumen

#### Tampilan Tabel (Table View)
- Dokumen ditampilkan dalam format tabel
- Lebih ringkas untuk melihat banyak dokumen sekaligus

Untuk beralih tampilan:
- Klik tombol **Kartu** atau **Tabel** di pojok kanan atas

### 8.3 Filter Tracking

#### Pencarian
- Ketik kata kunci di kotak "Cari Dokumen"
- Cari berdasarkan nomor agenda, SPP, uraian, atau kebun

#### Filter Status
Klik tombol filter status:
- **Semua** - Tampilkan semua dokumen
- **Belum Dikirim** - Dokumen draft
- **Terkirim** - Dokumen dalam proses
- **Sudah Dibayar** - Dokumen selesai

### 8.4 Memahami Progress Dokumen (Card View)

Setiap kartu dokumen menampilkan:

1. **Nomor Agenda dan SPP** - Identifikasi dokumen
2. **Nilai Rupiah** - Nominal pembayaran
3. **Posisi** - Di bagian mana dokumen berada saat ini
4. **Progress Bar** - Visualisasi progres alur kerja

#### Tahapan Alur Kerja

| Step | Tahap | Keterangan |
|------|-------|------------|
| 1 | **BAGIAN** | Dokumen dibuat/belum dikirim |
| 2 | **VERIF** | Di Team Verifikasi |
| 3 | **PERPAJAKAN** | Di bagian Perpajakan |
| 4 | **AKUTANSI** | Di bagian Akutansi |
| 5 | **PEMBAYARAN** | Di bagian Pembayaran |

#### Indikator Visual

| Indikator | Warna | Arti |
|-----------|-------|------|
| **Step Hijau dengan Centang** | Hijau | Tahap sudah selesai |
| **Step dengan Border Tebal** | Hijau Tua | Tahap saat ini (current) |
| **Step Abu-abu** | Abu-abu | Tahap belum tercapai |
| **Stempel "SUDAH DIBAYAR"** | Hijau | Dokumen sudah selesai dibayar |

### 8.5 Melihat Detail Alur Kerja

- Klik kartu dokumen untuk melihat detail alur kerja lengkap
- Akan menampilkan timeline perjalanan dokumen di setiap tahap

---

## 9. Tips dan FAQ

### 9.1 Tips Penggunaan

1. **Periksa data sebelum kirim** - Pastikan semua field wajib terisi dengan benar
2. **Gunakan filter** - Manfaatkan filter untuk menemukan dokumen dengan cepat
3. **Cek tracking secara berkala** - Pantau progress dokumen Anda
4. **Simpan nomor SPP** - Catat nomor SPP untuk referensi

### 9.2 FAQ (Pertanyaan yang Sering Diajukan)

#### Q: Bagaimana cara mengubah dokumen yang sudah dikirim?
**A:** Dokumen yang sudah dikirim tidak dapat diubah. Hubungi Operator Keuangan jika ada kesalahan data.

#### Q: Mengapa saya tidak bisa menghapus dokumen?
**A:** Dokumen hanya dapat dihapus selama statusnya "Belum Dikirim". Setelah dikirim, dokumen tidak dapat dihapus.

#### Q: Berapa lama proses pembayaran?
**A:** Waktu proses bervariasi tergantung kelengkapan dokumen dan antrian. Gunakan fitur Tracking untuk memantau progress.

#### Q: Apa yang dimaksud "Menunggu Approve"?
**A:** Dokumen Anda sudah dikirim dan sedang menunggu persetujuan dari Operator Keuangan sebelum diproses lebih lanjut.

#### Q: Bagaimana jika dokumen dikembalikan/ditolak?
**A:** Anda akan mendapat notifikasi. Dokumen perlu diperbaiki sesuai catatan dan dikirim ulang.

#### Q: Apakah saya bisa melihat dokumen dari bagian lain?
**A:** Tidak. Anda hanya dapat melihat dokumen yang dibuat dari bagian Anda sendiri.

---

## Kontak Support

Jika mengalami kendala teknis, hubungi:
- **IT Support**: [Hubungi administrator sistem]
- **Bidang Keuangan**: [Untuk pertanyaan proses pembayaran]

---

**Dokumen ini berlaku untuk Sistem Agenda Online PTPN**
*Terakhir diperbarui: Februari 2026*

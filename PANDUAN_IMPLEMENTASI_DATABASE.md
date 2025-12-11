# 📋 Panduan Implementasi Database Terbaru via phpMyAdmin/MySQL

## ⚠️ PENTING: Backup Database Terlebih Dahulu!

Sebelum melakukan perubahan apapun, **WAJIB** membuat backup database Anda!

### Cara Backup Database:

#### Via phpMyAdmin:
1. Login ke phpMyAdmin
2. Pilih database `agenda_ptpn_new` (atau nama database Anda)
3. Klik tab **"Export"**
4. Pilih metode **"Quick"** atau **"Custom"**
5. Format: **SQL**
6. Klik **"Go"** untuk download backup

#### Via Command Line:
```bash
mysqldump -u your_username -p agenda_ptpn_new > backup_$(date +%Y%m%d_%H%M%S).sql
```

---

## 🚀 Langkah-langkah Implementasi Database

### **Metode 1: Import File SQL Lengkap (Recommended untuk Database Baru)**

#### **A. Via phpMyAdmin:**

1. **Login ke phpMyAdmin**
   - Buka browser dan akses phpMyAdmin (biasanya: `http://localhost/phpmyadmin`)

2. **Buat Database Baru (Jika Belum Ada)**
   - Klik **"New"** di sidebar kiri
   - Masukkan nama database: `agenda_ptpn_new`
   - Pilih collation: `utf8mb4_unicode_ci`
   - Klik **"Create"**

3. **Import File SQL**
   - Pilih database `agenda_ptpn_new`
   - Klik tab **"Import"**
   - Klik **"Choose File"** dan pilih file `agenda_ptpn_new.sql`
   - Pastikan format: **SQL**
   - Klik **"Go"** atau **"Import"**
   - Tunggu hingga proses selesai (bisa memakan waktu beberapa menit)

4. **Verifikasi Import**
   - Klik tab **"Structure"** untuk melihat semua tabel
   - Pastikan semua tabel terlihat:
     - `pelacakan_posisi_dokumen`
     - `pelacakan_dokumen`
     - `log_aktivitas_dokumen`
     - `log_pembayaran`
     - `pengguna`
     - `pesan_sambutan`
     - `sesi`
     - `dokumens`
     - dll.

#### **B. Via Command Line MySQL:**

```bash
# Login ke MySQL
mysql -u your_username -p

# Buat database (jika belum ada)
CREATE DATABASE IF NOT EXISTS agenda_ptpn_new CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Keluar dari MySQL
exit

# Import file SQL
mysql -u your_username -p agenda_ptpn_new < agenda_ptpn_new.sql
```

---

### **Metode 2: Update Database yang Sudah Ada (Migration)**

Jika database sudah ada dan ingin diupdate dengan struktur baru:

#### **A. Backup Database Terlebih Dahulu!**

#### **B. Drop Database Lama (HATI-HATI!):**

```sql
-- Via phpMyAdmin SQL tab atau MySQL command line
DROP DATABASE IF EXISTS agenda_ptpn_new;
CREATE DATABASE agenda_ptpn_new CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### **C. Import Database Baru:**

Ikuti langkah di **Metode 1** untuk import file SQL baru.

---

## 🔧 Konfigurasi Laravel (.env)

Setelah database berhasil diimport, pastikan file `.env` Laravel Anda sudah dikonfigurasi dengan benar:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=agenda_ptpn_new
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

Kemudian jalankan:

```bash
php artisan config:clear
php artisan cache:clear
```

---

## ✅ Verifikasi Setelah Implementasi

### 1. **Cek Struktur Tabel**

Jalankan query berikut di phpMyAdmin untuk memastikan semua tabel ada:

```sql
SHOW TABLES;
```

### 2. **Cek Foreign Key Relationships**

```sql
-- Cek foreign key dari sesi ke pengguna
SELECT 
    CONSTRAINT_NAME,
    TABLE_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM 
    INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE 
    TABLE_SCHEMA = 'agenda_ptpn_new'
    AND REFERENCED_TABLE_NAME IS NOT NULL;
```

### 3. **Cek Data Sample**

```sql
-- Cek data pengguna
SELECT * FROM pengguna LIMIT 5;

-- Cek data dokumen
SELECT id, nomor_agenda, nomor_spp FROM dokumens LIMIT 5;
```

---

## 🐛 Troubleshooting

### **Error: Table Already Exists**

Jika muncul error bahwa tabel sudah ada:

**Solusi 1:** Drop database dan buat ulang (jika data tidak penting)
```sql
DROP DATABASE agenda_ptpn_new;
CREATE DATABASE agenda_ptpn_new CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

**Solusi 2:** Import dengan opsi "Add DROP TABLE" di phpMyAdmin
- Saat import, pilih **"Custom"** method
- Di bagian **"Format-specific options"**, centang **"Add DROP TABLE"**

### **Error: Foreign Key Constraint Fails**

Jika ada error foreign key:

1. **Nonaktifkan foreign key check sementara:**
```sql
SET FOREIGN_KEY_CHECKS = 0;
-- Import SQL di sini
SET FOREIGN_KEY_CHECKS = 1;
```

2. **Atau import via phpMyAdmin dengan opsi:**
   - Centang **"Disable foreign key checks"** saat import

### **Error: Max Execution Time**

Jika proses import terlalu lama:

**Via phpMyAdmin:**
- Edit file `php.ini`:
  ```ini
  max_execution_time = 300
  max_input_time = 300
  memory_limit = 256M
  ```
- Restart web server

**Via Command Line:**
- Import langsung via MySQL command line (lebih cepat)

### **Error: Character Set / Collation**

Jika ada masalah dengan karakter:

```sql
-- Set default character set untuk database
ALTER DATABASE agenda_ptpn_new CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Atau untuk tabel tertentu
ALTER TABLE nama_tabel CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

---

## 📝 Checklist Implementasi

- [ ] Backup database lama sudah dibuat
- [ ] File `agenda_ptpn_new.sql` sudah tersedia
- [ ] Database baru sudah dibuat atau sudah di-drop
- [ ] File SQL sudah diimport dengan sukses
- [ ] Semua tabel terlihat di phpMyAdmin
- [ ] Foreign key relationships sudah terverifikasi
- [ ] File `.env` Laravel sudah dikonfigurasi
- [ ] Cache Laravel sudah di-clear
- [ ] Aplikasi Laravel sudah diuji dan berfungsi

---

## 🔄 Rollback (Jika Ada Masalah)

Jika terjadi masalah dan ingin kembali ke database lama:

1. **Drop database baru:**
```sql
DROP DATABASE agenda_ptpn_new;
```

2. **Import backup database lama:**
```bash
mysql -u your_username -p agenda_ptpn_new < backup_YYYYMMDD_HHMMSS.sql
```

Atau via phpMyAdmin:
- Buat database baru
- Import file backup

---

## 📞 Support

Jika masih ada masalah:
1. Cek log MySQL: `/var/log/mysql/error.log`
2. Cek log Laravel: `storage/logs/laravel.log`
3. Pastikan versi MySQL/MariaDB kompatibel (minimal MySQL 5.7 atau MariaDB 10.2)

---

**Selamat! Database Anda sudah siap digunakan dengan struktur bahasa Indonesia dan relasi yang lebih baik! 🎉**


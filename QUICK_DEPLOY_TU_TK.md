# 🚀 Quick Deployment Guide - Rekapan TU/TK

## Setelah Git Pull di Server

### Step 1: Jalankan Migration ✅

```bash
cd /path/to/agenda_online_ptpn
php artisan migrate
```

**Atau migration spesifik:**
```bash
php artisan migrate --path=database/migrations/2025_12_03_044458_create_tu_tk_2023_table.php
```

### Step 2: Upload File SQL (jika belum ada) 📁

Pastikan file `tu_tk_2023.sql` ada di root project:

```bash
# Cek apakah file ada
ls -lh tu_tk_2023.sql

# Jika belum ada, upload via SCP atau FTP ke:
# /path/to/agenda_online_ptpn/tu_tk_2023.sql
```

### Step 3: Import Data 💾

**Menggunakan Laravel Command (RECOMMENDED):**

```bash
php artisan tu-tk:import tu_tk_2023.sql
```

Command ini akan:
- ✅ Otomatis skip CREATE TABLE (karena tabel sudah ada)
- ✅ Hanya import data INSERT
- ✅ Menampilkan progress dan statistik

**Jika command tidak ada atau error, gunakan alternatif:**

Via MySQL langsung (perlu edit file SQL dulu - hapus CREATE TABLE):

```bash
# Buat file baru tanpa CREATE TABLE
sed '/^CREATE TABLE/,/ENGINE=/d' tu_tk_2023.sql > tu_tk_insert.sql

# Import
mysql -u [username] -p agenda_online < tu_tk_insert.sql
```

### Step 4: Clear Cache 🧹

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

### Step 5: Verifikasi Data ✅

```bash
php artisan tinker
```

Lalu:
```php
// Cek jumlah data
\App\Models\TuTk::count();

// Harus return angka > 0 (misalnya: 20, 50, dll)

// Cek sample data
\App\Models\TuTk::first();
```

### Step 6: Test Halaman 🌐

Akses: `http://your-server-domain/rekapan-tu-tk`

**Yang harus muncul:**
- ✅ 4 Dashboard Scorecards
- ✅ Filter section
- ✅ Widget "5 Dokumen Terlama"
- ✅ Tabel dengan data

---

## ⚡ One-Line Commands (Jika Semuanya Sudah Siap)

```bash
# Di server, jalankan semua ini sekaligus:
cd /path/to/agenda_online_ptpn && \
php artisan migrate --path=database/migrations/2025_12_03_044458_create_tu_tk_2023_table.php && \
php artisan tu-tk:import tu_tk_2023.sql && \
php artisan config:clear && \
php artisan route:clear && \
echo "✅ Deployment selesai!"
```

---

## ❌ Troubleshooting

### Error: "Table already exists"
- **OK**, tabel sudah dibuat oleh migration
- Lanjutkan ke Step 3 (Import Data)

### Error: "File tidak ditemukan"
- Upload file `tu_tk_2023.sql` ke server dulu
- Pastikan path benar

### Error: "Command not found"
- Pastikan sudah git pull dan file command ada
- Atau gunakan alternatif import via MySQL

### Data tidak muncul
- Cek data sudah diimport: `php artisan tinker` → `\App\Models\TuTk::count()`
- Clear cache lagi
- Cek log: `tail -f storage/logs/laravel.log`

---

## 📋 Checklist

- [ ] Migration dijalankan
- [ ] File SQL ada di server
- [ ] Data sudah diimport
- [ ] Cache sudah clear
- [ ] Halaman bisa diakses
- [ ] Data muncul dengan benar

---

Untuk detail lebih lengkap, lihat: `DEPLOYMENT_TU_TK.md`


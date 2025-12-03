# Panduan Deployment Rekapan TU/TK di Server

Panduan lengkap untuk mengimplementasikan fitur Rekapan TU/TK di server setelah git pull.

## Prerequisites

Pastikan:
- ✅ Sudah melakukan `git pull` di server
- ✅ File `tu_tk_2023.sql` ada di server (di root project)
- ✅ Database `agenda_online` sudah dikonfigurasi dengan benar
- ✅ User MySQL memiliki akses untuk CREATE TABLE dan INSERT data

## Langkah-langkah Deployment

### 1. Jalankan Migration

Jalankan migration untuk membuat tabel `tu_tk_2023`:

```bash
cd /path/to/agenda_online_ptpn
php artisan migrate
```

Atau jika hanya ingin menjalankan migration spesifik:

```bash
php artisan migrate --path=database/migrations/2025_12_03_044458_create_tu_tk_2023_table.php
```

**Verifikasi:**
```bash
php artisan tinker
```
Lalu jalankan:
```php
Schema::hasTable('tu_tk_2023');
// Harus return: true
```

### 2. Upload File SQL ke Server

Pastikan file `tu_tk_2023.sql` sudah ada di server. Jika belum, upload file tersebut ke root project:

```bash
# Via SCP (dari local)
scp tu_tk_2023.sql user@server:/path/to/agenda_online_ptpn/

# Atau via FTP/SFTP
# Upload ke: /path/to/agenda_online_ptpn/tu_tk_2023.sql
```

**Verifikasi file ada:**
```bash
ls -lh tu_tk_2023.sql
```

### 3. Import Data dari SQL File

Gunakan Laravel command yang sudah dibuat untuk mengimport data (akan otomatis skip CREATE TABLE):

```bash
php artisan tu-tk:import tu_tk_2023.sql
```

**Output yang diharapkan:**
```
📂 Membaca file: tu_tk_2023.sql
📊 Menemukan X statements dalam file
✅ Menemukan Y INSERT statements
📈 Data existing dalam tabel: 0 records
🚀 Mulai mengimport data...
[Progress bar...]
✅ Import selesai!
```

**Alternatif jika command tidak berfungsi:**

Jika ada masalah dengan command, Anda bisa:
1. **Via MySQL Command Line** (skip CREATE TABLE):
   ```bash
   # Buat file SQL baru tanpa CREATE TABLE
   sed '/^CREATE TABLE/,/ENGINE=/d' tu_tk_2023.sql > tu_tk_2023_insert_only.sql
   
   # Import
   mysql -u [username] -p agenda_online < tu_tk_2023_insert_only.sql
   ```

2. **Via phpMyAdmin:**
   - Buka phpMyAdmin
   - Pilih database `agenda_online`
   - Klik tab "SQL"
   - Buka file `tu_tk_2023.sql`, copy hanya bagian INSERT statements (setelah baris 123)
   - Paste dan jalankan

### 4. Verifikasi Data

Cek apakah data sudah berhasil diimport:

```bash
php artisan tinker
```

Lalu jalankan:
```php
// Hitung jumlah data
\App\Models\TuTk::count();

// Cek sample data
\App\Models\TuTk::first();

// Cek total outstanding (nilai yang belum dibayar)
\App\Models\TuTk::sum('BELUM_DIBAYAR');
```

**Expected output:**
- `count()` harus > 0 (misalnya: 20, 50, atau lebih)
- `first()` harus menampilkan data dokumen
- `sum()` menampilkan total nilai yang belum dibayar

### 5. Clear Cache

Clear semua cache untuk memastikan perubahan terdeteksi:

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

### 6. Set Permission (jika perlu)

Pastikan file SQL bisa dibaca:

```bash
chmod 644 tu_tk_2023.sql
```

### 7. Test Halaman

Akses halaman Rekapan TU/TK untuk memastikan semuanya berfungsi:

```
http://your-server-domain/rekapan-tu-tk
```

**Yang harus muncul:**
- ✅ 4 Dashboard Scorecards (Total Outstanding, Dokumen Belum Lunas, dll)
- ✅ Filter section
- ✅ Widget "5 Dokumen Terlama Belum Dibayar"
- ✅ Tabel dengan data dokumen
- ✅ Color coding untuk umur hutang
- ✅ Progress bar untuk pembayaran

## Troubleshooting

### Error: "Table 'tu_tk_2023' doesn't exist"

**Solusi:**
```bash
php artisan migrate --path=database/migrations/2025_12_03_044458_create_tu_tk_2023_table.php
```

### Error: "Table already exists" saat import

**Solusi:** 
- Gunakan command `php artisan tu-tk:import` yang sudah otomatis skip CREATE TABLE
- Atau edit file SQL dan hapus bagian CREATE TABLE

### Error: "File tidak ditemukan"

**Solusi:**
```bash
# Pastikan file ada di root project
ls -lh tu_tk_2023.sql

# Jika belum ada, upload dulu
# Pastikan path lengkap saat import
php artisan tu-tk:import /full/path/to/tu_tk_2023.sql
```

### Data tidak muncul di halaman

**Cek:**
1. Data sudah diimport: `php artisan tinker` → `\App\Models\TuTk::count()`
2. Route sudah terdaftar: `php artisan route:list | grep tu-tk`
3. Cache sudah clear: `php artisan config:clear && php artisan route:clear`
4. Log error: `tail -f storage/logs/laravel.log`

### Error Permission Denied

**Solusi:**
```bash
# Set permission untuk storage dan cache
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## Checklist Deployment

Gunakan checklist ini untuk memastikan semua langkah sudah dilakukan:

- [ ] Git pull sudah dilakukan
- [ ] Migration sudah dijalankan
- [ ] Tabel `tu_tk_2023` sudah dibuat (verifikasi dengan `Schema::hasTable()`)
- [ ] File `tu_tk_2023.sql` sudah diupload ke server
- [ ] Data sudah diimport (verifikasi dengan `\App\Models\TuTk::count()`)
- [ ] Cache sudah di-clear
- [ ] Halaman `/rekapan-tu-tk` bisa diakses
- [ ] Dashboard scorecards menampilkan data
- [ ] Filter berfungsi dengan baik
- [ ] Tabel menampilkan data dengan benar

## Catatan Penting

1. **Backup Database:** Sebelum import data, disarankan untuk backup database:
   ```bash
   mysqldump -u [username] -p agenda_online > backup_$(date +%Y%m%d_%H%M%S).sql
   ```

2. **File SQL Size:** Jika file SQL sangat besar (> 100MB), import mungkin memakan waktu lama. Pastikan:
   - `max_execution_time` di PHP cukup besar
   - `memory_limit` di PHP cukup besar
   - Connection timeout MySQL cukup besar

3. **Multiple Database:** Jika Anda memiliki database INPUT_KS, INPUT_TAN, INPUT_VD, INPUT_PUPUK yang terpisah, proses import perlu diulang untuk masing-masing database.

## Support

Jika mengalami masalah:
1. Cek log Laravel: `storage/logs/laravel.log`
2. Cek log MySQL: `/var/log/mysql/error.log` (Linux) atau log MySQL di server
3. Pastikan semua file sudah ter-update dengan benar


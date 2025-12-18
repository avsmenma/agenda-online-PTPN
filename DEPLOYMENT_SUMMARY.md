# 📋 Ringkasan Deployment - Agenda Online PTPN

## ✅ Perubahan yang Sudah Dibuat

### 1. **Fix Migration Error**
- ✅ Membuat migration baru: `2025_11_23_232000_add_pembayaran_fields_to_dokumens_table.php`
  - Menambahkan kolom `sent_to_pembayaran_at`
  - Menambahkan kolom `status_pembayaran`
- ✅ Memperbaiki migration: `2025_11_23_232538_add_link_bukti_pembayaran_to_dokumens_table.php`
  - Sekarang mengecek apakah kolom `status_pembayaran` ada sebelum menggunakan `after()`

### 2. **File Deployment**
- ✅ `DEPLOYMENT_GUIDE.md` - Panduan lengkap deployment
- ✅ `FIX_MIGRATION_ERROR.md` - Panduan fix error migration
- ✅ `deploy.sh` - Script otomatis deployment untuk server

### 3. **Perubahan Kode**
- ✅ Export Excel/PDF untuk rekapan pembayaran
- ✅ Filter selesai/belum selesai di rekapan owner
- ✅ Perbaikan lainnya

---

## 🚀 Langkah-langkah Deployment

### **A. DI LOCAL (Development) - Sekarang**

#### 1. Commit dan Push ke GitHub

```bash
# Commit semua perubahan
git commit -m "Fix: Migration error untuk status_pembayaran, tambah export Excel/PDF, dan perbaikan lainnya"

# Push ke GitHub
git push origin main
```

**Atau jika ingin commit lebih detail:**

```bash
git commit -m "Fix migration error dan tambah fitur export

- Fix: Migration error untuk kolom status_pembayaran
- Add: Migration untuk menambahkan status_pembayaran dan sent_to_pembayaran_at
- Add: Export Excel/PDF untuk rekapan pembayaran
- Add: Filter selesai/belum selesai di rekapan owner
- Add: Script deployment otomatis (deploy.sh)
- Add: Dokumentasi deployment (DEPLOYMENT_GUIDE.md, FIX_MIGRATION_ERROR.md)"
```

---

### **B. DI SERVER (VPS Ubuntu Alibaba)**

#### **Opsi 1: Menggunakan Script Otomatis (Recommended)**

```bash
# 1. SSH ke server
ssh user@your-server-ip

# 2. Masuk ke direktori project
cd /var/www/agenda_online_ptpn

# 3. Berikan permission execute pada script
chmod +x deploy.sh

# 4. Jalankan script deployment
./deploy.sh
```

#### **Opsi 2: Manual Step-by-Step**

```bash
# 1. SSH ke server
ssh user@your-server-ip

# 2. Masuk ke direktori project
cd /var/www/agenda_online_ptpn

# 3. Backup .env
cp .env .env.backup.$(date +%Y%m%d_%H%M%S)

# 4. Pull perubahan terbaru
git pull origin main

# 5. Install/Update dependencies
composer install --no-dev --optimize-autoloader

# Install Node.js dependencies (PENTING: Harus dijalankan sebelum npm run build)
npm install

# Build assets untuk production
npm run build

# 6. Run migrations (PENTING: Pastikan backup database dulu!)
php artisan migrate --force

# 7. Clear dan optimize cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 8. Set permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# 9. Restart PHP-FPM (sesuaikan versi PHP Anda)
sudo systemctl restart php8.2-fpm
# atau
sudo systemctl restart php8.1-fpm
```

---

## ⚠️ PENTING: Sebelum Migration di Server

### **BACKUP DATABASE DULU!**

```bash
# Backup database MySQL
mysqldump -u your_username -p your_database_name > backup_$(date +%Y%m%d_%H%M%S).sql

# Atau jika menggunakan Laravel
php artisan db:backup  # Jika ada package backup
```

---

## 🔍 Troubleshooting

### Jika Migration Masih Error

1. **Cek file `FIX_MIGRATION_ERROR.md`** untuk panduan lengkap
2. **Rollback migration yang gagal:**
   ```bash
   php artisan migrate:rollback --step=1
   ```
3. **Jalankan migration lagi:**
   ```bash
   php artisan migrate --force
   ```

### Jika Website Masih Menampilkan Versi Lama

1. **Clear browser cache:** Ctrl+Shift+R (Windows) atau Cmd+Shift+R (Mac)
2. **Clear Laravel cache:**
   ```bash
   php artisan cache:clear
   php artisan view:clear
   ```
3. **Restart web server:**
   ```bash
   sudo systemctl restart nginx
   # atau
   sudo systemctl restart apache2
   ```

### Jika Assets Tidak Ter-load

1. **Pastikan build berhasil:**
   ```bash
   npm run build
   ```
2. **Cek file `public/build/manifest.json` ada**
3. **Clear view cache:**
   ```bash
   php artisan view:clear
   ```

### Jika Error "vite: not found" ⚠️

**Error ini terjadi karena `node_modules` belum terinstall atau Vite tidak tersedia.**

**Solusi:**

```bash
# 1. Pastikan berada di direktori project
cd /var/www/agendareg5.online

# 2. Install semua dependencies (termasuk vite)
# PENTING: Harus dijalankan sebelum npm run build!
npm install

# 3. Verifikasi vite terinstall
ls node_modules/.bin/vite

# 4. Setelah itu baru jalankan build
npm run build
```

**Jika masih error, coba install ulang dengan clean:**

```bash
# Hapus node_modules dan package-lock.json
rm -rf node_modules package-lock.json

# Install ulang
npm install

# Build assets
npm run build
```

**Pastikan Node.js dan npm sudah terinstall:**

```bash
# Cek versi Node.js dan npm
node --version
npm --version

# Jika belum terinstall, install Node.js (contoh untuk Ubuntu/Debian)
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt-get install -y nodejs

# Atau untuk versi LTS
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs
```

**Urutan yang benar saat deployment:**

1. ✅ `composer install --no-dev --optimize-autoloader`
2. ✅ `npm install` ← **PENTING: Jangan skip ini!**
3. ✅ `npm run build`
4. ✅ `php artisan migrate --force`
5. ✅ Clear cache dan optimize

---

## Troubleshooting

### Error: Permission denied pada halaman Login

Jika muncul error seperti:
```
file_put_contents(/var/www/agenda_online_ptpn/storage/framework/views/...): Failed to open stream: Permission denied
```

Saat mengakses halaman login, ini adalah masalah permission yang sama seperti di atas.

**Solusi cepat:**
```bash
cd /var/www/agenda_online_ptpn
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
php artisan view:clear
```

Lihat file `FIX_LOGIN_PERMISSION.md` untuk panduan lengkap.

### Error: Permission denied pada `storage/framework/views/`

Jika muncul error seperti:
```
file_put_contents(/var/www/agenda_online_ptpn/storage/framework/views/...): Failed to open stream: Permission denied
```

Ini adalah masalah permission. Laravel tidak bisa menulis file compiled view.

**Solusi:**

1. **Masuk ke server via SSH** dan navigasi ke direktori project:
   ```bash
   cd /var/www/agenda_online_ptpn
   ```

2. **Cek user web server yang sedang berjalan:**
   ```bash
   # Untuk Apache
   ps aux | grep apache | head -1
   
   # Untuk Nginx
   ps aux | grep nginx | head -1
   ```

3. **Set ownership ke web server user:**
   ```bash
   # Untuk Apache (biasanya www-data)
   sudo chown -R www-data:www-data storage bootstrap/cache
   
   # Untuk Nginx (biasanya nginx)
   sudo chown -R nginx:nginx storage bootstrap/cache
   ```

4. **Set permission yang benar:**
   ```bash
   sudo chmod -R 775 storage bootstrap/cache
   ```

5. **Jika masih error, coba set permission lebih luas (sementara untuk testing):**
   ```bash
   sudo chmod -R 777 storage bootstrap/cache
   ```

6. **Clear cache Laravel:**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan view:clear
   php artisan route:clear
   ```

7. **Verifikasi permission:**
   ```bash
   ls -la storage/framework/views/
   ls -la bootstrap/cache/
   ```
   
   Pastikan direktori tersebut memiliki permission `drwxrwxr-x` atau `drwxrwxrwx` dan ownership sesuai dengan web server user.

8. **Jika menggunakan SELinux (CentOS/RHEL), tambahkan context:**
   ```bash
   sudo chcon -R -t httpd_sys_rw_content_t storage
   sudo chcon -R -t httpd_sys_rw_content_t bootstrap/cache
   ```

**Catatan:**
- Ganti `www-data` atau `nginx` dengan user web server yang sesuai dengan server Anda
- Setelah permission diperbaiki, refresh halaman browser
- Jika masih error, pastikan SELinux tidak memblokir (jika menggunakan CentOS/RHEL)
- Untuk production, gunakan permission `775` (lebih aman dari `777`)

---

## 📝 Checklist Deployment

### Sebelum Deployment
- [ ] Backup database production
- [ ] Backup file `.env` di server
- [ ] Test semua fitur di local
- [ ] Commit dan push semua perubahan ke GitHub

### Saat Deployment
- [ ] Pull perubahan dari GitHub
- [ ] Install/update dependencies (composer install)
- [ ] **Install Node.js dependencies (npm install)** ⚠️ PENTING - Jangan skip!
- [ ] Build assets (npm run build)
- [ ] Run migrations
- [ ] Clear dan optimize cache
- [ ] Set permissions
- [ ] Restart PHP-FPM

### Setelah Deployment
- [ ] Test website berfungsi dengan baik
- [ ] Cek log jika ada error: `tail -f storage/logs/laravel.log`
- [ ] Verifikasi fitur baru berfungsi
- [ ] Clear browser cache

---

## 🔧 Troubleshooting

### Error: Permission Denied pada Storage

**Error:**
```
file_put_contents(.../storage/framework/views/...): Failed to open stream: Permission denied
```

**Solusi:**
```bash
cd /var/www/agenda_online_ptpn
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

**Quick Fix (One-Liner):**
```bash
cd /var/www/agenda_online_ptpn && sudo chown -R www-data:www-data storage bootstrap/cache && sudo chmod -R 775 storage bootstrap/cache && php artisan config:clear && php artisan cache:clear && php artisan route:clear && php artisan view:clear
```

Lihat detail lengkap di `FIX_STORAGE_PERMISSION.md`

---

## 📞 Support

Jika ada masalah:
1. Cek log Laravel: `storage/logs/laravel.log`
2. Cek log web server: `/var/log/nginx/error.log` atau `/var/log/apache2/error.log`
3. Cek status service: `sudo systemctl status nginx` atau `sudo systemctl status php-fpm`

---

## 🎯 Urutan Migration yang Benar

Migration akan dijalankan dalam urutan berikut:
1. `2025_11_23_232000_add_pembayaran_fields_to_dokumens_table` - Menambahkan `status_pembayaran` dan `sent_to_pembayaran_at`
2. `2025_11_23_232538_add_link_bukti_pembayaran_to_dokumens_table` - Menambahkan `link_bukti_pembayaran` setelah `status_pembayaran`

Urutan ini memastikan kolom `status_pembayaran` sudah ada sebelum migration kedua mencoba menggunakannya.


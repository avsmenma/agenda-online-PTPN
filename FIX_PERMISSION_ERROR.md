# 🔧 Fix Permission Error - Storage Framework Views

## Error yang Terjadi

```
file_put_contents(/var/www/agenda_online_ptpn/storage/framework/views/...): Failed to open stream: Permission denied
```

## Solusi Cepat

### 1. Masuk ke Server via SSH

```bash
ssh user@47.236.49.229
cd /var/www/agenda_online_ptpn
```

### 2. Cek User Web Server yang Berjalan

```bash
# Untuk Apache
ps aux | grep apache | head -1

# Untuk Nginx
ps aux | grep nginx | head -1

# Atau cek dengan:
ps aux | grep -E 'apache|httpd|nginx' | head -1
```

### 3. Set Ownership ke Web Server User

**Jika menggunakan Apache (biasanya `www-data`):**
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
```

**Jika menggunakan Nginx (biasanya `nginx`):**
```bash
sudo chown -R nginx:nginx storage bootstrap/cache
```

### 4. Set Permission yang Benar

```bash
sudo chmod -R 775 storage bootstrap/cache
```

**Jika masih error, coba permission lebih luas (sementara untuk testing):**
```bash
sudo chmod -R 777 storage bootstrap/cache
```

### 5. Clear Cache Laravel

```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

### 6. Verifikasi Permission

```bash
ls -la storage/framework/views/
ls -la bootstrap/cache/
```

Pastikan direktori tersebut memiliki:
- Permission: `drwxrwxr-x` atau `drwxrwxrwx`
- Ownership: sesuai dengan web server user (www-data atau nginx)

### 7. Jika Menggunakan SELinux (CentOS/RHEL)

```bash
sudo chcon -R -t httpd_sys_rw_content_t storage
sudo chcon -R -t httpd_sys_rw_content_t bootstrap/cache
```

## Script Lengkap (Copy-Paste)

```bash
# Masuk ke direktori project
cd /var/www/agenda_online_ptpn

# Cek user web server
WEB_USER=$(ps aux | grep -E 'apache|httpd|nginx' | grep -v grep | head -1 | awk '{print $1}')
echo "Web server user: $WEB_USER"

# Set ownership (ganti www-data dengan user yang sesuai)
sudo chown -R www-data:www-data storage bootstrap/cache
# atau
# sudo chown -R nginx:nginx storage bootstrap/cache

# Set permission
sudo chmod -R 775 storage bootstrap/cache

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Verifikasi
ls -la storage/framework/views/ | head -5
ls -la bootstrap/cache/ | head -5
```

## Catatan Penting

1. **Ganti `www-data` atau `nginx`** dengan user web server yang sesuai dengan server Anda
2. **Untuk production**, gunakan permission `775` (lebih aman dari `777`)
3. **Setelah permission diperbaiki**, refresh halaman browser
4. **Jika masih error**, pastikan SELinux tidak memblokir (jika menggunakan CentOS/RHEL)

## Troubleshooting Tambahan

### Jika masih error setelah set permission:

1. **Cek apakah direktori ada:**
   ```bash
   ls -la storage/framework/
   ```

2. **Buat direktori jika tidak ada:**
   ```bash
   mkdir -p storage/framework/views
   mkdir -p storage/framework/cache
   mkdir -p storage/framework/sessions
   mkdir -p bootstrap/cache
   ```

3. **Set permission lagi:**
   ```bash
   sudo chown -R www-data:www-data storage bootstrap/cache
   sudo chmod -R 775 storage bootstrap/cache
   ```

4. **Cek log Laravel:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

## Setelah Berhasil

Setelah permission diperbaiki:
1. Refresh halaman browser
2. Halaman seharusnya sudah bisa diakses
3. Jika masih ada error, cek log Laravel: `tail -f storage/logs/laravel.log`


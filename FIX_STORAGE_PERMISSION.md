# 🔧 Fix Storage Permission Error

## Error
```
file_put_contents(/var/www/agenda_online_ptpn/storage/framework/views/...): 
Failed to open stream: Permission denied
```

## Penyebab
Direktori `storage` dan `bootstrap/cache` tidak memiliki permission yang benar untuk web server (www-data) menulis file.

## Solusi

### Step 1: Login ke Server
```bash
ssh user@47.236.49.229
```

### Step 2: Masuk ke Direktori Project
```bash
cd /var/www/agenda_online_ptpn
```

### Step 3: Set Ownership ke www-data
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
```

### Step 4: Set Permission yang Benar
```bash
sudo chmod -R 775 storage bootstrap/cache
```

### Step 5: Clear Laravel Cache
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Step 6: Verifikasi Permission
```bash
ls -la storage/framework/views
ls -la bootstrap/cache
```

**Expected output:**
```
drwxrwxr-x  www-data www-data  storage/framework/views
drwxrwxr-x  www-data www-data  bootstrap/cache
```

## Alternatif: Jika Masih Error

Jika masih error setelah step di atas, coba:

### Option 1: Set Permission Lebih Luas (Temporary)
```bash
sudo chmod -R 777 storage bootstrap/cache
```

**⚠️ WARNING:** Permission 777 tidak aman untuk production. Gunakan hanya untuk testing.

### Option 2: Cek SELinux (Jika Aktif)
```bash
# Cek status SELinux
getenforce

# Jika SELinux aktif, set context
sudo chcon -R -t httpd_sys_rw_content_t storage bootstrap/cache
```

### Option 3: Cek AppArmor (Jika Aktif)
```bash
# Cek status AppArmor
sudo systemctl status apparmor

# Jika aktif, mungkin perlu konfigurasi khusus
```

## Troubleshooting

### Jika `www-data` tidak ada
```bash
# Cek user web server
ps aux | grep -E 'apache|nginx|httpd'

# Atau cek di config
cat /etc/apache2/envvars | grep APACHE_RUN_USER
# atau
cat /etc/nginx/nginx.conf | grep user
```

### Jika masih error setelah semua step
```bash
# Cek apakah direktori ada
ls -la storage/framework/views

# Jika tidak ada, buat manual
mkdir -p storage/framework/views
mkdir -p bootstrap/cache

# Set permission lagi
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

## Quick Fix (One-Liner)
```bash
cd /var/www/agenda_online_ptpn && \
sudo chown -R www-data:www-data storage bootstrap/cache && \
sudo chmod -R 775 storage bootstrap/cache && \
php artisan config:clear && \
php artisan cache:clear && \
php artisan route:clear && \
php artisan view:clear
```

## Setelah Fix

Refresh halaman:
```
http://47.236.49.229/documents/pembayaran
```

Error seharusnya sudah hilang.


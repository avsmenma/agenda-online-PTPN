# 🔐 Panduan Setup HTTPS - Agenda Online PTPN

Panduan lengkap untuk mengaktifkan HTTPS pada server VPS Alibaba Cloud.

---

## 📋 Ringkasan Masalah yang Diperbaiki

| Masalah | Penyebab | Solusi |
|---------|----------|--------|
| HTTPS tidak bisa diakses (`ERR_CONNECTION_TIMED_OUT`) | SSL certificate belum dikonfigurasi, port 443 belum listen | Setup SSL certificate + konfigurasi Nginx untuk HTTPS |
| Login stuck "Memproses..." | `APP_URL` tidak sesuai dengan URL production | Update `.env` dengan URL yang benar |
| Nginx gagal start | Apache menggunakan port 80 | Stop dan disable Apache |

---

## 🚀 Langkah-langkah Setup HTTPS

### Langkah 1: SSH ke Server

```bash
ssh root@47.236.49.229
cd /var/www/agenda_online_ptpn
```

---

### Langkah 2: Buat Self-Signed SSL Certificate

```bash
# Buat direktori untuk certificate (jika belum ada)
sudo mkdir -p /etc/ssl/private

# Generate self-signed certificate (berlaku 365 hari)
sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout /etc/ssl/private/nginx-selfsigned.key \
    -out /etc/ssl/certs/nginx-selfsigned.crt
```

Saat diminta, isi informasi certificate:
```
Country Name: ID
State: [Nama Provinsi]
Locality: [Nama Kota]
Organization Name: [Nama Organisasi]
Organizational Unit: [Nama Unit]
Common Name: 47.236.49.229
Email Address: [Email Admin]
```

---

### Langkah 3: Stop Apache (jika ada)

```bash
# Cek apakah Apache menggunakan port 80
sudo lsof -i :80

# Jika ada Apache, stop dan disable
sudo systemctl stop apache2
sudo systemctl disable apache2

# Verifikasi port 80 sudah bebas
sudo lsof -i :80
```

---

### Langkah 4: Buat Konfigurasi Nginx untuk HTTPS

```bash
# Backup konfigurasi default (opsional)
sudo cp /etc/nginx/sites-available/default /etc/nginx/sites-available/default.backup

# Buat file konfigurasi baru
sudo nano /etc/nginx/sites-available/agenda_online_ptpn
```

Paste konfigurasi berikut:

```nginx
server {
    listen 80;
    server_name 47.236.49.229;
    
    # Redirect semua HTTP ke HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl;
    server_name 47.236.49.229;

    # SSL Certificate
    ssl_certificate /etc/ssl/certs/nginx-selfsigned.crt;
    ssl_certificate_key /etc/ssl/private/nginx-selfsigned.key;

    # SSL Settings
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_prefer_server_ciphers on;
    ssl_ciphers ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256;

    # Laravel Application
    root /var/www/agenda_online_ptpn/public;
    index index.php index.html;

    # Logging
    access_log /var/log/nginx/agenda_access.log;
    error_log /var/log/nginx/agenda_error.log;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

Simpan file: `Ctrl+X`, lalu `Y`, lalu `Enter`

---

### Langkah 5: Aktifkan Konfigurasi Nginx

```bash
# Hapus link default jika ada
sudo rm -f /etc/nginx/sites-enabled/default

# Buat symbolic link untuk konfigurasi baru
sudo ln -sf /etc/nginx/sites-available/agenda_online_ptpn /etc/nginx/sites-enabled/

# Test konfigurasi nginx (harus sukses)
sudo nginx -t
```

Output yang diharapkan:
```
nginx: the configuration file /etc/nginx/nginx.conf syntax is ok
nginx: configuration file /etc/nginx/nginx.conf test is successful
```

---

### Langkah 6: Update File .env

```bash
sudo nano /var/www/agenda_online_ptpn/.env
```

Pastikan konfigurasi berikut sudah benar:

```ini
APP_URL=https://47.236.49.229

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
```

Simpan file: `Ctrl+X`, lalu `Y`, lalu `Enter`

---

### Langkah 7: Clear Cache Laravel

```bash
cd /var/www/agenda_online_ptpn
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

---

### Langkah 8: Restart Services

```bash
sudo systemctl restart php8.2-fpm
sudo systemctl start nginx
```

---

### Langkah 9: Verifikasi

```bash
# Cek status nginx
sudo systemctl status nginx

# Cek port 80 dan 443 aktif
sudo ss -tlnp | grep -E '80|443'

# Test HTTPS dari server
curl -vk https://47.236.49.229/login 2>&1 | head -30

# Test HTTP redirect
curl -I http://47.236.49.229/login
```

---

## ✅ Hasil yang Diharapkan

1. **Nginx status**: `Active: active (running)`
2. **Port listening**: Port 80 dan 443 aktif
3. **HTTPS test**: SSL handshake berhasil (TLSv1.3)
4. **HTTP redirect**: Response `301 Moved Permanently` ke HTTPS
5. **Browser access**: `https://47.236.49.229/login` dapat diakses
6. **Login**: Proses login berhasil tanpa stuck

---

## ⚠️ Catatan Penting

### Self-Signed Certificate Warning
Browser akan menampilkan warning "Your connection is not private" karena menggunakan self-signed certificate. User perlu klik **Advanced** → **Proceed to site** untuk melanjutkan.

### Jika Ingin Menggunakan Domain
Jika Anda memiliki domain (contoh: `agenda.ptpn.com`), gunakan Let's Encrypt untuk SSL gratis:

```bash
# Install certbot
sudo apt update
sudo apt install certbot python3-certbot-nginx -y

# Generate certificate
sudo certbot --nginx -d yourdomain.com

# Auto-renewal sudah termasuk
```

---

## 🔧 Troubleshooting

### Error: Port 80 already in use
```bash
# Cek proses yang menggunakan port 80
sudo lsof -i :80

# Stop Apache jika ada
sudo systemctl stop apache2
sudo systemctl disable apache2

# Kill nginx zombie process jika ada
sudo killall nginx
```

### Error: php8.2-fpm.sock not found
```bash
# Cek versi PHP yang terinstall
ls /var/run/php/

# Jika php8.1, edit konfigurasi nginx:
# Ganti: fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
# Menjadi: fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
```

### Error: nginx -t gagal
```bash
# Lihat error detail
sudo nginx -t 2>&1

# Cek syntax file konfigurasi
cat /etc/nginx/sites-available/agenda_online_ptpn
```

### Login masih stuck
```bash
# Pastikan APP_URL sudah benar
cat /var/www/agenda_online_ptpn/.env | grep APP_URL

# Clear semua cache
cd /var/www/agenda_online_ptpn
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Restart services
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

---

## 📅 Tanggal Setup

- **Tanggal**: 27 Januari 2026
- **Server**: VPS Alibaba Cloud (47.236.49.229)
- **SSL Type**: Self-Signed Certificate
- **Validity**: 1 Tahun (hingga 27 Januari 2027)

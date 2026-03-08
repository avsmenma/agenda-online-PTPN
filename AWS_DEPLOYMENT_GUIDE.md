# 🚀 Panduan Deployment ke AWS Ubuntu Server

## Agenda Online PTPN — Migrasi dari Alibaba Cloud ke AWS EC2

---

## 📋 Ringkasan Project

| Item | Detail |
|------|--------|
| **Framework** | Laravel 12, PHP 8.2+ |
| **Frontend** | Vite 7, Tailwind CSS 4 |
| **Database** | MySQL — 2 database: `agenda_ptpn_new` (~35MB) & `cash_bank_new` (~1.5MB) |
| **Web Server** | Nginx + PHP-FPM |
| **Fitur Tambahan** | Pusher (WebSocket), Fonnte (WhatsApp Gateway), 2FA |

---

## FASE 1: Setup AWS EC2 Instance

### 1.1 Buat EC2 Instance

1. Login ke **AWS Console** → **EC2** → **Launch Instance**
2. Konfigurasi:
   - **Name**: `agenda-online-ptpn`
   - **OS Image (AMI)**: Ubuntu Server 24.04 LTS (atau 22.04 LTS)
   - **Instance Type**: Minimum `t2.small` (2 GB RAM) — disarankan `t2.medium` (4 GB RAM) karena build Vite butuh memori
   - **Key Pair**: Buat baru atau gunakan yang sudah ada (download file `.pem`)
   - **Storage**: Minimum 20 GB SSD (gp3)

3. **Security Group** — buka port berikut:

   | Type | Port | Source | Keterangan |
   |------|------|--------|------------|
   | SSH | 22 | My IP / Custom IP | Akses SSH |
   | HTTP | 80 | 0.0.0.0/0 | Web traffic |
   | HTTPS | 443 | 0.0.0.0/0 | Web traffic SSL |
   | MySQL | 3306 | (opsional) | Hanya jika akses DB dari luar |

4. **Launch Instance** dan tunggu hingga status `Running`

### 1.2 Alokasikan Elastic IP (Opsional tapi Disarankan)

1. **EC2** → **Elastic IPs** → **Allocate Elastic IP address**
2. **Associate** Elastic IP ke instance Anda
3. Catat IP address ini — ini akan jadi IP tetap server Anda

### 1.3 Arahkan Domain (Jika Ada)

Jika Anda punya domain (misalnya `agendareg5.online`):

1. Masuk ke **DNS Manager** domain Anda
2. Buat **A Record**:
   - **Host**: `@` (atau subdomain yang diinginkan)
   - **Value**: IP Elastic IP AWS Anda
   - **TTL**: 300

---

## FASE 2: Setup Server Ubuntu

### 2.1 SSH ke Server

```bash
# Ubah permission key file (jalankan di local)
chmod 400 your-key.pem

# SSH ke server
ssh -i your-key.pem ubuntu@YOUR_AWS_IP
```

### 2.2 Update Sistem

```bash
sudo apt update && sudo apt upgrade -y
```

### 2.3 Install PHP 8.2 + Extensions

```bash
# Tambahkan repository PHP
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Install PHP 8.2 dan extensions yang dibutuhkan Laravel 12
sudo apt install -y php8.2-fpm php8.2-cli php8.2-mysql php8.2-mbstring \
  php8.2-xml php8.2-bcmath php8.2-curl php8.2-zip php8.2-gd \
  php8.2-intl php8.2-readline php8.2-tokenizer php8.2-redis

# Verifikasi
php -v
```

### 2.4 Install Nginx

```bash
sudo apt install -y nginx

# Start dan enable
sudo systemctl start nginx
sudo systemctl enable nginx
```

### 2.5 Install MySQL 8.0

```bash
sudo apt install -y mysql-server

# Jalankan secure installation
sudo mysql_secure_installation
# → Pilih password strength: Medium
# → Set root password
# → Remove anonymous users: Y
# → Disallow root login remotely: Y
# → Remove test database: Y
# → Reload privilege tables: Y
```

### 2.6 Install Composer

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
composer --version
```

### 2.7 Install Node.js (LTS) & NPM

```bash
# Install Node.js 20 LTS
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Verifikasi
node --version
npm --version
```

### 2.8 Install Git

```bash
sudo apt install -y git
```

---

## FASE 3: Setup Database (Import SQL Dumps)

> [!CAUTION]
> Fase ini SANGAT PENTING. Karena server Alibaba sudah expired, Anda harus menggunakan file SQL dump yang ada di local (`agenda_ptpn_new (1).sql` dan `cash_bank_new (1).sql`).

### 3.1 Buat Database dan User MySQL

```bash
# Login ke MySQL
sudo mysql -u root -p

# Di dalam MySQL, jalankan:
```

```sql
-- Buat database utama (Agenda Online)
CREATE DATABASE agenda_ptpn_new CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Buat database kedua (Cash Bank)
CREATE DATABASE cash_bank_new CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Buat user untuk aplikasi
CREATE USER 'agenda_user'@'localhost' IDENTIFIED BY 'PASSWORD_ANDA_YANG_KUAT';

-- Berikan akses ke kedua database
GRANT ALL PRIVILEGES ON agenda_ptpn_new.* TO 'agenda_user'@'localhost';
GRANT ALL PRIVILEGES ON cash_bank_new.* TO 'agenda_user'@'localhost';

-- Apply privileges
FLUSH PRIVILEGES;

EXIT;
```

### 3.2 Upload File SQL ke Server

```bash
# Dari LOCAL machine (bukan di server), jalankan:
# Upload file SQL dump ke server
scp -i your-key.pem "agenda_ptpn_new (1).sql" ubuntu@YOUR_AWS_IP:/home/ubuntu/
scp -i your-key.pem "cash_bank_new (1).sql" ubuntu@YOUR_AWS_IP:/home/ubuntu/
```

> **Catatan**: File `agenda_ptpn_new (1).sql` berukuran ~35MB. Pastikan koneksi internet stabil.

### 3.3 Import Database

```bash
# Kembali SSH ke server, lalu import:

# Import database agenda (ini mungkin butuh beberapa menit)
mysql -u agenda_user -p agenda_ptpn_new < "/home/ubuntu/agenda_ptpn_new (1).sql"

# Import database cash bank
mysql -u agenda_user -p cash_bank_new < "/home/ubuntu/cash_bank_new (1).sql"
```

### 3.4 Verifikasi Import

```bash
# Login MySQL dan cek
mysql -u agenda_user -p

# Di dalam MySQL:
USE agenda_ptpn_new;
SHOW TABLES;
SELECT COUNT(*) FROM dokumens;

USE cash_bank_new;
SHOW TABLES;

EXIT;
```

---

## FASE 4: Deploy Aplikasi Laravel

### 4.1 Clone Repository

```bash
# Buat direktori project
sudo mkdir -p /var/www/agenda_online_ptpn
sudo chown -R ubuntu:ubuntu /var/www/agenda_online_ptpn

# Clone dari GitHub
cd /var/www
git clone https://github.com/YOUR_USERNAME/YOUR_REPO.git agenda_online_ptpn
cd agenda_online_ptpn
```

> **Alternatif**: Jika belum push ke GitHub, upload langsung dari local:
> ```bash
> # Di LOCAL (exclude node_modules dan vendor):
> rsync -avz --exclude='node_modules' --exclude='vendor' --exclude='.env' \
>   -e "ssh -i your-key.pem" \
>   ./ ubuntu@YOUR_AWS_IP:/var/www/agenda_online_ptpn/
> ```

### 4.2 Setup Environment File (.env)

```bash
cd /var/www/agenda_online_ptpn
cp .env.example .env
nano .env
```

**Edit `.env` dengan konfigurasi production berikut:**

```ini
APP_NAME="Agenda Online PTPN"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://yourdomain.com

# ========================================
# DATABASE UTAMA (Agenda Online)
# ========================================
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=agenda_ptpn_new
DB_USERNAME=agenda_user
DB_PASSWORD=PASSWORD_ANDA_YANG_KUAT

# ========================================
# DATABASE CASH BANK NEW
# ========================================
CASH_BANK_NEW_DB_HOST=127.0.0.1
CASH_BANK_NEW_DB_PORT=3306
CASH_BANK_NEW_DB_DATABASE=cash_bank_new
CASH_BANK_NEW_DB_USERNAME=agenda_user
CASH_BANK_NEW_DB_PASSWORD=PASSWORD_ANDA_YANG_KUAT

# ========================================
# DATABASE CASH BANK (Legacy — arahkan ke cash_bank_new juga)
# ========================================
CASH_BANK_DB_HOST=127.0.0.1
CASH_BANK_DB_PORT=3306
CASH_BANK_DB_DATABASE=cash_bank_new
CASH_BANK_DB_USERNAME=agenda_user
CASH_BANK_DB_PASSWORD=PASSWORD_ANDA_YANG_KUAT

# ========================================
# SESSION & CACHE
# ========================================
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

# ========================================
# FONNTE WHATSAPP GATEWAY
# ========================================
FONNTE_API_TOKEN=WBK1rsTTiSxhSY4LTdsu
FONNTE_API_URL=https://api.fonnte.com/send
FONNTE_COUNTRY_CODE=62
FONNTE_DELAY=5
WHATSAPP_NOTIFICATIONS_ENABLED=true
WHATSAPP_NOTIFICATION_COOLDOWN=24

# ========================================
# PUSHER (jika digunakan)
# ========================================
BROADCAST_CONNECTION=pusher
PUSHER_APP_ID=your_pusher_app_id
PUSHER_APP_KEY=your_pusher_key
PUSHER_APP_SECRET=your_pusher_secret
PUSHER_APP_CLUSTER=ap1
```

### 4.3 Install Dependencies

```bash
# Install Composer dependencies (production mode)
composer install --no-dev --optimize-autoloader

# Generate application key
php artisan key:generate

# Install Node.js dependencies
npm install

# Build frontend assets (Vite + Tailwind)
npm run build
```

### 4.4 Jalankan Migration

> [!IMPORTANT]
> Karena Anda sudah **import SQL dump** yang berisi data lengkap (termasuk tabel & data), mungkin sebagian besar migration sudah ada di tabel `migrations`. Berikut cara mengatasinya:

**Cek apakah tabel `migrations` sudah ada di database:**

```bash
# Login MySQL
mysql -u agenda_user -p agenda_ptpn_new -e "SELECT * FROM migrations ORDER BY id DESC LIMIT 10;"
```

**Jika tabel migrations SUDAH ADA dan terisi** (artinya dump dari production):
```bash
# Jalankan migration untuk menambahkan migration baru yang belum ada
php artisan migrate --force
```

**Jika tabel migrations TIDAK ADA atau KOSONG**:
```bash
# Tandai semua migration sebagai sudah dijalankan (karena tabel sudah dibuat dari SQL dump)
# Ini TIDAK akan menjalankan migration, tapi hanya menandai record-nya
# PERHATIAN: Hanya lakukan ini jika tabel-tabel sudah ada dari SQL dump!

# Cara 1 (Recommended): Cek status dulu
php artisan migrate:status

# Cara 2: Jika semua tabel sudah ada dari dump, reset migration tracking
php artisan migrate --pretend  # Preview dulu, JANGAN jalankan langsung!

# Jika semua tabel memang sudah ada, jalankan:
php artisan migrate --force
# Migration akan skip tabel yang sudah ada (Laravel mengecek Schema::hasTable/hasColumn)
```

### 4.5 Optimize & Cache

```bash
# Clear semua cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Buat cache untuk production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Buat symbolic link storage
php artisan storage:link
```

### 4.6 Set Permissions

```bash
# Set ownership ke www-data (user Nginx/PHP-FPM)
sudo chown -R www-data:www-data /var/www/agenda_online_ptpn
sudo chmod -R 755 /var/www/agenda_online_ptpn
sudo chmod -R 775 /var/www/agenda_online_ptpn/storage
sudo chmod -R 775 /var/www/agenda_online_ptpn/bootstrap/cache
```

---

## FASE 5: Konfigurasi Nginx

### 5.1 Buat Virtual Host

```bash
sudo nano /etc/nginx/sites-available/agenda_online_ptpn
```

**Paste konfigurasi berikut:**

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;  # Ganti dengan domain/IP Anda
    root /var/www/agenda_online_ptpn/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    # Upload limit (sesuaikan jika perlu upload file besar)
    client_max_body_size 50M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    # Deny access to hidden files (e.g., .env)
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache static assets
    location ~* \.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
}
```

### 5.2 Enable Site & Test

```bash
# Enable virtual host
sudo ln -s /etc/nginx/sites-available/agenda_online_ptpn /etc/nginx/sites-enabled/

# Hapus default site (opsional)
sudo rm -f /etc/nginx/sites-enabled/default

# Test konfigurasi
sudo nginx -t

# Restart Nginx
sudo systemctl restart nginx
```

---

## FASE 6: Setup HTTPS dengan Let's Encrypt (Disarankan)

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Dapatkan SSL certificate (ganti domain)
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Test auto-renewal
sudo certbot renew --dry-run
```

> Certbot akan otomatis mengupdate konfigurasi Nginx untuk redirect HTTP → HTTPS.

---

## FASE 7: Konfigurasi PHP-FPM (Opsional tapi Disarankan)

```bash
sudo nano /etc/php/8.2/fpm/php.ini
```

**Ubah setting berikut:**

```ini
upload_max_filesize = 50M
post_max_size = 50M
max_execution_time = 300
memory_limit = 256M
```

```bash
# Restart PHP-FPM
sudo systemctl restart php8.2-fpm
```

---

## FASE 8: Setup Queue Worker (Jika Digunakan)

Aplikasi ini menggunakan `QUEUE_CONNECTION=database`. Setup systemd service:

```bash
sudo nano /etc/systemd/system/agenda-queue.service
```

```ini
[Unit]
Description=Agenda Online PTPN Queue Worker
After=network.target mysql.service

[Service]
User=www-data
Group=www-data
WorkingDirectory=/var/www/agenda_online_ptpn
ExecStart=/usr/bin/php artisan queue:work database --sleep=3 --tries=3 --max-time=3600
Restart=always
RestartSec=5
StandardOutput=journal
StandardError=journal

[Install]
WantedBy=multi-user.target
```

```bash
# Enable dan start
sudo systemctl daemon-reload
sudo systemctl enable agenda-queue
sudo systemctl start agenda-queue
sudo systemctl status agenda-queue
```

---

## FASE 9: Setup Cron Job untuk Laravel Scheduler

```bash
# Edit crontab untuk www-data
sudo crontab -u www-data -e

# Tambahkan baris ini:
* * * * * cd /var/www/agenda_online_ptpn && php artisan schedule:run >> /dev/null 2>&1
```

---

## ✅ Checklist Verifikasi Setelah Deploy

| # | Item | Status |
|---|------|--------|
| 1 | Buka website di browser → Tampil halaman login | ☐ |
| 2 | Login dengan akun yang ada di database | ☐ |
| 3 | Data dokumen tampil dengan benar | ☐ |
| 4 | Data Cash Bank tampil (koneksi ke database kedua) | ☐ |
| 5 | Upload file berfungsi | ☐ |
| 6 | WhatsApp notification terkirim | ☐ |
| 7 | HTTPS berfungsi (jika setup SSL) | ☐ |
| 8 | Cek log error: `tail -f storage/logs/laravel.log` | ☐ |

---

## 🔧 Quick Deploy Script (Untuk Update Selanjutnya)

Setelah setup awal selesai, untuk update berikutnya cukup jalankan:

```bash
cd /var/www/agenda_online_ptpn

# Pull latest code
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader
npm install
npm run build

# Run new migrations
php artisan migrate --force

# Clear & optimize cache
php artisan config:clear && php artisan cache:clear && php artisan route:clear && php artisan view:clear
php artisan config:cache && php artisan route:cache && php artisan view:cache

# Restart services
sudo systemctl restart php8.2-fpm
sudo systemctl restart agenda-queue
```

Atau gunakan script `deploy.sh` yang sudah ada di project.

---

## ⚠️ Troubleshooting

### Error: "SQLSTATE[HY000] [2002] Connection refused"
→ MySQL belum jalan. Jalankan: `sudo systemctl start mysql`

### Error: "Permission denied" di storage/
→ Fix permissions:
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Error: 502 Bad Gateway
→ PHP-FPM belum jalan: `sudo systemctl restart php8.2-fpm`

### Error: "No application encryption key"
→ Generate key: `php artisan key:generate`

### Build Vite gagal (out of memory)
→ Tambah swap memory:
```bash
sudo fallocate -l 2G /swapfile
sudo chmod 600 /swapfile
sudo mkswap /swapfile
sudo swapon /swapfile
echo '/swapfile swap swap defaults 0 0' | sudo tee -a /etc/fstab
```

### Migration conflict (tabel sudah ada)
→ Jalankan `php artisan migrate:status` untuk cek status, lalu:
```bash
php artisan migrate --force  # Akan skip yang sudah ada
```

---

## 📝 Catatan Penting

1. **Jangan lupa backup** `.env` setelah semua konfigurasi selesai
2. **FONNTE_API_TOKEN** sudah tersedia di `.env` — pastikan masih aktif
3. **Pusher credentials** — jika real-time notification digunakan, isi credentials Pusher
4. Project ini connect ke **2 database** di server yang sama, jadi pastikan kedua database terisi data dari SQL dump
5. Simpan file `.pem` (SSH key) di tempat aman — ini satu-satunya cara akses SSH

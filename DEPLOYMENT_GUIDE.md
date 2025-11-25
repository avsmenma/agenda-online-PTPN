# 🚀 Panduan Deployment ke Production Server

## 📋 Checklist Sebelum Deployment

### 1. Persiapan Lokal (Development)
- [ ] Pastikan semua perubahan sudah di-commit
- [ ] Pastikan tidak ada error di local
- [ ] Test semua fitur utama
- [ ] Backup database production (jika perlu)

### 2. Persiapan Server
- [ ] Backup database production
- [ ] Backup file `.env` di server
- [ ] Pastikan koneksi SSH ke server berjalan
- [ ] Pastikan Git sudah terkonfigurasi di server

---

## 🔄 Langkah-langkah Deployment

### **STEP 1: Git Push dari Local ke GitHub**

```bash
# 1. Cek status git
git status

# 2. Tambahkan semua perubahan
git add .

# 3. Commit perubahan (ganti pesan sesuai kebutuhan)
git commit -m "Update: Fitur export Excel/PDF, filter selesai/belum selesai, dan perbaikan lainnya"

# 4. Push ke GitHub
git push origin main
```

### **STEP 2: Deployment di Server (VPS Ubuntu Alibaba)**

**SSH ke server terlebih dahulu:**
```bash
ssh user@your-server-ip
```

**Setelah masuk ke server, jalankan perintah berikut:**

```bash
# 1. Masuk ke direktori project
cd /path/to/your/project  # Ganti dengan path project Anda

# 2. Backup .env (jika belum)
cp .env .env.backup.$(date +%Y%m%d_%H%M%S)

# 3. Pull perubahan terbaru dari GitHub
git pull origin main

# 4. Install/Update Composer Dependencies
composer install --no-dev --optimize-autoloader

# 5. Install/Update NPM Dependencies
npm install

# 6. Build Assets (Vite)
npm run build

# 7. Run Database Migrations
php artisan migrate --force

# 8. Clear All Cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 9. Optimize Application
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 10. Set Permissions (jika perlu)
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# 11. Restart PHP-FPM (jika menggunakan PHP-FPM)
sudo systemctl restart php8.2-fpm  # Sesuaikan versi PHP Anda
# atau
sudo service php-fpm restart
```

---

## 📝 Script Deployment Otomatis

Saya akan membuatkan script bash yang bisa dijalankan di server untuk mempermudah deployment.

---

## ⚠️ Catatan Penting

### Database Migrations
- Pastikan backup database dilakukan SEBELUM menjalankan `php artisan migrate`
- Jika ada data penting, pertimbangkan untuk membuat backup manual
- Migrations akan menambahkan kolom baru tanpa menghapus data yang ada

### Environment Variables
- Pastikan file `.env` di server sudah dikonfigurasi dengan benar
- Jangan commit file `.env` ke Git (sudah ada di .gitignore)
- Setelah pull, pastikan `.env` masih ada dan konfigurasinya benar

### Assets & Cache
- Setelah `npm run build`, assets baru akan tersedia
- Clear cache penting untuk memastikan perubahan terlihat
- Jika masih ada masalah, coba hard refresh browser (Ctrl+Shift+R)

### Permissions
- Pastikan folder `storage` dan `bootstrap/cache` memiliki permission yang benar
- User web server (biasanya `www-data`) harus bisa menulis ke folder tersebut

---

## 🔍 Troubleshooting

### Jika website masih menampilkan versi lama:
1. Clear browser cache (Ctrl+Shift+R)
2. Clear Laravel cache: `php artisan cache:clear`
3. Clear view cache: `php artisan view:clear`
4. Restart web server: `sudo systemctl restart nginx` atau `sudo systemctl restart apache2`

### Jika ada error migration:
1. Cek log: `tail -f storage/logs/laravel.log`
2. Rollback migration terakhir: `php artisan migrate:rollback`
3. Perbaiki masalah, lalu jalankan migration lagi

### Jika assets tidak ter-load:
1. Pastikan `npm run build` berhasil
2. Cek file `public/build/manifest.json` ada
3. Clear cache: `php artisan view:clear`

---

## 📞 Support

Jika ada masalah saat deployment, cek:
- Log Laravel: `storage/logs/laravel.log`
- Log Nginx/Apache: `/var/log/nginx/error.log` atau `/var/log/apache2/error.log`
- Status service: `sudo systemctl status nginx` atau `sudo systemctl status apache2`



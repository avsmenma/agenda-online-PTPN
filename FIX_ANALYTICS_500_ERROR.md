# 🔧 Panduan Fix Error 500 di Halaman Analytics Pembayaran

## 🔍 Analisis Masalah

Error 500 Internal Server Error terjadi ketika mengakses `/payment/analytics` di server, padahal di localhost berfungsi dengan baik.

## ⚠️ Kemungkinan Penyebab

1. **Query database terlalu berat** - mengambil semua dokumen sekaligus
2. **Format tanggal tidak konsisten** - tanggal_dibayar atau created_at mungkin null atau format berbeda
3. **Memory limit** - data terlalu banyak untuk diproses sekaligus
4. **Error pada saat filtering collection** - masalah dengan collection operations

## ✅ Perbaikan yang Sudah Dilakukan

### 1. **Error Handling**
- Menambahkan try-catch pada seluruh method
- Menambahkan error handling pada setiap operasi tanggal
- Menambahkan validasi input tahun

### 2. **Optimasi Query**
- Menggunakan chunking untuk mengurangi beban memory
- Optimasi query untuk mendapatkan available years
- Menambahkan filter null values

### 3. **Null Safety**
- Menambahkan pengecekan null pada semua operasi tanggal
- Menambahkan fallback values jika data tidak ditemukan
- Menambahkan validasi format tanggal

## 🛠️ Langkah Debugging di Server

### **Step 1: Cek Log Error di Server**

```bash
# SSH ke server
ssh user@47.236.49.229

# Masuk ke direktori project
cd /var/www/agenda_online_ptpn
# atau
cd /path/to/your/project

# Lihat log error Laravel
tail -n 100 storage/logs/laravel.log | grep -i "error\|exception\|analytics"

# Atau lihat log real-time
tail -f storage/logs/laravel.log
```

### **Step 2: Enable Debug Mode (Sementara)**

Edit file `.env` di server:

```bash
APP_DEBUG=true
LOG_LEVEL=debug
```

Lalu clear config cache:

```bash
php artisan config:clear
php artisan config:cache
```

**PENTING: Setelah debugging, set kembali ke `false`!**

### **Step 3: Test Query Manual**

Login ke MySQL dan test query:

```bash
mysql -u your_username -p your_database

# Test query untuk tahun
SELECT DISTINCT YEAR(tanggal_dibayar) as year 
FROM dokumens 
WHERE tanggal_dibayar IS NOT NULL 
ORDER BY year DESC;

SELECT DISTINCT YEAR(created_at) as year 
FROM dokumens 
WHERE nomor_agenda IS NOT NULL 
AND created_at IS NOT NULL
ORDER BY year DESC;
```

### **Step 4: Cek Memory Limit**

Cek memory limit PHP:

```bash
php -i | grep memory_limit
```

Jika perlu, tambahkan di `.env`:

```bash
PHP_MEMORY_LIMIT=256M
```

### **Step 5: Cek Permissions**

Pastikan permissions benar:

```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## 🔧 Perbaikan Kode yang Sudah Dilakukan

1. ✅ Menambahkan try-catch wrapper pada method analytics()
2. ✅ Menambahkan error handling pada semua operasi tanggal
3. ✅ Menggunakan chunking untuk mengurangi beban memory
4. ✅ Optimasi query untuk available years
5. ✅ Menambahkan validasi input tahun
6. ✅ Menambahkan default values jika data tidak ditemukan

## 📋 Checklist Debugging

- [ ] Cek log error di `storage/logs/laravel.log`
- [ ] Enable debug mode (sementara) dan lihat error detail
- [ ] Test query database manual
- [ ] Cek memory limit PHP
- [ ] Pastikan file sudah ter-deploy ke server
- [ ] Clear cache: `php artisan config:clear && php artisan cache:clear`
- [ ] Restart PHP-FPM dan web server

## 🚀 Command Cepat untuk Fix

Jalankan di server:

```bash
cd /var/www/agenda_online_ptpn

# Clear semua cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Re-cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Lihat log error
tail -n 200 storage/logs/laravel.log | grep -i "error\|exception"
```

## 📞 Informasi yang Diperlukan

Jika masalah masih terjadi, kirimkan:

1. **Log error lengkap** dari `storage/logs/laravel.log`
2. **Hasil query test** tahun (jika ada)
3. **PHP memory limit** (`php -i | grep memory_limit`)
4. **Jumlah dokumen** di database (`SELECT COUNT(*) FROM dokumens WHERE nomor_agenda IS NOT NULL`)
5. **Versi PHP** di server (`php -v`)

## 🔗 File yang Diubah

- `app/Http/Controllers/DashboardPembayaranController.php` - Method `analytics()`

## 📝 Catatan

- Error handling sudah ditambahkan untuk menangkap semua error
- Query sudah di-optimasi dengan chunking
- Null safety sudah ditambahkan pada semua operasi tanggal
- Jika masih error, kemungkinan masalah spesifik di server (memory, database, dll)


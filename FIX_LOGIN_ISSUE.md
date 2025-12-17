# Fix Login Issue - Semua User dan Password Salah

## Masalah
Setelah menambahkan connection database `cash_bank`, semua user dan password menjadi salah saat login.

## Penyebab
Default connection di `config/database.php` adalah `sqlite` (seharusnya `mysql`), dan mungkin ada cache config yang masih menggunakan nilai lama.

## Solusi

### 1. Clear Cache Config (PENTING!)

Jalankan perintah berikut di terminal:

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

**Atau jika menggunakan server production:**

```bash
cd /var/www/agendareg5.online
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### 2. Pastikan File `.env` Benar

Pastikan file `.env` memiliki konfigurasi database yang benar:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=agenda_ptpn_new
DB_USERNAME=agenda_user_new
DB_PASSWORD=@Skw12345
```

### 3. Verifikasi Koneksi Database

Test koneksi database dengan menjalankan:

```bash
php artisan tinker
```

Kemudian di tinker:
```php
DB::connection()->getPdo();
User::count();
```

Jika ada error, berarti ada masalah dengan koneksi database.

### 4. Cek Tabel Users

Pastikan tabel `users` ada di database:

```sql
USE agenda_ptpn_new;
SHOW TABLES LIKE 'users';
SELECT * FROM users LIMIT 5;
```

### 5. Reset Password User (Jika Perlu)

Jika user masih tidak bisa login, coba reset password:

```bash
php artisan tinker
```

Kemudian:
```php
$user = User::where('username', 'ibutara')->first();
$user->password = bcrypt('ibua825');
$user->save();
```

## Perubahan yang Sudah Dibuat

1. ✅ Default connection di `config/database.php` diubah dari `sqlite` ke `mysql`
2. ✅ Connection `cash_bank` ditambahkan untuk database `cash_bank_new`

## Catatan

- **PENTING:** Setelah perubahan config, selalu jalankan `php artisan config:clear`
- Pastikan file `.env` memiliki `DB_CONNECTION=mysql`
- Jika masih error, cek log Laravel: `storage/logs/laravel.log`


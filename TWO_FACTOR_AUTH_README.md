# Two-Factor Authentication (2FA) Implementation

## Overview
Fitur 2FA telah diimplementasikan menggunakan TOTP (Time-based One-Time Password) standar RFC 6238, kompatibel dengan aplikasi authenticator seperti Google Authenticator, Authy, Microsoft Authenticator, dll.

## Installation Steps

### 1. Install Dependencies
```bash
composer install
```
Library `pragmarx/google2fa` sudah ditambahkan ke `composer.json`.

### 2. Run Migration
```bash
php artisan migrate
```
Migration akan menambahkan kolom berikut ke tabel `users`:
- `two_factor_enabled` (boolean)
- `two_factor_secret` (text, encrypted)
- `two_factor_confirmed_at` (timestamp)
- `two_factor_recovery_codes` (json, encrypted)

### 3. Clear Cache (jika diperlukan)
```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## Features

### 1. Enable 2FA
- Route: `/2fa/setup`
- User dapat mengaktifkan 2FA dengan:
  - Scan QR Code dengan aplikasi authenticator
  - Atau input manual menggunakan secret key
  - Verifikasi dengan kode 6 digit

### 2. Login dengan 2FA
- Setelah login dengan email/password, jika user memiliki 2FA aktif:
  - User akan di-redirect ke halaman verifikasi 2FA
  - User harus memasukkan kode 6 digit dari aplikasi authenticator
  - Setelah verifikasi berhasil, user akan login

### 3. Recovery Codes
- Setiap user mendapatkan 8 recovery codes saat enable 2FA
- Recovery codes dapat digunakan jika kehilangan akses ke aplikasi authenticator
- Recovery codes dapat di-regenerate

### 4. Disable 2FA
- User dapat menonaktifkan 2FA dengan memasukkan password
- Semua data 2FA akan dihapus

## Routes

### Guest Routes (tanpa authentication)
- `GET /2fa/verify` - Halaman verifikasi 2FA (setelah login)
- `POST /2fa/verify` - Submit kode 2FA
- `POST /2fa/verify-recovery` - Verifikasi menggunakan recovery code

### Authenticated Routes (perlu login)
- `GET /2fa/setup` - Halaman setup 2FA
- `POST /2fa/enable` - Aktifkan 2FA
- `GET /2fa/recovery-codes` - Lihat recovery codes
- `POST /2fa/regenerate-recovery-codes` - Generate ulang recovery codes
- `POST /2fa/disable` - Nonaktifkan 2FA

## Security Features

1. **Encrypted Secret Key**: Secret key disimpan dalam bentuk encrypted menggunakan Laravel's `encrypt()`
2. **Encrypted Recovery Codes**: Recovery codes juga di-encrypt
3. **Time Window**: Kode 2FA valid untuk 2 time windows (60 detik) untuk mengakomodasi clock skew
4. **Session Management**: User tidak login sampai 2FA diverifikasi
5. **Rate Limiting**: Dapat ditambahkan rate limiting untuk mencegah brute force

## User Flow

### First Time Setup
1. User login normal (email + password)
2. User mengakses menu "Keamanan 2FA" di sidebar
3. User scan QR Code atau input secret key manual
4. User memasukkan kode 6 digit untuk verifikasi
5. 2FA diaktifkan, recovery codes ditampilkan
6. User menyimpan recovery codes

### Login dengan 2FA Aktif
1. User login dengan email + password
2. Sistem check apakah 2FA aktif
3. Jika aktif, redirect ke halaman verifikasi 2FA
4. User memasukkan kode 6 digit dari aplikasi authenticator
5. Setelah verifikasi berhasil, user login

### Recovery (Kehilangan Device)
1. User login dengan email + password
2. Di halaman verifikasi 2FA, klik "Kehilangan akses ke aplikasi authenticator?"
3. User memasukkan salah satu recovery code
4. Recovery code yang digunakan akan dihapus
5. User login berhasil

## Files Created/Modified

### New Files
- `database/migrations/2025_01_15_000001_add_two_factor_columns_to_users_table.php`
- `app/Http/Controllers/TwoFactorController.php`
- `resources/views/auth/2fa/verify.blade.php`
- `resources/views/auth/2fa/setup.blade.php`
- `resources/views/auth/2fa/recovery-codes.blade.php`
- `resources/views/auth/2fa/already-enabled.blade.php`

### Modified Files
- `composer.json` - Added `pragmarx/google2fa-laravel`
- `app/Models/User.php` - Added 2FA methods and fillable fields
- `app/Http/Controllers/Auth/LoginController.php` - Added 2FA check after login
- `routes/web.php` - Added 2FA routes
- `resources/views/layouts/app.blade.php` - Added 2FA menu item

## Testing

### Test Enable 2FA
1. Login sebagai user
2. Akses `/2fa/setup`
3. Scan QR Code dengan Google Authenticator
4. Masukkan kode 6 digit
5. Verifikasi berhasil, recovery codes ditampilkan

### Test Login dengan 2FA
1. Logout
2. Login dengan email + password
3. Harus redirect ke halaman verifikasi 2FA
4. Masukkan kode 6 digit dari Google Authenticator
5. Login berhasil

### Test Recovery Code
1. Login dengan email + password
2. Di halaman verifikasi, klik "Kehilangan akses"
3. Masukkan salah satu recovery code
4. Login berhasil

## Notes

- Secret key dan recovery codes di-encrypt menggunakan Laravel's encryption
- Kode 2FA valid untuk 2 time windows (60 detik) untuk mengakomodasi clock skew
- Recovery codes dapat digunakan sekali, lalu dihapus dari database
- User dapat regenerate recovery codes kapan saja
- 2FA bersifat optional, user dapat memilih untuk mengaktifkan atau tidak


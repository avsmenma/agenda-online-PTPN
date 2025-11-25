# Real-time Notification System Setup Guide

## Overview
Sistem notifikasi real-time untuk alur dokumen dari IbuA ke IbuB menggunakan Laravel Broadcasting dengan Pusher.

## Prerequisites
- Laravel 12.x
- Pusher account (gratis di https://pusher.com)
- Node.js & npm

## Installation Steps

### 1. Install Dependencies
```bash
# Backend (sudah terinstall)
composer require pusher/pusher-php-server

# Frontend (sudah terinstall)
npm install --save laravel-echo pusher-js
```

### 2. Setup Pusher Account
1. Daftar di https://pusher.com (gratis)
2. Buat aplikasi baru
3. Pilih cluster terdekat (misalnya: ap1 untuk Asia Pacific)
4. Copy credentials:
   - App ID
   - Key
   - Secret
   - Cluster

### 3. Configure Environment Variables
Tambahkan ke file `.env`:

```env
BROADCAST_DRIVER=pusher

PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=ap1
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
```

### 4. Files Already Created/Modified

#### Backend:
- ✅ `config/broadcasting.php` - Broadcasting configuration
- ✅ `routes/channels.php` - Channel authorization
- ✅ `app/Events/DocumentSent.php` - Event class dengan ShouldBroadcast
- ✅ `app/Http/Controllers/DokumenController.php` - Updated untuk broadcast event
- ✅ `routes/web.php` - Added Broadcast::routes()

#### Frontend:
- ✅ `resources/views/layouts/app.blade.php` - Notification UI & JavaScript
- ✅ `resources/js/app.js` - Laravel Echo setup

### 5. How It Works

#### Flow:
1. **IbuA mengirim dokumen** → `DokumenController::sendToIbuB()`
2. **Event di-broadcast** → `DocumentSent` event
3. **IbuB menerima notifikasi** → Laravel Echo listen ke channel `documents.ibuB`
4. **Smart Detection** → Cek apakah user sedang aktif (input/modal/typing)
5. **Notification Display**:
   - Jika user aktif → Hanya toast notification (tidak refresh)
   - Jika user idle → Toast notification + option refresh

#### Features:
- ✅ Real-time toast notification (top-right)
- ✅ Badge counter di sidebar menu "Daftar Dokumen"
- ✅ Pulse animation pada badge
- ✅ Menu highlight saat ada notifikasi baru
- ✅ Smart detection (tidak refresh jika user sedang input)
- ✅ Auto-remove notification setelah 10 detik
- ✅ Button "Refresh Data" dan "Lihat Detail"

### 6. Testing

#### Test Scenario 1: User IbuB sedang edit form
1. Buka halaman edit dokumen di IbuB
2. Mulai ketik di form
3. IbuA kirim dokumen baru
4. **Expected**: Notification muncul, halaman TIDAK refresh

#### Test Scenario 2: User IbuB idle
1. Buka dashboard IbuB (tidak ada input aktif)
2. IbuA kirim dokumen baru
3. **Expected**: Notification muncul + bisa klik "Refresh Data"

#### Test Scenario 3: Multiple notifications
1. IbuA kirim beberapa dokumen berturut-turut
2. **Expected**: Badge counter update, semua notification muncul

### 7. Troubleshooting

#### Notifikasi tidak muncul:
1. Cek browser console untuk error
2. Pastikan Pusher credentials benar di `.env`
3. Pastikan user berada di module IbuB (`$module === 'ibuB'`)
4. Cek koneksi Pusher di browser console:
   ```javascript
   window.Echo.connector.pusher.connection.state
   ```

#### Connection Error:
- Pastikan `BROADCAST_DRIVER=pusher` di `.env`
- Pastikan route `/broadcasting/auth` accessible
- Cek firewall untuk WebSocket connections

#### Event tidak ter-broadcast:
- Pastikan `event()` dipanggil setelah `DB::commit()`
- Cek Laravel logs: `storage/logs/laravel.log`
- Test dengan `php artisan tinker`:
  ```php
  event(new App\Events\DocumentSent($dokumen, 'ibuA', 'ibuB'));
  ```

### 8. Security Notes

- Private channels memerlukan authentication via `/broadcasting/auth`
- Channel authorization di `routes/channels.php` (saat ini allow all, sesuaikan untuk production)
- CSRF token sudah di-handle di JavaScript

### 9. Customization

#### Mengubah auto-remove timeout:
Edit di `resources/views/layouts/app.blade.php`:
```javascript
setTimeout(() => {
  removeNotification(notificationId);
}, 10000); // Ubah 10000 (10 detik) sesuai kebutuhan
```

#### Mengubah notification style:
Edit CSS di `resources/views/layouts/app.blade.php` (section `/* Notification System Styles */`)

#### Menambah department lain:
1. Update `DocumentSent` event untuk support department lain
2. Update channel di `routes/channels.php`
3. Update JavaScript di layout untuk listen channel baru

### 10. Production Checklist

- [ ] Update channel authorization di `routes/channels.php` dengan proper authentication
- [ ] Setup queue worker untuk broadcast (jika menggunakan queue)
- [ ] Enable HTTPS untuk Pusher (forceTLS: true)
- [ ] Setup error monitoring (Sentry, etc.)
- [ ] Test dengan multiple concurrent users
- [ ] Setup rate limiting untuk broadcast events
- [ ] Monitor Pusher dashboard untuk usage

## Support

Jika ada masalah, cek:
1. Browser console untuk JavaScript errors
2. Laravel logs: `storage/logs/laravel.log`
3. Pusher dashboard untuk connection status
4. Network tab di browser untuk WebSocket connections





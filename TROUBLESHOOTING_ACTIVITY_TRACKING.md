# 🔧 Troubleshooting Activity Tracking

## ❌ Masalah: Activity Tracking Tidak Muncul

Jika setelah login dengan 2 akun berbeda, activity tracking tidak muncul, ikuti langkah-langkah berikut:

---

## ✅ Checklist Debugging

### 1. **Pastikan Migration Sudah Dijalankan**

Jalankan di server:
```bash
php artisan migrate
```

Cek apakah table `document_activities` sudah ada:
```bash
php artisan tinker
>>> Schema::hasTable('document_activities')
```

Harus return `true`.

---

### 2. **Cek Browser Console**

Buka **Developer Tools** (F12) di browser, lalu cek **Console** tab.

**Yang harus muncul:**
```
🎯 Activity Tracking: Initializing... {dokumenId: 1735, currentUserId: 3, ...}
✅ Echo is ready, initializing activity tracking
🚀 Initializing activity tracking...
📡 Listening to channel: document.1735
✅ Subscribed to channel: document.1735
✅ Activity tracked: viewing
📊 Activities loaded: {viewing: [...], editing: [...]}
```

**Jika ada error:**
- `Laravel Echo not available` → Echo belum siap, tunggu beberapa detik
- `HTTP error! status: 404` → Route tidak ditemukan
- `HTTP error! status: 500` → Ada error di server
- `CORS error` → Masalah CORS

---

### 3. **Cek Network Tab**

Buka **Network** tab di Developer Tools, lalu:
1. Filter: `activity` atau `activities`
2. Refresh halaman
3. Cek request ke `/api/documents/{id}/activity` dan `/api/documents/{id}/activities`

**Request yang harus ada:**
- `POST /api/documents/1735/activity` → Status: 200
- `GET /api/documents/1735/activities` → Status: 200

**Jika 404:**
- Route tidak terdaftar
- Jalankan: `php artisan route:list | grep activity`

**Jika 500:**
- Ada error di server
- Cek: `storage/logs/laravel.log`

---

### 4. **Cek Laravel Logs**

Di server, jalankan:
```bash
tail -f storage/logs/laravel.log
```

Lalu buka halaman inbox. Harus muncul log:
```
[timestamp] local.INFO: Activity tracked successfully
```

**Jika ada error:**
- `Table 'document_activities' doesn't exist` → Migration belum dijalankan
- `Column not found` → Migration tidak lengkap
- `Class 'App\Models\DocumentActivity' not found` → Model tidak ditemukan

---

### 5. **Cek Route Terdaftar**

Jalankan di server:
```bash
php artisan route:list | grep activity
```

Harus muncul:
```
POST   api/documents/{dokumen}/activity
GET    api/documents/{dokumen}/activities
POST   api/documents/{dokumen}/activity/stop
```

**Jika tidak muncul:**
- Route belum terdaftar
- Clear route cache: `php artisan route:clear`

---

### 6. **Cek Laravel Echo Connection**

Di browser console, ketik:
```javascript
window.Echo
```

Harus return object Echo, bukan `undefined`.

**Jika undefined:**
- Echo belum di-load
- Cek apakah script Pusher & Echo ada di `<head>` atau sebelum closing `</body>`

**Cek connection status:**
```javascript
window.Echo.connector.pusher.connection.state
```

Harus return: `"connected"` atau `"connecting"`

---

### 7. **Test API Endpoint Manual**

Buka browser, akses langsung:
```
http://47.236.49.229/api/documents/1735/activities
```

**Harus return JSON:**
```json
{
  "success": true,
  "activities": {
    "viewing": [...],
    "editing": [...]
  }
}
```

**Jika error:**
- Cek apakah user sudah login
- Cek middleware `autologin` dan `web`
- Cek apakah dokumen dengan ID tersebut ada

---

### 8. **Cek Database**

Jalankan di server:
```bash
php artisan tinker
```

Lalu:
```php
// Cek apakah ada activities
\App\Models\DocumentActivity::count()

// Cek activities untuk dokumen tertentu
\App\Models\DocumentActivity::where('dokumen_id', 1735)->get()

// Cek apakah user sudah track activity
\App\Models\DocumentActivity::where('dokumen_id', 1735)
    ->where('user_id', 3)
    ->get()
```

---

### 9. **Cek Activity Panel di UI**

Activity panel harus muncul di sidebar kanan (di halaman inbox detail).

**Jika tidak muncul:**
- Cek apakah ada user lain yang sedang melihat dokumen yang sama
- Panel hanya muncul jika ada activity (minimal 1 user lain)
- Cek console untuk error JavaScript

**Untuk test:**
1. Buka dokumen di browser 1
2. Buka dokumen yang sama di browser 2 (user berbeda)
3. Panel harus muncul di kedua browser

---

### 10. **Force Show Activity Panel (Untuk Testing)**

Tambahkan di browser console:
```javascript
// Force show panel
document.getElementById('activity-panel').style.display = 'block';

// Force load activities
loadActivities();
```

---

## 🔍 Common Issues & Solutions

### Issue 1: "Laravel Echo not available"
**Solution:**
- Pastikan script Pusher & Echo di-load sebelum activity tracking script
- Tambahkan delay: `setTimeout(() => initActivityTracking(), 2000);`

### Issue 2: "404 Not Found" pada API
**Solution:**
```bash
php artisan route:clear
php artisan cache:clear
php artisan config:clear
```

### Issue 3: "Table doesn't exist"
**Solution:**
```bash
php artisan migrate
```

### Issue 4: "CSRF token mismatch"
**Solution:**
- Pastikan meta tag CSRF ada: `<meta name="csrf-token" content="...">`
- Pastikan header `X-CSRF-TOKEN` dikirim di request

### Issue 5: "Echo channel not subscribed"
**Solution:**
- Cek apakah channel adalah public channel (bukan private)
- Untuk private channel, perlu authorization
- Cek `routes/channels.php` untuk channel authorization

---

## 🧪 Testing Steps

### Step 1: Single User Test
1. Login dengan 1 user
2. Buka inbox detail: `/inbox/1735`
3. Buka console (F12)
4. Harus muncul: `✅ Activity tracked: viewing`
5. Harus muncul: `📊 Activities loaded`

### Step 2: Multi User Test
1. Browser 1: Login user A, buka `/inbox/1735`
2. Browser 2: Login user B, buka `/inbox/1735`
3. Browser 1: Harus muncul activity panel dengan user B
4. Browser 2: Harus muncul activity panel dengan user A

### Step 3: Real-time Test
1. Browser 1: Buka dokumen
2. Browser 2: Buka dokumen yang sama
3. Browser 1: Harus langsung muncul user B (tanpa refresh)
4. Browser 2: Harus langsung muncul user A (tanpa refresh)

---

## 📝 Debug Script (Copy ke Console)

```javascript
// Check Echo
console.log('Echo:', window.Echo);
console.log('Echo state:', window.Echo?.connector?.pusher?.connection?.state);

// Check channel
const channel = window.Echo.channel('document.1735');
console.log('Channel:', channel);

// Manual track activity
fetch('/api/documents/1735/activity', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Accept': 'application/json'
    },
    body: JSON.stringify({ activity_type: 'viewing' })
})
.then(r => r.json())
.then(d => console.log('Track result:', d))
.catch(e => console.error('Track error:', e));

// Manual load activities
fetch('/api/documents/1735/activities', {
    headers: { 'Accept': 'application/json' }
})
.then(r => r.json())
.then(d => console.log('Activities:', d))
.catch(e => console.error('Load error:', e));
```

---

## ✅ Quick Fix Commands

```bash
# 1. Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 2. Run migration
php artisan migrate

# 3. Check routes
php artisan route:list | grep activity

# 4. Check logs
tail -f storage/logs/laravel.log
```

---

## 🎯 Expected Behavior

**Ketika 2 user membuka dokumen yang sama:**

1. **Browser 1 (User A):**
   - Activity panel muncul di sidebar
   - Menampilkan: "User A (Anda)" dan "User B"
   - Status: 🟢 (green dot)

2. **Browser 2 (User B):**
   - Activity panel muncul di sidebar
   - Menampilkan: "User A" dan "User B (Anda)"
   - Status: 🟢 (green dot)

3. **Jika User B mulai edit:**
   - Browser 1: Warning muncul "⚠️ User B sedang mengedit dokumen ini"
   - Browser 2: Tidak ada warning (karena dia sendiri yang edit)

---

## 📞 Jika Masih Tidak Bekerja

1. **Cek semua checklist di atas**
2. **Copy error message dari console**
3. **Cek Laravel logs untuk error detail**
4. **Pastikan migration sudah dijalankan**
5. **Pastikan route sudah terdaftar**


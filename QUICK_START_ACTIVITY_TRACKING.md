# 🚀 Quick Start: Activity Tracking

## ⚡ Langkah Cepat untuk Mengaktifkan

### 1. **Jalankan Migration (WAJIB!)**

**Di server, jalankan:**
```bash
cd /var/www/agenda_online_ptpn
php artisan migrate
```

**Verifikasi table sudah dibuat:**
```bash
php artisan tinker
>>> Schema::hasTable('document_activities')
# Harus return: true
```

---

### 2. **Clear Cache**

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

---

### 3. **Test di Browser**

1. **Buka 2 browser berbeda** (Chrome normal + Chrome Incognito, atau 2 browser berbeda)
2. **Login dengan 2 user Team Verifikasi berbeda**
3. **Buka dokumen yang sama di inbox:**
   - Browser 1: `http://47.236.49.229/inbox/1735`
   - Browser 2: `http://47.236.49.229/inbox/1735`

4. **Buka Developer Tools (F12) di kedua browser**
5. **Cek Console tab** - harus muncul log:
   ```
   🎯 Activity Tracking: Initializing...
   ✅ Echo is ready, initializing activity tracking
   ✅ Activity tracked: viewing
   📊 Activities loaded: {viewing: [...], editing: []}
   ```

6. **Cek Activity Panel** di sidebar kanan - harus muncul panel dengan nama user lain

---

## 🔍 Debugging Cepat

### Cek Console Browser

Buka **F12 → Console**, harus muncul:
- ✅ `Laravel Echo initialized`
- ✅ `Pusher connected successfully`
- ✅ `Activity tracked: viewing`
- ✅ `Activities loaded`

**Jika ada error:**
- `404` → Route tidak ditemukan, jalankan: `php artisan route:clear`
- `500` → Error server, cek: `storage/logs/laravel.log`
- `Echo not available` → Tunggu beberapa detik, refresh halaman

---

### Test API Manual

Buka di browser (harus sudah login):
```
http://47.236.49.229/api/documents/1735/activities
```

**Harus return JSON:**
```json
{
  "success": true,
  "activities": {
    "viewing": [
      {
        "user_id": 3,
        "user_name": "Team Verifikasi",
        "user_role": "IbuB",
        "last_activity_at": "2025-12-17T10:30:00+00:00"
      }
    ],
    "editing": []
  }
}
```

---

### Cek Network Tab

1. Buka **F12 → Network**
2. Filter: `activity` atau `activities`
3. Refresh halaman
4. Harus ada request:
   - `POST /api/documents/1735/activity` → Status: 200
   - `GET /api/documents/1735/activities` → Status: 200

---

## ❌ Masalah Umum

### 1. Activity Panel Tidak Muncul

**Kemungkinan:**
- Hanya 1 user yang melihat dokumen (panel hanya muncul jika ada user lain)
- Migration belum dijalankan
- JavaScript error

**Solusi:**
- Pastikan 2 user berbeda membuka dokumen yang sama
- Jalankan migration
- Cek console untuk error

---

### 2. Real-time Tidak Bekerja

**Kemungkinan:**
- Pusher tidak terhubung
- Channel tidak subscribed

**Solusi:**
- Cek console: `window.Echo.connector.pusher.connection.state`
- Harus: `"connected"`
- Jika `"disconnected"`, cek Pusher credentials di `.env`

---

### 3. API Return 404

**Solusi:**
```bash
php artisan route:clear
php artisan route:list | grep activity
```

Harus muncul 3 route:
- `POST api/documents/{dokumen}/activity`
- `GET api/documents/{dokumen}/activities`
- `POST api/documents/{dokumen}/activity/stop`

---

## ✅ Checklist

- [ ] Migration sudah dijalankan
- [ ] Cache sudah di-clear
- [ ] Route terdaftar (cek dengan `route:list`)
- [ ] 2 user berbeda login
- [ ] 2 user membuka dokumen yang sama
- [ ] Console tidak ada error
- [ ] Network request return 200
- [ ] Activity panel muncul di sidebar

---

## 🎯 Expected Result

**Ketika 2 user membuka dokumen yang sama:**

**Browser 1:**
```
┌─────────────────────────────┐
│ 👥 Aktivitas Dokumen        │
├─────────────────────────────┤
│ 👁️ Sedang melihat:         │
│    • User A (Anda) 🟢       │
│    • User B 🟢              │
└─────────────────────────────┘
```

**Browser 2:**
```
┌─────────────────────────────┐
│ 👥 Aktivitas Dokumen        │
├─────────────────────────────┤
│ 👁️ Sedang melihat:         │
│    • User A 🟢              │
│    • User B (Anda) 🟢       │
└─────────────────────────────┘
```

---

## 📞 Masih Tidak Bekerja?

Lihat file `TROUBLESHOOTING_ACTIVITY_TRACKING.md` untuk debugging lengkap.


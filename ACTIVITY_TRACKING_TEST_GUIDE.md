# 🧪 Activity Tracking - Test Guide

## ⚠️ PENTING: Fitur Hanya Bekerja dengan User Berbeda!

**Activity Tracking dirancang untuk menampilkan user lain yang sedang melihat dokumen yang sama.**

### ❌ TIDAK AKAN BEKERJA:
- Login dengan credential yang sama di 2 browser
- Login dengan user yang sama di 2 tab berbeda
- Login dengan user yang sama di 2 window berbeda

### ✅ AKAN BEKERJA:
- Login dengan **2 user berbeda** dengan **credential berbeda**
- Contoh:
  - Browser 1: `teamverifikasi` / `teamverifikasi825`
  - Browser 2: `teamperpajakan` / `teamperpajakan825` (atau user Team Verifikasi lain)

---

## 🧪 Cara Test yang Benar

### Step 1: Siapkan 2 User Berbeda

**Pastikan ada minimal 2 user Team Verifikasi di database:**

```sql
-- Cek user yang ada
SELECT id, name, username, role FROM users WHERE role = 'IbuB' OR role LIKE '%verifikasi%';
```

**Atau buat user test baru:**
```sql
INSERT INTO users (name, username, password, role, created_at, updated_at)
VALUES 
('Team Verifikasi 1', 'teamverifikasi1', '$2y$10$...', 'IbuB', NOW(), NOW()),
('Team Verifikasi 2', 'teamverifikasi2', '$2y$10$...', 'IbuB', NOW(), NOW());
```

---

### Step 2: Login dengan 2 User Berbeda

**Browser 1:**
- URL: `http://47.236.49.229/login`
- Username: `teamverifikasi` (atau user Team Verifikasi pertama)
- Password: `teamverifikasi825`

**Browser 2 (Incognito/Private atau browser berbeda):**
- URL: `http://47.236.49.229/login`
- Username: `teamverifikasi2` (atau user Team Verifikasi kedua)
- Password: `teamverifikasi825` (atau password user kedua)

---

### Step 3: Buka Dokumen yang Sama

**Browser 1:**
- Buka: `http://47.236.49.229/inbox/1735`

**Browser 2:**
- Buka: `http://47.236.49.229/inbox/1735` (dokumen yang sama!)

---

### Step 4: Cek Activity Panel

**Di kedua browser, harus muncul:**

**Browser 1:**
```
┌─────────────────────────────┐
│ 👥 Aktivitas Dokumen        │
├─────────────────────────────┤
│ 👁️ Sedang melihat:         │
│    • Team Verifikasi (Anda) │
│    • Team Verifikasi 2      │
└─────────────────────────────┘
```

**Browser 2:**
```
┌─────────────────────────────┐
│ 👥 Aktivitas Dokumen        │
├─────────────────────────────┤
│ 👁️ Sedang melihat:         │
│    • Team Verifikasi        │
│    • Team Verifikasi 2 (Anda)│
└─────────────────────────────┘
```

---

## 🔍 Verifikasi di Console

**Buka F12 → Console di kedua browser:**

**Browser 1 harus menampilkan:**
```
📊 Activities loaded: {
  viewing: [
    {user_id: 3, user_name: "Team Verifikasi", ...},
    {user_id: 4, user_name: "Team Verifikasi 2", ...}
  ],
  editing: []
}
📊 Updating activity display: {
  viewers: 2,
  editors: 0,
  viewingMap: [[4, {name: "Team Verifikasi 2", ...}]]
}
✅ Showing activity panel - Found other users
```

**Browser 2 harus menampilkan:**
```
📊 Activities loaded: {
  viewing: [
    {user_id: 3, user_name: "Team Verifikasi", ...},
    {user_id: 4, user_name: "Team Verifikasi 2", ...}
  ],
  editing: []
}
📊 Updating activity display: {
  viewers: 2,
  editors: 0,
  viewingMap: [[3, {name: "Team Verifikasi", ...}]]
}
✅ Showing activity panel - Found other users
```

---

## 🎯 Expected Behavior

### ✅ Jika 2 User Berbeda:
- Activity panel muncul di kedua browser
- Menampilkan nama user lain
- Real-time update (tanpa refresh)
- Warning muncul jika user lain mulai edit

### ❌ Jika User Sama:
- Activity panel tidak muncul
- Console log: `Hiding activity panel - No other users detected`
- Console log: `TIP: Activity tracking requires 2 DIFFERENT users`

---

## 🛠️ Quick Test: Buat User Test

Jika tidak ada user Team Verifikasi lain, buat dengan seeder atau manual:

**Option 1: Via Tinker**
```bash
php artisan tinker
```

```php
$user = \App\Models\User::create([
    'name' => 'Team Verifikasi Test',
    'username' => 'teamverifikasi_test',
    'password' => bcrypt('test123'),
    'role' => 'IbuB'
]);
echo "User created: ID = " . $user->id;
```

**Option 2: Via SQL**
```sql
INSERT INTO users (name, username, password, role, created_at, updated_at)
VALUES (
    'Team Verifikasi Test',
    'teamverifikasi_test',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
    'IbuB',
    NOW(),
    NOW()
);
```

---

## 📝 Summary

**Fitur Activity Tracking:**
- ✅ Menampilkan user lain yang sedang melihat dokumen
- ✅ Real-time updates via Laravel Echo
- ✅ Warning jika ada yang sedang edit
- ❌ **TIDAK** menampilkan user yang sama (karena itu Anda sendiri)

**Untuk test:**
1. Login dengan **2 user berbeda**
2. Buka **dokumen yang sama**
3. Activity panel akan muncul di kedua browser

---

## 💡 Development Mode (Optional)

Jika ingin test dengan user yang sama (untuk development), bisa tambahkan test mode:

```javascript
// Di console browser, force show panel untuk testing
document.getElementById('activity-panel').style.display = 'block';
document.getElementById('viewers-items').innerHTML = `
    <div class="activity-item">
        <div class="activity-item-status"></div>
        <div class="activity-item-name">Test User (Mock)</div>
    </div>
`;
```

Tapi ini hanya untuk testing UI, bukan real activity tracking.


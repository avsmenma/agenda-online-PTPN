# 🔄 PROMPT LANJUTAN: Role Naming Refactor - Agenda Online PTPN

> **GUNAKAN PROMPT INI** jika chat limit tercapai untuk melanjutkan pekerjaan refactoring.

---

## 📋 Konteks Proyek

**Lokasi Proyek:** [c:\Users\ASUS\Downloads\agenda_2026\agenda-online-PTPN](file:///c:/Users/ASUS/Downloads/agenda_2026/agenda-online-PTPN)

**Tujuan:** Refactor penamaan role yang ambigu di seluruh codebase (file, program, database) untuk mempermudah maintenance dan development di masa depan.

---

## 🎯 MAPPING PERUBAHAN NAMA ROLE

| Nama Lama (CARI) | Nama Baru (GANTI) |
|------------------|-------------------|
| `ibua`, `ibuA`, `IbuA`, `ibu a`, `Ibu A`, `ibu tarapul`, `ibutarapul`, `IbuTarapul`, `Ibu Tarapul` | **`operator`** |
| `ibub`, `ibuB`, `IbuB`, `verifikasi`, `Verifikasi`, `teamverifikasi`, `Team Verifikasi` | **`team_verifikasi`** |

> **Catatan:** Role `perpajakan`, `akutansi`, dan `pembayaran` TIDAK PERLU diubah.

---

## 📁 FILE YANG PERLU DIUBAH

### 1. Controllers (Rename + Update)
```
app/Http/Controllers/
├── IbuACsvImportController.php  → OperatorCsvImportController.php
├── DashboardBController.php      → TeamVerifikasiController.php
├── DashboardController.php       → update references
├── DokumenController.php         → update references
├── InboxController.php           → update references
├── OwnerDashboardController.php  → update references
└── [all other controllers]       → update references
```

### 2. Views Directories (Rename)
```
resources/views/
├── IbuA/    → operator/
└── ibuB/    → team_verifikasi/
```

### 3. Routes
```
routes/web.php → update ALL role middleware and route names
```

### 4. Models
```
app/Models/
├── User.php    → update role mappings
└── Dokumen.php → update role references
```

### 5. Database Migrations (Create New)
- Create migration to update `dokumens.created_by`
- Create migration to update `dokumens.current_handler`
- Create migration to update `users.role`
- Create migration to update `roles.code`
- Create migration to update `dokumen_role_data.role_code`

---

## 🔍 PENCARIAN REFERENSI (Grep Commands)

Gunakan perintah ini untuk menemukan semua referensi:

```powershell
# Cari semua referensi ibua/ibuA
grep -rin "ibua\|ibuA\|ibutarapul\|ibu.?tarapul" --include="*.php" --include="*.blade.php"

# Cari semua referensi ibub/ibuB
grep -rin "ibub\|ibuB\|teamverifikasi\|verifikasi" --include="*.php" --include="*.blade.php"
```

---

## 📊 STATUS LENGKAP (Per Fase)

### ✅ Phase 1: Planning & Documentation - SELESAI
- Analisis codebase sudah selesai
- Semua file teridentifikasi
- Prompt continuation sudah dibuat

### ⏳ Phase 2: Database Schema & Migrations - BELUM DIMULAI
**Yang harus dilakukan:**
1. Buat file migration baru: `2026_01_24_xxxxxx_standardize_role_names.php`
2. Update kolom-kolom berikut:
   - `dokumens.created_by`: 'ibua'/'ibuA'/'ibutarapul' → 'operator'
   - `dokumens.current_handler`: sama seperti di atas
   - `dokumen_role_data.role_code`: sama seperti di atas
   - `users.role`: sama seperti di atas
   - `roles.code` dan `roles.name`

### ⏳ Phase 3: Backend Code Refactor - BELUM DIMULAI
**Yang harus dilakukan:**
1. Rename files:
   - [IbuACsvImportController.php](file:///c:/Users/ASUS/Downloads/agenda_2026/agenda-online-PTPN/app/Http/Controllers/IbuACsvImportController.php) → `OperatorCsvImportController.php`
   - [DashboardBController.php](file:///c:/Users/ASUS/Downloads/agenda_2026/agenda-online-PTPN/app/Http/Controllers/DashboardBController.php) → `TeamVerifikasiController.php`
2. Update semua references di semua controller

### ⏳ Phase 4: Routes Refactor - BELUM DIMULAI
**Yang harus dilakukan:**
1. Update [routes/web.php](file:///c:/Users/ASUS/Downloads/agenda_2026/agenda-online-PTPN/routes/web.php)
2. Ganti semua `role:ibua,IbuA,ibutarapul` menjadi `role:operator`
3. Ganti semua `role:ibub,IbuB,verifikasi` menjadi `role:team_verifikasi`

### ⏳ Phase 5: Views/Frontend Refactor - BELUM DIMULAI
**Yang harus dilakukan:**
1. Rename directories:
   - `resources/views/IbuA/` → `resources/views/operator/`
   - `resources/views/ibuB/` → `resources/views/team_verifikasi/`
2. Update semua view references di controllers dan routes

### ⏳ Phase 6: Database Data Migration - BELUM DIMULAI
**Yang harus dilakukan:**
1. Run migration untuk update existing data
2. Verify data integrity

### ⏳ Phase 7: Testing & Verification - BELUM DIMULAI
**Yang harus dilakukan:**
1. Test login semua role
2. Test document workflow
3. Test dashboards

---

## 🚀 QUICK START UNTUK MELANJUTKAN

Jika Anda baru melanjutkan setelah limit chat, gunakan prompt ini:

```
Saya ingin melanjutkan refactoring role naming di proyek Laravel "Agenda Online PTPN".

KONTEKS:
- Proyek ada di: c:\Users\ASUS\Downloads\agenda_2026\agenda-online-PTPN
- Dokumen tracking ada di: C:\Users\ASUS\.gemini\antigravity\brain\2fbca034-6ba8-4637-8f1d-fcfb6ed5c0d0\task.md

TUJUAN:
1. Ganti semua referensi "ibua/ibuA/ibutarapul/Ibu Tarapul" menjadi "operator"
2. Ganti semua referensi "ibub/ibuB/verifikasi/teamverifikasi" menjadi "team_verifikasi"

Tolong baca file task.md untuk melihat progress terakhir, lalu lanjutkan dari fase yang belum selesai.
```

---

## ⚠️ ATURAN PENTING

1. **BACKUP DULU** sebelum menjalankan migration di production
2. **TEST DI LOCAL** terlebih dahulu
3. **Jangan ubah** role `perpajakan`, `akutansi`, `pembayaran`
4. **Pertahankan backward compatibility** jika memungkinkan (redirect old routes)
5. **Update seeder files** juga setelah migration

---

## 📝 CATATAN TAMBAHAN

### Status Values yang Mengandung Role Name (perlu diperbarui)
```php
// Di dokumens.status enum:
'sent_to_ibub' → 'sent_to_team_verifikasi'
'processed_by_ibub' → 'processed_by_team_verifikasi'
'returned_to_ibua' → 'returned_to_operator'
'returned_to_ibub' → 'returned_to_team_verifikasi'
'pending_approval_ibub' → 'pending_approval_team_verifikasi'
```

### File Referensi Utama
- **Routes:** `routes/web.php` - ~1100+ baris, banyak middleware role checks
- **Main Layout:** `resources/views/layouts/app.blade.php` - menu dan navigasi
- **Owner Dashboard:** `app/Http/Controllers/OwnerDashboardController.php` - workflow tracking

---

**Dibuat:** 2026-01-24
**Untuk proyek:** Agenda Online PTPN
**Tujuan:** Standarisasi penamaan role

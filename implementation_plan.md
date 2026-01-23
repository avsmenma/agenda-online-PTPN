# Implementation Plan: Role Naming Standardization

## Goal Description
Refactor all ambiguous role naming in the Agenda Online PTPN Laravel application to use consistent, clear naming conventions.

**Mapping:**
- `ibua/ibuA/IbuA/ibutarapul/Ibu Tarapul` → **`operator`**
- `ibub/ibuB/IbuB/verifikasi/teamverifikasi` → **`team_verifikasi`**

---

## User Review Required

> [!IMPORTANT]
> Project ini merupakan refactoring besar yang akan mempengaruhi banyak file. Pastikan Anda memiliki **backup database production** sebelum menjalankan migration.

> [!WARNING]
> Perubahan ini akan mempengaruhi:
> - Login credentials (username mungkin perlu disesuaikan)
> - Semua URL yang mengandung nama role lama
> - Data existing di database (kolom `created_by`, `current_handler`, `role`, dll)

**Pertanyaan untuk Anda:**
1. Apakah Anda ingin mempertahankan backward compatibility dengan redirect dari URL lama?
2. Username untuk login, apakah perlu diubah juga (misal: dari `ibutarapul` → `operator`)?
3. Apakah ada data production yang perlu di-backup sebelum migration?

---

## Proposed Changes

### Phase 2: Database Migration

#### [NEW] [2026_01_24_000001_standardize_role_names.php](file:///c:/Users/ASUS/Downloads/agenda_2026/agenda-online-PTPN/database/migrations/2026_01_24_000001_standardize_role_names.php)
Migration baru untuk update semua data menggunakan nama role standar:

```php
// Update dokumens table
DB::table('dokumens')
    ->whereIn('created_by', ['ibua', 'ibuA', 'IbuA', 'ibu a', 'Ibu A', 'ibutarapul', 'IbuTarapul'])
    ->update(['created_by' => 'operator']);

DB::table('dokumens')
    ->whereIn('current_handler', ['ibua', 'ibuA', 'IbuA', 'ibutarapul', ...])
    ->update(['current_handler' => 'operator']);

// Similar for ibuB → team_verifikasi
```

---

### Phase 3: Backend Controller Refactor

#### [MODIFY] [DashboardController.php](file:///c:/Users/ASUS/Downloads/agenda_2026/agenda-online-PTPN/app/Http/Controllers/DashboardController.php)
Update semua referensi dari `['ibua', 'ibu a', 'ibutarapul']` → `['operator']`

#### [MODIFY] [DokumenController.php](file:///c:/Users/ASUS/Downloads/agenda_2026/agenda-online-PTPN/app/Http/Controllers/DokumenController.php)
Update semua referensi role lama ke nama baru

#### [NEW] [OperatorCsvImportController.php](file:///c:/Users/ASUS/Downloads/agenda_2026/agenda-online-PTPN/app/Http/Controllers/OperatorCsvImportController.php)
Rename dari [IbuACsvImportController.php](file:///c:/Users/ASUS/Downloads/agenda_2026/agenda-online-PTPN/app/Http/Controllers/IbuACsvImportController.php), update class name dan references

#### [NEW] [TeamVerifikasiController.php](file:///c:/Users/ASUS/Downloads/agenda_2026/agenda-online-PTPN/app/Http/Controllers/TeamVerifikasiController.php)
Rename dari [DashboardBController.php](file:///c:/Users/ASUS/Downloads/agenda_2026/agenda-online-PTPN/app/Http/Controllers/DashboardBController.php), update class name dan references

#### [DELETE] [IbuACsvImportController.php](file:///c:/Users/ASUS/Downloads/agenda_2026/agenda-online-PTPN/app/Http/Controllers/IbuACsvImportController.php)

#### [DELETE] [DashboardBController.php](file:///c:/Users/ASUS/Downloads/agenda_2026/agenda-online-PTPN/app/Http/Controllers/DashboardBController.php)

---

### Phase 4: Routes Refactor

#### [MODIFY] [web.php](file:///c:/Users/ASUS/Downloads/agenda_2026/agenda-online-PTPN/routes/web.php)
- Update middleware dari `'role:admin,ibua,IbuA,ibutarapul'` → `'role:admin,operator'`
- Update middleware dari `'role:admin,ibub,IbuB,verifikasi'` → `'role:admin,team_verifikasi'`
- Update route names dan groups

---

### Phase 5: Views Restructure

#### [NEW] [operator/](file:///c:/Users/ASUS/Downloads/agenda_2026/agenda-online-PTPN/resources/views/operator/)
Rename directory dari `IbuA/` → `operator/`

#### [NEW] [team_verifikasi/](file:///c:/Users/ASUS/Downloads/agenda_2026/agenda-online-PTPN/resources/views/team_verifikasi/)
Rename directory dari `ibuB/` → `team_verifikasi/`

#### [DELETE] [IbuA/](file:///c:/Users/ASUS/Downloads/agenda_2026/agenda-online-PTPN/resources/views/IbuA/)

#### [DELETE] [ibuB/](file:///c:/Users/ASUS/Downloads/agenda_2026/agenda-online-PTPN/resources/views/ibuB/)

---

### Phase 6: Model Updates

#### [MODIFY] [User.php](file:///c:/Users/ASUS/Downloads/agenda_2026/agenda-online-PTPN/app/Models/User.php)
Update role redirect mappings dari `'ibutarapul' => '/dashboard'` → `'operator' => '/dashboard'`

#### [MODIFY] [Dokumen.php](file:///c:/Users/ASUS/Downloads/agenda_2026/agenda-online-PTPN/app/Models/Dokumen.php)
Update method `getIbuTarapulStatusDisplay()` → `getOperatorStatusDisplay()`

---

## Verification Plan

### Manual Testing (PRIMARY)

> [!NOTE]
> Testing ini dilakukan manual karena proyek ini tidak memiliki automated test yang comprehensive.

**Test Case 1: Login untuk setiap role**
1. Buka browser ke `http://localhost:8000/login`
2. Login sebagai Operator (sebelumnya IbuA/Ibu Tarapul)
3. Verify: 
   - Berhasil masuk ke dashboard
   - Menu navigasi tampil dengan benar
   - Dapat mengakses halaman dokumen

**Test Case 2: Document Workflow - Operator**
1. Login sebagai Operator
2. Buat dokumen baru via `/documents/create`
3. Kirim dokumen ke Team Verifikasi
4. Verify: 
   - Status dokumen berubah menjadi "Terkirim ke Team Verifikasi"
   - Dokumen muncul di daftar Team Verifikasi

**Test Case 3: Document Workflow - Team Verifikasi**
1. Login sebagai Team Verifikasi (sebelumnya IbuB)
2. Buka dashboard verifikasi
3. Lihat dokumen yang dikirim dari Test Case 2
4. Verify:
   - Dapat melihat daftar dokumen
   - Dapat memproses dokumen (approve/reject)

**Test Case 4: Owner Dashboard**
1. Login sebagai Admin/Owner
2. Buka `/owner/dokumen`
3. Verify:
   - Workflow timeline menampilkan nama role yang baru
   - Semua statistik dashboard tampil dengan benar

**Test Case 5: Database Data Integrity**
```sql
-- Jalankan query ini setelah migration:
SELECT DISTINCT created_by FROM dokumens;
SELECT DISTINCT current_handler FROM dokumens;
SELECT DISTINCT role_code FROM dokumen_role_data;
SELECT DISTINCT role FROM users;
SELECT * FROM roles;

-- Tidak boleh ada ibua/ibuA/ibub/ibuB di hasil query
```

### Automated Tests
- Jalankan `php artisan test` untuk memastikan tidak ada error pada test suite existing
- Command: `cd c:\Users\ASUS\Downloads\agenda_2026\agenda-online-PTPN && php artisan test`

---

## Execution Order

1. **BACKUP** database terlebih dahulu
2. Jalankan Phase 2 (Database Migration)
3. Jalankan Phase 3 (Controller Refactor)
4. Jalankan Phase 4 (Routes Refactor)
5. Jalankan Phase 5 (Views Restructure)
6. Jalankan Phase 6 (Model Updates)
7. Clear cache: `php artisan cache:clear && php artisan route:clear && php artisan view:clear && php artisan config:clear`
8. Run Manual Tests

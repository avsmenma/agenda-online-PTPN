# Role Naming Refactor - Agenda Online PTPN

## Tujuan Utama
Menstandarisasi penamaan role yang saat ini ambigu di seluruh codebase (file, program, database).

## Mapping Perubahan Nama
| Nama Lama (Ambigu) | Nama Baru (Standar) |
|---|---|
| `ibua`, `ibuA`, `IbuA`, `ibu a`, `Ibu A`, `ibutarapul`, `IbuTarapul`, `Ibu Tarapul` | **`operator`** |
| `ibub`, `ibuB`, `IbuB`, `verifikasi`, `Verifikasi`, `teamverifikasi` | **`team_verifikasi`** |

> [!NOTE]
> Role `perpajakan`, `akutansi`, dan `pembayaran` sudah menggunakan nama yang konsisten.

---

## Task Checklist

### Phase 1: Planning & Documentation
- [x] Analyze codebase for all ambiguous naming patterns
- [x] Document all affected files and locations
- [/] Create comprehensive refactoring prompt for continuation
- [ ] Create implementation plan
- [ ] Get user approval for plan

### Phase 2: Database Schema & Migrations
- [ ] Create migration to update `dokumens` table columns (`created_by`, `current_handler`)
- [ ] Create migration to update `roles` table
- [ ] Create migration to update `dokumen_role_data` table
- [ ] Create migration to update `users` table (`role` column)
- [ ] Update enum/status values containing old role names

### Phase 3: Backend Code Refactor
- [ ] Rename [IbuACsvImportController.php](file:///c:/Users/ASUS/Downloads/agenda_2026/agenda-online-PTPN/app/Http/Controllers/IbuACsvImportController.php) → `OperatorCsvImportController.php`
- [ ] Rename [DashboardBController.php](file:///c:/Users/ASUS/Downloads/agenda_2026/agenda-online-PTPN/app/Http/Controllers/DashboardBController.php) → `TeamVerifikasiController.php`
- [ ] Update [DashboardController.php](file:///c:/Users/ASUS/Downloads/agenda_2026/agenda-online-PTPN/app/Http/Controllers/DashboardController.php) references
- [ ] Update [DokumenController.php](file:///c:/Users/ASUS/Downloads/agenda_2026/agenda-online-PTPN/app/Http/Controllers/DokumenController.php) references
- [ ] Update [InboxController.php](file:///c:/Users/ASUS/Downloads/agenda_2026/agenda-online-PTPN/app/Http/Controllers/InboxController.php) references
- [ ] Update [OwnerDashboardController.php](file:///c:/Users/ASUS/Downloads/agenda_2026/agenda-online-PTPN/app/Http/Controllers/OwnerDashboardController.php) references
- [ ] Update all other controllers
- [ ] Update Models ([User.php](file:///c:/Users/ASUS/Downloads/agenda_2026/agenda-online-PTPN/app/Models/User.php), [Dokumen.php](file:///c:/Users/ASUS/Downloads/agenda_2026/agenda-online-PTPN/app/Models/Dokumen.php))
- [ ] Update Middleware (`RoleMiddleware.php`)

### Phase 4: Routes Refactor
- [ ] Update [routes/web.php](file:///c:/Users/ASUS/Downloads/agenda_2026/agenda-online-PTPN/routes/web.php) - middleware role checks
- [ ] Update route names and groups
- [ ] Maintain backward compatibility with redirects (optional)

### Phase 5: Views/Frontend Refactor
- [ ] Rename `resources/views/IbuA/` → `resources/views/operator/`
- [ ] Rename `resources/views/ibuB/` → `resources/views/team_verifikasi/`
- [ ] Update `resources/views/layouts/app.blade.php`
- [ ] Update all blade templates containing role references
- [ ] Update JavaScript role checks in blade files

### Phase 6: Database Data Migration
- [ ] Create SQL script to update existing data
- [ ] Update `created_by` values
- [ ] Update `current_handler` values
- [ ] Update `role_code` in `dokumen_role_data`
- [ ] Update `roles` table entries

### Phase 7: Testing & Verification
- [ ] Test login for all roles
- [ ] Test document workflow (create → send → verify → etc)
- [ ] Test all dashboards render correctly
- [ ] Test navigation and menu items
- [ ] Verify database data integrity

---

## Affected Files Summary

### Controllers (11+ files)
- `IbuACsvImportController.php` → rename
- `DashboardBController.php` → rename + update
- `DashboardController.php` → update references
- `DokumenController.php` → update references
- `InboxController.php` → update references
- `OwnerDashboardController.php` → update references
- `DashboardAkutansiController.php` → update references
- `DashboardPerpajakanController.php` → update references
- `DashboardPembayaranController.php` → update references
- `BagianDokumenController.php` → update references
- `UniversalApprovalController.php` → update references

### Views Directories (rename required)
- `resources/views/IbuA/` → `resources/views/operator/`
- `resources/views/ibuB/` → `resources/views/team_verifikasi/`

### Routes
- `routes/web.php` - extensive updates required

### Models
- `app/Models/User.php`
- `app/Models/Dokumen.php`

### Migrations (new required)
- Create new migration for data update

### Seeders
- `UpdateUserCredentialsSeeder.php`
- `BagianSeeder.php`

### SQL Files
- `agenda_ptpn_new.sql` - reference only

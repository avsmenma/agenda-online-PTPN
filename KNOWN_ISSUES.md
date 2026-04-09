# Known Issues — Agenda Online PTPN

## 1. Typo "Akutansi" (seharusnya "Akuntansi")

**Severity:** Cosmetic / Technical Debt  
**Status:** Documented, **akan dipertahankan sementara**  
**Ditemukan:** Audit Sistem, April 2026

### Deskripsi

Seluruh sistem menggunakan ejaan **"Akutansi"** (salah) alih-alih **"Akuntansi"** (benar).
Typo ini telah menyebar ke seluruh stack:

| Layer | Contoh |
|-------|--------|
| Database (tabel) | `dokumen_role_data.role_code = 'akutansi'` |
| Database (value) | `dokumens.status = 'sent_to_akutansi'` |
| Controller | `DashboardAkutansiController.php` |
| Route | `/documents/akutansi/*`, `/reports/akutansi/*` |
| View | `akutansi/` directory, semua blade templates |
| Model | `Dokumen::STATUS_SENT_TO_AKUTANSI` (hardcoded strings) |
| URL | `/dashboard/akutansi`, `/api/documents/akutansi/*` |

### Mengapa Tidak Diperbaiki Sekarang

1. **Breaking change yang sangat luas** — Mengubah typo ini membutuhkan migrasi database, update 50+ file, dan perubahan semua URL.
2. **Risiko regresi tinggi** — Tanpa comprehensive test suite, perubahan ini sangat berisiko menyebabkan error di production.
3. **Kompatibilitas** — User mungkin sudah memiliki bookmark ke URL lama.

### Rencana Perbaikan (Masa Depan)

Setelah comprehensive test suite sudah tersedia (Fase 3.6+):

1. Buat migration untuk rename semua database values
2. Update semua file PHP (controller, model, helper, config)
3. Update semua blade templates
4. Buat redirect route dari URL lama → URL baru
5. Update semua JavaScript/AJAX endpoints
6. Test end-to-end sebelum deploy

---

## 2. Migration Files (99 files)

**Severity:** Maintenance Overhead  
**Status:** Documented, recommended to squash for new environments

### Deskripsi

Terdapat **99 file migrasi** di `database/migrations/`. Untuk environment baru, disarankan menjalankan:

```bash
php artisan schema:dump
```

Ini akan mengkonsolidasi semua migrasi menjadi satu file SQL dump, mempercepat `php artisan migrate:fresh` dari ~5 menit menjadi ~5 detik.

> **⚠️ JANGAN** hapus file migrasi lama di production. Gunakan `schema:dump` hanya untuk setup environment baru.

# URL Migration Guide - Professional URLs

Dokumen ini menjelaskan perubahan URL dari format lama ke format baru yang lebih profesional untuk production.

## Ringkasan Perubahan

### Dashboard Routes

| URL Lama | URL Baru | Status |
|----------|----------|--------|
| `/dashboardB` | `/dashboard/verifikasi` | ✅ Redirect 301 |
| `/dashboardPembayaran` | `/dashboard/pembayaran` | ✅ Redirect 301 |
| `/dashboardAkutansi` | `/dashboard/akutansi` | ✅ Redirect 301 |
| `/dashboardPerpajakan` | `/dashboard/perpajakan` | ✅ Redirect 301 |
| `/dashboardVerifikasi` | `/dashboard/verifikasi-role` | ✅ Redirect 301 |

### Document Routes

| URL Lama | URL Baru | Status |
|----------|----------|--------|
| `/dokumens` | `/documents` | ✅ Redirect 301 |
| `/dokumensB` | `/documents/verifikasi` | ✅ Redirect 301 |
| `/dokumensPembayaran` | `/documents/pembayaran` | ✅ Redirect 301 |
| `/dokumensAkutansi` | `/documents/akutansi` | ✅ Redirect 301 |
| `/dokumensPerpajakan` | `/documents/perpajakan` | ✅ Redirect 301 |

### API Routes

| URL Lama | URL Baru | Status |
|----------|----------|--------|
| `/dokumensB/check-updates` | `/api/documents/verifikasi/check-updates` | ✅ Redirect 301 |
| `/perpajakan/check-updates` | `/api/documents/perpajakan/check-updates` | ✅ Redirect 301 |
| `/akutansi/check-updates` | `/api/documents/akutansi/check-updates` | ✅ Redirect 301 |
| `/pembayaran/check-updates` | `/api/documents/pembayaran/check-updates` | ✅ Redirect 301 |
| `/ibua/check-rejected` | `/api/documents/rejected/check` | ✅ Redirect 301 |
| `/ibub/check-rejected` | `/api/documents/verifikasi/rejected/check` | ✅ Redirect 301 |

### Reports Routes

| URL Lama | URL Baru | Status |
|----------|----------|--------|
| `/rekapan` | `/reports` | ✅ Redirect 301 |
| `/rekapan-ibuB` | `/reports/verifikasi` | ✅ Redirect 301 |
| `/rekapan-pembayaran` | `/reports/pembayaran` | ✅ Redirect 301 |
| `/rekapan-akutansi` | `/reports/akutansi` | ✅ Redirect 301 |
| `/rekapan-perpajakan` | `/reports/perpajakan` | ✅ Redirect 301 |
| `/rekapan-keterlambatan` | `/reports/pembayaran/delays` | ✅ Redirect 301 |

### Diagram Routes

| URL Lama | URL Baru | Status |
|----------|----------|--------|
| `/diagramB` | `/reports/verifikasi/diagram` | ✅ Redirect 301 |
| `/diagramPembayaran` | `/reports/pembayaran/diagram` | ✅ Redirect 301 |
| `/diagramAkutansi` | `/reports/akutansi/diagram` | ✅ Redirect 301 |
| `/diagramPerpajakan` | `/reports/perpajakan/diagram` | ✅ Redirect 301 |

### Returns Routes

| URL Lama | URL Baru | Status |
|----------|----------|--------|
| `/pengembalian-dokumensB` | `/returns/verifikasi` | ✅ Redirect 301 |
| `/pengembalian-dokumensPembayaran` | `/returns/pembayaran` | ✅ Redirect 301 |
| `/pengembalian-dokumensAkutansi` | `/returns/akutansi` | ✅ Redirect 301 |
| `/pengembalian-dokumensPerpajakan` | `/returns/perpajakan` | ✅ Redirect 301 |

## Route Names

Semua route name juga telah diperbarui dengan format yang lebih profesional:

### Document Routes
- `documents.index` (IbuA)
- `documents.verifikasi.index` (IbuB)
- `documents.pembayaran.index`
- `documents.akutansi.index`
- `documents.perpajakan.index`

### Dashboard Routes
- `dashboard.main` (IbuA)
- `dashboard.verifikasi` (IbuB)
- `dashboard.pembayaran`
- `dashboard.akutansi`
- `dashboard.perpajakan`

### API Routes
- `api.documents.verifikasi.check-updates`
- `api.documents.perpajakan.check-updates`
- `api.documents.akutansi.check-updates`
- `api.documents.pembayaran.check-updates`
- `api.documents.rejected.check`
- `api.documents.verifikasi.rejected.check`

## Backward Compatibility

Semua URL lama masih berfungsi dengan redirect 301 (Permanent Redirect) ke URL baru. Ini memastikan:
- Bookmark lama masih berfungsi
- Link eksternal tidak rusak
- SEO tidak terpengaruh negatif

## Update Required

### Views
Perlu update referensi URL di:
- `resources/views/layouts/app.blade.php`
- `resources/views/ibuB/dokumens/daftarDokumenB.blade.php`
- `resources/views/perpajakan/dokumens/daftarPerpajakan.blade.php`
- `resources/views/akutansi/dokumens/daftarAkutansi.blade.php`
- `resources/views/pembayaran/dokumens/daftarPembayaran.blade.php`
- Dan view lainnya yang menggunakan URL langsung

### JavaScript
Perlu update AJAX calls dan fetch URLs di:
- Semua file JavaScript yang menggunakan URL langsung
- File yang menggunakan `check-updates` endpoints
- File yang menggunakan route names

### Controllers
Perlu update redirect dan URL generation di:
- Semua controller yang melakukan redirect
- Controller yang generate URL untuk response

## Cara Update

### Menggunakan Route Names (Recommended)

Ganti URL langsung dengan route names:

```php
// ❌ Lama
return redirect('/dokumensB');

// ✅ Baru
return redirect()->route('documents.verifikasi.index');
```

```blade
{{-- ❌ Lama --}}
<a href="/dokumensB">Dokumen</a>

{{-- ✅ Baru --}}
<a href="{{ route('documents.verifikasi.index') }}">Dokumen</a>
```

```javascript
// ❌ Lama
fetch('/dokumensB/check-updates')

// ✅ Baru
fetch('/api/documents/verifikasi/check-updates')
// atau gunakan route helper jika tersedia
```

## Testing

Setelah update, pastikan untuk test:
1. ✅ Semua dashboard dapat diakses
2. ✅ Semua document list dapat diakses
3. ✅ Semua API endpoints berfungsi
4. ✅ Redirect dari URL lama ke URL baru berfungsi
5. ✅ Form submission dan AJAX calls berfungsi
6. ✅ Navigation menu berfungsi

## Notes

- Semua redirect menggunakan HTTP 301 (Permanent Redirect) untuk SEO
- Route names tetap backward compatible dengan menambahkan `.old` suffix
- User model `DASHBOARD_ROUTES` constant telah diperbarui


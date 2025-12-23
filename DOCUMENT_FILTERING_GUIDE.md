# Panduan Filter Dokumen CSV Import - DRY Principle

## Ringkasan Masalah
Dokumen yang di-import via CSV di modul Pembayaran seharusnya **eksklusif** untuk modul Pembayaran saja dan tidak boleh muncul di modul lain (IbuA, IbuB, Perpajakan, Akutansi).

## Solusi: Scope Methods di Model Dokumen

Untuk memastikan konsistensi dan mengikuti prinsip DRY (Don't Repeat Yourself), telah dibuat **scope methods** di Model `Dokumen` yang dapat digunakan di seluruh aplikasi.

### Scope Methods yang Tersedia

#### 1. `scopeExcludeCsvImports()`
**Tujuan**: Mengecualikan dokumen CSV import dari query (untuk semua modul kecuali Pembayaran)

**Penggunaan**:
```php
// Contoh: Query untuk Perpajakan
$perpajakanDocs = Dokumen::query()
    ->where(function ($query) {
        $query->where('current_handler', 'perpajakan')
            ->orWhere('status', 'sent_to_akutansi');
    })
    ->excludeCsvImports()  // ← Gunakan scope method ini
    ->get();
```

#### 2. `scopeOnlyCsvImports()`
**Tujuan**: Hanya mengambil dokumen CSV import (untuk modul Pembayaran)

**Penggunaan**:
```php
// Contoh: Query untuk Pembayaran yang ingin melihat semua dokumen termasuk CSV import
$pembayaranDocs = Dokumen::query()
    ->where(function ($query) {
        $query->where('current_handler', 'pembayaran')
            ->orWhere('status', 'sent_to_pembayaran');
    })
    ->orWhere(function ($query) {
        $query->onlyCsvImports();  // ← Atau gunakan ini untuk hanya CSV imports
    })
    ->get();
```

## Aturan Penting

### ❌ JANGAN Menggunakan Status `sent_to_pembayaran` di Modul Lain

**Masalah**: Dokumen CSV import memiliki `status = 'sent_to_pembayaran'`. Jika query modul lain menggunakan kondisi `orWhere('status', 'sent_to_pembayaran')`, dokumen CSV import akan ikut terambil meskipun sudah ada filter `excludeCsvImports()`.

**Solusi**: **HAPUS** kondisi `orWhere('status', 'sent_to_pembayaran')` dari query modul selain Pembayaran.

**Contoh Salah**:
```php
// ❌ SALAH - Jangan lakukan ini di Perpajakan/Akutansi
$query = Dokumen::where(function ($q) {
    $q->where('current_handler', 'perpajakan')
      ->orWhere('status', 'sent_to_akutansi')
      ->orWhere('status', 'sent_to_pembayaran');  // ← HAPUS INI!
});
```

**Contoh Benar**:
```php
// ✅ BENAR - Hapus sent_to_pembayaran
$query = Dokumen::where(function ($q) {
    $q->where('current_handler', 'perpajakan')
      ->orWhere('status', 'sent_to_akutansi');
      // Tidak ada sent_to_pembayaran
})
->excludeCsvImports();  // ← Gunakan scope method
```

## Implementasi di Setiap Modul

### 1. Modul Pembayaran (`DashboardPembayaranController`)
**Query harus mengizinkan dokumen CSV import**:
```php
$query = Dokumen::whereNotNull('nomor_agenda')
    ->where(function ($q) {
        $q->where('current_handler', 'pembayaran')
          ->orWhere('status', 'sent_to_pembayaran')
          ->orWhere(function ($csvQ) {
              $csvQ->onlyCsvImports();  // Atau gunakan imported_from_csv = true
          });
    });
```

### 2. Modul Perpajakan (`DashboardPerpajakanController`)
**Query harus mengecualikan dokumen CSV import**:
```php
$query = Dokumen::query()
    ->where(function ($q) {
        $q->where('current_handler', 'perpajakan')
          ->orWhere('status', 'sent_to_akutansi');
          // TIDAK ADA sent_to_pembayaran
    })
    ->excludeCsvImports();
```

### 3. Modul Akutansi (`DashboardAkutansiController`)
**Query harus mengecualikan dokumen CSV import**:
```php
$query = Dokumen::where(function ($q) {
    $q->where('current_handler', 'akutansi')
      ->orWhere('status', 'sent_to_akutansi');
      // TIDAK ADA sent_to_pembayaran atau status terkait pembayaran
})
->excludeCsvImports();
```

### 4. Modul IbuB (`DashboardBController`)
**Query harus mengecualikan dokumen CSV import**:
```php
$query = Dokumen::where(function ($q) {
    $q->where('current_handler', 'ibuB')
      ->orWhereIn('status', ['sent_to_perpajakan', 'sent_to_akutansi']);
      // TIDAK ADA sent_to_pembayaran
})
->excludeCsvImports();
```

### 5. Modul IbuA (`DokumenController`)
**Query sudah menggunakan filter `created_by` dan harus mengecualikan CSV import**:
```php
$query = Dokumen::where(function ($q) {
    $q->whereRaw('LOWER(created_by) IN (?, ?)', ['ibua', 'ibu a'])
      ->orWhere('created_by', 'ibuA');
})
->excludeCsvImports();
```

## Lokasi File yang Perlu Diperbaiki

Jika Anda menemukan query dokumen di file berikut, pastikan menggunakan `excludeCsvImports()`:

1. ✅ `app/Http/Controllers/DashboardPembayaranController.php` - Sudah diperbaiki
2. ✅ `app/Http/Controllers/DashboardPerpajakanController.php` - Sudah diperbaiki
3. ✅ `app/Http/Controllers/DashboardAkutansiController.php` - Sudah diperbaiki
4. ✅ `app/Http/Controllers/DashboardBController.php` - Sudah diperbaiki
5. ✅ `app/Http/Controllers/DokumenController.php` - Sudah diperbaiki
6. ✅ `app/Http/Controllers/OwnerDashboardController.php` - Sudah diperbaiki
7. ✅ `app/Http/Controllers/InboxController.php` - Sudah diperbaiki
8. ✅ `routes/web.php` (endpoint check-updates) - Sudah diperbaiki

## Checklist untuk Modul Baru

Saat membuat modul baru yang perlu menampilkan dokumen:

- [ ] Gunakan `->excludeCsvImports()` di semua query dokumen
- [ ] Jangan gunakan kondisi `orWhere('status', 'sent_to_pembayaran')` kecuali untuk modul Pembayaran
- [ ] Test dengan meng-import dokumen CSV dan pastikan tidak muncul di modul baru
- [ ] Dokumentasikan di file ini jika ada modul baru

## Testing

Setelah perubahan, lakukan testing:

1. Import dokumen CSV di modul Pembayaran
2. Cek bahwa dokumen muncul di modul Pembayaran
3. Cek bahwa dokumen **TIDAK** muncul di:
   - Modul IbuA
   - Modul IbuB
   - Modul Perpajakan
   - Modul Akutansi
4. Pastikan statistik di dashboard setiap modul tidak termasuk dokumen CSV import

## Troubleshooting

### Masalah: Dokumen CSV import masih muncul di modul lain

**Kemungkinan penyebab**:
1. Query masih menggunakan `orWhere('status', 'sent_to_pembayaran')`
2. Scope method `excludeCsvImports()` tidak dipanggil
3. Filter diterapkan di tempat yang salah (di dalam `where` closure yang kompleks)

**Solusi**:
1. Hapus kondisi `sent_to_pembayaran` dari query
2. Pastikan `->excludeCsvImports()` dipanggil di level query builder yang tepat
3. Periksa urutan filter - `excludeCsvImports()` harus diterapkan setelah kondisi utama

### Masalah: Dokumen normal tidak muncul setelah menggunakan `excludeCsvImports()`

**Kemungkinan penyebab**:
- Dokumen normal secara tidak sengaja memiliki `imported_from_csv = true`

**Solusi**:
- Periksa data di database: `SELECT * FROM dokumens WHERE imported_from_csv = 1`
- Pastikan hanya dokumen CSV import yang memiliki flag ini

## Catatan Teknis

- Scope method menggunakan `Schema::hasColumn()` untuk kompatibilitas jika migration belum dijalankan
- Filter bekerja dengan mengecek `imported_from_csv = false OR imported_from_csv IS NULL`
- Dokumen CSV import memiliki:
  - `imported_from_csv = true`
  - `status = 'sent_to_pembayaran'`
  - `current_handler = 'pembayaran'`
  - `created_by = 'csv_import'`

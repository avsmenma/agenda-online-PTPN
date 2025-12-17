# Fix Error 500 pada Halaman Edit Verifikasi

## Masalah
Error 500 terjadi ketika mengakses `/documents/verifikasi/1730/edit` setelah pembaruan dropdown.

## Kemungkinan Penyebab

1. **Koneksi Database `cash_bank` tidak berfungsi**
   - Cek apakah connection `cash_bank` sudah dikonfigurasi dengan benar di `.env`
   - Pastikan database `cash_bank_new` ada dan bisa diakses

2. **Model tidak ditemukan atau error query**
   - Model `KategoriKriteria`, `SubKriteria`, atau `ItemSubKriteria` mungkin error
   - Query ke database `cash_bank` mungkin gagal

3. **Variabel tidak terdefinisi di view**
   - Variabel `$kategoriKriteria`, `$subKriteria`, atau `$itemSubKriteria` mungkin null

## Perbaikan yang Sudah Dilakukan

1. ✅ Menambahkan try-catch di controller untuk menangani error koneksi database
2. ✅ Menambahkan pengecekan `isset()` di view untuk variabel
3. ✅ Menambahkan fallback untuk `@json()` dengan array kosong

## Langkah Troubleshooting

### 1. Cek Log Error Laravel

```bash
tail -n 100 storage/logs/laravel.log
```

Atau di server production:
```bash
cd /var/www/agenda_online_ptpn
tail -n 100 storage/logs/laravel.log
```

### 2. Test Koneksi Database Cash Bank

Jalankan di tinker:
```bash
php artisan tinker
```

Kemudian:
```php
// Test koneksi
DB::connection('cash_bank')->getPdo();

// Test query
\App\Models\KategoriKriteria::count();
\App\Models\SubKriteria::count();
\App\Models\ItemSubKriteria::count();
```

### 3. Cek Konfigurasi Database

Pastikan di `.env` ada:
```env
CASH_BANK_DB_DATABASE=cash_bank_new
```

Atau jika tidak ada, akan menggunakan nilai dari `DB_*`.

### 4. Clear Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

## Jika Masih Error

Jika setelah perbaikan masih error, kemungkinan:
1. Database `cash_bank_new` tidak ada atau tidak bisa diakses
2. User MySQL tidak memiliki akses ke database `cash_bank_new`
3. Tabel `kategori_kriteria`, `sub_kriteria`, atau `item_sub_kriteria` tidak ada

**Solusi Sementara:**
Jika database `cash_bank_new` tidak bisa diakses, sistem akan menggunakan collection kosong dan dropdown akan kosong. User masih bisa mengedit dokumen, tapi tidak bisa memilih dropdown baru.


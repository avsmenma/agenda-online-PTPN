# Instruksi Menghapus dan Membuat Dokumen Dummy

## 1. Menghapus Semua Dokumen

### Opsi A: Menggunakan SQL File (Recommended)
```bash
# Masuk ke MySQL
mysql -u your_username -p your_database_name < clear_dokumen.sql
```

### Opsi B: Menggunakan Laravel Tinker
```bash
php artisan tinker
```
Kemudian jalankan:
```php
DB::statement('SET FOREIGN_KEY_CHECKS=0;');
\App\Models\DibayarKepada::truncate();
\App\Models\DokumenPo::truncate();
\App\Models\DokumenPr::truncate();
\App\Models\DokumenRoleData::truncate();
\App\Models\DokumenStatus::truncate();
\App\Models\Dokumen::truncate();
DB::statement('SET FOREIGN_KEY_CHECKS=1;');
```

### Opsi C: Menggunakan Artisan Command (Jika dibuat)
```bash
php artisan dokumen:clear
```

## 2. Membuat Dokumen Dummy

### Menggunakan Seeder
```bash
php artisan db:seed --class=DokumenDummySeeder
```

### Atau jika ingin menjalankan semua seeder
```bash
php artisan migrate:fresh --seed
# Pastikan DokumenDummySeeder sudah ditambahkan ke DatabaseSeeder.php
```

## 3. Verifikasi Data

Setelah membuat dummy dokumen, verifikasi dengan:

```bash
php artisan tinker
```

Kemudian jalankan:
```php
// Cek total dokumen
echo "Total Dokumen: " . \App\Models\Dokumen::count() . "\n";

// Cek dokumen per role
echo "Dokumen IbuA: " . \App\Models\DokumenRoleData::where('role_code', 'ibuA')->whereNull('processed_at')->count() . "\n";
echo "Dokumen IbuB: " . \App\Models\DokumenRoleData::where('role_code', 'ibuB')->whereNull('processed_at')->count() . "\n";
echo "Dokumen Perpajakan: " . \App\Models\DokumenRoleData::where('role_code', 'perpajakan')->whereNull('processed_at')->count() . "\n";
echo "Dokumen Akutansi: " . \App\Models\DokumenRoleData::where('role_code', 'akutansi')->whereNull('processed_at')->count() . "\n";
echo "Dokumen Pembayaran: " . \App\Models\DokumenRoleData::where('role_code', 'pembayaran')->whereNull('processed_at')->count() . "\n";
```

## 4. Struktur Dokumen Dummy yang Dibuat

### IbuA (Ibu Tara)
- 3 dokumen dengan berbagai umur

### IbuB (Team Verifikasi)
- 1 dokumen umur 1 hari (hijau)
- 2 dokumen umur 2 hari (kuning)
- 2 dokumen umur 3+ hari (merah)
- **Total: 5 dokumen**

### Perpajakan
- 1 dokumen umur 1 hari
- 1 dokumen umur 2 hari
- 2 dokumen umur 3+ hari
- **Total: 4 dokumen**

### Akutansi
- 1 dokumen umur 1 hari
- 1 dokumen umur 2 hari
- 1 dokumen umur 3+ hari
- **Total: 3 dokumen**

### Pembayaran
- 2 dokumen siap dibayar
- **Total: 2 dokumen**

## 5. Catatan Penting

⚠️ **PERINGATAN:**
- Perintah menghapus dokumen akan menghapus **SEMUA** data dokumen dan data terkait
- Pastikan sudah backup database sebelum menjalankan perintah penghapusan
- Dokumen dummy dibuat dengan tanggal relatif terhadap waktu sekarang
- Umur dokumen dihitung dari `received_at` di `dokumen_role_data`

## 6. Troubleshooting

Jika terjadi error saat menjalankan seeder:
1. Pastikan semua migration sudah dijalankan: `php artisan migrate`
2. Pastikan model `Dokumen` dan `DokumenRoleData` sudah ada
3. Cek foreign key constraints di database
4. Pastikan kolom yang diperlukan sudah ada di tabel `dokumens`


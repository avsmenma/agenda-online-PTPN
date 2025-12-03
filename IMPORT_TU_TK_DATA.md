# Panduan Import Data TU/TK

Tabel `tu_tk_2023` sudah berhasil dibuat melalui migration. Sekarang Anda perlu mengimport data dari file SQL yang sudah ada.

## ⚠️ PENTING: Error "Table already exists"

Jika Anda mendapat error `#1050 - Table 'tu_tk_2023' already exists`, itu karena tabel sudah dibuat oleh migration. Anda perlu menggunakan salah satu metode di bawah yang **melewati CREATE TABLE statement**.

## Cara Import Data:

### ✅ Opsi 1: Menggunakan Laravel Command (PALING MUDAH - RECOMMENDED)

Command ini secara otomatis akan melewati CREATE TABLE dan hanya mengimport data INSERT:

```bash
php artisan tu-tk:import tu_tk_2023.sql
```

Atau jika file SQL ada di lokasi lain:
```bash
php artisan tu-tk:import path/to/tu_tk_2023.sql
```

**Keuntungan:**
- ✅ Otomatis skip CREATE TABLE statements
- ✅ Hanya mengimport INSERT statements
- ✅ Menampilkan progress bar
- ✅ Menampilkan statistik import
- ✅ Handle errors dengan baik

**Contoh output:**
```
📂 Membaca file: tu_tk_2023.sql
📊 Menemukan 150 statements dalam file
✅ Menemukan 148 INSERT statements
📈 Data existing dalam tabel: 0 records
🚀 Mulai mengimport data...
[Progress bar...]
✅ Import selesai!
```

### Opsi 2: Edit File SQL Manual

1. Buka file `tu_tk_2023.sql`
2. Hapus bagian CREATE TABLE (baris 30-117)
3. Simpan file
4. Import via phpMyAdmin atau MySQL command line

### Opsi 3: Import via phpMyAdmin (dengan Skip CREATE TABLE)

1. Buka phpMyAdmin
2. Pilih database `agenda_online`
3. Klik tab "Import"
4. Pilih file `tu_tk_2023.sql`
5. **PENTING:** Centang opsi "Allow interruption of import in case script detects it is taking too long"
6. Sebelum klik "Go", edit query SQL di textarea dan hapus bagian CREATE TABLE
7. Klik "Go"

## Catatan:
- File SQL `tu_tk_2023.sql` sudah ada di root project
- Setelah import selesai, halaman `/rekapan-tu-tk` akan menampilkan data
- Pastikan file SQL berada di lokasi yang bisa diakses

## Verifikasi:
Setelah import, cek apakah data sudah masuk:
```bash
php artisan tinker
```
Lalu:
```php
\App\Models\TuTk::count();
```

Jika mengembalikan angka > 0, berarti data sudah berhasil diimport.


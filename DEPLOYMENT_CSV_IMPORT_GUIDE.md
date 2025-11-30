# 🚀 Panduan Import CSV Data Dokumen ke Ubuntu Server

## 📋 **Overview**
Berikut adalah langkah-langkah untuk menambahkan data dari file `DATA 12.csv` ke database Laravel di Ubuntu server Anda.

---

## 🔧 **1. Setup Database Migration**

### Jalankan Migration di Ubuntu
```bash
cd /path/to/your/laravel/project
php artisan migrate
```

---

## 🗑️ **1.5. Hapus Data Lama (Opsional tapi Disarankan)**

### ⚠️ **PENTING: Backup Database Terlebih Dahulu!**

Sebelum import data baru, disarankan untuk menghapus data lama agar tidak terjadi duplikasi atau konflik.

### Opsi 1: Hapus Semua Dokumen (Full Reset)
```bash
php artisan dokumen:clean-before-import --type=all --force
```

### Opsi 2: Hapus Hanya Dokumen dari CSV Import Sebelumnya
```bash
php artisan dokumen:clean-before-import --type=csv_import --force
```

### Opsi 3: Hapus Hanya Dokumen Pembayaran
```bash
php artisan dokumen:clean-before-import --type=pembayaran --force
```

### Opsi 4: Interaktif (Dengan Konfirmasi)
```bash
# Tanpa --force, akan muncul konfirmasi
php artisan dokumen:clean-before-import --type=all
```

### Penjelasan Opsi:
- **`--type=all`**: Hapus SEMUA dokumen di database
- **`--type=csv_import`**: Hapus hanya dokumen yang dibuat oleh CSV import sebelumnya (`created_by = 'csv_import'`)
- **`--type=pembayaran`**: Hapus hanya dokumen yang terkait dengan pembayaran
- **`--force`**: Skip konfirmasi (untuk automation/script)

### Catatan:
- Data terkait (dokumen_pos, dokumen_prs, activity_logs) akan otomatis terhapus karena cascade delete
- Pastikan sudah backup database sebelum menjalankan command ini

---

## 📁 **2. Copy File CSV**

### Upload CSV ke Server
```bash
# Upload file DATA 12.csv ke folder public/
scp DATA\ 12.csv user@your-server:/var/www/html/agenda_online_ptpn/public/
```

### Pastikan File Tersedia
```bash
ls -la public/DATA\ 12.csv
```

---

## 🛠️ **3. Import Data via Artisan Command**

### Cara 1: Direct Command Line
```bash
php artisan import:csv --path=public/DATA\ 12.csv
```

### Cara 2: Via Web Interface
1. Akses halaman Dashboard Pembayaran
2. Klik tombol "Import CSV"
3. Pilih file CSV yang telah diupload
4. Klik "Import CSV"

---

## 📄 **4. File yang Dibuat**

### 1. Migration File
- Lokasi: `database/migrations/2025_11_30_120000_add_csv_import_fields_to_dokumens_table.php`

### 2. Artisan Commands
- **ImportCsvData**: Lokasi: `app/Console/Commands/ImportCsvData.php`
  - Fungsi: Import data CSV ke database dengan validasi
- **CleanDokumenBeforeImport**: Lokasi: `app/Console/Commands/CleanDokumenBeforeImport.php`
  - Fungsi: Hapus data dokumen sebelum import CSV (opsi: all, csv_import, pembayaran)

### 3. Controller Methods
- `showImportForm()` - Menampilkan form import
- `importCsv()` - Memproses upload file CSV
- `downloadCsvTemplate()` - Download template CSV

### 4. View Files
- Import Form: `resources/views/pembayaranNEW/importCsv.blade.php`
- Updated Dashboard: `resources/views/pembayaranNEW/dashboardPembayaran.blade.php`

### 5. Routes
- `GET /dashboard-pembayaran/import` - Form import
- `POST /dashboard-pembayaran/import-csv` - Proses import
- `GET /dashboard-pembayaran/download-csv-template` - Download template

---

## 🎯 **5. Fitur Import CSV**

### ✅ **Yang Bisa Dilakukan:**
1. **Upload File CSV** (Max 10MB)
2. **Validasi Otomatis**:
   - Format CSV (semicolon delimiter)
   - Type file (.csv/.txt)
   - Size file
   - Required columns (no. SPP, Nama Kebun, dll)

3. **Import Data**:
   - Generate nomor agenda otomatis
   - Parse tanggal dengan multiple format
   - Handle currency (Rp, desimal)
   - Skip baris kosong dan header
   - Update data duplikat

4. **Progress Indicator**:
   - Real-time progress saat import
   - Loading spinner
   - Notifikasi error/sukses

5. **Error Handling**:
   - Validation errors
   - Database errors
   - File format errors
   - Rollback on failure

---

## 📊 **6. Mapping Data CSV**

### Field Mapping:
```
CSV Column            → Database Field
=====================================
no_spp              → nomor_spp
nama_kebuns         → nama_kebuns
dibayar_kepada        → dibayar_kepada
tanggal_spp          → tanggal_spp
no_spk              → no_spk
tgl_spk              → tanggal_spk
tgl_brkhir_spk        → tanggal_berakhir_spk
no_ba                → no_berita_acara & no_ba (keduanya)
tgl_ba                → tanggal_berita_acara
tgl_faktur            → tanggal_faktur
uraian_spp           → uraian_spp
nilai_rupiah         → nilai_rupiah
tgl_masuk             → tanggal_masuk
status pembayaran     → status_pembayaran (spasi diubah ke underscore)
DIBAYAR              → DIBAYAR
BELUM DIBAYAR        → BELUM_DIBAYAR (spasi diubah ke underscore)
kategori              → kategori (kolom 16 dari CSV)
jenis dokumen         → jenis_dokumen (spasi diubah ke underscore)
KATEGORI             → (dilewati - MySQL menganggap sama dengan kategori)
NO PO                 → NO_PO (spasi diubah ke underscore)
NO MIRO/SES          → NO_MIRO_SES (slash diubah ke underscore)

**Catatan Penting**: 
- Kolom `kategori` (kolom 16) dan `KATEGORI` (kolom 18) dianggap sama oleh MySQL (case-insensitive)
- Hanya kolom `kategori` yang diisi dari CSV untuk menghindari error duplikasi
```

### Penanganan Khusus:
- **Currency Fields**: Otomatis convert dari format Indonesian (xxx.xxx.xxx dengan titik sebagai thousand separator)
- **Date Fields**: Support multiple format:
  - `d-M-y` (20-Jan-23)
  - `d-M-Y` (20-Jan-2023)
  - `d/m/Y` (20/01/2023)
  - `Y-m-d` (2023-01-20)
  - `d/m/y` (20/01/23)
  - `d M Y` (3 Aug 2023) - dengan support bulan Indonesia (Jan, Feb, Mar, Apr, Mei, Jun, Jul, Agu, Sep, Okt, Nov, Des)
- **Duplicate Check**: Update existing data berdasarkan `nomor_spp`, tidak insert duplikat
- **Empty Rows**: Skip baris yang tidak memiliki data penting (nomor_spp atau nilai_rupiah kosong)
- **Header Skip**: Skip 3 baris pertama (baris 1-2 separator, baris 3 header)

---

## 🔍 **7. Validasi Data**

### Validasi di Controller:
```php
// Required columns
$rules = [
    'csv_file' => 'required|file|mimes:csv,txt|max:10240', // Max 10MB
];

// Validasi di Artisan Command
if (empty($data['nomor_spp']) || empty($data['nilai_rupiah'])) {
    // Skip invalid rows
    continue;
}

if (Dokumen::where('nomor_spp', $data['nomor_spp'])->exists()) {
    // Update existing record
    $existing->update($data);
} else {
    // Create new record
    Dokumen::create($data);
}
```

---

## 🚨 **8. Troubleshooting**

### Common Issues & Solutions:

#### 1. Error "Column 'kategori' specified twice"
- **Problem**: MySQL menganggap `kategori` dan `KATEGORI` sebagai kolom yang sama
- **Solution**: Sudah diperbaiki di command - hanya menggunakan `kategori` saja
- **Note**: Jika perlu menggunakan `KATEGORI` sebagai kolom terpisah, pastikan konfigurasi MySQL case-sensitive untuk nama kolom

#### 2. File Tidak Bisa Diupload
- **Problem**: File terlalu besar (>10MB)
- **Solution**: Compress CSV atau bagi menjadi beberapa file

#### 3. Format Tanggal Error
- **Problem**: Format tanggal tidak dikenali
- **Solution**: Command sudah support multiple format:
  - `d-M-y` (20-Jan-23)
  - `d/m/Y` (20/01/2023)
  - `d M Y` (3 Aug 2023 atau 3 Agu 2023)
  - Format lainnya seperti di dokumentasi
- **Note**: Bulan Indonesia (Agu, Okt, Des) otomatis dikonversi ke format standar

#### 3. Memory Limit Error
- **Problem**: Data terlalu besar untuk memory limit
- **Solution**:
  ```bash
  php -d memory_limit=512M artisan import:csv --path=public/DATA\ 12.csv
  ```

#### 4. Database Connection Error
- **Problem**: Koneksi database timeout
- **Solution**: Tambah `set_time_limit(300)` di command

#### 5. Character Encoding Issue
- **Problem**: Special characters tidak terbaca
- **Solution**: Pastikan CSV dalam format UTF-8

---

## 📝 **9. Log & Monitoring**

### Lokasi Log:
```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Log khusus import
grep "CSV Import" storage/logs/laravel.log
```

### Monitoring Progress:
```bash
# Real-time log
tail -f storage/logs/laravel.log | grep "CSV Import"
```

---

## 🔄 **10. Update Process untuk Produksi**

### Langkah Lengkap untuk Produksi:

#### 1. Backup Database:
```bash
# Buat backup database terlebih dahulu
mysqldump -u username -p database_name > backup_$(date +%Y%m%d_%H%M%S).sql
# atau
php artisan backup:database  # jika ada command backup
```

#### 2. Hapus Data Lama (Opsional):
```bash
# Hapus data CSV import sebelumnya (disarankan)
php artisan dokumen:clean-before-import --type=csv_import --force

# ATAU hapus semua dokumen jika ingin fresh start
# php artisan dokumen:clean-before-import --type=all --force
```

#### 3. Jalankan Migration:
```bash
php artisan migrate --force
```

#### 4. Import Data:
```bash
php artisan import:csv --path=public/DATA\ 12.csv
```

### Verifikasi Data:
```bash
php artisan tinker
>>> \App\Models\Dokumen::count();
>>> \App\Models\Dokumen::where('created_by', 'csv_import')->count();
```

---

## ✅ **Testing Checklist**

- [ ] Backup database sudah dibuat
- [ ] Data lama sudah dihapus (jika diperlukan)
- [ ] Migration sukses dijalankan
- [ ] File CSV ada di server
- [ ] Import command berjalan tanpa error
- [ ] Data muncul di dashboard pembayaran
- [ ] Nomor agenda otomatis tergenerate
- [ ] Data duplikat terupdate dengan benar
- [ ] PDF export berfungsi untuk data yang diimport
- [ ] Statistik dashboard pembayaran sesuai dengan data yang diimport

---

## 📞 **Support**

Jika mengalami masalah:
1. Cek Laravel log: `tail -f storage/logs/laravel.log`
2. Verifikasi table structure: `php artisan db:show dokumens`
3. Test dengan file sample kecil terlebih dahulu
4. Hubungi developer dengan log error yang spesifik

---

**Catatan**:
- Proses import akan menambahkan sekitar 800+ records dari DATA 12.csv
- Estimasi waktu proses: 2-5 menit tergantung spesifikasi server
- Pastikan server memiliki cukup resources (memory & storage)
# Panduan Generate 100 Dokumen Dummy

File seeder ini akan membuat 100 dokumen dummy dengan data yang realistis untuk testing di server VPS.

## Fitur

- ✅ 100 dokumen dengan data lengkap
- ✅ Beberapa dokumen memiliki data yang sama untuk vendor, kebun, kategori, jenis dokumen, jenis sub pekerjaan, dan jenis pembayaran (untuk testing filter)
- ✅ Semua field yang diminta terisi dengan data realistis
- ✅ Nomor agenda, PR, PO, BA, SPK yang unik
- ✅ Tanggal yang logis (tanggal SPP < tanggal BA < tanggal SPK < tanggal berakhir SPK)

## Field yang Diisi

Setiap dokumen akan memiliki:
- ✅ nomor_agenda (format: AGD/XXX/MM/YYYY)
- ✅ bagian (DPM, SKH, SDM, TEP, KPL, AKN, TAN, PMO)
- ✅ nama_pengirim
- ✅ nomor_spp
- ✅ tanggal_spp
- ✅ uraian_spp
- ✅ nilai_rupiah (10 juta - 1 milyar)
- ✅ kategori
- ✅ jenis_dokumen
- ✅ jenis_sub_pekerjaan
- ✅ jenis_pembayaran
- ✅ kebun
- ✅ nomor_pr (dalam tabel dokumen_prs)
- ✅ nomor_po (dalam tabel dokumen_pos)
- ✅ dibayar_kepada/vendor
- ✅ no_berita_acara
- ✅ tanggal_berita_acara
- ✅ no_spk
- ✅ tanggal_spk
- ✅ tanggal_berakhir_spk

## Cara Menjalankan

### Di Server VPS (Ubuntu)

1. **SSH ke server VPS:**
```bash
ssh user@your-server-ip
```

2. **Masuk ke direktori project:**
```bash
cd /path/to/agenda_online_ptpn
```

3. **Jalankan seeder:**
```bash
php artisan db:seed --class=DokumenDummySeeder
```

### Atau menggunakan Artisan Tinker

```bash
php artisan tinker
```

Kemudian jalankan:
```php
\DB::table('dokumens')->truncate();
\DB::table('dokumen_prs')->truncate();
\DB::table('dokumen_pos')->truncate();
\Artisan::call('db:seed', ['--class' => 'DokumenDummySeeder']);
```

## Catatan Penting

⚠️ **PERINGATAN:** Seeder ini akan menambahkan 100 dokumen baru ke database. Jika Anda ingin menghapus dokumen yang sudah ada terlebih dahulu, jalankan:

```bash
php artisan tinker
```

Kemudian:
```php
\App\Models\Dokumen::truncate();
\App\Models\DokumenPR::truncate();
\App\Models\DokumenPO::truncate();
```

## Data yang Akan Diulang

Untuk memudahkan testing filter, beberapa dokumen akan memiliki data yang sama:

- **Vendor:** 8 vendor berbeda (akan diulang setiap 8 dokumen)
- **Kebun:** 8 kebun berbeda (akan diulang setiap 8 dokumen)
- **Kategori:** 4 kategori berbeda (akan diulang setiap 4 dokumen)
- **Jenis Dokumen:** 5 jenis dokumen berbeda (akan diulang setiap 5 dokumen)
- **Jenis Sub Pekerjaan:** 5 jenis berbeda (akan diulang setiap 5 dokumen)
- **Jenis Pembayaran:** 6 jenis pembayaran berbeda (akan diulang setiap 6 dokumen)

## Contoh Data yang Dihasilkan

- Nomor Agenda: `AGD/001/01/2024`, `AGD/002/02/2024`, dll
- Nomor SPP: `123/M/SPP/15/01/2024`
- Vendor: `PT ABC Perkebunan`, `PT XYZ Konstruksi`, dll
- Kebun: `Kebun A`, `Kebun B`, dll
- Nilai Rupiah: Random antara 10 juta - 1 milyar

## Troubleshooting

Jika terjadi error:
1. Pastikan semua migration sudah dijalankan: `php artisan migrate`
2. Pastikan model Dokumen, DokumenPR, dan DokumenPO sudah ada
3. Pastikan database connection sudah benar di `.env`











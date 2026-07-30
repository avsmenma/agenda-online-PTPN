# Latihan Bertahap + Kunci Jawaban

> **Cara pakai:** tutup kunci jawaban. Jawab dulu. Baru buka.
> Membaca kunci duluan terasa seperti mengerti, padahal tidak.

Sebelum mulai:

```bash
git checkout -b latihan-sidang
```

Selesai tiap latihan: `git checkout -- <berkas>` untuk mengembalikan.

---

# §A — Navigasi: "Tunjukkan di mana"

Ini latihan paling penting. Penguji akan berkata *"coba tunjukkan kode yang menangani ini"*,
dan lima detik kebingungan sudah cukup membuat mereka curiga Anda tidak menulisnya.

Target: jawab **file + nomor baris**, dalam 30 detik, tanpa kunci.

### A1
Di mana ditentukan bahwa hanya role `admin` dan `akutansi` yang boleh membuka
`/documents/akutansi`?

<details><summary>Kunci</summary>

`routes/web.php:435` — `Route::middleware(['auth', 'role:admin,akutansi'])`.
`auth` memastikan sudah login, `role:...` memastikan perannya benar.
</details>

### A2
Kalau user dengan role salah memaksa membuka URL itu, kode mana yang menolaknya, dan dia
dilempar ke mana?

<details><summary>Kunci</summary>

`app/Http/Middleware/CheckRole.php:48-68`. Dilempar ke `/login` dengan pesan flash.
Kalau permintaannya AJAX, yang dibalas JSON 403 (baris 56-62), bukan redirect.
Percobaannya juga dicatat `Log::warning` di baris 49.
</details>

### A3
Halaman daftar dokumen akutansi merender berapa baris `<tr>` di HTML-nya?

<details><summary>Kunci</summary>

**Nol.** View-nya cuma 109 baris dan tabelnya kosong —
`daftarAkutansiTabulator.blade.php:73` hanya `<div id="akutansiTabulatorTable">`.
Isinya diambil permintaan AJAX kedua.
</details>

### A4
Dari mana JavaScript tahu URL untuk mengambil datanya?

<details><summary>Kunci</summary>

Dua langkah:
1. `daftarAkutansiTabulator.blade.php:18` — `'dataUrl' => route('documents.akutansi.data')`,
   ikut masuk `window.DOCUMENT_TABULATOR_CONFIG` di baris 100.
2. `public/js/document-tabulator.js:1006` — `ajaxURL: CFG.dataUrl`.
</details>

### A5
Teks badge **"⏳ Sedang Diproses"** dihasilkan di server atau di JavaScript?

<details><summary>Kunci</summary>

**Server** — `app/Support/AkutansiDocumentRow.php:113`. JavaScript hanya membaca
`row.status_badge.text`. Nol logika bisnis di klien.
</details>

### A6
Kalau user memfilter status lalu menekan Export, isi file Excel harus sama persis dengan
isi tabel. Kode apa yang menjamin itu?

<details><summary>Kunci</summary>

`buildAkutansiQuery()` (`DashboardAkutansiController.php:149`) dipakai bertiga:
`dokumens()` baris 342, `datatable()` baris 44, `exportDocuments()` baris 108.
Satu sumber filter → mustahil melenceng.
</details>

### A7
Label kolom "Nomor SPP" tertulis di mana?

<details><summary>Kunci</summary>

`config/document_columns.php:40`. Dulu array ini disalin di empat controller sehingga
rawan berbeda-beda; sekarang satu sumber (komentarnya sendiri menjelaskan itu di baris 3-20).
</details>

### A8
Kolom Tanggal Masuk tampil sebagai `30-07-2026 14:05`. Format itu ditentukan di mana?

<details><summary>Kunci</summary>

`app/Support/DocumentRow.php:90` — `'tanggal_masuk' => 'd-m-Y H:i'` di dalam
`formatDates()`.
</details>

### A9
Berapa query database yang berjalan untuk mengisi kolom "Dibayar Kepada" pada 100 baris?

<details><summary>Kunci</summary>

**Satu**, bukan seratus. Relasinya sudah di-*eager load* di
`DashboardAkutansiController.php:156` (`->with([... 'dibayarKepadas'])`), lalu
`DocumentRow.php:47` tinggal `pluck` dari koleksi yang sudah ada di memori.
Ini penanggulangan masalah **N+1**.
</details>

### A10
Kenapa `buildAkutansiHandlerOptions()` dipanggil sekali di baris 52, bukan di dalam
`map()` di baris 55-57?

<details><summary>Kunci</summary>

Karena di dalamnya ada query ke tabel `bagian` (baris 80). Kalau dipanggil per baris,
100 baris = 100 query tambahan. Komentar di baris 68 menyebut alasannya persis:
"SEKALI per-request ... hindari query per-baris".
</details>

---

# §B — Live coding tingkat 1: ubah satu baris

Tujuan: terbiasa **mengubah kode lalu membuktikannya**, bukan sekadar menunjuk.
Tiap latihan: ubah → jalankan → lihat hasilnya → kembalikan.

Jalankan server lokal dulu:

```bash
php artisan serve
```

### B1 — Ubah label kolom
*"Coba ganti label kolom 'No PO' jadi 'Nomor Purchase Order'."*

<details><summary>Kunci</summary>

`config/document_columns.php:64` — ubah `'nomor_po' => 'No PO'` jadi
`'nomor_po' => 'Nomor Purchase Order'`.

Config di-cache, jadi kalau tidak berubah:
```bash
php artisan config:clear
```
**Kalimat yang bagus diucapkan:** "labelnya terpusat di config supaya lima role tidak
punya salinan sendiri-sendiri."
</details>

### B2 — Ubah aturan validasi
*"Alasan pengajuan reset 2FA minimal 10 karakter. Coba jadikan 20."*

<details><summary>Kunci</summary>

Dua baris, di berkas yang sama:
`app/Http/Requests/Concerns/ValidatesTwoFactorResetReason.php:26` → `'min:20'`, dan
baris 34 pesannya → `'Alasan minimal 20 karakter.'`

Buktikan:
```bash
php artisan test --filter=TwoFactorResetRequestTest
```
Akan ada test **merah** — `test_alasan_kurang_dari_sepuluh_karakter_ditolak`. Itu **benar**,
bukan kecelakaan: testnya memang menjaga aturan lama.

**Kalimat yang bagus:** "trait ini dipakai dua jalur pengajuan, jadi mengubah di sini
otomatis berlaku di keduanya — itu memang tujuannya dibuat trait."
</details>

### B3 — Ubah format tanggal
*"Tanggal Bayar tampil `30/07/2026`. Buat jadi `30-07-2026`."*

<details><summary>Kunci</summary>

`app/Support/DocumentRow.php:95` — `'tanggal_dibayar' => 'd/m/Y'` jadi `'d-m-Y'`.
Berlaku otomatis untuk lima role karena kelas ini induk bersama.
</details>

### B4 — Ubah batas keamanan
*"Endpoint data membatasi berapa baris per permintaan? Naikkan jadi 500."*

<details><summary>Kunci</summary>

`DashboardAkutansiController.php:46-47`:
```php
$size = (int) $request->input('size', 100);
$size = ($size > 0 && $size <= 200) ? $size : 100;   // 200 → 500
```
**Pertanyaan lanjutan yang pasti muncul:** *"kenapa harus dibatasi?"*
Jawab: karena `size` datang dari URL dan bisa diketik siapa saja. Tanpa batas, seseorang
bisa meminta sejuta baris sekaligus dan membuat server kehabisan memori.
</details>

---

# §C — Live coding tingkat 2: end-to-end + test

Ini yang membedakan "bisa mengubah" dari "bisa membangun".

### C1 — Tambah aturan validasi baru + testnya
*"Tambahkan batas maksimal 500 karakter untuk alasan reset 2FA, lengkap dengan testnya."*

<details><summary>Kunci — langkah demi langkah</summary>

**1. Ubah aturannya** — `ValidatesTwoFactorResetReason.php:26`:
```php
'reason' => ['required', 'string', 'min:10', 'max:500'],
```

**2. Tambah pesannya** — baris 34, di bawah `reason.min`:
```php
'reason.max' => 'Alasan maksimal 500 karakter.',
```

**3. Tambah test** — di `tests/Feature/TwoFactorResetRequestTest.php`:
```php
public function test_alasan_lebih_dari_500_karakter_ditolak(): void
{
    $user = $this->userDengan2faAktif();

    $this->withSession(['2fa_user_id' => $user->id])
        ->post(route('2fa.reset-request'), ['reason' => str_repeat('a', 501)])
        ->assertSessionHasErrors('reason');

    $this->assertDatabaseCount('two_factor_reset_requests', 0);
}
```

**4. Buktikan testnya benar-benar menggigit** — ini bagian yang paling mengesankan penguji:
```bash
php artisan test --filter=TwoFactorResetRequestTest      # hijau
```
lalu **hapus** `'max:500'` dari aturannya, jalankan lagi → **merah**. Kembalikan → hijau lagi.

**Kalimat yang bagus:** "test yang tidak pernah saya lihat gagal belum tentu menguji apa
pun. Jadi saya rusak dulu kodenya untuk memastikan testnya memang menjaga."
</details>

### C2 — Tambah field baru ke keluaran JSON
*"Tambahkan umur dokumen dalam hari ke setiap baris tabel akutansi."*

<details><summary>Kunci — langkah demi langkah</summary>

**1. Hitung di DTO** — `app/Support/AkutansiDocumentRow.php`, di dalam `fromDokumen()`
sebelum `return $row;` (sekitar baris 51):
```php
$row['umur_hari'] = $dokumen->tanggal_masuk
    ? (int) Carbon::parse($dokumen->tanggal_masuk)->diffInDays(now())
    : null;
```
`Carbon` sudah di-import di baris 7, jadi tidak perlu menambah `use`.

**2. Buktikan lewat test** — di `tests/Feature/AkutansiDatatableTest.php`, tiru pola
`test_baris_memuat_objek_status_badge_dan_deadline` (baris 74) dan tambahkan `umur_hari`
ke struktur JSON yang di-assert.

**3. Jalankan:**
```bash
php artisan test --filter=AkutansiDatatableTest
```

**Pertanyaan lanjutan yang pasti muncul:** *"kenapa dihitung di PHP, tidak di JavaScript?"*
Jawab: sama seperti badge status — supaya aturannya satu tempat dan bisa diuji tanpa browser.
Kalau dihitung di JavaScript, jam komputer pengguna yang salah akan membuat angkanya salah.
</details>

### C3 — Rusak, baca error, perbaiki
*Latihan mental, bukan fitur. Tujuannya supaya Anda tidak panik melihat layar merah.*

<details><summary>Kunci</summary>

Lakukan tiga kerusakan ini satu per satu, baca pesannya, lalu kembalikan:

| Rusakkan | Yang akan Anda lihat | Artinya |
|---|---|---|
| Hapus `'akutansi'` dari `role:admin,akutansi` (`routes/web.php:435`) | dilempar ke `/login` terus | middleware bekerja |
| Ganti `'data'` jadi `'datax'` pada nama rute (baris 439) | `Route [documents.akutansi.data] not defined` | Blade memanggil rute lewat **nama** |
| Hapus `'dibayarKepadas'` dari `->with([...])` (baris 156) | halaman tetap jalan tapi **jauh lebih lambat** | eager loading = optimasi, bukan syarat jalan |

Yang ketiga paling berharga: bug performa **tidak menampakkan diri sebagai error**.
Kalau penguji bertanya "bagaimana Anda tahu aplikasi Anda cepat?", inilah contoh nyatanya.
</details>

---

## Checklist kesiapan

Centang jujur. Yang belum tercentang, itulah yang perlu diulang.

- [ ] Saya bisa menyebut 9 langkah alur tanpa membuka catatan
- [ ] Saya bisa menjawab 10 soal §A dalam 30 detik masing-masing
- [ ] Saya bisa mengubah label kolom dan membuktikannya di browser
- [ ] Saya bisa menambah aturan validasi **beserta** testnya
- [ ] Saya bisa menjelaskan kenapa badge status dihitung di server
- [ ] Saya bisa menjelaskan N+1 dan menunjuk kode yang mencegahnya
- [ ] Saya tidak panik saat layar merah — saya baca baris pertama pesannya

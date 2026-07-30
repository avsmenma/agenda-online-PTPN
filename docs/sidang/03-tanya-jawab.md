# Tanya-Jawab Penguji

Angka di dokumen ini terverifikasi 2026-07-30: **43 berkas test, 295 test, 93 migrasi**,
`document-tabulator.js` 2.085 baris dipakai 5 role.

Cara latihan: baca pertanyaannya, jawab **dengan suara keras** sampai selesai, baru buka
kuncinya. Menjawab dalam hati selalu terasa lebih lancar daripada kenyataannya.

---

## 1. "Coba jelaskan aplikasi Anda dalam dua menit."

<details><summary>Kerangka jawaban</summary>

Tiga kalimat, jangan lebih:

> "Ini aplikasi pencatatan dan persetujuan dokumen pembayaran di PTPN IV Regional V.
> Dokumen bergerak melalui beberapa tim — operator, verifikasi, perpajakan, akuntansi,
> lalu tim pembayaran — dan tiap tim melihat tabel yang sama tapi dengan hak dan
> tampilan berbeda sesuai perannya. Dibangun dengan Laravel 12 dan MySQL, tabelnya
> memakai satu mesin JavaScript bersama supaya lima tim itu tidak punya lima salinan kode."

Lalu **berhenti**. Biarkan mereka bertanya lanjutan. Menjelaskan terlalu panjang di awal
membuka pintu ke bagian yang belum Anda kuasai.
</details>

## 2. "Kenapa Laravel? Kenapa tidak PHP biasa?"

<details><summary>Kunci</summary>

Sebut tiga yang bisa Anda tunjuk kodenya:

1. **Routing terpusat** — semua URL dan hak aksesnya terbaca di satu berkas
   (`routes/web.php`), bukan tersebar sebagai file PHP terpisah per halaman.
2. **Eloquent ORM** — query ditulis sebagai objek, parameternya otomatis di-*binding*,
   sehingga aman dari SQL injection tanpa harus ingat `escape` tiap kali.
3. **Migrasi** — perubahan struktur database tercatat sebagai kode (93 berkas di
   `database/migrations/`), jadi struktur di komputer saya dan di server dijamin sama.

Kalau ditanya kekurangannya: Laravel lebih berat dan punya kurva belajar; untuk aplikasi
sekecil satu formulir itu berlebihan. Untuk aplikasi dengan enam peran dan alur
persetujuan seperti ini, justru sepadan.
</details>

## 3. "Bagaimana Anda mencegah SQL injection?"

<details><summary>Kunci</summary>

Query ditulis lewat Eloquent, contohnya `DashboardAkutansiController.php:153-156`:

```php
Dokumen::query()
    ->where('status', '!=', 'returned_to_bidang')
    ->excludeCsvImports()
    ->with(['dokumenPos', 'dokumenPrs', 'dibayarKepadas']);
```

Nilai yang masuk `where()` dikirim ke database sebagai **parameter terpisah**, bukan
disambung jadi teks SQL. Jadi kalau ada yang mengetik `' OR 1=1--` di kotak pencarian,
itu diperlakukan sebagai kata yang dicari, bukan sebagai perintah.

Kalau ditanya **"apakah ada tempat yang pakai SQL mentah?"** — jawab jujur, dan jawaban
jujurnya justru terdengar menguasai:

> "Ada, sekitar 48 pemakaian `DB::raw`/`whereRaw`/`orderByRaw` untuk hal yang tidak bisa
> diungkapkan Eloquent, misalnya fungsi `YEAR()` dan pengurutan berdasarkan kondisi.
> Tapi masukan dari pengguna tetap dioper sebagai parameter, tidak disambung ke teks SQL."

Contoh yang bisa langsung Anda buka — `app/Http/Controllers/AutocompleteController.php:29`:
```php
->orderByRaw('CASE WHEN nama_penerima LIKE ? THEN 1 ELSE 2 END', [$query . '%'])
```
Perhatikan tanda `?` dan nilainya di array terpisah. **Itu** yang membuatnya aman, bukan
"tidak pakai raw sama sekali". Jangan mengklaim nol mutlak untuk hal yang belum Anda cek.

Cara mengeceknya sendiri kalau lupa:
```bash
grep -rn "DB::raw\|whereRaw\|orderByRaw" app/ --include=*.php | head
```
</details>

## 4. "Bagaimana pembagian hak aksesnya?"

<details><summary>Kunci</summary>

Dua lapis:

**Lapis 1 — pintu masuk.** Middleware `role:` di rute
(`routes/web.php:435`), diperiksa `app/Http/Middleware/CheckRole.php:48`. Ini menentukan
**siapa boleh membuka halaman apa**.

**Lapis 2 — di dalam halaman.** Tiap baris tabel membawa hak yang sudah dihitung server,
misalnya `AkutansiDocumentRow.php:45`:
```php
$row['can_edit'] = DokumenHelper::canEditDocument($dokumen, 'akutansi');
```
Ini menentukan **baris mana yang boleh diedit**, karena satu tim bisa melihat dokumen
milik tim lain tapi tidak boleh mengubahnya.

> **Poin kunci yang mengesankan:** hak dihitung di server dan dikirim sebagai data, bukan
> diputuskan JavaScript. Kalau diputuskan di klien, siapa pun bisa membuka Inspect Element
> dan menghidupkan tombol yang seharusnya mati.
</details>

## 5. "Kenapa datanya diambil lewat permintaan kedua? Kenapa tidak sekalian saja?"

<details><summary>Kunci</summary>

Tiga alasan:

1. **Halaman muncul duluan.** Pengguna melihat kerangka dan toolbar seketika, tidak
   menunggu ribuan baris selesai dirakit.
2. **Bisa dimuat bertahap** — `document-tabulator.js:1012`, `progressiveLoad: 'scroll'`.
   Data diambil per potongan sambil digulir.
3. **Filter dan urutan tidak perlu memuat ulang halaman** — cukup ambil JSON baru.

Efek sampingannya juga bagus: endpoint JSON-nya bisa dipakai ulang oleh fitur Export
tanpa menulis query kedua.
</details>

## 6. "Kelas `DocumentRow` ini apa? Kenapa dibuat abstrak?"

<details><summary>Kunci</summary>

Ia mengubah satu baris database jadi satu baris tabel siap tampil.

Dibuat abstrak karena lima peran punya **banyak kesamaan** (format rupiah, format tanggal,
gabung nama penerima, sanitasi link) tapi **sedikit perbedaan** (akuntansi punya badge
status dan deadline sendiri). Yang sama ditaruh di induk `DocumentRow.php:24`, yang beda
ditaruh di anak `AkutansiDocumentRow.php:25`.

Sebelumnya kode ini disalin-tempel antar peran, dan akibatnya nyata: memperbaiki bug di
satu halaman tidak memperbaikinya di lima halaman lain, jadi bug yang "sudah diperbaiki"
muncul lagi di tempat lain.

> Kalau penguji menyukai istilah: ini penerapan **DRY** dan **inheritance**, dan objek yang
> dihasilkan berperan sebagai **DTO** (Data Transfer Object).
</details>

## 7. "Bagaimana Anda menguji aplikasi ini?"

<details><summary>Kunci</summary>

Tiga tingkat, sebutkan ketiganya:

| Tingkat | Contoh | Yang dijamin |
|---|---|---|
| **Unit** | `tests/Unit/AkutansiDocumentRowTest.php` | aturan badge status benar, tanpa database maupun browser |
| **Feature** | `tests/Feature/AkutansiDatatableTest.php` | endpoint membalas bentuk JSON yang benar dan menolak tamu |
| **Manual di browser** | — | yang tak bisa ditangkap test |

Totalnya **295 test** di 43 berkas, dijalankan otomatis tiap push lewat GitHub Actions
(`.github/workflows/tests.yml`).

**Kalimat pamungkas** — ini yang membedakan jawaban biasa dari jawaban bagus:

> "Test hijau tidak berarti fitur jalan. Pernah ada cacat yang lolos dari ratusan test
> hijau dan baru ketahuan saat diuji langsung di browser, karena penyimpanan lokal di
> peramban menimpa data yang dikirim server. Sejak itu apa pun yang digambar pustaka
> pihak ketiga wajib saya cek langsung, tidak cukup lewat test."

Itu cerita nyata dari proyek ini, dan penguji akan mengingatnya.
</details>

## 8. "Bagaimana kalau datanya seratus ribu baris?"

<details><summary>Kunci</summary>

Empat pertahanan yang sudah ada, tunjuk satu per satu:

1. **Pagination** — `DashboardAkutansiController.php:50`, `$query->paginate($size, ...)`.
   Server tidak pernah mengirim semuanya sekaligus.
2. **Batas atas permintaan** — baris 47, maksimal 200 baris per permintaan meski URL-nya
   diutak-atik.
3. **Progressive load** — data menyusul sambil digulir.
4. **Eager loading** — baris 156, mencegah N+1.

Kalau ditanya "apa yang masih kurang?", jawab jujur: indeks database sudah ditambahkan
untuk kolom yang sering difilter, tapi untuk skala jauh lebih besar berikutnya yang perlu
adalah pencarian ter-indeks penuh, karena pencarian teks sekarang memakai `LIKE` yang
tidak memanfaatkan indeks.
</details>

## 9. "Autentikasinya bagaimana? Ada dua faktor?"

<details><summary>Kunci</summary>

Ya. Alurnya, di `app/Http/Controllers/Auth/LoginController.php`:

1. Username + password diverifikasi dulu.
2. Kalau benar **dan** akun itu mengaktifkan 2FA — baris **39** menaruh penanda
   `session(['2fa_user_id' => $user->id])`, lalu baris **43** `Auth::logout()`.
3. Pengguna diarahkan ke halaman verifikasi kode. Baru setelah kodenya benar, dia
   benar-benar login.

> Urutan itu penting dan layak Anda tekankan: penanda sesi **hanya ada setelah password
> terbukti benar**. Itulah yang membuat halaman verifikasi tahu sedang melayani siapa
> tanpa pengguna itu login — dan yang membuat perbaikan di pertanyaan 10 aman.

> **Ini pertanyaan yang paling menguntungkan Anda**, karena ada cerita nyata di baliknya.
> Lihat pertanyaan 10.
</details>

## 10. "Pernah menemukan bug serius? Ceritakan."

<details><summary>Kunci — pakai yang ini, ceritanya kuat dan baru</summary>

Ceritakan lingkaran mati reset 2FA (diperbaiki 30 Juli 2026):

> "Ada pengguna yang kehilangan aplikasi *authenticator* sekaligus kode pemulihannya.
> Ternyata dia terkunci permanen, dan penyebabnya bukan satu bug melainkan **tiga pintu
> yang saling mengunci**: tombol pengajuan reset hanya ada di halaman profil yang butuh
> login; untuk login dia butuh 2FA yang sudah hilang; dan menu programmer menolak mereset
> kalau belum ada pengajuan. Jadi satu-satunya jalan keluar adalah menyentuh database
> langsung.
>
> Perbaikannya: saya tambahkan tombol pengajuan di halaman verifikasi 2FA itu sendiri,
> dengan endpoint yang boleh diakses tanpa login. Yang membuatnya tetap aman adalah
> identitas pengaju tidak diambil dari isian formulir, melainkan dari penanda sesi yang
> **hanya terisi setelah passwordnya terbukti benar**. Endpoint itu juga hanya membuat
> permintaan — mematikan 2FA tetap wewenang programmer, supaya tiap reset punya jejak."

Kalau ditanya "kenapa tidak sekalian dilonggarkan saja aturan programmernya?" — jawab:
karena itu menghapus jejak audit. Lebih baik menambah pintu masuk yang aman daripada
melepas kunci yang sudah benar.

**Bonus kalau mereka menggali lebih dalam:** cacat kedua yang hanya ketahuan saat diuji
di browser — halaman itu punya skrip yang menghapus semua kotak pesan setelah 5 detik,
sehingga saat pengajuan ditolak, formulirnya terbuka kembali tapi alasan penolakannya
sudah lenyap. Pengguna melihat formulir tanpa tahu apa salahnya. Perbaikannya: pesan
kesalahan dirender menetap dengan kelas CSS yang berbeda.

Berkasnya: `routes/web.php` (grup `guest`),
`app/Http/Requests/Concerns/ValidatesTwoFactorResetReason.php`,
`app/Http/Controllers/TwoFactorResetRequestController.php:35`,
`tests/Feature/TwoFactorResetRequestTest.php`.
</details>

## 11. "Apa kelemahan aplikasi Anda?"

<details><summary>Kunci — jangan jawab "tidak ada"</summary>

Menjawab "tidak ada kelemahan" adalah jawaban terburuk. Yang dinilai adalah kesadaran
Anda, bukan kesempurnaan aplikasi. Sebut tiga, dan sebut juga rencananya:

1. **Masih ada berkas yang terlalu besar.** `layouts/app.blade.php` hampir 6.000 baris.
   Rencananya dipecah bertahap — sudah dimulai, misalnya modal kustomisasi kolom yang
   dulu disalin di lima halaman kini jadi satu berkas bersama.
2. **Cakupan test belum merata.** 295 test terdengar banyak, tapi terkonsentrasi di
   bagian tabel dokumen; alur lama seperti impor CSV masih tipis.
3. **Aset frontend masih dari CDN**, belum lewat proses build. Konsekuensinya aplikasi
   bergantung pada koneksi internet ke penyedia CDN.

Ketiganya adalah kelemahan **yang saya tahu dan sudah saya petakan** — itu poinnya.
</details>

## 12. "Ini dibuat pakai AI, ya?"

<details><summary>Kunci — baca bagian ini dua kali</summary>

**Jangan berbohong.** Selain salah, itu juga mudah ketahuan: pertanyaan berikutnya
biasanya *"kalau begitu coba ubah bagian ini sekarang"*, dan di situlah kebohongan runtuh.

Jawaban yang jujur sekaligus kuat:

> "Ya, saya memakai bantuan AI dalam menulis sebagian kodenya, sama seperti saya memakai
> dokumentasi dan Stack Overflow. Yang saya pastikan adalah saya memahami dan bisa
> mempertanggungjawabkan hasilnya. Silakan tunjuk bagian mana saja, saya jelaskan alurnya
> dan saya ubah sekarang."

Lalu **buktikan**. Kalimat itu hanya kuat kalau Anda benar-benar bisa. Itulah seluruh isi
`02-latihan.md` — enam hari ini bukan untuk menghafal alasan, tapi untuk membuat kalimat
tadi jadi benar.

Hal yang tetap sepenuhnya milik Anda dan layak disebut: keputusan alur bisnisnya (dokumen
bergerak lewat peran apa saja, siapa boleh apa), pemilihan mana yang dikerjakan lebih dulu,
pengujian di lapangan, dan penanganan masalah yang muncul dari pengguna sungguhan —
termasuk kasus 2FA di pertanyaan 10, yang ditemukan dari laporan pengguna nyata, bukan
dari kode.

Satu lagi yang jarang dipunyai mahasiswa lain, dan sangat layak disebut: **aplikasi ini
sudah dipakai sungguhan** oleh tim di kantor, dengan data asli, bukan hanya berjalan di
laptop saat demo.
</details>

---

## Pertanyaan jebakan singkat

| Pertanyaan | Jawaban pendek |
|---|---|
| "Apa bedanya `GET` dan `POST` di sini?" | `GET` mengambil data dan boleh diulang tanpa efek; `POST` mengubah keadaan. Pengajuan reset 2FA `POST` karena membuat baris baru. |
| "Apa itu CSRF, di mana ditangani?" | Serangan menyuruh peramban korban mengirim permintaan diam-diam. Ditangkal token — dioper ke JS lewat `daftarAkutansiTabulator.blade.php:21`. |
| "Kenapa password tidak bisa dilihat di database?" | Disimpan sebagai *hash* satu arah, bukan terenkripsi. Tidak bisa dikembalikan, hanya dicocokkan. |
| "Migrasi itu apa?" | Perubahan struktur database yang ditulis sebagai kode dan bisa dijalankan berurutan, sehingga struktur di semua komputer sama. Ada 93 di proyek ini. |
| "Kenapa ada `roleStatuses` dan `roleData` terpisah dari tabel dokumen?" | Karena satu dokumen punya status berbeda-beda di tiap peran. Kalau dijadikan kolom, tabel dokumen akan punya belasan kolom status. |
| "Bedanya `session` dan `cookie`?" | Cookie disimpan di peramban pengguna; session disimpan di server dan cookie hanya membawa kuncinya. Penanda `2fa_user_id` ada di session — makanya tidak bisa dipalsukan pengguna. |

---

## Tiga kalimat yang jangan pernah diucapkan

1. ~~"Saya kurang tahu bagian itu."~~ → **"Bagian itu ada di `<file>`, boleh saya buka
   dulu?"** Membuka berkas itu wajar; mengaku tidak tahu tanpa mencari yang tidak wajar.
2. ~~"Pokoknya jalan saja."~~ → sebutkan **kenapa** dirancang begitu, sependek apa pun.
3. ~~"Tidak ada kelemahannya."~~ → lihat pertanyaan 11.

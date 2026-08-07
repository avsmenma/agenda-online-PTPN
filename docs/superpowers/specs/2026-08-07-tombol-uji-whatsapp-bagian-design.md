# Tombol Uji Kiriman WhatsApp di Role Bagian — Rancangan

**Tanggal:** 2026-08-07
**Status:** disetujui user
**Sifat:** **SEMENTARA** — dibuat untuk sesi uji coba pengguna, dicabut manual setelahnya
(lihat §9 Daftar Pencabutan).

---

## 1. Masalah

Kuesioner uji coba role Bagian (`docs/kuesioner-uji-coba-role-bagian-2026-08.md`) punya
butir **C4**: *"Kalau ada dokumen Bapak/Ibu dikembalikan, ingin diberi tahu lewat apa?"*
Responden tidak bisa menjawabnya dengan berdasar kalau belum pernah melihat wujud
pemberitahuan WhatsApp-nya.

Masalahnya berlapis:

- Alur sungguhan hanya terpicu bila Team Verifikasi benar-benar mengembalikan dokumen —
  tidak bisa diatur waktunya untuk sebuah sesi demo.
- **Nol dari delapan akun Bagian di produksi punya `phone_number` terisi**
  (pemeriksaan 2026-08-06, tercatat di docblock `DocumentReturnNotifier`). Jadi cabang
  WhatsApp pada notifier itu belum pernah sekalipun berjalan di produksi.
- Akibatnya, sampai hari ini **belum ada bukti bahwa Fonnte benar-benar bisa mengirim
  dari server produksi.** Token ada di `.env`, kodenya jalan di test — tapi jalur
  ujung-ke-ujungnya belum pernah terbukti.

Tombol ini menjawab keduanya sekaligus: responden melihat wujud pesannya, dan kita
mendapat bukti pertama bahwa gateway-nya hidup.

## 2. Lingkup

**Termasuk:** satu panel di halaman Bagian berisi input nomor + tombol kirim, yang
mengirim satu pesan WhatsApp berisi **data contoh** dengan penanda uji coba.

**Tidak termasuk:** mengubah alur pengembalian sungguhan, mengisi `phone_number` akun
Bagian, mengirim ke banyak nomor sekaligus, menyimpan riwayat kiriman uji.

## 3. Keputusan yang sudah diambil user

| # | Keputusan | Pilihan |
|---|---|---|
| 1 | Nomor tujuan | **Diketik saat itu juga** di panel — bukan dari `users.phone_number`, yang memang kosong di semua akun Bagian |
| 2 | Isi pesan | **Data contoh sepenuhnya** (agenda `9999_2026`), berpenanda `[UJI COBA]`. Tidak memakai dokumen sungguhan |
| 3 | Cara mencabut | **Selalu tampil, dihapus manual nanti** — tanpa saklar `.env` |

Risiko keputusan 3 sudah disampaikan (fitur uji coba yang lupa dicabut menjadi kode mati
permanen — pola yang berulang kali harus diberantas di project ini) dan diterima user.
Mitigasinya bukan saklar, melainkan **isolasi**: seluruh fitur ditaruh di berkas
tersendiri sehingga pencabutan = menghapus berkas, bukan membedah kode yang masih hidup.

## 4. Arsitektur

```
Panel Blade (partial tersendiri)
  └─ POST /bagian/uji-whatsapp  ──►  UjiWhatsAppBagianController::kirim()
                                        ├─ validasi nomor
                                        ├─ DocumentReturnNotifier::namaBagian($kode)
                                        ├─ DocumentReturnNotifier::pesanUjiCoba(...)  ◄── template BERSAMA
                                        └─ FonnteWhatsAppService::sendMessage()
                                              └─ hasil asli diteruskan apa adanya ke JSON
```

### 4.1 Route

Ditambahkan **satu baris** di dalam grup `Route::middleware(['auth', 'bagian'])` yang
sudah ada di `routes/web.php` (grup yang sama dengan `bagian.notifikasi.tandai-dibaca`):

```php
Route::post('/bagian/uji-whatsapp', [UjiWhatsAppBagianController::class, 'kirim'])
    ->name('bagian.uji-whatsapp')
    ->middleware('throttle:5,1');
```

`throttle:5,1` (5 kiriman per menit per user) bukan formalitas: setiap kiriman Fonnte
memotong kuota berbayar. Satu tombol tanpa batas bisa menghabiskannya dalam semenit.

### 4.2 Controller

Berkas baru `app/Http/Controllers/UjiWhatsAppBagianController.php`, satu method.
**Sengaja tidak ditempel ke `BagianDokumenController`** — memisahkannya membuat
pencabutan nanti jadi penghapusan satu berkas, bukan operasi bedah di controller yang
masih dipakai produksi.

Docblock kelasnya memuat daftar pencabutan (§9) supaya orang yang menemukannya enam
bulan lagi tahu berkas ini memang dirancang untuk dibuang.

**Validasi:**

```php
'nomor_hp' => ['required', 'string', 'regex:/^(\+?62|0)8[0-9]{7,13}$/'],
```

Pesan galat Indonesia: *"Masukkan nomor WhatsApp yang sah, contoh 081234567890."*

**Nama bagian** diambil dari akun yang login: `Auth::user()->bagian_code` →
`DocumentReturnNotifier::namaBagian()`. Responden dari TAN menerima "Bagian Tanaman",
bukan nama karangan — inilah yang membuat pesan contoh tetap terasa nyata.

**Tidak perlu cek `bagian_code` kosong di controller.** Rancangan awal memuatnya, lalu
dicabut setelah membaca `App\Http\Middleware\CheckBagianRole`: middleware itu **sudah**
menolak akun Bagian tanpa `bagian_code` dengan 403 (`"Bagian code not configured for
this user."`) sebelum request sampai ke controller. Menambahkan cek kedua hanya
melahirkan cabang yang tak pernah tereksekusi — dan cabang mati tak bisa diuji, jadi
tak bisa dipercaya.

`BagianDokumenController::index()` memang punya `abort(403)` serupa; itu peninggalan
yang juga tak terjangkau, di luar cakupan pekerjaan ini.

`namaBagian()` diubah dari `private static` menjadi `public static`. Ia sudah membaca
tabel `bagians` (bukan peta kode→nama yang di-hardcode), jadi memakainya kembali
mencegah lahirnya peta ketiga.

> Catatan: `BagianDokumenController::getBagianName()` memang masih memakai peta
> hardcode. Itu utang lama, **di luar cakupan** pekerjaan ini — jangan sekalian
> dirapikan di sini.

**Respons:**

| Keadaan | HTTP | Isi |
|---|---|---|
| Validasi gagal | 422 | pesan galat Laravel biasa |
| Fonnte sukses | 200 | `{ ok: true, pesan: "Pesan terkirim ke 6281…. Silakan cek WhatsApp." }` |
| Fonnte gagal | 200 | `{ ok: false, pesan: <alasan sebenarnya> }` |

Kegagalan gateway sengaja tetap **200**, bukan 5xx: ini laporan hasil, bukan error
server, dan menjaga sisi JS tetap satu jalur.

**Penerjemahan alasan** — `FonnteWhatsAppService::sendMessage()` mengembalikan
`reason` yang harus disampaikan apa adanya, bukan diseragamkan jadi "gagal":

| `reason` | Pesan yang ditampilkan |
|---|---|
| `disabled` | Notifikasi WhatsApp sedang dimatikan di server (`WHATSAPP_NOTIFICATIONS_ENABLED=false`). |
| `no_token` | Token Fonnte belum diisi di server (`FONNTE_API_TOKEN`). |
| `api_error` | Fonnte menolak: `<message dari API>` |
| `exception` | Gagal menghubungi Fonnte: `<message>` |

Ini bagian terpenting rancangan. Kalau tombolnya selalu melaporkan "terkirim" padahal
gagal, seluruh gunanya hilang — yang justru ingin dibuktikan adalah apakah WhatsApp
benar-benar sampai dari server.

### 4.3 Template pesan dipakai bersama, tidak disalin

`DocumentReturnNotifier::susunPesan()` sekarang menerima objek `Dokumen`. Ia diubah
menerima nilai biasa:

```php
private static function susunPesan(
    string $agenda,
    string $namaBagian,
    string $alasan,
    string $tautan
): string
```

`kirim()` (jalur produksi) menyusun argumen itu dari `$dokumen` — perilakunya tidak
berubah sama sekali.

Ditambah satu method publik:

```php
public static function pesanUjiCoba(string $namaBagian, string $tautan): string
{
    return self::PENANDA_UJI . self::susunPesan(
        '9999_2026',
        $namaBagian,
        'Lampiran faktur belum lengkap. (contoh)',
        $tautan
    );
}
```

dengan

```php
private const PENANDA_UJI = "🧪 *[UJI COBA — BUKAN PENGEMBALIAN SUNGGUHAN]*\n\n";
```

Alasan memilih ini daripada menyalin template ke controller baru: CLAUDE.md aturan 1
melarang salinan, dan lebih penting lagi — **pesan uji yang menyimpang dari pesan
sungguhan akan menipu responden tanpa ada yang menyadarinya.** Satu template berarti
perubahan format apa pun otomatis ikut ke demo.

### 4.4 Tautan di akhir pesan

**Menyimpang dari pratinjau yang disetujui**, dan ini disengaja.

Pratinjau memuat `…/inbox/0`. Dokumen ber-id `0` tidak ada — responden yang menekan
tautannya akan mendapat halaman error, dan kesan pertama yang tersisa justru
"sistemnya rusak".

Tautan pesan uji memakai `route('bagian.documents.index')` — halaman yang bisa dibuka,
dan kebetulan memang halaman yang sedang diuji. Jalur produksi tetap memakai
`route('inbox.show', $dokumen->id)` seperti sebelumnya.

### 4.5 UI — satu tombol di toolbar + modal

**Rancangan awal memakai panel permanen di bawah kartu info. Dibatalkan user:**
*"jelek, dan memakan space"*. Alasannya sahih — panel itu menempati ruang di setiap
kunjungan padahal hanya ditekan sekali per responden, dan halaman Bagian adalah
halaman pemantauan, bukan halaman uji coba.

**Tombol** `Uji Kirim Pesan` diletakkan di toolbar filter, **tepat setelah tombol
Refresh** yang sudah ada. Wajib `type="button"` — toolbar itu berada di dalam
`<form method="GET">`, dan tombol tanpa `type` akan men-submit form lalu memuat ulang
halaman sebelum modalnya sempat terbuka.

Nama tombol memakai **"Uji Kirim Pesan"** (bukan "Test/Uji Kirim Pesan") mengikuti
aturan project: UI berbahasa Indonesia.

**Modal** `#ujiWhatsAppModal` berisi, berurutan:

1. Keterangan bahwa uji ini akan **mengirim pemberitahuan "dokumen dikembalikan"** ke
   nomor yang dimasukkan, dan bahwa **tidak ada dokumen yang benar-benar dikembalikan**
   — pesannya bertanda `[UJI COBA]`.
2. Input nomor WhatsApp.
3. Satu baris hasil (kosong sampai tombol ditekan).
4. Tombol Batal & Kirim.

**Modal Bootstrap, digerakkan eksplisit.** Berkas ini sudah punya dua modal yang
bekerja dengan pola itu (`#perjalananModal`, `#rejectionDetailModal`) — markup statis di
Blade, dibuka lewat `new bootstrap.Modal(el).show()`. Ikuti pola yang sama, jangan
mengarang mekanisme ketiga. Yang bermasalah di layout ini adalah dropdown/modal yang
**disuntik JS** lalu mengandalkan data-api (memori `bootstrap-dropdown-pure-css`);
markup statis + instance eksplisit terbukti jalan di halaman yang sama.

**Penempatan markup modal:** di dalam partial yang sama dengan tombolnya, ditaruh
bersama modal-modal lain di dekat akhir `@section('content')`. Satu berkas partial
memuat tombol + modal + CSS + JS sekaligus, supaya pencabutan tetap satu penghapusan.

**CSS:** kelas ber-scope `.uwa-*`, **nol `!important`**, lewat `@push('styles')`
(`@stack('styles')` ada di `layouts/app.blade.php:3071`, sebelum `</head>` di 3122 —
sudah diverifikasi, bukan asumsi). Tombolnya meminjam bentuk `.btn-refresh` yang sudah
ada (tinggi 44px, radius 8px, inline-flex) agar sebaris rapi dengannya, dengan warna
berbeda supaya tetap terbaca sebagai tombol uji, bukan aksi biasa.

**JS:** IIFE di dalam partial. `fetch` POST dengan header CSRF, tombol Kirim
dinonaktifkan selagi mengirim (mencegah klik ganda yang memakan dua kuota), hasil
ditulis dengan `textContent` — **bukan `innerHTML`** — karena pesan galat Fonnte adalah
teks dari pihak luar.

## 5. Alur data

1. User Bagian membuka `/bagian/documents`, melihat panel uji.
2. Mengetik nomor, menekan Kirim.
3. `fetch` POST → route ber-throttle → controller.
4. Controller memvalidasi, menyusun pesan dari template bersama, memanggil Fonnte.
5. Hasil asli Fonnte diterjemahkan ke pesan Indonesia, dikembalikan sebagai JSON.
6. Panel menampilkan hasilnya — sukses maupun alasan gagalnya.

## 6. Penanganan error

- Validasi gagal → 422, pesan galat tampil di panel.
- Gateway gagal → 200 `ok:false` dengan alasan sebenarnya.
- Exception tak terduga di controller → biarkan naik ke handler Laravel. Berbeda dari
  `DocumentReturnNotifier` yang membungkus semuanya dengan try/catch (di sana kegagalan
  notifikasi tidak boleh menggagalkan pengembalian dokumen) — **di sini tidak ada
  transaksi bisnis yang perlu diselamatkan**, dan menelan error justru menyembunyikan
  hal yang sedang kita uji.
- Throttle terlampaui → 429 dari Laravel; panel menampilkan "Terlalu sering. Tunggu
  sebentar."

## 7. Pengujian

Berkas baru `tests/Feature/UjiWhatsAppBagianTest.php`, `FonnteWhatsAppService`
di-mock lewat container (controller me-resolve-nya via `app()`, jadi mock container
berlaku).

| # | Test | Yang dijaga |
|---|---|---|
| 1 | Role non-Bagian ditolak | Middleware `bagian` terpasang di route |
| 2 | Nomor kosong / tak sah → 422, service **tidak** dipanggil | Validasi menggigit sebelum kuota terpakai |
| 3 | Nomor sah → `sendMessage()` dipanggil dengan nomor itu & pesan memuat `PENANDA_UJI` | Penanda uji coba tak bisa hilang diam-diam |
| 4 | Service mengembalikan `success:false, reason:'no_token'` → respons memuat kata "Token" | Kegagalan diteruskan apa adanya, bukan diseragamkan jadi "gagal" |
| 5 | `pesanUjiCoba()` === `PENANDA_UJI` + hasil `susunPesan()` dengan argumen contoh yang sama (dipanggil lewat Reflection) | Template benar-benar dipakai bersama; menyalinnya ke tempat lain memerahkan test ini |

Selain itu: **`tests/Feature/NotifikasiPengembalianBagianTest.php` yang sudah ada wajib
tetap hijau** setelah refaktor tanda tangan `susunPesan()`. Itu jaring pengaman bahwa
refaktornya benar-benar behavior-preserving.

Tiap assertion baru dibuktikan menggigit sesuai CLAUDE.md aturan 8: rusakkan kode yang
dijaga → test GAGAL → pulihkan → LULUS → `git diff` kosong.

## 8. QA browser

Suite hijau tidak cukup (CLAUDE.md aturan 9). Yang wajib diperiksa langsung di produksi:

1. Tombol `Uji Kirim Pesan` tampil sebaris dengan Refresh, tingginya sama, tidak
   membungkus ke baris baru pada lebar layar biasa.
2. Menekannya **membuka modal** — bukan men-submit form filter (bukti `type="button"`
   terpasang).
3. Nomor kosong → pesan galat tampil di dalam modal, tidak ada kiriman.
4. Nomor sah → **satu kiriman sungguhan.**

Langkah 3 **membutuhkan nomor WhatsApp asli dan memotong kuota Fonnte.** Karena itu
langkah ini tidak dijalankan diam-diam: user diminta menyebutkan nomor yang boleh
dipakai, atau menekannya sendiri. Sampai itu terjadi, laporkan terus terang bahwa
kiriman ujung-ke-ujung **belum terbukti** — jangan menyimpulkan dari test yang memakai
mock.

## 9. Daftar pencabutan (setelah sesi uji coba selesai)

Disalin juga ke docblock controller.

1. Hapus `app/Http/Controllers/UjiWhatsAppBagianController.php`
2. Hapus `resources/views/bagian/partials/_ujiWhatsApp.blade.php`
3. Di `daftarDokumen.blade.php` hapus **dua** sisipan: tombol `id="btnUjiWhatsApp"` di
   toolbar filter, dan baris `@include('bagian.partials._ujiWhatsApp')`
4. Hapus baris route `bagian.uji-whatsapp` di `routes/web.php`
5. Hapus `tests/Feature/UjiWhatsAppBagianTest.php`
6. Di `DocumentReturnNotifier`: hapus `pesanUjiCoba()` + konstanta `PENANDA_UJI`,
   kembalikan `namaBagian()` ke `private` **bila tak ada pemakai lain**

Langkah 6 **opsional** — tanda tangan `susunPesan()` yang menerima nilai biasa adalah
perbaikan yang berdiri sendiri (lebih mudah diuji, tak terikat objek `Dokumen`) dan
boleh dipertahankan.

## 10. Yang sengaja TIDAK dibuat

- **Saklar `.env`** — keputusan user (§3 no. 3).
- **Riwayat kiriman uji** — tidak ada yang akan membacanya; log Laravel sudah mencatat
  tiap panggilan Fonnte.
- **Kirim ke banyak nomor** — satu nomor per klik sudah cukup untuk demo, dan bisa
  dipakai bergantian oleh beberapa responden.
- **Mengisi `phone_number` akun Bagian** — pekerjaan terpisah, dan justru **hasil C4
  kuesioner yang seharusnya memutuskan** apakah itu layak dikerjakan.

# Desain — Agenda Online ramah ponsel, Tahap 1: role Bagian

**Tanggal:** 2026-08-13
**Cakupan tahap ini:** role `bagian_*` (SKH, SDM, AKN, TAN, DPM, TEP, PTI, PMO, dst) —
halaman `/bagian/documents`.
**Di luar cakupan tahap ini:** operator, verifikasi, perpajakan, akutansi, pembayaran
(kelimanya memakai engine Tabulator — masalah dan solusinya berbeda, lihat §8).

---

## 1. Masalah

User melaporkan aplikasi tidak ramah ponsel. Pengukuran langsung di produksi
(`http://163.61.58.92/bagian/documents`, akun `skh`, viewport 375×844 — iPhone standar)
memberi angka konkret:

| Gejala | Angka terukur |
|---|---|
| Sidebar mencuri lebar permanen | **72px**, tidak bisa ditutup sama sekali |
| Jendela tabel yang tersisa | **208px** untuk konten selebar **1943px** (11 kolom) |
| Halaman melar horizontal | `scrollWidth` 390 > `clientWidth` 375 |
| Input & 3 dropdown filter | masing-masing **250px**, meluber ke luar layar |
| Kartu informasi (`_infoCards`) | **sudah benar** — menumpuk vertikal, tidak diubah |

Artinya user harus menggulir ±9 layar ke samping hanya untuk membaca satu baris dokumen.

### Kenapa `public/css/responsive.css` yang sudah ada tidak menolong

Berkas itu ada (356 baris) dan sudah dimuat global di `layouts/app.blade.php:26`, tetapi
**nol selektornya menyasar kelas yang benar-benar dipakai halaman ini**. Ia menulis aturan
untuk `.sidebar`, `.card`, `.stat-card`, `.filter-container`, `.table-mobile-cards` —
sementara halaman Bagian memakai `.sidebar-owner`, `.content`, `.data-table`,
`.search-filter-form`, `.wd-card`. Berkas itu praktis tidak berefek apa pun; ia bukan
penyebab masalah, hanya tidak pernah tersambung.

---

## 2. Keputusan desain

Tiga keputusan diambil user saat brainstorming:

1. **Daftar dokumen di ponsel = kartu per dokumen**, bukan tabel ringkas dan bukan tabel
   bergulir dengan kolom beku. Target: **nol gulir horizontal**.
2. **Sidebar di ponsel = drawer geser + overlay**, dibuka tombol hamburger yang sudah ada.
3. **Nol perubahan tampilan desktop.** Semua gaya baru dikurung `@media (max-width: 768px)`.
   Kalau desktop berubah, itu bug — bukan trade-off yang diterima.

---

## 3. Kenapa berkas CSS baru, bukan menambal `responsive.css`

`responsive.css` dimuat untuk **semua** role. Menambahkan selektor nyata ke sana membuat
setiap perubahan langsung mengenai 6 role sekaligus — persis yang dilarang CLAUDE.md §6
(pekerjaan yang menyentuh partial/aset global adalah gerbang kritis).

Karena itu: berkas baru **`public/css/mobile.css`**, juga dimuat global, tetapi isinya
disusun **bertahap per-role**:

- Blok A — shell (topbar, drawer, `.content`): berlaku semua role, ditulis sekali.
- Blok B — aturan berlabel `.bagian-layout`: hanya menggigit halaman Bagian.

Role berikutnya menambah **blok baru** berlabel kelas layout-nya masing-masing
(`.operator-layout`, `.payment-layout`, `.workflow-layout`) — bukan mengubah blok yang
sudah lulus QA. Kelas-kelas itu sudah ada di `<body>` (`layouts/app.blade.php:3141`), jadi
tidak ada infrastruktur baru yang perlu dibangun.

`responsive.css` **tidak dihapus dan tidak diubah** di tahap ini. Ia tidak berbahaya, dan
menghapusnya menuntut grep-gate lintas-role tersendiri (CLAUDE.md §3.2) yang bukan bagian
dari pekerjaan ini. Dicatat sebagai utang di §9.

---

## 4. Kenapa kartu di-render server-side sebagai markup kedua

Alternatif yang ditolak: memaksa `<table>` menjadi kartu lewat `display:block` +
`td::before { content: attr(data-label) }` — pola yang sudah tersedia di `responsive.css`
sebagai `.table-mobile-cards`.

Ditolak karena tabel Bagian bukan tabel polos:
- 11 kolom, sebagian tak relevan di ponsel; pola `data-label` menyeret **semuanya** masuk kartu.
- Sel `col-pengurus` memuat `<button data-perjalanan="{json}">` yang menjadi sumber data
  modal perjalanan — bukan sekadar teks.
- Sel `col-pengembalian` memuat badge dengan `onclick` ke modal alasan.
- Partial `_activeCellNav` memasang navigasi keyboard pada `.data-table tbody`; mengubah
  `display` baris berisiko mengacaukan cache barisnya (`rowsCache`, baris 149).

Markup kartu terpisah yang hanya tampil di ponsel membuat kedua tampilan **independen**:
tabel desktop tidak tersentuh satu byte pun, dan kartu bebas memilih field yang relevan.

**Konsekuensi yang diterima secara sadar:** kedua markup ada di DOM sekaligus, sehingga
HTML per halaman bertambah ±15–20% pada 25 baris. Alternatifnya deteksi User-Agent di
server — ditolak karena rapuh dan merusak cache HTTP. Bila kelak terasa berat, kartu dapat
dipindah ke endpoint terpisah tanpa mengubah kontrak apa pun.

---

## 5. Komponen

### 5.1 Shell mobile — `public/css/mobile.css` + cabang JS di layout

**Drawer.** Pada `≤768px`:
- `.sidebar-owner` → `transform: translateX(-100%)`, `transition`, `z-index` di atas konten.
- Muncul saat `<html>` memperoleh kelas **`.mobile-drawer-open`**.
- Overlay **`.mobile-drawer-scrim`** (elemen baru di layout, `display:none` di desktop)
  menutup drawer saat di-tap.
- `.content` → `margin-left: 0; padding: 12px;`
- `.app-topbar` tetap `fixed` (sudah benar), konten diberi `padding-top` yang sesuai.

**Tombol hamburger — TIDAK diganti.** Tombol `[data-sidebar-toggle]` sudah ada
(`layouts/app.blade.php:3306`) dengan handler yang mem-*toggle* `sidebar-collapsed` dan
menyimpannya ke `localStorage` (baris 3838–3851). Handler itu **dipertahankan apa adanya**;
ditambahkan **cabang lebar layar** di dalamnya:

- `window.matchMedia('(max-width: 768px)').matches` → toggle `.mobile-drawer-open`,
  **tanpa** menulis `localStorage` (drawer selalu mulai tertutup tiap kunjungan).
- selain itu → perilaku lama persis, termasuk penulisan `localStorage`.

Ini menjaga dua hal sekaligus: janji "nol perubahan desktop", dan preferensi collapse yang
sudah tersimpan di browser user produksi (mengubah kunci `sidebar_collapsed` akan
menghilangkan preferensi mereka secara senyap).

**Jebakan yang dijaga:** `.mobile-drawer-open` dipasang di `<html>` — konsisten dengan
`sidebar-collapsed` yang juga di `documentElement`. Memasangnya di `<body>` akan bekerja
tetapi memecah pola yang sudah ada.

### 5.2 Kartu dokumen — `resources/views/bagian/partials/_kartuDokumenMobile.blade.php`

Partial **khusus Bagian**, diletakkan di `resources/views/bagian/partials/` (bukan
`resources/views/partials/` yang global) — isi kartu Bagian belum tentu cocok untuk role
lain, dan menaruhnya di folder global mengundang penggunaan lintas-role yang belum
dirancang. Bila kelak terbukti dipakai bersama, pemindahannya adalah keputusan tersendiri.

Isi tiap kartu:

| Baris | Field |
|---|---|
| Judul | `nomor_agenda` (tebal) + `nomor_spp` |
| Isi | `dibayar_kepada` |
| Isi | `nilai_rupiah` (format Rp) |
| Badge | Status Pembayaran (3-state, lihat 5.3) |
| Kaki | `tanggal_masuk` + Waktu Pengerjaan (`umur_dokumen`) |
| Kondisional | Badge "Dikembalikan, Alasan" bila `status === 'returned_to_bidang'` |

`uraian_spp` **sengaja tidak ditampilkan di kartu** — teksnya panjang dan akan mendominasi
tampilan. Ia tetap dapat dibaca lewat modal.

**Interaksi — nol fungsi JS baru:**
- Tap kartu → `tampilkanPerjalanan(el)`. Kontrak terverifikasi: fungsi membaca atribut
  **`data-perjalanan`** dari elemen yang dioper (baris 2860–2908). Kartu menyalin atribut
  yang sama dari data `$perjalanan[$doc->id]`.
- Tap badge pengembalian → `showRejectionModal(id)`. Kontrak terverifikasi: menerima **ID
  dokumen**, lalu `fetch('/api/bagian/documents/{id}/return-detail')` (baris 2910+).
- Keduanya sudah `window.*` global di halaman itu, dan kedua modal (`#perjalananModal`,
  `#rejectionDetailModal`) sudah ada di DOM. Kartu cukup memanggilnya.

Badge pengembalian di dalam kartu memakai `event.stopPropagation()` agar tidak ikut memicu
modal perjalanan milik kartu induknya — pola yang sama dipakai sel tabel saat ini.

### 5.3 Status pembayaran 3-state — diekstrak, bukan disalin

Logika status kini berupa blok `@php` inline di dalam `<td>` (baris 1470–1492) dengan tiga
cabang: `Sudah Dibayar` / `Siap Dibayar` (bila `current_handler` mengandung "pembayaran") /
`Belum Siap Dibayar`, masing-masing dengan kelas CSS, ikon, dan tanggal acuan berbeda.

Kartu **wajib menampilkan status yang identik** dengan tabel. Menyalin blok itu ke partial
kartu berarti melahirkan salinan kedua dari aturan bisnis — persis penyakit utama yang
dilarang CLAUDE.md §3.1.

Karena itu logika dipindah ke satu helper: **`App\Support\StatusPembayaranBagian::untuk($doc)`**,
mengembalikan array `['kelas', 'teks', 'ikon', 'tanggal']`. Kelas biasa di `App\Support`
(bukan trait, bukan accessor model) agar bisa di-unit-test tanpa kelas inang — mengikuti
preseden `App\Support\ColumnCustomization` yang dipilih dengan alasan sama.

Tabel dan kartu sama-sama memanggil helper itu. Ini satu-satunya perubahan yang menyentuh
markup tabel desktop, dan sifatnya **murni pemindahan** — nilai yang dirender wajib identik
byte-per-byte, dijaga test.

### 5.4 Filter, toolbar, paginasi

Pada `≤768px`:
- `.search-filter-form` → satu kolom, tiap kontrol `width: 100%`.
- Semua `input`/`select` → **`font-size: 16px`**. Ini mencegah iOS melakukan auto-zoom saat
  field difokus — penyebab umum keluhan "layarnya meloncat sendiri dan tidak bisa balik".
- Tombol Refresh & Uji Kirim Pesan → berdampingan dalam satu baris, tinggi minimum **44px**
  (ambang target sentuh Apple HIG).
- `.pagination-container` → menumpuk vertikal, tombol halaman tinggi 44px.

Tombol "Uji Kirim Pesan" ikut dirapikan meski berstatus SEMENTARA (CLAUDE.md §7) — ia
tampil di toolbar yang sama, dan membiarkannya rusak akan terlihat seperti cacat. Saat
tombol itu dicabut nanti, aturan CSS-nya ikut tercabut; dicatat di §9.

---

## 6. Berkas yang berubah

| Berkas | Sifat |
|---|---|
| `public/css/mobile.css` | **BARU** — seluruh isinya di dalam `@media (max-width: 768px)` |
| `resources/views/bagian/partials/_kartuDokumenMobile.blade.php` | **BARU** |
| `app/Support/StatusPembayaranBagian.php` | **BARU** — ekstraksi, bukan aturan baru |
| `resources/views/layouts/app.blade.php` | `<link>` mobile.css; elemen scrim; cabang lebar layar di handler hamburger. **Aditif** |
| `resources/views/bagian/dokumens/daftarDokumen.blade.php` | `@include` partial kartu; `<td>` status memanggil helper |

God-file `layouts/app.blade.php` (5.905 baris) bertambah hanya beberapa baris; seluruh CSS
baru berada di berkas terpisah — sesuai CLAUDE.md §2 ("kalau harus menambah CSS/JS di sini,
pertimbangkan file terpisah di `public/css`").

---

## 7. Pengujian

### Suite otomatis

Tiap assertion wajib dibuktikan menggigit (CLAUDE.md §3.8): rusakkan kode yang dijaga →
test GAGAL → pulihkan → LULUS → `git diff` kosong.

1. **`StatusPembayaranBagianTest`** (unit) — ketiga state benar: sudah dibayar; sedang di
   pembayaran; belum. Termasuk kasus `tanggal_dibayar` terisi tetapi `status_pembayaran`
   belum — cabang `$isPaid` memakai OR, mudah pecah saat diekstrak.
2. **Kartu ter-render** — jumlah kartu = jumlah baris tabel, dan nomor agenda yang sama
   muncul di keduanya. Ini yang menangkap "kartu lupa di-render saat paginasi".
3. **Kartu tersembunyi di desktop** — pembungkus kartu punya aturan `display:none` di luar
   media query mobile, dan **CSS ter-link sebelum markup** (assertion urutan). Pelajaran
   flash-of-unstyled-modal dari program modal kustomisasi kolom (CLAUDE.md §7).
4. **`mobile.css` benar-benar ter-link** lewat `Asset::versioned()` di layout.
5. **Setiap aturan di `mobile.css` berada di dalam `@media`** — assertion inilah yang
   menjaga janji "nol perubahan desktop" secara mekanis, bukan sekadar niat baik. Test
   mem-parse berkas dan gagal bila ada deklarasi di luar blok media.

Assertion dipersempit ke badan fungsi/blok, bukan mencari string di seluruh berkas —
string yang sama biasanya sudah ada di tempat lain sehingga test menjadi hampa
(CLAUDE.md §3.8).

### QA browser — wajib, tidak boleh dilewat

CLAUDE.md §3.9 & §6: suite hijau bukan bukti tampilan benar. Playwright MCP tersedia
(memori `browser-qa-access`), jadi QA dilakukan sendiri sebelum menyatakan selesai:

- **375px** (iPhone SE/standar), **390px**, **768px** (batas breakpoint — rawan, aturan
  `max-width: 768px` inklusif), **1440px** (bukti desktop tidak berubah).
- Verifikasi mekanis: `document.documentElement.scrollWidth === clientWidth` → nol gulir
  horizontal.
- Drawer: buka, tutup lewat scrim, tutup lewat hamburger.
- Kedua modal (perjalanan, alasan pengembalian) tetap berfungsi **dari kartu**.
- Screenshot sebelum/sesudah diserahkan ke user. **Keputusan lolos milik user**, bukan agent.

---

## 8. Tahap berikutnya (bukan bagian dari pekerjaan ini)

Lima role keuangan memakai engine Tabulator bersama (`public/js/document-tabulator.js`,
2.639 baris). Membuatnya ramah ponsel adalah masalah berbeda: Tabulator merender tabelnya
sendiri, sehingga pola "markup kartu kedua" di sini tidak berlaku langsung — kemungkinan
besar dibutuhkan `responsiveLayout` bawaan Tabulator atau formatter baris khusus.

Tahap ini sengaja tidak mendahului keputusan itu. Yang diwariskan ke tahap berikutnya:
Blok A `mobile.css` (shell, drawer, topbar) yang sudah berlaku semua role, dan bukti bahwa
pendekatan per-blok-berlabel-kelas-layout dapat dikerjakan tanpa menyentuh role lain.

---

## 9. Utang tercatat

- `public/css/responsive.css` kini benar-benar mati (nol selektor menggigit). Penghapusannya
  menuntut grep-gate lintas-role tersendiri — dikerjakan terpisah, bukan di sini.
- Aturan CSS untuk tombol "Uji Kirim Pesan" ikut tercabut saat fitur SEMENTARA itu dicabut
  (daftar pencabutan ada di docblock `App\Http\Controllers\UjiWhatsAppBagianController`).
- Kartu dan tabel dikirim bersamaan dalam satu HTML. Bila ukuran halaman menjadi masalah,
  pindahkan kartu ke endpoint terpisah.

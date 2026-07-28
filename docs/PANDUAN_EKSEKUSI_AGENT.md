# Panduan Eksekusi Agent — pelajaran dari program yang berjalan 11,5 jam

**Ditulis:** 2026-07-28, setelah program "satukan modal Kustomisasi Kolom 5 role"
(27 commit, 3 deploy, ~16 putaran perbaikan).

Program itu **berhasil** — dua cacat yang tak akan tertangkap test mana pun tertangkap
sebelum sampai ke user. Tapi sekitar **4 jam terbuang** karena hal yang bisa
dikendalikan. Berkas ini merekam yang mana, supaya tidak terulang.

> **Cara memakai berkas ini.** Tiap aturan menyertakan **insiden nyata** yang
> melahirkannya, supaya Anda bisa menilai sendiri apakah masih relevan. Aturan yang
> bergantung pada keadaan kode ditulis sebagai **langkah verifikasi**, bukan pernyataan
> fakta — karena fakta bisa basi, langkah verifikasi tidak.
>
> **Kalau satu aturan di sini bertabrakan dengan kode yang Anda lihat: kode yang benar.
> Perbaiki berkas ini, jangan ikuti buta.**

---

## 1. Menjalankan test — sumber pemborosan terbesar

**Insiden:** suite dijalankan sekitar 30 kali dalam satu sesi. Saat itu sekali jalan
memakan ~5 menit → **2–2,5 jam murni menunggu**.

**Lakukan:**

1. **Saat iterasi**, jalankan test terfilter saja:
   `php artisan test --filter=NamaKelasTest`.
   Jalankan **suite penuh sekali**, tepat sebelum commit. Bukan tiap langkah.
   Ini sendiri sudah memangkas mayoritas pemborosan, tanpa memasang apa pun.

2. Kalau suite terasa lambat, ukur dulu sebelum menebak. Mayoritas berkas test di sini
   memakai `RefreshDatabase` — biasanya itu biang lambatnya.

**Jangan:** menjalankan suite penuh setelah setiap perubahan kecil.

### Catatan soal `--parallel` — sekaligus contoh cara aturan bisa salah

Versi pertama berkas ini menyarankan `php artisan test --parallel` dan menyuruh
memverifikasinya dengan `grep -c 'brianium/paratest' composer.lock`. **Keduanya salah**,
dan ketahuan justru saat mencoba membuktikannya sendiri:

- `composer.lock` memuat string `brianium/paratest` karena paket lain
  (`laravel/framework`) mencantumkannya di bagian **`suggest`**. Grep menemukannya
  padahal paketnya **tidak terpasang** — jadi pengecekannya sendiri menyesatkan.
- Perintah `--parallel` akan gagal.

**Cek yang benar:** `ls vendor/bin/paratest` — kalau tak ada, tak terpasang. Memasangnya
berarti mengubah `composer.json` **dan** menjalankan `composer install` di server, jadi
itu keputusan user, bukan agent.

Pelajarannya lebih luas dari soal test: **pengecekan berbasis grep ke berkas manifes
sering menemukan deklarasi, bukan kenyataan.** Verifikasi ke artefak yang benar-benar
ada di disk (`vendor/bin/…`, berkas terpasang), bukan ke penyebutan namanya.

---

## 2. Test yang tidak menguji apa pun

**Insiden:** dua kali dalam satu program, test yang ditulis di rencana ternyata hampa.
Sekali: empat dari sembilan assertion memeriksa string yang **sudah ada di berkas sejak
tugas sebelumnya**, jadi menghapus kode yang dijaganya tetap membuat suite hijau.
Sekali lagi: test yang docblock-nya berjanji menjaga "kontrak paling rawan" ternyata
tak menyentuh kontrak itu sama sekali. Reviewer yang menemukan keduanya, bukan penulisnya.

**Aturan — berlaku untuk setiap assertion baru, tanpa kecuali:**

1. **Uji mutasi wajib.** Rusakkan kode yang dijaga → jalankan test → pastikan **GAGAL**
   → pulihkan → pastikan **LULUS**. Kalau tidak gagal, assertion itu hiasan.
2. **Pulihkan segera, lalu buktikan.** Setelah tiap mutasi, jalankan
   `git diff <berkas>` dan pastikan **kosong** sebelum melangkah. Pernah terjadi mutasi
   tertinggal di working tree dan nyaris ikut ter-commit — yang di-deploy justru
   regresi yang test-nya dibuat untuk menangkap.
3. **Jangan pernah menjalankan suite penuh saat ada mutasi aktif.**

**Pola yang paling sering menipu:** assertion yang mencari string di **seluruh berkas**.
String itu sering sudah ada di tempat lain. Persempit ke badan fungsi yang relevan —
potong dengan `strpos` dari nama fungsi sampai fungsi berikutnya, lalu assert di dalam
potongan itu. Uji juga pemanggilannya (`searchParams.set('x'`), bukan sekadar `'x'`).

---

## 3. Sebelum mengklaim sesuatu tidak terpakai

**Insiden:** `#filterForm` disimpulkan "hanya dipakai modal ini" setelah grep ke
`public/js/` saja. Grep penuh menemukan pemakai kedua: partial **global**
`document-workbench-ui.blade.php`. Kalau rencana itu dijalankan, fitur di halaman lain
ikut mati.

**Lakukan:** grep ke **semua** ruang sekaligus, jangan satu folder:

```bash
grep -rn "<simbol>" resources public app tests config routes database
```

Baru setelah itu tulis kesimpulan. Ini juga syarat gerbang §6 CLAUDE.md
(hapus view/route/partial butuh bukti grep + persetujuan user).

---

## 4. Rencana yang menyatakan sebuah berkas "tidak perlu diubah"

**Insiden:** rencana menyatakan `public/js/document-tabulator.js` "nol perubahan".
Ternyata harus diubah **dua kali**, dan keduanya menyangkut cacat yang membuat fitur
baru tak berfungsi. Klaim itu ditulis dari membaca kode, tanpa pernah menjalankan
fiturnya end-to-end.

**Aturan:** jangan menulis "nol perubahan" untuk berkas yang **mengeksekusi** fitur
yang sedang dibangun. Yang boleh diklaim: "belum terlihat perlu diubah — diverifikasi
setelah fitur jalan di browser". Perbedaannya bukan gaya bahasa: klaim pertama membuat
reviewer berikutnya berhenti memeriksa.

---

## 5. State sisi klien bisa membatalkan seluruh kerja server

**Insiden — pelajaran termahal sesi itu.** Tabulator menyimpan definisi kolom di
`localStorage`. Modul persistence-nya memanggil `mergeDefinition(current, persisted)`
yang **mengiterasi array tersimpan**, sehingga **urutan dari localStorage menang atas
urutan dari server**. Komentar di kode saat itu mengklaim "simpan HANYA lebar, bukan
urutan" — klaim itu salah. Akibatnya seluruh fitur urutan/kolom beku tidak akan pernah
terlihat oleh user yang sudah pernah membuka halaman itu.

Nol test menangkapnya. Suite hijau sempurna. Yang menemukan: membaca sumber
`tabulator.min.js` lalu **membuktikannya di browser sungguhan** — menukar dua kolom
hanya di `localStorage`, reload, dan melihat tabel ikut tertukar padahal konfigurasi
server tak bergeser.

**Aturan:** untuk fitur yang keluarannya dirender pustaka pihak ketiga, test backend
**tidak cukup**. Sebelum menyatakan selesai, verifikasi di browser bahwa yang dikirim
server benar-benar yang tampil. Kalau pustakanya punya `persistence`, `localStorage`,
atau cache sejenis, curigai itu lebih dulu.

**Teknik yang terbukti berguna:** ubah **hanya** `localStorage` (nol data produksi
disentuh), reload, dan bandingkan tampilan dengan konfigurasi dari server. Kalau
tampilan mengikuti `localStorage`, Anda menemukannya.

---

## 6. Menjalankan subagent

**Insiden:** subagent berhenti **lima kali** karena menjalankan test di latar lalu
menganggur menunggu notifikasi. Sekali di antaranya berhenti dengan mutasi uji masih
aktif di working tree.

**Tulis di tiap prompt subagent:**

- Jalankan test di **foreground** dengan timeout memadai. **Jangan** pakai mode latar
  untuk test — itu penyebab berhenti berulang.
- Tiap mutasi dipulihkan di langkah berikutnya, diverifikasi `git diff` kosong.
- Kembalikan status ringkas (DONE/BLOCKED, daftar commit, satu baris hasil test);
  laporan panjang ditulis ke berkas, bukan ke balasan.

**Untuk yang men-dispatch:** kalau balasan subagent tidak memuat status yang diminta,
**jangan anggap selesai** — periksa `git status` dan `git log` sendiri. Dua kali
subagent "selesai" padahal belum commit apa pun.

---

## 7. Menulis rencana yang tidak melahirkan putaran perbaikan

Sekitar lima putaran perbaikan lahir dari kesalahan di rencana, bukan kesalahan
implementer. Sebelum menyerahkan rencana:

- **Jalankan sendiri uji mutasi mental pada setiap test yang Anda tulis di rencana.**
  Kalau kode yang dijaga dihapus, apakah assertion ini pasti gagal? Kalau ragu, ia hampa.
- **Nomor baris pasti bergeser.** Tulis penanda kode (nama fungsi, baris unik) sebagai
  patokan utama; nomor baris hanya pelengkap, dan sebut bahwa itu perkiraan.
- **Verifikasi ulang nomor baris tepat sebelum dispatch** kalau tugas sebelumnya
  menyentuh berkas yang sama.
- Kode yang Anda taruh di rencana akan disalin verbatim. Kalau ada bug di situ, ia
  masuk ke produksi lewat tangan orang lain.

---

## 8. Kedalaman review — yang justru **tidak** boleh dikurangi

Dari semua yang memakan waktu, review mendalam adalah satu-satunya yang terbayar.
Ia menangkap: kolom beku mengambang menutupi kolom lain di 4 role, `localStorage` yang
membatalkan perbaikan itu, rantai flex yang putus dan mengubah tata letak modal yang
sudah live, dan dua kelompok assertion hampa.

Semuanya lolos dari suite yang hijau.

**Jangan** memangkas review untuk mengejar waktu. Pangkas yang lain: cara menjalankan
test, jumlah putaran akibat rencana yang cacat, dan subagent yang berhenti sendiri.

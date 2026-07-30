# Persiapan Sidang — 6 Hari

Ditulis 2026-07-30. Asumsi: sidang ± 5 Agustus 2026. Kalau tanggalnya beda, geser saja
labelnya — urutannya yang penting, bukan tanggalnya.

## Kenyataan yang harus diterima dulu

6 hari **tidak cukup** untuk menguasai 144.000 baris. Jangan coba. Yang cukup, dan yang
sebenarnya diuji penguji, adalah ini:

> Anda bisa menelusuri **satu permintaan dari browser sampai balik lagi ke browser**,
> menunjuk file dan barisnya tanpa mencari-cari, menjelaskan **kenapa** dirancang begitu,
> dan **mengubah** sesuatu di depan mereka tanpa panik.

Penguji hampir tidak pernah minta Anda menulis fitur baru dari nol. Yang mereka minta
biasanya tiga hal: *"tunjukkan di mana kode yang menangani X"*, *"coba ubah Y"*, dan
*"kenapa Anda buat seperti ini?"*. Ketiganya bisa dilatih.

Karena itu materi ini memilih **satu tulang punggung** — halaman Daftar Dokumen Akutansi —
dan menggalinya sampai dasar. Empat role lain (operator, perpajakan, verifikasi,
pembayaran) memakai pola yang **sama persis**, jadi menguasai satu berarti menguasai lima.

## Aturan main latihan — jangan dilewat

1. Semua latihan dikerjakan di branch coret-coretan, **jangan pernah di-push**:
   ```bash
   git checkout -b latihan-sidang
   ```
2. Selesai satu latihan, kembalikan:
   ```bash
   git checkout -- <berkas yang diubah>
   ```
3. **Jangan sentuh server produksi** selama latihan. Aplikasi ini dipakai sungguhan.
4. Menjalankan test cukup terfilter, jangan suite penuh (buang waktu):
   ```bash
   php artisan test --filter=AkutansiDatatableTest
   ```
5. Setiap kali Anda menemukan jawaban dengan cara mencari (grep), **catat perintah
   grep-nya**. Di depan penguji, mencari dengan grep itu wajar dan justru terlihat
   menguasai — yang buruk adalah diam kebingungan.

## Jadwal

| Hari | Fokus | Berkas | Target keluar |
|---|---|---|---|
| **H-6** | Baca peta alur, telusuri 1 request end-to-end | `01-peta-alur.md` | Bisa menyebutkan 9 langkah tanpa melihat |
| **H-5** | Latihan A — navigasi "tunjukkan di mana" | `02-latihan.md` §A | 10/10 tanpa buka kunci |
| **H-4** | Latihan B — live coding 1 baris | `02-latihan.md` §B | 4 latihan, tiap perubahan terbukti di browser |
| **H-3** | Latihan C — live coding end-to-end + test | `02-latihan.md` §C | 3 latihan, test hijau |
| **H-2** | Tanya-jawab arsitektur & keamanan | `03-tanya-jawab.md` | Jawab 12 pertanyaan dengan lantang |
| **H-1** | Simulasi penuh + istirahat | semua | Sekali jalan tanpa kunci jawaban |

**H-1 jangan dipakai belajar hal baru.** Dipakai mengulang yang sudah bisa, lalu tidur.

## Cara pakai materi ini bersama saya

Setiap hari, buka sesi baru dan katakan misalnya:

> *"Uji saya Latihan A. Jangan kasih kunci jawaban sampai saya jawab."*

Saya akan bertindak sebagai penguji: melempar pertanyaan, menahan kunci, lalu menilai
jawaban Anda dan menunjukkan yang meleset. Itu jauh lebih berguna daripada Anda membaca
kunci jawaban sendiri — membaca jawaban terasa seperti mengerti, padahal belum.

## Yang paling sering bikin gagal, urut dari yang paling sering

1. **Tidak tahu di mana file-nya.** Terlihat seperti tidak menulis kodenya. Latihan A
   khusus untuk ini.
2. **Tidak bisa menjawab "kenapa".** Bisa menunjuk kode tapi tidak tahu alasan
   rancangannya. File `03-tanya-jawab.md` khusus untuk ini.
3. **Panik saat error.** Padahal error itu normal. Yang dinilai adalah cara Anda membaca
   pesan error, bukan ketiadaan error. Latihan C sengaja menyuruh Anda **merusak** kode
   dulu supaya terbiasa melihat pesan merah tanpa gugup.

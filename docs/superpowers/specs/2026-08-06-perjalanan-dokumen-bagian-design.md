# Perjalanan Dokumen untuk Role Bagian — Rancangan

**Tanggal:** 2026-08-06
**Status:** Disetujui user, siap disusun rencana implementasi

---

## 1. Latar

Saran dari responden UAT:

> "Saran dari aplikasi ini untuk kolom 'pengurus dokumen', mungkin bisa di detail kan
> lagi tahapannya, setau saya di bagian keuangan jika ada pembayaran masuk melewati
> beberapa tahap (koreksi jika saya salah), nah jika di detail kan kami akan lebih
> mudah dalam memantau nya, apakah masih di surat masuk, verifikasi, atau proses
> lainnya sebelum ke tahap pembayaran."

Kolom "Pengurus Dokumen" di tabel Bagian saat ini hanya menampilkan **satu label teks**
hasil terjemahan `current_handler` (`bagian/dokumens/daftarDokumen.blade.php:1348`),
misalnya "Tim Verifikasi". Posisi terakhir terlihat, alurnya tidak.

## 2. Temuan data yang membentuk rancangan ini

Pemeriksaan produksi 2026-08-06 (5.719 dokumen):

| Sumber | Baris | Kelayakan pakai |
|---|---|---|
| `dokumen_activity_logs` | ~28.400 | **Tidak layak jadi tulang punggung.** Hitungan per-aksi: `sent_to_inbox` 14.196 + `auto_forwarded` 10.647 + `auto_forwarded_to_pembayaran` 3.549 = **28.392 baris derau** impor CSV, semuanya bercap waktu detik yang sama. Sisanya hanya **11 baris** aksi manusia (`data_edited` 3, `created` 2, `sent` 2, `received` 2, `returned` 2), tersebar di **2 dokumen**. (Angka 28.064 dari `information_schema.table_rows` adalah taksiran InnoDB, bukan hitungan pasti.) |
| `dokumen_role_data` | 21.367 | Layak. `received_at`/`processed_at` per role. |
| `dokumen_statuses` | 17.442 | Pelengkap. |
| `document_trackings` | 0 | Mati. |

Contoh derau — seluruh "perjalanan" dokumen #1 tercatat pukul `22:38:03`, kelima tahap
sekaligus. Itu impor, bukan perjalanan.

Contoh jejak sungguhan — dokumen 5721 (dikerjakan manusia 2026-08-05):

```
23:54:20  Operator          Dokumen dibuat
23:54:29  operator          Edit Bagian: kosong -> AKN
23:54:33  operator          Dikirim ke team_verifikasi
23:54:33  team_verifikasi   Dokumen masuk
01:02:33  team_verifikasi   Dikembalikan ke AKN
```

**Kesimpulan:** mesin pencatatnya benar; yang kosong adalah masa lalunya. Membangun
"riwayat berwaktu" sekarang akan menghasilkan tampilan kosong pada 99,96% dokumen —
terlihat rusak padahal datanya memang tidak pernah ada.

Karena itu yang dibangun adalah **posisi dalam alur**, yang hanya butuh
`current_handler` + `status` — dimiliki setiap dokumen tanpa kecuali.

## 3. Keputusan user

| # | Pertanyaan | Keputusan |
|---|---|---|
| 1 | Apa yang paling ingin dilihat Bagian? | **Sampai mana perjalanannya** (bukan riwayat berwaktu) |
| 2 | Tahap yang dilewati? | Ditandai **"dilewati"**, dibedakan dari "belum" |
| 3 | Dokumen dikembalikan? | **Simpul Bagian di depan menyala**, ditandai perlu tindakan |
| 4 | Urutan tahap | Dikonfirmasi benar oleh user |

## 4. Urutan tahap kanonik

```
Bagian -> Operator -> Verifikasi -> Perpajakan -> Akuntansi -> Pembayaran
```

Simpul **Bagian** bukan tahap kerja keuangan; ia ada untuk menjawab "apakah bola ada di
tangan saya". Ia menyala hanya saat dokumen dikembalikan.

## 5. Aturan penentuan status tiap tahap

| Status | Aturan |
|---|---|
| `sekarang` | indeks tahap == indeks `current_handler` |
| `selesai` | indeks < sekarang **dan** ada jejak |
| `dilewati` | indeks < sekarang **dan** tidak ada jejak sama sekali |
| `belum` | indeks > sekarang |
| `perlu_diperbaiki` | `status == 'returned_to_bidang'` -> simpul Bagian; seluruh tahap hilir dinetralkan |

**"Ada jejak" didefinisikan berbeda untuk Operator.** Pada dokumen 5721, baris
`dokumen_role_data` milik `operator` punya `received_at = NULL` — karena Operator
*membuat* dokumen, bukan *menerima*. Aturan naif akan menandainya "dilewati", padahal
dialah yang memulai.

- **Operator:** ada jejak bila `tanggal_masuk` atau `created_at` terisi (praktis: selalu).
- **Role lain:** ada jejak bila `dokumen_role_data.received_at` terisi.

Catatan: dokumen hasil auto-forward tetap mengisi `received_at`, jadi tahapnya terbaca
`selesai`, bukan `dilewati`. Itu benar — dokumen memang melewatinya, hanya seketika.

`dilewati` menyala pada kasus yang berbeda: dokumen dipindah **langsung melompati** suatu
tahap lewat dropdown Pengurus Dokumen — mis. Operator mengirim langsung ke Pembayaran,
sehingga Perpajakan dan Akuntansi tak pernah punya baris `received_at`. Kasus ini nyata:
per 2026-08-06 belum ada guard urutan tahap di server (saran nomor 2 penguji masih
tertunda), jadi lompatan seperti itu masih mungkin terjadi.

## 6. Tampilan

**Sel** — satu baris, tidak menambah tinggi baris (mempertahankan hasil perampingan
badge 2026-08-06):

```
o-*-o-o-o  Verifikasi          dokumen normal
*-o-o-o-o  Perlu diperbaiki    dokumen dikembalikan (kuning)
```

**Modal saat diklik** — bukan popover. Tabel Bagian **bukan Tabulator**, sehingga
`.dbpop` di `public/js/document-tabulator.js` tidak tersedia di halaman itu. Halaman
Bagian sudah punya modal Bootstrap yang terbukti jalan (`showRejectionModal`), jadi
pola itu yang diikuti — jangan menanam mekanisme popup kedua di satu halaman.

```
PERJALANAN DOKUMEN
  v Operator          selesai
  * Verifikasi        sekarang
  - Perpajakan        dilewati
  o Akuntansi         belum
  o Pembayaran        belum
```

## 7. Arsitektur

Logika di **server**, klien hanya merender — mengikuti pola `App\Support\DocumentRow`
(badge & deadline dihitung server, nol logika bisnis di JS).

- **`App\Support\DocumentJourney`** — kelas biasa di `App\Support`, BUKAN trait
  `Concerns\`. `$dokumen` dan peta role-data dioper eksplisit supaya bisa di-unit-test
  tanpa kelas inang. Alasan yang sama melahirkan `App\Support\ColumnCustomization`.
- **`BagianDokumenController::index()`** memanggilnya per baris. Peta
  `dokumen_role_data` di-**eager-load sekali per request** — 5.719 dokumen tanpa itu
  berarti N+1.
- **View** hanya merender hasilnya.

### Kontrak keluaran

```php
DocumentJourney::forDokumen(Dokumen $d, array $roleDataMap): array
// [
//   'current_label' => 'Verifikasi',
//   'current_index' => 2,
//   'needs_action'  => false,          // true bila dikembalikan ke Bagian
//   'stages' => [
//     ['key' => 'bagian',      'label' => 'Bagian (AKN)', 'state' => 'belum'],
//     ['key' => 'operator',    'label' => 'Operator',     'state' => 'selesai'],
//     ['key' => 'verifikasi',  'label' => 'Verifikasi',   'state' => 'sekarang'],
//     ...
//   ],
// ]
```

## 8. Non-tujuan (sengaja TIDAK dikerjakan)

- **Cap waktu per tahap.** Datanya tidak ada untuk 5.717 dokumen lama. Bisa jadi lapisan
  tambahan kelak — `ActivityLogHelper` sudah mencatat benar untuk dokumen baru.
- **Mengubah alur atau `current_handler`.** Rancangan ini murni membaca.
- **Menerapkan ke role selain Bagian.** Role keuangan sudah punya kolom Pengurus Dokumen
  yang bisa diubah; kebutuhan mereka beda.

## 9. Pengujian

**Unit `DocumentJourney`** — enam keadaan:
1. Dokumen di tengah alur (verifikasi) — tahap sebelumnya `selesai`, sesudahnya `belum`
2. Dokumen di ujung (pembayaran) — semua sebelumnya `selesai`
3. Dokumen dikembalikan — simpul Bagian `perlu_diperbaiki`, hilir netral
4. Tahap tanpa `received_at` di tengah — `dilewati`, bukan `belum`
5. Dokumen baru di Operator — Operator `sekarang` meski `received_at` NULL
6. `current_handler` tak dikenal / kosong — jatuh ke Operator tanpa error

**Feature** — halaman Bagian benar-benar merender rangkaian tahap dan label posisi.

Tiap assertion wajib dibuktikan menggigit (rusakkan kode -> merah -> pulihkan -> hijau ->
`git diff` kosong), sesuai aturan 8 CLAUDE.md.

## 10. Risiko

| Risiko | Penanganan |
|---|---|
| Aturan "dilewati" salah untuk pola data yang belum saya lihat | Aturannya di satu kelas ber-unit-test; koreksi = ubah satu tempat |
| Tinggi baris tabel Bagian naik lagi | Sel dibatasi satu baris; modal menampung sisanya |
| N+1 pada 5.719 dokumen | Peta role-data di-eager-load sekali per request, diuji |
| `getRoleDisplayNameIndo` tak mengenal suatu handler | Keadaan #6 di pengujian |

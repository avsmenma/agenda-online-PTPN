# Desain: Kolom Beku yang Bisa Diatur User — Tabel Pembayaran

Tanggal: 2026-07-20
Status: disetujui user (brainstorming interaktif)
Halaman sasaran: `/documents/pembayaran/daftar` (route `documents.pembayaran.index`) — role pembayaran (+ admin)

## 1. Latar & Keputusan

Modal "Kustomisasi Kolom Tabel" saat ini hanya mengatur kolom mana yang tampil dan
urutannya. Kolom beku (frozen) tidak bisa diatur user — posisinya di-hardcode.
User ingin modal itu punya dua tab: kolom biasa, dan kolom beku (kiri/kanan).

Keputusan yang sudah diambil bersama user:

| Keputusan | Pilihan |
|---|---|
| Cakupan | **Halaman pembayaran dulu** — 4 halaman role lain tidak disentuh |
| Perilaku beku | **Kolom beku otomatis pindah ke tepi** (user memilih KOLOM, bukan jumlah) |
| Kondisi awal | Nomor Agenda beku kiri sebagai **bawaan yang boleh diubah**; kolom `No` tetap terkunci |
| Pengaman | **Peringatan berbasis lebar layar** (>50% lebar), tidak memblokir |
| Bentuk UI | **Tiga tombol per kolom** (Kiri / Bebas / Kanan), satu baris per kolom |

Alasan kolom beku harus menempel tepi: CSS `position: sticky` tidak bisa membekukan
kolom di tengah tanpa kolom di kirinya ikut beku — hasilnya saling tumpang tindih
saat digulir. Karena itu membekukan kolom = memindahkannya ke tepi.

## 2. Kondisi Saat Ini

- View: `resources/views/pembayaranNEW/dashboardPembayaran.blade.php` (±3.660 baris).
- Modal kustomisasi: baris ±2.665, dibuka `openColumnModal()`, disimpan
  `saveColumnCustomization()` (baris ±3.087) → menambah `columns[]` ke URL lalu reload.
- Persistensi kolom: `DashboardPembayaranController` baris 101–128 →
  `user.table_columns_preferences['pembayaran_dashboard']` + session
  `pembayaran_dashboard_table_columns`. **Bukan** localStorage (kunci
  `pembayaran_columns` hanya ditulis, tidak pernah dibaca — sudah dibersihkan).
- Kolom beku: di-hardcode di partial bersama
  `resources/views/partials/_documentTableStickyCells.blade.php`:
  - beku kiri: `.col-checkbox`, `.col-no`/`.col-number`, `.col-nomor_agenda`
  - beku kanan: `.col-handler`
  - JS `syncDocumentStickyOffsets()` mengukur lebar dan menyetel variabel CSS
    `--document-sticky-agenda-left`, `--document-sticky-left-width`,
    `--document-sticky-right-width`.
- Partial itu di-include **5 halaman**: akutansi, operator, pembayaran, perpajakan,
  team_verifikasi. Inilah alasan isolasi di §5 wajib ada.
- Setiap sel sudah punya kelas `col-<key>` (baris ±2.277), sehingga kolom mana pun
  bisa dijadikan sasaran sticky.

## 3. Model Data & Persistensi

Konfigurasi beku = dua daftar berurut berisi key kolom:

```php
['left' => ['nomor_agenda'], 'right' => []]
```

Dikirim lewat URL mengikuti pola yang sudah ada:
`?columns[]=...&frozen_left[]=nomor_agenda&frozen_right[]=status_pembayaran`

Controller menyimpan ke `user.table_columns_preferences['pembayaran_dashboard_frozen']`
dan session `pembayaran_dashboard_frozen_columns`, meniru persis alur `selectedColumns`.
Tidak ada mekanisme persistensi baru dan tidak ada migrasi database — kolom
`table_columns_preferences` sudah bertipe array/JSON.

Bawaan bila user belum pernah mengatur: `left = ['nomor_agenda']`, `right = []`,
sehingga tampilan awal identik dengan sekarang.

### Validasi server (wajib)

1. Buang key yang tidak ada di `$availableColumns`.
2. Buang key yang tidak ada di `$selectedColumns` — kolom tersembunyi tidak boleh beku.
3. Buang duplikat, dan buang key yang muncul di kiri sekaligus kanan (kiri menang).

## 4. Urutan Render

Controller menyusun urutan tabel: **beku kiri → kolom bebas → beku kanan**. Kolom `No`
selalu terdepan dan tetap beku.

Urutan **di dalam** ketiga kelompok sama-sama mengikuti urutan pemilihan user di tab 1.
Contoh: bila urutan pilihan user `[nomor_agenda, no_spp, nilai_rupiah, bulan]` dan yang
dibekukan kiri `[nilai_rupiah]` serta kanan `[no_spp]`, urutan render menjadi
`No | nilai_rupiah | nomor_agenda | bulan | no_spp`.

`$selectedColumns` tetap menyimpan urutan pilihan user apa adanya (dipakai tab 1);
urutan render tabel dihitung terpisah agar kedua kebutuhan tidak saling merusak.

## 5. Isolasi dari 4 Halaman Role Lain

Partial bersama diberi parameter opt-in dengan default aman:

```blade
@include('partials._documentTableStickyCells', ['dynamicFrozen' => true])
```

Di dalam partial: `@php $dynamicFrozen = $dynamicFrozen ?? false; @endphp`

Bila `true`, blok sticky hardcoded untuk `.col-nomor_agenda` dan `.col-handler`
dilewati dan digantikan kelas dinamis. Bila `false` (default), perilaku empat halaman
role lain **tidak berubah sama sekali**.

Kolom nomor urut baris (`.col-no`/`.col-number`) tetap beku di kedua mode. Kolom
`.col-checkbox` tidak ada di tabel pembayaran (hanya dipakai halaman role lain), jadi
aturannya dibiarkan apa adanya untuk mode default.

## 6. Mesin Frozen

Server menambahkan kelas pada `<th>` dan `<td>`: `is-frozen-left` / `is-frozen-right`.

CSS baru (khusus mode `dynamicFrozen`):
- `position: sticky !important` + `background-clip: padding-box`
- z-index: header beku > header biasa > sel beku > sel biasa
- latar opaque + zebra-striping: generalisasi aturan yang sekarang khusus
  `col-no`/`col-nomor_agenda` ke kelas baru

JS `syncDocumentStickyOffsets()` digeneralisasi:
- kolom beku kiri diiterasi dari kiri, `left` = jumlah kumulatif lebar kolom beku sebelumnya
- kolom beku kanan diiterasi dari kanan, `right` = jumlah kumulatif lebar kolom beku sesudahnya
- offset diset langsung ke elemen `th`/`td` terkait

Pemicu yang sudah tersedia dan dipakai ulang: `resize`, `document-table-refreshed`,
`fullscreenchange`, `DOMContentLoaded`.

## 7. UI Modal

Dua tab di dalam modal yang sudah ada:

1. **Kolom Tabel** — grid kartu yang sekarang, tidak diubah.
2. **Kolom Beku** — hanya menampilkan kolom yang tercentang di tab 1, satu baris per
   kolom dengan tiga tombol saling meniadakan: Kiri / Bebas / Kanan.

Urutan kolom beku mengikuti urutan pemilihan di tab 1.

Peringatan lebar memakai peta lebar yang sudah terdefinisi di partial: Nomor Agenda
210px, Pengurus Dokumen 240px, No 88px, sisanya 132px (default `min-width` header).
Bila total lebar beku > 50% `window.innerWidth`, tampilkan peringatan non-blocking
di dalam tab beku.

Tombol "Simpan Perubahan" mengirim `columns[]`, `frozen_left[]`, dan `frozen_right[]`
sekaligus dalam satu navigasi.

## 8. Kasus Tepi

| Kasus | Perlakuan |
|---|---|
| Kolom beku lalu disembunyikan di tab 1 | Status bekunya otomatis lepas — divalidasi di klien **dan** server |
| User lama: punya preferensi kolom, belum punya konfigurasi beku | Dapat bawaan `left = ['nomor_agenda']` |
| Semua kolom dibekukan | Diperingatkan, tetap diizinkan |
| Key beku tidak dikenal (URL diketik manual) | Dibuang diam-diam oleh validasi server |
| Mode fullscreen | Sticky tetap jalan; aturan sembunyi toolbar tidak terpengaruh |

## 9. Pengujian

Repo ini tidak punya test frontend otomatis, jadi verifikasi dilakukan manual dan
berurutan. Semua langkah wajib lulus sebelum pekerjaan dianggap selesai:

1. Modal punya 2 tab; tab beku hanya memuat kolom yang tercentang di tab 1
2. Bekukan kolom tengah → simpan → kolom pindah ke tepi dan **diam saat digulir horizontal**
3. Bekukan satu kolom ke kanan → menempel di tepi kanan saat digulir
4. Lepas semua beku → tabel bergulir penuh, tidak ada sisa sticky
5. Sembunyikan kolom yang sedang beku di tab 1 → status bekunya ikut lepas
6. Bekukan beberapa kolom lebar → peringatan lebar muncul
7. **Buka keempat halaman role lain (operator, akuntansi, perpajakan, tim verifikasi)
   → kolom beku bawaannya tidak berubah** — uji regresi terpenting
8. Muat ulang halaman → konfigurasi beku bertahan
9. Ulangi langkah 2–4 dalam mode fullscreen

## 10. Risiko

Partial sticky sudah menumpuk banyak aturan `!important` untuk sticky, zebra-striping,
dan z-index, ditambah set aturan terpisah untuk `body.is-fullscreen` dan
`body.document-table-only-fullscreen`. Kelas dinamis baru berpotensi bentrok,
terutama di mode fullscreen.

Mitigasi: kelas baru diberi spesifisitas setara dengan aturan yang digantikannya, dan
langkah uji 7 & 9 dijadikan gerbang wajib.

## 11. Di Luar Lingkup

- Menyebarkan fitur ini ke 4 halaman role lain (pekerjaan terpisah bila pilot berhasil)
- Mengubah lebar kolom secara manual oleh user
- Menyimpan beberapa preset tata letak kolom
- Menyeret (drag) kolom langsung di header tabel

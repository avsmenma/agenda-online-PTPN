# Larangan Jalur Mundur ke Operator & Bagian — Rancangan

**Tanggal:** 2026-08-09
**Status:** disetujui user (larangan sungguhan; pembayaran ikut)

---

## 1. Masalah

Dropdown **Pengurus Dokumen** menawarkan 5 peran alur kerja + optgroup Bagian kepada
setiap role yang memakainya. Server sama sekali tidak punya aturan tentang role mana
boleh menunjuk target mana — `DocumentHandlerController::update()` hanya menuntut
penggunanya adalah pengurus dokumen saat ini.

Akibatnya perpajakan, akutansi, dan pembayaran bisa mengembalikan dokumen langsung ke
**Operator** maupun ke **Bagian**. Yang ke Bagian bahkan memicu notifikasi WhatsApp
sungguhan lewat `DocumentReturnNotifier`.

Ini menyisakan lubang dari keputusan 2026-07-24 yang menghapus halaman "Pengembalian"
milik perpajakan & akutansi dengan alasan *"tak ada lagi pengembalian dokumen kecuali
verifikasi→bagian"*. Halamannya hilang, tapi dropdown tetap menyediakan jalurnya.

## 2. Keputusan user

| # | Pertanyaan | Keputusan |
|---|---|---|
| 1 | Pangkas dropdown saja, atau larangan sungguhan? | **Larangan sungguhan** — server ikut menolak. Pemangkasan dropdown saja bisa diakali lewat devtools karena opsi ditanam di data baris |
| 2 | Role mana saja? | **perpajakan, akutansi, pembayaran**. Operator & verifikasi tidak berubah |

Setelah perubahan, ketiga role kehilangan **Operator** dan **optgroup Bagian**. Sisa
pilihan: Tim Verifikasi, Tim Perpajakan, Tim Akuntansi, Tim Pembayaran. Jalan mundur
mereka hanya lewat Tim Verifikasi.

Operator dan verifikasi mempertahankan daftar penuh: operator memang hulu alur, dan
`returns.verifikasi.*` adalah satu-satunya alur pengembalian yang masih hidup.

## 3. Sumber aturan tunggal

Aturannya tinggal di `App\Support\HandlerOptions` dan dipakai **dua sisi** — meniru
preseden `DocumentHandlerController::tolakBilaBukanBagianAsal()` yang sengaja memakai
`bagianMap()` yang sama *"supaya apa yang ditawarkan UI dan apa yang diterima server
tidak pernah berbeda aturan"*:

```php
private const TANPA_JALUR_MUNDUR = ['perpajakan', 'akutansi', 'pembayaran'];

public static function bolehMenunjuk(string $rolePengguna, string $target): bool;
```

`false` bila `$rolePengguna` ada di daftar **dan** `$target` adalah `operator` atau
berawalan `bagian_`. Selain itu `true`.

- **UI** — `forDokumen()` memakainya untuk memangkas opsi.
- **Server** — `update()` memakainya untuk menolak, tanpa pengecualian apa pun.

## 4. Jebakan tampilan — kode ini pernah kena persis begini

Ketiga role melihat dokumen yang **bukan** miliknya:

| Role | Base query | Melihat dokumen milik Operator? | Melihat `returned_to_bidang`? |
|---|---|---|---|
| perpajakan | `status != 'returned_to_bidang'` + `excludeCsvImports()` | ya | tidak |
| akutansi | idem (identik) | ya | tidak |
| pembayaran | `whereNotNull('nomor_agenda')` — tanpa keduanya | ya | **ya** |

Bila opsi dipangkas mentah-mentah, `<select>` baris semacam itu kehilangan nilai
terpilihnya dan browser jatuh ke opsi pertama — tabel akan **menyatakan dokumen ada di
Tim Verifikasi padahal ada di Operator**. `DocumentRow.php:94-96` sudah menuliskan
bahayanya setelah kena sekali:

> *"menampilkan opsi pertama yang kebetulan terpilih ("Operator") jauh lebih menyesatkan"*

### 4.1 Aturan pengecualian

**Opsi dipertahankan bila ia adalah pengurus yang DITAMPILKAN untuk baris itu** — bukan
`current_handler` mentah.

Pembedaan itu wajib karena pembayaran melihat dokumen `returned_to_bidang`, dan untuk
baris tersebut nilai yang tampil adalah `bagian_<return_source>`, bukan `current_handler`
(yang sengaja tetap berisi role pengembali sejak commit `a1c9260`, 2026-05-13). Memangkas
optgroup Bagian tanpa pengecualian ini akan menjatuhkan tampilannya kembali ke
`current_handler` — menghidupkan lagi kontradiksi "dropdown bilang Tim Verifikasi, badge
bilang Dikembalikan ke AKN" yang justru diperbaiki commit itu.

### 4.2 Perhitungan nilai tampil dipisah

`DocumentRow::handlerUntukTampilan()` sekarang butuh daftar opsi untuk bekerja
(`opsiHandlerAda()`), sedangkan penyusun opsi butuh nilai tampil — melingkar.

Lingkaran diputus dengan mengekstrak perhitungan mentahnya, yang memang tak butuh opsi:

```php
// App\Support\DocumentRow — baru, public
public static function handlerTampilanMentah(Dokumen $dokumen): ?string
```

Isinya: `bagian_<return_source>` bila `status === 'returned_to_bidang'` dan
`return_source` terisi; selain itu `current_handler`.

`handlerUntukTampilan()` yang lama dipertahankan **perilakunya persis** — ia memanggil
method baru ini lalu tetap jatuh ke `current_handler` bila nilai `bagian_*` tak punya
opsi padanan (pengaman data lama yang `return_source`-nya tak cocok kolom `bagian`).

Controller mengoper hasilnya sebagai nilai yang dipertahankan. `HandlerOptions` tetap
tidak bergantung pada `DocumentRow` — ia hanya menerima `?string`.

## 5. Tanda tangan `forDokumen()`

```php
public static function forDokumen(
    ?string $bagian,
    array $bagianMap,
    string $rolePengguna,
    ?string $handlerDipertahankan
): array
```

Kedua parameter baru **wajib, tanpa nilai default**. Call site baru yang lupa mengisinya
akan error keras alih-alih diam-diam mengembalikan daftar penuh — CLAUDE.md aturan 6
melarang fitur yang mati tanpa suara.

Konsekuensinya **6 call site** ikut diperbarui: `DokumenController` (2×),
`DashboardAkutansiController` (2×), `DashboardPerpajakanController` (2×),
`TeamVerifikasiController`, `DashboardPembayaranController` — masing-masing sudah
mengetahui rolenya sendiri dan memegang `$d`.

## 6. Opsi terlarang yang tersisa dirender non-aktif

Klaim awal "opsi yang dipertahankan selalu berada di select yang disabled" **benar untuk
perpajakan & akutansi, tapi tidak untuk pembayaran.**

`can_change_handler = isCurrentHandler && !hasPending` (`DocumentRow.php:61`). Dokumen
yang dulu dikembalikan **pembayaran** ke Bagian menyimpan `current_handler = 'pembayaran'`
(bukan `bagian_x`), sehingga bagi user pembayaran select-nya **aktif** sementara nilai
tampilnya `bagian_x` — opsi terlarang di dalam dropdown yang bisa dipilih.

Baris seperti itu tak bisa lahir lagi setelah perubahan ini, tapi yang sudah ada tetap
ada. Karena itu opsi yang dipertahankan semata-mata demi tampilan diberi penanda:

```php
['value' => 'bagian_akn', 'label' => 'Akuntansi', 'disabled' => true]
```

dan `fmtHandler()` di `public/js/document-tabulator.js` merender ` disabled` bila
`o.disabled` bernilai true.

**Perubahan mesin bersama ini ADITIF.** Role yang tak pernah menyetel `disabled` tidak
berubah sama sekali — disiplin yang sama dipakai saat `showHandler` ditambahkan pada
Rollout 4. Server tetap penjaga sesungguhnya; atribut `disabled` hanya mencegah user
memilih sesuatu yang pasti ditolak.

## 7. Penegakan sisi server

Di `DocumentHandlerController::update()`, ditempatkan bersama pemeriksaan wewenang lain —
tepat **setelah** gerbang "hanya pengurus dokumen saat ini" (baris ~54-59) dan **sebelum**
`hasPendingApproval()`:

```php
if (!HandlerOptions::bolehMenunjuk($userRole, $targetHandler)) {
    return response()->json([
        'success' => false,
        'message' => Dokumen::getRoleDisplayNameIndo($userRole)
                   . ' tidak dapat mengembalikan dokumen ke Operator atau Bagian. '
                   . 'Kembalikan melalui Tim Verifikasi.',
    ], 403);
}
```

**403**, bukan 422 — ini aturan wewenang, sejalan dengan penolakan "hanya pengurus dokumen
saat ini" di atasnya yang juga 403.

Pengecualian §4.1 **tidak** berlaku di sini; ia murni urusan tampilan. Tak ada jalur sah
yang tertutup olehnya: target yang sama dengan pengurus saat ini sudah dihadang gerbang
`$userRole !== $currentHandler` lebih dulu.

Letak ini juga memastikan penolakan terjadi **sebelum** `DB::beginTransaction()` dan
sebelum `DocumentReturnNotifier::kirim()`, sehingga percobaan yang ditolak tidak mengubah
data dan tidak mengirim WhatsApp apa pun.

## 8. Pengujian

**Unit — `HandlerOptionsTest`:**

| # | Test | Yang dijaga |
|---|---|---|
| 1 | perpajakan/akutansi/pembayaran: tanpa `operator`, tanpa optgroup Bagian | Perilaku inti, ketiga role |
| 2 | operator & verifikasi: daftar tetap utuh 5 peran + Bagian | Gerbang tidak melebar |
| 3 | `bolehMenunjuk()` benar untuk tiap pasangan role×target | Aturan itu sendiri |
| 4 | perpajakan + `handlerDipertahankan='operator'` → opsi Operator bertahan, ber-`disabled` | Pengecualian tampilan §4.1 |
| 5 | pembayaran + `handlerDipertahankan='bagian_akn'` → optgroup Bagian bertahan, ber-`disabled` | Kasus `returned_to_bidang` §4.1 |
| 6 | Nilai yang dipertahankan TIDAK menghidupkan opsi terlarang lain | Pengecualian sempit, bukan pintu belakang |

**Unit — `DocumentRow`:** `handlerTampilanMentah()` mengembalikan `bagian_<kode>` hanya
saat `returned_to_bidang` + `return_source` terisi; `handlerUntukTampilan()` terbukti
tetap berperilaku sama seperti sebelum ekstraksi (termasuk fallback data lama).

**Feature — `DocumentHandlerController`:**

| # | Test | Yang dijaga |
|---|---|---|
| 7 | perpajakan → `operator` = 403 **dan** `current_handler` tak berubah | Penolakan nyata, bukan pesan kosong |
| 8 | akutansi → `bagian_<asal>` = 403 **dan nol notifikasi WhatsApp** | Notifier ada di jalur sukses; wajib dibuktikan tak tersentuh |
| 9 | pembayaran → `operator` = 403 | Role ketiga ikut terjaga |
| 10 | perpajakan → `akutansi` tetap berhasil | Gerbang tidak melebar ke target sah |
| 11 | verifikasi → `operator` tetap berhasil | Role di luar daftar tak terpengaruh |
| 12 | operator → `bagian_<asal>` tetap berhasil | Jalur operator utuh |

**Frontend:** assertion bahwa `fmtHandler()` merender `disabled` — dipersempit ke badan
fungsi, bukan pencarian string ke seluruh berkas (CLAUDE.md aturan 8; berkas ini sudah
memuat kata `disabled` di tempat lain).

Tiap assertion dibuktikan menggigit: rusakkan kode → test GAGAL → pulihkan → LULUS →
`git diff` kosong.

## 9. Perbaikan docblock basi di berkas yang disentuh

Dua docblock menyatakan hal yang sudah tidak benar dan langsung menyesatkan siapa pun
yang menelusuri perubahan ini:

- `HandlerOptions.php:14-15` — *"Pembayaran TIDAK memakai kelas ini ... showHandler:
  false dan menerima `$handlerOptions = []`"*. Salah sejak Rollout 4:
  `DashboardPembayaranController:664` memanggilnya dan
  `dashboardPembayaran.blade.php:2171` menyetel `showHandler => true`.
- `PembayaranDocumentRow.php:27` — *"TANPA forward"*, dengan alasan yang sama.

Keduanya diperbarui. Ini satu-satunya perubahan di luar lingkup fungsional, dan hanya
menyentuh komentar.

## 10. Yang sengaja TIDAK dikerjakan

- **Tidak ada migrasi data.** Dokumen yang sudah terlanjur dikembalikan ke Operator/Bagian
  oleh ketiga role tetap di tempatnya. Perubahan ini menutup jalur ke depan, bukan menarik
  kembali yang lampau.
- **`returnDirectlyToOperator()` dan `returnDirectlyToBagian()` tidak dihapus** — keduanya
  masih dipakai operator dan verifikasi. Perubahan ini tidak melahirkan kode mati.
- **`returns.verifikasi.*` tidak disentuh** — satu-satunya alur pengembalian yang hidup.
- **Urutan tahap tidak dijaga.** Operator masih bisa melompati Verifikasi; itu keputusan
  terpisah yang masih menunggu user.

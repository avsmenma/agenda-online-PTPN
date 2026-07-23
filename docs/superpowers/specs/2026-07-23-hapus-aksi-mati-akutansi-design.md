# Desain: Hapus program aksi mati akutansi (send-to-pembayaran / return / set-deadline)

Tanggal: 2026-07-23
Status: disetujui user (brainstorming interaktif)
Sasaran: role akutansi — `routes/web.php`, `app/Http/Controllers/DashboardAkutansiController.php`,
`resources/views/akutansi/dokumens/daftarAkutansi.blade.php`, `resources/views/akutansi/dokumens/_rows.blade.php`.

## 1. Latar & Keputusan

Akutansi mengirim dokumen ke pembayaran & mengembalikan ke verifikasi lewat **dropdown
Pengurus Dokumen** (`DocumentHandlerController::update`) — dikonfirmasi user. Fitur "aksi
per-baris" lama (tombol **Kirim Data** → send-to-pembayaran, **Balik** → return, plus modal
**set-deadline**) sudah **dormant**: `$showActionColumn = false` di-hardcode, jadi tombolnya
tak pernah render. User memutuskan program mati ini **dihapus total, bukan sekadar
disembunyikan**.

Ini **cleanup tersendiri, mendahului** rollout Tabulator akutansi (program terpisah).
Berkas view lama akutansi TIDAK dihapus di sini — ia masih merender tabel akutansi yang
hidup; hanya potongan aksi mati di dalamnya yang dibedah keluar. (Penghapusan view utuh
menyusul saat rollout Tabulator.)

Verifikasi grep (Explore 2026-07-23) membuktikan **ketiganya yatim**; Inbox TIDAK memakai
satu pun (Inbox pakai `inbox.reject`/`inbox.approve`). `set-deadline` mati ganda
(`openSetDeadlineModal` pun tak punya pemanggil). Nol test pecah.

> Nomor baris di bawah indikatif (snapshot pra-edit). Task menghapus baris → offset bergeser.
> **Jangkar sebenarnya adalah blok kode/nama yang ditunjuk** — cari berdasarkan isinya.

## 2. Yang DIHAPUS (semua terbukti yatim)

### 2.1 Route — `routes/web.php`
- `:481` `documents.akutansi.set-deadline` → `setDeadline`
- `:482` `documents.akutansi.send-to-pembayaran` → `sendToPembayaran`
- `:483` `documents.akutansi.return` → `returnDocument`

Tak ada `route('documents.akutansi.{send-to-pembayaran|return|set-deadline}')` di mana pun
(nol hit); semua pemanggil adalah `fetch()` URL hardcoded yang hanya hidup di view mati.

### 2.2 Method controller — `DashboardAkutansiController.php`
- `setDeadline` **519-680**
- `sendToPembayaran` **1036-1148**
- `returnDocument` **1224-1327** (method terakhir; brace penutup kelas :1328 tetap)

⚠️ **SISAKAN `getSearchSuggestions` 1150-1222** — helper pencarian HIDUP yang duduk di
antara `sendToPembayaran` dan `returnDocument`. Penghapusan tidak kontigu.
Tiga method itu hanya memanggil API model/helper bersama yang hidup — nol private-helper
yang jadi yatim.

### 2.3 View mati — `daftarAkutansi.blade.php`
- Header kolom `@if($showActionColumn) <th>…Aksi…</th> @endif` **2205-2207**
- 8 modal mati **2217-2543**: `#setDeadlineModal`, `#deadlineSuccessModal`, `#returnModal`,
  `#returnConfirmationModal`, `#returnSuccessModal`, `#returnValidationWarningModal`,
  `#sendToPembayaranModal`, `#sendToPembayaranSuccessModal`
- Fungsi JS: `sendToPembayaran` **2780-2806**, `selectDestination` **2808-2819**,
  `confirmSendToPembayaran` **2821-2879**, `openSetDeadlineModal` **2917-2924**,
  `confirmSetDeadline` **2937-3052**, `openReturnModal` **3344-3350**,
  `confirmReturn` **3353-3433**
- Listener char-counter `deadlineNote` **2913-2915** — tak ber-guard, akan error setelah
  modalnya hilang; hapus bersama modal set-deadline.
- (opsional) `openReturnToPerpajakanModal` **2926-2935** — stub mati tanpa pemanggil.

### 2.4 Sel aksi — `_rows.blade.php`
- Blok `@if($showActionColumn) <td class="col-action">…@endif` **516-582**.

### 2.5 Plumbing `$showActionColumn` (kini fully dead setelah 2.3-2.4)
- `$showActionColumn = false;` di `daftarAkutansi.blade.php:2175`
- default `$showActionColumn` di `_rows.blade.php:1`
- argumen `'showActionColumn' => false` pada render `_chunk` di
  `DashboardAkutansiController.php:~353`

## 3. ⚠️ Bedah di dalam file HIDUP — jangan patahkan tabel akutansi

`daftarAkutansi.blade.php` masih merender tabel akutansi yang dipakai produksi. Jebakan halus:
- **Char-counter `returnReason` 3327-3340** menumpang blok `DOMContentLoaded` bersama
  (3324-3341) yang JUGA memanggil `initializeDeadlines()` (baris 3325) — fungsi kolom
  Deadline yang HIDUP. **Hapus HANYA body 3327-3340; pertahankan `DOMContentLoaded`-nya
  dan `initializeDeadlines()`.**
- Kolom **Deadline** (`_rows.blade.php:170-411`) beserta render/countdown-nya
  (~3242-3321) & `initializeDeadlines()` — **HIDUP, jangan sentuh.** Terpisah penuh dari
  modal set-deadline.

## 4. Yang WAJIB TETAP (batas keselamatan)

- Kolom **Deadline** (tampilan) + `initializeDeadlines()` + render countdown.
- `getSearchSuggestions` (helper pencarian).
- Modal `#errorModal`/`#warningModal` + `showErrorModal`/`showWarningModal` (~3053+) —
  helper alert umum.
- Route `documents.akutansi.index` (:477) & `documents.akutansi.detail` (:480); Inbox
  (`inbox.approve`/`inbox.reject`/`inbox.bulk-approve` + `InboxController`).
- **Lintas-role — controller BERBEDA, jangan tersasar:** perpajakan (`setDeadline`,
  `returnDocument`, `sendToAkutansi`, `sendToNext` + route `documents.perpajakan.*`),
  pembayaran (`setDeadline`, `updateStatus` + `documents.pembayaran.*`), verifikasi
  (`setDeadline` + route sendiri). Serta `DokumenRoleData::setDeadline` (Model) &
  `AutoForwardDokumenService::sendToPembayaranAndApprove` (Service) — **tabrakan nama saja**,
  hidup, jangan disentuh.

## 5. Test

Nol test merujuk ketiga route/method/URL akutansi ini (grep `tests/` = nol hit relevan).
Penghapusan **tidak memecah test**. Suite tetap harus hijau sebagai bukti tak ada regresi
tak terduga.

## 6. Di luar lingkup
- Rollout Tabulator akutansi (program berikutnya).
- Penghapusan view lama akutansi secara utuh (menyusul saat rollout).
- Fitur/route role lain.

## 7. Gerbang verifikasi (dilaporkan verbatim di plan)
1. `php artisan test` → **hijau** (jumlah tak berubah; nol test terkait).
2. Grep bukti yatim → kosong:
   `grep -rn "send-to-pembayaran\|documents.akutansi.return\|akutansi.*set-deadline\|sendToPembayaran\|returnDocument\|openSetDeadlineModal\|confirmSetDeadline\|openReturnModal\|confirmReturn\|confirmSendToPembayaran\|selectDestination\|showActionColumn" routes/ app/Http/Controllers/DashboardAkutansiController.php resources/views/akutansi/`
   → sisa hanya rujukan SAH (mis. `sendToPembayaran`/`setDeadline`/`returnDocument` milik
   controller/model/service LAIN, di luar path akutansi ini) — diperiksa satu per satu.
3. **QA visual akutansi oleh user** (view masih hidup): buka halaman daftar dokumen
   akutansi → tabel **tetap render normal** (kolom Deadline tampil, pencarian jalan,
   dropdown Pengurus jalan, inline-edit jalan, kustomisasi kolom jalan), nol error konsol.

## 8. Deploy
Setelah QA akutansi lolos: commit per-file → `git push origin codinggemini` → pull server →
`php artisan route:clear && view:clear && config:clear`.

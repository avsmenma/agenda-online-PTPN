# Desain: Operator `/documents` → Tabulator-only (hapus tabel lama)

Tanggal: 2026-07-23
Status: disetujui user (brainstorming interaktif)
Halaman sasaran: `/documents` (route `documents.index`) — role operator (+ admin)

## 1. Latar & Keputusan

View Tabulator operator sudah dikonfirmasi user "oke". Fase §7 CLAUDE.md (pembersihan
tabel lama operator + penghapusan flag `?classic`) yang sebelumnya **terkunci** kini
**dibuka user**. Tujuannya: operator hanya dilayani view Tabulator; kode khusus-classic
operator dimusnahkan total.

Keputusan yang sudah diambil bersama user:

| Keputusan | Pilihan |
|---|---|
| Cakupan | **Operator saja.** 5 role lain (verifikasi, akutansi, perpajakan, pembayaran, bagian) TIDAK disentuh — mereka masih di tabel lama |
| Gaya | **Hapus-saja, TANPA rename.** `operator-tabulator.js`, `OPERATOR_TABULATOR_CONFIG`, id `#operatorTabulatorTable` dibiarkan utuh. Generalisasi nama ditunda ke Fase A (rollout role kedua) |
| Fallback `?classic` | **Dihapus total** — tidak ada lagi jalur mundur ke tabel lama |
| Aksi Kirim ke Verifikasi | **Dropdown "Pengurus Dokumen" adalah jalur forward yang berlaku.** Jalur "Kirim" lama (approval-gated) dihapus tuntas |

Ini bukan penyatuan 6 tabel (itu Fase A–E pada roadmap terpisah). Spec ini murni
**pembuangan kode operator lama** sebagai langkah B pada roadmap.

## 2. Kondisi Saat Ini — sakelar view

`DokumenController::index()` memilih view dari flag `?classic`:

- `app/Http/Controllers/DokumenController.php:321-323` —
  `$useClassic = $request->boolean('classic'); $view = $useClassic ? 'operator.dokumens.daftarDokumen' : 'operator.dokumens.daftarDokumenTabulator'; return view($view, $data);`
- `app/Http/Controllers/DokumenController.php:185-187` — guard yang hanya membersihkan
  sesi sort saat **bukan** classic.
- View classic dirender **hanya** di sini — tidak ada `@extends`/`@include` `daftarDokumen`
  di tempat lain (hanya controller + test yang menyebutnya).

## 3. Yang DIHAPUS (semua terbukti classic-only)

### 3.1 Berkas view / partial
- **`resources/views/operator/dokumens/daftarDokumen.blade.php`** — tabel classic lama.
  Hanya dirujuk `DokumenController.php:322` + test. AMAN.
- **`resources/views/operator/dokumens/_chunk.blade.php`** — dirender hanya oleh cabang
  `virtual_chunk` operator (`DokumenController.php:247-253`). Satu-satunya `@include`-nya
  adalah `_tableRowsAjax` (`_chunk.blade.php:9`). Role lain punya `_chunk` sendiri
  (`team_verifikasi/`, `perpajakan/`, `akutansi/`) — bukan yang ini. AMAN.
- **`resources/views/operator/dokumens/_tableRowsAjax.blade.php`** — ⚠️ lihat §4 (urutan
  wajib). Dirujuk classic `daftarDokumen.blade.php:2273`, operator `_chunk.blade.php:9`,
  DAN controller `inlineCreate()` `DokumenController.php:372`. Rujukan di
  `OperatorDocumentRow.php` & `operator-tabulator.js` hanyalah **komentar** (dok paritas),
  bukan kode hidup.
- Catatan: satu-satunya partial `_*.blade.php` di `operator/dokumens/` adalah `_chunk` &
  `_tableRowsAjax` — tidak ada partial classic-only lain.

### 3.2 Cabang / method controller (jalur render/serve classic-only)
- **Cabang pemilih `?classic`** — `DokumenController.php:321-323`, plus guard sesi
  `185-187`. Perbaikan: paksa `$view = 'operator.dokumens.daftarDokumenTabulator'` dan
  lepas syarat `!classic` pada penghapusan sesi sort.
- **Cabang `virtual_chunk`** — `DokumenController.php:247-253`. Satu-satunya yang merender
  `operator/_chunk`. Hanya terjangkau lewat JS `partials/virtual-document-table`
  (`virtual-document-table.blade.php:149` menyetel `virtual_chunk=1`) yang di-include
  classic TAPI TIDAK oleh view Tabulator. AMAN dihapus bersama classic (lihat test §5).
- **`getDocumentDetail()`** `DokumenController.php:545` + route `documents.detail`
  `routes/web.php:322`. Bukti classic-only: satu-satunya pemanggil frontend adalah classic
  `daftarDokumen.blade.php:3963` (`fetch('/documents/${docId}/detail')`), plus URL
  mati/rusak `daftarDokumen.blade.php:2999` (`/detail-Operator`, tanpa route). View
  Tabulator & `operator-tabulator.js` tak pernah memanggil `/detail`. Method
  `getDocumentDetail` di controller role lain (`TeamVerifikasiController.php:786`,
  `DashboardPembayaranController.php:2298`, `DashboardAkutansiController.php:787`,
  `DashboardPerpajakanController.php:687`, `BagianDokumenController.php:197`) adalah method
  TERPISAH pada route TERPISAH — **jangan disentuh**.
- **`sendToTeamVerifikasi()`** (jalur Kirim lama) + route `documents.send-to-verifikasi`
  `routes/web.php:325`. Method memakai `$dokumen->sendToInbox('team_verifikasi')`
  (`DokumenController.php:1292`) → status `waiting_reviewer_approval` /
  `sent_to_team_verifikasi` (menunggu approval reviewer). Satu-satunya pemanggil frontend:
  classic `daftarDokumen.blade.php:2914-2920`. View Tabulator tak punya tombol Kirim.
  Yatim total begitu classic dihapus → dihapus (lihat keputusan bisnis §6).

### 3.3 Tidak ada "endpoint tableRowsAjax" berdiri sendiri
`_tableRowsAjax` tak pernah dikembalikan route sendiri; hanya di-`@include`
(classic:2273, `_chunk`:9) dan dirender di dalam `inlineCreate()` bersama
(`DokumenController.php:372`). Lihat §4.

## 4. ⚠️ Urutan wajib — satu landmine

`inlineCreate()` (route `documents.inline-create`, dipakai classic DAN Tabulator)
memanggil `view('operator.dokumens._tableRowsAjax', ...)->render()` **tanpa syarat** di
`DokumenController.php:372-376` dan mengembalikannya sebagai key JSON `html`. Klien
Tabulator mengonsumsi key `row`, bukan `html` (komentar `DokumenController.php:382-384`).

Maka urutan implementasi WAJIB:
1. **Edit `inlineCreate()` dulu** — hentikan render `_tableRowsAjax` (buang penyusunan
   key `html`). Klien Tabulator memakai `row`, jadi ini aman **setelah** classic hilang.
2. **Baru** hapus `_tableRowsAjax.blade.php`.

Terbalik = endpoint bersama pecah + test `InlineCreateDokumenTest` &
`OperatorInlineCreateRowTest` gagal. Pastikan kedua test itu tidak meng-assert key `html`;
bila iya, sesuaikan asersinya ke `row`.

## 5. Yang WAJIB TETAP — batas keselamatan

Dipakai role lain yang MASIH di tabel lama; menghapusnya merusak halaman yang tak sedang
dibuka. Untuk tiap `@include` di `daftarDokumen.blade.php`:

| Partial (baris classic) | Juga di-include oleh (role lain / global) |
|---|---|
| `partials.virtual-document-table` (`:5203`) | perpajakan `:4551`, team_verifikasi `:5894`, akutansi `:4671` |
| `partials._inlineEditEngine` (`:5204`) | bagian `:2764`, perpajakan `:4552`, team_verifikasi `:5895`, akutansi `:4672`, pembayaranNEW `:3968` |
| `partials._activeCellNav` (`:5205`) | bagian `:2772`, perpajakan `:4553`, team_verifikasi `:5898`, akutansi `:4673`, pembayaranNEW `:3969` |
| `partials._documentTableStickyCells` (`:5206`) | perpajakan `:4554`, team_verifikasi `:5903`, akutansi `:4674`, pembayaranNEW `:3857` |
| `partials._compactDocumentTable` (`:5226`) | perpajakan `:4556`, team_verifikasi `:6044`, akutansi `:4676` |
| `operator.dokumens._tableRowsAjax` (`:2273`) | file operator-only, tapi terikat `inlineCreate()` bersama — lihat §4 |

Partial transitif / global (WAJIB TETAP):
- **`partials.document-handler-select`** — di-include `_tableRowsAjax.blade.php:251` (mati
  bersamanya) TAPI juga oleh `team_verifikasi/.../_rows.blade.php:611`,
  `perpajakan/.../_rows.blade.php:635`, `akutansi/.../_rows.blade.php:514`. BERSAMA.
- **`partials.compact-document-ui`** — di-include GLOBAL di `layouts/app.blade.php:3736`
  (semua halaman, termasuk Tabulator). TETAP.
- **`partials.document-workbench-ui`** — di-include GLOBAL di `layouts/app.blade.php:3737`;
  view Tabulator bergantung padanya (`daftarDokumenTabulator.blade.php:79`, id
  `documentTableContainer`). TETAP.

Endpoint bersama yang dirujuk classic (TETAP):
- **`documents.inline-update`** (`web.php:333`) — classic `:4865,:5062`,
  `_inlineEditEngine.blade.php:317,:632`, Tabulator `:20`. Semua role. TETAP.
- **`documents.inline-create`** (`web.php:318`) — classic `:2429` + Tabulator `:19`. TETAP.
- **`documents.destroy`** (`web.php:324`) — classic `:4559` + Tabulator `:22,:98`. TETAP.
- **`documents.handler.update`** (`web.php:340`, `DocumentHandlerController::update`) —
  Tabulator `:21` + `document-handler-select.blade.php:201`. **Ini jalur forward operator
  → verifikasi.** TETAP.
- **`documents.data`** (`web.php:301`, `datatable()` `:125`) — **Tabulator-only** (`:18`;
  `OperatorDatatableTest`). Classic memuat baris via `virtual_chunk`, BUKAN `documents.data`.
  Tetap karena menopang view yang bertahan.

## 6. Keputusan bisnis — jalur forward operator → verifikasi

Ada DUA mekanisme dengan semantik berbeda; user memutuskan yang lama dibuang:

| | Cara | Efek |
|---|---|---|
| **Dropdown Pengurus** (Tabulator, berlaku) | assign → Team Verifikasi via `DocumentHandlerController::update` `:25` → `moveDirectlyToTeamVerifikasi()` `:157-196` | **langsung diterima** (`last_action_status = auto_accepted_by_verifikasi` `:188`, tampilan operator `terkirim` `:178`). Guard: kolom **Bagian wajib terisi** `:109` |
| **Tombol "Kirim" classic** (lama, DIHAPUS) | `documents.send-to-verifikasi` → `sendToTeamVerifikasi` → `sendToInbox` | masuk inbox, **menunggu approval reviewer** (`waiting_reviewer_approval`) |

Keputusan user: **dropdown langsung yang berlaku; jalur Kirim lama (tombol + route + method)
dihapus tuntas.** Konsekuensi yang disadari: gerbang approval reviewer untuk kiriman
operator memang sudah tidak dipakai.

## 7. Test

- **`tests/Feature/OperatorTabulatorViewTest.php`** — hapus/tulis-ulang metode yang
  bergantung classic:
  - `test_flag_classic_menyajikan_view_lama()` `:99-107` (`:102` route `classic=1`; `:105`
    asersi penanda classic `btnTambahBarisInline`; `:106` `assertDontSee('operatorTabulatorTable')`).
  - `test_flag_classic_tidak_membersihkan_sesi_sort_lama()` `:135-147` (`:142` `classic=1`;
    `:145-146` sesi sort dipertahankan).
  - Komentar docblock `:12-13`, `:132-133` yang menyebut classic/toggleSort — sesuaikan.
  - Metode `test_default_*`, `test_toolbar_*`, `test_modal_dan_tombol_detail_*`, webfont/css
    menargetkan view Tabulator — TETAP.
- **`tests/Feature/VirtualChunkDokumenTest.php`** — seluruhnya menguji jalur
  `virtual_chunk`/`_chunk`; hapus berkas ini (docblock `:11`, test `:56`/`:61`, `:76`/`:81`).
- **`tests/Unit/OperatorDocumentRowTest.php:16`** — komentar saja (asal logika dari
  `_tableRowsAjax`); tak ada dependensi runtime. Boleh perbarui komentar.
- **`tests/Feature/InlineCreateDokumenTest.php`** (`:11,26,48,60,68,76,86`) &
  **`tests/Feature/OperatorInlineCreateRowTest.php`** (`:11,27,42,64`) — menguji
  `documents.inline-create` yang kini merender `_tableRowsAjax` (key `html`). TETAP, tapi
  urutkan edit controller lebih dulu (§4); bila ada asersi key `html`, alihkan ke `row`.

Tidak ada test yang merujuk `getDocumentDetail`, `documents.detail`, atau `send-to-verifikasi`
— penghapusan ketiganya tak punya test yang perlu diubah.

## 8. Di luar lingkup (sengaja — agar fokus & aman)

- Route yatim **pra-ada** yang tak terkait tabel: `documents.progress`
  (`getDocumentProgressForOperator`, `web.php:323`), `documents.approve` (`approveDocument`,
  `web.php:326`), `documents.bulk-send-to-verifikasi` (`web.php:315`) — nol pemanggil
  frontend, dead code lama independen dari classic. Cleanup terpisah.
- Rename/generalisasi engine → Fase A.
- Rollout/penghapusan tabel role lain → program terpisah.
- Penyatuan kustomisasi kolom & freeze → Fase D/E.

## 9. Pendekatan

**A — hapus bersih sekarang** (dipilih): sesuai keputusan "pembersihan total". Ditolak:
- *B deprecate-dulu* (biarkan berkas, jadikan `?classic` redirect) — setengah-jalan,
  bertentangan dengan kehendak user.
- *C hapus view saja, sisakan route/method dormant* — bertentangan dengan keputusan §6
  (hapus tuntas jalur Kirim lama).

## 10. Gerbang verifikasi (dilaporkan verbatim di plan)

1. Grep bukti tiap penghapusan yatim → **kosong**:
   `grep -rn "daftarDokumen\b\|_chunk\|_tableRowsAjax\|virtual_chunk\|getDocumentDetail\|documents.detail\|send-to-verifikasi\|sendToTeamVerifikasi\|classic" resources/ routes/ app/ tests/`
   (yang tersisa hanya rujukan sah: nama file role lain, method role lain, dsb — diperiksa
   satu per satu).
2. `node --check public/js/operator-tabulator.js` → exit 0 (harus tetap, meski tak diubah).
3. `php artisan test` → **hijau** (jumlah test berubah karena test classic dihapus).
4. **QA visual operator oleh user** (agent tak punya browser login): buka `/documents`,
   pastikan Tabulator tampil; uji **forward via dropdown Pengurus → Team Verifikasi**,
   **inline-create baris baru**, **hapus baris**, inline-edit. `?classic=1` harus TIDAK lagi
   menyajikan tabel lama (menyajikan Tabulator).

## 11. Deploy

Commit **per-file** (JANGAN `git add .`), pesan Bahasa Indonesia. Setelah QA user lolos:
commit → `git push origin codinggemini` → pull di server → `php artisan route:clear &&
view:clear && config:clear`. Clear cache tidak boleh dilewat.

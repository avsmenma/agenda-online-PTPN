# Desain: Fase A — Ekstrak engine Tabulator jadi komponen bersama

Tanggal: 2026-07-23
Status: disetujui user (brainstorming interaktif)
Sasaran: `public/js/operator-tabulator.js` + `public/css/tabulator-agenda.css` +
`resources/views/operator/dokumens/daftarDokumenTabulator.blade.php` (role operator).

## 1. Latar & Keputusan

Program: menyebarkan tabel Tabulator operator ke role lain (verifikasi, akutansi,
perpajakan, pembayaran) yang masih memakai tabel lama. Menyalin engine operator jadi
4 berkas baru = **penyakit utama** (salinan ke-7 dst). Karena itu engine operator
**diekstrak jadi satu komponen bersama yang di-parameter**, lalu tiap role jadi
konsumennya.

**Fase A** adalah langkah fondasi program ini, dipilih user sebagai **langkah tersendiri**:

| Keputusan | Pilihan |
|---|---|
| Cakupan Fase A | Ekstrak/generalisasi engine. **Operator konsumen satu-satunya.** |
| Perilaku operator | **NOL perubahan** — tabel operator identik sesudahnya. |
| Alasan standalone | Mengisolasi risiko "apakah operator rusak" dari rollout role manapun. |
| Rename | **Ya** (kebalikan keputusan cleanup 2026-07-23 yang menunda rename) — sebab kini engine memang jadi bersama. |

Bentuk kontrak config dibentuk **mengantisipasi trio classic** (verifikasi/akutansi/
perpajakan) berdasarkan peta 4-role, tapi operator hanya mengisi field yang ia butuh.
Slot untuk kebutuhan role lain **didefinisikan tapi belum dibangun** (YAGNI) — dibangun
saat rollout yang benar-benar membutuhkannya.

### Ringkasan peta 4-role (acuan bentuk config)
- **Trio classic (verifikasi/akutansi/perpajakan)**: kembar dekat operator — `Arr::except(config('document_columns.base'), ['status'])`, partial & route inline-update/handler sama. Gap utama: **belum punya endpoint JSON** (masih server-rendered `_rows`/`_chunk`), dan punya **kolom aksi per-baris** (Kirim/Balik/Paraf) yang operator tak punya. Akutansi termudah (2 aksi); verifikasi terberat (paraf + return-to-bagian).
- **pembayaran**: outlier — JSON DataTables-shaped, peta kolom sendiri, kustomisasi freeze 2-tab (`FrozenColumnLayout`), aksi bayar/PDF/rekapan. Butuh slot ekstra; rollout terakhir.

## 2. Kondisi Saat Ini

- Engine: `public/js/operator-tabulator.js` (±1.497 baris, satu IIFE) membaca
  `window.OPERATOR_TABULATOR_CONFIG` (baris 23). Menyimpan `window.operatorTable`.
- Mount: elemen `id="operatorTabulatorTable"`. JS menargetkannya di **8 titik** total
  (`operatorTabulatorTable`/`OPERATOR_TABULATOR_CONFIG`), termasuk query internal seperti
  `document.querySelector('#operatorTabulatorTable .tabulator-tableholder')` dan
  `'#operatorTabulatorTable .tabulator-header .tabulator-col.tabulator-frozen...'`.
- CSS: `public/css/tabulator-agenda.css` (±340 baris) — **39 selektor** di-scope ke
  `#operatorTabulatorTable` (mis. `#operatorTabulatorTable.tabulator .tabulator-cell...`).
  `!important` nyata = 4 (3 dropdown + 1 dark-mode).
- Blade: `daftarDokumenTabulator.blade.php` menyusun `window.OPERATOR_TABULATOR_CONFIG`
  (baris 17-35) dengan field: `dataUrl, inlineCreateUrl, inlineUpdateTpl, handlerTpl,
  destroyTpl, csrf, columns, availableColumns, selected, ie{kategori,sub,item,jenis,bagian},
  bulanList`. Memuat aset via `\App\Support\Asset::versioned('js/operator-tabulator.js')`
  dan `('css/tabulator-agenda.css')`.
- Test: `OperatorTabulatorViewTest` meng-assert `operatorTabulatorTable` muncul (2 titik).

## 3. Target Desain

### 3.1 Rename & generalisasi
- **Berkas JS**: `public/js/operator-tabulator.js` → `public/js/document-tabulator.js`
  (`git mv`). Perbarui referensi `Asset::versioned(...)` di Blade operator.
- **Config global**: `window.OPERATOR_TABULATOR_CONFIG` → `window.DOCUMENT_TABULATOR_CONFIG`.
  Nama global tabel `window.operatorTable` → **tetap ada untuk operator** demi kompatibilitas
  kode lain yang mungkin memakainya (dicek via grep di plan); tetapi engine juga menaruh
  instance di `window.documentTable` (generik) agar konsumen berikutnya punya pegangan.
  *(Plan memverifikasi siapa pemakai `window.operatorTable`; bila nol pemakai luar, cukup
  `window.documentTable`.)*
- **Mount id via config**: tambah field `mountId` ke config. Operator **tetap** memakai
  `id="operatorTabulatorTable"` (instance-id-nya sendiri) → test tetap valid, churn minimal.
  Engine TIDAK lagi hardcode `#operatorTabulatorTable`; ia membaca `CFG.mountId`, meresolusi
  elemen sekali, lalu memakai **query relatif-elemen** (`el.querySelector('.tabulator-tableholder')`)
  menggantikan `document.querySelector('#operatorTabulatorTable ...')`.

### 3.2 CSS jadi berbasis kelas bersama
- Elemen mount diberi kelas statis `doc-tabulator` di Blade:
  `<div id="operatorTabulatorTable" class="doc-tabulator">`.
- Ganti **39** selektor `#operatorTabulatorTable` → `.doc-tabulator`. Di mana sekarang
  `#operatorTabulatorTable.tabulator` (id+kelas), jadikan `.doc-tabulator.tabulator`
  (dua kelas) — **mempertahankan keunggulan spesifisitas atas tema bawaan Tabulator**
  (rantai `.doc-tabulator.tabulator .tabulator-row .tabulator-cell` = (0,4,0) tetap menang
  atas `.tabulator .tabulator-row .tabulator-cell` = (0,3,0)). Nama berkas
  `tabulator-agenda.css` **dipertahankan** (churn nihil; nama internal).
- Aturan `.op-copy-pop` (popup Disalin) yang ditempel ke `<body>` tetap apa adanya
  (sudah tak ber-scope ke id).

### 3.3 Kontrak config (interface engine)
Field yang **dipakai operator** (tak berubah maknanya, hanya berpindah ke nama global baru):

```
mountId          string   id elemen mount (operator: 'operatorTabulatorTable')
dataUrl          string   endpoint JSON baris (operator: documents.data)
inlineCreateUrl  string
inlineUpdateTpl  string   template URL, '{id}' diganti klien
handlerTpl       string
destroyTpl       string
csrf             string
columns          [{key,label}]  kolom tampil terurut
availableColumns {key:label}
selected         [key]
ie               {kategori,sub,item,jenis,bagian}   sumber dropdown inline-edit
bulanList        [string]
```

Slot **dicadangkan, TIDAK dibangun di Fase A** (operator tak mengisinya; engine
memperlakukan absennya sebagai "fitur mati"):

```
actionColumn     (def kolom aksi per-baris)   → dibangun saat rollout verifikasi
editableFields   (kebijakan sel editable per-role) → dibangun saat dibutuhkan
```

### 3.4 Default bersama tetap di engine
`FIELD_TYPE`, `DEPENDENT_FIELDS`, `NON_EDITABLE_FIELDS` (skema dokumen — sama untuk semua
role) tetap sebagai konstanta default di engine, dapat di-override lewat config di rollout
mendatang. Editor kustom (number/date/textarea), formatter, jalur §8 (nav, inline-edit,
Ctrl+C/V, Delete, kaskade, undo/redo, popup, persistensi lebar, koreksi gulir beku,
dropdown Pengurus) **tidak berubah logikanya** — hanya sumber id mount & scope CSS yang
digeneralisasi.

## 4. Di Luar Lingkup Fase A (dibangun per-rollout)
- Render kolom aksi per-baris (Kirim/Balik/Paraf/dsb).
- Endpoint JSON untuk trio classic (mereka belum punya `<role>/data`).
- Kustomisasi kolom beku + `FrozenColumnLayout` (pembayaran).
- Adapter JSON DataTables-shaped (pembayaran).
- Aksi bayar/PDF/rekapan (pembayaran).
- Konsumen kedua yang sesungguhnya (verifikasi dst) — program terpisah.

## 5. Risiko & Mitigasi
1. **Spesifisitas CSS turun (id → kelas).** Mitigasi: pertahankan awalan dua-kelas
   `.doc-tabulator.tabulator` di mana dulu `#id.tabulator`, sehingga tetap menang atas
   tema Tabulator. **QA visual operator wajib** memastikan tak ada gaya yang jebol.
2. **Banyak situs selektor** (JS 8 + CSS 39). Satu `#operatorTabulatorTable` yang
   terlewat di CSS = gaya diam-diam patah. Plan wajib enumerasi tuntas + grep akhir
   memastikan nol `#operatorTabulatorTable` tersisa di CSS dan nol mount-id hardcoded di JS.
3. **Menyentuh tabel produksi operator.** Disiplin nol-perubahan-perilaku + QA operator
   penuh (semua butir §8 CLAUDE.md) sebelum dianggap selesai.

## 6. Verifikasi (dilaporkan verbatim di plan)
1. `node --check public/js/document-tabulator.js` → exit 0.
2. `php artisan test` → hijau (jumlah tetap; tak ada test yang bergantung nama berkas JS —
   plan memverifikasi).
3. Grep gerbang (masing-masing harus **KOSONG**):
   - `grep -n "operatorTabulatorTable" public/css/tabulator-agenda.css` → kosong (semua 39 selektor pindah ke `.doc-tabulator`; id elemen hanya hidup di Blade, bukan CSS).
   - `grep -n "operatorTabulatorTable" public/js/document-tabulator.js` → kosong (semua mount-id via `CFG.mountId` + query relatif-elemen).
   - `grep -rn "OPERATOR_TABULATOR_CONFIG\|operator-tabulator\.js" resources/ public/` → kosong (global jadi `DOCUMENT_TABULATOR_CONFIG`, berkas jadi `document-tabulator.js`).
   Catatan: `id="operatorTabulatorTable"` di Blade operator **boleh tetap** (instance-id operator) — itu bukan pelanggaran gerbang.
4. **QA visual operator oleh user** (tabel produksi): sel aktif + panah + gulir ikut,
   klik pindah sel, Enter/dblclick edit, Ctrl+C/V, Delete/Backspace kosongkan, blok
   (drag/Shift), Ctrl+Z/Y, persistensi lebar kolom, kaskade Kriteria, popup "Disalin",
   dropdown Pengurus → forward, Tambah Baris, Hapus baris. Semua **identik** dengan sekarang.

## 7. Deploy
Setelah QA operator lolos: commit per-file → `git push origin codinggemini` → pull server →
`php artisan route:clear && view:clear && config:clear`. `Asset::versioned` mem-bust cache
otomatis; nama berkas JS baru (`document-tabulator.js`) sudah membawa `?v=<mtime>`.

# Desain: Restyle Tabel Tabulator Operator ala CASH_BANK

Tanggal: 2026-07-22
Status: disetujui user (brainstorming interaktif)
Halaman sasaran: `/documents` (route `documents.index`) — role operator (+ admin god-view)

## 1. Latar & Keputusan

User tidak puas dengan tampilan tabel Tabulator operator: header tidak clean (penuh
segitiga sort), font hambar, garis baris terlalu pucat, sel aktif kurang tegas.
Acuan yang disukai: tabel Bank Masuk / Bank Keluar project **CASH_BANK**
(`C:\Users\ASUS\Downloads\CASH_BANK\CASH_BANK`,
`resources/views/cash_bank/table/tableMasuk.blade.php`).

Keputusan yang sudah diambil bersama user:

| Keputusan | Pilihan |
|---|---|
| Sort header | **Dibuang sepenuhnya** — `headerSort: false` global, persis CASH_BANK. Klik header tidak melakukan apa-apa; urutan selalu default server (sesi). |
| Font tabel | **Source Sans Pro 12px** persis CASH_BANK, dimuat via 1 link webfont Google **hanya di view tabulator operator** (bukan layout global). |
| Warna header | **Navy `#0d3b6e`** persis CASH_BANK (bukan teal PTPN `#083E40`). |
| Cakupan | Hanya tabel operator: `tabulator-agenda.css` (tulis ulang total), `operator-tabulator.js` (3 suntingan kecil), `daftarDokumenTabulator.blade.php` (1 baris link font). |
| Tema vendor | **Tetap `tabulator.min.css` default** — TIDAK menukar ke `tabulator_semanticui.min.css`, karena agenda memakai kolom beku + modul SelectRange yang tidak dipakai CASH_BANK dan tak bisa di-QA tanpa browser. |

## 2. Fakta Perbandingan (hasil analisis kedua kodebase)

| Aspek | CASH_BANK (acuan) | Agenda sekarang |
|---|---|---|
| Tabulator | v6.3.1 | v6.3.1 — identik |
| Font tabel | `"Source Sans Pro"` eksplisit, 12px | 13px, jatuh ke Arial (bug: Poppins dituju `layouts/app.blade.php:30` tapi tak pernah dimuat) |
| Header | `#0d3b6e`, pemisah kolom **putih solid 1px**, 11.5px/600, rata tengah | `#083E40`, pemisah `rgba(255,255,255,.12)` nyaris tak terlihat |
| Segitiga sort | `headerSort: false` global | aktif + `sortMode: 'remote'` |
| Garis baris | `1px #c3d2e0` (biru-abu tegas) | `1px #eef2f2` / `#f0f4f4` nyaris putih |
| Baris genap / hover | `#fbfdff` / `#f0f5fb` | `#fafcfc` / `#eef6f5` |
| Sel aktif | mesin tulis-tangan `.bm-active-cell`: outline `2px #1b6fd8` + latar `#e8f1fd` | modul SelectRange bawaan: `.tabulator-range-cell-active` border `2px #2975dd` |
| Padding sel | `6px 8px` | `8px` |

Penting: sel aktif di agenda **bukan** kelas `bm-active-cell` (itu mesin tulis-tangan
CASH_BANK). Agenda memakai modul SelectRange bawaan Tabulator — selektor yang benar:

- Sel aktif: `.tabulator-tableholder .tabulator-range-overlay .tabulator-range-cell-active`
- Bingkai range: `... .tabulator-range` (border `1px #2975dd` bawaan)
- Blok terpilih: `.tabulator-cell.tabulator-range-selected:not(.tabulator-range-only-cell-selected):not(.tabulator-range-row-header)` (bawaan `#9abcea`)
- Header kolom tersorot range: `.tabulator-header .tabulator-col.tabulator-range-highlight` (bawaan `#d6d6d6`) dan `.tabulator-range-selected` (bawaan `#3876ca`)

## 3. Perubahan per Berkas

### 3.1 `public/css/tabulator-agenda.css` — TULIS ULANG TOTAL

Berkas lama (152 baris, hanya dipakai 1 view — aman ditulis ulang) diganti dengan
tema baru ala CASH_BANK, tetap di-scope `#operatorTabulatorTable`:

| Elemen | Nilai baru |
|---|---|
| Kontainer | border `1px solid #d0dce8`, radius 8px, font `"Source Sans Pro", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif`, **12px**, warna teks `#1f2933` |
| Header | latar `#0d3b6e`, border bawah `2px solid #082948`, pemisah kolom `1px solid #ffffff`, judul putih 11.5px/600 **rata tengah**, `white-space: normal` |
| Pegangan resize kolom | garis putih gradasi di batas header (disalin dari CASH_BANK baris 26-31) |
| Scrollbar gutter | `.tabulator-tableholder { scrollbar-gutter: stable; }` (header & isi tetap sejajar) |
| Sel | padding `6px 8px`, border kanan `1px solid #c3d2e0` |
| Baris | border bawah `1px solid #c3d2e0`, genap `#fbfdff`, hover `#f0f5fb` |
| **Sel aktif** | `.tabulator-range-cell-active` → border **3px solid #1b6fd8** (lebih tebal dari bawaan 2px, sesuai permintaan "active cell yang tebal") |
| Bingkai range | `.tabulator-range` → border `1px solid #1b6fd8` |
| Blok terpilih | `.tabulator-range-selected` (sel) → latar **`#dbeafe`** (bawaan `#9abcea` terlalu gelap) |
| Header kolom kena range | `.tabulator-range-highlight` → `#124a85`; `.tabulator-range-selected` (header) → `#1b6fd8`; keduanya teks putih (tidak menabrak header navy) |
| Kolom beku | bayangan `2px 0 4px rgba(13,59,110,.15)`, border kanan `1px solid #c3d2e0` |
| Placeholder kosong | abu `#6b7c7c`, 13px |

**Dipertahankan apa adanya** (disalin dari berkas lama, bukan gaya):
- Blok toolbar `.tabulator-toolbar` (dipakai view; bukan bagian penyakit).
- Blok badge status `.badge-status` + `.badge-terkirim` (makna status, bukan hiasan).
- Aturan sel `nomor_agenda` dua baris + subline `.text-muted`.
- Aturan `tabulator-field="status"` white-space normal.

**Dihapus** (jadi sampah setelah keputusan sort):
- Aturan `.tabulator-arrow` (panah sort).
- Komentar "SKELETON Tugas 4/5".
- `min-height: 38px` baris (padding 6px 8px + font 12px yang menentukan tinggi;
  `variableHeight` kolom nomor_agenda tetap bekerja).

### 3.2 `public/js/operator-tabulator.js` — 3 suntingan kecil

1. Tambah di konstruktor: `columnDefaults: { headerSort: false, resizable: true }`.
2. Hapus `headerSort: false` per-kolom yang jadi mubazir (kolom No baris ~550,
   nomor_agenda ~556, Pengurus Dokumen ~569).
3. Hapus `sortMode: 'remote'` + komentar terkait, dan hapus blok `getSorters()`
   di `getFilterParams()` (baris ~1018-1030) — kode mati begitu sort hilang.
   Parameter `params.sort` / `params.order` tidak lagi dikirim; server
   (`buildOperatorQuery`) sudah punya jalur default/sesi sendiri — endpoint TIDAK diubah.

### 3.3 `resources/views/operator/dokumens/daftarDokumenTabulator.blade.php` — 1 baris

Tambah sebelum link `tabulator-agenda.css`:

```html
<link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;600;700&display=swap" rel="stylesheet">
```

## 4. Yang SENGAJA Tidak Disentuh

- `layouts/app.blade.php` — god-file global; bug Poppins-tak-dimuat adalah keputusan
  terpisah, di luar lingkup.
- Tema vendor `public/vendor/tabulator/tabulator.min.css` — tidak ditukar/diedit.
- Seluruh logika §8 Tahap A/B (sel aktif, blok, Ctrl+C/V, Delete, undo/redo) —
  hanya rupa yang berubah, nol perubahan perilaku selain hilangnya sort header.
- Backend: endpoint `/documents/data`, `buildOperatorQuery` — dukungan `sort`/`order`
  server-side dibiarkan ada (harmless; klien saja yang berhenti mengirimnya).
- Warna badge status & formatter paritas.

## 5. Konsekuensi yang Disadari User

- **Fitur sort header hilang** dari UI (fitur commit `0675c23`). Urutan tabel selalu
  default server. Jika kelak ingin sort lagi, jalurnya menghidupkan kembali
  `headerSort` per-kolom + `sortMode: 'remote'` (kode server masih ada).
- Header navy `#0d3b6e` sementara logo/sidebar aplikasi bernuansa hijau — user
  memilih ini secara sadar.
- +1 request webfont Google di halaman tabel operator.

## 6. Pengujian & Batas Verifikasi

- `php artisan test` harus hijau (ada `OperatorTabulatorViewTest`; tak ada test yang
  mengunci gaya CSS, tapi test view memastikan halaman tetap ter-render).
- Agent TIDAK punya browser: paritas rupa (ketebalan 3px, ukuran 12px, kontras navy)
  **wajib QA visual user** di `/documents` setelah deploy + clear cache.
- Titik periksa QA user: (1) header bersih tanpa segitiga & pemisah putih terlihat,
  (2) font Source Sans Pro terpasang (bandingkan dengan bank-masuk CASH_BANK),
  (3) sel aktif berbingkai biru tebal, (4) blok seleksi biru muda `#dbeafe`,
  (5) garis antarbaris terlihat tegas, (6) kolom beku masih menempel & berbayang,
  (7) badge status & subline nomor agenda tak berubah, (8) inline edit + Ctrl+C/V/Z
  masih berfungsi.

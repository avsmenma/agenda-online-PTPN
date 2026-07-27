# Desain: Satukan Kartu Informasi Bagian ke Kartu Keuangan (partial bersama)

- **Tanggal:** 2026-07-27
- **Status:** Disetujui user (2026-07-27)
- **Cakupan:** Kartu informasi role `bagian` disamakan dengan kartu informasi role keuangan
  (`dashboard.workflow`) lewat SATU partial bersama. Menyentuh view keuangan lintas-4-role
  (gerbang kritis CLAUDE.md §6) + view god-file bagian (86 KB).

---

## 1. Masalah

Kartu informasi (stat card) diimplementasikan **terpisah** per kelompok role:

- **Keuangan** (pembayaran/akutansi/perpajakan/verifikasi) → `resources/views/dashboard/workflow.blade.php`,
  kelas `.wd-cards`/`.wd-card` (4 kartu), markup baris 54-113, CSS baris 218-241.
- **Bagian** (8 departemen, semua route ke `/bagian/documents`) →
  `resources/views/bagian/dokumens/daftarDokumen.blade.php`, kelas `.bagian-info-cards`/`.bic-card`
  (3 kartu), **bespoke** — komentar kodenya sendiri menyebut *"terinspirasi kartu owner, dibuat sendiri"*
  (CSS baris 961-1017, markup 1021-1047).

Ini duplikasi lintas-role (penyakit utama §1 CLAUDE.md). User ingin kartu bagian **disamakan**
dengan kartu keuangan dan **kode kartu lama (`.bic-*`) dihapus bersih tanpa sisa**.

## 2. Keputusan desain (disetujui)

1. **Pendekatan A — partial bersama** (bukan salin). Ekstrak kartu keuangan `.wd-card`
   (markup + CSS) jadi `resources/views/partials/_infoCards.blade.php`, dipakai `workflow.blade.php`
   **dan** view bagian. Satu sumber kartu; benar-benar seragam.
2. **Pertahankan klik-filter + sorotan aktif** di bagian: kartu bagian tetap tautan filter
   `?status=` dengan penanda kartu yang sedang aktif — hanya tampilannya jadi gaya keuangan.
3. **Hasil visual:** kartu bagian identik kartu keuangan — **putih `#fff`**, border `#e8ecf4`,
   radius 14px, bayangan halus, label uppercase kecil di atas → angka besar → sub kecil,
   ikon SVG di badge pojok kanan-atas. Satu-satunya beda: kartu aktif (bagian) dapat cincin sorotan.

## 3. Arsitektur

### 3.1 Partial baru `resources/views/partials/_infoCards.blade.php`
Kontrak: `@include('partials._infoCards', ['cards' => [ ...definisi kartu... ]])`.
Tiap elemen `$cards`:

| key | tipe | wajib | keterangan |
|---|---|---|---|
| `label` | string | ya | teks label kecil di atas |
| `value` | int | ya | angka; partial memformat `number_format($v,0,',','.')` |
| `sub` | string | ya | sub-teks kecil |
| `icon` | string (SVG) | ya | markup `<svg>…</svg>`, dirender via `{!! !!}` |
| `iconBg` | string (warna) | ya | background badge ikon |
| `valueColor` | string | tidak | override warna angka (default `#1a2340`) |
| `href` | string (URL) | tidak | ada → `<a href>`; tak ada → `<div>` |
| `active` | bool | tidak | true → tambah kelas `.wd-card--active` (cincin sorotan) |

Partial merender: container `.wd-cards` (grid) + satu `.wd-card` per item, **markup identik**
kartu keuangan sekarang. Membawa CSS `.wd-card*` (dipindah dari workflow) + tambahan kecil
`.wd-card--active`. Grid menyesuaikan jumlah kartu: `grid-template-columns: repeat(N, minmax(0,1fr))`
via kelas modifier (`.wd-cards--cols-3`/`--cols-4`) agar breakpoint responsif (media query 1100px→2,
560px→1) TETAP berlaku (hindari inline-style yang mematikan media query). CSS `.wd-card--active`:
`box-shadow: 0 0 0 2px #083E40, <shadow default>` — HANYA aktif bila ada kelas; kartu keuangan
tak pernah mengirim `active`, jadi **tak terpengaruh**.

### 3.2 `dashboard/workflow.blade.php` (4 role keuangan) — refaktor behavior-preserving
- Blok `@php` (baris 38-53) yang menghitung default kartu **tetap**.
- Markup kartu inline (54-113) → dibangun jadi array `$cards` (4 kartu, memetakan variabel yang
  SUDAH ada: `$totalDokumenAgenda`, `$card2Label/Value/Sub`, `$card3*`, `$fourth*`, href
  `route($cfg['docRouteName'])` dst — persis seperti sekarang), lalu `@include('partials._infoCards', …)`.
  Kartu ke-4 tanpa `href` (tetap `<div>`, sama seperti sekarang).
- CSS `.wd-card*` (218-241) + media query yang menyebut `.wd-cards` (300-301) **dipindah** ke partial.
  Sisa `<style>` workflow (chart/panel/bubble) tetap. **Output HTML & visual keuangan wajib IDENTIK.**

### 3.3 `bagian/dokumens/daftarDokumen.blade.php` — pakai partial, hapus bic-card
- Markup `.bic-card` (1021-1047) → `@include('partials._infoCards', ['cards' => [3 kartu]])`.
  Tiga kartu memetakan data controller yang SUDAH ada (`$totalDokumen`, `$totalBelumDibayar`,
  `$totalSudahDibayar`, `$bagianCode`):
  1. **Total {bagianCode}** — `href` `route('bagian.documents.index')`, `active = !request('status')`.
  2. **Belum Dibayar** — `href` `route('bagian.documents.index', ['status'=>'belum_dibayar'])`,
     `active = request('status')=='belum_dibayar'`, `valueColor` oranye (paritas warna lama).
  3. **Sudah Dibayar** — `href` `…['status'=>'sudah_dibayar']`,
     `active = request('status')=='sudah_dibayar'`, `valueColor` hijau.
  Ikon FA (`fa-folder-open`/`fa-hourglass-half`/`fa-circle-check`) → SVG gaya keuangan setara.
- **HAPUS TOTAL**: CSS `.bagian-info-cards`/`.bic-*` (961-1017) + markup lama (1021-1047).
  Nol sisa (grep-gate `bic-|bagian-info-cards` = nol hit setelahnya).

## 4. Yang TIDAK berubah (jaring pengaman)
- Controller (`DashboardPembayaran/Akutansi/Perpajakan/TeamVerifikasi::dashboard`,
  `BagianDokumenController::index`) — kontrak data tak disentuh; partial hanya mengonsumsi variabel
  yang sudah dikirim.
- Route, filter tabel bagian (`?status=`), inline-edit, dsb.
- Kartu owner (`owner.home`) & programmer — di luar cakupan (tak diminta disatukan).

## 5. Rencana pengujian
- **Backend:** `php artisan test` hijau (perubahan murni view; kontrak data tak berubah).
- **QA visual (Playwright + jujur):**
  - Keuangan: login **pembayaran** & **verifikasi** (keduanya render `workflow`) → kartu identik
    sebelum/sesudah, tanpa regresi (screenshot banding). akutansi/perpajakan pakai view yang SAMA
    (beda data saja) → paritas keyakinan-tinggi; dinyatakan jujur bila tak bisa login keduanya.
  - Bagian: login bagian (minta kredensial saat QA) → kartu baru putih tampil, klik tiap kartu
    memfilter tabel, kartu aktif dapat sorotan.
- **Grep-gate nol sisa:** `bic-|bagian-info-cards` = nol hit di `resources/`.

## 6. Risiko
- **Lintas-role (gerbang kritis §6):** `workflow.blade.php` dipakai 4 role keuangan. Dimitigasi:
  ekstraksi behavior-preserving (markup+CSS dipindah apa adanya), `active` bersifat aditif
  (keuangan tak memakainya), re-QA browser pasca-ubah.
- **God-file bagian (86 KB):** hanya blok kartu yang disentuh (hapus CSS+markup, sisip 1 `@include`).
- **`@vite` mati:** partial memakai `<style>` inline (konsisten pola view sekarang); ini MEMINDAH
  CSS (net berkurang, karena `.bic-*` dihapus), bukan menambah utang baru.

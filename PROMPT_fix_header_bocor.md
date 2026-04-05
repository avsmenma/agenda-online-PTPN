# TASK: Fix Header Tabel — Data Bocor di Atas Header

## MASALAH

Header tabel (`No`, `Nomor Agenda`, dst) sudah sticky, **tapi data baris tetap terlihat di atas header** saat di-scroll. Ini menciptakan kesan ada "kolong" atau celah aneh di atas header.

Lihat screenshot: baris `2156_2026` masih terlihat DI ATAS baris header teal.

---

## ROOT CAUSE

Ini terjadi karena **scroll container yang salah**.

`position: sticky` bekerja relatif terhadap **scroll ancestor** terdekat yang punya `overflow: auto/scroll`.
Jika scroll terjadi di `window`/`body` (bukan di wrapper khusus), maka `thead th { top: 0 }` berarti
header menempel di top viewport — tapi baris-baris di atas yang sudah di-scroll tetap "melayang"
karena tidak ada clipping yang memotong mereka.

**Solusi: Pindahkan scroll ke wrapper tabel, bukan di `body`/`window`.**

---

## FIX

### Opsi A — Scroll di Wrapper Tabel (DIREKOMENDASIKAN)

Bungkus tabel dalam sebuah `div` wrapper dengan height terbatas dan `overflow-y: auto`.
Header sticky akan bekerja sempurna di dalam wrapper ini.

```html
{{-- Blade: ganti struktur wrapper tabel --}}
<div class="table-scroll-wrapper">
    <table>
        <thead> ... </thead>
        <tbody> ... </tbody>
    </table>
</div>
```

```css
.table-scroll-wrapper {
    overflow-x: auto;
    overflow-y: auto;

    /*
     * Hitung tinggi: viewport dikurangi semua elemen di atas tabel
     * (navbar ~52px, baris-per-halaman ~40px, search/filter ~50px, hint bar ~36px, margin ~20px)
     * Sesuaikan angka ini dengan layout aktual
     */
    max-height: calc(100vh - 220px);

    position: relative; /* penting untuk sticky child */

    /* Sembunyikan konten yang keluar batas atas/bawah */
    /* overflow sudah auto, jadi ini sudah otomatis ter-clip */
}

/* Header sticky di dalam wrapper */
.table-scroll-wrapper thead th {
    position: sticky;
    top: 0;
    z-index: 20;
    background: #0d6b5e; /* warna teal PTPN */
}
```

> ✅ Dengan cara ini, konten tabel di-clip oleh wrapper — tidak ada baris yang bisa "bocor" ke atas header.

---

### Opsi B — Jika Scroll di Body/Window (Tidak Direkomendasikan, tapi bisa)

Jika layout mengharuskan scroll di body, gunakan pendekatan ini:

```css
thead th {
    position: sticky;
    top: 0px; /* sesuaikan dengan tinggi elemen di atas tabel yang fixed/sticky */
    z-index: 20;
    background: #0d6b5e;

    /*
     * WAJIB: clip konten yang bocor ke atas dengan box-shadow atau border-top
     * agar baris di atas tidak "tembus pandang" lewat header
     */
    box-shadow: 0 2px 4px rgba(0,0,0,0.15);
}

/* Pastikan tidak ada baris yang memiliki z-index lebih tinggi dari header */
tbody tr { position: relative; z-index: 1; }
tbody tr td.cell-focused  { z-index: 2; }
tbody tr td.cell-editing  { z-index: 3; }
/* Header HARUS di atas semua: z-index: 20 */
```

---

## CEK JIKA MASIH BERMASALAH

Jika setelah fix masih ada data bocor di atas header, kemungkinan ada ancestor dengan `overflow: hidden` atau `transform` yang memutus sticky context. Cek dengan DevTools:

1. Inspect `<thead>` → lihat computed `position` → harus `sticky`
2. Inspect semua ancestor dari `<table>` ke atas → pastikan tidak ada yang punya:
   - `overflow: hidden`
   - `overflow: clip`
   - `transform` (selain `none`)
   - `filter` (selain `none`)
   - `will-change: transform`
   → Semua itu bisa memutus sticky behavior

---

## YANG TIDAK BOLEH DIUBAH

- Warna, teks, dan urutan kolom header
- Logika keyboard navigation & inline editing yang sudah ada
- Fitur filter, pagination, tombol Kirim/Edit, status bar bawah

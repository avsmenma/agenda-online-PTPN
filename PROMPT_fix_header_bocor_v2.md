# TASK: Fix Definitif Header Bocor — Data Masih Terlihat di Atas Header

## STATUS

Fix flex-based layout sudah diterapkan tapi baris `2156_2026` masih bocor di atas header.
Artinya sticky bekerja (header tidak ikut scroll), tapi **konten di atas tidak ter-clip**.

---

## ROOT CAUSE SEBENARNYA

Ada **2 kemungkinan penyebab** yang harus dicek, pilih yang sesuai:

### Kemungkinan A — `overflow: hidden` pada parent memutus sticky

Dari summary AI sebelumnya, `.table-dokumen` diberi `overflow: hidden`.
**Ini adalah kesalahan klasik:** `overflow: hidden` pada ancestor langsung dari scroll container
bisa memutus `position: sticky` pada `<thead>`.

> Rule: `position: sticky` hanya bekerja jika **tidak ada ancestor antara elemen sticky
> dan scroll container-nya yang punya `overflow` selain `visible`**.

Solusi: ganti `overflow: hidden` pada `.table-dokumen` menjadi `overflow: clip`:

```css
/* SEBELUM (bermasalah): */
.table-dokumen {
    flex: 1;
    overflow: hidden;   /* ← ini memutus sticky context */
    display: flex;
    flex-direction: column;
}

/* SESUDAH (fix): */
.table-dokumen {
    flex: 1;
    overflow: clip;     /* ← clip tidak memutus sticky, tapi tetap memotong konten */
    display: flex;
    flex-direction: column;
    min-height: 0;      /* penting untuk flex child agar tidak overflow */
}
```

### Kemungkinan B — Background `<thead>` transparan

`position: sticky` bekerja tapi `<thead>` tidak menutup baris di belakangnya
karena background-nya transparan atau tidak solid.

```css
thead {
    position: sticky;
    top: 0;
    z-index: 20;
}

thead th {
    background-color: #0d6b5e !important;  /* warna teal PTPN — wajib solid, bukan transparan */
    /* Pastikan tidak ada rgba/transparent di sini */
}
```

---

## FIX YANG HARUS DITERAPKAN (LAKUKAN KEDUANYA)

### Step 1 — Ganti `overflow: hidden` → `overflow: clip` di semua ancestor tabel

Cek semua elemen antara `<table>` dan `.table-responsive` (scroll container):

```css
/* Semua wrapper/ancestor tabel yang sebelumnya overflow: hidden → ganti ke overflow: clip */
.table-dokumen,
.card,
.card-body,
[class*="table-container"] {
    overflow: clip !important;   /* sementara pakai !important untuk debug */
    min-height: 0;
}

/* Hanya .table-responsive yang boleh overflow: auto (ini scroll container-nya) */
.table-responsive {
    overflow-x: auto !important;
    overflow-y: auto !important;
    flex: 1;
    min-height: 0;
}
```

### Step 2 — Pastikan thead solid dan z-index benar

```css
.table-responsive thead th {
    position: sticky;
    top: 0;
    z-index: 20;
    background-color: #0d6b5e !important;  /* solid, tidak boleh transparan */
    box-shadow: 0 1px 0 #0d6b5e;           /* tutup gap 1px yang sering muncul di Chrome */
}

/* Pastikan tbody rows di bawah z-index header */
.table-responsive tbody tr {
    position: relative;
    z-index: 1;
}
```

### Step 3 — Verifikasi struktur flex dari atas ke bawah

```
body                    → overflow: hidden; height: 100vh
  .topbar/navbar        → flex-shrink: 0; height: 52px (atau sesuai aktual)
  .content-wrapper      → display: flex; flex-direction: column; height: calc(100vh - 52px); overflow: hidden
    .ss-toolbar         → flex-shrink: 0  (search, filter, tombol)
    .ss-hints           → flex-shrink: 0  (keyboard hints)
    .table-dokumen      → flex: 1; display: flex; flex-direction: column; overflow: clip; min-height: 0
      .table-responsive → flex: 1; overflow-y: auto; min-height: 0   ← SATU-SATUNYA yang scroll
        table           → border-collapse: collapse; width: 100%
          thead         → (sticky di sini) 
          tbody         → (rows ada di sini, ter-clip oleh .table-responsive)
    .pagination         → flex-shrink: 0
```

> **Kunci:** Hanya `.table-responsive` yang boleh punya `overflow: auto/scroll`.
> Semua ancestor lain: gunakan `overflow: clip` atau `overflow: visible`.
> `overflow: hidden` di ancestor manapun = sticky rusak.

---

## DEBUG CEPAT DI BROWSER (SEBELUM EDIT KODE)

Buka DevTools → Console, jalankan ini untuk cek apakah sticky aktif:

```js
// Cek apakah thead sticky bekerja
const th = document.querySelector('thead th');
console.log('sticky?', getComputedStyle(th).position); // harus: "sticky"

// Cek scroll container aktual
let el = th.parentElement;
while (el) {
    const style = getComputedStyle(el);
    const overflow = style.overflow + ' / ' + style.overflowY;
    if (overflow.includes('auto') || overflow.includes('scroll')) {
        console.log('SCROLL CONTAINER:', el, overflow);
        break;
    }
    el = el.parentElement;
}

// Cek ancestor yang punya overflow: hidden (pemutus sticky)
el = th.parentElement;
while (el && el !== document.body) {
    const oy = getComputedStyle(el).overflowY;
    if (oy === 'hidden') {
        console.warn('PEMUTUS STICKY DITEMUKAN:', el, el.className);
    }
    el = el.parentElement;
}
```

Output console akan langsung menunjukkan elemen mana yang bermasalah.

---

## YANG TIDAK BOLEH DIUBAH

- Warna dan konten header tabel
- Logika keyboard navigation & inline editing
- Fitur filter, pagination, tombol Kirim/Edit
- Struktur HTML tabel (hanya ubah CSS)

# TASK: Perbaikan Header Tabel — Spreadsheet Mode

## MASALAH

Header tabel (`No`, `Nomor Agenda`, `Nomor SPP`, `Tanggal Masuk`, `Nilai Rupiah`, `Status`, `Aksi`) mengalami 2 bug visual:

### Bug A — Header Tertutup / Hilang Saat Sel Edit Mode (lihat Image 1)
Ketika user mengedit sel di baris atas (terutama baris 1-2), elemen input yang muncul
**mendorong atau menutupi baris header** sehingga header tidak terlihat.
Ini terjadi karena `z-index` sel yang sedang diedit lebih tinggi dari header,
atau karena header tidak benar-benar `sticky`.

### Bug B — Header Tidak Sticky Saat Scroll (lihat Image 2)
Saat user scroll ke bawah, header ikut menghilang ke atas.
Header seharusnya **selalu terlihat di atas tabel** meski konten di-scroll.

---

## FIX YANG DIBUTUHKAN

### 1. Jadikan Header Benar-Benar Sticky di Atas Scroll Container

```css
/* Pastikan wrapper tabel punya overflow-y dan height terbatas */
.spreadsheet-wrapper {
    overflow-x: auto;
    overflow-y: auto;
    max-height: calc(100vh - 220px); /* sesuaikan dengan tinggi topbar + toolbar */
    position: relative;
}

/* Header sticky */
thead th {
    position: sticky;
    top: 0;
    z-index: 20;          /* ← lebih tinggi dari z-index sel biasa maupun sel editing */
    background: #0d6b5e;  /* warna teal PTPN — sesuaikan dengan yang sudah ada */
}
```

> ⚠️ `position: sticky` pada `<th>` hanya bekerja jika **ancestor element** (parent/grandparent)
> punya `overflow: auto` atau `overflow: scroll`, BUKAN `overflow: visible`.
> Pastikan tidak ada ancestor yang punya `overflow: hidden` karena itu akan menonaktifkan sticky.

### 2. Atur z-index Hierarki dengan Benar

```css
/* Urutan z-index dari bawah ke atas: */

td                  { z-index: 1; }   /* sel biasa */
td.cell-focused     { z-index: 2; }   /* sel terpilih */
td.cell-editing     { z-index: 3; }   /* sel sedang diedit */
thead th            { z-index: 20; }  /* header — HARUS di atas segalanya */
.statusbar          { z-index: 30; }  /* status bar bawah */
```

Sel yang sedang diedit (`cell-editing`) boleh punya `z-index` tinggi,
tapi **tidak boleh melebihi z-index header `<thead>`**.

### 3. Pastikan Input dalam Sel Tidak Overflow ke Luar Sel

Saat sel masuk edit mode, input element harus terkurung di dalam sel:

```css
td.cell-editing {
    position: relative;   /* ← penting sebagai containing block */
    overflow: visible;    /* boleh visible tapi input jangan keluar batas atas */
    padding: 0;
}

td.cell-editing input,
td.cell-editing select {
    position: relative;
    z-index: 3;           /* di bawah header */
    width: 100%;
    min-height: 100%;
    box-sizing: border-box;
    /* JANGAN pakai position: absolute tanpa top/left yang benar */
}
```

### 4. Scroll Otomatis ke Sel Aktif (Bonus Fix)

Saat user navigasi ke sel yang tersembunyi di balik header sticky,
pastikan scroll memperhitungkan tinggi header:

```js
function scrollToFocusedCell(td) {
    if (!td) return;
    const wrapper   = document.querySelector('.spreadsheet-wrapper');
    const headerH   = document.querySelector('thead').offsetHeight;
    const tdTop     = td.getBoundingClientRect().top - wrapper.getBoundingClientRect().top;
    const tdBottom  = td.getBoundingClientRect().bottom - wrapper.getBoundingClientRect().top;

    if (tdTop < headerH) {
        // Sel tersembunyi di balik header → scroll ke atas
        wrapper.scrollTop -= (headerH - tdTop + 4);
    } else if (tdBottom > wrapper.clientHeight) {
        // Sel di bawah area visible → scroll ke bawah
        wrapper.scrollTop += (tdBottom - wrapper.clientHeight + 4);
    }
}

// Panggil ini di dalam setFocus() setelah highlight sel
```

---

## CHECKLIST

- [ ] Header selalu terlihat saat scroll ke bawah
- [ ] Header tidak tertutup saat sel baris pertama/kedua masuk edit mode
- [ ] z-index header lebih tinggi dari z-index sel editing
- [ ] Scroll container punya `overflow-y: auto` dan `max-height` yang sesuai
- [ ] Tidak ada ancestor dengan `overflow: hidden` yang memblokir sticky
- [ ] Saat navigasi keyboard, sel aktif tidak bersembunyi di balik header

## YANG TIDAK BOLEH DIUBAH

- Warna, teks, dan urutan kolom header
- Logika keyboard navigation & inline editing yang sudah ada
- Fitur filter, pagination, tombol Kirim/Edit

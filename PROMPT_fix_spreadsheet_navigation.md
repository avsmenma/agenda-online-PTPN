# TASK: Perbaikan UX Spreadsheet Mode — Navigation & Selection Behavior

## KONTEKS

Halaman daftar dokumen untuk role Operator sudah berhasil diubah menjadi spreadsheet mode.
Namun terdapat **3 bug UX kritis** yang membuat pengalaman tidak semirip spreadsheet asli.
Perbaiki ketiga bug ini tanpa mengubah fitur lain yang sudah berjalan.

> **Referensi perilaku yang benar:** lihat file `agenda-spreadsheet-mode.html` yang dilampirkan.
> Itu adalah simulasi kerja yang sudah benar — jadikan acuan untuk logika keyboard & state.

---

## BUG YANG HARUS DIPERBAIKI

---

### BUG #1 — Tidak Ada Initial Selection Saat Halaman Dibuka

**Kondisi saat ini:**
Ketika halaman pertama kali dibuka atau di-refresh, tidak ada sel yang ter-highlight/terpilih.
User harus klik dulu dengan mouse sebelum bisa navigasi keyboard.

**Perilaku yang diinginkan (seperti Excel/Google Sheets):**
- Begitu halaman selesai load, **sel pertama (baris 1, kolom pertama yang bisa diedit) langsung aktif** — ada visual highlight/border
- User bisa **langsung menekan Arrow Key** tanpa klik mouse sama sekali
- Fokus keyboard harus di-handle oleh grid, bukan oleh elemen browser default

**Cara fix:**
```js
// Setelah DOM ready / setelah render tabel selesai:
document.addEventListener('DOMContentLoaded', () => {
    // Set focusR = 0, focusC = 0, lalu panggil setFocus()
    // Pastikan tabel/grid mendapat tabindex="0" atau ada elemen
    // yang bisa menerima keyboard event tanpa harus diklik dulu
    gridContainer.focus(); // atau elemen wrapper tabel
    setFocus(0, 0);
});
```

Pastikan:
- Grid wrapper punya `tabindex="0"` sehingga bisa menerima keyboard event
- `keydown` listener dipasang di grid wrapper atau `document`, bukan di elemen input saja
- Saat halaman load, `focusR = 0` dan `focusC = 0` langsung diset dan `setFocus()` dipanggil

---

### BUG #2 — Setelah Enter/Navigasi, Sel Tujuan Langsung Masuk Edit Mode

**Kondisi saat ini:**
1. User fokus di sel → tekan `Enter` untuk mulai edit → ubah nilai → tekan `Enter` lagi untuk simpan
2. Setelah simpan, selection pindah ke baris bawah ✅ (benar)
3. **MASALAH:** Sel baris bawah langsung masuk edit mode (input muncul) secara otomatis ❌

**Perilaku yang diinginkan (seperti Excel/Google Sheets):**
- Setelah `Enter` menyimpan dan berpindah → sel baru hanya **terpilih/terhighlight saja**, TIDAK langsung edit mode
- User harus tekan `F2`, `Enter`, atau **mulai ketik karakter** untuk masuk edit mode di sel baru
- Navigasi (Arrow, Enter, Tab) = **hanya berpindah selection**, tidak trigger edit mode

**Root cause yang perlu dicek:**
```js
// SALAH — ini yang kemungkinan terjadi:
function commitEdit() {
    // ... simpan nilai ...
    moveDown();
    startEdit(); // ← INI YANG SALAH, jangan panggil startEdit() setelah navigasi
}

// BENAR:
function commitEdit() {
    // ... simpan nilai ...
    editingCell = null;
    isEditing = false;
    moveDown();      // hanya pindah fokus
    setFocus();      // highlight sel baru, tapi TIDAK masuk edit mode
    // TIDAK ada pemanggilan startEdit() di sini
}
```

Pastikan state `isEditing` atau `editingCell` di-reset ke `false`/`null` SEBELUM navigasi,
dan fungsi `setFocus()` / `moveFocus()` tidak memanggil `startEdit()` di dalamnya.

---

### BUG #3 — Harus Tekan Enter 2x untuk Edit + Simpan (Seharusnya 1x Saja)

**Kondisi saat ini:**
- Tekan `Enter` pertama → masuk edit mode
- Edit nilai
- Tekan `Enter` kedua → simpan + pindah ke bawah

Total: **2x Enter** untuk 1 operasi edit.

**Perilaku yang diinginkan:**

Ada **2 mode** yang harus dibedakan:

#### Mode A — Sel Sedang TIDAK Diedit (Navigation Mode)
| Tombol | Aksi |
|--------|------|
| `Arrow ↑↓←→` | Pindah selection |
| `Enter` | **Masuk edit mode** pada sel aktif (bukan simpan) |
| `F2` | Masuk edit mode (cursor di akhir teks) |
| Ketik karakter apapun | Masuk edit mode + karakter langsung jadi input pertama (replace isi lama) |
| `Delete` | Kosongkan sel aktif langsung (tanpa masuk edit mode) |

#### Mode B — Sel Sedang DIEDIT (Edit Mode)
| Tombol | Aksi |
|--------|------|
| `Enter` | **Simpan + pindah ke bawah** (keluar edit mode) |
| `Tab` | **Simpan + pindah ke kanan** (keluar edit mode) |
| `Shift+Tab` | **Simpan + pindah ke kiri** (keluar edit mode) |
| `Arrow ↑` | **Simpan + pindah ke atas** (untuk input text biasa, bukan textarea) |
| `Arrow ↓` | **Simpan + pindah ke bawah** (untuk input text biasa) |
| `Arrow ←` / `Arrow →` | Gerakkan cursor dalam teks (jangan navigasi sel) |
| `Esc` | Batalkan edit, kembalikan nilai semula, keluar edit mode |

> ⚠️ Khusus `Arrow ↑` dan `Arrow ↓` saat edit mode:
> Untuk elemen `<input type="text">` dan `<input type="number">`, arrow atas/bawah tidak digunakan untuk navigasi dalam teks, sehingga aman di-capture untuk simpan+pindah.
> Untuk `<select>`, jangan capture arrow — biarkan browser default.
> Untuk `<textarea>` (jika ada), jangan capture arrow — biarkan browser default.

**Implementasi state machine yang benar:**

```js
// State
let focusR = 0, focusC = 0;
let isEditing = false;   // ← flag penting!
let editOriginalValue = null;

// Handler utama
document.addEventListener('keydown', (e) => {
    if (isEditing) {
        handleEditModeKey(e);
    } else {
        handleNavigationModeKey(e);
    }
});

function handleNavigationModeKey(e) {
    switch(e.key) {
        case 'ArrowUp':    e.preventDefault(); moveFocus(-1, 0); break;
        case 'ArrowDown':  e.preventDefault(); moveFocus(1, 0);  break;
        case 'ArrowLeft':  e.preventDefault(); moveFocus(0, -1); break;
        case 'ArrowRight': e.preventDefault(); moveFocus(0, 1);  break;
        case 'Enter':      e.preventDefault(); startEdit();       break;
        case 'F2':         e.preventDefault(); startEdit(true);   break; // true = cursor di akhir, tidak replace
        case 'Delete':
        case 'Backspace':  e.preventDefault(); clearCell();       break;
        case 'Tab':
            e.preventDefault();
            e.shiftKey ? moveFocus(0, -1) : moveFocus(0, 1);
            break;
        default:
            // Karakter printable → masuk edit mode, replace isi
            if (e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey) {
                startEdit(false, e.key); // false = replace mode, seed dengan karakter ini
                e.preventDefault();
            }
    }
}

function handleEditModeKey(e) {
    const tag = document.activeElement?.tagName;
    switch(e.key) {
        case 'Enter':
            e.preventDefault();
            commitEdit();
            moveFocus(1, 0);  // pindah ke bawah
            break;
        case 'Tab':
            e.preventDefault();
            commitEdit();
            e.shiftKey ? moveFocus(0, -1) : moveFocus(0, 1);
            break;
        case 'Escape':
            e.preventDefault();
            cancelEdit();
            break;
        case 'ArrowUp':
            if (tag === 'INPUT') {
                e.preventDefault();
                commitEdit();
                moveFocus(-1, 0);
            }
            // jika SELECT atau TEXTAREA: biarkan default browser
            break;
        case 'ArrowDown':
            if (tag === 'INPUT') {
                e.preventDefault();
                commitEdit();
                moveFocus(1, 0);
            }
            break;
        // ArrowLeft & ArrowRight: JANGAN capture, biarkan cursor bergerak dalam teks
    }
}

function moveFocus(dr, dc) {
    // isEditing sudah pasti false saat fungsi ini dipanggil
    // (commitEdit/cancelEdit harus set isEditing = false sebelum moveFocus)
    const newR = Math.max(0, Math.min(totalRows - 1, focusR + dr));
    const newC = Math.max(0, Math.min(totalCols - 1, focusC + dc));
    
    // Auto-add row jika ke bawah dari baris terakhir
    if (dr === 1 && focusR === totalRows - 1) {
        addNewRow();
        return;
    }
    
    focusR = newR;
    focusC = newC;
    setFocus(); // highlight saja, TIDAK startEdit()
}

function startEdit(cursorAtEnd = false, seedChar = null) {
    isEditing = true;
    editOriginalValue = getCurrentCellValue();
    // ... render input element ...
    // Jika seedChar: set input.value = seedChar (replace mode)
    // Jika cursorAtEnd: set input.value = originalValue, cursor di akhir
}

function commitEdit() {
    if (!isEditing) return;
    isEditing = false;          // ← reset dulu sebelum apapun
    const newValue = getInputValue();
    saveToServer(newValue);     // AJAX, bisa async
    updateCellDisplay(newValue);
    // TIDAK memanggil startEdit() atau moveFocus() di sini
}

function cancelEdit() {
    if (!isEditing) return;
    isEditing = false;
    restoreCellValue(editOriginalValue);
    setFocus(); // kembalikan highlight tanpa edit mode
}
```

---

## KOLOM YANG BISA DIEDIT INLINE

Sesuaikan dengan field yang ada di tabel dokumen sistem. Minimal:

| Kolom | Tipe Input | Catatan |
|-------|-----------|---------|
| Nomor Agenda | `text` | Bisa ada tombol Auto |
| Nomor SPP | `text` | |
| Bagian | `select` | Dropdown dari data master |
| Kebun | `select` | Dropdown dari data master |
| Nama Pengirim | `text` | |
| Jenis Pembayaran | `text` atau `select` | |
| Nilai Rupiah | `number` | Format Rp saat display, number saat edit |
| Kriteria CF | `select` | Trigger fetch Sub Kriteria |
| Sub Kriteria | `select` | Dependent pada Kriteria CF |
| Item Sub Kriteria | `select` | Dependent pada Sub Kriteria |
| Tanggal SPP | `text` | Format `dd/mm/yyyy hh:mm` |

**Read-only (tidak diedit inline):**
- No (auto)
- Tanggal Masuk (auto `created_at`)
- Status (hanya via tombol Kirim)

---

## VISUAL REQUIREMENTS

### Selection Highlight (Navigation Mode)
```css
td.cell-focused {
    outline: 2px solid #0d6b5e;   /* warna teal PTPN */
    outline-offset: -2px;
    background-color: #fffdf5 !important;
    position: relative;
    z-index: 2;
}
/* Titik resize di pojok kanan bawah seperti Google Sheets */
td.cell-focused::after {
    content: '';
    position: absolute;
    right: -3px; bottom: -3px;
    width: 6px; height: 6px;
    background: #0d6b5e;
    border-radius: 1px;
    z-index: 3;
}
```

### Edit Mode
```css
td.cell-editing {
    outline: 2px solid #f5a623;   /* kuning/oranye saat edit */
    outline-offset: -2px;
    background: #ffffff !important;
    padding: 0;
}
td.cell-editing input,
td.cell-editing select {
    width: 100%;
    height: 100%;
    border: none;
    outline: none;
    background: transparent;
    font: inherit;
    padding: 0 6px;
}
```

### Status Bar Bawah
Tampilkan di footer halaman (sudah ada atau buat baru):
- Posisi sel aktif: `A1`, `B3`, dst
- Mode saat ini: `Mode: Siap` / `Mode: Edit`
- Status simpan: `✔ Tersimpan` / `● N perubahan belum disimpan`

---

## CHECKLIST PERBAIKAN

Pastikan semua poin ini terpenuhi sebelum selesai:

- [ ] Saat halaman load → sel pertama (baris 1, kolom 1) langsung ter-highlight tanpa klik
- [ ] Arrow key berfungsi dari awal tanpa klik mouse
- [ ] `Enter` di navigation mode → masuk edit mode
- [ ] `Enter` di edit mode → simpan + pindah bawah (TIDAK langsung edit baris bawah)
- [ ] `Tab` di edit mode → simpan + pindah kanan
- [ ] `Arrow ↑↓` di edit mode (input text) → simpan + pindah atas/bawah
- [ ] `Esc` → batalkan edit, nilai kembali seperti semula
- [ ] `F2` → masuk edit mode, cursor di akhir teks (tidak replace)
- [ ] Ketik karakter di navigation mode → masuk edit, karakter jadi input pertama
- [ ] `Delete` di navigation mode → kosongkan sel tanpa masuk edit mode
- [ ] Setelah navigasi (Enter/Arrow/Tab), sel tujuan HANYA highlight, TIDAK edit mode
- [ ] Status bar menampilkan mode yang benar (Siap / Edit)
- [ ] Grid container punya `tabindex="0"` dan auto-focus saat load

---

## YANG TIDAK BOLEH DIUBAH

- Semua fitur yang sudah berjalan: filter, pagination, tombol Kirim, tombol Edit (form full), Import CSV
- Tampilan kolom tabel (No, Nomor Agenda, Nomor SPP, Tanggal Masuk, Nilai Rupiah, Status, Aksi)
- Logic AJAX save yang sudah ada — hanya perbaiki kapan dipanggil
- Middleware, auth, role permission

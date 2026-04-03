# TASK: Implementasi Spreadsheet-Mode pada Halaman Daftar Dokumen (Role Operator/Staf Input)

## KONTEKS PROYEK

Ini adalah aplikasi **Agenda Online PTPN** berbasis **Laravel** (Blade + vanilla JS / Alpine.js).
Terdapat satu role khusus — sebut saja **Operator** atau **Staf Input** — yang tugasnya adalah menginput dokumen baru dan melihat daftar dokumen yang sudah di-input.

Selama ini user role ini mengeluh karena proses tambah dokumen terasa lebih lambat dibanding Google Sheets/Excel. Solusinya: **ubah tampilan halaman daftar dokumen milik role ini menjadi inline-editable spreadsheet**, di mana user bisa navigasi dan input data murni menggunakan keyboard, tanpa perlu buka halaman form terpisah.

---

## REFERENSI UTAMA

> **File HTML simulasi dilampirkan bersama prompt ini.**
> Gunakan file tersebut sebagai **referensi implementasi keyboard navigation, inline editing, dan UX logic**.
> Jangan copy-paste 1:1 — adaptasikan ke struktur Laravel (Blade, controller, route, model) yang sudah ada.

---

## SCOPE PERUBAHAN

Perubahan **hanya** dilakukan pada:
- View Blade milik role Operator/Staf Input (halaman daftar dokumen)
- Controller yang melayani CRUD dokumen untuk role tersebut
- Route yang terkait (tambah endpoint inline save jika belum ada)

**Jangan ubah:**
- Role lain (Kabag, Verifikator, Admin, dst)
- Halaman approval / verifikasi
- Model & relasi database
- Struktur tabel `documents` (atau nama tabel yang ada)

---

## FITUR YANG HARUS TETAP ADA

Semua fitur berikut harus tetap berfungsi setelah perubahan:

1. **Semua kolom tabel yang sudah ada** tetap ditampilkan (lihat screenshot tabel lama sebagai acuan):
   - No, Nomor Agenda, Nomor SPP, Tanggal Masuk, Nilai Rupiah, Status, Aksi (Edit & Kirim)

2. **Filter & Search** — filter tahun, filter status, input pencarian

3. **Pagination** — baris per halaman tetap bisa diatur

4. **Tombol Kirim** — tetap ada dan tetap berfungsi per baris

5. **Status badge** — Terkirim / Belum Dikirim / Diproses (atau sesuai yang ada di sistem)

6. **Import CSV** — jika ada fitur import, jangan dihapus

7. **Rekapitulasi** — jika ada halaman/tab rekap, jangan disentuh

---

## FITUR BARU YANG HARUS DIIMPLEMENTASIKAN

### 1. Inline Editing — Spreadsheet Style

- Setiap baris pada tabel **bisa diedit langsung** tanpa navigasi ke halaman form terpisah
- Sel yang sedang aktif (focused) harus ada **visual highlight** (border/outline berwarna)
- Kolom yang bisa diedit inline:
  - Nomor Agenda
  - Nomor SPP
  - Bagian (dropdown)
  - Kebun (dropdown, jika ada relasi ke tabel kebun)
  - Nama Pengirim
  - Jenis Pembayaran (dropdown atau text)
  - Nilai Rupiah (number input)
  - Kriteria CF (dropdown, jika relasi ada)
  - Sub Kriteria (dropdown dependent pada Kriteria CF)
  - Item Sub Kriteria (dropdown dependent)
  - Tanggal SPP (date-time picker atau manual input format `dd/mm/yyyy hh:mm`)
- Kolom yang **tidak** bisa diedit inline (read-only):
  - No (auto)
  - Tanggal Masuk (auto dari `created_at`)
  - Status (diubah melalui tombol Kirim, bukan inline)

### 2. Keyboard Navigation

Implementasikan event listener `keydown` pada grid/tabel:

| Tombol | Aksi |
|--------|------|
| `Arrow ↑ ↓ ← →` | Pindah fokus antar sel |
| `F2` atau **langsung ketik** | Masuk mode edit pada sel aktif |
| `Enter` | Simpan perubahan sel → fokus turun ke baris berikutnya |
| `Tab` | Simpan perubahan sel → fokus geser ke kolom berikutnya |
| `Shift+Tab` | Fokus balik ke kolom sebelumnya |
| `Esc` | Batalkan edit, kembalikan nilai semula |
| `Delete` / `Backspace` (saat tidak editing) | Kosongkan isi sel aktif |
| `Arrow ↓` di **baris terakhir** | Otomatis tambah baris baru kosong |
| `Ctrl+Enter` | Paksa tambah baris baru |
| `Ctrl+S` | Simpan semua perubahan yang belum tersimpan |

### 3. Auto-Save Per Baris (AJAX)

- Setiap kali user berpindah dari baris yang sudah diedit (commit edit), otomatis kirim **PATCH/PUT request** ke endpoint Laravel
- Endpoint: `PATCH /documents/{id}/inline-update` (buat jika belum ada)
- Request body: hanya field yang berubah (dirty fields)
- Response: `{ success: true, data: {...} }` atau `{ success: false, errors: {...} }`
- Tampilkan **visual feedback** per baris:
  - 🔄 Menyimpan... (spinner kecil di kiri nomor baris)
  - ✅ Tersimpan (flash hijau sebentar)
  - ❌ Gagal (border merah + tooltip error)

### 4. Tambah Baris Baru Inline

- Baris baru muncul **di bawah tabel** (bukan redirect ke halaman form)
- Baris baru diberi visual berbeda (background kuning muda)
- Nomor Agenda bisa diisi manual atau ada tombol **Auto** yang generate otomatis (sesuai logika yang sudah ada)
- Saat user `Enter` / pindah dari baris baru → kirim **POST request** untuk create dokumen baru
- Endpoint: `POST /documents/inline-store` (atau gunakan endpoint store yang sudah ada, sesuaikan)
- Jika validasi gagal (misal Nomor Agenda kosong), highlight sel yang error + tampilkan tooltip

### 5. Dropdown Dependent (Kriteria CF → Sub Kriteria → Item)

- Saat user memilih Kriteria CF di inline dropdown → fetch Sub Kriteria via AJAX
- Endpoint yang mungkin sudah ada: `GET /sub-kriteria?kriteria_id={id}` — gunakan yang sudah ada, jangan buat duplikat
- Loading state: disable dropdown Sub Kriteria sambil fetch

### 6. Toolbar Tambahan

Tambahkan di atas tabel (sejajar dengan filter yang sudah ada):
- Tombol **"+ Baris Baru"** (shortcut visual untuk `Ctrl+Enter`)
- Label info shortcut keyboard: `↑↓←→ Navigasi • F2 Edit • Enter Simpan • Ctrl+S Simpan Semua`
- Badge counter: `N baris belum disimpan` (muncul jika ada dirty rows)

---

## PANDUAN TEKNIS IMPLEMENTASI

### Stack yang digunakan
- Laravel Blade (server-side render awal)
- Vanilla JS atau Alpine.js (jangan install library baru yang berat)
- Axios sudah tersedia via Laravel Mix / Vite — gunakan untuk AJAX
- CSS: gunakan CSS variables yang sudah ada di project, atau inline style jika tidak ada

### Struktur yang disarankan

```
resources/views/operator/documents/
  index.blade.php          ← ubah view ini
  _spreadsheet_grid.blade.php  ← (opsional) partial untuk tabel

app/Http/Controllers/Operator/
  DocumentController.php   ← tambah method inlineUpdate() dan inlineStore()

routes/web.php (atau routes/operator.php)
  PATCH documents/{id}/inline-update → DocumentController@inlineUpdate
  POST  documents/inline-store       → DocumentController@inlineStore
```

### Method `inlineUpdate` (contoh skeleton)

```php
public function inlineUpdate(Request $request, Document $document)
{
    // Validasi hanya field yang dikirim
    $validated = $request->validate([
        'nomor_agenda'    => 'sometimes|string|max:50',
        'nomor_spp'       => 'sometimes|string|max:100',
        'bagian'          => 'sometimes|string|max:100',
        'kebun_id'        => 'sometimes|exists:kebuns,id',
        'nama_pengirim'   => 'sometimes|string|max:150',
        'jenis_pembayaran'=> 'sometimes|string|max:100',
        'nilai_rupiah'    => 'sometimes|numeric|min:0',
        // tambah field lain sesuai kolom yang ada
    ]);

    $document->update($validated);

    return response()->json([
        'success' => true,
        'data'    => $document->fresh(),
    ]);
}
```

### Nama field & kolom

> ⚠️ **PENTING:** Sesuaikan nama field (`nomor_agenda`, `nomor_spp`, `nilai_rupiah`, dst) dengan **nama kolom aktual di tabel database**. Cek file migration atau Model `Document` (atau nama model yang ada) untuk nama kolom yang benar. Jangan mengarang nama kolom.

### CSRF Token

Pastikan semua AJAX request menyertakan CSRF token:
```js
// Di <head> Blade atau di JS file
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
```

### Pagination & Filter

- Data awal tetap di-render server-side (Blade loop)
- Filter/search tetap submit form biasa (atau sudah pakai AJAX — pertahankan behavior yang ada)
- Saat pindah halaman (pagination), state editing direset

---

## HAL YANG TIDAK BOLEH DILAKUKAN

- ❌ Jangan install package NPM/Composer baru tanpa konfirmasi
- ❌ Jangan ubah struktur database / migration
- ❌ Jangan hapus route atau controller method yang sudah ada
- ❌ Jangan sentuh view role lain
- ❌ Jangan ganti sistem autentikasi / middleware yang sudah ada
- ❌ Jangan ubah logika "Kirim" dokumen (multi-level approval)

---

## OUTPUT YANG DIHARAPKAN

1. File Blade view yang sudah diubah (dengan komentar perubahan)
2. Method controller baru (`inlineUpdate`, `inlineStore`)
3. Tambahan route (tunjukkan di mana letaknya di `web.php`)
4. Snippet JS inline editing (bisa di `<script>` dalam Blade atau file `.js` terpisah)

---

## REFERENSI FILE

- **`agenda-spreadsheet-mode.html`** → file simulasi yang dilampirkan. Gunakan sebagai referensi UX dan logika JS. Adaptasi ke Laravel, jangan copy-paste mentah.
- Cek `app/Models/Document.php` (atau nama model aktual) untuk field yang ada di `$fillable`
- Cek `resources/views/operator/documents/index.blade.php` (atau path yang sesuai) untuk struktur tabel yang harus dipertahankan

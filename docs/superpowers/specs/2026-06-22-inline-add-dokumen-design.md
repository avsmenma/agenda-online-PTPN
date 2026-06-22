# Desain: Tambah Dokumen Inline di Halaman Daftar Dokumen (Operator)

- **Tanggal:** 2026-06-22
- **Status:** Disetujui (siap dibuatkan rencana implementasi)
- **Role terdampak:** Operator (dan admin, karena berbagi route `documents.*`)

## 1. Latar Belakang & Tujuan

Pengguna Agenda Online berasal dari kebiasaan spreadsheet: baris kosong sudah
tersedia sehingga input data cepat dan tidak berpindah-pindah halaman. Pada web saat
ini, menambah dokumen mengharuskan operator membuka halaman terpisah
(`/documents/create`), lalu kembali lagi ke daftar — terasa lambat untuk input
sedikit demi sedikit atau berturut-turut.

**Tujuan:** memungkinkan operator menambah dokumen **langsung di halaman Daftar
Dokumen (`/documents`)** dengan pengalaman menyerupai spreadsheet, tanpa
meninggalkan halaman.

## 2. Keputusan Produk (hasil brainstorming)

| Topik | Keputusan |
|---|---|
| Model input | Tombol **"+ Tambah Baris"** menyisipkan satu baris kosong (bukan banyak baris permanen). |
| Kapan tersimpan ke DB | Baris **ditahan di browser**; otomatis dibuat di DB saat field wajib terisi. |
| Field pemicu auto-save | **Nomor Agenda** saja. |
| Form lama (`/documents/create`) | **Tetap ada** untuk sekarang; akan dihapus setelah sistem baru terbukti stabil. |
| Field multi-nilai (Dibayar Kepada / PO / PR) | Diisi sebagai **teks tunggal**; beberapa nilai dipisah **koma** (diurai di server). |
| Pendekatan teknis | **Opsi A** — reuse `inline-update` yang sudah ada + 1 endpoint kecil `inline-create`. |

## 3. Konteks Kode Saat Ini (yang di-reuse)

- Tabel daftar operator adalah **HTML `<table>`** server-rendered, bukan Tabulator:
  `resources/views/operator/dokumens/daftarDokumen.blade.php:2657`, body
  `<tbody id="dokumenTableBody">` diisi partial
  `resources/views/operator/dokumens/_tableRowsAjax.blade.php`.
- Inline-edit per sel sudah berfungsi via `dblclick` → `fetch` PATCH ke
  `documents.inline-update` (`DokumenController@inlineUpdate`,
  `app/Http/Controllers/DokumenController.php` ~baris 1782+). Field-whitelist & parsing
  khusus (nilai_rupiah, tanggal, sanitasi URL, pecah koma untuk `dibayar_kepada`)
  sudah ada di sana.
- `store()` (~baris 1071) memakai default: `status='draft'`, `created_by='operator'`,
  `current_handler='operator'`, `tanggal_masuk=now()`, `bulan`/`tahun` dari `now()`.
- Route group: `Route::middleware(['auth','role:admin,operator'])->prefix('documents')->name('documents.')`.

**Prinsip:** tidak mengubah `store()` maupun `StoreDokumenRequest` agar form lama
tetap utuh.

## 4. Alur Interaksi (UX)

1. Toolbar tabel mendapat tombol **"+ Tambah Baris"**.
2. Klik → `<tr class="new-row">` disisipkan di **atas** `#dokumenTableBody`, dengan
   sel editable kosong untuk kolom yang sedang ditampilkan; fokus ke sel **Nomor
   Agenda**.
3. Bila user mengisi sel lain dulu (mis. Uraian) sebelum Nomor Agenda, nilainya
   **dibuffer di browser** (belum dikirim).
4. Saat **Nomor Agenda** terisi & di-commit (Enter / pindah sel) → baris "lahir" di
   DB lewat `inline-create`. Buffer field lain langsung di-*flush* via `inline-update`.
5. Setelah lahir, baris menjadi draft normal: dapat diedit per sel, dikirim ke
   verifikasi, atau dihapus — memakai mekanisme yang sudah ada.
6. Enter pada sel Agenda yang sudah lahir (atau klik "+" lagi) → muncul baris kosong
   berikutnya, sehingga input berturut-turut terasa seperti spreadsheet.

## 5. Backend

### Route baru
Di dalam grup `documents.` (middleware `auth, role:admin,operator`):

```
POST /documents/inline-create  →  DokumenController@inlineCreate  (name: documents.inline-create)
```

### Method `DokumenController@inlineCreate(Request $request)`
- **Validasi minimal:** `nomor_agenda` → `required|string|unique:dokumens,nomor_agenda`.
- Buat record `Dokumen` dengan default identik `store()`: `status='draft'`,
  `created_by='operator'`, `current_handler='operator'`, `tanggal_masuk=now()`,
  `bulan`/`tahun` dari `now()`.
- Render **satu baris** menggunakan partial `_tableRowsAjax` (koleksi berisi 1
  dokumen) agar markup baris identik dengan baris lain (semua `data-*`, badge status,
  tombol aksi).
- Catat activity log via `ActivityLogHelper::logCreated` (seperti `store()`).
- **Response sukses:** JSON `{ success: true, id, html }`.
- **Response gagal** (agenda kosong/duplikat): HTTP 422 `{ success: false, errors: { nomor_agenda: "..." } }`
  (pesan duplikat mengikuti `StoreDokumenRequest`: "Nomor agenda sudah digunakan...").

Tidak menyentuh `store()` / `StoreDokumenRequest`.

## 6. Frontend (`daftarDokumen.blade.php`)

- Tambah tombol **"+ Tambah Baris"** di toolbar tabel.
- `tambahBarisInline()`: bangun `<tr class="new-row">` berisi `<td>` editable kosong
  untuk tiap kolom di `$operatorTableColumns` (memakai penanda kelas yang sama dengan
  sel editable existing), sisipkan di awal `#dokumenTableBody`, fokus ke sel Nomor
  Agenda. Kolom non-editable (`No`, `status`, `Pengurus Dokumen`) dibiarkan kosong/
  read-only sampai baris lahir.
- Reuse handler commit edit yang ada. Khusus baris `new-row`:
  - Commit sel **non-agenda** sebelum lahir → simpan ke objek `pendingEdits`
    (tidak fetch).
  - Commit sel **Nomor Agenda** non-kosong → `POST inline-create`.
    - Sukses: ganti `<tr>` client dengan `html` dari server (kini punya `data-id`),
      lalu flush tiap entri `pendingEdits` via `inline-update`.
    - Gagal: tandai sel agenda (merah) + tampilkan pesan; baris tetap client-only.
  - Enter pada sel Agenda yang **sudah** lahir → panggil `tambahBarisInline()` lagi.
- Kolom multi-nilai diperlakukan sebagai teks; pemisahan koma diurai server oleh
  `inline-update` (perilaku `dibayar_kepada` yang sudah ada).

## 7. Validasi & Edge Case

- **Duplikat nomor agenda** → 422, sel merah, baris tetap client-only sampai
  diperbaiki.
- **User batal** (klik "+" lalu pergi tanpa mengisi agenda) → baris client dibuang;
  **tidak ada draft kosong nyasar** di DB.
- **Kolom non-editable** (`No`, `status`, `Pengurus Dokumen`) tidak dapat diedit di
  baris baru sampai lahir.
- **Race / dobel submit** → kunci sel agenda selama request `inline-create` berjalan
  agar tidak membuat dua record.
- **Flush buffer gagal sebagian** → tampilkan toast; sel tetap dapat diedit ulang
  (inline-update idempoten).

## 8. Rencana Pengujian

**Feature test** (`tests/Feature/InlineCreateDokumenTest.php`):
1. Operator membuat baris via Nomor Agenda → record `draft` tercipta dengan default
   benar (`created_by`, `current_handler`, `tanggal_masuk`, `bulan`, `tahun`).
2. Nomor agenda duplikat → 422 dengan pesan yang sesuai.
3. Nomor agenda kosong → 422.
4. User non-operator (mis. viewer) → ditolak (403/redirect).
5. Response sukses memuat `html` baris yang valid (mengandung `data-id`).

**Manual / acceptance:**
- Tambah beberapa baris berturut-turut (Enter beruntun) berfungsi mulus.
- Mengisi sel dengan urutan campur (Uraian dulu, lalu Agenda) → buffer ter-flush
  benar.
- Baris hasil inline-add bisa dikirim ke verifikasi & dihapus seperti draft biasa.

## 9. Di Luar Lingkup (fase ini)

- Tidak mengubah/menghapus form `/documents/create` (akan dihapus di fase berikutnya
  setelah sistem ini stabil).
- Tidak membuat editor multi-nilai (chip/tag) untuk Dibayar Kepada / PO / PR.
- Tidak mengubah tabel role lain (verifikasi, perpajakan, dll) — hanya operator.

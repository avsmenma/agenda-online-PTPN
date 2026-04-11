# LAPORAN EVALUASI ALUR PENGEMBALIAN DOKUMEN
**Sistem:** Agenda Online PTPN  
**Tanggal Evaluasi:** 11 April 2026  
**Evaluator:** AI Code & Database Analysis  
**Database:** `agenda_ptpn_new`  
**Total Dokumen:** 2.158

---

## 1. RINGKASAN ALUR SAAT INI

### 1.1 Alur Normal (Happy Path)

```
Bagian  →  [sendToOperator]  →  menunggu_approval_keuangan  (handler: operator)
        →  [Operator approve]  →  sent_to_team_verifikasi  (handler: team_verifikasi)
        →  [set deadline]  →  'sedang diproses'
        →  [sendToNextHandler]  →  sent_to_perpajakan / sent_to_akutansi
        →  [proses dept]  →  sent_to_pembayaran
        →  [bayar]  →  completed / selesai
```

### 1.2 Jalur Pengembalian (4 Jalur)

**Jalur A: Team Verifikasi → Bidang**
```
[returnToBidang]
  → status: 'returned_to_bidang'
  → current_handler: TETAP 'team_verifikasi'   ⚠️ (handler tidak berpindah)
  → return_source: kode bidang (mis. 'TEP')
  → Bagian bisa kirim ulang via sendToOperator
      JIKA return_source='team_verifikasi' → langsung ke Team Verifikasi
      JIKA tidak → ke Operator
```

**Jalur B: Team Verifikasi → Operator**
```
[returnToOperator]
  → status: 'returned_to_Operator'   ⚠️ (case inconsistent vs config)
  → current_handler: 'operator'
  → alasan_pengembalian: WAJIB (min 5 karakter)
```

**Jalur C: Team Verifikasi → Department (Perpajakan/Akutansi/Pembayaran)**
```
[returnToDepartment]
  → status: 'returned_to_department'
  → current_handler: TETAP 'team_verifikasi'
  → diikuti [sendToTargetDepartment] → sent_to_{dept}
```

**Jalur D: Perpajakan/Akutansi → Team Verifikasi (via Inbox Rejection)**
```
[inbox.reject]
  → DokumenStatus.status: 'rejected'
  → status dokumen: 'returned_to_verifikasi' ATAU 'returned_to_department'
  → Team Verifikasi kirim ulang via [sendBackToPerpajakan] / [sendToNextHandler]
```

### 1.3 Alur Kirim Ulang dari Bagian

```
sendToOperator() dipanggil → cek status 'returned_to_bidang'
  IF return_source === 'team_verifikasi'
      → sendBackToVerifikasi()
      → status: 'sent_to_team_verifikasi', handler: 'team_verifikasi'
      → return_source, return_reason, returned_at: SET NULL  ⚠️ (history hilang)
  ELSE
      → kirim ke Operator: 'menunggu_approval_keuangan'
```

---

## 2. DATA STATISTIK DOKUMEN

### 2.1 Distribusi Status Saat Ini

| Status | Jumlah | Keterangan |
|--------|--------|-----------|
| `draft` | 2.157 | Belum memasuki alur aktif |
| `sedang diproses` | 1 | Aktif di Team Verifikasi |
| `returned_to_bidang` | 0 | Tidak ada saat ini |
| `returned_to_department` | 0 | Tidak ada |
| `returned_to_operator` | 0 | Tidak ada |
| `completed` / `selesai` | 0 | Belum ada |

### 2.2 Current Handler Distribution

| Current Handler | Jumlah |
|----------------|--------|
| `operator` | 2.157 |
| `team_verifikasi` | 1 |

### 2.3 Field Pengembalian

| Field | Terisi | Kosong |
|-------|--------|--------|
| `return_source` | 0 | 2.158 |
| `return_reason` | 0 | 2.158 |
| `returned_at` | 0 | 2.158 |
| `resent_to_verifikasi_at` | 0 | 2.158 |

> **Catatan:** Database lokal belum memiliki data pengembalian aktual. Semua dokumen masih `draft` dengan `created_by: 'operator'`. Evaluasi berdasarkan analisis kode sumber.

### 2.4 Activity Logs

| Tipe Aksi | Jumlah |
|-----------|--------|
| `data_edited` | 30 |
| `sent_to_inbox` | 1 |
| `sent` | 1 |
| `received` | 1 |
| `approved` | 1 |
| `status_changed` | 1 |
| Aksi pengembalian (return/reject) | **0** |

---

## 3. TEMUAN MASALAH

### 3.1 KRITIS

---

**[KRITIS-1] Inkonsistensi Case: `returned_to_Operator` vs `returned_to_operator`**
- **Lokasi:** `TeamVerifikasiController.php:2586`, `PengembalianDokumenController.php:13`
- **Deskripsi:** Fungsi `returnToOperator()` menyimpan `'returned_to_Operator'` (huruf O kapital), tetapi `config/document_statuses.php:30` mendefinisikan `'returned_to_operator'` (semua huruf kecil).
- **Kode bermasalah:**
  ```php
  // TeamVerifikasiController.php baris 2586
  'status' => 'returned_to_Operator',  // huruf kapital - SALAH
  
  // Seharusnya (sesuai config):
  'status' => 'returned_to_operator',
  ```
- **Dampak:** Jika MySQL collation diubah ke `utf8_bin` (case-sensitive), semua dokumen yang dikembalikan ke Operator tidak akan ditemukan. Kode berbasis `config('document_statuses')` dan hardcode akan berperilaku berbeda.

---

**[KRITIS-2] Bagian Bisa Kirim Ulang Tanpa Perubahan**
- **Lokasi:** `BagianDokumenController.php:811-817`
- **Deskripsi:** Validasi hanya cek status, tidak cek apakah konten dokumen diubah setelah dikembalikan.
- **Kode bermasalah:**
  ```php
  if (!in_array($dokumen->status, ['belum dikirim', 'returned_to_bidang'])) {
      // hanya cek status, tidak cek apakah ada perubahan
  }
  ```
- **Dampak:** Loop bolak-balik dokumen tanpa resolusi. Team Verifikasi membuang waktu mereview dokumen yang sama dengan masalah yang sama berulang kali.

---

**[KRITIS-3] Alasan Pengembalian ke Bidang Tidak Wajib**
- **Lokasi:** `TeamVerifikasiController.php:2432-2436`
- **Deskripsi:** Field `bidang_return_reason` adalah `nullable`, fallback ke teks generik `'Dikembalikan ke bidang asal'`.
- **Kode bermasalah:**
  ```php
  $request->validate([
      'bidang_return_reason' => 'nullable|string|max:1000'  // seharusnya required!
  ]);
  // ...
  'return_reason' => $request->bidang_return_reason ?? 'Dikembalikan ke bidang asal',
  ```
- **Dampak:** Bagian tidak tahu apa yang perlu diperbaiki, menyebabkan siklus pengembalian berulang.

---

### 3.2 SEDANG

---

**[SEDANG-1] History Pengembalian Terhapus Saat Kirim Ulang**
- **Lokasi:** `BagianDokumenController.php:896-900` (`sendBackToVerifikasi`)
- **Deskripsi:** Alasan pengembalian dihapus tanpa disimpan ke log apapun.
  ```php
  $dokumen->update([
      'return_source' => null,   // ⚠️ dihapus tanpa backup
      'return_reason' => null,   // ⚠️ dihapus tanpa backup
      'returned_at' => null,     // ⚠️ dihapus tanpa backup
  ]);
  ```
- **Dampak:** Team Verifikasi tidak bisa melihat riwayat instruksi perbaikan saat mereview dokumen yang dikirim ulang.

---

**[SEDANG-2] Tidak Ada Notifikasi ke Bagian saat Dokumen Dikembalikan**
- **Lokasi:** `TeamVerifikasiController.php` fungsi `returnToBidang()` (baris 2346-2508)
- **Deskripsi:** Fungsi hanya mencatat `ActivityLogHelper::logReturned()` tapi tidak ada trigger notifikasi WhatsApp/push ke user Bagian.
- **Dampak:** Bagian harus aktif login dan cek manual untuk tahu dokumennya dikembalikan.

---

**[SEDANG-3] Duplikasi Status Semantik yang Membingungkan**
- **Lokasi:** `config/document_statuses.php`, `TeamVerifikasiController.php`
- **Deskripsi:** Dua status dengan tujuan mirip: `returned_to_department` (current_handler='team_verifikasi') dan `returned_to_verifikasi` (current_handler bisa tetap di dept asal). Logika query menjadi sangat kompleks.
- **Dampak:** Kode sulit dipahami dan dirawat, rawan bug di query filter.

---

**[SEDANG-4] Routing Kirim Ulang Bergantung pada `return_source` yang Bisa Null**
- **Lokasi:** `BagianDokumenController.php:820-821`
- **Deskripsi:**
  ```php
  if ($dokumen->return_source === 'team_verifikasi') {
      // langsung ke Team Verifikasi
  }
  // else: ke Operator (default)
  ```
  Jika `return_source` null (dokumen dalam `returned_to_bidang` tapi `return_source` tidak terisi), dokumen akan miskonfigurasi routing ke Operator alih-alih Team Verifikasi.
- **Dampak:** Salah routing dokumen.

---

**[SEDANG-5] `sendBackToMainList` Mengubah Status Tanpa Log**
- **Lokasi:** `TeamVerifikasiController.php:2515-2557`
- **Deskripsi:** Mengubah status `returned_to_bidang` → `sent_to_team_verifikasi` tanpa mencatat ke `dokumen_activity_logs`.
- **Dampak:** Timeline dokumen tidak lengkap, audit trail bolong.

---

**[SEDANG-6] `sendBackToMainList` Dapat Dipanggil Tanpa Konfirmaai Bagian**
- **Lokasi:** Route: `POST /returns/verifikasi/{dokumen}/restore-from-bidang`
- **Deskripsi:** Team Verifikasi bisa memulihkan dokumen dari status `returned_to_bidang` kembali ke daftar utama tanpa Bagian melakukan apapun.
- **Dampak:** Bypass alur — dokumen dikembalikan ke Team Verifikasi tanpa Bagian tahu atau sudah memperbaiki.

---

### 3.3 RINGAN

---

**[RINGAN-1] Mapping Nama Bagian Tidak Exhaustive di `returnToBidang`**
- **Lokasi:** `TeamVerifikasiController.php:2361-2396`
- **Deskripsi:** Fungsi bergantung pada peta nama bagian hardcoded. Jika nilai field `bagian` tidak match, gagal dengan error 422.
- **Dampak:** Team Verifikasi bisa tidak bisa mengembalikan dokumen jika nama bagian tidak sesuai mapping.

---

**[RINGAN-2] `PengembalianDokumenController` Statistik Duplikat**
- **Lokasi:** `PengembalianDokumenController.php:18-26`
- **Deskripsi:** `$totalDibaca` dan `$totalDikembalikan` dihitung dengan query identik, selalu nilainya sama.
  ```php
  $totalDibaca = Dokumen::where(...)->where('status', 'returned_to_Operator')->count();
  $totalDikembalikan = Dokumen::where(...)->where('status', 'returned_to_Operator')->count();
  // Sama persis!
  ```
- **Dampak:** Statistik tidak informatif di halaman operator.

---

**[RINGAN-3] Format `received_from` di `dokumen_role_data` Tidak Konsisten**
- **Lokasi:** `BagianDokumenController.php:845,911`
- **Kode:** `'bagian_tep'` (kirim normal) vs `'bagian_resend_tep'` (kirim ulang)
- **Dampak:** Tracking di `dokumen_role_data` tidak seragam.

---

**[RINGAN-4] Kondisi Redundan di `editDokumen` dan `updateDokumen`**
- **Lokasi:** `TeamVerifikasiController.php:812,907`
- **Kode:** `in_array($dokumen->current_handler, ['team_verifikasi', 'team_verifikasi'])` — dua nilai identik.
- **Dampak:** Tidak ada bug, tapi mencerminkan kode yang kurang dirawat (kemungkinan sisa refactoring).

---

## 4. CELAH ALUR KERJA

### 4.1 Bypass yang Mungkin Terjadi

| Skenario Bypass | Risiko |
|-----------------|--------|
| Bagian kirim ulang tanpa edit | Loop sia-sia (KRITIS-2) |
| Team Verifikasi `restore-from-bidang` tanpa Bagian mengetahui | Bypass tahapan perbaikan |
| Bagian edit dokumen saat sedang diproses Team Verifikasi | Tidak ada proteksi di `update()` BagianDokumenController |
| `sendToTargetDepartment` dipanggil tanpa `returnToDepartment` terlebih dahulu | Validasi cek `status === 'returned_to_department'` ada, cukup aman |

### 4.2 Race Condition

Jika dua user Team Verifikasi membuka dokumen yang sama bersamaan:
- User A klik "Kembalikan ke Bidang"
- User B klik "Kirim ke Perpajakan" 
- Keduanya bisa berhasil karena tidak ada mekanisme locking

**Mitigasi parsial:** Cek `current_handler === 'team_verifikasi'` di awal setiap fungsi. Namun bukan atomic lock.

### 4.3 Status Ambigu

| Status | Ambiguitas |
|--------|-----------|
| `returned_to_Operator` (kapital O) | Inconsistent dengan semua status lain |
| `'sedang diproses'` (dengan spasi) | Perlu hati-hati saat strict comparison |
| `current_handler='team_verifikasi'` + `status='returned_to_bidang'` | Semantically confusing: handler di verifikasi tapi status dikembalikan ke bidang |

---

## 5. KUALITAS DATA

### 5.1 Kondisi Database

| Aspek | Nilai |
|-------|-------|
| Total dokumen | 2.158 |
| Sudah melewati alur pengembalian | **0** |
| Activity log untuk pengembalian | **0** |
| Dokumen dari `created_by: 'bagian_*'` | **0** |
| Rows di `dokumen_statuses` | 1 |
| Rows di `document_trackings` | 0 |

### 5.2 Inkonsistensi Struktur

1. `document_trackings` **kosong** — `DocumentTracking::logAction()` dipanggil di kode tapi tidak ada data
2. `dokumen_activity_logs` hanya 35 baris — semua `data_edited`, tidak ada log return/forward
3. Semua dokumen `created_by: 'operator'` — fitur Bagian belum pernah dipakai
4. `dokumen_statuses` 1 baris saja (`approved`, `team_verifikasi`)
5. `perpajakan_return_data` JSON field direferensikan di kode (`baris 1608`) tapi kemungkinan besar tidak ada sebagai kolom di schema

---

## 6. REKOMENDASI PERBAIKAN

### Prioritas TINGGI (Segera Lakukan)

**R1 — Perbaiki inkonsistensi status `returned_to_Operator`**
```php
// TeamVerifikasiController.php baris 2586 — SEBELUM:
'status' => 'returned_to_Operator',

// SESUDAH:
'status' => 'returned_to_operator',
```
Juga update semua query dan filter yang menggunakan string tersebut menjadi lowercase.

---

**R2 — Wajibkan alasan pengembalian ke Bidang**
```php
// TeamVerifikasiController.php di returnToBidang() — SEBELUM:
'bidang_return_reason' => 'nullable|string|max:1000'

// SESUDAH:
'bidang_return_reason' => 'required|string|min:10|max:1000'
```
Hapus fallback default `'Dikembalikan ke bidang asal'`.

---

**R3 — Simpan history pengembalian sebelum dihapus**

Di `sendBackToVerifikasi()`, sebelum set null, simpan ke activity log:
```php
// Tambahkan di BagianDokumenController.php sebelum update:
\App\Helpers\ActivityLogHelper::log($dokumen, 'resent_after_return', 'bagian_' . strtolower($bagianCode), [
    'previous_return_reason' => $dokumen->return_reason,
    'previous_returned_at'   => $dokumen->returned_at,
    'previous_return_source' => $dokumen->return_source,
]);
```

---

**R4 — Validasi dokumen sudah diubah sebelum kirim ulang**

```php
// Di sendToOperator() BagianDokumenController.php:
if ($dokumen->status === 'returned_to_bidang') {
    if ($dokumen->updated_at && $dokumen->returned_at) {
        if ($dokumen->updated_at->lte($dokumen->returned_at)) {
            return response()->json([
                'success' => false,
                'message' => 'Harap lakukan perubahan pada dokumen terlebih dahulu sebelum mengirim ulang.'
            ], 422);
        }
    }
}
```

---

### Prioritas SEDANG

**R5 — Perbaiki routing condition kirim ulang**
```php
// SEBELUM (BagianDokumenController.php:820):
if ($dokumen->return_source === 'team_verifikasi') {

// SESUDAH (lebih robust):
if ($dokumen->status === 'returned_to_bidang' && $dokumen->return_source === 'team_verifikasi') {
```

**R6 — Tambah notifikasi ke Bagian saat dikembalikan**

Di akhir `returnToBidang()`, trigger notifikasi WhatsApp atau email ke user dengan `bagian_code === $targetBidang`.

**R7 — Tambah activity log di `sendBackToMainList()`**
```php
// Setelah DB::commit():
\App\Helpers\ActivityLogHelper::log($dokumen, 'restored_from_bidang', 'team_verifikasi', [
    'previous_return_source' => $dokumen->return_source,
]);
```

**R8 — Proteksi edit dokumen yang sedang diproses**

Di `BagianDokumenController::update()`, tambahkan:
```php
$allowedEditStatus = ['belum dikirim', 'returned_to_bidang'];
if (!in_array($dokumen->status, $allowedEditStatus)) {
    abort(403, 'Dokumen tidak dapat diedit dalam status: ' . $dokumen->status);
}
```

**R9 — Konsolidasi dua status pengembalian yang ambigu**

Unifikasi `returned_to_department` dan `returned_to_verifikasi` menjadi satu status dengan field `return_target` yang menjelaskan tujuan.

---

### Prioritas RENDAH

**R10 — Perbaiki statistik duplikat** di `PengembalianDokumenController.php:18-26`

**R11 — Perbaiki kondisi redundan** di `TeamVerifikasiController.php:812,907`:
```php
// SEBELUM:
in_array($dokumen->current_handler, ['team_verifikasi', 'team_verifikasi'])
// SESUDAH:
in_array($dokumen->current_handler, ['team_verifikasi', 'verifikasi'])
```

**R12 — Verifikasi keberadaan kolom `perpajakan_return_data`** di tabel `dokumens`. Jika tidak ada, tambahkan via migration.

---

## 7. KESIMPULAN

### Scorecard

| Aspek | Nilai | Keterangan |
|-------|-------|-----------|
| Kelengkapan Fitur | ★★★★☆ | 4 jalur pengembalian terimplementasi |
| Validasi Input | ★★★☆☆ | Return ke Operator wajib alasan, ke Bidang tidak |
| Konsistensi Kode | ★★☆☆☆ | Case inconsistency, duplikasi status semantik |
| Logging & Audit Trail | ★★☆☆☆ | Log tidak konsisten, history hilang saat kirim ulang |
| Notifikasi | ★★☆☆☆ | Tidak ada notifikasi ke Bagian |
| Keamanan Alur | ★★★☆☆ | Tidak ada cek dokumen sudah diubah sebelum resend |
| Proteksi Race Condition | ★★☆☆☆ | Tidak ada locking mekanisme |

### Kesimpulan Akhir

> **Alur pengembalian dokumen di sistem Agenda Online PTPN sudah CUKUP berfungsi secara struktural, namun memerlukan perbaikan signifikan di 4 area utama sebelum dapat diandalkan di produksi.**

**Kekuatan:**
- 4 jalur pengembalian terstruktur lengkap
- Validasi role/handler di setiap fungsi
- DB transaction (beginTransaction/rollback) konsisten
- Routing konteks-aware (bypass Operator jika dokumen dari Team Verifikasi)

**Kelemahan Kritis yang Harus Diperbaiki:**
1. `returned_to_Operator` vs `returned_to_operator` — bom waktu inkonsistensi
2. Tidak ada cek "sudah diubah" sebelum kirim ulang — loop sia-sia bisa terjadi
3. History pengembalian terhapus saat kirim ulang — audit trail bolong
4. Tidak ada notifikasi ke Bagian — Bagian tidak tahu dokumennya dikembalikan

**Tindakan Segera (1 sprint):** R1 (perbaiki case), R2 (wajibkan alasan), R3 (simpan history), R4 (validasi perubahan).  
Keempat perbaikan ini dapat dilakukan tanpa schema migration dan akan meningkatkan reliabilitas alur secara signifikan.

---

*Laporan ini dibuat 11 April 2026 berdasarkan analisis kode sumber dan database lokal `agenda_ptpn_new`. Database lokal belum memiliki data pengembalian aktual; untuk statistik pengembalian nyata, diperlukan analisis database production.*

# Rollout Tabulator — Role Verifikasi (Rollout 3)

**Tanggal:** 2026-07-25
**Status:** Disetujui (siap → writing-plans)
**Role:** `team_verifikasi` (verifikasi)
**Preseden pola:** operator (2026-07-23), akutansi (Rollout 1, 2026-07-24), perpajakan (Rollout 2, 2026-07-24)

---

## 1. Tujuan

Migrasikan tabel dokumen role **verifikasi** dari tabel HTML sticky + `_rows`/`_chunk`
server-rendered + virtual-scroll ke **engine Tabulator bersama** (`public/js/document-tabulator.js`),
mengikuti pola yang sudah terbukti di operator/akutansi/perpajakan. Setelah QA lolos,
hapus tabel legacy + kode aksi mati (grep-gated). **Nol arsitektur baru** — ini klon
pola, bukan penemuan ulang.

Verifikasi adalah role **paling kompleks**: badge status ~24 cabang + sistem `display_status`
beku, deadline count-up, visibilitas lintas-role, fitur khas **Paraf** (kini dipensiunkan),
dan badge duplikat di panel detail.

---

## 2. Arsitektur (identik 3 role sebelumnya — acuan, bukan hal baru)

- **Engine JS bersama:** `public/js/document-tabulator.js` membaca `window.DOCUMENT_TABULATOR_CONFIG`,
  mount via `CFG.mountId`, kolom tetap terparameter via `CFG.extraColumns` `[{field,title,formatter}]`,
  registry formatter `EXTRA_FORMATTERS{deadline,akutansiStatus}` (tambah varian bila perlu),
  nama field filter dibaca generik dari DOM `.tabulator-toolbar [name]`.
- **DTO server-side:** `App\Support\VerifikasiDocumentRow extends App\Support\DocumentRow`.
  Base menyediakan `baseRow()`/`normalizeRole()`/`formatDates()`. Subclass menambah objek
  siap-render `status_badge {class, icon|null, text, link|null}` dan `deadline {variant, type,
  color, received_display, indicator_icon, indicator_label, age_text, footer|null}`.
  **Klien hanya MERENDER objek ini — nol logika bisnis di JS.**
- **Endpoint JSON:** `GET documents/verifikasi/data` → nama `documents.verifikasi.data`
  → `TeamVerifikasiController::datatable()` (method baru), query bersama `buildVerifikasiQuery()`
  diekstrak dari `dokumens()`.
- **View baru:** `resources/views/team_verifikasi/dokumens/daftarDokumenTabulator.blade.php`.
- **Forward:** tetap via dropdown **Pengurus Dokumen** (`partials/document-handler-select` →
  `DocumentHandlerController`) — sudah jalur aktif verifikasi, TIDAK berubah.

---

## 3. `VerifikasiDocumentRow` — deltas khas verifikasi

Semua dihitung **SERVER-side** di DTO. Sumber porting: `_rows.blade.php`.

### 3.1 `status_badge` — port ~24 cabang (`_rows.blade.php` setup 3–138, cascade 453–609)

- Pertahankan **urutan cabang persis**. Cabang top-level (ringkas):
  `isReturnedToVerifikasi` → `isRejected` (nested other-role/draft) → `selesai/approved_Team Verifikasi`
  → `rejected_Team Verifikasi` → `returned_to_bidang` → `displayStatusLabel` (nested terkirim/menunggu/
  sedang_diproses/terkunci) → `isPendingPerpajakan` → `isPendingAkuntansi` → `sentToTeamLabel`
  → `sent_to_{perpajakan,akutansi,pembayaran}` → `waiting_approval_{perpajakan,akuntansi,pembayaran}`
  → grup `menunggu_di_approve/...` → `sedang diproses (+isLocked)` → `sent_to_team_verifikasi (±isLocked)`
  → `returned_to_operator` → `returned_to_department` (nested workflow-return) → `returned_from_*`
  → fallback `⏳ ucfirst(status)`.
- **Sistem `display_status` beku:** `$displayStatus = roleData(team_verifikasi)->display_status`;
  label via `\App\Models\Dokumen::getFinalStatusLabel($displayStatus)`. Ini status FINAL/beku
  yang tak berubah saat role hilir bertindak. **Fallback legacy** (saat `display_status` null)
  mendeteksi `waiting_approval_*` dan `wasSentToPerpajakan` — WAJIB diporting utuh (dokumen
  pra-fitur bergantung padanya).
- **Tanpa link** — verifikasi tidak punya badge "cek disini" ber-link (berbeda dari badge ditolak
  akutansi/perpajakan yang sudah dihapus). `link` selalu `null`.

### 3.2 `deadline` — count-up umur (`_rows.blade.php` 300–451)

- Model "hitung naik" sejak `received_at` (roleData team_verifikasi), **bukan** countdown ke `deadline_at`.
- Ambang: **AMAN < 24 jam, PERINGATAN 24–72 jam, TERLAMBAT ≥ 72 jam**; warna green/yellow/red.
- **Beku** saat sent/paused(returned)/completed → `endTime = processed_at`, warna abu, `⏸️`.
- `type` ∈ `active|paused|sent|completed`. Semantik IDENTIK `AkutansiDocumentRow::buildDeadline()`
  (Path A diterima → kartu umur; Path C belum diterima → `variant:none`). Path B (bypass) verifikasi
  kemungkinan tak relevan — verifikasi hulu; verifikasi ke bawah, bukan di-bypass. Cek saat porting;
  bila tak ada jalur bypass di legacy verifikasi, cukup Path A + Path C.

### 3.3 Kolom Paraf — read-only (keputusan user: Paraf DIPENSIUNKAN)

- `tanggal_paraf` & `pemaraf` tetap **tampil** sebagai kolom data biasa (read-only), diformat
  seperti legacy (`_rows.blade.php:223–233`).
- **Tidak ada aksi Paraf** di tabel baru (tak ada tombol/modal). Kode aksi Paraf dihapus di §5.

### 3.4 Visibilitas lintas-role & paritas data

- Verifikasi menampilkan **SEMUA dokumen** (`is_at_my_role` dihitung controller; aksi non-aktif
  bila dokumen bukan di role verifikasi). Base `DocumentRow` sudah menangani bit `is_at_my_role`.
- **RISIKO PARITAS UTAMA:** `VerifikasiDocumentRow` WAJIB diberi eager-load relasi (`roleData`,
  `roleStatuses`) **identik byte** dengan `dokumens()` legacy. Cascade badge & fallback menyentuh
  `getDataForRole('perpajakan')` (`_rows.blade.php:94`) dan `roleStatuses` semua role. Jika legacy
  memuat roleData terbatas satu role, DTO harus meniru persis (jangan menambah/mengurangi query),
  agar hasil badge byte-identik. **Verifikasi scope eager-load `dokumens()` sebelum menulis DTO.**

---

## 4. Panel detail (rekonsiliasi badge duplikat)

- `TeamVerifikasiController::generateDocumentDetailHtml` (970–980) punya badge **4-cabang terpisah**
  (duplikat lebih sederhana dari cascade tabel).
- Ikuti cara akutansi/perpajakan: interaksi detail lewat **row-click → panel workbench global**
  (`document-workbench-ui`). Sebelum memutuskan hapus/simpan `getDocumentDetail`/`generateDocumentDetailHtml`,
  **grep-gate** — pastikan tak ada pemakai lain (mis. halaman lain, polling). Bila yatim → hapus di §5;
  bila masih dipakai → biarkan + catat.

---

## 5. Cleanup pasca-QA (grep-gated, findings di-surface DULU)

Hanya dijalankan **setelah** tabel Tabulator lolos QA. Setiap penghapusan kode aksi WAJIB
didahului grep lintas `resources/` + `routes/` dan **findings dilaporkan ke user sebelum hapus**
(presedan wajib — lihat pelajaran `sendToAkutansi` di perpajakan).

**Aman dihapus bersama file view (otomatis):** `daftarDokumen.blade.php` (6046) + `_rows.blade.php`
+ `_chunk.blade.php`, beserta modal Paraf, JS `openParafModal`/`submitParaf`, CSS `.btn-paraf`/
`.badge-paraf-done` yang hidup DI DALAM file-file itu. Cabang `virtual_chunk` di `dokumens()` +
flag `?classic` → dihapus/no-op.

**Hapus HANYA setelah grep membuktikan yatim (surface dulu):**
- `parafDokumen()` + route `documents.verifikasi.paraf` (Paraf pensiun — tak ada pemicu lain).
- Tombol/method Kirim/Balik yang gated `$showActionColumn=false`: `sendToNextHandler`,
  `returnToDepartment`, `returnToOperator` + route terkait.
- `getDocumentDetail`/`generateDocumentDetailHtml` bila §4 menyatakan yatim.

**JANGAN sentuh tanpa bukti kuat / mungkin dipakai halaman lain:**
- `acceptDocument`/`rejectDocument` + route `.accept`/`.reject` — kemungkinan dipakai halaman **Inbox**
  approval, BUKAN tabel. Grep dulu; default: PERTAHANKAN.
- `checkRejectedDocuments`/`showRejectedDocument` (polling notifikasi) — pertahankan.

**🚫 HARAM disentuh (satu-satunya return hidup):** `returns.verifikasi.*`
(`pengembalianKeBidang`, `returnToBidang`) + view `pengembalianKeBidang.blade.php`.
Catatan: route `returns.verifikasi.restore-from-bidang` → `restoreFromBidang` menunjuk method
yang **tak ada** (broken) — laporkan ke user sebagai temuan terpisah; jangan diam-diam diperbaiki
atau dihapus tanpa keputusan.

---

## 6. Testing & QA

- **Unit:** `tests/Unit/VerifikasiDocumentRowTest.php` — badge tiap cabang kunci (minimal:
  returned_to_verifikasi, isRejected other-role vs draft, displayStatusLabel terkirim/menunggu,
  waiting_approval_*, sedang diproses, fallback) + deadline Path A (AMAN/PERINGATAN/TERLAMBAT +
  beku sent/paused) & Path C.
- **Feature:** endpoint `documents.verifikasi.data` mengembalikan JSON valid untuk viewer verifikasi;
  route lama yang dihapus → 404/no-op sesuai keputusan cleanup.
- **Suite hijau** (`php artisan test`) sebelum tiap commit.
- **QA visual produksi (Playwright, READ-ONLY, login `verifikasi`):** tabel muat, 0 error console,
  badge/deadline byte-cocok sampel dokumen, filter toolbar aktif, dropdown Pengurus Dokumen forward,
  `returns.verifikasi.*` masih hidup. **QA visual = tanggung jawab akhir user.**

---

## 7. Urutan eksekusi (garis besar — detail di plan)

1. `VerifikasiDocumentRow` + unit test (port badge + deadline, paritas eager-load).
2. `buildVerifikasiQuery()` + `datatable()` + route `documents.verifikasi.data` + feature test.
3. View `daftarDokumenTabulator` + `DOCUMENT_TABULATOR_CONFIG` + extraColumns (deadline, status, paraf).
4. Alihkan `dokumens()` menyajikan view Tabulator (flag transisi `?classic` sementara bila perlu).
5. **QA gate (user).**
6. Cleanup grep-gated (§5) + hapus legacy + update `CLAUDE.md §7`.
7. Deploy (commit per-file → push → pull server → clear cache).

---

## 8. Gerbang kritis (berhenti & minta keputusan)

- Sebelum hapus kode aksi apa pun (§5) — surface findings grep.
- Bila QA visual gagal.
- Temuan `restore-from-bidang` broken — laporkan, jangan perbaiki diam-diam.
- Menyentuh partial global (`document-workbench-ui`, `document-handler-select`) — aditif saja.

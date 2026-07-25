# Rollout Tabulator — Role Pembayaran (Rollout 4, outlier)

**Tanggal:** 2026-07-25
**Status:** Disetujui (siap → writing-plans)
**Role:** `pembayaran` (Tim Pembayaran)
**Preseden pola:** operator, akutansi, perpajakan, verifikasi (Rollout 1–3)

---

## 1. Tujuan

Migrasikan **tabel dokumen utama** pembayaran (`pembayaranNEW/dashboardPembayaran.blade.php`,
3970 baris — tabel biasa + CSS sticky + renderer JS bespoke `renderFallbackRows`/`loadFallbackRows`,
menyantap endpoint shape-DataTables) ke **engine Tabulator bersama** (`public/js/document-tabulator.js`),
mengikuti pola 4 role sebelumnya. Setelah QA lolos, hapus renderer bespoke + endpoint lama +
kode aksi mati (grep-gated). Reimplement fitur **freeze kolom 2-tab** di atas frozen-column native
Tabulator. Ini rollout tersulit — outlier.

**Keputusan user (WAJIB):** Full Tabulator + reimplement freeze; **hanya tabel utama** (CSV import,
export, rekapan-vendor, virtual-assistant dibiarkan). Freeze = bagian dari fitur kustomisasi kolom.
Pembayaran **boleh edit dokumen walau belum sampai** di role-nya (edit-anywhere).

---

## 2. Arsitektur (pola bersama + deltas)

- Engine `document-tabulator.js` baca `window.DOCUMENT_TABULATOR_CONFIG` (mount `CFG.mountId`,
  `extraColumns`, `progressiveLoad:'scroll'` → endpoint `{last_page,total,data}`, filter DOM generik).
- DTO `App\Support\PembayaranDocumentRow extends App\Support\DocumentRow`. Base sediakan
  `baseRow()`/`formatDates()`; subclass tambah bit khas pembayaran + objek `status_badge` (pill).
- Endpoint JSON baru/reshape `documents.pembayaran.data` → `{last_page,total,data}`.
- View Tabulator baru menggantikan renderer bespoke di `pembayaranNEW/dashboardPembayaran.blade.php`.

---

## 3. `PembayaranDocumentRow` — deltas khas pembayaran

### 3.1 Status = pill pembayaran 3-state (BUKAN badge workflow)

- Port `getPembayaranComputedStatus()` (controller :1108-1125) → `buildStatusBadge()` server:
  1. `sudah_dibayar` bila `tanggal_dibayar` OR `link_bukti_pembayaran` OR
     `status_pembayaran ∈ {sudah_dibayar, "SUDAH DIBAYAR", SUDAH_DIBAYAR}` (toleran casing CSV).
  2. else `siap_dibayar` bila `current_handler==='pembayaran'` OR `status==='sent_to_pembayaran'`.
  3. else `belum_siap_dibayar`.
- Objek badge → formatter engine baru **`paymentPill`** (aditif): kelas `status-pill--paid` /
  `status-pill--ready` / `status-pill--pending` (port `renderPembayaranStatusPill()` :1127-1138).
- **Satukan duplikasi ≥5×** (index closure :203-217, exportRekapan :1463-1480, exportToPDF :2064-2080,
  Blade rekapan :2231-2249, stat-card :229-258) → panggil DTO/method bersama. **CATATAN:** export &
  rekapan DI LUAR scope tabel — port ke DTO boleh, tapi JANGAN ubah perilaku export; bila berisiko,
  cukup tabel yang pakai DTO, export tetap pakai jalur lamanya (dedup menyusul).

### 3.2 Tanpa kolom deadline per-baris

- Pembayaran **tak punya** kolom deadline/countdown per-baris. DTO **tidak** mengeluarkan objek `deadline`.
- **3 kartu agregat** Aman/Peringatan/Terlambat (count-up sejak `received_at`, fallback `tanggal_masuk`/
  `created_at`; beku di `processed_at`/`tanggal_dibayar` saat `sudah_dibayar`; ambang 168j/504j) tetap
  dihitung server di `index()` — **tak berubah**. Tabulator view tetap menampilkan kartu ini.

### 3.3 Edit-anywhere (beda tegas dari role lain)

- `can_edit = true` untuk **semua baris** — TIDAK di-gate `is_at_my_role`/arrival (dikonfirmasi: cek
  `current_handler` hanya di method aksi terpisah, bukan jalur inline-edit tabel).
- Set kolom editable (~30, dari `editableColumns` view :3576-3608 + `getPembayaranInlineEditOptions()`
  :963-1032) **dipertahankan persis**, termasuk `tanggal_dibayar` (cara "mark as paid" = inline-edit).
- Inline-edit tetap POST ke shared `PATCH /documents/{id}/inline-update` (sama seperti sekarang).

### 3.4 Tanpa forward

- **JANGAN** render `partials/document-handler-select`. `handler`/`handler_options`/`can_change_handler`
  dari `baseRow()` boleh ada di row tapi tak dipakai/ditampilkan (tak ada kolom Pengurus Dokumen).

### 3.5 Query INCLUDE CSV

- `buildPembayaranQuery()` (endpoint) **memasukkan** `imported_from_csv` (pembayaran satu-satunya yang
  include). JANGAN salin `where('imported_from_csv', false)` dari role lain. Baris CSV bawa casing
  uppercase (`"SUDAH DIBAYAR"`) — badge sudah toleran (§3.1).

---

## 4. Kustomisasi kolom + Freeze 2-tab (crux, risiko tertinggi)

Fitur user-configurable: modal 2-tab **"Kolom Tabel"** (pilih kolom terlihat + urutan) + **"Kolom Beku"**
(per-kolom **Kiri / Bebas / Kanan**), dengan persistensi.

**Pertahankan:**
- UI modal 2-tab (port ke view Tabulator baru).
- Katalog 48-kolom (`getPembayaranDashboardAvailableColumns()` :911-961).
- **Kunci penyimpanan preferensi TAK BERUBAH** (`table_columns_preferences['pembayaran_dashboard']`,
  `['pembayaran_dashboard_frozen']`, session `pembayaran_dashboard_frozen_columns`, sentinel `frozen_config`)
  — export & rekapan membacanya; mengubahnya merusak mereka.
- Logika `App\Support\FrozenColumnLayout` (`normalize()` "kiri menang" + `renderOrder()` beku-kiri→bebas→beku-kanan).

**Ubah — mekanisme freeze:**
- DARI: server-side reorder `$renderColumns` + emit sticky-CSS per-kolom + `window.DOCUMENT_STICKY_CONFIG`.
- KE: **frozen-column native Tabulator**. View/DTO menghitung frozen order (pakai `FrozenColumnLayout`)
  lalu meneruskannya ke engine via config **aditif** `CFG.frozen = { left: [...keys], right: [...keys] }`.
  Engine set `frozen:true` pada kolom itu + posisikan (Tabulator bekukan left-frozen ke tepi kiri,
  right-frozen ke tepi kanan). Engine SUDAH memakai frozen columns (warning selectRange terlihat di QA
  role lain) — ini perluasan, bukan hal baru. Perubahan engine WAJIB **aditif** (role lain tak set `CFG.frozen`).
- **RISIKO:** bila frozen-column native Tabulator tak bisa mereplikasi penuh (mis. right-freeze +
  lebar variabel + peringatan "terlalu banyak beku"), tugas freeze di plan WAJIB **surface ke user**
  sebelum kompromi UX. Ini gerbang kritis.

---

## 5. Endpoint

- `datatable()` lama kembalikan shape DataTables `{draw, recordsTotal, recordsFiltered, data}` di route
  `dashboard.pembayaran.data`, server-side paginate `start`/`length`.
- Buat/reshape → endpoint `documents.pembayaran.data` shape **`{last_page,total,data}`** (mirror pola
  akutansi/perpajakan/verifikasi), baca `size`/`page`. Query bersama `buildPembayaranQuery()` (include CSV,
  sort/filter, eager-load yg dibutuhkan `PembayaranDocumentRow`). Map tiap Dokumen → DTO.
- **Paritas eager-load:** DTO base pakai `dibayarKepadas`/`dokumenPos` (relasi, bukan withCount) —
  pastikan endpoint eager-load relasi yang dibutuhkan (pelajaran verifikasi: `dokumenPos` relasi).

---

## 6. Interaksi §8

Engine memberi **copy/paste (Excel-style)/multiselect(drag/Shift)/Delete-clear/Undo-Redo** yang saat ini
BELUM ada di pembayaran, sambil mempertahankan **inline-edit** (Enter/dbl-klik) + **arrow-nav** yang sudah
ada. Aturan penempelan/hapus mengikuti CLAUDE.md §8.

---

## 7. Cleanup pasca-QA (grep-gated, surface DULU)

Hanya setelah tabel Tabulator lolos QA. Setiap hapus WAJIB grep lintas `resources/`+`routes/`+`app/` +
**findings dilaporkan ke user sebelum hapus** (presedan wajib).

- **Aman (bersama file/route lama):** renderer bespoke `renderFallbackRows`/`loadFallbackRows`/
  `initFallbackTableScroll`, endpoint shape-DataTables lama (`datatable()` + route `dashboard.pembayaran.data`)
  bila diganti endpoint baru, `window.DOCUMENT_STICKY_CONFIG`/sticky-CSS server bila digantikan frozen Tabulator.
- **Grep-gate DULU (mungkin dipakai luar tabel — mis. VA, rekapan, aksi masa depan):**
  `set-deadline`(pembayaran)/`update-status`/`upload-proof`/`payment-data`/`getDocumentDetail` + route-nya.
  Default PERTAHANKAN bila ragu; hapus hanya bila grep membuktikan yatim + user setuju.
- **JANGAN sentuh:** CsvImportController + import UI, export (Excel/PDF/per-vendor), rekapan-vendor,
  virtual-assistant, `FrozenColumnLayout` (dipakai lagi), kunci preferensi kolom.

---

## 8. DI LUAR SCOPE (tak disentuh, tetap jalan)

CSV import UI + `CsvImportController` · export Excel/PDF/per-vendor (`exportRekapan` dkk) · mode
`rekapan_table` vendor-grouped · virtual-assistant. Semua mengonsumsi preferensi kolom yang sama →
kunci penyimpanan dipertahankan (§4).

---

## 9. Testing & QA

- **Unit:** `PembayaranDocumentRowTest` — 3 cabang pill (belum_siap/siap/sudah, + varian casing CSV),
  edit-anywhere (`can_edit` true tanpa gate), CSV-include, tanpa objek deadline.
- **Feature:** endpoint `documents.pembayaran.data` → JSON `{last_page,total,data}` valid untuk viewer
  pembayaran; INCLUDE baris CSV; route lama dihapus → sesuai keputusan cleanup.
- **Suite hijau** sebelum tiap commit.
- **QA visual produksi (Playwright READ-ONLY, login `pembayaran`):** tabel muat, 0 error; pill 3-state
  cocok; **freeze 2-tab kiri/bebas/kanan berfungsi + persist**; kustomisasi kolom (pilih/urut) jalan;
  inline-edit **edit-anywhere** (dokumen belum sampai pun bisa) + `tanggal_dibayar`; copy/paste/undo (§8);
  kartu agregat Aman/Peringatan/Terlambat cocok; CSV rows tampil; export & CSV import masih jalan.
  **QA visual = tanggung jawab user.**

---

## 10. Gerbang kritis (berhenti & minta keputusan)

- **Freeze 2-tab** tak bisa direplikasi penuh di Tabulator native → surface sebelum kompromi UX.
- Menyentuh **engine global** `document-tabulator.js` (config `CFG.frozen` + formatter `paymentPill`) —
  wajib **aditif**, tak mengubah 4 role lain.
- Sebelum hapus kode aksi (§7) — surface findings grep.
- Perubahan menyentuh **kunci preferensi kolom** (dipakai export) — jangan ubah tanpa keputusan.
- QA visual gagal.

# CLAUDE.md — Agenda Online PTPN IV Regional V

Aplikasi pencatatan & alur persetujuan **dokumen pembayaran**, dari Operator sampai
Tim Pembayaran. Laravel 12, PHP `^8.2`, MySQL 8, Blade + Alpine.js + Bootstrap 5 (CDN).
Repo: `avsmenma/agenda-online-PTPN` — branch kerja `codinggemini`.

Aturan universal (gaya commit, `git add` per-file, bahasa) ada di `~/.claude/CLAUDE.md`.
Berkas ini hanya memuat yang **khas project ini**.

---

## 1. Kondisi Kodebase — baca sebelum menyentuh apa pun

Acuan lengkap: **`docs/AUDIT_KESEHATAN_KODEBASE_2026-07-03.md`** (audit 6 domain, ~144k baris, skor rata-rata ≈46/100).

> ### Keputusan final: REFAKTOR BERTAHAP. JANGAN rewrite dari 0.
> Pondasi sehat — skema relasional benar (FK cascade konsisten), middleware RBAC benar,
> nol SQL injection. Yang sakit adalah **duplikasi** dan **ukuran file**. Itu diekstrak,
> bukan dilahirkan ulang. Cakupan test masih rendah, jadi rewrite = menghancurkan ratusan
> aturan bisnis yang ter-enkode diam-diam.

### Penyakit utama — ingat ini setiap kali memperbaiki bug tabel

> **6 tabel dokumen per-role adalah ~73% salinan copy-paste satu sama lain, memakai
> 3 teknologi tabel berbeda** (Tabulator di operator, DataTables di pembayaran,
> tabel biasa + sticky di sisanya).
>
> Karena itu **memperbaiki 1 role tidak menyebar ke 5 role lain.** Kalau user melaporkan
> "bug ini sudah pernah diperbaiki tapi muncul lagi di halaman lain" — inilah sebabnya.
> Selalu tanyakan: perbaikan ini perlu disalin ke berapa file?

### Status Juli 2026 (kodebase sudah bergerak sejak audit — jangan kutip audit mentah-mentah)

| | Temuan audit | Status sekarang |
|---|---|---|
| ✅ | 6 lubang keamanan mendesak (§7 audit) | **Sudah ditutup** — autocomplete & `pengembalian-dokumens` kini ber-`auth`+`role`, `channels.php` punya otorisasi peran nyata, `DokumenDummySeeder` punya guard produksi, grup `dashboard-pembayaran` dihapus |
| ✅ | Tanpa CI | Ada `.github/workflows/tests.yml` |
| ✅ | `User::ROLES` kunci duplikat | Sudah dinormalisasi (alias ditangani `App\Support\Role::normalize()`) |
| ✅ | Test 9 file | Naik ke 21 file |
| ❌ | **`@vite` mati total** | Masih mati. `resources/css/app.css` & `resources/js/app.js` tetap dead asset. UI 100% dari CDN + CSS inline |
| ❌ | God-file | `layouts/app.blade.php` 5.968 baris. (`operator/daftarDokumen` 5.227 baris **sudah dihapus 2026-07-23** — operator kini Tabulator-only.) |
| ❌ | Duplikasi 6 tabel role | Belum disatukan |
| ❌ | `pint.json` | Belum ada |
| ❌ | `maatwebsite/excel ^1.1` | Masih rilis era 2015 di atas Laravel 12 |

**Sebelum melaporkan temuan audit sebagai masalah aktif, verifikasi dulu ke kode.**
Audit ini bertanggal 3 Juli 2026 dan sebagian sudah usang.

---

## 2. Peta Berkas

### Partial shared — HARAM dihapus tanpa grep lintas-role

Dipakai banyak role sekaligus. Menghapusnya merusak halaman yang tidak sedang Anda buka:

```
resources/views/partials/
  _inlineEditEngine.blade.php        mesin inline-edit (PATCH /documents/{id}/inline-update)
  _documentTableStickyCells.blade.php  frozen column
  _activeCellNav.blade.php           navigasi sel keyboard
  virtual-document-table.blade.php   infinite scroll (virtual_chunk)
  _compactDocumentTable.blade.php
  compact-document-ui.blade.php      dimuat GLOBAL dari layouts/app.blade.php
  document-handler-select.blade.php  dropdown Pengurus Dokumen
  document-workbench-ui.blade.php    panel Detail Cepat
```

`compact-document-ui` dan `document-workbench-ui` bersifat **global** — mengubahnya
menyentuh semua role sekaligus. Perubahan di sini wajib aditif (tambah, jangan ubah
jalur lama) kecuali user memutuskan lain.

### God-file — jangan tambah baris, ekstrak keluar

`layouts/app.blade.php` (5.968), `pembayaranNEW/dashboardPembayaran.blade.php` (3.970),
`OwnerDashboardController.php`. (`operator/dokumens/daftarDokumen.blade.php` sudah dihapus
2026-07-23 — operator kini Tabulator-only.) Kalau harus menambah CSS/JS di sini,
pertimbangkan file terpisah di `public/css` atau `public/js` lalu `@push`.

---

## 3. Aturan Kerja Wajib

1. **Jangan tambah salinan ke-7.** Sebelum copy-paste blok dari role lain, cek apakah
   bisa jadi partial shared. Duplikasi baru = memperparah penyakit utama.
2. **Sebelum menghapus apa pun**, grep dulu ke seluruh `resources/` dan `routes/`.
   Banyak partial/endpoint dipakai role yang tidak terlihat dari file yang sedang dibuka.
3. **Test sebelum refaktor.** Cakupan masih rendah; refaktor tanpa jaring pengaman
   pernah melahirkan regresi yang bocor ke produksi (auto-forward, import CSV).
   Jalankan `php artisan test` — suite harus hijau sebelum commit.
4. **Jangan tambah CSS inline baru.** Sudah ada 1.623 `!important`. Perang spesifisitas
   melawan Bootstrap CDN adalah utang, bukan solusi.
5. **Nol tebakan pada aturan bisnis.** Alur dokumen, deadline, dan hak per-role sudah
   diputuskan pemilik. Kalau tidak yakin — tanya, jangan karang.
6. **Jangan biarkan drift skema.** Migrasi baru wajib idempoten (guard kolom **dan**
   index). Contoh yang benar: `2026_01_26_000000_add_performance_indexes` (`indexExists()`).
   Jangan tambah guard `Schema::hasColumn()` baru di kode bisnis — itu membuat fitur
   mati diam-diam, bukan error keras.

---

## 4. Data & Server Produksi

Aplikasi ini **dipakai sungguhan** dan datanya tidak bisa dipulihkan sembarangan.

- **Jangan** jalankan perintah destruktif (`drop`, `rm -rf`, `reset --hard`,
  `truncate`) tanpa konfirmasi eksplisit user.
- **Jangan** jalankan `migrate:fresh` / `migrate:wipe` pada database berisi data.
- **Jangan** jalankan seeder dummy atau command penghapus massal
  (`data:clean`, `dokumen:clean-before-import`, `dokumen:clear`) di luar lokal.
- Sebelum menghapus/menimpa berkas, **baca dulu isinya**.
- Jika memang ingin dijalankan, tanyakan dulu ke user untuk mendapatkan izin

---

## 5. Alur Deploy

Setiap update yang sudah disetujui: **commit → push → pull di server → clear cache.**

```bash
git push origin codinggemini
# di server:
git pull
php artisan route:clear && php artisan view:clear && php artisan config:clear
```

Detail koneksi server ada di `deploy_to_server.bat`. **Clear cache tidak boleh dilewat** —
Blade & route yang ter-cache membuat perubahan tampak tidak berefek, lalu waktu terbuang
mencari bug yang sebenarnya tidak ada.

---

## 6. Gerbang Kritis — berhenti dan minta keputusan user

Untuk rencana yang sudah disetujui, tugas boleh berjalan berurutan tanpa menunggu
"lanjut". Tetapi **berhenti** bila:

- Acceptance sebuah tugas gagal.
- Muncul ambiguitas atau keputusan desain baru.
- Pekerjaan menyentuh **partial global** (`compact-document-ui`, `document-workbench-ui`,
  `layouts/app.blade.php`) — dampaknya lintas-role.
- Pekerjaan menyentuh **skema database**, **RBAC/route middleware**, atau **auto-forward**.
- Akan **menghapus** view/route/partial — perlu bukti grep + persetujuan.

**QA visual adalah tanggung jawab user.** Agent tidak punya browser atau sesi login;
paritas tampilan tidak pernah bisa diklaim selesai dari test backend saja. Nyatakan
dengan jujur apa yang sudah diuji dan apa yang belum.

---

## 7. Pekerjaan Berjalan

**Operator `/documents` sudah Tabulator-only — SELESAI & ter-deploy, QA lolos 2026-07-23.**
Tabel classic operator (`daftarDokumen.blade.php`, `_chunk`, `_tableRowsAjax`), cabang
`virtual_chunk`, flag `?classic`, dan route/method yatim (`getDocumentDetail`+route,
`sendToTeamVerifikasi`+route, method mati `operatorDocumentColumns`) sudah **dihapus permanen
dari kode — bukan dimatikan**. `/documents?classic=1` kini no-op (menyajikan Tabulator).
Forward operator→verifikasi lewat dropdown **Pengurus Dokumen** (`DocumentHandlerController::
moveDirectlyToTeamVerifikasi`, langsung diterima), BUKAN tombol "Kirim" lama (jalur
approval-gated `sendToInbox` sudah dihapus). Guard "Bagian wajib terisi" sebelum forward tetap.

- Pilot: `docs/superpowers/specs/2026-07-17-tabulator-operator-pilot-design.md`,
  `docs/superpowers/plans/2026-07-21-tabulator-operator-pilot.md`
- Pembersihan: `docs/superpowers/specs/2026-07-23-operator-tabulator-only-cleanup-design.md`,
  `docs/superpowers/plans/2026-07-23-operator-tabulator-only-cleanup.md`

**Akutansi `/documents/akutansi` sudah Tabulator-only — SELESAI & ter-deploy, QA lolos 2026-07-24
(Rollout 1).** Engine operator SUDAH diekstrak jadi komponen bersama: `public/js/document-tabulator.js`
(baca `window.DOCUMENT_TABULATOR_CONFIG`, mount via `CFG.mountId`, kolom tetap terparameter via
`CFG.extraColumns`) + basis DTO **`App\Support\DocumentRow`** yang diwarisi `OperatorDocumentRow` &
`AkutansiDocumentRow`. Badge Status & kolom Deadline akutansi **dihitung SERVER** di
`AkutansiDocumentRow` (objek `status_badge`/`deadline`); klien hanya merender (nol logika bisnis di JS).
Endpoint JSON `documents.akutansi.data` (query bersama `buildAkutansiQuery()`). View legacy
(`daftarAkutansi` 3995 baris, `_rows`, `_chunk`), cabang `?classic`/`virtual_chunk` **dihapus permanen**
(~4539 baris). Forward lewat dropdown **Pengurus Dokumen** (sama seperti operator). Filter toolbar
engine kini baca nama field dari DOM (generik lintas-role, bukan hardcode). `?classic=1` no-op (Tabulator).

- Spec/plan: `docs/superpowers/specs/2026-07-23-rollout-tabulator-akutansi-design.md`,
  `docs/superpowers/plans/2026-07-24-rollout-tabulator-akutansi.md`

**Perpajakan `/documents/perpajakan` sudah Tabulator-only — SELESAI & ter-deploy, QA lolos 2026-07-24
(Rollout 2).** Klon pola akutansi: `App\Support\PerpajakanDocumentRow` (badge+deadline server, bug laten
`$isBypassedToPembayaran` diperbaiki), endpoint `documents.perpajakan.data`, view `daftarPerpajakanTabulator`.
Forward via dropdown; gate wajib-isi NPWP/Faktur dibiarkan tanpa gate (perilaku live). View legacy
(`daftarPerpajakan` 4557/`_rows`/`_chunk`) + rute/method aksi mati (`set-deadline`/`send-to-next`/`return`
+ `setDeadline`/`returnDocument`) **dihapus**. **PENTING (pelajaran):** `sendToAkutansi()`+`sendToNext()`+rute
`send-to-akutansi` **DIPERTAHANKAN** — HIDUP dipakai halaman Pengembalian (`pengembalianPerpajakan.blade.php`),
grep gate menangkapnya sebelum salah hapus. `?classic=1` no-op (Tabulator).

- Spec/plan: `docs/superpowers/specs/2026-07-24-rollout-tabulator-perpajakan-design.md`,
  `docs/superpowers/plans/2026-07-24-rollout-tabulator-perpajakan.md`

**Belum dikerjakan — rollout Tabulator ke role verifikasi & pembayaran** (bagian sengaja
DILEWATI atas keputusan user). Masih pakai tabel lama. Program terpisah: engine Tabulator
bersama (`document-tabulator.js` + basis `DocumentRow`) diterapkan per-role → QA → hapus tabel
lama role itu. Pola akutansi/perpajakan jadi templat: DTO `<Role>DocumentRow` mewarisi `DocumentRow`,
endpoint `@datatable`, `extraColumns` per-role, port badge/deadline ke server. Verifikasi lebih rumit
(badge ~24 cabang + workflow **Paraf** khas). Pembayaran outlier (DataTables, kolom kustom, freeze 2-tab,
tanpa forward). SEBELUM hapus kode aksi "mati" tiap role: WAJIB grep gate — halaman Pengembalian sering
memakai rute yang tampak mati (lihat pelajaran perpajakan di atas). Kustomisasi kolom masih diduplikasi per-role (fondasi bersama:
`config/document_columns.php` + partial `document-role-filter-toolbar`); penyatuannya —
plus freeze ala pembayaran (modal 2-tab) — menyusul setelah rollout.

## 8. Hal Yang harus bisa dilakukan pada tabel tabulator

- Terdapat sel aktif yang dapat digerakkan dengan tombol panah pada keyboard, dan tabel akan otomatis menggulir mengikuti sel aktif. - Sel aktif dapat dipindahkan secara instan cukup dengan mengeklik sel yang diinginkan.
- Tersedia inline edit: arahkan sel aktif ke sel yang ingin diubah, tekan Enter untuk mulai mengedit, lalu tekan Enter lagi untuk menyimpan (atau Esc untuk membatalkan). Dobel-klik juga bisa dipakai untuk mulai mengedit.
- Data dapat disalin tanpa masuk mode edit: arahkan sel aktif ke sel yang ingin disalin, lalu tekan Ctrl+C.
- Data dapat dihapus tanpa masuk mode edit: arahkan sel aktif ke sel yang ingin dikosongkan, lalu tekan Delete atau Backspace.
- Beberapa sel dapat dipilih sekaligus (blok) dengan cara men-drag mouse, Shift+Klik, atau Shift+Panah.
- Seluruh isi blok dapat disalin sekaligus dengan Ctrl+C.
- Data hasil salinan dapat ditempel ke dalam blok dengan Ctrl+V, mengikuti aturan penempelan ala Excel.
- Setiap perubahan dapat dibatalkan dengan Ctrl+Z dan diulang kembali dengan Ctrl+Y.
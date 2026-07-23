# Hapus Program Aksi Mati Akutansi — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menghapus total program aksi mati akutansi (route + method + UI dormant untuk send-to-pembayaran / return / set-deadline) tanpa merusak halaman akutansi yang masih hidup.

**Architecture:** Dua penghapusan logis terpisah — (1) backend (3 route + 3 method controller), (2) UI mati di dalam view akutansi yang MASIH hidup (8 modal + 7 fungsi JS + sel aksi + plumbing `showActionColumn`) — lalu (3) verifikasi + QA. Bedah rapi: potongan hidup (kolom Deadline, getSearchSuggestions, modal error/warning) wajib utuh.

**Tech Stack:** Laravel 12, Blade, PHPUnit (SQLite in-memory dengan polyfill REGEXP/SUBSTRING_INDEX untuk sort natural nomor_agenda).

Spec sumber: `docs/superpowers/specs/2026-07-23-hapus-aksi-mati-akutansi-design.md`.

> **Nomor baris indikatif** (snapshot pra-edit; Task 1 menggeser offset method controller). **Jangkar sebenarnya = nama/blok yang ditunjuk** — cari berdasarkan isinya, bukan nomor baris.

## Global Constraints

- **Halaman akutansi (`documents.akutansi.index`) WAJIB tetap render normal** sesudahnya — kolom Deadline tampil, pencarian jalan, dropdown Pengurus jalan, inline-edit jalan, kustomisasi kolom jalan.
- **WAJIB TETAP (jangan kena):** kolom Deadline + `initializeDeadlines()` + render countdown; `getSearchSuggestions` (helper pencarian, di antara method yang dihapus); modal `#errorModal`/`#warningModal` + `showErrorModal`/`showWarningModal`; route `documents.akutansi.index`/`detail`; Inbox; method senama di controller LAIN (perpajakan/pembayaran/verifikasi), `DokumenRoleData::setDeadline` (Model), `AutoForwardDokumenService::sendToPembayaranAndApprove` (Service) — **tabrakan nama saja**.
- **JANGAN hapus berkas view akutansi** (`daftarAkutansi.blade.php`, `_rows.blade.php`) — masih hidup; hanya potongan mati di dalamnya. Penghapusan view utuh menyusul di rollout Tabulator (di luar lingkup).
- Char-counter `returnReason` menumpang `DOMContentLoaded` bersama yang memanggil `initializeDeadlines()` (hidup) — buang HANYA body char-counter-nya, pertahankan `DOMContentLoaded` + `initializeDeadlines()`.
- `git add` PER-FILE — JANGAN `git add .`. Pesan commit Bahasa Indonesia. Satu commit = satu perubahan logis. Komentar Indonesia, identifier English.
- `php artisan test` **hijau** sebelum tiap commit.
- JANGAN sentuh controller/view role lain, partial bersama, atau Inbox.
- **Risiko JS:** menghapus fungsi dari `<script>` inline bisa memunculkan error sintaks JS yang TIDAK tertangkap PHPUnit (hanya render HTML yang diuji). Validitas JS dibuktikan oleh **QA browser (0 error konsol)** — Task 3. Buang fungsi utuh (`function foo() {...}`) dengan batas jelas.
- JANGAN push/deploy sampai QA akutansi user lolos.

---

### Task 1: Hapus 3 route + 3 method backend yatim

**Files:**
- Modify: `routes/web.php:481-483` (hapus 3 route)
- Modify: `app/Http/Controllers/DashboardAkutansiController.php` (hapus method `setDeadline` ~519-680, `sendToPembayaran` ~1036-1148, `returnDocument` ~1224-1327; SISAKAN `getSearchSuggestions` ~1150-1222)
- Test: `tests/Feature/AkutansiHapusAksiMatiTest.php` (BUAT)

**Interfaces:**
- Consumes: —
- Produces: route bernama `documents.akutansi.{set-deadline,send-to-pembayaran,return}` tidak ada; method `setDeadline`/`sendToPembayaran`/`returnDocument` tidak ada di `DashboardAkutansiController`.

- [ ] **Step 1: Buat test berkas + assertion route yatim hilang (RED)**

Buat `tests/Feature/AkutansiHapusAksiMatiTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Menguji pembersihan program aksi mati akutansi (send-to-pembayaran / return /
 * set-deadline): route+method backend yatim dihapus, UI dormant dibuang, DAN
 * halaman akutansi tetap render normal (potongan hidup — kolom Deadline dsb — utuh).
 */
class AkutansiHapusAksiMatiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // dokumens() memakai fungsi MySQL (REGEXP, SUBSTRING_INDEX) di ORDER BY
        // nomor_agenda — polyfill untuk SQLite (sama seperti OperatorTabulatorViewTest).
        $pdo = \DB::connection()->getPdo();
        if ($pdo->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $pdo->sqliteCreateFunction('regexp', function ($pattern, $value) {
                return preg_match('/' . $pattern . '/u', (string) $value) ? 1 : 0;
            });
            $pdo->sqliteCreateFunction('substring_index', function ($str, $delim, $count) {
                $parts = explode($delim, (string) $str);
                return implode($delim, array_slice($parts, 0, (int) $count));
            });
        }
    }

    private function akutansi(): User
    {
        return User::factory()->create(['role' => 'akutansi']);
    }

    public function test_route_aksi_mati_akutansi_dihapus(): void
    {
        $router = app('router');

        $this->assertFalse($router->has('documents.akutansi.set-deadline'));
        $this->assertFalse($router->has('documents.akutansi.send-to-pembayaran'));
        $this->assertFalse($router->has('documents.akutansi.return'));

        // Yang hidup tetap ada:
        $this->assertTrue($router->has('documents.akutansi.index'));
        $this->assertTrue($router->has('documents.akutansi.detail'));
    }
}
```

- [ ] **Step 2: Jalankan test → GAGAL**

Run: `php artisan test --filter=test_route_aksi_mati_akutansi_dihapus`
Expected: FAIL — ketiga route masih terdaftar (assertFalse gagal).

- [ ] **Step 3: Hapus 3 route di `routes/web.php`**

Hapus baris 481-483 (di dalam grup `documents.akutansi.` yang dibuka `:476`):

```php
    Route::post('/{dokumen}/set-deadline', [DashboardAkutansiController::class, 'setDeadline'])->name('set-deadline');
    Route::post('/{dokumen}/send-to-pembayaran', [DashboardAkutansiController::class, 'sendToPembayaran'])->name('send-to-pembayaran');
    Route::post('/{dokumen}/return', [DashboardAkutansiController::class, 'returnDocument'])->name('return');
```

Biarkan `index` (:477), komentar create/store (:478-479), dan `detail` (:480) apa adanya.

- [ ] **Step 4: Hapus 3 method di `DashboardAkutansiController.php`**

Buka berkas, cari dan hapus SETIAP method utuh (docblock di atasnya sampai `}` penutup method):
- `public function setDeadline(` … (sekitar 519-680)
- `public function sendToPembayaran(` … (sekitar 1036-1148)
- `public function returnDocument(` … (sekitar 1224-1327) — method TERAKHIR sebelum `}` penutup KELAS; jangan hapus brace penutup kelas.

⚠️ **JANGAN hapus `private function getSearchSuggestions(` (sekitar 1150-1222)** — ia duduk di antara `sendToPembayaran` dan `returnDocument` dan MASIH DIPAKAI pencarian. Penghapusan tidak kontigu: hapus `sendToPembayaran`, LOMPATI `getSearchSuggestions`, lalu hapus `returnDocument`.

- [ ] **Step 5: Jalankan test filter + suite penuh → LULUS**

Run: `php artisan test --filter=test_route_aksi_mati_akutansi_dihapus`
Expected: PASS.
Run: `php artisan test`
Expected: PASS semua (+1 test baru; nol test lama memanggil method yang dihapus).

- [ ] **Step 6: Commit (per-file)**

```bash
git add routes/web.php
git add app/Http/Controllers/DashboardAkutansiController.php
git add tests/Feature/AkutansiHapusAksiMatiTest.php
git commit -m "refactor(akutansi): hapus route & method aksi mati (send-to-pembayaran/return/set-deadline)"
```

---

### Task 2: Hapus UI aksi mati dari view (yang masih hidup) + plumbing

**Files:**
- Modify: `resources/views/akutansi/dokumens/daftarAkutansi.blade.php` (8 modal + 7 fungsi JS + header Aksi + char-counter deadlineNote + `$showActionColumn = false`)
- Modify: `resources/views/akutansi/dokumens/_rows.blade.php` (sel aksi + default `$showActionColumn`)
- Modify: `app/Http/Controllers/DashboardAkutansiController.php` (argumen `'showActionColumn' => false` pada render `_chunk`)
- Test: `tests/Feature/AkutansiHapusAksiMatiTest.php` (tambah test render)

**Interfaces:**
- Consumes: hasil Task 1 (backend sudah tiada).
- Produces: view akutansi tak lagi memuat UI aksi mati; halaman tetap render normal.

- [ ] **Step 1: Tambah test render (RED)**

Tambahkan ke `tests/Feature/AkutansiHapusAksiMatiTest.php`:

```php
    public function test_halaman_akutansi_render_tanpa_ui_aksi_mati(): void
    {
        $response = $this->actingAs($this->akutansi())
            ->get(route('documents.akutansi.index'));

        $response->assertOk();

        // Potongan HIDUP tetap ada — bukti tak kebablasan menghapus:
        $response->assertSee('Deadline', false);            // header kolom Deadline
        $response->assertSee('columnCustomizationModal', false); // modal kustomisasi kolom (hidup)

        // UI aksi MATI sudah tiada:
        $response->assertDontSee('setDeadlineModal', false);
        $response->assertDontSee('sendToPembayaranModal', false);
        $response->assertDontSee('returnModal', false);
        $response->assertDontSee('function sendToPembayaran(', false);
        $response->assertDontSee('function confirmReturn(', false);
        $response->assertDontSee('function confirmSetDeadline(', false);
    }
```

- [ ] **Step 2: Jalankan test → GAGAL**

Run: `php artisan test --filter=test_halaman_akutansi_render_tanpa_ui_aksi_mati`
Expected: FAIL pada assertDontSee — modal/fungsi mati masih ada di HTML.
> Jika `assertOk()` sendiri GAGAL (500) sebelum perubahan apa pun, berarti view akutansi tak bisa render di lingkungan test (mungkin butuh data/seed). Laporkan BLOCKED dengan pesan errornya — jangan paksa; kita sesuaikan (seed minimal) atau turunkan ke gerbang grep+QA.

- [ ] **Step 3: Hapus UI mati di `daftarAkutansi.blade.php`**

Cari berdasarkan isi (nama/id), hapus utuh:
- Header kolom aksi: blok `@if($showActionColumn) <th ...>…Aksi…</th> @endif` (~2205-2207).
- 8 modal mati (blok kontigu ~2217-2543): `#setDeadlineModal`, `#deadlineSuccessModal`, `#returnModal`, `#returnConfirmationModal`, `#returnSuccessModal`, `#returnValidationWarningModal`, `#sendToPembayaranModal`, `#sendToPembayaranSuccessModal`.
- Fungsi JS (masing-masing `function …(…) { … }` utuh): `sendToPembayaran` (~2780-2806), `selectDestination` (~2808-2819), `confirmSendToPembayaran` (~2821-2879), `openSetDeadlineModal` (~2917-2924), `confirmSetDeadline` (~2937-3052), `openReturnModal` (~3344-3350), `confirmReturn` (~3353-3433).
- Listener char-counter `deadlineNote` (~2913-2915) — tak ber-guard, buang bersama modal set-deadline.
- Baris `$showActionColumn = false;` (~2175).
- (opsional) stub mati `openReturnToPerpajakanModal` (~2926-2935).

⚠️ **JANGAN sentuh:**
- Char-counter `returnReason` (~3327-3340): buang **HANYA body-nya**, PERTAHANKAN blok `DOMContentLoaded` pembungkusnya (~3324-3341) yang memanggil `initializeDeadlines()` (~3325) — LIVE.
- `initializeDeadlines()` + render kartu deadline (~3242-3321) — LIVE (kolom Deadline).
- `#errorModal`/`#warningModal` + `showErrorModal`/`showWarningModal` (~3053+) — helper umum.

- [ ] **Step 4: Hapus sel aksi di `_rows.blade.php`**

- Hapus blok `@if($showActionColumn) <td class="col-action">…@endif` (~516-582).
- Hapus/ubah default `$showActionColumn` di baris 1 (kini tak ada lagi `@if($showActionColumn)` di berkas ini). Jika baris 1 hanya menyetel default itu, hapus barisnya.
- **JANGAN sentuh** kolom Deadline (`<td class="col-deadline">` ~170-411) — LIVE.

- [ ] **Step 5: Hapus argumen `showActionColumn` di render `_chunk`**

Di `DashboardAkutansiController.php` (~baris 349-355, cabang `virtual_chunk`), hapus argumen `'showActionColumn' => false,` dari array yang dikirim ke `view('akutansi.dokumens._chunk', [...])`. (Variabelnya kini fully dead.)

- [ ] **Step 6: Jalankan test render + suite penuh → LULUS**

Run: `php artisan test --filter=test_halaman_akutansi_render_tanpa_ui_aksi_mati`
Expected: PASS (render 200, potongan hidup ada, UI mati tiada).
Run: `php artisan test`
Expected: PASS semua.

- [ ] **Step 7: Commit (per-file)**

```bash
git add resources/views/akutansi/dokumens/daftarAkutansi.blade.php
git add resources/views/akutansi/dokumens/_rows.blade.php
git add app/Http/Controllers/DashboardAkutansiController.php
git add tests/Feature/AkutansiHapusAksiMatiTest.php
git commit -m "refactor(akutansi): hapus UI aksi mati (8 modal + fungsi JS + sel aksi) dari view hidup"
```

---

### Task 3: Sapuan verifikasi akhir + serah-terima QA

**Files:** — (gerbang akhir, tanpa perubahan kode)

**Interfaces:**
- Consumes: hasil Task 1-2.
- Produces: bukti verifikasi + checklist QA untuk user.

- [ ] **Step 1: Gerbang grep — dead symbols hilang dari path akutansi**

Run:
```bash
grep -rn "sendToPembayaran\|confirmSendToPembayaran\|selectDestination\|openSetDeadlineModal\|confirmSetDeadline\|openReturnModal\|confirmReturn\|showActionColumn\|setDeadlineModal\|sendToPembayaranModal\|returnModal" routes/ app/Http/Controllers/DashboardAkutansiController.php resources/views/akutansi/
grep -rn "documents\.akutansi\.\(set-deadline\|send-to-pembayaran\|return\)\|/set-deadline\|/send-to-pembayaran\|akutansi/.*/return" resources/views/akutansi/ app/Http/Controllers/DashboardAkutansiController.php
```
Expected: KOSONG untuk semua simbol di atas pada path akutansi. (`returnDocument`/`setDeadline` masih WAJAR muncul di controller LAIN — perpajakan/pembayaran/verifikasi — dan `DokumenRoleData::setDeadline`/`AutoForwardDokumenService`; itu di luar scope grep di atas. Jika ada sisa tak terduga di path akutansi, hentikan & periksa.)

- [ ] **Step 2: Suite penuh hijau**

Run: `php artisan test`
Expected: PASS semua; catat `Tests: N passed`.

- [ ] **Step 3: Serahkan QA akutansi ke user (WAJIB sebelum deploy)**

Laporkan ke user untuk uji di browser pada halaman **daftar dokumen akutansi** (view masih hidup — belum diganti Tabulator):
1. Tabel **render normal**, tak ada yang rusak/kosong.
2. **Kolom Deadline** tetap tampil dengan hitung umur.
3. **Pencarian** jalan (bukti `getSearchSuggestions` utuh).
4. **Dropdown Pengurus Dokumen** jalan (kirim/kembalikan lewat sini).
5. **Inline-edit** sel jalan; **kustomisasi kolom** jalan.
6. **0 error di konsol browser** (bukti pembuangan fungsi JS tak meninggalkan error sintaks).

- [ ] **Step 4: Deploy SETELAH user konfirmasi QA lolos**

```bash
git push origin codinggemini
# di server:
git pull
php artisan route:clear && php artisan view:clear && php artisan config:clear
```

> Clear cache tidak boleh dilewat.

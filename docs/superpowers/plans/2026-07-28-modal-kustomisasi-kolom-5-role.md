# Modal Kustomisasi Kolom 5 Role — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Kelima role keuangan (operator, akutansi, perpajakan, verifikasi, pembayaran) memakai satu modal Kustomisasi Kolom bertab-dua — Kolom Tabel + Kolom Beku — menggantikan modal 1-tab di 4 role dan salinan inline ~868 baris di god-file pembayaran.

**Architecture:** Tiga lapis sudah ada dan tidak diubah (`document-tabulator.js` sudah membaca `cfg.frozen`; `App\Support\FrozenColumnLayout` sudah menormalkan & mengurutkan). Yang dikerjakan: satu kelas server baru `App\Support\ColumnCustomization` untuk resolusi preferensi beku, tab kedua pada partial + JS bersama, lalu penyambungan 5 role. Rollout 2 deploy: 4 role dulu, pembayaran menyusul.

**Tech Stack:** Laravel 12, PHP ^8.2, Blade, JavaScript statis di `public/js` (tanpa Vite — `@vite` mati di project ini), Tabulator 6 (CDN lokal `public/vendor/tabulator`), PHPUnit via `php artisan test`.

**Spec:** `docs/superpowers/specs/2026-07-28-modal-kustomisasi-kolom-5-role-design.md`

## Global Constraints

- **Commit per-file.** `git add <path>` satu per satu. **JANGAN** `git add .` / `git add -A`.
- **Pesan commit Bahasa Indonesia.** Satu commit = satu perubahan logis.
- **UI & komentar Bahasa Indonesia, identifier English.**
- **Suite wajib hijau sebelum tiap commit:** `php artisan test`.
- **CSS modal WAJIB lewat `@push('styles')`** — bukan `<style>` inline di body. Regresi flash-of-unstyled-modal pernah terjadi 2026-07-28 karena aturan `display:none` ter-parse setelah markup.
- **Jangan tambah CSS inline baru di Blade** selain di dalam blok `@push('styles')` partial yang sudah ada.
- **Nama ter-reservasi query (4):** `columns[]`, `frozen_config`, `frozen_left[]`, `frozen_right[]`. `enable_customization` DIHAPUS (parameter mati).
- **Nomor Agenda selalu beku kiri.** `document-tabulator.js` membekukannya tanpa syarat; server wajib menjaga konsistensi ini.
- **Jangan sentuh** skema DB, RBAC/route middleware, auto-forward, `#filterForm` (dipakai partial global `document-workbench-ui`).
- **Deploy** (hanya bila user menyetujui): `git push origin codinggemini`, lalu di server `git pull && php artisan route:clear && php artisan view:clear && php artisan config:clear`.

---

## File Structure

| Berkas | Tanggung jawab | Tugas |
|---|---|---|
| `app/Support/ColumnCustomization.php` | **BARU.** Resolusi preferensi kolom beku: baca request/DB/sesi, paksa kolom pinned, normalisasi, hasilkan urutan render. Murni — user dioper, tidak memanggil `Auth`. | 1 |
| `tests/Unit/ColumnCustomizationTest.php` | **BARU.** Unit test kelas di atas. | 1 |
| `resources/views/partials/_columnCustomizationModal.blade.php` | Markup modal bersama + CSS via `@push('styles')` + jembatan `window.COLUMN_CUSTOMIZATION_CONFIG`. Ditambah tab bar & panel Beku. | 2 |
| `public/js/column-customization.js` | Seluruh logika modal. Ditambah: Simpan lewat URL, perbaikan bug drag, state & render tab Beku. | 3, 4 |
| `app/Http/Controllers/DokumenController.php` | Operator — sambungkan beku (sesi). | 5 |
| `app/Http/Controllers/DashboardAkutansiController.php` | Akutansi — sambungkan beku (DB). | 5 |
| `app/Http/Controllers/DashboardPerpajakanController.php` | Perpajakan — sambungkan beku (DB). | 5 |
| `app/Http/Controllers/TeamVerifikasiController.php` | Verifikasi — sambungkan beku (DB). | 5 |
| 4 view `daftar*Tabulator.blade.php` | Kirim `frozen` + pakai urutan render untuk `columns`. | 5 |
| `tests/Feature/ColumnCustomizationSharedTest.php` | Diperluas: tab Beku, urutan CSS, dua-urutan kolom. | 2, 5 |
| `resources/views/pembayaranNEW/dashboardPembayaran.blade.php` | Migrasi ke partial+JS bersama; hapus modal inline. | 6 |
| `app/Http/Controllers/DashboardPembayaranController.php` | Ganti blok beku bespoke dengan `ColumnCustomization`. | 6 |
| `CLAUDE.md` | Catat penyatuan selesai. | 7 |

---

## Catatan cakupan `ColumnCustomization` (baca sebelum Task 1)

Spec §3.1 menggambarkan kelas ini menangani preferensi **kolom + beku**. Rencana ini
mempersempitnya ke **beku saja**, dengan alasan:

- Logika pemilihan kolom sudah berbeda-beda per role dan sudah bekerja: operator punya
  `isLegacyOperatorDefaultColumns()` (memetakan 2 set default lama ke default baru),
  akutansi/perpajakan/verifikasi menyaring `status` + `nomor_mirror`, pembayaran memaksa
  `tanggal_dibayar` selalu ikut. Menyeragamkannya = menulis ulang 5 jalur yang sudah
  jalan, demi nol manfaat bagi user.
- Duplikasi logika kolom itu **sudah ada sebelum fitur ini** — bukan salinan baru yang
  kita lahirkan. Yang dilarang §3 CLAUDE.md adalah menambah salinan, dan logika **beku**
  itulah yang akan tersalin 5× kalau tidak diekstrak.

Hasilnya: blast radius jauh lebih kecil, tujuan spec (nol salinan logika beku) tetap
tercapai. Penyatuan logika pemilihan kolom masuk daftar utang, bukan bagian rencana ini.

---

## Deploy 1 — 4 role (pembayaran belum disentuh)

### Task 1: Kelas `App\Support\ColumnCustomization`

**Files:**
- Create: `app/Support/ColumnCustomization.php`
- Test: `tests/Unit/ColumnCustomizationTest.php`

**Interfaces:**
- Consumes: `App\Support\FrozenColumnLayout::normalize(array $left, array $right, array $selected, array $available): array{left: array<int,string>, right: array<int,string>}` dan `::renderOrder(array $selected, array $left, array $right): array<int,string>` — keduanya sudah ada, jangan diubah.
- Produces: `ColumnCustomization::resolveFrozen(Request $request, ?Authenticatable $user, array $options): array{left: array<int,string>, right: array<int,string>, render: array<int,string>}`.
  `$options` bertipe:
  `['available' => array<string,string>, 'selected' => array<int,string>, 'default' => array{left: array<int,string>, right: array<int,string>}, 'pinnedLeft' => array<int,string>, 'prefKey' => ?string, 'sessionKey' => string]`.
  `prefKey` null berarti simpan hanya ke sesi (dipakai operator).

- [ ] **Step 1: Tulis test yang gagal**

Buat `tests/Unit/ColumnCustomizationTest.php`:

```php
<?php

namespace Tests\Unit;

use App\Support\ColumnCustomization;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Kontrak resolusi preferensi kolom beku bersama (dipakai 5 role keuangan).
 */
class ColumnCustomizationTest extends TestCase
{
    /** Peta kolom tersedia yang dipakai semua kasus uji. */
    private function available(): array
    {
        return [
            'nomor_agenda' => 'Nomor Agenda',
            'nomor_spp'    => 'Nomor SPP',
            'nilai_rupiah' => 'Nilai Rupiah',
            'keterangan'   => 'Keterangan',
        ];
    }

    private function options(array $overrides = []): array
    {
        return array_merge([
            'available'  => $this->available(),
            'selected'   => ['nomor_agenda', 'nomor_spp', 'nilai_rupiah'],
            'default'    => ['left' => ['nomor_agenda'], 'right' => []],
            'pinnedLeft' => ['nomor_agenda'],
            'prefKey'    => null,
            'sessionKey' => 'uji_frozen',
        ], $overrides);
    }

    public function test_tanpa_request_memakai_default(): void
    {
        $hasil = ColumnCustomization::resolveFrozen(Request::create('/'), null, $this->options());

        $this->assertSame(['nomor_agenda'], $hasil['left']);
        $this->assertSame([], $hasil['right']);
    }

    public function test_request_membekukan_kolom_kanan(): void
    {
        $request = Request::create('/', 'GET', [
            'frozen_config' => '1',
            'frozen_left'   => ['nomor_agenda'],
            'frozen_right'  => ['nilai_rupiah'],
        ]);

        $hasil = ColumnCustomization::resolveFrozen($request, null, $this->options());

        $this->assertSame(['nomor_agenda'], $hasil['left']);
        $this->assertSame(['nilai_rupiah'], $hasil['right']);
        // Urutan render: beku kiri -> bebas -> beku kanan.
        $this->assertSame(['nomor_agenda', 'nomor_spp', 'nilai_rupiah'], $hasil['render']);
    }

    /**
     * Inti keberadaan penanda frozen_config: "user melepas SEMUA kolom beku"
     * harus bisa dibedakan dari "request tak membawa konfigurasi beku".
     */
    public function test_melepas_semua_beku_tidak_dipulihkan_dari_preferensi(): void
    {
        session(['uji_frozen' => ['left' => ['nomor_spp'], 'right' => ['nilai_rupiah']]]);

        $request = Request::create('/', 'GET', ['frozen_config' => '1']);
        $hasil = ColumnCustomization::resolveFrozen($request, null, $this->options());

        // Hanya kolom pinned yang tersisa; sisanya benar-benar lepas.
        $this->assertSame(['nomor_agenda'], $hasil['left']);
        $this->assertSame([], $hasil['right']);
    }

    public function test_kolom_pinned_dipaksa_masuk_kiri_meski_diminta_kanan(): void
    {
        $request = Request::create('/', 'GET', [
            'frozen_config' => '1',
            'frozen_left'   => [],
            'frozen_right'  => ['nomor_agenda'],
        ]);

        $hasil = ColumnCustomization::resolveFrozen($request, null, $this->options());

        $this->assertSame(['nomor_agenda'], $hasil['left']);
        $this->assertNotContains('nomor_agenda', $hasil['right']);
    }

    public function test_kolom_beku_yang_disembunyikan_ikut_lepas(): void
    {
        $request = Request::create('/', 'GET', [
            'frozen_config' => '1',
            'frozen_left'   => ['nomor_agenda'],
            'frozen_right'  => ['keterangan'], // tidak ada di 'selected'
        ]);

        $hasil = ColumnCustomization::resolveFrozen($request, null, $this->options());

        $this->assertSame([], $hasil['right']);
    }

    public function test_hasil_disimpan_ke_sesi_saat_request_membawa_konfigurasi(): void
    {
        $request = Request::create('/', 'GET', [
            'frozen_config' => '1',
            'frozen_left'   => ['nomor_agenda'],
            'frozen_right'  => ['nilai_rupiah'],
        ]);

        ColumnCustomization::resolveFrozen($request, null, $this->options());

        $this->assertSame(
            ['left' => ['nomor_agenda'], 'right' => ['nilai_rupiah']],
            session('uji_frozen')
        );
    }

    public function test_preferensi_sesi_dipakai_saat_request_kosong(): void
    {
        session(['uji_frozen' => ['left' => ['nomor_agenda'], 'right' => ['nilai_rupiah']]]);

        $hasil = ColumnCustomization::resolveFrozen(Request::create('/'), null, $this->options());

        $this->assertSame(['nilai_rupiah'], $hasil['right']);
    }
}
```

- [ ] **Step 2: Jalankan test, pastikan GAGAL**

Run: `php artisan test --filter=ColumnCustomizationTest`
Expected: FAIL — `Class "App\Support\ColumnCustomization" not found`.

- [ ] **Step 3: Tulis implementasi minimal**

Buat `app/Support/ColumnCustomization.php`:

```php
<?php

namespace App\Support;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;

/**
 * Resolusi preferensi KOLOM BEKU bersama untuk 5 role keuangan.
 *
 * Cakupan sengaja dibatasi ke kolom beku saja — pemilihan kolom biasa tetap
 * di controller masing-masing karena aturannya memang berbeda per role
 * (legacy default operator, penyaringan status/nomor_mirror, dsb).
 *
 * Kelas biasa, bukan trait: logikanya murni sehingga bisa di-unit-test tanpa
 * kelas inang. $user dioper eksplisit supaya test tak perlu menyalakan sesi auth.
 */
class ColumnCustomization
{
    /**
     * @param  array{
     *     available: array<string,string>,
     *     selected: array<int,string>,
     *     default: array{left: array<int,string>, right: array<int,string>},
     *     pinnedLeft: array<int,string>,
     *     prefKey: ?string,
     *     sessionKey: string
     * }  $options
     * @return array{left: array<int,string>, right: array<int,string>, render: array<int,string>}
     */
    public static function resolveFrozen(Request $request, ?Authenticatable $user, array $options): array
    {
        $available  = $options['available'];
        $selected   = array_values($options['selected']);
        $default    = $options['default'];
        $pinnedLeft = $options['pinnedLeft'] ?? [];
        $prefKey    = $options['prefKey'] ?? null;
        $sessionKey = $options['sessionKey'];

        // Penanda WAJIB: tanpa frozen_config, "user melepas SEMUA kolom beku"
        // (tidak mengirim frozen_left/frozen_right) tampak sama persis dengan
        // "request tidak membawa konfigurasi beku" — preferensi lama akan dipakai
        // ulang dan user tak pernah bisa mengosongkan kolom beku.
        $hasFrozenRequest = $request->has('frozen_config')
            || $request->has('frozen_left')
            || $request->has('frozen_right');

        if ($hasFrozenRequest) {
            $raw = [
                'left'  => (array) $request->get('frozen_left', []),
                'right' => (array) $request->get('frozen_right', []),
            ];
        } else {
            $raw = self::readStored($user, $prefKey, $sessionKey, $default);
        }

        // Kolom pinned selalu beku kiri: mesin tabel (document-tabulator.js)
        // membekukan nomor_agenda tanpa syarat, jadi urutan render wajib sejalan
        // agar kolom beku tetap menempel tepi.
        $left = array_values((array) ($raw['left'] ?? []));
        foreach (array_reverse($pinnedLeft) as $key) {
            $left = array_values(array_diff($left, [$key]));
            array_unshift($left, $key);
        }

        $right = array_values(array_diff((array) ($raw['right'] ?? []), $pinnedLeft));

        $frozen = FrozenColumnLayout::normalize($left, $right, $selected, $available);

        if ($hasFrozenRequest && $user !== null && $prefKey !== null) {
            $preferences = $user->table_columns_preferences ?? [];
            $preferences[$prefKey] = $frozen;
            $user->table_columns_preferences = $preferences;
            $user->save();
        }

        session([$sessionKey => $frozen]);

        return [
            'left'   => $frozen['left'],
            'right'  => $frozen['right'],
            'render' => FrozenColumnLayout::renderOrder($selected, $frozen['left'], $frozen['right']),
        ];
    }

    /**
     * Urutan baca preferensi tersimpan: DB (permanen) -> sesi -> default.
     *
     * @return array{left: array<int,string>, right: array<int,string>}
     */
    private static function readStored(?Authenticatable $user, ?string $prefKey, string $sessionKey, array $default): array
    {
        if ($user !== null && $prefKey !== null && isset($user->table_columns_preferences[$prefKey])) {
            $stored = $user->table_columns_preferences[$prefKey];

            if (is_array($stored)) {
                return $stored;
            }
        }

        $stored = session($sessionKey, $default);

        return is_array($stored) ? $stored : $default;
    }
}
```

- [ ] **Step 4: Jalankan test, pastikan LULUS**

Run: `php artisan test --filter=ColumnCustomizationTest`
Expected: PASS, 7 test.

- [ ] **Step 5: Jalankan seluruh suite**

Run: `php artisan test`
Expected: seluruhnya hijau (baseline 252 test sebelum tugas ini).

- [ ] **Step 6: Commit**

```bash
git add app/Support/ColumnCustomization.php
git add tests/Unit/ColumnCustomizationTest.php
git commit -m "feat(support): kelas ColumnCustomization untuk resolusi kolom beku bersama

Mengekstrak logika kolom beku yang selama ini hanya ada di
DashboardPembayaranController, agar 4 role lain tidak menyalinnya.

Penanda frozen_config dipertahankan: tanpa itu 'lepas semua kolom beku' tak
bisa dibedakan dari 'request tanpa konfigurasi beku'. Kolom pinned
(nomor_agenda) dipaksa ke kiri agar sejalan dengan hardcode di
document-tabulator.js."
```

---

### Task 2: Tab Kolom Beku pada partial bersama

**Files:**
- Modify: `resources/views/partials/_columnCustomizationModal.blade.php`
- Test: `tests/Feature/ColumnCustomizationSharedTest.php`

**Interfaces:**
- Consumes: variabel Blade `$availableColumns`, `$selectedColumns` (sudah ada), plus **baru** `$frozenColumns` (`array{left: array, right: array}`) dan `$pinnedColumns` (`array<int,string>`). Keduanya punya default defensif sehingga partial tetap merender tanpa Task 5.
- Produces: elemen DOM yang dipakai Task 3 & 4 — `#tabPanelKolom`, `#tabPanelBeku`, `#frozenList`, `#frozenWarning`, tombol `.column-tab[data-tab="kolom"|"beku"]`; serta kunci `frozen` & `pinned` di `window.COLUMN_CUSTOMIZATION_CONFIG`.

- [ ] **Step 1: Tulis test yang gagal**

Tambahkan ke `tests/Feature/ColumnCustomizationSharedTest.php` (di dalam kelas yang sudah ada):

```php
    /**
     * Tab kedua (Kolom Beku) hadir di keempat role, dan CSS-nya tetap lewat
     * @push('styles') — urutan CSS sebelum markup adalah jaring
     * flash-of-unstyled-modal yang sudah dipasang 2026-07-28.
     */
    public function test_tab_kolom_beku_hadir_di_semua_view(): void
    {
        $cases = [
            ['role' => 'operator',        'route' => 'documents.index'],
            ['role' => 'akutansi',        'route' => 'documents.akutansi.index'],
            ['role' => 'perpajakan',      'route' => 'documents.perpajakan.index'],
            ['role' => 'team_verifikasi', 'route' => 'documents.verifikasi.index'],
        ];

        foreach ($cases as $c) {
            $user = User::factory()->create(['role' => $c['role']]);
            $res = $this->actingAs($user)->get(route($c['route']));
            $res->assertOk();

            $res->assertSee('data-tab="kolom"', false);
            $res->assertSee('data-tab="beku"', false);
            $res->assertSee('id="tabPanelBeku"', false);
            $res->assertSee('id="frozenList"', false);
            $res->assertSee('id="frozenWarning"', false);
            // Jembatan data untuk tab Beku.
            $res->assertSee('frozen:', false);
            $res->assertSee('pinned:', false);

            // CSS tab wajib sampai <head> SEBELUM markup modal.
            $res->assertSeeInOrder([
                '.column-tabs {',
                'id="columnCustomizationModal"',
            ], false);
        }
    }
```

- [ ] **Step 2: Jalankan test, pastikan GAGAL**

Run: `php artisan test --filter=test_tab_kolom_beku_hadir_di_semua_view`
Expected: FAIL — `data-tab="kolom"` tidak ditemukan.

- [ ] **Step 3: Tambah default defensif untuk variabel baru**

Di `_columnCustomizationModal.blade.php`, ganti blok `@php` (baris 7–10) menjadi:

```blade
@php
    $availableColumns = $availableColumns ?? [];
    $selectedColumns = $selectedColumns ?? [];
    // Kolom beku (tab kedua). Default defensif: partial tetap merender walau
    // controller role belum mengirimnya.
    $frozenColumns = $frozenColumns ?? ['left' => [], 'right' => []];
    // Kolom yang TIDAK boleh dilepas dari beku kiri — document-tabulator.js
    // membekukan nomor_agenda tanpa syarat, jadi kontrolnya dimatikan di modal.
    $pinnedColumns = $pinnedColumns ?? ['nomor_agenda'];
@endphp
```

- [ ] **Step 4: Sisipkan tab bar setelah header modal**

Tepat setelah `</div>` penutup `.modal-header-custom` (baris 18) dan sebelum `<div class="modal-body-custom">`, sisipkan:

```blade
        <div class="column-tabs">
            <button type="button" class="column-tab active" data-tab="kolom" onclick="switchColumnTab('kolom')">
                <i class="fa-solid fa-table-columns"></i> Kolom Tabel
            </button>
            <button type="button" class="column-tab" data-tab="beku" onclick="switchColumnTab('beku')">
                <i class="fa-solid fa-thumbtack"></i> Kolom Beku
            </button>
        </div>
```

- [ ] **Step 5: Bungkus grid lama sebagai panel tab pertama, tambah panel kedua**

Di dalam `.modal-body-custom`, bungkus `<div class="customization-grid">…</div>` yang sudah ada dengan `<div id="tabPanelKolom">`, lalu tambahkan panel kedua **setelah** penutupnya (tetap di dalam `.modal-body-custom`):

```blade
            <div id="tabPanelBeku" style="display:none;">
                <div class="panel-description">
                    Tentukan kolom mana yang tetap terlihat saat tabel digulir ke samping.
                    Kolom beku otomatis dipindahkan ke tepi tabel.
                </div>
                <div id="frozenWarning" class="frozen-warning" style="display:none;"></div>
                <div id="frozenList"></div>
            </div>
```

- [ ] **Step 6: Tambah CSS tab ke dalam blok `@push('styles')` yang sudah ada**

Sisipkan sebelum blok `@media (max-width: 768px)` di dalam `<style>` yang sudah ada
(**jangan** membuat blok `<style>` baru):

```css
    .column-tabs { display: flex; gap: 0.5rem; padding: 0 1.5rem; border-bottom: 1px solid #e2e8f0; }
    .column-tab { padding: 0.75rem 1.1rem; border: none; background: transparent; font-size: 0.85rem; font-weight: 700; color: #64748b; cursor: pointer; border-bottom: 3px solid transparent; }
    .column-tab.active { color: #0f4c3a; border-bottom-color: #0f4c3a; }
    .frozen-row { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 0.6rem 0.9rem; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 0.5rem; }
    .frozen-options { display: inline-flex; gap: 0.25rem; }
    .frozen-opt { padding: 0.35rem 0.75rem; border: 1px solid #cbd5e1; background: #ffffff; border-radius: 6px; font-size: 0.78rem; font-weight: 700; color: #64748b; cursor: pointer; }
    .frozen-opt.active { background: #0f4c3a; border-color: #0f4c3a; color: #ffffff; }
    .frozen-opt:disabled { opacity: 0.45; cursor: not-allowed; }
    .frozen-row-note { font-size: 0.72rem; color: #94a3b8; font-weight: 600; }
    .frozen-warning { padding: 0.7rem 0.9rem; margin-bottom: 0.75rem; border-radius: 8px; background: #fef3c7; border: 1px solid #fcd34d; color: #92400e; font-size: 0.82rem; font-weight: 600; }
```

- [ ] **Step 7: Perluas jembatan config**

Ganti blok `<script>` di akhir berkas menjadi:

```blade
<script>
    // Jembatan data Blade→JS (pola window.DOCUMENT_TABULATOR_CONFIG). Dibaca column-customization.js.
    window.COLUMN_CUSTOMIZATION_CONFIG = {
        availableColumns: @json($availableColumns),
        selected: @json(array_values($selectedColumns)),
        frozen: @json(['left' => array_values($frozenColumns['left'] ?? []), 'right' => array_values($frozenColumns['right'] ?? [])]),
        pinned: @json(array_values($pinnedColumns)),
    };
</script>
```

- [ ] **Step 8: Jalankan test, pastikan LULUS**

Run: `php artisan test --filter=ColumnCustomizationSharedTest`
Expected: PASS, seluruh test di kelas itu (lama + baru).

- [ ] **Step 9: Jalankan seluruh suite & commit**

Run: `php artisan test`

```bash
git add resources/views/partials/_columnCustomizationModal.blade.php
git add tests/Feature/ColumnCustomizationSharedTest.php
git commit -m "feat(ui): tab Kolom Beku pada modal kustomisasi kolom bersama

Markup tab bar + panel beku mengikuti versi pembayaran, CSS masuk blok
@push('styles') yang sudah ada (bukan style inline di body — jaring
flash-of-unstyled-modal). Jembatan config bertambah kunci frozen & pinned
dengan default defensif, jadi partial tetap merender sebelum controller
role disambungkan."
```

---

### Task 3: Simpan lewat URL + perbaikan bug drag

**Files:**
- Modify: `public/js/column-customization.js:104-156` (`saveColumnCustomization` + `appendActiveFilterInputs`)
- Modify: `public/js/column-customization.js:22-40` (`toggleColumn`)
- Test: `tests/Feature/ColumnCustomizationSharedTest.php`

**Interfaces:**
- Consumes: `window.COLUMN_CUSTOMIZATION_CONFIG` (Task 2), variabel modul `selectedColumnsOrder`, fungsi `initializeDragAndDrop()` (sudah ada).
- Produces: `saveColumnCustomization()` yang mengarahkan `location.href` ke URL berisi `columns[]` + parameter URL lama yang dipertahankan; `applyToolbarParams(url)` sebagai pengganti `appendActiveFilterInputs()`.

> **Catatan penting:** tugas ini **mengubah jalur simpan yang sekarang sudah jalan**
> di 4 role — bukan menambah. Test paritas filter toolbar yang sudah ada
> (`test_view_keuangan_memakai_modal_bersama` dkk) wajib tetap hijau.

- [ ] **Step 1: Tulis test yang gagal**

Tambahkan ke `tests/Feature/ColumnCustomizationSharedTest.php`:

```php
    /**
     * Jalur simpan pindah dari filterForm.submit() ke pembangunan URL.
     * Parameter mati enable_customization tidak boleh dikirim lagi.
     */
    public function test_js_bersama_simpan_lewat_url_bukan_submit_form(): void
    {
        $js = file_get_contents(public_path('js/column-customization.js'));

        // Jalur baru: bangun URL lalu arahkan browser.
        $this->assertStringContainsString('new URL(', $js);
        $this->assertStringContainsString('function applyToolbarParams', $js);

        // Jalur lama benar-benar hilang.
        $this->assertStringNotContainsString('filterForm.submit()', $js);
        $this->assertStringNotContainsString('function appendActiveFilterInputs', $js);

        // Parameter mati tidak dikirim lagi (nol pembaca di sisi server).
        $this->assertStringNotContainsString('enable_customization', $js);
    }

    /**
     * Bug 2026-07-28: toggleColumn memasang atribut draggable tapi tidak
     * memasang listener drag, sehingga kolom yang baru dicentang tak bisa
     * ditarik sampai modal ditutup-buka.
     */
    public function test_toggle_kolom_memasang_ulang_listener_drag(): void
    {
        $js = file_get_contents(public_path('js/column-customization.js'));

        $awal = strpos($js, 'function toggleColumn(');
        $this->assertNotFalse($awal, 'fungsi toggleColumn tidak ditemukan');

        $akhir = strpos($js, 'function selectAllColumns(', $awal);
        $badan = substr($js, $awal, $akhir - $awal);

        $this->assertStringContainsString('initializeDragAndDrop()', $badan);
    }
```

- [ ] **Step 2: Jalankan test, pastikan GAGAL**

Run: `php artisan test --filter="test_js_bersama_simpan_lewat_url_bukan_submit_form|test_toggle_kolom_memasang_ulang_listener_drag"`
Expected: FAIL — `filterForm.submit()` masih ada, `initializeDragAndDrop()` tidak ada di `toggleColumn`.

- [ ] **Step 3: Perbaiki bug drag di `toggleColumn`**

Ganti dua baris terakhir badan `toggleColumn()` (`updateColumnOrderBadges(); … updateDraggableState();`) sehingga bagian akhirnya menjadi:

```javascript
    updateColumnOrderBadges();
    updatePreviewTable();
    updateSelectedCount();
    updateDraggableState();
    // Kolom yang BARU dicentang perlu listener drag-nya dipasang sekarang juga.
    // Sebelumnya listener hanya dipasang saat modal dibuka, sehingga kolom baru
    // tampak bisa ditarik (atribut draggable menyala) tapi tak bereaksi sampai
    // modal ditutup lalu dibuka lagi.
    // Wajib dipanggil PALING AKHIR: initializeDragAndDrop() mengganti node
    // #columnSelectionList dengan klonanya, jadi referensi node lama basi.
    initializeDragAndDrop();
```

- [ ] **Step 4: Ganti `saveColumnCustomization` + `appendActiveFilterInputs`**

Ganti seluruh blok baris 104–156 dengan:

```javascript
function saveColumnCustomization() {
    if (selectedColumnsOrder.length === 0) {
        alert('Silakan pilih minimal satu kolom untuk ditampilkan.');
        return;
    }
    // Mulai dari URL berjalan supaya parameter yang tidak diwakili toolbar tetap
    // hidup (mis. mode=rekapan_table & per_page milik pembayaran, page, sort).
    const url = new URL(window.location.href);

    applyToolbarParams(url);

    url.searchParams.delete('columns[]');
    url.searchParams.delete('columns');
    selectedColumnsOrder.forEach(function (columnKey) {
        url.searchParams.append('columns[]', columnKey);
    });

    closeColumnCustomizationModal();
    window.location.href = url.toString();
}

// Nama yang punya jalur simpan sendiri — kontrol toolbar bernama sama tidak
// boleh menimpanya (lihat saveColumnCustomization & applyFrozenParams).
var RESERVED_PARAM_NAMES = ['columns[]', 'columns', 'frozen_config', 'frozen_left[]', 'frozen_right[]'];

// Timpa URL dengan nilai kontrol toolbar yang sedang aktif (generik lintas-role:
// tiap toolbar hanya memuat field-nya sendiri). Nilai kosong MENGHAPUS param —
// itulah semantik "bersihkan filter"; tanpa ini filter yang dikosongkan user
// akan hidup lagi dari URL sebelumnya.
function applyToolbarParams(url) {
    const toolbar = document.querySelector('.tabulator-toolbar');
    if (!toolbar) return;
    const controls = toolbar.querySelectorAll('input[name], select[name], textarea[name]');
    controls.forEach(function (el) {
        if (!el.name || RESERVED_PARAM_NAMES.indexOf(el.name) !== -1) return;
        if ((el.type === 'checkbox' || el.type === 'radio') && !el.checked) {
            url.searchParams.delete(el.name);
            return;
        }
        if (el.value === '' || el.value == null) {
            url.searchParams.delete(el.name);
            return;
        }
        url.searchParams.set(el.name, el.value);
    });
}
```

- [ ] **Step 5: Jalankan test, pastikan LULUS**

Run: `php artisan test --filter=ColumnCustomizationSharedTest`
Expected: PASS — termasuk test lama yang menjaga paritas.

- [ ] **Step 6: Jalankan seluruh suite & commit**

Run: `php artisan test`

```bash
git add public/js/column-customization.js
git add tests/Feature/ColumnCustomizationSharedTest.php
git commit -m "refactor(ui): simpan kustomisasi kolom lewat URL + perbaiki bug drag

Simpan tidak lagi submit #filterForm melainkan membangun URL dari
location.href, sehingga parameter yang tidak diwakili toolbar ikut selamat
(mode=rekapan_table & per_page pembayaran, page, sort). Nilai toolbar kosong
kini menghapus param = bersihkan filter. #filterForm sengaja tidak disentuh
karena dipakai partial global document-workbench-ui.

Parameter enable_customization berhenti dikirim: grep menunjukkan nol pembaca
di app/, routes/, config/, tests/.

Bug drag diperbaiki: toggleColumn memasang atribut draggable tapi tidak
memasang listener, jadi kolom yang baru dicentang tak bisa ditarik sampai
modal ditutup-buka."
```

---

### Task 4: Logika tab Kolom Beku di JS bersama

**Files:**
- Modify: `public/js/column-customization.js`
- Test: `tests/Feature/ColumnCustomizationSharedTest.php`

**Interfaces:**
- Consumes: `window.COLUMN_CUSTOMIZATION_CONFIG.frozen` & `.pinned` (Task 2), `applyToolbarParams(url)` & `RESERVED_PARAM_NAMES` (Task 3), elemen `#tabPanelKolom`/`#tabPanelBeku`/`#frozenList`/`#frozenWarning` (Task 2).
- Produces: `switchColumnTab(tab)`, `getFrozenState(key)`, `setFrozenState(key, state)`, `renderFrozenTab()`, `renderFrozenWarning()`; `saveColumnCustomization()` kini juga menulis `frozen_config`/`frozen_left[]`/`frozen_right[]`.

- [ ] **Step 1: Tulis test yang gagal**

Tambahkan ke `tests/Feature/ColumnCustomizationSharedTest.php`:

```php
    public function test_js_bersama_punya_logika_tab_beku(): void
    {
        $js = file_get_contents(public_path('js/column-customization.js'));

        foreach ([
            'function switchColumnTab',
            'function getFrozenState',
            'function setFrozenState',
            'function renderFrozenTab',
            'function renderFrozenWarning',
        ] as $fungsi) {
            $this->assertStringContainsString($fungsi, $js);
        }

        // Penanda wajib pada simpan (lihat spec §5.5).
        $this->assertStringContainsString("'frozen_config'", $js);
        $this->assertStringContainsString("'frozen_left[]'", $js);
        $this->assertStringContainsString("'frozen_right[]'", $js);

        // Kolom pinned dirender non-aktif, bukan bisa dilepas.
        $this->assertStringContainsString('disabled', $js);
    }

    /**
     * Invarian: renderFrozenTab() TIDAK boleh menugaskan ulang state beku.
     * Dulu fungsi ini memangkas frozenLeftOrder/frozenRightOrder padahal hanya
     * jalan saat tab Beku dibuka — akibatnya hasil simpan bergantung pada apakah
     * tab itu pernah dibuka. Penyaringan hanya boleh di saveColumnCustomization().
     */
    public function test_render_tab_beku_tidak_menugaskan_ulang_state(): void
    {
        $js = file_get_contents(public_path('js/column-customization.js'));

        $awal = strpos($js, 'function renderFrozenTab(');
        $this->assertNotFalse($awal, 'fungsi renderFrozenTab tidak ditemukan');

        $akhir = strpos($js, 'function renderFrozenWarning(', $awal);
        $badan = substr($js, $awal, $akhir - $awal);

        $this->assertStringNotContainsString('frozenLeftOrder =', $badan);
        $this->assertStringNotContainsString('frozenRightOrder =', $badan);
    }
```

- [ ] **Step 2: Jalankan test, pastikan GAGAL**

Run: `php artisan test --filter="test_js_bersama_punya_logika_tab_beku|test_render_tab_beku_tidak_menugaskan_ulang_state"`
Expected: FAIL — `function switchColumnTab` tidak ditemukan.

- [ ] **Step 3: Tambah state beku di bagian atas berkas**

Tepat setelah baris `let selectedColumnsOrder = …` (baris 9), sisipkan:

```javascript
let frozenLeftOrder = Array.isArray(__CCCFG.frozen && __CCCFG.frozen.left) ? __CCCFG.frozen.left.slice() : [];
let frozenRightOrder = Array.isArray(__CCCFG.frozen && __CCCFG.frozen.right) ? __CCCFG.frozen.right.slice() : [];
// Kolom yang selalu beku kiri (document-tabulator.js membekukannya tanpa syarat).
const PINNED_LEFT_COLUMNS = Array.isArray(__CCCFG.pinned) ? __CCCFG.pinned.slice() : ['nomor_agenda'];
// Perkiraan lebar untuk peringatan "kolom beku memakan layar" (angka dari pembayaran).
const FROZEN_WIDTH_MAP = { nomor_agenda: 210 };
const FROZEN_WIDTH_DEFAULT = 132;
const FROZEN_NO_COLUMN_WIDTH = 88;
```

- [ ] **Step 4: Tambah fungsi tab & beku di akhir berkas (sebelum blok listener `document.addEventListener`)**

```javascript
function switchColumnTab(tab) {
    document.querySelectorAll('.column-tab').forEach(function (btn) {
        btn.classList.toggle('active', btn.dataset.tab === tab);
    });
    const panelKolom = document.getElementById('tabPanelKolom');
    const panelBeku = document.getElementById('tabPanelBeku');
    if (panelKolom) panelKolom.style.display = tab === 'kolom' ? '' : 'none';
    if (panelBeku) panelBeku.style.display = tab === 'beku' ? '' : 'none';
    if (tab === 'beku') renderFrozenTab();
}

function getFrozenState(key) {
    if (frozenLeftOrder.indexOf(key) !== -1) return 'left';
    if (frozenRightOrder.indexOf(key) !== -1) return 'right';
    return 'none';
}

function setFrozenState(key, state) {
    if (PINNED_LEFT_COLUMNS.indexOf(key) !== -1) return; // terkunci, abaikan
    frozenLeftOrder = frozenLeftOrder.filter(function (k) { return k !== key; });
    frozenRightOrder = frozenRightOrder.filter(function (k) { return k !== key; });
    if (state === 'left') frozenLeftOrder.push(key);
    if (state === 'right') frozenRightOrder.push(key);
    renderFrozenTab();
}

// Render bersifat NON-DESTRUKTIF: frozenLeftOrder/frozenRightOrder tidak pernah
// ditugaskan ulang di sini. Dulu fungsi ini memangkas kedua array padahal hanya
// jalan saat user membuka tab Beku — akibatnya hasil simpan ikut bergantung pada
// apakah tab itu pernah dibuka. Satu-satunya titik penegakan adalah filter di
// saveColumnCustomization().
function renderFrozenTab() {
    const list = document.getElementById('frozenList');
    if (!list) return;

    list.innerHTML = selectedColumnsOrder.map(function (key) {
        const label = availableColumnsData[key] || key;
        const state = getFrozenState(key);
        const terkunci = PINNED_LEFT_COLUMNS.indexOf(key) !== -1;
        const opt = function (value, text) {
            return '<button type="button" class="frozen-opt' + (state === value ? ' active' : '') + '"' +
                (terkunci ? ' disabled' : ' onclick="setFrozenState(\'' + key + '\', \'' + value + '\')"') +
                '>' + text + '</button>';
        };
        const catatan = terkunci
            ? '<span class="frozen-row-note">identitas baris selalu terlihat</span>'
            : '';
        return '<div class="frozen-row"><span>' + label + ' ' + catatan + '</span>' +
            '<span class="frozen-options">' + opt('left', 'Kiri') + opt('none', 'Bebas') + opt('right', 'Kanan') +
            '</span></div>';
    }).join('');

    renderFrozenWarning();
}

function renderFrozenWarning() {
    const box = document.getElementById('frozenWarning');
    if (!box) return;

    // Hanya kolom yang benar-benar ditampilkan yang dihitung. Penyaringan memakai
    // variabel lokal, bukan menugaskan ulang state (lihat catatan non-destruktif).
    const visibleFrozen = frozenLeftOrder
        .concat(frozenRightOrder)
        .filter(function (key) { return selectedColumnsOrder.indexOf(key) !== -1; });

    const total = visibleFrozen.reduce(function (sum, key) {
        return sum + (FROZEN_WIDTH_MAP[key] || FROZEN_WIDTH_DEFAULT);
    }, FROZEN_NO_COLUMN_WIDTH);

    if (total > window.innerWidth * 0.5) {
        box.style.display = '';
        box.textContent = 'Kolom beku memakan sekitar ' + Math.round(total) +
            'px dari lebar layar Anda. Area yang bisa digulir jadi sempit — pertimbangkan mengurangi kolom beku.';
    } else {
        box.style.display = 'none';
    }
}
```

- [ ] **Step 5: Kirim parameter beku saat simpan**

Di `saveColumnCustomization()`, tepat sebelum `closeColumnCustomizationModal();`, sisipkan:

```javascript
    url.searchParams.delete('frozen_left[]');
    url.searchParams.delete('frozen_right[]');
    // Penanda WAJIB: tanpa ini "user melepas semua kolom beku" tidak bisa
    // dibedakan dari "request tanpa konfigurasi beku" di sisi server.
    url.searchParams.set('frozen_config', '1');
    // Kolom yang sudah tidak ditampilkan tidak boleh ikut dikirim sebagai beku.
    frozenLeftOrder
        .filter(function (col) { return selectedColumnsOrder.indexOf(col) !== -1; })
        .forEach(function (col) { url.searchParams.append('frozen_left[]', col); });
    frozenRightOrder
        .filter(function (col) { return selectedColumnsOrder.indexOf(col) !== -1; })
        .forEach(function (col) { url.searchParams.append('frozen_right[]', col); });
```

- [ ] **Step 6: Reset tab ke "Kolom Tabel" saat modal dibuka**

Di `openColumnCustomizationModal()`, tepat sebelum `initializeModalState();`, sisipkan:

```javascript
    switchColumnTab('kolom');
```

- [ ] **Step 7: Jalankan test, pastikan LULUS**

Run: `php artisan test --filter=ColumnCustomizationSharedTest`
Expected: PASS.

- [ ] **Step 8: Jalankan seluruh suite & commit**

Run: `php artisan test`

```bash
git add public/js/column-customization.js
git add tests/Feature/ColumnCustomizationSharedTest.php
git commit -m "feat(ui): logika tab Kolom Beku di JS kustomisasi kolom bersama

Memindahkan state & render kolom beku dari modal inline pembayaran ke file
bersama. Invarian non-destruktif renderFrozenTab dibawa utuh dan kini dijaga
test. Kolom pinned (nomor_agenda) dirender non-aktif — memperbaiki kejanggalan
lama pembayaran yang membiarkan user menyetel 'Bebas' padahal tak berefek."
```

---

### Task 5: Sambungkan 4 controller + 4 view

**Files:**
- Modify: `app/Http/Controllers/DokumenController.php` (sekitar 258–292, dan array data view sekitar 351)
- Modify: `app/Http/Controllers/DashboardAkutansiController.php` (sekitar 413–471, array data sekitar 617)
- Modify: `app/Http/Controllers/DashboardPerpajakanController.php` (sekitar 450–507, array data sekitar 647)
- Modify: `app/Http/Controllers/TeamVerifikasiController.php` (sekitar 772–829, array data sekitar 883)
- Modify: `resources/views/operator/dokumens/daftarDokumenTabulator.blade.php:31`
- Modify: `resources/views/akutansi/dokumens/daftarAkutansiTabulator.blade.php:25`
- Modify: `resources/views/perpajakan/dokumens/daftarPerpajakanTabulator.blade.php:25`
- Modify: `resources/views/team_verifikasi/dokumens/daftarDokumenTabulator.blade.php:45`
- Test: `tests/Feature/ColumnCustomizationSharedTest.php`

**Interfaces:**
- Consumes: `ColumnCustomization::resolveFrozen()` (Task 1); variabel view `$frozenColumns`/`$pinnedColumns` yang dibaca partial (Task 2).
- Produces: variabel view `$frozenColumns`, `$pinnedColumns`, `$renderColumns`; kunci `frozen` pada `window.DOCUMENT_TABULATOR_CONFIG`.

- [ ] **Step 1: Tulis test yang gagal**

Tambahkan ke `tests/Feature/ColumnCustomizationSharedTest.php`:

```php
    /**
     * Dua urutan kolom TIDAK boleh tertukar: DOCUMENT_TABULATOR_CONFIG.columns
     * memakai urutan render (beku kiri -> bebas -> beku kanan), sedangkan modal
     * menampilkan urutan pilihan asli user.
     */
    public function test_urutan_render_berbeda_dari_urutan_modal_saat_ada_beku_kanan(): void
    {
        $user = User::factory()->create(['role' => 'akutansi']);

        $res = $this->actingAs($user)->get(route('documents.akutansi.index', [
            'columns'       => ['nomor_agenda', 'nomor_spp', 'nilai_rupiah'],
            'frozen_config' => '1',
            'frozen_left'   => ['nomor_agenda'],
            'frozen_right'  => ['nomor_spp'],
        ]));
        $res->assertOk();

        // Modal: urutan pilihan asli.
        $res->assertSee('"selected":["nomor_agenda","nomor_spp","nilai_rupiah"]', false);
        // Tabel: nomor_spp pindah ke akhir karena dibekukan di kanan.
        $res->assertSee('"frozen":{"left":["nomor_agenda"],"right":["nomor_spp"]}', false);
    }

    public function test_kolom_beku_tersimpan_dan_bisa_dikosongkan(): void
    {
        $user = User::factory()->create(['role' => 'akutansi']);

        $this->actingAs($user)->get(route('documents.akutansi.index', [
            'columns'       => ['nomor_agenda', 'nomor_spp'],
            'frozen_config' => '1',
            'frozen_left'   => ['nomor_agenda'],
            'frozen_right'  => ['nomor_spp'],
        ]))->assertOk();

        $user->refresh();
        $this->assertSame(['nomor_spp'], $user->table_columns_preferences['akutansi_frozen']['right']);

        // Lepas semua: hanya penanda yang dikirim, tanpa frozen_left/right.
        $this->actingAs($user)->get(route('documents.akutansi.index', [
            'columns'       => ['nomor_agenda', 'nomor_spp'],
            'frozen_config' => '1',
        ]))->assertOk();

        $user->refresh();
        $this->assertSame([], $user->table_columns_preferences['akutansi_frozen']['right']);
        // Kolom pinned tetap beku kiri.
        $this->assertSame(['nomor_agenda'], $user->table_columns_preferences['akutansi_frozen']['left']);
    }
```

- [ ] **Step 2: Jalankan test, pastikan GAGAL**

Run: `php artisan test --filter="test_urutan_render_berbeda_dari_urutan_modal_saat_ada_beku_kanan|test_kolom_beku_tersimpan_dan_bisa_dikosongkan"`
Expected: FAIL — kunci `"frozen"` tidak ada di config.

- [ ] **Step 3: Sambungkan `DashboardAkutansiController`**

Tepat **setelah** blok resolusi `$selectedColumns` selesai (setelah baris `session(['akutansi_dokumens_table_columns' => $selectedColumns]);` yang menutup cabang `else`, sekitar baris 471), sisipkan:

```php
        // === Konfigurasi kolom beku (frozen) ===
        // Dinormalkan ulang tiap request: kolom yang dibekukan bisa saja sudah
        // disembunyikan user lewat tab pertama modal.
        $pinnedColumns = ['nomor_agenda'];
        $frozenResolved = \App\Support\ColumnCustomization::resolveFrozen($request, Auth::user(), [
            'available'  => $availableColumns,
            'selected'   => $selectedColumns,
            'default'    => ['left' => $pinnedColumns, 'right' => []],
            'pinnedLeft' => $pinnedColumns,
            'prefKey'    => 'akutansi_frozen',
            'sessionKey' => 'akutansi_dokumens_frozen_columns',
        ]);
        $frozenColumns = ['left' => $frozenResolved['left'], 'right' => $frozenResolved['right']];
        // Urutan render tabel: beku kiri -> bebas -> beku kanan. $selectedColumns
        // sengaja TIDAK diubah agar tab pertama modal tetap menampilkan urutan
        // pilihan asli user.
        $renderColumns = $frozenResolved['render'];
```

Lalu pada array data view (sekitar baris 617), tepat setelah `'selectedColumns' => $selectedColumns,` tambahkan:

```php
            'frozenColumns' => $frozenColumns,
            'pinnedColumns' => $pinnedColumns,
            'renderColumns' => $renderColumns,
```

- [ ] **Step 4: Ulangi untuk 3 controller lain**

Sisipkan blok yang sama persis, hanya berbeda pada `prefKey`/`sessionKey`, dan
letakkan **setelah** blok resolusi `$selectedColumns` masing-masing:

| Controller | `prefKey` | `sessionKey` | Sisip setelah | Array data |
|---|---|---|---|---|
| `DashboardPerpajakanController` | `'perpajakan_frozen'` | `'perpajakan_dokumens_frozen_columns'` | ~baris 507 | ~baris 647 |
| `TeamVerifikasiController` | `'team_verifikasi_frozen'` | `'team_verifikasi_dokumens_frozen_columns'` | ~baris 829 | ~baris 883 |
| `DokumenController` (operator) | **`null`** (sesi saja) | `'dokumens_frozen_columns'` | ~baris 292 | ~baris 351 |

Untuk `DokumenController`, array data memakai kunci bertanda kutip ganda mengikuti gaya berkas itu:

```php
            "frozenColumns" => $frozenColumns,
            "pinnedColumns" => $pinnedColumns,
            "renderColumns" => $renderColumns,
```

- [ ] **Step 5: Ubah 4 view — pakai urutan render untuk tabel**

Di masing-masing view, ganti baris `'columns' => …` menjadi (gunakan `$renderColumns`,
bukan `$selectedColumns`) dan tambahkan kunci `frozen`:

```blade
        'columns'          => collect($renderColumns ?? $selectedColumns)->map(fn ($k) => ['key' => $k, 'label' => $availableColumns[$k] ?? $k])->values(),
        'frozen'           => $frozenColumns ?? ['left' => [], 'right' => []],
```

`'selected' => array_values($selectedColumns)` **dibiarkan apa adanya** — itu urutan
pilihan modal, bukan urutan render.

- [ ] **Step 6: Jalankan test, pastikan LULUS**

Run: `php artisan test --filter=ColumnCustomizationSharedTest`
Expected: PASS.

- [ ] **Step 7: Jalankan seluruh suite**

Run: `php artisan test`
Expected: hijau. Test Tabulator per-role yang sudah ada (`AkutansiTabulatorSwitchTest`, `OperatorTabulatorViewTest`, `PerpajakanTabulatorSwitchTest`, `VerifikasiTabulatorSwitchTest`) berfungsi sebagai jaring paritas — kalau ada yang merah, itu regresi, bukan test yang perlu diubah.

- [ ] **Step 8: Commit (per-file)**

```bash
git add app/Http/Controllers/DashboardAkutansiController.php
git add app/Http/Controllers/DashboardPerpajakanController.php
git add app/Http/Controllers/TeamVerifikasiController.php
git add app/Http/Controllers/DokumenController.php
git add resources/views/akutansi/dokumens/daftarAkutansiTabulator.blade.php
git add resources/views/perpajakan/dokumens/daftarPerpajakanTabulator.blade.php
git add resources/views/team_verifikasi/dokumens/daftarDokumenTabulator.blade.php
git add resources/views/operator/dokumens/daftarDokumenTabulator.blade.php
git add tests/Feature/ColumnCustomizationSharedTest.php
git commit -m "feat(kolom): sambungkan kolom beku ke 4 role Tabulator

Operator/akutansi/perpajakan/verifikasi kini memakai ColumnCustomization untuk
resolusi kolom beku dan mengirim frozen ke DOCUMENT_TABULATOR_CONFIG. Tabel
memakai urutan render (beku kiri -> bebas -> beku kanan) sementara modal tetap
menampilkan urutan pilihan asli user — dua urutan yang tidak boleh tertukar.

Default beku left:[nomor_agenda] membuat tampilan awal identik dengan sebelum
perubahan. Preferensi beku disimpan di DB untuk 3 role keuangan dan di sesi
untuk operator, mengikuti pola preferensi kolomnya masing-masing."
```

- [ ] **Step 9: GERBANG — minta keputusan user sebelum deploy**

Deploy 1 menyentuh 4 halaman produksi. Laporkan ke user: apa yang berubah, hasil
suite, dan **minta izin** sebelum `git push` + `git pull` di server. Jangan deploy
tanpa persetujuan eksplisit (CLAUDE.md §4 & §6).

---

## Deploy 2 — pembayaran menyusul

### Task 6: Migrasi pembayaran ke modal bersama

**Files:**
- Modify: `resources/views/pembayaranNEW/dashboardPembayaran.blade.php` (hapus CSS 2336–2744, markup 2747–2901, JS modal 2903–3206; ganti tombol baris 2198)
- Modify: `app/Http/Controllers/DashboardPembayaranController.php:138-194`
- Test: `tests/Feature/ColumnCustomizationSharedTest.php`

**Interfaces:**
- Consumes: partial `partials._columnCustomizationModal` + `public/js/column-customization.js` (Task 2–4), `ColumnCustomization::resolveFrozen()` (Task 1).
- Produces: halaman pembayaran yang memakai modal bersama; variabel view `$frozenColumns`/`$pinnedColumns` menggantikan `$frozenLeft`/`$frozenRight`.

> **Tiga jebakan yang sudah diverifikasi — baca sebelum memotong apa pun:**
> 1. **4 nama fungsi bentrok** antara modal inline dan JS bersama: `selectAllColumns`, `removeAllColumns`, `updateSelectedCount`, `saveColumnCustomization`. Kalau blok inline tersisa sedikit saja, keduanya mendefinisikan global yang sama dan yang terakhir menang **tanpa error**.
> 2. **Tombol pembuka** baris 2198 memakai `onclick="openColumnModal()"`; nama bersamanya `openColumnCustomizationModal()`. Terlewat = tombol mati diam-diam.
> 3. **Empat fungsi non-modal WAJIB selamat** di baris 3210–3236: `setViewMode`, `refreshPembayaranTable`, `changePerPage`, `toggleVendorGroup`. Batas potong terlalu lebar mematikan tombol mode tampilan, refresh, per-page, dan grup vendor sekaligus.

- [ ] **Step 1: Tulis test yang gagal**

Tambahkan ke `tests/Feature/ColumnCustomizationSharedTest.php`:

```php
    public function test_pembayaran_memakai_modal_bersama(): void
    {
        $user = User::factory()->create(['role' => 'pembayaran']);
        $res = $this->actingAs($user)->get(route('documents.pembayaran.index'));
        $res->assertOk();

        $res->assertSee('js/column-customization.js', false);
        $res->assertSee('window.COLUMN_CUSTOMIZATION_CONFIG', false);
        $res->assertSee('openColumnCustomizationModal()', false);

        // Jejak modal inline lama benar-benar lenyap.
        $res->assertDontSee('openColumnModal()', false);
        $res->assertDontSee('toggleColumnSelection(', false);
        $res->assertDontSee('applyTemplateAgenda', false);
        $res->assertDontSee('pembayaran_columns', false);
        $res->assertDontSee('enable_customization', false);

        // Fungsi non-modal di blok yang sama WAJIB selamat.
        $res->assertSee('function setViewMode', false);
        $res->assertSee('function refreshPembayaranTable', false);
        $res->assertSee('function changePerPage', false);
        $res->assertSee('function toggleVendorGroup', false);
    }

    /** Nol definisi ganda: tiap nama fungsi bentrok hanya boleh muncul sekali. */
    public function test_pembayaran_tidak_punya_definisi_fungsi_ganda(): void
    {
        $user = User::factory()->create(['role' => 'pembayaran']);
        $html = $this->actingAs($user)->get(route('documents.pembayaran.index'))->getContent();

        foreach (['selectAllColumns', 'removeAllColumns', 'updateSelectedCount', 'saveColumnCustomization'] as $nama) {
            $this->assertSame(
                0,
                substr_count($html, 'function ' . $nama . '('),
                "Definisi inline {$nama}() masih ada — akan menimpa versi bersama tanpa error."
            );
        }
    }
```

- [ ] **Step 2: Jalankan test, pastikan GAGAL**

Run: `php artisan test --filter="test_pembayaran_memakai_modal_bersama|test_pembayaran_tidak_punya_definisi_fungsi_ganda"`
Expected: FAIL — `js/column-customization.js` belum dimuat.

- [ ] **Step 3: Ganti blok beku controller dengan kelas bersama**

Di `DashboardPembayaranController.php`, ganti baris 138–194 (dari komentar
`// === Konfigurasi kolom beku (frozen) ===` sampai baris `$renderColumns = …`) dengan:

```php
        // === Konfigurasi kolom beku (frozen) ===
        // Logika bersama 5 role — lihat App\Support\ColumnCustomization.
        $pinnedColumns = ['nomor_agenda'];
        $frozenResolved = ColumnCustomization::resolveFrozen($request, Auth::user(), [
            'available'  => $this->getPembayaranDashboardAvailableColumns(),
            'selected'   => $selectedColumns,
            'default'    => ['left' => $pinnedColumns, 'right' => []],
            'pinnedLeft' => $pinnedColumns,
            'prefKey'    => 'pembayaran_dashboard_frozen',
            'sessionKey' => 'pembayaran_dashboard_frozen_columns',
        ]);
        $frozenColumns = ['left' => $frozenResolved['left'], 'right' => $frozenResolved['right']];
        $frozenLeft = $frozenResolved['left'];
        $frozenRight = $frozenResolved['right'];
        $renderColumns = $frozenResolved['render'];
```

Tambahkan `use App\Support\ColumnCustomization;` di daftar `use` (dekat baris 14 yang
sudah memuat `use App\Support\FrozenColumnLayout;`). Bila `FrozenColumnLayout` tak lagi
dipakai langsung di controller ini setelah perubahan, hapus `use`-nya.

Pada array data view, tambahkan setelah `'selectedColumns' => $selectedColumns,`:

```php
            'frozenColumns' => $frozenColumns,
            'pinnedColumns' => $pinnedColumns,
```

- [ ] **Step 4: Hapus CSS modal inline (baris 2336–2744)**

Hapus dari `.customization-modal {` sampai tepat sebelum `</style>` di baris 2745.
**Jangan** hapus `</style>` itu sendiri — blok `<style>` masih memuat CSS lain.
Verifikasi: `.filter-section`, `.data-table`, `.table-header` harus tetap ada.

- [ ] **Step 5: Ganti markup modal inline dengan include partial**

Hapus baris 2747–2901 (seluruh `<div class="customization-modal" id="columnCustomizationModal">`
sampai penutupnya), ganti dengan:

```blade
  @include('partials._columnCustomizationModal')
```

- [ ] **Step 6: Potong blok JS modal (2903–3206) dengan hati-hati**

Hapus `<script>` pembuka di baris 2903 sampai `</script>` di baris 3206 — blok itu berisi
`FROZEN_WIDTH_*`, `switchColumnTab`, `getFrozenState`, `setFrozenState`, `renderFrozenTab`,
`renderFrozenWarning`, `openColumnModal`, `closeColumnModal`, `toggleColumnSelection`,
`updateOrderNumbers`, `updateSelectedCount`, `updatePreview`, `selectAllColumns`,
`removeAllColumns`, `applyTemplateAgenda`, `saveColumnCustomization`, dan dua listener
(Escape + klik backdrop).

**JANGAN sentuh** `<script>` berikutnya di baris 3208 — di situlah `setViewMode`,
`refreshPembayaranTable`, `changePerPage`, `toggleVendorGroup` berada.

- [ ] **Step 7: Ganti tombol pembuka & muat JS bersama**

Baris 2198 — ganti `onclick="openColumnModal()"` menjadi
`onclick="openColumnCustomizationModal()"`.

Di dalam `@push('scripts')` view ini, tambahkan setelah pemuatan `document-tabulator.js`:

```blade
    <script src="{{ \App\Support\Asset::versioned('js/column-customization.js') }}"></script>
```

- [ ] **Step 8: Grep-gate — buktikan nol residu sebelum lanjut**

```bash
grep -n "applyTemplateAgenda\|pembayaran_columns\|openColumnModal\|toggleColumnSelection\|enable_customization" resources/views/pembayaranNEW/dashboardPembayaran.blade.php
grep -n "function setViewMode\|function refreshPembayaranTable\|function changePerPage\|function toggleVendorGroup" resources/views/pembayaranNEW/dashboardPembayaran.blade.php
```

Expected: perintah pertama **nol baris**; perintah kedua **empat baris**.

- [ ] **Step 9: Jalankan test, pastikan LULUS**

Run: `php artisan test --filter=ColumnCustomizationSharedTest`
Expected: PASS.

- [ ] **Step 10: Jalankan seluruh suite**

Run: `php artisan test`
Expected: hijau — termasuk `PembayaranTabulatorSwitchTest` sebagai jaring paritas.

- [ ] **Step 11: Commit (per-file)**

```bash
git add app/Http/Controllers/DashboardPembayaranController.php
git add resources/views/pembayaranNEW/dashboardPembayaran.blade.php
git add tests/Feature/ColumnCustomizationSharedTest.php
git commit -m "refactor(pembayaran): pindah ke modal kustomisasi kolom bersama

Menghapus modal inline ~868 baris dari god-file (CSS 409, markup 155, JS 304)
dan menggantinya dengan partial + JS bersama. Kelima role keuangan kini memakai
satu modal.

Ikut dihapus: tombol Template Agenda + applyTemplateAgenda() (keputusan user)
dan localStorage('pembayaran_columns') yang hanya ditulis tanpa pernah dibaca.

Pembayaran MENDAPAT drag-reorder kolom yang selama ini ikonnya hanya hiasan
(nol dragstart/draggable di god-file). Empat fungsi non-modal di blok JS
tetangga - setViewMode, refreshPembayaranTable, changePerPage, toggleVendorGroup
- dijaga test agar tidak ikut terpotong."
```

- [ ] **Step 12: GERBANG — minta keputusan user sebelum deploy**

Deploy 2 menyentuh god-file pembayaran. Tunjukkan hasil grep-gate Step 8 dan hasil
suite, lalu **minta izin** sebelum push & pull di server.

---

### Task 7: Perbarui dokumentasi project

**Files:**
- Modify: `CLAUDE.md` (§2 daftar partial bersama, §7 pekerjaan berjalan)

- [ ] **Step 1: Perbarui §2**

Pada entri `_columnCustomizationModal.blade.php` di daftar partial shared, ubah
keterangannya menjadi:

```
  _columnCustomizationModal.blade.php  modal Kustomisasi Kolom bersama (markup + CSS via
                                     @push('styles'), 2 tab: Kolom Tabel + Kolom Beku) —
                                     dipakai 5 view role keuangan termasuk pembayaran;
                                     logikanya di public/js/column-customization.js,
                                     resolusi beku di App\Support\ColumnCustomization
```

- [ ] **Step 2: Tambah entri §7**

Tambahkan paragraf baru di akhir §7:

```
**Modal Kustomisasi Kolom SATU untuk 5 role (2 tab) — SELESAI 2026-07-28.** Modal
pembayaran (Kolom Tabel + Kolom Beku) jadi acuan bersama; 4 role lain naik dari 1 tab
ke 2 tab, pembayaran turun dari salinan inline ~868 baris ke partial bersama. Resolusi
kolom beku dipusatkan di **`App\Support\ColumnCustomization`** (kelas biasa, bukan trait
— agar bisa di-unit-test tanpa kelas inang); `FrozenColumnLayout` & `document-tabulator.js`
TIDAK diubah karena keduanya sudah generik. Simpan kini **membangun URL**, bukan
`filterForm.submit()` — supaya param yang tidak diwakili toolbar ikut selamat
(`mode=rekapan_table`/`per_page` pembayaran). `nomor_agenda` terkunci beku kiri
(kontrolnya non-aktif di modal). DIHAPUS: Template Agenda, `localStorage('pembayaran_columns')`,
parameter mati `enable_customization`. `#filterForm` TIDAK dihapus — dipakai partial global
`document-workbench-ui`. Dua urutan kolom wajib dibedakan: `DOCUMENT_TABULATOR_CONFIG.columns`
= urutan render (beku kiri→bebas→beku kanan), `COLUMN_CUSTOMIZATION_CONFIG.selected` =
urutan pilihan user.
- Spec/plan: `docs/superpowers/specs/2026-07-28-modal-kustomisasi-kolom-5-role-design.md`,
  `docs/superpowers/plans/2026-07-28-modal-kustomisasi-kolom-5-role.md`
```

- [ ] **Step 3: Commit**

```bash
git add CLAUDE.md
git commit -m "docs(CLAUDE): catat penyatuan modal kustomisasi kolom 5 role (2 tab)"
```

---

## Self-Review

**Cakupan spec:**

| Spec | Tugas |
|---|---|
| §3.1 kelas `ColumnCustomization` | Task 1 (dipersempit ke beku — lihat catatan cakupan) |
| §3.2 default beku `left:['nomor_agenda']` | Task 5 Step 3 |
| §4 kontrak data `frozen` | Task 2 Step 7 |
| §4.1 dua urutan kolom | Task 5 Step 5 + test Step 1 |
| §4.2 Nomor Agenda terkunci | Task 1 (`pinnedLeft`), Task 4 (`disabled`) |
| §5.2 simpan bangun URL | Task 3 Step 4 |
| §5.3 `#filterForm` tak disentuh | Tidak ada tugas yang menyentuhnya (disengaja) |
| §5.5 `frozen_config` | Task 1, Task 4 Step 5 |
| §6.1 invarian non-destruktif | Task 4 Step 4 + test |
| §6.2 validasi ulang server | Task 1 (`normalize` tiap request) |
| §6.3 bug drag | Task 3 Step 3 |
| §7 penanganan error | Task 3 (alert 0 kolom), Task 4 (peringatan lebar) |
| §8 penghapusan | Task 3 (`enable_customization`), Task 6 (sisanya) |
| §9 rollout 2 deploy | Gerbang di Task 5 Step 9 & Task 6 Step 12 |
| §10 testing | Test di tiap tugas |

**Placeholder:** nol. Setiap langkah kode memuat kode sungguhan.

**Konsistensi tipe:** `resolveFrozen()` mengembalikan `['left','right','render']` — dipakai konsisten di Task 5 & 6. `$frozenColumns` selalu `['left'=>[], 'right'=>[]]`; `$pinnedColumns` selalu `array<int,string>`; kunci config JS `frozen`/`pinned` cocok antara Task 2 (Blade) dan Task 4 (JS).

**Celah yang diketahui:** `prefKey` pembayaran tetap `pembayaran_dashboard_frozen` sehingga preferensi beku user yang sudah ada **tidak hilang** saat migrasi. Tiga role lain memakai kunci baru (`akutansi_frozen` dst) yang memang belum pernah ada — jadi mereka mulai dari default, sesuai §3.2.

# Kolom Beku Kustom — Tabel Pembayaran — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menambah tab kedua pada modal "Kustomisasi Kolom Tabel" di halaman pembayaran, sehingga user bisa memilih kolom mana yang dibekukan di kiri atau kanan tabel.

**Architecture:** Logika murni (validasi + urutan render) diisolasi ke `App\Support\FrozenColumnLayout` agar bisa diuji tanpa database. Controller hanya membaca/menyimpan preferensi mengikuti pola `selectedColumns` yang sudah ada. Kolom beku diterapkan lewat CSS yang menyasar kelas `col-<key>` yang sudah melekat di setiap sel — tidak perlu kelas baru dan tidak perlu mengubah pembuat baris di JS. Partial sticky bersama diberi jalur opt-in agar 4 halaman role lain tidak tersentuh.

**Tech Stack:** Laravel 12, PHP 8.4, Blade, JavaScript vanilla, PHPUnit (SQLite in-memory).

## Global Constraints

- Spec acuan: `docs/superpowers/specs/2026-07-20-kolom-beku-kustom-pembayaran-design.md`
- Cakupan **hanya** `/documents/pembayaran/daftar`. Halaman operator, akuntansi, perpajakan, dan team_verifikasi **tidak boleh berubah perilakunya**.
- Bawaan bila user belum pernah mengatur: `left = ['nomor_agenda']`, `right = []` — tampilan awal wajib identik dengan sekarang.
- Kolom `No` (nomor urut baris, `.col-no`) selalu beku paling kiri dan tidak bisa diubah user.
- Kolom yang tidak ditampilkan tidak boleh beku — divalidasi di server **dan** klien.
- Komentar domain & pesan commit dalam Bahasa Indonesia; **identifier dalam English — termasuk variabel lokal** (CLAUDE.md). Acuan gaya: `app/Support/KeterlambatanClassifier.php` (komentar Indonesia, lokal English).
- Test untuk kelas yang bebas framework memakai `PHPUnit\Framework\TestCase`, bukan `Tests\TestCase` (acuan: `tests/Unit/SafeUrlTest.php`).
- `git add` per-file. Dilarang `git add .` / `git add -A`.
- Peringatan lebar bersifat non-blocking (tidak menghalangi simpan).

---

### Task 1: Logika murni kolom beku (`FrozenColumnLayout`)

**Files:**
- Create: `app/Support/FrozenColumnLayout.php`
- Test: `tests/Unit/FrozenColumnLayoutTest.php`

**Interfaces:**
- Consumes: tidak ada (murni, tanpa dependensi framework)
- Produces:
  - `FrozenColumnLayout::normalize(array $left, array $right, array $selected, array $available): array{left: array<int,string>, right: array<int,string>}`
  - `FrozenColumnLayout::renderOrder(array $selected, array $left, array $right): array<int,string>`

- [ ] **Step 1: Tulis test yang gagal**

Buat `tests/Unit/FrozenColumnLayoutTest.php`:

```php
<?php

namespace Tests\Unit;

use App\Support\FrozenColumnLayout;
use Tests\TestCase;

class FrozenColumnLayoutTest extends TestCase
{
    /** @var array<string,string> peta key => label, meniru $availableColumns */
    private array $available = [
        'nomor_agenda' => 'Nomor Agenda',
        'no_spp'       => 'No SPP',
        'nilai_rupiah' => 'Nilai Rupiah',
        'bulan'        => 'Bulan',
        'tahun'        => 'Tahun',
    ];

    public function test_menerima_pilihan_beku_yang_sah(): void
    {
        $hasil = FrozenColumnLayout::normalize(
            ['nomor_agenda'],
            ['nilai_rupiah'],
            ['nomor_agenda', 'no_spp', 'nilai_rupiah'],
            $this->available
        );

        $this->assertSame(['nomor_agenda'], $hasil['left']);
        $this->assertSame(['nilai_rupiah'], $hasil['right']);
    }

    public function test_membuang_kolom_yang_tidak_ditampilkan(): void
    {
        // 'nilai_rupiah' dikenal tapi tidak dicentang user -> tidak boleh beku.
        $hasil = FrozenColumnLayout::normalize(
            ['nomor_agenda', 'nilai_rupiah'],
            [],
            ['nomor_agenda', 'no_spp'],
            $this->available
        );

        $this->assertSame(['nomor_agenda'], $hasil['left']);
    }

    public function test_membuang_key_yang_tidak_dikenal(): void
    {
        $hasil = FrozenColumnLayout::normalize(
            ['nomor_agenda', 'kolom_karangan'],
            [],
            ['nomor_agenda', 'kolom_karangan'],
            $this->available
        );

        $this->assertSame(['nomor_agenda'], $hasil['left']);
    }

    public function test_membuang_duplikat(): void
    {
        $hasil = FrozenColumnLayout::normalize(
            ['nomor_agenda', 'nomor_agenda'],
            [],
            ['nomor_agenda'],
            $this->available
        );

        $this->assertSame(['nomor_agenda'], $hasil['left']);
    }

    public function test_kiri_menang_bila_key_ada_di_kedua_sisi(): void
    {
        $hasil = FrozenColumnLayout::normalize(
            ['no_spp'],
            ['no_spp'],
            ['nomor_agenda', 'no_spp'],
            $this->available
        );

        $this->assertSame(['no_spp'], $hasil['left']);
        $this->assertSame([], $hasil['right']);
    }

    public function test_membuang_nilai_kosong_dan_bukan_teks(): void
    {
        $hasil = FrozenColumnLayout::normalize(
            ['', '   ', 'nomor_agenda'],
            [],
            ['nomor_agenda'],
            $this->available
        );

        $this->assertSame(['nomor_agenda'], $hasil['left']);
    }

    /** Contoh persis dari spec §4. */
    public function test_urutan_render_memindahkan_kolom_beku_ke_tepi(): void
    {
        $urutan = FrozenColumnLayout::renderOrder(
            ['nomor_agenda', 'no_spp', 'nilai_rupiah', 'bulan'],
            ['nilai_rupiah'],
            ['no_spp']
        );

        $this->assertSame(
            ['nilai_rupiah', 'nomor_agenda', 'bulan', 'no_spp'],
            $urutan
        );
    }

    public function test_urutan_dalam_kelompok_mengikuti_urutan_pilihan(): void
    {
        $urutan = FrozenColumnLayout::renderOrder(
            ['nomor_agenda', 'no_spp', 'nilai_rupiah'],
            ['nilai_rupiah', 'nomor_agenda'],
            []
        );

        // Meski 'nilai_rupiah' disebut lebih dulu di daftar beku,
        // urutannya tetap mengikuti urutan pilihan user.
        $this->assertSame(
            ['nomor_agenda', 'nilai_rupiah', 'no_spp'],
            $urutan
        );
    }

    public function test_tanpa_beku_urutan_tidak_berubah(): void
    {
        $urutan = FrozenColumnLayout::renderOrder(
            ['nomor_agenda', 'no_spp', 'bulan'],
            [],
            []
        );

        $this->assertSame(['nomor_agenda', 'no_spp', 'bulan'], $urutan);
    }
}
```

- [ ] **Step 2: Jalankan test, pastikan GAGAL**

Run: `./vendor/bin/phpunit --filter=FrozenColumnLayoutTest`
Expected: FAIL — `Error: Class "App\Support\FrozenColumnLayout" not found` (9 error).

- [ ] **Step 3: Tulis implementasi minimal**

Buat `app/Support/FrozenColumnLayout.php`:

```php
<?php

namespace App\Support;

/**
 * Menyusun konfigurasi kolom beku (frozen) untuk tabel dokumen pembayaran.
 *
 * Kolom beku WAJIB menempel tepi tabel: `position: sticky` tidak bisa
 * membekukan kolom di tengah tanpa kolom di kirinya ikut beku — hasilnya
 * saling tumpang tindih saat digulir. Karena itu membekukan sebuah kolom
 * berarti memindahkannya ke tepi saat render.
 */
class FrozenColumnLayout
{
    /**
     * Bersihkan pilihan beku dari key yang tidak sah.
     *
     * Aturan: key harus dikenal ($available) DAN sedang ditampilkan
     * ($selected); duplikat dibuang; key yang muncul di kiri sekaligus di
     * kanan dimenangkan oleh kiri.
     *
     * @param  array<int,mixed>  $left
     * @param  array<int,mixed>  $right
     * @param  array<int,string>  $selected
     * @param  array<string,mixed>  $available  peta key => label
     * @return array{left: array<int,string>, right: array<int,string>}
     */
    public static function normalize(array $left, array $right, array $selected, array $available): array
    {
        $sanitize = static function (array $keys) use ($selected, $available): array {
            $result = [];

            foreach ($keys as $key) {
                $key = is_string($key) ? trim($key) : '';

                if ($key === '' || in_array($key, $result, true)) {
                    continue;
                }

                if (!array_key_exists($key, $available) || !in_array($key, $selected, true)) {
                    continue;
                }

                $result[] = $key;
            }

            return $result;
        };

        $leftClean = $sanitize($left);
        $rightClean = array_values(array_diff($sanitize($right), $leftClean));

        return ['left' => $leftClean, 'right' => $rightClean];
    }

    /**
     * Urutan render tabel: beku kiri -> kolom bebas -> beku kanan.
     * Urutan di dalam tiap kelompok mengikuti urutan pilihan user.
     *
     * @param  array<int,string>  $selected
     * @param  array<int,string>  $left
     * @param  array<int,string>  $right
     * @return array<int,string>
     */
    public static function renderOrder(array $selected, array $left, array $right): array
    {
        $frozenLeft = [];
        $middle = [];
        $frozenRight = [];

        foreach ($selected as $key) {
            if (in_array($key, $left, true)) {
                $frozenLeft[] = $key;
            } elseif (in_array($key, $right, true)) {
                $frozenRight[] = $key;
            } else {
                $middle[] = $key;
            }
        }

        return array_merge($frozenLeft, $middle, $frozenRight);
    }
}
```

- [ ] **Step 4: Jalankan test, pastikan LULUS**

Run: `./vendor/bin/phpunit --filter=FrozenColumnLayoutTest`
Expected: `OK (9 tests, ...)`

- [ ] **Step 5: Pastikan tidak merusak test lain**

Run: `./vendor/bin/phpunit`
Expected: jumlah kegagalan sama dengan sebelum Task 1 (catat baseline sebelum mulai).

- [ ] **Step 6: Commit**

```bash
git add app/Support/FrozenColumnLayout.php
git add tests/Unit/FrozenColumnLayoutTest.php
git commit -m "feat(pembayaran): logika kolom beku - validasi dan urutan render"
```

---

### Task 2: Controller membaca, menyimpan, dan mengirim konfigurasi beku

**Files:**
- Modify: `app/Http/Controllers/DashboardPembayaranController.php` (blok setelah baris 134; array data view di sekitar baris 569 dan 623)

**Interfaces:**
- Consumes: `FrozenColumnLayout::normalize()`, `FrozenColumnLayout::renderOrder()` dari Task 1
- Produces: variabel view baru `$frozenLeft` (array), `$frozenRight` (array), `$renderColumns` (array) — dipakai Task 4 dan Task 5

**Catatan penting:** `$availableColumns` baru dibuat di baris ~543, jauh setelah blok kolom di baris 101–134. Jangan memindahkan baris 543. Panggil ulang getter-nya secara lokal di blok baru — metodenya murni dan murah.

- [ ] **Step 1: Tambah import**

Di bagian `use` paling atas `DashboardPembayaranController.php`, tambahkan:

```php
use App\Support\FrozenColumnLayout;
```

- [ ] **Step 2: Sisipkan blok konfigurasi beku**

Sisipkan tepat SETELAH blok yang berakhir di baris 134 (blok `if (!in_array('tanggal_dibayar', ...))`) dan SEBELUM baris `// Handler yang dianggap "belum siap dibayar"`:

```php
        // === Konfigurasi kolom beku (frozen) ===
        // Dibaca/disimpan mengikuti pola selectedColumns di atas. Divalidasi
        // ulang setiap request karena kolom yang dibekukan bisa saja sudah
        // disembunyikan user lewat tab pertama modal.
        $frozenAvailableColumns = $this->getPembayaranDashboardAvailableColumns();
        $frozenDefault = ['left' => ['nomor_agenda'], 'right' => []];

        if (request()->has('frozen_left') || request()->has('frozen_right')) {
            $frozenRaw = [
                'left'  => (array) request('frozen_left', []),
                'right' => (array) request('frozen_right', []),
            ];
        } else {
            $user = Auth::user();

            if ($user && isset($user->table_columns_preferences['pembayaran_dashboard_frozen'])) {
                $frozenRaw = $user->table_columns_preferences['pembayaran_dashboard_frozen'];
            } else {
                $frozenRaw = session('pembayaran_dashboard_frozen_columns', $frozenDefault);
            }

            $frozenRaw = is_array($frozenRaw) ? $frozenRaw : $frozenDefault;
        }

        $frozen = FrozenColumnLayout::normalize(
            (array) ($frozenRaw['left'] ?? []),
            (array) ($frozenRaw['right'] ?? []),
            $selectedColumns,
            $frozenAvailableColumns
        );

        $frozenLeft = $frozen['left'];
        $frozenRight = $frozen['right'];

        if (request()->has('frozen_left') || request()->has('frozen_right')) {
            $user = Auth::user();

            if ($user) {
                $preferences = $user->table_columns_preferences ?? [];
                $preferences['pembayaran_dashboard_frozen'] = $frozen;
                $user->table_columns_preferences = $preferences;
                $user->save();
            }
        }

        session(['pembayaran_dashboard_frozen_columns' => $frozen]);

        // Urutan render tabel: beku kiri -> bebas -> beku kanan.
        // $selectedColumns sengaja TIDAK diubah agar tab pertama modal tetap
        // menampilkan urutan pilihan asli user.
        $renderColumns = FrozenColumnLayout::renderOrder($selectedColumns, $frozenLeft, $frozenRight);
```

- [ ] **Step 3: Kirim variabel baru ke view**

Ada DUA tempat yang harus diubah.

**(a)** Array `$data` di sekitar baris 569 — blok yang memuat `'availableColumns' => $availableColumns,`. Inilah yang dipakai `return view('pembayaranNEW.dashboardPembayaran', $data)` di baris ~628. Tambahkan tiga baris:

```php
            'frozenLeft' => $frozenLeft,
            'frozenRight' => $frozenRight,
            'renderColumns' => $renderColumns,
```

**(b)** Respons JSON untuk AJAX di sekitar baris 609-625 — blok `return response()->json([...])` yang memuat `'selectedColumns' => $selectedColumns,`. Tambahkan tepat di bawahnya:

```php
                'renderColumns' => $renderColumns,
                'frozenLeft' => $frozenLeft,
                'frozenRight' => $frozenRight,
```

Tanpa (b), pemuatan ulang lewat AJAX akan memakai urutan kolom lama sementara header tabel sudah memakai urutan baru — kolom dan isinya jadi tidak sinkron.

- [ ] **Step 4: Verifikasi tidak ada error sintaks**

Run: `php -l app/Http/Controllers/DashboardPembayaranController.php`
Expected: `No syntax errors detected`

- [ ] **Step 5: Verifikasi tidak ada regresi pada test yang ada**

Run: `./vendor/bin/phpunit`
Expected: jumlah kegagalan sama dengan baseline (tidak ada test baru yang pecah).

Lalu verifikasi manual di browser: buka `/documents/pembayaran/daftar?frozen_left[]=nomor_agenda`.
Expected: halaman tampil normal tanpa error 500, dan tampilannya belum berubah (Task 4 yang menerapkan efek visualnya).

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/DashboardPembayaranController.php
git commit -m "feat(pembayaran): baca dan simpan konfigurasi kolom beku"
```

---

### Task 3: Jalur opt-in di partial sticky bersama

**Files:**
- Modify: `resources/views/partials/_documentTableStickyCells.blade.php` (blok CSS baris ~125-133; blok JS `syncDocumentStickyOffsets` baris ~305-317)

**Interfaces:**
- Consumes: variabel Blade `$dynamicFrozen` (bool, default `false`)
- Produces: kontrak JS global `window.DOCUMENT_STICKY_CONFIG = { left: string[], right: string[] }` — bila diisi sebelum partial dijalankan, partial memakai perhitungan offset dinamis; bila `null`/tidak ada, memakai perhitungan lama yang tidak berubah.

**Aturan keselamatan:** default WAJIB mempertahankan perilaku lama persis, karena partial ini dipakai 4 halaman role lain.

- [ ] **Step 1: Tambahkan penjaga default di baris paling atas partial**

Sisipkan di baris 1 (sebelum `<style>`):

```blade
@php
    // Halaman yang memakai kolom beku pilihan user menyalakan $dynamicFrozen.
    // Default false => perilaku lama (kolom beku di-hardcode) tidak berubah.
    $dynamicFrozen = $dynamicFrozen ?? false;
@endphp
```

- [ ] **Step 2: Bungkus blok sticky hardcoded**

Blok CSS di baris ~125-133 yang berbunyi:

```css
  #documentTableContainer .data-table .col-checkbox,
  #documentTableContainer .data-table .col-no,
  #documentTableContainer .data-table .col-number,
  #documentTableContainer .data-table .col-nomor_agenda,
  #documentTableContainer .data-table .col-handler {
    position: sticky !important;
    background-clip: padding-box;
  }
```

Ubah menjadi dua aturan terpisah: kolom nomor urut tetap beku di kedua mode, sedangkan `col-nomor_agenda` dan `col-handler` hanya beku pada mode lama.

```blade
  #documentTableContainer .data-table .col-checkbox,
  #documentTableContainer .data-table .col-no,
  #documentTableContainer .data-table .col-number {
    position: sticky !important;
    background-clip: padding-box;
  }

@unless($dynamicFrozen)
  #documentTableContainer .data-table .col-nomor_agenda,
  #documentTableContainer .data-table .col-handler {
    position: sticky !important;
    background-clip: padding-box;
  }
@endunless
```

Dua deklarasi memang terduplikasi, dan itu disengaja: menyisipkan `@unless` di tengah daftar selector (mis. lewat ternary yang mencetak koma) membuat CSS-nya sulit dibaca dan gampang rusak saat diedit berikutnya.

- [ ] **Step 3: Tambahkan jalur perhitungan offset dinamis**

Di dalam `<script>`, ganti isi fungsi `syncDocumentStickyOffsets()` (baris ~305-317) sehingga bercabang. Sisipkan fungsi baru SEBELUM `syncDocumentStickyOffsets`:

```javascript
    function syncDynamicStickyOffsets(config) {
      const container = getContainer();
      const table = getTable(container);
      if (!container || !table) return;

      const numberWidth = measureWidth(table, '.col-no, .col-number');
      let leftOffset = numberWidth;

      (config.left || []).forEach(function (key) {
        const cells = table.querySelectorAll(`thead .col-${key}, tbody .col-${key}`);
        if (!cells.length) return;
        const width = cells[0].getBoundingClientRect().width;
        cells.forEach(cell => { cell.style.left = `${Math.round(leftOffset)}px`; });
        leftOffset += width;
      });

      let rightOffset = 0;

      (config.right || []).slice().reverse().forEach(function (key) {
        const cells = table.querySelectorAll(`thead .col-${key}, tbody .col-${key}`);
        if (!cells.length) return;
        const width = cells[0].getBoundingClientRect().width;
        cells.forEach(cell => { cell.style.right = `${Math.round(rightOffset)}px`; });
        rightOffset += width;
      });

      container.style.setProperty('--document-sticky-left-width', `${Math.round(leftOffset)}px`);
      container.style.setProperty('--document-sticky-right-width', `${Math.round(rightOffset)}px`);
    }
```

Lalu ubah baris pertama `syncDocumentStickyOffsets()` menjadi:

```javascript
    function syncDocumentStickyOffsets() {
      const dynamicConfig = window.DOCUMENT_STICKY_CONFIG;
      if (dynamicConfig) {
        syncDynamicStickyOffsets(dynamicConfig);
        return;
      }

      const container = getContainer();
      const table = getTable(container);
      if (!container || !table) return;
      // ...sisa fungsi lama dibiarkan apa adanya...
```

- [ ] **Step 4: Verifikasi 4 halaman role lain tidak berubah**

Buka berurutan dan pastikan kolom beku bawaannya masih berfungsi (gulir horizontal, kolom No & Nomor Agenda tetap diam, Pengurus Dokumen tetap menempel kanan):

1. `/documents` (operator)
2. halaman daftar akuntansi
3. halaman daftar perpajakan
4. halaman daftar tim verifikasi

Expected: tidak ada perubahan visual sama sekali dibanding sebelum Task 3.

- [ ] **Step 5: Commit**

```bash
git add resources/views/partials/_documentTableStickyCells.blade.php
git commit -m "feat(tabel): jalur opt-in kolom beku dinamis di partial sticky"
```

---

### Task 4: Terapkan kolom beku di halaman pembayaran

**Files:**
- Modify: `resources/views/pembayaranNEW/dashboardPembayaran.blade.php` (header tabel baris ~2277; `const selectedColumns` baris ~3362; include partial baris ~3656)

**Interfaces:**
- Consumes: `$frozenLeft`, `$frozenRight`, `$renderColumns` dari Task 2; kontrak `window.DOCUMENT_STICKY_CONFIG` dari Task 3
- Produces: tabel yang benar-benar membekukan kolom sesuai konfigurasi

- [ ] **Step 1: Render header memakai urutan baru**

Ganti baris ~2277-2279:

```blade
                  @foreach($selectedColumns as $colKey)
                    <th class="col-{{ $colKey }}">{{ $availableColumns[$colKey] ?? Str::headline($colKey) }}</th>
                  @endforeach
```

menjadi:

```blade
                  @foreach($renderColumns as $colKey)
                    <th class="col-{{ $colKey }}">{{ $availableColumns[$colKey] ?? Str::headline($colKey) }}</th>
                  @endforeach
```

- [ ] **Step 2: Render sel memakai urutan baru**

Ganti baris ~3362:

```javascript
        const selectedColumns = @json($selectedColumns);
```

menjadi:

```javascript
        // Urutan render tabel (beku kiri -> bebas -> beku kanan). Berbeda dari
        // selectedColumnsOrder di modal, yang menyimpan urutan pilihan asli user.
        const selectedColumns = @json($renderColumns);
```

- [ ] **Step 3: Nyalakan mode dinamis dan set konfigurasi**

Ganti baris ~3656:

```blade
  @include('partials._documentTableStickyCells')
```

menjadi:

```blade
  <script>
    // Harus diset SEBELUM partial dijalankan agar perhitungan offset memakai
    // jalur dinamis, bukan jalur lama yang kolomnya di-hardcode.
    window.DOCUMENT_STICKY_CONFIG = @json(['left' => $frozenLeft, 'right' => $frozenRight]);
  </script>

  @include('partials._documentTableStickyCells', ['dynamicFrozen' => true])
```

- [ ] **Step 4: Tambahkan CSS kolom beku dinamis**

Sisipkan blok `<style>` baru tepat setelah `@include` di Step 3. CSS ini menyasar kelas `col-<key>` yang sudah melekat di setiap sel, jadi tidak perlu kelas baru:

```blade
  <style>
    @foreach(array_merge($frozenLeft, $frozenRight) as $frozenKey)
      #documentTableContainer .data-table .col-{{ $frozenKey }} {
        position: sticky !important;
        background-clip: padding-box;
      }

      #documentTableContainer .data-table thead .col-{{ $frozenKey }} {
        background: #0d3b6e !important;
        box-shadow: 0 2px 0 #1a5276 !important;
        z-index: 560 !important;
      }

      #documentTableContainer .data-table tbody .col-{{ $frozenKey }} {
        background: #ffffff !important;
        z-index: 30 !important;
      }

      #documentTableContainer .data-table tbody tr:nth-child(even) .col-{{ $frozenKey }} {
        background: #f8fafc !important;
      }

      #documentTableContainer .data-table tbody tr:hover .col-{{ $frozenKey }} {
        background: #f3faf9 !important;
      }
    @endforeach
  </style>
```

- [ ] **Step 5: Verifikasi manual**

1. Buka `/documents/pembayaran/daftar` — tampilan harus **identik dengan sebelumnya** (bawaan: Nomor Agenda beku kiri)
2. Tambahkan `?frozen_left[]=nilai_rupiah` ke URL → kolom Nilai Rupiah pindah ke tepi kiri dan **diam saat digulir horizontal**
3. Tambahkan `?frozen_right[]=bulan` → kolom Bulan menempel di tepi kanan
4. Muat ulang tanpa parameter → konfigurasi terakhir bertahan (tersimpan di preferensi user)

- [ ] **Step 6: Commit**

```bash
git add resources/views/pembayaranNEW/dashboardPembayaran.blade.php
git commit -m "feat(pembayaran): terapkan kolom beku sesuai konfigurasi user"
```

---

### Task 5: Tab kedua di modal kustomisasi

**Files:**
- Modify: `resources/views/pembayaranNEW/dashboardPembayaran.blade.php` (modal baris ~2665-2680; JS state baris ~2934; `saveColumnCustomization()` baris ~3087)

**Interfaces:**
- Consumes: `$frozenLeft`, `$frozenRight` dari Task 2
- Produces: UI lengkap; mengirim `columns[]`, `frozen_left[]`, `frozen_right[]` dalam satu navigasi

- [ ] **Step 1: Tambahkan markup tab di modal**

Sisipkan tepat setelah `</div>` penutup `modal-header-custom` (baris ~2675), sebelum `<div class="modal-body-custom">`:

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

Lalu bungkus isi `customization-grid` yang sudah ada dengan `<div id="tabPanelKolom">...</div>`, dan tambahkan panel kedua sesudahnya:

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

- [ ] **Step 2: Tambahkan CSS tab dan baris beku**

Sisipkan ke blok `<style>` milik modal (sebelum baris ~2663 `</style>`):

```css
    .column-tabs {
      display: flex;
      gap: 0.5rem;
      padding: 0 1.5rem;
      border-bottom: 1px solid #e2e8f0;
    }

    .column-tab {
      padding: 0.75rem 1.1rem;
      border: none;
      background: transparent;
      font-size: 0.85rem;
      font-weight: 700;
      color: #64748b;
      cursor: pointer;
      border-bottom: 3px solid transparent;
    }

    .column-tab.active {
      color: #0f4c3a;
      border-bottom-color: #0f4c3a;
    }

    .frozen-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
      padding: 0.6rem 0.9rem;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      margin-bottom: 0.5rem;
    }

    .frozen-options {
      display: inline-flex;
      gap: 0.25rem;
    }

    .frozen-opt {
      padding: 0.35rem 0.75rem;
      border: 1px solid #cbd5e1;
      background: #ffffff;
      border-radius: 6px;
      font-size: 0.78rem;
      font-weight: 700;
      color: #64748b;
      cursor: pointer;
    }

    .frozen-opt.active {
      background: #0f4c3a;
      border-color: #0f4c3a;
      color: #ffffff;
    }

    .frozen-warning {
      padding: 0.7rem 0.9rem;
      margin-bottom: 0.75rem;
      border-radius: 8px;
      background: #fef3c7;
      border: 1px solid #fcd34d;
      color: #92400e;
      font-size: 0.82rem;
      font-weight: 600;
    }
```

- [ ] **Step 3: Tambahkan state dan fungsi JS**

Sisipkan setelah baris ~2935 (`const availableColumnsData = @json($availableColumns);`) — harus SESUDAH baris itu karena `renderFrozenTab()` memakai `availableColumnsData`:

```javascript
    let frozenLeftOrder = @json($frozenLeft);
    let frozenRightOrder = @json($frozenRight);

    // Peta lebar kolom untuk estimasi peringatan; angka mengikuti CSS di
    // partials/_documentTableStickyCells.blade.php.
    const FROZEN_WIDTH_MAP = { nomor_agenda: 210, handler: 240 };
    const FROZEN_WIDTH_DEFAULT = 132;
    const FROZEN_NO_COLUMN_WIDTH = 88;

    function switchColumnTab(tab) {
      document.querySelectorAll('.column-tab').forEach(function (btn) {
        btn.classList.toggle('active', btn.dataset.tab === tab);
      });
      document.getElementById('tabPanelKolom').style.display = tab === 'kolom' ? '' : 'none';
      document.getElementById('tabPanelBeku').style.display = tab === 'beku' ? '' : 'none';
      if (tab === 'beku') renderFrozenTab();
    }

    function getFrozenState(key) {
      if (frozenLeftOrder.includes(key)) return 'left';
      if (frozenRightOrder.includes(key)) return 'right';
      return 'none';
    }

    function setFrozenState(key, state) {
      frozenLeftOrder = frozenLeftOrder.filter(k => k !== key);
      frozenRightOrder = frozenRightOrder.filter(k => k !== key);
      if (state === 'left') frozenLeftOrder.push(key);
      if (state === 'right') frozenRightOrder.push(key);
      renderFrozenTab();
    }

    function renderFrozenTab() {
      // Kolom yang sudah tidak ditampilkan tidak boleh tetap beku.
      frozenLeftOrder = frozenLeftOrder.filter(k => selectedColumnsOrder.includes(k));
      frozenRightOrder = frozenRightOrder.filter(k => selectedColumnsOrder.includes(k));

      const list = document.getElementById('frozenList');
      if (!list) return;

      list.innerHTML = selectedColumnsOrder.map(function (key) {
        const label = availableColumnsData[key] || key;
        const state = getFrozenState(key);
        const opt = (value, text) =>
          '<button type="button" class="frozen-opt' + (state === value ? ' active' : '') +
          '" onclick="setFrozenState(\'' + key + '\', \'' + value + '\')">' + text + '</button>';
        return '<div class="frozen-row"><span>' + label + '</span>' +
          '<span class="frozen-options">' + opt('left', 'Kiri') + opt('none', 'Bebas') + opt('right', 'Kanan') +
          '</span></div>';
      }).join('');

      renderFrozenWarning();
    }

    function renderFrozenWarning() {
      const box = document.getElementById('frozenWarning');
      if (!box) return;

      const total = frozenLeftOrder.concat(frozenRightOrder).reduce(function (sum, key) {
        return sum + (FROZEN_WIDTH_MAP[key] || FROZEN_WIDTH_DEFAULT);
      }, FROZEN_NO_COLUMN_WIDTH);

      const limit = window.innerWidth * 0.5;

      if (total > limit) {
        box.style.display = '';
        box.textContent = 'Kolom beku memakan sekitar ' + Math.round(total) +
          'px dari lebar layar Anda. Area yang bisa digulir jadi sempit — pertimbangkan mengurangi kolom beku.';
      } else {
        box.style.display = 'none';
      }
    }
```

- [ ] **Step 4: Kirim konfigurasi beku saat menyimpan**

Di `saveColumnCustomization()` (baris ~3087), setelah blok yang menambahkan `columns[]`, sisipkan sebelum `window.location.href = url.toString();`:

```javascript
      url.searchParams.delete('frozen_left[]');
      url.searchParams.delete('frozen_right[]');

      // Penanda WAJIB: tanpa ini, "user melepas semua kolom beku" tidak bisa
      // dibedakan dari "tidak ada konfigurasi beku yang dikirim", karena
      // keduanya sama-sama tidak mengirim frozen_left/frozen_right. Akibatnya
      // controller akan membekukan ulang dari preferensi tersimpan dan user
      // tidak pernah bisa mengosongkan kolom beku.
      url.searchParams.set('frozen_config', '1');

      // Kolom yang sudah tidak ditampilkan tidak boleh ikut dikirim sebagai beku.
      frozenLeftOrder
        .filter(col => selectedColumnsOrder.includes(col))
        .forEach(col => url.searchParams.append('frozen_left[]', col));

      frozenRightOrder
        .filter(col => selectedColumnsOrder.includes(col))
        .forEach(col => url.searchParams.append('frozen_right[]', col));
```

- [ ] **Step 5: Verifikasi manual lengkap (gerbang wajib)**

Jalankan seluruh 9 langkah uji dari spec §9:

1. Modal punya 2 tab; tab beku hanya memuat kolom yang tercentang di tab 1
2. Bekukan kolom tengah → Simpan → kolom pindah ke tepi dan diam saat digulir horizontal
3. Bekukan satu kolom ke kanan → menempel di tepi kanan
4. Lepas semua beku → tabel bergulir penuh, tidak ada sisa sticky
5. Sembunyikan kolom yang sedang beku di tab 1 → buka tab beku → kolom itu hilang dari daftar dan status bekunya lepas
6. Bekukan beberapa kolom lebar → peringatan lebar muncul
6b. **Lepas SEMUA kolom beku (semua diset Bebas) → Simpan → tabel benar-benar tanpa kolom beku, dan tetap begitu setelah muat ulang.** Ini menguji penanda `frozen_config`; tanpa penanda, konfigurasi lama akan hidup kembali.
7. **Buka keempat halaman role lain → kolom beku bawaannya tidak berubah**
8. Muat ulang halaman → konfigurasi beku bertahan
9. Ulangi langkah 2-4 dalam mode Fullscreen

- [ ] **Step 6: Commit**

```bash
git add resources/views/pembayaranNEW/dashboardPembayaran.blade.php
git commit -m "feat(pembayaran): tab kolom beku di modal kustomisasi kolom"
```

---

## Catatan Deviasi dari Spec

Spec §6 menyebut server menambahkan kelas `is-frozen-left`/`is-frozen-right` pada `th`/`td`.
Rencana ini **tidak** memakai kelas baru, karena penelusuran menunjukkan `<tbody>` diisi di
sisi klien oleh `renderFallbackRows()` (baris ~3511) sehingga kelas server-side tidak akan
sampai ke `<td>`. Setiap sel sudah punya kelas `col-<key>`, jadi CSS beku cukup menyasar
kelas itu — hasilnya sama, tanpa perlu menyentuh pembuat baris. Sisa spec tidak berubah.

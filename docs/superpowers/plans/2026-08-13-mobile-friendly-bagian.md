# Tampilan Ramah Ponsel — Tahap 1 (role Bagian) — Rencana Implementasi

> **Untuk pekerja agentik:** SUB-SKILL WAJIB: pakai superpowers:subagent-driven-development
> (disarankan) atau superpowers:executing-plans untuk mengerjakan rencana ini task demi task.
> Langkah memakai sintaks checkbox (`- [ ]`) untuk penjejakan.

**Goal:** Membuat halaman `/bagian/documents` terbaca nyaman di ponsel — nol gulir
horizontal — tanpa mengubah satu piksel pun tampilan desktop.

**Arsitektur:** Berkas CSS baru `public/css/mobile.css` yang seluruh isinya terkurung
`@media (max-width: 768px)`, disusun per-blok berlabel kelas layout (`.bagian-layout`)
agar role berikutnya menambah blok, bukan mengubah blok yang sudah lulus QA. Daftar
dokumen dirender **dua kali**: tabel lama (tampil di desktop) dan partial kartu baru
(tampil di ponsel), keduanya membaca status pembayaran dari satu helper bersama.

**Tech Stack:** Laravel 12, Blade, PHP 8.2, Bootstrap 5 (CDN), CSS murni (tanpa Vite —
`@vite` mati di project ini), PHPUnit.

**Spec:** `docs/superpowers/specs/2026-08-13-mobile-friendly-bagian-design.md`

## Global Constraints

- **Breakpoint tunggal: `max-width: 768px`.** Semua aturan mobile memakai ambang ini.
- **NOL perubahan tampilan desktop.** Setiap deklarasi CSS baru wajib berada di dalam blok
  `@media`. Task 2 memasang test yang menegakkan ini secara mekanis.
- **Jangan tambah CSS inline baru** (CLAUDE.md §4) — CSS baru masuk `public/css/mobile.css`.
- **Aset dimuat lewat `\App\Support\Asset::versioned('css/mobile.css')`**, bukan `asset()`
  polos — mengikuti pola berkas CSS lain di layout.
- **UI/komentar Bahasa Indonesia, identifier English** (aturan global user).
- **Commit per-file** (`git add <berkas>`), jangan `git add .` / `-A`.
- **Test terfilter saat iterasi**: `php artisan test --filter=NamaTest`. Suite penuh
  **sekali** sebelum push (CLAUDE.md §3 & §7). `--parallel` tidak tersedia (paratest tak
  terpasang).
- **Tiap assertion baru wajib dibuktikan menggigit** (CLAUDE.md §3.8): rusakkan kode yang
  dijaga → test GAGAL → pulihkan → LULUS → `git diff <berkas>` kosong sebelum lanjut.
- **Jangan mengedit berkas lewat skrip Python** — `autocrlf` mengubah LF jadi CRLF dan
  mematahkan test pembanding sumber, sementara `git diff` tampak kosong (memori
  `autocrlf-jebakan-edit-python`). Pakai tool edit biasa.

---

## Temuan pra-implementasi (dibaca sebelum Task 1)

**Cabang `$col == 'status_pembayaran'` di `daftarDokumen.blade.php:1351-1395` adalah KODE
MATI.** `status_pembayaran` tidak terdaftar di `$availableColumns`
(`BagianDokumenController.php:126-141`), dan guard `array_intersect` di baris 162 & 181
membuang kolom apa pun di luar daftar itu. Cabang tersebut tak pernah bisa dieksekusi.

Ini pola yang sama persis dengan cabang `$col == 'status'` yang sudah dihapus di commit
`c839666` (dicatat sebagai komentar di baris 1281-1284 view yang sama).

Kolom status yang **hidup** adalah `<td class="col-status_pembayaran">` di baris 1469-1505 —
kolom tetap paling kanan, di luar perulangan `$selectedColumns`.

**Keputusan:** Task 1 mengganti isi kolom hidup (baris 1469) dengan pemanggilan helper.
Cabang mati di baris 1351 **TIDAK disentuh di rencana ini** — menghapusnya adalah pekerjaan
dead-code tersendiri yang butuh grep-gate + persetujuan user (CLAUDE.md §6). Dicatat di
§Utang agar tidak hilang.

---

## Struktur berkas

| Berkas | Tanggung jawab |
|---|---|
| `app/Support/StatusPembayaranBagian.php` | **BARU.** Satu-satunya sumber aturan status pembayaran 3-state untuk role Bagian. Kelas biasa, statis, tanpa dependensi Auth/Request — bisa di-unit-test tanpa kelas inang (preseden: `App\Support\ColumnCustomization`). |
| `public/css/mobile.css` | **BARU.** Seluruh gaya ponsel. Blok A = shell semua role; Blok B = `.bagian-layout`. |
| `resources/views/bagian/partials/_kartuDokumenMobile.blade.php` | **BARU.** Markup kartu + pembungkus `.mob-cards` yang hanya tampil di ponsel. |
| `resources/views/layouts/app.blade.php` | **UBAH, aditif.** `<link>` mobile.css; elemen scrim; cabang lebar layar di handler hamburger. |
| `resources/views/bagian/dokumens/daftarDokumen.blade.php` | **UBAH.** `<td>` status memanggil helper; `@include` partial kartu. |
| `tests/Unit/StatusPembayaranBagianTest.php` | **BARU.** Task 1. |
| `tests/Feature/MobileBagianTest.php` | **BARU.** Task 2-4. |

---

## Task 1: Helper status pembayaran 3-state

Mengekstrak aturan status dari blok `@php` inline agar tabel dan kartu tidak menghitungnya
dua kali. Murni pemindahan — output wajib identik.

**Files:**
- Create: `app/Support/StatusPembayaranBagian.php`
- Create: `tests/Unit/StatusPembayaranBagianTest.php`
- Modify: `resources/views/bagian/dokumens/daftarDokumen.blade.php:1469-1505`

**Interfaces:**
- Consumes: model `App\Models\Dokumen` (properti `status_pembayaran`, `tanggal_dibayar`,
  `current_handler`, `sent_at`, `created_at`; method `getDataForRole(string): ?DokumenRoleData`).
- Produces: `App\Support\StatusPembayaranBagian::untuk(Dokumen $doc): array` mengembalikan
  `['kelas' => string, 'teks' => string, 'ikon' => string, 'tanggal' => mixed]`.
  Dipakai Task 3 (kartu). Nama kunci array ini dipakai apa adanya di Blade.

- [ ] **Step 1: Tulis test yang gagal**

Buat `tests/Unit/StatusPembayaranBagianTest.php`:

```php
<?php

namespace Tests\Unit;

use App\Models\Dokumen;
use App\Support\StatusPembayaranBagian;
use PHPUnit\Framework\TestCase;

/**
 * Menguji aturan status pembayaran 3-state milik role Bagian.
 *
 * PHPUnit\TestCase (bukan Tests\TestCase): helper murni, tanpa DB.
 * Model dibuat via `new Dokumen()` + isi atribut — tidak disimpan.
 */
class StatusPembayaranBagianTest extends TestCase
{
    public function test_sudah_dibayar_saat_status_pembayaran_final(): void
    {
        $doc = new Dokumen();
        $doc->status_pembayaran = 'sudah_dibayar';
        $doc->tanggal_dibayar = '2026-08-11 10:00:00';

        $hasil = StatusPembayaranBagian::untuk($doc);

        $this->assertSame('sudah-dibayar', $hasil['kelas']);
        $this->assertSame('Sudah Dibayar', $hasil['teks']);
        $this->assertSame('fa-check-circle', $hasil['ikon']);
        $this->assertSame('2026-08-11 10:00:00', $hasil['tanggal']);
    }

    public function test_sudah_dibayar_saat_hanya_tanggal_dibayar_terisi(): void
    {
        // Cabang OR: tanggal_dibayar terisi TAPI status_pembayaran belum final.
        // Mudah pecah saat diekstrak kalau OR keliru jadi AND.
        $doc = new Dokumen();
        $doc->status_pembayaran = null;
        $doc->tanggal_dibayar = '2026-08-12 08:30:00';
        $doc->current_handler = 'team_verifikasi';

        $hasil = StatusPembayaranBagian::untuk($doc);

        $this->assertSame('sudah-dibayar', $hasil['kelas']);
        $this->assertSame('Sudah Dibayar', $hasil['teks']);
    }

    public function test_belum_siap_dibayar_saat_masih_di_role_lain(): void
    {
        $doc = new Dokumen();
        $doc->status_pembayaran = null;
        $doc->tanggal_dibayar = null;
        $doc->current_handler = 'team_verifikasi';
        $doc->sent_at = '2026-08-01 09:00:00';

        $hasil = StatusPembayaranBagian::untuk($doc);

        $this->assertSame('belum-dibayar', $hasil['kelas']);
        $this->assertSame('Belum Siap Dibayar', $hasil['teks']);
        $this->assertSame('fa-clock', $hasil['ikon']);
        $this->assertSame('2026-08-01 09:00:00', $hasil['tanggal']);
    }

    public function test_handler_pembayaran_dikenali_case_insensitive(): void
    {
        // Kode lama memakai str_contains(strtolower(...), 'pembayaran').
        // Handler produksi pernah ditulis 'Team Pembayaran' berkapital.
        $doc = new Dokumen();
        $doc->status_pembayaran = null;
        $doc->tanggal_dibayar = null;
        $doc->current_handler = 'Team Pembayaran';

        $hasil = StatusPembayaranBagian::untuk($doc);

        $this->assertSame('siap-dibayar', $hasil['kelas']);
        $this->assertSame('Siap Dibayar', $hasil['teks']);
        $this->assertSame('fa-money-bill-wave', $hasil['ikon']);
    }

    public function test_current_handler_null_tidak_meledak(): void
    {
        $doc = new Dokumen();
        $doc->status_pembayaran = null;
        $doc->tanggal_dibayar = null;
        $doc->current_handler = null;
        $doc->sent_at = null;

        $hasil = StatusPembayaranBagian::untuk($doc);

        $this->assertSame('belum-dibayar', $hasil['kelas']);
    }
}
```

- [ ] **Step 2: Jalankan test, pastikan GAGAL**

```
php artisan test --filter=StatusPembayaranBagianTest
```

Harapan: FAIL — `Class "App\Support\StatusPembayaranBagian" not found`.

- [ ] **Step 3: Tulis implementasi minimal**

Buat `app/Support/StatusPembayaranBagian.php`. Salin logika **apa adanya** dari
`daftarDokumen.blade.php:1470-1491` — jangan "rapikan" aturannya, ini pemindahan:

```php
<?php

namespace App\Support;

use App\Models\Dokumen;

/**
 * Aturan badge Status Pembayaran untuk role Bagian (3-state).
 *
 * Diekstrak dari blok @php inline di dalam <td> agar tabel desktop dan kartu
 * mobile membaca aturan yang SAMA. Menyalinnya ke partial kartu akan melahirkan
 * salinan kedua aturan bisnis — penyakit utama yang dilarang CLAUDE.md §3.1.
 *
 * Kelas biasa + method statis (bukan accessor model): bisa di-unit-test tanpa
 * DB maupun kelas inang. Preseden: App\Support\ColumnCustomization.
 */
class StatusPembayaranBagian
{
    /**
     * @return array{kelas: string, teks: string, ikon: string, tanggal: mixed}
     */
    public static function untuk(Dokumen $doc): array
    {
        // "Sudah dibayar" = status final ATAU tanggal_dibayar terisi (OR, bukan AND).
        $isPaid = $doc->status_pembayaran === 'sudah_dibayar' || !empty($doc->tanggal_dibayar);

        if ($isPaid) {
            return [
                'kelas'   => 'sudah-dibayar',
                'teks'    => 'Sudah Dibayar',
                'ikon'    => 'fa-check-circle',
                'tanggal' => $doc->tanggal_dibayar,
            ];
        }

        // Dokumen sedang berada di Tim Pembayaran — belum dibayar, tapi sudah siap.
        if (str_contains(strtolower($doc->current_handler ?? ''), 'pembayaran')) {
            return [
                'kelas'   => 'siap-dibayar',
                'teks'    => 'Siap Dibayar',
                'ikon'    => 'fa-money-bill-wave',
                'tanggal' => $doc->getDataForRole('pembayaran')?->received_at,
            ];
        }

        return [
            'kelas'   => 'belum-dibayar',
            'teks'    => 'Belum Siap Dibayar',
            'ikon'    => 'fa-clock',
            'tanggal' => $doc->sent_at ?? $doc->created_at,
        ];
    }
}
```

- [ ] **Step 4: Jalankan test, pastikan LULUS**

```
php artisan test --filter=StatusPembayaranBagianTest
```

Harapan: PASS, 5 test.

- [ ] **Step 5: Buktikan test menggigit**

Ubah `'Sudah Dibayar'` jadi `'Sudah Dibayarr'` di helper → jalankan test → **wajib GAGAL**.
Pulihkan. Ubah `||` jadi `&&` pada `$isPaid` → jalankan → **wajib GAGAL** (test kedua).
Pulihkan. Lalu:

```
git diff app/Support/StatusPembayaranBagian.php
```

Harapan: **kosong**. Kalau tidak kosong, mutasi masih tertinggal — pulihkan dulu.

- [ ] **Step 6: Sambungkan tabel ke helper**

Di `resources/views/bagian/dokumens/daftarDokumen.blade.php`, ganti blok `@php` di dalam
`<td class="col-status_pembayaran">` (baris 1469-1505). Yang lama:

```blade
<td class="col-status_pembayaran">
  @php
    // Status pembayaran (kolom tetap paling kanan, beku).
    $isPaid = $doc->status_pembayaran === 'sudah_dibayar' || !empty($doc->tanggal_dibayar);
    ...
  @endphp
  <div class="payment-status-container"
```

Jadi:

```blade
<td class="col-status_pembayaran">
  @php
    // Aturan 3-state dipusatkan di App\Support\StatusPembayaranBagian agar
    // kartu mobile (_kartuDokumenMobile) membaca aturan yang sama.
    $status = \App\Support\StatusPembayaranBagian::untuk($doc);
  @endphp
  <div class="payment-status-container"
    style="display: flex; flex-direction: column; align-items: center; gap: 4px;">
    <span class="payment-status-badge {{ $status['kelas'] }}">
      <i class="fa-solid {{ $status['ikon'] }}"></i>
      {{ $status['teks'] }}
    </span>
    @if($status['tanggal'])
      <small style="font-size: 10px; color: #6c757d; text-align: center;">
        {{ \Carbon\Carbon::parse($status['tanggal'])->format('d M Y, H:i') }}
      </small>
    @endif
  </div>
</td>
```

**PENTING:** hanya blok `@php` + nama variabel yang berubah. Markup, kelas CSS, gaya inline,
dan format tanggal (`d M Y, H:i`) wajib identik — ini kolom yang tampil di desktop.

- [ ] **Step 7: Jalankan test bagian yang sudah ada, pastikan tidak ada regresi**

```
php artisan test --filter=PerjalananDokumenBagianTest
php artisan test --filter=NotifikasiPengembalianBagianTest
```

Harapan: PASS keduanya. Keduanya merender halaman ini penuh, jadi kesalahan sintaks Blade
atau nama kunci array akan tertangkap di sini.

- [ ] **Step 8: Commit**

```bash
git add app/Support/StatusPembayaranBagian.php
git add tests/Unit/StatusPembayaranBagianTest.php
git add resources/views/bagian/dokumens/daftarDokumen.blade.php
git commit -m "refactor(bagian): pusatkan aturan status pembayaran 3-state ke App\Support"
```

---

## Task 2: Berkas CSS mobile + shell (drawer, topbar, konten)

**Files:**
- Create: `public/css/mobile.css`
- Create: `tests/Feature/MobileBagianTest.php`
- Modify: `resources/views/layouts/app.blade.php` (link CSS ~baris 26; scrim setelah topbar
  ~baris 3317; cabang lebar layar di handler hamburger ~baris 3838-3851)

**Interfaces:**
- Consumes: kelas `.bagian-layout` di `<body>` (`layouts/app.blade.php:3141`), tombol
  `[data-sidebar-toggle]` (baris 3306), `\App\Support\Asset::versioned()`.
- Produces: kelas `.mobile-drawer-open` pada `<html>`; elemen `.mobile-drawer-scrim`;
  berkas `public/css/mobile.css`. Task 3 & 4 menambah blok ke berkas ini.

- [ ] **Step 1: Tulis test yang gagal**

Buat `tests/Feature/MobileBagianTest.php`:

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Menjaga kontrak tampilan ponsel role Bagian.
 *
 * Test paling penting di berkas ini adalah
 * test_seluruh_aturan_mobile_terkurung_media_query — ia menegakkan janji
 * "nol perubahan desktop" secara mekanis, bukan sekadar niat baik.
 */
class MobileBagianTest extends TestCase
{
    private function mobileCss(): string
    {
        $path = public_path('css/mobile.css');
        $this->assertFileExists($path, 'public/css/mobile.css wajib ada.');

        return file_get_contents($path);
    }

    public function test_seluruh_aturan_mobile_terkurung_media_query(): void
    {
        $css = $this->mobileCss();

        // Buang komentar /* ... */ agar contoh kode di dalamnya tak ikut terhitung.
        $tanpaKomentar = preg_replace('#/\*.*?\*/#s', '', $css);

        // Buang setiap blok @media beserta isinya (kurung bersarang 1 tingkat).
        $diLuarMedia = preg_replace('#@media[^{]*\{(?:[^{}]*\{[^{}]*\})*[^{}]*\}#s', '', $tanpaKomentar);

        // Yang tersisa harus tak punya deklarasi CSS sama sekali.
        $this->assertStringNotContainsString(
            '{',
            trim($diLuarMedia),
            'Ada aturan CSS di LUAR @media — ini akan mengubah tampilan desktop.'
        );
    }

    public function test_mobile_css_ter_link_di_layout(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/app.blade.php'));

        // Wajib lewat Asset::versioned() — bukan asset() polos — agar cache
        // browser ter-bust saat berkas berubah (pola berkas CSS lain di layout).
        $this->assertStringContainsString("Asset::versioned('css/mobile.css')", $layout);
    }

    public function test_layout_punya_scrim_dan_cabang_lebar_layar(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/app.blade.php'));

        $this->assertStringContainsString('mobile-drawer-scrim', $layout);
        // Cabang lebar layar: di ponsel hamburger menggerakkan drawer, BUKAN
        // menulis localStorage sidebar_collapsed milik desktop.
        $this->assertStringContainsString('mobile-drawer-open', $layout);
        $this->assertStringContainsString('max-width: 768px', $layout);
    }

    public function test_drawer_dan_konten_diatur_di_css(): void
    {
        $css = $this->mobileCss();

        // Sidebar disembunyikan dengan transform (bukan display:none) supaya
        // bisa dianimasikan menggeser masuk.
        $this->assertStringContainsString('.sidebar-owner', $css);
        $this->assertStringContainsString('translateX(-100%)', $css);
        // Konten mengambil lebar penuh — inilah yang mengembalikan 72px yang dicuri sidebar.
        $this->assertStringContainsString('margin-left: 0', $css);
    }
}
```

- [ ] **Step 2: Jalankan test, pastikan GAGAL**

```
php artisan test --filter=MobileBagianTest
```

Harapan: FAIL — `public/css/mobile.css wajib ada.`

- [ ] **Step 3: Buat `public/css/mobile.css`**

Seluruh isi berada di dalam `@media`. Blok A dulu (shell, semua role):

```css
/**
 * Gaya khusus layar ponsel — Agenda Online PTPN.
 *
 * ATURAN BERKAS INI: setiap deklarasi WAJIB berada di dalam @media
 * (max-width: 768px). Tidak boleh ada satu pun aturan di luar blok media —
 * dijaga test MobileBagianTest::test_seluruh_aturan_mobile_terkurung_media_query.
 * Ini yang membuat janji "nol perubahan desktop" bisa ditegakkan mesin.
 *
 * Susunan per BLOK berlabel kelas layout di <body>:
 *   Blok A — shell (topbar, drawer, konten): berlaku semua role.
 *   Blok B — .bagian-layout: hanya halaman role Bagian.
 * Role berikutnya MENAMBAH blok baru (.operator-layout, .payment-layout, dst),
 * bukan mengubah blok yang sudah lulus QA.
 */

@media (max-width: 768px) {
  /* ===== Blok A — Shell: drawer, topbar, konten ===== */

  /* Sidebar jadi panel geser. transform (bukan display:none) supaya bisa
     dianimasikan, dan supaya isinya tetap terbaca screen reader. */
  body.owner-layout .sidebar-owner {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: 82%;
    max-width: 300px;
    transform: translateX(-100%);
    transition: transform 0.25s ease;
    z-index: 1200; /* di atas topbar (1100) */
    overflow-y: auto;
  }

  html.mobile-drawer-open body.owner-layout .sidebar-owner {
    transform: translateX(0);
  }

  /* Latar gelap saat drawer terbuka. Tap di mana pun padanya menutup drawer. */
  .mobile-drawer-scrim {
    display: block;
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.5);
    z-index: 1150;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.25s ease;
  }

  html.mobile-drawer-open .mobile-drawer-scrim {
    opacity: 1;
    pointer-events: auto;
  }

  /* Konten memakai lebar penuh — mengembalikan 72px yang dicuri sidebar.
     !important diperlukan: aturan lama di layouts/app.blade.php menyetel
     margin-left dengan !important juga (baris ~3047). */
  body.owner-layout .content {
    margin-left: 0 !important;
    padding: 12px !important;
    padding-top: 72px !important; /* topbar fixed setinggi 60px + jarak */
  }

  /* Sidebar yang ter-collapse dari preferensi desktop tak boleh menyempitkan
     drawer — di ponsel lebar drawer ditentukan blok di atas. */
  html.sidebar-collapsed body.owner-layout .sidebar-owner {
    width: 82%;
    max-width: 300px;
  }

  /* Judul topbar dipendekkan agar muat bersama hamburger. */
  .app-topbar-sub {
    display: none;
  }
}
```

- [ ] **Step 4: Sambungkan ke layout — link CSS**

Di `resources/views/layouts/app.blade.php`, tepat SETELAH baris 26 (`responsive.css`):

```blade
  <!-- Mobile Responsive CSS -->
  <link rel="stylesheet" href="{{ \App\Support\Asset::versioned('css/responsive.css') }}">

  <!-- Gaya khusus layar ponsel (semua aturan terkurung @media max-width: 768px) -->
  <link rel="stylesheet" href="{{ \App\Support\Asset::versioned('css/mobile.css') }}">
```

Urutan penting: `mobile.css` SESUDAH `responsive.css` agar menang saat spesifisitas seri.

- [ ] **Step 5: Sambungkan ke layout — elemen scrim**

Tepat setelah `</header>` topbar (baris 3317), masih di dalam `@if(!($isOperatorSpreadsheet ?? false))`:

```blade
  </header>

  {{-- Latar gelap drawer ponsel. Selalu ada di DOM; disembunyikan di desktop
       oleh CSS (display:none default di bawah), ditampilkan hanya di ≤768px. --}}
  <div class="mobile-drawer-scrim" data-drawer-scrim hidden></div>
  @endif
```

Atribut `hidden` membuatnya tak tampil di desktop tanpa perlu satu pun aturan CSS di luar
`@media` (blok A sudah menyetel `display:block` di dalam media query, yang menimpa `hidden`).

- [ ] **Step 6: Sambungkan ke layout — cabang lebar layar di handler hamburger**

Ganti handler pada baris 3838-3851. Yang lama:

```javascript
      sidebarToggleButtons.forEach(function(button) {
        button.addEventListener('click', function() {
          const shouldCollapse = !document.documentElement.classList.contains('sidebar-collapsed');
          document.documentElement.classList.toggle('sidebar-collapsed', shouldCollapse);

          try {
            localStorage.setItem(sidebarStorageKey, shouldCollapse ? '1' : '0');
          } catch (error) {
            // Sidebar tetap berjalan meski browser menolak localStorage.
          }

          syncSidebarToggleState();
        });
      });
```

Jadi:

```javascript
      // Di ponsel tombol yang sama menggerakkan DRAWER, bukan menyempitkan sidebar.
      // Sengaja tidak menulis localStorage: drawer selalu mulai tertutup tiap
      // kunjungan, dan preferensi 'sidebar_collapsed' milik desktop tidak boleh
      // tertimpa oleh sentuhan di ponsel (user produksi sudah punya preferensi).
      const kueriPonsel = window.matchMedia('(max-width: 768px)');

      function tutupDrawerPonsel() {
        document.documentElement.classList.remove('mobile-drawer-open');
      }

      sidebarToggleButtons.forEach(function(button) {
        button.addEventListener('click', function() {
          if (kueriPonsel.matches) {
            document.documentElement.classList.toggle('mobile-drawer-open');
            return;
          }

          const shouldCollapse = !document.documentElement.classList.contains('sidebar-collapsed');
          document.documentElement.classList.toggle('sidebar-collapsed', shouldCollapse);

          try {
            localStorage.setItem(sidebarStorageKey, shouldCollapse ? '1' : '0');
          } catch (error) {
            // Sidebar tetap berjalan meski browser menolak localStorage.
          }

          syncSidebarToggleState();
        });
      });

      // Tap latar gelap menutup drawer.
      document.querySelectorAll('[data-drawer-scrim]').forEach(function(scrim) {
        scrim.addEventListener('click', tutupDrawerPonsel);
      });

      // Drawer tak boleh tertinggal terbuka saat layar diputar/diperbesar ke desktop.
      kueriPonsel.addEventListener('change', function(event) {
        if (!event.matches) {
          tutupDrawerPonsel();
        }
      });
```

- [ ] **Step 7: Jalankan test, pastikan LULUS**

```
php artisan test --filter=MobileBagianTest
```

Harapan: PASS, 4 test.

- [ ] **Step 8: Buktikan test menggigit**

Tambahkan aturan di LUAR `@media` pada `mobile.css`, mis. `.uji-palsu { color: red; }` di
baris paling bawah → jalankan test → `test_seluruh_aturan_mobile_terkurung_media_query`
**wajib GAGAL**. Hapus lagi. Lalu hapus baris `<link>` mobile.css dari layout → jalankan →
`test_mobile_css_ter_link_di_layout` **wajib GAGAL**. Pulihkan. Lalu:

```bash
git diff public/css/mobile.css resources/views/layouts/app.blade.php
```

Harapan: hanya perubahan yang memang diniatkan — nol sisa mutasi.

- [ ] **Step 9: Commit**

```bash
git add public/css/mobile.css
git add tests/Feature/MobileBagianTest.php
git add resources/views/layouts/app.blade.php
git commit -m "feat(mobile): drawer geser + shell ponsel untuk semua role"
```

---

## Task 3: Kartu dokumen untuk ponsel

**Files:**
- Create: `resources/views/bagian/partials/_kartuDokumenMobile.blade.php`
- Modify: `public/css/mobile.css` (tambah Blok B)
- Modify: `resources/views/bagian/dokumens/daftarDokumen.blade.php` (@include setelah
  `</table>`/`.table-wrapper`, ~baris 1510)
- Modify: `tests/Feature/MobileBagianTest.php` (tambah test)

**Interfaces:**
- Consumes: `App\Support\StatusPembayaranBagian::untuk()` (Task 1); variabel view
  `$dokumens` (paginator) & `$perjalanan` (array ber-key id dokumen); fungsi global
  `tampilkanPerjalanan(el)` dan `showRejectionModal(id)` yang sudah ada di halaman.
- Produces: pembungkus `.mob-cards` + kartu `.mob-card`.

**Kontrak yang sudah diverifikasi ke kode (jangan dikarang ulang):**
- `tampilkanPerjalanan(tombol)` membaca atribut **`data-perjalanan`** berisi JSON dari
  elemen yang dioper (`daftarDokumen.blade.php:2860-2908`).
- `showRejectionModal(dokumenId)` menerima **ID dokumen**, lalu memanggil
  `/api/bagian/documents/{id}/return-detail` (baris 2910+).
- Kedua modal (`#perjalananModal`, `#rejectionDetailModal`) sudah ada di DOM halaman ini.
  **Nol fungsi JS baru.**

- [ ] **Step 1: Tulis test yang gagal**

Tambahkan ke `tests/Feature/MobileBagianTest.php`. Berkas ini sekarang perlu DB, jadi
tambahkan `use` dan trait di atas kelas:

```php
use App\Models\Dokumen;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
```

Tambahkan `use RefreshDatabase;` di dalam kelas, lalu:

```php
    protected function setUp(): void
    {
        parent::setUp();

        // Query daftar dokumen Bagian memakai SUBSTRING_INDEX (fungsi MySQL) yang
        // tak ada di SQLite. Polyfill sama dengan PerjalananDokumenBagianTest.
        $pdo = DB::connection()->getPdo();
        if ($pdo->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $pdo->sqliteCreateFunction('substring_index', function ($str, $delim, $count) {
                $parts = explode($delim, (string) $str);

                return implode($delim, array_slice($parts, 0, (int) $count));
            });
        }
    }

    private function userBagian(string $kode = 'AKN'): User
    {
        // CheckBagianRole menuntut role BERAWALAN 'bagian_' DAN bagian_code terisi.
        return User::factory()->create([
            'role'        => 'bagian_' . strtolower($kode),
            'bagian_code' => $kode,
        ]);
    }

    public function test_kartu_mobile_terender_sebanyak_baris_tabel(): void
    {
        Dokumen::create([
            'nomor_agenda'    => 'MOB001_2026',
            'nomor_spp'       => 'SPP-MOB-1',
            'bulan'           => 'Agustus',
            'tahun'           => 2026,
            'tanggal_masuk'   => '2026-08-01',
            'status'          => 'sedang diproses',
            'created_by'      => 'operator',
            'current_handler' => 'team_verifikasi',
            'bagian'          => 'AKN',
            'nilai_rupiah'    => 42500000,
            'dibayar_kepada'  => 'PT Sumber Makmur',
        ]);

        $html = $this->actingAs($this->userBagian())
            ->get('/bagian/documents')
            ->assertOk()
            ->getContent();

        // Satu dokumen → satu kartu.
        $this->assertSame(1, substr_count($html, 'class="mob-card"'));
        // Kartu memuat data yang sama dengan tabel.
        $this->assertStringContainsString('MOB001_2026', $html);
        $this->assertStringContainsString('PT Sumber Makmur', $html);
        // Nilai diformat gaya Indonesia.
        $this->assertStringContainsString('42.500.000', $html);
        // Badge status ikut dirender di kartu (dari helper Task 1).
        $this->assertStringContainsString('Belum Siap Dibayar', $html);
    }

    public function test_kartu_memakai_fungsi_modal_yang_sudah_ada(): void
    {
        Dokumen::create([
            'nomor_agenda'    => 'MOB002_2026',
            'bulan'           => 'Agustus',
            'tahun'           => 2026,
            'tanggal_masuk'   => '2026-08-01',
            'status'          => 'sedang diproses',
            'created_by'      => 'operator',
            'current_handler' => 'team_verifikasi',
            'bagian'          => 'AKN',
        ]);

        $html = $this->actingAs($this->userBagian())
            ->get('/bagian/documents')
            ->assertOk()
            ->getContent();

        // Kartu memanggil fungsi perjalanan yang SUDAH ADA, dengan membawa
        // atribut data-perjalanan (kontrak fungsi tersebut).
        $posisiKartu = strpos($html, 'class="mob-card"');
        $this->assertNotFalse($posisiKartu, 'Kartu mobile tidak ditemukan.');

        $potonganKartu = substr($html, $posisiKartu, 2000);
        $this->assertStringContainsString('tampilkanPerjalanan(this)', $potonganKartu);
        $this->assertStringContainsString('data-perjalanan', $potonganKartu);
    }

    public function test_pembungkus_kartu_hanya_tampil_di_ponsel(): void
    {
        $css = $this->mobileCss();
        $layout = file_get_contents(resource_path('views/layouts/app.blade.php'));

        // Pembungkus disembunyikan di desktop. Aturan display:none itu TIDAK boleh
        // ada di mobile.css (berkas itu hanya berisi @media) — ia dibawa partial
        // sendiri lewat @push('styles').
        $partial = file_get_contents(
            resource_path('views/bagian/partials/_kartuDokumenMobile.blade.php')
        );

        $this->assertStringContainsString("@push('styles')", $partial);
        $this->assertStringContainsString('.mob-cards', $partial);
        $this->assertStringContainsString('display: none', $partial);

        // Di mobile.css pembungkus DITAMPILKAN kembali (di dalam @media).
        $this->assertStringContainsString('.mob-cards', $css);
    }

    public function test_css_kartu_dipush_sebelum_markup(): void
    {
        // Regresi flash-of-unstyled: kalau CSS display:none ter-parse SETELAH
        // markup, kartu berkedip muncul di desktop sebelum disembunyikan.
        // Pelajaran dari program modal kustomisasi kolom (CLAUDE.md §7).
        Dokumen::create([
            'nomor_agenda'    => 'MOB003_2026',
            'bulan'           => 'Agustus',
            'tahun'           => 2026,
            'tanggal_masuk'   => '2026-08-01',
            'status'          => 'sedang diproses',
            'created_by'      => 'operator',
            'current_handler' => 'team_verifikasi',
            'bagian'          => 'AKN',
        ]);

        // Urutan yang diuji: blok <style> dari @push('styles') (berisi
        // ".mob-cards { display: none; }") WAJIB muncul sebelum markup kartu.
        // Dicari string persis milik blok itu — bukan sekadar ".mob-cards",
        // yang juga muncul di <link> mobile.css sehingga assertion jadi hampa.
        $this->actingAs($this->userBagian())
            ->get('/bagian/documents')
            ->assertOk()
            ->assertSeeInOrder(['.mob-cards { display: none; }', 'class="mob-card"'], false);
    }
```

- [ ] **Step 2: Jalankan test, pastikan GAGAL**

```
php artisan test --filter=MobileBagianTest
```

Harapan: FAIL — kartu belum ada (`substr_count` mengembalikan 0), dan partial belum ada.

- [ ] **Step 3: Buat partial kartu**

Buat `resources/views/bagian/partials/_kartuDokumenMobile.blade.php`:

```blade
{{-- Kartu dokumen untuk layar ponsel (role Bagian).

     Dirender BERDAMPINGAN dengan tabel: tabel tampil di desktop, kartu tampil
     di ponsel. Keduanya independen — mengubah kartu tak menyentuh tabel.

     Kenapa bukan mengubah <table> jadi kartu lewat CSS: tabel Bagian punya 11
     kolom, sel ber-onclick, dan navigasi keyboard (_activeCellNav) yang memasang
     cache baris pada tbody. Memaksa display:block di sana rapuh.

     NOL fungsi JS baru — kartu memanggil tampilkanPerjalanan() dan
     showRejectionModal() yang sudah ada di daftarDokumen.blade.php.

     Butuh: $dokumens (paginator), $perjalanan (array ber-key id dokumen).
--}}

@push('styles')
<style>
  /* Disembunyikan secara default (desktop). Ditampilkan kembali di
     public/css/mobile.css di dalam @media (max-width: 768px).
     WAJIB lewat @push('styles') — bukan <style> inline di body — supaya
     ter-parse SEBELUM markup dan kartu tak berkedip muncul di desktop. */
  .mob-cards { display: none; }
</style>
@endpush

<div class="mob-cards">
  @foreach($dokumens as $doc)
    @php
      $status = \App\Support\StatusPembayaranBagian::untuk($doc);
      $jalan = $perjalanan[$doc->id] ?? null;
      $dikembalikan = strtolower($doc->status ?? '') === 'returned_to_bidang';
    @endphp

    <div class="mob-card"
      @if($jalan)
        data-perjalanan="{{ json_encode($jalan) }}"
        onclick="tampilkanPerjalanan(this)"
        role="button"
        tabindex="0"
        title="Ketuk untuk melihat perjalanan dokumen"
      @endif
    >
      <div class="mob-card__judul">
        <strong class="mob-card__agenda">{{ $doc->nomor_agenda }}</strong>
        @if($doc->nomor_spp)
          <span class="mob-card__spp">{{ $doc->nomor_spp }}</span>
        @endif
      </div>

      <div class="mob-card__penerima">
        {{ $doc->dibayar_kepada ?: ($doc->dibayarKepadas->pluck('nama_penerima')->join(', ') ?: '-') }}
      </div>

      <div class="mob-card__nilai">
        Rp {{ number_format($doc->nilai_rupiah ?? 0, 0, ',', '.') }}
      </div>

      <div class="mob-card__status">
        <span class="payment-status-badge {{ $status['kelas'] }}">
          <i class="fa-solid {{ $status['ikon'] }}"></i>
          {{ $status['teks'] }}
        </span>
      </div>

      @if($dikembalikan)
        {{-- stopPropagation: tanpa ini ketukan badge ikut membuka modal
             perjalanan milik kartu induknya. --}}
        <div class="mob-card__pengembalian">
          <span class="badge-status badge-dikembalikan"
            onclick="event.stopPropagation(); showRejectionModal({{ $doc->id }})">
            <i class="fa-solid fa-undo"></i>
            Dikembalikan, <span style="text-decoration: underline; font-weight: 700;">Alasan</span>
          </span>
        </div>
      @endif

      <div class="mob-card__kaki">
        <span>
          <i class="fa-solid fa-calendar-day"></i>
          {{ $doc->tanggal_masuk ? $doc->tanggal_masuk->format('d M Y') : '-' }}
        </span>
        @if($jalan)
          <span class="mob-card__detail">Detail &rsaquo;</span>
        @endif
      </div>
    </div>
  @endforeach
</div>
```

- [ ] **Step 4: Tambahkan Blok B ke `public/css/mobile.css`**

Di DALAM blok `@media (max-width: 768px)` yang sudah ada (sebelum kurung tutupnya):

```css
  /* ===== Blok B — .bagian-layout: kartu dokumen ===== */

  /* Tabel disembunyikan, kartu ditampilkan. Inilah yang menghapus gulir
     horizontal 1943px itu. */
  body.bagian-layout .table-wrapper {
    display: none;
  }

  body.bagian-layout .mob-cards {
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  .mob-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 14px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
    cursor: pointer;
  }

  .mob-card__judul {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    gap: 8px;
    margin-bottom: 6px;
  }

  .mob-card__agenda {
    font-size: 15px;
    color: #0f172a;
  }

  .mob-card__spp {
    font-size: 12px;
    color: #64748b;
    white-space: nowrap;
  }

  .mob-card__penerima {
    font-size: 14px;
    color: #334155;
    margin-bottom: 4px;
  }

  .mob-card__nilai {
    font-size: 16px;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 8px;
  }

  .mob-card__status,
  .mob-card__pengembalian {
    margin-bottom: 8px;
  }

  .mob-card__kaki {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 12px;
    color: #64748b;
    border-top: 1px solid #f1f5f9;
    padding-top: 8px;
  }

  .mob-card__detail {
    color: #2563eb;
    font-weight: 600;
  }
```

- [ ] **Step 5: Sisipkan `@include` di view Bagian**

Di `resources/views/bagian/dokumens/daftarDokumen.blade.php`, tepat SETELAH penutup
`</div>` milik `.table-wrapper` (~baris 1510) dan SEBELUM `<!-- Pagination -->`:

```blade
        </div>

        {{-- Kartu dokumen untuk ponsel — tampil hanya di ≤768px, menggantikan
             tabel yang butuh gulir horizontal 1943px. --}}
        @include('bagian.partials._kartuDokumenMobile')

        <!-- Pagination -->
```

Letaknya di dalam `@if($dokumens->count() > 0)` agar kondisi "belum ada dokumen" tetap
memakai tampilan kosong yang sudah ada.

- [ ] **Step 6: Jalankan test, pastikan LULUS**

```
php artisan test --filter=MobileBagianTest
```

Harapan: PASS, 8 test.

- [ ] **Step 7: Buktikan test menggigit**

Ubah `class="mob-card"` jadi `class="mob-kartu"` di partial → jalankan → test pertama
**wajib GAGAL**. Pulihkan. Hapus blok `@push('styles')` dari partial → jalankan →
`test_pembungkus_kartu_hanya_tampil_di_ponsel` **wajib GAGAL**. Pulihkan. Lalu:

```bash
git diff resources/views/bagian/partials/_kartuDokumenMobile.blade.php
```

Harapan: kosong.

- [ ] **Step 8: Commit**

```bash
git add resources/views/bagian/partials/_kartuDokumenMobile.blade.php
git add public/css/mobile.css
git add resources/views/bagian/dokumens/daftarDokumen.blade.php
git add tests/Feature/MobileBagianTest.php
git commit -m "feat(bagian): kartu dokumen ponsel menggantikan tabel bergulir"
```

---

## Task 4: Filter, toolbar, dan paginasi ramah sentuh

**Files:**
- Modify: `public/css/mobile.css` (tambah ke Blok B)
- Modify: `tests/Feature/MobileBagianTest.php`

**Interfaces:**
- Consumes: kelas `.search-filter-form`, `.search-input-group`, `.btn-year-select`,
  `.btn-month-select`, `.btn-status-select`, `.btn-refresh`, `.pagination-container`
  (semuanya sudah ada di `daftarDokumen.blade.php`).
- Produces: (tidak ada — task terakhir, murni CSS)

- [ ] **Step 1: Tulis test yang gagal**

Tambahkan ke `tests/Feature/MobileBagianTest.php`:

```php
    public function test_input_filter_mencegah_zoom_ios(): void
    {
        $css = $this->mobileCss();

        // font-size minimal 16px pada input mencegah iOS auto-zoom saat field
        // difokus — penyebab keluhan "layarnya meloncat sendiri".
        $this->assertStringContainsString('font-size: 16px', $css);
        $this->assertStringContainsString('.search-filter-form', $css);
    }

    public function test_target_sentuh_minimal_44px(): void
    {
        $css = $this->mobileCss();

        // 44px = ambang target sentuh Apple HIG.
        $this->assertStringContainsString('min-height: 44px', $css);
    }
```

- [ ] **Step 2: Jalankan test, pastikan GAGAL**

```
php artisan test --filter=MobileBagianTest
```

Harapan: FAIL pada dua test baru.

- [ ] **Step 3: Tambahkan aturan ke Blok B**

Masih di DALAM `@media (max-width: 768px)`:

```css
  /* ===== Blok B — .bagian-layout: filter, toolbar, paginasi ===== */

  body.bagian-layout .search-filter-form {
    display: flex;
    flex-direction: column;
    gap: 10px;
    width: 100%;
  }

  body.bagian-layout .search-input-group,
  body.bagian-layout .search-filter-form .input-group {
    width: 100%;
  }

  /* font-size 16px WAJIB: di bawah itu iOS otomatis memperbesar halaman saat
     field difokus, dan user tak bisa mengembalikannya. */
  body.bagian-layout .search-filter-form input,
  body.bagian-layout .search-filter-form select {
    width: 100%;
    font-size: 16px;
    min-height: 44px; /* ambang target sentuh Apple HIG */
  }

  /* Dua tombol toolbar berdampingan, berbagi lebar sama rata. */
  body.bagian-layout .btn-refresh {
    flex: 1 1 0;
    min-height: 44px;
    justify-content: center;
  }

  body.bagian-layout .pagination-container {
    flex-direction: column;
    gap: 12px;
    align-items: stretch;
  }

  body.bagian-layout .pagination-container .page-link {
    min-height: 44px;
    min-width: 44px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }

  /* Panel notifikasi pengembalian: beri ruang baca, jangan dipepet tepi layar. */
  body.bagian-layout .notif-pengembalian__head {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
```

**Catatan:** tombol "Uji Kirim Pesan" memakai kelas `.btn-refresh` yang sama, jadi ia ikut
tertata tanpa aturan tambahan. Tombol itu berstatus SEMENTARA (CLAUDE.md §7) — saat dicabut,
tak ada CSS yatim yang tertinggal.

- [ ] **Step 4: Jalankan test, pastikan LULUS**

```
php artisan test --filter=MobileBagianTest
```

Harapan: PASS, 10 test.

- [ ] **Step 5: Buktikan test menggigit**

Ubah `font-size: 16px` jadi `font-size: 14px` → jalankan → `test_input_filter_mencegah_zoom_ios`
**wajib GAGAL**. Pulihkan. Lalu `git diff public/css/mobile.css` → harapan: hanya penambahan
yang diniatkan.

- [ ] **Step 6: Jalankan SELURUH suite sebelum push**

```
php artisan test
```

Harapan: hijau semua. Ini gerbang wajib CLAUDE.md §3 — suite penuh sekali sebelum
push/deploy, bukan tiap commit.

- [ ] **Step 7: Commit**

```bash
git add public/css/mobile.css
git add tests/Feature/MobileBagianTest.php
git commit -m "feat(bagian): filter & paginasi ramah sentuh di ponsel"
```

---

## Task 5: QA browser di produksi (WAJIB — suite hijau bukan bukti)

CLAUDE.md §3.9 & §6: apa pun yang dirender di browser wajib diverifikasi di browser.
Cacat `localStorage` yang membatalkan satu fitur penuh pernah lolos dari 284 test hijau.

**Files:** (tidak ada perubahan kode; task ini menghasilkan bukti)

- [ ] **Step 1: Deploy ke server**

```bash
git push origin codinggemini
```

Di server (detail di `deploy_to_server.bat`):

```bash
git pull
php artisan route:clear && php artisan view:clear && php artisan config:clear
```

**Clear cache tidak boleh dilewat** — Blade ter-cache membuat perubahan tampak tak berefek.

- [ ] **Step 2: QA di 375px (iPhone standar)**

Login `skh` / `12345678` ke `http://163.61.58.92/bagian/documents` (memori
`browser-qa-access`). Verifikasi mekanis lewat konsol:

```javascript
({
  viewport: document.documentElement.clientWidth,
  scrollWidth: document.documentElement.scrollWidth,
  kartu: document.querySelectorAll('.mob-card').length,
  tabelTampil: getComputedStyle(document.querySelector('.table-wrapper')).display,
})
```

Harapan: `scrollWidth === viewport` (nol gulir horizontal), `kartu > 0`,
`tabelTampil === 'none'`.

- [ ] **Step 3: QA interaksi**

- Ketuk hamburger → drawer menggeser masuk, latar menggelap.
- Ketuk latar gelap → drawer menutup.
- Ketuk kartu → modal perjalanan terbuka dengan tahapan benar.
- Ketuk badge "Dikembalikan, Alasan" (cari dokumen berstatus itu) → modal alasan terbuka,
  **dan modal perjalanan TIDAK ikut terbuka** (bukti `stopPropagation` bekerja).
- Fokuskan kotak cari → halaman **tidak** ikut membesar (bukti `font-size: 16px`).

- [ ] **Step 4: QA di 768px (batas breakpoint)**

Ambang `max-width: 768px` bersifat inklusif — pada tepat 768px aturan mobile MASIH aktif.
Verifikasi tidak ada tampilan setengah jadi (kartu tampil, tabel tersembunyi).

- [ ] **Step 5: QA desktop 1440px — bukti nol regresi**

Verifikasi: tabel tampil normal 11 kolom, kartu **tidak** terlihat, sidebar seperti semula,
tombol hamburger tetap menyempitkan/melebarkan sidebar seperti sebelumnya, dan preferensi
collapse masih tersimpan setelah reload.

- [ ] **Step 6: Serahkan bukti ke user**

Kumpulkan screenshot sebelum/sesudah (375px & 1440px) dan hasil verifikasi mekanis.
**Keputusan lolos milik user, bukan agent.** Nyatakan terus terang apa yang diuji dan apa
yang tidak.

---

## Utang tercatat (jangan hilang)

- **Cabang mati `$col == 'status_pembayaran'`** di `daftarDokumen.blade.php:1351-1395` —
  tak pernah bisa dieksekusi (`status_pembayaran` bukan anggota `$availableColumns`).
  Penghapusannya butuh grep-gate + persetujuan user, dikerjakan terpisah.
- **`public/css/responsive.css`** kini terbukti mati (nol selektor menggigit halaman mana
  pun). Penghapusannya menuntut grep-gate lintas-role tersendiri.
- **Aturan CSS `.btn-refresh`** ikut tercabut saat tombol "Uji Kirim Pesan" dicabut
  (daftar pencabutan di docblock `App\Http\Controllers\UjiWhatsAppBagianController`).
- **Kartu & tabel dikirim bersamaan** dalam satu HTML (+15-20% ukuran halaman). Bila jadi
  masalah, pindahkan kartu ke endpoint terpisah.
- **Tahap 2 — 5 role Tabulator** (operator, verifikasi, perpajakan, akutansi, pembayaran):
  pola "markup kartu kedua" TIDAK berlaku langsung karena Tabulator merender tabelnya
  sendiri lewat JS. Kemungkinan butuh `responsiveLayout` bawaan Tabulator atau formatter
  baris khusus. Blok A `mobile.css` (shell/drawer) sudah berlaku untuk mereka.

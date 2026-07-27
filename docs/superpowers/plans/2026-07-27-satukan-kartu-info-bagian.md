# Satukan Kartu Informasi Bagian ke Kartu Keuangan — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Satukan kartu informasi role `bagian` dengan kartu informasi role keuangan lewat SATU partial bersama (`partials/_infoCards`), lalu hapus tuntas kode kartu lama (`.bic-*`).

**Architecture:** Ekstrak markup+CSS kartu keuangan `.wd-card` dari `dashboard/workflow.blade.php` menjadi partial bersama `resources/views/partials/_infoCards.blade.php` yang menerima array `$cards` (kontrak data-driven). `workflow.blade.php` (4 role keuangan) memakainya secara behavior-preserving; view bagian memakainya untuk menggantikan kartu bespoke `.bic-*` yang lalu dihapus. Klik-filter `?status=` + sorotan kartu aktif bagian dipertahankan lewat field `href`/`active`.

**Tech Stack:** Laravel 12 Blade, PHPUnit (`Tests\TestCase`), CSS inline (pola view sekarang; `@vite` mati). QA visual via Playwright MCP.

## Global Constraints

- Bahasa: UI/komentar Indonesia, identifier English. Pesan commit Bahasa Indonesia.
- `git add` per-file — JANGAN `git add .` / `git add -A`.
- Satu commit = satu perubahan logis.
- Refaktor keuangan **behavior-preserving**: output visual `workflow` (pembayaran/akutansi/perpajakan/verifikasi) wajib IDENTIK sebelum/sesudah.
- Jangan tambah utang CSS baru: perubahan ini **memindah** CSS (net berkurang karena `.bic-*` dihapus), bukan menambah.
- `workflow.blade.php` = gerbang kritis lintas-4-role (§6 CLAUDE.md) — scope sudah disetujui user; wajib re-QA browser pasca-ubah.
- Kontrak data controller TIDAK disentuh (partial hanya mengonsumsi variabel yang sudah dikirim).
- `php artisan test` wajib hijau sebelum tiap commit.

**Sumber kebenaran desain:** `docs/superpowers/specs/2026-07-27-satukan-kartu-info-bagian-design.md`.

---

## File Structure

- **Create** `resources/views/partials/_infoCards.blade.php` — partial bersama: container `.wd-cards` + N `.wd-card` dari array `$cards`; membawa CSS `.wd-card*` (dipindah dari workflow) + tambahan aditif `.wd-card--active`.
- **Create** `tests/Feature/InfoCardsPartialTest.php` — render test kontrak partial (tag `<a>`/`<div>` by `href`, kelas `.wd-card--active`, `number_format`, `valueColor` default vs override, kelas kolom).
- **Modify** `resources/views/dashboard/workflow.blade.php` — markup kartu 54-113 → build `$cards` + `@include`; hapus CSS `.wd-cards`/`.wd-card*`/`@keyframes wdFadeUp` (218-243) + bagian `.wd-cards` di media query (300-301) yang pindah ke partial.
- **Modify** `resources/views/bagian/dokumens/daftarDokumen.blade.php` — markup `.bagian-info-cards` (1021-1047) → `@include`; HAPUS CSS `.bagian-info-cards`/`.bic-*` (961-1017).

---

## Task 1: Partial bersama `_infoCards` + render test

**Files:**
- Create: `resources/views/partials/_infoCards.blade.php`
- Test: `tests/Feature/InfoCardsPartialTest.php`

**Interfaces:**
- Consumes: (tak ada — task pertama)
- Produces: partial `partials._infoCards` dengan kontrak `@include('partials._infoCards', ['cards' => $cards])`. Tiap elemen `$cards`:
  - `label` string (wajib) — teks label kecil di atas
  - `value` int (wajib) — diformat `number_format($v, 0, ',', '.')`
  - `sub` string (wajib) — sub-teks kecil
  - `icon` string SVG (wajib) — dirender `{!! !!}`
  - `iconBg` string warna (wajib) — background badge ikon
  - `valueColor` string (opsional, default `#1a2340`) — warna angka
  - `href` string URL (opsional) — ada → `<a href>`; tidak ada → `<div>`
  - `active` bool (opsional) — true → tambah kelas `.wd-card--active`
  - Container mendapat kelas `wd-cards wd-cards--cols-{N}` dengan `N = count($cards)`.

- [ ] **Step 1: Tulis test yang gagal**

Buat `tests/Feature/InfoCardsPartialTest.php`:

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Menguji kontrak partial bersama partials._infoCards (dipakai kartu keuangan
 * dashboard.workflow DAN kartu informasi bagian). Partial murni presentasional,
 * tanpa akses DB — cukup render langsung dengan array $cards contoh.
 */
class InfoCardsPartialTest extends TestCase
{
    private function render(array $cards): string
    {
        $html = view('partials._infoCards', ['cards' => $cards])->render();

        // Buang blok <style> agar assertion penghitungan (substr_count) hanya
        // mengukur MARKUP kartu, bukan selector CSS yang bernama sama
        // (mis. .wd-card-value / .wd-card--active di dalam <style>).
        return preg_replace('/<style\b[^>]*>.*?<\/style>/s', '', $html);
    }

    public function test_href_jadi_anchor_tanpa_href_jadi_div(): void
    {
        $html = $this->render([
            ['label' => 'A', 'value' => 1000, 'sub' => 'sub a', 'icon' => '<svg id="ia"></svg>', 'iconBg' => '#f0f4ff', 'href' => '/go'],
            ['label' => 'B', 'value' => 5,    'sub' => 'sub b', 'icon' => '<svg id="ib"></svg>', 'iconBg' => '#ffffff'],
        ]);

        // Kartu 1 (ber-href) = <a href="/go">, kartu 2 tanpa href = <div> (nol anchor).
        $this->assertSame(1, substr_count($html, '<a '));
        $this->assertStringContainsString('href="/go"', $html);
        // Dua kartu terender.
        $this->assertSame(2, substr_count($html, 'wd-card-value'));
        // Jumlah kolom mengikuti jumlah kartu.
        $this->assertStringContainsString('wd-cards--cols-2', $html);
        // Angka diformat gaya Indonesia (titik ribuan).
        $this->assertStringContainsString('1.000', $html);
        // Ikon dirender mentah (tak ter-escape).
        $this->assertStringContainsString('<svg id="ia"></svg>', $html);
    }

    public function test_active_dan_value_color_default_vs_override(): void
    {
        $html = $this->render([
            ['label' => 'X', 'value' => 42, 'sub' => 's', 'icon' => '<svg></svg>', 'iconBg' => '#fff', 'href' => '/x', 'active' => true],
            ['label' => 'Y', 'value' => 7,  'sub' => 's', 'icon' => '<svg></svg>', 'iconBg' => '#fff', 'href' => '/y', 'valueColor' => '#10b981'],
        ]);

        // Kartu aktif dapat kelas sorotan; hanya satu kartu aktif.
        $this->assertSame(1, substr_count($html, 'wd-card--active'));
        // Kartu X pakai warna default; kartu Y override hijau.
        $this->assertStringContainsString('color:#1a2340', $html);
        $this->assertStringContainsString('color:#10b981', $html);
    }
}
```

- [ ] **Step 2: Jalankan test untuk memastikan gagal**

Run: `php artisan test --filter=InfoCardsPartialTest`
Expected: FAIL — `View [partials._infoCards] not found`.

- [ ] **Step 3: Buat partial (implementasi minimal)**

Buat `resources/views/partials/_infoCards.blade.php`:

```blade
{{--
  Kartu informasi bersama (stat card) — dipakai kartu keuangan (dashboard.workflow)
  DAN kartu informasi bagian. Data-driven lewat array $cards. Lihat spec
  docs/superpowers/specs/2026-07-27-satukan-kartu-info-bagian-design.md untuk kontrak.
--}}
@php
    /** @var array $cards */
    $cols = count($cards);
@endphp
<div class="wd-cards wd-cards--cols-{{ $cols }}">
    @foreach ($cards as $card)
        @php
            $hasHref    = ! empty($card['href']);
            $tag        = $hasHref ? 'a' : 'div';
            $valueColor = $card['valueColor'] ?? '#1a2340';
            $isActive   = ! empty($card['active']);
        @endphp
        <{{ $tag }} class="wd-card{{ $isActive ? ' wd-card--active' : '' }}"@if ($hasHref) href="{{ $card['href'] }}"@endif>
            <div class="wd-card-label">{{ $card['label'] }}</div>
            <div class="wd-card-icon" style="background:{{ $card['iconBg'] }}">{!! $card['icon'] !!}</div>
            <div class="wd-card-value" style="color:{{ $valueColor }}">{{ number_format($card['value'], 0, ',', '.') }}</div>
            <div class="wd-card-sub">{{ $card['sub'] }}</div>
        </{{ $tag }}>
    @endforeach
</div>

<style>
  /* Kartu informasi bersama — gaya "kartu informasi" mengikuti /owner/home (stat-card).
     Dipindah dari dashboard/workflow.blade.php agar dipakai lintas role (keuangan + bagian). */
  .wd-cards { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; margin-bottom: 20px; }
  .wd-cards--cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
  .wd-cards--cols-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
  .wd-card {
    position: relative; overflow: hidden;
    background: #fff; border: 1px solid #e8ecf4; border-radius: 14px;
    padding: 18px 18px 14px;
    box-shadow: 0 1px 3px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.05);
    text-decoration: none; color: #1a2340;
    transition: transform .2s, box-shadow .2s;
    animation: wdFadeUp .4s ease both;
  }
  .wd-card:hover { transform: translateY(-2px); box-shadow: 0 4px 20px rgba(0,0,0,.09); color: #1a2340; }
  .wd-card:nth-child(1) { animation-delay: .05s; }
  .wd-card:nth-child(2) { animation-delay: .1s; }
  .wd-card:nth-child(3) { animation-delay: .15s; }
  .wd-card:nth-child(4) { animation-delay: .2s; }
  /* Sorotan kartu aktif (dipakai bagian saat filter ?status= aktif). Aditif —
     kartu keuangan tak pernah mengirim active, jadi tak terpengaruh. */
  .wd-card--active { box-shadow: 0 0 0 2px #083E40, 0 1px 3px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.05); }

  .wd-card-label { font-size: 10.5px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em;
    color: #a0aec0; margin-bottom: 10px; padding-right: 44px; line-height: 1.3; }
  .wd-card-value { font-family: 'Sora', 'Plus Jakarta Sans', sans-serif; font-size: 26px; font-weight: 700;
    color: #1a2340; line-height: 1; margin-bottom: 6px; }
  .wd-card-sub { font-size: 11px; font-weight: 500; color: #a0aec0; }
  .wd-card-icon { position: absolute; right: 16px; top: 16px; width: 36px; height: 36px; border-radius: 9px;
    display: flex; align-items: center; justify-content: center; }
  .wd-card-icon svg { width: 18px; height: 18px; }

  @keyframes wdFadeUp { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }

  @media (max-width: 1100px) { .wd-cards { grid-template-columns: repeat(2, 1fr); } }
  @media (max-width: 560px)  { .wd-cards { grid-template-columns: 1fr; } }
</style>
```

- [ ] **Step 4: Jalankan test untuk memastikan lulus**

Run: `php artisan test --filter=InfoCardsPartialTest`
Expected: PASS (2 test).

- [ ] **Step 5: Commit**

```bash
git add resources/views/partials/_infoCards.blade.php
git add tests/Feature/InfoCardsPartialTest.php
git commit -m "feat(ui): partial kartu informasi bersama _infoCards + test kontrak"
```

---

## Task 2: `workflow.blade.php` (4 role keuangan) pakai partial — behavior-preserving

**Files:**
- Modify: `resources/views/dashboard/workflow.blade.php` (markup kartu 54-113; CSS 218-243; media query 300-301)

**Interfaces:**
- Consumes: partial `partials._infoCards` (Task 1). Variabel view yang SUDAH ada: `$totalDokumenAgenda`, `$card2Label/Value/Sub`, `$card3Label/Value/Sub/Color/IconBg`, `$fourthLabel/Count/Sub/Color/IconBg/IsReturn`, `$cfg['docRouteName']`.
- Produces: (tak ada konsumen hilir baru)

- [ ] **Step 1: Ganti markup kartu inline dengan build `$cards` + `@include`**

Di `resources/views/dashboard/workflow.blade.php`, blok `@php` (baris 38-53) DIPERTAHANKAN. Tambahkan build `$cards` **di akhir blok `@php` yang sama** (setelah baris 52 `$fourthIconBg = ...`, sebelum `@endphp` baris 53):

```php
    // Bangun array kartu untuk partial bersama _infoCards (output identik markup lama).
    $cards = [
      [
        'label'  => 'Total Dokumen Agenda',
        'value'  => $totalDokumenAgenda,
        'sub'    => 'seluruh dokumen sistem',
        'icon'   => '<svg viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14,2 14,8 20,8"/></svg>',
        'iconBg' => '#f0f4ff',
        'href'   => route($cfg['docRouteName']),
      ],
      [
        'label'  => 'Total Dokumen ' . $card2Label,
        'value'  => $card2Value,
        'sub'    => $card2Sub,
        'icon'   => '<svg viewBox="0 0 24 24" fill="none" stroke="#0891b2" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>',
        'iconBg' => '#ecfeff',
        'href'   => route($cfg['docRouteName']),
      ],
      [
        'label'      => 'Total Dokumen ' . $card3Label,
        'value'      => $card3Value,
        'sub'        => $card3Sub,
        'icon'       => '<svg viewBox="0 0 24 24" fill="none" stroke="' . $card3Color . '" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>',
        'iconBg'     => $card3IconBg,
        'valueColor' => $card3Color,
        'href'       => route($cfg['docRouteName'], ['status' => 'terkirim']),
      ],
      [
        'label'      => 'Total Dokumen ' . $fourthLabel,
        'value'      => $fourthCount,
        'sub'        => $fourthSub,
        'icon'       => $fourthIsReturn
          ? '<svg viewBox="0 0 24 24" fill="none" stroke="' . $fourthColor . '" stroke-width="2"><polyline points="9 14 4 9 9 4"/><path d="M20 20v-7a4 4 0 00-4-4H4"/></svg>'
          : '<svg viewBox="0 0 24 24" fill="none" stroke="' . $fourthColor . '" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',
        'iconBg'     => $fourthIconBg,
        'valueColor' => $fourthColor,
        // tanpa href → kartu ke-4 tetap <div> (sama seperti sekarang)
      ],
    ];
```

Lalu GANTI seluruh blok markup kartu (baris 54-113, dari `<div class="wd-cards">` sampai penutup `</div>` kartu ke-4 tepat sebelum komentar `{{-- ===== CHART AREA ===== --}}`) dengan satu baris:

```blade
  @include('partials._infoCards', ['cards' => $cards])
```

> Catatan behavior-preserving: kartu 1 & 2 dulu tanpa atribut `style="color:..."` pada `.wd-card-value`; partial kini selalu mengeluarkan `style="color:#1a2340"`. `.wd-card-value` CSS memang sudah `color:#1a2340` → warna komputasi identik (nol perubahan visual). Ikon/iconBg/href/valueColor kartu 3 & 4 dipetakan persis dari variabel lama.

- [ ] **Step 2: Pindahkan CSS kartu ke partial (hapus dari workflow)**

Di blok `<style>` workflow, HAPUS baris 217-243 — dari komentar `/* Cards — gaya ... */` sampai baris `@keyframes wdFadeUp { ... }` (inklusif). Yaitu buang seluruh:

```css
  /* Cards — gaya "kartu informasi" mengikuti /owner/home (stat-card) */
  .wd-cards { ... }
  .wd-card { ... }
  .wd-card:hover { ... }
  .wd-card:nth-child(1..4) { ... }
  .wd-card-label { ... }
  .wd-card-value { ... }
  .wd-card-sub { ... }
  .wd-card-icon { ... }
  .wd-card-icon svg { ... }
  @keyframes wdFadeUp { ... }
```

(Semua sudah pindah ke partial `_infoCards`.) CSS lain (`.wd-wrap`, `.wd-charts`, `.wd-panel`, bubble, table, badge) TETAP.

- [ ] **Step 3: Pisahkan media query — buang bagian `.wd-cards`**

Baris 300 sekarang:

```css
  @media (max-width: 1100px) { .wd-charts { grid-template-columns: 1fr; } .wd-cards { grid-template-columns: repeat(2, 1fr); } }
  @media (max-width: 560px) { .wd-cards { grid-template-columns: 1fr; } }
```

Ganti menjadi (hapus aturan `.wd-cards`, sudah di partial; sisakan `.wd-charts`):

```css
  @media (max-width: 1100px) { .wd-charts { grid-template-columns: 1fr; } }
```

Baris `@media (max-width: 560px)` yang HANYA memuat `.wd-cards` DIHAPUS seluruhnya (tak ada selektor lain di dalamnya).

- [ ] **Step 4: Verifikasi kompilasi Blade + suite hijau**

Run: `php artisan view:clear` lalu `php artisan test`
Expected: nol error kompilasi Blade; SEMUA test hijau (baseline sebelumnya 245+). Tak ada test menargetkan markup kartu keuangan, jadi tak ada yang berubah statusnya.

- [ ] **Step 5: Commit**

```bash
git add resources/views/dashboard/workflow.blade.php
git commit -m "refactor(keuangan): kartu informasi workflow pakai partial _infoCards (behavior-preserving)"
```

---

## Task 3: `bagian/dokumens/daftarDokumen.blade.php` pakai partial + hapus `.bic-*`

**Files:**
- Modify: `resources/views/bagian/dokumens/daftarDokumen.blade.php` (markup 1021-1047; CSS 961-1017)

**Interfaces:**
- Consumes: partial `partials._infoCards` (Task 1). Variabel view yang SUDAH ada dari `BagianDokumenController@index`: `$totalDokumen`, `$totalBelumDibayar`, `$totalSudahDibayar`, `$bagianCode`. Route `bagian.documents.index` + query `?status=belum_dibayar|sudah_dibayar`.
- Produces: (tak ada konsumen hilir baru)

- [ ] **Step 1: Ganti markup `.bagian-info-cards` dengan build `$cards` + `@include`**

Di `resources/views/bagian/dokumens/daftarDokumen.blade.php`, GANTI blok markup baris 1021-1047 (komentar `<!-- Kartu Informasi -->` + seluruh `<div class="bagian-info-cards"> ... </div>`) dengan:

```blade
    {{-- Kartu Informasi (partial bersama _infoCards — gaya kartu keuangan) --}}
    @php
      $status = request('status');
      $cards = [
        [
          'label'  => 'Total Dokumen ' . $bagianCode,
          'value'  => $totalDokumen,
          'sub'    => 'seluruh dokumen bagian',
          'icon'   => '<svg viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14,2 14,8 20,8"/></svg>',
          'iconBg' => '#f0f4ff',
          'href'   => route('bagian.documents.index'),
          'active' => empty($status),
        ],
        [
          'label'      => 'Belum Dibayar',
          'value'      => $totalBelumDibayar,
          'sub'        => 'menunggu pembayaran',
          'icon'       => '<svg viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 14"/></svg>',
          'iconBg'     => '#fffbeb',
          'valueColor' => '#f59e0b',
          'href'       => route('bagian.documents.index', ['status' => 'belum_dibayar']),
          'active'     => $status === 'belum_dibayar',
        ],
        [
          'label'      => 'Sudah Dibayar',
          'value'      => $totalSudahDibayar,
          'sub'        => 'telah dibayar',
          'icon'       => '<svg viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>',
          'iconBg'     => '#ecfdf5',
          'valueColor' => '#10b981',
          'href'       => route('bagian.documents.index', ['status' => 'sudah_dibayar']),
          'active'     => $status === 'sudah_dibayar',
        ],
      ];
    @endphp
    @include('partials._infoCards', ['cards' => $cards])
```

> Pemetaan dari kartu lama: `bic-total`→kartu 1 (biru, tetap default warna angka gelap), `bic-belum`→kartu 2 (oranye), `bic-sudah`→kartu 3 (hijau). Ikon FontAwesome (`fa-folder-open`/`fa-hourglass-half`/`fa-circle-check`) → SVG setara gaya keuangan. Klik-filter `?status=` + sorotan aktif dipertahankan lewat `href`/`active`. `sub` adalah teks baru (kartu lama tak punya sub) agar identik tata letak kartu keuangan.

- [ ] **Step 2: Hapus CSS `.bagian-info-cards` / `.bic-*` (nol sisa)**

HAPUS seluruh blok CSS baris 961-1017 di dalam `<style>` bagian — dari komentar `/* ── Kartu informasi Bagian (terinspirasi kartu owner, dibuat sendiri) ── */` sampai (inklusif) blok:

```css
    @media (max-width: 900px) {
      .bagian-info-cards { grid-template-columns: 1fr; }
    }
```

Termasuk semua aturan `.bagian-info-cards`, `.bic-card`, `.bic-card::before`, `.bic-card:hover`, `.bic-card.bic-active`, `.bic-icon`, `.bic-value`, `.bic-label`, `.bic-total*`, `.bic-belum*`, `.bic-sudah*`, `.dark .bic-*`, dan media query 900px-nya. Responsif kini ditangani partial (breakpoint 1100px/560px).

> Catatan dark-mode (disetujui user): kartu lama punya `.dark .bic-*`; kartu keuangan TIDAK punya varian gelap. Sesuai keputusan user ("sama seperti kartu keuangan, putih"), kartu bagian kini **selalu putih**, termasuk saat tema gelap aktif. Ini konsekuensi yang sudah disetujui, bukan regresi tak sengaja.

- [ ] **Step 3: Grep-gate nol sisa `.bic-*`**

Run: `git grep -nE "bic-|bagian-info-cards" -- resources/`
Expected: **nol hasil**. Bila ada hasil, hapus sisa tersebut sebelum lanjut.

- [ ] **Step 4: Verifikasi kompilasi Blade + suite hijau**

Run: `php artisan view:clear` lalu `php artisan test`
Expected: nol error kompilasi Blade; SEMUA test hijau.

- [ ] **Step 5: Commit**

```bash
git add resources/views/bagian/dokumens/daftarDokumen.blade.php
git commit -m "refactor(bagian): kartu informasi pakai partial _infoCards + hapus tuntas .bic-*"
```

---

## Task 4: Verifikasi integrasi + QA visual (gerbang sebelum deploy)

**Files:** (tak ada perubahan kode — gerbang verifikasi)

**Interfaces:**
- Consumes: hasil Task 1-3.
- Produces: keputusan lolos/gagal QA untuk gerbang deploy user (§5/§6 CLAUDE.md).

- [ ] **Step 1: Suite penuh hijau**

Run: `php artisan test`
Expected: SEMUA test hijau (termasuk `InfoCardsPartialTest`).

- [ ] **Step 2: Grep-gate akhir**

Run: `git grep -nE "bic-|bagian-info-cards" -- resources/`
Expected: nol hasil (kode kartu lama benar-benar bersih).

- [ ] **Step 3: QA visual keuangan (Playwright MCP) — banding identik**

Login **pembayaran** (`pembayaran`/`12345678`) → `/dashboard/pembayaran` dan **verifikasi** (`12345678`) → dashboard verifikasi (keduanya render `dashboard.workflow`). Ambil screenshot 4 kartu; bandingkan dengan perilaku sebelum ubah: tata letak, warna angka (kartu 3 hijau, kartu 4 oranye/merah), ikon badge, hover, dan link tiap kartu wajib IDENTIK. akutansi/perpajakan memakai view yang sama (beda data) → paritas keyakinan-tinggi; nyatakan jujur bila kredensial tak tersedia.

- [ ] **Step 4: QA visual bagian (Playwright MCP) — fungsi klik-filter + sorotan**

Login akun bagian (minta kredensial saat QA) → `/bagian/documents`. Verifikasi: (a) 3 kartu tampil **putih** gaya keuangan; (b) klik "Belum Dibayar" memfilter tabel (`?status=belum_dibayar`) DAN kartu itu mendapat cincin sorotan `.wd-card--active`; sama untuk "Sudah Dibayar" dan "Total" (reset). Uji juga tema gelap bila ada toggle → kartu tetap putih (sesuai keputusan user).

- [ ] **Step 5: Serahkan ke user untuk gerbang deploy**

Laporkan hasil QA apa adanya (yang teruji vs yang belum bisa login). JANGAN deploy tanpa persetujuan user. Setelah disetujui (§5 CLAUDE.md):

```bash
git push origin codinggemini
# di server:
git pull
php artisan route:clear && php artisan view:clear && php artisan config:clear
```

---

## Self-Review

**1. Spec coverage:**
- Spec §2.1 partial bersama A → Task 1. ✓
- Spec §2.2 pertahankan klik-filter + sorotan aktif → Task 3 (`href`/`active`) + Task 4 Step 4. ✓
- Spec §2.3 visual putih identik keuangan + cincin aktif → Task 1 CSS (`.wd-card` putih + `.wd-card--active`). ✓
- Spec §3.1 kontrak partial → Task 1 Interfaces + implementasi. ✓
- Spec §3.2 workflow behavior-preserving (markup→array, CSS+media pindah) → Task 2. ✓
- Spec §3.3 bagian pakai partial + hapus bic-card → Task 3. ✓
- Spec §4 controller/route tak disentuh → dijaga (Global Constraints; partial hanya konsumsi var). ✓
- Spec §5 rencana uji (suite + Playwright keuangan/bagian + grep-gate) → Task 4. ✓

**2. Placeholder scan:** Nol "TBD/TODO/handle edge cases". Semua langkah kode memuat kode nyata (partial penuh, array kartu penuh, test penuh, blok CSS yang dihapus disebut eksplisit). ✓

**3. Type consistency:** Nama partial `partials._infoCards` konsisten di Task 1-3. Field `$cards` (`label/value/sub/icon/iconBg/valueColor/href/active`) sama persis di kontrak Task 1 dan pemakaian Task 2 & 3. Kelas `.wd-cards--cols-{N}` didefinisikan di partial (cols-3/cols-4) dan `N=count($cards)` menghasilkan 4 (keuangan) / 3 (bagian) — keduanya terdefinisi. ✓

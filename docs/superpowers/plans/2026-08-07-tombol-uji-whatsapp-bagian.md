# Tombol Uji Kiriman WhatsApp (Role Bagian) — Rencana Implementasi

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menambah satu panel di halaman role Bagian yang, setelah nomor WhatsApp
diketik dan tombol ditekan, mengirim satu pesan WhatsApp contoh "dokumen dikembalikan"
lewat gateway Fonnte dan melaporkan hasil sebenarnya.

**Architecture:** Panel Blade tersendiri → `POST /bagian/uji-whatsapp` (ber-throttle) →
controller khusus → template pesan **dipakai bersama** dengan
`DocumentReturnNotifier` (bukan disalin) → `FonnteWhatsAppService::sendMessage()` →
hasil asli diteruskan ke JSON.

**Tech Stack:** Laravel 12, PHP 8.2, Blade, `fetch` polos (tanpa Alpine/jQuery baru),
PHPUnit, Fonnte WhatsApp Gateway.

**Spec:** `docs/superpowers/specs/2026-08-07-tombol-uji-whatsapp-bagian-design.md`

## Global Constraints

- **Fitur ini SEMENTARA.** Tanpa saklar `.env` (keputusan user). Isolasi berkas adalah
  mitigasinya — tiap berkas baru wajib memuat penanda `SEMENTARA` di komentar teratas.
- **Jangan menyalin template pesan.** CLAUDE.md aturan 1. Pesan uji wajib memanggil
  method yang sama dengan pesan pengembalian sungguhan.
- **Jangan tambah CSS ber-`!important`.** CLAUDE.md aturan 4. Kelas ber-scope `.uwa-*`,
  ditaruh di `@push('styles')` (stack ada di `layouts/app.blade.php:3071`, sebelum
  `</head>` di baris 3122).
- **Bahasa:** UI & komentar Indonesia, identifier English (kecuali nama method domain
  yang sudah berbahasa Indonesia di kelas terkait, mis. `kirim`, `susunPesan`).
- **`git add` per-berkas.** JANGAN `git add .` / `git add -A`. Pesan commit Bahasa
  Indonesia.
- **Test terfilter saat iterasi** (`php artisan test --filter=NamaTest`). Suite penuh
  **sekali sebelum push**, bukan tiap commit (CLAUDE.md aturan 3 & 7).
- **Tiap assertion baru wajib dibuktikan menggigit** (CLAUDE.md aturan 8): rusakkan kode
  yang dijaga → test GAGAL → pulihkan → LULUS → `git diff <berkas>` kosong.
- **Akun Bagian di test** wajib `role` berawalan `bagian_` **dan** `bagian_code` terisi —
  `CheckBagianRole` menuntut keduanya. Contoh:
  `User::factory()->create(['role' => 'bagian_tan', 'bagian_code' => 'TAN'])`.

---

## Struktur Berkas

| Berkas | Tanggung jawab | Aksi |
|---|---|---|
| `app/Services/DocumentReturnNotifier.php` | Sumber tunggal pesan pengembalian. Bertambah satu pintu masuk untuk pesan uji. | Modifikasi |
| `app/Http/Controllers/UjiWhatsAppBagianController.php` | Menerima nomor, menyusun pesan uji, memanggil Fonnte, menerjemahkan hasil. | **Baru** |
| `routes/web.php` | Satu baris route di grup `['auth','bagian']` yang sudah ada. | Modifikasi |
| `resources/views/bagian/partials/_ujiWhatsApp.blade.php` | Markup + CSS + JS panel uji. Mandiri; menghapusnya menghapus seluruh UI-nya. | **Baru** |
| `resources/views/bagian/dokumens/daftarDokumen.blade.php` | Satu baris `@include`. | Modifikasi |
| `tests/Feature/UjiWhatsAppBagianTest.php` | Semua test fitur ini. | **Baru** |

---

## Task 1: Template pesan dipakai bersama

**Files:**
- Modify: `app/Services/DocumentReturnNotifier.php`
- Test: `tests/Feature/UjiWhatsAppBagianTest.php` (baru, berisi 1 test di task ini)

**Interfaces:**
- Consumes: —
- Produces:
  - `DocumentReturnNotifier::pesanUjiCoba(string $namaBagian, string $tautan): string`
  - `DocumentReturnNotifier::namaBagian(string $bagianCode): string` (dari `private` → `public`)
  - `DocumentReturnNotifier::susunPesan(string $agenda, string $namaBagian, string $alasan, string $tautan): string` (tetap `private`; tanda tangan berubah dari menerima `Dokumen`)

- [ ] **Step 1: Tulis test yang gagal**

Buat `tests/Feature/UjiWhatsAppBagianTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Services\DocumentReturnNotifier;
use Tests\TestCase;

/**
 * SEMENTARA — menguji tombol uji kiriman WhatsApp di halaman role Bagian.
 * Hapus seluruh berkas ini saat fitur uji coba dicabut (lihat daftar pencabutan
 * di docblock App\Http\Controllers\UjiWhatsAppBagianController).
 */
class UjiWhatsAppBagianTest extends TestCase
{
    public function test_pesan_uji_memakai_template_yang_sama_dengan_pesan_sungguhan(): void
    {
        // Pesan uji yang MENYIMPANG dari pesan sungguhan akan menipu responden
        // tanpa seorang pun menyadarinya. Karena itu bukan cuma "mengandung kata
        // yang mirip" — badannya wajib byte-per-byte hasil susunPesan().
        $tautan = 'http://contoh.test/bagian/documents';

        $uji = DocumentReturnNotifier::pesanUjiCoba('Tanaman', $tautan);

        $susunPesan = new \ReflectionMethod(DocumentReturnNotifier::class, 'susunPesan');
        $susunPesan->setAccessible(true);
        $badan = $susunPesan->invoke(
            null,
            '9999_2026',
            'Tanaman',
            'Lampiran faktur belum lengkap. (contoh)',
            $tautan
        );

        $this->assertStringEndsWith(
            $badan,
            $uji,
            'pesanUjiCoba() tidak berakhir dengan hasil susunPesan() — templatenya tersalin, bukan dipakai bersama.'
        );

        $this->assertStringStartsWith(
            '🧪',
            $uji,
            'Penanda uji coba hilang — responden bisa mengira ini pengembalian sungguhan.'
        );

        $this->assertStringContainsString('[UJI COBA', $uji);
    }
}
```

- [ ] **Step 2: Jalankan test, pastikan GAGAL**

Run: `php artisan test --filter=UjiWhatsAppBagianTest`
Expected: FAIL — `Call to undefined method App\Services\DocumentReturnNotifier::pesanUjiCoba()`

- [ ] **Step 3: Ubah tanda tangan `susunPesan()`**

Di `app/Services/DocumentReturnNotifier.php`, ganti method `susunPesan` yang lama:

```php
    private static function susunPesan(
        string $agenda,
        string $namaBagian,
        string $alasan,
        string $tautan
    ): string {
        return "🔔 *NOTIFIKASI SISTEM AGENDA ONLINE*\n\n"
            . "Dokumen dengan nomor agenda *{$agenda}* telah *dikembalikan* ke Bagian {$namaBagian}.\n\n"
            . "📋 *Alasan Pengembalian:*\n{$alasan}\n\n"
            . "Silakan lakukan perbaikan dan kirim ulang dokumen.\n\n"
            . "🔗 Lihat dokumen: {$tautan}";
    }
```

- [ ] **Step 4: Sesuaikan pemanggilnya di `kirim()`**

Di dalam `kirim()`, ganti baris:

```php
            $pesan = self::susunPesan($dokumen, $namaBagian, $alasan);
```

menjadi:

```php
            $pesan = self::susunPesan(
                $dokumen->nomor_agenda ?: 'N/A',
                $namaBagian,
                $alasan,
                url(route('inbox.show', $dokumen->id, false))
            );
```

Perilakunya identik — dua nilai itu dulu dihitung di dalam `susunPesan()`.

- [ ] **Step 5: Naikkan `namaBagian()` jadi publik**

Ganti `private static function namaBagian` menjadi `public static function namaBagian`.
Tambahkan satu kalimat di docblock-nya:

```php
    /**
     * Nama bagian dibaca dari tabel `bagians`, BUKAN peta kode→nama yang di-hardcode
     * seperti dulu di returnToBidang(). Peta hardcode itu ikut membusuk setiap kali
     * daftar bagian berubah.
     *
     * PUBLIK sejak 2026-08-07: dipakai juga UjiWhatsAppBagianController supaya panel
     * uji tidak melahirkan peta kode→nama ketiga.
     */
```

- [ ] **Step 6: Tambahkan konstanta + `pesanUjiCoba()`**

Tambahkan konstanta tepat di bawah deklarasi `class DocumentReturnNotifier`:

```php
    /**
     * SEMENTARA (2026-08-07) — penanda pesan uji coba. Hapus bersama pesanUjiCoba()
     * saat fitur uji coba dicabut.
     */
    private const PENANDA_UJI = "🧪 *[UJI COBA — BUKAN PENGEMBALIAN SUNGGUHAN]*\n\n";
```

Tambahkan method ini tepat di atas `susunPesan()`:

```php
    /**
     * SEMENTARA (2026-08-07) — pesan contoh untuk panel uji di halaman Bagian.
     *
     * Sengaja memanggil susunPesan() yang sama dengan jalur produksi, bukan menyalin
     * templatenya: pesan uji yang menyimpang dari pesan sungguhan akan menipu
     * responden uji coba tanpa ada yang menyadarinya.
     *
     * Nomor agenda 9999_2026 dipilih karena mustahil bertabrakan dengan dokumen nyata.
     *
     * Hapus method ini + konstanta PENANDA_UJI saat fitur uji coba dicabut.
     */
    public static function pesanUjiCoba(string $namaBagian, string $tautan): string
    {
        return self::PENANDA_UJI . self::susunPesan(
            '9999_2026',
            $namaBagian,
            'Lampiran faktur belum lengkap. (contoh)',
            $tautan
        );
    }
```

- [ ] **Step 7: Jalankan test baru, pastikan LULUS**

Run: `php artisan test --filter=UjiWhatsAppBagianTest`
Expected: PASS (1 test)

- [ ] **Step 8: Pastikan refaktor tidak merusak jalur produksi**

Run: `php artisan test --filter=NotifikasiPengembalianBagianTest`
Expected: PASS — semua test lama hijau. Ini jaring pengaman bahwa perubahan tanda
tangan `susunPesan()` benar-benar behavior-preserving.

Kalau ada yang merah: JANGAN ubah test lamanya. Perbaiki `kirim()` — argumennya salah
urutan atau salah nilai.

- [ ] **Step 9: Buktikan test menggigit**

Tiga mutasi, satu per satu (pulihkan sebelum lanjut ke berikutnya):

1. Di `pesanUjiCoba()`, ganti `self::PENANDA_UJI .` jadi `'' .` →
   `--filter=UjiWhatsAppBagianTest` harus **GAGAL** di assertion penanda.
2. Di `pesanUjiCoba()`, ganti alasan `'Lampiran faktur belum lengkap. (contoh)'` jadi
   `'lain'` → harus **GAGAL** di `assertStringEndsWith`.
3. Di `kirim()`, tukar urutan argumen `$namaBagian` dan `$alasan` →
   `--filter=NotifikasiPengembalianBagianTest` harus **GAGAL**.

Setelah ketiganya: pulihkan, jalankan ulang kedua filter (hijau), lalu pastikan
`git diff app/Services/DocumentReturnNotifier.php` hanya berisi perubahan yang memang
direncanakan — tidak ada sisa mutasi.

- [ ] **Step 10: Commit**

```bash
git add app/Services/DocumentReturnNotifier.php
git add tests/Feature/UjiWhatsAppBagianTest.php
git commit -m "refactor(notifikasi): susunPesan terima nilai biasa + pintu pesan uji coba

susunPesan() dulu menerima objek Dokumen sehingga hanya bisa dipakai jalur
pengembalian sungguhan. Diubah menerima agenda/nama bagian/alasan/tautan
supaya panel uji coba WhatsApp bisa memakai template yang SAMA, bukan
menyalinnya - pesan uji yang menyimpang dari pesan sungguhan akan menipu
responden tanpa ada yang menyadarinya.

namaBagian() dinaikkan jadi publik supaya panel uji tak melahirkan peta
kode->nama ketiga (sudah ada dua: di sini dan di BagianDokumenController).

pesanUjiCoba() + PENANDA_UJI ditandai SEMENTARA - dihapus saat fitur uji
coba dicabut."
```

---

## Task 2: Route + controller

**Files:**
- Create: `app/Http/Controllers/UjiWhatsAppBagianController.php`
- Modify: `routes/web.php` (grup `Route::middleware(['auth', 'bagian'])`, baris ~481-496)
- Test: `tests/Feature/UjiWhatsAppBagianTest.php` (tambah 4 test)

**Interfaces:**
- Consumes: `DocumentReturnNotifier::pesanUjiCoba()`, `DocumentReturnNotifier::namaBagian()` (Task 1)
- Produces: route bernama `bagian.uji-whatsapp` (POST), badan JSON `{ok: bool, pesan: string}`

- [ ] **Step 1: Tulis 4 test yang gagal**

Tambahkan `use` berikut di bagian atas `tests/Feature/UjiWhatsAppBagianTest.php`:

```php
use App\Models\User;
use App\Services\FonnteWhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
```

Tambahkan `use RefreshDatabase;` sebagai baris pertama di dalam kelas, lalu tambahkan
helper + 4 test ini:

```php
    private function userBagian(string $kode = 'TAN'): User
    {
        // CheckBagianRole menuntut role BERAWALAN 'bagian_' DAN bagian_code terisi.
        return User::factory()->create([
            'role'        => 'bagian_' . strtolower($kode),
            'bagian_code' => $kode,
        ]);
    }

    public function test_role_selain_bagian_ditolak(): void
    {
        $this->mock(FonnteWhatsAppService::class, function (MockInterface $m) {
            $m->shouldNotReceive('sendMessage');
        });

        $this->actingAs(User::factory()->create(['role' => 'team_verifikasi']))
            ->postJson(route('bagian.uji-whatsapp'), ['nomor_hp' => '081234567890'])
            ->assertForbidden();
    }

    public function test_nomor_tidak_sah_ditolak_dan_tidak_memanggil_gateway(): void
    {
        // Validasi harus menggigit SEBELUM kuota Fonnte terpakai.
        $this->mock(FonnteWhatsAppService::class, function (MockInterface $m) {
            $m->shouldNotReceive('sendMessage');
        });

        $this->actingAs($this->userBagian())
            ->postJson(route('bagian.uji-whatsapp'), ['nomor_hp' => '12345'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('nomor_hp');
    }

    public function test_nomor_sah_mengirim_pesan_berpenanda_uji_coba(): void
    {
        $this->mock(FonnteWhatsAppService::class, function (MockInterface $m) {
            $m->shouldReceive('sendMessage')
                ->once()
                ->withArgs(function (string $nomor, string $pesan) {
                    return $nomor === '081234567890'
                        && str_contains($pesan, '[UJI COBA')
                        && str_contains($pesan, '9999_2026')
                        // Nama bagian diambil dari akun yang login, bukan karangan.
                        && str_contains($pesan, 'Tanaman');
                })
                ->andReturn(['success' => true]);

            $m->shouldReceive('formatPhoneNumber')->andReturn('6281234567890');
        });

        \App\Models\Bagian::create(['kode' => 'TAN', 'nama' => 'Tanaman']);

        $this->actingAs($this->userBagian('TAN'))
            ->postJson(route('bagian.uji-whatsapp'), ['nomor_hp' => '081234567890'])
            ->assertOk()
            ->assertJson(['ok' => true]);
    }

    public function test_kegagalan_gateway_diteruskan_apa_adanya(): void
    {
        // Kalau tombol selalu bilang "terkirim", seluruh gunanya hilang — yang
        // sedang diuji justru apakah kiriman benar-benar sampai dari server.
        $this->mock(FonnteWhatsAppService::class, function (MockInterface $m) {
            $m->shouldReceive('sendMessage')->once()->andReturn([
                'success' => false,
                'reason'  => 'no_token',
                'message' => 'Fonnte API token not configured',
            ]);
        });

        \App\Models\Bagian::create(['kode' => 'TAN', 'nama' => 'Tanaman']);

        $response = $this->actingAs($this->userBagian('TAN'))
            ->postJson(route('bagian.uji-whatsapp'), ['nomor_hp' => '081234567890'])
            ->assertOk()
            ->assertJson(['ok' => false]);

        $this->assertStringContainsString(
            'Token',
            $response->json('pesan'),
            'Alasan gagal diseragamkan jadi pesan generik — user tak akan tahu apa yang harus diperbaiki.'
        );
    }
```

- [ ] **Step 2: Jalankan test, pastikan GAGAL**

Run: `php artisan test --filter=UjiWhatsAppBagianTest`
Expected: FAIL — `Route [bagian.uji-whatsapp] not defined.`

- [ ] **Step 3: Buat controller**

Buat `app/Http/Controllers/UjiWhatsAppBagianController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Services\DocumentReturnNotifier;
use App\Services\FonnteWhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * ============================ FITUR SEMENTARA ============================
 * Dibuat 2026-08-07 untuk sesi UJI COBA PENGGUNA role Bagian.
 *
 * Alasannya dua: (1) butir C4 kuesioner menanyakan lewat apa responden ingin
 * diberi tahu saat dokumennya dikembalikan — tak bisa dijawab berdasar kalau
 * mereka belum pernah melihat wujud pesannya; (2) nol dari delapan akun Bagian
 * punya phone_number terisi, sehingga cabang WhatsApp di DocumentReturnNotifier
 * belum pernah sekalipun berjalan di produksi — belum ada bukti Fonnte benar-
 * benar bisa mengirim dari server.
 *
 * DAFTAR PENCABUTAN (setelah sesi uji coba selesai):
 *   1. Hapus berkas ini
 *   2. Hapus resources/views/bagian/partials/_ujiWhatsApp.blade.php
 *   3. Hapus baris @include('bagian.partials._ujiWhatsApp') di
 *      resources/views/bagian/dokumens/daftarDokumen.blade.php
 *   4. Hapus route bernama 'bagian.uji-whatsapp' di routes/web.php
 *   5. Hapus tests/Feature/UjiWhatsAppBagianTest.php
 *   6. Di DocumentReturnNotifier: hapus pesanUjiCoba() + konstanta PENANDA_UJI;
 *      kembalikan namaBagian() ke private BILA tak ada pemakai lain.
 *      Tanda tangan susunPesan() yang menerima nilai biasa BOLEH dipertahankan —
 *      itu perbaikan yang berdiri sendiri.
 *
 * Cek bagian_code kosong SENGAJA tidak ada di sini: middleware CheckBagianRole
 * sudah menolaknya dengan 403 sebelum request sampai ke controller.
 * =========================================================================
 */
class UjiWhatsAppBagianController extends Controller
{
    public function kirim(Request $request, FonnteWhatsAppService $wa): JsonResponse
    {
        $data = $request->validate([
            'nomor_hp' => ['required', 'string', 'regex:/^(\+?62|0)8[0-9]{7,13}$/'],
        ], [
            'nomor_hp.required' => 'Nomor WhatsApp wajib diisi.',
            'nomor_hp.regex'    => 'Masukkan nomor WhatsApp yang sah, contoh 081234567890.',
        ]);

        $pesan = DocumentReturnNotifier::pesanUjiCoba(
            DocumentReturnNotifier::namaBagian(strtoupper(trim((string) Auth::user()->bagian_code))),
            // Tautan diarahkan ke halaman Bagian, BUKAN inbox dokumen contoh:
            // dokumen 9999_2026 tidak ada, dan responden yang menekan tautan mati
            // akan menyimpulkan "sistemnya rusak" — kesan pertama yang mahal.
            url(route('bagian.documents.index', [], false))
        );

        $hasil = $wa->sendMessage($data['nomor_hp'], $pesan);

        if (($hasil['success'] ?? false) === true) {
            return response()->json([
                'ok'    => true,
                'pesan' => 'Pesan terkirim ke ' . $wa->formatPhoneNumber($data['nomor_hp'])
                    . '. Silakan cek WhatsApp.',
            ]);
        }

        return response()->json([
            'ok'    => false,
            'pesan' => self::alasanTerbaca($hasil),
        ]);
    }

    /**
     * Menerjemahkan hasil FonnteWhatsAppService ke kalimat yang bisa ditindaklanjuti.
     * Sengaja TIDAK diseragamkan jadi "gagal": justru alasannya yang ingin diketahui.
     */
    private static function alasanTerbaca(array $hasil): string
    {
        $pesanApi = trim((string) ($hasil['message'] ?? ''));
        $ekor     = $pesanApi !== '' ? $pesanApi : 'tanpa keterangan';

        return match ($hasil['reason'] ?? '') {
            'disabled'  => 'Notifikasi WhatsApp sedang dimatikan di server (WHATSAPP_NOTIFICATIONS_ENABLED=false).',
            'no_token'  => 'Token Fonnte belum diisi di server (FONNTE_API_TOKEN).',
            'api_error' => 'Fonnte menolak kiriman: ' . $ekor,
            'exception' => 'Gagal menghubungi Fonnte: ' . $ekor,
            default     => 'Pengiriman gagal: ' . $ekor,
        };
    }
}
```

- [ ] **Step 4: Tambahkan route**

Di `routes/web.php`, di dalam grup `Route::middleware(['auth', 'bagian'])->group(...)`,
tepat SETELAH baris route `bagian.notifikasi.tandai-dibaca`, tambahkan:

```php

        // SEMENTARA (2026-08-07) — tombol uji kiriman WhatsApp untuk sesi uji coba
        // pengguna. throttle:5,1 bukan formalitas: tiap kiriman memotong kuota Fonnte
        // berbayar. Hapus bersama UjiWhatsAppBagianController (lihat docblock-nya).
        Route::post('/bagian/uji-whatsapp', [\App\Http\Controllers\UjiWhatsAppBagianController::class, 'kirim'])
            ->name('bagian.uji-whatsapp')
            ->middleware('throttle:5,1');
```

- [ ] **Step 5: Jalankan test, pastikan LULUS**

Run: `php artisan test --filter=UjiWhatsAppBagianTest`
Expected: PASS (5 test)

Kalau `test_role_selain_bagian_ditolak` malah dapat 302 alih-alih 403: pastikan test
memakai `postJson` (bukan `post`) — `CheckBagianRole` hanya mengembalikan 403 JSON bila
`$request->expectsJson()`.

- [ ] **Step 6: Buktikan test menggigit**

Empat mutasi, satu per satu (pulihkan sebelum lanjut):

1. Hapus `->middleware('throttle:5,1')` **dan** pindahkan route ke luar grup
   `['auth','bagian']` → `test_role_selain_bagian_ditolak` harus **GAGAL**.
2. Ganti aturan validasi `regex:...` jadi `string` saja →
   `test_nomor_tidak_sah_ditolak_dan_tidak_memanggil_gateway` harus **GAGAL**.
3. Di controller, ganti `DocumentReturnNotifier::pesanUjiCoba(...)` jadi
   `'halo'` → `test_nomor_sah_mengirim_pesan_berpenanda_uji_coba` harus **GAGAL**.
4. Di `alasanTerbaca()`, ganti seluruh `match` jadi `return 'Pengiriman gagal.';` →
   `test_kegagalan_gateway_diteruskan_apa_adanya` harus **GAGAL**.

Setelah keempatnya: pulihkan, jalankan ulang filter (hijau), lalu
`git diff app/Http/Controllers/UjiWhatsAppBagianController.php routes/web.php` — tak
boleh ada sisa mutasi.

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers/UjiWhatsAppBagianController.php
git add routes/web.php
git add tests/Feature/UjiWhatsAppBagianTest.php
git commit -m "feat(bagian): endpoint uji kiriman WhatsApp (SEMENTARA)

POST /bagian/uji-whatsapp di grup auth+bagian, throttle:5,1 karena tiap
kiriman memotong kuota Fonnte berbayar.

Hasil Fonnte diteruskan apa adanya (no_token/disabled/api_error/exception)
alih-alih diseragamkan jadi 'gagal' - yang sedang dibuktikan justru apakah
kiriman benar-benar sampai dari server produksi, jadi alasan gagalnya
adalah informasinya.

Tautan pesan mengarah ke halaman Bagian, bukan inbox dokumen contoh:
dokumen 9999_2026 tak ada, tautan mati akan dibaca responden sebagai
'sistemnya rusak'.

Controller berdiri sendiri (tidak ditempel ke BagianDokumenController)
supaya pencabutannya nanti = hapus berkas, bukan bedah kode hidup.
Daftar pencabutan ada di docblock-nya."
```

---

## Task 3: Panel UI di halaman Bagian

**Files:**
- Create: `resources/views/bagian/partials/_ujiWhatsApp.blade.php`
- Modify: `resources/views/bagian/dokumens/daftarDokumen.blade.php` (satu baris `@include`,
  tepat setelah `@include('partials._infoCards', ['cards' => $cards])` — cari baris itu,
  sekitar baris 1157)
- Test: `tests/Feature/UjiWhatsAppBagianTest.php` (tambah 1 test)

**Interfaces:**
- Consumes: route `bagian.uji-whatsapp` (Task 2), respons `{ok, pesan}`, helper
  `userBagian(string $kode = 'TAN'): User` yang sudah ada di berkas test sejak Task 2
- Produces: —

- [ ] **Step 1: Tulis test yang gagal**

Tambahkan test ini ke `tests/Feature/UjiWhatsAppBagianTest.php`:

```php
    public function test_panel_uji_tampil_di_halaman_bagian(): void
    {
        \App\Models\Bagian::create(['kode' => 'TAN', 'nama' => 'Tanaman']);

        $response = $this->actingAs($this->userBagian('TAN'))
            ->get(route('bagian.documents.index'))
            ->assertOk();

        $response->assertSee('Uji coba pemberitahuan WhatsApp');

        // CSS panel WAJIB berada di dalam <head>, artinya lewat @push('styles').
        // Kalau ia ditulis <style> polos di badan, panel sempat tampil telanjang
        // sebelum gayanya ter-parse — regresi flash-of-unstyled yang persis pernah
        // terjadi saat ekstraksi modal Kustomisasi Kolom.
        //
        // Membandingkan posisi terhadap </head> — BUKAN assertSeeInOrder terhadap
        // judul panel. Assertion yang membandingkan CSS dengan markupnya sendiri
        // akan tetap hijau saat <style> dipindah ke badan (CSS-nya toh tetap
        // muncul lebih dulu daripada div-nya), jadi hampa.
        $html    = $response->getContent();
        $posCss  = strpos($html, '.uwa-panel {');
        $posHead = strpos($html, '</head>');

        $this->assertNotFalse($posCss, 'CSS panel uji tidak dirender sama sekali.');
        $this->assertNotFalse($posHead, 'Layout tidak punya </head> — asumsi test ini salah.');
        $this->assertLessThan(
            $posHead,
            $posCss,
            "CSS panel uji dirender di badan, bukan di <head> — @push('styles') tidak dipakai."
        );
    }
```

- [ ] **Step 2: Jalankan test, pastikan GAGAL**

Run: `php artisan test --filter=test_panel_uji_tampil_di_halaman_bagian`
Expected: FAIL — teks "Uji coba pemberitahuan WhatsApp" tidak ditemukan.

- [ ] **Step 3: Buat partial**

Buat `resources/views/bagian/partials/_ujiWhatsApp.blade.php`:

```blade
{{--
  ============================ FITUR SEMENTARA ============================
  Panel uji kiriman WhatsApp untuk sesi UJI COBA PENGGUNA (2026-08-07).
  Gaya visualnya SENGAJA berbeda dari komponen lain (garis putus-putus, ikon
  labu kimia) supaya siapa pun langsung tahu ini bukan fitur produksi.

  Daftar pencabutan lengkap ada di docblock
  App\Http\Controllers\UjiWhatsAppBagianController.
  =========================================================================
--}}
@push('styles')
<style>
  /* Kelas ber-scope .uwa-*, NOL !important — mengikuti pola .notif-pengembalian
     di halaman yang sama. Jangan berperang spesifisitas dengan Bootstrap CDN. */
  .uwa-panel {
    border: 2px dashed #f59e0b;
    background: #fffbeb;
    border-radius: 12px;
    padding: 16px 18px;
    margin-bottom: 18px;
  }
  .uwa-judul {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 700;
    font-size: 15px;
    color: #92400e;
    margin-bottom: 4px;
  }
  .uwa-ket {
    font-size: 13px;
    color: #78350f;
    margin-bottom: 12px;
    line-height: 1.5;
  }
  .uwa-form {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
  }
  .uwa-input {
    flex: 1 1 220px;
    min-width: 0;
    padding: 8px 12px;
    border: 1px solid #d6b47a;
    border-radius: 8px;
    font-size: 14px;
    background: #fff;
    color: #1a2340;
  }
  .uwa-input:focus {
    outline: 2px solid #f59e0b;
    outline-offset: 1px;
  }
  .uwa-tombol {
    padding: 8px 16px;
    border: none;
    border-radius: 8px;
    background: #d97706;
    color: #fff;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
  }
  .uwa-tombol:hover:not(:disabled) { background: #b45309; }
  .uwa-tombol:disabled { opacity: .6; cursor: progress; }
  .uwa-hasil {
    flex-basis: 100%;
    font-size: 13px;
    line-height: 1.5;
    margin-top: 4px;
    min-height: 1em;
  }
  .uwa-hasil--ok    { color: #047857; }
  .uwa-hasil--gagal { color: #b91c1c; }
</style>
@endpush

<div class="uwa-panel">
  <div class="uwa-judul">
    <span aria-hidden="true">🧪</span>
    Uji coba pemberitahuan WhatsApp
  </div>
  <p class="uwa-ket">
    Masukkan nomor WhatsApp Bapak/Ibu, lalu tekan Kirim. Sistem akan mengirim
    <strong>satu pesan contoh</strong> yang bentuknya sama persis dengan pemberitahuan
    saat dokumen benar-benar dikembalikan. Pesannya bertanda
    <strong>[UJI COBA]</strong> &mdash; tidak ada dokumen yang benar-benar dikembalikan.
  </p>

  <div class="uwa-form">
    <label class="visually-hidden" for="uwaNomor">Nomor WhatsApp</label>
    <input type="tel" id="uwaNomor" class="uwa-input"
           placeholder="Contoh: 081234567890" autocomplete="tel" inputmode="numeric">
    <button type="button" id="uwaKirim" class="uwa-tombol">Kirim pesan uji</button>
    <div class="uwa-hasil" id="uwaHasil" role="status" aria-live="polite"></div>
  </div>
</div>

<script>
  (function () {
    const tombol = document.getElementById('uwaKirim');
    const input  = document.getElementById('uwaNomor');
    const hasil  = document.getElementById('uwaHasil');
    if (!tombol || !input || !hasil) return;

    const URL_KIRIM = @json(route('bagian.uji-whatsapp'));

    function tulisHasil(teks, ok) {
      // textContent, BUKAN innerHTML — pesan galat Fonnte adalah teks dari pihak
      // luar dan tidak boleh diperlakukan sebagai HTML.
      hasil.textContent = teks;
      hasil.className = 'uwa-hasil ' + (ok ? 'uwa-hasil--ok' : 'uwa-hasil--gagal');
    }

    tombol.addEventListener('click', async function () {
      const nomor = input.value.trim();
      if (nomor === '') {
        tulisHasil('Nomor WhatsApp wajib diisi.', false);
        input.focus();
        return;
      }

      tombol.disabled = true;
      const labelAsli = tombol.textContent;
      tombol.textContent = 'Mengirim…';
      hasil.textContent = '';

      try {
        const res = await fetch(URL_KIRIM, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
          },
          body: JSON.stringify({ nomor_hp: nomor }),
        });

        if (res.status === 429) {
          tulisHasil('Terlalu sering. Tunggu sebentar sebelum mencoba lagi.', false);
          return;
        }

        const data = await res.json().catch(function () { return {}; });

        if (res.status === 422) {
          tulisHasil(data.errors?.nomor_hp?.[0] || 'Nomor WhatsApp tidak sah.', false);
          return;
        }

        if (!res.ok) {
          tulisHasil('Terjadi kesalahan di server (' + res.status + ').', false);
          return;
        }

        tulisHasil(data.pesan || 'Tidak ada keterangan dari server.', data.ok === true);
      } catch (e) {
        tulisHasil('Gagal menghubungi server: ' + e.message, false);
      } finally {
        tombol.disabled = false;
        tombol.textContent = labelAsli;
      }
    });

    input.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') { e.preventDefault(); tombol.click(); }
    });
  })();
</script>
```

- [ ] **Step 4: Pasang partial di view Bagian**

Di `resources/views/bagian/dokumens/daftarDokumen.blade.php`, cari baris:

```blade
    @include('partials._infoCards', ['cards' => $cards])
```

Tambahkan tepat di bawahnya:

```blade

    {{-- SEMENTARA — panel uji kiriman WhatsApp untuk sesi uji coba pengguna.
         Hapus baris ini bersama partialnya (lihat docblock
         App\Http\Controllers\UjiWhatsAppBagianController). --}}
    @include('bagian.partials._ujiWhatsApp')
```

- [ ] **Step 5: Jalankan test, pastikan LULUS**

Run: `php artisan test --filter=UjiWhatsAppBagianTest`
Expected: PASS (6 test)

- [ ] **Step 6: Buktikan test menggigit**

Dua mutasi, satu per satu (pulihkan sebelum lanjut):

1. Komentari baris `@include('bagian.partials._ujiWhatsApp')` →
   `test_panel_uji_tampil_di_halaman_bagian` harus **GAGAL**.
2. Di partial, hapus baris `@push('styles')` dan `@endpush` (biarkan `<style>` polos di
   badan) → `test_panel_uji_tampil_di_halaman_bagian` harus **GAGAL** pada
   `assertLessThan`, karena CSS kini dirender setelah `</head>`.

Setelah keduanya: pulihkan, jalankan ulang filter (hijau), `git diff` bersih.

- [ ] **Step 7: Commit**

```bash
git add resources/views/bagian/partials/_ujiWhatsApp.blade.php
git add resources/views/bagian/dokumens/daftarDokumen.blade.php
git add tests/Feature/UjiWhatsAppBagianTest.php
git commit -m "feat(bagian): panel uji kiriman WhatsApp di halaman dokumen (SEMENTARA)

Panel inline di bawah kartu info, BUKAN modal: data-api Bootstrap 5 mati di
layout jQuery+BS5 ini, dan panel inline melewati persoalan itu tanpa satu
baris pun kode penggerak modal.

Gaya visual sengaja dibuat berbeda (garis putus-putus + ikon labu kimia)
supaya jelas ini bukan fitur produksi dan jelas apa yang harus dihapus.
Kelas ber-scope .uwa-*, nol !important, CSS lewat @push('styles') mengikuti
pola .notif-pengembalian di halaman yang sama.

Hasil ditulis dengan textContent, bukan innerHTML - pesan galat Fonnte
adalah teks dari pihak luar."
```

---

## Task 4: Suite penuh, deploy, QA browser

**Files:** tidak ada perubahan kode (kecuali perbaikan bila ada yang merah)

- [ ] **Step 1: Jalankan suite penuh**

Run: `php artisan test`
Expected: seluruh suite hijau.

CLAUDE.md aturan 3: suite penuh wajib hijau **sebelum push**, bukan sebelum tiap commit.
Ini titik itu. Kalau ada yang merah dan tak berkaitan dengan pekerjaan ini, laporkan ke
user — jangan diam-diam dilewati.

- [ ] **Step 2: Push**

```bash
git push origin codinggemini
```

- [ ] **Step 3: Deploy di server**

```bash
git pull
php artisan route:clear && php artisan view:clear && php artisan config:clear
```

Route baru **tidak akan ada** tanpa `route:clear`, dan partial baru tidak akan tampil
tanpa `view:clear`. Melewatkannya akan membuat perubahan tampak tidak berefek, lalu
waktu terbuang mencari bug yang tidak ada.

- [ ] **Step 4: QA browser — tampilan & validasi**

Login ke produksi sebagai akun Bagian (kredensial per-role ada di memori
`browser-qa-access`), buka `/bagian/documents`, lalu periksa:

1. Panel bergaris putus-putus tampil tepat di bawah tiga kartu info.
2. Gayanya utuh (latar krem, tombol jingga) — bukti `@push('styles')` benar-benar
   sampai ke `<head>`.
3. Tombol Kirim dengan input kosong → muncul "Nomor WhatsApp wajib diisi." dan
   **tidak ada** request ke server.
4. Isi `12345` lalu Kirim → muncul pesan galat validasi dari server, bukan "terkirim".

- [ ] **Step 5: QA browser — kiriman sungguhan (BUTUH KEPUTUSAN USER)**

**BERHENTI di sini dan tanyakan ke user.** Langkah ini mengirim pesan WhatsApp
sungguhan ke nomor sungguhan dan memotong kuota Fonnte berbayar.

Tanyakan: nomor mana yang boleh dipakai, atau apakah user ingin menekannya sendiri.

Sampai langkah ini benar-benar dijalankan, laporkan terus terang bahwa **kiriman
ujung-ke-ujung belum terbukti** — test memakai mock, jadi test hijau **tidak**
membuktikan Fonnte bisa mengirim dari server produksi. Itu justru pertanyaan yang
melahirkan fitur ini.

- [ ] **Step 6: Laporkan hasil ke user**

Sebutkan secara eksplisit: apa yang sudah diuji, apa yang belum, dan (bila Step 5
dijalankan) apa hasil sebenarnya dari Fonnte — termasuk bila gagal.

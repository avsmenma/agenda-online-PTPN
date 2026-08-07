# Tombol Uji Kiriman WhatsApp (Role Bagian) — Rencana Implementasi

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menambah tombol **Uji Kirim Pesan** di toolbar halaman role Bagian yang
membuka modal berisi keterangan + input nomor, lalu mengirim satu pesan WhatsApp contoh
"dokumen dikembalikan" lewat gateway Fonnte dan melaporkan hasil sebenarnya.

**Architecture:** Tombol toolbar → modal Blade (partial tersendiri) →
`POST /bagian/uji-whatsapp` (ber-throttle) → controller khusus → template pesan
**dipakai bersama** dengan `DocumentReturnNotifier` (bukan disalin) →
`FonnteWhatsAppService::sendMessage()` → hasil asli diteruskan ke JSON.

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
| `resources/views/bagian/partials/_ujiWhatsApp.blade.php` | CSS + markup modal + JS. Mandiri; menghapusnya menghapus hampir seluruh UI-nya. | **Baru** |
| `resources/views/bagian/dokumens/daftarDokumen.blade.php` | Dua sisipan: tombol di toolbar (harus di sana, sebaris dengan Refresh) + `@include` partial. | Modifikasi |
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
 *   3. Di resources/views/bagian/dokumens/daftarDokumen.blade.php hapus DUA
 *      sisipan: tombol id="btnUjiWhatsApp" di toolbar filter, dan baris
 *      @include('bagian.partials._ujiWhatsApp') di dekat modal lain
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

## Task 3: Tombol di toolbar + modal

**Files:**
- Create: `resources/views/bagian/partials/_ujiWhatsApp.blade.php` (memuat CSS, tombol,
  modal, dan JS sekaligus — satu berkas supaya pencabutan tetap satu penghapusan)
- Modify: `resources/views/bagian/dokumens/daftarDokumen.blade.php` (dua sisipan: satu
  baris tombol di toolbar, satu baris `@include` untuk modal+CSS+JS)
- Test: `tests/Feature/UjiWhatsAppBagianTest.php` (tambah 1 test)

**Interfaces:**
- Consumes: route `bagian.uji-whatsapp` (Task 2), respons `{ok, pesan}`, helper
  `userBagian(string $kode = 'TAN'): User` yang sudah ada di berkas test sejak Task 2
- Produces: —

**Catatan rancangan (jangan diubah sendiri):** rancangan awal memakai panel permanen di
bawah kartu info. **Dibatalkan user** — memakan ruang di setiap kunjungan padahal hanya
ditekan sekali per responden. Yang benar adalah tombol di toolbar + modal.

- [ ] **Step 1: Tulis test yang gagal**

Tambahkan test ini ke `tests/Feature/UjiWhatsAppBagianTest.php`:

```php
    public function test_tombol_dan_modal_uji_tampil_di_halaman_bagian(): void
    {
        \App\Models\Bagian::create(['kode' => 'TAN', 'nama' => 'Tanaman']);

        $response = $this->actingAs($this->userBagian('TAN'))
            ->get(route('bagian.documents.index'))
            ->assertOk();

        $html = $response->getContent();

        $response->assertSee('Uji Kirim Pesan');
        $response->assertSee('ujiWhatsAppModal', false);

        // Tombol berada di dalam <form method="GET"> milik toolbar filter. Tanpa
        // type="button" ia men-submit form dan memuat ulang halaman sebelum modalnya
        // sempat terbuka — cacat yang tak terlihat di test manapun kalau tidak
        // diperiksa di sini.
        $this->assertMatchesRegularExpression(
            '/<button[^>]*type="button"[^>]*id="btnUjiWhatsApp"/',
            $html,
            'Tombol Uji Kirim Pesan tidak bertipe button — ia akan men-submit form filter.'
        );

        // CSS WAJIB berada di dalam <head>, artinya lewat @push('styles'). Kalau ia
        // ditulis <style> polos di badan, tombol sempat tampil telanjang sebelum
        // gayanya ter-parse — regresi flash-of-unstyled yang persis pernah terjadi
        // saat ekstraksi modal Kustomisasi Kolom.
        $posCss  = strpos($html, '.uwa-tombol {');
        $posHead = strpos($html, '</head>');

        $this->assertNotFalse($posCss, 'CSS tombol uji tidak dirender sama sekali.');
        $this->assertNotFalse($posHead, 'Layout tidak punya </head> — asumsi test ini salah.');
        $this->assertLessThan(
            $posHead,
            $posCss,
            "CSS tombol uji dirender di badan, bukan di <head> — @push('styles') tidak dipakai."
        );
    }
```

- [ ] **Step 2: Jalankan test, pastikan GAGAL**

Run: `php artisan test --filter=test_tombol_dan_modal_uji_tampil_di_halaman_bagian`
Expected: FAIL — teks "Uji Kirim Pesan" tidak ditemukan.

- [ ] **Step 3: Buat partial**

Buat `resources/views/bagian/partials/_ujiWhatsApp.blade.php`:

```blade
{{--
  ============================ FITUR SEMENTARA ============================
  Modal uji kiriman WhatsApp untuk sesi UJI COBA PENGGUNA (2026-08-07).
  Tombol pemicunya disisipkan terpisah di toolbar filter daftarDokumen.blade.php
  (cari id="btnUjiWhatsApp").

  Daftar pencabutan lengkap ada di docblock
  App\Http\Controllers\UjiWhatsAppBagianController.
  =========================================================================
--}}
@push('styles')
<style>
  /* Kelas ber-scope .uwa-*, NOL !important — mengikuti pola .notif-pengembalian
     di halaman yang sama. Jangan berperang spesifisitas dengan Bootstrap CDN. */

  /* Bentuk meniru .btn-refresh (tinggi 44px, radius 8px, inline-flex) supaya
     sebaris rapi dengannya. Warnanya sengaja beda: ini tombol uji, bukan aksi
     sehari-hari. */
  .uwa-tombol {
    padding: 10px 20px;
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: #fff;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 2px 6px rgba(245, 158, 11, 0.3);
    min-height: 44px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
  }
  .uwa-tombol:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
    background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
  }
  .uwa-tombol:active { transform: translateY(0); }

  .uwa-ket {
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 10px;
    padding: 12px 14px;
    font-size: 13.5px;
    line-height: 1.6;
    color: #78350f;
    margin-bottom: 16px;
  }
  .uwa-ket strong { color: #92400e; }

  .uwa-label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 6px;
  }
  .uwa-input {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 14px;
    color: #1a2340;
  }
  .uwa-input:focus {
    outline: 2px solid #f59e0b;
    outline-offset: 1px;
    border-color: #f59e0b;
  }

  .uwa-hasil {
    font-size: 13px;
    line-height: 1.5;
    margin-top: 10px;
    min-height: 1em;
  }
  .uwa-hasil--ok    { color: #047857; }
  .uwa-hasil--gagal { color: #b91c1c; }

  .uwa-kirim:disabled { opacity: .6; cursor: progress; }

  /* Bentuk cangkang modal. Dua modal lain di berkas ini memakai atribut style=
     inline untuk hal yang sama; DI SINI TIDAK — CLAUDE.md aturan 4 melarang CSS
     inline baru, dan style= adalah spesifisitas tertinggi yang justru memulai
     perang yang dilarang itu. Selektor .uwa-modal .modal-* (0,2,0) sudah menang
     atas .modal-* milik Bootstrap (0,1,0) tanpa satu pun !important. */
  .uwa-modal .modal-content { border: none; border-radius: 16px; }
  .uwa-modal .modal-header  { border-bottom: 1px solid #e2e8f0; }
  .uwa-modal .modal-footer  { border-top: 1px solid #e2e8f0; }
  .uwa-modal .modal-body    { padding: 1.25rem 1.5rem; }
  .uwa-modal .modal-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 1.05rem;
    font-weight: 700;
    color: #1f2937;
  }
  .uwa-modal .modal-title i { color: #d97706; }
</style>
@endpush

{{-- Markup statis + dibuka lewat instance bootstrap.Modal eksplisit — pola yang
     SAMA dengan #perjalananModal & #rejectionDetailModal di berkas ini. Jangan
     mengarang mekanisme ketiga. --}}
<div class="modal fade uwa-modal" id="ujiWhatsAppModal" tabindex="-1"
  aria-labelledby="ujiWhatsAppModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="ujiWhatsAppModalLabel">
          <i class="fa-solid fa-flask" aria-hidden="true"></i>Uji Kirim Pesan WhatsApp
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>

      <div class="modal-body">
        <div class="uwa-ket">
          Uji coba ini akan mengirim <strong>satu pesan WhatsApp</strong> berisi
          pemberitahuan <strong>&ldquo;dokumen dikembalikan&rdquo;</strong> ke nomor yang
          Bapak/Ibu masukkan &mdash; bentuknya sama persis dengan pemberitahuan sungguhan.
          <br><br>
          <strong>Tidak ada dokumen yang benar-benar dikembalikan.</strong> Pesannya
          memakai data contoh dan bertanda <strong>[UJI COBA]</strong>.
        </div>

        <label class="uwa-label" for="uwaNomor">Nomor WhatsApp</label>
        <input type="tel" id="uwaNomor" class="uwa-input"
               placeholder="Contoh: 081234567890" autocomplete="tel" inputmode="numeric">

        <div class="uwa-hasil" id="uwaHasil" role="status" aria-live="polite"></div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="uwa-tombol uwa-kirim" id="uwaKirim">
          <i class="fa-brands fa-whatsapp"></i> Kirim
        </button>
      </div>
    </div>
  </div>
</div>

<script>
  (function () {
    const pemicu = document.getElementById('btnUjiWhatsApp');
    const modalEl = document.getElementById('ujiWhatsAppModal');
    const tombol = document.getElementById('uwaKirim');
    const input  = document.getElementById('uwaNomor');
    const hasil  = document.getElementById('uwaHasil');
    if (!pemicu || !modalEl || !tombol || !input || !hasil) return;

    const URL_KIRIM = @json(route('bagian.uji-whatsapp'));

    pemicu.addEventListener('click', function () {
      hasil.textContent = '';
      hasil.className = 'uwa-hasil';
      new bootstrap.Modal(modalEl).show();
      setTimeout(function () { input.focus(); }, 300);
    });

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
      const isiAsli = tombol.innerHTML;
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
        tombol.innerHTML = isiAsli;
      }
    });

    input.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') { e.preventDefault(); tombol.click(); }
    });
  })();
</script>
```

- [ ] **Step 4: Sisipkan tombol di toolbar**

Di `resources/views/bagian/dokumens/daftarDokumen.blade.php`, cari tombol Refresh
(sekitar baris 1234):

```blade
        <button type="button" class="btn-refresh" id="btnRefreshTable" onclick="refreshDocumentTable()">
          <i class="fa-solid fa-arrows-rotate"></i> Refresh
        </button>
```

Tambahkan tepat **di bawahnya**, masih di dalam `<form>` yang sama:

```blade

        {{-- SEMENTARA — pemicu modal uji kiriman WhatsApp. type="button" WAJIB:
             toolbar ini ada di dalam <form method="GET">, tanpa itu tombolnya
             men-submit form dan memuat ulang halaman. Hapus bersama partial
             bagian.partials._ujiWhatsApp. --}}
        <button type="button" class="uwa-tombol" id="btnUjiWhatsApp">
          <i class="fa-solid fa-flask"></i> Uji Kirim Pesan
        </button>
```

- [ ] **Step 5: Sisipkan partial (CSS + modal + JS)**

Masih di berkas yang sama, cari modal yang sudah ada:

```blade
  <!-- Modal: Rejection Detail - Bagian -->
```

Tambahkan tepat **di atas baris itu**:

```blade
  {{-- SEMENTARA — modal uji kiriman WhatsApp untuk sesi uji coba pengguna.
       Hapus baris ini bersama partialnya (lihat docblock
       App\Http\Controllers\UjiWhatsAppBagianController). --}}
  @include('bagian.partials._ujiWhatsApp')

```

- [ ] **Step 6: Jalankan test, pastikan LULUS**

Run: `php artisan test --filter=UjiWhatsAppBagianTest`
Expected: PASS (6 test)

- [ ] **Step 7: Buktikan test menggigit**

Tiga mutasi, satu per satu (pulihkan sebelum lanjut):

1. Komentari baris `@include('bagian.partials._ujiWhatsApp')` →
   `test_tombol_dan_modal_uji_tampil_di_halaman_bagian` harus **GAGAL**.
2. Hapus `type="button"` dari tombol `#btnUjiWhatsApp` → assertion regex harus **GAGAL**.
3. Di partial, hapus baris `@push('styles')` dan `@endpush` (biarkan `<style>` polos di
   badan) → assertion `assertLessThan` harus **GAGAL**, karena CSS kini dirender
   setelah `</head>`.

Setelah ketiganya: pulihkan, jalankan ulang filter (hijau), `git diff` bersih.

- [ ] **Step 8: Commit**

```bash
git add resources/views/bagian/partials/_ujiWhatsApp.blade.php
git add resources/views/bagian/dokumens/daftarDokumen.blade.php
git add tests/Feature/UjiWhatsAppBagianTest.php
git commit -m "feat(bagian): tombol Uji Kirim Pesan + modal WhatsApp (SEMENTARA)

Tombol di toolbar filter, sebaris dengan Refresh. Rancangan awal memakai
panel permanen di bawah kartu info; dibatalkan user karena memakan ruang
di setiap kunjungan padahal hanya ditekan sekali per responden.

type=\"button\" wajib: toolbar berada di dalam <form method=\"GET\">, tanpa
itu tombolnya men-submit form dan memuat ulang halaman sebelum modal
sempat terbuka. Dijaga assertion regex.

Modal mengikuti pola #perjalananModal yang sudah ada di berkas ini: markup
statis + instance bootstrap.Modal eksplisit. Isinya menjelaskan lebih dulu
bahwa uji ini mengirim pemberitahuan dokumen dikembalikan dan tak ada
dokumen yang benar-benar dikembalikan, baru meminta nomor.

CSS lewat @push('styles'), kelas ber-scope .uwa-*, nol !important. Hasil
ditulis textContent - pesan galat Fonnte adalah teks dari pihak luar."
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

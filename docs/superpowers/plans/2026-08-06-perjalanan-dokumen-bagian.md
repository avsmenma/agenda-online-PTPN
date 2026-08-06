# Perjalanan Dokumen untuk Role Bagian — Rencana Implementasi

> **Untuk pekerja agentik:** SUB-SKILL WAJIB: pakai superpowers:subagent-driven-development
> (disarankan) atau superpowers:executing-plans untuk mengerjakan rencana ini tugas demi
> tugas. Langkah memakai sintaks checkbox (`- [ ]`) untuk pelacakan.

**Goal:** Kolom "Pengurus Dokumen" di tabel role Bagian menampilkan posisi dokumen dalam
alur keuangan (rangkaian tahap + penanda posisi sekarang), bukan hanya satu label handler
terakhir.

**Arsitektur:** Logika penentuan status tahap dihitung SERVER di kelas murni
`App\Support\DocumentJourney` (mengikuti pola `App\Support\DocumentRow` — nol logika bisnis
di klien). Controller memanggilnya per baris dengan peta `dokumen_role_data` yang
di-eager-load sekali. View merender rangkaian titik; rinciannya di modal Bootstrap yang
membaca atribut `data-perjalanan` — TANPA endpoint baru, karena datanya sudah ada di halaman.

**Tech Stack:** Laravel 12, PHP 8.2, Blade, Bootstrap 5 (modal), PHPUnit.

**Spec:** `docs/superpowers/specs/2026-08-06-perjalanan-dokumen-bagian-design.md`

## Global Constraints

- UI/komentar Bahasa Indonesia, identifier English (CLAUDE.md).
- `git add` per-file. JANGAN `git add .` / `git add -A`.
- Nol CSS `!important` baru (aturan 4). CSS baru masuk blok `<style>` view Bagian yang
  sudah ada, dengan kelas ber-scope.
- Tiap assertion baru WAJIB dibuktikan menggigit: rusakkan kode → test GAGAL → pulihkan →
  LULUS → `git diff <berkas>` kosong (aturan 8).
- Saat iterasi jalankan test terfilter (`php artisan test --filter=Nama`). Suite penuh
  SEKALI sebelum push/deploy (aturan 3 & 7).
- **JANGAN pakai `Dokumen::getRoleDisplayNameIndo()` untuk label tahap.** Helper itu
  memetakan `'operator' => 'Bagian'` (`app/Models/Dokumen.php:221`), sehingga simpul
  Operator akan berlabel "Bagian" dan bertabrakan dengan simpul Bagian. `DocumentJourney`
  mendefinisikan labelnya sendiri.
- Role Bagian **view-only**. Rencana ini murni membaca; tidak mengubah `current_handler`,
  status, atau alur apa pun.

---

## Struktur Berkas

| Berkas | Tanggung jawab |
|---|---|
| `app/Support/DocumentJourney.php` (baru) | Menghitung status tiap tahap dari `current_handler` + `status` + jejak role. Kelas murni, tanpa query DB. |
| `tests/Unit/DocumentJourneyTest.php` (baru) | Enam keadaan aturan status. |
| `app/Http/Controllers/BagianDokumenController.php` (ubah) | Eager-load `roleData`, susun peta perjalanan per dokumen, oper ke view. |
| `tests/Feature/PerjalananDokumenBagianTest.php` (baru) | Render halaman + penjaga N+1. |
| `resources/views/bagian/dokumens/daftarDokumen.blade.php` (ubah) | Sel rangkaian titik, modal, CSS ber-scope, JS pengisi modal. |

---

### Task 1: Kelas DocumentJourney

**Files:**
- Create: `app/Support/DocumentJourney.php`
- Test: `tests/Unit/DocumentJourneyTest.php`

**Interfaces:**
- Consumes: `App\Models\Dokumen` (kolom `current_handler`, `status`, `bagian`,
  `tanggal_masuk`, `created_at`).
- Produces:
  `DocumentJourney::forDokumen(Dokumen $dokumen, array $roleCodeTerlacak = []): array`
  mengembalikan
  `['current_label' => string, 'current_index' => int, 'needs_action' => bool, 'stages' => array<int, array{key:string,label:string,state:string}>]`.
  Nilai `state` yang mungkin: `perlu_diperbaiki`, `sekarang`, `selesai`, `dilewati`,
  `belum`, `netral`.
  Konstanta publik `DocumentJourney::STAGES` (6 tahap berurutan).

- [ ] **Step 1: Tulis test yang gagal**

Buat `tests/Unit/DocumentJourneyTest.php`:

```php
<?php

namespace Tests\Unit;

use App\Models\Dokumen;
use App\Support\DocumentJourney;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Menguji App\Support\DocumentJourney — penentu posisi dokumen dalam alur keuangan
 * untuk role Bagian.
 *
 * Aturan (spec 2026-08-06):
 *   sekarang         : indeks == indeks current_handler
 *   selesai          : indeks < sekarang DAN ada jejak
 *   dilewati         : indeks < sekarang TANPA jejak
 *   belum            : indeks > sekarang
 *   perlu_diperbaiki : status returned_to_bidang -> simpul Bagian
 */
class DocumentJourneyTest extends TestCase
{
    use RefreshDatabase;

    private function dokumen(array $atribut = []): Dokumen
    {
        return Dokumen::create(array_merge([
            'nomor_agenda'    => '1_2026',
            'bulan'           => 'Agustus',
            'tahun'           => 2026,
            'tanggal_masuk'   => '2026-08-01',
            'status'          => 'sedang diproses',
            'created_by'      => 'operator',
            'current_handler' => 'team_verifikasi',
            'bagian'          => 'AKN',
        ], $atribut));
    }

    /** @return array<string,string> kunci tahap => state */
    private function petaState(array $hasil): array
    {
        $peta = [];
        foreach ($hasil['stages'] as $t) {
            $peta[$t['key']] = $t['state'];
        }

        return $peta;
    }

    public function test_dokumen_di_tengah_alur(): void
    {
        $hasil = DocumentJourney::forDokumen($this->dokumen(), ['team_verifikasi']);
        $peta  = $this->petaState($hasil);

        $this->assertSame('selesai', $peta['operator']);
        $this->assertSame('sekarang', $peta['verifikasi']);
        $this->assertSame('belum', $peta['perpajakan']);
        $this->assertSame('belum', $peta['akutansi']);
        $this->assertSame('belum', $peta['pembayaran']);
        $this->assertFalse($hasil['needs_action']);
        $this->assertSame('Verifikasi', $hasil['current_label']);
    }

    public function test_dokumen_di_ujung_alur(): void
    {
        $hasil = DocumentJourney::forDokumen(
            $this->dokumen(['current_handler' => 'pembayaran']),
            ['team_verifikasi', 'perpajakan', 'akutansi', 'pembayaran']
        );
        $peta = $this->petaState($hasil);

        $this->assertSame('selesai', $peta['operator']);
        $this->assertSame('selesai', $peta['verifikasi']);
        $this->assertSame('selesai', $peta['perpajakan']);
        $this->assertSame('selesai', $peta['akutansi']);
        $this->assertSame('sekarang', $peta['pembayaran']);
    }

    public function test_dokumen_dikembalikan_menyalakan_simpul_bagian(): void
    {
        $hasil = DocumentJourney::forDokumen(
            $this->dokumen(['status' => 'returned_to_bidang']),
            ['team_verifikasi']
        );
        $peta = $this->petaState($hasil);

        $this->assertSame('perlu_diperbaiki', $peta['bagian']);
        $this->assertSame('netral', $peta['operator']);
        $this->assertSame('netral', $peta['verifikasi']);
        $this->assertSame('netral', $peta['pembayaran']);
        $this->assertTrue($hasil['needs_action']);
        $this->assertSame('Bagian (AKN)', $hasil['stages'][0]['label']);
    }

    public function test_tahap_tanpa_jejak_ditandai_dilewati(): void
    {
        // Dokumen melompat langsung ke Pembayaran: perpajakan & akutansi tak pernah
        // punya baris received_at.
        $hasil = DocumentJourney::forDokumen(
            $this->dokumen(['current_handler' => 'pembayaran']),
            ['team_verifikasi']
        );
        $peta = $this->petaState($hasil);

        $this->assertSame('selesai', $peta['verifikasi']);
        $this->assertSame('dilewati', $peta['perpajakan']);
        $this->assertSame('dilewati', $peta['akutansi']);
    }

    public function test_dokumen_baru_di_operator_tidak_dianggap_dilewati(): void
    {
        // Operator MEMBUAT dokumen, bukan menerima — dokumen_role_data miliknya
        // ber-received_at NULL. Aturan naif akan menandainya 'dilewati'.
        $hasil = DocumentJourney::forDokumen(
            $this->dokumen(['current_handler' => 'operator']),
            []
        );
        $peta = $this->petaState($hasil);

        $this->assertSame('sekarang', $peta['operator']);
        $this->assertSame('belum', $peta['verifikasi']);
        $this->assertSame('Operator', $hasil['current_label']);
    }

    public function test_current_handler_tak_dikenal_jatuh_ke_operator(): void
    {
        foreach ([null, '', 'entah_apa'] as $nilai) {
            $hasil = DocumentJourney::forDokumen(
                $this->dokumen(['current_handler' => $nilai]),
                []
            );

            $this->assertSame(
                'sekarang',
                $this->petaState($hasil)['operator'],
                'Gagal untuk current_handler: ' . var_export($nilai, true)
            );
        }
    }
}
```

- [ ] **Step 2: Jalankan test, pastikan GAGAL**

Run: `php artisan test --filter=DocumentJourneyTest`
Expected: FAIL — `Class "App\Support\DocumentJourney" not found`

- [ ] **Step 3: Tulis implementasi minimal**

Buat `app/Support/DocumentJourney.php`:

```php
<?php

namespace App\Support;

use App\Models\Dokumen;

/**
 * Posisi sebuah dokumen dalam alur keuangan, untuk dipantau role Bagian.
 *
 * Menjawab pertanyaan responden UAT: "apakah masih di surat masuk, verifikasi, atau
 * proses lainnya sebelum ke tahap pembayaran". Kolom Pengurus Dokumen di tabel Bagian
 * sebelumnya hanya menampilkan SATU label handler terakhir.
 *
 * Kelas murni: TIDAK query DB. Jejak role dioper pemanggil (hindari N+1) — pola yang
 * sama dengan App\Support\HandlerOptions & App\Support\ColumnCustomization, dan alasan
 * yang sama: bisa di-unit-test tanpa kelas inang.
 *
 * Sengaja menampilkan POSISI, bukan riwayat bercap waktu. Pemeriksaan produksi
 * 2026-08-06: dari 5.719 dokumen hanya 2 yang punya jejak aksi manusia di
 * dokumen_activity_logs; sisanya derau auto-forward impor CSV. Riwayat bercap waktu
 * akan tampil kosong pada hampir semua dokumen.
 */
class DocumentJourney
{
    /**
     * Urutan tahap kanonik. Simpul 'bagian' BUKAN tahap kerja keuangan — ia ada untuk
     * menjawab "apakah bola ada di tangan saya", dan hanya menyala saat dokumen
     * dikembalikan.
     */
    public const STAGES = [
        ['key' => 'bagian',     'label' => 'Bagian'],
        ['key' => 'operator',   'label' => 'Operator'],
        ['key' => 'verifikasi', 'label' => 'Verifikasi'],
        ['key' => 'perpajakan', 'label' => 'Perpajakan'],
        ['key' => 'akutansi',   'label' => 'Akuntansi'],
        ['key' => 'pembayaran', 'label' => 'Pembayaran'],
    ];

    /**
     * role_code di database => kunci tahap.
     *
     * Label TIDAK diambil dari Dokumen::getRoleDisplayNameIndo(): helper itu memetakan
     * 'operator' => 'Bagian' (Dokumen.php:221), yang akan membuat simpul Operator
     * berlabel sama dengan simpul Bagian.
     */
    private const ROLE_KE_TAHAP = [
        'operator'        => 'operator',
        'team_verifikasi' => 'verifikasi',
        'verifikasi'      => 'verifikasi',
        'perpajakan'      => 'perpajakan',
        'akutansi'        => 'akutansi',
        'akuntansi'       => 'akutansi',
        'pembayaran'      => 'pembayaran',
    ];

    private const INDEKS_OPERATOR = 1;

    /**
     * @param  array<int,string>  $roleCodeTerlacak  role_code yang punya received_at
     */
    public static function forDokumen(Dokumen $dokumen, array $roleCodeTerlacak = []): array
    {
        $sekarang     = self::indeksTahap($dokumen->current_handler);
        $dikembalikan = strtolower((string) $dokumen->status) === 'returned_to_bidang';
        $terlacak     = self::tahapTerlacak($roleCodeTerlacak);

        $stages = [];
        foreach (self::STAGES as $i => $tahap) {
            $stages[] = [
                'key'   => $tahap['key'],
                'label' => self::label($tahap, $dokumen),
                'state' => self::keadaan($i, $sekarang, $tahap['key'], $dokumen, $terlacak, $dikembalikan),
            ];
        }

        return [
            'current_label' => $dikembalikan
                ? 'Perlu diperbaiki'
                : self::STAGES[$sekarang]['label'],
            'current_index' => $sekarang,
            'needs_action'  => $dikembalikan,
            'stages'        => $stages,
        ];
    }

    private static function label(array $tahap, Dokumen $dokumen): string
    {
        $bagian = trim((string) $dokumen->bagian);

        if ($tahap['key'] === 'bagian' && $bagian !== '') {
            return 'Bagian (' . $bagian . ')';
        }

        return $tahap['label'];
    }

    /** Indeks tahap dari current_handler. Kosong/tak dikenal => Operator. */
    private static function indeksTahap(?string $handler): int
    {
        $kunci = self::ROLE_KE_TAHAP[strtolower(trim((string) $handler))] ?? null;

        if ($kunci === null) {
            return self::INDEKS_OPERATOR;
        }

        foreach (self::STAGES as $i => $tahap) {
            if ($tahap['key'] === $kunci) {
                return $i;
            }
        }

        return self::INDEKS_OPERATOR;
    }

    /** @return array<string,true> kunci tahap yang punya jejak */
    private static function tahapTerlacak(array $roleCodeTerlacak): array
    {
        $hasil = [];
        foreach ($roleCodeTerlacak as $role) {
            $kunci = self::ROLE_KE_TAHAP[strtolower(trim((string) $role))] ?? null;
            if ($kunci !== null) {
                $hasil[$kunci] = true;
            }
        }

        return $hasil;
    }

    private static function keadaan(
        int $i,
        int $sekarang,
        string $kunci,
        Dokumen $dokumen,
        array $terlacak,
        bool $dikembalikan
    ): string {
        // Dokumen dikembalikan: bola di tangan Bagian, tahap hilir tidak relevan.
        if ($dikembalikan) {
            return $i === 0 ? 'perlu_diperbaiki' : 'netral';
        }

        if ($i === 0) {
            return 'belum';
        }

        if ($i === $sekarang) {
            return 'sekarang';
        }

        if ($i > $sekarang) {
            return 'belum';
        }

        return self::adaJejak($kunci, $dokumen, $terlacak) ? 'selesai' : 'dilewati';
    }

    /**
     * Operator MEMBUAT dokumen, bukan menerimanya — baris dokumen_role_data miliknya
     * ber-received_at NULL (terbukti pada dokumen 5721 produksi). Tanpa pengecualian
     * ini, Operator akan selalu tertandai 'dilewati'.
     */
    private static function adaJejak(string $kunci, Dokumen $dokumen, array $terlacak): bool
    {
        if ($kunci === 'operator') {
            return $dokumen->tanggal_masuk !== null || $dokumen->created_at !== null;
        }

        return isset($terlacak[$kunci]);
    }
}
```

- [ ] **Step 4: Jalankan test, pastikan LULUS**

Run: `php artisan test --filter=DocumentJourneyTest`
Expected: PASS — 6 test

- [ ] **Step 5: Buktikan test menggigit (aturan 8)**

Terapkan mutasi berikut SATU PER SATU, jalankan test, pastikan test yang disebut GAGAL,
lalu PULIHKAN:

| Mutasi | Test yang harus gagal |
|---|---|
| `adaJejak()`: hapus cabang `if ($kunci === 'operator')` | `test_dokumen_di_tengah_alur` |
| `keadaan()`: ganti `return ... ? 'selesai' : 'dilewati'` jadi `return 'selesai'` | `test_tahap_tanpa_jejak_ditandai_dilewati` |
| `keadaan()`: hapus blok `if ($dikembalikan)` | `test_dokumen_dikembalikan_menyalakan_simpul_bagian` |
| `indeksTahap()`: `return self::INDEKS_OPERATOR` pertama jadi `return 2` | `test_current_handler_tak_dikenal_jatuh_ke_operator` |

Setelah semua dipulihkan:

Run: `git diff app/Support/DocumentJourney.php`
Expected: kosong

- [ ] **Step 6: Commit**

```bash
git add app/Support/DocumentJourney.php tests/Unit/DocumentJourneyTest.php
git commit -m "feat(bagian): kelas DocumentJourney penentu posisi dokumen dalam alur"
```

---

### Task 2: Sambungkan controller + render rangkaian tahap di sel

> Controller yang menghitung dan view yang merender adalah SATU hasil. Memisahkannya
> akan meninggalkan test merah di antara tugas — hasil yang tidak bisa diuji sendiri.

**Files:**
- Modify: `app/Http/Controllers/BagianDokumenController.php:62` (eager load) dan blok
  `return view(...)` di `index()`
- Modify: `resources/views/bagian/dokumens/daftarDokumen.blade.php` — sel `col-pengurus`
  (cari `<td class="col-pengurus"`), dan blok `<style>` yang sudah ada (sisipkan sebelum
  `</style>`)
- Test: `tests/Feature/PerjalananDokumenBagianTest.php`

**Interfaces:**
- Consumes: `DocumentJourney::forDokumen(Dokumen, array): array` (Task 1).
- Produces: variabel view `$perjalanan` — `array<int, array>` berkunci `dokumen.id`;
  markup `.dj-cell` ber-atribut `data-perjalanan` (JSON) yang dibaca Task 3.

- [ ] **Step 1: Tulis test yang gagal**

Buat `tests/Feature/PerjalananDokumenBagianTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\Dokumen;
use App\Models\DokumenRoleData;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Menguji tampilan perjalanan dokumen di halaman role Bagian.
 */
class PerjalananDokumenBagianTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Query daftar dokumen Bagian mengurutkan pakai SUBSTRING_INDEX (fungsi MySQL)
        // yang tak ada di SQLite. Polyfill sama dengan OperatorDatatableTest.
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

    private function dokumen(string $nomor, array $atribut = []): Dokumen
    {
        return Dokumen::create(array_merge([
            'nomor_agenda'    => $nomor . '_2026',
            'bulan'           => 'Agustus',
            'tahun'           => 2026,
            'tanggal_masuk'   => '2026-08-01',
            'status'          => 'sedang diproses',
            'created_by'      => 'operator',
            'current_handler' => 'team_verifikasi',
            'bagian'          => 'AKN',
        ], $atribut));
    }

    public function test_halaman_merender_rangkaian_tahap(): void
    {
        $this->dokumen('1');

        $this->actingAs($this->userBagian())
            ->get(route('bagian.documents.index'))
            ->assertOk()
            ->assertSee('dj-node', false)
            ->assertSee('Verifikasi', false);
    }

    public function test_dokumen_dikembalikan_ditandai_perlu_diperbaiki(): void
    {
        $this->dokumen('2', ['status' => 'returned_to_bidang']);

        $this->actingAs($this->userBagian())
            ->get(route('bagian.documents.index'))
            ->assertOk()
            ->assertSee('Perlu diperbaiki', false)
            ->assertSee('dj-node--perlu_diperbaiki', false);
    }

    public function test_jejak_role_terbaca_dari_dokumen_role_data(): void
    {
        $dokumen = $this->dokumen('3', ['current_handler' => 'pembayaran']);

        DokumenRoleData::create([
            'dokumen_id'  => $dokumen->id,
            'role_code'   => 'team_verifikasi',
            'received_at' => now(),
        ]);

        $this->actingAs($this->userBagian())
            ->get(route('bagian.documents.index'))
            ->assertOk()
            // perpajakan & akutansi tak punya received_at => dilewati
            ->assertSee('dj-node--dilewati', false);
    }

    public function test_tidak_ada_n_plus_1_saat_dokumen_bertambah(): void
    {
        $user = $this->userBagian();

        $this->dokumen('10');
        DB::enableQueryLog();
        $this->actingAs($user)->get(route('bagian.documents.index', ['per_page' => 100]))->assertOk();
        $satuDokumen = count(DB::getQueryLog());

        DB::disableQueryLog();
        for ($i = 11; $i <= 25; $i++) {
            $this->dokumen((string) $i);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAs($user)->get(route('bagian.documents.index', ['per_page' => 100]))->assertOk();
        $limaBelasDokumen = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertLessThanOrEqual(
            $satuDokumen + 2,
            $limaBelasDokumen,
            "Jumlah query tumbuh dari {$satuDokumen} (1 dokumen) ke {$limaBelasDokumen} "
            . '(16 dokumen) — indikasi N+1 pada perjalanan dokumen.'
        );
    }
}
```

- [ ] **Step 2: Jalankan test, pastikan GAGAL**

Run: `php artisan test --filter=PerjalananDokumenBagianTest`
Expected: FAIL — `dj-node` tidak ditemukan di HTML

- [ ] **Step 3: Ubah controller**

Di `app/Http/Controllers/BagianDokumenController.php`, ubah baris 62 — tambahkan
`'roleData'` ke eager load:

```php
$query = Dokumen::with(['dokumenPos', 'dokumenPrs', 'dibayarKepadas', 'roleData'])
```

Lalu, tepat SEBELUM `return view('bagian.dokumens.daftarDokumen', compact(...))` di
`index()`, sisipkan:

```php
        // Perjalanan dokumen dalam alur keuangan (kolom Pengurus Dokumen).
        // roleData sudah ter-eager-load di query atas, jadi tidak ada query per-baris.
        $perjalanan = [];
        foreach ($dokumens as $dokumen) {
            $roleCodeTerlacak = $dokumen->roleData
                ->filter(fn ($rd) => $rd->received_at !== null)
                ->pluck('role_code')
                ->all();

            $perjalanan[$dokumen->id] = \App\Support\DocumentJourney::forDokumen(
                $dokumen,
                $roleCodeTerlacak
            );
        }
```

Dan tambahkan `'perjalanan'` ke daftar `compact(...)`:

```php
        return view('bagian.dokumens.daftarDokumen', compact(
            'dokumens',
            'bagianCode',
            'bagianName',
            'availableColumns',
            'selectedColumns',
            'totalDokumen',
            'totalBelumDibayar',
            'totalSudahDibayar',
            'notifPengembalian',
            'perjalanan'
        ));
```

- [ ] **Step 4: Ganti isi sel**

Ganti seluruh isi `<td class="col-pengurus" ...>` (saat ini satu `<span>` berisi label
handler) dengan:

```blade
                        <td class="col-pengurus" onclick="event.stopPropagation()">
                          {{-- Bagian view-only: perjalanan ditampilkan read-only.
                               Rinciannya di modal (Task 4) yang membaca data-perjalanan —
                               tanpa endpoint baru, datanya sudah ada di halaman. --}}
                          @php $jalan = $perjalanan[$doc->id] ?? null; @endphp
                          @if($jalan)
                            <button type="button"
                                    class="dj-cell {{ $jalan['needs_action'] ? 'dj-cell--action' : '' }}"
                                    title="Klik untuk melihat perjalanan dokumen"
                                    data-perjalanan="{{ json_encode($jalan) }}"
                                    onclick="event.stopPropagation(); tampilkanPerjalanan(this)">
                              <span class="dj-track">
                                @foreach($jalan['stages'] as $i => $tahap)
                                  {{-- Simpul Bagian hanya ditampilkan di sel saat dokumen
                                       dikembalikan; selebihnya ia inert dan hanya menambah derau. --}}
                                  @if($i > 0 || $jalan['needs_action'])
                                    <span class="dj-node dj-node--{{ $tahap['state'] }}"></span>
                                  @endif
                                @endforeach
                              </span>
                              <span class="dj-label">{{ $jalan['current_label'] }}</span>
                            </button>
                          @else
                            <span class="text-muted">-</span>
                          @endif
                        </td>
```

- [ ] **Step 5: Tambahkan CSS ber-scope**

Sisipkan sebelum `</style>` di blok `<style>` view yang sudah ada. Nol `!important`:

```css
    /* ===== Perjalanan dokumen (kolom Pengurus Dokumen) ===== */
    .dj-cell {
      display: inline-flex;
      flex-direction: column;
      align-items: flex-start;
      gap: 4px;
      padding: 4px 6px;
      border: 1px solid transparent;
      border-radius: 8px;
      background: none;
      cursor: pointer;
      font: inherit;
      text-align: left;
    }

    .dj-cell:hover,
    .dj-cell:focus-visible {
      background: #f4f7fb;
      border-color: #e2e8f0;
    }

    .dj-track {
      display: inline-flex;
      align-items: center;
      gap: 4px;
    }

    .dj-node {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: #fff;
      border: 1.5px solid #cbd5e1;
      flex: 0 0 auto;
    }

    .dj-node--selesai          { background: #10b981; border-color: #10b981; }
    .dj-node--sekarang         { background: #0ea5e9; border-color: #0ea5e9; box-shadow: 0 0 0 3px rgba(14, 165, 233, .18); }
    .dj-node--dilewati         { background: #e2e8f0; border-color: #e2e8f0; }
    .dj-node--belum            { background: #fff; border-color: #cbd5e1; }
    .dj-node--netral           { background: #f1f5f9; border-color: #e2e8f0; }
    .dj-node--perlu_diperbaiki { background: #f59e0b; border-color: #f59e0b; box-shadow: 0 0 0 3px rgba(245, 158, 11, .22); }

    .dj-label {
      font-size: 11px;
      font-weight: 600;
      color: #475569;
      white-space: nowrap;
    }

    .dj-cell--action .dj-label { color: #b45309; }
```

- [ ] **Step 6: Jalankan test, pastikan LULUS**

Run: `php artisan view:clear && php artisan test --filter=PerjalananDokumenBagianTest`
Expected: PASS — 4 test

- [ ] **Step 7: Buktikan test menggigit (aturan 8)**

| Mutasi | Test yang harus gagal |
|---|---|
| Hapus `dj-node--{{ $tahap['state'] }}`, sisakan `class="dj-node"` | `test_dokumen_dikembalikan_ditandai_perlu_diperbaiki`, `test_jejak_role_terbaca_dari_dokumen_role_data` |
| Ganti `{{ $jalan['current_label'] }}` jadi `{{ '-' }}` | `test_dokumen_dikembalikan_ditandai_perlu_diperbaiki` |

Pulihkan, lalu:

Run: `git diff resources/views/bagian/dokumens/daftarDokumen.blade.php`
Expected: hanya berisi perubahan Step 1 & 2 (bukan sisa mutasi)

- [ ] **Step 8: Commit**

```bash
git add app/Http/Controllers/BagianDokumenController.php resources/views/bagian/dokumens/daftarDokumen.blade.php tests/Feature/PerjalananDokumenBagianTest.php
git commit -m "feat(bagian): tampilkan perjalanan dokumen di kolom Pengurus Dokumen"
```

---

### Task 3: Modal rincian perjalanan

**Files:**
- Modify: `resources/views/bagian/dokumens/daftarDokumen.blade.php` — tambah markup modal
  (di dekat modal `rejectionDetailModal` yang sudah ada) dan fungsi JS
  `tampilkanPerjalanan` (di blok `<script>` yang sudah ada, dekat `showRejectionModal`)

**Interfaces:**
- Consumes: atribut `data-perjalanan` pada `.dj-cell` (Task 2).
- Produces: `window.tampilkanPerjalanan(tombol)` — dipanggil dari `onclick` sel (Task 2).

- [ ] **Step 1: Tambahkan markup modal**

Sisipkan tepat SEBELUM markup `<div class="modal fade" id="rejectionDetailModal"`:

```blade
  {{-- Modal perjalanan dokumen. Diisi dari atribut data-perjalanan pada sel —
       TANPA panggilan AJAX, karena datanya sudah dirender bersama halaman. --}}
  <div class="modal fade" id="perjalananModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content" style="border: none; border-radius: 16px;">
        <div class="modal-header" style="border-bottom: 1px solid #e2e8f0;">
          <h5 class="modal-title" style="font-size: 1.05rem; font-weight: 700; color: #1f2937;">
            <i class="fa-solid fa-route me-2" style="color: #0ea5e9;"></i>Perjalanan Dokumen
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body" style="padding: 1.25rem 1.5rem;">
          <ul class="dj-list" id="perjalananList"></ul>
        </div>
      </div>
    </div>
  </div>
```

- [ ] **Step 2: Tambahkan CSS daftar modal**

Sisipkan sebelum `</style>`, setelah blok CSS Task 2:

```css
    .dj-list {
      list-style: none;
      margin: 0;
      padding: 0;
    }

    .dj-list li {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 9px 0;
      border-bottom: 1px solid #f1f5f9;
    }

    .dj-list li:last-child { border-bottom: none; }

    .dj-list .dj-nama {
      flex: 1;
      font-size: 13px;
      font-weight: 600;
      color: #1f2937;
    }

    .dj-list .dj-ket {
      font-size: 11px;
      font-weight: 600;
      letter-spacing: .04em;
      text-transform: uppercase;
      color: #64748b;
    }

    .dj-list li.is-sekarang .dj-nama { color: #0369a1; }
    .dj-list li.is-sekarang .dj-ket  { color: #0369a1; }
    .dj-list li.is-perlu_diperbaiki .dj-nama { color: #b45309; }
    .dj-list li.is-perlu_diperbaiki .dj-ket  { color: #b45309; }
    .dj-list li.is-dilewati .dj-nama { color: #94a3b8; }
    .dj-list li.is-netral .dj-nama   { color: #94a3b8; }
```

- [ ] **Step 3: Tambahkan JS**

Sisipkan di blok `<script>` yang memuat `window.showRejectionModal`, tepat sebelum fungsi
itu:

```javascript
    // Modal perjalanan dokumen — dibaca dari atribut data-perjalanan pada sel,
    // tanpa AJAX. Dipanggil dari onclick sel (Task 3).
    window.tampilkanPerjalanan = function(tombol) {
      const modalEl = document.getElementById('perjalananModal');
      const daftar  = document.getElementById('perjalananList');
      if (!modalEl || !daftar) return;

      let jalan;
      try {
        jalan = JSON.parse(tombol.getAttribute('data-perjalanan') || '{}');
      } catch (err) {
        return;
      }

      const keterangan = {
        selesai:          'selesai',
        sekarang:         'sekarang',
        dilewati:         'dilewati',
        belum:            'belum',
        netral:           '—',
        perlu_diperbaiki: 'perlu diperbaiki',
      };

      daftar.innerHTML = (jalan.stages || []).map(function (tahap) {
        return '<li class="is-' + tahap.state + '">' +
          '<span class="dj-node dj-node--' + tahap.state + '"></span>' +
          '<span class="dj-nama"></span>' +
          '<span class="dj-ket"></span>' +
        '</li>';
      }).join('');

      // Teks disisipkan lewat textContent, bukan innerHTML — label memuat nama bagian
      // dari database dan tidak boleh diperlakukan sebagai HTML.
      Array.from(daftar.children).forEach(function (li, i) {
        const tahap = jalan.stages[i];
        li.querySelector('.dj-nama').textContent = tahap.label;
        li.querySelector('.dj-ket').textContent  = keterangan[tahap.state] || tahap.state;
      });

      new bootstrap.Modal(modalEl).show();
    };
```

- [ ] **Step 4: Tambahkan test render modal**

Tambahkan ke `tests/Feature/PerjalananDokumenBagianTest.php`:

```php
    public function test_markup_modal_perjalanan_ikut_dirender(): void
    {
        $this->dokumen('4');

        $this->actingAs($this->userBagian())
            ->get(route('bagian.documents.index'))
            ->assertOk()
            ->assertSee('id="perjalananModal"', false)
            ->assertSee('tampilkanPerjalanan', false)
            ->assertSee('data-perjalanan', false);
    }
```

- [ ] **Step 5: Jalankan test, pastikan LULUS**

Run: `php artisan view:clear && php artisan test --filter=PerjalananDokumenBagianTest`
Expected: PASS — 5 test

- [ ] **Step 6: Buktikan test menggigit (aturan 8)**

| Mutasi | Test yang harus gagal |
|---|---|
| Hapus `id="perjalananModal"` dari markup modal | `test_markup_modal_perjalanan_ikut_dirender` |
| Ganti nama fungsi `window.tampilkanPerjalanan` jadi `window.tampilkanPerjalananX` | `test_markup_modal_perjalanan_ikut_dirender` |

Pulihkan, lalu `git diff` pada berkas view harus hanya berisi perubahan yang disengaja.

- [ ] **Step 7: Commit**

```bash
git add resources/views/bagian/dokumens/daftarDokumen.blade.php tests/Feature/PerjalananDokumenBagianTest.php
git commit -m "feat(bagian): modal rincian perjalanan dokumen"
```

---

### Task 4: Suite penuh, deploy, QA browser

**Files:** tidak ada perubahan kode.

- [ ] **Step 1: Jalankan suite penuh**

Run: `php artisan test`
Expected: PASS, jumlah test = 340 + 11 test baru = 351

Kalau ada yang merah: HENTIKAN, laporkan ke user, JANGAN push.

- [ ] **Step 2: Push & deploy**

```bash
git push origin codinggemini
```

Di server:

```bash
cd /var/www/agenda-online-PTPN
git pull origin codinggemini
php artisan view:clear && php artisan route:clear && php artisan config:clear
```

`view:clear` WAJIB — perubahan Blade tidak akan terlihat tanpa itu.

- [ ] **Step 3: QA browser (aturan 9 — suite hijau ≠ fitur jalan)**

Login produksi `http://163.61.58.92` sebagai `akn` / `12345678` → `/bagian/documents`.

Periksa:

| Yang diperiksa | Harapan |
|---|---|
| Kolom Pengurus Dokumen | Rangkaian titik + label posisi, bukan satu label teks |
| Dokumen 5720/5721 (dikembalikan ke AKN) | Titik kuning di depan + label "Perlu diperbaiki" |
| Klik sel | Modal terbuka, keenam tahap terdaftar dengan keterangannya |
| Label simpul pertama | "Bagian (AKN)", BUKAN "Bagian" polos |
| Tinggi baris | Tidak bertambah dibanding sebelumnya |

- [ ] **Step 4: Laporkan hasil QA ke user**

Sebutkan apa yang terbukti dan apa yang TIDAK sempat diuji. Keputusan lolos milik user.

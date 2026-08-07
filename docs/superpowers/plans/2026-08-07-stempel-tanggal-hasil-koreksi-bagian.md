# Stempel `tanggal_hasil_koreksi_bagian` — Rencana Implementasi

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Kolom "Tgl Hasil Koreksi Bagian" terisi otomatis dengan tanggal & jam saat
Tim Verifikasi menerima kembali dokumen hasil revisi dari Bagian (lewat dropdown
Pengurus Dokumen), dan nilainya benar-benar tampil di tabel.

**Architecture:** Kolom databasenya belum pernah ada — dibuat lewat migrasi idempoten,
di-cast `datetime` di model, lalu distempel **satu baris** di
`DocumentHandlerController::receiveBackFromBagian()` (gerbangnya sudah ada, tidak
ditambah). Nilainya diikutkan ke daftar select eksplisit `buildVerifikasiQuery()` dan
ditandai hanya-baca di klien.

**Tech Stack:** Laravel 12, PHP 8.2, MySQL 8 (produksi) / SQLite in-memory (test),
Tabulator.js, PHPUnit.

**Spec:** `docs/superpowers/specs/2026-08-07-stempel-tanggal-hasil-koreksi-bagian-design.md`

## Global Constraints

- **Migrasi wajib idempoten** — guard `Schema::hasColumn` di `up()` DAN `down()`
  (CLAUDE.md aturan 6). Contoh acuan:
  `database/migrations/2026_08_06_100000_add_tanggal_kembali_ke_bagian_to_dokumens.php`.
- **Jangan tambah guard `Schema::hasColumn()` di kode bisnis** — hanya di migrasi.
  Guard di kode bisnis membuat fitur mati diam-diam alih-alih error keras.
- **Nol tebakan aturan bisnis.** Keputusan yang sudah diambil user: koreksi berulang
  **MENIMPA** stempel lama (kolom selalu menunjuk siklus terakhir).
- **Jangan tambah pintu kedua.** Stempel hanya di `receiveBackFromBagian()` — satu-
  satunya pintu terima-balik yang ada. Jangan menyalinnya ke tempat lain.
- Bahasa: UI & komentar Indonesia, identifier English (nama method domain yang sudah
  Indonesia tetap: `receiveBackFromBagian` adalah English, biarkan).
- **`git add` per-berkas.** JANGAN `git add .` / `git add -A`. Pesan commit Bahasa
  Indonesia.
- **Test terfilter saat iterasi** (`php artisan test --filter=NamaTest`). Suite penuh
  **sekali sebelum push** (CLAUDE.md aturan 3 & 7) — itu Task 3.
- **Tiap assertion baru wajib dibuktikan menggigit** (CLAUDE.md aturan 8): rusakkan kode
  yang dijaga → test GAGAL → pulihkan → LULUS → `git diff <berkas>` kosong.
- **JANGAN jalankan `migrate:fresh`/`migrate:wipe`/seeder.** `php artisan migrate` biasa
  di lokal boleh; di server hanya pada Task 3.
- `core.autocrlf = true` — jangan menulis berkas lewat script Python/Node.

---

## Struktur Berkas

| Berkas | Tanggung jawab | Aksi |
|---|---|---|
| `database/migrations/2026_08_07_100000_add_tanggal_hasil_koreksi_bagian_to_dokumens.php` | Membuat kolomnya, idempoten. | **Baru** |
| `app/Models/Dokumen.php` | `$fillable` + `$casts` untuk kolom baru. | Modifikasi |
| `tests/Unit/OperatorDocumentRowTest.php` | Dua test jalur parse defensif — ditulis ulang agar tak bergantung kolom non-cast. | Modifikasi |
| `app/Http/Controllers/DocumentHandlerController.php` | Stempel satu baris di `receiveBackFromBagian()`. | Modifikasi |
| `app/Http/Controllers/TeamVerifikasiController.php` | Kolom ikut di daftar select eksplisit. | Modifikasi |
| `public/js/document-tabulator.js` | Kolom ditandai hanya-baca. | Modifikasi |
| `tests/Feature/TanggalHasilKoreksiBagianTest.php` | Semua test perilaku fitur ini. | **Baru** |

---

## Task 1: Buat kolomnya nyata

**Files:**
- Create: `database/migrations/2026_08_07_100000_add_tanggal_hasil_koreksi_bagian_to_dokumens.php`
- Modify: `app/Models/Dokumen.php` (`$fillable` ~baris 52, `$casts` ~baris 132)
- Modify: `tests/Unit/OperatorDocumentRowTest.php` (dua test, ~baris 349-380)
- Test: `tests/Feature/TanggalHasilKoreksiBagianTest.php` (baru, 1 test di task ini)

**Interfaces:**
- Consumes: —
- Produces: kolom DB `dokumens.tanggal_hasil_koreksi_bagian` (`timestamp` nullable),
  ter-`$fillable` dan ter-`$casts` sebagai `'datetime'` di `App\Models\Dokumen`.

- [ ] **Step 1: Tulis test yang gagal**

Buat `tests/Feature/TanggalHasilKoreksiBagianTest.php`:

```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Menguji kolom `tanggal_hasil_koreksi_bagian` — stempel waktu saat dokumen hasil
 * revisi DITERIMA KEMBALI dari Bagian oleh Team Verifikasi.
 *
 * Pasangan dari `tanggal_kembali_ke_bagian`: yang itu mencatat kapan dokumen
 * DIKIRIM ke Bagian, yang ini kapan dokumen KEMBALI. Tanpa keduanya, lama Bagian
 * mengoreksi dokumen tak bisa diukur.
 *
 * Kolom ini sudah lama terdaftar di katalog (config/document_columns.php:51)
 * sehingga muncul di tabel, tetapi kolom databasenya TIDAK PERNAH ADA sampai
 * migrasi 2026_08_07_100000 — selalu tampil '-' dan mustahil terisi.
 */
class TanggalHasilKoreksiBagianTest extends TestCase
{
    use RefreshDatabase;

    public function test_kolom_database_benar_benar_ada(): void
    {
        // Migrasi dijaga hasColumn, jadi test ini sekaligus membuktikan migrasinya
        // jalan dan bukan no-op diam-diam.
        $this->assertTrue(
            Schema::hasColumn('dokumens', 'tanggal_hasil_koreksi_bagian'),
            'Kolom tanggal_hasil_koreksi_bagian tidak ada — katalog kolom akan menjanjikan sesuatu yang tak bisa terisi.'
        );
    }
}
```

- [ ] **Step 2: Jalankan test, pastikan GAGAL**

Run: `php artisan test --filter=TanggalHasilKoreksiBagianTest`
Expected: FAIL — `Kolom tanggal_hasil_koreksi_bagian tidak ada`

- [ ] **Step 3: Buat migrasi**

Buat `database/migrations/2026_08_07_100000_add_tanggal_hasil_koreksi_bagian_to_dokumens.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kolom `tanggal_hasil_koreksi_bagian` — stempel waktu saat dokumen hasil revisi
 * DITERIMA KEMBALI dari Bagian oleh Team Verifikasi.
 *
 * Pasangan dari `tanggal_kembali_ke_bagian` (migrasi 2026_08_06_100000): yang itu
 * mencatat kapan dokumen DIKIRIM ke Bagian, yang ini kapan dokumen KEMBALI. Tanpa
 * keduanya, lama Bagian mengoreksi dokumen tidak bisa diukur sama sekali.
 *
 * Sama seperti kembarannya, kolom ini SUDAH lama terdaftar di katalog
 * (config/document_columns.php:51) sehingga muncul sebagai pilihan kolom di tabel,
 * TETAPI kolom databasenya tidak pernah dibuat — nol migrasi. Selalu tampil '-'.
 *
 * Diisi OTOMATIS oleh DocumentHandlerController::receiveBackFromBagian(), satu-
 * satunya pintu terima-balik yang ada. Karena diisi otomatis, kolomnya juga
 * hanya-baca di tabel (NON_EDITABLE_FIELDS di public/js/document-tabulator.js).
 *
 * Idempoten (aturan 6 CLAUDE.md): dijaga hasColumn agar aman dijalankan ulang.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('dokumens', 'tanggal_hasil_koreksi_bagian')) {
            return;
        }

        Schema::table('dokumens', function (Blueprint $table) {
            $table->timestamp('tanggal_hasil_koreksi_bagian')
                ->nullable()
                ->after('tanggal_kembali_ke_bagian');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('dokumens', 'tanggal_hasil_koreksi_bagian')) {
            return;
        }

        Schema::table('dokumens', function (Blueprint $table) {
            $table->dropColumn('tanggal_hasil_koreksi_bagian');
        });
    }
};
```

- [ ] **Step 4: Daftarkan di model**

Di `app/Models/Dokumen.php`, pada array `$fillable`, tepat SETELAH baris
`'tanggal_kembali_ke_bagian',` tambahkan:

```php
        'tanggal_hasil_koreksi_bagian',
```

Pada array `$casts`, tepat SETELAH baris `'tanggal_kembali_ke_bagian' => 'datetime',`
tambahkan:

```php
        'tanggal_hasil_koreksi_bagian' => 'datetime',
```

Keduanya perlu: tanpa `$fillable`, `update()` mengabaikannya **diam-diam**; tanpa cast,
`formatDates()` jatuh ke jalur parse defensif alih-alih memformat objek Carbon.

- [ ] **Step 5: Jalankan test, pastikan LULUS**

Run: `php artisan test --filter=TanggalHasilKoreksiBagianTest`
Expected: PASS (1 test)

- [ ] **Step 6: Jalankan test yang AKAN pecah karenanya**

Run: `php artisan test --filter=OperatorDocumentRowTest`
Expected: **2 test GAGAL** —
`test_dates_kolom_non_cast_diparse_defensif` dan
`test_dates_kolom_non_cast_tak_terparse_jadi_strip`.

Ini **diharapkan**, bukan kejutan. Kedua test itu memakai
`tanggal_hasil_koreksi_bagian` justru KARENA belum ber-cast, dan komentarnya sendiri
sudah meramalkan akan pecah. Step berikutnya memperbaikinya.

Kalau salah satunya justru HIJAU, berhenti dan laporkan: berarti cast belum benar-benar
terpasang.

- [ ] **Step 7: Tulis ulang kedua test itu**

Di `tests/Unit/OperatorDocumentRowTest.php`, tambahkan helper ini tepat di bawah method
`baris()` (sekitar baris 60):

```php
    /**
     * Salinan dokumen dengan cast satu kolom DILEPAS, supaya jalur parse defensif
     * DocumentRow::formatDates() (cabang untuk nilai yang BUKAN DateTimeInterface)
     * benar-benar tereksekusi.
     *
     * Dulu helper ini tak perlu: cukup memakai kolom tanggal yang kebetulan belum
     * ber-cast. tanggal_kembali_ke_bagian dipakai sampai 2026-08-06, lalu
     * tanggal_hasil_koreksi_bagian sampai 2026-08-07 — keduanya kini ber-cast
     * 'datetime', dan pemeriksaan seluruh peta formatDates() terhadap $casts
     * menemukan TIDAK ADA lagi kolom tanggal non-cast yang tersisa.
     *
     * Melepas cast secara eksplisit lebih jujur daripada berburu kolom yang
     * kebetulan terlupakan — kolom semacam itu akan hilang lagi pada migrasi
     * berikutnya, dan tesnya ikut pindah-pindah tanpa alasan yang sebenarnya.
     *
     * setRawAttributes() TIDAK cukup sendirian: cast diterapkan saat atribut
     * DIBACA, bukan saat ditulis, jadi nilai mentah tetap akan dikonversi Carbon
     * (atau melempar exception untuk nilai tak sah) sebelum formatDates() sempat
     * melihatnya.
     */
    private function dokumenTanpaCastKoreksiBagian(mixed $nilai): Dokumen
    {
        $asli = $this->buatDokumen();

        // Kunci cast di-unset secara hardcode (bukan lewat properti) supaya ikut
        // terbawa saat Eloquent membuat instance baru lewat newFromBuilder().
        $prototipe = new class extends Dokumen
        {
            public function getCasts(): array
            {
                $casts = parent::getCasts();
                unset($casts['tanggal_hasil_koreksi_bagian']);

                return $casts;
            }
        };

        $dokumen = $prototipe->newQuery()->findOrFail($asli->id);
        $dokumen->tanggal_hasil_koreksi_bagian = $nilai;

        return $dokumen;
    }
```

Lalu ganti kedua test lama dengan versi ini:

```php
    public function test_dates_kolom_non_cast_diparse_defensif(): void
    {
        // Nilai mentah berupa string (bukan Carbon) harus tetap terformat benar
        // lewat cabang parse defensif. Lihat dokumenTanpaCastKoreksiBagian() untuk
        // alasan cast dilepas alih-alih memakai kolom yang kebetulan non-cast.
        $dokumen = $this->dokumenTanpaCastKoreksiBagian('2026-07-05 14:00:00');

        $row = $this->baris($dokumen);

        $this->assertSame('05/07/2026 14:00', $row['dates']['tanggal_hasil_koreksi_bagian']);
    }

    public function test_dates_kolom_non_cast_tak_terparse_jadi_strip(): void
    {
        // Nilai mentah yang tidak bisa di-parse Carbon → fallback '-', BUKAN
        // exception yang menjatuhkan seluruh baris tabel.
        $dokumen = $this->dokumenTanpaCastKoreksiBagian('bukan-tanggal-valid');

        $row = $this->baris($dokumen);

        $this->assertSame('-', $row['dates']['tanggal_hasil_koreksi_bagian']);
    }
```

- [ ] **Step 8: Jalankan ulang, pastikan LULUS**

Run: `php artisan test --filter=OperatorDocumentRowTest`
Expected: PASS (semua test hijau)

Run: `php artisan test --filter=TanggalKembaliKeBagianTest`
Expected: PASS — kolom kembarannya tidak terganggu.

- [ ] **Step 9: Buktikan test menggigit**

Dua mutasi, satu per satu (pulihkan sebelum lanjut):

1. Di migrasi, ubah nama kolom di `Schema::table` jadi `'tanggal_hasil_koreksi_xxx'`,
   lalu `php artisan test --filter=TanggalHasilKoreksiBagianTest` → harus **GAGAL**.
   (Test memakai `RefreshDatabase` sehingga migrasi dijalankan ulang tiap test.)
2. Di `App\Support\DocumentRow::formatDates()`, hapus blok
   `try { ... } catch (\Throwable $e) { $dates[$col] = '-'; }`, sisakan hanya baris
   `$dates[$col] = \Illuminate\Support\Carbon::parse($value)->format($format);` →
   `php artisan test --filter=test_dates_kolom_non_cast_tak_terparse_jadi_strip` harus
   **GAGAL** dengan exception.

   Mutasi kedua ini yang penting: ia membuktikan helper `dokumenTanpaCastKoreksiBagian()`
   benar-benar menyentuh jalur parse defensif. Kalau ternyata tetap hijau, helper-nya
   gagal melepas cast — nilai sudah jadi Carbon sebelum `formatDates()` melihatnya, dan
   kedua test itu hampa. Berhenti dan perbaiki helper-nya sebelum lanjut.

Mutasi terhadap `$fillable` sengaja TIDAK ada di task ini: tak ada test di sini yang
menulis ke kolom itu lewat mass assignment. Mutasi itu ada di Task 2 Step 7.

Setelah keduanya: pulihkan, jalankan ulang kedua filter (hijau), lalu pastikan
`git diff` hanya berisi perubahan yang direncanakan — tanpa sisa mutasi.

- [ ] **Step 10: Commit**

```bash
git add database/migrations/2026_08_07_100000_add_tanggal_hasil_koreksi_bagian_to_dokumens.php
git add app/Models/Dokumen.php
git add tests/Unit/OperatorDocumentRowTest.php
git add tests/Feature/TanggalHasilKoreksiBagianTest.php
git commit -m "feat(dokumen): kolom tanggal_hasil_koreksi_bagian, pasangan tanggal_kembali_ke_bagian

Kolom sudah lama ada di katalog (config/document_columns.php:51) sehingga
muncul sebagai pilihan kolom, tapi kolom databasenya tak pernah dibuat -
nol migrasi, selalu tampil '-'. Kondisi yang persis sama dengan
tanggal_kembali_ke_bagian sebelum diperbaiki 2026-08-06.

Dua test jalur parse defensif di OperatorDocumentRowTest ditulis ulang.
Keduanya memakai kolom ini justru KARENA belum ber-cast, dan komentarnya
sendiri meramalkan harus pindah begitu kolomnya dibuat. Tapi kolom ini
adalah kolom tanggal non-cast TERAKHIR di peta formatDates - tak ada
tempat pindah. Karena itu cast-nya dilepas eksplisit lewat subclass
anonim, bukan berburu kolom yang kebetulan terlupakan."
```

---

## Task 2: Isi otomatis & tampilkan

**Files:**
- Modify: `app/Http/Controllers/DocumentHandlerController.php` (`receiveBackFromBagian()`, ~baris 303-311)
- Modify: `app/Http/Controllers/TeamVerifikasiController.php` (daftar select, ~baris 227)
- Modify: `public/js/document-tabulator.js` (`NON_EDITABLE_FIELDS`, baris 107)
- Test: `tests/Feature/TanggalHasilKoreksiBagianTest.php` (tambah 5 test)

**Interfaces:**
- Consumes: kolom `dokumens.tanggal_hasil_koreksi_bagian` ber-cast `datetime` (Task 1)
- Produces: —

- [ ] **Step 1: Tulis 5 test yang gagal**

Tambahkan `use` berikut di bagian atas `tests/Feature/TanggalHasilKoreksiBagianTest.php`:

```php
use App\Models\Bagian;
use App\Models\Dokumen;
use App\Models\User;
```

Tambahkan `setUp()`, helper, dan kelima test ini ke dalam kelas:

```php
    protected function setUp(): void
    {
        parent::setUp();

        // buildVerifikasiQuery() memakai fungsi MySQL (REGEXP, SUBSTRING_INDEX) pada
        // ORDER BY nomor_agenda — tak tersedia di SQLite. Polyfill sama dengan
        // TanggalKembaliKeBagianTest.
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

    /** Dokumen yang SEDANG dikembalikan ke Bagian — kondisi awal terima-balik. */
    private function dokumenDikembalikanKeBagian(string $bagian = 'KEU'): Dokumen
    {
        return Dokumen::create([
            'nomor_agenda'    => '1_2026',
            'bulan'           => 'Agustus',
            'tahun'           => 2026,
            'tanggal_masuk'   => '2026-08-01',
            'status'          => 'returned_to_bidang',
            'created_by'      => 'operator',
            'current_handler' => 'team_verifikasi',
            'return_source'   => $bagian,
            'bagian'          => $bagian,
        ]);
    }

    private function verifikasi(): User
    {
        return User::factory()->create(['role' => 'team_verifikasi']);
    }

    public function test_terima_balik_dari_bagian_mengisi_stempel(): void
    {
        Bagian::create(['kode' => 'KEU', 'nama' => 'Keuangan']);
        $dokumen = $this->dokumenDikembalikanKeBagian();

        $this->assertNull($dokumen->tanggal_hasil_koreksi_bagian);

        $this->actingAs($this->verifikasi())
            ->patchJson(route('documents.handler.update', $dokumen), [
                'target_handler' => 'team_verifikasi',
            ])
            ->assertOk();

        $this->assertNotNull(
            $dokumen->fresh()->tanggal_hasil_koreksi_bagian,
            'Terima-balik dari Bagian tidak mengisi stempel hasil koreksi.'
        );
    }

    public function test_forward_biasa_ke_verifikasi_tidak_mengisi_stempel(): void
    {
        // INI yang membuat kolomnya bermakna. Kalau setiap perpindahan ke Verifikasi
        // ikut mengisi, angkanya tak menandakan koreksi apa pun.
        $dokumen = Dokumen::create([
            'nomor_agenda'    => '2_2026',
            'bulan'           => 'Agustus',
            'tahun'           => 2026,
            'tanggal_masuk'   => '2026-08-01',
            'status'          => 'sedang diproses',
            'created_by'      => 'operator',
            'current_handler' => 'operator',
            'bagian'          => 'KEU',
        ]);

        $this->actingAs(User::factory()->create(['role' => 'operator']))
            ->patchJson(route('documents.handler.update', $dokumen), [
                'target_handler' => 'team_verifikasi',
            ])
            ->assertOk();

        $segar = $dokumen->fresh();
        $this->assertSame('team_verifikasi', $segar->current_handler, 'Forward-nya sendiri harus tetap berhasil.');
        $this->assertNull(
            $segar->tanggal_hasil_koreksi_bagian,
            'Forward biasa operator→verifikasi tidak boleh mengisi stempel hasil koreksi.'
        );
    }

    public function test_koreksi_kedua_menimpa_stempel_pertama(): void
    {
        // Keputusan user: kolom selalu menunjuk siklus koreksi TERAKHIR, sepasang
        // dengan tanggal_kembali_ke_bagian yang juga ditimpa tiap pengembalian.
        Bagian::create(['kode' => 'KEU', 'nama' => 'Keuangan']);
        $dokumen = $this->dokumenDikembalikanKeBagian();

        // Siklus 1 sudah terjadi jauh sebelumnya.
        $stempelLama = '2026-01-02 03:04:05';
        $dokumen->forceFill(['tanggal_hasil_koreksi_bagian' => $stempelLama])->save();

        $this->actingAs($this->verifikasi())
            ->patchJson(route('documents.handler.update', $dokumen), [
                'target_handler' => 'team_verifikasi',
            ])
            ->assertOk();

        $baru = $dokumen->fresh()->tanggal_hasil_koreksi_bagian;

        $this->assertNotNull($baru);
        $this->assertNotSame(
            $stempelLama,
            $baru->format('Y-m-d H:i:s'),
            'Stempel lama tidak ditimpa — kolom akan menunjuk siklus koreksi yang salah.'
        );
    }

    public function test_nilai_ikut_terkirim_di_endpoint_tabel_verifikasi(): void
    {
        // buildVerifikasiQuery() memakai daftar select EKSPLISIT. Kolom yang tak
        // disebut di sana sampai ke DTO sebagai null meski datanya ada di database —
        // terbukti saat tanggal_kembali_ke_bagian baru dibuat: DB terisi, sel '-'.
        Bagian::create(['kode' => 'KEU', 'nama' => 'Keuangan']);

        $dokumen = $this->dokumenDikembalikanKeBagian();
        $dokumen->forceFill(['tanggal_hasil_koreksi_bagian' => '2026-08-07 09:30:00'])->save();

        $response = $this->actingAs($this->verifikasi())
            ->getJson(route('documents.verifikasi.data'));

        $response->assertOk();

        $baris = collect($response->json('data'))->firstWhere('id', $dokumen->id);

        $this->assertNotNull($baris, 'Dokumen tidak muncul di endpoint tabel.');
        $this->assertNotNull(
            $baris['tanggal_hasil_koreksi_bagian'],
            'Kolom tidak ikut di-select buildVerifikasiQuery() — sel akan selalu tampil "-".'
        );
        $this->assertSame('07/08/2026 09:30', $baris['dates']['tanggal_hasil_koreksi_bagian']);
    }

    public function test_kolom_ditandai_hanya_baca_di_klien(): void
    {
        // Diisi otomatis; bila selnya tampak bisa diedit, user akan mengetik lalu
        // ditolak server — persis keluhan yang sudah diperbaiki di kolom lain.
        $js = file_get_contents(public_path('js/document-tabulator.js'));

        $this->assertMatchesRegularExpression(
            '/const NON_EDITABLE_FIELDS = \[[^\]]*\'tanggal_hasil_koreksi_bagian\'/',
            $js,
            'tanggal_hasil_koreksi_bagian tidak ada di NON_EDITABLE_FIELDS — selnya akan tampak bisa diedit.'
        );
    }
```

- [ ] **Step 2: Jalankan test, pastikan GAGAL**

Run: `php artisan test --filter=TanggalHasilKoreksiBagianTest`
Expected: 4 test GAGAL (`test_forward_biasa...` mungkin sudah hijau karena memang
belum ada yang mengisi — itu wajar; ia baru bermakna setelah Step 3).

- [ ] **Step 3: Stempel di `receiveBackFromBagian()`**

Di `app/Http/Controllers/DocumentHandlerController.php`, di dalam method
`receiveBackFromBagian()`, cari blok:

```php
        $dokumen->update([
            'current_handler' => 'team_verifikasi',
            'status' => 'sedang diproses',
            'current_stage' => 'reviewer',
            'return_source' => null,
            'return_reason' => null,
            'returned_at' => null,
            'last_action_status' => 'returned_from_bidang',
        ]);
```

Ubah menjadi:

```php
        $dokumen->update([
            'current_handler' => 'team_verifikasi',
            'status' => 'sedang diproses',
            'current_stage' => 'reviewer',
            'return_source' => null,
            'return_reason' => null,
            'returned_at' => null,
            'last_action_status' => 'returned_from_bidang',
            // Stempel hasil koreksi Bagian. Ditaruh DI SINI, bukan di update(),
            // karena method ini hanya terpanggil lewat gerbang
            // $canReceiveReturnedBagian yang menuntut status dokumen
            // 'returned_to_bidang' — forward biasa operator→verifikasi jatuh ke
            // moveDirectlyToTeamVerifikasi() dan tidak boleh ikut terstempel.
            //
            // Sengaja DITIMPA tiap siklus koreksi (keputusan user 2026-08-07):
            // kolomnya sepasang dengan tanggal_kembali_ke_bagian yang juga ditimpa
            // tiap pengembalian, jadi keduanya selalu menunjuk siklus yang sama.
            'tanggal_hasil_koreksi_bagian' => now(),
        ]);
```

- [ ] **Step 4: Ikutkan di daftar select verifikasi**

Di `app/Http/Controllers/TeamVerifikasiController.php`, cari baris:

```php
                'dokumens.tanggal_kembali_ke_bagian',
```

Tambahkan tepat di bawahnya:

```php
                'dokumens.tanggal_hasil_koreksi_bagian',
```

Komentar "WAJIB ikut di-select" yang sudah ada tepat di atas baris itu berlaku untuk
keduanya — jangan digandakan.

- [ ] **Step 5: Tandai hanya-baca di klien**

Di `public/js/document-tabulator.js` baris 107, ubah:

```js
  const NON_EDITABLE_FIELDS = ['tanggal_masuk', 'status', 'nomor_mirror', 'keterangan', 'tanggal_kembali_ke_bagian'];
```

menjadi:

```js
  // Kolom yang diisi OTOMATIS oleh server — dibuat hanya-baca supaya user tak
  // mengetik lalu ditolak server.
  const NON_EDITABLE_FIELDS = ['tanggal_masuk', 'status', 'nomor_mirror', 'keterangan', 'tanggal_kembali_ke_bagian', 'tanggal_hasil_koreksi_bagian'];
```

- [ ] **Step 6: Jalankan test, pastikan LULUS**

Run: `php artisan test --filter=TanggalHasilKoreksiBagianTest`
Expected: PASS (6 test)

- [ ] **Step 7: Buktikan test menggigit**

Empat mutasi, satu per satu (pulihkan sebelum lanjut):

1. Hapus baris `'tanggal_hasil_koreksi_bagian' => now(),` dari `receiveBackFromBagian()`
   → `test_terima_balik_dari_bagian_mengisi_stempel` dan
   `test_koreksi_kedua_menimpa_stempel_pertama` harus **GAGAL**.
2. Pindahkan baris `'tanggal_hasil_koreksi_bagian' => now(),` ke dalam
   `moveDirectlyToTeamVerifikasi()` (pada `$dokumen->update([...])` di sana)
   → `test_forward_biasa_ke_verifikasi_tidak_mengisi_stempel` harus **GAGAL**.
   Mutasi ini yang membuktikan gerbangnya benar-benar dijaga, bukan kebetulan.
3. Hapus baris `'dokumens.tanggal_hasil_koreksi_bagian',` dari daftar select
   → `test_nilai_ikut_terkirim_di_endpoint_tabel_verifikasi` harus **GAGAL**.
4. Hapus `'tanggal_hasil_koreksi_bagian'` dari `NON_EDITABLE_FIELDS`
   → `test_kolom_ditandai_hanya_baca_di_klien` harus **GAGAL**.
5. Di `app/Models/Dokumen.php`, hapus `'tanggal_hasil_koreksi_bagian',` dari `$fillable`
   (biarkan `$casts` apa adanya) → `test_terima_balik_dari_bagian_mengisi_stempel` harus
   **GAGAL**. Ini membuktikan `$fillable` benar-benar dibutuhkan: tanpanya `update()`
   mengabaikan kolom itu **diam-diam**, tanpa error apa pun.

Setelah kelimanya: pulihkan, jalankan ulang filter (hijau), `git diff` bersih dari
sisa mutasi.

- [ ] **Step 8: Commit**

```bash
git add app/Http/Controllers/DocumentHandlerController.php
git add app/Http/Controllers/TeamVerifikasiController.php
git add public/js/document-tabulator.js
git add tests/Feature/TanggalHasilKoreksiBagianTest.php
git commit -m "feat(verifikasi): stempel otomatis tanggal hasil koreksi bagian

Saat Tim Verifikasi menarik kembali dokumen hasil revisi lewat dropdown
Pengurus Dokumen, tanggal & jam kini tercatat di kolom Tgl Hasil Koreksi
Bagian.

Nol logika gerbang baru: update() sudah punya \$canReceiveReturnedBagian
yang menuntut userRole verifikasi + target verifikasi + status
returned_to_bidang sekaligus. Syarat ketiga itulah yang membuat forward
biasa operator->verifikasi TIDAK ikut terstempel - kalau setiap
perpindahan ke Verifikasi mengisi, angkanya tak menandakan koreksi apa
pun. Dijaga test khusus, dan mutasi yang memindahkan stempel ke
moveDirectlyToTeamVerifikasi() memerahkannya.

Kolom diikutkan ke daftar select EKSPLISIT buildVerifikasiQuery() -
tanpa itu database terisi tapi sel tetap '-', persis bug yang baru
diperbaiki untuk kolom kembarannya. Ditandai hanya-baca di klien karena
diisi otomatis."
```

---

## Task 3: Suite penuh, deploy, QA

**Files:** tidak ada perubahan kode (kecuali perbaikan bila ada yang merah)

- [ ] **Step 1: Jalankan suite penuh**

Run: `php artisan test`
Expected: seluruh suite hijau (patokan: 381 test sebelum pekerjaan ini).

CLAUDE.md aturan 3: suite penuh wajib hijau **sebelum push**. Kalau ada yang merah dan
tak berkaitan dengan pekerjaan ini, laporkan ke user — jangan diam-diam dilewati.

- [ ] **Step 2: Push**

```bash
git push origin codinggemini
```

- [ ] **Step 3: Deploy di server — JALANKAN MIGRASI**

```bash
git pull
php artisan migrate --force
php artisan route:clear && php artisan view:clear && php artisan config:clear
```

**`php artisan migrate` WAJIB** — beda dari deploy-deploy sebelumnya yang tidak menyentuh
skema. Tanpa itu kolomnya tidak ada di produksi dan fitur ini mati diam-diam.

Migrasinya idempoten dan hanya `ADD COLUMN` nullable — tidak menyentuh data yang ada.
JANGAN jalankan `migrate:fresh`, `migrate:refresh`, atau `migrate:rollback`.

Verifikasi kolomnya benar-benar terbuat:

```bash
php artisan tinker --execute="echo Schema::hasColumn('dokumens','tanggal_hasil_koreksi_bagian') ? 'ADA' : 'TIDAK ADA';"
```

- [ ] **Step 4: QA browser**

Login produksi sebagai akun Verifikasi (kredensial di memori `browser-qa-access`),
buka `/documents/verifikasi`, lalu:

1. Tambahkan kolom **"Tgl Hasil Koreksi Bagian"** lewat modal Kustomisasi Kolom;
   pastikan ia muncul di tabel (bukan hilang diam-diam).
2. Cari dokumen berstatus dikembalikan ke Bagian. Kalau tidak ada, kembalikan satu
   dokumen uji ke Bagian lebih dulu lewat dropdown Pengurus Dokumen.
3. Ubah dropdown Pengurus Dokumen dokumen itu kembali ke **Tim Verifikasi**.
4. Pastikan sel "Tgl Hasil Koreksi Bagian" **langsung terisi** tanggal & jam hari ini
   dengan format `dd/mm/yyyy HH:MM` — tanpa perlu refresh.
5. Pastikan selnya **tidak bisa diedit** (arahkan sel aktif ke sana, tekan Enter —
   tidak boleh masuk mode edit).

> **Catatan:** langkah 2-3 mengubah data dokumen sungguhan di produksi. Pakai dokumen
> uji, dan catat nomor agendanya di laporan supaya bisa ditelusuri.

- [ ] **Step 5: Laporkan ke user**

Sebutkan eksplisit: apa yang sudah diuji, apa yang belum, dan nomor agenda dokumen yang
dipakai untuk uji coba di produksi.

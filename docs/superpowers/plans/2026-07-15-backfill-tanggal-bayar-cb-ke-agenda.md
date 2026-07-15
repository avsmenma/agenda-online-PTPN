# Backfill Tanggal Bayar Cash Bank → Agenda Online Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Membangun satu Artisan command sekali-jalan yang mengisi `tanggal_dibayar` dokumen Agenda dari tanggal bank keluar Cash Bank (satu arah, hanya bila kosong).

**Architecture:** Logika inti di `App\Services\BackfillTanggalBayarService` (mudah diuji, tanpa console), dibungkus command tipis `dokumen:backfill-tanggal-bayar`. Service membaca `bank_keluars` via koneksi `cash_bank_new` yang sudah ada, meng-agregasi tanggal TERAWAL per dokumen di memori, lalu menulis ke `dokumens` dengan raw `DB::table()->update()` (tidak memicu `DokumenObserver`, jadi tak ada push-balik ke Cash Bank). Sync dua arah live yang sudah ada tidak diubah.

**Tech Stack:** Laravel 12, PHP 8.3, MySQL 8 (produksi), SQLite in-memory (test). Koneksi `cash_bank_new` (Agenda→CashBank) sudah terdaftar di `config/database.php`.

## Global Constraints

- PHP 8.3, Laravel 12. Ikuti pola command yang ada (`app/Console/Commands/SyncCashBankCommand.php`) — Laravel 12 meng-auto-discover command, tidak perlu registrasi manual.
- **REVISI pasca-review (lihat `.superpowers/sdd/task-1-revision-brief.md` untuk kode lengkap):** (a) saat mengisi, set `tanggal_dibayar` **dan** `status_pembayaran='sudah_dibayar'` (bukan hanya tanggal); auto-forward yang terpicu diterima sebagai keputusan bisnis. (b) Matcher `nomor_agenda` menangani `agenda_tahun` komposit `'{nomor}_{tahun}'` (cocok ke nomor_agenda+tahun) maupun polos (hanya bila unik; >1 → ambigu dilewati), karena `nomor_agenda` unik komposit `(nomor_agenda, created_by)`.
- `git add` **per-file** (jangan `git add .`/`-A`). Pesan commit **Bahasa Indonesia**.
- Nama koneksi Cash Bank: `config('sync.cashbank_connection', 'cash_bank_new')` (pola identik `DokumenSyncService`).
- **Hanya** sentuh field `tanggal_dibayar`. Jangan ubah `status_pembayaran` atau field lain.
- **Isi hanya bila kosong.** Konflik (Agenda sudah terisi & beda) → dilewati, dicatat. Tidak pernah menimpa.
- Multi-baris per dokumen → pakai tanggal **TERAWAL** (`MIN`).
- Saat mengisi, raw update **hanya** menulis `tanggal_dibayar` — **jangan** menyetel `updated_at`. Menyetel `updated_at` akan membuat fallback poller `dokumen:sync-cashbank --since` menarik dokumen itu dan mendorongnya balik ke Cash Bank (persis push-balik yang harus dihindari).
- Tabel `sync_logs`: `direction` enum hanya `ao_to_cb|cb_to_ao`; `status` enum `success|failed|skipped_no_change|conflict_resolved`. Konflik dicatat sebagai `direction='cb_to_ao'`, `status='conflict_resolved'`, `source_wins='agenda_online'`.
- **JANGAN** jalankan run sungguhan ke DB produksi tanpa izin eksplisit user. Verifikasi produksi hanya lewat `--dry-run` (lihat bagian Verifikasi Akhir).

---

### Task 1: BackfillTanggalBayarService (logika inti)

**Files:**
- Create: `app/Services/BackfillTanggalBayarService.php`
- Test: `tests/Feature/BackfillTanggalBayarTest.php`

**Interfaces:**
- Consumes: koneksi `cash_bank_new` (tabel `bank_keluars`: `id_bank_keluar`, `dokumen_id`, `no_agenda`, `agenda_tahun`, `tanggal`), model `App\Models\Dokumen`, `App\Models\SyncLog`.
- Produces:
  ```php
  BackfillTanggalBayarService::run(bool $dryRun = false, ?int $limit = null): array
  // return: [
  //   'diperiksa' => int, 'diisi' => int, 'sama' => int, 'konflik' => int,
  //   'tidak_ketemu' => int,
  //   'konflik_detail' => array<int,array{dokumen_id:int,nomor_agenda:?string,tanggal_agenda:string,tanggal_cashbank:string}>,
  // ]
  ```

- [ ] **Step 1: Tulis test yang gagal**

Buat `tests/Feature/BackfillTanggalBayarTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Services\BackfillTanggalBayarService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BackfillTanggalBayarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Koneksi cash_bank_new → sqlite in-memory terpisah (mensimulasikan DB kedua).
        config()->set('database.connections.cash_bank_new', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]);
        DB::purge('cash_bank_new');

        Schema::connection('cash_bank_new')->dropIfExists('bank_keluars');
        Schema::connection('cash_bank_new')->create('bank_keluars', function (Blueprint $t) {
            $t->id('id_bank_keluar');
            $t->unsignedBigInteger('dokumen_id')->nullable();
            $t->string('no_agenda')->nullable();
            $t->string('agenda_tahun')->nullable();
            $t->date('tanggal')->nullable();
            $t->timestamps();
        });
    }

    private function buatDokumen(array $override = []): int
    {
        return DB::table('dokumens')->insertGetId(array_merge([
            'nomor_agenda'  => '0001',
            'bulan'         => 'Januari',
            'tahun'         => 2026,
            'tanggal_masuk' => '2026-01-01 00:00:00',
            'nomor_spp'     => 'SPP-1',
            'tanggal_spp'   => '2026-01-01 00:00:00',
            'uraian_spp'    => 'Uji',
            'nilai_rupiah'  => 1000,
            'kategori'      => 'Umum',
            'jenis_dokumen' => 'Invoice',
            'status'        => 'draft',
            'tanggal_dibayar' => null,
            'created_at'    => now(),
            'updated_at'    => now(),
        ], $override));
    }

    private function buatBankKeluar(array $override = []): void
    {
        DB::connection('cash_bank_new')->table('bank_keluars')->insert(array_merge([
            'dokumen_id'   => null,
            'no_agenda'    => null,
            'agenda_tahun' => null,
            'tanggal'      => null,
            'created_at'   => now(),
            'updated_at'   => now(),
        ], $override));
    }

    private function service(): BackfillTanggalBayarService
    {
        return app(BackfillTanggalBayarService::class);
    }

    public function test_mengisi_tanggal_dibayar_yang_kosong(): void
    {
        $id = $this->buatDokumen(['nomor_agenda' => '0001', 'tanggal_dibayar' => null]);
        $this->buatBankKeluar(['dokumen_id' => $id, 'tanggal' => '2026-03-10']);

        $s = $this->service()->run(false);

        $this->assertSame('2026-03-10', substr((string) DB::table('dokumens')->where('id', $id)->value('tanggal_dibayar'), 0, 10));
        $this->assertSame(1, $s['diisi']);
        $this->assertSame(0, $s['konflik']);
    }

    public function test_memakai_tanggal_terawal(): void
    {
        $id = $this->buatDokumen(['nomor_agenda' => '0002', 'tanggal_dibayar' => null]);
        $this->buatBankKeluar(['dokumen_id' => $id, 'tanggal' => '2026-05-01']);
        $this->buatBankKeluar(['dokumen_id' => $id, 'tanggal' => '2026-03-02']);

        $this->service()->run(false);

        $this->assertSame('2026-03-02', substr((string) DB::table('dokumens')->where('id', $id)->value('tanggal_dibayar'), 0, 10));
    }

    public function test_tidak_menimpa_dan_mencatat_konflik(): void
    {
        $id = $this->buatDokumen(['nomor_agenda' => '0003', 'tanggal_dibayar' => '2026-01-01']);
        $this->buatBankKeluar(['dokumen_id' => $id, 'tanggal' => '2026-03-10']);

        $s = $this->service()->run(false);

        $this->assertSame('2026-01-01', substr((string) DB::table('dokumens')->where('id', $id)->value('tanggal_dibayar'), 0, 10));
        $this->assertSame(1, $s['konflik']);
        $this->assertSame(1, DB::table('sync_logs')->where('direction', 'cb_to_ao')->where('status', 'conflict_resolved')->count());
    }

    public function test_cocok_via_nomor_agenda_jika_dokumen_id_kosong(): void
    {
        $id = $this->buatDokumen(['nomor_agenda' => '0009', 'tanggal_dibayar' => null]);
        $this->buatBankKeluar(['dokumen_id' => null, 'agenda_tahun' => '0009', 'tanggal' => '2026-04-04']);

        $s = $this->service()->run(false);

        $this->assertSame('2026-04-04', substr((string) DB::table('dokumens')->where('id', $id)->value('tanggal_dibayar'), 0, 10));
        $this->assertSame(1, $s['diisi']);
    }

    public function test_dry_run_tidak_menulis(): void
    {
        $id = $this->buatDokumen(['nomor_agenda' => '0005', 'tanggal_dibayar' => null]);
        $this->buatBankKeluar(['dokumen_id' => $id, 'tanggal' => '2026-03-10']);

        $s = $this->service()->run(true);

        $this->assertNull(DB::table('dokumens')->where('id', $id)->value('tanggal_dibayar'));
        $this->assertSame(1, $s['diisi']); // "akan diisi"
        $this->assertSame(0, DB::table('sync_logs')->count());
    }
}
```

- [ ] **Step 2: Jalankan test, pastikan gagal**

Run: `php artisan test --filter=BackfillTanggalBayarTest`
Expected: FAIL — `Class "App\Services\BackfillTanggalBayarService" not found`.

- [ ] **Step 3: Implementasikan service**

Buat `app/Services/BackfillTanggalBayarService.php`:

```php
<?php

namespace App\Services;

use App\Models\Dokumen;
use App\Models\SyncLog;
use Illuminate\Support\Facades\DB;

/**
 * Backfill sekali-jalan: isi tanggal_dibayar dokumen Agenda dari tanggal
 * bank keluar Cash Bank. Satu arah (CB → AO), hanya bila kosong, pakai
 * tanggal TERAWAL bila ada beberapa baris. Tidak menimpa data yang sudah ada.
 */
class BackfillTanggalBayarService
{
    private function cbConnection(): string
    {
        return config('sync.cashbank_connection', 'cash_bank_new');
    }

    /**
     * @return array{diperiksa:int,diisi:int,sama:int,konflik:int,tidak_ketemu:int,konflik_detail:array<int,array<string,mixed>>}
     */
    public function run(bool $dryRun = false, ?int $limit = null): array
    {
        // 1. Muat peta dokumen Agenda ke memori (id → data, dan nomor_agenda → [id]).
        $dokById    = [];
        $idsByNomor = [];

        Dokumen::query()
            ->select(['id', 'nomor_agenda', 'tanggal_dibayar'])
            ->chunk(500, function ($rows) use (&$dokById, &$idsByNomor) {
                foreach ($rows as $d) {
                    $dokById[$d->id] = $d;
                    if (!empty($d->nomor_agenda)) {
                        $idsByNomor[$d->nomor_agenda][] = $d->id;
                    }
                }
            });

        // 2. Agregasi tanggal TERAWAL per dokumen dari bank_keluars Cash Bank.
        $earliestByDokId = [];
        $unmatchedKeys   = [];

        DB::connection($this->cbConnection())
            ->table('bank_keluars')
            ->select(['id_bank_keluar', 'dokumen_id', 'no_agenda', 'agenda_tahun', 'tanggal'])
            ->whereNotNull('tanggal')
            ->where('tanggal', '!=', '')
            ->orderBy('id_bank_keluar')
            ->chunk(1000, function ($rows) use (&$earliestByDokId, &$unmatchedKeys, $dokById, $idsByNomor) {
                foreach ($rows as $r) {
                    $tgl = substr((string) $r->tanggal, 0, 10);
                    if ($tgl === '' || $tgl === '0000-00-00') {
                        continue;
                    }

                    $dokId = null;
                    if (!empty($r->dokumen_id) && isset($dokById[$r->dokumen_id])) {
                        $dokId = (int) $r->dokumen_id;
                    } else {
                        $nomor = $r->agenda_tahun ?: $r->no_agenda;
                        if (!empty($nomor) && isset($idsByNomor[$nomor])) {
                            if (count($idsByNomor[$nomor]) === 1) {
                                $dokId = $idsByNomor[$nomor][0];
                            } else {
                                $unmatchedKeys['agenda:' . $nomor] = true; // ambigu
                                continue;
                            }
                        }
                    }

                    if ($dokId === null) {
                        $key = !empty($r->dokumen_id)
                            ? 'id:' . $r->dokumen_id
                            : 'agenda:' . ($r->agenda_tahun ?: $r->no_agenda ?: '?');
                        $unmatchedKeys[$key] = true;
                        continue;
                    }

                    if (!isset($earliestByDokId[$dokId]) || $tgl < $earliestByDokId[$dokId]) {
                        $earliestByDokId[$dokId] = $tgl;
                    }
                }
            });

        // 3. Terapkan keputusan per dokumen.
        $summary = [
            'diperiksa'      => 0,
            'diisi'          => 0,
            'sama'           => 0,
            'konflik'        => 0,
            'tidak_ketemu'   => count($unmatchedKeys),
            'konflik_detail' => [],
        ];

        $processed = 0;
        foreach ($earliestByDokId as $dokId => $earliest) {
            if ($limit !== null && $processed >= $limit) {
                break;
            }
            $processed++;
            $summary['diperiksa']++;

            $dok = $dokById[$dokId];
            $existing = $dok->tanggal_dibayar ? substr((string) $dok->tanggal_dibayar, 0, 10) : null;

            if ($existing === null || $existing === '' || $existing === '0000-00-00') {
                if (!$dryRun) {
                    // Sengaja TIDAK menyentuh updated_at. Fallback poller
                    // `dokumen:sync-cashbank --since` mencari Dokumen dengan
                    // updated_at terbaru lalu mendorongnya balik ke Cash Bank;
                    // membiarkan updated_at apa adanya mencegah push-balik
                    // (raw update sudah menghindari jalur DokumenObserver).
                    DB::table('dokumens')->where('id', $dokId)->update([
                        'tanggal_dibayar' => $earliest,
                    ]);
                }
                $summary['diisi']++;
            } elseif ($existing === $earliest) {
                $summary['sama']++;
            } else {
                $summary['konflik']++;
                $summary['konflik_detail'][] = [
                    'dokumen_id'       => $dokId,
                    'nomor_agenda'     => $dok->nomor_agenda,
                    'tanggal_agenda'   => $existing,
                    'tanggal_cashbank' => $earliest,
                ];

                if (!$dryRun) {
                    SyncLog::create([
                        'dokumen_id'      => $dokId,
                        'direction'       => 'cb_to_ao',
                        'status'          => 'conflict_resolved',
                        'fields_synced'   => [],
                        'conflict_fields' => ['tanggal_dibayar'],
                        'source_wins'     => 'agenda_online',
                        'synced_at'       => now(),
                    ]);
                }
            }
        }

        return $summary;
    }
}
```

- [ ] **Step 4: Jalankan test, pastikan lulus**

Run: `php artisan test --filter=BackfillTanggalBayarTest`
Expected: PASS (5 test, semua hijau).

- [ ] **Step 5: Commit**

```bash
git add app/Services/BackfillTanggalBayarService.php
git add tests/Feature/BackfillTanggalBayarTest.php
git commit -m "feat(sync): service backfill tanggal bayar Cash Bank ke Agenda

Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>"
```

---

### Task 2: Command dokumen:backfill-tanggal-bayar

**Files:**
- Create: `app/Console/Commands/BackfillTanggalBayarCommand.php`
- Test: `tests/Feature/BackfillTanggalBayarCommandTest.php`

**Interfaces:**
- Consumes: `BackfillTanggalBayarService::run()` (dari Task 1).
- Produces: command signature `dokumen:backfill-tanggal-bayar {--dry-run} {--limit=}`.

- [ ] **Step 1: Tulis test yang gagal**

Buat `tests/Feature/BackfillTanggalBayarCommandTest.php`:

```php
<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BackfillTanggalBayarCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.connections.cash_bank_new', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]);
        DB::purge('cash_bank_new');

        Schema::connection('cash_bank_new')->dropIfExists('bank_keluars');
        Schema::connection('cash_bank_new')->create('bank_keluars', function (Blueprint $t) {
            $t->id('id_bank_keluar');
            $t->unsignedBigInteger('dokumen_id')->nullable();
            $t->string('no_agenda')->nullable();
            $t->string('agenda_tahun')->nullable();
            $t->date('tanggal')->nullable();
            $t->timestamps();
        });
    }

    private function buatDokumen(array $override = []): int
    {
        return DB::table('dokumens')->insertGetId(array_merge([
            'nomor_agenda'  => '0001',
            'bulan'         => 'Januari',
            'tahun'         => 2026,
            'tanggal_masuk' => '2026-01-01 00:00:00',
            'nomor_spp'     => 'SPP-1',
            'tanggal_spp'   => '2026-01-01 00:00:00',
            'uraian_spp'    => 'Uji',
            'nilai_rupiah'  => 1000,
            'kategori'      => 'Umum',
            'jenis_dokumen' => 'Invoice',
            'status'        => 'draft',
            'tanggal_dibayar' => null,
            'created_at'    => now(),
            'updated_at'    => now(),
        ], $override));
    }

    public function test_command_mengisi_dan_exit_sukses(): void
    {
        $id = $this->buatDokumen(['nomor_agenda' => '0001', 'tanggal_dibayar' => null]);
        DB::connection('cash_bank_new')->table('bank_keluars')->insert([
            'dokumen_id' => $id, 'tanggal' => '2026-03-10',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->artisan('dokumen:backfill-tanggal-bayar')->assertExitCode(0);

        $this->assertSame('2026-03-10', substr((string) DB::table('dokumens')->where('id', $id)->value('tanggal_dibayar'), 0, 10));
    }

    public function test_command_dry_run_tidak_menulis(): void
    {
        $id = $this->buatDokumen(['nomor_agenda' => '0002', 'tanggal_dibayar' => null]);
        DB::connection('cash_bank_new')->table('bank_keluars')->insert([
            'dokumen_id' => $id, 'tanggal' => '2026-03-10',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->artisan('dokumen:backfill-tanggal-bayar --dry-run')->assertExitCode(0);

        $this->assertNull(DB::table('dokumens')->where('id', $id)->value('tanggal_dibayar'));
    }
}
```

- [ ] **Step 2: Jalankan test, pastikan gagal**

Run: `php artisan test --filter=BackfillTanggalBayarCommandTest`
Expected: FAIL — command `dokumen:backfill-tanggal-bayar` tidak dikenali (`Command "dokumen:backfill-tanggal-bayar" is not defined`).

- [ ] **Step 3: Implementasikan command**

Buat `app/Console/Commands/BackfillTanggalBayarCommand.php`:

```php
<?php

namespace App\Console\Commands;

use App\Services\BackfillTanggalBayarService;
use Illuminate\Console\Command;

class BackfillTanggalBayarCommand extends Command
{
    protected $signature = 'dokumen:backfill-tanggal-bayar
        {--dry-run : Hanya laporkan, jangan tulis ke database}
        {--limit= : Batasi jumlah dokumen yang diproses}';

    protected $description = 'Isi tanggal_dibayar dokumen Agenda dari tanggal bank keluar Cash Bank (satu arah, hanya bila kosong).';

    public function handle(BackfillTanggalBayarService $service): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $limit  = $this->option('limit') !== null ? (int) $this->option('limit') : null;

        if ($dryRun) {
            $this->warn('MODE DRY-RUN: tidak ada data yang akan ditulis.');
        }

        $s = $service->run($dryRun, $limit);

        $this->info('Ringkasan backfill tanggal bayar (Cash Bank -> Agenda):');
        $this->table(['Metrik', 'Jumlah'], [
            ['Dokumen diperiksa', $s['diperiksa']],
            [$dryRun ? 'Akan diisi' : 'Diisi', $s['diisi']],
            ['Sudah sama', $s['sama']],
            ['Konflik (dilewati)', $s['konflik']],
            ['Tidak ketemu dokumennya', $s['tidak_ketemu']],
        ]);

        if (!empty($s['konflik_detail'])) {
            $this->warn('Konflik (Agenda sudah punya tanggal berbeda - tidak ditimpa):');
            $this->table(
                ['Dokumen ID', 'Nomor Agenda', 'Tanggal Agenda', 'Tanggal Cash Bank'],
                array_map(fn ($c) => [
                    $c['dokumen_id'], $c['nomor_agenda'], $c['tanggal_agenda'], $c['tanggal_cashbank'],
                ], $s['konflik_detail'])
            );
        }

        return self::SUCCESS;
    }
}
```

- [ ] **Step 4: Jalankan test, pastikan lulus**

Run: `php artisan test --filter=BackfillTanggalBayarCommandTest`
Expected: PASS (2 test hijau).

- [ ] **Step 5: Jalankan seluruh suite (pastikan tak ada regresi)**

Run: `php artisan test`
Expected: PASS semua (atau minimal tak ada kegagalan baru dari perubahan ini).

- [ ] **Step 6: Commit**

```bash
git add app/Console/Commands/BackfillTanggalBayarCommand.php
git add tests/Feature/BackfillTanggalBayarCommandTest.php
git commit -m "feat(sync): command dokumen:backfill-tanggal-bayar

Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>"
```

---

## Verifikasi Akhir (manual — WAJIB minta izin user dulu)

Ini **bukan** langkah otomatis. Menyentuh DB produksi butuh izin eksplisit user.

1. **Dry-run terhadap server** (read-only, aman) untuk melihat dampak:
   Jalankan di environment yang koneksinya menunjuk DB produksi:
   `php artisan dokumen:backfill-tanggal-bayar --dry-run`
   Tinjau ringkasan: berapa yang "Akan diisi", berapa konflik, berapa tidak ketemu.
2. **Tinjau daftar konflik** bersama user. Konflik tidak akan ditimpa; putuskan manual bila perlu.
3. **Setelah user menyetujui**, jalankan run sungguhan:
   `php artisan dokumen:backfill-tanggal-bayar`
   (opsional bertahap: `--limit=50` dulu, verifikasi, lalu tanpa limit).
4. Deploy mengikuti alur proyek (commit → push → pull di server), sesuai memori deploy.

## Catatan Self-Review

- **Cakupan spec:** §3 keputusan (arah tunggal, isi bila kosong, konflik dilewati, MIN terawal, lingkup field, dry-run) → semuanya diuji di Task 1. §4.2 command + opsi → Task 2. §4.6 pencatatan konflik ke `sync_logs` → Task 1 (`test_tidak_menimpa_dan_mencatat_konflik`). §4.5 hindari push-balik → dipenuhi dengan raw `DB::table()->update()` (bukan Eloquent), sehingga `DokumenObserver` tak terpicu.
- **Deviasi terdokumentasi dari spec §4.6:** spec menyebut `direction = 'cb_to_ao_backfill'`, tetapi enum `sync_logs.direction` hanya menerima `ao_to_cb|cb_to_ao`. Dipakai `cb_to_ao` + `status='conflict_resolved'` + `source_wins='agenda_online'` agar tak perlu migrasi enum. Hanya konflik yang dicatat ke `sync_logs` (baris terisi diringkas di output, menghindari bloat).
- **Konsistensi tipe:** `run(bool,$?int): array` dipakai identik di command Task 2. Kunci summary (`diperiksa/diisi/sama/konflik/tidak_ketemu/konflik_detail`) sama di service, test, dan command.

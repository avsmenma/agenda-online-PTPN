# Rollout Tabulator ke Role Akutansi — Rencana Implementasi

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Mengganti tabel classic akutansi dengan engine Tabulator bersama (seperti operator) tanpa menyalin engine ke-7, mengekstrak basis DTO `DocumentRow` yang diwarisi operator & akutansi, lalu menghapus view legacy akutansi setelah QA lolos.

**Architecture:** Basis abstrak `App\Support\DocumentRow` memuat derivasi baris bersama (kolom base, format rupiah, join relasi, tanggal, handler). `OperatorDocumentRow` & `AkutansiDocumentRow` mewarisinya dan menambah bit khas peran. Endpoint JSON `documents.akutansi.data` memakai ulang query `dokumens()` (diekstrak jadi method privat) dan memetakan tiap baris via `AkutansiDocumentRow`. Badge Status & kolom Deadline dihitung SERVER (di dalam DTO) sebagai objek siap-render; engine `document-tabulator.js` diperluas dengan kolom tetap terparameter (`extraColumns`) + dua formatter dumb yang hanya merender objek server. View baru meniru `daftarDokumenTabulator.blade.php`. Transisi memakai `?classic` untuk banding; view lama dihapus utuh setelah QA.

**Tech Stack:** Laravel 12, PHP ^8.2, MySQL 8, Blade, Tabulator.js 6.3.1 (self-hosted), Bootstrap 5 (CDN), PHPUnit (SQLite in-memory untuk test).

## Global Constraints

- **Jangan salinan ke-7.** Engine Tabulator TETAP satu berkas `public/js/document-tabulator.js`; perluasan harus terparameter (operator tak berubah perilaku). Sumber kolom TETAP `config/document_columns.php`.
- **Parity operator wajib.** Ekstraksi basis TIDAK boleh mengubah keluaran `OperatorDocumentRow::fromDokumen(...)`. Gerbang: `OperatorDatatableTest`, `OperatorInlineCreateRowTest`, `InlineCreateDokumenTest` HARUS tetap hijau + QA visual operator (tanggung jawab user).
- **Nol logika bisnis di JS.** Pohon keputusan Status & Deadline dihitung di DTO (server). Formatter klien hanya merender objek. Switch presentasional kecil (mis. `footer.kind`) boleh — itu bukan aturan bisnis.
- **Parity data akutansi wajib.** `dokumens()` meng-eager-load `roleData` HANYA `role_code='akutansi'` dan `roleStatuses` untuk `['team_verifikasi','perpajakan','akutansi','pembayaran']`. Endpoint datatable WAJIB memakai eager-load yang SAMA agar `getDataForRole('pembayaran'/'team_verifikasi')` mengembalikan null persis seperti sekarang (tanpa N+1, tanpa mengubah tampilan). Jangan menambah eager-load roleData peran lain.
- **`php artisan test` hijau di TIAP tahap sebelum commit.** `node --check public/js/document-tabulator.js` exit 0 bila berkas itu disentuh.
- **git add per-file** (jangan `git add .`/`-A`). **Pesan commit Bahasa Indonesia.** Satu commit = satu perubahan logis. **UI/komentar Indonesia, identifier English.**
- **Jangan tambah CSS inline `!important` baru.** CSS Deadline/Badge yang sudah ada di-PORT verbatim (bukan ditulis ulang) ke berkas terpisah.
- **Akutansi tak punya create/destroy route** (dihapus 2026-07-11). View Tabulator akutansi TIDAK memuat tombol "Tambah Baris" maupun "Hapus" (engine sudah menjaga: `if(addBtn)`, `if(!deleteBtn) return;`).
- **Gerbang kritis (CLAUDE.md §6):** Tugas 7 (switch default) & Tugas 8 (hapus legacy) menyentuh routing/penghapusan view — Tugas 8 HANYA dijalankan setelah user mengonfirmasi QA akutansi lolos.

---

### Peta Berkas

**Dibuat:**
- `app/Support/DocumentRow.php` — basis abstrak DTO baris (shared).
- `app/Support/AkutansiDocumentRow.php` — DTO baris akutansi (extends DocumentRow).
- `resources/views/akutansi/dokumens/daftarAkutansiTabulator.blade.php` — view Tabulator akutansi.
- `public/css/akutansi-deadline-badge.css` — CSS Deadline+Badge di-port dari view legacy.
- `tests/Unit/DocumentRowBaseTest.php` — unit test basis.
- `tests/Unit/AkutansiDocumentRowTest.php` — unit test DTO akutansi.
- `tests/Feature/AkutansiDatatableTest.php` — feature test endpoint JSON.
- `tests/Feature/AkutansiTabulatorSwitchTest.php` — feature test transisi ?classic.

**Diubah:**
- `app/Support/OperatorDocumentRow.php` — refaktor agar extends `DocumentRow` (parity mutlak).
- `app/Http/Controllers/DashboardAkutansiController.php` — ekstrak `buildAkutansiQuery()`, tambah `datatable()`, `dokumens()` menyajikan Tabulator default + `?classic`.
- `routes/web.php` — tambah route STATIS `documents.akutansi.data` sebelum route `{dokumen}`.
- `public/js/document-tabulator.js` — dukungan `CFG.extraColumns` + formatter `fmtDeadline`/`fmtAkutansiStatus` (operator tak terpengaruh).

> **Utang de-dup (disengaja, sesuai CLAUDE.md §7):** modal Kustomisasi Kolom + JS-nya DIDUPLIKASI ke view Tabulator akutansi (bukan diekstrak jadi partial bersama). CLAUDE.md §7 menyatakan penyatuan kustomisasi kolom lintas-role adalah program TERPISAH setelah rollout (toolbar tiap role beda nama field: operator `year`/`status_filter`, akutansi `status`/`filter_dari`). Rollout 1 sengaja self-contained agar TIDAK menyentuh view operator. Catat di ledger; lihat memori [[dedup-slice-3-5-ditunda]].

**Dihapus (Tugas 8, PASCA-QA, gated):**
- `resources/views/akutansi/dokumens/daftarAkutansi.blade.php`, `_rows.blade.php`, `_chunk.blade.php`, cabang `virtual_chunk` + flag `?classic` di `dokumens()`.

---

## Task 1: Basis abstrak `App\Support\DocumentRow`

**Files:**
- Create: `app/Support/DocumentRow.php`
- Test: `tests/Unit/DocumentRowBaseTest.php`

**Interfaces:**
- Produces: `abstract class App\Support\DocumentRow` dengan `protected static function baseRow(\App\Models\Dokumen $dokumen, array $handlerOptions, ?string $viewerRole = null): array` dan `protected static function formatDates(\App\Models\Dokumen $dokumen): array` dan `protected static function normalizeRole(?string $role): string`. `baseRow()` mengembalikan array baris bersama TANPA kunci `display_status`, `reject_reason`, `rejected_by`, `rejected_at`, `can_edit` (itu ditambah subclass).
- Konsumen: Tugas 2 (OperatorDocumentRow), Tugas 3 (AkutansiDocumentRow).

Prasyarat data: `$dokumen` sudah eager-load `roleStatuses`, `dibayarKepadas`, `dokumenPos`. `baseRow()` TIDAK melakukan query DB.

- [ ] **Step 1: Tulis test yang gagal**

Basis abstrak — uji lewat subclass stub anonim di dalam test. Buat `tests/Unit/DocumentRowBaseTest.php`:

```php
<?php

namespace Tests\Unit;

use App\Models\Dokumen;
use App\Support\DocumentRow;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class DocumentRowBaseTest extends TestCase
{
    /** Subclass stub: mengekspos baseRow() yang protected untuk pengujian unit. */
    private function rowFor(Dokumen $dokumen, array $handlerOptions = [], ?string $viewerRole = null): array
    {
        $probe = new class extends DocumentRow {
            public static function call(Dokumen $d, array $h, ?string $v): array
            {
                return static::baseRow($d, $h, $v);
            }
        };

        return $probe::call($dokumen, $handlerOptions, $viewerRole);
    }

    public function test_base_row_memuat_id_kolom_base_dan_turunan_format(): void
    {
        $dokumen = new Dokumen([
            'nomor_agenda'  => '12_A',
            'bulan'         => 'Januari',
            'tahun'         => '2026',
            'nilai_rupiah'  => 1500000,
            'dpp_pph'       => 1000000,
            'ppn_terhutang' => null,
            'current_handler' => 'operator',
            'status'        => 'draft',
        ]);
        $dokumen->id = 77;
        // Relasi kosong yang wajib ter-load agar tak ada query.
        $dokumen->setRelation('roleStatuses', new Collection());
        $dokumen->setRelation('dibayarKepadas', new Collection());
        $dokumen->setRelation('dokumenPos', new Collection());

        $handlerOptions = [['value' => 'operator', 'label' => 'Operator']];
        $row = $this->rowFor($dokumen, $handlerOptions, 'operator');

        // Identitas + sebagian kolom base disalin apa adanya.
        $this->assertSame(77, $row['id']);
        $this->assertSame('12_A', $row['nomor_agenda']);
        $this->assertSame('Januari', $row['bulan']);

        // Turunan format server.
        $this->assertSame('Rp 1.000.000', $row['dpp_pph_formatted']);
        $this->assertSame('-', $row['ppn_terhutang_formatted']);

        // Handler ditanam apa adanya.
        $this->assertSame('operator', $row['handler']);
        $this->assertSame($handlerOptions, $row['handler_options']);

        // Kunci khas subclass TIDAK boleh ada di basis.
        $this->assertArrayNotHasKey('display_status', $row);
        $this->assertArrayNotHasKey('can_edit', $row);
        $this->assertArrayNotHasKey('reject_reason', $row);

        // dates selalu ada sebagai peta.
        $this->assertIsArray($row['dates']);
    }

    public function test_can_change_handler_true_hanya_bila_viewer_adalah_pengurus_saat_ini_tanpa_pending(): void
    {
        $dokumen = new Dokumen(['current_handler' => 'akutansi', 'status' => 'draft']);
        $dokumen->id = 5;
        $dokumen->setRelation('roleStatuses', new Collection());
        $dokumen->setRelation('dibayarKepadas', new Collection());
        $dokumen->setRelation('dokumenPos', new Collection());

        $rowMatch = $this->rowFor($dokumen, [], 'akutansi');
        $this->assertTrue($rowMatch['can_change_handler']);

        $rowMismatch = $this->rowFor($dokumen, [], 'perpajakan');
        $this->assertFalse($rowMismatch['can_change_handler']);
    }
}
```

- [ ] **Step 2: Jalankan test — pastikan gagal**

Run: `php artisan test tests/Unit/DocumentRowBaseTest.php`
Expected: FAIL — `Class "App\Support\DocumentRow" not found`.

- [ ] **Step 3: Buat basis abstrak**

Pindahkan derivasi bersama dari `OperatorDocumentRow` ke basis. Buat `app/Support/DocumentRow.php`:

```php
<?php

namespace App\Support;

use App\Models\Dokumen;

/**
 * Basis abstrak DTO baris tabel dokumen (Tabulator). Memuat derivasi yang
 * dipakai SEMUA role: salin kolom base, format rupiah/DPP/PPN, join relasi
 * (dibayar_kepada, nomor_po), tanggal terformat sisi-server, dropdown pengurus
 * (handler + handler_options + can_change_handler), dan link ter-sanitasi.
 *
 * Subclass (OperatorDocumentRow, AkutansiDocumentRow) memanggil baseRow() lalu
 * MENAMBAH bit khas peran (display_status/can_edit operator; status_badge/
 * deadline/lock akutansi). Basis TIDAK menyertakan display_status, reject_*,
 * maupun can_edit karena tiap role menghitungnya berbeda.
 *
 * Prasyarat: relasi roleStatuses, dibayarKepadas, dokumenPos sudah ter-eager-load
 * (basis TIDAK query DB). $handlerOptions disediakan pemanggil (hindari query
 * per-baris) dan ditanamkan apa adanya.
 */
abstract class DocumentRow
{
    protected static function baseRow(Dokumen $dokumen, array $handlerOptions, ?string $viewerRole = null): array
    {
        $statuses = $dokumen->roleStatuses;

        // === Nilai ATTRIBUTE mentah untuk setiap kolom base (formatter di klien) ===
        $row = ['id' => $dokumen->id];
        foreach (array_keys(config('document_columns.base')) as $key) {
            $row[$key] = $dokumen->{$key};
        }

        // Status dokumen: utamakan nilai CSV bila ada, fallback ke custom
        // (identik _tableRowsAjax.blade.php:203-204). Kosong/null tetap null; klien merender '-'.
        $row['status_dokumen_custom'] = $dokumen->status_dokumen_csv ?? $dokumen->status_dokumen_custom;

        // === Kunci turunan tampilan bersama ===
        $row['nilai_rupiah_formatted']  = $dokumen->formatted_nilai_rupiah;
        $row['dpp_pph_formatted']       = $dokumen->dpp_pph !== null
            ? 'Rp ' . number_format((float) $dokumen->dpp_pph, 0, ',', '.')
            : '-';
        $row['ppn_terhutang_formatted'] = $dokumen->ppn_terhutang !== null
            ? 'Rp ' . number_format((float) $dokumen->ppn_terhutang, 0, ',', '.')
            : '-';
        // Join nama penerima, fallback ke kolom flat lama bila relasi kosong.
        $row['dibayar_kepada']     = $dokumen->dibayarKepadas->pluck('nama_penerima')->join(', ') ?: ($dokumen->dibayar_kepada ?? '');
        // Join nomor PO dengan fallback ke kolom CSV NO_PO.
        $row['nomor_po']           = $dokumen->dokumenPos->pluck('nomor_po')->filter()->join(', ') ?: ($dokumen->NO_PO ?? '-');
        $row['nomor_miro_display'] = $dokumen->nomor_miro_display;
        $row['handler']            = $dokumen->current_handler;
        $row['handler_options']    = $handlerOptions;

        // === can_change_handler: paritas gate dropdown pengurus. 'verifikasi' &
        // 'team_verifikasi' disamakan (alias lama/baru), perbandingan case-insensitive. ===
        $isCurrentHandler = $viewerRole !== null
            && static::normalizeRole($viewerRole) === static::normalizeRole($dokumen->current_handler ?? '');
        $hasPending = $statuses->contains(
            fn ($s) => strtolower((string) $s->status) === strtolower(\App\Models\DokumenStatus::STATUS_PENDING)
        );
        $row['can_change_handler'] = $isCurrentHandler && ! $hasPending;

        // === URL link ter-sanitasi (SafeUrl::external memaksa skema http(s)). ===
        $row['link_safe']               = SafeUrl::external($dokumen->link);
        $row['link_dokumen_pajak_safe'] = SafeUrl::external($dokumen->link_dokumen_pajak);

        // === Tanggal terformat sisi-server. Null/kosong → '-'. ===
        $row['dates'] = static::formatDates($dokumen);

        return $row;
    }

    protected static function normalizeRole(?string $role): string
    {
        $role = strtolower(trim((string) $role));
        return $role === 'verifikasi' ? 'team_verifikasi' : $role;
    }

    /**
     * Peta kolom tanggal → string terformat (format identik render lama).
     * Kolom cast (Carbon) diformat langsung; kolom string mentah di-parse defensif.
     */
    protected static function formatDates(Dokumen $dokumen): array
    {
        $formats = [
            'tanggal_spp'                      => 'd-m-Y',
            'tanggal_berita_acara'             => 'd-m-Y',
            'tanggal_spk'                      => 'd-m-Y',
            'tanggal_berakhir_spk'             => 'd-m-Y',
            'tanggal_masuk'                    => 'd-m-Y H:i',
            'tanggal_paraf'                    => 'd/m/Y H:i',
            'tanggal_selesai_diproses'         => 'd/m/Y H:i',
            'tanggal_kembali_ke_bagian'        => 'd/m/Y H:i',
            'tanggal_hasil_koreksi_bagian'     => 'd/m/Y H:i',
            'tanggal_dibayar'                  => 'd/m/Y',
            'tanggal_faktur'                   => 'd/m/Y',
            'tanggal_selesai_verifikasi_pajak' => 'd/m/Y',
        ];

        $dates = [];
        foreach ($formats as $col => $format) {
            $value = $dokumen->{$col} ?? null;

            if ($value === null || $value === '') {
                $dates[$col] = '-';
                continue;
            }

            if ($value instanceof \DateTimeInterface) {
                $dates[$col] = $value->format($format);
                continue;
            }

            try {
                $dates[$col] = \Illuminate\Support\Carbon::parse($value)->format($format);
            } catch (\Throwable $e) {
                $dates[$col] = '-';
            }
        }

        return $dates;
    }
}
```

- [ ] **Step 4: Jalankan test — pastikan lulus**

Run: `php artisan test tests/Unit/DocumentRowBaseTest.php`
Expected: PASS (2 test).

- [ ] **Step 5: Commit**

```bash
git add app/Support/DocumentRow.php tests/Unit/DocumentRowBaseTest.php
git commit -m "feat(support): basis abstrak DocumentRow untuk DTO baris bersama"
```

---

## Task 2: `OperatorDocumentRow` mewarisi basis (parity mutlak)

**Files:**
- Modify: `app/Support/OperatorDocumentRow.php`

**Interfaces:**
- Consumes: `App\Support\DocumentRow::baseRow(...)`, `::normalizeRole(...)` dari Task 1.
- Produces: `OperatorDocumentRow::fromDokumen(Dokumen $dokumen, array $handlerOptions, ?string $viewerRole = null): array` — **keluaran IDENTIK nilai** dengan versi sebelumnya (semua kunci lama tetap ada: `display_status`, `reject_reason`, `rejected_by`, `rejected_at`, `can_edit`, + semua kunci basis).

- [ ] **Step 1: Jalankan test parity operator SEBELUM mengubah (rekam baseline hijau)**

Run: `php artisan test --filter="OperatorDatatable|OperatorInlineCreateRow|InlineCreateDokumen"`
Expected: PASS (baseline hijau; ini jaring pengaman refaktor).

- [ ] **Step 2: Refaktor `OperatorDocumentRow` agar extends basis**

Ganti seluruh isi `app/Support/OperatorDocumentRow.php`. Bit operator (status verifikasi, display_status, can_edit, reject_*) TETAP di sini; derivasi bersama sekarang dari `baseRow()`. Metode `formatDates()` privat lama DIHAPUS (pindah ke basis).

```php
<?php

namespace App\Support;

use App\Models\Dokumen;
use App\Models\DokumenStatus;

/**
 * DTO baris tabel operator (Tabulator). Mewarisi derivasi bersama dari
 * App\Support\DocumentRow (kolom base, rupiah, join, tanggal, handler) dan
 * menambah bit khas operator: pohon display_status, can_edit operator, dan
 * alasan penolakan. Sumber kebenaran TUNGGAL badge status operator.
 *
 * Prasyarat: roleStatuses, dibayarKepadas, dokumenPos sudah eager-load (tanpa
 * query DB). $handlerOptions disediakan pemanggil.
 */
class OperatorDocumentRow extends DocumentRow
{
    public static function fromDokumen(Dokumen $dokumen, array $handlerOptions, ?string $viewerRole = null): array
    {
        $row = static::baseRow($dokumen, $handlerOptions, $viewerRole);

        $statuses = $dokumen->roleStatuses;

        // === Status Team Verifikasi terbaru ===
        $tvStatus   = $statuses->where('role_code', 'team_verifikasi')->sortByDesc('status_changed_at')->first();
        $tvRejected = $tvStatus && strtolower($tvStatus->status ?? '') === DokumenStatus::STATUS_REJECTED;
        $tvPending  = $tvStatus && strtolower($tvStatus->status ?? '') === DokumenStatus::STATUS_PENDING;
        $tvApproved = $tvStatus && strtolower($tvStatus->status ?? '') === DokumenStatus::STATUS_APPROVED;

        $statusLower            = strtolower($dokumen->status ?? '');
        $currentHandlerLower    = strtolower($dokumen->current_handler ?? '');
        $currentHandlerOperator = $currentHandlerLower === 'operator';

        // Ada status peran hilir — bukan sekadar "selain operator".
        $hasOtherRoles = $statuses->whereIn('role_code', ['perpajakan', 'akutansi', 'pembayaran'])->isNotEmpty();

        // Penanda ditolak untuk aturan can_edit.
        $isRejected = $tvRejected;
        if (! $isRejected && $statusLower === 'returned_to_operator') {
            $isRejected = $statuses->where('status', DokumenStatus::STATUS_REJECTED)->isNotEmpty();
        }

        // === Pohon keputusan display_status ===
        if ($statusLower === 'returned_to_operator') {
            $code = 'dikembalikan';
        } elseif ($tvRejected) {
            $code = 'ditolak_verifikasi';
        } elseif ($tvPending) {
            $code = 'menunggu_approval_verifikasi';
        } elseif ($tvApproved || $hasOtherRoles) {
            $code = 'terkirim';
        } elseif ($statusLower === 'menunggu_approval_keuangan' && $currentHandlerOperator) {
            $code = 'draft';
        } elseif ($currentHandlerOperator && in_array($statusLower, ['draft', 'returned_to_operator'], true)) {
            $code = 'draft';
        } else {
            $code = in_array($currentHandlerLower, ['team_verifikasi', 'verifikasi', 'perpajakan', 'akutansi', 'pembayaran'], true)
                ? 'terkirim'
                : 'draft';
        }

        $label = match ($code) {
            'draft'                        => 'Belum Dikirim',
            'menunggu_approval_verifikasi' => 'Menunggu Approve Team Verifikasi',
            'ditolak_verifikasi'           => 'Dokumen Ditolak oleh Team Verifikasi',
            'dikembalikan'                 => 'Dikembalikan',
            default                        => 'Terkirim',
        };

        // === Aturan can_edit operator (&& mengikat lebih kuat dari ||) ===
        $canEdit = ($currentHandlerOperator
                && in_array($statusLower, ['draft', 'returned_to_operator', 'belum_dikirim', 'belum dikirim', 'menunggu_approval_keuangan'], true))
            || ($isRejected && $currentHandlerOperator);

        // === Alasan penolakan dari status verifikasi/team_verifikasi ===
        $rejectReason = null;
        $rejectedBy   = null;
        $rejectedAt   = null;
        $verifStatus  = $statuses->whereIn('role_code', ['verifikasi', 'team_verifikasi'])->first();
        if ($verifStatus && $verifStatus->status === DokumenStatus::STATUS_REJECTED) {
            $rejectReason = $verifStatus->notes;
            $rejectedBy   = $verifStatus->changed_by;
            $rejectedAt   = $verifStatus->status_changed_at?->format('d-m-Y H:i');
        }

        // === Overlay bit operator di atas basis ===
        $row['display_status'] = ['code' => $code, 'label' => $label, 'variant' => $code];
        $row['reject_reason']  = $rejectReason;
        $row['rejected_by']    = $rejectedBy;
        $row['rejected_at']    = $rejectedAt;
        $row['can_edit']       = $canEdit;

        return $row;
    }
}
```

- [ ] **Step 3: Jalankan test parity operator — pastikan tetap hijau**

Run: `php artisan test --filter="OperatorDatatable|OperatorInlineCreateRow|InlineCreateDokumen"`
Expected: PASS (identik baseline Step 1). Bila ada yang merah, keluaran berubah — perbaiki hingga identik SEBELUM lanjut.

- [ ] **Step 4: Jalankan seluruh suite**

Run: `php artisan test`
Expected: PASS (hijau penuh).

- [ ] **Step 5: Commit**

```bash
git add app/Support/OperatorDocumentRow.php
git commit -m "refactor(support): OperatorDocumentRow mewarisi basis DocumentRow (parity)"
```

---

## Task 3: `App\Support\AkutansiDocumentRow`

**Files:**
- Create: `app/Support/AkutansiDocumentRow.php`
- Test: `tests/Unit/AkutansiDocumentRowTest.php`

**Interfaces:**
- Consumes: `DocumentRow::baseRow(...)` dari Task 1; `App\Helpers\DokumenHelper`.
- Produces: `AkutansiDocumentRow::fromDokumen(Dokumen $dokumen, array $handlerOptions, ?string $viewerRole = null): array`. Selain kunci basis, menambah: `is_at_my_role` (bool), `is_locked` (bool), `lock_status_message` (string), `lock_status_class` (string), `can_edit` (bool), `can_set_deadline` (bool), `status_pembayaran` (mixed), `status_badge` (array `{class, icon|null, text, link|null}`), `deadline` (array; lihat bentuk di bawah).
- **Bentuk `status_badge`:** `['class' => string, 'icon' => ?string /* fa tanpa "fa-solid " */, 'text' => string, 'link' => null | ['href' => string, 'text' => string]]`.
- **Bentuk `deadline`:** `['variant' => 'card'|'sent_fallback'|'none', 'type' => ?('active'|'paused'|'completed'|'sent'), 'color' => ?('green'|'yellow'|'red'|'gray'), 'received_display' => ?string, 'indicator_icon' => ?string, 'indicator_label' => ?string, 'age_text' => ?string, 'footer' => null | ['kind' => 'paused'|'sent'|'completed', 'icon' => string, 'text' => string]]`.

Prasyarat data (sama dengan `dokumens()`): `roleData` eager-load HANYA `role_code='akutansi'`; `roleStatuses` eager-load `['team_verifikasi','perpajakan','akutansi','pembayaran']`; `dibayarKepadas`, `dokumenPos` eager-load. Dengan begitu `getDataForRole('pembayaran'/'team_verifikasi')` (roleData) mengembalikan null tanpa query — PARITY dengan `_rows.blade.php` saat ini.

- [ ] **Step 1: Tulis test yang gagal**

Buat `tests/Unit/AkutansiDocumentRowTest.php`. Uji cabang badge kunci + status deadline "none" + basis. Gunakan model tanpa DB (relasi di-set manual).

```php
<?php

namespace Tests\Unit;

use App\Models\Dokumen;
use App\Models\DokumenStatus;
use App\Support\AkutansiDocumentRow;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class AkutansiDocumentRowTest extends TestCase
{
    private function makeDokumen(array $attrs, Collection $statuses, Collection $roleData): Dokumen
    {
        $dokumen = new Dokumen($attrs);
        $dokumen->id = $attrs['id'] ?? 1;
        $dokumen->setRelation('roleStatuses', $statuses);
        $dokumen->setRelation('roleData', $roleData);
        $dokumen->setRelation('dibayarKepadas', new Collection());
        $dokumen->setRelation('dokumenPos', new Collection());

        return $dokumen;
    }

    public function test_badge_draft_saat_dokumen_masih_di_hulu(): void
    {
        // Belum sampai akutansi (handler operator), belum selesai/dibayar → "Draft".
        $dokumen = $this->makeDokumen(
            ['id' => 10, 'current_handler' => 'operator', 'status' => 'draft', 'status_pembayaran' => null],
            new Collection(),        // tak ada roleStatuses
            new Collection()         // tak ada roleData akutansi → belum diterima
        );

        $row = AkutansiDocumentRow::fromDokumen($dokumen, [], 'akutansi');

        $this->assertSame('badge-proses', $row['status_badge']['class']);
        $this->assertSame('⏳ Draft', $row['status_badge']['text']);
        $this->assertNull($row['status_badge']['link']);
        // Belum diterima → deadline variant none.
        $this->assertSame('none', $row['deadline']['variant']);
    }

    public function test_badge_ditolak_menyertakan_link_cek_disini(): void
    {
        $rejected = new DokumenStatus(['role_code' => 'akutansi', 'status' => 'rejected']);
        $dokumen = $this->makeDokumen(
            ['id' => 11, 'nomor_agenda' => '99', 'current_handler' => 'akutansi', 'status' => 'returned_to_verifikasi'],
            new Collection([$rejected]),
            new Collection()
        );

        $row = AkutansiDocumentRow::fromDokumen($dokumen, [], 'akutansi');

        $this->assertSame('badge-dikembalikan', $row['status_badge']['class']);
        $this->assertSame('fa-times-circle', $row['status_badge']['icon']);
        $this->assertIsArray($row['status_badge']['link']);
        $this->assertSame('cek disini', $row['status_badge']['link']['text']);
        $this->assertStringContainsString('99', $row['status_badge']['link']['href']);
    }

    public function test_basis_ikut_terbawa(): void
    {
        $dokumen = $this->makeDokumen(
            ['id' => 12, 'current_handler' => 'akutansi', 'status' => 'sent_to_akutansi', 'nilai_rupiah' => 2000000],
            new Collection(),
            new Collection()
        );

        $row = AkutansiDocumentRow::fromDokumen($dokumen, [['value' => 'akutansi', 'label' => 'Tim Akuntansi']], 'akutansi');

        // Kunci basis hadir.
        $this->assertArrayHasKey('nilai_rupiah_formatted', $row);
        $this->assertArrayHasKey('dates', $row);
        $this->assertArrayHasKey('handler_options', $row);
        // Kunci akutansi hadir.
        $this->assertArrayHasKey('is_at_my_role', $row);
        $this->assertArrayHasKey('status_pembayaran', $row);
        // Tidak membawa kunci operator.
        $this->assertArrayNotHasKey('display_status', $row);
    }
}
```

- [ ] **Step 2: Jalankan test — pastikan gagal**

Run: `php artisan test tests/Unit/AkutansiDocumentRowTest.php`
Expected: FAIL — `Class "App\Support\AkutansiDocumentRow" not found`.

- [ ] **Step 3: Buat DTO akutansi**

Port pohon Status dari `_rows.blade.php:417-511` dan pohon Deadline dari `_rows.blade.php:170-414` menjadi objek. Buat `app/Support/AkutansiDocumentRow.php`:

```php
<?php

namespace App\Support;

use App\Helpers\DokumenHelper;
use App\Models\Dokumen;
use Carbon\Carbon;

/**
 * DTO baris tabel akutansi (Tabulator). Mewarisi derivasi bersama dari
 * App\Support\DocumentRow dan menambah bit khas akutansi: is_at_my_role, kunci
 * lock, can_edit(akutansi), can_set_deadline, status_pembayaran, plus dua objek
 * siap-render yang menggantikan pohon keputusan Blade lama:
 *   - status_badge  ← porting _rows.blade.php:417-511 (kolom Status).
 *   - deadline      ← porting _rows.blade.php:170-414 (kolom Deadline).
 * Klien (document-tabulator.js) hanya MERENDER objek ini; nol logika bisnis di JS.
 *
 * Prasyarat data (WAJIB sama dengan dokumens()): roleData eager-load HANYA
 * role_code='akutansi'; roleStatuses eager-load semua role terkait. Dengan itu
 * getDataForRole('pembayaran'/'team_verifikasi') mengembalikan null tanpa query
 * — paritas persis dengan tabel lama.
 */
class AkutansiDocumentRow extends DocumentRow
{
    public static function fromDokumen(Dokumen $dokumen, array $handlerOptions, ?string $viewerRole = null): array
    {
        $row = static::baseRow($dokumen, $handlerOptions, $viewerRole);

        $isLocked = DokumenHelper::isDocumentLocked($dokumen);

        // Cross-role visibility: dokumen "di role saya" (identik dokumens():266-273).
        $isAtMyRole = in_array($dokumen->current_handler, ['akutansi'])
            || in_array($dokumen->status, [
                'sent_to_pembayaran',
                'pending_approval_pembayaran',
                'waiting_approval_pembayaran',
                'menunggu_di_approve',
            ])
            || (in_array($dokumen->status, ['completed', 'selesai']) && ! empty($dokumen->status_pembayaran));

        $row['is_at_my_role']       = $isAtMyRole;
        $row['is_locked']           = $isLocked;
        $row['lock_status_message'] = DokumenHelper::getLockedStatusMessage($dokumen);
        $row['lock_status_class']   = DokumenHelper::getLockStatusClass($dokumen);
        $row['can_edit']            = DokumenHelper::canEditDocument($dokumen, 'akutansi');
        $row['can_set_deadline']    = DokumenHelper::canSetDeadline($dokumen)['can_set'];
        $row['status_pembayaran']   = $dokumen->status_pembayaran;

        $row['status_badge'] = static::buildStatusBadge($dokumen, $isLocked);
        $row['deadline']     = static::buildDeadline($dokumen);

        return $row;
    }

    /**
     * Port pohon badge Status akutansi (_rows.blade.php:417-511) → objek
     * {class, icon, text, link}. Urutan cabang DIPERTAHANKAN persis.
     */
    protected static function buildStatusBadge(Dokumen $dokumen, bool $isLocked): array
    {
        $isRejected = $dokumen->roleStatuses
            ->whereIn('role_code', ['akutansi', 'pembayaran'])
            ->where('status', 'rejected')
            ->isNotEmpty();

        $akutansiRoleData   = $dokumen->getDataForRole('akutansi');
        $pembayaranRoleData = $dokumen->getDataForRole('pembayaran'); // null (parity: roleData akutansi-only)

        $pembayaranHasApproved = $dokumen->roleStatuses
            ->where('role_code', 'pembayaran')->where('status', 'approved')->isNotEmpty();
        $pembayaranIsPending = $dokumen->roleStatuses
            ->where('role_code', 'pembayaran')->where('status', 'pending')->isNotEmpty();

        $isBypassedToPayment = (
            $dokumen->current_handler === 'pembayaran'
            || $dokumen->status === 'completed'
            || $dokumen->status_pembayaran === 'sudah_dibayar'
            || ($pembayaranRoleData && $pembayaranRoleData->received_at)
        ) && ! $akutansiRoleData?->received_at;

        $sentFromAkutansi = (
            $isBypassedToPayment
            || $pembayaranHasApproved
            || ($pembayaranRoleData && $pembayaranRoleData->received_at && ! $pembayaranIsPending)
        ) && ! $isRejected;

        if ($isRejected) {
            return [
                'class' => 'badge-dikembalikan',
                'icon'  => 'fa-times-circle',
                'text'  => 'Dokumen ditolak,',
                'link'  => [
                    'href' => route('returns.akutansi.index') . '?search=' . urlencode((string) $dokumen->nomor_agenda),
                    'text' => 'cek disini',
                ],
            ];
        }
        if (! ($akutansiRoleData?->received_at)
            && in_array($dokumen->current_handler, ['operator', 'team_verifikasi', 'perpajakan'], true)
            && ! in_array($dokumen->status, ['completed', 'selesai'], true)
            && $dokumen->status_pembayaran !== 'sudah_dibayar') {
            return ['class' => 'badge-proses', 'icon' => null, 'text' => '⏳ Draft', 'link' => null];
        }
        if ($pembayaranIsPending) {
            return ['class' => 'badge-warning', 'icon' => 'fa-clock', 'text' => 'Menunggu Approval dari Pembayaran', 'link' => null];
        }
        if ($sentFromAkutansi) {
            return ['class' => 'badge-sent', 'icon' => null, 'text' => '📤 Terkirim ke Pembayaran', 'link' => null];
        }
        if ($dokumen->status === 'sent_to_pembayaran' && ! $pembayaranIsPending) {
            return ['class' => 'badge-sent', 'icon' => null, 'text' => '📤 Terkirim ke Pembayaran', 'link' => null];
        }
        if ($isLocked) {
            return ['class' => 'badge-locked', 'icon' => null, 'text' => '🔒 Terkunci', 'link' => null];
        }
        if ($dokumen->status === 'selesai') {
            return ['class' => 'badge-selesai', 'icon' => null, 'text' => '✓ Selesai', 'link' => null];
        }
        if ($dokumen->status === 'returned_to_verifikasi') {
            return ['class' => 'badge-sent', 'icon' => 'fa-paper-plane', 'text' => 'Kembali ke Team Verifikasi', 'link' => null];
        }
        if ($dokumen->current_handler === 'akutansi'
            && ! in_array($dokumen->status, ['sent_to_pembayaran', 'selesai', 'completed', 'menunggu_di_approve', 'pending_approval_pembayaran'], true)) {
            return ['class' => 'badge-proses', 'icon' => null, 'text' => '⏳ Sedang Diproses', 'link' => null];
        }
        if ($dokumen->status === 'sent_to_akutansi' && $dokumen->current_handler !== 'akutansi') {
            return ['class' => 'badge-belum', 'icon' => null, 'text' => '⏳ Belum Diproses', 'link' => null];
        }
        if (in_array($dokumen->status, ['returned_to_operator', 'returned_to_department', 'dikembalikan'], true)) {
            return ['class' => 'badge-dikembalikan', 'icon' => null, 'text' => '← Dikembalikan', 'link' => null];
        }
        if ($dokumen->status === 'completed') {
            return ['class' => 'badge-selesai', 'icon' => null, 'text' => '✓ Selesai - Sudah Dibayar', 'link' => null];
        }

        return ['class' => 'badge-proses', 'icon' => null, 'text' => '⏳ Sedang Diproses', 'link' => null];
    }

    /**
     * Port kolom Deadline akutansi (_rows.blade.php:170-414) → objek siap-render.
     * Model "hitung naik" (umur sejak received_at), beku untuk sent/completed/
     * returned. Tanpa live-update klien (updater lama mati: data-attr mismatch).
     */
    protected static function buildDeadline(Dokumen $dokumen): array
    {
        $roleData   = $dokumen->getDataForRole('akutansi');
        $receivedAt = $roleData?->received_at;
        $pembayaranRoleData = $dokumen->getDataForRole('pembayaran'); // null (parity)

        $isBypassedToPaymentDeadline = (
            $dokumen->current_handler === 'pembayaran'
            || $dokumen->status === 'completed'
            || $dokumen->status_pembayaran === 'sudah_dibayar'
            || ($pembayaranRoleData && $pembayaranRoleData->received_at)
        ) && ! $roleData?->received_at;

        $isSent = in_array($dokumen->status, ['sent_to_pembayaran', 'pending_approval_pembayaran', 'menunggu_di_approve'], true);
        $isCompleted = in_array($dokumen->status, ['selesai', 'completed', 'approved_data_sudah_terkirim'], true)
            || ($dokumen->status_pembayaran === 'sudah_dibayar');
        $isReturned = $dokumen->status === 'returned_to_verifikasi';

        // === Path A: sudah diterima akutansi → kartu umur ===
        if ($receivedAt) {
            $receivedAt = $receivedAt instanceof Carbon ? $receivedAt : Carbon::parse($receivedAt);
            $processedAt = $roleData?->processed_at;
            $timeFrozen = false;

            if (($isSent || $isCompleted || $isReturned) && $processedAt) {
                $endTime = $processedAt instanceof Carbon ? $processedAt : Carbon::parse($processedAt);
                $timeFrozen = true;
            } elseif ($isReturned) {
                $endTime = Carbon::now();
                $timeFrozen = true;
            } else {
                $endTime = Carbon::now();
            }

            $diff = $receivedAt->diff($endTime);

            $elapsedParts = [];
            if ($diff->days > 0)  { $elapsedParts[] = $diff->days . ' hari'; }
            if ($diff->h > 0)     { $elapsedParts[] = $diff->h . ' jam'; }
            if ($diff->i > 0 || empty($elapsedParts)) { $elapsedParts[] = $diff->i . ' menit'; }
            $ageText = implode(' ', $elapsedParts);
            if ($timeFrozen) { $ageText .= ' ⏸️'; }

            $totalHours = ($diff->days * 24) + $diff->h;
            if ($totalHours >= 72) {
                $ageLabel = 'TERLAMBAT'; $ageIcon = 'fa-times-circle';
            } elseif ($totalHours >= 24) {
                $ageLabel = 'PERINGATAN'; $ageIcon = 'fa-exclamation-triangle';
            } else {
                $ageLabel = 'AMAN'; $ageIcon = 'fa-check-circle';
            }

            if ($isSent || $isCompleted || $isReturned) {
                $ageColor = 'gray';
            } elseif ($totalHours >= 72) {
                $ageColor = 'red';
            } elseif ($totalHours >= 24) {
                $ageColor = 'yellow';
            } else {
                $ageColor = 'green';
            }

            $type = 'active';
            if ($isReturned)        { $type = 'paused'; }
            elseif ($isCompleted)   { $type = 'completed'; }
            elseif ($isSent)        { $type = 'sent'; }

            $footer = null;
            if ($isReturned) {
                $footer = ['kind' => 'paused', 'icon' => 'fa-pause-circle', 'text' => 'Berhenti Sementara'];
            } elseif ($isSent) {
                $footer = ['kind' => 'sent', 'icon' => 'fa-paper-plane', 'text' => 'Terkirim'];
            } elseif ($isCompleted) {
                $footer = ['kind' => 'completed', 'icon' => 'fa-check-circle', 'text' => 'Selesai'];
            }

            return [
                'variant'          => 'card',
                'type'             => $type,
                'color'            => $ageColor,
                'received_display' => $receivedAt->format('d M Y, H:i'),
                'indicator_icon'   => $ageIcon,
                'indicator_label'  => $ageLabel,
                'age_text'         => $ageText,
                'footer'           => $footer,
            ];
        }

        // === Path B: bypass akutansi (langsung ke pembayaran) ===
        if ($isBypassedToPaymentDeadline) {
            $bypassVerifikasiData = $dokumen->getDataForRole('team_verifikasi'); // null (parity)
            $bypassTimestamp = $pembayaranRoleData?->received_at
                ?? $bypassVerifikasiData?->processed_at
                ?? $dokumen->tanggal_masuk;

            if (! $bypassTimestamp) {
                return static::deadlineSentFallback();
            }

            $bypassStartTime = $bypassTimestamp instanceof Carbon ? $bypassTimestamp : Carbon::parse($bypassTimestamp);
            $bypassProcessedAt = $bypassVerifikasiData?->processed_at ?? $pembayaranRoleData?->received_at;
            $bypassEndTime = $bypassProcessedAt
                ? ($bypassProcessedAt instanceof Carbon ? $bypassProcessedAt : Carbon::parse($bypassProcessedAt))
                : $bypassStartTime;

            $bypassDiff = $bypassStartTime->diff($bypassEndTime);
            $parts = [];
            if ($bypassDiff->days > 0) { $parts[] = $bypassDiff->days . ' hari'; }
            if ($bypassDiff->h > 0)    { $parts[] = $bypassDiff->h . ' jam'; }
            if ($bypassDiff->i > 0 || empty($parts)) { $parts[] = $bypassDiff->i . ' menit'; }
            $bypassAgeText = implode(' ', $parts) . ' ⏸️';

            $bypassTotalHours = ($bypassDiff->days * 24) + $bypassDiff->h;
            if ($bypassTotalHours >= 72) {
                $bypassAgeLabel = 'TERLAMBAT'; $bypassAgeIcon = 'fa-times-circle';
            } elseif ($bypassTotalHours >= 24) {
                $bypassAgeLabel = 'PERINGATAN'; $bypassAgeIcon = 'fa-exclamation-triangle';
            } else {
                $bypassAgeLabel = 'AMAN'; $bypassAgeIcon = 'fa-check-circle';
            }

            return [
                'variant'          => 'card',
                'type'             => 'sent',
                'color'            => 'gray',
                'received_display' => $bypassStartTime->format('d M Y, H:i'),
                'indicator_icon'   => $bypassAgeIcon,
                'indicator_label'  => $bypassAgeLabel,
                'age_text'         => $bypassAgeText,
                'footer'           => ['kind' => 'sent', 'icon' => 'fa-paper-plane', 'text' => 'Terkirim'],
            ];
        }

        // === Path C: belum diterima ===
        return ['variant' => 'none', 'type' => null, 'color' => null,
                'received_display' => null, 'indicator_icon' => null,
                'indicator_label' => null, 'age_text' => null, 'footer' => null];
    }

    protected static function deadlineSentFallback(): array
    {
        return ['variant' => 'sent_fallback', 'type' => 'sent', 'color' => 'gray',
                'received_display' => null, 'indicator_icon' => null,
                'indicator_label' => null, 'age_text' => null, 'footer' => null];
    }
}
```

> **Catatan verifikasi manual (jangkar sumber):** cocokkan tiap cabang `buildStatusBadge()` dengan `_rows.blade.php:459-510` (urutan if/elseif identik) dan `buildDeadline()` dengan `_rows.blade.php:170-408`. `$pembayaranRoleData`/`$bypassVerifikasiData` sengaja null (roleData akutansi-only) demi paritas.

- [ ] **Step 4: Jalankan test — pastikan lulus**

Run: `php artisan test tests/Unit/AkutansiDocumentRowTest.php`
Expected: PASS (3 test).

- [ ] **Step 5: Commit**

```bash
git add app/Support/AkutansiDocumentRow.php tests/Unit/AkutansiDocumentRowTest.php
git commit -m "feat(support): AkutansiDocumentRow (badge Status + Deadline dihitung server)"
```

---

## Task 4: Endpoint JSON `documents.akutansi.data`

**Files:**
- Modify: `app/Http/Controllers/DashboardAkutansiController.php`
- Modify: `routes/web.php:476-481`
- Test: `tests/Feature/AkutansiDatatableTest.php`

**Interfaces:**
- Consumes: `AkutansiDocumentRow::fromDokumen(...)` (Task 3); pola `DokumenController@datatable`.
- Produces: `DashboardAkutansiController@datatable(Request): \Illuminate\Http\JsonResponse` mengembalikan `{last_page, total, data}` (nama field cocok default `progressiveLoad:'scroll'` Tabulator). Route name `documents.akutansi.data`, path GET `documents/akutansi/data` (STATIS, sebelum `{dokumen}`). Method privat baru `buildAkutansiQuery(Request): \Illuminate\Database\Eloquent\Builder` + `buildAkutansiHandlerOptions(): array`.

- [ ] **Step 1: Tulis test yang gagal**

Buat `tests/Feature/AkutansiDatatableTest.php`. Login sebagai user akutansi, hit endpoint, assert bentuk JSON + kunci baris. Ikuti pola SQLite-polyfill dari `tests/Feature/AkutansiHapusAksiMatiTest.php` (REGEXP/SUBSTRING_INDEX) karena query akutansi memakai `orderByRaw` natural sort.

```php
<?php

namespace Tests\Feature;

use App\Models\Dokumen;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AkutansiDatatableTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Polyfill fungsi MySQL yang dipakai orderByRaw natural sort di SQLite.
        if (DB::connection()->getDriverName() === 'sqlite') {
            $pdo = DB::connection()->getPdo();
            $pdo->sqliteCreateFunction('REGEXP', fn ($pattern, $value) => preg_match('/' . $pattern . '/', (string) $value) ? 1 : 0, 2);
            $pdo->sqliteCreateFunction('SUBSTRING_INDEX', function ($str, $delim, $count) {
                $parts = explode($delim, (string) $str);
                return implode($delim, array_slice($parts, 0, (int) $count));
            }, 3);
            $pdo->sqliteCreateFunction('LPAD', fn ($str, $len, $pad) => str_pad((string) $str, (int) $len, (string) $pad, STR_PAD_LEFT), 3);
        }
    }

    private function akutansi(): User
    {
        return User::factory()->create(['role' => 'akutansi']);
    }

    public function test_endpoint_data_akutansi_mengembalikan_bentuk_json_datatable(): void
    {
        Dokumen::factory()->create(['nomor_agenda' => '1', 'current_handler' => 'akutansi', 'status' => 'sent_to_akutansi']);

        $response = $this->actingAs($this->akutansi())->getJson(route('documents.akutansi.data'));

        $response->assertOk()
            ->assertJsonStructure([
                'last_page',
                'total',
                'data' => [['id', 'nomor_agenda', 'status_badge', 'deadline', 'handler_options']],
            ]);
    }

    public function test_baris_memuat_objek_status_badge_dan_deadline(): void
    {
        Dokumen::factory()->create(['nomor_agenda' => '2', 'current_handler' => 'operator', 'status' => 'draft']);

        $response = $this->actingAs($this->akutansi())->getJson(route('documents.akutansi.data'));

        $first = $response->json('data.0');
        $this->assertArrayHasKey('class', $first['status_badge']);
        $this->assertArrayHasKey('variant', $first['deadline']);
    }

    public function test_endpoint_data_menolak_tamu(): void
    {
        $this->getJson(route('documents.akutansi.data'))->assertUnauthorized();
    }
}
```

> Bila `Dokumen::factory()`/`User::factory()` field wajib berbeda, sesuaikan dengan factory yang ada (lihat `database/factories/`). Jangan mengubah factory demi test ini.

- [ ] **Step 2: Jalankan test — pastikan gagal**

Run: `php artisan test tests/Feature/AkutansiDatatableTest.php`
Expected: FAIL — route `documents.akutansi.data` belum ada (RouteNotFoundException).

- [ ] **Step 3: Ekstrak query + tambah method `datatable()` + handler options**

Di `DashboardAkutansiController.php`: (a) ekstrak blok pembangun query dari `dokumens()` (baris ~42-221: base query + search + filter dari/tanggal/nilai + switch status + eager-load + sort) menjadi `private function buildAkutansiQuery(Request $request): \Illuminate\Database\Eloquent\Builder`, lalu `dokumens()` memanggilnya (ganti `$query = Dokumen::query()->...` menjadi `$query = $this->buildAkutansiQuery($request);` dan hapus blok yang dipindah; SISakan bagian pagination/transform/stats apa adanya). (b) Tambah `datatable()` + `buildAkutansiHandlerOptions()`.

Tambahkan method berikut ke kelas (letakkan tepat sebelum `dokumens()`):

```php
    /**
     * Endpoint JSON untuk tabel Tabulator akutansi. Membalas {last_page,total,data}
     * (nama field cocok progressiveLoad Tabulator). Memakai ulang query & eager-load
     * yang SAMA dengan dokumens() lewat buildAkutansiQuery(), lalu memetakan tiap
     * baris via AkutansiDocumentRow (badge Status & Deadline dihitung server).
     */
    public function datatable(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = $this->buildAkutansiQuery($request);

        $size = (int) $request->input('size', 100);
        $size = ($size > 0 && $size <= 200) ? $size : 100;
        $page = max(1, (int) $request->input('page', 1));

        $paginator = $query->paginate($size, ['*'], 'page', $page);

        $handlerOptions = $this->buildAkutansiHandlerOptions();
        $viewerRole = Auth::user()?->role;

        $data = collect($paginator->items())
            ->map(fn ($d) => \App\Support\AkutansiDocumentRow::fromDokumen($d, $handlerOptions, $viewerRole))
            ->all();

        return response()->json([
            'last_page' => $paginator->lastPage(),
            'total'     => $paginator->total(),
            'data'      => $data,
        ]);
    }

    /**
     * Opsi pengurus dokumen (handler_options) SEKALI per-request: 5 peran base +
     * optgroup Bagian bila ada. Ditanam apa adanya oleh AkutansiDocumentRow.
     * Bentuk identik DokumenController::buildHandlerOptions() (sumber tunggal bentuk).
     */
    private function buildAkutansiHandlerOptions(): array
    {
        $handlerOptions = [
            ['value' => 'operator',        'label' => 'Operator'],
            ['value' => 'team_verifikasi', 'label' => 'Tim Verifikasi'],
            ['value' => 'perpajakan',      'label' => 'Tim Perpajakan'],
            ['value' => 'akutansi',        'label' => 'Tim Akuntansi'],
            ['value' => 'pembayaran',      'label' => 'Tim Pembayaran'],
        ];
        $bagian = \App\Models\Bagian::active()->ordered()->get(['kode', 'nama']);
        if ($bagian->isNotEmpty()) {
            $handlerOptions[] = [
                'optgroup' => 'Bagian',
                'options'  => $bagian->map(fn ($b) => [
                    'value' => 'bagian_' . strtolower($b->kode),
                    'label' => $b->nama ?: $b->kode,
                ])->all(),
            ];
        }

        return $handlerOptions;
    }
```

Lalu bungkus blok query ke method privat. Ekstrak baris ~42-221 `dokumens()` menjadi:

```php
    /**
     * Pembangun query daftar dokumen akutansi (cross-role visibility) —
     * SUMBER TUNGGAL dipakai dokumens() (view) & datatable() (JSON). Meliputi
     * base query, search, filter (dari/tanggal/nilai), switch status 5 bucket,
     * eager-load (roleData akutansi-only + roleStatuses semua role terkait +
     * dokumenPos/dokumenPrs/dibayarKepadas), dan sort natural nomor_agenda.
     *
     * PENTING: roleData sengaja di-load HANYA role_code='akutansi' (paritas
     * tampilan lama; AkutansiDocumentRow bergantung padanya). Jangan diperluas.
     */
    private function buildAkutansiQuery(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        $hasImportedFromCsvColumn = \Schema::hasColumn('dokumens', 'imported_from_csv');

        $query = Dokumen::query()
            ->where('status', '!=', 'returned_to_bidang')
            ->excludeCsvImports()
            ->with(['dokumenPos', 'dokumenPrs', 'dibayarKepadas']);

        // ... (PINDAHKAN VERBATIM seluruh isi search + filter + switch status +
        //      eager-load roleData/roleStatuses + blok sort dari dokumens():48-221) ...

        return $query;
    }
```

> **Instruksi ekstraksi (mekanis, tanpa perubahan perilaku):**
> 1. Salin baris `dokumens()` saat ini dari `$hasImportedFromCsvColumn = ...` (∼:40) sampai AKHIR blok sort (∼:221, tepat SEBELUM `$perPage = $request->get('per_page', 'all');` di :223) ke dalam `buildAkutansiQuery()`, ganti `$dokumens = $query->orderByRaw(...)...` di blok sort menjadi menetapkan ke `$query` lalu `return $query;`. Blok sort saat ini menetapkan `$dokumens`; ubah agar sort di-`->orderByRaw(...)` diterapkan pada `$query` (chain) dan method mengembalikan `$query` (BUKAN paginator).
> 2. Di `dokumens()`, ganti blok yang dipindah dengan satu baris `$query = $this->buildAkutansiQuery($request);` lalu LANJUTKAN dengan `$perPage = ...; ... $dokumens = $query->orderBy('dokumens.id','DESC')->paginate(...)` seperti semula (baris :223-233 tetap, memakai `$query`). Pastikan variabel `$sortColumn`/`$sortOrder` yang dipakai di `$data` (:498-499) tetap tersedia — bila sort dihitung di dalam `buildAkutansiQuery()`, kembalikan juga via session (sudah disimpan ke session di :196/:230) ATAU hitung ulang `$sortColumn = session('akutansi_sort_column','nomor_agenda')` di `dokumens()`. Pilih cara paling minim perubahan; JANGAN mengubah logika sort.
> 3. Jalankan test setelah ekstraksi untuk membuktikan `dokumens()` masih setara.

Tambahkan route di `routes/web.php` DALAM grup akutansi (setelah baris `Route::get('/', ...)->name('index');`, SEBELUM route `{dokumen}/detail` agar `/data` tak tertangkap wildcard):

```php
Route::get('/data', [DashboardAkutansiController::class, 'datatable'])->name('data');
```

- [ ] **Step 4: Jalankan test — pastikan lulus**

Run: `php artisan test tests/Feature/AkutansiDatatableTest.php`
Expected: PASS (3 test).

- [ ] **Step 5: Jalankan seluruh suite (buktikan `dokumens()` tak regresi)**

Run: `php artisan test`
Expected: PASS penuh (termasuk `AkutansiHapusAksiMatiTest` yang me-render `dokumens()`).

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/DashboardAkutansiController.php routes/web.php tests/Feature/AkutansiDatatableTest.php
git commit -m "feat(akutansi): endpoint JSON documents.akutansi.data + ekstrak buildAkutansiQuery"
```

---

## Task 5: Engine `document-tabulator.js` — kolom tetap terparameter + formatter akutansi

**Files:**
- Modify: `public/js/document-tabulator.js`

**Interfaces:**
- Consumes: `CFG.extraColumns` (baru; opsional) — array `[{field, title, formatter}]` di mana `formatter` ∈ `{'deadline','akutansiStatus'}`. Objek baris menyediakan `row.status_badge` & `row.deadline` (Task 3).
- Produces: `buildColumns()` menyisipkan kolom `extraColumns` (non-editable) ANTARA kolom kustomisasi dan kolom Pengurus Dokumen. Dua formatter baru: `fmtDeadline(cell)` merender `row.deadline`; `fmtAkutansiStatus(cell)` merender `row.status_badge`. Registry `EXTRA_FORMATTERS`.
- **Paritas operator:** CFG operator TIDAK punya `extraColumns` → `(cfg.extraColumns || [])` kosong → susunan kolom operator TAK BERUBAH.

- [ ] **Step 1: Tambah formatter Status akutansi + Deadline (dumb renderer objek server)**

Di `public/js/document-tabulator.js`, tepat SETELAH `fmtStatus` (∼:545), tambahkan:

```javascript
  // === Formatter akutansi (Rollout 1) — merender objek server, nol logika bisnis. ===

  // Badge Status akutansi dari row.status_badge {class, icon, text, link}.
  // Port kolom Status _rows.blade.php:459-510 (pohon keputusan sudah di server).
  function fmtAkutansiStatus(cell) {
    const b = cell.getRow().getData().status_badge;
    if (!b || !b.class) return '-';
    let html = '<span class="badge-status ' + esc(b.class) + '" onclick="event.stopPropagation()">';
    if (b.icon) { html += '<i class="fa-solid ' + esc(b.icon) + ' me-1"></i>'; }
    html += esc(b.text);
    if (b.link && b.link.href) {
      html += ' <a href="' + esc(b.link.href) + '" class="text-white text-decoration-underline fw-bold" ' +
        'style="color:#fff !important;text-decoration:underline !important;font-weight:600 !important;" ' +
        'onclick="event.stopPropagation()">' + esc(b.link.text) + '</a>';
    }
    html += '</span>';
    return html;
  }

  // Kolom Deadline akutansi dari row.deadline. Port _rows.blade.php:291-413
  // (kartu umur / bypass / belum-diterima) — semua nilai sudah dihitung server.
  function fmtDeadline(cell) {
    const d = cell.getRow().getData().deadline;
    if (!d || d.variant === 'none') {
      return '<div class="no-deadline"><i class="fa-solid fa-clock"></i><span>Belum diterima</span></div>';
    }
    if (d.variant === 'sent_fallback') {
      return '<div class="deadline-card deadline-sent deadline-gray">' +
        '<div class="deadline-label" style="font-size:10px;color:#6b7280;font-weight:600;">' +
        '<i class="fa-solid fa-paper-plane"></i> Terkirim ke Pembayaran</div></div>';
    }
    // variant === 'card'
    let html = '<div class="deadline-card deadline-' + esc(d.type) + ' deadline-' + esc(d.color) + '">';
    html += '<div class="deadline-time"><i class="fa-solid fa-calendar"></i><span>' + esc(d.received_display) + '</span></div>';
    html += '<div class="deadline-indicator deadline-' + esc(d.color) + '"><i class="fa-solid ' + esc(d.indicator_icon) + '"></i>' +
      '<span class="status-text">' + esc(d.indicator_label) + '</span></div>';
    html += '<div class="deadline-age" style="font-size:10px;color:#6b7280;margin-top:4px;">' +
      '<i class="fa-solid fa-hourglass-half"></i><span>' + esc(d.age_text) + '</span></div>';
    if (d.footer) {
      if (d.footer.kind === 'paused') {
        html += '<div class="deadline-paused-label"><i class="fa-solid ' + esc(d.footer.icon) + '"></i> ' + esc(d.footer.text) + '</div>';
      } else {
        html += '<div class="deadline-label" style="font-size:8px;color:#6b7280;margin-top:4px;font-weight:600;">' +
          '<i class="fa-solid ' + esc(d.footer.icon) + '"></i> ' + esc(d.footer.text) + '</div>';
      }
    }
    html += '</div>';
    return html;
  }

  // Registry formatter kolom tetap terparameter (CFG.extraColumns[].formatter).
  const EXTRA_FORMATTERS = {
    deadline: fmtDeadline,
    akutansiStatus: fmtAkutansiStatus,
  };
```

- [ ] **Step 2: Sisipkan `extraColumns` di `buildColumns()`**

Di `buildColumns(cfg)` (∼:641), TEPAT SEBELUM baris `cols.push({ title: 'Pengurus Dokumen', ... })` (∼:661), sisipkan:

```javascript
    // Kolom tetap terparameter per-role (mis. akutansi: Deadline + Status). Tanpa
    // editor Tabulator; formatter merender objek server. Operator tak mengirim
    // extraColumns → tak ada kolom tambahan (paritas).
    (cfg.extraColumns || []).forEach(function (ec) {
      cols.push({
        title: ec.title,
        field: ec.field,
        formatter: EXTRA_FORMATTERS[ec.formatter] || fmtPlain,
        editable: false,
      });
    });
```

- [ ] **Step 3: Verifikasi sintaks JS**

Run: `node --check public/js/document-tabulator.js`
Expected: exit 0 (tanpa output).

- [ ] **Step 4: Verifikasi operator tak regresi (suite backend + syntax sudah; QA visual = user)**

Run: `php artisan test --filter="OperatorDatatable"`
Expected: PASS. (Perubahan JS tak menyentuh jalur operator; `extraColumns` operator kosong.)

- [ ] **Step 5: Commit**

```bash
git add public/js/document-tabulator.js
git commit -m "feat(tabulator): kolom tetap terparameter (extraColumns) + formatter Deadline & Status akutansi"
```

---

## Task 6: View `daftarAkutansiTabulator.blade.php` + CSS Deadline/Badge

**Files:**
- Create: `resources/views/akutansi/dokumens/daftarAkutansiTabulator.blade.php`
- Create: `public/css/akutansi-deadline-badge.css`

**Interfaces:**
- Consumes: `CFG.extraColumns`/formatter dari Task 5; endpoint `documents.akutansi.data` dari Task 4; partial global `document-workbench-ui`/`compact-document-ui` (butuh `#documentTableContainer`); modal kustomisasi kolom + JS diduplikasi dari `daftarDokumenTabulator.blade.php` (self-contained; lihat utang de-dup di Peta Berkas).
- Produces: view yang meng-emit `window.DOCUMENT_TABULATOR_CONFIG` dengan `mountId:'akutansiTabulatorTable'`, `dataUrl: route('documents.akutansi.data')`, `handlerTpl`/`inlineUpdateTpl` bersama, `columns`/`availableColumns`/`selected` akutansi, `extraColumns` Deadline+Status; mount `<div id="akutansiTabulatorTable" class="doc-tabulator">` di `#documentTableContainer`. TANPA tombol Tambah/Hapus (akutansi tak punya create/destroy). TIDAK menyentuh view operator.

- [ ] **Step 1: Port CSS Deadline+Badge ke berkas terpisah (verbatim)**

Buat `public/css/akutansi-deadline-badge.css`. Salin VERBATIM aturan CSS dari `resources/views/akutansi/dokumens/daftarAkutansi.blade.php`:
- Blok Deadline: semua aturan `.deadline-card*`, `.deadline-time*`, `.deadline-indicator*`, `.deadline-age`, `.deadline-label`, `.deadline-paused-label`, `.no-deadline*`, dan varian `@media` responsifnya — rentang baris **264–849**.
- Blok Badge: semua aturan `.badge-status*` — rentang baris **851–940** dan varian `@media` di **~1307** (blok `.badge-status` responsif).

JANGAN menyalin aturan aksi mati (`.col-action`, `.btn-action`, `.btn-send`, `.btn-set-deadline`, dsb). JANGAN menyalin `.badge.badge-*` non-`badge-status` (mis. :1621 — itu dipakai kartu bento, bukan kolom Status).

Verifikasi inventaris selektor setelah menyalin — berkas WAJIB memuat minimal: `.deadline-card`, `.deadline-card.deadline-green`, `.deadline-card.deadline-yellow`, `.deadline-card.deadline-red`, `.deadline-card.deadline-sent`, `.deadline-card.deadline-paused`, `.deadline-card.deadline-completed`, `.deadline-indicator`, `.deadline-time`, `.no-deadline`, `.deadline-paused-label`, `.badge-status`, `.badge-status.badge-proses`, `.badge-status.badge-sent`, `.badge-status.badge-belum`, `.badge-status.badge-selesai`, `.badge-status.badge-locked`, `.badge-status.badge-warning`, `.badge-status.badge-dikembalikan`.

Run (cek inventaris): `grep -oE '\.(deadline-[a-z-]+|badge-status(\.[a-z-]+)?|no-deadline)' public/css/akutansi-deadline-badge.css | sort -u`
Expected: memuat semua selektor di daftar di atas.

- [ ] **Step 2: Buat view Tabulator akutansi (self-contained; modal kustomisasi diduplikasi)**

Buat `resources/views/akutansi/dokumens/daftarAkutansiTabulator.blade.php`. Struktur meniru `daftarDokumenTabulator.blade.php` TETAPI: dataUrl akutansi, TANPA tombol Tambah/Hapus & tanpa `inlineCreateUrl`/`destroyTpl`, dengan `extraColumns` Deadline+Status, memuat CSS Deadline/Badge, dan toolbar filter memakai nama param yang dibaca `buildAkutansiQuery()` (`search`, `status`, `filter_dari`). Modal Kustomisasi Kolom + JS-nya DISALIN dari `daftarDokumenTabulator.blade.php` (self-contained; utang de-dup sesuai §7) dengan DUA penyesuaian: (a) `#filterForm` action = `route('documents.akutansi.index')`; (b) `appendActiveFilterInputs()` membawa field toolbar akutansi (`search`, `status`, `filter_dari`) — BUKAN `year`/`status_filter` milik operator.

Rangka view (bagian atas):

```blade
@extends('layouts.app')

@section('content')
{{--
  View Tabulator (Rollout 1) untuk Daftar Dokumen Akutansi. Meniru view operator
  tetapi: endpoint documents.akutansi.data, kolom tetap Deadline + Status (via
  extraColumns), TANPA Tambah/Hapus (akutansi tak punya create/destroy). Tabel
  di-mount public/js/document-tabulator.js membaca window.DOCUMENT_TABULATOR_CONFIG.
  Modal Kustomisasi Kolom diduplikasi dari view operator (utang de-dup §7).
--}}
@php
    $selectedColumns = $selectedColumns ?? [];
    $availableColumns = $availableColumns ?? [];

    $configArray = [
        'mountId'          => 'akutansiTabulatorTable',
        'dataUrl'          => route('documents.akutansi.data'),
        'inlineUpdateTpl'  => str_replace('__ID__', '{id}', route('documents.inline-update', ['dokumen' => '__ID__'])),
        'handlerTpl'       => str_replace('__ID__', '{id}', route('documents.handler.update', ['dokumen' => '__ID__'])),
        'csrf'             => csrf_token(),
        'columns'          => collect($selectedColumns)->map(fn ($k) => ['key' => $k, 'label' => $availableColumns[$k] ?? $k])->values(),
        'availableColumns' => $availableColumns,
        'selected'         => array_values($selectedColumns),
        // Kolom tetap khas akutansi: Deadline + Status (dirender formatter server-object).
        'extraColumns'     => [
            ['field' => 'deadline',     'title' => 'Deadline', 'formatter' => 'deadline'],
            ['field' => 'status_badge', 'title' => 'Status',   'formatter' => 'akutansiStatus'],
        ],
        'ie'               => [
            'kategori' => $ieKategoriList ?? [],
            'sub'      => $ieSubKriteriaList ?? [],
            'item'     => $ieItemSubKriteriaList ?? [],
            'jenis'    => $ieJenisPembayaranList ?? [],
            'bagian'   => \App\Models\Bagian::active()->ordered()->get(['kode', 'nama']),
        ],
        'bulanList'        => ['Januari', 'Februari', 'Maret', 'April', 'May', 'Juni', 'July', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
    ];
@endphp

<div class="tabulator-page">
    <div class="tabulator-toolbar">
        <input type="text" name="search" class="form-control tabulator-toolbar-search"
               placeholder="Cari dokumen..." autocomplete="off" value="{{ request('search') }}">

        <select name="status" class="form-select" style="max-width: 240px;">
            <option value="">Semua Status</option>
            <option value="sedang_proses" {{ request('status') == 'sedang_proses' ? 'selected' : '' }}>Sedang Diproses</option>
            <option value="menunggu_approve" {{ request('status') == 'menunggu_approve' ? 'selected' : '' }}>Menunggu Approve</option>
            <option value="terkirim_pembayaran" {{ request('status') == 'terkirim_pembayaran' ? 'selected' : '' }}>Terkirim ke Pembayaran</option>
            <option value="terkirim" {{ request('status') == 'terkirim' ? 'selected' : '' }}>Terkirim</option>
            <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
        </select>

        <select name="filter_dari" class="form-select" style="max-width: 200px;">
            <option value="">Semua Bagian</option>
            @foreach(($filterDariOptions ?? []) as $bagianVal => $bagianLabel)
                <option value="{{ $bagianVal }}" {{ request('filter_dari') == $bagianVal ? 'selected' : '' }}>{{ $bagianLabel }}</option>
            @endforeach
        </select>

        <button type="button" class="btn btn-outline-secondary" onclick="openColumnCustomizationModal()">
            <i class="fa-solid fa-table-columns me-1"></i> Kustomisasi Kolom
        </button>
    </div>

    {{-- id="documentTableContainer" WAJIB: partial global document-workbench-ui bergantung padanya --}}
    <div class="table-section table-dokumen" id="documentTableContainer">
        <div id="akutansiTabulatorTable" class="doc-tabulator"></div>
    </div>
</div>

{{-- Form GET tersembunyi untuk kustomisasi kolom (reload view Tabulator dgn kolom baru). --}}
<form action="{{ route('documents.akutansi.index') }}" method="GET" id="filterForm" class="d-none"></form>

{{-- ==== Modal Kustomisasi Kolom — SALIN VERBATIM blok HTML daftarDokumenTabulator.blade.php:205-314 ==== --}}
{{-- (salin apa adanya; struktur identik, memakai $availableColumns + $selectedColumns) --}}
@endsection
```

Lalu SALIN blok berikut dari `daftarDokumenTabulator.blade.php` ke view akutansi APA ADANYA:
- HTML modal kustomisasi (`<div class="customization-modal" id="columnCustomizationModal">...`) — baris **206-314** — tempel di atas `@endsection`.
- `@push('styles')` blok `<style>` modal kustomisasi — baris **328-398** — tempel ke `@push('styles')` view akutansi, DI ATAS `</style>` toolbar berikut:

```blade
@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/tabulator/tabulator.min.css') }}">
    <link rel="stylesheet" href="{{ \App\Support\Asset::versioned('css/tabulator-agenda.css') }}">
    <link rel="stylesheet" href="{{ \App\Support\Asset::versioned('css/akutansi-deadline-badge.css') }}">
    <style>
    .tabulator-toolbar { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; margin-bottom: 16px; }
    .tabulator-toolbar-search { max-width: 320px; }
    /* + SALIN VERBATIM aturan modal kustomisasi (.customization-modal ... dst) dari
       daftarDokumenTabulator.blade.php:329-397 KE SINI. */
    </style>
@endpush
```

- `@push('scripts')` pemuat Tabulator + engine:

```blade
@push('scripts')
    <script>window.DOCUMENT_TABULATOR_CONFIG = @json($configArray);</script>
    <script src="{{ asset('vendor/tabulator/tabulator.min.js') }}"></script>
    <script src="{{ \App\Support\Asset::versioned('js/document-tabulator.js') }}"></script>
@endpush
```

- `@push('scripts')` blok JS kustomisasi kolom — baris **460-721** — SALIN APA ADANYA, KECUALI fungsi `appendActiveFilterInputs(filterForm)` yang harus diganti agar membawa field toolbar akutansi (`search`, `status`, `filter_dari`):

```javascript
    // Bawa filter toolbar akutansi (search/status/filter_dari) agar tak hilang saat reload GET.
    function appendActiveFilterInputs(filterForm) {
        filterForm.querySelectorAll('input[name="search"], input[name="status"], input[name="filter_dari"]').forEach(input => input.remove());
        const searchEl = document.querySelector('.tabulator-toolbar input[name="search"]');
        const statusEl = document.querySelector('.tabulator-toolbar select[name="status"]');
        const dariEl   = document.querySelector('.tabulator-toolbar select[name="filter_dari"]');
        [['search', searchEl], ['status', statusEl], ['filter_dari', dariEl]].forEach(([name, el]) => {
            if (el && el.value) {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = name;
                hiddenInput.value = el.value;
                filterForm.appendChild(hiddenInput);
            }
        });
    }
```

> Jangan salin fungsi Hapus/Detail dari view operator (akutansi tak punya create/destroy). Hanya blok Kustomisasi Kolom (`selectedColumnsOrder`, `openColumnCustomizationModal`, `toggleColumn`, drag-drop, `saveColumnCustomization`, dll) + `appendActiveFilterInputs` versi akutansi di atas.

- [ ] **Step 3: Verifikasi sintaks & suite (view belum tersambung sampai Task 7)**

Run: `node --check public/js/document-tabulator.js`
Expected: exit 0.

Run: `php artisan test`
Expected: PASS (view belum dirujuk `dokumens()`; belum ada test baru gagal). Render view diverifikasi di Task 7 (saat `dokumens()` menyajikannya).

- [ ] **Step 4: Commit**

```bash
git add public/css/akutansi-deadline-badge.css resources/views/akutansi/dokumens/daftarAkutansiTabulator.blade.php
git commit -m "feat(akutansi): view Tabulator + CSS Deadline/Badge (modal kustomisasi self-contained)"
```

---

## Task 7: Transisi — `dokumens()` menyajikan Tabulator default, `?classic` → legacy

**Files:**
- Modify: `app/Http/Controllers/DashboardAkutansiController.php` (akhir `dokumens()`, baris ~506)
- Test: `tests/Feature/AkutansiTabulatorSwitchTest.php`

**Interfaces:**
- Consumes: view baru (Task 6), endpoint (Task 4).
- Produces: `dokumens()` mengembalikan `akutansi.dokumens.daftarAkutansiTabulator` secara default; `?classic=1` mengembalikan `akutansi.dokumens.daftarAkutansi` (legacy) untuk banding QA.

- [ ] **Step 1: Tulis test yang gagal**

Buat `tests/Feature/AkutansiTabulatorSwitchTest.php` (sertakan SQLite polyfill setUp seperti Task 4):

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AkutansiTabulatorSwitchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        if (DB::connection()->getDriverName() === 'sqlite') {
            $pdo = DB::connection()->getPdo();
            $pdo->sqliteCreateFunction('REGEXP', fn ($p, $v) => preg_match('/' . $p . '/', (string) $v) ? 1 : 0, 2);
            $pdo->sqliteCreateFunction('SUBSTRING_INDEX', function ($s, $d, $c) {
                return implode($d, array_slice(explode($d, (string) $s), 0, (int) $c));
            }, 3);
            $pdo->sqliteCreateFunction('LPAD', fn ($s, $l, $p) => str_pad((string) $s, (int) $l, (string) $p, STR_PAD_LEFT), 3);
        }
    }

    private function akutansi(): User
    {
        return User::factory()->create(['role' => 'akutansi']);
    }

    public function test_default_menyajikan_view_tabulator(): void
    {
        $response = $this->actingAs($this->akutansi())->get(route('documents.akutansi.index'));
        $response->assertOk()
            ->assertSee('akutansiTabulatorTable', false)
            ->assertSee('DOCUMENT_TABULATOR_CONFIG', false);
    }

    public function test_classic_menyajikan_view_legacy(): void
    {
        $response = $this->actingAs($this->akutansi())->get(route('documents.akutansi.index', ['classic' => 1]));
        $response->assertOk()
            ->assertDontSee('akutansiTabulatorTable', false);
    }
}
```

- [ ] **Step 2: Jalankan test — pastikan gagal**

Run: `php artisan test tests/Feature/AkutansiTabulatorSwitchTest.php`
Expected: FAIL — `test_default_menyajikan_view_tabulator` gagal (masih view legacy, tak ada `akutansiTabulatorTable`).

- [ ] **Step 3: Switch view di `dokumens()`**

Di `DashboardAkutansiController@dokumens()`, ganti baris terakhir `return view('akutansi.dokumens.daftarAkutansi', $data);` (~:506) dengan:

```php
        // Transisi Rollout 1: default → Tabulator; ?classic=1 → view legacy (banding QA).
        // Setelah QA akutansi lolos, cabang legacy + flag ini DIHAPUS (Tugas 8).
        if ($request->boolean('classic')) {
            return view('akutansi.dokumens.daftarAkutansi', $data);
        }

        return view('akutansi.dokumens.daftarAkutansiTabulator', $data);
```

- [ ] **Step 4: Jalankan test — pastikan lulus**

Run: `php artisan test tests/Feature/AkutansiTabulatorSwitchTest.php`
Expected: PASS (2 test).

- [ ] **Step 5: Jalankan seluruh suite**

Run: `php artisan test`
Expected: PASS penuh. Catatan: `AkutansiHapusAksiMatiTest::test_halaman_akutansi_render_tanpa_ui_aksi_mati` me-render `documents.akutansi.index` TANPA `?classic` → kini merender view TABULATOR, bukan legacy. Bila test itu meng-assert elemen khas legacy (mis. `columnCustomizationModal`, `Deadline`), sesuaikan test itu untuk memakai `?classic=1` (agar tetap menguji view legacy sampai Tugas 8) ATAU perbarui assert-nya ke elemen view Tabulator. Pilih: tambah `['classic' => 1]` pada request di test tersebut sehingga tetap menjaga legacy sampai dihapus. Perubahan test ini masuk commit Task 7.

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/DashboardAkutansiController.php tests/Feature/AkutansiTabulatorSwitchTest.php tests/Feature/AkutansiHapusAksiMatiTest.php
git commit -m "feat(akutansi): dokumens() sajikan Tabulator default, ?classic untuk banding QA"
```

- [ ] **Step 7: Deploy untuk QA user (GERBANG)**

Ini menyentuh routing yang dilihat user. Deploy per alur baku lalu SERAHKAN QA ke user (agent tak punya browser/sesi login):

```bash
git push origin codinggemini
# di server: git pull && php artisan route:clear && php artisan view:clear && php artisan config:clear
```

**BERHENTI** di sini. Minta user QA `/documents/akutansi` (Tabulator) vs `/documents/akutansi?classic=1` (legacy). Daftar cek QA ada di §Verifikasi. **Tugas 8 hanya jalan setelah user konfirmasi lolos.**

---

## Task 8: Hapus legacy akutansi (PASCA-QA — GATED oleh konfirmasi user)

> **JANGAN mulai Tugas 8 sebelum user menyatakan QA akutansi Tabulator lolos.** Ini penghapusan view/partial (CLAUDE.md §6).

**Files:**
- Delete: `resources/views/akutansi/dokumens/daftarAkutansi.blade.php`
- Delete: `resources/views/akutansi/dokumens/_rows.blade.php`
- Delete: `resources/views/akutansi/dokumens/_chunk.blade.php`
- Modify: `app/Http/Controllers/DashboardAkutansiController.php` — hapus cabang `?classic`, cabang `virtual_chunk`, flag.

**Interfaces:**
- Produces: `dokumens()` hanya menyajikan Tabulator (tanpa `?classic`/`virtual_chunk`). Route `documents.akutansi.index`/`detail`/`data` tetap.

- [ ] **Step 1: Grep gate lintas-role (bukti sebelum hapus)**

Run:
```bash
grep -rn "dokumens/_rows\|dokumens\._rows\|dokumens/_chunk\|dokumens\._chunk\|daftarAkutansi'\|daftarAkutansi\"" resources/ app/ routes/
grep -rn "virtual_chunk" app/Http/Controllers/DashboardAkutansiController.php
```
Expected: satu-satunya perujuk `akutansi.dokumens._rows`/`_chunk`/`daftarAkutansi` adalah `dokumens()` (cabang classic/virtual) yang akan dihapus. Partial `_rows`/`_chunk` role LAIN berkas berbeda — pastikan tak ada yang mengimpor berkas akutansi. Bila ada perujuk tak terduga → BERHENTI, laporkan.

- [ ] **Step 2: Hapus cabang classic + virtual_chunk di `dokumens()`**

Hapus blok `if ($request->boolean('virtual_chunk')) { return view('akutansi.dokumens._chunk', ...); }` (~:346-351) dan cabang `if ($request->boolean('classic')) { return view('akutansi.dokumens.daftarAkutansi', $data); }` (Task 7). `dokumens()` diakhiri `return view('akutansi.dokumens.daftarAkutansiTabulator', $data);`.

- [ ] **Step 3: Hapus berkas view legacy**

```bash
git rm resources/views/akutansi/dokumens/daftarAkutansi.blade.php resources/views/akutansi/dokumens/_rows.blade.php resources/views/akutansi/dokumens/_chunk.blade.php
```

- [ ] **Step 4: Perbarui test yang bergantung pada legacy**

`AkutansiHapusAksiMatiTest` & `AkutansiTabulatorSwitchTest::test_classic_menyajikan_view_legacy` kini merujuk view yang dihapus. Perbarui: hapus `test_classic_menyajikan_view_legacy`, dan ubah assert `AkutansiHapusAksiMatiTest` agar menguji view Tabulator (elemen aksi mati memang sudah tak ada di Tabulator — assert `assertDontSee` tetap valid; hapus `?classic`).

- [ ] **Step 5: Jalankan seluruh suite**

Run: `php artisan test`
Expected: PASS penuh.

- [ ] **Step 6: Verifikasi tak ada referensi yatim**

Run: `grep -rn "daftarAkutansi\b\|akutansi.dokumens._rows\|akutansi.dokumens._chunk\|boolean('classic')\|virtual_chunk" resources/ app/`
Expected: kosong untuk akutansi (kecuali nama berkas Tabulator `daftarAkutansiTabulator`).

- [ ] **Step 7: Commit + Deploy**

```bash
git add app/Http/Controllers/DashboardAkutansiController.php tests/Feature/AkutansiHapusAksiMatiTest.php tests/Feature/AkutansiTabulatorSwitchTest.php
git commit -m "refactor(akutansi): hapus view legacy + cabang classic/virtual_chunk pasca-QA Tabulator"
# git rm sudah men-stage penghapusan; masuk ke commit yang sama bila belum:
git commit --amend --no-edit 2>/dev/null || true
git push origin codinggemini
# server: git pull && php artisan route:clear && php artisan view:clear && php artisan config:clear
```

> Catatan: CSS Deadline/Badge kini HANYA hidup di `public/css/akutansi-deadline-badge.css` (view legacy yang memuat salinan inline-nya sudah terhapus). Tak ada aksi tambahan.

---

## Verifikasi (dilaporkan verbatim; sebagian QA = tanggung jawab user)

1. **`php artisan test` hijau** di tiap tahap (Task 1-8). Ekstraksi basis dijaga `OperatorDatatableTest`/`OperatorInlineCreateRowTest`/`InlineCreateDokumenTest`; DTO akutansi oleh `AkutansiDocumentRowTest`; endpoint oleh `AkutansiDatatableTest`; switch oleh `AkutansiTabulatorSwitchTest`.
2. **`node --check public/js/document-tabulator.js`** exit 0 (Task 5).
3. **QA visual operator (user)** — ekstraksi basis menyentuh backend operator: tabel operator IDENTIK (nav panah/auto-scroll, klik pindah sel, Enter edit, Ctrl+C/V, Delete kaskade, Ctrl+Z/Y, blok drag/Shift, dropdown Pengurus, Tambah/Hapus baris, kustomisasi kolom).
4. **QA visual akutansi (user)** — `/documents/akutansi` → Tabulator:
   - Kolom Deadline benar (umur naik, warna hijau/kuning/merah, ⏸️ untuk terkirim/selesai, "Belum diterima", bypass "Terkirim").
   - Badge Status benar (Draft, Menunggu Approval dari Pembayaran, Terkirim ke Pembayaran, Terkunci, Selesai, Kembali ke Team Verifikasi, Sedang/Belum Diproses, Dikembalikan, "Dokumen ditolak, cek disini" + link).
   - Dropdown Pengurus (forward/kembalikan) jalan; inline-edit jalan; kustomisasi kolom jalan; search/filter (status/dari) jalan; lintas-role "Di {role}" benar; **0 error konsol**.
   - `?classic=1` → view lama (banding) sampai Tugas 8.
   - **Paritas bypass (flag khusus):** untuk dokumen yang MELEWATI akutansi langsung ke pembayaran, kartu Deadline harus tampak SAMA seperti tabel lama (roleData pembayaran/verifikasi sengaja tak dimuat → parity). Bila user melihat perbedaan, laporkan sebelum Tugas 8.

## Deploy
Per tahap disetujui: commit per-file → push → `git pull` server → `php artisan route:clear && view:clear && config:clear`. Penghapusan legacy (Tugas 8) hanya SETELAH user konfirmasi QA akutansi lolos.

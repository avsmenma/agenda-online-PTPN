# Rollout Tabulator ke Role Perpajakan — Rencana Implementasi

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Mengganti tabel classic perpajakan dengan engine Tabulator bersama (Rollout 2), memakai ulang infrastruktur yang sudah dibangun di Rollout 1 (akutansi) — basis DTO `App\Support\DocumentRow`, engine generik `document-tabulator.js` (`extraColumns` + formatter Deadline/Status), lalu menghapus view legacy perpajakan + kode aksi mati.

**Architecture:** DTO baru `App\Support\PerpajakanDocumentRow extends DocumentRow` menambah `status_badge` & `deadline` (di-port dari `perpajakan/dokumens/_rows.blade.php`, dihitung server). Endpoint JSON `documents.perpajakan.data` memakai ulang query `dokumens()` (diekstrak `buildPerpajakanQuery()`) dan memetakan baris via DTO. Engine Tabulator TIDAK diubah (sudah generik sejak Rollout 1). View `daftarPerpajakanTabulator.blade.php` meniru `daftarAkutansiTabulator.blade.php`. Transisi `?classic`; view lama + kode aksi mati dihapus setelah QA.

**Tech Stack:** Laravel 12, PHP ^8.2, MySQL 8, Blade, Tabulator.js 6.3.1 (self-hosted), Bootstrap 5 (CDN), PHPUnit (SQLite in-memory).

## Global Constraints

- **Reuse, jangan bangun ulang.** Basis `App\Support\DocumentRow`, engine `public/js/document-tabulator.js` (`extraColumns`, formatter `fmtDeadline`/`fmtAkutansiStatus`, filter dari DOM), dan pola akutansi SUDAH ADA. Engine TIDAK boleh diubah untuk rollout ini. **Jangan salinan ke-7** logika bersama.
- **Nol logika bisnis di JS.** Pohon Status & Deadline dihitung di `PerpajakanDocumentRow` (server); klien merender objek. Formatter engine yang ada (`akutansiStatus`/`deadline`) dipakai apa adanya — objek `status_badge`/`deadline` perpajakan HARUS berbentuk sama dengan akutansi (`status_badge {class, icon|null, text, link|null}`; `deadline {variant, type, color, received_display, indicator_icon, indicator_label, age_text, footer}`).
- **Parity data perpajakan wajib.** `dokumens()` meng-`loadMissing` `roleData` HANYA `role_code='perpajakan'` + `roleStatuses` untuk `['team_verifikasi','perpajakan','akutansi','pembayaran']`. Endpoint datatable WAJIB memakai eager-load yang SAMA → `getDataForRole('akutansi'/'pembayaran'/'team_verifikasi')` mengembalikan null (parity byte dengan tabel lama; `$isBypassedToPembayaran` fallback ke `tanggal_masuk`). Jangan menambah eager-load roleData peran lain.
- **Parity operator & akutansi.** DTO basis `DocumentRow` dan engine TIDAK berubah → suite Rollout 1 (`OperatorDocumentRowTest`, `AkutansiDocumentRowTest`) HARUS tetap hijau.
- **Forward via dropdown Pengurus Dokumen.** Tombol Kirim/Balik perpajakan sudah mati (`$showActionColumn=false`). View Tabulator TANPA kolom aksi/Tambah/Hapus. Gate wajib-isi NPWP/Faktur **TIDAK** ditegakkan (mempertahankan perilaku live sekarang — di luar lingkup).
- **`php artisan test` hijau di TIAP tahap** sebelum commit. `node --check public/js/document-tabulator.js` TIDAK perlu (engine tak disentuh).
- **git add per-file** (jangan `git add .`/`-A`). **Pesan commit Bahasa Indonesia.** Satu commit = satu perubahan logis. **UI/komentar Indonesia, identifier English.**
- **CSS Deadline/Badge di-PORT verbatim** dari view legacy perpajakan (bukan reuse akutansi — nilai kelas bisa beda). Jangan tambah `!important` baru.
- **Gerbang kritis (CLAUDE.md §6):** Task 4 (switch routing) & Task 5 (hapus legacy + rute/method) — Task 5 HANYA setelah user konfirmasi QA lolos.

---

### Peta Berkas

**Dibuat:**
- `app/Support/PerpajakanDocumentRow.php` — DTO baris perpajakan (extends `DocumentRow`).
- `resources/views/perpajakan/dokumens/daftarPerpajakanTabulator.blade.php` — view Tabulator.
- `public/css/perpajakan-deadline-badge.css` — CSS Deadline+Badge di-port dari view legacy.
- `tests/Unit/PerpajakanDocumentRowTest.php`
- `tests/Feature/PerpajakanDatatableTest.php`
- `tests/Feature/PerpajakanTabulatorSwitchTest.php`

**Diubah:**
- `app/Http/Controllers/DashboardPerpajakanController.php` — ekstrak `buildPerpajakanQuery()`, tambah `datatable()` + `buildPerpajakanHandlerOptions()`, `dokumens()` sajikan Tabulator default + `?classic`.
- `routes/web.php` — tambah route STATIS `documents.perpajakan.data` sebelum route `{dokumen}`.

**Dihapus (Task 5, PASCA-QA, gated):**
- `resources/views/perpajakan/dokumens/daftarPerpajakan.blade.php`, `_rows.blade.php`, `_chunk.blade.php`, cabang `?classic`/`virtual_chunk`.
- Rute + method aksi mati: `set-deadline`/`send-to-next`/`send-to-akutansi`/`return` + `setDeadline`/`sendToNext`/`sendToAkutansi`/`returnDocument` (grep-verified).

**Reuse tanpa ubah:** `app/Support/DocumentRow.php`, `public/js/document-tabulator.js`, partial `document-handler-select`/`compact-document-ui`/`document-workbench-ui`, endpoint `documents.inline-update`/`documents.handler.update`.

---

## Task 1: `App\Support\PerpajakanDocumentRow`

**Files:**
- Create: `app/Support/PerpajakanDocumentRow.php`
- Test: `tests/Unit/PerpajakanDocumentRowTest.php`

**Interfaces:**
- Consumes: `App\Support\DocumentRow::baseRow(Dokumen, array $handlerOptions, ?string $viewerRole)` (sudah ada); `App\Helpers\DokumenHelper`; `Dokumen::getDataForRole(string)`, `Dokumen::getDisplayStatusForRole(string)`.
- Produces: `PerpajakanDocumentRow::fromDokumen(\App\Models\Dokumen $dokumen, array $handlerOptions, ?string $viewerRole = null): array`. Selain kunci basis, menambah `is_at_my_role`, `is_locked`, `lock_status_message`, `lock_status_class`, `can_edit`, `can_set_deadline`, `status_pembayaran`, `status_badge`, `deadline` (bentuk objek IDENTIK `AkutansiDocumentRow`, agar formatter engine `akutansiStatus`/`deadline` merendernya).

Prasyarat data (sama dengan `dokumens()`): `roleData` di-load HANYA `role_code='perpajakan'`; `roleStatuses` untuk 4 role. DTO TIDAK query DB.

- [ ] **Step 1: Tulis test yang gagal**

Buat `tests/Unit/PerpajakanDocumentRowTest.php`. Ikuti pola `tests/Unit/AkutansiDocumentRowTest.php` (extends `Tests\TestCase`, `use RefreshDatabase`, persist `Dokumen`/`DokumenStatus`/`DokumenRoleData` — karena `DokumenHelper::isDocumentLocked()` query DB). Uji cabang badge kunci + deadline "none" + basis:

```php
<?php

namespace Tests\Unit;

use App\Models\Dokumen;
use App\Models\DokumenStatus;
use App\Models\DokumenRoleData;
use App\Support\PerpajakanDocumentRow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerpajakanDocumentRowTest extends TestCase
{
    use RefreshDatabase;

    private function buatDokumen(array $attrs = []): Dokumen
    {
        return Dokumen::create(array_merge([
            'nomor_agenda'  => '100',
            'current_handler' => 'perpajakan',
            'status'        => 'sent_to_perpajakan',
            'nilai_rupiah'  => 1000000,
        ], $attrs));
    }

    private function buatStatus(Dokumen $d, string $role, string $status): void
    {
        DokumenStatus::create(['dokumen_id' => $d->id, 'role_code' => $role, 'status' => $status]);
    }

    private function buatRoleData(Dokumen $d, string $role, array $attrs = []): void
    {
        DokumenRoleData::create(array_merge(['dokumen_id' => $d->id, 'role_code' => $role], $attrs));
    }

    /** Muat relasi persis seperti dokumens(): roleData perpajakan-only + roleStatuses 4 role. */
    private function row(Dokumen $d, array $handlerOptions = []): array
    {
        $d->load([
            'roleData' => fn ($q) => $q->where('role_code', 'perpajakan'),
            'roleStatuses' => fn ($q) => $q->whereIn('role_code', ['team_verifikasi', 'perpajakan', 'akutansi', 'pembayaran']),
            'dibayarKepadas', 'dokumenPos',
        ]);
        return PerpajakanDocumentRow::fromDokumen($d, $handlerOptions, 'perpajakan');
    }

    public function test_badge_draft_saat_masih_di_hulu(): void
    {
        // current_handler operator, perpajakan belum terima → "Draft".
        $d = $this->buatDokumen(['current_handler' => 'operator', 'status' => 'draft']);
        $row = $this->row($d);
        $this->assertSame('badge-proses', $row['status_badge']['class']);
        $this->assertSame('⏳ Draft', $row['status_badge']['text']);
        $this->assertSame('none', $row['deadline']['variant']); // belum diterima
    }

    public function test_badge_ditolak_menyertakan_link(): void
    {
        $d = $this->buatDokumen(['nomor_agenda' => '99', 'status' => 'returned_to_verifikasi']);
        $this->buatStatus($d, 'perpajakan', 'rejected');
        $row = $this->row($d);
        $this->assertSame('badge-dikembalikan', $row['status_badge']['class']);
        $this->assertSame('fa-times-circle', $row['status_badge']['icon']);
        $this->assertSame('cek disini', $row['status_badge']['link']['text']);
        $this->assertStringContainsString('99', $row['status_badge']['link']['href']);
    }

    public function test_badge_terkirim_ke_akutansi_saat_akutansi_approve(): void
    {
        // display_status belum final → fallback: akutansi approved → "Terkirim ke Team Akutansi".
        $d = $this->buatDokumen(['status' => 'sent_to_akutansi']);
        $this->buatRoleData($d, 'perpajakan', ['received_at' => now()->subHours(3), 'processed_at' => now()->subHours(1)]);
        $this->buatStatus($d, 'akutansi', 'approved');
        $row = $this->row($d);
        $this->assertSame('badge-sent', $row['status_badge']['class']);
        $this->assertStringContainsString('Terkirim ke Team Akutansi', $row['status_badge']['text']);
        // deadline: sudah diterima + sent → kartu beku gray.
        $this->assertSame('card', $row['deadline']['variant']);
        $this->assertSame('gray', $row['deadline']['color']);
        $this->assertStringContainsString('⏸️', $row['deadline']['age_text']);
    }

    public function test_deadline_aktif_hijau_saat_diproses(): void
    {
        $d = $this->buatDokumen(['status' => 'sent_to_perpajakan']);
        $this->buatRoleData($d, 'perpajakan', ['received_at' => now()->subHours(5)]); // <24j → AMAN/green
        $row = $this->row($d);
        $this->assertSame('card', $row['deadline']['variant']);
        $this->assertSame('green', $row['deadline']['color']);
        $this->assertSame('AMAN', $row['deadline']['indicator_label']);
        $this->assertSame('active', $row['deadline']['type']);
        $this->assertNull($row['deadline']['footer']);
    }

    public function test_basis_dan_kunci_perpajakan_hadir_tanpa_key_operator(): void
    {
        $d = $this->buatDokumen();
        $this->buatRoleData($d, 'perpajakan', ['received_at' => now()->subHours(1)]);
        $row = $this->row($d, [['value' => 'perpajakan', 'label' => 'Tim Perpajakan']]);
        $this->assertArrayHasKey('nilai_rupiah_formatted', $row);
        $this->assertArrayHasKey('dates', $row);
        $this->assertArrayHasKey('handler_options', $row);
        $this->assertArrayHasKey('is_at_my_role', $row);
        $this->assertArrayHasKey('status_pembayaran', $row);
        $this->assertArrayNotHasKey('display_status', $row); // bukan operator
    }
}
```

> Bila field wajib factory/`Dokumen::create` berbeda, sesuaikan ke skema nyata (lihat `AkutansiDocumentRowTest` yang sudah lulus). Jangan ubah skema.

- [ ] **Step 2: Jalankan test — pastikan gagal**

Run: `php artisan test tests/Unit/PerpajakanDocumentRowTest.php`
Expected: FAIL — `Class "App\Support\PerpajakanDocumentRow" not found`.

- [ ] **Step 3: Buat DTO**

Port pohon Status dari `_rows.blade.php:14-118` (penentuan status) + `:587-632` (badge) dan Deadline dari `:355-583`. Buat `app/Support/PerpajakanDocumentRow.php`:

```php
<?php

namespace App\Support;

use App\Helpers\DokumenHelper;
use App\Models\Dokumen;
use Carbon\Carbon;

/**
 * DTO baris tabel perpajakan (Tabulator, Rollout 2). Mewarisi derivasi bersama
 * dari App\Support\DocumentRow dan menambah bit khas perpajakan: is_at_my_role,
 * lock, can_edit(perpajakan), can_set_deadline, status_pembayaran, plus dua objek
 * siap-render (bentuk IDENTIK AkutansiDocumentRow agar formatter engine sama):
 *   - status_badge ← porting _rows.blade.php:14-118 + 587-632 (kolom Status).
 *   - deadline     ← porting _rows.blade.php:355-583 (kolom Deadline).
 * Klien hanya MERENDER objek ini; nol logika bisnis di JS.
 *
 * Prasyarat data (WAJIB sama dgn dokumens()): roleData load HANYA role_code=
 * 'perpajakan'; roleStatuses 4 role. getDataForRole('akutansi'/'pembayaran'/
 * 'team_verifikasi') → null tanpa query (parity byte tabel lama).
 */
class PerpajakanDocumentRow extends DocumentRow
{
    public static function fromDokumen(Dokumen $dokumen, array $handlerOptions, ?string $viewerRole = null): array
    {
        $row = static::baseRow($dokumen, $handlerOptions, $viewerRole);

        $isLocked = DokumenHelper::isDocumentLocked($dokumen);
        $perpajakanRoleData = $dokumen->getDataForRole('perpajakan');

        // is_at_my_role: dokumen sedang/pernah di perpajakan (paritas kolom aksi lama).
        $isAtMyRole = $dokumen->current_handler === 'perpajakan'
            || in_array($dokumen->status, ['sent_to_akutansi', 'sent_to_pembayaran', 'pending_approval_akutansi', 'pending_approval_pembayaran'], true)
            || (in_array($dokumen->status, ['completed', 'selesai'], true) && ! empty($dokumen->status_pembayaran));

        $row['is_at_my_role']       = $isAtMyRole;
        $row['is_locked']           = $isLocked;
        $row['lock_status_message'] = DokumenHelper::getLockedStatusMessage($dokumen);
        $row['lock_status_class']   = DokumenHelper::getLockStatusClass($dokumen);
        $row['can_edit']            = DokumenHelper::canEditDocument($dokumen, 'perpajakan');
        $row['can_set_deadline']    = DokumenHelper::canSetDeadline($dokumen)['can_set'];
        $row['status_pembayaran']   = $dokumen->status_pembayaran;

        $ctx = static::statusContext($dokumen);
        $row['status_badge'] = static::buildStatusBadge($dokumen, $isLocked, $ctx);
        $row['deadline']     = static::buildDeadline($dokumen, $ctx);

        return $row;
    }

    /**
     * Konteks status bersama (dipakai badge & deadline) — port _rows.blade.php:14-118.
     * Mengembalikan: is_rejected, sent_to_team (?string), is_pending_downstream (bool),
     * pending_downstream_team (?string), is_bypassed_to_pembayaran (bool — DIDEFINISIKAN
     * TAK-BERSYARAT, memperbaiki bug laten view lama yang merujuknya tanpa selalu men-set).
     */
    protected static function statusContext(Dokumen $dokumen): array
    {
        $statuses = $dokumen->roleStatuses;

        // === is_rejected (port :14-31) ===
        $isRejectedByPerpajakan = $statuses->where('role_code', 'perpajakan')->where('status', 'rejected')->isNotEmpty();
        $isReturnedFromAkutansi = $dokumen->status === 'returned_to_department' && $dokumen->return_source === 'akutansi';
        $isRejectedByAkutansi   = $statuses->where('role_code', 'akutansi')->where('status', 'rejected')->isNotEmpty();
        $isRejected = $isRejectedByPerpajakan || $isReturnedFromAkutansi || $isRejectedByAkutansi;

        // === display_status-first + fallback (port :33-118) ===
        $perpajakanDisplayStatus = $dokumen->getDisplayStatusForRole('perpajakan');
        $perpajakanRoleData = $dokumen->getDataForRole('perpajakan');
        $akutansiRoleData   = $dokumen->getDataForRole('akutansi');   // null (parity)
        $pembayaranRoleData = $dokumen->getDataForRole('pembayaran'); // null (parity)

        $akutansiHasApproved = $statuses->where('role_code', 'akutansi')->where('status', 'approved')->isNotEmpty();
        $akutansiIsPending   = $statuses->where('role_code', 'akutansi')->where('status', 'pending')->isNotEmpty();

        $sentToTeam = null;
        $isPendingDownstream = false;
        $pendingDownstreamTeam = null;
        $isBypassedToPembayaran = false;

        if ($perpajakanDisplayStatus && str_starts_with($perpajakanDisplayStatus, 'terkirim')) {
            $sentToTeam = match ($perpajakanDisplayStatus) {
                'terkirim_akutansi'   => 'Team Akutansi',
                'terkirim_pembayaran' => 'Team Pembayaran',
                'terkirim'            => 'Team Akutansi',
                default               => 'Team Akutansi',
            };
        } elseif ($perpajakanDisplayStatus && str_starts_with($perpajakanDisplayStatus, 'menunggu_approval')) {
            $isPendingDownstream = true;
            $pendingDownstreamTeam = match ($perpajakanDisplayStatus) {
                'menunggu_approval_akutansi'   => 'Team Akutansi',
                'menunggu_approval_pembayaran' => 'Team Pembayaran',
                default                        => 'Team Akutansi',
            };
        } else {
            $isBypassedToPembayaran = (
                $dokumen->current_handler === 'pembayaran'
                || $dokumen->status === 'completed'
                || $dokumen->status_pembayaran === 'sudah_dibayar'
                || ($pembayaranRoleData && $pembayaranRoleData->received_at)
            ) && ! $perpajakanRoleData?->received_at;

            if ($isBypassedToPembayaran) {
                $sentToTeam = 'Team Pembayaran';
            } elseif ($akutansiHasApproved || ($akutansiRoleData && $akutansiRoleData->received_at && ! $akutansiIsPending)) {
                $sentToTeam = 'Team Akutansi';
            }

            if ($akutansiIsPending && ! $sentToTeam) {
                $isPendingDownstream = true;
                $pendingDownstreamTeam = 'Team Akutansi';
            }
        }

        return [
            'is_rejected'               => $isRejected,
            'sent_to_team'              => $sentToTeam,
            'is_pending_downstream'     => $isPendingDownstream,
            'pending_downstream_team'   => $pendingDownstreamTeam,
            'is_bypassed_to_pembayaran' => $isBypassedToPembayaran,
        ];
    }

    /** Port badge Status _rows.blade.php:587-632 → {class, icon, text, link}. Urutan cabang DIPERTAHANKAN. */
    protected static function buildStatusBadge(Dokumen $dokumen, bool $isLocked, array $ctx): array
    {
        $statuses = $dokumen->roleStatuses;
        $perpajakanRoleData = $dokumen->getDataForRole('perpajakan');
        $akutansiIsPending  = $statuses->where('role_code', 'akutansi')->where('status', 'pending')->isNotEmpty();
        $pembayaranIsPending = $statuses->where('role_code', 'pembayaran')->where('status', 'pending')->isNotEmpty();

        if ($ctx['is_rejected']) {
            return [
                'class' => 'badge-dikembalikan',
                'icon'  => 'fa-times-circle',
                'text'  => 'Dokumen ditolak,',
                'link'  => [
                    'href' => route('returns.perpajakan.index') . '?search=' . urlencode((string) $dokumen->nomor_agenda),
                    'text' => 'cek disini',
                ],
            ];
        }
        if (! ($perpajakanRoleData?->received_at)
            && in_array($dokumen->current_handler, ['operator', 'team_verifikasi'], true)
            && ! in_array($dokumen->status, ['completed', 'selesai'], true)
            && $dokumen->status_pembayaran !== 'sudah_dibayar') {
            return ['class' => 'badge-proses', 'icon' => null, 'text' => '⏳ Draft', 'link' => null];
        }
        if ($ctx['is_pending_downstream']) {
            return ['class' => 'badge-warning', 'icon' => null, 'text' => '⏳ Menunggu Approval dari ' . $ctx['pending_downstream_team'], 'link' => null];
        }
        if ($ctx['sent_to_team']) {
            return ['class' => 'badge-sent', 'icon' => null, 'text' => '📤 Terkirim ke ' . $ctx['sent_to_team'], 'link' => null];
        }
        if ($dokumen->status === 'sent_to_akutansi' && ! $akutansiIsPending) {
            return ['class' => 'badge-sent', 'icon' => null, 'text' => '📤 Terkirim ke Team Akutansi', 'link' => null];
        }
        if ($dokumen->status === 'sent_to_pembayaran' && ! $pembayaranIsPending) {
            return ['class' => 'badge-sent', 'icon' => null, 'text' => '📤 Terkirim ke Team Pembayaran', 'link' => null];
        }
        if ($dokumen->status === 'sent_to_perpajakan' && $dokumen->current_handler === 'perpajakan') {
            return ['class' => 'badge-proses', 'icon' => null, 'text' => '⏳ Sedang Diproses', 'link' => null];
        }
        if ($isLocked) {
            return ['class' => 'badge-locked', 'icon' => null, 'text' => '🔒 Terkunci - Menunggu Deadline', 'link' => null];
        }
        if ($dokumen->status === 'pending_approval_perpajakan') {
            return ['class' => 'badge-warning', 'icon' => null, 'text' => '📥 Baru Diterima', 'link' => null];
        }
        if ($dokumen->status === 'returned_to_verifikasi') {
            return ['class' => 'badge-sent', 'icon' => 'fa-paper-plane', 'text' => 'Kembali ke Team Verifikasi', 'link' => null];
        }
        // 'sedang diproses' & else → sama.
        return ['class' => 'badge-proses', 'icon' => null, 'text' => '⏳ Sedang Diproses', 'link' => null];
    }

    /** Port kolom Deadline _rows.blade.php:355-583 → objek siap-render. Count-up, beku utk sent/completed/returned. */
    protected static function buildDeadline(Dokumen $dokumen, array $ctx): array
    {
        $roleData   = $dokumen->getDataForRole('perpajakan');
        $receivedAt = $roleData?->received_at;

        $isSent = in_array($dokumen->status, ['sent_to_akutansi', 'sent_to_pembayaran', 'pending_approval_akutansi', 'pending_approval_pembayaran'], true);
        $isCompleted = in_array($dokumen->status, ['selesai', 'completed', 'approved_data_sudah_terkirim'], true)
            || ($dokumen->status_pembayaran === 'sudah_dibayar');
        $isReturned = $dokumen->status === 'returned_to_verifikasi';

        // === Path A: sudah diterima perpajakan ===
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
            $parts = [];
            if ($diff->days > 0) { $parts[] = $diff->days . ' hari'; }
            if ($diff->h > 0)    { $parts[] = $diff->h . ' jam'; }
            if ($diff->i > 0 || empty($parts)) { $parts[] = $diff->i . ' menit'; }
            $ageText = implode(' ', $parts);
            if ($timeFrozen) { $ageText .= ' ⏸️'; }

            $totalHours = ($diff->days * 24) + $diff->h;
            if ($totalHours >= 72)      { $ageLabel = 'TERLAMBAT'; $ageIcon = 'fa-times-circle'; }
            elseif ($totalHours >= 24)  { $ageLabel = 'PERINGATAN'; $ageIcon = 'fa-exclamation-triangle'; }
            else                        { $ageLabel = 'AMAN'; $ageIcon = 'fa-check-circle'; }

            if ($isSent || $isCompleted || $isReturned) { $ageColor = 'gray'; }
            elseif ($totalHours >= 72)  { $ageColor = 'red'; }
            elseif ($totalHours >= 24)  { $ageColor = 'yellow'; }
            else                        { $ageColor = 'green'; }

            $type = 'active';
            if ($isReturned)       { $type = 'paused'; }
            elseif ($isCompleted)  { $type = 'completed'; }
            elseif ($isSent)       { $type = 'sent'; }

            $footer = null;
            if ($isReturned)       { $footer = ['kind' => 'paused', 'icon' => 'fa-pause-circle', 'text' => 'Berhenti Sementara']; }
            elseif ($isSent)       { $footer = ['kind' => 'sent', 'icon' => 'fa-paper-plane', 'text' => 'Terkirim']; }
            elseif ($isCompleted)  { $footer = ['kind' => 'completed', 'icon' => 'fa-check-circle', 'text' => 'Selesai']; }

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

        // === Path B: bypass perpajakan (langsung ke pembayaran) ===
        if ($ctx['is_bypassed_to_pembayaran']) {
            $bypassPembayaranData = $dokumen->getDataForRole('pembayaran');       // null (parity)
            $bypassVerifikasiData = $dokumen->getDataForRole('team_verifikasi');  // null (parity)
            $bypassTimestamp = $bypassPembayaranData?->received_at
                ?? $bypassVerifikasiData?->processed_at
                ?? $dokumen->tanggal_masuk;

            if (! $bypassTimestamp) {
                return ['variant' => 'sent_fallback', 'type' => 'sent', 'color' => 'gray',
                        'received_display' => null, 'indicator_icon' => null, 'indicator_label' => null,
                        'age_text' => null, 'footer' => null];
            }

            $start = $bypassTimestamp instanceof Carbon ? $bypassTimestamp : Carbon::parse($bypassTimestamp);
            $processed = $bypassVerifikasiData?->processed_at ?? $bypassPembayaranData?->received_at;
            $end = $processed ? ($processed instanceof Carbon ? $processed : Carbon::parse($processed)) : $start;

            $diff = $start->diff($end);
            $parts = [];
            if ($diff->days > 0) { $parts[] = $diff->days . ' hari'; }
            if ($diff->h > 0)    { $parts[] = $diff->h . ' jam'; }
            if ($diff->i > 0 || empty($parts)) { $parts[] = $diff->i . ' menit'; }
            $ageText = implode(' ', $parts) . ' ⏸️';

            $totalHours = ($diff->days * 24) + $diff->h;
            if ($totalHours >= 72)     { $ageLabel = 'TERLAMBAT'; $ageIcon = 'fa-times-circle'; }
            elseif ($totalHours >= 24) { $ageLabel = 'PERINGATAN'; $ageIcon = 'fa-exclamation-triangle'; }
            else                       { $ageLabel = 'AMAN'; $ageIcon = 'fa-check-circle'; }

            return [
                'variant'          => 'card',
                'type'             => 'sent',
                'color'            => 'gray',
                'received_display' => $start->format('d M Y, H:i'),
                'indicator_icon'   => $ageIcon,
                'indicator_label'  => $ageLabel,
                'age_text'         => $ageText,
                'footer'           => ['kind' => 'sent', 'icon' => 'fa-paper-plane', 'text' => 'Terkirim'],
            ];
        }

        // === Path C: belum diterima ===
        return ['variant' => 'none', 'type' => null, 'color' => null,
                'received_display' => null, 'indicator_icon' => null, 'indicator_label' => null,
                'age_text' => null, 'footer' => null];
    }
}
```

> **Catatan verifikasi:** cocokkan tiap cabang `buildStatusBadge()` dgn `_rows.blade.php:587-632` (urutan identik) & `buildDeadline()` dgn `:355-583`. `statusContext()` = port `:14-118`. `$isBypassedToPembayaran` kini SELALU terdefinisi (perbaikan bug laten §Spec 3.1). `getDisplayStatusForRole('perpajakan')` membaca roleData perpajakan yang sudah di-load — verifikasi tak memicu query saat implement.

- [ ] **Step 4: Jalankan test — pastikan lulus**

Run: `php artisan test tests/Unit/PerpajakanDocumentRowTest.php`
Expected: PASS.

- [ ] **Step 5: Jalankan suite Rollout 1 (buktikan basis tak regresi)**

Run: `php artisan test --filter="AkutansiDocumentRow|OperatorDocumentRow"`
Expected: PASS (basis `DocumentRow` tak berubah).

- [ ] **Step 6: Commit**

```bash
git add app/Support/PerpajakanDocumentRow.php tests/Unit/PerpajakanDocumentRowTest.php
git commit -m "feat(support): PerpajakanDocumentRow (badge Status + Deadline dihitung server)"
```

---

## Task 2: Endpoint JSON `documents.perpajakan.data`

**Files:**
- Modify: `app/Http/Controllers/DashboardPerpajakanController.php`
- Modify: `routes/web.php:496-503`
- Test: `tests/Feature/PerpajakanDatatableTest.php`

**Interfaces:**
- Consumes: `PerpajakanDocumentRow::fromDokumen(...)` (Task 1). Pola: `DashboardAkutansiController@datatable`/`buildAkutansiQuery`/`buildAkutansiHandlerOptions` (acuan konkret — baca berkas itu).
- Produces: `DashboardPerpajakanController@datatable(Request): \Illuminate\Http\JsonResponse` → `{last_page, total, data}`. Method privat `buildPerpajakanQuery(Request): \Illuminate\Database\Eloquent\Builder` + `buildPerpajakanHandlerOptions(): array`. Route name `documents.perpajakan.data`, path GET `documents/perpajakan/data` (STATIS, sebelum `{dokumen}`).

- [ ] **Step 1: Tulis test yang gagal**

Buat `tests/Feature/PerpajakanDatatableTest.php`, meniru `tests/Feature/AkutansiDatatableTest.php` (SQLite polyfill setUp REGEXP/SUBSTRING_INDEX/LPAD; user role `perpajakan`; `Dokumen::create`):

```php
<?php

namespace Tests\Feature;

use App\Models\Dokumen;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PerpajakanDatatableTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        if (DB::connection()->getDriverName() === 'sqlite') {
            $pdo = DB::connection()->getPdo();
            $pdo->sqliteCreateFunction('REGEXP', fn ($p, $v) => preg_match('/' . $p . '/', (string) $v) ? 1 : 0, 2);
            $pdo->sqliteCreateFunction('SUBSTRING_INDEX', fn ($s, $d, $c) => implode($d, array_slice(explode($d, (string) $s), 0, (int) $c)), 3);
            $pdo->sqliteCreateFunction('LPAD', fn ($s, $l, $p) => str_pad((string) $s, (int) $l, (string) $p, STR_PAD_LEFT), 3);
        }
    }

    private function perpajakan(): User
    {
        return User::factory()->create(['role' => 'perpajakan']);
    }

    public function test_endpoint_data_mengembalikan_bentuk_datatable(): void
    {
        Dokumen::create(['nomor_agenda' => '1', 'current_handler' => 'perpajakan', 'status' => 'sent_to_perpajakan', 'nilai_rupiah' => 1000]);

        $this->actingAs($this->perpajakan())->getJson(route('documents.perpajakan.data'))
            ->assertOk()
            ->assertJsonStructure(['last_page', 'total', 'data' => [['id', 'nomor_agenda', 'status_badge', 'deadline', 'handler_options']]]);
    }

    public function test_baris_memuat_objek_status_badge_dan_deadline(): void
    {
        Dokumen::create(['nomor_agenda' => '2', 'current_handler' => 'operator', 'status' => 'draft', 'nilai_rupiah' => 1000]);
        $resp = $this->actingAs($this->perpajakan())->getJson(route('documents.perpajakan.data'));
        $first = $resp->json('data.0');
        $this->assertArrayHasKey('class', $first['status_badge']);
        $this->assertArrayHasKey('variant', $first['deadline']);
    }

    public function test_endpoint_menolak_tamu(): void
    {
        $this->getJson(route('documents.perpajakan.data'))->assertUnauthorized();
    }
}
```

- [ ] **Step 2: Jalankan test — pastikan gagal**

Run: `php artisan test tests/Feature/PerpajakanDatatableTest.php`
Expected: FAIL — route `documents.perpajakan.data` belum ada.

- [ ] **Step 3: Ekstrak query + tambah `datatable()` + handler options**

Di `DashboardPerpajakanController.php`: (a) ekstrak pembangun query `dokumens()` (base query + JOIN `perpajakan_data` untuk sort + filter + switch status + sort natural) menjadi `private function buildPerpajakanQuery(Request $request): \Illuminate\Database\Eloquent\Builder`, `dokumens()` memanggilnya. **Pertahankan** `loadMissing(roleData perpajakan + roleStatuses 4)` + `transform()` enrich SETELAH paginate — itu tetap di `dokumens()`. (b) Tambah `datatable()` + `buildPerpajakanHandlerOptions()`.

Tambahkan method (letakkan sebelum `dokumens()`), meniru `DashboardAkutansiController` (baca sebagai template konkret):

```php
    /**
     * Endpoint JSON tabel Tabulator perpajakan. {last_page,total,data} (cocok progressiveLoad).
     * Query sama dgn dokumens() via buildPerpajakanQuery(); baris via PerpajakanDocumentRow.
     * Eager-load roleData perpajakan-only + roleStatuses 4 role (parity byte tabel lama).
     */
    public function datatable(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = $this->buildPerpajakanQuery($request);

        $size = (int) $request->input('size', 100);
        $size = ($size > 0 && $size <= 200) ? $size : 100;
        $page = max(1, (int) $request->input('page', 1));

        $paginator = $query->paginate($size, ['*'], 'page', $page);

        // Eager-load relasi PERSIS seperti dokumens() (loadMissing pasca-paginate).
        $paginator->getCollection()->loadMissing([
            'roleData'     => fn ($q) => $q->where('role_code', 'perpajakan'),
            'roleStatuses' => fn ($q) => $q->whereIn('role_code', ['team_verifikasi', 'perpajakan', 'akutansi', 'pembayaran']),
            'dibayarKepadas', 'dokumenPos',
        ]);

        $handlerOptions = $this->buildPerpajakanHandlerOptions();
        $viewerRole = Auth::user()?->role;

        $data = collect($paginator->items())
            ->map(fn ($d) => \App\Support\PerpajakanDocumentRow::fromDokumen($d, $handlerOptions, $viewerRole))
            ->all();

        return response()->json([
            'last_page' => $paginator->lastPage(),
            'total'     => $paginator->total(),
            'data'      => $data,
        ]);
    }

    /** Opsi pengurus dokumen (5 peran base + optgroup Bagian). Bentuk identik DokumenController::buildHandlerOptions(). */
    private function buildPerpajakanHandlerOptions(): array
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
                'options'  => $bagian->map(fn ($b) => ['value' => 'bagian_' . strtolower($b->kode), 'label' => $b->nama ?: $b->kode])->all(),
            ];
        }
        return $handlerOptions;
    }
```

> **Instruksi ekstraksi (mekanis, tanpa ubah perilaku):** pindahkan blok pembangun query `dokumens()` (dari awal `$query = ...`/join `perpajakan_data` sampai SEBELUM `->paginate(...)`) ke `buildPerpajakanQuery()`, `return $query;` (Builder, BUKAN paginator). `dokumens()` ganti dgn `$query = $this->buildPerpajakanQuery($request);` lalu LANJUTKAN `->paginate(...)` + `loadMissing` + `transform` seperti semula. Sort natural `orderByRaw` + `orderByDesc('perpajakan_data.received_at')`/`updated_at` ikut ke `buildPerpajakanQuery()` (join `perpajakan_data` WAJIB ikut). Jaga variabel `$sortColumn`/`$sortOrder` yang dipakai `$data` view (baca ulang dari session bila perlu, seperti akutansi). Jalankan suite sesudah ekstraksi.

Tambah route di grup perpajakan `routes/web.php` (SETELAH `index`, SEBELUM `{dokumen}/detail`):

```php
Route::get('/data', [DashboardPerpajakanController::class, 'datatable'])->name('data');
```

- [ ] **Step 4: Jalankan test — pastikan lulus**

Run: `php artisan test tests/Feature/PerpajakanDatatableTest.php`
Expected: PASS.

- [ ] **Step 5: Jalankan seluruh suite**

Run: `php artisan test`
Expected: PASS penuh (dokumens() tak regresi).

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/DashboardPerpajakanController.php routes/web.php tests/Feature/PerpajakanDatatableTest.php
git commit -m "feat(perpajakan): endpoint JSON documents.perpajakan.data + ekstrak buildPerpajakanQuery"
```

---

## Task 3: View `daftarPerpajakanTabulator.blade.php` + CSS Deadline/Badge

**Files:**
- Create: `resources/views/perpajakan/dokumens/daftarPerpajakanTabulator.blade.php`
- Create: `public/css/perpajakan-deadline-badge.css`

**Interfaces:**
- Consumes: endpoint `documents.perpajakan.data` (Task 2); engine `document-tabulator.js` (`extraColumns` + formatter `deadline`/`akutansiStatus`, filter dari DOM — SUDAH ADA); partial global `document-workbench-ui`/`compact-document-ui`.
- Produces: view meng-emit `window.DOCUMENT_TABULATOR_CONFIG` (`mountId:'perpajakanTabulatorTable'`, `dataUrl: route('documents.perpajakan.data')`, `extraColumns` Deadline+Status, `handlerTpl`/`inlineUpdateTpl` bersama, `columns`/`availableColumns`/`selected` perpajakan, `ie` perpajakan). Mount `<div id="perpajakanTabulatorTable" class="doc-tabulator">` di `#documentTableContainer`. TANPA Tambah/Hapus. Modal kustomisasi self-contained.

- [ ] **Step 1: Port CSS Deadline+Badge ke berkas terpisah (verbatim, parity)**

Buat `public/css/perpajakan-deadline-badge.css`. Salin VERBATIM aturan CSS Deadline+Badge dari `resources/views/perpajakan/dokumens/daftarPerpajakan.blade.php` (blok `<style>`, mulai ~baris 165): SEMUA `.deadline-*` (`.deadline-card`, varian `.deadline-safe/.deadline-warning/.deadline-danger/.deadline-green/.deadline-yellow/.deadline-red/.deadline-gray/.deadline-sent/.deadline-completed/.deadline-paused`, `.deadline-time`, `.deadline-indicator`, `.deadline-age`, `.deadline-label`, `.deadline-paused-label`, `.no-deadline`, `@media` responsif) DAN semua `.badge-status*` (`.badge-proses`, `.badge-sent`, `.badge-warning`, `.badge-locked`, `.badge-dikembalikan`, dst + `@media`). JANGAN salin CSS aksi mati (`.col-action`, `.btn-action`, `.btn-send`, `.btn-kembalikan`). Cari batas blok via selektor (jangan tebak nomor baris).

Verifikasi inventaris — berkas WAJIB memuat selektor untuk SEMUA kelas yang di-emit DTO/formatter: `deadline-card`, `deadline-{active,sent,completed,paused}` (dari `type`), `deadline-{green,yellow,red,gray}` (dari `color`), `deadline-indicator`, `deadline-time`, `deadline-age`, `deadline-label`, `deadline-paused-label`, `no-deadline`, `badge-status`, `badge-{proses,sent,warning,locked,dikembalikan}`. (Bila view lama TIDAK punya aturan `.deadline-green/.deadline-active` dsb — itu wajar; formatter tetap meng-emit-nya seperti tabel lama, styling datang dari kombinasi kelas lain. Salin apa adanya.)

Run (cek): `grep -oE '\.(deadline-[a-z-]+|badge-status(\.[a-z-]+)?|no-deadline)' public/css/perpajakan-deadline-badge.css | sort -u`
Expected: memuat minimal `deadline-card`, `deadline-indicator`, `no-deadline`, `deadline-paused-label`, `badge-status`, `badge-proses`, `badge-sent`, `badge-warning`, `badge-locked`, `badge-dikembalikan`.

- [ ] **Step 2: Buat view Tabulator perpajakan (self-contained, meniru akutansi)**

Buat `resources/views/perpajakan/dokumens/daftarPerpajakanTabulator.blade.php` dengan MENIRU `resources/views/akutansi/dokumens/daftarAkutansiTabulator.blade.php` (baca berkas itu sebagai template konkret) dan substitusi berikut:
- `mountId => 'perpajakanTabulatorTable'`, `dataUrl => route('documents.perpajakan.data')`.
- `<form id="filterForm" action="{{ route('documents.perpajakan.index') }}" ...>`.
- Muat CSS: ganti `css/akutansi-deadline-badge.css` → `css/perpajakan-deadline-badge.css` (+ tetap `vendor/tabulator/tabulator.min.css`, `css/tabulator-agenda.css`).
- Mount `<div id="perpajakanTabulatorTable" class="doc-tabulator">` di `#documentTableContainer`.
- `extraColumns` SAMA: `[{field:'deadline',title:'Deadline',formatter:'deadline'},{field:'status_badge',title:'Status',formatter:'akutansiStatus'}]`.
- Toolbar filter perpajakan — nama field yang dibaca `buildPerpajakanQuery()` (verifikasi nama saat implement; umumnya `search`, `status`, `filter_dari`). `ie` perpajakan (`$ieKategoriList` dst dari `dokumens()`), `$filterDariOptions` perpajakan.
- Modal kustomisasi kolom + JS: SALIN dari view akutansi (self-contained, utang de-dup §7). `appendActiveFilterInputs()` bawa field toolbar perpajakan yang benar.
- TANPA tombol Tambah/Hapus, TANPA `inlineCreateUrl`/`destroyTpl`.

- [ ] **Step 3: Verifikasi kompilasi + suite (view belum tersambung)**

Run: `php artisan view:cache && php artisan view:clear`
Expected: kompilasi bersih (tak ada error menyebut `daftarPerpajakanTabulator`).

Run: `php artisan test`
Expected: PASS penuh (view belum dirujuk `dokumens()`).

- [ ] **Step 4: Commit**

```bash
git add public/css/perpajakan-deadline-badge.css resources/views/perpajakan/dokumens/daftarPerpajakanTabulator.blade.php
git commit -m "feat(perpajakan): view Tabulator + CSS Deadline/Badge (modal kustomisasi self-contained)"
```

---

## Task 4: Transisi — `dokumens()` sajikan Tabulator default, `?classic` → legacy

**Files:**
- Modify: `app/Http/Controllers/DashboardPerpajakanController.php` (akhir `dokumens()`)
- Test: `tests/Feature/PerpajakanTabulatorSwitchTest.php`

**Interfaces:**
- Produces: `dokumens()` mengembalikan `perpajakan.dokumens.daftarPerpajakanTabulator` default; `?classic=1` → `perpajakan.dokumens.daftarPerpajakan` (legacy).

- [ ] **Step 1: Tulis test yang gagal**

Buat `tests/Feature/PerpajakanTabulatorSwitchTest.php` (SQLite polyfill setUp sama Task 2; user role `perpajakan`):

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PerpajakanTabulatorSwitchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        if (DB::connection()->getDriverName() === 'sqlite') {
            $pdo = DB::connection()->getPdo();
            $pdo->sqliteCreateFunction('REGEXP', fn ($p, $v) => preg_match('/' . $p . '/', (string) $v) ? 1 : 0, 2);
            $pdo->sqliteCreateFunction('SUBSTRING_INDEX', fn ($s, $d, $c) => implode($d, array_slice(explode($d, (string) $s), 0, (int) $c)), 3);
            $pdo->sqliteCreateFunction('LPAD', fn ($s, $l, $p) => str_pad((string) $s, (int) $l, (string) $p, STR_PAD_LEFT), 3);
        }
    }

    private function perpajakan(): User { return User::factory()->create(['role' => 'perpajakan']); }

    public function test_default_menyajikan_view_tabulator(): void
    {
        $this->actingAs($this->perpajakan())->get(route('documents.perpajakan.index'))
            ->assertOk()->assertSee('perpajakanTabulatorTable', false)->assertSee('DOCUMENT_TABULATOR_CONFIG', false);
    }

    public function test_classic_menyajikan_view_legacy(): void
    {
        $this->actingAs($this->perpajakan())->get(route('documents.perpajakan.index', ['classic' => 1]))
            ->assertOk()->assertDontSee('perpajakanTabulatorTable', false);
    }
}
```

- [ ] **Step 2: Jalankan test — pastikan gagal**

Run: `php artisan test tests/Feature/PerpajakanTabulatorSwitchTest.php`
Expected: FAIL — `test_default_...` (masih view legacy).

- [ ] **Step 3: Switch view di `dokumens()`**

Ganti baris terakhir `return view('perpajakan.dokumens.daftarPerpajakan', $data);` dengan:

```php
        // Transisi Rollout 2: default → Tabulator; ?classic=1 → view legacy (banding QA).
        // Setelah QA perpajakan lolos, cabang legacy + flag ini DIHAPUS (Task 5).
        if ($request->boolean('classic')) {
            return view('perpajakan.dokumens.daftarPerpajakan', $data);
        }

        return view('perpajakan.dokumens.daftarPerpajakanTabulator', $data);
```

- [ ] **Step 4: Jalankan test — pastikan lulus**

Run: `php artisan test tests/Feature/PerpajakanTabulatorSwitchTest.php`
Expected: PASS.

- [ ] **Step 5: Jalankan seluruh suite**

Run: `php artisan test`
Expected: PASS penuh. Bila ada test perpajakan lama yang me-render `documents.perpajakan.index` tanpa `?classic` dan meng-assert elemen legacy, tambahkan `['classic' => 1]` (jaga legacy sampai Task 5) — masuk commit ini.

- [ ] **Step 6: Commit + Deploy (GERBANG)**

```bash
git add app/Http/Controllers/DashboardPerpajakanController.php tests/Feature/PerpajakanTabulatorSwitchTest.php
git commit -m "feat(perpajakan): dokumens() sajikan Tabulator default, ?classic untuk banding QA"
git push origin codinggemini
# server: git pull && php artisan route:clear && php artisan view:clear && php artisan config:clear
```

**BERHENTI** — QA visual (agent via Playwright READ-ONLY, login `pajak`/`12345678`; jalur tulis diserahkan user). Task 5 hanya setelah QA lolos.

---

## Task 5: Hapus legacy perpajakan + kode aksi mati (PASCA-QA — GATED)

> **JANGAN mulai sebelum user + QA menyatakan lolos.** Penghapusan view/route/method (CLAUDE.md §6).

**Files:**
- Delete: `resources/views/perpajakan/dokumens/daftarPerpajakan.blade.php`, `_rows.blade.php`, `_chunk.blade.php`
- Modify: `app/Http/Controllers/DashboardPerpajakanController.php` (hapus cabang `?classic` + `virtual_chunk`; hapus method aksi mati), `routes/web.php` (hapus rute aksi mati)

- [ ] **Step 1: Grep gate lintas-role (bukti sebelum hapus)**

Run:
```bash
grep -rn "perpajakan.dokumens.daftarPerpajakan\|perpajakan\.dokumens\._rows\|perpajakan\.dokumens\._chunk\|boolean('classic')" app/ resources/ routes/ | grep -v pengembalian
grep -rn "sendToNext\|sendToAkutansi\|returnDocument\|setDeadline\|send-to-next\|send-to-akutansi\|set-deadline\|handleSendToNext\|openReturnModal" app/Http/Controllers/DashboardPerpajakanController.php resources/views/perpajakan/ routes/web.php
```
Expected: perujuk view legacy hanya `dokumens()` (cabang classic/virtual). Method aksi mati (`sendToNext`/`sendToAkutansi`/`returnDocument`/`setDeadline`) TIDAK dipanggil dari view/JS lain (halaman Pengembalian `pengembalianPerpajakan` TIDAK memakainya — verifikasi). `getDocumentDetail` TETAP bila dipakai `pengembalianPerpajakan`. Bila ada perujuk tak terduga → BERHENTI, laporkan.

- [ ] **Step 2: Hapus cabang classic + virtual_chunk di `dokumens()`**

Hapus blok `if ($request->boolean('virtual_chunk')) { return view('perpajakan.dokumens._chunk', ...); }` dan cabang `?classic` (Task 4). `dokumens()` diakhiri `return view('perpajakan.dokumens.daftarPerpajakanTabulator', $data);`. Hapus juga flag `$showActionColumn` bila hanya dipakai cabang yang dihapus.

- [ ] **Step 3: Hapus method + rute aksi mati (grep-verified yatim)**

Hapus dari `DashboardPerpajakanController.php`: method `setDeadline`, `sendToNext`, `sendToAkutansi`, `returnDocument`. Dari `routes/web.php` grup perpajakan: rute `set-deadline`, `send-to-next`, `send-to-akutansi`, `return`. (JANGAN hapus `index`/`detail`/`data`/`pengembalian`.)

- [ ] **Step 4: Hapus berkas view legacy**

```bash
git rm resources/views/perpajakan/dokumens/daftarPerpajakan.blade.php resources/views/perpajakan/dokumens/_rows.blade.php resources/views/perpajakan/dokumens/_chunk.blade.php
```

- [ ] **Step 5: Perbarui test bergantung legacy + verifikasi**

Hapus `PerpajakanTabulatorSwitchTest::test_classic_menyajikan_view_legacy`. Sesuaikan test perpajakan lain yang memakai `?classic`.

Run: `grep -rn "daftarPerpajakan\b\|perpajakan.dokumens._rows\|perpajakan.dokumens._chunk\|boolean('classic')\|virtual_chunk" resources/ app/ | grep -iv pengembalian`
Expected: kosong untuk perpajakan (kecuali `daftarPerpajakanTabulator`).

Run: `php artisan view:cache && php artisan view:clear` → bersih.
Run: `php artisan test` → PASS penuh.

- [ ] **Step 6: Commit + Deploy**

```bash
git add app/Http/Controllers/DashboardPerpajakanController.php routes/web.php tests/Feature/PerpajakanTabulatorSwitchTest.php
git commit -m "refactor(perpajakan): hapus view legacy + cabang classic/virtual_chunk + kode aksi mati pasca-QA"
git push origin codinggemini
# server: git pull && php artisan route:clear && php artisan view:clear && php artisan config:clear
```

---

## Verifikasi (dilaporkan verbatim; QA visual = agent Playwright READ-ONLY)

1. `php artisan test` hijau di tiap tahap. DTO dijaga `PerpajakanDocumentRowTest`; endpoint `PerpajakanDatatableTest`; switch `PerpajakanTabulatorSwitchTest`. Basis Rollout 1 (`AkutansiDocumentRowTest`/`OperatorDocumentRowTest`) tetap hijau.
2. **QA visual perpajakan (agent Playwright, READ-ONLY)** — login `pajak`/`12345678`: `/documents/perpajakan` → Tabulator; kolom Deadline & badge Status benar (bandingkan `?classic=1`); **filter status/dari jalan** (engine baca nama dari DOM); dropdown Pengurus jalan; inline-edit terlihat; kustomisasi kolom jalan; lintas-role "Di {role}" benar; **0 error konsol**. **Jalur TULIS diserahkan user.**
3. **Parity:** operator & akutansi tak regresi (engine + basis `DocumentRow` tak berubah — smoke cek 1 halaman akutansi/operator).
4. **Paritas bypass:** dokumen yang melewati perpajakan langsung ke pembayaran — kartu Deadline tampak sama seperti `?classic=1` (roleData akutansi/pembayaran/verifikasi sengaja null).

## Deploy
Per tahap disetujui: commit per-file → push → pull server → clear cache. Penghapusan legacy (Task 5) hanya SETELAH QA perpajakan lolos + konfirmasi user.

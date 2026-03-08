<?php

namespace App\Services;

use App\Models\Dokumen;
use App\Models\DokumenActivityLog;
use App\Models\DokumenRoleData;
use App\Models\DokumenStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * AutoForwardDokumenService
 *
 * Menangani logika auto-forward dokumen ke role Pembayaran
 * ketika status_pembayaran diubah menjadi 'sudah_dibayar'
 * oleh project eksternal via raw DB query.
 *
 * Menggunakan metode sendToInbox() dan approveFromRoleInbox() yang sudah ada
 * agar data workflow tetap konsisten dengan forward manual.
 */
class AutoForwardDokumenService
{
    /**
     * Urutan role dalam workflow dokumen.
     * Operator → Team Verifikasi → Perpajakan → Akutansi → Pembayaran
     */
    private const ROLE_ORDER = [
        'operator',
        'team_verifikasi',
        'perpajakan',
        'akutansi',
        'pembayaran',
    ];

    /**
     * Meneruskan dokumen ke role Pembayaran secara otomatis,
     * melewati semua tahap yang belum dilalui.
     *
     * @param  Dokumen $dokumen
     * @return bool    true jika berhasil diforward, false jika diskip
     * @throws \Throwable jika terjadi error (untuk ditangkap oleh caller)
     */
    public function forwardToPembayaran(Dokumen $dokumen): bool
    {
        // === VALIDASI AWAL (GUARD CLAUSES) ===

        // Double-check: pastikan memang sudah_dibayar
        if ($dokumen->status_pembayaran !== 'sudah_dibayar') {
            // [FIX LOG BLOATING] Guard clause skip → debug (bukan info)
            Log::debug('AutoForward skipped: status_pembayaran bukan sudah_dibayar', [
                'dokumen_id'        => $dokumen->id,
            ]);
            return false;
        }

        // Skip jika sudah pernah di-auto-forward
        if ($dokumen->auto_forwarded_at !== null) {
            // [FIX LOG BLOATING] Guard clause skip → debug
            Log::debug('AutoForward skipped: dokumen sudah pernah di-auto-forward', [
                'dokumen_id' => $dokumen->id,
            ]);
            return false;
        }

        // Skip jika sudah berada di pembayaran dan statusnya sudah approved
        if ($dokumen->current_handler === 'pembayaran') {
            $pembayaranStatus = $dokumen->getStatusForRole('pembayaran');
            if ($pembayaranStatus && $pembayaranStatus->status === DokumenStatus::STATUS_APPROVED) {
                // [FIX LOG BLOATING] Guard clause skip → debug
                Log::debug('AutoForward skipped: dokumen sudah di role pembayaran (approved)', [
                    'dokumen_id' => $dokumen->id,
                ]);
                return false;
            }
        }

        // === TENTUKAN POSISI SAAT INI ===
        $currentRole = $this->determineCurrentRole($dokumen);
        $currentIndex = array_search($currentRole, self::ROLE_ORDER);

        if ($currentIndex === false) {
            // Role tidak dikenal — mulai dari awal (operator)
            Log::warning('AutoForward: current_handler tidak dikenal, mulai dari operator', [
                'dokumen_id'      => $dokumen->id,
                'current_handler' => $dokumen->current_handler,
                'status'          => $dokumen->status,
            ]);
            $currentIndex = 0;
        }

        // Jika sudah di pembayaran (index akhir), tidak perlu forward
        if ($currentIndex >= count(self::ROLE_ORDER) - 1) {
            // [FIX LOG BLOATING] Guard clause skip → debug
            Log::debug('AutoForward skipped: dokumen sudah di pembayaran', [
                'dokumen_id' => $dokumen->id,
            ]);
            return false;
        }

        // === JALANKAN FORWARD DALAM SATU TRANSACTION ===
        DB::transaction(function () use ($dokumen, $currentIndex) {
            $now = now();
            $tanggalDibayar = $dokumen->tanggal_dibayar ?? $now;

            Log::info('AutoForward dimulai', [
                'dokumen_id'     => $dokumen->id,
                'nomor_agenda'   => $dokumen->nomor_agenda,
                'dari_role'      => self::ROLE_ORDER[$currentIndex],
                'tujuan'         => 'pembayaran',
                'status'         => $dokumen->status,
                'current_handler' => $dokumen->current_handler,
            ]);

            // === FORWARD MELEWATI SEMUA ROLE YANG BELUM DILALUI ===
            // Mulai dari role SETELAH posisi saat ini, sampai SEBELUM 'pembayaran'
            for ($i = $currentIndex; $i < count(self::ROLE_ORDER) - 2; $i++) {
                $senderRole = self::ROLE_ORDER[$i];
                $nextRole   = self::ROLE_ORDER[$i + 1];

                $this->passThrough($dokumen, $senderRole, $nextRole, $now);
            }

            // === FORWARD KE PEMBAYARAN (TAHAP AKHIR) ===
            // Kirim ke inbox pembayaran
            $this->sendToPembayaranAndApprove($dokumen, $now, $tanggalDibayar);

            // === MARK AUTO-FORWARDED ===
            $dokumen->auto_forwarded_at = $now;
            $dokumen->save();

            // === UPDATE QUEUE RECORD (jika ada) ===
            DB::table('dokumen_auto_forward_queue')
                ->where('dokumen_id', $dokumen->id)
                ->update([
                    'status'       => 'done',
                    'processed_at' => $now,
                    'updated_at'   => $now,
                ]);

            Log::info('AutoForward selesai', [
                'dokumen_id'        => $dokumen->id,
                'nomor_agenda'      => $dokumen->nomor_agenda,
                'auto_forwarded_at' => $now,
                'current_handler'   => $dokumen->current_handler,
            ]);
        });

        return true;
    }

    /**
     * Menentukan posisi role dokumen saat ini.
     * Menggunakan kombinasi current_handler dan DokumenStatus untuk akurasi maksimal.
     */
    private function determineCurrentRole(Dokumen $dokumen): string
    {
        // Prioritas 1: current_handler field (paling akurat untuk posisi saat ini)
        $handler = strtolower($dokumen->current_handler ?? '');
        if (in_array($handler, self::ROLE_ORDER)) {
            return $handler;
        }

        // Prioritas 2: Cek DokumenStatus yang sedang pending atau approved terakhir
        $latestApprovedStatus = $dokumen->roleStatuses()
            ->whereIn('role_code', self::ROLE_ORDER)
            ->whereIn('status', [DokumenStatus::STATUS_APPROVED, DokumenStatus::STATUS_PENDING])
            ->orderByRaw("FIELD(role_code, 'operator', 'team_verifikasi', 'perpajakan', 'akutansi', 'pembayaran') DESC")
            ->first();

        if ($latestApprovedStatus) {
            return $latestApprovedStatus->role_code;
        }

        // Prioritas 3: Cek DokumenRoleData yang ada received_at (pernah menerima dokumen)
        $latestRoleData = $dokumen->roleData()
            ->whereNotNull('received_at')
            ->whereIn('role_code', self::ROLE_ORDER)
            ->orderByRaw("FIELD(role_code, 'operator', 'team_verifikasi', 'perpajakan', 'akutansi', 'pembayaran') DESC")
            ->first();

        if ($latestRoleData) {
            return $latestRoleData->role_code;
        }

        // Default: mulai dari operator
        return 'operator';
    }

    /**
     * Melewatkan dokumen melalui satu role ke role berikutnya.
     * Ini mensimulasikan aksi "kirim" + "approve dari inbox" yang dilakukan secara manual.
     */
    private function passThrough(Dokumen $dokumen, string $fromRole, string $toRole, $timestamp): void
    {
        Log::debug('AutoForward passThrough', [
            'dokumen_id' => $dokumen->id,
            'from'       => $fromRole,
            'to'         => $toRole,
        ]);

        // Pastikan sender role data ada (untuk konsistensi history)
        // Jika belum ada record untuk fromRole, buat dengan received_at dan processed_at = timestamp
        $fromRoleData = $dokumen->getDataForRole($fromRole);
        if (!$fromRoleData) {
            DokumenRoleData::updateOrCreate(
                ['dokumen_id' => $dokumen->id, 'role_code' => $fromRole],
                [
                    'received_at'  => $timestamp,
                    'processed_at' => $timestamp,
                ]
            );
        } elseif (!$fromRoleData->processed_at) {
            $fromRoleData->processed_at = $timestamp;
            $fromRoleData->received_at  = $fromRoleData->received_at ?? $timestamp;
            $fromRoleData->save();
        }

        // Pastikan sender role status ada dan approved
        $dokumen->setStatusForRole($fromRole, DokumenStatus::STATUS_APPROVED, 'system');

        // Kirim ke inbox role berikutnya — ini membuat DokumenStatus pending untuk toRole
        // dan mencatat DokumenActivityLog 'sent_to_inbox'
        $dokumen->sendToRoleInbox($toRole, 'system');

        // Langsung approve dari inbox — ini mencatat received_at, processed_at, dan log 'approved'
        // Kita timpa auth()->user() dengan 'system' di dalam method via workaround
        $this->approveRoleInboxAsSystem($dokumen, $toRole, $timestamp);
    }

    /**
     * Approve dokumen dari inbox sebagai 'system' untuk tahap antara.
     * Menggunakan logika yang sama dengan approveFromRoleInbox() tapi tanpa auth dependency.
     */
    private function approveRoleInboxAsSystem(Dokumen $dokumen, string $roleCode, $timestamp): void
    {
        $roleCode = strtolower($roleCode);

        // Update status role ke approved
        $dokumen->setStatusForRole($roleCode, DokumenStatus::STATUS_APPROVED, 'system');

        // Update role data — set received_at dan processed_at
        $roleData = $dokumen->getDataForRole($roleCode);
        if ($roleData) {
            if (!$roleData->received_at) {
                $roleData->received_at = $timestamp;
            }
            if (!$roleData->processed_at) {
                $roleData->processed_at = $timestamp;
            }
            $roleData->save();
        } else {
            DokumenRoleData::updateOrCreate(
                ['dokumen_id' => $dokumen->id, 'role_code' => $roleCode],
                ['received_at' => $timestamp, 'processed_at' => $timestamp]
            );
        }

        // Catat activity log untuk tahap ini
        DokumenActivityLog::create([
            'dokumen_id'       => $dokumen->id,
            'stage'            => $roleCode,
            'action'           => 'auto_forwarded',
            'action_description' => 'Auto-forward: dilewati otomatis oleh sistem karena pembayaran dikonfirmasi project eksternal',
            'performed_by'     => 'system',
            'action_at'        => $timestamp,
            'details'          => [
                'reason'     => 'status_pembayaran = sudah_dibayar dari project eksternal',
                'auto_mode'  => true,
            ],
        ]);

        // Sync legacy column current_handler
        $dokumen->current_handler = $roleCode;
        $dokumen->save();
    }

    /**
     * Kirim dokumen ke inbox Pembayaran dan langsung approve.
     * Ini adalah tahap akhir dari auto-forward.
     */
    private function sendToPembayaranAndApprove(Dokumen $dokumen, $now, $tanggalDibayar): void
    {
        // Kirim ke inbox pembayaran (mencatat log 'sent_to_inbox')
        $dokumen->sendToRoleInbox('pembayaran', 'system');

        // Approve dari inbox pembayaran sebagai system
        $dokumen->setStatusForRole('pembayaran', DokumenStatus::STATUS_APPROVED, 'system');

        // Setup role data untuk pembayaran
        $pembayaranRoleData = $dokumen->getDataForRole('pembayaran');
        if ($pembayaranRoleData) {
            $pembayaranRoleData->received_at  = $pembayaranRoleData->received_at ?? $now;
            $pembayaranRoleData->processed_at = $now;
            $pembayaranRoleData->save();
        } else {
            DokumenRoleData::create([
                'dokumen_id'   => $dokumen->id,
                'role_code'    => 'pembayaran',
                'received_at'  => $now,
                'processed_at' => $now,
            ]);
        }

        // Update dokumen ke status final di pembayaran
        $dokumen->current_handler    = 'pembayaran';
        $dokumen->status             = 'sent_to_pembayaran';
        $dokumen->status_pembayaran  = 'sudah_dibayar'; // pastikan tidak berubah
        $dokumen->tanggal_dibayar    = $tanggalDibayar;
        $dokumen->save();

        // Catat activity log final
        DokumenActivityLog::create([
            'dokumen_id'         => $dokumen->id,
            'stage'              => 'pembayaran',
            'action'             => 'auto_forwarded_to_pembayaran',
            'action_description' => 'Dokumen otomatis diteruskan ke role Pembayaran karena status pembayaran dikonfirmasi oleh sistem eksternal.',
            'performed_by'       => 'system',
            'action_at'          => $now,
            'details'            => [
                'reason'         => 'status_pembayaran = sudah_dibayar dari project eksternal',
                'auto_mode'      => true,
                'tanggal_dibayar' => (string) $tanggalDibayar,
            ],
        ]);

        Log::info('AutoForward: dokumen berhasil diteruskan ke pembayaran', [
            'dokumen_id'   => $dokumen->id,
            'nomor_agenda' => $dokumen->nomor_agenda,
        ]);
    }
}

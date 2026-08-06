<?php

namespace App\Services;

use App\Models\DokumenStatus;
use App\Models\WhatsAppNotificationLog;
use App\Support\RoleRecipients;
use Illuminate\Support\Facades\Log;

/**
 * Pengingat WhatsApp untuk dokumen yang MENGGANTUNG di inbox: sudah dikirim ke
 * suatu peran, tapi belum di-approve setelah sekian jam.
 *
 * KENAPA LAYANAN TERPISAH, bukan menumpang LateDocumentNotificationService:
 * layanan itu berbasis DEADLINE dan menyaring `dokumen_role_data.received_at IS
 * NOT NULL`. Sementara `received_at` SENGAJA dibiarkan kosong sampai dokumen
 * di-approve dari inbox (lihat Dokumen::sendToRoleInbox() — "deadline countdown
 * only starts AFTER approval"). Akibatnya justru jendela "terkirim tapi belum
 * di-approve" adalah satu-satunya jendela yang tidak terpantau sama sekali —
 * temuan yang melahirkan fitur ini (saran penguji sidang 2026-08-05).
 *
 * Karena itu sumber datanya beda: baris `dokumen_statuses` berstatus `pending`,
 * diukur dari `status_changed_at`. Yang dipakai bersama (pencarian penerima,
 * dedup, pencatatan) memakai komponen yang sama dengan layanan deadline.
 */
class PendingApprovalReminderService
{
    public function __construct(
        protected FonnteWhatsAppService $whatsAppService
    ) {}

    private const MESSAGE_TYPE = 'pending_approval';

    /**
     * @return array{diperiksa:int, terkirim:int, dilewati:int, gagal:int, rincian:array}
     */
    public function kirimPengingat(bool $dryRun = false): array
    {
        $ambangJam   = (int) config('fonnte.pending_approval.hours', 6);
        $cooldownJam = (int) config('fonnte.pending_approval.cooldown_hours', 24);
        $batas       = now()->subHours($ambangJam);

        $hasil = ['diperiksa' => 0, 'terkirim' => 0, 'dilewati' => 0, 'gagal' => 0, 'rincian' => []];

        $menggantung = DokumenStatus::query()
            ->whereRaw('LOWER(status) = ?', [DokumenStatus::STATUS_PENDING])
            ->where('status_changed_at', '<=', $batas)
            ->with('dokumen')
            ->get();

        $hasil['diperiksa'] = $menggantung->count();

        // Penerima dicari SEKALI per peran, bukan per dokumen (hindari N+1).
        $penerimaPerPeran = [];

        foreach ($menggantung as $status) {
            $dokumen = $status->dokumen;

            if ($dokumen === null) {
                // Baris status yatim — dokumennya sudah terhapus.
                $hasil['dilewati']++;
                continue;
            }

            $peran = strtolower(trim((string) $status->role_code));
            $penerimaPerPeran[$peran] ??= RoleRecipients::withPhone($peran);
            $penerima = $penerimaPerPeran[$peran];

            $catatan = [
                'dokumen_id'   => $dokumen->id,
                'nomor_agenda' => $dokumen->nomor_agenda,
                'peran'        => $peran,
                'menggantung_jam' => (int) $status->status_changed_at->diffInHours(now()),
            ];

            if ($penerima->isEmpty()) {
                // Bukan error: perannya memang belum ada yang mengisi nomor HP.
                $hasil['dilewati']++;
                $hasil['rincian'][] = $catatan + ['alasan' => 'tidak ada penerima ber-nomor HP'];
                continue;
            }

            if (WhatsAppNotificationLog::wasRecentlySent($dokumen->id, $peran, self::MESSAGE_TYPE, $cooldownJam)) {
                $hasil['dilewati']++;
                $hasil['rincian'][] = $catatan + ['alasan' => 'masih dalam masa tenang'];
                continue;
            }

            $pesan = $this->susunPesan($dokumen, $peran, $catatan['menggantung_jam']);

            if ($dryRun) {
                $hasil['dilewati']++;
                $hasil['rincian'][] = $catatan + ['alasan' => 'dry-run'];
                continue;
            }

            $adaYangBerhasil = false;

            foreach ($penerima as $user) {
                try {
                    $this->whatsAppService->sendMessage($user->phone_number, $pesan);

                    WhatsAppNotificationLog::create([
                        'dokumen_id'   => $dokumen->id,
                        'role_code'    => $peran,
                        'user_id'      => $user->id,
                        'phone_number' => $user->phone_number,
                        'message_type' => self::MESSAGE_TYPE,
                        'message'      => $pesan,
                        'status'       => 'success',
                        'channel'      => 'whatsapp',
                        'sent_at'      => now(),
                    ]);

                    $adaYangBerhasil = true;
                } catch (\Throwable $e) {
                    Log::error('[pengingat-approval] Gagal kirim WhatsApp: ' . $e->getMessage(), [
                        'dokumen_id' => $dokumen->id,
                        'user_id'    => $user->id,
                    ]);

                    WhatsAppNotificationLog::create([
                        'dokumen_id'      => $dokumen->id,
                        'role_code'       => $peran,
                        'user_id'         => $user->id,
                        'phone_number'    => $user->phone_number,
                        'message_type'    => self::MESSAGE_TYPE,
                        'message'         => $pesan,
                        'status'          => 'failed',
                        'channel'         => 'whatsapp',
                        'fallback_reason' => substr($e->getMessage(), 0, 500),
                        'sent_at'         => now(),
                    ]);
                }
            }

            if ($adaYangBerhasil) {
                $hasil['terkirim']++;
                $hasil['rincian'][] = $catatan + ['alasan' => 'terkirim'];
            } else {
                $hasil['gagal']++;
                $hasil['rincian'][] = $catatan + ['alasan' => 'semua pengiriman gagal'];
            }
        }

        return $hasil;
    }

    private function susunPesan(\App\Models\Dokumen $dokumen, string $peran, int $jam): string
    {
        $agenda = $dokumen->nomor_agenda ?: 'N/A';
        $uraian = trim((string) $dokumen->uraian_spp);
        $uraian = $uraian !== '' ? mb_substr($uraian, 0, 120) : '-';
        $tautan = url(route('inbox.index', [], false));

        return "⏰ *PENGINGAT AGENDA ONLINE*\n\n"
            . "Dokumen *{$agenda}* sudah *{$jam} jam* menunggu persetujuan di inbox "
            . \App\Models\Dokumen::getRoleDisplayNameIndo($peran) . " dan belum diproses.\n\n"
            . "📄 *Uraian:*\n{$uraian}\n\n"
            . "Mohon segera diperiksa agar dokumen tidak tertahan.\n\n"
            . "🔗 Buka inbox: {$tautan}";
    }
}

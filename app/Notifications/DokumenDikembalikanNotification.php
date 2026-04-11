<?php

namespace App\Notifications;

use App\Models\Dokumen;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

/**
 * R6: Notifikasi in-app (database) untuk Bagian saat dokumen dikembalikan
 * dari Team Verifikasi.
 *
 * Digunakan sebagai fallback ketika user Bagian tidak memiliki nomor HP
 * untuk notifikasi WhatsApp via Fonnte.
 */
class DokumenDikembalikanNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Dokumen $dokumen,
        protected string $alasan,
        protected string $bidangName
    ) {}

    /**
     * Kanal notifikasi — hanya database (in-app) untuk fallback.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Representasi database untuk tabel notifications.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type'          => 'dokumen_dikembalikan',
            'dokumen_id'    => $this->dokumen->id,
            'nomor_agenda'  => $this->dokumen->nomor_agenda,
            'nomor_spp'     => $this->dokumen->nomor_spp,
            'bidang_name'   => $this->bidangName,
            'alasan'        => $this->alasan,
            'returned_at'   => now()->toDateTimeString(),
            'url'           => route('inbox.show', $this->dokumen->id),
            'message'       => "Dokumen {$this->dokumen->nomor_agenda} dikembalikan dari Team Verifikasi ke Bidang {$this->bidangName}.",
        ];
    }
}

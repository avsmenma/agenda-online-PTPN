<?php

namespace App\Console\Commands;

use App\Services\PendingApprovalReminderService;
use Illuminate\Console\Command;

/**
 * Kirim pengingat WhatsApp untuk dokumen yang menggantung di inbox — sudah
 * dikirim ke suatu peran tapi belum di-approve melewati ambang waktu.
 *
 * Dijadwalkan tiap jam di routes/console.php. Ambang & masa tenang diatur di
 * config/fonnte.php ('pending_approval').
 */
class SendPendingApprovalReminders extends Command
{
    protected $signature = 'notifications:send-pending-approval
                            {--dry-run : Hitung saja, jangan kirim pesan}';

    protected $description = 'Ingatkan peran penerima yang belum menyetujui dokumen di inbox melewati ambang waktu';

    public function handle(PendingApprovalReminderService $service): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $ambang = (int) config('fonnte.pending_approval.hours', 6);

        $this->info('Pengingat dokumen menggantung di inbox');
        $this->line('Ambang: ' . $ambang . ' jam | Waktu: ' . now()->format('Y-m-d H:i:s'));

        if ($dryRun) {
            $this->warn('>>> DRY RUN — tidak ada pesan yang dikirim <<<');
        }

        if (! config('fonnte.enabled')) {
            $this->error('Notifikasi WhatsApp dinonaktifkan (WHATSAPP_NOTIFICATIONS_ENABLED).');

            return self::FAILURE;
        }

        if (empty(config('fonnte.api_token'))) {
            $this->error('FONNTE_API_TOKEN belum diisi.');

            return self::FAILURE;
        }

        try {
            $hasil = $service->kirimPengingat($dryRun);
        } catch (\Throwable $e) {
            $this->error('Gagal: ' . $e->getMessage());

            return self::FAILURE;
        }

        $this->newLine();
        $this->table(['Metrik', 'Jumlah'], [
            ['Dokumen menggantung diperiksa', $hasil['diperiksa']],
            ['Pengingat terkirim', $hasil['terkirim']],
            ['Dilewati', $hasil['dilewati']],
            ['Gagal', $hasil['gagal']],
        ]);

        foreach ($hasil['rincian'] as $baris) {
            $this->line(sprintf(
                '  #%d (%s) — %s, %d jam — %s',
                $baris['dokumen_id'],
                $baris['nomor_agenda'] ?? '-',
                $baris['peran'],
                $baris['menggantung_jam'],
                $baris['alasan']
            ));
        }

        return $hasil['gagal'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}

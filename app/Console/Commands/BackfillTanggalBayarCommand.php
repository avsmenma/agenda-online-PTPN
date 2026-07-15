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

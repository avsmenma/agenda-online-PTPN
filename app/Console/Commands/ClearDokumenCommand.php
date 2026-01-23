<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Dokumen;
use App\Models\DokumenRoleData;
use App\Models\DokumenStatus;
use App\Models\DokumenPo;
use App\Models\DokumenPr;
use App\Models\DibayarKepada;
use Illuminate\Support\Facades\DB;

class ClearDokumenCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dokumen:clear {--force : Force the operation without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menghapus semua dokumen dan data terkait dari database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('Apakah Anda yakin ingin menghapus SEMUA dokumen dan data terkait? Tindakan ini tidak dapat dibatalkan!')) {
                $this->info('Operasi dibatalkan.');
                return 0;
            }
        }

        $this->info('Menghapus semua dokumen dan data terkait...');

        try {
            DB::beginTransaction();

            // Disable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // Get counts before deletion
            $dokumenCount = Dokumen::count();
            $roleDataCount = DokumenRoleData::count();
            $statusCount = DokumenStatus::count();
            $poCount = DokumenPo::count();
            $prCount = DokumenPr::count();
            $dibayarKepadaCount = DibayarKepada::count();

            // Delete related data first
            $this->info('Menghapus data terkait...');
            DibayarKepada::truncate();
            DokumenPo::truncate();
            DokumenPr::truncate();
            DokumenRoleData::truncate();
            DokumenStatus::truncate();
            Dokumen::truncate();

            // Enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            DB::commit();

            $this->info('✓ Semua dokumen dan data terkait berhasil dihapus!');
            $this->table(
                ['Tabel', 'Jumlah Data yang Dihapus'],
                [
                    ['Dokumen', $dokumenCount],
                    ['Dokumen Role Data', $roleDataCount],
                    ['Dokumen Status', $statusCount],
                    ['Dokumen PO', $poCount],
                    ['Dokumen PR', $prCount],
                    ['Dibayar Kepada', $dibayarKepadaCount],
                ]
            );

            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}






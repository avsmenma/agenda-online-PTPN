<?php

namespace App\Services;

use App\Models\Dokumen;
use App\Models\ItemSubKriteria;
use App\Models\KategoriKriteria;
use App\Models\SubKriteria;
use App\Models\SyncLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * DokumenSyncService
 *
 * Menangani sinkronisasi data dokumen dari Agenda Online → Cash Bank.
 *
 * Arsitektur:
 *   - Agenda Online punya koneksi 'cash_bank_new' ke DB Cash Bank
 *   - Cash Bank punya koneksi 'mysql_agenda_online' ke DB Agenda Online
 *   - Service ini dijalankan di sisi Agenda Online
 *
 * Field yang disinkronkan ke Cash Bank (tabel bank_keluars):
 *   - uraian_spp  → uraian
 *   - nilai_rupiah → nilai_rupiah
 *   - dibayar_kepada → penerima
 *   - status_pembayaran → (dicatat di sync_log, CB punya otoritas)
 *   - tanggal_dibayar → tanggal
 *   - nomor_agenda → no_agenda
 *   - kategori → id_kategori_kriteria (reverse name→ID lookup)
 *   - jenis_dokumen → id_sub_kriteria (reverse name→ID lookup)
 *   - jenis_sub_pekerjaan → id_item_sub_kriteria (reverse name→ID lookup)
 *
 * Tabel Cash Bank yang berkaitan dengan kategori:
 *   - kategori_kriteria (id_kategori_kriteria, nama_kriteria)
 *   - sub_kriteria (id_sub_kriteria, id_kategori_kriteria, nama_sub_kriteria)
 *   - item_sub_kriteria (id_item_sub_kriteria, id_sub_kriteria, nama_item_sub_kriteria)
 */
class DokumenSyncService
{
    /**
     * Field di Dokumen AO yang akan di-push ke Cash Bank (bank_keluars).
     * Perubahan pada salah satu field ini akan men-trigger sync.
     */
    public const SYNCABLE_FIELDS = [
        'uraian_spp',
        'nilai_rupiah',
        'dibayar_kepada',
        'status_pembayaran',
        'tanggal_dibayar',
        'link_bukti_pembayaran',
        'nomor_agenda',
        'kategori',
        'jenis_dokumen',
        'jenis_sub_pekerjaan',
        'jenis_pembayaran',
        'keterangan',
    ];

    /**
     * Mapping field AO → field di tabel bank_keluars Cash Bank
     */
    private const FIELD_MAP = [
        'uraian_spp'      => 'uraian',
        'nilai_rupiah'    => 'nilai_rupiah',
        'dibayar_kepada'  => 'penerima',
        'tanggal_dibayar' => 'tanggal',
        'keterangan'      => 'keterangan',
        'nomor_agenda'    => 'no_agenda',
    ];

    /**
     * Push perubahan dokumen dari Agenda Online ke Cash Bank.
     * Mengupdate record di tabel bank_keluars yang memiliki dokumen_id atau no_agenda yang cocok.
     */
    public function pushToCashBank(Dokumen $dokumen): void
    {
        // Guard: cegah infinite sync loop
        if ($dokumen->_syncing ?? false) {
            return;
        }

        try {
            $cbConnection = config('sync.cashbank_connection', 'cash_bank_new');

            // Cari record di bank_keluars berdasarkan dokumen_id atau nomor_agenda
            $bankKeluar = DB::connection($cbConnection)
                ->table('bank_keluars')
                ->where(function ($q) use ($dokumen) {
                    $q->where('dokumen_id', $dokumen->id)
                      ->orWhere('no_agenda', $this->buildAgendaKey($dokumen));
                })
                ->first();

            if (! $bankKeluar) {
                // Dokumen belum ada di Cash Bank — tidak ada yang perlu diupdate
                Log::info('[DokumenSync] Dokumen tidak ditemukan di Cash Bank, skip.', [
                    'dokumen_id'    => $dokumen->id,
                    'nomor_agenda'  => $dokumen->nomor_agenda,
                ]);
                $this->log($dokumen->id, 'ao_to_cb', 'skipped_no_change', [], null, null,
                    'Record tidak ditemukan di bank_keluars');
                return;
            }

            // Build payload yang akan diupdate ke Cash Bank
            $updatePayload = [];
            $fieldsSynced  = [];

            foreach (self::FIELD_MAP as $aoField => $cbField) {
                $value = $dokumen->$aoField;
                if ($value !== null) {
                    $updatePayload[$cbField] = $value;
                    $fieldsSynced[]          = "{$aoField}→{$cbField}";
                }
            }

            // Sync kategori → id_kategori_kriteria (reverse name → ID lookup)
            if (!empty($dokumen->kategori)) {
                try {
                    $kategoriKriteria = KategoriKriteria::where('nama_kriteria', $dokumen->kategori)->first();
                    if ($kategoriKriteria) {
                        $updatePayload['id_kategori_kriteria'] = $kategoriKriteria->id_kategori_kriteria;
                        $fieldsSynced[] = 'kategori→id_kategori_kriteria';
                    }
                } catch (\Throwable $e) {
                    Log::warning('[DokumenSync] Gagal lookup kategori_kriteria: ' . $e->getMessage());
                }
            }

            // Sync jenis_dokumen → id_sub_kriteria (reverse name → ID lookup)
            if (!empty($dokumen->jenis_dokumen)) {
                try {
                    $subKriteriaQuery = SubKriteria::where('nama_sub_kriteria', $dokumen->jenis_dokumen);
                    // Jika kategori sudah di-resolve, filter by parent untuk akurasi
                    if (isset($updatePayload['id_kategori_kriteria'])) {
                        $subKriteriaQuery->where('id_kategori_kriteria', $updatePayload['id_kategori_kriteria']);
                    }
                    $subKriteria = $subKriteriaQuery->first();
                    if ($subKriteria) {
                        $updatePayload['id_sub_kriteria'] = $subKriteria->id_sub_kriteria;
                        $fieldsSynced[] = 'jenis_dokumen→id_sub_kriteria';
                    }
                } catch (\Throwable $e) {
                    Log::warning('[DokumenSync] Gagal lookup sub_kriteria: ' . $e->getMessage());
                }
            }

            // Sync jenis_sub_pekerjaan → id_item_sub_kriteria (reverse name → ID lookup)
            if (!empty($dokumen->jenis_sub_pekerjaan)) {
                try {
                    $itemQuery = ItemSubKriteria::where('nama_item_sub_kriteria', $dokumen->jenis_sub_pekerjaan);
                    // Jika sub_kriteria sudah di-resolve, filter by parent untuk akurasi
                    if (isset($updatePayload['id_sub_kriteria'])) {
                        $itemQuery->where('id_sub_kriteria', $updatePayload['id_sub_kriteria']);
                    }
                    $itemSubKriteria = $itemQuery->first();
                    if ($itemSubKriteria) {
                        $updatePayload['id_item_sub_kriteria'] = $itemSubKriteria->id_item_sub_kriteria;
                        $fieldsSynced[] = 'jenis_sub_pekerjaan→id_item_sub_kriteria';
                    }
                } catch (\Throwable $e) {
                    Log::warning('[DokumenSync] Gagal lookup item_sub_kriteria: ' . $e->getMessage());
                }
            }

            // Tambahkan updated_at untuk tracking
            $updatePayload['updated_at'] = now();

            if (empty($updatePayload)) {
                $this->log($dokumen->id, 'ao_to_cb', 'skipped_no_change', [], null, null,
                    'Tidak ada data untuk di-sync');
                return;
            }

            // Eksekusi update ke Cash Bank
            $affected = DB::connection($cbConnection)
                ->table('bank_keluars')
                ->where('id_bank_keluar', $bankKeluar->id_bank_keluar)
                ->update($updatePayload);

            Log::info('[DokumenSync] Push AO → CB berhasil.', [
                'dokumen_id'   => $dokumen->id,
                'bank_keluar'  => $bankKeluar->id_bank_keluar,
                'fields'       => $fieldsSynced,
                'rows_affected'=> $affected,
            ]);

            $this->log($dokumen->id, 'ao_to_cb', 'success', $fieldsSynced);

        } catch (\Throwable $e) {
            Log::error('[DokumenSync] Push AO → CB GAGAL.', [
                'dokumen_id' => $dokumen->id,
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ]);

            $this->log($dokumen->id, 'ao_to_cb', 'failed', [], null, null,
                $e->getMessage());

            // Tidak throw — jangan interrupt alur utama Agenda Online
        }
    }

    /**
     * Sync semua dokumen yang berubah dalam periode tertentu.
     * Digunakan oleh Artisan command sebagai fallback polling.
     *
     * @param  string  $since  Format PHP strtotime, contoh: '5 minutes ago', '1 hour ago'
     */
    public function syncAllChanged(string $since = '5 minutes ago'): int
    {
        $sinceTime = now()->sub(now()->diffAsCarbonInterval(
            \Carbon\Carbon::createFromTimestamp(strtotime($since))
        ));

        $dokumens = Dokumen::where('updated_at', '>=', $sinceTime)
            ->whereNotNull('nomor_agenda')
            ->get();

        $count = 0;
        foreach ($dokumens as $dokumen) {
            $this->pushToCashBank($dokumen);
            $count++;
        }

        return $count;
    }

    /**
     * Sync satu dokumen berdasarkan ID.
     */
    public function syncById(int $dokumenId): void
    {
        $dokumen = Dokumen::findOrFail($dokumenId);
        $this->pushToCashBank($dokumen);
    }

    /**
     * Build key agenda untuk pencarian di Cash Bank.
     * Format Cash Bank: '{nomor_agenda}_{tahun}' atau hanya nomor_agenda
     */
    private function buildAgendaKey(Dokumen $dokumen): ?string
    {
        if (empty($dokumen->nomor_agenda)) {
            return null;
        }

        // Cash Bank menyimpan agenda_tahun seperti '0004_2026'
        // Kita coba match dengan nomor_agenda langsung
        return $dokumen->nomor_agenda;
    }

    /**
     * Catat aktivitas sync ke tabel sync_logs.
     */
    private function log(
        ?int $dokumenId,
        string $direction,
        string $status,
        array $fieldsSynced,
        ?array $conflictFields = null,
        ?string $sourceWins = null,
        ?string $errorMessage = null
    ): void {
        try {
            SyncLog::create([
                'dokumen_id'     => $dokumenId,
                'direction'      => $direction,
                'status'         => $status,
                'fields_synced'  => $fieldsSynced,
                'conflict_fields'=> $conflictFields,
                'source_wins'    => $sourceWins,
                'error_message'  => $errorMessage,
                'synced_at'      => now(),
            ]);
        } catch (\Throwable $e) {
            // Jangan sampai logging gagal menghentikan sync
            Log::warning('[DokumenSync] Gagal mencatat sync_log: ' . $e->getMessage());
        }
    }
}

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

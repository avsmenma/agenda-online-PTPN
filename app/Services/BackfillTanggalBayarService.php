<?php

namespace App\Services;

use App\Models\Dokumen;
use App\Models\SyncLog;
use Illuminate\Support\Facades\DB;

/**
 * Backfill sekali-jalan: isi tanggal_dibayar (dan status_pembayaran) dokumen
 * Agenda dari tanggal bank keluar Cash Bank. Satu arah (CB → AO), hanya bila
 * tanggal_dibayar kosong, pakai tanggal TERAWAL. Tidak menimpa tanggal yang ada.
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
    public function run(bool $dryRun = false, ?int $limit = null, bool $tanggalSaja = false): array
    {
        // 1. Muat peta dokumen Agenda ke memori.
        $dokById         = [];   // id => object{nomor_agenda, tahun, tanggal_dibayar}
        $idsByNomor      = [];   // nomor_agenda => [id, ...]
        $idsByNomorTahun = [];   // "nomor|tahun" => [id, ...]

        Dokumen::query()
            ->select(['id', 'nomor_agenda', 'tahun', 'tanggal_dibayar'])
            ->chunk(500, function ($rows) use (&$dokById, &$idsByNomor, &$idsByNomorTahun) {
                foreach ($rows as $d) {
                    $dokById[$d->id] = $d;
                    if (!empty($d->nomor_agenda)) {
                        $idsByNomor[$d->nomor_agenda][] = $d->id;
                        $idsByNomorTahun[$d->nomor_agenda . '|' . $d->tahun][] = $d->id;
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
            // JANGAN pakai ->where('tanggal','!=','') : pada kolom DATE MySQL,
            // membandingkan ke '' membuat seluruh predikat NULL sehingga SEMUA
            // baris terfilter (hasil 0). Baris '0000-00-00' sudah disaring di PHP
            // (cek substr di bawah). Bug ini lolos di test karena SQLite berbeda.
            ->orderBy('id_bank_keluar')
            ->chunk(1000, function ($rows) use (&$earliestByDokId, &$unmatchedKeys, $dokById, $idsByNomor, $idsByNomorTahun) {
                foreach ($rows as $r) {
                    $tgl = substr((string) $r->tanggal, 0, 10);
                    if ($tgl === '' || $tgl === '0000-00-00') {
                        continue;
                    }

                    $dokId = $this->resolveDokumenId($r, $dokById, $idsByNomor, $idsByNomorTahun, $unmatchedKeys);
                    if ($dokId === null) {
                        continue; // resolveDokumenId sudah mencatat unmatched
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
                    // Set tanggal_dibayar + status_pembayaran. Sengaja TIDAK
                    // menyentuh updated_at: fallback poller `dokumen:sync-cashbank
                    // --since` mencari updated_at terbaru lalu mendorong balik ke
                    // Cash Bank; membiarkan updated_at apa adanya mencegah push-balik.
                    // Perubahan status_pembayaran via raw write akan memicu MySQL
                    // trigger auto-forward (ProcessAutoForwardQueue) di produksi —
                    // sesuai keputusan bisnis saat backfill historis sekali-jalan
                    // (dokumen historis mengalir ke Pembayaran).
                    //
                    // Mode --tanggal-saja mematikan efek itu: hanya tanggal yang
                    // diisi, status dan alur dokumen tetap milik operator. Mode
                    // inilah yang dipakai penjadwalan rutin, supaya jaring pengaman
                    // tidak diam-diam mendorong dokumen ke Pembayaran.
                    $isian = ['tanggal_dibayar' => $earliest];
                    if (!$tanggalSaja) {
                        $isian['status_pembayaran'] = 'sudah_dibayar';
                    }

                    DB::table('dokumens')->where('id', $dokId)->update($isian);
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

    /**
     * Resolusikan satu baris bank_keluar ke id dokumen Agenda.
     * Prioritas: dokumen_id → agenda_tahun → no_agenda.
     * agenda_tahun/no_agenda bisa polos ('0004') atau komposit ('0004_2026').
     * Komposit dicocokkan ke (nomor_agenda + tahun); polos ke nomor_agenda saja
     * dan HANYA bila unik (bila >1 dokumen → ambigu, dilewati demi keamanan).
     */
    private function resolveDokumenId(
        object $r,
        array $dokById,
        array $idsByNomor,
        array $idsByNomorTahun,
        array &$unmatchedKeys
    ): ?int {
        if (!empty($r->dokumen_id) && isset($dokById[$r->dokumen_id])) {
            return (int) $r->dokumen_id;
        }

        foreach ([$r->agenda_tahun, $r->no_agenda] as $raw) {
            if (empty($raw)) {
                continue;
            }
            $raw = (string) $raw;

            // 1. Cocok LANGSUNG ke nomor_agenda. Ini kasus data nyata: kedua sisi
            //    menyimpan format sama, mis. dokumens.nomor_agenda='5000_2026' dan
            //    bank_keluars.agenda_tahun='5000_2026' (atau sama-sama polos '5000').
            if (isset($idsByNomor[$raw])) {
                if (count($idsByNomor[$raw]) === 1) {
                    return (int) $idsByNomor[$raw][0];
                }
                $unmatchedKeys['ambigu:' . $raw] = true;
                return null;
            }

            // 2. Fallback: raw komposit '{nomor}_{tahun}' sedangkan nomor_agenda
            //    tersimpan polos → cocokkan (nomor_agenda + tahun).
            if (preg_match('/^(.+)_(\d{4})$/', $raw, $m)) {
                $key = $m[1] . '|' . $m[2];
                if (isset($idsByNomorTahun[$key])) {
                    if (count($idsByNomorTahun[$key]) === 1) {
                        return (int) $idsByNomorTahun[$key][0];
                    }
                    $unmatchedKeys['ambigu:' . $key] = true;
                    return null;
                }
                // 3. Fallback terakhir: nomor polos hasil pisah, hanya bila unik.
                if (isset($idsByNomor[$m[1]])) {
                    if (count($idsByNomor[$m[1]]) === 1) {
                        return (int) $idsByNomor[$m[1]][0];
                    }
                    $unmatchedKeys['ambigu:' . $m[1]] = true;
                    return null;
                }
            }
        }

        $key = !empty($r->dokumen_id)
            ? 'id:' . $r->dokumen_id
            : 'agenda:' . ($r->agenda_tahun ?: $r->no_agenda ?: '?');
        $unmatchedKeys[$key] = true;
        return null;
    }
}

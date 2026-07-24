<?php

namespace App\Support;

use App\Models\Dokumen;

/**
 * Basis abstrak DTO baris tabel dokumen (Tabulator). Memuat derivasi yang
 * dipakai SEMUA role: salin kolom base, format rupiah/DPP/PPN, join relasi
 * (dibayar_kepada, nomor_po), tanggal terformat sisi-server, dropdown pengurus
 * (handler + handler_options + can_change_handler), dan link ter-sanitasi.
 *
 * Subclass (OperatorDocumentRow, AkutansiDocumentRow) memanggil baseRow() lalu
 * MENAMBAH bit khas peran (display_status/can_edit operator; status_badge/
 * deadline/lock akutansi). Basis TIDAK menyertakan display_status, reject_*,
 * maupun can_edit karena tiap role menghitungnya berbeda.
 *
 * Prasyarat: relasi roleStatuses, dibayarKepadas, dokumenPos sudah ter-eager-load
 * (basis TIDAK query DB). $handlerOptions disediakan pemanggil (hindari query
 * per-baris) dan ditanamkan apa adanya.
 */
abstract class DocumentRow
{
    protected static function baseRow(Dokumen $dokumen, array $handlerOptions, ?string $viewerRole = null): array
    {
        $statuses = $dokumen->roleStatuses;

        // === Nilai ATTRIBUTE mentah untuk setiap kolom base (formatter di klien) ===
        $row = ['id' => $dokumen->id];
        foreach (array_keys(config('document_columns.base')) as $key) {
            $row[$key] = $dokumen->{$key};
        }

        // Status dokumen: utamakan nilai CSV bila ada, fallback ke custom
        // (identik _tableRowsAjax.blade.php:203-204). Kosong/null tetap null; klien merender '-'.
        $row['status_dokumen_custom'] = $dokumen->status_dokumen_csv ?? $dokumen->status_dokumen_custom;

        // === Kunci turunan tampilan bersama ===
        $row['nilai_rupiah_formatted']  = $dokumen->formatted_nilai_rupiah;
        $row['dpp_pph_formatted']       = $dokumen->dpp_pph !== null
            ? 'Rp ' . number_format((float) $dokumen->dpp_pph, 0, ',', '.')
            : '-';
        $row['ppn_terhutang_formatted'] = $dokumen->ppn_terhutang !== null
            ? 'Rp ' . number_format((float) $dokumen->ppn_terhutang, 0, ',', '.')
            : '-';
        // Join nama penerima, fallback ke kolom flat lama bila relasi kosong.
        $row['dibayar_kepada']     = $dokumen->dibayarKepadas->pluck('nama_penerima')->join(', ') ?: ($dokumen->dibayar_kepada ?? '');
        // Join nomor PO dengan fallback ke kolom CSV NO_PO.
        $row['nomor_po']           = $dokumen->dokumenPos->pluck('nomor_po')->filter()->join(', ') ?: ($dokumen->NO_PO ?? '-');
        $row['nomor_miro_display'] = $dokumen->nomor_miro_display;
        $row['handler']            = $dokumen->current_handler;
        $row['handler_options']    = $handlerOptions;

        // === can_change_handler: paritas gate dropdown pengurus. 'verifikasi' &
        // 'team_verifikasi' disamakan (alias lama/baru), perbandingan case-insensitive. ===
        $isCurrentHandler = $viewerRole !== null
            && static::normalizeRole($viewerRole) === static::normalizeRole($dokumen->current_handler ?? '');
        $hasPending = $statuses->contains(
            fn ($s) => strtolower((string) $s->status) === strtolower(\App\Models\DokumenStatus::STATUS_PENDING)
        );
        $row['can_change_handler'] = $isCurrentHandler && ! $hasPending;

        // === URL link ter-sanitasi (SafeUrl::external memaksa skema http(s)). ===
        $row['link_safe']               = SafeUrl::external($dokumen->link);
        $row['link_dokumen_pajak_safe'] = SafeUrl::external($dokumen->link_dokumen_pajak);

        // === Tanggal terformat sisi-server. Null/kosong → '-'. ===
        $row['dates'] = static::formatDates($dokumen);

        return $row;
    }

    protected static function normalizeRole(?string $role): string
    {
        $role = strtolower(trim((string) $role));
        return $role === 'verifikasi' ? 'team_verifikasi' : $role;
    }

    /**
     * Peta kolom tanggal → string terformat (format identik render lama).
     * Kolom cast (Carbon) diformat langsung; kolom string mentah di-parse defensif.
     */
    protected static function formatDates(Dokumen $dokumen): array
    {
        $formats = [
            'tanggal_spp'                      => 'd-m-Y',
            'tanggal_berita_acara'             => 'd-m-Y',
            'tanggal_spk'                      => 'd-m-Y',
            'tanggal_berakhir_spk'             => 'd-m-Y',
            'tanggal_masuk'                    => 'd-m-Y H:i',
            'tanggal_paraf'                    => 'd/m/Y H:i',
            'tanggal_selesai_diproses'         => 'd/m/Y H:i',
            'tanggal_kembali_ke_bagian'        => 'd/m/Y H:i',
            'tanggal_hasil_koreksi_bagian'     => 'd/m/Y H:i',
            'tanggal_dibayar'                  => 'd/m/Y',
            'tanggal_faktur'                   => 'd/m/Y',
            'tanggal_selesai_verifikasi_pajak' => 'd/m/Y',
        ];

        $dates = [];
        foreach ($formats as $col => $format) {
            $value = $dokumen->{$col} ?? null;

            if ($value === null || $value === '') {
                $dates[$col] = '-';
                continue;
            }

            if ($value instanceof \DateTimeInterface) {
                $dates[$col] = $value->format($format);
                continue;
            }

            try {
                $dates[$col] = \Illuminate\Support\Carbon::parse($value)->format($format);
            } catch (\Throwable $e) {
                $dates[$col] = '-';
            }
        }

        return $dates;
    }
}

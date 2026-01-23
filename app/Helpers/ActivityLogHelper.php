<?php

namespace App\Helpers;

use App\Models\Dokumen;
use App\Models\DokumenActivityLog;
use Carbon\Carbon;

class ActivityLogHelper
{
    /**
     * Log activity untuk dokumen
     */
    public static function log(Dokumen $dokumen, string $action, string $actionDescription, ?string $stage = null, ?string $performedBy = null, ?array $details = null): void
    {
        DokumenActivityLog::create([
            'dokumen_id' => $dokumen->id,
            'stage' => $stage ?? self::getStageFromHandler($dokumen->current_handler),
            'action' => $action,
            'action_description' => $actionDescription,
            'performed_by' => $performedBy ?? $dokumen->current_handler,
            'details' => $details,
            'action_at' => Carbon::now(),
        ]);
    }

    /**
     * Log ketika dokumen dOperatort
     */
    public static function logCreated(Dokumen $dokumen): void
    {
        self::log(
            $dokumen,
            'created',
            'Dokumen dOperatort',
            'sender',
            'Operator',
            ['nomor_agenda' => $dokumen->nomor_agenda]
        );
    }

    /**
     * Log ketika dokumen dikirim
     * Log ini muncul di stage PENGIRIM (from), bukan penerima (to)
     */
    public static function logSent(Dokumen $dokumen, string $to, ?string $from = null): void
    {
        $from = $from ?? $dokumen->current_handler;
        $stage = self::getStageFromHandler($from); // Stage pengirim, bukan penerima

        $descriptions = [
            'Team Verifikasi' => 'Dokumen dikirim ke Team Verifikasi',
            'perpajakan' => 'Dokumen dikirim ke Team Perpajakan',
            'akutansi' => 'Dokumen dikirim ke Team Akutansi',
            'pembayaran' => 'Dokumen dikirim ke Team Pembayaran',
        ];

        self::log(
            $dokumen,
            'sent',
            $descriptions[$to] ?? "Dokumen dikirim ke {$to}",
            $stage, // Stage pengirim
            $from,
            ['to' => $to, 'from' => $from]
        );
    }

    /**
     * Log ketika dokumen diterima/masuk
     * Log ini muncul di stage PENERIMA
     */
    public static function logReceived(Dokumen $dokumen, string $by): void
    {
        $stage = self::getStageFromHandler($by);

        // Format: "Dokumen masuk pada tanggal" - tanggal dan jam akan ditambahkan di view
        $description = 'Dokumen masuk pada tanggal';

        self::log(
            $dokumen,
            'received',
            $description,
            $stage,
            $by
        );
    }

    /**
     * Log ketika deadline di-set
     * Format: "Deadline diatur pada tanggal {tanggal} jam {jam}"
     */
    public static function logDeadlineSet(Dokumen $dokumen, ?string $by = null, ?array $details = null): void
    {
        $by = $by ?? $dokumen->current_handler;
        $stage = self::getStageFromHandler($by);

        // Format: "Deadline diatur pada tanggal" - tanggal dan jam akan ditambahkan di view
        $description = 'Deadline diatur pada tanggal';

        self::log(
            $dokumen,
            'deadline_set',
            $description,
            $stage,
            $by,
            $details
        );
    }

    /**
     * Log ketika data di-edit
     */
    public static function logDataEdited(Dokumen $dokumen, string $field, ?string $oldValue = null, ?string $newValue = null, ?string $by = null): void
    {
        $by = $by ?? $dokumen->current_handler;
        $stage = self::getStageFromHandler($by);

        $fieldNames = [
            'no_faktur' => 'No Faktur',
            'tanggal_faktur' => 'Tanggal Faktur',
            'tanggal_selesai_verifikasi_pajak' => 'Tanggal Selesai Verifikasi Pajak',
            'npwp' => 'NPWP',
            'status_perpajakan' => 'Status Perpajakan',
            'jenis_pph' => 'Jenis PPh',
            'dpp_pph' => 'DPP PPh',
            'ppn_terhutang' => 'PPN Terhutang',
            'link_dokumen_pajak' => 'Link Dokumen Pajak',
            'nomor_miro' => 'Nomor MIRO',
            'status_pembayaran' => 'Status Pembayaran',
            'link_bukti_pembayaran' => 'Link Bukti Pembayaran',
            'tanggal_dibayar' => 'Tanggal Dibayar',
            // Field-field dari DokumenController
            'nomor_agenda' => 'Nomor Agenda',
            'bulan' => 'Bulan',
            'tahun' => 'Tahun',
            'nomor_spp' => 'Nomor SPP',
            'tanggal_spp' => 'Tanggal SPP',
            'uraian_spp' => 'Uraian SPP',
            'nilai_rupiah' => 'Nilai Rupiah',
            'kategori' => 'Kriteria FC',
            'jenis_dokumen' => 'Sub Kriteria',
            'jenis_sub_pekerjaan' => 'Item Sub Kriteria',
            'jenis_pembayaran' => 'Jenis Pembayaran',
            'kebun' => 'Kebun',
            'bagian' => 'Bagian',
            'nama_pengirim' => 'Nama Pengirim',
            'no_berita_acara' => 'Nomor Berita Acara',
            'tanggal_berita_acara' => 'Tanggal Berita Acara',
            'no_spk' => 'Nomor SPK',
            'tanggal_spk' => 'Tanggal SPK',
            'tanggal_berakhir_spk' => 'Tanggal Berakhir SPK',
        ];

        $fieldName = $fieldNames[$field] ?? $field;

        // Format description dengan old dan new value
        $oldValueStr = $oldValue ?? 'kosong';
        $newValueStr = $newValue ?? 'kosong';
        $description = "Edit data {$fieldName} dari '{$oldValueStr}' menjadi '{$newValueStr}'";

        self::log(
            $dokumen,
            'data_edited',
            $description,
            $stage,
            $by,
            [
                'field' => $field,
                'field_name' => $fieldName,
                'old_value' => $oldValue,
                'new_value' => $newValue,
            ]
        );
    }

    /**
     * Log ketika form diisi
     */
    public static function logFormFilled(Dokumen $dokumen, string $formName, ?string $by = null, ?array $details = null): void
    {
        $by = $by ?? $dokumen->current_handler;
        $stage = self::getStageFromHandler($by);

        self::log(
            $dokumen,
            'form_filled',
            "Form {$formName} diisi",
            $stage,
            $by,
            array_merge(['form_name' => $formName], $details ?? [])
        );
    }

    /**
     * Log ketika dokumen dikembalikan
     */
    public static function logReturned(Dokumen $dokumen, string $to, ?string $reason = null, ?string $by = null): void
    {
        $by = $by ?? $dokumen->current_handler;
        $stage = self::getStageFromHandler($by);

        self::log(
            $dokumen,
            'returned',
            "Dokumen dikembalikan ke {$to}",
            $stage,
            $by,
            ['to' => $to, 'reason' => $reason]
        );
    }

    /**
     * Log ketika status diubah
     */
    public static function logStatusChanged(Dokumen $dokumen, string $oldStatus, string $newStatus, ?string $by = null): void
    {
        $by = $by ?? $dokumen->current_handler;
        $stage = self::getStageFromHandler($by);

        self::log(
            $dokumen,
            'status_changed',
            "Status diubah dari {$oldStatus} menjadi {$newStatus}",
            $stage,
            $by,
            ['old_status' => $oldStatus, 'new_status' => $newStatus]
        );
    }

    /**
     * Get stage dari handler
     */
    private static function getStageFromHandler(?string $handler): ?string
    {
        $stageMap = [
            'Operator' => 'sender',
            'Team Verifikasi' => 'reviewer',
            'perpajakan' => 'tax',
            'akutansi' => 'accounting',
            'pembayaran' => 'payment',
        ];

        return $stageMap[$handler] ?? null;
    }
}







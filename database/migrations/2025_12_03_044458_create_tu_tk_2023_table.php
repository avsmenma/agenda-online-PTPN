<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create table using raw SQL because of special column names (numbers)
        DB::statement("
            CREATE TABLE IF NOT EXISTS `tu_tk_2023` (
                `KONTROL` bigint DEFAULT NULL,
                `AGENDA` text,
                `TGL_SPP` text,
                `NO_SPP` text,
                `KATEGORI` text,
                `VENDOR` text,
                `NO_KONTRAK` text,
                `TGL_KONTRAK` text,
                `TGL_KONTRAK_BERAKHIR` text,
                `NO_BERITA_ACARA` text,
                `TGL_BERITA_ACARA` text,
                `TGL_FAKTUR_PAJAK` text,
                `HAL` text,
                `NILAI` double DEFAULT NULL,
                `POSISI_DOKUMEN` text,
                `TANGGAL_MASUK_DOKUMEN` text,
                `PROSES_VERIFIKASI` text,
                `KETERANGAN_VERIFIKASI` text,
                `NILAI_SETELAH_VERIFIKASI` double DEFAULT NULL,
                `DIBAYAR` double DEFAULT NULL,
                `BELUM_DIBAYAR` double DEFAULT NULL,
                `NO_PO` text,
                `NO_MIRO_SES` text,
                `SIAP_BAYAR` double DEFAULT NULL,
                `BELUM_SIAP_BAYAR` double DEFAULT NULL,
                `KETERANGAN` double DEFAULT NULL,
                `DIBUKUKAN_TAHUN_2023` double DEFAULT NULL,
                `DIBUKUKAN_TAHUN_2024` double DEFAULT NULL,
                `TGL_BUKU_TAHUN_2024` double DEFAULT NULL,
                `JUMLAH` text,
                `TANGGAL_BAYAR_I` double DEFAULT NULL,
                `JUMLAH1` text,
                `TANGGAL_BAYAR_II` double DEFAULT NULL,
                `JUMLAH2` text,
                `TANGGAL_BAYAR_III` double DEFAULT NULL,
                `JUMLAH3` text,
                `TANGGAL_BAYAR_IV` double DEFAULT NULL,
                `JUMLAH4` text,
                `TANGGAL_BAYAR_V` double DEFAULT NULL,
                `JUMLAH5` double DEFAULT NULL,
                `TANGGAL_BAYAR_VI` text,
                `JUMLAH6` double DEFAULT NULL,
                `TANGGAL_BAYAR_RAMPUNG` text,
                `JUMLAH_DIBAYAR` double DEFAULT NULL,
                `BELUM_DIBAYAR1` double DEFAULT NULL,
                `FILE_SPP` bigint DEFAULT NULL,
                `SUB_PEKERJAAN` text,
                `TGL_INPUT` double DEFAULT NULL,
                `UMUR_HUTANG_HARI` bigint DEFAULT NULL,
                `UMUR_SPP` text,
                `UMUR_SPK_HARI` text,
                `UMUR_SPK` text,
                `UMUR_BA_HARI` bigint DEFAULT NULL,
                `UMUR_BA` text,
                `SALDO_HUTANG` double DEFAULT NULL,
                `2023` text,
                `JAN` text,
                `FEB` text,
                `MAR` text,
                `APR` text,
                `MEI` text,
                `JUN` text,
                `JUL` text,
                `AGU` text,
                `SEP` text,
                `OKT` text,
                `NOV` text,
                `DES` text,
                `2024` text,
                `TOTAL` double DEFAULT NULL,
                `SELISIH` text,
                `JAN1` text,
                `FEB1` text,
                `MAR1` text,
                `APR1` text,
                `MEI1` text,
                `JUN1` text,
                `JUL1` text,
                `AGU1` text,
                `SEP1` text,
                `OKT1` text,
                `NOV1` text,
                `DES1` text,
                `2025` text,
                `TOTAL_2` double DEFAULT NULL,
                `SELISIH_2` double DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tu_tk_2023');
    }
};


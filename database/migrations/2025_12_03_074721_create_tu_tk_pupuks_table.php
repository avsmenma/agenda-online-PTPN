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
        // Create table using raw SQL because of special column names (numbers and double underscores)
        DB::statement("
            CREATE TABLE IF NOT EXISTS `tu_tk_pupuk_2023` (
                `EXTRA_COL_0` double DEFAULT NULL,
                `AGENDA` text,
                `TGL_SPP` text,
                `NO_SPP` text,
                `KEBUN` double DEFAULT NULL,
                `VENDOR` text,
                `NO_KONTRAK` text,
                `TGL__KONTRAK` text,
                `TGL__KONTRAK_BERAKHIR` text,
                `NO__BERITA_ACARA` text,
                `TGL__BERITA_ACARA` text,
                `TGL__FAKTUR_PAJAK` text,
                `HAL` text,
                `NILAI` double DEFAULT NULL,
                `POSISI_DOKUMEN` text,
                `TANGGAL_MASUK_DOKUMEN` text,
                `PROSES_VERIFIKASI` text,
                `KETERANGAN_VERIFIKASI` text,
                `NILAI_SETELAH_VERIFIKASI` double DEFAULT NULL,
                `DIBAYAR` double DEFAULT NULL,
                `BELUM_DIBAYAR` double DEFAULT NULL,
                `NO_PO` double DEFAULT NULL,
                `NO_MIRO_SES` text,
                `SIAP_BAYAR` double DEFAULT NULL,
                `BELUM_SIAP_BAYAR` double DEFAULT NULL,
                `KETERANGAN` double DEFAULT NULL,
                `DIBUKUKAN_TAHUN_2023` text,
                `DIBUKUKAN_TAHUN_2024` text,
                `TGL_BUKU_TAHUN_2024` text,
                `JUMLAH` double DEFAULT NULL,
                `TANGGAL_BAYAR_I` text,
                `JUMLAH_1` double DEFAULT NULL,
                `TANGGAL_BAYAR_II` text,
                `JUMLAH_2` double DEFAULT NULL,
                `TANGGAL_BAYAR_III` text,
                `JUMLAH_3` double DEFAULT NULL,
                `TANGGAL_BAYAR_IV` text,
                `JUMLAH_4` double DEFAULT NULL,
                `TANGGAL_BAYAR_V` text,
                `JUMLAH_5` double DEFAULT NULL,
                `TANGGAL_BAYAR_VI` text,
                `JUMLAH_6` double DEFAULT NULL,
                `TANGGAL_BAYAR_RAMPUNG` text,
                `JUMLAH_DIBAYAR` double DEFAULT NULL,
                `BELUM_DIBAYAR_1` double DEFAULT NULL,
                `FILE_SPP` double DEFAULT NULL,
                `EXTRA_COL_46` double DEFAULT NULL,
                `EXTRA_COL_47` double DEFAULT NULL,
                `UMUR_HUTANG_HARI` bigint DEFAULT NULL,
                `UMUR_SPP` text,
                `UMUR_SPK_HARI` double DEFAULT NULL,
                `UMUR_SPK` text,
                `UMUR_BA_HARI` double DEFAULT NULL,
                `UMUR_BA` text,
                `SALDO_HUTANG` double DEFAULT NULL,
                `2_023` text,
                `JAN` text,
                `FEB` double DEFAULT NULL,
                `MAR` double DEFAULT NULL,
                `APR` double DEFAULT NULL,
                `MEI` double DEFAULT NULL,
                `JUN` double DEFAULT NULL,
                `JUL` double DEFAULT NULL,
                `AGU` double DEFAULT NULL,
                `SEP` double DEFAULT NULL,
                `OKT` text,
                `NOV` double DEFAULT NULL,
                `DES` text,
                `SD_2024` text,
                `TOTAL` double DEFAULT NULL,
                `SELISIH` double DEFAULT NULL,
                `JAN_2` text,
                `FEB_2` text,
                `MAR_2` text,
                `APR_2` text,
                `MEI_2` text,
                `JUN_2` text,
                `JUL_2` text,
                `AGU_2` text,
                `SEP_2` text,
                `OKT_2` text,
                `NOV_2` text,
                `DES_2` text,
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
        Schema::dropIfExists('tu_tk_pupuk_2023');
    }
};

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
        DB::statement("
            CREATE TABLE IF NOT EXISTS `tu_tk_vd_2023` (
                `KONTROL` bigint DEFAULT NULL,
                `AGENDA` text,
                `TGL_SPP` text,
                `NO_SPP` text,
                `KATEGORI` text,
                `VENDOR` text,
                `NO_KONTRAK` text,
                `TGL__KONTRAK` text,
                `TGL__KONTRAK_BERAKHIR` text,
                `NO_BERITA_ACARA` text,
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
                `NO_PO` text,
                `NO_MIRO_SES` text,
                `SIAP_BAYAR` double DEFAULT NULL,
                `BELUM_SIAP_BAYAR` double DEFAULT NULL,
                `KETERANGAN` text,
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
                `SUB_PEKERJAAN` text,
                `TGL_INPUT` text,
                `UMUR_HUTANG_HARI` bigint DEFAULT NULL,
                `UMUR_SPP` text,
                `UMUR_SPK_HARI` bigint DEFAULT NULL,
                `UMUR_SPK` text,
                `UMUR_BA_HARI` bigint DEFAULT NULL,
                `UMUR_BA` text,
                `SALDO_HUTANG` double DEFAULT NULL,
                `2023` text,
                `JAN` double DEFAULT NULL,
                `FEB` double DEFAULT NULL,
                `MAR` double DEFAULT NULL,
                `APR` double DEFAULT NULL,
                `MEI` double DEFAULT NULL,
                `JUN` double DEFAULT NULL,
                `JUL` double DEFAULT NULL,
                `AGU` double DEFAULT NULL,
                `SEP` double DEFAULT NULL,
                `OKT` double DEFAULT NULL,
                `NOV` double DEFAULT NULL,
                `DES` double DEFAULT NULL,
                `SD_2024` text,
                `TOTAL` double DEFAULT NULL,
                `SELISIH` double DEFAULT NULL,
                `JAN_1` text,
                `FEB_1` text,
                `MAR_1` text,
                `APR_1` text,
                `MEI_1` text,
                `JUN_1` text,
                `JUL_1` text,
                `AGU_1` text,
                `SEP_1` text,
                `OKT_1` text,
                `NOV_1` text,
                `DES_1` text,
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
        Schema::dropIfExists('tu_tk_vd_2023');
    }
};

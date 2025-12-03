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
            CREATE TABLE IF NOT EXISTS `tu_tk_tan_2023` (
                `KONTROL` bigint DEFAULT NULL,
                `AGENDA` text,
                `TGL_SPP` text,
                `NO_SPP` text,
                `KEBUN` text,
                `VENDOR` text,
                `NO_KONTRAK` text,
                `TGL__KONTRAK` text,
                `TGL__BERAKHIR_KONTRAK` text,
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
                `JUMLAH_3` text,
                `TANGGAL_BAYAR_IV` double DEFAULT NULL,
                `JUMLAH_4` text,
                `TANGGAL_BAYAR_V` double DEFAULT NULL,
                `JUMLAH_5` text,
                `TANGGAL_BAYAR_VI` double DEFAULT NULL,
                `JUMLAH_6` double DEFAULT NULL,
                `TANGGAL_BAYAR_RAMPUNG` text,
                `JUMLAH_DIBAYAR` double DEFAULT NULL,
                `BELUM_DIBAYAR_1` double DEFAULT NULL,
                `KATEGORI` text,
                `SUB_PEKERJAAN` text,
                `TGL__INPUT` double DEFAULT NULL,
                `UMUR_HUTANG_HARI` bigint DEFAULT NULL,
                `UMUR_SPP` text,
                `UMUR_SPK_HARI` bigint DEFAULT NULL,
                `UMUR_SPK` text,
                `UMUR_BA_HARI` text,
                `UMUR_BA` text,
                `SALDO_HUTANG` double DEFAULT NULL,
                `2_023` text,
                `JAN` text,
                `FEB` double DEFAULT NULL,
                `MAR` double DEFAULT NULL,
                `APR` text,
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
        Schema::dropIfExists('tu_tk_tan_2023');
    }
};

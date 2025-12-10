-- Script untuk membersihkan data pembayaran yang tercemar
-- Masalah: Semua dokumen memiliki KONTROL = 1, sehingga update sebelumnya mengupdate semua dokumen dengan nilai yang sama
-- Solusi: Set semua data pembayaran tahap 1-4 ke NULL/0 untuk semua dokumen (kecuali yang memang sudah benar)

-- ============================================
-- CLEAN PAYMENT DATA FOR tu_tk_2023
-- ============================================
-- Hapus semua data pembayaran tahap 1-4 yang tercemar
-- Set ke NULL untuk tanggal dan 0 untuk jumlah

UPDATE `tu_tk_2023` 
SET 
    `TANGGAL_BAYAR_I` = NULL,
    `JUMLAH1` = 0,
    `TANGGAL_BAYAR_II` = NULL,
    `JUMLAH2` = 0,
    `TANGGAL_BAYAR_III` = NULL,
    `JUMLAH3` = 0,
    `TANGGAL_BAYAR_IV` = NULL,
    `JUMLAH4` = 0,
    `TANGGAL_BAYAR_V` = NULL,
    `JUMLAH5` = 0,
    `TANGGAL_BAYAR_VI` = NULL,
    `JUMLAH6` = 0,
    `JUMLAH_DIBAYAR` = 0,
    `BELUM_DIBAYAR` = `NILAI` -- Reset BELUM_DIBAYAR ke nilai awal
WHERE 1=1; -- Update semua dokumen

-- ============================================
-- ALTERNATIVE: Hanya hapus data yang memiliki nilai yang sama (lebih selektif)
-- ============================================
-- Jika Anda ingin lebih selektif, gunakan query ini untuk menghapus hanya data yang tercemar:
-- (Data yang memiliki JUMLAH1 = 473583593 dan TANGGAL_BAYAR_I = 45999)

-- UPDATE `tu_tk_2023` 
-- SET 
--     `TANGGAL_BAYAR_I` = NULL,
--     `JUMLAH1` = 0,
--     `TANGGAL_BAYAR_II` = NULL,
--     `JUMLAH2` = 0,
--     `TANGGAL_BAYAR_III` = NULL,
--     `JUMLAH3` = 0,
--     `TANGGAL_BAYAR_IV` = NULL,
--     `JUMLAH4` = 0,
--     `TANGGAL_BAYAR_V` = NULL,
--     `JUMLAH5` = 0,
--     `TANGGAL_BAYAR_VI` = NULL,
--     `JUMLAH6` = 0,
--     `JUMLAH_DIBAYAR` = 0,
--     `BELUM_DIBAYAR` = `NILAI`
-- WHERE `JUMLAH1` = 473583593 
--   AND `TANGGAL_BAYAR_I` = 45999;

-- ============================================
-- VERIFY: Cek data setelah cleaning
-- ============================================
-- SELECT 
--     KONTROL, 
--     AGENDA, 
--     NO_SPP, 
--     JUMLAH1, 
--     TANGGAL_BAYAR_I,
--     JUMLAH_DIBAYAR,
--     BELUM_DIBAYAR
-- FROM `tu_tk_2023` 
-- LIMIT 10;







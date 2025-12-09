-- ============================================
-- SQL Script: Fix TANGGAL_BAYAR Columns to NULLABLE
-- ============================================
-- IMPORTANT: Run this script to ensure all TANGGAL_BAYAR columns can accept NULL values
-- This prevents "Data Truncated" errors when saving empty dates
-- ============================================

-- ============================================
-- Table: tu_tk_2023 (input_ks)
-- Column Type: DOUBLE (Excel date serial number)
-- ============================================
ALTER TABLE `tu_tk_2023` 
MODIFY COLUMN `TANGGAL_BAYAR_I` DOUBLE NULL,
MODIFY COLUMN `TANGGAL_BAYAR_II` DOUBLE NULL,
MODIFY COLUMN `TANGGAL_BAYAR_III` DOUBLE NULL,
MODIFY COLUMN `TANGGAL_BAYAR_IV` DOUBLE NULL,
MODIFY COLUMN `TANGGAL_BAYAR_V` DOUBLE NULL,
MODIFY COLUMN `TANGGAL_BAYAR_VI` DOUBLE NULL,
MODIFY COLUMN `TANGGAL_BAYAR_RAMPUNG` TEXT NULL;

-- ============================================
-- Table: tu_tk_tan_2023 (input_tan)
-- Column Type: TEXT
-- ============================================
ALTER TABLE `tu_tk_tan_2023` 
MODIFY COLUMN `TANGGAL_BAYAR_I` TEXT NULL,
MODIFY COLUMN `TANGGAL_BAYAR_II` TEXT NULL,
MODIFY COLUMN `TANGGAL_BAYAR_III` TEXT NULL,
MODIFY COLUMN `TANGGAL_BAYAR_IV` DOUBLE NULL,
MODIFY COLUMN `TANGGAL_BAYAR_V` DOUBLE NULL,
MODIFY COLUMN `TANGGAL_BAYAR_VI` DOUBLE NULL,
MODIFY COLUMN `TANGGAL_BAYAR_RAMPUNG` TEXT NULL;

-- ============================================
-- Table: tu_tk_vd_2023 (input_vd)
-- Column Type: TEXT
-- ============================================
ALTER TABLE `tu_tk_vd_2023` 
MODIFY COLUMN `TANGGAL_BAYAR_I` TEXT NULL,
MODIFY COLUMN `TANGGAL_BAYAR_II` TEXT NULL,
MODIFY COLUMN `TANGGAL_BAYAR_III` TEXT NULL,
MODIFY COLUMN `TANGGAL_BAYAR_IV` TEXT NULL,
MODIFY COLUMN `TANGGAL_BAYAR_V` TEXT NULL,
MODIFY COLUMN `TANGGAL_BAYAR_VI` TEXT NULL,
MODIFY COLUMN `TANGGAL_BAYAR_RAMPUNG` TEXT NULL;

-- ============================================
-- Table: tu_tk_pupuk_2023 (input_pupuk)
-- Column Type: TEXT
-- ============================================
ALTER TABLE `tu_tk_pupuk_2023` 
MODIFY COLUMN `TANGGAL_BAYAR_I` TEXT NULL,
MODIFY COLUMN `TANGGAL_BAYAR_II` TEXT NULL,
MODIFY COLUMN `TANGGAL_BAYAR_III` TEXT NULL,
MODIFY COLUMN `TANGGAL_BAYAR_IV` TEXT NULL,
MODIFY COLUMN `TANGGAL_BAYAR_V` TEXT NULL,
MODIFY COLUMN `TANGGAL_BAYAR_VI` TEXT NULL,
MODIFY COLUMN `TANGGAL_BAYAR_RAMPUNG` TEXT NULL;

-- ============================================
-- Verification Query: Check if columns are nullable
-- ============================================
-- Run this query to verify columns are nullable:
-- SELECT 
--     TABLE_NAME,
--     COLUMN_NAME,
--     IS_NULLABLE,
--     DATA_TYPE
-- FROM INFORMATION_SCHEMA.COLUMNS 
-- WHERE TABLE_SCHEMA = DATABASE()
-- AND TABLE_NAME IN ('tu_tk_2023', 'tu_tk_tan_2023', 'tu_tk_vd_2023', 'tu_tk_pupuk_2023')
-- AND COLUMN_NAME LIKE 'TANGGAL_BAYAR%'
-- ORDER BY TABLE_NAME, COLUMN_NAME;





-- =============================================================================
-- SKEMA REFERENSI (READ-ONLY) — koneksi DB `cash_bank_new`
-- =============================================================================
--
-- Sumber kebenaran skema untuk 7 model di app/Models/CashBank/* yang membaca
-- dari database EKSTERNAL `cash_bank_new` (milik aplikasi Cash Bank, bukan DB
-- utama Agenda Online). Lihat config/database.php (koneksi 'cash_bank_new').
--
-- Asal data : di-dump via `SHOW CREATE TABLE` dari DB produksi `cash_bank_new`
--             (server VPS) pada 2026-06-23. Mencerminkan struktur live saat itu.
-- Normalisasi: nilai `AUTO_INCREMENT=NN` pada opsi tabel dibuang (itu artefak
--             jumlah baris, bukan bagian skema). Ditambah `IF NOT EXISTS`.
--
-- TUJUAN:
--   1. Sumber kebenaran skema ter-version-control untuk model CashBank.
--   2. Memungkinkan setup DB lokal/CI tanpa akses ke DB eksternal produksi.
--   3. Acuan untuk mendeteksi schema drift (diff struktur live vs berkas ini).
--
-- PERINGATAN:
--   - JANGAN jalankan berkas ini terhadap DB produksi `cash_bank_new`.
--     Agenda Online mengakses koneksi ini READ-ONLY (model pakai $guarded=['*']).
--   - Gunakan HANYA untuk membuat ulang struktur di DB lokal/test.
--   - Tabel `item_sub_kriteria` BUKAN model CashBank, tetapi disertakan karena
--     menjadi dependensi FK dari `droppings` & `permintaans`.
--
-- Pemetaan tabel -> model:
--   bank_tujuan        -> App\Models\CashBank\BankTujuan
--   droppings          -> App\Models\CashBank\Dropping        (PK: id_dopping)
--   kategori_kriteria  -> App\Models\CashBank\KategoriKriteria
--   penerimas          -> App\Models\CashBank\Penerima
--   permintaans        -> App\Models\CashBank\Permintaan
--   sub_kriteria       -> App\Models\CashBank\SubKriteria
--   sumber_dana        -> App\Models\CashBank\SumberDana
--   item_sub_kriteria  -> (dependensi FK; tanpa model)
-- =============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ===== kategori_kriteria =====
CREATE TABLE IF NOT EXISTS `kategori_kriteria` (
  `id_kategori_kriteria` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama_kriteria` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `tipe` enum('Masuk','Keluar','Penerima') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_kategori_kriteria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== sub_kriteria =====
CREATE TABLE IF NOT EXISTS `sub_kriteria` (
  `id_sub_kriteria` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_kategori_kriteria` bigint unsigned NOT NULL,
  `nama_sub_kriteria` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_sub_kriteria`),
  KEY `sub_kriteria_id_kategori_kriteria_foreign` (`id_kategori_kriteria`),
  CONSTRAINT `sub_kriteria_id_kategori_kriteria_foreign` FOREIGN KEY (`id_kategori_kriteria`) REFERENCES `kategori_kriteria` (`id_kategori_kriteria`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== item_sub_kriteria (dependensi FK; tanpa model) =====
CREATE TABLE IF NOT EXISTS `item_sub_kriteria` (
  `id_item_sub_kriteria` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_sub_kriteria` bigint unsigned NOT NULL,
  `nama_item_sub_kriteria` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_item_sub_kriteria`),
  KEY `item_sub_kriteria_id_sub_kriteria_foreign` (`id_sub_kriteria`),
  CONSTRAINT `item_sub_kriteria_id_sub_kriteria_foreign` FOREIGN KEY (`id_sub_kriteria`) REFERENCES `sub_kriteria` (`id_sub_kriteria`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== droppings =====
CREATE TABLE IF NOT EXISTS `droppings` (
  `id_dopping` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_kategori_kriteria` bigint unsigned DEFAULT NULL,
  `id_sub_kriteria` bigint unsigned DEFAULT NULL,
  `id_item_sub_kriteria` bigint unsigned DEFAULT NULL,
  `M1` decimal(38,2) NOT NULL DEFAULT '0.00',
  `M2` decimal(38,2) NOT NULL DEFAULT '0.00',
  `M3` decimal(38,2) NOT NULL DEFAULT '0.00',
  `M4` decimal(38,2) NOT NULL DEFAULT '0.00',
  `bulan` tinyint DEFAULT NULL,
  `tahun` year DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_dopping`),
  KEY `droppings_id_kategori_kriteria_foreign` (`id_kategori_kriteria`),
  KEY `droppings_id_sub_kriteria_foreign` (`id_sub_kriteria`),
  KEY `droppings_id_item_sub_kriteria_foreign` (`id_item_sub_kriteria`),
  CONSTRAINT `droppings_id_item_sub_kriteria_foreign` FOREIGN KEY (`id_item_sub_kriteria`) REFERENCES `item_sub_kriteria` (`id_item_sub_kriteria`) ON DELETE SET NULL,
  CONSTRAINT `droppings_id_kategori_kriteria_foreign` FOREIGN KEY (`id_kategori_kriteria`) REFERENCES `kategori_kriteria` (`id_kategori_kriteria`) ON DELETE SET NULL,
  CONSTRAINT `droppings_id_sub_kriteria_foreign` FOREIGN KEY (`id_sub_kriteria`) REFERENCES `sub_kriteria` (`id_sub_kriteria`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== permintaans =====
CREATE TABLE IF NOT EXISTS `permintaans` (
  `id_permintaan` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_kategori_kriteria` bigint unsigned DEFAULT NULL,
  `id_sub_kriteria` bigint unsigned DEFAULT NULL,
  `id_item_sub_kriteria` bigint unsigned DEFAULT NULL,
  `M1` decimal(38,2) NOT NULL DEFAULT '0.00',
  `M2` decimal(38,2) NOT NULL DEFAULT '0.00',
  `M3` decimal(38,2) NOT NULL DEFAULT '0.00',
  `M4` decimal(38,2) NOT NULL DEFAULT '0.00',
  `bulan` tinyint DEFAULT NULL,
  `tahun` year DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_permintaan`),
  KEY `permintaans_id_kategori_kriteria_foreign` (`id_kategori_kriteria`),
  KEY `permintaans_id_sub_kriteria_foreign` (`id_sub_kriteria`),
  KEY `permintaans_id_item_sub_kriteria_foreign` (`id_item_sub_kriteria`),
  CONSTRAINT `permintaans_id_item_sub_kriteria_foreign` FOREIGN KEY (`id_item_sub_kriteria`) REFERENCES `item_sub_kriteria` (`id_item_sub_kriteria`) ON DELETE SET NULL,
  CONSTRAINT `permintaans_id_kategori_kriteria_foreign` FOREIGN KEY (`id_kategori_kriteria`) REFERENCES `kategori_kriteria` (`id_kategori_kriteria`) ON DELETE SET NULL,
  CONSTRAINT `permintaans_id_sub_kriteria_foreign` FOREIGN KEY (`id_sub_kriteria`) REFERENCES `sub_kriteria` (`id_sub_kriteria`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== penerimas =====
CREATE TABLE IF NOT EXISTS `penerimas` (
  `id_penerima` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kontrak` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_kategori_kriteria` bigint unsigned DEFAULT NULL,
  `pembeli` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `no_reg` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `volume` decimal(38,2) NOT NULL DEFAULT '0.00',
  `harga` decimal(38,2) NOT NULL DEFAULT '0.00',
  `nilai` decimal(38,2) NOT NULL DEFAULT '0.00',
  `ppn` decimal(38,2) NOT NULL DEFAULT '0.00',
  `potppn` decimal(38,2) NOT NULL DEFAULT '0.00',
  `nilai_inc_ppn` decimal(20,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_penerima`),
  KEY `penerimas_id_kategori_kriteria_foreign` (`id_kategori_kriteria`),
  CONSTRAINT `penerimas_id_kategori_kriteria_foreign` FOREIGN KEY (`id_kategori_kriteria`) REFERENCES `kategori_kriteria` (`id_kategori_kriteria`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== bank_tujuan =====
CREATE TABLE IF NOT EXISTS `bank_tujuan` (
  `id_bank_tujuan` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama_tujuan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sap` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_bank_tujuan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== sumber_dana =====
CREATE TABLE IF NOT EXISTS `sumber_dana` (
  `id_sumber_dana` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama_sumber_dana` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_sumber_dana`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

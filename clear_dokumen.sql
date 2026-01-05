-- Perintah untuk menghapus semua dokumen dan data terkait
-- HATI-HATI: Perintah ini akan menghapus SEMUA data dokumen!

-- Nonaktifkan foreign key checks sementara
SET FOREIGN_KEY_CHECKS=0;

-- Hapus semua data dari tabel terkait dokumen
TRUNCATE TABLE dibayar_kepadas;
TRUNCATE TABLE dokumen_pos;
TRUNCATE TABLE dokumen_prs;
TRUNCATE TABLE dokumen_role_data;
TRUNCATE TABLE dokumen_statuses;
TRUNCATE TABLE dokumens;

-- Aktifkan kembali foreign key checks
SET FOREIGN_KEY_CHECKS=1;

-- Verifikasi bahwa semua data sudah terhapus
SELECT 'Dokumen tersisa: ' AS info, COUNT(*) AS jumlah FROM dokumens;
SELECT 'Dokumen Role Data tersisa: ' AS info, COUNT(*) AS jumlah FROM dokumen_role_data;


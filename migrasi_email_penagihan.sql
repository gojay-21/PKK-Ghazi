-- =========================================================
-- Migrasi: tambah kolom email penagihan pada tabel penyewa
--
-- Jalankan sekali pada database yang sudah ada:
--   mysql -u root kelolakost < migrasi_email_penagihan.sql
--
-- Kolom ini opsional. Jika kosong, sistem memakai email login
-- penyewa sebagai email penagihan.
-- =========================================================

ALTER TABLE penyewa
    ADD COLUMN IF NOT EXISTS email_penagihan VARCHAR(120) NULL AFTER email;

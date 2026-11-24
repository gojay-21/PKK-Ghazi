-- =========================================================
-- KelolaKost — Skema & data demo
-- Import melalui phpMyAdmin atau:
--   mysql -u root < database.sql
-- =========================================================

CREATE DATABASE IF NOT EXISTS kelolakost
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE kelolakost;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS pembayaran;
DROP TABLE IF EXISTS tagihan;
DROP TABLE IF EXISTS penyewa;
DROP TABLE IF EXISTS kamar;
DROP TABLE IF EXISTS admin;
DROP TABLE IF EXISTS pengaturan;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------
-- Pengaturan kost (selalu satu baris, id = 1)
-- ---------------------------------------------------------
CREATE TABLE pengaturan (
    id              TINYINT UNSIGNED NOT NULL DEFAULT 1,
    nama_kost       VARCHAR(100)     NOT NULL,
    alamat          TEXT             NULL,
    deskripsi       TEXT             NULL,
    kontak_wa       VARCHAR(30)      NULL,
    kontak_email    VARCHAR(120)     NULL,
    jam_operasional VARCHAR(100)     NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO pengaturan (id, nama_kost, alamat, deskripsi, kontak_wa, kontak_email, jam_operasional) VALUES
(1, 'KelolaKost', 'Tambun Selatan',
 'Kost nyaman, bersih, dan aman dengan lokasi strategis. Dilengkapi fasilitas yang menunjang kebutuhan sehari-hari, cocok untuk pelajar, mahasiswa, maupun pekerja.',
 '6281398913187', 'kinggojay@gmail.com', 'Setiap hari, 08.00 - 20.00');

-- ---------------------------------------------------------
-- Akun admin / pengelola
-- ---------------------------------------------------------
CREATE TABLE admin (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nama_lengkap VARCHAR(100) NOT NULL,
    username     VARCHAR(50)  NOT NULL,
    password     VARCHAR(255) NOT NULL,
    created_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_admin_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Password demo (plain text -> hash):
--   admin  / admin123
--   gojay  / admin123
INSERT INTO admin (id, nama_lengkap, username, password, created_at) VALUES
(1, 'Administrator Kost', 'admin', '$2y$10$YEn0HULeFsnp4aE7luIO4.ZsYOAWvjhr6ue6CfrTfYzV6WKv0CyJO', '2026-08-01 09:00:00'),
(2, 'Ghazi', 'gojay', '$2y$10$YEn0HULeFsnp4aE7luIO4.ZsYOAWvjhr6ue6CfrTfYzV6WKv0CyJO', '2026-08-02 09:00:00');

-- ---------------------------------------------------------
-- Kamar
-- ---------------------------------------------------------
CREATE TABLE kamar (
    id            INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    nomor_kamar   VARCHAR(20)    NOT NULL,
    tipe          VARCHAR(50)    NOT NULL,
    harga_bulanan DECIMAL(12,2)  NOT NULL DEFAULT 0,
    fasilitas     TEXT           NULL,
    status        ENUM('kosong','terisi') NOT NULL DEFAULT 'kosong',
    PRIMARY KEY (id),
    UNIQUE KEY uq_kamar_nomor (nomor_kamar)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO kamar (id, nomor_kamar, tipe, harga_bulanan, fasilitas, status) VALUES
(1, 'A1', 'Standar', 750000.00,  'Kasur, Lemari, Kipas Angin', 'terisi'),
(2, 'A2', 'Standar', 750000.00,  'Kasur, Lemari, Kipas Angin', 'terisi'),
(3, 'B1', 'VIP',     1200000.00, 'Kasur, Lemari, AC, Kamar Mandi Dalam', 'kosong'),
(6, 'C1', 'VVIP',    1500000.00, 'Kasur, Lemari, Ac, Kamar Mandi Dalam, Meja dan kursi, Kipas Angin', 'kosong');

-- ---------------------------------------------------------
-- Penyewa (sekaligus akun portal penyewa)
-- ---------------------------------------------------------
CREATE TABLE penyewa (
    id            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    nama          VARCHAR(100)  NOT NULL,
    kamar_id      INT UNSIGNED  NOT NULL,
    no_hp           VARCHAR(30)   NULL,
    email           VARCHAR(120)  NULL,
    email_penagihan VARCHAR(120)  NULL,
    no_ktp          VARCHAR(30)   NULL,
    tanggal_masuk DATE          NOT NULL,
    alamat_asal   TEXT          NULL,
    status        ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
    password      VARCHAR(255)  NULL,
    created_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_penyewa_email (email),
    KEY idx_penyewa_kamar (kamar_id),
    CONSTRAINT fk_penyewa_kamar FOREIGN KEY (kamar_id) REFERENCES kamar (id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Password portal penyewa (email login / password):
--   gojay@demo.test / penyewa123
--   gogo@demo.test  / penyewa123
INSERT INTO penyewa (id, nama, kamar_id, no_hp, email, email_penagihan, no_ktp, tanggal_masuk, alamat_asal, status, password, created_at) VALUES
(6, 'gojay', 2, '081398913187', 'gojay@demo.test', 'gojay.tagihan@demo.test', '123456789', '2026-09-07', 'tambun', 'aktif', '$2y$10$wuuTFwYktLMMPAVPxiES8uOw3y3CkMEp/moozZIWELDTwDn7sZVWy', '2026-09-07 10:00:00'),
(8, 'gogo',  1, '081234567890', 'gogo@demo.test',  NULL, '987654321', '2026-09-14', 'Bekasi', 'aktif', '$2y$10$wuuTFwYktLMMPAVPxiES8uOw3y3CkMEp/moozZIWELDTwDn7sZVWy', '2026-09-14 10:00:00');

-- ---------------------------------------------------------
-- Tagihan
-- ---------------------------------------------------------
CREATE TABLE tagihan (
    id                  INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    penyewa_id          INT UNSIGNED  NOT NULL,
    jumlah_tagihan      DECIMAL(12,2) NOT NULL DEFAULT 0,
    periode_bulan       TINYINT UNSIGNED NOT NULL,
    periode_tahun       SMALLINT UNSIGNED NOT NULL,
    tanggal_jatuh_tempo DATE          NOT NULL,
    status              ENUM('belum_lunas','lunas') NOT NULL DEFAULT 'belum_lunas',
    keterangan          VARCHAR(255)  NULL,
    created_at          TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_tagihan_penyewa (penyewa_id),
    KEY idx_tagihan_periode (periode_tahun, periode_bulan),
    CONSTRAINT fk_tagihan_penyewa FOREIGN KEY (penyewa_id) REFERENCES penyewa (id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO tagihan (id, penyewa_id, jumlah_tagihan, periode_bulan, periode_tahun, tanggal_jatuh_tempo, status, keterangan, created_at) VALUES
(9,  6, 750000.00, 9,  2026, '2026-09-01', 'lunas',        'Tagihan sewa kamar A2 — September 2026', '2026-09-01 08:00:00'),
(14, 6, 750000.00, 10, 2026, '2026-10-01', 'lunas',        'Tagihan sewa kamar A2 — Oktober 2026',   '2026-10-01 08:00:00'),
(16, 8, 750000.00, 10, 2026, '2026-10-01', 'lunas',        'Tagihan sewa kamar A1 — Oktober 2026',   '2026-10-01 08:00:00'),
(17, 6, 750000.00, 11, 2026, '2026-11-01', 'belum_lunas',  'Tagihan sewa kamar A2 — November 2026 (dibuat otomatis)', '2026-11-01 08:00:00'),
(18, 8, 750000.00, 11, 2026, '2026-11-01', 'belum_lunas',  'Tagihan sewa kamar A1 — November 2026 (dibuat otomatis)', '2026-11-01 08:00:00');

-- ---------------------------------------------------------
-- Pembayaran
-- ---------------------------------------------------------
CREATE TABLE pembayaran (
    id             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    tagihan_id     INT UNSIGNED  NOT NULL,
    jumlah_bayar   DECIMAL(12,2) NOT NULL DEFAULT 0,
    tanggal_bayar  DATE          NOT NULL,
    metode_bayar   ENUM('tunai','transfer','qris','lainnya') NOT NULL DEFAULT 'tunai',
    catatan        VARCHAR(255)  NULL,
    kode_referensi VARCHAR(40)   NULL,
    created_at     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_pembayaran_tagihan (tagihan_id),
    KEY idx_pembayaran_tanggal (tanggal_bayar),
    CONSTRAINT fk_pembayaran_tagihan FOREIGN KEY (tagihan_id) REFERENCES tagihan (id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO pembayaran (id, tagihan_id, jumlah_bayar, tanggal_bayar, metode_bayar, catatan, kode_referensi, created_at) VALUES
(5, 9,  750000.00, '2026-09-22', 'qris', 'Pembayaran via QRIS (simulasi/uji coba)', 'QRIS-20260922-A1B2C3', '2026-09-22 10:00:00'),
(6, 14, 750000.00, '2026-10-22', 'qris', 'Pembayaran via QRIS (simulasi/uji coba)', 'QRIS-20261022-1E8FEC', '2026-10-22 10:00:00'),
(7, 16, 750000.00, '2026-10-22', 'qris', 'Pembayaran via QRIS (simulasi/uji coba)', 'QRIS-20261022-7D9A21', '2026-10-22 10:00:00');

-- ---------------------------------------------------------
-- Lanjutkan AUTO_INCREMENT setelah data demo
-- ---------------------------------------------------------
ALTER TABLE admin      AUTO_INCREMENT = 3;
ALTER TABLE kamar      AUTO_INCREMENT = 7;
ALTER TABLE penyewa    AUTO_INCREMENT = 9;
ALTER TABLE tagihan    AUTO_INCREMENT = 19;
ALTER TABLE pembayaran AUTO_INCREMENT = 8;

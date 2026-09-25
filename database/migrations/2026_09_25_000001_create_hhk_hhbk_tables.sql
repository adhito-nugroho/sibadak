-- ============================================================
-- SIBADAK MIGRATION: Modul HHK, HHBK, Penyuluh & Laporan
-- ============================================================

-- 1. Master Penyuluh Kehutanan
CREATE TABLE IF NOT EXISTS `penyuluh_kehutanan` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nip`        VARCHAR(32)  NOT NULL UNIQUE,
    `nama`       VARCHAR(150) NOT NULL,
    `pangkat`    VARCHAR(100) NOT NULL,
    `jabatan`    VARCHAR(255) NOT NULL,
    `is_active`  TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_penyuluh_nama` (`nama`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Master data penyuluh kehutanan CDK Wilayah Bojonegoro';

INSERT INTO `penyuluh_kehutanan` (`nip`, `nama`, `pangkat`, `jabatan`, `is_active`) VALUES
('196703071998031004','PRAWOTO, SP','Penata Tingkat I /III-d','Penyuluh Kehutanan Keahlian Ahli Muda pada Cabang Dinas Kehutanan Wilayah Bojonegoro',1),
('196710292009011003','MOKHAMAD DUKHA, SP, MM','Penata Tingkat I /III-d','Pengadministrasi Kepegawaian pada Cabang Dinas Kehutanan Wilayah Bojonegoro',1),
('196711181998031002','SUWARNO, SP','Penata Tingkat I /III-d','Penyuluh Kehutanan Keahlian Ahli Muda pada Cabang Dinas Kehutanan Wilayah Bojonegoro',1),
('196802171998031006','IMAM WIDJAJANTO','Penata Muda Tingkat I /III-b','Pengolah Data pada Cabang Dinas Kehutanan Wilayah Bojonegoro',1),
('196803061998031007','NALI, SP','Penata /III-c','Penyuluh Kehutanan Keahlian Ahli Muda pada Cabang Dinas Kehutanan Wilayah Bojonegoro',1),
('196806102000032009','NUR`AISYAH, SP','Penata Tingkat I /III-d','Penyuluh Kehutanan Keahlian Ahli Muda pada Cabang Dinas Kehutanan Wilayah Bojonegoro',1),
('196808092000031008','AGUS FENDI BUDI SUSANTO, SP','Penata /III-c','Pengelola Peletarian Sumber Daya Alam pada Cabang Dinas Kehutanan Wilayah Bojonegoro',1),
('196901251998031004','SARPAN, SP','Penata Tingkat I /III-d','Penyuluh Kehutanan Keahlian Ahli Muda pada Cabang Dinas Kehutanan Wilayah Bojonegoro',1),
('196902021998031006','AKHMAD BISRI, SP','Penata /III-c','Penyuluh Kehutanan Keahlian Ahli Muda pada Cabang Dinas Kehutanan Wilayah Bojonegoro',1),
('196905162000031004','DUL ROKIM , SP','Penata Tingkat I /III-d','Penyuluh Kehutanan Keahlian Ahli Muda pada Cabang Dinas Kehutanan Wilayah Bojonegoro',1),
('197001071998031009','SOIMAN, SST','Penata Tingkat I /III-d','Penyuluh Kehutanan Keahlian Ahli Muda pada Cabang Dinas Kehutanan Wilayah Bojonegoro',1),
('197004031998032006','HARTANTI, SP','Penata Tingkat I /III-d','Penyuluh Kehutanan Keahlian Ahli Muda pada Cabang Dinas Kehutanan Wilayah Bojonegoro',1),
('197005101998031015','GATOT BUDI SANTOSO','Penata Tingkat I /III-d','Penyuluh Kehutanan Keterampilan Penyelia pada Cabang Dinas Kehutanan Wilayah Bojonegoro',1),
('197007151998031007','PURWO YULIANTO, SP','Pembina /IV-a','Penyuluh Kehutanan Keahlian Ahli Madya pada Cabang Dinas Kehutanan Wilayah Bojonegoro',1),
('197101121998031010','SOFYAN SUHARDIONO, SP','Penata Tingkat I /III-d','Penyuluh Kehutanan Keahlian Ahli Muda pada Cabang Dinas Kehutanan Wilayah Bojonegoro',1),
('197109161998032004','UMI CHOMSATUR ROCHMAH, SP','Penata /III-c','Penyuluh Kehutanan Keahlian Ahli Muda pada Cabang Dinas Kehutanan Wilayah Bojonegoro',1),
('197203091994031004','DWI DANANG HENDRIYANTO','Penata /III-c','Pengelola Keuangan pada Cabang Dinas Kehutanan Wilayah Bojonegoro',1),
('197203121998031004','MUJIYONO, SP','Penata Tingkat I /III-d','Penyuluh Kehutanan Keahlian Ahli Muda pada Cabang Dinas Kehutanan Wilayah Bojonegoro',1),
('197207231998032004','ETIK NURJANAH, SP','Penata Tingkat I /III-d','Penyuluh Kehutanan Keahlian Ahli Muda pada Cabang Dinas Kehutanan Wilayah Bojonegoro',1),
('197304142000031009','WIDODO JOKO SANTOSO, S.Hut, MM','Pembina/ IV-a','Kepala Cabang Dinas Kehutanan Wilayah Bojonegoro',1),
('197309111998031004','SAMSU NASTAIN, SP','Pembina /IV-a','Penyuluh Kehutanan Keahlian Ahli Madya pada Cabang Dinas Kehutanan Wilayah Bojonegoro',1),
('197406282007011007','JUMAIDI','Penata Muda/III-a','Pengadministrasi Keuangan pada Cabang Dinas Kehutanan Wilayah Bojonegoro',1),
('198108172010012018','SRI AGUS INDARYANI, S.Hut','Penata /III-c','Penyuluh Kehutanan Keahlian Ahli Muda pada Cabang Dinas Kehutanan Wilayah Bojonegoro',1),
('198401132009031002','RIZKY FIRMANSYAH, S.Hut','Penata Tingkat I /III-d','Kepala Seksi Tata Kelola dan Usaha Kehutanan di Cabang Dinas Kehutanan Wilayah Bojonegoro',1),
('198402142010011011','ADHITO NUGROHO, S.Kom','Penata/III-c','Pranata Komputer Keahlian Ahli Muda pada Cabang Dinas Kehutanan Wilayah Bojonegoro',1)
ON DUPLICATE KEY UPDATE `nama`=VALUES(`nama`), `pangkat`=VALUES(`pangkat`), `jabatan`=VALUES(`jabatan`), `is_active`=VALUES(`is_active`);

-- 2. Header HHK
CREATE TABLE IF NOT EXISTS `hhk` (
    `id`                     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `kth_id`                 MEDIUMINT UNSIGNED DEFAULT NULL,
    `nama_kth`               VARCHAR(150) DEFAULT NULL,
    `kabupaten_id`           TINYINT UNSIGNED NOT NULL,
    `kecamatan_id`           SMALLINT UNSIGNED DEFAULT NULL,
    `desa_id`                MEDIUMINT UNSIGNED DEFAULT NULL,
    `penyuluh_id`            INT UNSIGNED DEFAULT NULL,
    `nama_penyuluh`          VARCHAR(150) DEFAULT NULL,
    `bulan`                  TINYINT UNSIGNED NOT NULL,
    `tahun`                  YEAR NOT NULL,
    `total_bulan_ini_m3`     DECIMAL(14,4) NOT NULL DEFAULT 0,
    `total_sd_bulan_lalu_m3` DECIMAL(14,4) NOT NULL DEFAULT 0,
    `total_sd_bulan_ini_m3`  DECIMAL(14,4) NOT NULL DEFAULT 0,
    `keterangan`             TEXT DEFAULT NULL,
    `created_at`             TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`             TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_hhk_kabupaten` FOREIGN KEY (`kabupaten_id`) REFERENCES `kabupaten`(`id`),
    INDEX `idx_hhk_periode` (`tahun`, `bulan`),
    INDEX `idx_hhk_penyuluh` (`penyuluh_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Header produksi HHK per KTH dan periode BA Rekon';

-- 3. Detail HHK
CREATE TABLE IF NOT EXISTS `hhk_detail` (
    `id`                     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `hhk_id`                 INT UNSIGNED NOT NULL,
    `jenis_kayu`             VARCHAR(100) NOT NULL,
    `volume_bulan_ini_m3`    DECIMAL(14,4) NOT NULL DEFAULT 0,
    `volume_sd_bulan_lalu_m3` DECIMAL(14,4) NOT NULL DEFAULT 0,
    `volume_sd_bulan_ini_m3`  DECIMAL(14,4) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_hhkdetail_jenis` (`hhk_id`, `jenis_kayu`),
    CONSTRAINT `fk_hhkdetail_hhk` FOREIGN KEY (`hhk_id`) REFERENCES `hhk`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Detail volume produksi HHK per jenis kayu';

-- 4. Header HHBK
CREATE TABLE IF NOT EXISTS `hhbk` (
    `id`                      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `kth_id`                  MEDIUMINT UNSIGNED DEFAULT NULL,
    `nama_kth`                VARCHAR(150) DEFAULT NULL,
    `kabupaten_id`            TINYINT UNSIGNED NOT NULL,
    `kecamatan_id`            SMALLINT UNSIGNED DEFAULT NULL,
    `desa_id`                 MEDIUMINT UNSIGNED DEFAULT NULL,
    `penyuluh_id`             INT UNSIGNED DEFAULT NULL,
    `nama_penyuluh`           VARCHAR(150) DEFAULT NULL,
    `bulan`                   TINYINT UNSIGNED NOT NULL,
    `tahun`                   YEAR NOT NULL,
    `total_btg_bulan_ini`     DECIMAL(14,4) NOT NULL DEFAULT 0,
    `total_kg_bulan_ini`      DECIMAL(14,4) NOT NULL DEFAULT 0,
    `total_btg_sd_bulan_lalu` DECIMAL(14,4) NOT NULL DEFAULT 0,
    `total_kg_sd_bulan_lalu`  DECIMAL(14,4) NOT NULL DEFAULT 0,
    `total_btg_sd_bulan_ini`  DECIMAL(14,4) NOT NULL DEFAULT 0,
    `total_kg_sd_bulan_ini`   DECIMAL(14,4) NOT NULL DEFAULT 0,
    `keterangan`              TEXT DEFAULT NULL,
    `created_at`              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`              TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_hhbk_kabupaten` FOREIGN KEY (`kabupaten_id`) REFERENCES `kabupaten`(`id`),
    INDEX `idx_hhbk_periode` (`tahun`, `bulan`),
    INDEX `idx_hhbk_penyuluh` (`penyuluh_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Header produksi HHBK per KTH dan periode BA Rekon';

-- 5. Detail HHBK
CREATE TABLE IF NOT EXISTS `hhbk_detail` (
    `id`                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `hhbk_id`              INT UNSIGNED NOT NULL,
    `komoditas`            VARCHAR(100) NOT NULL,
    `satuan`               ENUM('Kg','Batang','Btg') NOT NULL DEFAULT 'Kg',
    `jumlah_bulan_ini`     DECIMAL(14,4) NOT NULL DEFAULT 0,
    `jumlah_sd_bulan_lalu` DECIMAL(14,4) NOT NULL DEFAULT 0,
    `jumlah_sd_bulan_ini`  DECIMAL(14,4) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_hhbkdetail_komoditas` (`hhbk_id`, `komoditas`, `satuan`),
    CONSTRAINT `fk_hhbkdetail_hhbk` FOREIGN KEY (`hhbk_id`) REFERENCES `hhbk`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Detail produksi HHBK per komoditas dan satuan';

-- 6. Master Komoditas HHK
CREATE TABLE IF NOT EXISTS `hhk_komoditas` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nama`       VARCHAR(100) NOT NULL,
    `urutan`     TINYINT UNSIGNED DEFAULT 99 COMMENT 'Urutan tampil di laporan',
    `is_active`  TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `nama` (`nama`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Master jenis kayu HHK untuk kolom laporan';

INSERT INTO `hhk_komoditas` (`nama`, `urutan`, `is_active`) VALUES
('Jati', 1, 1),
('Sengon', 2, 1),
('Mahoni', 3, 1),
('Gmelina', 4, 1),
('Sonokeling', 5, 1),
('Pinus', 6, 1),
('Akasia', 7, 1),
('Mindi', 8, 1),
('Balsa', 9, 1),
('Jabon', 10, 1),
('Jenis Lainnya', 11, 1)
ON DUPLICATE KEY UPDATE `urutan`=VALUES(`urutan`), `is_active`=VALUES(`is_active`);

-- 7. Target DPA HHK
CREATE TABLE IF NOT EXISTS `hhk_target_dpa` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tahun`        YEAR NOT NULL,
    `komoditas_id` INT UNSIGNED NOT NULL,
    `target_m3`    DECIMAL(14,4) DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_target_hhk` (`tahun`, `komoditas_id`),
    KEY `fk_target_hhk_komoditas` (`komoditas_id`),
    CONSTRAINT `fk_target_hhk_komoditas` FOREIGN KEY (`komoditas_id`) REFERENCES `hhk_komoditas`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Target DPA HHK per tahun per komoditas';

-- 8. Master Komoditas HHBK
CREATE TABLE IF NOT EXISTS `hhbk_komoditas` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nama`       VARCHAR(100) NOT NULL,
    `satuan`     ENUM('Kg','Batang','Btg') NOT NULL DEFAULT 'Kg',
    `urutan`     TINYINT UNSIGNED DEFAULT 99,
    `is_active`  TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `nama` (`nama`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Master komoditas HHBK untuk kolom laporan';

INSERT INTO `hhbk_komoditas` (`nama`, `satuan`, `urutan`, `is_active`) VALUES
('Bambu', 'Batang', 1, 1),
('Getah Pinus', 'Kg', 2, 1),
('Daun Kayu Putih', 'Kg', 3, 1),
('Porang', 'Kg', 4, 1),
('Kopi', 'Kg', 5, 1),
('Madu', 'Kg', 6, 1),
('Durian', 'Kg', 7, 1),
('Alpokat', 'Kg', 8, 1),
('Jahe', 'Kg', 9, 1),
('Kunyit', 'Kg', 10, 1),
('HHBK Lainnya', 'Kg', 11, 1)
ON DUPLICATE KEY UPDATE `satuan`=VALUES(`satuan`), `urutan`=VALUES(`urutan`), `is_active`=VALUES(`is_active`);

-- 9. Target DPA HHBK
CREATE TABLE IF NOT EXISTS `hhbk_target_dpa` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tahun`        YEAR NOT NULL,
    `komoditas_id` INT UNSIGNED NOT NULL,
    `target_nilai` DECIMAL(14,4) DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_target_hhbk` (`tahun`, `komoditas_id`),
    KEY `fk_target_hhbk_komoditas` (`komoditas_id`),
    CONSTRAINT `fk_target_hhbk_komoditas` FOREIGN KEY (`komoditas_id`) REFERENCES `hhbk_komoditas`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Target DPA HHBK per tahun per komoditas';

-- Migrasi master penyuluh kehutanan dan produksi HHK/HHBK berbasis BA Rekon April 2026.

CREATE TABLE IF NOT EXISTS penyuluh_kehutanan (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nip        VARCHAR(32)  NOT NULL UNIQUE,
    nama       VARCHAR(150) NOT NULL,
    pangkat    VARCHAR(100) NOT NULL,
    jabatan    VARCHAR(255) NOT NULL,
    is_active  TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_penyuluh_nama (nama)
) COMMENT='Master data penyuluh kehutanan CDK Wilayah Bojonegoro';

INSERT INTO penyuluh_kehutanan (nip, nama, pangkat, jabatan, is_active) VALUES
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
ON DUPLICATE KEY UPDATE nama=VALUES(nama), pangkat=VALUES(pangkat), jabatan=VALUES(jabatan), is_active=VALUES(is_active);

DROP TABLE IF EXISTS hhk_detail;
DROP TABLE IF EXISTS hhk;
DROP TABLE IF EXISTS hhbk_detail;
DROP TABLE IF EXISTS hhbk;

CREATE TABLE hhk (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kth_id          MEDIUMINT UNSIGNED NOT NULL,
    kabupaten_id    TINYINT UNSIGNED NOT NULL,
    kecamatan_id    SMALLINT UNSIGNED NOT NULL,
    desa_id         MEDIUMINT UNSIGNED NOT NULL,
    penyuluh_id     INT UNSIGNED NOT NULL,
    bulan           TINYINT UNSIGNED NOT NULL,
    tahun           YEAR NOT NULL,
    total_bulan_ini_m3 DECIMAL(14,4) NOT NULL DEFAULT 0,
    total_sd_bulan_lalu_m3 DECIMAL(14,4) NOT NULL DEFAULT 0,
    total_sd_bulan_ini_m3 DECIMAL(14,4) NOT NULL DEFAULT 0,
    keterangan      TEXT DEFAULT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_hhk_kth FOREIGN KEY (kth_id) REFERENCES kth(id) ON DELETE CASCADE,
    CONSTRAINT fk_hhk_kabupaten FOREIGN KEY (kabupaten_id) REFERENCES kabupaten(id),
    CONSTRAINT fk_hhk_kecamatan FOREIGN KEY (kecamatan_id) REFERENCES kecamatan(id),
    CONSTRAINT fk_hhk_desa FOREIGN KEY (desa_id) REFERENCES desa(id),
    CONSTRAINT fk_hhk_penyuluh FOREIGN KEY (penyuluh_id) REFERENCES penyuluh_kehutanan(id),
    UNIQUE KEY uq_hhk_kth_periode (kth_id, bulan, tahun),
    INDEX idx_hhk_periode (tahun, bulan),
    INDEX idx_hhk_penyuluh (penyuluh_id)
) COMMENT='Header produksi HHK per KTH dan periode BA Rekon';

CREATE TABLE hhk_detail (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hhk_id       INT UNSIGNED NOT NULL,
    jenis_kayu   VARCHAR(100) NOT NULL,
    volume_bulan_ini_m3 DECIMAL(14,4) NOT NULL DEFAULT 0,
    volume_sd_bulan_lalu_m3 DECIMAL(14,4) NOT NULL DEFAULT 0,
    volume_sd_bulan_ini_m3 DECIMAL(14,4) NOT NULL DEFAULT 0,
    CONSTRAINT fk_hhkdetail_hhk FOREIGN KEY (hhk_id) REFERENCES hhk(id) ON DELETE CASCADE,
    UNIQUE KEY uq_hhkdetail_jenis (hhk_id, jenis_kayu)
) COMMENT='Detail volume produksi HHK per jenis kayu';

CREATE TABLE hhbk (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kth_id          MEDIUMINT UNSIGNED NOT NULL,
    kabupaten_id    TINYINT UNSIGNED NOT NULL,
    kecamatan_id    SMALLINT UNSIGNED NOT NULL,
    desa_id         MEDIUMINT UNSIGNED NOT NULL,
    penyuluh_id     INT UNSIGNED NOT NULL,
    bulan           TINYINT UNSIGNED NOT NULL,
    tahun           YEAR NOT NULL,
    total_btg_bulan_ini DECIMAL(14,4) NOT NULL DEFAULT 0,
    total_kg_bulan_ini DECIMAL(14,4) NOT NULL DEFAULT 0,
    total_btg_sd_bulan_lalu DECIMAL(14,4) NOT NULL DEFAULT 0,
    total_kg_sd_bulan_lalu DECIMAL(14,4) NOT NULL DEFAULT 0,
    total_btg_sd_bulan_ini DECIMAL(14,4) NOT NULL DEFAULT 0,
    total_kg_sd_bulan_ini DECIMAL(14,4) NOT NULL DEFAULT 0,
    keterangan      TEXT DEFAULT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_hhbk_kth FOREIGN KEY (kth_id) REFERENCES kth(id) ON DELETE CASCADE,
    CONSTRAINT fk_hhbk_kabupaten FOREIGN KEY (kabupaten_id) REFERENCES kabupaten(id),
    CONSTRAINT fk_hhbk_kecamatan FOREIGN KEY (kecamatan_id) REFERENCES kecamatan(id),
    CONSTRAINT fk_hhbk_desa FOREIGN KEY (desa_id) REFERENCES desa(id),
    CONSTRAINT fk_hhbk_penyuluh FOREIGN KEY (penyuluh_id) REFERENCES penyuluh_kehutanan(id),
    UNIQUE KEY uq_hhbk_kth_periode (kth_id, bulan, tahun),
    INDEX idx_hhbk_periode (tahun, bulan),
    INDEX idx_hhbk_penyuluh (penyuluh_id)
) COMMENT='Header produksi HHBK per KTH dan periode BA Rekon';

CREATE TABLE hhbk_detail (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hhbk_id       INT UNSIGNED NOT NULL,
    komoditas     VARCHAR(100) NOT NULL,
    satuan        ENUM('Btg','Kg') NOT NULL DEFAULT 'Kg',
    jumlah_bulan_ini DECIMAL(14,4) NOT NULL DEFAULT 0,
    jumlah_sd_bulan_lalu DECIMAL(14,4) NOT NULL DEFAULT 0,
    jumlah_sd_bulan_ini DECIMAL(14,4) NOT NULL DEFAULT 0,
    CONSTRAINT fk_hhbkdetail_hhbk FOREIGN KEY (hhbk_id) REFERENCES hhbk(id) ON DELETE CASCADE,
    UNIQUE KEY uq_hhbkdetail_komoditas (hhbk_id, komoditas, satuan)
) COMMENT='Detail produksi HHBK per komoditas dan satuan';

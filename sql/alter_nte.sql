USE db_kth_cdk_bjn;

-- Tambah kolom nama_kth dan penyuluh untuk mendukung import dari Excel
-- kth_id dibuat nullable agar bisa simpan data meski KTH belum ada di master
ALTER TABLE nte
    MODIFY COLUMN kth_id MEDIUMINT UNSIGNED NULL DEFAULT NULL,
    ADD COLUMN nama_kth VARCHAR(200) DEFAULT NULL COMMENT 'Nama KTH dari sumber Excel' AFTER kth_id,
    ADD COLUMN kabupaten_id TINYINT UNSIGNED DEFAULT NULL AFTER nama_kth,
    ADD COLUMN penyuluh VARCHAR(150) DEFAULT NULL COMMENT 'Nama penyuluh' AFTER nilai_rp;

-- Index tambahan untuk query cepat
CREATE INDEX IF NOT EXISTS idx_nte_nama_kth ON nte (nama_kth);
CREATE INDEX IF NOT EXISTS idx_nte_kabupaten ON nte (kabupaten_id);

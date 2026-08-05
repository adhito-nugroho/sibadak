-- ============================================================
-- MIGRASI DATA WILAYAH RESMI (Kemendagri 2025)
-- Hanya 4 kabupaten: Bojonegoro, Tuban, Lamongan, Gresik
-- ============================================================

USE db_kth_cdk_bjn;

-- 1. Buat tabel sementara untuk import
DROP TABLE IF EXISTS _wilayah_import;
CREATE TABLE _wilayah_import (
    kode VARCHAR(13) NOT NULL PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    INDEX idx_kode (kode)
) ENGINE=InnoDB;

-- 2. Import data dari file wilayah.sql akan dilakukan terpisah
-- Setelah import, jalankan script di bawah ini:

-- 3. Tambah kolom kode_kemendagri di tabel yang ada
ALTER TABLE kabupaten ADD COLUMN IF NOT EXISTS kode_kemendagri VARCHAR(5) DEFAULT NULL;
ALTER TABLE kecamatan ADD COLUMN IF NOT EXISTS kode_kemendagri VARCHAR(8) DEFAULT NULL;
ALTER TABLE desa ADD COLUMN IF NOT EXISTS kode_kemendagri VARCHAR(13) DEFAULT NULL;

-- 4. Update kode kabupaten yang sudah ada
UPDATE kabupaten SET kode_kemendagri = '35.22' WHERE kode = 'BJN';
UPDATE kabupaten SET kode_kemendagri = '35.23' WHERE kode = 'TBN';
UPDATE kabupaten SET kode_kemendagri = '35.24' WHERE kode = 'LMG';
UPDATE kabupaten SET kode_kemendagri = '35.25' WHERE kode = 'GRK';

-- 5. Sync kecamatan: update existing by name match, insert new ones
-- Ini dilakukan via PHP script untuk lebih aman (matching by nama)

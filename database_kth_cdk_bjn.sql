-- ============================================================
--  DATABASE: db_kth_cdk_bjn
--  Sistem Informasi KTH CDK Wilayah Bojonegoro
--  Mencakup: Bojonegoro, Tuban, Lamongan, Gresik
--  Versi: 1.0 | Tahun: 2026
-- ============================================================

CREATE DATABASE IF NOT EXISTS db_kth_cdk_bjn
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE db_kth_cdk_bjn;

-- ============================================================
-- BAGIAN 1: MASTER DATA WILAYAH
-- ============================================================

CREATE TABLE kabupaten (
    id          TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode        VARCHAR(10)  NOT NULL UNIQUE COMMENT 'Kode singkat: BJN, TBN, LMG, GRK',
    nama        VARCHAR(100) NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) COMMENT='Master kabupaten wilayah CDK Bojonegoro';

CREATE TABLE kecamatan (
    id           SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kabupaten_id TINYINT UNSIGNED NOT NULL,
    nama         VARCHAR(100) NOT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_kecamatan_kabupaten FOREIGN KEY (kabupaten_id) REFERENCES kabupaten(id)
) COMMENT='Master kecamatan';

CREATE TABLE desa (
    id           MEDIUMINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kecamatan_id SMALLINT UNSIGNED NOT NULL,
    nama         VARCHAR(100) NOT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_desa_kecamatan FOREIGN KEY (kecamatan_id) REFERENCES kecamatan(id)
) COMMENT='Master desa/kelurahan';

-- ============================================================
-- BAGIAN 2: KELOMPOK TANI HUTAN (KTH)
-- ============================================================

CREATE TABLE kth (
    id                   MEDIUMINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode_register        VARCHAR(60)  NOT NULL UNIQUE COMMENT 'Nomor Register KTH, contoh: 35/22/01/2003/KTH.003/2015',
    nama                 VARCHAR(150) NOT NULL,
    kabupaten_id         TINYINT UNSIGNED NOT NULL,
    kecamatan_id         SMALLINT UNSIGNED NOT NULL,
    desa_id              MEDIUMINT UNSIGNED NOT NULL,
    dusun_blok           VARCHAR(100) DEFAULT NULL,
    kelas                ENUM('Pemula','Madya','Utama') NOT NULL DEFAULT 'Pemula',
    jenis_usaha          VARCHAR(150) DEFAULT NULL COMMENT 'Hutan Rakyat, KBR, Perhutanan Sosial, dll',
    jumlah_anggota       SMALLINT UNSIGNED DEFAULT 0,
    koordinat_ls         DECIMAL(10,7) DEFAULT NULL COMMENT 'Latitude (Lintang Selatan)',
    koordinat_bt         DECIMAL(10,7) DEFAULT NULL COMMENT 'Longitude (Bujur Timur)',
    -- Legalitas
    sk_kepala_desa       VARCHAR(200) DEFAULT NULL,
    sk_kepala_dinas      VARCHAR(200) DEFAULT NULL,
    akta_notaris         VARCHAR(200) DEFAULT NULL,
    sk_kemenkumham       VARCHAR(200) DEFAULT NULL,
    -- File link (Google Drive, dsb)
    link_sk_kth          TEXT DEFAULT NULL,
    link_sk_kades        TEXT DEFAULT NULL,
    link_sertifikat      TEXT DEFAULT NULL,
    -- Status
    is_active            TINYINT(1) NOT NULL DEFAULT 1,
    created_at           TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at           TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_kth_kabupaten  FOREIGN KEY (kabupaten_id)  REFERENCES kabupaten(id),
    CONSTRAINT fk_kth_kecamatan  FOREIGN KEY (kecamatan_id)  REFERENCES kecamatan(id),
    CONSTRAINT fk_kth_desa       FOREIGN KEY (desa_id)        REFERENCES desa(id),
    INDEX idx_kth_kelas          (kelas),
    INDEX idx_kth_kabupaten      (kabupaten_id),
    INDEX idx_kth_kecamatan      (kecamatan_id)
) COMMENT='Master data Kelompok Tani Hutan (KTH)';

-- ============================================================
-- BAGIAN 3: KELEMBAGAAN — PENGURUS & ANGGOTA KTH
-- ============================================================

CREATE TABLE kth_anggota (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kth_id          MEDIUMINT UNSIGNED NOT NULL,
    posisi          ENUM('Kantor KTH','Ketua','Sekretaris','Bendahara','Seksi','Anggota') NOT NULL,
    nama            VARCHAR(150) NOT NULL,
    nik             VARCHAR(20)  DEFAULT NULL UNIQUE,
    no_kk           VARCHAR(20)  DEFAULT NULL,
    alamat          TEXT         DEFAULT NULL,
    pekerjaan       VARCHAR(100) DEFAULT NULL,
    gender          ENUM('L','P') DEFAULT NULL,
    no_telpon       VARCHAR(20)  DEFAULT NULL,
    -- Data usaha komoditi
    luas_garapan_ha DECIMAL(10,4) DEFAULT NULL,
    komoditi_hhbk   VARCHAR(200)  DEFAULT NULL COMMENT 'Komoditi Hasil Hutan Bukan Kayu',
    komoditi_hhk    VARCHAR(200)  DEFAULT NULL COMMENT 'Komoditi Hasil Hutan Kayu',
    htm             VARCHAR(100)  DEFAULT NULL COMMENT 'Hutan Tanaman Masyarakat',
    -- Koordinat pribadi (opsional)
    koordinat_ls    DECIMAL(10,7) DEFAULT NULL,
    koordinat_bt    DECIMAL(10,7) DEFAULT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_anggota_kth FOREIGN KEY (kth_id) REFERENCES kth(id) ON DELETE CASCADE,
    INDEX idx_anggota_posisi (posisi),
    INDEX idx_anggota_nik    (nik)
) COMMENT='Pengurus dan anggota KTH (sheet Kelembagaan)';

-- ============================================================
-- BAGIAN 3B: MASTER PENYULUH KEHUTANAN
-- ============================================================

CREATE TABLE penyuluh_kehutanan (
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

-- ============================================================
-- BAGIAN 4: KELOMPOK PERHUTANAN SOSIAL (KPS)
-- ============================================================

CREATE TABLE kps (
    id                       MEDIUMINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kth_id                   MEDIUMINT UNSIGNED DEFAULT NULL COMMENT 'Opsional: kaitkan KPS ke master KTH',
    kabupaten_id             TINYINT UNSIGNED NOT NULL,
    kecamatan_id             SMALLINT UNSIGNED NOT NULL,
    desa_id                  MEDIUMINT UNSIGNED NOT NULL,
    skema                    ENUM('HKm','HD','HTR','Kulin KK','IPHPS') NOT NULL,
    nama_lembaga             VARCHAR(200) NOT NULL,
    no_sk                    VARCHAR(200) NOT NULL,
    luas_wilayah_ha          DECIMAL(12,4) DEFAULT NULL,
    bukti_sk_link            TEXT         DEFAULT NULL,
    nama_pendamping          VARCHAR(150) DEFAULT NULL,
    jumlah_kk                SMALLINT UNSIGNED DEFAULT 0,
    rkps_link                TEXT         DEFAULT NULL COMMENT 'Path file / tautan RKPS',
    jumlah_kups              TINYINT UNSIGNED DEFAULT 0,
    penandaan_batas_areal    VARCHAR(50)  DEFAULT NULL COMMENT 'Sudah/Proses/Belum',
    penandaan_batas_andil    VARCHAR(50)  DEFAULT NULL,
    created_at               TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at               TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_kps_kth        FOREIGN KEY (kth_id)       REFERENCES kth(id),
    CONSTRAINT fk_kps_kabupaten  FOREIGN KEY (kabupaten_id)  REFERENCES kabupaten(id),
    CONSTRAINT fk_kps_kecamatan  FOREIGN KEY (kecamatan_id)  REFERENCES kecamatan(id),
    CONSTRAINT fk_kps_desa       FOREIGN KEY (desa_id)        REFERENCES desa(id),
    INDEX idx_kps_skema          (skema),
    INDEX idx_kps_kabupaten      (kabupaten_id)
) COMMENT='Data Kelompok Perhutanan Sosial (sheet Data KPS)';

-- ============================================================
-- BAGIAN 5: RENCANA KERJA TAHUNAN (RKT) — KPS
-- ============================================================

CREATE TABLE kps_rkt (
    id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kps_id   MEDIUMINT UNSIGNED NOT NULL,
    tahun    YEAR       NOT NULL,
    status   ENUM('sudah','belum','proses') NOT NULL DEFAULT 'belum',
    catatan  TEXT       DEFAULT NULL,
    dokumen_link TEXT   DEFAULT NULL COMMENT 'Path file dokumen RKT (PDF/JPG/PNG)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_rkt_kps    FOREIGN KEY (kps_id) REFERENCES kps(id) ON DELETE CASCADE,
    UNIQUE KEY uq_rkt_kps_tahun (kps_id, tahun)
) COMMENT='Status RKT per tahun per KPS (sheet RKT, tahun 2023–2030)';

-- ============================================================
-- BAGIAN 5B: ANGGOTA KPS (ANDIL GARAPAN & RUANG KOMUNAL)
-- ============================================================

CREATE TABLE kps_anggota (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kps_id        MEDIUMINT UNSIGNED NOT NULL,
    kategori      ENUM('ruang_perlindungan_komunal','andil_garapan') NOT NULL DEFAULT 'andil_garapan',
    no_andil      VARCHAR(50)  DEFAULT NULL COMMENT 'Nomor andil (contoh: 33.PG.1.K / 33.PG.1)',
    nama_penggarap VARCHAR(150) NOT NULL,
    no_kk         VARCHAR(30)  DEFAULT NULL,
    nik           VARCHAR(30)  DEFAULT NULL,
    -- Koordinat (boleh format derajat-menit-detik seperti di dokumen sumber)
    koordinat_bt  VARCHAR(40)  DEFAULT NULL COMMENT 'Bujur (BT)',
    koordinat_ls  VARCHAR(40)  DEFAULT NULL COMMENT 'Lintang (LS)',
    -- Batas andil garapan (deskripsi batas, biasanya nama orang/objek)
    batas_barat   VARCHAR(150) DEFAULT NULL,
    batas_utara   VARCHAR(150) DEFAULT NULL,
    batas_selatan VARCHAR(150) DEFAULT NULL,
    batas_timur   VARCHAR(150) DEFAULT NULL,
    desa_id       MEDIUMINT UNSIGNED DEFAULT NULL,
    kecamatan_id  SMALLINT UNSIGNED  DEFAULT NULL,
    pengukur      VARCHAR(100) DEFAULT NULL,
    tanggal       DATE         DEFAULT NULL,
    komoditas     VARCHAR(150) DEFAULT NULL,
    luas_ha       DECIMAL(10,4) DEFAULT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_kpsanggota_kps FOREIGN KEY (kps_id) REFERENCES kps(id) ON DELETE CASCADE,
    CONSTRAINT fk_kpsanggota_desa FOREIGN KEY (desa_id) REFERENCES desa(id),
    CONSTRAINT fk_kpsanggota_kecamatan FOREIGN KEY (kecamatan_id) REFERENCES kecamatan(id),
    INDEX idx_kpsanggota_kps (kps_id),
    INDEX idx_kpsanggota_kategori (kategori),
    INDEX idx_kpsanggota_nik (nik),
    INDEX idx_kpsanggota_no_andil (no_andil)
) COMMENT='Daftar anggota KPS (andil garapan & ruang perlindungan/komunal)';

-- ============================================================
-- BAGIAN 6: REHABILITASI HUTAN DAN LAHAN (RHL)
-- ============================================================

CREATE TABLE rhl (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kth_id       MEDIUMINT UNSIGNED DEFAULT NULL COMMENT 'NULL jika pelaksana bukan KTH spesifik',
    nama_kth     VARCHAR(200) DEFAULT NULL COMMENT 'Nama bebas jika tidak ada di master KTH',
    desa_id      MEDIUMINT UNSIGNED DEFAULT NULL,
    kabupaten_id TINYINT UNSIGNED NOT NULL,
    kegiatan     ENUM(
        'Penanaman Hutan Rakyat',
        'Agroforestry Luar Kawasan Hutan',
        'Penghijauan Lingkungan',
        'RHL Mangrove',
        'RHL Mangrove Folu Net Sink',
        'Agroforestry Pada Areal Perhutanan Sosial',
        'Fape',
        'Penanaman Bibit Produktif',
        'Penanaman Pesisir Pantai',
        'Penanaman Eksternal',
        'Rehabilitasi Dalam Kawasan PS'
    ) NOT NULL,
    luas_ha      DECIMAL(10,4) DEFAULT NULL,
    tahun        YEAR NOT NULL,
    koordinat_ls DECIMAL(10,7) DEFAULT NULL,
    koordinat_bt DECIMAL(10,7) DEFAULT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_rhl_kth       FOREIGN KEY (kth_id)       REFERENCES kth(id),
    CONSTRAINT fk_rhl_kabupaten FOREIGN KEY (kabupaten_id) REFERENCES kabupaten(id),
    INDEX idx_rhl_kegiatan (kegiatan),
    INDEX idx_rhl_tahun    (tahun)
) COMMENT='Data kegiatan Rehabilitasi Hutan dan Lahan (sheet RHL)';

CREATE TABLE rhl_bibit (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    rhl_id      INT UNSIGNED NOT NULL,
    jenis_bibit VARCHAR(100) NOT NULL,
    jumlah_btg  INT UNSIGNED DEFAULT 0,
    CONSTRAINT fk_rhlbibit_rhl FOREIGN KEY (rhl_id) REFERENCES rhl(id) ON DELETE CASCADE
) COMMENT='Detail bibit per kegiatan RHL';

-- ============================================================
-- BAGIAN 7: KEBUN BIBIT RAKYAT (KBR)
-- ============================================================

CREATE TABLE kbr (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kth_id       MEDIUMINT UNSIGNED DEFAULT NULL,
    nama_kth     VARCHAR(200) DEFAULT NULL,
    lokasi       TEXT         DEFAULT NULL COMMENT 'Deskripsi lokasi lengkap',
    desa_id      MEDIUMINT UNSIGNED DEFAULT NULL,
    subdas       VARCHAR(100) DEFAULT NULL,
    tahun_tanam  YEAR DEFAULT NULL,
    koordinat_ls DECIMAL(10,7) DEFAULT NULL,
    koordinat_bt DECIMAL(10,7) DEFAULT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_kbr_kth FOREIGN KEY (kth_id) REFERENCES kth(id)
) COMMENT='Data Kebun Bibit Rakyat (sheet KBR)';

CREATE TABLE kbr_tanaman (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kbr_id       INT UNSIGNED NOT NULL,
    jenis        VARCHAR(100) NOT NULL,
    jumlah_btg   INT UNSIGNED DEFAULT 0,
    luas_ha      DECIMAL(10,4) DEFAULT NULL,
    CONSTRAINT fk_kbrtanaman_kbr FOREIGN KEY (kbr_id) REFERENCES kbr(id) ON DELETE CASCADE
) COMMENT='Detail jenis tanaman per KBR';

-- ============================================================
-- BAGIAN 8: ALAT & SARANA EKONOMI PRODUKTIF (AEP)
-- ============================================================

CREATE TABLE aep (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kth_id       MEDIUMINT UNSIGNED DEFAULT NULL,
    nama_kth     VARCHAR(200) DEFAULT NULL,
    desa_id      MEDIUMINT UNSIGNED DEFAULT NULL,
    kabupaten_id TINYINT UNSIGNED NOT NULL,
    jenis_bantuan VARCHAR(150) NOT NULL COMMENT 'Cultivator, Pencacah Rumput, dll',
    jumlah       SMALLINT UNSIGNED DEFAULT 1,
    tahun        YEAR NOT NULL,
    keterangan   TEXT DEFAULT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_aep_kth       FOREIGN KEY (kth_id)       REFERENCES kth(id),
    CONSTRAINT fk_aep_kabupaten FOREIGN KEY (kabupaten_id) REFERENCES kabupaten(id)
) COMMENT='Bantuan Alat Ekonomi Produktif (sheet AEP)';

-- ============================================================
-- BAGIAN 9: DAM PENAHAN (DPN)
-- ============================================================

CREATE TABLE dpn (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sasaran      VARCHAR(200) DEFAULT NULL COMMENT 'Nama kelompok penerima',
    lokasi       TEXT NOT NULL,
    desa_id      MEDIUMINT UNSIGNED DEFAULT NULL,
    jumlah_unit  SMALLINT UNSIGNED DEFAULT 1,
    koordinat_ls DECIMAL(10,7) DEFAULT NULL,
    koordinat_bt DECIMAL(10,7) DEFAULT NULL,
    subdas       VARCHAR(100) DEFAULT NULL,
    panjang_m    DECIMAL(8,2) DEFAULT NULL,
    lebar_m      DECIMAL(8,2) DEFAULT NULL,
    tinggi_m     DECIMAL(8,2) DEFAULT NULL,
    tahun        YEAR NOT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) COMMENT='Data Dam Penahan (sheet DPN)';

-- ============================================================
-- BAGIAN 10: GULLY PLUG
-- ============================================================

CREATE TABLE gully_plug (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sasaran      VARCHAR(200) DEFAULT NULL,
    lokasi       TEXT NOT NULL,
    desa_id      MEDIUMINT UNSIGNED DEFAULT NULL,
    jumlah_unit  SMALLINT UNSIGNED DEFAULT 1,
    koordinat_ls DECIMAL(10,7) DEFAULT NULL,
    koordinat_bt DECIMAL(10,7) DEFAULT NULL,
    subdas       VARCHAR(100) DEFAULT NULL,
    panjang_m    DECIMAL(8,2) DEFAULT NULL,
    lebar_m      DECIMAL(8,2) DEFAULT NULL,
    tinggi_m     DECIMAL(8,2) DEFAULT NULL,
    tahun        YEAR NOT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) COMMENT='Data Gully Plug (sheet Gully Plug)';

-- ============================================================
-- BAGIAN 11: HASIL HUTAN KAYU (HHK)
-- ============================================================

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
    jenis_kayu   VARCHAR(100) NOT NULL COMMENT 'Jati, Sengon, Mahoni, Gmelina, Sonokeling, Pinus, Akasia, Mindi, Balsa, Jabon, Jenis Lainnya',
    volume_bulan_ini_m3 DECIMAL(14,4) NOT NULL DEFAULT 0,
    volume_sd_bulan_lalu_m3 DECIMAL(14,4) NOT NULL DEFAULT 0,
    volume_sd_bulan_ini_m3 DECIMAL(14,4) NOT NULL DEFAULT 0,
    CONSTRAINT fk_hhkdetail_hhk FOREIGN KEY (hhk_id) REFERENCES hhk(id) ON DELETE CASCADE,
    UNIQUE KEY uq_hhkdetail_jenis (hhk_id, jenis_kayu)
) COMMENT='Detail volume produksi HHK per jenis kayu';

-- ============================================================
-- BAGIAN 12: HASIL HUTAN BUKAN KAYU (HHBK)
-- ============================================================

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
    komoditas     VARCHAR(100) NOT NULL COMMENT 'Bambu, Getah Pinus, Daun Kayu Putih, Porang, Kopi, Madu, Durian, Alpokat, Jahe, Kunyit, HHBK Lainnya',
    satuan        ENUM('Btg','Kg') NOT NULL DEFAULT 'Kg',
    jumlah_bulan_ini DECIMAL(14,4) NOT NULL DEFAULT 0,
    jumlah_sd_bulan_lalu DECIMAL(14,4) NOT NULL DEFAULT 0,
    jumlah_sd_bulan_ini DECIMAL(14,4) NOT NULL DEFAULT 0,
    CONSTRAINT fk_hhbkdetail_hhbk FOREIGN KEY (hhbk_id) REFERENCES hhbk(id) ON DELETE CASCADE,
    UNIQUE KEY uq_hhbkdetail_komoditas (hhbk_id, komoditas, satuan)
) COMMENT='Detail produksi HHBK per komoditas dan satuan';

-- ============================================================
-- BAGIAN 13: MPTS (Multi-Purpose Tree Species / Tanaman Serbaguna)
-- ============================================================

CREATE TABLE mpts (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kth_id       MEDIUMINT UNSIGNED NOT NULL,
    -- Komoditi empon-empon & lainnya (satuan Ha kecuali yang dicatat Rumpun/Btg)
    porang_ha    DECIMAL(10,4) DEFAULT NULL,
    porang_rumpun INT UNSIGNED  DEFAULT NULL,
    kunyit_ha    DECIMAL(10,4) DEFAULT NULL,
    jahe_ha      DECIMAL(10,4) DEFAULT NULL,
    kencur_ha    DECIMAL(10,4) DEFAULT NULL,
    laos_ha      DECIMAL(10,4) DEFAULT NULL,
    kapulaga_ha  DECIMAL(10,4) DEFAULT NULL,
    gadung_ha    DECIMAL(10,4) DEFAULT NULL,
    pohon_kenanga_btg INT UNSIGNED DEFAULT NULL,
    kopi_btg     INT UNSIGNED  DEFAULT NULL,
    lainnya      TEXT DEFAULT NULL COMMENT 'JSON: {"nama":"...","satuan":"Ha/Rumpun/Btg","jumlah":...}',
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_mpts_kth FOREIGN KEY (kth_id) REFERENCES kth(id) ON DELETE CASCADE
) COMMENT='Data MPTS / komoditi non-kayu empon-empon (sheet MPTS)';

-- ============================================================
-- BAGIAN 14: NILAI TRANSAKSI EKONOMI (NTE)
-- ============================================================

CREATE TABLE nte (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kth_id       MEDIUMINT UNSIGNED NOT NULL,
    tahun        YEAR NOT NULL,
    bulan        TINYINT UNSIGNED NOT NULL COMMENT '1=Jan ... 12=Des',
    jenis_barang VARCHAR(150) NOT NULL COMMENT 'HHK, HHBK, MPTS, Jasa Lingkungan, dll',
    produk       VARCHAR(150) DEFAULT NULL,
    jumlah       DECIMAL(12,4) DEFAULT NULL,
    satuan       VARCHAR(50)  DEFAULT NULL,
    nilai_rp     BIGINT UNSIGNED DEFAULT 0,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_nte_kth FOREIGN KEY (kth_id) REFERENCES kth(id) ON DELETE CASCADE,
    INDEX idx_nte_tahun_bulan (tahun, bulan),
    INDEX idx_nte_kth (kth_id)
) COMMENT='Nilai Transaksi Ekonomi bulanan per KTH (sheet NTE)';

-- ============================================================
-- BAGIAN 15: JASA LINGKUNGAN
-- ============================================================

CREATE TABLE jasa_lingkungan (
    id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kth_id   MEDIUMINT UNSIGNED NOT NULL,
    -- Pemanfaatan Aliran Air (PLTA/PLTMH)
    kups_aliran_air          VARCHAR(150) DEFAULT NULL,
    mitra_aliran_air         VARCHAR(200) DEFAULT NULL,
    jangka_waktu_aliran_air  VARCHAR(50)  DEFAULT NULL,
    volume_aliran_kwh        DECIMAL(12,4) DEFAULT NULL,
    sarpras_aliran_air       TEXT DEFAULT NULL,
    pendapatan_aliran_air    BIGINT DEFAULT NULL COMMENT 'Rp/Bulan',
    -- Pemanfaatan Air Bersih
    kups_air_bersih          VARCHAR(150) DEFAULT NULL,
    mitra_air_bersih         VARCHAR(200) DEFAULT NULL,
    jangka_waktu_air_bersih  VARCHAR(50)  DEFAULT NULL,
    volume_air_kwh           DECIMAL(12,4) DEFAULT NULL,
    sarpras_air_bersih       TEXT DEFAULT NULL,
    pendapatan_air_bersih    BIGINT DEFAULT NULL,
    -- Wisata Alam
    kups_wisata              VARCHAR(150) DEFAULT NULL,
    mitra_wisata             VARCHAR(200) DEFAULT NULL,
    jangka_waktu_wisata      VARCHAR(50)  DEFAULT NULL,
    sarpras_wisata           TEXT DEFAULT NULL,
    wisatawan_mancanegara    INT UNSIGNED DEFAULT 0,
    wisatawan_nusantara      INT UNSIGNED DEFAULT 0,
    pendapatan_tiket         BIGINT DEFAULT NULL,
    pendapatan_parkir        BIGINT DEFAULT NULL,
    pendapatan_sewa          BIGINT DEFAULT NULL,
    -- Keanekaragaman Hayati
    kups_kehati              VARCHAR(150) DEFAULT NULL,
    mitra_kehati             VARCHAR(200) DEFAULT NULL,
    jangka_waktu_kehati      VARCHAR(50)  DEFAULT NULL,
    sarpras_kehati           TEXT DEFAULT NULL,
    flora_penting            TEXT DEFAULT NULL,
    fauna_penting            TEXT DEFAULT NULL,
    flora_ekonomi            TEXT DEFAULT NULL,
    fauna_ekonomi            TEXT DEFAULT NULL,
    -- Penyerapan Karbon
    kups_karbon              VARCHAR(150) DEFAULT NULL,
    mitra_karbon             VARCHAR(200) DEFAULT NULL,
    jangka_waktu_karbon      VARCHAR(50)  DEFAULT NULL,
    nama_kegiatan_karbon     TEXT DEFAULT NULL,
    luar_areal_karbon        TEXT DEFAULT NULL,
    created_at               TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at               TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_jasling_kth FOREIGN KEY (kth_id) REFERENCES kth(id) ON DELETE CASCADE
) COMMENT='Data Jasa Lingkungan per KTH (sheet Jasa Lingkungan)';

-- ============================================================
-- BAGIAN 16: UPSA (Usaha Produktif Skala Kecil/Anak)
-- ============================================================

CREATE TABLE upsa (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sasaran      VARCHAR(200) DEFAULT NULL,
    lokasi       TEXT DEFAULT NULL,
    desa_id      MEDIUMINT UNSIGNED DEFAULT NULL,
    koordinat_ls DECIMAL(10,7) DEFAULT NULL,
    koordinat_bt DECIMAL(10,7) DEFAULT NULL,
    subdas       VARCHAR(100) DEFAULT NULL,
    tahun_tanam  YEAR DEFAULT NULL,
    jenis_tanaman VARCHAR(150) DEFAULT NULL,
    jumlah       INT UNSIGNED DEFAULT NULL,
    keterangan   TEXT DEFAULT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_upsa_desa FOREIGN KEY (desa_id) REFERENCES desa(id)
) COMMENT='Data UPSA / Usaha Produktif Skala Kecil (sheet UPSA)';

-- ============================================================
-- BAGIAN 17: SISTEM — USERS & ROLES
-- ============================================================

CREATE TABLE roles (
    id    TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama  VARCHAR(50) NOT NULL UNIQUE COMMENT 'admin, operator, viewer'
) COMMENT='Role/hak akses pengguna';

CREATE TABLE users (
    id           MEDIUMINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama         VARCHAR(150) NOT NULL,
    username     VARCHAR(60)  NOT NULL UNIQUE,
    email        VARCHAR(150) NOT NULL UNIQUE,
    password     VARCHAR(255) NOT NULL COMMENT 'bcrypt hash',
    role_id      TINYINT UNSIGNED NOT NULL DEFAULT 3,
    kabupaten_id TINYINT UNSIGNED DEFAULT NULL COMMENT 'NULL = akses semua kabupaten',
    is_active    TINYINT(1) NOT NULL DEFAULT 1,
    last_login   DATETIME DEFAULT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_users_role      FOREIGN KEY (role_id)      REFERENCES roles(id),
    CONSTRAINT fk_users_kabupaten FOREIGN KEY (kabupaten_id) REFERENCES kabupaten(id)
) COMMENT='Tabel pengguna aplikasi';

CREATE TABLE activity_log (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    MEDIUMINT UNSIGNED NOT NULL,
    modul      VARCHAR(100) NOT NULL COMMENT 'kth, kps, rhl, nte, dst',
    aksi       VARCHAR(50)  NOT NULL COMMENT 'create, update, delete, export',
    detail     TEXT         DEFAULT NULL,
    ip_address VARCHAR(45)  DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_log_user FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_log_user  (user_id),
    INDEX idx_log_modul (modul),
    INDEX idx_log_waktu (created_at)
) COMMENT='Log aktivitas pengguna';

-- ============================================================
-- BAGIAN 18: DATA AWAL (SEED)
-- ============================================================

-- Roles
INSERT INTO roles (nama) VALUES ('admin'), ('operator'), ('viewer');

-- Kabupaten
INSERT INTO kabupaten (kode, nama) VALUES
  ('BJN', 'Bojonegoro'),
  ('TBN', 'Tuban'),
  ('LMG', 'Lamongan'),
  ('GRK', 'Gresik');

-- Kecamatan Bojonegoro (id kabupaten = 1)
INSERT INTO kecamatan (kabupaten_id, nama) VALUES
  (1,'Balen'),(1,'Baureno'),(1,'Bojonegoro'),(1,'Bubulan'),(1,'Dander'),
  (1,'Gayam'),(1,'Gondang'),(1,'Kalitidu'),(1,'Kanor'),(1,'Kapas'),
  (1,'Kasiman'),(1,'Kedewan'),(1,'Kedungadem'),(1,'Kepohbaru'),(1,'Malo'),
  (1,'Margomulyo'),(1,'Ngambon'),(1,'Ngasem'),(1,'Ngraho'),(1,'Padangan'),
  (1,'Purwosari'),(1,'Sekar'),(1,'Sugihwaras'),(1,'Sukosewu'),(1,'Sumberrejo'),
  (1,'Tambakrejo'),(1,'Temayang'),(1,'Trucuk');

-- Kecamatan Tuban (id kabupaten = 2)
INSERT INTO kecamatan (kabupaten_id, nama) VALUES
  (2,'Bancar'),(2,'Bangilan'),(2,'Grabagan'),(2,'Jatirogo'),(2,'Jenu'),
  (2,'Kenduruan'),(2,'Kerek'),(2,'Merakurak'),(2,'Montong'),(2,'Palang'),
  (2,'Parengan'),(2,'Plumpang'),(2,'Rengel'),(2,'Semanding'),(2,'Senori'),
  (2,'Singgahan'),(2,'Soko'),(2,'Tambakboyo'),(2,'Tuban'),(2,'Widang');

-- Kecamatan Lamongan (id kabupaten = 3)
INSERT INTO kecamatan (kabupaten_id, nama) VALUES
  (3,'Babat'),(3,'Bluluk'),(3,'Brondong'),(3,'Deket'),(3,'Glagah'),
  (3,'Kalitengah'),(3,'Karangbinangun'),(3,'Karanggeneng'),(3,'Kedungpring'),(3,'Kembangbahu'),
  (3,'Lamongan'),(3,'Laren'),(3,'Maduran'),(3,'Mantup'),(3,'Modo'),
  (3,'Ngimbang'),(3,'Paciran'),(3,'Pucuk'),(3,'Sambeng'),(3,'Sarirejo'),
  (3,'Sekaran'),(3,'Solokuro'),(3,'Sugio'),(3,'Sukodadi'),(3,'Sukorame'),
  (3,'Tikung'),(3,'Turi');

-- Kecamatan Gresik (id kabupaten = 4)
INSERT INTO kecamatan (kabupaten_id, nama) VALUES
  (4,'Panceng'),(4,'Sangkapura');

-- Default admin user (password: Admin@1234 — ganti sebelum production!)
INSERT INTO users (nama, username, email, password, role_id, kabupaten_id) VALUES
  ('Administrator', 'admin', 'admin@cdkbjn.go.id',
   '$2y$12$TH3i1vlZQwVf9PH18hHWTOaKA1sHD4IRGASo6.OxD9gSKnCOcL8Ey', 1, NULL);

-- Penyuluh Kehutanan CDK Wilayah Bojonegoro
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
('198402142010011011','ADHITO NUGROHO, S.Kom','Penata/III-c','Pranata Komputer Keahlian Ahli Muda pada Cabang Dinas Kehutanan Wilayah Bojonegoro',1);

-- ============================================================
-- BAGIAN 19: VIEWS BANTU (untuk laporan cepat)
-- ============================================================

-- Rekap jumlah KTH per kabupaten & kelas
CREATE OR REPLACE VIEW v_rekap_kth_per_kabupaten AS
SELECT
    k.nama        AS kabupaten,
    kth.kelas,
    COUNT(kth.id) AS jumlah_kth,
    SUM(kth.jumlah_anggota) AS total_anggota
FROM kth
JOIN kabupaten k ON kth.kabupaten_id = k.id
GROUP BY k.id, kth.kelas
ORDER BY k.nama, FIELD(kth.kelas,'Utama','Madya','Pemula');

-- Rekap luas RHL per kegiatan per tahun
CREATE OR REPLACE VIEW v_rekap_rhl_per_tahun AS
SELECT
    kegiatan,
    tahun,
    SUM(luas_ha) AS total_luas_ha,
    COUNT(id)    AS jumlah_lokasi
FROM rhl
GROUP BY kegiatan, tahun
ORDER BY tahun, kegiatan;

-- Rekap KPS per kabupaten & skema
CREATE OR REPLACE VIEW v_rekap_kps AS
SELECT
    k.nama        AS kabupaten,
    kps.skema,
    COUNT(kps.id) AS jumlah_kelompok,
    SUM(kps.luas_wilayah_ha) AS total_luas_ha,
    SUM(kps.jumlah_kk)       AS total_kk
FROM kps
JOIN kabupaten k ON kps.kabupaten_id = k.id
GROUP BY k.id, kps.skema
ORDER BY k.nama, kps.skema;

-- Total NTE per KTH per tahun
CREATE OR REPLACE VIEW v_nte_tahunan AS
SELECT
    kth.nama           AS nama_kth,
    kb.nama            AS kabupaten,
    nte.tahun,
    SUM(nte.nilai_rp)  AS total_nilai_rp
FROM nte
JOIN kth ON nte.kth_id = kth.id
JOIN kabupaten kb ON kth.kabupaten_id = kb.id
GROUP BY kth.id, nte.tahun
ORDER BY kb.nama, kth.nama, nte.tahun;

-- ============================================================
-- SELESAI
-- ============================================================

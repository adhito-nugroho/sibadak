USE db_kth_cdk_bjn;

-- KBR 1: Sumber Winong - Kalisumber, Tambakrejo, Bojonegoro
INSERT INTO kbr (nama_kth, lokasi, desa_id, subdas, tahun_tanam) VALUES
('Sumber Winong', 'Desa Kalisumber Kec. Tambakrejo Kab. Bojonegoro', 120, 'Solo', 2025);
SET @kbr1 = LAST_INSERT_ID();

INSERT INTO kbr_tanaman (kbr_id, jenis, jumlah_btg, luas_ha) VALUES
(@kbr1, 'Jati', 5000, 13),
(@kbr1, 'Mahoni', 15500, 39),
(@kbr1, 'Jambu Mente', 7500, 19),
(@kbr1, 'Trembesi', 2000, 5),
(@kbr1, 'Indigofera', 5000, 13);

-- KBR 2: Wonosari - Sambongrejo, Gondang, Bojonegoro
INSERT INTO kbr (nama_kth, lokasi, desa_id, subdas, tahun_tanam) VALUES
('Wonosari', 'Sambongrejo Kec. Gondang Kab. Bojonegoro', 48, 'Solo', 2025);
SET @kbr2 = LAST_INSERT_ID();

INSERT INTO kbr_tanaman (kbr_id, jenis, jumlah_btg, luas_ha) VALUES
(@kbr2, 'Balsa', 25000, 63),
(@kbr2, 'Alpukat', 10000, 25);

-- KBR 3: Lembah Hijau - Pekuwon, Rengel, Tuban
INSERT INTO kbr (nama_kth, lokasi, desa_id, subdas, tahun_tanam) VALUES
('Lembah Hijau', 'Desa Pekuwon Kec. Rengel Kab. Tuban', 174, 'Solo', 2025);
SET @kbr3 = LAST_INSERT_ID();

INSERT INTO kbr_tanaman (kbr_id, jenis, jumlah_btg, luas_ha) VALUES
(@kbr3, 'Gmelina', 15000, 38),
(@kbr3, 'Kelor', 2500, 6),
(@kbr3, 'Nangka', 8000, 20),
(@kbr3, 'Jambu Biji', 2500, 6),
(@kbr3, 'Sirsat', 7000, 18);

-- KBR 4: Wono Lestari - Waleran, Grabagan, Tuban
INSERT INTO kbr (nama_kth, lokasi, desa_id, subdas, tahun_tanam) VALUES
('Wono Lestari', 'Desa Waleran Kec. Grabagan Kab. Tuban', 204, 'Solo', 2025);
SET @kbr4 = LAST_INSERT_ID();

INSERT INTO kbr_tanaman (kbr_id, jenis, jumlah_btg, luas_ha) VALUES
(@kbr4, 'Gmelina', 15000, 38),
(@kbr4, 'Kelor', 2500, 6),
(@kbr4, 'Nangka', 8000, 20),
(@kbr4, 'Jambu Biji', 2500, 6),
(@kbr4, 'Sirsat', 7000, 18);

-- KBR 5: KTH Wono Sekar Makmur - Sekar, Sekar, Bojonegoro
INSERT INTO kbr (nama_kth, lokasi, desa_id, subdas, tahun_tanam) VALUES
('KTH Wono Sekar Makmur', 'Desa Sekar Kec. Sekar Kab. Bojonegoro', 118, 'Solo', 2024);
SET @kbr5 = LAST_INSERT_ID();

INSERT INTO kbr_tanaman (kbr_id, jenis, jumlah_btg, luas_ha) VALUES
(@kbr5, 'Campuran', 35000, 88);

-- KBR 6: KTH Wono Joyo - Miyono, Sekar, Bojonegoro
INSERT INTO kbr (nama_kth, lokasi, desa_id, subdas, tahun_tanam) VALUES
('KTH Wono Joyo', 'Desa Miyono Kec. Sekar Kab. Bojonegoro', 117, 'Solo', 2024);
SET @kbr6 = LAST_INSERT_ID();

INSERT INTO kbr_tanaman (kbr_id, jenis, jumlah_btg, luas_ha) VALUES
(@kbr6, 'Campuran', 35000, 88);

-- KBR 7: KTH Sido Makmur - Lajolor, Singgahan, Tuban
INSERT INTO kbr (nama_kth, lokasi, desa_id, subdas, tahun_tanam) VALUES
('KTH Sido Makmur', 'Desa Lajolor Kec. Singgahan Kab. Tuban', 429, 'Solo', 2023);
SET @kbr7 = LAST_INSERT_ID();

INSERT INTO kbr_tanaman (kbr_id, jenis, jumlah_btg, luas_ha) VALUES
(@kbr7, 'Campuran', 35000, 88);

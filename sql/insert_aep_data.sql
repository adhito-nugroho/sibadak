USE db_kth_cdk_bjn;

-- Data AEP (Alat & Sarana Ekonomi Produktif)
INSERT INTO aep (nama_kth, desa_id, kabupaten_id, jenis_bantuan, jumlah, tahun, keterangan) VALUES
-- 1. LMDH Tani Lestari Kolong - Kolong, Ngasem, Bojonegoro
('LMDH Tani Lestari Kolong', 81, 1, 'Cultivator', 1, 2023, NULL),
-- 2. LMDH Jati Luhur - Geneng, Margomulyo, Bojonegoro
('LMDH Jati Luhur', 36, 1, 'Cultivator', 1, 2023, NULL),
-- 3. KTH Tani Mulyo - Nglampin, Ngambon, Bojonegoro
('KTH Tani Mulyo', 112, 1, 'Pencacah Rumput', 1, 2023, NULL),
-- 4. KTH Gading Makmur - Gading, Tambakrejo, Bojonegoro
('KTH Gading Makmur', 3, 1, 'Pencacah Rumput', 1, 2023, NULL),
-- 5. KTH Wana Sejahtera - Kesongo, Kedungadem, Bojonegoro
('KTH Wana Sejahtera', 9, 1, 'Pencacah Rumput', 1, 2023, NULL),
-- 6. KTH Sukun Makmur - Ngino, Semanding, Tuban
('KTH Sukun Makmur', 144, 2, 'Pencacah Rumput', 1, 2023, NULL),
-- 7. KUPS Sido Makmur - Ngimbang, Palang, Tuban
('KUPS Sido Makmur', 149, 2, 'Pencacah Rumput', 1, 2023, NULL),
-- 8. KTH Sumber Berkah - Bringin, Montong, Tuban
('KTH Sumber Berkah', 188, 2, 'Pencacah Rumput', 1, 2023, NULL),
-- 9. LMDH Pandan Arum - Klino, Sekar, Bojonegoro
('LMDH Pandan Arum', 396, 1, 'Stup Trigona Itama', 10, 2023, NULL),
-- 10. LMDH Ngasem Barokah - Ngasem, Ngasem, Bojonegoro
('LMDH Ngasem Barokah', 77, 1, 'Cultivator', 1, 2024, NULL),
-- 11. Gapoktan Amanah - Mojorejo, Modo, Lamongan
('Gapoktan Amanah', 315, 3, 'Cultivator', 2, 2024, NULL),
-- 12. KTH Jono Puro - Jono, Temayang, Bojonegoro
('KTH Jono Puro', 93, 1, 'Kompos Rotari/APPO', 1, 2024, NULL),
-- 13. KTH Wono Sekar Makmur - Sekar, Sekar, Bojonegoro (2 bantuan)
('KTH Wono Sekar Makmur', 118, 1, 'Mesin Pipil Jagung Mobile', 1, 2025, NULL),
('KTH Wono Sekar Makmur', 118, 1, 'Mesin Pencacah Rumput/Chopper', 1, 2025, NULL),
-- 14. Gapoktanhut Lereng Kendeng Satu - Bobol, Sekar, Bojonegoro
('Gapoktanhut Lereng Kendeng Satu', 116, 1, 'Mesin Pipil Jagung Mobile', 1, 2025, NULL),
-- 15. LMDH Wono Lestari - Waleran, Grabagan, Tuban (2 bantuan)
('LMDH Wono Lestari', 204, 2, 'Mesin Pencacah Rumput/Chopper', 1, 2025, NULL),
('LMDH Wono Lestari', 204, 2, 'Mesin Pipil Jagung', 1, 2025, NULL),
-- 16. KTH Sukun Makmur - Ngino, Semanding, Tuban
('KTH Sukun Makmur', 144, 2, 'Pemotong Rumput', 2, 2025, NULL),
-- 17. KTH Wono Lestari - Tegalrejo, Merakurak, Tuban (2 bantuan)
('KTH Wono Lestari', 141, 2, 'Mesin Pipil Jagung Mobile', 1, 2025, NULL),
('KTH Wono Lestari', 141, 2, 'Mesin Pencacah Rumput/Chopper', 1, 2025, NULL),
-- 18. KTH Sumber Lestari - Prupuh, Panceng, Gresik
('KTH Sumber Lestari', 334, 4, 'Mesin Pipil Jagung Mobile', 1, 2025, NULL),
-- 19. Pokmaswas Randuboto - Randuboto, Sidayu, Gresik
('Pokmaswas Randuboto', 341, 4, 'Hammer Mill', 1, 2025, NULL),
-- 20. LMDH Selo Joyo - Siwalan, Panceng, Gresik (2 bantuan)
('LMDH Selo Joyo', 335, 4, 'Mesin Ekstraktor Madu', 1, 2025, NULL),
('LMDH Selo Joyo', 335, 4, 'Stup Lebah Apis Mellifera', 10, 2025, NULL);

-- Jalankan jika database sudah di-import dengan hash admin lama (login gagal).
-- Password setelah update: Admin@1234
USE db_kth_cdk_bjn;

UPDATE users
SET password = '$2y$12$TH3i1vlZQwVf9PH18hHWTOaKA1sHD4IRGASo6.OxD9gSKnCOcL8Ey'
WHERE username = 'admin';

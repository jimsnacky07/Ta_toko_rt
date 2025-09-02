-- Hapus data user lama
DELETE FROM users;
ALTER TABLE users AUTO_INCREMENT = 1;

-- Insert data users baru
INSERT INTO users (name, nama, email, password, level, no_telp, alamat, email_verified_at, created_at, updated_at) VALUES
-- Admin
('Administrator', 'Administrator', 'admin@tokort.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '081234567890', 'Jl. Admin No. 1, Jakarta', NOW(), NOW(), NOW()),

-- Tailor
('Master Tailor', 'Master Tailor', 'tailor@tokort.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tailor', '081234567891', 'Jl. Tailor No. 1, Jakarta', NOW(), NOW(), NOW()),

-- 9 User biasa
('Siti Nurhaliza', 'Siti Nurhaliza', 'siti@user.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', '081234567892', 'Jl. Melati No. 12, Bandung', NOW(), NOW(), NOW()),
('Budi Santoso', 'Budi Santoso', 'budi@user.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', '081234567893', 'Jl. Mawar No. 15, Surabaya', NOW(), NOW(), NOW()),
('Rina Kartika', 'Rina Kartika', 'rina@user.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', '081234567894', 'Jl. Anggrek No. 8, Yogyakarta', NOW(), NOW(), NOW()),
('Ahmad Fauzi', 'Ahmad Fauzi', 'ahmad@user.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', '081234567895', 'Jl. Kenanga No. 22, Medan', NOW(), NOW(), NOW()),
('Dewi Sartika', 'Dewi Sartika', 'dewi@user.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', '081234567896', 'Jl. Cempaka No. 7, Semarang', NOW(), NOW(), NOW()),
('Rudi Hermawan', 'Rudi Hermawan', 'rudi@user.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', '081234567897', 'Jl. Dahlia No. 19, Malang', NOW(), NOW(), NOW()),
('Maya Sari', 'Maya Sari', 'maya@user.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', '081234567898', 'Jl. Tulip No. 3, Denpasar', NOW(), NOW(), NOW()),
('Indra Gunawan', 'Indra Gunawan', 'indra@user.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', '081234567899', 'Jl. Sakura No. 11, Makassar', NOW(), NOW(), NOW()),
('Lestari Wulandari', 'Lestari Wulandari', 'lestari@user.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', '081234567800', 'Jl. Bougenville No. 25, Palembang', NOW(), NOW(), NOW());

-- Tampilkan hasil
SELECT 'Data berhasil diinsert!' as status;
SELECT id, name, email, level FROM users ORDER BY id;

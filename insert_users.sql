-- Insert users directly via SQL
USE toko_rt;

-- Clear existing users
TRUNCATE TABLE users;

-- Insert all 21 users
INSERT INTO users (name, nama, email, password, level, no_telp, alamat, email_verified_at, created_at, updated_at) VALUES
('Administrator', 'Administrator', 'admin@tokort.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '081234567890', 'Jl. Admin No. 1, Jakarta', NOW(), NOW(), NOW()),
('Master Tailor', 'Master Tailor', 'tailor@tokort.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tailor', '081234567891', 'Jl. Tailor No. 1, Jakarta', NOW(), NOW(), NOW()),
('Test User', 'Test User', 'user@user.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', '081234567999', 'Jl. Test No. 1, Jakarta', NOW(), NOW(), NOW()),
('Siti Nurhaliza', 'Siti Nurhaliza', 'siti@customer.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', '081234567892', 'Jl. Melati No. 12, Bandung', NOW(), NOW(), NOW()),
('Budi Santoso', 'Budi Santoso', 'budi@customer.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', '081234567893', 'Jl. Mawar No. 15, Surabaya', NOW(), NOW(), NOW()),
('Rina Kartika', 'Rina Kartika', 'rina@customer.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', '081234567894', 'Jl. Anggrek No. 8, Yogyakarta', NOW(), NOW(), NOW()),
('Ahmad Fauzi', 'Ahmad Fauzi', 'ahmad@customer.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', '081234567895', 'Jl. Kenanga No. 22, Medan', NOW(), NOW(), NOW()),
('Dewi Sartika', 'Dewi Sartika', 'dewi@customer.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', '081234567896', 'Jl. Cempaka No. 7, Semarang', NOW(), NOW(), NOW()),
('Rudi Hermawan', 'Rudi Hermawan', 'rudi@customer.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', '081234567897', 'Jl. Dahlia No. 19, Malang', NOW(), NOW(), NOW()),
('Maya Sari', 'Maya Sari', 'maya@customer.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', '081234567898', 'Jl. Tulip No. 3, Denpasar', NOW(), NOW(), NOW()),
('Indra Gunawan', 'Indra Gunawan', 'indra@customer.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', '081234567899', 'Jl. Sakura No. 11, Makassar', NOW(), NOW(), NOW()),
('Lestari Wulandari', 'Lestari Wulandari', 'lestari@customer.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', '081234567800', 'Jl. Bougenville No. 25, Palembang', NOW(), NOW(), NOW()),
('Fajar Pratama', 'Fajar Pratama', 'fajar@customer.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', '081234567801', 'Jl. Kamboja No. 14, Balikpapan', NOW(), NOW(), NOW()),
('Sari Indah', 'Sari Indah', 'sari@customer.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', '081234567802', 'Jl. Flamboyan No. 9, Pontianak', NOW(), NOW(), NOW()),
('Hendra Wijaya', 'Hendra Wijaya', 'hendra@customer.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', '081234567803', 'Jl. Teratai No. 21, Pekanbaru', NOW(), NOW(), NOW()),
('Nurul Aini', 'Nurul Aini', 'nurul@customer.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', '081234567804', 'Jl. Seroja No. 16, Banjarmasin', NOW(), NOW(), NOW()),
('Agus Setiawan', 'Agus Setiawan', 'agus@customer.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', '081234567805', 'Jl. Alamanda No. 5, Samarinda', NOW(), NOW(), NOW()),
('Fitri Handayani', 'Fitri Handayani', 'fitri@customer.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', '081234567806', 'Jl. Gardenia No. 18, Manado', NOW(), NOW(), NOW()),
('Doni Kurniawan', 'Doni Kurniawan', 'doni@customer.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', '081234567807', 'Jl. Lavender No. 13, Ambon', NOW(), NOW(), NOW()),
('Wati Suharto', 'Wati Suharto', 'wati@customer.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', '081234567808', 'Jl. Jasmine No. 26, Jayapura', NOW(), NOW(), NOW()),
('Rizki Ramadhan', 'Rizki Ramadhan', 'rizki@customer.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', '081234567809', 'Jl. Magnolia No. 4, Kupang', NOW(), NOW(), NOW());

-- Show results
SELECT COUNT(*) as total_users FROM users;
SELECT level, COUNT(*) as count FROM users GROUP BY level;
SELECT name, email, level FROM users ORDER BY level, name;

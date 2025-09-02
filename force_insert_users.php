<?php

// Force insert users directly with raw MySQL
$host = '127.0.0.1';
$dbname = 'toko_rt';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🔌 Connected to database successfully!\n";
    
    // Clear existing users
    $pdo->exec("TRUNCATE TABLE users");
    echo "🗑️ Cleared existing users\n";
    
    // Insert users with raw SQL
    $password_hash = password_hash('zxcvbnm123', PASSWORD_DEFAULT);
    $now = date('Y-m-d H:i:s');
    
    $users = [
        // Admin
        ['Administrator', 'Administrator', 'admin@tokort.com', $password_hash, 'admin', '081234567890', 'Jl. Admin No. 1, Jakarta'],
        // Tailor  
        ['Master Tailor', 'Master Tailor', 'tailor@tokort.com', $password_hash, 'tailor', '081234567891', 'Jl. Tailor No. 1, Jakarta'],
        // Test User
        ['Test User', 'Test User', 'user@user.com', $password_hash, 'customer', '081234567999', 'Jl. Test No. 1, Jakarta'],
        // 18 Customers
        ['Siti Nurhaliza', 'Siti Nurhaliza', 'siti@customer.com', $password_hash, 'customer', '081234567892', 'Jl. Melati No. 12, Bandung'],
        ['Budi Santoso', 'Budi Santoso', 'budi@customer.com', $password_hash, 'customer', '081234567893', 'Jl. Mawar No. 15, Surabaya'],
        ['Rina Kartika', 'Rina Kartika', 'rina@customer.com', $password_hash, 'customer', '081234567894', 'Jl. Anggrek No. 8, Yogyakarta'],
        ['Ahmad Fauzi', 'Ahmad Fauzi', 'ahmad@customer.com', $password_hash, 'customer', '081234567895', 'Jl. Kenanga No. 22, Medan'],
        ['Dewi Sartika', 'Dewi Sartika', 'dewi@customer.com', $password_hash, 'customer', '081234567896', 'Jl. Cempaka No. 7, Semarang'],
        ['Rudi Hermawan', 'Rudi Hermawan', 'rudi@customer.com', $password_hash, 'customer', '081234567897', 'Jl. Dahlia No. 19, Malang'],
        ['Maya Sari', 'Maya Sari', 'maya@customer.com', $password_hash, 'customer', '081234567898', 'Jl. Tulip No. 3, Denpasar'],
        ['Indra Gunawan', 'Indra Gunawan', 'indra@customer.com', $password_hash, 'customer', '081234567899', 'Jl. Sakura No. 11, Makassar'],
        ['Lestari Wulandari', 'Lestari Wulandari', 'lestari@customer.com', $password_hash, 'customer', '081234567800', 'Jl. Bougenville No. 25, Palembang'],
        ['Fajar Pratama', 'Fajar Pratama', 'fajar@customer.com', $password_hash, 'customer', '081234567801', 'Jl. Kamboja No. 14, Balikpapan'],
        ['Sari Indah', 'Sari Indah', 'sari@customer.com', $password_hash, 'customer', '081234567802', 'Jl. Flamboyan No. 9, Pontianak'],
        ['Hendra Wijaya', 'Hendra Wijaya', 'hendra@customer.com', $password_hash, 'customer', '081234567803', 'Jl. Teratai No. 21, Pekanbaru'],
        ['Nurul Aini', 'Nurul Aini', 'nurul@customer.com', $password_hash, 'customer', '081234567804', 'Jl. Seroja No. 16, Banjarmasin'],
        ['Agus Setiawan', 'Agus Setiawan', 'agus@customer.com', $password_hash, 'customer', '081234567805', 'Jl. Alamanda No. 5, Samarinda'],
        ['Fitri Handayani', 'Fitri Handayani', 'fitri@customer.com', $password_hash, 'customer', '081234567806', 'Jl. Gardenia No. 18, Manado'],
        ['Doni Kurniawan', 'Doni Kurniawan', 'doni@customer.com', $password_hash, 'customer', '081234567807', 'Jl. Lavender No. 13, Ambon'],
        ['Wati Suharto', 'Wati Suharto', 'wati@customer.com', $password_hash, 'customer', '081234567808', 'Jl. Jasmine No. 26, Jayapura'],
        ['Rizki Ramadhan', 'Rizki Ramadhan', 'rizki@customer.com', $password_hash, 'customer', '081234567809', 'Jl. Magnolia No. 4, Kupang']
    ];
    
    $sql = "INSERT INTO users (name, nama, email, password, level, no_telp, alamat, email_verified_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    
    $inserted = 0;
    foreach ($users as $user) {
        $result = $stmt->execute([
            $user[0], // name
            $user[1], // nama
            $user[2], // email
            $user[3], // password
            $user[4], // level
            $user[5], // no_telp
            $user[6], // alamat
            $now,     // email_verified_at
            $now,     // created_at
            $now      // updated_at
        ]);
        if ($result) {
            $inserted++;
            echo "✅ Inserted: {$user[0]} ({$user[2]}) - {$user[4]}\n";
        }
    }
    
    $count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    echo "\n🎉 SUCCESS! Inserted $inserted users. Total users in database: $count\n";
    
    // Show breakdown
    $admin = $pdo->query("SELECT COUNT(*) FROM users WHERE level = 'admin'")->fetchColumn();
    $tailor = $pdo->query("SELECT COUNT(*) FROM users WHERE level = 'tailor'")->fetchColumn();
    $customer = $pdo->query("SELECT COUNT(*) FROM users WHERE level = 'customer'")->fetchColumn();
    
    echo "\n📊 User Breakdown:\n";
    echo "- 👑 Admin: $admin\n";
    echo "- ✂️ Tailor: $tailor\n";
    echo "- 👤 Customer: $customer\n";
    echo "- 📧 All passwords: zxcvbnm123\n";
    
    echo "\n🔐 Login Accounts:\n";
    echo "- Admin: admin@tokort.com / zxcvbnm123\n";
    echo "- Tailor: tailor@tokort.com / zxcvbnm123\n";
    echo "- Customer: user@user.com / zxcvbnm123\n";
    
    echo "\n🔄 REFRESH phpMyAdmin sekarang untuk melihat semua user!\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ General Error: " . $e->getMessage() . "\n";
}

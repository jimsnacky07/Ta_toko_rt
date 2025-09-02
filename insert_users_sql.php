<?php

try {
    // Koneksi database
    $host = 'localhost';
    $dbname = 'toko_rt';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Hapus data lama
    $pdo->exec("DELETE FROM users");
    $pdo->exec("ALTER TABLE users AUTO_INCREMENT = 1");
    
    // Password hash untuk 'zxcvbnm123'
    $hashedPassword = password_hash('zxcvbnm123', PASSWORD_DEFAULT);
    $now = date('Y-m-d H:i:s');
    
    // Data users
    $users = [
        // Admin
        ['Administrator', 'Administrator', 'admin@tokort.com', 'admin', '081234567890', 'Jl. Admin No. 1, Jakarta'],
        // Tailor
        ['Master Tailor', 'Master Tailor', 'tailor@tokort.com', 'tailor', '081234567891', 'Jl. Tailor No. 1, Jakarta'],
        // Regular users
        ['Siti Nurhaliza', 'Siti Nurhaliza', 'siti@user.com', 'user', '081234567892', 'Jl. Melati No. 12, Bandung'],
        ['Budi Santoso', 'Budi Santoso', 'budi@user.com', 'user', '081234567893', 'Jl. Mawar No. 15, Surabaya'],
        ['Rina Kartika', 'Rina Kartika', 'rina@user.com', 'user', '081234567894', 'Jl. Anggrek No. 8, Yogyakarta'],
        ['Ahmad Fauzi', 'Ahmad Fauzi', 'ahmad@user.com', 'user', '081234567895', 'Jl. Kenanga No. 22, Medan'],
        ['Dewi Sartika', 'Dewi Sartika', 'dewi@user.com', 'user', '081234567896', 'Jl. Cempaka No. 7, Semarang'],
        ['Rudi Hermawan', 'Rudi Hermawan', 'rudi@user.com', 'user', '081234567897', 'Jl. Dahlia No. 19, Malang'],
        ['Maya Sari', 'Maya Sari', 'maya@user.com', 'user', '081234567898', 'Jl. Tulip No. 3, Denpasar'],
        ['Indra Gunawan', 'Indra Gunawan', 'indra@user.com', 'user', '081234567899', 'Jl. Sakura No. 11, Makassar'],
        ['Lestari Wulandari', 'Lestari Wulandari', 'lestari@user.com', 'user', '081234567800', 'Jl. Bougenville No. 25, Palembang']
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO users (name, nama, email, password, level, no_telp, alamat, email_verified_at, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    foreach ($users as $user) {
        $stmt->execute([
            $user[0], // name
            $user[1], // nama
            $user[2], // email
            $hashedPassword, // password
            $user[3], // level
            $user[4], // no_telp
            $user[5], // alamat
            $now, // email_verified_at
            $now, // created_at
            $now  // updated_at
        ]);
    }
    
    echo "✅ Berhasil menambahkan " . count($users) . " pengguna ke database!\n\n";
    
    // Tampilkan hasil
    $result = $pdo->query("SELECT id, name, email, level FROM users ORDER BY id");
    echo "📋 Daftar pengguna yang berhasil ditambahkan:\n";
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "   {$row['id']}. {$row['name']} ({$row['email']}) - Level: {$row['level']}\n";
    }
    
    echo "\n🔑 Password untuk semua user: zxcvbnm123\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

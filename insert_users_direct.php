<?php

require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

// Setup database connection
$capsule = new Capsule;
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => '127.0.0.1',
    'database' => 'toko_rt',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

try {
    // Check current user count
    $currentCount = $capsule->table('users')->count();
    echo "Current users in database: $currentCount\n";
    
    $password = password_hash('zxcvbnm123', PASSWORD_DEFAULT);
    $now = date('Y-m-d H:i:s');
    
    // Insert users directly
    $users = [
        // Admin
        [
            'name' => 'Administrator',
            'nama' => 'Administrator',
            'email' => 'admin@tokort.com',
            'password' => $password,
            'level' => 'admin',
            'no_telp' => '081234567890',
            'alamat' => 'Jl. Admin No. 1, Jakarta',
            'email_verified_at' => $now,
            'created_at' => $now,
            'updated_at' => $now
        ],
        // Tailor
        [
            'name' => 'Master Tailor',
            'nama' => 'Master Tailor',
            'email' => 'tailor@tokort.com',
            'password' => $password,
            'level' => 'tailor',
            'no_telp' => '081234567891',
            'alamat' => 'Jl. Tailor No. 1, Jakarta',
            'email_verified_at' => $now,
            'created_at' => $now,
            'updated_at' => $now
        ],
        // Test User
        [
            'name' => 'Test User',
            'nama' => 'Test User',
            'email' => 'user@user.com',
            'password' => $password,
            'level' => 'customer',
            'no_telp' => '081234567999',
            'alamat' => 'Jl. Test No. 1, Jakarta',
            'email_verified_at' => $now,
            'created_at' => $now,
            'updated_at' => $now
        ]
    ];
    
    // 18 Customers
    $customers = [
        ['name' => 'Siti Nurhaliza', 'email' => 'siti@customer.com', 'no_telp' => '081234567892', 'alamat' => 'Jl. Melati No. 12, Bandung'],
        ['name' => 'Budi Santoso', 'email' => 'budi@customer.com', 'no_telp' => '081234567893', 'alamat' => 'Jl. Mawar No. 15, Surabaya'],
        ['name' => 'Rina Kartika', 'email' => 'rina@customer.com', 'no_telp' => '081234567894', 'alamat' => 'Jl. Anggrek No. 8, Yogyakarta'],
        ['name' => 'Ahmad Fauzi', 'email' => 'ahmad@customer.com', 'no_telp' => '081234567895', 'alamat' => 'Jl. Kenanga No. 22, Medan'],
        ['name' => 'Dewi Sartika', 'email' => 'dewi@customer.com', 'no_telp' => '081234567896', 'alamat' => 'Jl. Cempaka No. 7, Semarang'],
        ['name' => 'Rudi Hermawan', 'email' => 'rudi@customer.com', 'no_telp' => '081234567897', 'alamat' => 'Jl. Dahlia No. 19, Malang'],
        ['name' => 'Maya Sari', 'email' => 'maya@customer.com', 'no_telp' => '081234567898', 'alamat' => 'Jl. Tulip No. 3, Denpasar'],
        ['name' => 'Indra Gunawan', 'email' => 'indra@customer.com', 'no_telp' => '081234567899', 'alamat' => 'Jl. Sakura No. 11, Makassar'],
        ['name' => 'Lestari Wulandari', 'email' => 'lestari@customer.com', 'no_telp' => '081234567800', 'alamat' => 'Jl. Bougenville No. 25, Palembang'],
        ['name' => 'Fajar Pratama', 'email' => 'fajar@customer.com', 'no_telp' => '081234567801', 'alamat' => 'Jl. Kamboja No. 14, Balikpapan'],
        ['name' => 'Sari Indah', 'email' => 'sari@customer.com', 'no_telp' => '081234567802', 'alamat' => 'Jl. Flamboyan No. 9, Pontianak'],
        ['name' => 'Hendra Wijaya', 'email' => 'hendra@customer.com', 'no_telp' => '081234567803', 'alamat' => 'Jl. Teratai No. 21, Pekanbaru'],
        ['name' => 'Nurul Aini', 'email' => 'nurul@customer.com', 'no_telp' => '081234567804', 'alamat' => 'Jl. Seroja No. 16, Banjarmasin'],
        ['name' => 'Agus Setiawan', 'email' => 'agus@customer.com', 'no_telp' => '081234567805', 'alamat' => 'Jl. Alamanda No. 5, Samarinda'],
        ['name' => 'Fitri Handayani', 'email' => 'fitri@customer.com', 'no_telp' => '081234567806', 'alamat' => 'Jl. Gardenia No. 18, Manado'],
        ['name' => 'Doni Kurniawan', 'email' => 'doni@customer.com', 'no_telp' => '081234567807', 'alamat' => 'Jl. Lavender No. 13, Ambon'],
        ['name' => 'Wati Suharto', 'email' => 'wati@customer.com', 'no_telp' => '081234567808', 'alamat' => 'Jl. Jasmine No. 26, Jayapura'],
        ['name' => 'Rizki Ramadhan', 'email' => 'rizki@customer.com', 'no_telp' => '081234567809', 'alamat' => 'Jl. Magnolia No. 4, Kupang']
    ];
    
    foreach ($customers as $customer) {
        $users[] = [
            'name' => $customer['name'],
            'nama' => $customer['name'],
            'email' => $customer['email'],
            'password' => $password,
            'level' => 'customer',
            'no_telp' => $customer['no_telp'],
            'alamat' => $customer['alamat'],
            'email_verified_at' => $now,
            'created_at' => $now,
            'updated_at' => $now
        ];
    }
    
    // Clear existing users first
    $capsule->table('users')->delete();
    echo "Cleared existing users\n";
    
    // Insert all users
    foreach ($users as $user) {
        $existing = $capsule->table('users')->where('email', $user['email'])->first();
        if (!$existing) {
            $capsule->table('users')->insert($user);
            echo "Inserted: {$user['name']} ({$user['email']})\n";
        } else {
            echo "Skipped existing: {$user['name']} ({$user['email']})\n";
        }
    }
    
    $finalCount = $capsule->table('users')->count();
    echo "\n✅ SUCCESS! Total users now: $finalCount\n";
    
    // Show breakdown
    $adminCount = $capsule->table('users')->where('level', 'admin')->count();
    $tailorCount = $capsule->table('users')->where('level', 'tailor')->count();
    $customerCount = $capsule->table('users')->where('level', 'customer')->count();
    
    echo "📊 Breakdown:\n";
    echo "- Admin: $adminCount\n";
    echo "- Tailor: $tailorCount\n";
    echo "- Customer: $customerCount\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

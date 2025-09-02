<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Hash;
use App\Models\User;

// Load Laravel app
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    // Hapus semua user lama
    User::truncate();
    
    $password = Hash::make('zxcvbnm123');
    $now = now();

    // 1. Admin User
    User::create([
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
    ]);

    // 2. Tailor User
    User::create([
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
    ]);

    // 3-11. User Biasa (9 pengguna)
    $users = [
        ['name' => 'Siti Nurhaliza', 'email' => 'siti@user.com', 'no_telp' => '081234567892', 'alamat' => 'Jl. Melati No. 12, Bandung'],
        ['name' => 'Budi Santoso', 'email' => 'budi@user.com', 'no_telp' => '081234567893', 'alamat' => 'Jl. Mawar No. 15, Surabaya'],
        ['name' => 'Rina Kartika', 'email' => 'rina@user.com', 'no_telp' => '081234567894', 'alamat' => 'Jl. Anggrek No. 8, Yogyakarta'],
        ['name' => 'Ahmad Fauzi', 'email' => 'ahmad@user.com', 'no_telp' => '081234567895', 'alamat' => 'Jl. Kenanga No. 22, Medan'],
        ['name' => 'Dewi Sartika', 'email' => 'dewi@user.com', 'no_telp' => '081234567896', 'alamat' => 'Jl. Cempaka No. 7, Semarang'],
        ['name' => 'Rudi Hermawan', 'email' => 'rudi@user.com', 'no_telp' => '081234567897', 'alamat' => 'Jl. Dahlia No. 19, Malang'],
        ['name' => 'Maya Sari', 'email' => 'maya@user.com', 'no_telp' => '081234567898', 'alamat' => 'Jl. Tulip No. 3, Denpasar'],
        ['name' => 'Indra Gunawan', 'email' => 'indra@user.com', 'no_telp' => '081234567899', 'alamat' => 'Jl. Sakura No. 11, Makassar'],
        ['name' => 'Lestari Wulandari', 'email' => 'lestari@user.com', 'no_telp' => '081234567800', 'alamat' => 'Jl. Bougenville No. 25, Palembang']
    ];

    foreach ($users as $user) {
        User::create([
            'name' => $user['name'],
            'nama' => $user['name'],
            'email' => $user['email'],
            'password' => $password,
            'level' => 'user',
            'no_telp' => $user['no_telp'],
            'alamat' => $user['alamat'],
            'email_verified_at' => $now,
            'created_at' => $now,
            'updated_at' => $now
        ]);
    }

    echo "✅ Berhasil menambahkan 11 pengguna:\n";
    echo "   - 1 Admin: admin@tokort.com\n";
    echo "   - 1 Penjahit: tailor@tokort.com\n";
    echo "   - 9 Pengguna biasa\n";
    echo "\n📝 Password untuk semua user: zxcvbnm123\n";
    
    // Tampilkan semua user
    $allUsers = User::all();
    echo "\n📋 Daftar User yang berhasil dibuat:\n";
    foreach ($allUsers as $user) {
        echo "   {$user->id}. {$user->name} ({$user->email}) - Level: {$user->level}\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

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
    // Test database connection
    $pdo = $capsule->getConnection()->getPdo();
    echo "✅ Database connected successfully!\n";
    
    // Check if products table exists
    $tables = $capsule->select("SHOW TABLES LIKE 'products'");
    if (empty($tables)) {
        echo "❌ Products table does not exist!\n";
        exit;
    }
    echo "✅ Products table exists!\n";
    
    // Count products
    $count = $capsule->table('products')->count();
    echo "📊 Total products in database: $count\n";
    
    if ($count == 0) {
        echo "⚠️ No products found! Inserting sample products...\n";
        
        // Insert sample products directly
        $products = [
            [
                'name' => 'Kemeja Cowok Polos Lengan Panjang',
                'image' => 'images/baju kemeja cowok 2.jpg',
                'price' => 85000,
                'kategory' => 'Kemeja',
                'bahan' => 'Cotton Premium',
                'motif' => 'Polos',
                'dikirim_dari' => 'Jakarta',
                'warna' => 'Putih, Biru Muda, Abu-abu',
                'ukuran' => 'S, M, L, XL',
                'deskripsi_ukuran' => 'S: Lingkar dada 88-92cm, M: Lingkar dada 92-96cm, L: Lingkar dada 96-100cm, XL: Lingkar dada 100-104cm',
                'deskripsi' => 'Kemeja cowok polos lengan panjang dengan bahan cotton premium yang nyaman dan breathable.',
                'description' => 'Kemeja formal pria dengan bahan cotton premium, nyaman dipakai seharian',
                'colors' => 'Putih,Biru Muda,Abu-abu',
                'sizes' => 'S,M,L,XL',
                'fabric_type' => 'Cotton Premium',
                'is_preorder' => false,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'Kemeja Cowok Levis Style',
                'image' => 'images/baju kemeja cowok 3.jpg',
                'price' => 220000,
                'kategory' => 'Kemeja',
                'bahan' => 'Denim Premium',
                'motif' => 'Polos',
                'dikirim_dari' => 'Jakarta',
                'warna' => 'Biru Tua, Hitam',
                'ukuran' => 'S, M, L, XL',
                'deskripsi_ukuran' => 'S: Lingkar dada 88-92cm, M: Lingkar dada 92-96cm, L: Lingkar dada 96-100cm, XL: Lingkar dada 100-104cm',
                'deskripsi' => 'Kemeja cowok style levis dengan kualitas premium. Berbahan denim tebal yang tahan lama dan nyaman.',
                'description' => 'Kemeja denim premium dengan style levis yang trendy',
                'colors' => 'Biru Tua,Hitam',
                'sizes' => 'S,M,L,XL',
                'fabric_type' => 'Denim Premium',
                'is_preorder' => false,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'Blouse Wanita Elegant Chiffon',
                'image' => 'images/baju blus.png',
                'price' => 120000,
                'kategory' => 'Blouse',
                'bahan' => 'Chiffon Premium',
                'motif' => 'Polos',
                'dikirim_dari' => 'Jakarta',
                'warna' => 'Putih, Pink, Navy',
                'ukuran' => 'S, M, L, XL',
                'deskripsi_ukuran' => 'S: Lingkar dada 88cm, M: Lingkar dada 92cm, L: Lingkar dada 96cm, XL: Lingkar dada 100cm',
                'deskripsi' => 'Blouse wanita dengan desain elegant dan feminin. Berbahan chiffon premium yang ringan dan nyaman.',
                'description' => 'Blouse elegant berbahan chiffon untuk tampilan profesional',
                'colors' => 'Putih,Pink,Navy',
                'sizes' => 'S,M,L,XL',
                'fabric_type' => 'Chiffon Premium',
                'is_preorder' => false,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];
        
        foreach ($products as $product) {
            $capsule->table('products')->insert($product);
        }
        
        echo "✅ Sample products inserted!\n";
        $count = $capsule->table('products')->count();
        echo "📊 New total products: $count\n";
    }
    
    // Display first few products
    $products = $capsule->table('products')->limit(5)->get();
    echo "\n🛍️ First 5 products:\n";
    foreach ($products as $product) {
        echo "- {$product->name} - Rp " . number_format($product->price) . " ({$product->kategory})\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

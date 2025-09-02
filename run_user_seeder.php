<?php

// Bootstrap Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Import seeder
use Database\Seeders\AdminUserSeeder;

try {
    echo "🚀 Menjalankan AdminUserSeeder...\n\n";
    
    $seeder = new AdminUserSeeder();
    $seeder->run();
    
    echo "\n✅ Seeder berhasil dijalankan!\n";
    echo "Silakan cek tabel users di database Anda.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

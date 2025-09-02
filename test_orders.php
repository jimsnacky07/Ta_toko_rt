<?php

// Simple test script to create sample orders and verify admin dashboard connection
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;

echo "Testing Order Creation and Admin Dashboard Connection\n";
echo "====================================================\n";

try {
    // Check current counts
    $orderCount = Order::count();
    $userCount = User::count();
    
    echo "Current Orders: $orderCount\n";
    echo "Current Users: $userCount\n";
    
    // Create test user if none exist
    if ($userCount == 0) {
        echo "Creating test user...\n";
        $user = User::create([
            'name' => 'Test Customer',
            'nama' => 'Test Customer',
            'email' => 'customer@test.com',
            'password' => bcrypt('password'),
            'level' => 'user',
            'no_telp' => '081234567890'
        ]);
        echo "Test user created: {$user->name} (ID: {$user->id})\n";
    } else {
        $user = User::where('level', 'user')->first();
        if (!$user) {
            $user = User::first();
        }
        echo "Using existing user: {$user->name} (ID: {$user->id})\n";
    }
    
    // Create sample orders
    echo "\nCreating sample orders...\n";
    
    // Product Order
    $productOrder = Order::create([
        'user_id' => $user->id,
        'order_code' => 'PROD-' . date('YmdHis') . '-001',
        'kode_pesanan' => 'PROD-' . date('YmdHis') . '-001',
        'status' => 'paid',
        'total_amount' => 250000,
        'total_harga' => 250000,
        'paid_at' => now(),
    ]);
    
    OrderItem::create([
        'order_id' => $productOrder->id,
        'product_id' => 1,
        'garment_type' => 'Kemeja Batik',
        'fabric_type' => 'Katun Premium',
        'size' => 'L',
        'price' => 250000,
        'quantity' => 1,
        'total_price' => 250000,
        'special_request' => 'Warna biru navy'
    ]);
    
    echo "Product order created: {$productOrder->order_code}\n";
    
    // Custom Order
    $customOrder = Order::create([
        'user_id' => $user->id,
        'order_code' => 'CUSTOM-' . date('YmdHis') . '-002',
        'kode_pesanan' => 'CUSTOM-' . date('YmdHis') . '-002',
        'status' => 'pending',
        'total_amount' => 350000,
        'total_harga' => 350000,
    ]);
    
    OrderItem::create([
        'order_id' => $customOrder->id,
        'product_id' => null,
        'garment_type' => 'Jas Custom',
        'fabric_type' => 'Wool Import',
        'size' => 'Custom',
        'price' => 350000,
        'quantity' => 1,
        'total_price' => 350000,
        'special_request' => 'Jas formal untuk acara pernikahan, warna hitam'
    ]);
    
    echo "Custom order created: {$customOrder->order_code}\n";
    
    // Verify orders can be retrieved with relationships
    echo "\nVerifying order relationships...\n";
    $orders = Order::with(['user', 'orderItems'])->get();
    
    foreach ($orders as $order) {
        $userName = $order->user->nama ?? $order->user->name ?? 'Unknown';
        $itemsCount = $order->orderItems->count();
        $orderType = $order->orderItems->first()->product_id ? 'Product' : 'Custom';
        
        echo "Order #{$order->id}: {$order->order_code} - User: $userName - Type: $orderType - Items: $itemsCount\n";
    }
    
    echo "\nTest completed successfully!\n";
    echo "Total orders now: " . Order::count() . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}

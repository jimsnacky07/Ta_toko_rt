<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Product;

try {
    // Get a test user (assuming user ID 1 exists)
    $user = User::find(1);
    if (!$user) {
        echo "User not found. Creating test user...\n";
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'level' => 'user'
        ]);
    }
    
    // Get a test product
    $product = Product::first();
    if (!$product) {
        echo "No products found. Please run ProductSeeder first.\n";
        exit(1);
    }
    
    // Create test order
    $order = Order::create([
        'user_id' => $user->id,
        'order_code' => 'TEST' . time(),
        'status' => 'paid',
        'total_amount' => 150000,
        'paid_at' => now(),
    ]);
    
    // Create order item
    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'garment_type' => 'Ready Made',
        'fabric_type' => 'Cotton',
        'size' => 'M',
        'price' => 150000,
        'quantity' => 1,
        'total_price' => 150000,
        'special_request' => 'Test order'
    ]);
    
    echo "Test order created successfully!\n";
    echo "Order ID: {$order->id}\n";
    echo "Order Code: {$order->order_code}\n";
    echo "User: {$user->name} (ID: {$user->id})\n";
    echo "Product: {$product->nama} (ID: {$product->id})\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

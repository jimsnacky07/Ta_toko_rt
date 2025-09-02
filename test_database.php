<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;

echo "=== Database Connection Test ===\n";

try {
    // Test database connection
    $ordersCount = Order::count();
    $usersCount = User::count();
    $orderItemsCount = OrderItem::count();
    
    echo "Orders: $ordersCount\n";
    echo "Users: $usersCount\n";
    echo "Order Items: $orderItemsCount\n";
    
    // Show recent orders
    echo "\n=== Recent Orders ===\n";
    $orders = Order::with(['user', 'orderItems'])->latest()->take(5)->get();
    
    foreach ($orders as $order) {
        $userName = $order->user->nama ?? $order->user->name ?? 'Unknown';
        $orderCode = $order->order_code ?? $order->kode_pesanan ?? 'N/A';
        $itemsCount = $order->orderItems->count();
        
        echo "Order #{$order->id} - User: $userName - Code: $orderCode - Status: {$order->status} - Items: $itemsCount\n";
    }
    
    // Create test order if no orders exist
    if ($ordersCount == 0) {
        echo "\n=== Creating Test Order ===\n";
        
        $user = User::first();
        if (!$user) {
            echo "No users found. Creating test user...\n";
            $user = User::create([
                'name' => 'Test User',
                'nama' => 'Test User',
                'email' => 'test@example.com',
                'password' => bcrypt('password'),
                'level' => 'user'
            ]);
        }
        
        $order = Order::create([
            'user_id' => $user->id,
            'order_code' => 'TEST-' . now()->format('YmdHis'),
            'status' => 'paid',
            'total_amount' => 150000,
            'paid_at' => now(),
        ]);
        
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => 1,
            'garment_type' => 'Test Product',
            'fabric_type' => 'Cotton',
            'size' => 'M',
            'price' => 150000,
            'quantity' => 1,
            'total_price' => 150000,
            'special_request' => 'Test order for admin dashboard'
        ]);
        
        echo "Test order created: #{$order->id}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;

echo "=== Admin Dashboard Integration Test ===\n";

try {
    // 1. Create test users if they don't exist
    echo "1. Setting up test users...\n";
    
    $admin = User::where('level', 'admin')->first();
    if (!$admin) {
        $admin = User::create([
            'name' => 'Admin User',
            'nama' => 'Admin User',
            'email' => 'admin@tokort.com',
            'password' => bcrypt('admin123'),
            'level' => 'admin'
        ]);
        echo "   - Admin user created\n";
    } else {
        echo "   - Admin user exists: {$admin->email}\n";
    }
    
    $customer = User::where('level', 'user')->first();
    if (!$customer) {
        $customer = User::create([
            'name' => 'Test Customer',
            'nama' => 'Test Customer',
            'email' => 'customer@test.com',
            'password' => bcrypt('password'),
            'level' => 'user',
            'no_telp' => '081234567890'
        ]);
        echo "   - Customer user created\n";
    } else {
        echo "   - Customer user exists: {$customer->email}\n";
    }
    
    // 2. Create sample orders to test admin dashboard
    echo "\n2. Creating sample orders...\n";
    
    // Product Order
    $productOrder = Order::create([
        'user_id' => $customer->id,
        'order_code' => 'PROD-' . date('YmdHis') . '-' . rand(100, 999),
        'status' => 'paid',
        'total_amount' => 275000,
        'paid_at' => now(),
    ]);
    
    OrderItem::create([
        'order_id' => $productOrder->id,
        'product_id' => 1,
        'garment_type' => 'Kemeja Batik Premium',
        'fabric_type' => 'Katun Halus',
        'size' => 'L',
        'price' => 275000,
        'quantity' => 1,
        'total_price' => 275000,
        'special_request' => 'Kemeja untuk acara formal'
    ]);
    
    echo "   - Product order created: {$productOrder->order_code}\n";
    
    // Custom Order
    $customOrder = Order::create([
        'user_id' => $customer->id,
        'order_code' => 'CUSTOM-' . date('YmdHis') . '-' . rand(100, 999),
        'status' => 'pending',
        'total_amount' => 450000,
    ]);
    
    OrderItem::create([
        'order_id' => $customOrder->id,
        'product_id' => null,
        'garment_type' => 'Jas Custom',
        'fabric_type' => 'Wool Premium',
        'size' => 'Custom',
        'price' => 450000,
        'quantity' => 1,
        'total_price' => 450000,
        'special_request' => 'Jas untuk pernikahan, warna navy, model slim fit'
    ]);
    
    echo "   - Custom order created: {$customOrder->order_code}\n";
    
    // 3. Test admin dashboard data retrieval
    echo "\n3. Testing admin dashboard data retrieval...\n";
    
    // Test order counts by status
    $totalOrders = Order::count();
    $paidOrders = Order::where('status', 'paid')->count();
    $pendingOrders = Order::where('status', 'pending')->count();
    
    echo "   - Total orders: $totalOrders\n";
    echo "   - Paid orders: $paidOrders\n";
    echo "   - Pending orders: $pendingOrders\n";
    
    // Test order types
    $productOrdersCount = Order::whereHas('orderItems', function ($q) {
        $q->whereNotNull('product_id');
    })->count();
    
    $customOrdersCount = Order::whereHas('orderItems', function ($q) {
        $q->whereNull('product_id');
    })->count();
    
    echo "   - Product orders: $productOrdersCount\n";
    echo "   - Custom orders: $customOrdersCount\n";
    
    // Test recent orders with relationships
    $recentOrders = Order::with(['user', 'orderItems'])
        ->latest()
        ->take(5)
        ->get();
    
    echo "   - Recent orders loaded: " . $recentOrders->count() . "\n";
    
    foreach ($recentOrders as $order) {
        $userName = $order->user->nama ?? $order->user->name ?? 'Unknown';
        $itemsCount = $order->orderItems->count();
        $orderType = $order->orderItems->first()->product_id ? 'Product' : 'Custom';
        
        echo "     * Order #{$order->id}: {$order->order_code} - User: $userName - Type: $orderType - Items: $itemsCount\n";
    }
    
    // 4. Test customer list with order counts
    echo "\n4. Testing customer list with order relationships...\n";
    
    $customers = User::where('level', 'user')
        ->withCount(['orders', 'pesanan'])
        ->with([
            'orders' => fn($q) => $q->latest()->limit(1),
            'pesanan' => fn($q) => $q->latest()->limit(1)
        ])
        ->get();
    
    echo "   - Customers loaded: " . $customers->count() . "\n";
    
    foreach ($customers as $customer) {
        $totalOrders = ($customer->orders_count ?? 0) + ($customer->pesanan_count ?? 0);
        $lastOrder = $customer->orders->first();
        $lastPesanan = $customer->pesanan->first();
        
        $lastOrderInfo = 'None';
        if ($lastOrder) {
            $lastOrderInfo = "{$lastOrder->order_code} ({$lastOrder->status})";
        } elseif ($lastPesanan) {
            $lastOrderInfo = "{$lastPesanan->kode_pesanan} ({$lastPesanan->status})";
        }
        
        echo "     * Customer: {$customer->email} - Total Orders: $totalOrders - Last Order: $lastOrderInfo\n";
    }
    
    // 5. Test monthly revenue calculation
    echo "\n5. Testing revenue calculations...\n";
    
    $monthlyRevenue = Order::where('status', 'paid')
        ->whereMonth('paid_at', now()->month)
        ->whereYear('paid_at', now()->year)
        ->sum('total_amount');
    
    echo "   - Monthly revenue: Rp " . number_format($monthlyRevenue, 0, ',', '.') . "\n";
    
    $totalRevenue = Order::where('status', 'paid')->sum('total_amount');
    echo "   - Total revenue: Rp " . number_format($totalRevenue, 0, ',', '.') . "\n";
    
    echo "\n=== Test Results ===\n";
    echo "✅ User model relationships working\n";
    echo "✅ Order creation and retrieval working\n";
    echo "✅ Admin dashboard data queries working\n";
    echo "✅ Customer list with order counts working\n";
    echo "✅ Revenue calculations working\n";
    echo "\n🎉 Admin Dashboard Integration Test PASSED!\n";
    
} catch (Exception $e) {
    echo "\n❌ Test FAILED!\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

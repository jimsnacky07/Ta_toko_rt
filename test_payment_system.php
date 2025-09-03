<?php

/**
 * Test Script untuk Pembayaran Midtrans
 * 
 * Script ini untuk testing apakah sistem pembayaran sudah berfungsi dengan benar
 * setelah perbaikan yang dilakukan.
 */

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Product;

echo "=== TEST SISTEM PEMBAYARAN ===\n\n";

// 1. Test Database Connection
echo "1. Testing Database Connection...\n";
try {
    DB::connection()->getPdo();
    echo "✅ Database connection: OK\n";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// 2. Test Tables Exist
echo "\n2. Testing Database Tables...\n";
$tables = ['orders', 'order_items', 'users', 'products'];
foreach ($tables as $table) {
    if (Schema::hasTable($table)) {
        echo "✅ Table {$table}: EXISTS\n";
    } else {
        echo "❌ Table {$table}: MISSING\n";
    }
}

// 3. Test User Data
echo "\n3. Testing User Data...\n";
$users = User::all();
echo "✅ Total users: " . $users->count() . "\n";

$admin = User::where('level', 'admin')->first();
if ($admin) {
    echo "✅ Admin user: " . $admin->email . "\n";
} else {
    echo "❌ Admin user not found\n";
}

$customer = User::where('level', 'user')->first();
if ($customer) {
    echo "✅ Customer user: " . $customer->email . "\n";
} else {
    echo "❌ Customer user not found\n";
}

// 4. Test Product Data
echo "\n4. Testing Product Data...\n";
$products = Product::all();
echo "✅ Total products: " . $products->count() . "\n";

if ($products->count() > 0) {
    $firstProduct = $products->first();
    echo "✅ Sample product: " . $firstProduct->name . " (Rp " . number_format($firstProduct->price) . ")\n";
} else {
    echo "❌ No products found\n";
}

// 5. Test Order Data
echo "\n5. Testing Order Data...\n";
$orders = Order::all();
echo "✅ Total orders: " . $orders->count() . "\n";

if ($orders->count() > 0) {
    $recentOrder = Order::latest()->first();
    echo "✅ Recent order: " . $recentOrder->order_code . " (Status: " . $recentOrder->status . ")\n";

    $orderItems = OrderItem::where('order_id', $recentOrder->id)->get();
    echo "✅ Order items: " . $orderItems->count() . " items\n";
} else {
    echo "ℹ️  No orders found (this is normal for new system)\n";
}

// 6. Test Midtrans Configuration
echo "\n6. Testing Midtrans Configuration...\n";
$serverKey = config('midtrans.server_key');
$clientKey = config('midtrans.client_key');
$isProduction = config('midtrans.is_production');

if ($serverKey) {
    echo "✅ Server Key: " . substr($serverKey, 0, 10) . "...\n";
} else {
    echo "❌ Server Key: NOT SET\n";
}

if ($clientKey) {
    echo "✅ Client Key: " . substr($clientKey, 0, 10) . "...\n";
} else {
    echo "❌ Client Key: NOT SET\n";
}

echo "✅ Production Mode: " . ($isProduction ? 'YES' : 'NO') . "\n";

// 7. Test Routes
echo "\n7. Testing Routes...\n";
$routes = [
    'midtrans.create-snap-token' => '/midtrans/create-snap-token',
    'midtrans.finish' => '/midtrans/finish',
    'midtrans.unfinish' => '/midtrans/unfinish',
    'midtrans.error' => '/midtrans/error',
    'midtrans.notification' => '/midtrans/notification',
];

foreach ($routes as $name => $path) {
    if (Route::has($name)) {
        echo "✅ Route {$name}: EXISTS\n";
    } else {
        echo "❌ Route {$name}: MISSING\n";
    }
}

// 8. Test Session Configuration
echo "\n8. Testing Session Configuration...\n";
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "✅ Session: ACTIVE\n";
} else {
    echo "❌ Session: NOT ACTIVE\n";
}

// 9. Summary
echo "\n=== SUMMARY ===\n";
echo "Database: " . (DB::connection()->getPdo() ? "✅ OK" : "❌ FAILED") . "\n";
echo "Tables: " . (Schema::hasTable('orders') && Schema::hasTable('order_items') ? "✅ OK" : "❌ MISSING") . "\n";
echo "Users: " . ($users->count() > 0 ? "✅ OK" : "❌ EMPTY") . "\n";
echo "Products: " . ($products->count() > 0 ? "✅ OK" : "❌ EMPTY") . "\n";
echo "Midtrans Config: " . ($serverKey && $clientKey ? "✅ OK" : "❌ MISSING") . "\n";

echo "\n=== RECOMMENDATIONS ===\n";
if (!$serverKey || !$clientKey) {
    echo "⚠️  Set Midtrans credentials in .env file:\n";
    echo "   MIDTRANS_SERVER_KEY=your_server_key\n";
    echo "   MIDTRANS_CLIENT_KEY=your_client_key\n";
    echo "   MIDTRANS_IS_PRODUCTION=false\n";
}

if ($products->count() == 0) {
    echo "⚠️  No products found. Run seeder:\n";
    echo "   php artisan db:seed --class=ProductSeeder\n";
}

if ($users->count() == 0) {
    echo "⚠️  No users found. Run seeder:\n";
    echo "   php artisan db:seed --class=AdminUserSeeder\n";
}

echo "\n=== TEST COMPLETED ===\n";
echo "If all checks show ✅, your payment system should work correctly.\n";
echo "If you see ❌, please fix the issues before testing payment.\n";

<?php

/**
 * Script untuk Test Webhook Midtrans
 * 
 * Script ini akan membantu mengatur dan test webhook Midtrans
 */

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TEST WEBHOOK MIDTRANS ===\n\n";

// Cek konfigurasi Midtrans
$serverKey = config('midtrans.server_key');
$clientKey = config('midtrans.client_key');
$isProduction = config('midtrans.is_production');

echo "Server Key: " . ($serverKey ? substr($serverKey, 0, 20) . "..." : "❌ KOSONG") . "\n";
echo "Client Key: " . ($clientKey ? substr($clientKey, 0, 20) . "..." : "❌ KOSONG") . "\n";
echo "Production Mode: " . ($isProduction ? "YES" : "NO") . "\n\n";

if (empty($serverKey) || empty($clientKey)) {
    echo "❌ MIDTRANS KEYS BELUM DIATUR!\n";
    exit(1);
}

// Cek route webhook
echo "=== CEK ROUTE WEBHOOK ===\n";
$routes = \Illuminate\Support\Facades\Route::getRoutes();
$webhookRoute = null;

foreach ($routes as $route) {
    if ($route->uri() === 'midtrans/notification') {
        $webhookRoute = $route;
        break;
    }
}

if ($webhookRoute) {
    echo "✅ Route webhook ditemukan: " . $webhookRoute->uri() . "\n";
    echo "   Method: " . implode('|', $webhookRoute->methods()) . "\n";
    echo "   Name: " . $webhookRoute->getName() . "\n";
    echo "   Controller: " . $webhookRoute->getActionName() . "\n";
} else {
    echo "❌ Route webhook tidak ditemukan!\n";
}

echo "\n=== WEBHOOK URL YANG HARUS DIATUR DI MIDTRANS DASHBOARD ===\n";

$baseUrl = config('app.url', 'http://localhost:8000');
$webhookUrl = $baseUrl . '/midtrans/notification';

echo "Webhook URL: " . $webhookUrl . "\n\n";

echo "=== LANGKAH-LANGKAH MENGATUR WEBHOOK ===\n\n";
echo "1. Login ke Midtrans Dashboard: https://dashboard.midtrans.com\n";
echo "2. Pilih Environment: SANDBOX (untuk testing)\n";
echo "3. Buka menu Settings > Configuration\n";
echo "4. Scroll ke bagian 'Notification URL'\n";
echo "5. Masukkan URL: " . $webhookUrl . "\n";
echo "6. Klik Save\n\n";

echo "=== TEST WEBHOOK MANUAL ===\n\n";

// Simulasi data webhook
$webhookData = [
    'order_id' => 'ORD-20250903031643748-7XWVPZ',
    'transaction_status' => 'settlement',
    'payment_type' => 'qris',
    'gross_amount' => '220000',
    'customer_details' => [
        'email' => 'anton@gmail.com'
    ]
];

echo "Data webhook yang akan dikirim:\n";
echo json_encode($webhookData, JSON_PRETTY_PRINT) . "\n\n";

echo "Untuk test webhook manual, gunakan command:\n";
echo "curl -X POST " . $webhookUrl . " \\\n";
echo "  -H 'Content-Type: application/json' \\\n";
echo "  -d '" . json_encode($webhookData) . "'\n\n";

echo "=== CEK ORDER YANG PERLU DIUPDATE ===\n\n";

// Cek order dengan status pending
$pendingOrders = \App\Models\Order::where('metode_pembayaran', 'pending')
    ->orWhere('status', 'menunggu')
    ->get();

if ($pendingOrders->count() > 0) {
    echo "Order yang perlu diupdate:\n";
    foreach ($pendingOrders as $order) {
        echo "- ID: " . $order->id . "\n";
        echo "  Order Code: " . $order->order_code . "\n";
        echo "  Status: " . $order->status . "\n";
        echo "  Metode Pembayaran: " . $order->metode_pembayaran . "\n";
        echo "  Total: " . $order->total_amount . "\n";
        echo "  Created: " . $order->created_at . "\n\n";
    }
} else {
    echo "✅ Tidak ada order dengan status pending\n";
}

echo "=== SELESAI ===\n";
echo "Setelah mengatur webhook di Midtrans Dashboard, test pembayaran baru.\n";
echo "Webhook akan dipanggil otomatis setelah pembayaran berhasil.\n";

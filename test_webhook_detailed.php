<?php

/**
 * Script untuk Test Webhook Midtrans dengan Logging Detail
 * 
 * Script ini akan membantu test webhook dan melihat semua data yang dikirim Midtrans
 */

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TEST WEBHOOK MIDTRANS DENGAN LOGGING DETAIL ===\n\n";

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

echo "\n=== WEBHOOK URL ===\n";
$baseUrl = config('app.url', 'http://localhost:8000');
$webhookUrl = $baseUrl . '/midtrans/notification';
echo "Webhook URL: " . $webhookUrl . "\n\n";

echo "=== SIMULASI DATA WEBHOOK ===\n\n";

// Simulasi data webhook untuk QRIS
$webhookDataQRIS = [
    'order_id' => 'ORD-20250903031643748-7XWVPZ',
    'transaction_id' => 'TRX-' . time(),
    'transaction_status' => 'settlement',
    'transaction_time' => date('Y-m-d H:i:s'),
    'payment_type' => 'qris',
    'gross_amount' => '220000',
    'currency' => 'IDR',
    'fraud_status' => 'accept',
    'status_code' => '200',
    'status_message' => 'Success',
    'merchant_id' => 'G123456789',
    'finish_redirect_url' => $baseUrl . '/payment/finish',
    'error_snap_url' => $baseUrl . '/payment/error',
    'pending_redirect_url' => $baseUrl . '/payment/pending',
    'unfinish_redirect_url' => $baseUrl . '/payment/unfinish',
    'customer_details' => [
        'first_name' => 'Anton',
        'last_name' => 'Sabu',
        'email' => 'anton@gmail.com',
        'phone' => '08123456789'
    ],
    'item_details' => [
        [
            'id' => 'ITEM001',
            'price' => '220000',
            'quantity' => '1',
            'name' => 'Kemeja'
        ]
    ]
];

echo "Data webhook QRIS yang akan dikirim:\n";
echo json_encode($webhookDataQRIS, JSON_PRETTY_PRINT) . "\n\n";

// Simulasi data webhook untuk Bank Transfer
$webhookDataBankTransfer = [
    'order_id' => 'ORD-20250903030005812-SFRAPD',
    'transaction_id' => 'TRX-' . (time() + 1),
    'transaction_status' => 'settlement',
    'transaction_time' => date('Y-m-d H:i:s'),
    'payment_type' => 'bank_transfer',
    'gross_amount' => '100000',
    'currency' => 'IDR',
    'fraud_status' => 'accept',
    'status_code' => '200',
    'status_message' => 'Success',
    'merchant_id' => 'G123456789',
    'finish_redirect_url' => $baseUrl . '/payment/finish',
    'error_snap_url' => $baseUrl . '/payment/error',
    'pending_redirect_url' => $baseUrl . '/payment/pending',
    'unfinish_redirect_url' => $baseUrl . '/payment/unfinish',
    'customer_details' => [
        'first_name' => 'Anton',
        'last_name' => 'Sabu',
        'email' => 'anton@gmail.com',
        'phone' => '08123456789'
    ],
    'item_details' => [
        [
            'id' => 'ITEM002',
            'price' => '100000',
            'quantity' => '1',
            'name' => 'Kemeja'
        ]
    ]
];

echo "Data webhook Bank Transfer yang akan dikirim:\n";
echo json_encode($webhookDataBankTransfer, JSON_PRETTY_PRINT) . "\n\n";

echo "=== COMMAND UNTUK TEST WEBHOOK ===\n\n";

echo "1. Test QRIS Webhook:\n";
echo "curl -X POST " . $webhookUrl . " \\\n";
echo "  -H 'Content-Type: application/json' \\\n";
echo "  -H 'User-Agent: Midtrans-Webhook/1.0' \\\n";
echo "  -d '" . json_encode($webhookDataQRIS) . "'\n\n";

echo "2. Test Bank Transfer Webhook:\n";
echo "curl -X POST " . $webhookUrl . " \\\n";
echo "  -H 'Content-Type: application/json' \\\n";
echo "  -H 'User-Agent: Midtrans-Webhook/1.0' \\\n";
echo "  -d '" . json_encode($webhookDataBankTransfer) . "'\n\n";

echo "=== MONITORING LOG ===\n\n";
echo "Untuk melihat log webhook secara real-time, jalankan:\n";
echo "tail -f storage/logs/laravel.log | grep MIDTRANS\n\n";

echo "=== CEK ORDER YANG AKAN DIUPDATE ===\n\n";

// Cek order dengan status pending
$pendingOrders = \App\Models\Order::where('metode_pembayaran', 'pending')
    ->orWhere('status', 'menunggu')
    ->get();

if ($pendingOrders->count() > 0) {
    echo "Order yang akan diupdate oleh webhook:\n";
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

echo "=== LANGKAH-LANGKAH TEST ===\n\n";
echo "1. Jalankan command curl untuk test webhook\n";
echo "2. Monitor log dengan: tail -f storage/logs/laravel.log | grep MIDTRANS\n";
echo "3. Cek database untuk memastikan order terupdate\n";
echo "4. Verifikasi metode pembayaran berubah dari 'pending' ke 'QRIS' atau 'Bank Transfer'\n\n";

echo "=== SELESAI ===\n";
echo "Setelah menjalankan webhook test, cek log untuk melihat semua data yang dikirim Midtrans.\n";
echo "Log akan menampilkan:\n";
echo "- Raw request data (headers, body, IP)\n";
echo "- Parsed notification data\n";
echo "- Customer details\n";
echo "- Item details\n";
echo "- Payment method mapping\n";
echo "- Order update process\n";
echo "- Final response\n";

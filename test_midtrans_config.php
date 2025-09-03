<?php

/**
 * Script untuk Test Konfigurasi Midtrans
 * 
 * Script ini akan mengecek apakah konfigurasi Midtrans sudah benar
 */

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TEST KONFIGURASI MIDTRANS ===\n\n";

// Cek konfigurasi Midtrans
$serverKey = config('midtrans.server_key');
$clientKey = config('midtrans.client_key');
$isProduction = config('midtrans.is_production');

echo "Server Key: " . ($serverKey ? substr($serverKey, 0, 20) . "..." : "❌ KOSONG") . "\n";
echo "Client Key: " . ($clientKey ? substr($clientKey, 0, 20) . "..." : "❌ KOSONG") . "\n";
echo "Production Mode: " . ($isProduction ? "YES" : "NO") . "\n\n";

if (empty($serverKey) || empty($clientKey)) {
    echo "❌ MIDTRANS KEYS BELUM DIATUR!\n\n";
    echo "📋 Langkah-langkah untuk mengatur Midtrans Keys:\n\n";
    echo "1. Login ke Midtrans Dashboard: https://dashboard.midtrans.com\n";
    echo "2. Pilih Environment: SANDBOX (untuk testing)\n";
    echo "3. Buka menu Settings > Access Keys\n";
    echo "4. Copy Server Key dan Client Key\n";
    echo "5. Edit file .env dan ganti nilai berikut:\n\n";
    echo "   MIDTRANS_SERVER_KEY=SB-Mid-server-XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX\n";
    echo "   MIDTRANS_CLIENT_KEY=SB-Mid-client-XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX\n\n";
    echo "6. Jalankan: php artisan config:clear\n\n";
} else {
    echo "✅ MIDTRANS KEYS SUDAH DIATUR!\n\n";

    // Test Midtrans configuration
    try {
        \Midtrans\Config::$serverKey = $serverKey;
        \Midtrans\Config::$clientKey = $clientKey;
        \Midtrans\Config::$isProduction = $isProduction;
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;

        echo "✅ Konfigurasi Midtrans berhasil di-set\n";

        // Test membuat snap token sederhana
        $params = [
            'transaction_details' => [
                'order_id'     => 'TEST-' . time(),
                'gross_amount' => 100000,
            ],
            'customer_details' => [
                'first_name' => 'Test Customer',
                'email'      => 'test@example.com',
                'phone'      => '08123456789',
            ],
        ];

        $snapToken = \Midtrans\Snap::getSnapToken($params);
        echo "✅ Snap token berhasil dibuat: " . substr($snapToken, 0, 20) . "...\n";
        echo "✅ Sistem pembayaran siap digunakan\n\n";
    } catch (Exception $e) {
        echo "❌ Error saat test Midtrans: " . $e->getMessage() . "\n\n";
    }
}

echo "=== SELESAI ===\n";

<?php

/**
 * Script untuk Setup Environment Variables Midtrans
 * 
 * Script ini akan membantu mengatur konfigurasi Midtrans di file .env
 */

echo "=== SETUP MIDTRANS ENVIRONMENT VARIABLES ===\n\n";

// Cek apakah file .env ada
$envFile = '.env';
$envExampleFile = '.env.example';

if (!file_exists($envFile)) {
    echo "❌ File .env tidak ditemukan!\n\n";

    if (file_exists($envExampleFile)) {
        echo "✅ File .env.example ditemukan\n";
        echo "📋 Copy .env.example ke .env:\n";
        echo "   copy .env.example .env\n\n";
    } else {
        echo "📋 Buat file .env dengan konfigurasi berikut:\n\n";
        echo "APP_NAME=Laravel\n";
        echo "APP_ENV=local\n";
        echo "APP_KEY=base64:your-app-key-here\n";
        echo "APP_DEBUG=true\n";
        echo "APP_URL=http://localhost:8000\n\n";
        echo "DB_CONNECTION=mysql\n";
        echo "DB_HOST=127.0.0.1\n";
        echo "DB_PORT=3306\n";
        echo "DB_DATABASE=toko_rt\n";
        echo "DB_USERNAME=root\n";
        echo "DB_PASSWORD=\n\n";
        echo "# MIDTRANS CONFIGURATION\n";
        echo "MIDTRANS_SERVER_KEY=SB-Mid-server-XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX\n";
        echo "MIDTRANS_CLIENT_KEY=SB-Mid-client-XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX\n";
        echo "MIDTRANS_IS_PRODUCTION=false\n";
        echo "MIDTRANS_IS_SANITIZED=true\n";
        echo "MIDTRANS_IS_3DS=true\n\n";
    }
} else {
    echo "✅ File .env ditemukan\n\n";

    // Baca file .env
    $envContent = file_get_contents($envFile);

    // Cek apakah Midtrans config sudah ada
    if (strpos($envContent, 'MIDTRANS_SERVER_KEY') !== false) {
        echo "✅ MIDTRANS_SERVER_KEY sudah ada di .env\n";
    } else {
        echo "❌ MIDTRANS_SERVER_KEY belum ada di .env\n";
        echo "📋 Tambahkan konfigurasi berikut ke file .env:\n\n";
        echo "# MIDTRANS CONFIGURATION\n";
        echo "MIDTRANS_SERVER_KEY=SB-Mid-server-XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX\n";
        echo "MIDTRANS_CLIENT_KEY=SB-Mid-client-XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX\n";
        echo "MIDTRANS_IS_PRODUCTION=false\n";
        echo "MIDTRANS_IS_SANITIZED=true\n";
        echo "MIDTRANS_IS_3DS=true\n\n";
    }

    if (strpos($envContent, 'MIDTRANS_CLIENT_KEY') !== false) {
        echo "✅ MIDTRANS_CLIENT_KEY sudah ada di .env\n";
    } else {
        echo "❌ MIDTRANS_CLIENT_KEY belum ada di .env\n";
    }
}

echo "=== CARA MENDAPATKAN MIDTRANS KEYS ===\n\n";
echo "1. Login ke Midtrans Dashboard: https://dashboard.midtrans.com\n";
echo "2. Pilih Environment: SANDBOX (untuk testing)\n";
echo "3. Buka menu Settings > Access Keys\n";
echo "4. Copy Server Key dan Client Key\n";
echo "5. Paste ke file .env\n\n";

echo "=== CONTOH KONFIGURASI ===\n\n";
echo "MIDTRANS_SERVER_KEY=SB-Mid-server-XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX\n";
echo "MIDTRANS_CLIENT_KEY=SB-Mid-client-XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX\n";
echo "MIDTRANS_IS_PRODUCTION=false\n\n";

echo "=== SETUP SELESAI ===\n";
echo "Setelah mengatur .env, jalankan:\n";
echo "php artisan config:clear\n";
echo "php artisan cache:clear\n\n";

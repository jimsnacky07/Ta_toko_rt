<?php
/**
 * Test Script untuk Route Midtrans
 * 
 * Script ini untuk testing apakah route midtrans sudah terdaftar dengan benar
 * setelah perbaikan yang dilakukan.
 */

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Route;

echo "=== TEST ROUTE MIDTRANS ===\n\n";

// Test route yang diperlukan
$requiredRoutes = [
    'user.user.midtrans.create-snap-token',
    'user.user.midtrans.finish',
    'user.user.midtrans.unfinish', 
    'user.user.midtrans.error',
    'midtrans.notification'
];

echo "Testing Required Routes:\n";
foreach ($requiredRoutes as $routeName) {
    if (Route::has($routeName)) {
        echo "✅ Route '{$routeName}': EXISTS\n";
        
        // Get route details
        $route = Route::getRoutes()->getByName($routeName);
        if ($route) {
            $methods = implode('|', $route->methods());
            $uri = $route->uri();
            echo "   - Methods: {$methods}\n";
            echo "   - URI: {$uri}\n";
        }
    } else {
        echo "❌ Route '{$routeName}': MISSING\n";
    }
    echo "\n";
}

// Test controller methods exist
echo "Testing Controller Methods:\n";

// Test CheckoutController::createSnapToken
try {
    $controller = new \App\Http\Controllers\User\CheckoutController();
    $reflection = new ReflectionMethod($controller, 'createSnapToken');
    echo "✅ CheckoutController::createSnapToken: EXISTS\n";
} catch (Exception $e) {
    echo "❌ CheckoutController::createSnapToken: " . $e->getMessage() . "\n";
}

// Test MidtransController methods
$midtransMethods = ['finish', 'unfinish', 'error', 'notification'];
foreach ($midtransMethods as $method) {
    try {
        $controller = new \App\Http\Controllers\User\MidtransController();
        $reflection = new ReflectionMethod($controller, $method);
        echo "✅ MidtransController::{$method}: EXISTS\n";
    } catch (Exception $e) {
        echo "❌ MidtransController::{$method}: " . $e->getMessage() . "\n";
    }
}

echo "\n=== SUMMARY ===\n";
$allRoutesExist = true;
foreach ($requiredRoutes as $routeName) {
    if (!Route::has($routeName)) {
        $allRoutesExist = false;
        break;
    }
}

if ($allRoutesExist) {
    echo "✅ All required routes are registered correctly!\n";
    echo "✅ Payment system should work now.\n";
} else {
    echo "❌ Some routes are missing. Please check the route configuration.\n";
}

echo "\n=== TEST COMPLETED ===\n";

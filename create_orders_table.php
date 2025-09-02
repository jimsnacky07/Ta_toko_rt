<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;

try {
    echo "Creating orders and order_items tables...\n";
    
    // Drop existing tables if they exist (to recreate fresh)
    Schema::dropIfExists('order_items');
    Schema::dropIfExists('orders');
    
    // Create orders table
    Schema::create('orders', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->string('kode_pesanan')->unique();
        $table->string('status')->default('pending');
        $table->decimal('total_amount', 10, 2);
        $table->timestamp('paid_at')->nullable();
        $table->timestamps();
        
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        $table->index(['user_id', 'status']);
        $table->index('created_at');
    });
    
    echo "✓ Orders table created\n";
    
    // Create order_items table
    Schema::create('order_items', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('order_id');
        $table->unsignedBigInteger('product_id');
        $table->string('garment_type');
        $table->string('fabric_type');
        $table->string('size');
        $table->decimal('price', 10, 2);
        $table->integer('quantity');
        $table->decimal('total_price', 10, 2);
        $table->text('special_request')->nullable();
        $table->timestamps();
        
        $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
    });
    
    echo "✓ Order_items table created\n";
    
    // Test creating an order
    $order = \App\Models\Order::create([
        'user_id' => 1,
        'kode_pesanan' => 'TEST' . time(),
        'status' => 'paid',
        'total_amount' => 150000,
        'paid_at' => now(),
    ]);
    
    echo "✓ Test order created with ID: {$order->id}\n";
    
    // Create order item
    \App\Models\OrderItem::create([
        'order_id' => $order->id,
        'product_id' => 1,
        'garment_type' => 'Ready Made',
        'fabric_type' => 'Cotton',
        'size' => 'M',
        'price' => 150000,
        'quantity' => 1,
        'total_price' => 150000,
        'special_request' => 'Test order item'
    ]);
    
    echo "✓ Test order item created\n";
    echo "\n✅ Database setup completed successfully!\n";
    echo "Orders table and test data created.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

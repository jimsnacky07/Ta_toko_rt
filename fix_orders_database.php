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
    echo "Fixing orders database structure...\n";
    
    // Check if orders table exists
    if (!Schema::hasTable('orders')) {
        echo "Creating orders table...\n";
        
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
    } else {
        echo "✓ Orders table already exists\n";
        
        // Check if paid_at column exists
        if (!Schema::hasColumn('orders', 'paid_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->timestamp('paid_at')->nullable()->after('total_amount');
            });
            echo "✓ Added paid_at column to orders table\n";
        }
    }
    
    // Check if order_items table exists
    if (!Schema::hasTable('order_items')) {
        echo "Creating order_items table...\n";
        
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('garment_type');
            $table->string('fabric_type');
            $table->string('size');
            $table->decimal('price', 10, 2);
            $table->integer('quantity');
            $table->decimal('total_price', 10, 2);
            $table->text('special_request')->nullable();
            $table->timestamps();
            
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            if (Schema::hasTable('products')) {
                $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
            }
        });
        
        echo "✓ Order_items table created\n";
    } else {
        echo "✓ Order_items table already exists\n";
    }
    
    // Test creating an order for current user
    $user = \App\Models\User::first();
    if ($user) {
        $testOrder = \App\Models\Order::create([
            'user_id' => $user->id,
            'order_code' => 'TEST-' . time(),
            'status' => 'paid',
            'total_amount' => 85000,
            'paid_at' => now(),
        ]);
        
        echo "✓ Test order created with ID: {$testOrder->id} for user: {$user->name}\n";
        
        // Create test order item
        \App\Models\OrderItem::create([
            'order_id' => $testOrder->id,
            'product_id' => 1,
            'garment_type' => 'Kemeja Cowok',
            'fabric_type' => 'Cotton',
            'size' => 'M',
            'price' => 85000,
            'quantity' => 1,
            'total_price' => 85000,
            'special_request' => 'Test order from payment'
        ]);
        
        echo "✓ Test order item created\n";
    }
    
    echo "\n✅ Database setup completed successfully!\n";
    echo "Tables: orders, order_items\n";
    echo "Test order created and should appear in Pesanan Saya\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

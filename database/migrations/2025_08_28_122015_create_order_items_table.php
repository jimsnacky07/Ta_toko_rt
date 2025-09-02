<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('order_items')) {
            Schema::create('order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
                $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
                
                // Detail produk
                $table->string('product_name');
                $table->string('image')->nullable();
                $table->decimal('price', 12, 2);
                $table->integer('quantity')->default(1);
                $table->decimal('total_price', 12, 2);
                
                // Spesifikasi pesanan
                $table->string('size')->nullable(); // S, M, L, XL
                $table->string('color')->nullable();
                $table->string('garment_type')->nullable();
                $table->string('fabric_type')->nullable();
                $table->text('special_request')->nullable();
                
                // Status item
                $table->enum('status', ['menunggu','diproses','siap','selesai','dibatalkan'])
                      ->default('menunggu');
                
                $table->timestamps();
                $table->index(['order_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};

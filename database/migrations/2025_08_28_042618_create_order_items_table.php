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
                $table->unsignedBigInteger('product_id')->nullable();
                $table->string('garment_type');
                $table->string('fabric_type');
                $table->string('size');
                $table->decimal('price', 12, 2);
                $table->integer('quantity');
                $table->decimal('total_price', 12, 2);
                $table->text('special_request')->nullable();
                $table->string('image')->nullable();
                $table->string('status')->default('pending');
                $table->timestamps();
                
                $table->index(['order_id']);
                $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};

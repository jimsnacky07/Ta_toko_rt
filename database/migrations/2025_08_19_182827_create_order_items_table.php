<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
       Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id'); // Relasi ke tabel orders
            $table->unsignedBigInteger('product_id'); // Relasi ke tabel products
            $table->string('garment_type'); // Jenis pakaian
            $table->string('fabric_type'); // Jenis kain
            $table->string('size'); // Ukuran produk
            $table->decimal('price', 10, 2); // Harga per item
            $table->integer('quantity'); // Jumlah produk yang dibeli
            $table->decimal('total_price', 10, 2); // Total harga
            $table->string('special_request')->nullable(); // Permintaan khusus
            $table->timestamps();

            // Foreign key untuk order_id dan product_id
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};

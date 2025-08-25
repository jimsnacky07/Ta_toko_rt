<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            // Relasi ke pesanan
            $table->foreignId('order_id')->constrained()->cascadeOnDelete(); // Tabel orders

            // Produk atau jasa yang dipesan
            $table->foreignId('product_id')->constrained()->cascadeOnDelete(); // Tabel products (bisa produk jahitan atau kain)

            // Jika pesanan adalah layanan jahitan khusus (misalnya baju, celana, jas, dll)
            $table->string('garment_type')->nullable(); // Jenis pakaian (misalnya 'jas', 'kemeja', 'celana')

            // Detail tambahan untuk produk/jasa (misalnya jenis kain, ukuran)
            $table->string('fabric_type')->nullable(); // Jenis kain (misalnya 'katun', 'denim')
            $table->string('size')->nullable(); // Ukuran pakaian (misalnya 'S', 'M', 'L')

            // Harga per unit dari produk atau jasa
            $table->unsignedBigInteger('price');

            // Jumlah produk atau layanan jahitan yang dipesan
            $table->unsignedInteger('quantity')->default(1);

            // Total harga per item (harga * quantity)
            $table->unsignedBigInteger('total_price');

            // Catatan khusus (misalnya permintaan jahitan khusus)
            $table->text('special_request')->nullable();

            // Timestamps
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};

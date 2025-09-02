<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('kode_pesanan')->unique();
                $table->string('order_code')->unique()->nullable(); // Alias untuk konsistensi
                $table->enum('status', ['menunggu','diproses','siap-diambil','selesai','dibatalkan'])
                      ->default('menunggu');
                $table->decimal('total_harga', 12, 2)->default(0);
                $table->decimal('total_amount', 12, 2)->default(0); // Alias untuk konsistensi

                $table->string('metode_pembayaran')->nullable();
                $table->string('bukti_pembayaran')->nullable();

                $table->string('nama_pengiriman')->nullable();
                $table->string('no_telp_pengiriman')->nullable();
                $table->string('alamat_pengiriman', 500)->nullable();
                $table->string('kota_pengiriman')->nullable();
                $table->string('kecamatan_pengiriman')->nullable();
                $table->string('kode_pos_pengiriman', 10)->nullable();
                $table->text('catatan')->nullable();
                $table->timestamp('paid_at')->nullable();

                $table->timestamps();
                $table->index(['user_id','status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

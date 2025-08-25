<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customer_request', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesanan_id')
                  ->constrained('pesanan')
                  ->cascadeOnDelete();
            $table->string('catatan_khusus')->nullable();
            $table->string('jenis_bahan')->nullable();
            $table->string('metode_penyimpanan')->nullable();
            $table->string('referensi_foto')->nullable(); // bisa simpan path file foto
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_request');
    }
};

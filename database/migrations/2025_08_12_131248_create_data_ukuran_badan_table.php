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
        Schema::create('data_ukuran_badan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pesanan_id');  // Menyimpan ID pesanan yang terhubung
            $table->integer('lingkaran_siku')->nullable();
            $table->integer('lingkaran_dada')->nullable();
            $table->integer('lingkaran_kaki_bawah')->nullable();
            $table->integer('lingkaran_leher')->nullable();
            $table->integer('lingkaran_lutut')->nullable();
            $table->integer('lingkaran_paha')->nullable();
            $table->integer('lingkaran_panjang_lengan')->nullable();
            $table->integer('lingkaran_pinggang')->nullable();
            $table->integer('lingkaran_pinggul')->nullable();
            $table->integer('lingkaran_ujung_tangan')->nullable();
            $table->integer('panjang_celana')->nullable();
            $table->integer('panjang_bahu')->nullable();
            $table->integer('panjang_baju')->nullable();
            $table->integer('panjang_pisik')->nullable();
            $table->integer('panjang_rok')->nullable();
            $table->integer('panjang_tangan')->nullable();
            $table->timestamps();

            // Set foreign key untuk hubungan dengan pesanan
            $table->foreign('pesanan_id')->references('id')->on('pesanan')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_ukuran_badan');
    }
};

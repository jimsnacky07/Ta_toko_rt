<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('image', 255)->nullable();
            $table->integer('price');
            
            // Kolom utama
            $table->string('kategory', 100)->nullable();
            $table->string('bahan', 100)->nullable();
            $table->string('motif', 100)->nullable();
            $table->string('dikirim_dari', 100)->nullable();
            $table->text('deskripsi')->nullable();
            $table->text('deskripsi_ukuran')->nullable();
            
            // Kolom tambahan untuk seeder
            $table->text('description')->nullable();
            $table->string('warna', 255)->nullable();
            $table->string('ukuran', 255)->nullable();
            $table->string('colors', 255)->nullable();
            $table->string('sizes', 255)->nullable();
            $table->string('fabric_type', 100)->nullable();
            $table->boolean('is_preorder')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

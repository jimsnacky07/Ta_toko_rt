<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('data_ukuran_badan')) {
            Schema::create('data_ukuran_badan', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->integer('lingkaran_dada')->nullable();
                $table->integer('lingkaran_pinggang')->nullable();
                $table->integer('lingkaran_pinggul')->nullable();
                $table->integer('lingkaran_leher')->nullable();
                $table->integer('lingkaran_lengan')->nullable();
                $table->integer('lingkaran_paha')->nullable();
                $table->integer('lingkaran_lutut')->nullable();
                $table->integer('panjang_baju')->nullable();
                $table->integer('panjang_lengan')->nullable();
                $table->integer('panjang_celana')->nullable();
                $table->integer('panjang_rok')->nullable();
                $table->integer('lebar_bahu')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('data_ukuran_badan');
    }
};

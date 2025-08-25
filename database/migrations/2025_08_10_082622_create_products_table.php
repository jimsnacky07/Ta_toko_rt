<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id(); // bigint unsigned auto_increment primary
            $table->string('name', 255);
            $table->string('image', 255)->nullable();
            $table->integer('price');

            // kolom tambahan
            $table->string('kategory', 100)->nullable();
            $table->string('bahan', 100)->nullable();
            $table->string('motif', 100)->nullable();
            $table->string('dikirim_dari', 100)->nullable();
            $table->text('deskripsi_ukuran')->nullable();
            $table->text('deskripsi')->nullable();

            $table->timestamps(); // created_at & updated_at
        });
    }

   public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('description'); // Menghapus kolom description jika rollback
        });
    }
    
};

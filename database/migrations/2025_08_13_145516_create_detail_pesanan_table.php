<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
   public function up()
   {
       Schema::table('detail_pesanan', function (Blueprint $table) {
           // Hapus foreign key yang lama jika ada
           $table->dropForeign(['product_id']); 

           // Menambahkan foreign key yang baru
           $table->foreign('product_id')
                 ->references('id')
                 ->on('products')
                 ->onDelete('cascade');
       });
   }

   public function down(): void
   {
       Schema::table('detail_pesanan', function (Blueprint $table) {
           // Hapus foreign key jika rollback migrasi
           $table->dropForeign(['product_id']);
       });
   }
};

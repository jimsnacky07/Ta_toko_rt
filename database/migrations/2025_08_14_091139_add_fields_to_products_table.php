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
        Schema::table('products', function (Blueprint $table) {
            $table->string('kategory', 100)->nullable()->after('price');
            $table->string('bahan', 100)->nullable()->after('kategory');
            $table->string('motif', 100)->nullable()->after('bahan');
            $table->string('dikirim_dari', 100)->nullable()->after('motif');
            $table->text('deskripsi_ukuran')->nullable()->after('dikirim_dari');
            $table->text('deskripsi')->nullable()->after('deskripsi_ukuran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'kategory',
                'bahan',
                'motif',
                'dikirim_dari',
                'deskripsi_ukuran',
                'deskripsi',
            ]);
        });
    }
};

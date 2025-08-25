<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Menambahkan kolom baru
            $table->text('description')->nullable()->after('price');
            $table->string('category', 100)->nullable()->after('description');
            $table->string('material', 100)->nullable()->after('category');
            $table->string('pattern', 100)->nullable()->after('material');
            $table->string('ship_from', 100)->nullable()->after('pattern');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Menghapus kolom jika migration dibatalkan
            $table->dropColumn(['description', 'category', 'material', 'pattern', 'ship_from']);
        });
    }
};

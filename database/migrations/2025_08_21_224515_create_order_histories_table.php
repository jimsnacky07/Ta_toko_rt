<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_histories', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained()->cascadeOnDelete();
            $t->foreignId('changed_by')->constrained('users')->cascadeOnDelete();
            $t->enum('from_status', ['QUEUE','IN_PROGRESS','DONE'])->nullable();
            $t->enum('to_status', ['QUEUE','IN_PROGRESS','DONE']);
            $t->text('note')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_histories');
    }
};

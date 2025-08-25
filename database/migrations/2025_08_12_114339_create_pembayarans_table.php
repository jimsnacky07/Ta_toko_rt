<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePembayaranTable extends Migration
{
    public function up()
    {
        Schema::create('pembayaran', function (Blueprint $t) {
            $t->id();

            // relasi ke orders
            $t->foreignId('order_id')->constrained()->cascadeOnDelete();

            // jumlah rupiah (integer) versi Midtrans
            $t->unsignedBigInteger('gross_amount')->default(0);

            // kalau mau tetap simpan jumlah versi lama (decimal), biarkan saja:
            $t->decimal('jumlah', 8, 2)->nullable();

            // metode pembayaran internal kamu (misal: transfer, kartu kredit)
            $t->string('payment_method')->nullable();

            // status internal lama (opsional dipertahankan)
            $t->enum('status', ['pending','completed','failed'])->default('pending');

            // ===== kolom tambahan untuk Midtrans =====
            $t->string('transaction_id')->nullable();
            $t->string('payment_type')->nullable();            // bank_transfer, qris, gopay, ...
            $t->string('va_number')->nullable();               // nomor VA (kalau VA)
            $t->string('bank')->nullable();                    // BCA/BNI/BRI/...
            $t->string('transaction_status')->nullable();      // settlement/pending/expire/cancel/deny/...
            $t->json('raw')->nullable();                       // payload Midtrans utuh
            // =========================================

            $t->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pembayaran');
    }
}

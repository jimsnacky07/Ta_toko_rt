<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;

    protected $table = 'pembayaran';

    // kolom-kolom yang boleh diisi mass-assignment
    protected $fillable = [
        // punyamu
        'pesanan_id',       // jika masih pakai model Pesanan lama
        'order_id',         // relasi ke Order baru (singular)
        'jumlah',
        'metode',
        'transaction_status',
        'snap_token',

        // opsional kalau nanti kamu tambahkan kolom midtrans:
        // 'transaction_id','payment_type','va_number','bank','gross_amount','raw','payment_method','status',
    ];

    // kalau nanti punya kolom 'raw' (JSON midtrans), aktifkan cast ini:
    // protected $casts = ['raw' => 'array'];

    /** Relasi ke model Pesanan (lama) */
    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'pesanan_id');
    }

    /** Relasi ke model Order (baru, singular) */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id'); // pastikan ada app/Models/Order.php
    }
}

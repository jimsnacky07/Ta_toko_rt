<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    /**
     * Catatan:
     * - 'jumlah' = nilai lama (decimal atau int) yang kamu pakai.
     * - 'gross_amount' = nilai rupiah (integer) versi Midtrans.
     *   Keduanya boleh hidup berdampingan untuk kompatibilitas.
     */
    protected $fillable = [
        // Punyamu (lokal)
        'order_id',
        'jumlah',           // total pembayaran versi lama
        'payment_method',   // metode lama (transfer/kartu dll)
        'status',           // pending/completed/failed (lama)

        // Tambahan integrasi Midtrans
        'transaction_id',
        'payment_type',     // bank_transfer, qris, gopay, ...
        'va_number',        // nomor VA (kalau VA)
        'bank',             // BCA/BNI/BRI/...
        'transaction_status', // settlement/pending/expire/cancel/deny/...
        'gross_amount',     // rupiah (integer)
        'raw',              // payload JSON dari Midtrans
    ];

    protected $casts = [
        'raw' => 'array',
        // (opsional) kalau kamu ingin pastikan grosir integer:
        // 'gross_amount' => 'integer',
    ];

    /** Relasi balik ke Order */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}

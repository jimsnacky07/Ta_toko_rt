<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    // tabel default 'orders' sudah benar, tak perlu $table

    protected $fillable = [
        'user_id',
        'kode_pesanan',
        'order_code',
        'status',
        'total_harga',
        'total_amount',
        'metode_pembayaran',
        'bukti_pembayaran',
        'nama_pengiriman',
        'no_telp_pengiriman',
        'alamat_pengiriman',
        'kota_pengiriman',
        'kecamatan_pengiriman',
        'kode_pos_pengiriman',
        'catatan',
        'paid_at',
        'tailor_id',
    ];

    protected $casts = [
        'total_harga' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'tailor_id' => 'integer',
    ];

    // Relasi
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tailor()
    {
        return $this->belongsTo(User::class, 'tailor_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    // Alias untuk konsistensi
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    /**
     * Sinkronisasi status order_items dengan status order
     */
    public function syncOrderItemsStatus()
    {
        $statusMapping = [
            'menunggu' => 'menunggu',
            'diproses' => 'diproses',
            'siap-diambil' => 'siap-diambil',
            'selesai' => 'selesai',
            'dibatalkan' => 'dibatalkan'
        ];

        $newStatus = $statusMapping[$this->status] ?? 'menunggu';

        $this->orderItems()->update(['status' => $newStatus]);

        \Illuminate\Support\Facades\Log::info("Synced order items status for order {$this->id} to {$newStatus}");
    }
}

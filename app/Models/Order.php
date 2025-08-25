<?php
// app/Models/Order.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'order_code', 'status', 'total_amount', 'paid_at'
    ];

    /**
     * Relasi ke order_items (setiap order memiliki banyak order item)
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class); // Relasi satu ke banyak
    }

    /**
     * Relasi ke User (setiap order dimiliki oleh satu user)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

     public function updateOrderAfterPayment()
    {
        // Misalnya, mengupdate kolom total_amount atau informasi lainnya setelah pembayaran
        $this->total_amount = $this->orderItems()->sum('total_price'); // Menghitung total harga berdasarkan item di order
        $this->save();
    }
}

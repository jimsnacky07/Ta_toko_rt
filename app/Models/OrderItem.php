<?php
// app/Models/OrderItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'product_id', 'garment_type', 'fabric_type', 'size', 'price', 'quantity', 'total_price', 'special_request'
    ];

    /**
     * Relasi ke Order (setiap order item terkait dengan satu order)
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relasi ke Product (setiap order item terkait dengan satu produk)
     */
    public function product()
    {
        return $this->belongsTo(Product::class); // Relasi ke produk yang dipesan
    }
}

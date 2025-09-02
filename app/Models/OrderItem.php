<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * @property int $id
 * @property int $pesanan_id
 * @property int|null $product_id
 * @property string|null $garment_type
 * @property string|null $fabric_type
 * @property string|null $size
 * @property string $price
 * @property int $quantity
 * @property string $total_price
 * @property string|null $special_request
 * @property string|null $image
 * @property string|null $status
 */
class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'garment_type',
        'fabric_type',
        'size',
        'price',
        'quantity',
        'total_price',
        'special_request',
        'image',
        'status',
    ];

    protected $casts = [
        'price'       => 'decimal:2',
        'total_price' => 'decimal:2',
        'quantity'    => 'integer',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    // Event model — pakai booted + type-hint biar IDE nggak merah
    protected static function booted(): void
    {
        static::created(function (OrderItem $orderItem): void {
            Log::info('Order item created', [
                'order_item_id' => $orderItem->id,
                'order_id'      => $orderItem->order_id,
                'product_id'    => $orderItem->product_id,
                'quantity'      => $orderItem->quantity,
                'total_price'   => $orderItem->total_price,
            ]);
        });

        static::updated(function (OrderItem $orderItem): void {
            Log::info('Order item updated', [
                'order_item_id' => $orderItem->id,
                'order_id'      => $orderItem->order_id,
                'changes'       => $orderItem->getChanges(),
            ]);
        });
    }

    // Relasi
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

}

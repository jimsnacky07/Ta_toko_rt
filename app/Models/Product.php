<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage; // <— untuk konversi path storage -> URL

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'name','price','image',
        'kategori','bahan','motif','dikirim_dari',
        'deskripsi',
        // tambahkan kolom lain kalau nanti ada
    ];

    /**
     * Optional casts:
     * Aman walaupun kolomnya tidak ada—Laravel akan mengabaikan yang tidak ada nilainya.
     */
    protected $casts = [
        'price'       => 'integer',
        'is_preorder' => 'boolean',
        'colors'      => 'array',   // kalau suatu saat simpan JSON
        'sizes'       => 'array',
        'spec'        => 'array',
    ];

    /**
     * Accessor: selalu hasilkan URL gambar yang valid.
     * - Jika value sudah http(s), pakai apa adanya
     * - Jika path di storage/app/public/..., ubah jadi /storage/...
     * - Jika kosong, fallback ke placeholder
     */
    public function getImageUrlAttribute(): string
    {
        $img = $this->image ?? '';
        if (!$img) {
            return asset('images/placeholder.png');
        }
        if (is_string($img) && str_starts_with($img, 'http')) {
            return $img;
        }
        return Storage::url($img); // butuh: php artisan storage:link
    }

    public function detailPesanan()
    {
        return $this->hasMany(DetailPesanan::class, 'product_id', 'id')->onDelete('cascade');
    }

     public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}

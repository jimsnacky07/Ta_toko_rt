<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPesanan extends Model
{
    protected $table = 'detail_pesanan';
    protected $fillable = [
        'pesanan_id', 'product_id', 'jumlah', 'harga_satuan', 'total_harga', 'catatan_khusus'
    ];

    /**
     * Relasi dengan pesanan
     */
    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'pesanan_id', 'id');
    }

    /**
     * Relasi dengan produk
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}

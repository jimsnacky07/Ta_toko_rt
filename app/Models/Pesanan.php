<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pesanan extends Model
{
    use HasFactory;

    protected $table = 'pesanan'; // Nama tabel pesanan
    protected $fillable = [
        'user_id', 'order_date', 'status', 'total_harga', 'nama_pelanggan', 'telepon_pelanggan'
    ];

    protected $casts = ['order_date' => 'date'];

    // Relasi dengan User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    // Relasi dengan DetailPesanan (1 pesanan bisa memiliki banyak detail pesanan)
    public function details()
    {
        return $this->hasMany(DetailPesanan::class, 'pesanan_id', 'id');
    }

    // Relasi dengan Pembayaran
    public function pembayarans()
    {
        return $this->hasMany(Pembayaran::class, 'pesanan_id', 'id');
    }

    // Relasi dengan DataUkuranBadan
    public function ukuran()
    {
        return $this->hasOne(DataUkuranBadan::class, 'pesanan_id', 'id');
    }

    // Relasi dengan CustomerRequest
    public function customerRequest()
    {
        return $this->hasOne(CustomerRequest::class, 'pesanan_id', 'id');
    }

    // Relasi dengan Produk (via detail_pesanan)
    public function products()
    {
        return $this->belongsToMany(Product::class, 'detail_pesanan', 'pesanan_id', 'product_id')
                    ->withPivot(['jumlah', 'harga_satuan', 'total_harga']);
    }

    public const STATUSES = ['menunggu', 'diproses', 'siap-diambil', 'selesai', 'dibatalkan'];
}

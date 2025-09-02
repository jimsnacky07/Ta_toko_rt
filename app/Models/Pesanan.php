<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pesanan extends Model
{
    use HasFactory;

    protected $table = 'pesanan'; // Nama tabel pesanan
    
    protected $fillable = [
        'user_id',
        'kode_pesanan',
        'status',
        'total_harga',
        'metode_pembayaran',
        'bukti_pembayaran',
        'nama_pengiriman',
        'no_telp_pengiriman',
        'alamat_pengiriman',
        'kota_pengiriman',
        'kecamatan_pengiriman',
        'kode_pos_pengiriman',
        'catatan'
    ];

    protected $casts = [
        'total_harga' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

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

    // Relasi dengan OrderItem
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'pesanan_id');
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
   public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');  // Menghubungkan dengan kolom product_id
    }

    public const STATUSES = ['menunggu', 'diproses', 'siap-diambil', 'selesai', 'dibatalkan'];
}

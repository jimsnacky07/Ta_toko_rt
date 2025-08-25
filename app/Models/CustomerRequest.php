<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerRequest extends Model
{
    protected $table = 'customer_request';
    protected $fillable = ['pesanan_id','catatan_khusus','jenis_bahan','metode_penyimpanan','referensi_foto'];

    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'pesanan_id', 'id');
    }
}

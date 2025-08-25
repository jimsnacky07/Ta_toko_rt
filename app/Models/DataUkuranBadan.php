<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataUkuranBadan extends Model
{
    use HasFactory;

    protected $table = 'data_ukuran_badan';  // Nama tabel yang sesuai

    protected $fillable = [
        'pesanan_id', 
        'lingkaran_siku', 
        'lingkaran_dada',
        'lingkaran_kaki_bawah',
        'lingkaran_leher',
        'lingkaran_lutut',
        'lingkaran_paha',
        'lingkaran_panjang_lengan',
        'lingkaran_pinggang',
        'lingkaran_pinggul',
        'lingkaran_ujung_tangan',
        'panjang_celana',
        'panjang_bahu',
        'panjang_baju',
        'panjang_pisik',
        'panjang_rok',
        'panjang_tangan'
    ];

    // Relasi dengan tabel pesanan
    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class);
    }
}

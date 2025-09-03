<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataUkuranBadan extends Model
{
    use HasFactory;

    protected $table = 'data_ukuran_badan';  // Nama tabel yang sesuai

    protected $fillable = [
        // Kolom yang benar sesuai skema toko_rt.sql
        'user_id',
        'lingkaran_dada',
        'lingkaran_pinggang',
        'lingkaran_pinggul',
        'lingkaran_leher',
        'lingkaran_lengan',
        'lingkaran_paha',
        'lingkaran_lutut',
        'panjang_baju',
        'panjang_lengan',
        'panjang_celana',
        'panjang_rok',
        'lebar_bahu',
    ];
}

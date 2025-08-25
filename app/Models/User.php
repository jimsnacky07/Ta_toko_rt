<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nama',        // nama pelanggan
        'email',       // email login / kontak
        'no_telp',     // nomor telepon
        'alamat',      // alamat lengkap
        'password',    // password login
        'level',       // admin / pelanggan / dll
        'role_id',     // relasi ke tabel roles (jika pakai role terpisah)
    ];

    /**
     * Relasi ke tabel pesanan
     * 1 user bisa punya banyak pesanan
     */
    public function pesanan()
    {
        return $this->hasMany(Pesanan::class);
        return $this->hasMany(Pesanan::class, 'user_id');
    }

    /**
     * Alias relasi orders (jika mau pakai nama lain)
     */
    public function orders()
    {
        return $this->hasMany(Pesanan::class);
    }

    /**
     * Mengambil level user
     */
    public function getLevel()
    {
        return $this->level;
    }

    
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TailorSeeder extends Seeder
{
    public function run(): void
    {
        $tailors = [
            [
                'nama' => 'Siti Nurhaliza',
                'email' => 'siti@tailor.com',
                'password' => Hash::make('zxcvbnm123'),
                'level' => 'tailor',
                'no_telp' => '081234567890',
                'alamat' => 'Jakarta Selatan',
                'email_verified_at' => now(),
            ],
            [
                'nama' => 'Ahmad Tailor',
                'email' => 'ahmad@tailor.com', 
                'password' => Hash::make('zxcvbnm123'),
                'level' => 'tailor',
                'no_telp' => '081234567891',
                'alamat' => 'Jakarta Pusat',
                'email_verified_at' => now(),
            ],
            [
                'nama' => 'Rina Penjahit',
                'email' => 'rina@tailor.com',
                'password' => Hash::make('zxcvbnm123'),
                'level' => 'tailor', 
                'no_telp' => '081234567892',
                'alamat' => 'Jakarta Timur',
                'email_verified_at' => now(),
            ]
        ];

        foreach ($tailors as $tailor) {
            User::updateOrCreate(
                ['email' => $tailor['email']],
                $tailor
            );
        }
    }
}

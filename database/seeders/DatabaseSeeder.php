<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1 Taylor
        User::create([
            'email' => 'tailor@example.com',
            'password' => Hash::make('123'),
            'nama' => 'Tailor',
            'no_telp' => '081234567890',
            'alamat' => 'Jl. Tailor',
            'level' => 'tailor',
        ]);

        // 1 Admin
        User::create([
            'email' => 'admin@example.com',
            'password' => Hash::make('123'),
            'nama' => 'Admin',
            'no_telp' => '081234567891',
            'alamat' => 'Jl. Admin',
            'level' => 'admin',
        ]);

        // 28 Users
        User::factory(28)->create();

        $this->call([
        \Database\Seeders\DemoOrderSeeder::class,
    ]);
    
    }
}

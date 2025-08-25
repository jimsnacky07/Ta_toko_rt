<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('products')->insert([
            'name' => 'Kemeja Cowok Polos Lengan Panjang',
            'image' => 'images/baju kemeja cowok 2.jpg',
            'price' => 85000,
        ]);

        DB::table('products')->insert([
            'name' => 'Rok Ungu Shimmer',
            'image' => 'images/rok 4.jpg',
            'price' => 160000,
        ]);

        DB::table('products')->insert([
            'name' => 'Rok Beige Shimmer',
            'image' => 'images/rok 6.jpg',
            'price' => 120000,
        ]);

        DB::table('products')->insert([
            'name' => 'Rok Brukat Maron',
            'image' => 'images/rok 5 (1).jpg',
            'price' => 198000,
        ]);

        DB::table('products')->insert([
            'name' => 'Workshirt kemeja lengan pendek',
            'image' => 'images/baju kemeja cowok 4.jpg',
            'price' => 180000,
        ]);

        DB::table('products')->insert([
            'name' => 'Rok Susun Ruffel',
            'image' => 'images/rok 3.jpg',
            'price' => 180000,
        ]);

        DB::table('products')->insert([
            'name' => 'Rok Jenne Susun',
            'image' => 'images/rok 2.jpg',
            'price' => 60000,
        ]);

        DB::table('products')->insert([
            'name' => 'Kemeja Garis Cowok linen',
            'image' => 'images/baju kemeja cowok 1.jpg',
            'price' => 90000,
        ]);

        DB::table('products')->insert([
            'name' => 'Rok Coqueete',
            'image' => 'images/rok 8.jpg',
            'price' => 150000,
        ]);

        DB::table('products')->insert([
            'name' => 'Kemeja Levis Cowok',
            'image' => 'images/baju kemeja cowok 3.jpg',
            'price' => 220000,
        ]);

        DB::table('products')->insert([
            'name' => 'Rok Brukat Hijau',
            'image' => 'images/rok 5 (3).jpg',
            'price' => 198000,
        ]);

        DB::table('products')->insert([
            'name' => 'Rok Brukat Hitam',
            'image' => 'images/rok 5(2).jpg',
            'price' => 198000,
        ]);

        DB::table('products')->insert([
            'name' => 'Rok Brukat cream',
            'image' => 'images/rok 5 (4).jpg',
            'price' => 198000,
        ]);

        DB::table('products')->insert([
            'name' => 'Celana Coklat Formal',
            'image' => 'images/celana 1.jpg',
            'price' => 170000,
        ]);

        DB::table('products')->insert([
            'name' => 'Celana Cream Casual',
            'image' => 'images/celana 2.jpg',
            'price' => 160000,
        ]);

        DB::table('products')->insert([
            'name' => 'Celana Linen',
            'image' => 'images/celana 3.jpg',
            'price' => 78000,
        ]);

        DB::table('products')->insert([
            'name' => 'Celana Slim Fit Hitam',
            'image' => 'images/celana 4.jpg',
            'price' => 179000,
        ]);

        DB::table('products')->insert([
            'name' => 'Celana Chino Light',
            'image' => 'images/celana 5.jpg',
            'price' => 174000,
        ]);

        DB::table('products')->insert([
            'name' => 'Celana Wide Black',
            'image' => 'images/celana 6.jpg',
            'price' => 180000,
        ]);

        DB::table('products')->insert([
            'name' => 'Jas Wanita navy',
            'image' => 'images/jas.jpg',
            'price' => 310000,
        ]);

        DB::table('products')->insert([
            'name' => 'Jas Wanita Abu-Abu Elegan',
            'image' => 'images/jas cewek 2.jpg',
            'price' => 350000,
        ]);

        DB::table('products')->insert([
            'name' => 'kebaya Taro',
            'image' => 'images/kebaya.jpg',
            'price' => 350000,
        ]);

        DB::table('products')->insert([
            'name' => 'Jas Wanita Abu-Abu Gelap',
            'image' => 'images/jas cewek 3.jpg',
            'price' => 380000,
        ]);

        DB::table('products')->insert([
            'name' => 'Jas Wanita Simpel Mahogany',
            'image' => 'images/jas cewek 4.jpg',
            'price' => 350000,
        ]);

        DB::table('products')->insert([
            'name' => 'Jas Wanita Mahogany',
            'image' => 'images/jas cewek 5.jpg',
            'price' => 350000,
        ]);

        DB::table('products')->insert([
            'name' => 'Jas Wanita Hijau Zaitun',
            'image' => 'images/jas cewek 6.jpg',
            'price' => 350000,
        ]);

        DB::table('products')->insert([
            'name' => 'Jas Wanita Army',
            'image' => 'images/jas cewek 7.jpg',
            'price' => 450000,
        ]);

        DB::table('products')->insert([
            'name' => 'Jas Wanita Cream Elegan',
            'image' => 'images/jas cewek 8.jpg',
            'price' => 400000,
        ]);

        DB::table('products')->insert([
            'name' => 'Jas Wanita Biru Navy',
            'image' => 'images/jas cewek 9.jpg',
            'price' => 400000,
        ]);

        DB::table('products')->insert([
            'name' => 'Jas Wanita Hitam Modern',
            'image' => 'images/jas cewek 10.jpg',
            'price' => 410000,
        ]);

        DB::table('products')->insert([
            'name' => 'Baju Kemeja Cowok Cream',
            'image' => 'images/baju kemeja cowok 2.jpg',
            'price' => 100000,
        ]);

        DB::table('products')->insert([
            'name' => 'Baju Kemeja Cowok Lengan Panjang Abu-Abu',
            'image' => 'images/baju kemeja cowok 5.jpg',
            'price' => 100000,
        ]);

        DB::table('products')->insert([
            'name' => 'Baju Kemeja Cowok Lengan Pendek Navy',
            'image' => 'images/kemeja cowok.jpg',
            'price' => 100000,
        ]);

        DB::table('products')->insert([
            'name' => 'Baju Kemeja Cowok Navy',
            'image' => 'images/baju kemeja  cowok 5.jpg',
            'price' => 100000,
        ]);
    }
}

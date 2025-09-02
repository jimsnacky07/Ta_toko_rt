<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Product;
use App\Models\Pesanan;
use App\Models\DetailPesanan;

class DemoOrderSeeder extends Seeder
{
    public function run(): void
    {
        // 1) User
        $user = User::first() ?? User::create([
            'name' => 'Pembeli Demo',
            'email' => 'demo@example.com',
            'password' => Hash::make('password'),
        ]);

        // 2) Siapkan mapping kolom untuk products
        $cols = Schema::getColumnListing('products');

        $nameKey = collect(['name','title','nama','nama_produk','product_name'])->first(fn($k)=>in_array($k,$cols)) ?? null;
        $priceKey = collect(['price','harga','harga_satuan','unit_price'])->first(fn($k)=>in_array($k,$cols)) ?? null;
        $descKey = collect(['description','deskripsi','detail'])->first(fn($k)=>in_array($k,$cols)) ?? null;

        $payload = [];
        if ($nameKey) $payload[$nameKey] = 'Kemeja Custom';
        if ($priceKey) $payload[$priceKey] = 150000;
        if ($descKey) $payload[$descKey] = 'Contoh produk demo';

        // Tambahkan nilai default kolom NOT NULL lain kalau ada
        foreach ($cols as $c) {
            if (!in_array($c, array_keys($payload)) && !in_array($c, ['id','created_at','updated_at'])) {
                // kalau ada kolom NOT NULL tanpa default, isi nilai aman:
                $payload[$c] = $payload[$c] ?? (str_contains($c,'stok') ? 10 : (str_contains($c,'gambar') ? null : null));
            }
        }

        $product = Product::first() ?? Product::create($payload);

        // 3) Pesanan
        $pesanan = Pesanan::create([
            'user_id'               => $user->id,
            'kode_pesanan'         => 'ORD-' . now()->format('Ymd') . '-' . rand(1000, 9999),
            'status'               => 'pending',
            'total_harga'          => 150000,
            'metode_pembayaran'    => 'transfer_bank',
            'nama_pengiriman'      => $user->name,
            'no_telp_pengiriman'   => '081234567890',
            'alamat_pengiriman'    => 'Jl. Contoh No. 123',
            'kota_pengiriman'      => 'Jakarta',
            'kecamatan_pengiriman' => 'Kebayoran Baru',
            'kode_pos_pengiriman'  => '12120',
            'catatan'              => 'Pesanan demo',
        ]);

        // 4) Detail Pesanan
        $qty = 2; $harga = 150000; $total = $qty * $harga;

        DetailPesanan::create([
            'pesanan_id'   => $pesanan->id,
            'product_id'   => $product->id,
            'jumlah'       => $qty,
            'harga_satuan' => $harga,
            'total_harga'  => $total,
            'catatan_khusus' => 'Lengan panjang, jahitan rapat',
        ]);

        // 5) Update total pesanan
        $pesanan->update([
            'total_harga' => $pesanan->detailPesanan()->sum('total_harga'),
        ]);
    }
}

<?php

namespace App\Http\Controllers\User;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage; // untuk konversi path storage -> URL

class ProductController extends Controller
{
    public function dashboard()
    {
        // Ambil 5 produk terbaru
        $products = Product::latest()->limit(5)->get();

        // Kirim ke view dashboard
        return view('user.dashboard', compact('products'));
    }

    public function show(int $id)
    {
        $product = Product::findOrFail($id);

        // --- Normalisasi image menjadi URL yang valid ---
        $rawImage = $product->image ?? $product->photo ?? $product->thumbnail ?? '';
        if (!empty($rawImage)) {
            if (is_string($rawImage) && str_starts_with($rawImage, 'http')) {
                $imageUrl = $rawImage; // sudah URL
            } else {
                // gunakan asset() langsung untuk path images/
                $imageUrl = asset($rawImage);
            }
        } else {
            $imageUrl = asset('images/placeholder.png'); // fallback aman
        }

        // --- Deskripsi: dukung beberapa nama kolom umum di admin ---
        $desc = $product->description
            ?? $product->deskripsi
            ?? $product->detail
            ?? $product->body
            ?? '';

        // --- Spesifikasi: ambil dari JSON 'spec' atau rangkai dari kolom terpisah ---
        $spec = $this->toAssocArray($product->spec ?? []);

        // Kolom-kolom terpisah yang sering ada di halaman admin
        $mapCols = [
            'Bahan'        => ['bahan', 'material', 'fabric'],
            'Motif'        => ['motif', 'pattern'],
            'Dikirim Dari' => ['dikirim_dari', 'asal_pengiriman', 'kota_pengirim'],
            'Kategori'     => ['kategory', 'category'],
            'Warna'        => ['warna'],
            'Ukuran'       => ['ukuran', 'size_label', 'deskripsi_ukuran'],
        ];
        foreach ($mapCols as $label => $candidates) {
            foreach ($candidates as $col) {
                if (isset($product->{$col}) && $product->{$col} !== null && $product->{$col} !== '') {
                    $val = $product->{$col};
                    if (is_string($val) && str_contains($val, ',')) {
                        // rapikan jika CSV
                        $val = implode(', ', array_filter(array_map('trim', explode(',', $val))));
                    }
                    $spec[$label] = $val;
                    break;
                }
            }
        }

        // Bentuk payload $p sesuai yang dipakai di Blade kamu
        $p = [
            'name'        => $product->name,                        // tetap milikmu
            'price'       => $product->price,                       // tetap milikmu
            'image'       => $imageUrl,                             // >>> perbaikan URL image
            'is_preorder' => $product->is_preorder ?? false,        // tetap milikmu

            // colors/sizes bisa disimpan sebagai string "merah,hitam" atau JSON; amankan keduanya
            'colors'      => $this->toArrayFromMixed($product->colors),
            'sizes'       => $this->toArrayFromMixed($product->sizes),

            // spec bisa JSON (key-value), atau dari kolom terpisah → keduanya ditangani
            'spec'        => $spec,

            // deskripsi: dukung beberapa nama kolom
            'desc'        => $desc,
        ];

        return view('user.product.show', compact('p'));
    }

    /**
     * Helper: terima null/string/array -> array numerik (untuk colors/sizes)
     */
    private function toArrayFromMixed($value): array
    {
        if (is_array($value)) return $value;

        if (is_string($value)) {
            // Coba decode JSON
            $json = json_decode($value, true);
            if (is_array($json)) {
                // Pastikan list numerik
                return array_values($json);
            }
            // Fallback: pecah dengan koma
            return array_values(array_filter(array_map('trim', explode(',', $value))));
        }

        return [];
    }

    private function toAssocArray($value): array
    {
        if (is_array($value)) return $value;

        if (is_string($value)) {
            $json = json_decode($value, true);
            return is_array($json) ? $json : [];
        }

        return [];
    }

    /**
     * Helper: untuk mengonversi nilai harga dari format "Rp 120.000" ke integer
     */
    private function toInt($value): int
    {
        if (is_int($value)) return $value;
        if (is_numeric($value)) return (int)$value;
        return (int) preg_replace('/[^\d]/', '', (string) $value);
    }

    /**
     * Tambahkan produk ke keranjang
     */
    public function tambahKeKeranjang(Request $r, $productId)
    {
        // Ambil data produk dari database
        $product = Product::findOrFail($productId);

        // Ambil harga dan qty
        $qty = max(1, (int) $r->input('qty', 1));
        $harga = $this->toInt($product->price);

        // Simpan produk ke keranjang (session)
        $keranjang = $r->session()->get('keranjang', []);
        $keranjang[] = [
            'id'         => 'prod:' . $productId,
            'product_id' => $productId,
            'nama'       => $product->name,
            'harga'      => $harga,
            'qty'        => $qty,
            'gambar'     => Storage::url($product->image),  // Pastikan gambar valid
        ];
        $r->session()->put('keranjang', $keranjang);

        return redirect()->route('keranjang.index')->with('success', 'Produk berhasil ditambahkan ke keranjang.');
    }
}

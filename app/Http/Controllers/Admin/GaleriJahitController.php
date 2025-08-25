<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GaleriJahitController extends Controller
{
    // List + Search (prioritas nama diawali query, fallback mengandung)
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $products = Product::when($q !== '', function ($query) use ($q) {
                $starts   = $q.'%';
                $contains = '%'.$q.'%';

                $query->where(function ($w) use ($starts, $contains) {
                    $w->where('name', 'like', $starts)
                      ->orWhere('name', 'like', $contains)
                      ->orWhere('deskripsi', 'like', $contains);
                })->orderByRaw(
                    "CASE WHEN name LIKE ? THEN 0 WHEN name LIKE ? THEN 1 ELSE 2 END, name ASC",
                    [$starts, $contains]
                );
            })
            ->latest('id') // saat q kosong
            ->paginate(12)
            ->withQueryString();

        return view('admin.galeri-jahit.index', compact('products','q'));
    }

    public function create()
    {
        return view('admin.galeri-jahit.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama'         => 'required|string|max:200',
            'harga'        => 'required|integer|min:0',
            'deskripsi'    => 'nullable|string',
            'gambar'       => 'nullable|image|max:2048',
            'kategori'     => 'nullable|string|max:100',
            'bahan'        => 'nullable|string|max:100',
            'motif'        => 'nullable|string|max:100',
            'dikirim_dari' => 'nullable|string|max:100',
        ]);

        if ($request->hasFile('gambar')) {
            $data['gambar'] = $request->file('gambar')->store('pakaian', 'public');
        }

        $payload = [
            'name'         => $data['nama'],
            'price'        => $data['harga'],
            'deskripsi'    => $data['deskripsi']    ?? null,
            'image'        => $data['gambar']       ?? null,
            'kategori'     => $data['kategori']     ?? null,
            'bahan'        => $data['bahan']        ?? null,
            'motif'        => $data['motif']        ?? null,
            'dikirim_dari' => $data['dikirim_dari'] ?? null,
        ];

        $payload = array_filter($payload, fn ($v) => !is_null($v));

        Product::create($payload);

        return redirect()->route('admin.galeri.jahit.index')
                         ->with('ok', 'Pakaian berhasil ditambahkan.');
    }

    public function show(Product $product)
    {
        return view('admin.galeri-jahit.show', ['item' => $product]);
    }

    public function edit(Product $product)
    {
        return view('admin.galeri-jahit.edit', ['item' => $product]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'nama'         => 'required|string|max:200',
            'harga'        => 'required|integer|min:0',
            'deskripsi'    => 'nullable|string',
            'gambar'       => 'nullable|image|max:2048',
            'kategori'     => 'nullable|string|max:100',
            'bahan'        => 'nullable|string|max:100',
            'motif'        => 'nullable|string|max:100',
            'dikirim_dari' => 'nullable|string|max:100',
        ]);

        if ($request->hasFile('gambar')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['gambar'] = $request->file('gambar')->store('pakaian', 'public');
        }

        $payload = [
            'name'         => $data['nama'],
            'price'        => $data['harga'],
            'kategori'     => $data['kategori']     ?? null,
            'bahan'        => $data['bahan']        ?? null,
            'motif'        => $data['motif']        ?? null,
            'dikirim_dari' => $data['dikirim_dari'] ?? null,
            'deskripsi'    => $data['deskripsi']    ?? null,
        ];

        if (isset($data['gambar'])) {
            $payload['image'] = $data['gambar'];
        }

        $payload = array_filter($payload, fn ($v) => !is_null($v));

        $product->update($payload);

        return redirect()->route('admin.galeri.jahit.index')
                         ->with('ok', 'Pakaian berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        $product->delete();

        return back()->with('ok', 'Pakaian dihapus.');
    }
}

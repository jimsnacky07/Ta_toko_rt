<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PelangganController extends Controller
{
    /**
     * GET /admin/pelanggan
     * Default: hanya user level "user" yang SUDAH punya pesanan.
     * ?all=1  -> tampilkan semua user level "user" (meski belum pernah pesan).
     */
    public function index(Request $request)
    {
        $query = User::query()
            ->where('level', 'user')     // filter hanya pelanggan (bukan admin/tailor)
            ->withCount(['orders', 'pesanan']); // Count both new and old orders

        if (! $request->boolean('all')) {
            // default: hanya yang punya pesanan atau orders
            $query->where(function($q) {
                $q->whereHas('orders')
                  ->orWhereHas('pesanan');
            });
        }

        // pesanan terakhir untuk kolom status/aksi di tabel
        $pelanggan = $query
            ->with([
                'orders' => fn($q) => $q->latest()->limit(1),
                'pesanan' => fn($q) => $q->latest()->limit(1),
                // kalau perlu status bayar detail, bisa eager load juga:
                // 'pesanan.pembayarans'
            ])
            ->orderBy('email')
            ->get();

        // kirim dua alias variabel agar kompatibel dengan view lama/baru
        return view('admin.pelanggan.index', [
            'pelanggan' => $pelanggan,
            'customers' => $pelanggan,
        ]);
    }

    /**
     * GET /admin/pelanggan/{id}
     * Detail 1 pelanggan + semua pesanannya (terbaru dulu)
     */
    public function show($id)
    {
        $customer = User::where('level', 'user')
            ->with([
                'orders' => function ($q) {
                    $q->latest()
                      ->with(['orderItems.product']); // new order items & products
                },
                'pesanan' => function ($q) {
                    $q->latest()
                      ->with(['detailPesanan.product']); // legacy order items & products
                },
            ])
            ->findOrFail($id);

        return view('admin.pelanggan.show', compact('customer'));
    }

    /**
     * DELETE /admin/pelanggan/{id}
     * Hapus pelanggan. Pastikan FK di migrations sudah onDelete('cascade')
     * untuk otomatis menghapus pesanan & detailnya. Kalau belum, gunakan
     * komentar di bawah untuk hapus manual.
     */
    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            $customer = User::where('level', 'user')->findOrFail($id);

            // Jika FK belum cascade, bisa pakai hapus manual:
            // foreach ($customer->pesanan as $p) {
            //     $p->detailPesanan()->delete();
            //     $p->pembayarans()->delete();
            //     $p->delete();
            // }

            $customer->delete();
        });

        // nama route index-mu: admin.pelanggan
        return redirect()
            ->route('admin.pelanggan')
            ->with('success', 'Pelanggan berhasil dihapus.');
    }
}

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
        $excludedStatuses = ['dibatalkan', 'cancelled', 'canceled', 'batal'];

        $query = User::query()
            ->where('level', 'user')     // filter hanya pelanggan (bukan admin/tailor)
            ->withCount(['orders' => function ($q) use ($excludedStatuses) {
                $q->whereNotIn('status', $excludedStatuses);
            }]); // Hitung pesanan tanpa yang dibatalkan

        if (! $request->boolean('all')) {
            // default: hanya yang punya pesanan atau orders
            $query->where(function ($q) use ($excludedStatuses) {
                $q->whereHas('orders', function ($qo) use ($excludedStatuses) {
                    $qo->whereNotIn('status', $excludedStatuses);
                });
                // ->orWhereHas('pesanan');
            });
        }

        // pesanan terakhir untuk kolom status/aksi di tabel
        $pelanggan = $query
            ->with([
                'orders' => fn($q) => $q->whereNotIn('status', $excludedStatuses)->latest()->limit(1)->with([
                    // Ambil item pesanan terbaru untuk status penjemputan
                    'orderItems' => fn($qi) => $qi->latest('id')
                ]),
            ])
            ->orderBy('email')
            ->get();

        // Tambahkan properti ringkas untuk dipakai di view
        $pelanggan->transform(function ($u) {
            $lastOrder = $u->orders->first();

            $u->last_payment_method = $lastOrder->metode_pembayaran ?? null;
            $u->last_pickup_status  = optional(optional($lastOrder)->orderItems->first())->status;
            return $u;
        });

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
                //'pesanan' => function ($q) {
                //     $q->latest()
                //         ->with(['detailPesanan.product']); // legacy order items & products
                // },
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

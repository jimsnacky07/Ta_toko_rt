<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// MODELS BARU
use App\Models\Order;
use App\Models\Tailor;

// MODELS LEGACY (kalau masih dipakai)
use App\Models\Pesanan;

class OrderController extends Controller
{
    /* =========================================================
     *  A. VERSI BARU — ORDERS (+ assign tailor)
     * ========================================================= */

    /**
     * Daftar orders baru (dengan filter q dan status)
     * View: resources/views/admin/orders/index.blade.php
     */
    public function index(Request $request)
    {
        $q      = $request->input('q');
        $status = $request->input('status');

        $orders = Order::with(['user','payment','tailor'])
            ->when($q, function ($query) use ($q) {
                $query->whereHas('user', function ($u) use ($q) {
                        $u->where('name', 'like', "%{$q}%")
                          ->orWhere('email', 'like', "%{$q}%");
                    })
                    ->orWhere('id', $q)
                    ->orWhere('order_id', 'like', "%{$q}%")
                    ->orWhere('order_code', 'like', "%{$q}%")
                    ->orWhereDate('created_at', $q);
            })
            ->when($status, fn($query) => $query->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $tailors = Tailor::orderBy('name')->get();

        return view('admin.pesanan.index', compact('orders','tailors','q','status'));
    }

    /**
     * Detail order baru (opsional kalau kamu punya halaman show untuk order baru)
     * View: resources/views/admin/orders/show.blade.php
     */
    public function showOrder(Order $order)
    {
        $order->load([
            'user',
            'payment',
            'tailor',
            'histories',
            'orderItems',
            'detailPesanan',
        ]);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Tetapkan tailor ke sebuah order (baru)
     */
    public function assign(Request $r, $orderId)
    {
        $r->validate([
            'tailor_id' => 'required|exists:tailors,id',
        ]);

        $order = Order::where('order_id', $orderId)->firstOrFail();
        $order->tailor_id = $r->tailor_id;
        $order->save();

        return back()->with('ok', 'Tailor ditetapkan');
    }

    /* =========================================================
     *  B. VERSI LEGACY — PESANAN (kode kamu sebelumnya)
     * ========================================================= */

    /**
     * Daftar pesanan (legacy)
     * View: resources/views/admin/pesanan/index.blade.php
     */
    public function indexPesanan(Request $request)
    {
        $q      = $request->input('q');
        $status = $request->input('status');

        $pesanans = Pesanan::with('user')
            ->when($q, function ($query) use ($q) {
                $query->whereHas('user', fn($sub) => $sub->where('name','like',"%{$q}%"))
                      ->orWhere('id', $q)
                      ->orWhereDate('order_date', $q);
            })
            ->when($status, fn($query) => $query->where('status',$status))
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('admin.pesanan.index', [
            'pesanans' => $pesanans,
            'q'        => $q,
            'status'   => $status,
            'statuses' => \App\Models\Pesanan::STATUSES,
        ]);
    }

    /**
     * Detail pesanan (legacy)
     * View: resources/views/admin/pesanan/show.blade.php
     */
    public function showPesanan(Pesanan $pesanan)
    {
        $pesanan->load([
            'user',
            'detailPesanan.product',
            'pembayarans',
            'ukuran',
            'customerRequest'
        ]);

        return view('admin.pesanan.show', compact('pesanan'));
    }

    /**
     * Update status pesanan (legacy)
     */
    public function updatePesananStatus(Request $request, Pesanan $pesanan)
    {
        $validated = $request->validate([
            'status' => ['required','in:'.implode(',', \App\Models\Pesanan::STATUSES)],
        ]);

        $pesanan->update(['status' => $validated['status']]);

        return back()->with('success','Status pesanan diperbarui.');
    }
}

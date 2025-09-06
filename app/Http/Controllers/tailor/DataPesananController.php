<?php

namespace App\Http\Controllers\tailor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// ===== Skema Baru =====
use App\Models\Order;
use App\Models\Tailor;
use App\Models\OrderHistory;

// ===== Skema Legacy =====
use App\Models\Pesanan;

class DataPesananController extends Controller
{
    /* =========================================================
     *  A. SKEMA BARU — ORDER, TAILOR, ORDER_HISTORY
     * ========================================================= */

    /**
     * Daftar order yang ditugaskan ke tailor yang sedang login.
     * View: resources/views/tailor/data_pesanan.blade.php
     */
    public function index(Request $r)
    {
        // Mengambil semua pesanan dari database orders dengan relasi user
        $orders = Order::with(['user', 'orderItems'])
            ->latest()
            ->get();

        return view('tailor.data_pesanan', compact('orders'));
    }

    /**
     * Detail 1 order untuk tailor (beserta riwayat).
     * (pastikan kamu punya file: resources/views/tailor/pesanan/show.blade.php)
     */
    public function show(Request $r, $orderId)
    {
        $tailor = Tailor::where('user_id', $r->user()->id)->firstOrFail();

        $order = Order::with(['user','payment','tailor','histories','orderItems','detailPesanan'])
            ->where('order_id', $orderId)
            ->where('tailor_id', $tailor->id)
            ->firstOrFail();

        // Kalau file view detail kamu masih di tempat lama, samakan path-nya.
        return view('tailor.pesanan.show', compact('order'));
    }

    /**
     * Detail pesanan lengkap untuk tailor dengan data ukuran badan
     */
    public function showDetail($id)
    {
        $order = Order::with([
            'user.dataUkuranBadan',
            'orderItems'
        ])->findOrFail($id);

        return view('tailor.show', compact('order'));
    }

    /**
     * Update status pesanan dari tailor
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:menunggu,diproses,selesai,dibatalkan,siap-diambil'
        ]);

        $order = Order::findOrFail($id);
        $oldStatus = $order->status;
        $newStatus = $request->status;

        if ($oldStatus !== $newStatus) {
            $order->update(['status' => $newStatus]);
            
            // Sinkronisasi status order_items dengan status order
            $order->syncOrderItemsStatus();
            
            // Log perubahan status
            \Illuminate\Support\Facades\Log::info("Status order {$order->id} changed from {$oldStatus} to {$newStatus} by tailor, order_items status synced");
        }

        return back()->with('success', 'Status pesanan dan item berhasil diperbarui');
    }

    /**
     * Sinkronisasi manual status order_items dengan status order
     */
    public function syncOrderItemsStatus($id)
    {
        $order = Order::findOrFail($id);
        $order->syncOrderItemsStatus();
        
        return back()->with('success', 'Status item berhasil disinkronkan dengan status pesanan');
    }

    /**
     * Update status produksi (QUEUE/IN_PROGRESS/DONE) + catat ke order_histories.
     */
    public function updateProduction(Request $r, $orderId)
    {
        $r->validate([
            'to_status' => 'required|in:QUEUE,IN_PROGRESS,DONE',
            'note'      => 'nullable|string',
        ]);

        $tailor = Tailor::where('user_id', $r->user()->id)->firstOrFail();

        $order = Order::where('order_id', $orderId)
            ->where('tailor_id', $tailor->id)
            ->firstOrFail();

        $from = $order->production_status;
        $to   = $r->to_status;

        if ($from !== $to) {
            $order->production_status = $to;
            $order->save();

            OrderHistory::create([
                'order_id'    => $order->id,
                'changed_by'  => $r->user()->id, // user tailor yang login
                'from_status' => $from,
                'to_status'   => $to,
                'note'        => $r->note,
            ]);
        }

        return back()->with('ok', 'Status produksi diperbarui');
    }

    /* =========================================================
     *  B. SKEMA LEGACY — PESANAN
     * ========================================================= */

    /**
     * Daftar pesanan (legacy) yang statusnya "diproses oleh tailor".
     * View: resources/views/tailor/pesanan/data_pesanan.blade.php
     */
    public function indexLegacy()
    {
        $orders = Pesanan::where('status', 'diproses oleh tailor')
            ->whereHas('detailPesanan')
            ->get();

        // pakai view yang sama biar konsisten tampilannya
        return view('tailor.pesanan.data_pesanan', compact('orders'));
    }

    /**
     * Update status pesanan (legacy) -> selesai/diambil/menunggu.
     */
    public function updateStatusLegacy(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:selesai,diambil,menunggu',
        ]);

        $order = Pesanan::findOrFail($id);
        $order->status = $request->input('status');
        $order->save();

        return redirect()->route('tailor.data.pesanan')->with('success', 'Status pesanan berhasil diperbarui.');
    }
}

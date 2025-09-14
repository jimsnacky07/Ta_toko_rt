<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use App\Models\Order;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();

        // Status yang harus dikecualikan dari dashboard
        $excludedStatuses = ['dibatalkan', 'cancelled', 'canceled', 'batal'];

        // Total pesanan bulan ini (kecualikan dibatalkan)
        $ordersThisMonth = Order::whereYear('created_at', $now->year)
            ->whereMonth('created_at', $now->month)
            ->whereNotIn('status', $excludedStatuses)
            ->count();

        // Pesanan hari ini (opsional)
        $ordersToday = Order::whereDate('created_at', $now->toDateString())
            ->whereNotIn('status', $excludedStatuses)
            ->count();

        // Jumlah pesanan PROSES (pakai whereIn biar aman dengan variasi nama status)
        $inProcess = Order::whereIn('status', ['process', 'proses', 'processing', 'diproses'])->count();

        // Jumlah pesanan SELESAI
        $completed = Order::whereIn('status', ['selesai', 'completed', 'done', 'success'])->count();

        // (Opsional) pesanan menunggu/belum bayar
        $pending = Order::whereIn('status', ['pending', 'unpaid', 'waiting', 'menunggu'])->count();

        // Jumlah pelanggan (opsional)
        $customers = User::count();

        // 5 pesanan terbaru buat tabel ringkas
        $latestOrders = Order::whereNotIn('status', $excludedStatuses)
            ->latest()
            ->take(5)
            ->get(['id', 'user_id', 'status', 'created_at']);

        return view('admin.dashboard', compact(
            'ordersThisMonth',
            'ordersToday',
            'inProcess',
            'completed',
            'pending',
            'customers',
            'latestOrders'
        ));
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use App\Models\Pesanan;   // pastikan modelnya benar
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();

        // Total pesanan bulan ini
        $ordersThisMonth = Pesanan::whereYear('created_at', $now->year)
            ->whereMonth('created_at', $now->month)
            ->count();

        // Pesanan hari ini (opsional)
        $ordersToday = Pesanan::whereDate('created_at', $now->toDateString())->count();

        // Jumlah pesanan PROSES (pakai whereIn biar aman dengan variasi nama status)
        $inProcess = Pesanan::whereIn('status', ['process', 'proses', 'processing'])->count();

        // Jumlah pesanan SELESAI
        $completed = Pesanan::whereIn('status', ['selesai', 'completed', 'done', 'success'])->count();

        // (Opsional) pesanan menunggu/belum bayar
        $pending = Pesanan::whereIn('status', ['pending', 'unpaid', 'waiting'])->count();

        // Jumlah pelanggan (opsional)
        $customers = User::count();

        // 5 pesanan terbaru buat tabel ringkas
        $latestOrders = Pesanan::latest()->take(5)->get(['id','user_id','status','created_at']);

        return view('admin.dashboard', compact(
            'ordersThisMonth', 'ordersToday', 'inProcess', 'completed', 'pending', 'customers', 'latestOrders'
        ));
    }
}

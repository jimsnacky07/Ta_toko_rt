<?php

// app/Http/Controllers/Tailor/DashboardController.php

namespace App\Http\Controllers\Tailor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Tailor;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $r)
    {
        // Hitung berdasarkan semua order (tanpa filter tailor)
        $myOrders    = Order::where('status', '!=', 'dibatalkan')->count();
        $pending     = Order::where('status', 'menunggu')->count();
        $inProgress  = Order::where('status', 'diproses')->count();
        $readyToPick = Order::where('status', 'siap-diambil')->count();
        $completed   = Order::where('status', 'selesai')->count();

        // 10 pesanan terbaru untuk tabel bawah
        $recentOrders = Order::with(['user', 'orderItems'])
            ->where('status', '!=', 'dibatalkan')
            ->latest('created_at')
            ->limit(10)
            ->get();

        return view('tailor.dashboard', compact('myOrders', 'inProgress', 'completed', 'pending', 'readyToPick', 'recentOrders'));
    }
}

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
        // Identitas tailor saat ini
        $tailor = Order::where('user_id', $r->user()->id)->first();

        // Scope untuk order yang ditugaskan ke tailor ini (jika ada data tailor)
        $orderQuery = Order::query();
        if ($tailor) {
            $orderQuery->where('tailor_id', $tailor->id);
        }

        // Angka ringkasan
        $myOrders   = (clone $orderQuery)->count();
        $inProgress = (clone $orderQuery)->where('status', 'diproses')->count();
        $completed  = (clone $orderQuery)->where('status', 'selesai')->count();
        $pending    = (clone $orderQuery)->where('status', 'menunggu')->count();

        // 10 pesanan terbaru untuk tabel bawah
        $recentOrders = (clone $orderQuery)
            ->with(['user', 'orderItems'])
            ->latest('created_at')
            ->limit(10)
            ->get();

        return view('tailor.dashboard', compact('myOrders', 'inProgress', 'completed', 'pending', 'recentOrders'));
    }
}

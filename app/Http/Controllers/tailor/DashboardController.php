<?php

// app/Http/Controllers/Tailor/DashboardController.php

namespace App\Http\Controllers\Tailor;

use App\Http\Controllers\Controller;
use App\Models\Order as Pesanan;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Ambil data yang dibutuhkan untuk dashboard
        $ordersThisMonth = Pesanan::whereMonth('created_at', now()->month)->count();
        $customers = \App\Models\User::where('level', 'user')->count(); // Hitung jumlah pelanggan
        $inProcess = Pesanan::where('status', 'diproses')->count();
        $completed = Pesanan::where('status', 'selesai')->count();
        $pending = Pesanan::where('status', 'menunggu')->count();

        return view('tailor.dashboard', compact('ordersThisMonth', 'customers', 'inProcess', 'completed', 'pending'));
    }
}
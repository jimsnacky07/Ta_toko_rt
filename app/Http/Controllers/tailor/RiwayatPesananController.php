<?php

namespace App\Http\Controllers\Tailor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

class RiwayatPesananController extends Controller
{
    public function index()
    {
        // Mengambil semua pesanan dari database orders dengan relasi user
        $orders = Order::with(['user', 'orderItems'])
            ->latest()
            ->get();

        // Mengirimkan data pesanan ke tampilan riwayat_pesanan
        return view('tailor.riwayat_pesanan', compact('orders'));
    }
}

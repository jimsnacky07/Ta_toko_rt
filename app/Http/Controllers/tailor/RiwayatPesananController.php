<?php

namespace App\Http\Controllers\Tailor;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;  // Import model Pesanan
use Illuminate\Http\Request;

class RiwayatPesananController extends Controller
{
    public function index()
    {
        // Mengambil pesanan yang dikerjakan oleh tailor berdasarkan status tertentu
        $orders = Pesanan::where('status', 'diproses oleh tailor')  // Pastikan status sesuai
            ->whereHas('detailPesanan', function($query) {
                // Filter detail pesanan untuk tailor yang sesuai
            })
            ->get();

        // Mengirimkan data pesanan ke tampilan riwayat_pesanan
        return view('tailor.riwayat_pesanan', compact('orders'));
        
    }
}

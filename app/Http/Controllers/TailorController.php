<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;

class TailorController extends Controller
{
    public function dashboard()
    {
        $tailorId = auth()->id();
        
        $myOrders = Order::where('tailor_id', $tailorId)->count();
        $inProgress = Order::where('tailor_id', $tailorId)->where('status', 'diproses')->count();
        $completed = Order::where('tailor_id', $tailorId)->where('status', 'selesai')->count();
        $pending = Order::where('tailor_id', $tailorId)->where('status', 'menunggu')->count();
        
        $recentOrders = Order::with(['user', 'orderItems'])
            ->where('tailor_id', $tailorId)
            ->latest()
            ->take(5)
            ->get();
        
        return view('tailor.dashboard', compact('myOrders', 'inProgress', 'completed', 'pending', 'recentOrders'));
    }
    
    public function orders(Request $request)
    {
        $tailorId = auth()->id();
        $status = $request->get('status');
        
        $orders = Order::with(['user', 'orderItems'])
            ->where('tailor_id', $tailorId)
            ->when($status, fn($query) => $query->where('status', $status))
            ->latest()
            ->paginate(10);
        
        return view('tailor.orders', compact('orders', 'status'));
    }
    
    public function updateOrderStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:menunggu,diproses,siap-diambil,selesai'
        ]);
        
        // Pastikan order ini milik tailor yang login
        if ($order->tailor_id !== auth()->id()) {
            return back()->with('error', 'Anda tidak memiliki akses ke pesanan ini.');
        }
        
        $order->update(['status' => $request->status]);
        
        return back()->with('success', 'Status pesanan berhasil diperbarui.');
    }
}

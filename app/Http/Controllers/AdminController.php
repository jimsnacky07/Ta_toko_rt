<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;

class AdminController extends Controller
{
    public function dashboard()
    {
        $ordersThisMonth = Order::whereMonth('created_at', now()->month)->count();
        $customers = User::where('level', 'user')->count();
        $inProcess = Order::where('status', 'diproses')->count();
        $completed = Order::where('status', 'selesai')->count();
        $pending = Order::where('status', 'menunggu')->count();

        // Get recent orders with user information
        $recentOrders = Order::with(['user', 'orderItems'])
            ->latest()
            ->limit(10)
            ->get();

        // Get revenue this month
        $revenueThisMonth = Order::whereMonth('created_at', now()->month)
            ->where('status', '!=', 'cancelled')
            ->sum('total_amount');

        return view('admin.dashboard', compact(
            'ordersThisMonth',
            'customers',
            'inProcess',
            'completed',
            'pending',
            'recentOrders',
            'revenueThisMonth'
        ));
    }

    public function daftarPesanan(Request $request)
    {
        $q = $request->input('q');
        $status = $request->input('status');
        $type = $request->input('type', 'all'); // all, product, custom

        $orders = Order::with(['user', 'orderItems'])
            ->when($q, function ($query) use ($q) {
                $query->whereHas('user', function ($u) use ($q) {
                    $u->where('nama', 'like', "%{$q}%")
                        ->orWhere('nama', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                })
                    ->orWhere('id', $q)
                    ->orWhere('order_code', 'like', "%{$q}%")
                    ->orWhere('kode_pesanan', 'like', "%{$q}%");
            })
            ->when($status, fn($query) => $query->where('status', $status))
            ->when($type === 'product', function ($query) {
                $query->whereHas('orderItems', function ($q) {
                    $q->whereNotNull('product_id');
                });
            })
            ->when($type === 'custom', function ($query) {
                $query->whereHas('orderItems', function ($q) {
                    $q->whereNull('product_id')
                        ->orWhere('garment_type', 'like', '%custom%')
                        ->orWhere('garment_type', 'like', '%Order Custom%');
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $tailors = User::where('level', 'tailor')->orderBy('nama')->get();

        // Get counts for tabs
        $allCount = Order::count();
        $productCount = Order::whereHas('orderItems', function ($q) {
            $q->whereNotNull('product_id');
        })->count();
        $customCount = Order::whereHas('orderItems', function ($q) {
            $q->whereNull('product_id')
                ->orWhere('garment_type', 'like', '%custom%')
                ->orWhere('garment_type', 'like', '%Order Custom%');
        })->count();

        return view('admin.daftar-pesanan', compact(
            'orders',
            'tailors',
            'q',
            'status',
            'type',
            'allCount',
            'productCount',
            'customCount'
        ));
    }
}
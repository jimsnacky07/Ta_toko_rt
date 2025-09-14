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
        $excludedStatuses = ['dibatalkan', 'cancelled', 'canceled', 'batal'];
        $ordersThisMonth = Order::whereMonth('created_at', now()->month)
            ->whereNotIn('status', $excludedStatuses)
            ->count();
        $customers = User::where('level', 'user')->count();
        $inProcess = Order::where('status', 'diproses')->count();
        $completed = Order::where('status', 'selesai')->count();
        $pending = Order::where('status', 'menunggu')->count();
        $readyToPick = Order::where('status', 'siap-diambil')->count();

        // Get recent orders with user information
        $recentOrders = Order::with(['user', 'orderItems'])
            ->whereNotIn('status', $excludedStatuses)
            ->latest()
            ->limit(10)
            ->get();

        // Get revenue this month
        $revenueThisMonth = Order::whereMonth('created_at', now()->month)
            ->whereNotIn('status', $excludedStatuses)
            ->sum('total_amount');

        return view('admin.dashboard', compact(
            'ordersThisMonth',
            'customers',
            'inProcess',
            'completed',
            'pending',
            'recentOrders',
            'revenueThisMonth',
            'readyToPick'
        ));
    }

    public function daftarPesanan(Request $request)
    {
        $q = $request->input('q');
        $status = $request->input('status');
        $type = $request->input('type', 'all'); // all, product, custom

        $excludedStatuses = ['dibatalkan', 'cancelled', 'canceled', 'batal'];

        $orders = Order::with(['user', 'orderItems'])
            ->whereNotIn('status', $excludedStatuses)
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
                $query->where(function ($qq) {
                    $qq->where('order_code', 'like', 'OP-%')
                        ->orWhere('kode_pesanan', 'like', 'OP-%')
                        ->orWhereHas('orderItems', function ($q) {
                            $q->whereNotNull('product_id');
                        });
                });
            })
            ->when($type === 'custom', function ($query) {
                $query->where(function ($qq) {
                    $qq->where('order_code', 'like', 'OC-%')
                        ->orWhere('kode_pesanan', 'like', 'OC-%')
                        ->orWhere(function ($qOr) {
                            // Hanya anggap custom jika BUKAN berprefix OP- dan item terlihat custom
                            $qOr->where('order_code', 'not like', 'OP-%')
                                ->where('kode_pesanan', 'not like', 'OP-%')
                                ->whereHas('orderItems', function ($q) {
                                    $q->whereNull('product_id')
                                        ->orWhere('garment_type', 'like', '%custom%')
                                        ->orWhere('garment_type', 'like', '%Order Custom%');
                                });
                        });
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $tailors = User::where('level', 'tailor')->orderBy('nama')->get();

        // Get counts for tabs
        $allCount = Order::whereNotIn('status', $excludedStatuses)->count();
        $productCount = Order::whereNotIn('status', $excludedStatuses)
            ->where(function ($q2) {
                $q2->where('order_code', 'like', 'OP-%')
                    ->orWhere('kode_pesanan', 'like', 'OP-%')
                    ->orWhereHas('orderItems', function ($q) {
                        $q->whereNotNull('product_id');
                    });
            })
            ->count();
        $customCount = Order::whereNotIn('status', $excludedStatuses)
            ->where(function ($q2) {
                $q2->where('order_code', 'like', 'OC-%')
                    ->orWhere('kode_pesanan', 'like', 'OC-%')
                    ->orWhere(function ($qOr) {
                        $qOr->where('order_code', 'not like', 'OP-%')
                            ->where('kode_pesanan', 'not like', 'OP-%')
                            ->whereHas('orderItems', function ($q) {
                                $q->whereNull('product_id')
                                    ->orWhere('garment_type', 'like', '%custom%')
                                    ->orWhere('garment_type', 'like', '%Order Custom%');
                            });
                    });
            })
            ->count();

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

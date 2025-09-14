<?php

namespace App\Http\Controllers;

// use App\Models\Pesanan; // Model lama, sekarang pakai Order
use App\Models\DataUkuranBadan;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;



class PesananController extends Controller
{
    /**
     * Buat order produk dan simpan ke database sebelum pembayaran Midtrans
     * POST /pesanan/store
     */
    public function store(Request $request)
    {
        Log::info('[PESANAN] Mulai proses pembuatan order produk', $request->all());

        Log::info('[PESANAN] [DEBUG] Request masuk ke store', $request->all());

        // Debug: cek apakah request method POST dan field product_id ada
        if ($request->method() !== 'POST') {
            Log::warning('[PESANAN] [DEBUG] Request bukan POST', ['method' => $request->method()]);
        }
        if (!$request->has('product_id')) {
            Log::warning('[PESANAN] [DEBUG] Field product_id tidak ada di request', $request->all());
        }

        // Validasi
        Log::info('[PESANAN] [DEBUG] Mulai validasi');
        $data = $request->validate([
            'product_id'   => 'required|integer|exists:products,id',
            'qty'          => 'required|integer|min:1',
            'size'         => 'nullable|string',
            'color'        => 'nullable|string',
            'notes'        => 'nullable|string',
        ]);

        Log::info('[PESANAN] Validasi sukses', $data);

        $product = \App\Models\Product::findOrFail($data['product_id']);
        $userId = Auth::id();
        $totalAmount = $product->price * $data['qty'];

        // Simpan order produk dengan kode berawalan OP-
        $order = Order::create([
            'user_id'           => $userId,
            'kode_pesanan'      => null, // diisi setelah dapat ID
            'order_code'        => null, // diisi setelah dapat ID
            'status'            => 'menunggu',
            'total_harga'       => $totalAmount,
            'total_amount'      => $totalAmount,
            'metode_pembayaran' => null,
        ]);

        // Update order_code dan kode_pesanan setelah ID didapat (OP-)
        $orderCode = 'OP-' . now()->format('YmdHis') . '-' . $order->id;
        $order->order_code = $orderCode;
        $order->kode_pesanan = $orderCode;
        $order->save();

        Log::info('[PESANAN] Order produk berhasil dibuat', [
            'order_id' => $order->id,
            'order_code' => $orderCode,
            'fields' => $order->toArray()
        ]);

        $item = \App\Models\OrderItem::create([
            'order_id'        => $order->id,
            'product_id'      => $product->id,
            'garment_type'    => $product->name,
            'fabric_type'     => $product->fabric_type ?? '-',
            'size'            => $data['size'] ?? null,
            'price'           => $product->price,
            'quantity'        => $data['qty'],
            'total_price'     => $totalAmount,
            'special_request' => $data['notes'] ?? null,
            'image'           => $product->image ?? null,
            'status'          => 'pending',
        ]);

        Log::info('[PESANAN] Order item produk berhasil dibuat', [
            'order_item_id' => $item->id,
            'order_id' => $order->id,
            'fields' => $item->toArray()
        ]);

        // Simpan order ke session pending_order (untuk Midtrans)
        $pendingOrderArr = [
            'user_id'      => $userId,
            'user_email'   => Auth::user() ? Auth::user()->email : null,
            'order_code'   => $orderCode,
            'total_amount' => $totalAmount,
            'items'        => [[
                'product_id'      => $product->id,
                'garment_type'    => $product->name,
                'fabric_type'     => $product->fabric_type ?? '-',
                'size'            => $data['size'] ?? null,
                'price'           => $product->price,
                'quantity'        => $data['qty'],
                'total_price'     => $totalAmount,
                'special_request' => $data['notes'] ?? null,
            ]],
            'selected_cart_ids' => [$product->id],
        ];
        session(['pending_order' => $pendingOrderArr]);
        Log::debug('[PESANAN] Simpan session pending_order', $pendingOrderArr);

        return redirect()->route('user.pesanan')->with('success', 'Order berhasil dibuat. Silakan lanjutkan pembayaran.');
    }

    // (Bisa dipakai kalau akses langsung /pesanan/konfirmasi tanpa form)
    /**
     * Update order status setelah respon dari Midtrans (webhook/callback)
     * POST /pesanan/update-status
     */
    public function updateStatus(Request $request)
    {
        $orderId = $request->input('order_id');
        $status  = $request->input('status');
        $paymentType = $request->input('payment_type');

        $order = Order::find($orderId);
        if (!$order) {
            Log::error('[PESANAN] Order tidak ditemukan saat update status', ['order_id' => $orderId]);
            return response()->json(['error' => 'Order not found'], 404);
        }

        $oldStatus = $order->status;
        $order->status = $status;
        if ($paymentType) {
            $order->metode_pembayaran = $paymentType;
        }
        $order->save();

        Log::info('[PESANAN] Status order diupdate setelah Midtrans', [
            'order_id' => $orderId,
            'old_status' => $oldStatus,
            'new_status' => $status,
            'payment_type' => $paymentType,
        ]);

        return response()->json(['ok' => true]);
    }



    public function showOrders(Request $request)
    {
        $tab = $request->get('tab', 'all');

        // Cek apakah tabel orders ada
        if (!\Illuminate\Support\Facades\Schema::hasTable('orders')) {
            $orders = collect();
            return view('user.pesanan.pusat_pesanan', compact('orders'));
        }

        $excludedStatuses = ['dibatalkan', 'cancelled', 'canceled', 'batal'];

        $q = Order::where('user_id', Auth::id())
            ->with('orderItems')
            ->whereNotIn('status', $excludedStatuses)
            ->latest();

        if ($tab === 'unpaid') {
            // status menunggu dipakai sebagai padanan unpaid
            $q->where('status', 'pending');
        }

        $orders = $q->get();

        return view('user.pesanan.pusat_pesanan', compact('orders'));
    }


    public function index(Request $request)
    {
        $userId = Auth::id();
        $user   = Auth::user();

        Log::info('PesananController index called', [
            'user_id' => $userId,
            'user_email' => $user?->email,
            'user_name' => $user->nama ?? 'No user',
            'session_id' => session()->getId(),
        ]);

        if (!Schema::hasTable('orders')) {
            Log::warning('Orders table does not exist');
            $orders = new \Illuminate\Pagination\LengthAwarePaginator(
                collect(),
                0,
                10,
                1,
                ['path' => request()->url()]
            );
            return view('user.pesanan.pusat_pesanan', compact('orders'));
        }

        try {
            DB::connection()->getPdo();
            Log::info('Database connection successful');
        } catch (\Throwable $e) {
            Log::error('Database connection failed', ['error' => $e->getMessage()]);
        }

        // ===== FIX DISINI: pakai alias kolom yg sesuai dengan DB =====
        $excludedStatuses = ['dibatalkan', 'cancelled', 'canceled', 'batal'];

        $query = Order::where('user_id', $userId)
            ->with('orderItems') // jangan batasi select agar FK aman (order_id/pesanan_id)
            ->select([
                'id',
                'user_id',
                'kode_pesanan as order_code', // alias dari kolom DB
                'status',
                'total_harga as total_amount', // alias dari kolom DB
                'created_at',
                'updated_at',
            ])
            ->whereNotIn('status', $excludedStatuses)
            ->latest();

        Log::info('Orders query', ['sql' => $query->toSql(), 'bindings' => $query->getBindings()]);

        $orders = $query->get();

        Log::info('Orders retrieved', [
            'user_id'      => $userId,
            'orders_count' => $orders->count(),
            'order_ids'    => $orders->pluck('id')->all(),
            'order_codes'  => $orders->pluck('order_code')->all(),
        ]);

        if ($orders->isEmpty()) {
            $allOrdersCount = Order::count();
            // ambil sample dgn nama kolom asli
            $sampleOrder = Order::select('id', 'user_id', 'kode_pesanan', 'status')->first();

            Log::warning('No orders found for user', [
                'user_id' => $userId,
                'total_orders_in_db' => $allOrdersCount,
                'sample_order' => $sampleOrder ? [
                    'id'          => $sampleOrder->id,
                    'user_id'     => $sampleOrder->user_id,
                    'order_code'  => $sampleOrder->kode_pesanan, // map manual
                    'status'      => $sampleOrder->status,
                ] : null,
            ]);
        }
        return view('user.pesanan.pusat_pesanan', compact('orders'));
    }

    public function handlePaymentSuccess(Request $request)
    {
        $orderId = $request->input('order_id'); // Mendapatkan order_id dari request
        $order = Order::findOrFail($orderId); // Mengambil data pesanan berdasarkan ID

        // Update status pesanan setelah pembayaran berhasil
        $order->status = 'PAID';
        $order->paid_at = now(); // Waktu pembayaran

        // Cek dan perbarui kolom 'amount' dan 'garment_type'
        if (!$order->amount) {
            // Menghitung total harga pesanan
            $order->amount = $this->calculateOrderAmount($order);
        }

        if (!$order->garment_type) {
            // Menentukan jenis pakaian berdasarkan item pesanan
            $order->garment_type = $this->getGarmentType($order);
        }

        // Simpan perubahan data pesanan
        $order->save();

        return redirect()->route('user.pusat-pesanan')->with('success', 'Pembayaran berhasil.');
    }

    // Fungsi untuk menghitung total harga pesanan
    private function calculateOrderAmount(Order $order)
    {
        $total = 0;
        foreach ($order->items as $item) {
            $total += $item->quantity * $item->price; // Menghitung berdasarkan harga per item dan jumlah
        }

        return $total;
    }

    // Fungsi untuk mendapatkan jenis pakaian berdasarkan item pesanan
    private function getGarmentType(Order $order)
    {
        $item = $order->items()->first(); // Ambil item pertama (sesuaikan dengan logika)
        return $item ? $item->garment_type : null;
    }
}

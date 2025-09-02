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
    public function pembayaran(Request $request)
    {
        // (Opsional) kalau memang ada pesanan_id
        $pesanan = $request->pesanan_id ? Order::find($request->pesanan_id) : null;
        $ukuran  = $pesanan ? DataUkuranBadan::where('pesanan_id', $pesanan->id)->first() : null;

        // >>> AMBIL ANGKA HARGA DARI FORM (hidden input)
        // Pastikan di form 'order_custom.blade.php' ada input hidden: base, add, unitTotal, grand
        $base      = (int) preg_replace('/[^\d]/', '', $request->input('base', 0));
        $add       = (int) preg_replace('/[^\d]/', '', $request->input('add', 0));
        $unitTotal = (int) preg_replace('/[^\d]/', '', $request->input('unitTotal', 0));
        $grand     = (int) preg_replace('/[^\d]/', '', $request->input('grand', 0));

        return view('user.pesanan.konfirmasi', [
            'jenis_pakaian'    => $request->jenis_pakaian,
            'jenis_kain'       => $request->jenis_kain,
            'request_customer' => $request->request,      // atau $request->request_customer kalau itu nama fieldnya
            'gambar'           => $request->gambar_custom,
            'ukuran'           => $ukuran,

            // >>> KIRIM KE VIEW (ini yang dipakai oleh konfirmasi.blade.php)
            'base'       => $base,
            'add'        => $add,
            'unitTotal'  => $unitTotal,
            'grand'      => $grand,
        ]);
    }

    // (Bisa dipakai kalau akses langsung /pesanan/konfirmasi tanpa form)
    public function konfirmasi()
    {
        return view('user.pesanan.konfirmasi');
    }

    // public function showOrders(Request $request)
    // {
    //     $tab = $request->get('tab', 'all');
        
    //     // Ambil pesanan berdasarkan tab tertentu
    //     if ($tab === 'unpaid') {
    //         $orders = Order::whereHas('payment', function ($query) {
    //             $query->where('transaction_status', 'UNPAID');
    //         })->get();
    //     } else {
    //         // Untuk tab 'all' atau 'orders', ambil semua pesanan
    //         $orders = Order::all();
    //     }

    //     return view('user.pesanan.pusat_pesanan', compact('orders'));  // Mengirim data pesanan ke view
    // }

    public function showOrders(Request $request)
    {
        $tab = $request->get('tab', 'all');

        // Cek apakah tabel orders ada
        if (!\Illuminate\Support\Facades\Schema::hasTable('orders')) {
            $orders = collect();
            return view('user.pesanan.pusat_pesanan', compact('orders'));
        }

        $q = Order::where('user_id', Auth::id())
                  ->with('orderItems')
                  ->latest();

        if ($tab === 'unpaid') {
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
            collect(), 0, 10, 1, ['path' => request()->url()]
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
        $sampleOrder = Order::select('id','user_id','kode_pesanan','status')->first();

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

   public function store(Request $request)
{
    // logging request & database yang dipakai
    Log::info('PESANAN STORE INPUT', $request->all());
    Log::info('DB USING', ['db' => DB::connection()->getDatabaseName()]);

    // validasi input
    $data = $request->validate([
        'nama_pelanggan'    => 'required|string|max:100',
        'telepon_pelanggan' => 'nullable|string|max:30',
        'items'             => 'required|array', // items wajib ada
        'jumlah'            => 'required|numeric',
    ]);

    // simpan ke database sesuai struktur tabel kamu
    $pesanan = Order::create([
        'order_code'        => 'ORD' . time(),
        'user_id'           => Auth::id(),
        'status'            => 'pending',
        'total_amount'      => $data['jumlah'],
    ]);

    // Simpan detail pesanan sebagai order items
    foreach ($data['items'] as $item) {
        \App\Models\OrderItem::create([
            'order_id' => $pesanan->id,
            'product_id' => null, // Custom order tidak punya product_id
            'garment_type' => $item['jenis_pakaian'] ?? 'Custom Order',
            'fabric_type' => $item['jenis_kain'] ?? 'Custom Fabric',
            'size' => $item['size'] ?? 'Custom Size',
            'price' => $item['price'],
            'quantity' => $item['quantity'],
            'total_price' => $item['quantity'] * $item['price'],
            'special_request' => $item['catatan_khusus'] ?? null,
        ]);
    }

    // logging id pesanan yang berhasil dibuat
    Log::info('PESANAN CREATED', ['id' => $pesanan->id]);

    return back()->with('success', 'Pesanan berhasil disimpan! ID: '.$pesanan->id);
}


    public function show($id)
    {
        // Ambil data pesanan berdasarkan ID
        $pesanan = Order::findOrFail($id);

        // Return ke view dengan data pesanan
        return view('pesanan.show', compact('pesanan'));
    }

    public function sukses()
    {
        return view('pesanan.sukses');
    }

    public function pembayaranBerhasil(Request $request)
    {
        // Pastikan status pembayaran berhasil diperbarui
        $order = Order::findOrFail($request->order_id);
        $order->status = 'Lunas';  // Atau sesuai dengan status yang kamu pakai
        $order->save();
        
        // Redirect ke halaman detail pesanan
        return redirect()->route('pesanan.sukses');
    }

    
}

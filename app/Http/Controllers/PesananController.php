<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use App\Models\DataUkuranBadan;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use App\Models\CustomerRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class PesananController extends Controller
{
    public function pembayaran(Request $request)
    {
        // (Opsional) kalau memang ada pesanan_id
        $pesanan = $request->pesanan_id ? Pesanan::find($request->pesanan_id) : null;
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

    $q = Order::where('user_id', Auth::id())->latest();

    if ($tab === 'unpaid') {
        // Versi berdasarkan kolom status di orders:
        $q->where('status', 'PENDING');

        // ATAU, kalau mau berdasarkan tabel payments:
        // $q->whereHas('payment', fn($x) => $x->where('transaction_status', 'pending'));
    }

    $orders = $q->get();

    return view('user.pesanan.pusat_pesanan', compact('orders'));
}


     public function index(Request $request)
    {
        $userId = Auth::id(); // Mendapatkan user_id yang sedang login

        // Query pesanan dengan mengambil kolom 'garment_type'
        $orders = Order::where('user_id', $userId)
                       ->select('id', 'user_id', 'order_code', 'garment_type', 'status', 'created_at')
                       ->latest() // Urutkan berdasarkan pesanan terbaru
                       ->paginate(10); // Gunakan paginate atau get sesuai kebutuhan

        // Kirim data pesanan ke view
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
    $pesanan = Pesanan::create([
        'order_date'        => now(),
        'status'            => 'UNPAID',
        'total_harga'       => $data['jumlah'],
        'user_id'           => Auth::id(),
        'nama_pelanggan'    => $data['nama_pelanggan'],
        'telepon_pelanggan' => $data['telepon_pelanggan'] ?? null,
    ]);

    // Simpan detail pesanan
    foreach ($data['items'] as $item) {
        $pesanan->details()->create([
            'product_id'   => $item['id'],
            'jumlah'       => $item['quantity'],
            'harga_satuan' => $item['price'],
            'total_harga'  => $item['quantity'] * $item['price'],
            'catatan_khusus' => $item['catatan_khusus'] ?? null,
        ]);
    }

    // logging id pesanan yang berhasil dibuat
    Log::info('PESANAN CREATED', ['id' => $pesanan->id]);

    return back()->with('success', 'Pesanan berhasil disimpan! ID: '.$pesanan->id);
}


    public function show($id)
    {
        // Ambil data pesanan berdasarkan ID
        $pesanan = Pesanan::findOrFail($id);

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
        $order = Pesanan::findOrFail($request->order_id);
        $order->status = 'Lunas';  // Atau sesuai dengan status yang kamu pakai
        $order->save();
        
        // Redirect ke halaman detail pesanan
        return redirect()->route('pesanan.sukses');
    }

    
}

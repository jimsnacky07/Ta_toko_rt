<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Midtrans\Snap;
use Midtrans\Config as MidtransConfig;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;

class PaymentController extends Controller
{
    // Menampilkan halaman checkout
    public function checkout(Request $request)
    {
        // Ambil data dari session
        $sess = $request->session()->get('checkout_data', []);

        // Cek apakah data items ada di session atau fallback data contoh
        $items = collect($sess['items'] ?? [
            [
                'id'    => 1,
                'name'  => $request->input('product_name', 'Produk'),
                'price' => (int) $request->input('price', 120000),
                'qty'   => (int) $request->input('qty', 1),
                'image' => $request->input('image'),
            ],
        ]);

        // Hitung subtotal, ongkir, dan total
        $subtotal = (int) $items->sum(fn($it) => (int) $it['price'] * (int) $it['qty']);
        $pickup   = $sess['pickup_method'] ?? $request->input('pickup_method', 'store');
        $shipping = $pickup === 'jnt' ? 32000 : 0;
        $total    = $subtotal + $shipping;

        // Data lain untuk form
        $data = [
            'pickup_method' => $pickup,
            'pesanan_id'    => $sess['pesanan_id'] ?? (int) $request->input('pesanan_id', 0),
            'order_id'      => $sess['order_id']   ?? $request->input('order_id', 'ORD-' . now()->format('YmdHis')),
            'notes'         => $sess['notes']      ?? $request->input('notes'),
        ];

        return view('user.checkout.index', [
            'items'    => $items,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total'    => $total,
            'data'     => $data,
        ]);
    }

    // Method untuk generate Snap Token dan mengarahkan pengguna ke halaman pembayaran Midtrans
    public function createSnapToken(Request $request)
    {
        // Validasi input yang diterima
        $data = $request->validate([
            'order_id'     => 'required|string',
            'total'        => 'required|integer',
            'product_name' => 'required|string',
            'customer_name'=> 'required|string',
            'customer_email'=> 'required|email',
            'customer_phone'=> 'required|string',
        ]);

        // Set konfigurasi Midtrans
        MidtransConfig::$serverKey    = config('services.midtrans.server_key');
        MidtransConfig::$isProduction = (bool) config('services.midtrans.is_production', false);
        MidtransConfig::$isSanitized  = true;
        MidtransConfig::$is3ds        = true;

        // Persiapkan parameter untuk Snap API Midtrans
        $params = [
            'transaction_details' => [
                'order_id'     => $data['order_id'],
                'gross_amount' => $data['total'],
            ],
            'item_details' => [
                [
                    'id'       => 'product-'.$data['order_id'],
                    'price'    => $data['total'],
                    'quantity' => 1,
                    'name'     => $data['product_name'],
                ]
            ],
            'customer_details' => [
                'first_name' => $data['customer_name'],
                'email'      => $data['customer_email'],
                'phone'      => $data['customer_phone'],
            ],
            'callbacks' => [
                'finish' => route('user.orders.show', $data['order_id']), // Route untuk halaman setelah pembayaran
            ],
        ];

        // Dapatkan Snap Token
        try {
            $snapToken = Snap::getSnapToken($params);
            return response()->json(['token' => $snapToken]);
        } catch (\Exception $e) {
            // Menangani error jika terjadi masalah
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    // Method untuk menampilkan halaman pembayaran setelah mendapatkan Snap Token
    public function show(Request $request)
    {
        $sess = $request->session()->get('checkout_data', []);

        $items = collect($sess['items'] ?? [[
            'id'    => 1,
            'name'  => $request->input('product_name', 'Produk'),
            'price' => (int) $request->input('price', 0),
            'qty'   => (int) $request->input('qty', 1),
            'image' => $request->input('image'),
        ]]);

        $subtotal = (int) $items->sum(fn($it) => (int)$it['price'] * (int)$it['qty']);
        $pickup   = $sess['pickup_method'] ?? $request->input('pickup_method', 'store');
        $shipping = $pickup === 'jnt' ? 32000 : 0;
        $total    = $subtotal + $shipping;

        $first = $items->first();

        $data = [
            'order_id'      => $sess['order_id'] ?? ('ORD-' . now()->format('YmdHis')),
            'total'         => $total,
            'product_name'  => $first['name']  ?? 'Produk',
            'image'         => $first['image'] ?? null,
            'pickup_method' => $pickup,
        ];

        return view('user.payment.index', compact('data', 'items', 'subtotal', 'shipping'));
    }

    public function handlePaymentSuccess(Request $request)
    {
        dd($request->all()); $orderId = $request->input('order_id');  // Mendapatkan order_id dari request
        if (!$orderId) {
            return redirect()->route('user.pusat-pesanan')->with('error', 'Order ID tidak ditemukan.');
        }

        // Gunakan find() dan cek jika order tidak ada
    dd($request->all());  $order = Order::find($orderId);  // Mengambil data pesanan berdasarkan ID
        if (!$order) {
            return redirect()->route('user.pusat-pesanan')->with('error', 'Pesanan tidak ditemukan.');
        }

        // Update status pesanan menjadi 'PAID' setelah pembayaran sukses
        $order->status = 'PAID';
        $order->paid_at = now();  // Waktu pembayaran

        // Update amount dan garment_type secara otomatis setelah pembayaran
        $order->updateOrderAfterPayment();

        return redirect()->route('user.pusat-pesanan')->with('success', 'Pembayaran berhasil');
    }

    private function storeOrderItems($orderId, $request)
    {
        // Misalnya kamu ingin menyimpan produk yang dibeli ke order_items
        $productId = $request->input('product_id');
        $product = Product::find($productId);

        // Pastikan produk ditemukan
        if (!$product) {
            return redirect()->route('keranjang.index')->with('error', 'Produk tidak ditemukan!');
        }

        $qty = max(1, (int) $request->input('qty', 1));
        $harga = $request->input('harga');
        $total = $harga * $qty;

        // Simpan ke order_items
        OrderItem::create([
            'order_id' => $orderId, // Menggunakan order_id yang sudah ada
            'product_id' => $productId,
            'garment_type' => $request->input('garment_type'),
            'fabric_type' => $request->input('fabric_type'),
            'size' => $request->input('size'),
            'price' => $harga,
            'quantity' => $qty,
            'total_price' => $total,
            'special_request' => $request->input('special_request'),
        ]);
    }

}

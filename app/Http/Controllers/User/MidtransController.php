<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Order;
use App\Models\Payment;
use Midtrans\Snap;
use Midtrans\Config as MidtransConfig;
use Midtrans\Notification;

class MidtransController extends Controller
{
    public function __construct()
    {
        // Pakai services.midtrans*, kalau kosong fallback ke midtrans.*
        $serverKey     = config('services.midtrans.server_key', config('midtrans.server_key'));
        $isProduction  = (bool) config('services.midtrans.is_production', config('midtrans.is_production', false));

        MidtransConfig::$serverKey    = $serverKey;
        MidtransConfig::$isProduction = $isProduction;
        MidtransConfig::$isSanitized  = true;
        MidtransConfig::$is3ds        = true;
    }

    /* =========================================================
     *  A. A L U R   1  —  TAMPILAN CHECKOUT + SNAP TOKEN MANUAL
     * ========================================================= */

    // 1) Halaman checkout (pakai data dari session)
    public function show(Request $request)
    {
        $sess = $request->session()->get('checkout_data', []);
        if (!$sess) {
            return redirect()->route('user.checkout')->with('error', 'Data tidak ditemukan.');
        }

        $items    = collect($sess['items'] ?? []);
        $subtotal = $items->sum(fn ($it) => (int) $it['price'] * (int) $it['qty']);
        $pickup   = $sess['pickup_method'] ?? 'store';
        $shipping = $pickup === 'jnt' ? 32000 : 0;
        $total    = $subtotal + $shipping;

        $data = [
            'order_id'      => $sess['order_id'] ?? 'ORD-' . now()->format('YmdHis'),
            'total'         => $total,
            'product_name'  => $items->first()['name'] ?? 'Produk',
            'image'         => $items->first()['image'] ?? null,
            'pickup_method' => $pickup,
        ];

        return view('user.payment.index', compact('data', 'items', 'subtotal', 'shipping'));
    }

    // 2) Generate Snap Token (tanpa buat Order di DB)
    public function createSnapToken(Request $request)
    {
        $data = $request->validate([
            'order_id'       => 'required|string',
            'total'          => 'required|integer',
            'product_name'   => 'required|string',
            'customer_name'  => 'required|string',
            'customer_email' => 'required|email',
            'customer_phone' => 'required|string',
        ]);

        $params = [
            'transaction_details' => [
                'order_id'     => $data['order_id'],
                'gross_amount' => (int) $data['total'],
            ],
            'customer_details' => [
                'first_name' => $data['customer_name'],
                'email'      => $data['customer_email'],
                'phone'      => $data['customer_phone'],
            ],
            'enabled_payments' => [
                'bri_va','bca_va','bni_va','permata_va','gopay','other_qris','shopeepay','credit_card',
            ],
            'callbacks' => [
                'finish'   => route('midtrans.finish'),
                'unfinish' => route('midtrans.unfinish'),
                'error'    => route('midtrans.error'),
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            return response()->json(['token' => $snapToken]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /* =========================================================
     *  B. A L U R   2  —  BUAT ORDER + SNAP TOKEN SEKALIGUS
     * ========================================================= */

    // 3) Buat Order di DB + buat baris Payment + ambil Snap Token
    public function createAndSnap(Request $r)
    {
        $r->validate([
            'title'         => 'required|string',
            'description'   => 'nullable|string',
            'amount'        => 'required|integer|min:1000',
            'order_type'    => 'required|in:PRODUCT,CUSTOM',
            'garment_type'  => 'nullable|string',
            'fabric_type'   => 'nullable|string',
            'measurements'  => 'nullable|array',
            'request_note'  => 'nullable|string',
            'images'        => 'nullable|array',
            'tailor_id'     => 'nullable|exists:tailors,id',
        ]);

        // ID unik untuk transaksi (dipakai ke Midtrans)
        $oid = 'ORDER-' . Str::upper(Str::random(28));

        $order = Order::create([
            'user_id'           => $r->user()->id,
            'order_id'          => $oid,          // id unik ke Midtrans
            'order_code'        => $oid,          // kalau kamu pakai field ini juga
            'title'             => $r->title,
            'description'       => $r->description,
            'order_type'        => $r->order_type,
            'garment_type'      => $r->garment_type,
            'fabric_type'       => $r->fabric_type,
            'measurements'      => $r->measurements,
            'request_note'      => $r->request_note,
            'images'            => $r->images,
            'ordered_at'        => now(),
            'tailor_id'         => $r->tailor_id,
            'amount'            => $r->amount,
            'gross_amount'      => $r->amount,
            'status'            => 'PENDING',     // status bayar
            'production_status' => 'QUEUE',       // status produksi (tailor)
            'queue_date'        => now()->toDateString(),
            'queue_no'          => 1,             // TODO: ganti sesuai logic antrianmu
        ]);

        // siapkan baris payment (biar nanti webhook tinggal update kolom midtrans)
        Payment::create([
            'order_id'     => $order->id,
            'gross_amount' => $order->amount,
            'status'       => 'pending',
        ]);

        $params = [
            'transaction_details'=> [
                'order_id'     => $order->order_id,
                'gross_amount' => (int) $order->amount
            ],
            'customer_details'=> [
                'first_name'   => $r->user()->name,
                'email'        => $r->user()->email
            ],
            'enabled_payments'=> ['bca_va','bri_va','bni_va','permata_va','other_va','qris','gopay'],
            'callbacks' => [
                'finish'   => route('midtrans.finish'),
                'unfinish' => route('midtrans.unfinish'),
                'error'    => route('midtrans.error'),
            ],
        ];

        $token = Snap::getSnapToken($params);

        return response()->json([
            'order_id'   => $order->order_id,
            'snap_token' => $token,
            'client_key' => config('services.midtrans.client_key', config('midtrans.client_key')),
        ]);
    }

    /* =========================================================
     *  C. W E B H O O K   /   N O T I F I K A S I
     * ========================================================= */

    // 4) Webhook dari Midtrans (bisa kamu daftarkan route POST ke method ini)
    // public function notification(Request $request)
    // {
    //     // Bisa pakai Notification helper (lebih simple)
    //     $notif = new Notification();

    //     // Cari order berdasar order_id yang dikirim Midtrans
    //     $order = Order::where('order_id', $notif->order_id)
    //                   ->orWhere('order_code', $notif->order_id)
    //                   ->orWhere('code', $notif->order_id) // kalau kamu pakai 'code'
    //                   ->with('payment')
    //                   ->first();

    //     if (!$order) {
    //         return response('order-not-found', 404);
    //     }

    //     $trx   = $notif->transaction_status;
    //     $fraud = $notif->fraud_status ?? null;
    //     $type  = $notif->payment_type ?? null;

    //     // Ambil info VA jika tipe bank_transfer
    //     $va = $bank = null;
    //     if ($type === 'bank_transfer' && !empty($notif->va_numbers[0])) {
    //         $va   = $notif->va_numbers[0]->va_number ?? null;
    //         $bank = strtoupper($notif->va_numbers[0]->bank ?? '');
    //     }

    //     // Update/isi record Payment
    //     $pay = $order->payment ?: new Payment(['order_id' => $order->id]);
    //     $pay->transaction_id     = $notif->transaction_id ?? $pay->transaction_id;
    //     $pay->payment_type       = $type ?? $pay->payment_type;
    //     $pay->va_number          = $va ?? $pay->va_number;
    //     $pay->bank               = $bank ?? $pay->bank;
    //     $pay->transaction_status = $trx ?? $pay->transaction_status;
    //     $pay->gross_amount       = (int) ($notif->gross_amount ?? $pay->gross_amount);
    //     $pay->raw                = $pay->raw ?: []; // pastikan array
    //     $pay->raw                = array_merge((array) $pay->raw, json_decode(json_encode($notif), true));
    //     // sinkronkan ke status lama milikmu (optional)
    //     $pay->status             = in_array($trx, ['capture','settlement']) ? 'completed'
    //                              : ($trx === 'pending' ? 'pending' : 'failed');
    //     $pay->save();

    //     // Sinkron ke kolom status di orders (untuk tampilan user)
    //     if (in_array($trx, ['capture','settlement'])) {
    //         $order->status = 'PAID';
    //     } elseif ($trx === 'pending') {
    //         $order->status = 'PENDING';
    //     } elseif (in_array($trx, ['expire','cancel','deny','failure'])) {
    //         $order->status = 'CANCELED';
    //     }
    //     $order->save();

    //     return response()->json(['ok' => true]);
    // }


    public function notification(Request $request)
    {
        try {
            $notif = new \Midtrans\Notification();
            
            \Log::info('=== WEBHOOK RECEIVED ===', [
                'order_id' => $notif->order_id,
                'transaction_status' => $notif->transaction_status,
                'payment_type' => $notif->payment_type ?? 'unknown',
                'gross_amount' => $notif->gross_amount ?? 0,
                'fraud_status' => $notif->fraud_status ?? 'unknown',
                'customer_details' => $notif->customer_details ?? null,
                'full_notification' => json_decode(json_encode($notif), true)
            ]);
            
            $order = \App\Models\Order::where('order_code', $notif->order_id)
                ->orWhere('kode_pesanan', $notif->order_id)
                ->first();
            $trx = $notif->transaction_status;

            // Jika order belum ada dan pembayaran sukses, buat order baru dari session
            if (!$order && in_array($trx, ['capture','settlement'])) {
                // Coba ambil dari session global atau request session
                $pendingOrder = session('pending_order');
                
                \Log::info('Pending Order from Session', ['pending_order' => $pendingOrder]);
                
                // Jika tidak ada pending order di session, coba buat order langsung dari notifikasi
                if (!$pendingOrder) {
                    \Log::info('No pending order in session, creating order from notification', [
                        'order_id' => $notif->order_id,
                        'gross_amount' => $notif->gross_amount
                    ]);
                    
                    // Force create tables if they don't exist
                    if (!\Schema::hasTable('orders')) {
                        \Schema::create('orders', function (\Illuminate\Database\Schema\Blueprint $table) {
                            $table->id();
                            $table->unsignedBigInteger('user_id');
                            $table->string('order_code')->unique();
                            $table->string('status')->default('pending');
                            $table->decimal('total_amount', 10, 2);
                            $table->timestamp('paid_at')->nullable();
                            $table->timestamps();
                            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                        });
                    }
                    
                    if (!\Schema::hasTable('order_items')) {
                        \Schema::create('order_items', function (\Illuminate\Database\Schema\Blueprint $table) {
                            $table->id();
                            $table->unsignedBigInteger('order_id');
                            $table->unsignedBigInteger('product_id')->nullable();
                            $table->string('garment_type');
                            $table->string('fabric_type');
                            $table->string('size');
                            $table->decimal('price', 10, 2);
                            $table->integer('quantity');
                            $table->decimal('total_price', 10, 2);
                            $table->text('special_request')->nullable();
                            $table->timestamps();
                            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
                        });
                    }
                    
                    // Find user by email from notification or session
                    $userId = null;
                    
                    // 1. Try to get user from session first (more reliable)
                    $pendingOrder = session('pending_order');
                    if ($pendingOrder && isset($pendingOrder['user_email'])) {
                        $user = \App\Models\User::where('email', $pendingOrder['user_email'])->first();
                        if ($user) {
                            $userId = $user->id;
                            \Log::info('Found user from session', ['user_id' => $userId, 'email' => $pendingOrder['user_email']]);
                        }
                    }
                    
                    // 2. If not found in session, try from notification
                    if (!$userId && isset($notif->customer_details) && isset($notif->customer_details->email)) {
                        $user = \App\Models\User::where('email', $notif->customer_details->email)->first();
                        if ($user) {
                            $userId = $user->id;
                            \Log::info('Found user from notification', ['user_id' => $userId, 'email' => $notif->customer_details->email]);
                        }
                    }
                    
                    // 3. If still not found, use the first user as fallback (for testing)
                    if (!$userId) {
                        $user = \App\Models\User::first();
                        $userId = $user ? $user->id : 1;
                        \Log::warning('Using fallback user', ['user_id' => $userId]);
                    }
                    
                    // Buat order minimal dari notifikasi
                    $order = \App\Models\Order::create([
                        'user_id' => $userId,
                        'kode_pesanan' => $notif->order_id,
                        'order_code' => $notif->order_id,
                        'status' => 'paid',
                        'total_harga' => $notif->gross_amount ?? 0,
                        'total_amount' => $notif->gross_amount ?? 0,
                        'paid_at' => now(),
                    ]);
                    
                    // Tentukan jenis order berdasarkan order_id
                    $isCustomOrder = str_contains($notif->order_id, 'ORD-');
                    
                    if ($isCustomOrder) {
                        // Order Custom
                        \App\Models\OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => null,
                            'garment_type' => 'Order Custom',
                            'fabric_type' => 'Custom Fabric',
                            'size' => 'Custom Size',
                            'price' => $notif->gross_amount ?? 0,
                            'quantity' => 1,
                            'total_price' => $notif->gross_amount ?? 0,
                            'special_request' => 'Custom Order - Payment ID: ' . $notif->order_id
                        ]);
                    } else {
                        // Product Order
                        \App\Models\OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => 1,
                            'garment_type' => 'Product Order',
                            'fabric_type' => 'Standard',
                            'size' => 'M',
                            'price' => $notif->gross_amount ?? 0,
                            'quantity' => 1,
                            'total_price' => $notif->gross_amount ?? 0,
                            'special_request' => 'Product Order - Payment ID: ' . $notif->order_id
                        ]);
                    }
                    
                    \Log::info('Order Created from Notification', [
                        'order_id' => $order->id,
                        'order_code' => $order->order_code,
                        'amount' => $order->total_amount
                    ]);
                    
                } elseif ($pendingOrder) {
                    // Cari user berdasarkan email untuk memastikan user_id yang benar
                    $userId = $pendingOrder['user_id'];
                    if (isset($pendingOrder['user_email'])) {
                        $user = \App\Models\User::where('email', $pendingOrder['user_email'])->first();
                        if ($user) {
                            $userId = $user->id;
                        }
                    }
                    
                    // Buat order baru
                    $order = \App\Models\Order::create([
                        'user_id' => $userId,
                        'kode_pesanan' => $notif->order_id,
                        'order_code' => $notif->order_id,
                        'status' => 'paid',
                        'total_harga' => $pendingOrder['total_amount'],
                        'total_amount' => $pendingOrder['total_amount'],
                        'paid_at' => now(),
                    ]);

                    \Log::info('Order Created Successfully', [
                        'order_id' => $order->id,
                        'order_code' => $order->order_code,
                        'user_id' => $order->user_id,
                        'total_amount' => $order->total_amount
                    ]);

                    // Buat order items
                    foreach ($pendingOrder['items'] as $item) {
                        $orderItem = \App\Models\OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => $item['product_id'] ?? 1,
                            'garment_type' => $item['garment_type'],
                            'fabric_type' => $item['fabric_type'],
                            'size' => $item['size'],
                            'price' => $item['price'],
                            'quantity' => $item['quantity'],
                            'total_price' => $item['total_price'],
                            'special_request' => $item['special_request'],
                        ]);
                        
                        \Log::info('Order Item Created', ['item_id' => $orderItem->id]);
                    }

                    // Hapus item dari keranjang
                    if (!empty($pendingOrder['selected_cart_ids'])) {
                        $keranjang = session('keranjang', []);
                        $selectedIds = array_map('strval', $pendingOrder['selected_cart_ids']);
                        
                        $keranjang = array_values(array_filter(
                            $keranjang,
                            fn($item) => !in_array((string)($item['id'] ?? ''), $selectedIds)
                        ));
                        
                        session(['keranjang' => $keranjang]);
                        \Log::info('Cart Items Removed', ['removed_ids' => $selectedIds]);
                    }

                    // Hapus pending order dari session
                    session()->forget('pending_order');
                    \Log::info('Pending Order Cleared from Session');
                } else {
                    \Log::warning('Pending Order Not Found or Mismatch', [
                        'expected_order_code' => $notif->order_id,
                        'session_order_code' => $pendingOrder['order_code'] ?? null
                    ]);
                }
            }

            // Update status order yang sudah ada
            if ($order) {
                $oldStatus = $order->status;
                
                if (in_array($trx, ['capture','settlement'])) {
                    $order->status = 'paid';
                    $order->paid_at = now();
                } elseif ($trx === 'pending') {
                    $order->status = 'pending';
                } elseif (in_array($trx, ['expire','cancel','deny','failure'])) {
                    $order->status = 'canceled';
                }
                
                $order->save();
                
                \Log::info('Order Status Updated', [
                    'order_id' => $order->id,
                    'old_status' => $oldStatus,
                    'new_status' => $order->status
                ]);
            }

            return response()->json(['ok' => true]);
            
        } catch (\Exception $e) {
            \Log::error('Midtrans Notification Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }


    /* =========================================================
     *  D. R E D I R E C T  P A G E S
     * ========================================================= */

    public function finish(Request $request)
    {
        // Tunggu sebentar untuk memastikan webhook sudah diproses
        sleep(3);
        
        return redirect()->route('user.orders.index')->with('success', 'Pembayaran berhasil! Pesanan Anda sedang diproses.');
    }

    public function unfinish()
    {
        return redirect()->route('user.orders.index')
            ->with('warning', 'Pembayaran belum selesai.');
    }

    public function error()
    {
        return redirect()->route('user.orders.index')
            ->with('error', 'Terjadi error pembayaran.');
    }

    
}

<?php

namespace App\Http\Controllers\User;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Order;
use App\Models\Payment;
use Midtrans\Snap;
use Midtrans\Config as MidtransConfig;

class MidtransController extends Controller
{
    public function __construct()
    {
        // Set Midtrans configuration
        $serverKey     = config('midtrans.server_key');
        $clientKey     = config('midtrans.client_key');
        $isProduction  = config('midtrans.is_production');
        $isSanitized   = config('midtrans.is_sanitized');
        $is3ds         = config('midtrans.is_3ds');

        MidtransConfig::$serverKey    = $serverKey;
        MidtransConfig::$clientKey    = $clientKey;
        MidtransConfig::$isProduction = $isProduction;
        MidtransConfig::$isSanitized  = $isSanitized;
        MidtransConfig::$is3ds        = $is3ds;
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
        $subtotal = $items->sum(fn($it) => (int) $it['price'] * (int) $it['qty']);
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
                'bri_va',
                'bca_va',
                'bni_va',
                'permata_va',
                'gopay',
                'other_qris',
                'shopeepay',
                'credit_card',
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
            'transaction_details' => [
                'order_id'     => $order->order_id,
                'gross_amount' => (int) $order->amount
            ],
            'customer_details' => [
                'first_name'   => $r->user()->name,
                'email'        => $r->user()->email
            ],
            'enabled_payments' => ['bca_va', 'bri_va', 'bni_va', 'permata_va', 'other_va', 'qris', 'gopay'],
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

    /**
     * Map payment type dari Midtrans ke metode pembayaran yang user-friendly
     */
    private function mapPaymentType($paymentType)
    {
        $paymentTypeMap = [
            'bank_transfer' => 'Bank Transfer',
            'bca_va' => 'Bank Transfer BCA',
            'bni_va' => 'Bank Transfer BNI',
            'bri_va' => 'Bank Transfer BRI',
            'permata_va' => 'Bank Transfer Permata',
            'gopay' => 'GoPay',
            'qris' => 'QRIS',
            'credit_card' => 'Credit Card',
            'cstore' => 'Convenience Store',
            'other_va' => 'Bank Transfer Lainnya',
            'other_qris' => 'QRIS Lainnya',
        ];

        return $paymentTypeMap[$paymentType] ?? $paymentType;
    }

    public function notification(Request $request)
    {
        try {
            // Log raw request data
            Log::info('=== [MIDTRANS] WEBHOOK RECEIVED - RAW DATA ===', [
                'request_method' => $request->method(),
                'request_url' => $request->fullUrl(),
                'request_headers' => $request->headers->all(),
                'request_body' => $request->all(),
                'content_type' => $request->header('Content-Type'),
                'user_agent' => $request->header('User-Agent'),
                'ip_address' => $request->ip(),
            ]);

            $notif = new \Midtrans\Notification();

            // Log parsed notification data
            Log::info('=== [MIDTRANS] WEBHOOK RECEIVED - PARSED DATA ===', [
                'order_id' => $notif->order_id ?? 'null',
                'transaction_id' => $notif->transaction_id ?? 'null',
                'transaction_status' => $notif->transaction_status ?? 'null',
                'transaction_time' => $notif->transaction_time ?? 'null',
                'payment_type' => $notif->payment_type ?? 'null',
                'gross_amount' => $notif->gross_amount ?? 'null',
                'currency' => $notif->currency ?? 'null',
                'fraud_status' => $notif->fraud_status ?? 'null',
                'status_code' => $notif->status_code ?? 'null',
                'status_message' => $notif->status_message ?? 'null',
                'merchant_id' => $notif->merchant_id ?? 'null',
                'finish_redirect_url' => $notif->finish_redirect_url ?? 'null',
                'error_snap_url' => $notif->error_snap_url ?? 'null',
                'pending_redirect_url' => $notif->pending_redirect_url ?? 'null',
                'unfinish_redirect_url' => $notif->unfinish_redirect_url ?? 'null',
            ]);

            // Log customer details
            if (isset($notif->customer_details)) {
                Log::info('=== [MIDTRANS] CUSTOMER DETAILS ===', [
                    'customer_details' => json_decode(json_encode($notif->customer_details), true),
                ]);
            }

            // Log item details
            if (isset($notif->item_details)) {
                Log::info('=== [MIDTRANS] ITEM DETAILS ===', [
                    'item_details' => json_decode(json_encode($notif->item_details), true),
                ]);
            }

            // Log billing address
            if (isset($notif->billing_address)) {
                Log::info('=== [MIDTRANS] BILLING ADDRESS ===', [
                    'billing_address' => json_decode(json_encode($notif->billing_address), true),
                ]);
            }

            // Log shipping address
            if (isset($notif->shipping_address)) {
                Log::info('=== [MIDTRANS] SHIPPING ADDRESS ===', [
                    'shipping_address' => json_decode(json_encode($notif->shipping_address), true),
                ]);
            }

            // Log full notification object
            Log::info('=== [MIDTRANS] FULL NOTIFICATION OBJECT ===', [
                'full_notification' => json_decode(json_encode($notif), true),
            ]);

            // Log session data
            Log::info('=== [MIDTRANS] SESSION DATA ===', [
                'session_pending_order' => session('pending_order'),
                'session_id' => session()->getId(),
            ]);

            // Cari order berdasarkan order_code atau kode_pesanan
            $order = \App\Models\Order::where('order_code', $notif->order_id)
                ->orWhere('kode_pesanan', $notif->order_id)
                ->first();

            $trx = $notif->transaction_status;

            // Log order search result
            Log::info('=== [MIDTRANS] ORDER SEARCH RESULT ===', [
                'search_order_id' => $notif->order_id,
                'order_found' => $order ? true : false,
                'order_id' => $order ? $order->id : null,
                'order_code' => $order ? $order->order_code : null,
                'transaction_status' => $trx
            ]);

            // Jika order belum ada dan pembayaran sukses, buat order baru dari session
            if (!$order && in_array($trx, ['capture', 'settlement'])) {
                Log::warning('[MIDTRANS] Order tidak ditemukan di database, mencoba buat dari session', [
                    'order_id' => $notif->order_id,
                    'gross_amount' => $notif->gross_amount
                ]);

                // Coba ambil dari session global atau request session
                $pendingOrder = session('pending_order');

                // Jika tidak ada di session, coba ambil dari cache
                if (!$pendingOrder) {
                    $pendingOrder = cache()->get('pending_order_' . $notif->order_id);
                    Log::info('Pending Order from Cache', ['pending_order' => $pendingOrder]);
                } else {
                    Log::info('Pending Order from Session', ['pending_order' => $pendingOrder]);
                }

                // Cek apakah order sudah ada dengan format yang berbeda
                $existingOrder = \App\Models\Order::where('order_code', 'like', '%' . $notif->order_id . '%')
                    ->orWhere('kode_pesanan', 'like', '%' . $notif->order_id . '%')
                    ->first();

                if ($existingOrder) {
                    Log::info('Found existing order with similar code', [
                        'existing_order_id' => $existingOrder->id,
                        'existing_order_code' => $existingOrder->order_code,
                        'search_order_id' => $notif->order_id
                    ]);
                    $order = $existingOrder;
                } elseif (!$pendingOrder) {
                    Log::info('No pending order in session, creating order from notification', [
                        'order_id' => $notif->order_id,
                        'gross_amount' => $notif->gross_amount
                    ]);

                    // Force create tables if they don't exist
                    if (!Schema::hasTable('orders')) {
                        Schema::create('orders', function (\Illuminate\Database\Schema\Blueprint $table) {
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

                    if (!Schema::hasTable('order_items')) {
                        Schema::create('order_items', function (\Illuminate\Database\Schema\Blueprint $table) {
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
                            Log::info('Found user from session', ['user_id' => $userId, 'email' => $pendingOrder['user_email']]);
                        }
                    }

                    // 2. If not found in session, try from notification
                    if (!$userId && isset($notif->customer_details) && isset($notif->customer_details->email)) {
                        $user = \App\Models\User::where('email', $notif->customer_details->email)->first();
                        if ($user) {
                            $userId = $user->id;
                            Log::info('Found user from notification', ['user_id' => $userId, 'email' => $notif->customer_details->email]);
                        }
                    }

                    // 3. If still not found, use the first user as fallback (for testing)
                    if (!$userId) {
                        $user = \App\Models\User::first();
                        $userId = $user ? $user->id : 1;
                        Log::warning('Using fallback user', ['user_id' => $userId]);
                    }

                    // Buat order minimal dari notifikasi
                    $order = \App\Models\Order::create([
                        'user_id' => $userId,
                        'kode_pesanan' => $notif->order_id,
                        'order_code' => $notif->order_id,
                        'status' => 'diproses', // Status sesuai enum values
                        'total_harga' => $notif->gross_amount ?? 0,
                        'total_amount' => $notif->gross_amount ?? 0,
                        'metode_pembayaran' => $this->mapPaymentType($notif->payment_type ?? 'unknown'),
                        'paid_at' => now(),
                    ]);

                    // Pembeda order produk vs custom:
                    // 1. Jika ada order_type di session pending_order, gunakan itu
                    // 2. Jika tidak, cek pending_order['items']:
                    //    - Jika ada product_id, berarti produk
                    //    - Jika product_id null, berarti custom
                    $pendingOrder = session('pending_order');
                    $orderType = null;
                    if ($pendingOrder && isset($pendingOrder['items'][0]['product_id'])) {
                        $orderType = $pendingOrder['items'][0]['product_id'] ? 'PRODUCT' : 'CUSTOM';
                    }
                    // Fallback: jika tidak ada info, asumsikan produk
                    if ($orderType === 'CUSTOM') {
                        \App\Models\OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => null,
                            'garment_type' => 'Order Custom',
                            'fabric_type' => 'Custom Fabric',
                            'size' => 'Custom Size',
                            'price' => $notif->gross_amount ?? 0,
                            'quantity' => 1,
                            'total_price' => $notif->gross_amount ?? 0,
                            'special_request' => 'Custom Order - Payment ID: ' . $notif->order_id,
                            'status' => 'menunggu' // Status sesuai enum values
                        ]);
                    } else {
                        \App\Models\OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => ((($pendingOrder['items'][0]['product_id'] ?? null) === '' || ($pendingOrder['items'][0]['product_id'] ?? null) === 0) ? null : ($pendingOrder['items'][0]['product_id'] ?? null)),
                            'garment_type' => $pendingOrder['items'][0]['garment_type'] ?? 'Product Order',
                            'fabric_type' => $pendingOrder['items'][0]['fabric_type'] ?? 'Standard',
                            'size' => $pendingOrder['items'][0]['size'] ?? 'M',
                            'price' => $pendingOrder['items'][0]['price'] ?? ($notif->gross_amount ?? 0),
                            'quantity' => $pendingOrder['items'][0]['quantity'] ?? 1,
                            'total_price' => $pendingOrder['items'][0]['total_price'] ?? ($notif->gross_amount ?? 0),
                            'special_request' => $pendingOrder['items'][0]['special_request'] ?? ('Product Order - Payment ID: ' . $notif->order_id),
                            'status' => 'menunggu' // Status sesuai enum values
                        ]);
                    }

                    Log::info('Order Created from Notification', [
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

                    // Batalkan order duplikat (pre-order) yang sempat tersimpan sebelum bayar dari keranjang
                    try {
                        $allItems = collect($pendingOrder['items'] ?? []);
                        $candidateTotals = $allItems->map(function ($it) {
                            $qty = (int)($it['quantity'] ?? 1);
                            $price = (int)($it['price'] ?? 0);
                            return (int)($it['total_price'] ?? ($price * $qty));
                        })
                            ->filter(fn($v) => $v > 0)
                            ->unique()
                            ->values()
                            ->all();

                        if (!empty($candidateTotals)) {
                            $duplicates = \App\Models\Order::where('user_id', $userId)
                                ->where('status', 'menunggu')
                                ->whereIn('total_amount', $candidateTotals)
                                ->where('created_at', '>=', now()->subMinutes(60))
                                ->get();

                            foreach ($duplicates as $dup) {
                                $old = $dup->status;
                                $dup->status = 'dibatalkan';
                                $dup->save();
                                Log::info('Cancelled duplicate pre-order before creating OP/OC orders', [
                                    'duplicate_order_id' => $dup->id,
                                    'old_status' => $old,
                                    'new_status' => $dup->status,
                                    'total_amount' => $dup->total_amount,
                                ]);
                            }
                        }
                    } catch (\Throwable $e) {
                        Log::warning('Failed cancelling potential duplicate pre-orders (OP/OC path)', ['error' => $e->getMessage()]);
                    }

                    // Pisahkan item menjadi produk (prod) dan custom
                    $items = collect($pendingOrder['items'] ?? []);
                    $prodItems = $items->filter(fn($it) => ($it['type'] ?? 'prod') === 'prod')->values();
                    $customItems = $items->filter(fn($it) => ($it['type'] ?? 'prod') !== 'prod')->values();

                    $sumTotal = function ($col) {
                        return (int) $col->sum(function ($it) {
                            $qty = (int)($it['quantity'] ?? 1);
                            $price = (int)($it['price'] ?? 0);
                            return (int)($it['total_price'] ?? ($price * $qty));
                        });
                    };

                    $prodTotal = $sumTotal($prodItems);
                    $customTotal = $sumTotal($customItems);

                    $paymentMethod = $this->mapPaymentType($notif->payment_type ?? 'unknown');

                    // Buat order OP- untuk produk jika ada
                    if ($prodItems->isNotEmpty()) {
                        $opCode = 'OP-' . $notif->order_id;
                        $opOrder = \App\Models\Order::create([
                            'user_id' => $userId,
                            'kode_pesanan' => $opCode,
                            'order_code' => $opCode,
                            'status' => 'diproses',
                            'total_harga' => $prodTotal,
                            'total_amount' => $prodTotal,
                            'metode_pembayaran' => $paymentMethod,
                            'paid_at' => now(),
                        ]);

                        foreach ($prodItems as $item) {
                            \App\Models\OrderItem::create([
                                'order_id' => $opOrder->id,
                                'product_id' => ((($item['product_id'] ?? null) === '' || ($item['product_id'] ?? null) === 0) ? null : ($item['product_id'] ?? null)),
                                'garment_type' => $item['garment_type'] ?? 'Product Order',
                                'fabric_type' => $item['fabric_type'] ?? 'Standard',
                                'size' => $item['size'] ?? 'M',
                                'price' => (int)($item['price'] ?? 0),
                                'quantity' => (int)($item['quantity'] ?? 1),
                                'total_price' => (int)($item['total_price'] ?? 0),
                                'special_request' => $item['special_request'] ?? null,
                                'status' => 'menunggu',
                            ]);
                        }

                        Log::info('OP order created from cart payment', [
                            'order_id' => $opOrder->id,
                            'order_code' => $opCode,
                            'items_count' => $prodItems->count(),
                            'total' => $prodTotal,
                        ]);
                    }

                    // Buat order OC- untuk custom jika ada
                    if ($customItems->isNotEmpty()) {
                        $ocCode = 'OC-' . $notif->order_id;
                        $ocOrder = \App\Models\Order::create([
                            'user_id' => $userId,
                            'kode_pesanan' => $ocCode,
                            'order_code' => $ocCode,
                            'status' => 'diproses',
                            'total_harga' => $customTotal,
                            'total_amount' => $customTotal,
                            'metode_pembayaran' => $paymentMethod,
                            'paid_at' => now(),
                        ]);

                        foreach ($customItems as $item) {
                            \App\Models\OrderItem::create([
                                'order_id' => $ocOrder->id,
                                // Custom orders should not be tied to a product
                                'product_id' => null,
                                'garment_type' => $item['garment_type'] ?? 'Order Custom',
                                'fabric_type' => $item['fabric_type'] ?? 'Custom Fabric',
                                'size' => $item['size'] ?? 'Custom',
                                'price' => (int)($item['price'] ?? 0),
                                'quantity' => (int)($item['quantity'] ?? 1),
                                'total_price' => (int)($item['total_price'] ?? 0),
                                'special_request' => $item['special_request'] ?? null,
                                'status' => 'menunggu',
                            ]);
                        }

                        Log::info('OC order created from cart payment', [
                            'order_id' => $ocOrder->id,
                            'order_code' => $ocCode,
                            'items_count' => $customItems->count(),
                            'total' => $customTotal,
                        ]);
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
                        Log::info('Cart Items Removed', ['removed_ids' => $selectedIds]);
                    }

                    // Hapus pending order dari session dan cache
                    session()->forget('pending_order');
                    cache()->forget('pending_order_' . $notif->order_id);
                    Log::info('Pending Order Cleared from Session and Cache');
                } else {
                    Log::warning('Pending Order Not Found or Mismatch', [
                        'expected_order_code' => $notif->order_id,
                        'session_order_code' => $pendingOrder['order_code'] ?? null
                    ]);
                }
            }

            // Update status order yang sudah ada
            if ($order) {
                // Hapus cache pending order karena order sudah ditemukan
                cache()->forget('pending_order_' . $notif->order_id);
                Log::info('=== [MIDTRANS] UPDATING EXISTING ORDER ===', [
                    'order_id' => $order->id,
                    'order_code' => $order->order_code,
                    'current_status' => $order->status,
                    'current_payment_method' => $order->metode_pembayaran,
                    'transaction_status' => $trx,
                    'payment_type' => $notif->payment_type ?? 'null',
                ]);

                $oldStatus = $order->status;
                $oldPaymentMethod = $order->metode_pembayaran;

                // Mapping status Midtrans ke status order di DB (sesuai enum values)
                $statusMap = [
                    'pending'     => 'menunggu',
                    'capture'     => 'diproses',
                    'settlement'  => 'diproses',
                    'deny'        => 'dibatalkan',
                    'cancel'      => 'dibatalkan',
                    'expire'      => 'dibatalkan',
                    'failure'     => 'dibatalkan',
                ];

                $newStatus = $statusMap[$trx] ?? $order->status;
                $order->status = $newStatus;

                if (in_array($trx, ['capture', 'settlement'])) {
                    $order->paid_at = now();
                    Log::info('Payment completed, setting paid_at', [
                        'paid_at' => $order->paid_at,
                        'transaction_status' => $trx
                    ]);
                }

                // Simpan metode pembayaran dari notifikasi (force override)
                // Ambil payment_type dari objek notif atau langsung dari body request sebagai fallback
                $paymentTypeFromNotif = isset($notif->payment_type) ? $notif->payment_type : null;
                $paymentTypeFromBody  = $request->input('payment_type');
                $effectivePaymentType = $paymentTypeFromNotif ?: $paymentTypeFromBody;

                if ($effectivePaymentType) {
                    // Default mapping
                    $paymentTypeMap = [
                        'bank_transfer' => 'Bank Transfer',
                        'bca_va'        => 'Bank Transfer BCA',
                        'bni_va'        => 'Bank Transfer BNI',
                        'bri_va'        => 'Bank Transfer BRI',
                        'permata_va'    => 'Bank Transfer Permata',
                        'gopay'         => 'GoPay',
                        'qris'          => 'QRIS',
                        'other_qris'    => 'QRIS',
                        'credit_card'   => 'Credit Card',
                        'cstore'        => 'Convenience Store',
                        'other_va'      => 'Bank Transfer Lainnya',
                    ];

                    $paymentType = $effectivePaymentType;
                    $metodePembayaran = $paymentTypeMap[$paymentType] ?? $paymentType;

                    // Khusus bank_transfer: tentukan bank dari va_numbers jika tersedia
                    if ($paymentType === 'bank_transfer') {
                        $vaNumbers = null;
                        if (isset($notif->va_numbers)) {
                            $vaNumbers = json_decode(json_encode($notif->va_numbers), true);
                        }
                        if (!$vaNumbers) {
                            $vaNumbers = $request->input('va_numbers');
                        }

                        $bank = '';
                        if (is_array($vaNumbers) && count($vaNumbers) > 0) {
                            $first = $vaNumbers[0];
                            $bank = strtolower(($first['bank'] ?? ($first->bank ?? '')));
                        }

                        if ($bank === 'bca') {
                            $metodePembayaran = 'Bank Transfer BCA';
                        } elseif ($bank === 'bni') {
                            $metodePembayaran = 'Bank Transfer BNI';
                        } elseif ($bank === 'bri') {
                            $metodePembayaran = 'Bank Transfer BRI';
                        } elseif ($bank === 'permata') {
                            $metodePembayaran = 'Bank Transfer Permata';
                        } else {
                            $metodePembayaran = 'Bank Transfer';
                        }
                    }

                    // Khusus QRIS lain: normalisasi ke QRIS
                    if (in_array($paymentType, ['qris', 'other_qris'], true)) {
                        $metodePembayaran = 'QRIS';
                    }

                    $order->metode_pembayaran = $metodePembayaran;

                    Log::info('=== [MIDTRANS] PAYMENT METHOD MAPPING ===', [
                        'original_payment_type_notif' => $paymentTypeFromNotif,
                        'original_payment_type_body'  => $paymentTypeFromBody,
                        'effective_payment_type'      => $paymentType,
                        'va_numbers_from_notif'       => isset($notif->va_numbers) ? json_decode(json_encode($notif->va_numbers), true) : null,
                        'va_numbers_from_body'        => $request->input('va_numbers'),
                        'mapped_payment_method' => $metodePembayaran,
                        'old_payment_method' => $oldPaymentMethod,
                        'new_payment_method' => $metodePembayaran,
                    ]);
                }

                $order->save();

                Log::info('=== [MIDTRANS] ORDER UPDATE COMPLETED ===', [
                    'order_id' => $order->id,
                    'order_code' => $order->order_code,
                    'old_status' => $oldStatus,
                    'new_status' => $order->status,
                    'old_payment_method' => $oldPaymentMethod,
                    'new_payment_method' => $order->metode_pembayaran ?? 'null',
                    'paid_at' => $order->paid_at,
                    'updated_at' => $order->updated_at
                ]);
            } else {
                Log::warning('=== [MIDTRANS] ORDER NOT FOUND ===', [
                    'order_id_from_notification' => $notif->order_id,
                    'transaction_status' => $trx,
                    'payment_type' => $notif->payment_type ?? 'null',
                    'gross_amount' => $notif->gross_amount ?? 'null'
                ]);
            }

            Log::info('=== [MIDTRANS] WEBHOOK PROCESSING COMPLETED ===', [
                'order_found' => $order ? true : false,
                'order_id' => $order ? $order->id : null,
                'order_code' => $order ? $order->order_code : null,
                'final_status' => $order ? $order->status : null,
                'final_payment_method' => $order ? $order->metode_pembayaran : null,
                'response' => ['ok' => true]
            ]);

            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            Log::error('Midtrans Notification Error', [
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

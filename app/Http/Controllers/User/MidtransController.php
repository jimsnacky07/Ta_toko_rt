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
    $notif = new \Midtrans\Notification();

    $order = \App\Models\Order::where('order_code', $notif->order_id)
        ->orWhere('id', $notif->order_id) // jaga-jaga
        ->first();

    if (!$order) return response('order-not-found', 404);

    $trx = $notif->transaction_status;

    if (in_array($trx, ['capture','settlement'])) {
        $order->status = 'PAID';
    } elseif ($trx === 'pending') {
        $order->status = 'PENDING';
    } elseif (in_array($trx, ['expire','cancel','deny','failure'])) {
        $order->status = 'CANCELED';
    }
    $order->save();

    return response()->json(['ok'=>true]);
}


    /* =========================================================
     *  D. R E D I R E C T  P A G E S
     * ========================================================= */

    public function finish()
    {
        return redirect()->route('user.orders.index')
            ->with('success', 'Pembayaran diproses.');
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

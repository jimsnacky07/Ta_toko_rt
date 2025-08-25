<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Payment;

class MidtransWebhookController extends Controller
{
    // public function notify(Request $r)
    // {
    //     $p = $r->all();

    //     $orderId = $p['order_id'] ?? '';
    //     $status  = $p['status_code'] ?? '';
    //     $gross   = $p['gross_amount'] ?? '';
    //     $sign    = $p['signature_key'] ?? '';

    //     $serverKey = config('services.midtrans.server_key');
    //     $calc = hash('sha512', $orderId.$status.$gross.$serverKey);
    //     if (!hash_equals($calc, $sign)) {
    //         return response()->json(['msg' => 'invalid sig'], 401);
    //     }

    //     $trx  = $p['transaction_status'] ?? '';
    //     $type = $p['payment_type'] ?? '';
    //     $va   = '';
    //     $bank = '';

    //     if ($type === 'bank_transfer' && !empty($p['va_numbers'][0])) {
    //         $va   = $p['va_numbers'][0]['va_number'] ?? '';
    //         $bank = strtoupper($p['va_numbers'][0]['bank'] ?? '');
    //     }

    //     $orderId = $p['order_id'] ?? '';

    //     // $order = Order::with('payment')->where('order_id', $orderId)->first();
    //     $order = Order::with('payment')->where('order_code', $orderId)->first();
    //     if (!$order) return response()->json(['msg' => 'order not found'], 404);

    //     $pay = $order->payment ?: new Payment(['order_id' => $order->id]);
    //     $pay->transaction_id     = $p['transaction_id'] ?? $pay->transaction_id;
    //     $pay->payment_type       = $type;
    //     $pay->va_number          = $va;
    //     $pay->bank               = $bank;
    //     $pay->transaction_status = $trx;
    //     $pay->gross_amount       = (int) ($gross ?: $pay->gross_amount);
    //     $pay->raw                = $p;
    //     $pay->status             = $trx === 'settlement' || $trx === 'capture' ? 'completed'
    //                              : ($trx === 'pending' ? 'pending' : 'failed'); // map ke skema lamamu
    //     $pay->save();

    //     // sync status bayar di orders
    //     if (in_array($trx, ['capture','settlement']))     $order->status = 'PAID';
    //     elseif ($trx === 'pending')                        $order->status = 'PENDING';
    //     elseif (in_array($trx, ['expire','cancel','deny','failure'])) $order->status = 'CANCELED';
    //     $order->save();

    //     return response()->json(['ok' => true]);
    // }
}

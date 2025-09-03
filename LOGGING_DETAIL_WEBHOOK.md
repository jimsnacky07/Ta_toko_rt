# 🔧 LOGGING DETAIL MIDTRANS WEBHOOK

## 🔍 **Masalah yang Ditemukan:**

### **Tidak Ada Logging Detail untuk Webhook**
- Webhook Midtrans tidak berfungsi
- Tidak ada log yang menunjukkan data yang dikirim Midtrans
- Sulit untuk debug masalah webhook
- Tidak bisa melihat semua field yang dikirim oleh Midtrans

## ✅ **Perbaikan yang Dilakukan:**

### **1. Tambah Logging Detail di MidtransController**

#### **File:** `app/Http/Controllers/User/MidtransController.php`

**Logging yang Ditambahkan:**

#### **A. Raw Request Data**
```php
Log::info('=== [MIDTRANS] WEBHOOK RECEIVED - RAW DATA ===', [
    'request_method' => $request->method(),
    'request_url' => $request->fullUrl(),
    'request_headers' => $request->headers->all(),
    'request_body' => $request->all(),
    'content_type' => $request->header('Content-Type'),
    'user_agent' => $request->header('User-Agent'),
    'ip_address' => $request->ip(),
]);
```

#### **B. Parsed Notification Data**
```php
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
```

#### **C. Customer Details**
```php
if (isset($notif->customer_details)) {
    Log::info('=== [MIDTRANS] CUSTOMER DETAILS ===', [
        'customer_details' => json_decode(json_encode($notif->customer_details), true),
    ]);
}
```

#### **D. Item Details**
```php
if (isset($notif->item_details)) {
    Log::info('=== [MIDTRANS] ITEM DETAILS ===', [
        'item_details' => json_decode(json_encode($notif->item_details), true),
    ]);
}
```

#### **E. Billing & Shipping Address**
```php
if (isset($notif->billing_address)) {
    Log::info('=== [MIDTRANS] BILLING ADDRESS ===', [
        'billing_address' => json_decode(json_encode($notif->billing_address), true),
    ]);
}

if (isset($notif->shipping_address)) {
    Log::info('=== [MIDTRANS] SHIPPING ADDRESS ===', [
        'shipping_address' => json_decode(json_encode($notif->shipping_address), true),
    ]);
}
```

#### **F. Full Notification Object**
```php
Log::info('=== [MIDTRANS] FULL NOTIFICATION OBJECT ===', [
    'full_notification' => json_decode(json_encode($notif), true),
]);
```

#### **G. Session Data**
```php
Log::info('=== [MIDTRANS] SESSION DATA ===', [
    'session_pending_order' => session('pending_order'),
    'session_id' => session()->getId(),
]);
```

#### **H. Order Update Process**
```php
Log::info('=== [MIDTRANS] UPDATING EXISTING ORDER ===', [
    'order_id' => $order->id,
    'order_code' => $order->order_code,
    'current_status' => $order->status,
    'current_payment_method' => $order->metode_pembayaran,
    'transaction_status' => $trx,
    'payment_type' => $notif->payment_type ?? 'null',
]);
```

#### **I. Payment Method Mapping**
```php
Log::info('=== [MIDTRANS] PAYMENT METHOD MAPPING ===', [
    'original_payment_type' => $notif->payment_type,
    'mapped_payment_method' => $metodePembayaran,
    'old_payment_method' => $oldPaymentMethod,
    'new_payment_method' => $metodePembayaran,
    'mapping_applied' => isset($paymentTypeMap[$notif->payment_type])
]);
```

#### **J. Order Update Completed**
```php
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
```

#### **K. Webhook Processing Completed**
```php
Log::info('=== [MIDTRANS] WEBHOOK PROCESSING COMPLETED ===', [
    'order_found' => $order ? true : false,
    'order_id' => $order ? $order->id : null,
    'order_code' => $order ? $order->order_code : null,
    'final_status' => $order ? $order->status : null,
    'final_payment_method' => $order ? $order->metode_pembayaran : null,
    'response' => ['ok' => true]
]);
```

### **2. Script Test Webhook dengan Logging Detail**

#### **File:** `test_webhook_detailed.php`

**Fitur Script:**
- Simulasi data webhook QRIS dan Bank Transfer
- Command curl untuk test webhook
- Monitoring log real-time
- Cek order yang akan diupdate

## 🚀 **Cara Menggunakan Logging:**

### **1. Monitor Log Real-time**
```bash
tail -f storage/logs/laravel.log | grep MIDTRANS
```

### **2. Test Webhook Manual**
```bash
# Test QRIS Webhook
curl -X POST http://localhost/midtrans/notification \
  -H 'Content-Type: application/json' \
  -H 'User-Agent: Midtrans-Webhook/1.0' \
  -d '{
    "order_id": "ORD-20250903031643748-7XWVPZ",
    "transaction_status": "settlement",
    "payment_type": "qris",
    "gross_amount": "220000"
  }'

# Test Bank Transfer Webhook
curl -X POST http://localhost/midtrans/notification \
  -H 'Content-Type: application/json' \
  -H 'User-Agent: Midtrans-Webhook/1.0' \
  -d '{
    "order_id": "ORD-20250903030005812-SFRAPD",
    "transaction_status": "settlement",
    "payment_type": "bank_transfer",
    "gross_amount": "100000"
  }'
```

### **3. Jalankan Script Test**
```bash
php test_webhook_detailed.php
```

## 📋 **Contoh Output Log:**

### **QRIS Webhook Log:**
```
[2025-09-03 15:30:00] local.INFO: === [MIDTRANS] WEBHOOK RECEIVED - RAW DATA ===
[2025-09-03 15:30:00] local.INFO: === [MIDTRANS] WEBHOOK RECEIVED - PARSED DATA ===
[2025-09-03 15:30:00] local.INFO: === [MIDTRANS] CUSTOMER DETAILS ===
[2025-09-03 15:30:00] local.INFO: === [MIDTRANS] ITEM DETAILS ===
[2025-09-03 15:30:00] local.INFO: === [MIDTRANS] FULL NOTIFICATION OBJECT ===
[2025-09-03 15:30:00] local.INFO: === [MIDTRANS] SESSION DATA ===
[2025-09-03 15:30:00] local.INFO: === [MIDTRANS] UPDATING EXISTING ORDER ===
[2025-09-03 15:30:00] local.INFO: === [MIDTRANS] PAYMENT METHOD MAPPING ===
[2025-09-03 15:30:00] local.INFO: === [MIDTRANS] ORDER UPDATE COMPLETED ===
[2025-09-03 15:30:00] local.INFO: === [MIDTRANS] WEBHOOK PROCESSING COMPLETED ===
```

## ✅ **Status: LOGGING SELESAI**

**Perbaikan yang telah dilakukan:**
- ✅ Tambah logging raw request data
- ✅ Tambah logging parsed notification data
- ✅ Tambah logging customer details
- ✅ Tambah logging item details
- ✅ Tambah logging billing/shipping address
- ✅ Tambah logging full notification object
- ✅ Tambah logging session data
- ✅ Tambah logging order update process
- ✅ Tambah logging payment method mapping
- ✅ Tambah logging order update completed
- ✅ Buat script test webhook dengan logging detail

**Sekarang semua data yang dikirim Midtrans akan tercatat dengan detail di log!** 🎉

### **Langkah Selanjutnya:**
1. **Test webhook** dengan script `test_webhook_detailed.php`
2. **Monitor log** untuk melihat semua data yang dikirim Midtrans
3. **Verifikasi** bahwa webhook berfungsi dengan benar
4. **Debug** masalah webhook jika masih ada
5. **Atur webhook URL** di Midtrans Dashboard

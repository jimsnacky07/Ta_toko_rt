<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

// Opsional: kalau project-mu memang punya model-model ini
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Pesanan;
use App\Models\DetailPesanan;

class CheckoutController extends Controller
{
    /**
     * GET /user/checkout
     */
    public function index(Request $request)
    {
        $cart     = session('cart', []); // [product_id => qty]
        $items    = new Collection();
        $subtotal = 0;

        if (!empty($cart)) {
            $products = Product::whereIn('id', array_keys($cart))->get();

            foreach ($products as $p) {
                $qty = max(0, (int)($cart[$p->id] ?? 0));
                if ($qty <= 0) continue;

                $nama  = $p->name  ?? $p->nama  ?? $p->product_name ?? 'Produk';
                $harga = (int)($p->harga ?? $p->price ?? 0);
                if ($harga <= 0) continue;
                $bahan     = $request->input('bahan', '-');
                $rawImage = $p->image ?? $p->gambar ?? null;
                $imgUrl   = $this->normalizeImageUrl($rawImage);
                $line     = $qty * $harga;

                $items->push([
                    'id'         => $p->id,
                    'name'       => $nama,
                    'harga'      => $harga,
                    'qty'        => $qty,
                    'image'      => $rawImage,
                    'image_url'  => $imgUrl,
                    'line_total' => $line,
                ]);

                $subtotal += $line;
            }

            if ($items->isNotEmpty()) {
                $pickup   = $request->query('pickup_method', 'store'); // store|jnt
                $shipping = ($pickup === 'jnt') ? 32000 : 0;
                $total    = $subtotal + $shipping;

                $first = $items->first();

                $data  = [
                    'pesanan_id'    => 0,
                    'order_id'      => 'OP-' . now()->format('YmdHis'),
                    'product_name'  => $first['name'],
                    'harga'         => $first['harga'],
                    'qty'           => $first['qty'],
                    'color'         => null,
                    'size'          => null,
                    'image'         => $first['image_url'] ?? null,
                    'notes'         => null,
                    'pickup_method' => $pickup,
                    'subtotal'      => $subtotal,
                    'shipping'      => $shipping,
                    'total'         => $total,
                ];

                return view('user.checkout.index', [
                    'data'     => $data,
                    'items'    => $items,
                    'subtotal' => $subtotal,
                    'shipping' => $shipping,
                    'total'    => $total,
                ]);
            }
        }

        // ---------- Fallback: checkout_data ----------
        $data = (array) $request->session()->get('checkout_data', [
            'pesanan_id'    => 0,
            'order_id'      => 'OP-' . now()->format('YmdHis'),
            'product_name'  => 'Produk',
            'harga'         => 0,
            'qty'           => 1,
            'color'         => null,
            'size'          => null,
            'image'         => null,
            'notes'         => null,
            'pickup_method' => 'store',
        ]);

        $data['harga'] = (int)($data['harga'] ?? $data['price'] ?? 0);
        $data['qty']   = (int)($data['qty']   ?? $data['jumlah'] ?? 1);
        if (!isset($data['product_name'])) {
            $data['product_name'] = $data['nama_produk'] ?? $data['name'] ?? 'Produk';
        }
        if (!isset($data['pickup_method'])) {
            $data['pickup_method'] = 'store';
        }

        if (!empty($data['image'])) {
            $data['image'] = $this->normalizeImageUrl($data['image']);
        } elseif (!empty($data['gambar'])) {
            $data['image'] = $this->normalizeImageUrl($data['gambar']);
        }

        $data = $this->computeTotals($data);

        return view('user.checkout.index', [
            'data'     => $data,
            'items'    => collect(),
            'subtotal' => $data['subtotal'],
            'shipping' => $data['shipping'],
            'total'    => $data['total'],
        ]);
    }

    /**
     * POST /user/checkout/store
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pesanan_id'    => 'nullable|integer',
            'order_id'      => 'nullable|string',
            'product_name'  => 'required|string',
            'harga'         => 'required|numeric|min:0',
            'qty'           => 'required|integer|min:1',
            'color'         => 'nullable|string',
            'size'          => 'nullable|string',
            'image'         => 'nullable|string',
            'notes'         => 'nullable|string',
            'pickup_method' => 'nullable|in:store,jnt',
        ]);

        $validated['order_id']      = $validated['order_id']      ?? ('OP-' . now()->format('YmdHis'));
        $validated['pickup_method'] = $validated['pickup_method'] ?? 'store';

        if (!empty($validated['image'])) {
            $validated['image'] = $this->normalizeImageUrl($validated['image']);
        }

        $validated = $this->computeTotals($validated);
        $request->session()->put('checkout_data', $validated);

        return redirect()->route('user.checkout')
            ->with('success', 'Data checkout disimpan.');
    }

    /** (Opsional) POST dari halaman produk langsung ke pembayaran. */
    public function create(Request $request)
    {
        $data = $request->validate([
            'pesanan_id'    => 'nullable|integer',
            'product_name'  => 'required|string',
            'harga'         => 'required|numeric|min:0',
            'color'         => 'nullable|string',
            'size'          => 'nullable|string',
            'qty'           => 'required|integer|min:1',
            'image'         => 'nullable|string',
            'notes'         => 'nullable|string',
            'pickup_method' => 'nullable|in:store,jnt',
            'order_id'      => 'nullable|string',
        ]);

        // Untuk order custom, tetap gunakan format ORD-2025xxx
        $isCustom = isset($data['order_id']) && str_starts_with(strtolower($data['order_id']), 'op-');
        if (!$isCustom) {
            // Jika bukan custom, kosongkan order_id, nanti diisi oleh proses order produk
            unset($data['order_id']);
        }
        $data['pickup_method'] = $data['pickup_method'] ?? 'store';

        if (!empty($data['image'])) {
            $data['image'] = $this->normalizeImageUrl($data['image']);
        }

        $data = $this->computeTotals($data);
        session(['checkout_data' => $data]);

        return redirect()->route('user.payment.show', [
            'order_id'     => $data['order_id'] ?? '',
            'total'        => $data['total'],
            'product_name' => $data['product_name'],
            'image'        => $data['image'] ?? null,
        ]);
    }

    /** Contoh simpan pesanan ke DB (opsional). */
    public function storeToDatabase(Request $r)
    {
        $noTelp = $r->input('no_telp') ?? $r->input('no_telpon');

        $data = $r->validate([
            'email' => 'required|email',
            'nama'  => 'nullable|string',
            'cart'  => 'required|array',
        ]);

        $data['no_telp'] = $noTelp;

        return DB::transaction(function () use ($data, $r) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'nama'     => $data['nama'] ?? null,
                    'no_telp'  => $data['no_telp'] ?? null,
                    'password' => bcrypt(Str::random(12)),
                    'level'    => 'user',
                ]
            );

            $items = collect($data['cart']);
            $total = $items->sum(fn($i) => intval($i['qty']) * intval($i['harga']));

            $pesanan = $user->pesanan()->create([
                'order_date'  => now()->toDateString(),
                'status'      => 'menunggu',
                'total_harga' => $total,
            ]);

            foreach ($items as $i) {
                $pesanan->details()->create([
                    'product_id'     => $i['product_id'] ?? null,
                    'jumlah'         => intval($i['qty']),
                    'harga_satuan'   => intval($i['harga']),
                    'total_harga'    => intval($i['qty']) * intval($i['harga']),
                    'catatan_khusus' => $i['catatan'] ?? null,
                ]);
            }

            $r->session()->forget('cart');

            return redirect()->route('user.checkout')
                ->with('success', 'Checkout berhasil. Pesanan tersimpan!');
        });
    }

    private function computeTotals(array $data): array
    {
        $harga  = (int)($data['harga'] ?? 0);
        $qty    = (int)($data['qty'] ?? 1);
        $method = $data['pickup_method'] ?? 'store';

        $subtotal = $harga * $qty;
        $shipping = ($method === 'jnt') ? 32000 : 0;

        $data['subtotal'] = $subtotal;
        $data['shipping'] = $shipping;
        $data['total']    = $subtotal + $shipping;

        return $data;
    }

    private function normalizeImageUrl(?string $file): ?string
    {
        if (!$file) return null;

        if (preg_match('#^https?://#i', $file)) {
            return $file;
        }
        if (str_starts_with($file, '/')) {
            return url($file);
        }
        if (str_starts_with($file, 'storage/')) {
            return asset($file);
        }
        return asset('storage/' . ltrim($file, '/'));
    }

    /**
     * POST /midtrans/create-snap-token
     * Simpan order pending -> minta Snap token -> kembalikan token
     */
    public function createSnapToken(Request $request)
    {
        // 1) VALIDASI (TANPA order_id dari form)
        $validated = $request->validate([
            'subtotal'        => ['required', 'integer', 'min:1'],
            'shipping'        => ['required', 'integer', 'min:0'],
            'total'           => ['required', 'integer', 'min:1'],
            'product_name'    => ['required', 'string', 'max:100'],
            'harga'           => ['required', 'integer', 'min:1'],
            'qty'             => ['required', 'integer', 'min:1'],
            'pickup_method'   => ['required', 'in:store,jnt'],
            'customer_name'   => ['required', 'string', 'max:100'],
            'customer_email'  => ['required', 'email', 'max:100'],
            'customer_phone'  => ['required', 'string', 'max:20'],
            'size'            => ['nullable', 'string', 'max:20'],
            'notes'           => ['nullable', 'string', 'max:255'],
        ]);

        $subtotal = (int) $validated['subtotal'];
        $shipping = (int) $validated['shipping'];
        $total    = (int) $validated['total'];
        $harga    = (int) $validated['harga'];
        $qty      = (int) $validated['qty'];
        $name     = (string) $validated['product_name'];
        $orderId  = 'OP-' . now()->format('YmdHisv') . '-' . Str::upper(Str::random(6));
        $userId   = Auth::id();
        $userEmail = Auth::user() ? Auth::user()->email : null;
        // Ambil data keranjang dari session. Aplikasi ini menggunakan kunci 'keranjang'.
        // Tetap beri fallback ke 'cart' untuk kompatibilitas lama.
        $keranjang = session('keranjang', []);
        $cart      = session('cart', []);
        $items = [];
        $selectedCartIds = [];

        // Gunakan total dari form, bukan dari cart
        $finalTotal = $total; // Simpan total dari form

        // Validasi total tidak boleh 0
        if ($finalTotal <= 0) {
            Log::error('[CHECKOUT] Total amount tidak valid', [
                'total' => $finalTotal,
                'subtotal' => $subtotal,
                'shipping' => $shipping
            ]);
            return response()->json(['error' => 'Total pembayaran tidak valid. Silakan coba lagi.'], 400);
        }

        if (!empty($keranjang)) {
            // Struktur item 'keranjang' di app ini: {id: 'prod:ID', product_id, nama, harga, qty, gambar}
            foreach ($keranjang as $kItem) {
                $pid = (int)($kItem['product_id'] ?? (preg_match('/prod:(\d+)/', (string)($kItem['id'] ?? ''), $m) ? ($m[1] ?? 0) : 0));
                $qty = max(0, (int)($kItem['qty'] ?? 0));
                $harga = (int)($kItem['harga'] ?? 0);
                if ($qty <= 0 || $harga <= 0) continue;
                // Pastikan fabric_type diisi dari bahan produk (fallback jika tidak ada di item keranjang)
                $productForType = $pid ? Product::find($pid) : null;
                $fabricType = $kItem['bahan'] ?? ($productForType->bahan ?? '-');

                $items[] = [
                    'product_id'      => $pid ?: null,
                    'garment_type'    => (string)($kItem['nama'] ?? 'Produk'),
                    'fabric_type'     => $fabricType,
                    'size'            => $request->input('size', '-') ?? '-',
                    'price'           => $harga,
                    'quantity'        => $qty,
                    'total_price'     => $harga * $qty,
                    'special_request' => $request->input('notes', ''),
                ];
                if ($pid) $selectedCartIds[] = (int)$pid;
            }
        } elseif (!empty($cart)) {
            // Kompatibilitas lama: cart = [product_id => qty]
            $products = Product::whereIn('id', array_keys($cart))->get();
            foreach ($products as $p) {
                $qty = max(0, (int)($cart[$p->id] ?? 0));
                if ($qty <= 0) continue;
                $harga = (int)($p->harga ?? $p->price ?? 0);
                if ($harga <= 0) continue;
                $items[] = [
                    'product_id'      => $p->id,
                    'garment_type'    => $p->name ?? $p->nama ?? 'Produk',
                    'fabric_type'     => $p->bahan,
                    'size'            => $request->input('size', '-') ?? '-',
                    'price'           => $harga,
                    'quantity'        => $qty,
                    'total_price'     => $harga * $qty,
                    'special_request' => $request->input('notes', ''),
                ];
                $selectedCartIds[] = $p->id;
            }
        }

        Log::info('[CHECKOUT] Order akan dibayar', [
            'user_id' => $userId,
            'user_email' => $userEmail,
            'order_code' => $orderId,
            'total_amount' => $finalTotal,
            'items' => $items,
        ]);

        // Jika belum ada item dari keranjang/cart, bentuk 1 item dari data form checkout
        if (empty($items)) {
            $formQty   = max(1, (int) $request->input('qty', 1));
            $formPrice = max(0, (int) $request->input('harga', 0));
            $formName  = (string) $request->input('product_name', $name);
            $formSize  = (string) $request->input('size', '-');
            $formNote  = (string) $request->input('notes', '-');
            $formPid   = $request->input('product_id');
            $formBahan = $request->input('bahan', '-');
            if ($formPrice > 0 && $formQty > 0) {
                // Jika product_id valid dan form tidak membawa bahan, ambil dari DB
                if (($formBahan === '-' || $formBahan === null || $formBahan === '') && $formPid) {
                    $pf = Product::find($formPid);
                    if ($pf && !empty($pf->bahan)) {
                        $formBahan = $pf->bahan;
                    }
                }
                $items[] = [
                    'product_id'      => ($formPid === '' || $formPid === 0) ? null : $formPid,
                    'garment_type'    => $formName ?: 'Produk',
                    'fabric_type'     => $formBahan,
                    'size'            => $formSize ?: '-',
                    'price'           => (int) $formPrice,
                    'quantity'        => (int) $formQty,
                    'total_price'     => (int) $formPrice * (int) $formQty,
                    'special_request' => $formNote,
                ];
            }
        }

        // 2) BUAT ORDER DI DATABASE
        try {
            $order = Order::create([
                'user_id'           => $userId,
                'kode_pesanan'      => $orderId,
                'order_code'        => $orderId,
                'status'            => 'menunggu',
                'total_harga'       => $finalTotal,
                'total_amount'      => $finalTotal,
                'metode_pembayaran' => 'pending', // Akan diupdate oleh webhook sesuai pilihan user
            ]);

            Log::info('[CHECKOUT] Order berhasil dibuat di database', [
                'order_id' => $order->id,
                'order_code' => $orderId
            ]);

            // 3) BUAT ORDER ITEMS
            $pickupMethod = $validated['pickup_method'] ?? 'store';
            $initialPickupStatus = $pickupMethod === 'jnt' ? 'Dikirim Via Kurir' : 'Diambil Ditoko';
            foreach ($items as $item) {
                OrderItem::create([
                    'order_id'        => $order->id,
                    'product_id'      => $item['product_id'],
                    'garment_type'    => $item['garment_type'],
                    'fabric_type'     => $item['fabric_type'],
                    'size'            => $item['size'],
                    'price'           => $item['price'],
                    'quantity'        => $item['quantity'],
                    'total_price'     => $item['total_price'],
                    'special_request' => $item['special_request'],
                    'status'          => $initialPickupStatus,
                ]);
            }

            Log::info('[CHECKOUT] Order items berhasil dibuat', [
                'order_id' => $order->id,
                'items_count' => count($items)
            ]);
        } catch (\Exception $e) {
            Log::error('[CHECKOUT] Gagal membuat order di database', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Gagal membuat order: ' . $e->getMessage()], 500);
        }

        // 4) SIMPAN KE SESSION UNTUK WEBHOOK
        $pendingOrderArr = [
            'user_id'           => $userId,
            'user_email'        => $userEmail,
            'order_code'        => $orderId,
            'total_amount'      => $finalTotal,
            'items'             => $items,
            'selected_cart_ids' => $selectedCartIds,
            'pickup_method'     => $validated['pickup_method'] ?? 'store',
        ];
        session(['pending_order' => $pendingOrderArr]);
        Log::debug('[CHECKOUT] Simpan session pending_order', $pendingOrderArr);

        // 5) GENERATE SNAP TOKEN
        try {
            // Set Midtrans configuration
            \Midtrans\Config::$serverKey = config('midtrans.server_key');
            \Midtrans\Config::$clientKey = config('midtrans.client_key');
            \Midtrans\Config::$isProduction = config('midtrans.is_production');
            \Midtrans\Config::$isSanitized = config('midtrans.is_sanitized');
            \Midtrans\Config::$is3ds = config('midtrans.is_3ds');

            $params = [
                'transaction_details' => [
                    'order_id'     => $orderId,
                    'gross_amount' => (int) $finalTotal,
                ],
                'customer_details' => [
                    'first_name' => $validated['customer_name'],
                    'email'      => $validated['customer_email'],
                    'phone'      => $validated['customer_phone'],
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
                    'finish'   => route('user.user.midtrans.finish'),
                    'unfinish' => route('user.user.midtrans.unfinish'),
                    'error'    => route('user.user.midtrans.error'),
                ],
            ];

            $snapToken = \Midtrans\Snap::getSnapToken($params);

            Log::info('[CHECKOUT] Snap token berhasil dibuat', [
                'order_code' => $orderId,
                'token' => $snapToken
            ]);

            return response()->json(['token' => $snapToken]);
        } catch (\Exception $e) {
            Log::error('[CHECKOUT] Gagal membuat snap token', [
                'error' => $e->getMessage(),
                'order_code' => $orderId
            ]);
            return response()->json(['error' => 'Gagal membuat token pembayaran: ' . $e->getMessage()], 500);
        }
    }
}

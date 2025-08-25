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
                    'order_id'      => 'ORD-' . now()->format('YmdHis'),
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
            'order_id'      => 'ORD-' . now()->format('YmdHis'),
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

        $validated['order_id']      = $validated['order_id']      ?? ('ORD-' . now()->format('YmdHis'));
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

        $data['order_id']      = $data['order_id']      ?? ('ORD-' . now()->format('YmdHis'));
        $data['pickup_method'] = $data['pickup_method'] ?? 'store';

        if (!empty($data['image'])) {
            $data['image'] = $this->normalizeImageUrl($data['image']);
        }

        $data = $this->computeTotals($data);
        session(['checkout_data' => $data]);

        return redirect()->route('user.payment.show', [
            'order_id'     => $data['order_id'],
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
            $total = $items->sum(fn ($i) => intval($i['qty']) * intval($i['harga']));

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
    try {
        $validated = $request->validate([
            'subtotal'        => ['required','integer','min:1'],
            'shipping'        => ['required','integer','min:0'],
            'total'           => ['required','integer','min:1'],
            'product_name'    => ['required','string','max:100'],
            'harga'           => ['required','integer','min:1'],
            'qty'             => ['required','integer','min:1'],
            'pickup_method'   => ['required','in:store,jnt'],
            'customer_name'   => ['required','string','max:100'],
            'customer_email'  => ['required','email','max:100'],
            'customer_phone'  => ['required','string','max:20'],
        ]);
    } catch (\Illuminate\Validation\ValidationException $ve) {
        return response()->json([
            'message'  => 'Validasi gagal',
            'errors'   => $ve->errors(),
            'received' => $request->all(),
        ], 422);
    }

    // 2) CAST angka + cek total
    $subtotal = (int) $validated['subtotal'];
    $shipping = (int) $validated['shipping'];
    $total    = (int) $validated['total'];
    $harga    = (int) $validated['harga'];
    $qty      = (int) $validated['qty'];
    $name     = (string) $validated['product_name'];

    if ($total !== ($subtotal + $shipping)) {
        return response()->json([
            'message' => 'Total tidak sesuai (subtotal + shipping).',
            'debug'   => compact('subtotal','shipping','total'),
        ], 422);
    }

    // 3) SELALU bikin order_id unik di server
    // format: ORD-YYYYMMDDHHIISSmmm-ABC123 (hingga milidetik + random)
    $orderId = 'ORD-'.now()->format('YmdHisv').'-'.Str::upper(Str::random(6));

    // (Opsional) pastikan unik terhadap tabel orders jika ada
    if (class_exists(\App\Models\Order::class)) {
        $tries = 0;
        while (\App\Models\Order::where('order_id', $orderId)->exists() && $tries < 3) {
            $orderId = 'ORD-'.now()->format('YmdHisv').'-'.Str::upper(Str::random(6));
            $tries++;
        }
    }

    // 4) Konfig Midtrans
    \Midtrans\Config::$serverKey    = config('midtrans.server_key');
    \Midtrans\Config::$isProduction = (bool) config('midtrans.is_production', false);
    \Midtrans\Config::$isSanitized  = true;
    \Midtrans\Config::$is3ds        = true;

    if (empty(\Midtrans\Config::$serverKey)) {
        return response()->json([
            'message' => 'Server Key Midtrans belum di-set. Cek .env & config/midtrans.php',
        ], 422);
    }

    // 5) Siapkan payload & customer
    $customerName  = (string) $validated['customer_name'];
    $customerEmail = (string) $validated['customer_email'];
    $customerPhone = (string) $validated['customer_phone'];

    $items = [
        ['id' => 'item-1',   'price' => $harga,    'quantity' => $qty, 'name' => $name],
    ];
    if ($shipping > 0) {
        $items[] = ['id' => 'shipping', 'price' => $shipping, 'quantity' => 1,  'name' => 'Ongkir'];
    }

    $params = [
        'transaction_details' => [
            'order_id'     => $orderId,
            'gross_amount' => $total,
        ],
        'item_details' => $items,
        'customer_details' => [
            'first_name' => $customerName,
            'email'      => $customerEmail,
            'phone'      => $customerPhone,
        ],
    ];

    // 6) SIMPAN ORDER "PENDING" KE DB (orders atau pesanan)
    $orderInternal = null;
    DB::beginTransaction();
    try {
        // coba ambil product_id dari session cart (jika ada)
        $productId = null;
        $cart = session('cart', []);
        if (!empty($cart)) {
            $productId = array_key_first($cart);
        }

        if (class_exists(\App\Models\Order::class)) {
            // Tabel orders milikmu
            $orderInternal = \App\Models\Order::create([
                'order_id'       => $orderId,
                'user_id'        => Auth::id(),
                'customer_name'  => $customerName,
                'customer_email' => $customerEmail,
                'customer_phone' => $customerPhone,
                'subtotal'       => $subtotal,
                'shipping'       => $shipping,
                'total'          => $total,
                'pickup_method'  => $validated['pickup_method'],
                'status'         => 'pending',
            ]);

            if (class_exists(\App\Models\OrderItem::class)) {
                \App\Models\OrderItem::create([
                    'order_id'   => $orderInternal->id,
                    'sku'        => 'item-1',
                    'name'       => $name,
                    'price'      => $harga,
                    'qty'        => $qty,
                    'line_total' => $harga * $qty,
                    'product_id' => $productId,
                ]);
                if ($shipping > 0) {
                    \App\Models\OrderItem::create([
                        'order_id'   => $orderInternal->id,
                        'sku'        => 'shipping',
                        'name'       => 'Ongkir',
                        'price'      => $shipping,
                        'qty'        => 1,
                        'line_total' => $shipping,
                    ]);
                }
            }
        } elseif (class_exists(\App\Models\Pesanan::class)) {
    // cari user (kalau guest biarkan null/0 sesuai skema)
    $userId = \Illuminate\Support\Facades\Auth::id();

    // SIMPAN HEADER pesanan (tabel: pesanan)
    $orderInternal = \App\Models\Pesanan::create([
        'user_id'      => $userId,                       // penting utk "Nama Pemesan"
        'order_id'     => $orderId,                      // kalau tabelmu punya kolom ini
        'order_date'   => now()->toDateString(),         // dipakai kolom "Tanggal/Hari"
        'status'       => 'menunggu',                    // agar tidak "Status Tidak Diketahui"
        'total_harga'  => $total,
    ]);

    // SIMPAN DETAIL (tabel: detail_pesanan)
    if (class_exists(\App\Models\DetailPesanan::class)) {
        // ambil product id pertama dari cart (kalau ada)
        $productId = null;
        $cart = session('cart', []);
        if (!empty($cart)) {
            $productId = array_key_first($cart);
        }

        // kalau relasi ada:
        try {
            $orderInternal->details()->create([
                'product_id'     => $productId,
                'jumlah'         => $qty,
                'harga_satuan'   => $harga,
                'total_harga'    => $harga * $qty,
                'catatan_khusus' => null,
            ]);
        } catch (\Throwable $e) {
            // kalau tidak ada relasi, create manual:
            \App\Models\DetailPesanan::create([
                'pesanan_id'     => $orderInternal->id,
                'product_id'     => $productId,
                'jumlah'         => $qty,
                'harga_satuan'   => $harga,
                'total_harga'    => $harga * $qty,
                'catatan_khusus' => null,
            ]);
        }
    }
}
        DB::commit();
    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('[ORDER] gagal simpan order pending', ['msg' => $e->getMessage()]);
        return response()->json(['message' => 'Gagal menyimpan order'], 422);
    }

    // 7) MINTA SNAP TOKEN & simpan ke order
    try {
        $snapToken = \Midtrans\Snap::getSnapToken($params);

        if ($orderInternal) {
            try {
                $orderInternal->update(['snap_token' => $snapToken]);
            } catch (\Throwable $e) {
                // kolom snap_token bisa saja belum ada—abaikan
            }
        }

        return response()->json(['token' => $snapToken]);
    } catch (\Throwable $e) {
        Log::error('Midtrans error: '.$e->getMessage(), [
            'params' => $params,
            'trace'  => $e->getTraceAsString(),
        ]);
        return response()->json([
            'message' => 'Midtrans error',
            'error'   => $e->getMessage(),
        ], 422);
    }
}

}

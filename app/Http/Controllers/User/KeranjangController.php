<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

// Midtrans (untuk redirect & token)
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap   as MidtransSnap;

class KeranjangController extends Controller
{
    /* ===================== Helpers session ===================== */

    // Ambil & normalisasi keranjang jadi list item seragam
    private function ambilKeranjang(Request $r): array
    {
        $raw  = $r->session()->get('keranjang', []);
        $norm = $this->normalize($raw);

        // simpan balik supaya id/fixed fields melekat di session
        $this->simpanKeranjang($r, $norm);
        return $norm;
    }

    // Simpan kembali ke session (sebagai list berindex numerik)
    private function simpanKeranjang(Request $r, array $list): void
    {
        $r->session()->put('keranjang', array_values($list));
    }

    // Utility: ubah “Rp 120.000” → 120000
    private function toInt($val): int
    {
        if (is_int($val)) return $val;
        if (is_numeric($val)) return (int)$val;
        return (int) preg_replace('/[^\d]/', '', (string) $val);
    }

    /**
     * Normalisasi struktur keranjang agar seragam bertipe list.
     * - Support struktur lama keyed-by-id -> diubah jadi list item 'prod'
     * - Pastikan field penting ada baik untuk 'prod' maupun 'oc'
     */
    private function normalize(array $raw): array
    {
        // Jika associative keyed-by-id (struktur lama)
        $isAssoc = (array_keys($raw) !== range(0, max(0, count($raw) - 1)));
        if ($isAssoc) {
            $list = [];
            foreach ($raw as $id => $v) {
                if (is_array($v) && isset($v['nama'], $v['harga'])) {
                    $list[] = [
                        'type'   => 'prod',
                        'id'     => (string) $id,
                        'nama'   => $v['nama'],
                        'harga'  => $this->toInt($v['harga'] ?? 0),
                        'qty'    => max(1, (int)($v['qty'] ?? 1)),
                        'gambar' => $v['gambar'] ?? ($v['image'] ?? null),
                        'image'  => $v['image']  ?? ($v['gambar'] ?? null),
                        'color'  => $v['color']  ?? null,
                        'size'   => $v['size']   ?? null,
                        'product_id' => (string)($v['product_id'] ?? ''),
                    ];
                } elseif (is_array($v) && isset($v['jenis_pakaian'])) {
                    // jaga-jaga kalau pernah menyimpan 'oc' keyed-by-id
                    $jumlah = max(1, (int)($v['jumlah'] ?? 1));
                    $base   = $this->toInt($v['prices']['base_price']  ?? 0);
                    $add    = $this->toInt($v['prices']['fabric_add']  ?? 0);
                    $unit   = $this->toInt($v['prices']['total_price'] ?? ($base + $add));
                    $list[] = [
                        'type'   => 'oc',
                        'id'     => (string) $id,
                        'jumlah' => $jumlah,
                        'prices' => [
                            'base_price'  => $base,
                            'fabric_add'  => $add,
                            'total_price' => $unit,
                            'grand_total' => $this->toInt($v['prices']['grand_total'] ?? ($unit * $jumlah)),
                        ],
                    ] + $v;
                }
            }
            return $list;
        }

        // Sudah list: pastikan field penting ada
        return array_map(function ($it) {
            if (!is_array($it)) return $it;

            // Tebak tipe bila belum ada
            if (!isset($it['type'])) {
                $it['type'] = isset($it['jenis_pakaian']) ? 'oc' : 'prod';
            }

            if ($it['type'] === 'prod') {
                $it['id']        = (string)($it['id'] ?? ($it['row_id'] ?? $it['product_id'] ?? 'prod:' . Str::upper(Str::random(10))));
                $it['qty']       = max(1, (int)($it['qty'] ?? 1));
                $it['harga']     = $this->toInt($it['harga'] ?? ($it['price'] ?? 0));
                $it['gambar']    = $it['gambar'] ?? ($it['image'] ?? null);
                $it['image']     = $it['image']  ?? ($it['gambar'] ?? null);
                $it['product_id'] = (string)($it['product_id'] ?? '');
            } else { // 'oc'
                $it['id']     = (string)($it['id'] ?? ($it['row_id'] ?? 'oc:' . Str::upper(Str::random(10))));
                $it['jumlah'] = max(1, (int)($it['jumlah'] ?? 1));
                $base   = $this->toInt($it['prices']['base_price']  ?? 0);
                $add    = $this->toInt($it['prices']['fabric_add']  ?? 0);
                $unit   = $this->toInt($it['prices']['total_price'] ?? ($base + $add));
                $it['prices']['total_price'] = $unit;
                $it['prices']['grand_total'] = $this->toInt($it['prices']['grand_total'] ?? ($unit * $it['jumlah']));
            }
            return $it;
        }, $raw);
    }

    /* ===================== Pages ===================== */

    // Tampilkan keranjang (gabungan prod + oc)
    public function index(Request $r)
    {
        $keranjang = $this->ambilKeranjang($r);

        $totalProd = collect($keranjang)
            ->where('type', 'prod')
            ->sum(fn($i) => $this->toInt($i['harga'] ?? 0) * (int)($i['qty'] ?? 1));

        $totalOC = collect($keranjang)
            ->where('type', 'oc')
            ->sum(fn($i) => $this->toInt($i['prices']['grand_total'] ?? 0));

        $grand = $totalProd + $totalOC;

        return view('user.keranjang.index', compact('keranjang', 'grand', 'totalProd', 'totalOC'));
    }

    /* ===================== Actions: tambah item ===================== */

    /**
     * Tambah item ke keranjang.
     * - type=custom  → item “Order Custom”
     * - type=prod    → produk biasa (ambil data dari DB bila product_id dikirim)
     */
    public function tambah(Request $r)
    {
        $type = $r->input('type', 'prod');

        if ($type === 'custom') {
            $jumlah = max(1, (int) $r->input('qty', 1));
            $base   = $this->toInt($r->input('base_price', 0));
            $add    = $this->toInt($r->input('fabric_add', 0));

            $unit       = $base + $add;
            $grandTotal = $unit * $jumlah;

            $row = [
                'type'   => 'oc',
                'id'     => 'oc:' . Str::upper(Str::random(10)),
                'jumlah' => $jumlah,
                'title'  => $r->input('title', 'Order Custom'),
                'subtitle' => $r->input('subtitle', null),
                'jenis_pakaian' => $r->input('jenis_pakaian'),
                'jenis_kain'    => $r->input('jenis_kain'),
                'pickup'        => $r->input('pickup'),
                'request'       => $r->input('request'),
                'prices' => [
                    'base_price'  => $base,
                    'fabric_add'  => $add,
                    'total_price' => $unit,
                    'grand_total' => $grandTotal,
                ],
            ];

            $keranjang   = $this->ambilKeranjang($r);
            $keranjang[] = $row;
            $this->simpanKeranjang($r, $keranjang);

            return redirect()->route('keranjang.index')->with('success', 'Item custom ditambahkan ke keranjang.');
        }

        // Produk Biasa (prod)
        $qty       = max(1, (int) $r->input('qty', 1));
        $productId = $r->input('product_id');
        $nama      = $r->input('nama', 'Produk');
        $harga     = $this->toInt($r->input('harga', 0));
        $gambar    = $r->input('gambar') ?: $r->input('image');

        if ($productId) {
            if ($product = Product::find($productId)) {
                $nama   = $product->name ?? 'Produk Tidak Ditemukan';
                $harga  = $this->toInt($product->price ?? 0);
                $gambar = $product->image_url ?? $product->image ?? asset('images/placeholder.png');
            }
        }

        $row = [
            'type'       => 'prod',
            'id'         => 'prod:' . Str::upper(Str::random(10)),
            'product_id' => (string) $productId,
            'nama'       => $nama,
            'harga'      => $harga,
            'qty'        => $qty,
            'gambar'     => $gambar,
            'image'      => $gambar,
            'color'      => $r->input('color'),
            'size'       => $r->input('size'),
        ];

        $keranjang   = $this->ambilKeranjang($r);
        $keranjang[] = $row;
        $this->simpanKeranjang($r, $keranjang);

        return redirect()->route('keranjang.index')->with('success', 'Produk ditambahkan ke keranjang.');
    }

    /* ===================== Update qty/jumlah ===================== */

    public function update(Request $r)
    {
        $data = $r->validate([
            'id'  => 'required',
            'aksi' => 'required|in:inc,dec,set',
            'qty' => 'nullable|integer|min:1',
        ]);

        $keranjang = $this->ambilKeranjang($r);

        foreach ($keranjang as &$it) {
            if ((string)($it['id'] ?? '') !== (string)$data['id']) continue;

            if (($it['type'] ?? '') === 'prod') {
                $q = (int)($it['qty'] ?? 1);
                if ($data['aksi'] === 'inc') $q++;
                elseif ($data['aksi'] === 'dec') $q = max(1, $q - 1);
                else $q = max(1, (int)($data['qty'] ?? 1));
                $it['qty'] = $q;
            } else { // 'oc'
                $q = (int)($it['jumlah'] ?? 1);
                if ($data['aksi'] === 'inc') $q++;
                elseif ($data['aksi'] === 'dec') $q = max(1, $q - 1);
                else $q = max(1, (int)($data['qty'] ?? 1));
                $it['jumlah'] = $q;

                $base = $this->toInt($it['prices']['base_price']  ?? 0);
                $add  = $this->toInt($it['prices']['fabric_add']  ?? 0);
                $unit = $this->toInt($it['prices']['total_price'] ?? ($base + $add));
                $it['prices']['total_price'] = $unit;
                $it['prices']['grand_total'] = $unit * $q;
            }
            break;
        }
        unset($it);

        $this->simpanKeranjang($r, $keranjang);
        return back();
    }

    /* ===================== Hapus/bersihkan ===================== */

    public function remove(Request $r, $id)
    {
        $keranjang = $this->ambilKeranjang($r);
        $keranjang = array_values(array_filter(
            $keranjang,
            fn($it) => (string)($it['id'] ?? '') !== (string)$id
        ));
        $this->simpanKeranjang($r, $keranjang);
        return back()->with('success', 'Item dihapus.');
    }

    public function hapus(Request $r)
    {
        $id = $r->validate(['id' => 'required'])['id'];
        return $this->remove($r, $id);
    }

    public function clear(Request $r)
    {
        $r->session()->forget('keranjang');
        return back()->with('success', 'Keranjang dikosongkan.');
    }

    public function bersihkan(Request $r)
    {
        return $this->clear($r);
    }

    /* ===================== Kompat lama: add(productId) ===================== */

    public function add(Request $r, $productId)
    {
        $qty = max(1, (int)$r->input('qty', 1));
        $keranjang = $this->ambilKeranjang($r);

        // Coba ambil dari DB agar nama/harga valid
        $nama = "Produk $productId";
        $harga = 0;
        $gambar = null;
        if ($p = Product::find($productId)) {
            $nama = $p->name ?? $p->nama ?? $nama;
            $harga = $this->toInt($p->price ?? $p->harga ?? 0);
            $gambar = $p->image_url ?? $p->image ?? $p->gambar ?? null;
        }

        $keranjang[] = [
            'type'       => 'prod',
            'id'         => 'prod:' . Str::upper(Str::random(10)),
            'product_id' => (string)$productId,
            'nama'       => $nama,
            'harga'      => $harga,
            'qty'        => $qty,
            'gambar'     => $gambar,
            'image'      => $gambar,
            'color'      => null,
            'size'       => null,
        ];

        $this->simpanKeranjang($r, $keranjang);
        return redirect()->route('keranjang.index');
    }

    /* ===================== Checkout (tampilan pembayaran kiri) ===================== */

    // menerima selected[] dari keranjang → siapkan view pembayaran
    public function checkoutSelected(Request $r)
    {
        $selected = (array) $r->input('selected', []);
        if (empty($selected)) {
            return back()->with('error', 'Silakan pilih minimal satu item.');
        }

        $keranjang = $this->ambilKeranjang($r);

        $items = [];
        $subtotal = 0;

        foreach ($keranjang as $row) {
            $rid = (string)($row['id'] ?? $row['row_id'] ?? '');
            if (!in_array($rid, $selected, true)) continue;

            if (($row['type'] ?? '') === 'oc') {
                $qty   = (int)($row['jumlah'] ?? 1);
                $unit  = $this->toInt($row['prices']['total_price'] ?? 0);
                $total = $this->toInt($row['prices']['grand_total'] ?? ($unit * $qty));
                $items[] = [
                    'id'      => $rid,
                    'title'   => 'Order Custom',
                    'subtitle' => trim(($row['jenis_pakaian'] ?? '-') . ' — ' . ($row['jenis_kain'] ?? '-')),
                    'image'   => asset('images/placeholder.png'),
                    'qty'     => $qty,
                    'unit'    => $unit,
                    'total'   => $total,
                ];
                $subtotal += $total;
            } else {
                $qty   = (int)($row['qty'] ?? 1);
                $unit  = $this->toInt($row['harga'] ?? $row['price'] ?? 0);
                $total = $unit * $qty;
                $items[] = [
                    'id'      => $rid,
                    'title'   => $row['nama'] ?? 'Produk',
                    'subtitle' => trim(($row['color'] ?? '') . ' ' . ($row['size'] ?? '')),
                    'image'   => $row['image'] ?? $row['gambar'] ?? asset('images/placeholder.png'),
                    'qty'     => $qty,
                    'unit'    => $unit,
                    'total'   => $total,
                ];
                $subtotal += $total;
            }
        }

        if (empty($items)) {
            return back()->with('error', 'Item yang dipilih tidak ditemukan.');
        }

        $r->session()->put('checkout_selected', [
            'items'    => $items,
            'subtotal' => $subtotal,
        ]);

        $default_shipping = ['method' => 'toko', 'ongkir' => 0];
        return view('user.checkout', compact('items', 'subtotal', 'default_shipping'));
    }

    public function lanjutPembayaran(Request $r)
    {
        $payload = $r->validate([
            'shipping_method' => 'required|in:toko,kurir',
            'ongkir'          => 'required|integer|min:0',
            'subtotal'        => 'required|integer|min:0',
            'total'           => 'required|integer|min:0',
        ]);

        $selected = $r->session()->get('checkout_selected');
        if (!$selected || empty($selected['items'])) {
            return redirect()->route('keranjang.index')->with('error', 'Data checkout tidak ditemukan.');
        }

        return view('user.checkout_summary_demo', [
            'items'    => $selected['items'],
            'subtotal' => (int) $payload['subtotal'],
            'ongkir'   => (int) $payload['ongkir'],
            'total'    => (int) $payload['total'],
            'shipping' => $payload['shipping_method'],
        ]);
    }

    // kompat lama: redirect ke alur baru
    public function checkout(Request $r)
    {
        $selectedItems = $r->input('selected_items');
        if (empty($selectedItems)) {
            return back()->with('error', 'Silakan pilih produk terlebih dahulu.');
        }
        return $this->checkoutSelected($r);
    }

    /* ===================== BAYAR LANGSUNG: POPUP (snap.pay) ===================== */

    public function payNow(Request $r)
    {
        $ids = $r->input('selected', $r->input('selected_items', []));
        if (!is_array($ids) || count($ids) === 0) {
            return response()->json(['message' => 'Tidak ada item dipilih.'], 422);
        }

        $keranjang = $this->ambilKeranjang($r);

        $dipilih = collect($keranjang)
            ->filter(fn($it) => in_array((string)($it['id'] ?? ''), array_map('strval', $ids)))
            ->values();

        if ($dipilih->isEmpty()) {
            return response()->json(['message' => 'Item tidak ditemukan di keranjang.'], 422);
        }

        $items = [];
        $subtotal = 0;

        foreach ($dipilih as $row) {
            $isProd = (($row['type'] ?? 'prod') === 'prod');
            $qty    = (int)($isProd ? ($row['qty'] ?? 1) : 1);
            $unit   = $this->toInt($isProd ? ($row['harga'] ?? 0) : 0);
            $title  = $isProd ? ($row['nama'] ?? 'Produk Tidak Ditemukan') : '';

            $items[] = [
                'id'       => (string)($row['id'] ?? ''),
                'price'    => $unit,
                'quantity' => $qty,
                'name'     => $title,
            ];
            $subtotal += $unit * $qty;
        }

        $shipping = 0;
        $total    = $subtotal + $shipping;

        MidtransConfig::$serverKey    = config('midtrans.server_key');
        MidtransConfig::$isProduction = (bool) config('midtrans.is_production', false);
        MidtransConfig::$isSanitized  = true;
        MidtransConfig::$is3ds        = true;

        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Silakan login untuk melanjutkan pembayaran.'], 401);
        }

        $firstName = (string) ($user->name ?? '');
        $email     = (string) ($user->email ?? '');
        $phone     = (string) ($user->no_telp ?? $user->phone ?? $user->no_hp ?? '');

        if ($firstName === '' || $email === '' || $phone === '') {
            return response()->json([
                'message' => 'Profil belum lengkap. Pastikan Nama, Email, dan No. Telepon sudah diisi.'
            ], 422);
        }

        $params = [
            'transaction_details' => [
                'order_id'     => 'ORD-' . now()->format('YmdHis') . '-' . rand(100, 999),
                'gross_amount' => $total,
            ],
            'item_details'     => $items,
            'customer_details' => [
                'first_name' => $firstName,
                'email'      => $email,
                'phone'      => $phone,
            ],
        ];

        try {
            $token = MidtransSnap::getSnapToken($params);
            return response()->json(['token' => $token]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Gagal membuat Snap Token',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /* ===================== BAYAR LANGSUNG: REDIRECT (halaman Midtrans penuh) ===================== */

    /**
     * Klik "Pesan Sekarang" → langsung pindah ke halaman Midtrans (tanpa popup).
     * Form mengirim selected_ids (JSON). Jika kosong, bisa kamu ubah untuk pakai semua item keranjang.
     */
    public function pay(Request $r)
    {
        // Ambil id yang dicentang dari form (JSON)
        $selectedIdsJson = $r->input('selected_ids', '[]');
        $selectedIds = json_decode($selectedIdsJson, true);
        if (json_last_error() !== JSON_ERROR_NONE) $selectedIds = [];

        $keranjang = $this->ambilKeranjang($r);

        $list = collect($keranjang);
        if (!empty($selectedIds)) {
            $sel = array_map('strval', $selectedIds);
            $list = $list->filter(fn($it) => in_array((string)($it['id'] ?? ''), $sel));
        }
        $list = $list->values();

        if ($list->isEmpty()) {
            return back()->with('error', 'Silakan pilih minimal satu item.');
        }

        // Build item_details & total
        $itemDetails = [];
        $gross = 0;
        $orderItems = []; // Untuk disimpan ke database

        foreach ($list as $row) {
            $type = $row['type'] ?? 'prod';

            if ($type === 'prod') {
                $qty   = (int)($row['qty'] ?? 1);
                $price = $this->toInt($row['harga'] ?? 0);
                $name  = (string)($row['nama'] ?? 'Produk');
                $productId = $row['product_id'] ?? null;

                $itemDetails[] = [
                    'id'       => (string)($row['id'] ?? Str::uuid()),
                    'price'    => $price,
                    'quantity' => $qty,
                    'name'     => $name,
                ];

                // Data untuk database
                $orderItems[] = [
                    'type' => 'prod',
                    'product_id' => ($productId === '' || $productId === null) ? null : $productId,
                    'garment_type' => 'Ready Made',
                    'fabric_type' => 'Standard',
                    'size' => $row['size'] ?? 'M',
                    'price' => $price,
                    'quantity' => $qty,
                    'total_price' => $price * $qty,
                    'special_request' => null,
                ];

                $gross += $price * $qty;
            } else {
                $qty   = (int)($row['jumlah'] ?? 1);
                $price = $this->toInt($row['prices']['total_price'] ?? 0);
                $name  = trim(($row['jenis_pakaian'] ?? 'Order Custom') . ' — ' . ($row['jenis_kain'] ?? '-'));

                $itemDetails[] = [
                    'id'       => (string)($row['id'] ?? Str::uuid()),
                    'price'    => $price,
                    'quantity' => $qty,
                    'name'     => $name,
                ];

                // Data untuk database
                $orderItems[] = [
                    'type' => 'custom',
                    'product_id' => 1, // Default product ID untuk custom order
                    'garment_type' => $row['jenis_pakaian'] ?? 'Custom',
                    'fabric_type' => $row['jenis_kain'] ?? 'Custom',
                    'size' => 'Custom',
                    'price' => $price,
                    'quantity' => $qty,
                    'total_price' => $price * $qty,
                    'special_request' => $row['request'] ?? null,
                ];

                $gross += $price * $qty;
            }
        }

        if ($gross < 1) {
            return back()->with('error', 'Total transaksi tidak valid.');
        }

        // Generate unique order code
        $orderCode = 'ORDER' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        Log::info('KeranjangController.pay() - Starting checkout process', [
            'user_id' => Auth::id(),
            'user_email' => Auth::user()->email,
            'order_code' => $orderCode,
            'total_amount' => $gross,
            'items_count' => count($orderItems)
        ]);

        // Simpan data order ke session untuk digunakan di webhook
        $user = Auth::user();
        $pendingOrder = [
            'user_id' => $user->id,
            'user_email' => $user->email, // Pastikan email disertakan
            'items' => $orderItems,
            'total_amount' => $gross,
            'selected_cart_ids' => $selectedIds, // Untuk menghapus dari keranjang setelah sukses
        ];

        // Simpan ke session dengan lifetime yang lebih lama
        session(['pending_order' => $pendingOrder]);

        // Juga simpan ke cache untuk backup jika session hilang
        cache()->put('pending_order_' . $orderCode, $pendingOrder, now()->addHours(2));

        // Log the session data for debugging
        Log::info('Pending order saved to session and cache', [
            'session_id' => session()->getId(),
            'order_code' => $orderCode,
            'user_id' => $user->id,
            'user_email' => $user->email,
            'order_total' => $gross,
            'item_count' => count($orderItems)
        ]);

        // Konfigurasi Midtrans
        MidtransConfig::$serverKey    = config('midtrans.server_key');
        MidtransConfig::$isProduction = (bool) config('midtrans.is_production', false);
        MidtransConfig::$isSanitized  = true;
        MidtransConfig::$is3ds        = true;

        $user = Auth::user();
        $firstName = (string) ($user->name ?? 'Guest');
        $email     = (string) ($user->email ?? 'guest@example.com');
        $phone     = (string) ($user->no_telp ?? $user->phone ?? $user->no_hp ?? '');

        $params = [
            'transaction_details' => [
                'order_id'     => $orderCode,   // Gunakan order code yang konsisten
                'gross_amount' => (int) $gross,
            ],
            'item_details'     => $itemDetails,
            'customer_details' => [
                'first_name' => $firstName,
                'email'      => $email,
                'phone'      => $phone,
            ],
            'callbacks' => [
                'finish'   => route('payment.finish'),
                'unfinish' => route('payment.unfinish'),
                'error'    => route('payment.error'),
            ],
        ];

        // Buat transaksi dan redirect ke halaman Midtrans
        $trx = MidtransSnap::createTransaction($params);
        return redirect()->away($trx->redirect_url);
    }
}

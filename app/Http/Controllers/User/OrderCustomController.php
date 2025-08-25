<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Midtrans\Snap;

class OrderCustomController extends Controller
{
    // ======= Master Harga (base / item) =======
    private array $PAJAKAIN = [
        ['value' => 'jas',          'label' => 'Jas',                  'price' => 200_000],
        ['value' => 'blezer',       'label' => 'Blezer',               'price' => 180_000],
        ['value' => 'dress',        'label' => 'Dress',                'price' => 120_000],
        ['value' => 'baju_dinas',   'label' => 'Baju Dinas',           'price' => 150_000],
        ['value' => 'baju_seragam', 'label' => 'Baju Seragam',         'price' => 100_000],
        ['value' => 'kebaya',       'label' => 'Kebaya',               'price' => 210_000],
        ['value' => 'kemeja',       'label' => 'Kemeja Cowok',         'price' => 80_000],
        ['value' => 'kemeja_cewek', 'label' => 'Kemeja Cewek',         'price' => 95_000],
        ['value' => 'baju_melayu',  'label' => 'Baju Melayu',          'price' => 170_000],
        ['value' => 'baju_couple',  'label' => 'Baju Couple Keluarga', 'price' => 270_000],
        ['value' => 'rok',          'label' => 'Rok',                  'price' => 50_000],
        ['value' => 'celana_bahan', 'label' => 'Celana Bahan',         'price' => 100_000],
        ['value' => 'celana_levis', 'label' => 'Celana Levis',         'price' => 150_000],
    ];

    // ======= Harga Tambahan Kain (sebelum multiplier & meter) =======
    private array $KAIN = [
        ['value' => 'american drill',     'label' => 'American Drill',      'add' => 38_000],
        ['value' => 'nagata drill',       'label' => 'Nagata Drill',        'add' => 48_500],
        ['value' => 'america drill 1919', 'label' => 'America Drill 1919',  'add' => 45_000],
        ['value' => 'twist drill',        'label' => 'Twist Drill',         'add' => 36_000],
        ['value' => 'katun',              'label' => 'Katun',               'add' => 45_000],
        ['value' => 'linen',              'label' => 'Linen',               'add' => 90_000],
        ['value' => 'rayon',              'label' => 'Rayon',               'add' => 40_000],
        ['value' => 'polyester',          'label' => 'Polyester',           'add' => 30_000],
        ['value' => 'wolfis',             'label' => 'Wolfis',              'add' => 28_000],
        ['value' => 'batik',              'label' => 'Batik',               'add' => 55_000],
        ['value' => 'kopri',              'label' => 'Kopri',               'add' => 60_000],
        ['value' => 'jeans',              'label' => 'Jeans',               'add' => 80_000],
        ['value' => 'sutra',              'label' => 'Sutra',               'add' => 150_000],
        ['value' => 'brokat',             'label' => 'Brokat',              'add' => 100_000],
        ['value' => 'satin',              'label' => 'Satin',               'add' => 38_000],
        ['value' => 'velvet',             'label' => 'Velvet',              'add' => 70_000],
    ];

    // ======= Kain yang diizinkan per jenis pakaian =======
    private array $KAIN_BY_CLOTHES = [
        'jas'          => ['american drill','twist drill','velvet'],
        'blezer'       => ['american drill','twist drill','velvet'],
        'dress'        => ['katun','wolfis','sutra','linen','brokat','batik'],
        'baju_dinas'   => ['kopri','katun','nagata drill','america drill 1919'],
        'baju_seragam' => ['katun','polyester','batik','rayon'],
        'kebaya'       => ['sutra','brokat','satin'],
        'kemeja'       => ['katun','linen','batik'],
        'kemeja_cewek' => ['katun','linen','batik'],
        'baju_melayu'  => ['katun','batik','linen','satin'],
        'baju_couple'  => ['katun','batik','linen'],
        'rok'          => ['linen','polyester','jeans','katun','satin'],
        'celana_bahan' => ['linen','polyester','jeans','katun','satin'],
        'celana_levis' => ['jeans'],
    ];

    // ======= Ukuran preset (opsional via Ajax) =======
    private array $SIZES_BY_CLOTHES = [
        'jas' => ['S','M','L','XL','XXL'], 'blezer'=> ['S','M','L','XL'],
        'dress'=> ['S','M','L','XL'], 'baju_dinas'=> ['S','M','L','XL'],
        'baju_seragam'=> ['S','M','L','XL','XXL'], 'kebaya'=> ['S','M','L','XL'],
        'kemeja'=> ['S','M','L','XL','XXL','Custom'], 'kemeja_cewek'=> ['S','M','L','XL','Custom'],
        'baju_melayu'=> ['S','M','L','XL'], 'baju_couple'=> ['Anak','S','M','L','XL','XXL'],
        'rok'=> ['26','27','28','29','30','31','32','Custom'],
        'celana_bahan'=> ['27','28','29','30','31','32','33','34','Custom'],
        'celana_levis'=> ['27','28','29','30','31','32','33','34','Custom'],
    ];

    // ======= Pengali & meter default (dipakai hanya saat fallback server-side) =======
    private array $X3 = ['dress','baju_couple'];
    private array $X2 = ['blezer','kebaya','baju_melayu','celana_levis','celana_bahan'];
    private float $MULT_DEFAULT = 1.5;

    private float $METER_DEFAULT = 2.0;
    private array $METER_PER_CLOTHES = [
        'dress'        => 3,
        'baju_couple'  => 3,
        'rok'          => 2,
        'celana_bahan' => 2,
        'celana_levis' => 2,
    ];

    // ================== PAGES ==================
    public function index()
    {
        return view('user.pesanan.order_custom', [
            'PAJAKAIN' => $this->PAJAKAIN,
            'KAIN'     => $this->KAIN,
        ]);
    }

    // ======= Simpan dari form -> redirect ke konfirmasi (pakai angka dari form) =======
    public function simpan(Request $request)
    {
        $data = $request->validate([
            'jenis_pakaian'           => 'required|string',
            'jenis_kain'              => 'required|string',
            'jenis_ukuran'            => 'required|string',
            'jumlah'                  => 'nullable|integer|min:1',
            'pickup'                  => 'nullable|string',
            'request'                 => 'nullable|string',
            'kain_aksesoris_customer' => 'nullable|in:0,1',
            'gambar_custom'           => 'nullable|image|max:2048',

            // >>> angka yang sudah tampil di kartu "Perkiraan Harga"
            'base_price'   => 'nullable|numeric',
            'fabric_add'   => 'nullable|numeric',
            'unit_total'   => 'nullable|numeric',
            'grand_total'  => 'nullable|numeric',
        ]);

        // defaults aman
        $data['jumlah']  = max(1, (int)($data['jumlah'] ?? 1));
        $data['pickup']  = $data['pickup'] ?? 'jemput_toko';
        $data['request'] = $data['request'] ?? '-';
        $data['kain_aksesoris_customer'] = (int)($data['kain_aksesoris_customer'] ?? 0);

        if ($request->hasFile('gambar_custom')) {
            $data['gambar_path'] = $request->file('gambar_custom')->store('pesanan', 'public');
        }

        // kirim ke konfirmasi via query (angka2 tidak dihitung ulang)
        return redirect()->route('oc.konfirmasi', $data);
    }

    // ======= Halaman konfirmasi: pakai angka dari form; fallback hanya jika kosong =======
    public function konfirmasi(Request $request)
    {
        $qty        = max(1, (int) $request->query('jumlah', 1));
        $isCustomer = (int) $request->query('kain_aksesoris_customer', 0) === 1;

        // 1) Ambil angka dari form (biar sama persis dengan halaman sebelumnya)
        $base      = (int) $request->query('base_price', 0);
        $fabricAdd = (int) $request->query('fabric_add', 0);
        $unitTotal = (int) $request->query('unit_total', 0);
        $grand     = (int) $request->query('grand_total', 0);

        if ($base > 0 && $unitTotal > 0) {
            if ($isCustomer) {
                // safety: kalau centang "kain customer", pastikan add = 0
                $fabricAdd = 0;
                $unitTotal = $base;
                $grand     = $unitTotal * $qty;
            }

            // Snap Token (opsional)
            $snapToken = $this->makeSnapTokenSafe($grand);

            return view('user.pesanan.konfirmasi', [
                'data'      => $request->all(),
                'snapToken' => $snapToken,
                'base'      => $base,
                'fabricAdd' => $fabricAdd,
                'unitTotal' => $unitTotal,
                'grand'     => $grand,
                'qty'       => $qty,
                // kompat lama:
                'sum' => [
                    'base_price'  => $base,
                    'fabric_add'  => $fabricAdd,
                    'total_price' => $unitTotal,
                    'qty'         => $qty,
                    'grand_total' => $grand,
                ],
            ]);
        }

        // 2) Fallback: user akses URL langsung -> hitung server-side (agar tetap jalan)
        $jenisPakaian = strtolower((string) $request->query('jenis_pakaian'));
        $jenisKain    = strtolower((string) $request->query('jenis_kain'));

        $base    = $this->lookupBasePrice($jenisPakaian);
        $addBase = $this->lookupFabricAddBase($jenisKain);

        $mult   = $this->getMultiplierFor($jenisPakaian);
        $meters = $this->getMetersFor($jenisPakaian);

        $fabricAdd = (int) round($addBase * $mult * $meters);
        if ($isCustomer) $fabricAdd = 0;

        $unitTotal = $base + $fabricAdd;
        $grand     = $unitTotal * $qty;

        $snapToken = $this->makeSnapTokenSafe($grand);

        return view('user.pesanan.konfirmasi', [
            'data'      => $request->all(),
            'snapToken' => $snapToken,
            'base'      => $base,
            'fabricAdd' => $fabricAdd,
            'unitTotal' => $unitTotal,
            'grand'     => $grand,
            'qty'       => $qty,
            'sum' => [
                'base_price'  => $base,
                'fabric_add'  => $fabricAdd,
                'total_price' => $unitTotal,
                'qty'         => $qty,
                'grand_total' => $grand,
            ],
        ]);
    }

    // ================= Helpers =================

    private function lookupBasePrice(?string $value): int
    {
        $needle = strtolower(trim((string)$value));
        foreach ($this->PAJAKAIN as $row) {
            if ($row['value'] === $needle) {
                return (int) $row['price'];
            }
        }
        return 0;
    }

    /** ambil add dasar kain (belum dikali multiplier & meter) */
    private function lookupFabricAddBase(?string $fabricValue): int
    {
        $needle = strtolower(trim((string)$fabricValue));
        foreach ($this->KAIN as $row) {
            if (strtolower(trim($row['value'])) === $needle) {
                return (int) $row['add'];
            }
        }
        return 0;
    }

    private function getMultiplierFor(?string $clothes): float
    {
        $c = strtolower(trim((string)$clothes));
        if (in_array($c, $this->X3, true)) return 3.0;
        if (in_array($c, $this->X2, true)) return 2.0;
        return $this->MULT_DEFAULT;
    }

    private function getMetersFor(?string $clothes): float
    {
        $c = strtolower(trim((string)$clothes));
        return (float)($this->METER_PER_CLOTHES[$c] ?? $this->METER_DEFAULT);
    }

    /** Buat Snap Token tapi aman kalau config belum ada (return null) */
    private function makeSnapTokenSafe(int $grossAmount): ?string
    {
        try {
            \Midtrans\Config::$serverKey    = (string) config('midtrans.server_key');
            \Midtrans\Config::$isProduction = (bool)   config('midtrans.is_production', false);
            \Midtrans\Config::$isSanitized  = true;
            \Midtrans\Config::$is3ds        = true;

            if (!config('midtrans.server_key')) {
                return null;
            }

            $orderId = 'ORD-' . now()->format('YmdHis') . '-' . mt_rand(1000, 9999);
            $user    = Auth::user();

            $params = [
                'transaction_details' => [
                    'order_id'     => $orderId,
                    'gross_amount' => max(0, (int) $grossAmount),
                ],
                'customer_details' => [
                    'first_name' => $user->name  ?? 'Customer',
                    'email'      => $user->email ?? 'customer@example.com',
                    'phone'      => '08123456789',
                ],
            ];

            return Snap::getSnapToken($params);
        } catch (\Throwable $e) {
            // Log::warning('Gagal membuat Snap Token: '.$e->getMessage());
            return null;
        }
    }

    // ================= Ajax helper =================

    public function kainByPakaian(string $pakaian)
    {
        $key = strtolower($pakaian);
        $allowed = $this->KAIN_BY_CLOTHES[$key] ?? null;
        if (!$allowed) {
            // kalau tidak ada mapping, kirim semua kain
            return response()->json(
                collect($this->KAIN)->map(fn($r)=>['id'=>$r['value'],'name'=>$r['label']])->values()
            );
        }

        $map = collect($this->KAIN)->keyBy(fn($r)=>strtolower($r['value']));
        $options = collect($allowed)
            ->map(fn($val) => $map->get(strtolower($val)))
            ->filter()
            ->values()
            ->map(fn($r)=>['id'=>$r['value'],'name'=>$r['label']])
            ->all();

        return response()->json($options);
    }

    public function ukuranByPakaian(string $pakaian)
    {
        $list = $this->SIZES_BY_CLOTHES[strtolower($pakaian)] ?? ['S','M','L','XL','XXL'];
        return response()->json(
            collect($list)->map(fn($x) => ['id' => (string)$x, 'name' => (string)$x])->values()
        );
    }

    public function imageByPakaian(string $pakaian)
    {
        $file = 'images/pakaian/' . strtolower($pakaian) . '.png';
        $full = public_path($file);
        return response()->json([
            'image_url' => file_exists($full) ? asset($file) : asset('images/placeholder.png')
        ]);
    }

    public function showCart()
    {
        // Ambil cart dari session atau dari database
        $cart = session()->get('cart', []); // atau bisa dari database

        // Cek jika cart kosong
        if (empty($cart)) {
            return view('keranjang', ['cart' => []]); // Jika kosong, kirim array kosong
        }

        // Kirim cart ke view
        return view('keranjang', ['cart' => $cart]);
    }
}

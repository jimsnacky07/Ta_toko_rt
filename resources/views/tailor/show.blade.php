@extends('tailor.layouts.app')

@section('title', 'Detail Pesanan')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('tailor.data.pesanan') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Kembali ke Data Pesanan
        </a>
    </div>

    <h1 class="text-2xl font-semibold text-gray-800 mb-6 text-center">
        Detail Pesanan - {{ $order->order_code ?? $order->kode_pesanan ?? '#' . $order->id }}
    </h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Informasi Pesanan -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Header Pesanan -->
            <div class="bg-white rounded-xl shadow p-6">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">Informasi Pesanan</h2>
                        <p class="text-sm text-gray-500">ID: {{ $order->order_code ?? $order->kode_pesanan ?? '#' . $order->id }}</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-sm font-medium
                        @if($order->status === 'menunggu') bg-yellow-100 text-yellow-800
                        @elseif($order->status === 'diproses') bg-blue-100 text-blue-800
                        @elseif($order->status === 'selesai') bg-green-100 text-green-800
                        @elseif($order->status === 'siap-diambil') bg-purple-100 text-purple-800
                        @elseif($order->status === 'dibatalkan') bg-red-100 text-red-800
                        @else bg-gray-100 text-gray-800
                        @endif">
                        {{ ucfirst(str_replace('-', ' ', $order->status)) }}
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Tanggal Pesanan</label>
                        <p class="text-gray-800">{{ $order->created_at->format('d F Y, H:i') }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Metode Pembayaran</label>
                        <p class="text-gray-800">{{ $order->metode_pembayaran ?? 'Belum dibayar' }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Total Harga</label>
                        <p class="text-lg font-semibold text-gray-800">Rp {{ number_format($order->total_harga ?? $order->total_amount ?? 0, 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Tanggal Dibayar</label>
                        <p class="text-gray-800">{{ $order->paid_at ? $order->paid_at->format('d F Y, H:i') : 'Belum dibayar' }}</p>
                    </div>
                </div>
            </div>

            <!-- Detail Item Pesanan -->
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Detail Item Pesanan</h3>
                <div class="space-y-4">
                    @forelse($order->orderItems as $item)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-medium text-gray-500">Jenis Pakaian</label>
                                <p class="text-gray-800 font-medium">{{ $item->garment_type }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500">Jenis Kain</label>
                                <p class="text-gray-800">{{ $item->fabric_type }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500">Ukuran</label>
                                <p class="text-gray-800">{{ $item->size }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500">Jumlah</label>
                                <p class="text-gray-800">{{ $item->quantity }} pcs</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500">Harga Satuan</label>
                                <p class="text-gray-800">Rp {{ number_format($item->price, 0, ',', '.') }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500">Total Harga</label>
                                <p class="text-gray-800 font-semibold">Rp {{ number_format($item->total_price, 0, ',', '.') }}</p>
                            </div>
                        </div>

                        @if($item->special_request)
                        <div class="mt-3">
                            <label class="text-sm font-medium text-gray-500">Permintaan Khusus</label>
                            <p class="text-gray-800 bg-gray-50 p-3 rounded-lg">{{ $item->special_request }}</p>
                        </div>
                        @endif

                        <div class="mt-3">
                            <label class="text-sm font-medium text-gray-500">Status Item</label>
                            <div class="flex items-center gap-2">
                                <span class="inline-block px-2 py-1 rounded-full text-xs font-medium
                                        @if($item->status === 'menunggu') bg-yellow-100 text-yellow-800
                                        @elseif($item->status === 'diproses') bg-blue-100 text-blue-800
                                        @elseif($item->status === 'siap-diambil') bg-purple-100 text-purple-800
                                        @elseif($item->status === 'selesai') bg-green-100 text-green-800
                                        @elseif($item->status === 'dibatalkan') bg-red-100 text-red-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                    {{ ucfirst($item->status) }}
                                </span>
                                @if($item->status === $order->status ||
                                ($item->status === 'siap-diambil' && $order->status === 'siap-diambil'))
                                <span class="text-xs text-green-600">✓ Sinkron</span>
                                @else
                                <span class="text-xs text-orange-600">⚠ Perlu Sync</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-gray-500 text-center py-4">Tidak ada item pesanan</p>
                    @endforelse
                </div>
            </div>

            <!-- Catatan -->
            @if($order->catatan)
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Catatan</h3>
                <p class="text-gray-800 bg-gray-50 p-4 rounded-lg">{{ $order->catatan }}</p>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Informasi Pelanggan -->
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Pelanggan</h3>
                <div class="space-y-3">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Nama</label>
                        <p class="text-gray-800">{{ $order->user->nama ?? $order->user->name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Email</label>
                        <p class="text-gray-800">{{ $order->user->email ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">No. Telepon</label>
                        <p class="text-gray-800">{{ $order->user->no_telp ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Alamat</label>
                        <p class="text-gray-800">{{ $order->user->alamat ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <!-- Informasi Pengiriman -->
            @if($order->nama_pengiriman || $order->alamat_pengiriman)
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Pengiriman</h3>
                <div class="space-y-3">
                    @if($order->nama_pengiriman)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Nama Penerima</label>
                        <p class="text-gray-800">{{ $order->nama_pengiriman }}</p>
                    </div>
                    @endif
                    @if($order->no_telp_pengiriman)
                    <div>
                        <label class="text-sm font-medium text-gray-500">No. Telepon</label>
                        <p class="text-gray-800">{{ $order->no_telp_pengiriman }}</p>
                    </div>
                    @endif
                    @if($order->alamat_pengiriman)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Alamat</label>
                        <p class="text-gray-800">{{ $order->alamat_pengiriman }}</p>
                    </div>
                    @endif
                    @if($order->kota_pengiriman)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Kota</label>
                        <p class="text-gray-800">{{ $order->kota_pengiriman }}</p>
                    </div>
                    @endif
                    @if($order->kecamatan_pengiriman)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Kecamatan</label>
                        <p class="text-gray-800">{{ $order->kecamatan_pengiriman }}</p>
                    </div>
                    @endif
                    @if($order->kode_pos_pengiriman)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Kode Pos</label>
                        <p class="text-gray-800">{{ $order->kode_pos_pengiriman }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Data Ukuran Badan (Order Custom) / Panduan Ukuran (Order Product) -->
            @php
            $firstItem = $order->orderItems->first();
            // Deteksi order product vs custom berdasarkan order_code
            $isProductOrder = $order->order_code && str_starts_with($order->order_code, 'OP-');
            $isCustomOrder = $order->order_code && str_starts_with($order->order_code, 'OC-');
            @endphp

            @if($isProductOrder)
            @php
            // Untuk Order Product - Tampilkan Panduan Ukuran
            $selectedSize = $firstItem->size; // Ukuran yang dipilih customer (S, M, L, XL)
            $selectedSizeData = [];
            $garmentType = $firstItem->garment_type ?? '';

            // Panduan ukuran statis berdasarkan jenis pakaian
            $sizeGuides = [
            'kemeja' => [
            'S' => ['Lingkar Dada: 88-92 cm', 'Lingkar Pinggang: 68-72 cm', 'Panjang: 65-68 cm'],
            'M' => ['Lingkar Dada: 92-96 cm', 'Lingkar Pinggang: 72-76 cm', 'Panjang: 68-71 cm'],
            'L' => ['Lingkar Dada: 96-100 cm', 'Lingkar Pinggang: 76-80 cm', 'Panjang: 71-74 cm'],
            'XL' => ['Lingkar Dada: 100-104 cm', 'Lingkar Pinggang: 80-84 cm', 'Panjang: 74-77 cm']
            ],
            'baju' => [
            'S' => ['Lingkar Dada: 88-92 cm', 'Lingkar Pinggang: 68-72 cm', 'Panjang: 65-68 cm'],
            'M' => ['Lingkar Dada: 92-96 cm', 'Lingkar Pinggang: 72-76 cm', 'Panjang: 68-71 cm'],
            'L' => ['Lingkar Dada: 96-100 cm', 'Lingkar Pinggang: 76-80 cm', 'Panjang: 71-74 cm'],
            'XL' => ['Lingkar Dada: 100-104 cm', 'Lingkar Pinggang: 80-84 cm', 'Panjang: 74-77 cm']
            ],
            'rok' => [
            'S' => ['Lingkar Pinggang: 62-66 cm', 'Lingkar Pinggul: 88-92 cm', 'Panjang: 60-65 cm'],
            'M' => ['Lingkar Pinggang: 66-70 cm', 'Lingkar Pinggul: 92-96 cm', 'Panjang: 65-70 cm'],
            'L' => ['Lingkar Pinggang: 70-74 cm', 'Lingkar Pinggul: 96-100 cm', 'Panjang: 70-75 cm'],
            'XL' => ['Lingkar Pinggang: 74-78 cm', 'Lingkar Pinggul: 100-104 cm', 'Panjang: 75-80 cm']
            ],
            'celana' => [
            'S' => ['Lingkar Pinggang: 76-80 cm', 'Lingkar Pinggul: 88-92 cm', 'Panjang: 100-105 cm'],
            'M' => ['Lingkar Pinggang: 80-84 cm', 'Lingkar Pinggul: 92-96 cm', 'Panjang: 105-110 cm'],
            'L' => ['Lingkar Pinggang: 84-88 cm', 'Lingkar Pinggul: 96-100 cm', 'Panjang: 110-115 cm'],
            'XL' => ['Lingkar Pinggang: 88-92 cm', 'Lingkar Pinggul: 100-104 cm', 'Panjang: 115-120 cm']
            ],
            'dress' => [
            'S' => ['Lingkar Dada: 86-90 cm', 'Lingkar Pinggang: 68-72 cm', 'Panjang: 120-125 cm'],
            'M' => ['Lingkar Dada: 90-94 cm', 'Lingkar Pinggang: 72-76 cm', 'Panjang: 125-130 cm'],
            'L' => ['Lingkar Dada: 94-98 cm', 'Lingkar Pinggang: 76-80 cm', 'Panjang: 130-135 cm'],
            'XL' => ['Lingkar Dada: 98-102 cm', 'Lingkar Pinggang: 80-84 cm', 'Panjang: 135-140 cm']
            ]
            ];

            // Tentukan jenis pakaian yang sesuai
            $garmentTypeLower = strtolower($garmentType);
            $garmentKey = 'kemeja'; // default
            if (str_contains($garmentTypeLower, 'rok')) { $garmentKey = 'rok'; }
            elseif (str_contains($garmentTypeLower, 'celana')) { $garmentKey = 'celana'; }
            elseif (str_contains($garmentTypeLower, 'dress')) { $garmentKey = 'dress'; }
            elseif (str_contains($garmentTypeLower, 'baju') || str_contains($garmentTypeLower, 'kemeja')) { $garmentKey = 'kemeja'; }

            // Ambil data ukuran yang dipilih
            if (isset($sizeGuides[$garmentKey][$selectedSize])) {
            $selectedSizeData = $sizeGuides[$garmentKey][$selectedSize];
            }
            @endphp
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Panduan Ukuran Produk (Ukuran {{ $selectedSize }})</h3>
                <div class="space-y-3 text-sm">
                    @if(!empty($selectedSizeData))
                    @foreach($selectedSizeData as $measurement)
                    <div>
                        <label class="text-gray-500">{{ $measurement }}</label>
                    </div>
                    @endforeach
                    @else
                    <div>
                        <p class="text-gray-500 text-center">Panduan ukuran tidak tersedia untuk jenis pakaian ini</p>
                    </div>
                    @endif
                </div>
            </div>
            @elseif($isCustomOrder && $order->user->dataUkuranBadan)
            @php
            // Untuk Order Custom - Tampilkan Data Ukuran Badan (tetap seperti sebelumnya)
            $garment = strtolower($firstItem->garment_type ?? '');
            // Tentukan kelompok ukuran sesuai jenis pakaian custom
            $map = [
            'rok' => ['lingkaran_pinggang','lingkaran_pinggul','panjang_rok'],
            'celana' => ['lingkaran_pinggang','lingkaran_pinggul','panjang_celana','lingkaran_paha','lingkaran_lutut'],
            // baju/kemeja/jas/dress/kebaya/blouse
            'baju' => ['lingkaran_dada','lingkaran_pinggang','lingkaran_lengan','lingkaran_leher','lebar_bahu','panjang_baju','panjang_lengan'],
            ];
            $keys = $map['baju'];
            if (str_contains($garment,'rok')) { $keys = $map['rok']; }
            elseif (str_contains($garment,'celana')) { $keys = $map['celana']; }
            elseif (str_contains($garment,'kemeja') || str_contains($garment,'jas') || str_contains($garment,'dress') || str_contains($garment,'kebaya') || str_contains($garment,'blouse') || str_contains($garment,'baju')) { $keys = $map['baju']; }
            $labels = [
            'lingkaran_dada' => 'Lingkar Dada',
            'lingkaran_pinggang' => 'Lingkar Pinggang',
            'lingkaran_pinggul' => 'Lingkar Pinggul',
            'lingkaran_leher' => 'Lingkar Leher',
            'lingkaran_lengan' => 'Lingkar Lengan',
            'lingkaran_paha' => 'Lingkar Paha',
            'lingkaran_lutut' => 'Lingkar Lutut',
            'panjang_baju' => 'Panjang Baju',
            'panjang_lengan' => 'Panjang Lengan',
            'panjang_celana' => 'Panjang Celana',
            'panjang_rok' => 'Panjang Rok',
            'lebar_bahu' => 'Lebar Bahu',
            ];
            $uk = $order->user->dataUkuranBadan;
            @endphp
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Data Ukuran Badan (sesuai pesanan)</h3>
                <div class="space-y-3 text-sm">
                    @foreach($keys as $k)
                    @if(!is_null($uk->$k))
                    <div>
                        <label class="text-gray-500">{{ $labels[$k] }}</label>
                        <p class="font-medium">{{ $uk->$k }} cm</p>
                    </div>
                    @endif
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Update Status -->
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Update Status</h3>
                <form action="{{ route('tailor.update.status', $order->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="space-y-3">
                        <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="menunggu" {{ $order->status == 'menunggu' ? 'selected' : '' }}>Menunggu Giliran</option>
                            <option value="diproses" {{ $order->status == 'diproses' ? 'selected' : '' }}>Sedang Dikerjakan</option>
                            <option value="selesai" {{ $order->status == 'selesai' ? 'selected' : '' }}>Selesai</option>
                            <option value="siap-diambil" {{ $order->status == 'siap-diambil' ? 'selected' : '' }}>Siap Diambil</option>
                        </select>
                        <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors">
                            Update Status
                        </button>
                    </div>
                </form>

                <!-- Info Sinkronisasi -->
                <div class="mt-4 p-3 bg-blue-50 rounded-lg">
                    <div class="flex items-center gap-2 text-sm text-blue-800">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="font-medium">Info:</span>
                    </div>
                    <p class="text-xs text-blue-700 mt-1">
                        Status item akan otomatis disinkronkan dengan status pesanan saat diupdate.
                    </p>
                </div>

                <!-- Tombol Sinkronisasi Manual -->
                <div class="mt-3">
                    <form action="{{ route('tailor.sync.order.items', $order->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="w-full bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 transition-colors text-sm">
                            🔄 Sinkronkan Status Item
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
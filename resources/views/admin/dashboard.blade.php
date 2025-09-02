@extends('admin.layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
    <div class="max-w-7xl mx-auto">
        <h1 class="text-2xl font-semibold text-gray-800 mb-6 text-center">
            Dashboard Admin - Ringkasan Bisnis
        </h1>

        <!-- Ringkasan Utama -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Jumlah Pesanan -->
            <div class="bg-white rounded-xl shadow p-5">
                <div class="text-sm text-gray-500">Pesanan Bulan Ini</div>
                <div class="mt-2 text-3xl font-bold text-blue-600">{{ $ordersThisMonth }}</div>
            </div>

            <!-- Revenue -->
            <div class="bg-white rounded-xl shadow p-5">
                <div class="text-sm text-gray-500">Pendapatan Bulan Ini</div>
                <div class="mt-2 text-2xl font-bold text-green-600">Rp {{ number_format($revenueThisMonth ?? 0, 0, ',', '.') }}</div>
            </div>

            <!-- Jumlah Pelanggan -->
            <div class="bg-white rounded-xl shadow p-5">
                <div class="text-sm text-gray-500">Total Pelanggan</div>
                <div class="mt-2 text-3xl font-bold text-purple-600">{{ $customers }}</div>
            </div>

            <!-- Pesanan Proses -->
            <div class="bg-white rounded-xl shadow p-5">
                <div class="text-sm text-gray-500">Sedang Diproses</div>
                <div class="mt-2 text-3xl font-bold text-orange-600">{{ $inProcess }}</div>
            </div>
        </div>

        <!-- Status Pesanan -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-6 mb-8">
            <!-- Pesanan Selesai -->
            <div class="bg-white rounded-xl shadow p-5">
                <div class="text-sm text-gray-500">Pesanan Selesai</div>
                <div class="mt-2 text-3xl font-bold text-green-600">{{ $completed }}</div>
            </div>

            <!-- Pesanan Menunggu -->
            <div class="bg-white rounded-xl shadow p-5">
                <div class="text-sm text-gray-500">Pesanan Menunggu</div>
                <div class="mt-2 text-3xl font-bold text-yellow-600">{{ $pending }}</div>
            </div>
        </div>

        <!-- Pesanan Terbaru -->
        <div class="bg-white rounded-xl shadow mb-8">
            <div class="p-6 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-gray-800">Pesanan Terbaru</h2>
                    <a href="{{ route('admin.daftar.pesanan') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        Lihat Semua →
                    </a>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode Pesanan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pelanggan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($recentOrders ?? [] as $order)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $order->order_code ?? $order->kode_pesanan ?? '#' . $order->id }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $order->user->nama ?? $order->user->name ?? 'N/A' }}
                                    <div class="text-xs text-gray-400">{{ $order->user->email ?? '' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    Rp {{ number_format($order->total_amount ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusColors = [
                                            'menunggu' => 'bg-yellow-100 text-yellow-800',
                                            'diproses' => 'bg-blue-100 text-blue-800',
                                            'selesai' => 'bg-green-100 text-green-800',
                                            'paid' => 'bg-green-100 text-green-800',
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'cancelled' => 'bg-red-100 text-red-800',
                                        ];
                                        $statusClass = $statusColors[$order->status] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $order->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('admin.daftar.pesanan') }}?q={{ $order->order_code ?? $order->id }}" 
                                       class="text-blue-600 hover:text-blue-900">Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                    Belum ada pesanan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <a href="{{ route('admin.daftar.pesanan') }}" class="bg-blue-600 hover:bg-blue-700 text-white p-6 rounded-xl text-center transition-colors">
                <div class="text-2xl mb-2">📋</div>
                <div class="font-semibold">Kelola Pesanan</div>
                <div class="text-sm opacity-90">Lihat dan kelola semua pesanan</div>
            </a>
            
            <a href="{{ route('admin.pelanggan') }}" class="bg-purple-600 hover:bg-purple-700 text-white p-6 rounded-xl text-center transition-colors">
                <div class="text-2xl mb-2">👥</div>
                <div class="font-semibold">Data Pelanggan</div>
                <div class="text-sm opacity-90">Kelola data pelanggan</div>
            </a>
            
            <a href="{{ route('admin.galeri.jahit.index') }}" class="bg-green-600 hover:bg-green-700 text-white p-6 rounded-xl text-center transition-colors">
                <div class="text-2xl mb-2">🎨</div>
                <div class="font-semibold">Galeri Jahit</div>
                <div class="text-sm opacity-90">Kelola produk dan galeri</div>
            </a>
        </div>
    </div>
@endsection

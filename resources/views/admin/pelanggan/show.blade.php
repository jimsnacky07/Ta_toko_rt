@extends('admin.layouts.app')

@section('title', 'Detail Pelanggan')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Detail Pelanggan</h1>
                <p class="text-gray-600 mt-1">Informasi lengkap pelanggan dan riwayat pesanan</p>
            </div>
            <a href="{{ route('admin.pelanggan') }}"
                class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                ← Kembali
            </a>
        </div>
    </div>

    <!-- Customer Info -->
    <div class="bg-white rounded-xl shadow p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Pelanggan</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                <p class="text-gray-900">{{ $customer->nama ?? $customer->name ?? 'N/A' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <p class="text-gray-900">{{ $customer->email }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">No. Telepon</label>
                <p class="text-gray-900">{{ $customer->no_telp ?? $customer->phone ?? 'N/A' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                <p class="text-gray-900">{{ $customer->alamat ?? 'N/A' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Bergabung</label>
                <p class="text-gray-900">{{ $customer->created_at->format('d F Y H:i') }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Total Pesanan</label>
                <p class="text-gray-900 font-semibold">{{ $customer->orders->count() }} pesanan</p>
            </div>
        </div>
    </div>

    <!-- Orders History -->
    <div class="bg-white rounded-xl shadow">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Riwayat Pesanan</h2>
        </div>

        @php
        $excludedStatuses = ['dibatalkan','cancelled','canceled','batal'];
        $orders = $customer->orders->filter(fn($o) => !in_array(strtolower($o->status), $excludedStatuses));
        @endphp

        @if($orders->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode
                            Pesanan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Produk</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status Order</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Penjemputan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($orders as $order)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $order->order_code ?? $order->kode_pesanan ?? '#' . $order->id }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($order->orderItems && $order->orderItems->count() > 0)
                            @foreach($order->orderItems as $item)
                            <div class="mb-1">
                                <span class="font-medium">{{ $item->garment_type }}</span>
                                @if($item->fabric_type)
                                <span class="text-gray-600">- {{ $item->fabric_type }}</span>
                                @endif
                                @if($item->size)
                                <span class="text-gray-500">({{ $item->size }})</span>
                                @endif
                            </div>
                            @endforeach
                            @else
                            <span class="text-gray-400">-</span>
                            @endif
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
                            'dibatalkan' => 'bg-red-100 text-red-800',
                            ];
                            $statusClass = $statusColors[$order->status] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            @php
                            $pickup = optional($order->orderItems->first())->status;
                            $mapPickup = [
                            'jemput_toko' => 'Ambil di Toko',
                            'pickup_jnt' => 'Pick-Up J&T',
                            'pending' => 'Belum ditetapkan',
                            ];
                            @endphp
                            <span class="inline-flex px-2 py-0.5 rounded bg-gray-100">{{ $mapPickup[$pickup] ?? ($pickup
                                ?? '-') }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $order->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('admin.daftar.pesanan') }}?q={{ $order->order_code ?? $order->id }}"
                                class="text-blue-600 hover:text-blue-900">Detail</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="p-6 text-center text-gray-500">
            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada pesanan</h3>
            <p class="text-gray-600">Pelanggan ini belum melakukan pesanan apapun.</p>
        </div>
        @endif
    </div>
</div>
@endsection
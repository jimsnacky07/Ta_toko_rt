@extends('tailor.layouts.app')

@section('title', 'Dashboard Tailor')

@section('content')
<div class="max-w-7xl mx-auto">
    <h1 class="text-2xl font-semibold text-gray-800 mb-6 text-center">
        Dashboard Tailor - {{ Auth::user()->nama ?? Auth::user()->name }}
    </h1>

    <!-- Ringkasan Pesanan -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Pesanan Saya -->
        <div class="bg-white rounded-xl shadow p-5">
            <div class="text-sm text-gray-500">Total Pesanan</div>
            <div class="mt-2 text-3xl font-bold">{{ $myOrders ?? 0 }}</div>
        </div>
        
        <div class="bg-white rounded-xl shadow p-5">
            <div class="text-sm text-gray-500">Menunggu Giliran</div>
            <div class="mt-2 text-3xl font-bold text-yellow-600">{{ $pending ?? 0 }}</div>
        </div>
        <!-- Sedang Dikerjakan -->
        <div class="bg-white rounded-xl shadow p-5">
            <div class="text-sm text-gray-500">Sedang Dikerjakan</div>
            <div class="mt-2 text-3xl font-bold text-blue-600">{{ $inProgress ?? 0 }}</div>
        </div>

        <!-- Selesai -->
        <div class="bg-white rounded-xl shadow p-5">
            <div class="text-sm text-gray-500">Siap Diambil</div>
            <div class="mt-2 text-3xl font-bold text-purple-600">{{ $readyToPick ?? 0 }}</div>
        </div>
        <div class="bg-white rounded-xl shadow p-5">
            <div class="text-sm text-gray-500">Selesai</div>
            <div class="mt-2 text-3xl font-bold text-green-600">{{ $completed ?? 0 }}</div>
        </div>

        <!-- Siap Diambil -->
    </div>

    <!-- Pesanan Terbaru -->
    <div class="bg-white rounded-xl shadow">
        <div class="p-6 border-b">
            <h2 class="text-lg font-semibold">Pesanan Terbaru</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-3 text-left">ID Pesanan</th>
                        <th class="p-3 text-left">Pelanggan</th>
                        <th class="p-3 text-left">Produk</th>
                        <th class="p-3 text-left">Status</th>
                        <th class="p-3 text-left">Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders ?? [] as $order)
                    <tr class="border-b">
                        <td class="p-3">
                            <a href="{{ route('tailor.data.pesanan.show', $order->id) }}" class="text-blue-600 hover:text-blue-800 font-mono">
                                #{{ $order->order_code ?? $order->kode_pesanan ?? $order->id }}
                            </a>
                        </td>
                        <td class="p-3">{{ $order->user->nama ?? $order->user->name ?? 'N/A' }}</td>
                        <td class="p-3">
                            @if(isset($order->orderItems) && $order->orderItems->count() > 0)
                            {{ $order->orderItems->first()->product_name }}
                            @if($order->orderItems->count() > 1)
                            <span class="text-gray-500">+{{ $order->orderItems->count() - 1 }} lainnya</span>
                            @endif
                            @else
                            Produk Jahit
                            @endif
                        </td>
                        <td class="p-3">
                            <span class="px-2 py-1 rounded-full text-xs
                                    @if($order->status === 'menunggu') bg-yellow-100 text-yellow-800
                                    @elseif($order->status === 'diproses') bg-blue-100 text-blue-800
                                    @elseif($order->status === 'siap-diambil') bg-purple-100 text-purple-800
                                    @elseif($order->status === 'selesai') bg-green-100 text-green-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                        <td class="p-3">{{ $order->created_at->format('d/m/Y') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-6 text-center text-gray-500">
                            Belum ada pesanan yang ditugaskan kepada Anda.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(isset($recentOrders) && $recentOrders->count() > 0)
            {{-- <div class="p-4 border-t">
                <a href="{{ route('tailor.orders.index') }}" class="text-blue-600 hover:underline">
                    Lihat semua pesanan →
                </a>
            </div> --}}
        @endif
    </div>
</div>
@endsection
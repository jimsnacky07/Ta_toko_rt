@extends('admin.layouts.app')

@section('title', 'Pelanggan')

@section('content')
@php
// kompatibilitas: controller mengirim 'pelanggan' dan 'customers'
$list = isset($pelanggan) ? $pelanggan : ($customers ?? collect());
@endphp

<div class="container mx-auto py-6">
    <h1 class="text-3xl font-semibold text-gray-800 mb-6">Daftar Pelanggan</h1>

    <table class="table-auto w-full border-collapse text-left">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-2 border-b">No</th>
                <th class="px-4 py-2 border-b">Email</th>
                <th class="px-4 py-2 border-b">Nama</th>
                <th class="px-4 py-2 border-b">No Telpon</th>
                <th class="px-4 py-2 border-b">Total Orders</th>
                <th class="px-4 py-2 border-b">Metode Pembayaran</th>
                <!-- <th class="px-4 py-2 border-b">Status Penjemputan</th> -->
                <th class="px-4 py-2 border-b">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($list as $index => $u)
            @php
            // Get the most recent order
            $lastOrder = $u->orders->first();
            $last = $lastOrder;

            // Metode pembayaran dari tabel orders
            $metodePembayaran = $u->last_payment_method ?? ($last->metode_pembayaran ?? '-');

            // Status penjemputan diambil dari order_items.status terbaru
            $pickupStatus = $u->last_pickup_status ?? optional(optional($last)->orderItems->first())->status;

            // Total orders count
            $totalOrders = $u->orders_count ?? 0;
            @endphp

            <tr class="hover:bg-gray-50">
                <td class="px-4 py-2 border-b">{{ $index + 1 }}</td>
                <td class="px-4 py-2 border-b">{{ $u->email }}</td>
                <td class="px-4 py-2 border-b">{{ $u->nama ?? $u->name }}</td>
                <td class="px-4 py-2 border-b">{{ $u->no_telp ?? $u->no_telpon ?? $u->phone }}</td>
                <td class="px-4 py-2 border-b">
                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm">{{ $totalOrders }}</span>
                </td>
                <td class="px-4 py-2 border-b">{{ $metodePembayaran ?: '-' }}</td>
                <!-- <td class="px-4 py-2 border-b">{{ $pickupStatus ?: '-' }}</td> -->
                <td class="px-4 py-2 border-b space-x-2">
                    {{-- Lihat detail pelanggan --}}
                    <a href="{{ route('admin.pelanggan.show', $u->id) }}" class="text-blue-600 hover:underline">👁️</a>

                    {{-- (Opsional) Lihat pesanan terakhir --}}
                    {{-- @if($last)
                    <a href="{{ url('/admin/daftar-pesanan/'.$last->id) }}"
                        class="text-indigo-600 hover:underline">🧾</a>
                    @endif --}}

                    {{-- Hapus pelanggan --}}
                    <form action="{{ route('admin.pelanggan.destroy', $u->id) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline"
                            onclick="return confirm('Yakin hapus pelanggan ini?')">🗑️</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-4 py-6 text-center text-gray-500">Belum ada pelanggan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if(session('success'))
    <div class="mt-4 p-4 bg-green-200 text-green-700 rounded">
        {{ session('success') }}
    </div>
    @endif
</div>
@endsection
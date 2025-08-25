@extends('tailor.layouts.app')

@section('title', 'Data Pesanan')

@section('content')
    <div class="max-w-7xl mx-auto">
        <h1 class="text-2xl font-semibold text-gray-800 mb-6 text-center">Data Pesanan Pelanggan</h1>

        <!-- Versi Tabel Data Pesanan -->
        <table class="table-auto w-full border-collapse bg-white rounded shadow">
            <thead>
                <tr>
                    <th class="px-4 py-2 border-b">No</th>
                    <th class="px-4 py-2 border-b">Nama Pemesan</th>
                    <th class="px-4 py-2 border-b">Tanggal/Hari</th>
                    <th class="px-4 py-2 border-b">ID Pesanan</th>
                    <th class="px-4 py-2 border-b">Status Produksi</th>
                    <th class="px-4 py-2 border-b">Status Pesanan</th>
                    <th class="px-4 py-2 border-b">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $index => $o)
                    <tr>
                        <td class="px-4 py-2 border-b">{{ $index + 1 }}</td>
                        <td class="px-4 py-2 border-b">{{ optional($o->user)->name ?? optional($o->user)->nama ?? '-' }}</td>
                        <td class="px-4 py-2 border-b">
                            {{ \Carbon\Carbon::parse($o->order_date ?? $o->created_at)->format('l, j/n/Y') }}
                        </td>
                        <td class="px-4 py-2 border-b">{{ $o->order_id ?? $o->order_code ?? $o->id }}</td>
                        <td class="px-4 py-2 border-b">
                            <span class="text-sm text-stone-600">
                                {{ $o->production_status ?? '-' }}
                            </span>
                        </td>
                        <td class="px-4 py-2 border-b">
                            <span class="text-sm font-semibold 
                                {{ $o->status == 'selesai' ? 'text-green-500' : 
                                    ($o->status == 'menunggu' ? 'text-yellow-500' : 'text-blue-500') }}">
                                {{ ucfirst($o->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-2 border-b">
                            <a href="{{ route('tailor.orders.show', $o->order_id ?? $o->order_code ?? $o->id) }}"
                               class="px-3 py-1 bg-black text-white rounded">Detail</a>
                            
                            <form action="{{ route('tailor.update.status', $o->id) }}" method="POST" class="inline-block mt-1">
                                @csrf
                                @method('PUT')
                                <select name="status" class="border border-gray-300 rounded-md p-1">
                                    <option value="menunggu" {{ $o->status == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                                    <option value="selesai" {{ $o->status == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                    <option value="diambil" {{ $o->status == 'diambil' ? 'selected' : '' }}>Diambil</option>
                                </select>
                                <button type="submit" class="bg-blue-500 text-white py-1 px-3 rounded-md">Update</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Versi Card List ala "Pesanan Untuk Saya" -->
        <div class="mt-10">
            <h2 class="text-xl font-semibold mb-4">Pesanan Untuk Saya (Ringkas)</h2>
            @forelse($orders as $o)
                <div class="bg-white rounded-xl shadow p-4 mb-3">
                    <div class="flex justify-between">
                        <div>
                            <div class="font-semibold">{{ $o->title ?? ('Order #' . ($o->order_id ?? $o->id)) }}</div>
                            <div class="text-xs text-gray-500">
                                Order ID: {{ $o->order_id ?? $o->order_code ?? $o->id }}
                                • User: {{ optional($o->user)->name ?? optional($o->user)->nama ?? '-' }}
                            </div>
                            @if($o->production_status)
                                <div class="text-xs mt-1 text-stone-600">Produksi: {{ $o->production_status }}</div>
                            @endif
                        </div>
                        <a href="{{ route('tailor.orders.show', $o->order_id ?? $o->order_code ?? $o->id) }}"
                           class="px-3 py-1 bg-black text-white rounded">Detail</a>
                    </div>
                </div>
            @empty
                <div class="text-gray-500">Belum ada tugas.</div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="mt-4">{{ $orders->links() }}</div>
    </div>
@endsection

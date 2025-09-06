@extends('tailor.layouts.app')

@section('title', 'Data Pesanan')

@section('content')
<div class="max-w-7xl mx-auto">
    @if(session('success'))
    <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
    @endif

    <h1 class="text-2xl font-semibold text-gray-800 mb-6 text-center">Pesanan Pelanggan</h1>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-amber-100 text-gray-700">
                    <tr class="border-b">
                        <th class="p-3 text-left w-16">No</th>
                        <th class="p-3 text-left">Nama Pemesan</th>
                        <th class="p-3 text-left">Tanggal/Hari</th>
                        <th class="p-3 text-left">ID Pesanan</th>
                        <th class="p-3 text-left">Detail Pesanan</th>
                        <th class="p-3 text-left">Status Pesanan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $index => $order)
                    @php
                    $namaUser = $order->user->nama ?? $order->user->name ?? '—';
                    $kodeOrder = $order->order_code ?? $order->kode_pesanan ?? '#' . $order->id;
                    $tanggal = $order->created_at;
                    $hari = $tanggal->translatedFormat('l');
                    $tanggalFormatted = $tanggal->format('d-m-Y');

                    // Status colors
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

                    <tr class="border-b last:border-0 hover:bg-gray-50">
                        <td class="p-3">{{ $index + 1 }}</td>
                        <td class="p-3">
                            <div class="font-medium">{{ $namaUser }}</div>
                        </td>
                        <td class="p-3">
                            <div class="font-medium">{{ $hari }}</div>
                            <div class="text-xs text-gray-500">{{ $tanggalFormatted }}</div>
                        </td>
                        <td class="p-3">
                            <span class="font-mono text-sm">{{ $kodeOrder }}</span>
                        </td>
                        <td class="p-3">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('tailor.data.pesanan.show', $order->id) }}" class="text-blue-600 hover:text-blue-800" title="Lihat Detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                            </div>
                        </td>
                        <td class="p-3">
                            <div class="flex items-center gap-2">
                                <form action="{{ route('tailor.update.status', $order->id) }}" method="POST" class="flex items-center gap-2">
                                    @csrf
                                    @method('PUT')
                                    <select name="status" class="text-xs border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                        <option value="menunggu" {{ $order->status == 'menunggu' ? 'selected' : '' }}>Menunggu Giliran</option>
                                        <option value="diproses" {{ $order->status == 'diproses' ? 'selected' : '' }}>Sedang Dikerjakan</option>
                                        <option value="selesai" {{ $order->status == 'selesai' ? 'selected' : '' }}>Selesai Dikerjakan</option>
                                        <option value="siap-diambil" {{ $order->status == 'siap-diambil' ? 'selected' : '' }}>Siap Diambil</option>
                                    </select>
                                    <button type="submit" class="text-blue-600 hover:text-blue-800 text-xs">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                            Belum ada data pesanan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
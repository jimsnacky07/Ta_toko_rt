<!-- resources/views/tailor/riwayat_pesanan.blade.php -->
@extends('tailor.layouts.app')

@section('title', 'Riwayat Pesanan')

@section('content')
    <div class="max-w-7xl mx-auto">
        <h1 class="text-2xl font-semibold text-gray-800 mb-6 text-center">
            Riwayat Pesanan Pelanggan
        </h1>

        <table class="table-auto w-full border-collapse">
            <thead>
                <tr>
                    <th class="px-4 py-2 border-b">No</th>
                    <th class="px-4 py-2 border-b">Nama Pemesan</th>
                    <th class="px-4 py-2 border-b">Tanggal/Hari</th>
                    <th class="px-4 py-2 border-b">ID Pesanan</th>
                    <th class="px-4 py-2 border-b">Detail Pesanan</th>
                    <th class="px-4 py-2 border-b">Status Pesanan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orders as $index => $order)
                    <tr>
                        <td class="px-4 py-2 border-b">{{ $index + 1 }}</td>
                        <td class="px-4 py-2 border-b">{{ $order->user->nama }}</td>
                        <td class="px-4 py-2 border-b">{{ $order->order_date }}</td>
                        <td class="px-4 py-2 border-b">{{ $order->id }}</td>
                        <td class="px-4 py-2 border-b">
                            <a href="{{ route('tailor.detail.pesanan', $order->id) }}" class="text-blue-500">
                                Lihat Detail
                            </a>
                        </td>
                        <td class="px-4 py-2 border-b">{{ $order->status }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

@extends('layouts.admin')

@section('title', 'Pelanggan')

@section('content')
<div class="container mx-auto p-4 bg-white rounded shadow">
    <h2 class="text-lg font-bold mb-4">Daftar Pelanggan</h2>
    <table class="table-auto w-full border-collapse border border-gray-300">
        <thead>
            <tr class="bg-[#D2B48C] text-white">
                <th class="border p-2">No</th>
                <th class="border p-2">Email</th>
                <th class="border p-2">Nama</th>
                <th class="border p-2">No Telepon</th>
                <th class="border p-2">Action</th>
                <th class="border p-2">Status Pembayaran</th>
                <th class="border p-2">Status Penjemputan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pelanggan as $index => $p)
            <tr>
                <td class="border p-2 text-center">{{ $index + 1 }}</td>
                <td class="border p-2">{{ $p->email }}</td>
                <td class="border p-2">{{ $p->name }}</td>
                <td class="border p-2">{{ $p->no_telepon }}</td>
                <td class="border p-2 text-center">
                    <a href="#" class="text-blue-500">👁</a>
                    <a href="#" class="text-red-500 ml-2">🗑</a>
                </td>
                <td class="border p-2 text-center">
                    @if($p->orders->last()->status_pembayaran == 'lunas')
                        ✅
                    @else
                        ⏳
                    @endif
                </td>
                <td class="border p-2 text-center">
                    @if($p->orders->last()->status_penjemputan == 'sudah')
                        🚗
                    @else
                        ⭕
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center p-4">Belum ada pelanggan</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

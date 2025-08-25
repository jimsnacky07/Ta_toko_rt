@extends('admin.layouts.admin')

@section('title','Daftar Pesanan')
@section('content')
<h2 class="text-3xl font-semibold mb-6 text-center">Daftar Pesanan</h2>

<!-- Table -->
<div class="overflow-x-auto bg-white shadow-lg rounded-lg">
    <table class="min-w-full text-sm text-gray-700">
        <thead class="bg-[#EAE0C8]">
            <tr>
                <th class="px-6 py-3 text-center">No</th>
                <th class="px-6 py-3">Nama Pemesan</th>
                <th class="px-6 py-3 text-center">ID Pesanan</th>
                <th class="px-6 py-3">Status Pesanan</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @foreach($orders as $i => $p)
                <tr class="hover:bg-gray-100">
                    <td class="px-6 py-3 text-center">{{ $orders->firstItem() + $i }}</td>
                    <td class="px-6 py-3">{{ $p->user?->name ?? '—' }}</td>
                    <td class="px-6 py-3 text-center">{{ $p->id }}</td>
                    <td class="px-6 py-3">
                        <!-- Menampilkan status pesanan dengan badge -->
                        @switch($p->status)
                            @case('menunggu_giliran')
                                <span class="badge bg-warning">Menunggu Giliran</span>
                                @break
                            @case('sedang_dikerjakan')
                                <span class="badge bg-info">Sedang Dikerjakan</span>
                                @break
                            @case('selesai_dikerjakan')
                                <span class="badge bg-success">Selesai Dikerjakan</span>
                                @break
                            @case('siap_dijemput')
                                <span class="badge bg-primary">Siap Dijemput</span>
                                @break
                            @default
                                <span class="badge bg-secondary">Status Tidak Diketahui</span>
                        @endswitch

                        <!-- Form untuk update status (Hanya untuk Tailor) -->
                        @if(auth()->user()->level === 'tailor')
                            <form action="{{ route('admin.daftar.pesanan.updateStatus', $p) }}" method="POST" class="flex items-center gap-2 mt-2">
                                @csrf @method('PATCH')
                                <select name="status" class="border rounded px-3 py-2" onchange="this.form.submit()">
                                    @foreach($statuses as $s)
                                        <option value="{{ $s }}" @selected($p->status === $s)>{{ ucfirst(str_replace('-', ' ', $s)) }}</option>
                                    @endforeach
                                </select>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="px-6 py-3">
        {{ $pesanans->links() }}
    </div>
</div>
@endsection

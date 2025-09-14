@extends('admin.layouts.app')
@section('title','Pendapatan Toko Bulan Ini')

@section('content')
<div class="max-w-7xl mx-auto">
  <h1 class="text-2xl font-semibold text-gray-800 mb-6 text-center">
    Pendapatan Toko Bulan Ini
  </h1>

  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow p-5">
      <div class="text-sm text-gray-500">Jumlah Pesanan (Bulan Ini)</div>
      <div class="mt-2 text-3xl font-bold">{{ $ordersThisMonth }}</div>
    </div>

    <div class="bg-white rounded-xl shadow p-5">
      <div class="text-sm text-gray-500">Pesanan Proses</div>
      <div class="mt-2 text-3xl font-bold">{{ $inProcess }}</div>
    </div>

    <div class="bg-white rounded-xl shadow p-5">
      <div class="text-sm text-gray-500">Pesanan Selesai</div>
      <div class="mt-2 text-3xl font-bold">{{ $completed }}</div>
    </div>

    <div class="bg-white rounded-xl shadow p-5">
      <div class="text-sm text-gray-500">Pesanan Menunggu</div>
      <div class="mt-2 text-3xl font-bold">{{ $pending }}</div>
    </div>

    <div class="bg-white rounded-xl shadow p-5">
      <div class="text-sm text-gray-500">Pesanan Hari Ini</div>
      <div class="mt-2 text-3xl font-bold">{{ $ordersToday }}</div>
    </div>

    <div class="bg-white rounded-xl shadow p-5">
      <div class="text-sm text-gray-500">Jumlah Pelanggan</div>
      <div class="mt-2 text-3xl font-bold">{{ $customers }}</div>
    </div>
  </div>

  <div class="bg-white rounded-xl shadow overflow-hidden">
    <div class="px-5 py-4 border-b">
      <h2 class="font-semibold">Pesanan Terbaru</h2>
    </div>
    <div class="p-5 overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="text-left text-gray-500 border-b">
          <tr>
            <th class="py-2 pr-4">ID</th>
            <th class="py-2 pr-4">User</th>
            <th class="py-2 pr-4">Status</th>
            <th class="py-2">Tanggal</th>
          </tr>
        </thead>
        <tbody>
          @forelse($latestOrders as $o)
          @if(in_array($o->status, ['dibatalkan','cancelled','canceled','batal']))
          @continue
          @endif
          <tr class="border-b last:border-0">
            <td class="py-2 pr-4">#{{ $o->id }}</td>
            <td class="py-2 pr-4">{{ $o->user_id }}</td>
            <td class="py-2 pr-4">
              <span class="px-2 py-0.5 rounded-full text-xs
                  @class([
                    'bg-yellow-100 text-yellow-800' => in_array($o->status, ['menunggu','unpaid','waiting']),
                    'bg-blue-100 text-blue-800'     => in_array($o->status, ['diproses','proses','processing']),
                    'bg-green-100 text-green-800'   => in_array($o->status, ['selesai','completed','done','success']),
                    'bg-purple-100 text-purple-800'   => in_array($o->status, ['siap-diambil','siap-dijemput']),
                    'bg-gray-100 text-gray-800'     => true,
                  ])">
                {{ ucfirst($o->status) }}
              </span>
            </td>
            <td class="py-2">{{ $o->created_at->format('d M Y H:i') }}</td>
          </tr>
          @empty
          <tr>
            <td colspan="4" class="py-3 text-center text-gray-500">Belum ada pesanan.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
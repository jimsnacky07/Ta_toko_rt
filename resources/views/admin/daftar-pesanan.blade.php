@extends('admin.layouts.app')
@section('title','Daftar Pesanan')

@section('content')
<div class="max-w-6xl mx-auto">

  @if(session('ok'))
    <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('ok') }}</div>
  @endif

  <h1 class="text-2xl font-semibold mb-4 text-center">Daftar Pesanan</h1>

  {{-- Filter sederhana (opsional) --}}
  <form method="get" class="mb-4 flex flex-wrap gap-2">
    <input type="text" name="q" value="{{ request('q') }}" class="border rounded p-2 w-64" placeholder="Cari nama/email/ID...">
    <select name="status" class="border rounded p-2">
      <option value="">Semua status bayar</option>
      <option value="PENDING"  @selected(request('status')==='PENDING')>PENDING</option>
      <option value="PAID"     @selected(request('status')==='PAID')>PAID</option>
      <option value="CANCELED" @selected(request('status')==='CANCELED')>CANCELED</option>
    </select>
    <button class="px-4 py-2 bg-black text-white rounded">Filter</button>
  </form>

  <div class="bg-white rounded-xl shadow overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-amber-100 text-gray-700">
          <tr class="border-b">
            <th class="p-3 text-left w-16">No</th>
            <th class="p-3 text-left">Nama Pemesan</th>
            <th class="p-3 text-left">ID Pesanan</th>
            <th class="p-3 text-left">Status Bayar</th>
            <th class="p-3 text-left">Status Produksi</th>
            <th class="p-3 text-left">Assign Tailor</th>
          </tr>
        </thead>
        <tbody>
          @forelse($orders as $i => $o)
            @php
              // pilih field nama yang ada
              $namaUser   = $o->user->name ?? $o->user->nama ?? '—';
              $emailUser  = $o->user->email ?? '';
              $kodeOrder  = $o->order_id ?? $o->order_code ?? $o->id;
              // status bayar: ambil dari kolom order->status atau payment->transaction_status
              $statusBayar = $o->status ?? (optional($o->payment)->transaction_status ?? '-');

              // mapping badge warna untuk status bayar (gabungan punyamu + midtrans)
              $isPending   = in_array(strtolower($statusBayar), ['pending','unpaid','waiting']) ||
                             str_contains(strtolower($statusBayar), 'pending');
              $isProcess   = in_array(strtolower($statusBayar), ['proses','process','processing','approved','challenge']) ||
                             str_contains(strtolower($statusBayar), 'process');
              $isSuccess   = in_array(strtolower($statusBayar), ['paid','selesai','completed','done','success','settlement','capture']);
              $badgeClass  = $isPending ? 'bg-yellow-100 text-yellow-800'
                           : ($isProcess ? 'bg-blue-100 text-blue-800'
                           : ($isSuccess ? 'bg-green-100 text-green-800'
                           : 'bg-gray-100 text-gray-800'));

              // label produksi
              $prodLabel = match($o->production_status ?? null) {
                'QUEUE' => 'Menunggu giliran',
                'IN_PROGRESS' => 'Sedang dikerjakan',
                'DONE' => 'Selesai',
                default => $o->production_status ?? '—',
              };
            @endphp

            <tr class="border-b last:border-0">
              <td class="p-3">{{ $orders->firstItem() + $i }}</td>

              <td class="p-3">
                {{ $namaUser }}
                @if($emailUser)
                  <div class="text-xs text-gray-500">{{ $emailUser }}</div>
                @endif
              </td>

              <td class="p-3">#{{ $kodeOrder }}</td>

              <td class="p-3">
                <span class="px-2 py-0.5 rounded-full text-xs {{ $badgeClass }}">
                  {{ strtoupper($statusBayar) }}
                </span>
              </td>

              <td class="p-3">
                <span class="text-xs font-medium
                  @class([
                    'text-stone-700' => ($o->production_status ?? '') === 'QUEUE',
                    'text-yellow-700' => ($o->production_status ?? '') === 'IN_PROGRESS',
                    'text-green-700' => ($o->production_status ?? '') === 'DONE',
                    'text-stone-600' => ! in_array(($o->production_status ?? ''), ['QUEUE','IN_PROGRESS','DONE']),
                  ])">
                  {{ $prodLabel }}
                </span>
              </td>

              <td class="p-3">
                <form method="post"
                      action="{{ route('admin.orders.assign', $kodeOrder) }}"
                      class="flex gap-2">
                  @csrf
                  <select name="tailor_id" class="border rounded p-1">
                    <option value="">- pilih -</option>
                    @foreach($tailors as $t)
                      <option value="{{ $t->id }}" @selected(($o->tailor_id ?? null) == $t->id)>{{ $t->name }}</option>
                    @endforeach
                  </select>
                  <button class="px-3 py-1 bg-black text-white rounded">Simpan</button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="px-4 py-6 text-center text-gray-500">Belum ada pesanan.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if(method_exists($orders, 'links'))
      <div class="px-4 py-3 border-t bg-gray-50">
        {{ $orders->withQueryString()->links() }}
      </div>
    @endif
  </div>
</div>
@endsection

@extends('user.layouts.app')

@section('content')
@php
  use Illuminate\Support\Str;

  // Fungsi kecil untuk memberi warna badge status pembayaran
  $badgePay = function ($status) {
      $s = strtoupper((string) $status);
      return match (true) {
          str_contains($s,'PAID') || str_contains($s,'SETTLEMENT') || str_contains($s,'CAPTURE')
              => 'text-green-700 bg-green-100',
          str_contains($s,'PENDING')
              => 'text-yellow-700 bg-yellow-100',
          str_contains($s,'CANCEL') || str_contains($s,'EXPIRE') || str_contains($s,'DENY') || str_contains($s,'FAIL')
              => 'text-red-700 bg-red-100',
          default => 'text-stone-700 bg-stone-100',
      };
  };

  // Warna untuk status produksi
  $badgeProd = function ($prod) {
      return match ($prod) {
          'IN_PROGRESS' => 'text-yellow-600',
          'DONE'        => 'text-green-600',
          'QUEUE', null => 'text-stone-600',
          default       => 'text-stone-600',
      };
  };

  // Label Indonesia untuk status produksi
  $labelProd = function ($prod) {
      return match ($prod) {
          'QUEUE'       => 'Menunggu giliran',
          'IN_PROGRESS' => 'Sedang dikerjakan',
          'DONE'        => 'Selesai',
          default       => $prod,
      };
  };
@endphp

{{-- Header --}}
<div class="flex items-center gap-2 mb-6">
  <img src="{{ asset('images/panah.png') }}" alt="Kembali" class="w-5 h-5">
  <h1 class="text-xl font-bold">Pusat Pesanan</h1>
</div>

{{-- Tabs sederhana --}}
@php $tab = request('tab','all'); @endphp
<div class="flex items-center gap-4 mb-4">
  <a href="{{ route('pusat.pesanan', ['tab'=>'all']) }}">Semua</a>
  <a href="{{ route('pusat.pesanan', ['tab'=>'orders']) }}">Pesanan</a>
  <a href="{{ route('pusat.pesanan', ['tab'=>'unpaid']) }}">Belum Dibayar</a>
</div>

{{-- Flash message --}}
@if(session('success'))
  <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
@endif
@if(session('warning'))
  <div class="mb-4 p-3 bg-yellow-100 text-yellow-800 rounded">{{ session('warning') }}</div>
@endif
@if(session('error'))
  <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">{{ session('error') }}</div>
@endif

{{-- Daftar Pesanan --}}
<div class="space-y-4">
  @forelse($orders as $o)
    @php
      // Tentukan status bayar dari kolom order/status atau payment->transaction_status
      $paidStatus = $o->status ?? optional($o->payment)->transaction_status ?? '-';
      // Filter sederhana berdasarkan tab
      $skip = $tab === 'unpaid' ? !str_contains(strtoupper($paidStatus), 'PENDING') && !str_contains(strtoupper($paidStatus), 'UNPAID') : false;
    @endphp

    @if(!$skip)
    <div class="flex gap-4 items-center border-b pb-4">
      <div class="w-24 h-24 bg-gray-200 flex items-center justify-center overflow-hidden rounded">
        @php
          $img = null;
          if (!empty($o->images) && is_array($o->images)) {
            $img = $o->images[0] ?? null;
          }
        @endphp
        @if($img)
          <img src="{{ $img }}" alt="Foto" class="w-full h-full object-cover">
        @else
          <span class="text-gray-500">X</span>
        @endif
      </div>

      <div class="flex-1">
        <div class="font-medium">
          {{ $o->title ?? 'Pesanan #' . ($o->order_id ?? $o->order_code ?? $o->id) }}
        </div>
        <div class="text-xs text-gray-500">
          Order ID: {{ $o->order_id ?? $o->order_code ?? $o->id }}
          • Tgl: {{ optional($o->ordered_at ?? $o->created_at)->format('d M Y') }}
        </div>
        <p class="text-gray-700 mt-1">
          @php
            $ringkas = $o->description ?? ($o->garment_type ? ('Jenis pakaian: '.$o->garment_type) : null) ?? ($o->fabric_type  ? ('Jenis kain: '.$o->fabric_type) : null);
          @endphp
          {{ $ringkas ? Str::limit($ringkas, 140) : 'Detail pesanan ditampilkan di sini.' }}
        </p>
        <div class="mt-1 text-sm">
          Total: <span class="font-semibold">
            Rp{{ number_format($o->amount ?? $o->gross_amount ?? 0, 0, ',', '.') }}
          </span>
        </div>
      </div>

      <div class="text-right">
        <div class="text-sm font-semibold {{ $badgeProd($o->production_status ?? null) }}">
          {{ $labelProd($o->production_status ?? null) }}
        </div>
        <div class="mt-2">
          <span class="text-xs px-2 py-1 rounded {{ $badgePay($paidStatus) }}">
            Bayar: {{ strtoupper($paidStatus) }}
          </span>
        </div>
      </div>
    </div>
    @endif
  @empty
    <div class="text-gray-500">Belum ada pesanan.</div>
  @endforelse
</div>

{{-- pagination jika menggunakan paginate() --}}
@if(method_exists($orders, 'links'))
  <div class="mt-4">
    {{ $orders->withQueryString()->links() }}
  </div>
@endif


@endsection

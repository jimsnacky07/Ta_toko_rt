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

{{-- Debug Info --}}
<div class="mb-4 p-3 bg-blue-100 text-blue-800 rounded">
  <strong>Debug Info:</strong><br>
  User ID: {{ Auth::id() }}<br>
  User Name: {{ Auth::user() ? Auth::user()->nama : 'No user' }}<br>
  Orders Count: {{ is_countable($orders) ? count($orders) : 0 }}<br>
  Orders Type: {{ gettype($orders) }}
</div>

{{-- Daftar Pesanan --}}
<div class="space-y-4">
  @if(is_countable($orders) && count($orders) > 0)
    @foreach($orders as $o)
      @php
        // Status pembayaran dari kolom status di orders
        $paidStatus = $o->status ?? 'pending';
        
        // Ambil item pertama untuk menampilkan detail
        $firstItem = $o->orderItems->first();
        $itemCount = $o->orderItems->count();
      @endphp

      <div class="flex gap-4 items-center border-b pb-4">
        <div class="w-24 h-24 bg-gray-200 flex items-center justify-center overflow-hidden rounded">
          <span class="text-gray-500 text-xs">{{ $firstItem->garment_type ?? 'Item' }}</span>
        </div>

        <div class="flex-1">
          <div class="font-medium">
            Pesanan #{{ $o->order_code }}
          </div>
          <div class="text-xs text-gray-500">
            Order ID: {{ $o->order_code }}
            • Tgl: {{ $o->created_at->format('d M Y') }}
          </div>
          <p class="text-gray-700 mt-1">
            @if($firstItem)
              {{ $firstItem->garment_type }} - {{ $firstItem->fabric_type }}
              @if($itemCount > 1)
                <span class="text-gray-500">dan {{ $itemCount - 1 }} item lainnya</span>
              @endif
            @else
              Detail pesanan tidak tersedia
            @endif
          </p>
          <div class="mt-1 text-sm">
            Total: <span class="font-semibold">
              Rp{{ number_format($o->total_amount ?? 0, 0, ',', '.') }}
            </span>
          </div>
          @if($firstItem && $firstItem->special_request)
            <div class="text-xs text-gray-600 mt-1">
              Catatan: {{ Str::limit($firstItem->special_request, 50) }}
            </div>
          @endif
        </div>

        <div class="text-right">
          <div class="text-sm font-semibold text-blue-600">
            {{ $itemCount }} item{{ $itemCount > 1 ? 's' : '' }}
          </div>
          <div class="mt-2">
            <span class="text-xs px-2 py-1 rounded {{ $badgePay($paidStatus) }}">
              {{ ucfirst($paidStatus) }}
            </span>
          </div>
          @if($o->paid_at)
            <div class="text-xs text-gray-500 mt-1">
              Dibayar: {{ $o->paid_at->format('d/m/Y H:i') }}
            </div>
          @endif
        </div>
      </div>
    @endforeach
  @else
    <div class="text-gray-500">
      Belum ada pesanan.<br>
      <small>Debug: Orders variable type: {{ gettype($orders) }}</small>
    </div>
  @endif
</div>

{{-- pagination jika menggunakan paginate() --}}
@if(method_exists($orders, 'links'))
  <div class="mt-4">
    {{ $orders->withQueryString()->links() }}
  </div>
@endif


@endsection

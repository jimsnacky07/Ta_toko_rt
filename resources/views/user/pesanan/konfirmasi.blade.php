@extends('user.layouts.app')

@section('title', 'Konfirmasi Pesanan')

@section('content')
@php
  // Ambil payload dari controller (data) atau dari query
  $q = $data ?? request()->all();

  $jenisPakaian = strtolower($q['jenis_pakaian'] ?? '-');
  $jenisKain    = strtolower($q['jenis_kain']    ?? '-');
  $catatan      = $q['request'] ?? '-';
  $pickup       = $q['pickup']  ?? '-';

  // qty final dari controller (variabel $qty), fallback ke query.
  $qtyShown     = (int)($qty ?? $q['jumlah'] ?? 1);
  if ($qtyShown < 1) $qtyShown = 1;

  // Pastikan variabel harga dari controller tersedia
  $base       = (int)($base       ?? 0);
  $fabricAdd  = (int)($fabricAdd  ?? 0);
  $unitTotal  = (int)($unitTotal  ?? 0);
  $grand      = (int)($grand      ?? 0);
  $snapToken  = $snapToken ?? null;
@endphp

<div class="bg-white p-6 rounded shadow-md max-w-4xl mx-auto">
  <h2 class="text-xl font-bold mb-4 text-center text-[#A38C6C]">Konfirmasi Pemesanan</h2>

  <!-- Info pesanan -->
  <div class="bg-gray-100 p-4 rounded mb-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
      <p class="mb-0"><strong>Jenis Pakaian:</strong> {{ $jenisPakaian ?: '-' }}</p>
      <p class="mb-0"><strong>Jenis Kain:</strong> {{ $jenisKain ?: '-' }}</p>
      <p class="mb-0"><strong>Jumlah:</strong> {{ $qtyShown }}</p>
      <p class="mb-0"><strong>Pick-up:</strong> {{ $pickup ?: '-' }}</p>
      <p class="md:col-span-2 mb-0"><strong>Request Customer:</strong> {{ $catatan ?: '-' }}</p>
      <p class="md:col-span-2 mb-0"><strong>Gambar Tambahan:</strong> Tidak ada gambar diunggah</p>
    </div>
  </div>

  <!-- Ringkasan harga (PERSIS dari controller) -->
  <div class="bg-gray-50 p-4 rounded mb-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div>
        <div class="text-sm text-gray-600">Base / item</div>
        <div class="font-semibold">Rp{{ number_format($base, 0, ',', '.') }}</div>
      </div>
      <div>
        <div class="text-sm text-gray-600">Tambahan Kain / item</div>
        <div class="font-semibold">Rp{{ number_format($fabricAdd, 0, ',', '.') }}</div>
      </div>
      <div>
        <div class="text-sm text-gray-600">Total / item</div>
        <div class="font-semibold">Rp{{ number_format($unitTotal, 0, ',', '.') }}</div>
      </div>
      <div>
        <div class="text-sm text-gray-600">Total (× jumlah)</div>
        <div class="text-xl font-bold">Rp{{ number_format($grand, 0, ',', '.') }}</div>
      </div>
    </div>
  </div>

  <!-- Pesan -->
  <div class="border p-4 rounded text-justify text-gray-800 leading-relaxed mb-6">
    <p>Terima kasih telah melakukan pemesanan. Silakan tinjau kembali semua detail pesanan Anda.
      Jika semua sudah benar, lanjutkan dengan menekan tombol <strong>"Pesan Sekarang"</strong>.
      Jika ingin menyimpan dulu, klik tombol <strong>"Simpan"</strong>.
    </p>
  </div>

  <div class="flex flex-wrap items-center justify-between gap-3">
    {{-- Kembali ke form, bawa semua query agar field tetap terisi --}}
    <a href="{{ route('buat.pesanan') . '?' . http_build_query($q) }}"
       class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300">Ubah</a>

    <div class="flex items-center gap-3">
      {{-- Tombol Pesan Selalu Ada: kalau snapToken null, kasih alert --}}
      <a href="#" id="btn-checkout"
         class="bg-[#A38C6C] hover:bg-[#8a7554] text-white px-4 py-2 rounded">
        Pesan Sekarang
      </a>

      {{-- Simpan ke keranjang --}}
      <form action="{{ route('keranjang.tambah') }}" method="POST" class="inline">
        @csrf
        <input type="hidden" name="type" value="custom">

        {{-- Detail pesanan --}}
        <input type="hidden" name="jenis_pakaian" value="{{ $jenisPakaian }}">
        <input type="hidden" name="jenis_kain"    value="{{ $jenisKain }}">
        <input type="hidden" name="pickup"        value="{{ $pickup }}">
        <input type="hidden" name="request"       value="{{ $catatan }}">

        {{-- Untuk tampilan keranjang --}}
        <input type="hidden" name="title"    value="Order Custom">
        <input type="hidden" name="subtitle" value="{{ ucwords(str_replace('_',' ', $jenisPakaian)) }} • {{ ucwords($jenisKain) }}">

        {{-- Harga final & qty (HARUS sama dengan kartu di atas) --}}
        <input type="hidden" name="unit_price"   value="{{ $unitTotal }}">
        <input type="hidden" name="qty"          value="{{ $qtyShown }}">
        <input type="hidden" name="base_price"   value="{{ $base }}">
        <input type="hidden" name="fabric_add"   value="{{ $fabricAdd }}">
        <input type="hidden" name="total_price"  value="{{ $unitTotal }}">
        <input type="hidden" name="grand_total"  value="{{ $grand }}">

        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
          Simpan
        </button>
      </form>
    </div>
  </div>
</div>

{{-- Snap script (boleh tetap dimuat; kalau client_key kosong juga aman) --}}
<script src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="{{ config('midtrans.client_key') }}"></script>
<script>
  document.getElementById('btn-checkout').addEventListener('click', function (e) {
    e.preventDefault();

    const token = @json($snapToken);
    if (token) {
      // Check if popup blocker is active
      const testPopup = window.open('', '_blank', 'width=1,height=1');
      if (!testPopup || testPopup.closed || typeof testPopup.closed == 'undefined') {
        // Popup blocked - use redirect method
        alert('Popup diblokir browser. Mengalihkan ke halaman pembayaran...');
        window.location.href = 'https://app.sandbox.midtrans.com/snap/v2/vtweb/' + token;
        return;
      } else {
        testPopup.close();
      }

      if (window.snap && typeof window.snap.pay === 'function') {
        window.snap.pay(token, {
          onSuccess : () => window.location = "{{ route('user.orders.index') }}",
          onPending : () => window.location = "{{ route('user.orders.index') }}",
          onError   : () => alert('Terjadi kesalahan pembayaran.'),
          onClose   : () => console.log('Popup ditutup.')
        });
      } else {
        // Fallback to redirect if snap not loaded
        window.location.href = 'https://app.sandbox.midtrans.com/snap/v2/vtweb/' + token;
      }
    } else {
      alert('Pembayaran online belum aktif. Silakan klik "Simpan" dulu atau lanjutkan pembayaran manual di toko.');
    }
  });
</script>
@endsection

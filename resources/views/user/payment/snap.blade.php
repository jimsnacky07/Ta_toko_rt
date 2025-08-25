@extends('user.layouts.app')
@section('title','Bayar')

@section('content')
<div class="bg-white rounded-md shadow p-6">
  <h1 class="text-xl font-semibold mb-2">Pembayaran</h1>
  <div class="text-sm text-gray-600">Order ID: <span class="font-medium">{{ $orderId }}</span></div>
  <div class="text-sm text-gray-600 mb-4">Total: <span class="font-semibold">Rp {{ number_format($amount,0,',','.') }}</span></div>

  <button id="payBtn" class="px-5 py-2.5 rounded-2xl bg-blue-600 text-white shadow hover:shadow-md">
    Bayar Sekarang
  </button>

  <p class="text-xs text-gray-500 mt-3">
    Setelah berhasil, status akan diperbarui otomatis melalui notifikasi Midtrans.
  </p>
</div>
@endsection

@push('floating')
  @include('user.partials.whatsapp')
@endpush

@push('scripts')
<script
  src="{{ config('midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
  data-client-key="{{ $clientKey }}">
</script>
<script>
  document.getElementById('payBtn').addEventListener('click', function () {
    window.snap.pay(@json($snapToken), {
      onSuccess:  () => window.location.href = "{{ route('user.midtrans.finish') }}",
      onPending:  () => window.location.href = "{{ route('user.midtrans.unfinish') }}",
      onError:    () => window.location.href = "{{ route('user.midtrans.error') }}",
      onClose:    () => console.log('popup closed')
    });
  });
</script>
@endpush

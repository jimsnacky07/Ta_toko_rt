@extends('user.layouts.app')

@section('title', 'Pilih Metode Pembayaran')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="bg-white rounded-md shadow overflow-hidden">
                <div class="flex items-center justify-between border-b px-5 py-3">
                    <div class="font-semibold">Total Harga</div>
                    <div class="text-right">
                        <div class="font-semibold">Rp {{ number_format($data['total'] ?? 0, 0, ',', '.') }}</div>
                        <div class="text-xs text-gray-500">Order ID #{{ $data['order_id'] }}</div>
                    </div>
                </div>

                <!-- Form untuk checkout -->
                <form id="payment-form" method="POST" action="{{ route('midtrans.create-snap-token') }}">
                    @csrf
                    <input type="hidden" name="order_id" value="{{ $data['order_id'] }}">
                    <input type="hidden" name="total" value="{{ $data['total'] }}">
                    <input type="hidden" name="product_name" value="{{ $data['product_name'] }}">
                    <input type="hidden" name="customer_name" value="{{ auth()->user()->nama ?? 'Customer' }}">
                    <input type="hidden" name="customer_email" value="{{ auth()->user()->email ?? 'customer@example.com' }}">
                    <input type="hidden" name="customer_phone" value="{{ auth()->user()->no_telepon ?? '08123456789' }}">
                </form>

                <button id="pay-button" class="btn btn-primary">Bayar dengan Midtrans</button>
            </div>
        </div>

        <!-- Kolom kanan: Ringkasan order -->
        <div>
            <div class="bg-white rounded-md shadow p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <div class="font-semibold text-sm">{{ $data['product_name'] ?? '-' }}</div>
                        <div class="text-sm text-gray-600">
                            Total: Rp {{ number_format($data['total'] ?? 0, 0, ',', '.') }}
                        </div>
                        <div class="text-xs text-gray-500">
                            Order #{{ $data['order_id'] ?? '-' }}
                        </div>
                    </div>

                    <div class="w-20 h-20 rounded border flex items-center justify-center bg-gray-50">
                        @if (!empty($data['image']))
                            <img src="{{ asset($data['image']) }}" class="max-h-full object-contain" alt="">
                        @else
                            <div class="text-xs text-gray-400">No image</div>
                        @endif
                    </div>
                </div>

                <p class="text-xs text-gray-500 mt-3">
                    Setelah memilih metode, kamu akan mendapat instruksi pembayaran.
                </p>
            </div>
        </div>
    </div>

    <!-- Skrip untuk Snap UI -->
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
    <script type="text/javascript">
        document.getElementById('pay-button').addEventListener('click', function (e) {
            e.preventDefault();

            // Mengambil Snap token dari form
            $.ajax({
                url: "{{ route('midtrans.create-snap-token') }}",
                type: "POST",
                data: $('#payment-form').serialize(),
                success: function (response) {
                    if (response.token) {
                        // Panggil Snap UI
                        snap.pay(response.token, {
                            onSuccess: function (result) {
                                console.log(result);
                                window.location.href = '{{ route("midtrans.finish") }}';
                            },
                            onPending: function (result) {
                                console.log(result);
                                window.location.href = '{{ route("midtrans.unfinish") }}';
                            },
                            onError: function (result) {
                                console.log(result);
                                window.location.href = '{{ route("midtrans.error") }}';
                            }
                        });
                    } else {
                        alert('Terjadi masalah saat mendapatkan Snap Token.');
                    }
                },
                error: function (xhr, status, error) {
                    alert('Terjadi kesalahan. Coba lagi.');
                }
            });
        });
    </script>
@endsection

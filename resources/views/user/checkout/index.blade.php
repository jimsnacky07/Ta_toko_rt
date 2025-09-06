@extends('user.layouts.app')
@section('title', 'Pembayaran')

@section('content')
@php
// ====== Ambil sumber data dari controller ======
$firstItem =
isset($items) && $items instanceof \Illuminate\Support\Collection && $items->count()
? $items->first()
: null;

// Pilihan metode (store|jnt)
$selected = old('pickup_method', $data['pickup_method'] ?? 'store');
$shipRate = 32000;

// ====== Normalisasi nilai agar tidak 0 ======
$price =
(int) ($firstItem['harga'] ?? ($firstItem['price'] ?? (null ?? ($data['harga'] ?? ($data['price'] ?? 0)))));
$qty = (int) ($firstItem['qty'] ?? (null ?? ($data['qty'] ?? ($data['jumlah'] ?? 1))));
$name = $firstItem['name'] ?? (null ?? ($data['product_name'] ?? 'Produk'));

// Gambar: bisa URL penuh, bisa nama file di storage
$imageUrl = $firstItem['image_url'] ?? (null ?? ($data['image'] ?? ($data['gambar'] ?? null)));
if ($imageUrl) {
// kalau bukan http(s) dan bukan path absolut, bungkus images
if (!preg_match('#^(https?://|/)#i', $imageUrl)) {
$imageUrl = asset('images/' . $imageUrl);
}
}

// ====== Ringkasan (fallback kalau controller tidak kirim) ======
$subtotal = isset($subtotal) ? (int) $subtotal : $price * $qty;
$shipping = isset($shipping) ? (int) $shipping : ($selected === 'jnt' ? $shipRate : 0);
$total = isset($total) ? (int) $total : $subtotal + $shipping;
@endphp

<div class="bg-white rounded-md shadow p-4 md:p-6">
    <h1 class="text-2xl font-semibold text-center mb-4">Pembayaran</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- ================== KIRI: Detail & Metode ================== --}}
        <div class="lg:col-span-2 space-y-4">
            {{-- Detail Pesanan --}}
            <div class="border rounded-md p-4">
                <h2 class="font-semibold mb-3">Detail Pesanan</h2>
                <div class="flex gap-4">
                    <div
                        class="w-32 h-32 border rounded flex items-center justify-center p-2 bg-gray-50 overflow-hidden">
                        @if ($imageUrl)
                        <img src="{{ $imageUrl }}" class="max-h-full object-contain" alt="Gambar produk">
                        @else
                        {{-- jika tidak ada gambar, biarkan kosong agar tampilan tetap sama --}}
                        @endif
                    </div>

                    <div class="space-y-1 text-sm">
                        <div class="font-medium text-gray-800">{{ $name }}</div>
                        <div>Harga: <span class="font-semibold">Rp {{ number_format($price, 0, ',', '.') }}</span></div>
                        <div>Jumlah: <span class="font-medium">{{ $qty }}</span></div>

                        {{-- Ukuran yang dipilih --}}
                        @php
                        $selectedSize = $data['size'] ?? (session('selected_size') ?? (request('size') ?? ''));
                        @endphp
                        @if ($selectedSize)
                        <div class="mt-2">
                            <span class="text-xs text-gray-600">Ukuran: </span>
                            <span
                                class="inline-block px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded font-medium">{{ $selectedSize }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Metode Pengambilan --}}
            <div class="border rounded-md p-4">
                <h2 class="font-semibold mb-3">Metode Pengambilan</h2>
                <p class="text-sm text-gray-600 mb-3">Pilih cara menerima pesananmu.</p>

                <div class="space-y-3">
                    <label class="flex items-center gap-2">
                        <input type="radio" id="radio-store" name="pickup_method_choice" value="store"
                            {{ $selected === 'store' ? 'checked' : '' }}>
                        <span class="font-medium">Ambil di Toko (Ongkir Rp 0)</span>
                    </label>

                    <label class="flex items-center gap-2">
                        <input type="radio" id="radio-jnt" name="pickup_method_choice" value="jnt"
                            {{ $selected === 'jnt' ? 'checked' : '' }}>
                        <span class="font-medium">Pick-Up (J&T Express) — Ongkir Rp 32.000</span>
                    </label>
                </div>
            </div>
        </div>

        {{-- ================== KANAN: Ringkasan & Submit ================== --}}
        <div>
            <div class="border rounded-md p-4">
                <h2 class="font-semibold mb-3">Ringkasan</h2>

                <div class="flex justify-between text-sm mb-1">
                    <span>Subtotal</span>
                    <span id="subtotal-text">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-sm mb-3">
                    <span>Ongkir</span>
                    <span id="shipping-text">Rp {{ number_format($shipping, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between font-semibold text-gray-800 text-lg border-t pt-2">
                    <span>Total</span>
                    <span id="total-text">Rp {{ number_format($total, 0, ',', '.') }}</span>
                </div>

                {{-- Hidden angka untuk tampilan (opsional) --}}
                <input type="hidden" id="subtotal-input" value="{{ (int) $subtotal }}">
                <input type="hidden" id="shipping-input" value="{{ (int) $shipping }}">
                <input type="hidden" id="total-input" value="{{ (int) $total }}">

                {{-- ===== FORM LANJUT KE PEMBAYARAN ===== --}}
                <form id="checkout-form" method="POST" action="{{ route('pesanan.store') }}" class="mt-4">
                    <input type="hidden" name="product_id"
                        value="{{ $data['product_id'] ?? ($firstItem['id'] ?? '') }}">
                    @csrf
                    {{-- ringkasan yang dikirim ke server --}}
                    <input type="hidden" name="subtotal" id="f_subtotal" value="{{ (int) $subtotal }}">
                    <input type="hidden" name="shipping" id="f_shipping" value="{{ (int) $shipping }}">
                    <input type="hidden" name="total" id="f_total" value="{{ (int) $total }}">

                    {{-- detail produk yang tampil di kiri --}}
                    <input type="hidden" name="product_name" value="{{ $name }}">
                    <input type="hidden" name="harga" value="{{ (int) $price }}">
                    <input type="hidden" name="qty" value="{{ (int) $qty }}">
                    <input type="hidden" name="image" value="{{ $imageUrl }}">

                    {{-- method pengambilan disinkronkan via JS --}}
                    <input type="hidden" name="pickup_method" id="pickup-method-input" value="{{ $selected }}">

                    {{-- ukuran produk yang dipilih --}}
                    <input type="hidden" name="size" id="selected-size-input" value="{{ $selectedSize }}">

                    {{-- ==== WAJIB KIRIM KE SERVER (sesuai validasi 422 kamu) ==== --}}
                    @php
                    $u = auth()->user();
                    $orderIdHidden = $data['order_id'] ?? 'ORD-' . now()->format('YmdHis');
                    @endphp
                    <input type="hidden" name="order_id" value="{{ $orderIdHidden }}">
                    <input type="hidden" name="customer_name" value="{{ $u->name ?? ($u->nama ?? 'Guest') }}">
                    <input type="hidden" name="customer_email" value="{{ $u->email ?? 'guest@example.com' }}">
                    <input type="hidden" name="customer_phone"
                        value="{{ $u->no_telp ?? ($u->phone ?? '081234567890') }}">

                    <button id="myButton" type="submit"
                        class="w-full px-5 py-2.5 rounded-2xl bg-blue-600 text-white font-medium shadow hover:shadow-md">
                        Lanjut ke Pembayaran
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- ================== SCRIPTS ================== --}}
@push('scripts')
{{-- Hapus baris ini jika snap.js sudah dimuat di layout global --}}
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}">
</script>

<script>
    (function() {
        console.log('[CHECKOUT] script injected via push(scripts)');

        document.addEventListener('DOMContentLoaded', function() {
            console.log('[CHECKOUT] DOMContentLoaded');

            const payBtn = document.getElementById('myButton'); // <- pastikan ID sama dengan tombol
            const form = document.getElementById('checkout-form');
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            if (!payBtn) {
                console.error('[CHECKOUT] ❌ #myButton tidak ditemukan');
                return;
            }
            if (!form) {
                console.error('[CHECKOUT] ❌ #checkout-form tidak ditemukan');
                return;
            }

            // paksa type=button agar tidak auto submit
            if (!payBtn.getAttribute('type')) payBtn.setAttribute('type', 'button');

            // === fungsi util ===
            function show422(respStatus, raw) {
                try {
                    const err = JSON.parse(raw);
                    alert(
                        `HTTP ${respStatus}\n` +
                        (err.message ? `${err.message}\n` : '') +
                        (err.errors ? JSON.stringify(err.errors, null, 2) : raw)
                    );
                } catch (_) {
                    alert(`HTTP ${respStatus}\n${raw}`);
                }
            }

            // log cek klik
            payBtn.addEventListener('click', function() {
                console.log('[CHECKOUT] tombol diklik');
            });

            let busy = false;
            payBtn.addEventListener('click', async function(e) {
                e.preventDefault();
                if (busy) return;

                // Ukuran sudah dipilih dari halaman produk, tidak perlu validasi lagi

                busy = true;
                payBtn.disabled = true;

                // pastikan snap ada (kalau tidak, nanti fallback VTWeb)
                if (typeof snap === 'undefined' || typeof snap.pay !== 'function') {
                    console.warn(
                        '[CHECKOUT] snap.js belum ter-load; fallback VTWeb jika token didapat'
                    );
                }

                try {
                    const resp = await fetch("{{ route('user.user.midtrans.create-snap-token') }}", {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrf || ''
                        },
                        body: new FormData(form) // @csrf ikut terkirim
                    });

                    const raw = await resp.text();
                    console.log('[CHECKOUT] response raw:', raw);

                    let data = null;
                    try {
                        data = JSON.parse(raw);
                    } catch (_) {}

                    if (!resp.ok) {
                        // tampilkan alasan 422/500 dari server
                        show422(resp.status, raw);
                        return;
                    }
                    if (!data || !data.token) {
                        alert(
                            'Response tidak mengandung token. Cek controller create-snap-token.'
                        );
                        return;
                    }

                    // kalau snap.js ada, tampilkan popup
                    if (typeof snap !== 'undefined' && typeof snap.pay === 'function') {
                        console.log('[CHECKOUT] panggil snap.pay dengan token:', data.token);
                        snap.pay(data.token, {
                            onSuccess: function(result) {
                                console.log('[CHECKOUT] success', result);
                                window.location.href =
                                    "{{ route('user.user.midtrans.finish') }}";
                            },
                            onPending: function(result) {
                                console.log('[CHECKOUT] pending', result);
                                window.location.href =
                                    "{{ route('user.user.midtrans.unfinish') }}";
                            },
                            onError: function(result) {
                                console.log('[CHECKOUT] error', result);
                                window.location.href =
                                    "{{ route('user.user.midtrans.error') }}";
                            },
                            onClose: function() {
                                console.log('[CHECKOUT] snap ditutup user');
                                busy = false;
                                payBtn.disabled = false;
                            }
                        });
                    } else {
                        // fallback VTWeb (tetap mengarahkan ke halaman pembayaran)
                        console.log('[CHECKOUT] fallback VTWeb dengan token:', data.token);
                        window.location.href =
                            'https://app.sandbox.midtrans.com/snap/v2/vtweb/' + data.token;
                    }
                } catch (err) {
                    console.error('[CHECKOUT] exception', err);
                    alert('Terjadi kesalahan jaringan. Coba lagi.');
                } finally {
                    // aktifkan lagi tombol jika tidak sedang menunggu popup
                    if (typeof snap === 'undefined' || typeof snap.pay !== 'function') {
                        busy = false;
                        payBtn.disabled = false;
                    }
                }
            });

            // === Sinkronisasi radio method + update ringkasan (tanpa ubah tampilan) ===
            const radioStore = document.getElementById('radio-store');
            const radioJnt = document.getElementById('radio-jnt');
            const pickupInp = document.getElementById('pickup-method-input');

            const subNum = document.getElementById('subtotal-input');
            const shipNum = document.getElementById('shipping-input');
            const totalNum = document.getElementById('total-input');

            const shipText = document.getElementById('shipping-text');
            const totalText = document.getElementById('total-text');

            const fShip = document.getElementById('f_shipping');
            const fTot = document.getElementById('f_total');

            function formatRp(n) {
                return 'Rp ' + (Number(n) || 0).toLocaleString('id-ID');
            }

            function recalc(method) {
                const subtotal = Number(subNum.value || 0);
                const shipping = method === 'jnt' ? 32000 : 0;
                const total = subtotal + shipping;

                shipNum.value = shipping;
                totalNum.value = total;

                shipText.textContent = formatRp(shipping);
                totalText.textContent = formatRp(total);

                fShip.value = shipping;
                fTot.value = total;
            }

            if (radioStore) {
                radioStore.addEventListener('change', () => {
                    pickupInp.value = 'store';
                    recalc('store');
                });
            }
            if (radioJnt) {
                radioJnt.addEventListener('change', () => {
                    pickupInp.value = 'jnt';
                    recalc('jnt');
                });
            }


            // lakukan kalkulasi awal sesuai nilai tersembunyi saat halaman dibuka
            recalc(pickupInp?.value === 'jnt' ? 'jnt' : 'store');

            // Ukuran sudah dipilih dari halaman produk, tidak perlu handler lagi
        });
    })();
</script>
@endpush
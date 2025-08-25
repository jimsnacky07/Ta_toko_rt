@extends('user.layouts.app')

@section('title', $p['name'])

@section('content')
<div class="bg-white rounded-md p-4 md:p-6 shadow">
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    {{-- Gambar utama --}}
    <div class="border rounded-md p-3 flex items-center justify-center">
      <img src="{{ asset($p['image']) }}" alt="{{ $p['name'] }}" class="max-h-[420px] object-contain">
    </div>

    {{-- Info produk --}}
    <div>
      <h1 class="text-2xl font-semibold text-gray-800">{{ $p['name'] }}</h1>

      <div class="mt-2">
        <div class="text-xl font-bold text-[#D2B48C]">
          Rp {{ number_format($p['price'], 0, ',', '.') }}
        </div>

        @if(!empty($p['is_preorder']))
          <div class="mt-1 text-sm text-gray-600 flex items-center gap-2">
            <span class="inline-flex items-center px-2 py-0.5 rounded bg-amber-100 text-amber-700 text-xs font-medium">
              Pre-Order
            </span>
            <span>Perkiraan kirim 10 hari</span>
          </div>
        @endif
      </div>

      {{-- Opsi warna --}}
      @if(!empty($p['colors']))
      <div class="mt-5">
        <div class="text-sm text-gray-600 mb-1">Warna</div>
        <div class="flex flex-wrap gap-2">
          @foreach($p['colors'] as $c)
            <button type="button"
                    class="px-3 py-1.5 rounded border text-sm hover:bg-gray-50"
                    data-color="{{ $c }}">{{ $c }}</button>
          @endforeach
        </div>
      </div>
      @endif

      {{-- Opsi ukuran --}}
      @if(!empty($p['sizes']))
      <div class="mt-4">
        <div class="text-sm text-gray-600 mb-1">Ukuran</div>
        <div class="flex flex-wrap gap-2">
          @foreach($p['sizes'] as $s)
            <button type="button"
                    class="w-12 py-1.5 rounded border text-sm hover:bg-gray-50"
                    data-size="{{ $s }}">{{ $s }}</button>
          @endforeach
        </div>
      </div>
      @endif

      {{-- Jumlah --}}
      <div class="mt-4">
        <div class="text-sm text-gray-600 mb-1">Jumlah</div>
        <div class="inline-flex items-center border rounded">
          <button type="button" id="qtyMinus" class="px-3 py-2">-</button>
          <input id="qty" type="number" min="1" value="1"
                 class="w-16 text-center border-x outline-none py-2
                        appearance-none [appearance:textfield]
                        [&::-webkit-outer-spin-button]:appearance-none
                        [&::-webkit-inner-spin-button]:appearance-none"
                 onwheel="this.blur()">
          <button type="button" id="qtyPlus" class="px-3 py-2">+</button>
        </div>
      </div>

      {{-- Aksi: form Pesan Sekarang --}}
      <form id="checkoutForm" action="{{ route('user.checkout.store') }}" method="POST" class="mt-6">
        @csrf
        {{-- hidden dikirim ke checkout --}}
        <input type="hidden" name="product_name" value="{{ $p['name'] }}">
        <input type="hidden" name="price"        value="{{ $p['price'] }}">
        <input type="hidden" name="image"        value="{{ $p['image'] }}">
        <input type="hidden" name="color"        id="f_color">
        <input type="hidden" name="size"         id="f_size">
        <input type="hidden" name="qty"          id="f_qty" value="1">
        <input type="hidden" name="notes"        id="f_notes">

        <div class="flex flex-wrap gap-3">
          <button type="submit"
                  class="px-5 py-2.5 rounded-2xl bg-blue-600 text-white shadow hover:shadow-md">
            Pesan Sekarang
          </button>

          <button type="button"
                  class="inline-flex items-center gap-2 px-4 py-2 rounded-full border shadow-sm hover:shadow-md transition">
            <img src="{{ asset('images/keranjang.png') }}" alt="Keranjang" class="w-7 h-7">
            Tambah ke Keranjang
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Spesifikasi & Deskripsi --}}
<div class="mt-6 bg-white rounded-md shadow">
  <button class="w-full text-left px-4 py-3 font-medium border-b" onclick="toggleSpec()">
    Spesifikasi dan Deskripsi Produk
  </button>
  <div id="specBody" class="px-4 py-4 space-y-3">
    <div>
      <div class="font-semibold mb-1">Spesifikasi</div>
      <ul class="text-sm text-gray-700 list-disc pl-5">
        @foreach($p['spec'] as $k => $v)
          <li><span class="font-medium">{{ $k }}:</span> {{ $v }}</li>
        @endforeach
      </ul>
    </div>
    <div>
      <div class="font-semibold mb-1">Deskripsi</div>
      <p class="text-sm text-gray-700">{{ $p['desc'] }}</p>
    </div>
  </div>
</div>

@push('floating')
  @include('user.partials.whatsapp')
@endpush

{{-- JS kecil --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
  // qty +/-
  const qty     = document.getElementById('qty');
  const qtyPlus = document.getElementById('qtyPlus');
  const qtyMin  = document.getElementById('qtyMinus');
  qtyPlus.onclick = () => qty.value = (+qty.value || 1) + 1;
  qtyMin.onclick  = () => qty.value = Math.max(1, (+qty.value || 1) - 1);

  // pilih warna & ukuran -> simpan ke hidden
  const setChoice = (attr, hiddenId) => {
    const btns = document.querySelectorAll(`[${attr}]`);
    btns.forEach(btn => {
      btn.addEventListener('click', () => {
        btns.forEach(b => b.classList.remove('active','ring-2','ring-blue-500'));
        btn.classList.add('active','ring-2','ring-blue-500');
        document.getElementById(hiddenId).value = btn.getAttribute(attr);
      });
    });
  };
  setChoice('data-color','f_color');
  setChoice('data-size','f_size');

  // sebelum submit, sinkron qty ke hidden
  document.getElementById('checkoutForm').addEventListener('submit', () => {
    document.getElementById('f_qty').value = qty.value;
  });
});

function toggleSpec(){
  document.getElementById('specBody').classList.toggle('hidden');
}
</script>
@endsection

@extends('user.layouts.app')

@php
  // import helper Str untuk cek prefix URL
  use Illuminate\Support\Str;

  // --- Normalisasi object/array produk ---
  $pid    = $p->id     ?? ($p['id']     ?? null);
  $name   = $p->name   ?? $p->nama      ?? ($p['name']   ?? ($p['nama']   ?? 'Produk'));
  $harga  = (int)($p->harga ?? $p->price ?? ($p['harga'] ?? ($p['price'] ?? 0)));
  $gambar = $p->image  ?? $p->gambar     ?? ($p['image']  ?? ($p['gambar'] ?? null));

  // URL gambar yang robust:
  // - Jika sudah "http/https" dipakai apa adanya
  // - Jika relatif (contoh: images/..., storage/...), pakai asset() langsung (TIDAK dipaksa 'storage/')
  $imgUrl = $gambar
      ? (Str::startsWith($gambar, ['http://','https://'])
          ? $gambar
          : asset(ltrim($gambar, '/')))
      : null;

  // Properti opsional
  $isPre  = $p->is_preorder ?? ($p['is_preorder'] ?? false);
  $colors = (array)($p->colors ?? ($p['colors'] ?? []));
  $sizes  = (array)($p->sizes  ?? ($p['sizes']  ?? []));
  $spec   = (array)($p->spec   ?? ($p['spec']   ?? []));
  $desc   = $p->desc ?? ($p['desc'] ?? '');

  // Nama route (fleksibel: kalau kamu pakai prefix "user." atau tidak)
  $rtCart     = \Illuminate\Support\Facades\Route::has('keranjang.tambah')
                ? 'keranjang.tambah'
                : (\Illuminate\Support\Facades\Route::has('user.keranjang.tambah') ? 'user.keranjang.tambah' : null);
  $rtCheckout = \Illuminate\Support\Facades\Route::has('user.checkout.store')
                ? 'user.checkout.store'
                : (\Illuminate\Support\Facades\Route::has('checkout.store') ? 'checkout.store' : null);
@endphp

@section('title', $name)

@section('content')
<div class="bg-white rounded-md p-4 md:p-6 shadow">
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    {{-- Gambar utama --}}
    <div class="border rounded-md p-3 flex items-center justify-center">
      @if($imgUrl)
        <img src="{{ $imgUrl }}" alt="{{ $name }}" class="max-h-[420px] object-contain">
      @else
        <div class="w-full h-[420px] flex items-center justify-center text-gray-400">Tidak ada gambar</div>
      @endif
    </div>

    {{-- Info produk --}}
    <div x-data="{ qty: 1 }">
      <h1 class="text-2xl font-semibold text-gray-800">{{ $name }}</h1>

      <div class="mt-2">
        <div class="text-xl font-bold text-[#D2B48C]">
          Rp {{ number_format($harga, 0, ',', '.') }}
        </div>

        @if(!empty($isPre))
          <div class="mt-1 text-sm text-gray-600 flex items-center gap-2">
            <span class="inline-flex items-center px-2 py-0.5 rounded bg-amber-100 text-amber-700 text-xs font-medium">
              Pre-Order
            </span>
            <span>Perkiraan kirim 10 hari</span>
          </div>
        @endif
      </div>

      {{-- Opsi warna --}}
      @if(!empty($colors))
        <div class="mt-5">
          <div class="text-sm text-gray-600 mb-1">Warna</div>
          <div class="flex flex-wrap gap-2" id="colorList">
            @foreach($colors as $c)
              <button type="button"
                      class="px-3 py-1.5 rounded border text-sm hover:bg-gray-50"
                      data-color="{{ $c }}">{{ $c }}</button>
            @endforeach
          </div>
        </div>
      @endif

      {{-- Opsi ukuran --}}
      @if(!empty($sizes))
        <div class="mt-4">
          <div class="text-sm text-gray-600 mb-1">Ukuran</div>
          <div class="flex flex-wrap gap-2" id="sizeList">
            @foreach($sizes as $s)
              <button type="button"
                      class="w-12 py-1.5 rounded border text-sm hover:bg-gray-50"
                      data-size="{{ $s }}">{{ $s }}</button>
            @endforeach
          </div>
        </div>
      @endif

      {{-- Jumlah (pakai Alpine untuk ±) --}}
      <div class="mt-4">
        <div class="text-sm text-gray-600 mb-1">Jumlah</div>
        <div class="inline-flex items-center border rounded">
          <button type="button" @click="qty = Math.max(1, (parseInt(qty)||1) - 1)" class="px-3 py-2">−</button>
          <input type="number"
                 min="1"
                 x-model.number="qty"
                 class="w-16 text-center border-x outline-none py-2
                        appearance-none [appearance:textfield]
                        [&::-webkit-outer-spin-button]:appearance-none
                        [&::-webkit-inner-spin-button]:appearance-none"
                 onwheel="this.blur()">
          <button type="button" @click="qty = (parseInt(qty)||1) + 1" class="px-3 py-2">+</button>
        </div>
      </div>

      {{-- Aksi --}}
      <div class="mt-6 flex flex-wrap gap-3">
        {{-- PESAN SEKARANG -> Checkout (opsional) --}}
        @if($rtCheckout)
          <form method="POST" action="{{ route($rtCheckout) }}">
            @csrf
            <input type="hidden" name="product_id"   value="{{ $pid }}">
            <input type="hidden" name="product_name" value="{{ $name }}">
            <input type="hidden" name="nama"         value="{{ $name }}">
            <input type="hidden" name="harga"        value="{{ $harga }}">
            <input type="hidden" name="gambar"       value="{{ $gambar }}">
            <input type="hidden" name="image"        value="{{ $imgUrl }}">
            <input type="hidden" name="color"        id="f_color_checkout">
            <input type="hidden" name="size"         id="f_size_checkout">
            <input type="hidden" name="qty"          id="f_qty_checkout" x-bind:value="qty">
            <button type="submit" class="px-5 py-2.5 rounded-2xl bg-blue-600 text-white shadow hover:shadow-md">
              Pesan Sekarang
            </button>
          </form>
        @endif

        {{-- TAMBAH KE KERANJANG --}}
        @if($rtCart)
          <form method="POST" action="{{ route('keranjang.store') }}">
            @csrf
            <input type="hidden" name="id"     value="{{ $pid }}">
            <input type="hidden" name="nama"   value="{{ $name }}">
            <input type="hidden" name="harga"  value="{{ $harga }}">
            <input type="hidden" name="gambar" value="{{ $imgUrl ?? '' }}">
            <input type="hidden" name="qty"    id="f_qty_cart" x-bind:value="qty">
            <input type="hidden" name="color"  id="f_color_cart">
            <input type="hidden" name="size"   id="f_size_cart">
            <button type="submit"
                    formaction="{{ route('keranjang.tambah') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-full border shadow-sm hover:shadow-md transition">
              <img src="{{ asset('images/keranjang.png') }}" alt="Keranjang" class="w-7 h-7">
              Tambah ke Keranjang
            </button>
          </form>
        @else
          <div class="text-sm text-red-600">Route keranjang.tambah belum ada.</div>
        @endif
      </div>

    </div>
  </div>
</div>

{{-- Spesifikasi & Deskripsi --}}
<div class="mt-6 bg-white rounded-md shadow">
  <button class="w-full text-left px-4 py-3 font-medium border-b" onclick="toggleSpec()">
    Spesifikasi dan Deskripsi Produk
  </button>
  <div id="specBody" class="px-4 py-4 space-y-3">
    @if(!empty($spec))
      <div>
        <div class="font-semibold mb-1">Spesifikasi</div>
        <ul class="text-sm text-gray-700 list-disc pl-5">
          @foreach($spec as $k => $v)
            <li><span class="font-medium">{{ $k }}:</span> {{ $v }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    @if(!empty($desc))
      <div>
        <div class="font-semibold mb-1">Deskripsi</div>
        <p class="text-sm text-gray-700">{{ $desc }}</p>
      </div>
    @endif
  </div>
</div>

@push('floating')
  @include('user.partials.whatsapp')
@endpush

{{-- (Opsional) Alpine.js untuk x-data qty jika belum dimuat di layout --}}
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

{{-- JS kecil untuk memilih color/size & toggle spesifikasi --}}
<script>
function toggleSpec(){
  document.getElementById('specBody').classList.toggle('hidden');
}

document.addEventListener('DOMContentLoaded', () => {
  const wireChoice = (selector, hiddenIds) => {
    const wrap = document.querySelector(selector);
    if (!wrap) return;
    const btns = wrap.querySelectorAll('button[data-color],button[data-size]');
    btns.forEach(btn => {
      btn.addEventListener('click', () => {
        const isColor = btn.hasAttribute('data-color');
        const attr = isColor ? 'data-color' : 'data-size';
        // reset style
        btns.forEach(b => b.classList.remove('ring-2','ring-blue-500'));
        // set style active
        btn.classList.add('ring-2','ring-blue-500');
        // isi semua hidden target
        hiddenIds.forEach(id => {
          const el = document.getElementById(id);
          if (el) el.value = btn.getAttribute(attr);
        });
      });
    });
  };

  // sinkron color
  wireChoice('#colorList', ['f_color_cart','f_color_checkout']);
  // sinkron size
  wireChoice('#sizeList',  ['f_size_cart','f_size_checkout']);
});
</script>
@endsection

@extends('user.layouts.app')

@section('title', 'Dashboard')

@section('content')
  <div class="relative h-[400px] rounded-md shadow-md mb-6 overflow-hidden">
    <img src="{{ asset('images/gambar1.jpg') }}" alt="Banner" class="w-full h-full object-cover">
    <div class="absolute inset-0 bg-black/40 flex flex-col items-center justify-center">
      <h1 class="text-white text-3xl md:text-4xl font-bold">Selamat Datang di Aplikasi Pemesanan Jahit</h1>
      <p class="text-white mt-2 text-lg">Pilih style sendiri, kami jahitkan dengan rapi</p>
    </div>
  </div>

  <div class="mb-8">
    <h2 class="text-xl font-semibold text-gray-800 mb-3">Produk Pilihan</h2>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
      @forelse ($products as $product)
        <a href="{{ route('user.product.show', $product->id) }}"
           class="block bg-white rounded-lg shadow hover:shadow-md transition overflow-hidden">
          <div class="p-2 bg-gray-50 flex items-center justify-center">
            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-64 object-contain">
          </div>
          <div class="p-3">
            <h3 class="text-gray-800 font-semibold text-base mb-1 line-clamp-1">{{ $product->name }}</h3>
            <p class="text-[#D2B48C] font-bold text-sm">Rp {{ number_format((int)$product->price, 0, ',', '.') }}</p>
          </div>
        </a>
      @empty
        <div class="col-span-full text-gray-500">Belum ada produk.</div>
      @endforelse
    </div>
  </div>
@endsection

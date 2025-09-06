@extends('admin.layouts.app')
@section('title','Galeri Jahitan')

@section('content')
@php use Illuminate\Support\Str; @endphp
<div class="max-w-7xl mx-auto">
  <!-- Banner Header -->
  <div class="relative h-[300px] rounded-md shadow-md mb-6 overflow-hidden">
    <img src="{{ asset('images/gambar1.jpg') }}" alt="Banner" class="w-full h-full object-cover">
    <div class="absolute inset-0 bg-black/40 flex flex-col items-center justify-center">
      <h1 class="text-white text-3xl md:text-4xl font-bold">Galeri Jahitan</h1>
      <p class="text-white mt-2 text-lg">Kelola produk jahitan dengan mudah</p>
    </div>
  </div>

  <!-- Search & Filter -->
  <div class="bg-white rounded-lg shadow p-4 mb-6">
    <div class="flex flex-col md:flex-row gap-4">
      <div class="flex-1">
        <form method="GET" action="{{ route('admin.galeri.jahit.index') }}">
          <input type="text"
            name="q"
            value="{{ $q }}"
            placeholder="Cari produk..."
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            oninput="this.form.requestSubmit()"
            autocomplete="off">
        </form>
      </div>
      <div class="flex gap-2">
        <a href="{{ route('admin.galeri.jahit.create') }}"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
          + Tambah Produk
        </a>
      </div>
    </div>
  </div>

  <!-- Products Grid -->
  <div class="mb-8">
    <h2 class="text-xl font-semibold text-gray-800 mb-3">Semua Produk</h2>

    @if($products->count())
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
      @foreach ($products as $product)
      <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow duration-300 overflow-hidden">
        <a href="{{ route('admin.galeri.jahit.show', $product) }}" class="block">
          <!-- Product Image -->
          <div class="aspect-square bg-gray-50 flex items-center justify-center p-4">
            @php
            $img = $product->image
            ? (Str::startsWith($product->image, ['http://','https://']) ? $product->image
            : asset(Str::startsWith($product->image, 'images/') ? $product->image : 'images/'.$product->image))
            : null;
            @endphp
            @if($img)
            <img src="{{ $img }}" alt="{{ $product->name }}"
              class="w-full h-full object-contain hover:scale-105 transition-transform duration-300">
            @else
            <div class="text-gray-400 text-center">
              <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <p class="text-sm mt-2">No Image</p>
            </div>
            @endif
          </div>

          <!-- Product Info -->
          <div class="p-4">
            <h3 class="font-semibold text-gray-800 mb-2 line-clamp-2 min-h-[3rem]">
              {{ $product->name ?? '—' }}
            </h3>

            <!-- Category -->
            @if($product->kategory)
            <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full mb-2">
              {{ $product->kategory }}
            </span>
            @endif

            <!-- Price -->
            <div class="flex items-center justify-between mb-3">
              <span class="text-lg font-bold text-[#D2B48C]">
                Rp {{ number_format($product->price ?? 0, 0, ',', '.') }}
              </span>

              <!-- Preorder Badge -->
              @if($product->is_preorder)
              <span class="bg-orange-100 text-orange-800 text-xs px-2 py-1 rounded-full">
                Pre-order
              </span>
              @endif
            </div>

            <!-- Available Sizes -->
            @if($product->sizes)
            <div class="mb-2 flex flex-wrap gap-1">
              @foreach(explode(',', $product->sizes) as $size)
              <span class="bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded">
                {{ trim($size) }}
              </span>
              @endforeach
            </div>
            @endif

            <!-- Material Info -->
            @if($product->bahan)
            <p class="text-sm text-gray-600 mb-3">
              <span class="font-medium">Bahan:</span> {{ $product->bahan }}
            </p>
            @endif
          </div>
        </a>

        <!-- Admin Actions -->
        <div class="p-4 border-t bg-gray-50">
          <div class="flex items-center gap-2">
            <a href="{{ route('admin.galeri.jahit.edit', $product) }}"
              class="flex-1 inline-flex items-center justify-center px-3 py-2 text-sm rounded-md border border-gray-300 hover:bg-gray-50 transition-colors">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              Edit
            </a>

            <form action="{{ route('admin.galeri.jahit.destroy', $product) }}"
              method="POST"
              onsubmit="return confirm('Hapus item ini?')"
              class="flex-1">
              @csrf
              @method('DELETE')
              <button type="submit"
                class="w-full inline-flex items-center justify-center px-3 py-2 text-sm rounded-md border border-red-300 text-red-600 hover:bg-red-50 transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Hapus
              </button>
            </form>
          </div>
        </div>
      </div>
      @endforeach
    </div>

    <!-- Pagination -->
    <div class="mt-6">
      {{ $products->withQueryString()->links() }}
    </div>
    @else
    <!-- Empty state -->
    <div class="col-span-full text-center py-12">
      <div class="text-gray-400 mb-4">
        <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2 2v-5m16 0h-2M4 13h2m13-8V4a1 1 0 00-1-1H7a1 1 0 00-1 1v1m8 0V4.5" />
        </svg>
      </div>
      <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada produk</h3>
      <p class="text-gray-600 mb-4">Mulai dengan menambahkan produk pertama Anda.</p>
      <a href="{{ route('admin.galeri.jahit.create') }}"
        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Tambah Produk
      </a>
    </div>
    @endif
  </div>
</div>
@endsection
@extends('admin.layouts.admin')
@section('title','Galeri Jahitan')

@section('content')
@php use Illuminate\Support\Str; @endphp
<section class="mx-auto max-w-7xl">
  {{-- Header: Title + Actions --}}
  <div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-semibold">Galeri Jahitan</h2>

    <a href="{{ route('admin.galeri.jahit.create') }}"
       class="inline-flex items-center rounded-lg px-4 py-2 text-sm font-medium border hover:bg-gray-50">
      Create Pakaian
    </a>
  </div>

  {{-- Toolbar: Search --}}
  <form method="GET" action="{{ route('admin.galeri.jahit.index') }}" class="mb-6">
    <div class="relative w-full sm:w-80">
      <span class="absolute left-3 top-2.5">🔎</span>
      <input
        type="text"
        name="q"
        value="{{ $q }}"
        placeholder="Search..."
        class="w-full rounded-full border px-9 py-2 outline-none focus:ring"
        oninput="this.form.requestSubmit()"  {{-- auto submit saat ketik --}}
        autocomplete="off"
      />
    </div>
  </form>

  {{-- Grid kartu produk (HANYA SATU BLOK INI SAJA) --}}
  @if($products->count())
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      @foreach($products as $p)
        {{-- kartu produkmu di sini --}}
      @endforeach
    </div>

    {{ $products->links() }}
  @elseif(!empty($q))
    <div class="text-gray-500 rounded-lg border bg-white p-6">
      Pencarian tidak ada.
    </div>
  @endif

  {{-- Grid cards --}}
@if($products->count())
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
    @foreach ($products as $product)
      <div class="rounded-xl border bg-white shadow-sm overflow-hidden">
        {{-- Image (klik → halaman detail) --}}
        <a href="{{ route('admin.galeri.jahit.show', $product) }}" class="block">
          <div class="h-48 w-full bg-gray-100 flex items-center justify-center">
            @php
              $img = $product->image
                ? (Str::startsWith($product->image, ['http://','https://']) ? $product->image
                   : asset('storage/'.$product->image))
                : null;
            @endphp
            @if($img)
              <img src="{{ $img }}" alt="{{ $product->name }}" 
                   class="max-h-full max-w-full object-contain">
            @else
              <span class="text-gray-400">Image</span>
            @endif
          </div>
        </a>

        {{-- Body --}}
        <div class="p-4">
          {{-- Nama (klik → halaman detail) --}}
          <h3 class="text-base font-semibold mb-1 truncate">
            <a href="{{ route('admin.galeri.jahit.show', $product) }}" class="hover:underline">
              {{ $product->name ?? '—' }}
            </a>
          </h3>

          @if(!empty($product->description))
            <p class="text-sm text-gray-600 line-clamp-2">
              {{ Str::limit(strip_tags($product->description), 110) }}
            </p>
          @else
          @endif

          <div class="mt-3 font-semibold">Rp {{ number_format($product->price ?? 0, 0, ',', '.') }}</div>

          {{-- Actions --}}
          <div class="mt-4 flex items-center gap-3">
            <a href="{{ route('admin.galeri.jahit.edit', $product) }}"
              class="inline-flex items-center px-3 py-1.5 text-sm rounded-md border hover:bg-gray-50">
              <img src="{{ asset('images/edit.png') }}" alt="Edit" class="w-4 h-4 mr-1">
              Edit
            </a>

            <form action="{{ route('admin.galeri.jahit.destroy', $product) }}"
                  method="POST"
                  onsubmit="return confirm('Hapus item ini?')">
              @csrf
              @method('DELETE')
              <button type="submit"
                  class="inline-flex items-center px-3 py-1.5 text-sm rounded-md border text-red-600 hover:bg-red-50">
                <img src="{{ asset('images/sampah.png') }}" alt="Hapus" class="w-4 h-4 mr-1">
                Hapus
              </button>
            </form>
          </div>
        </div>
      </div>
    @endforeach
  </div>

  {{-- Pagination --}}
  <div class="mt-6">
    {{ $products->withQueryString()->links() }}
  </div>
@else
  {{-- Empty state --}}
  <div class="rounded-xl border bg-white p-10 text-center text-gray-600">
    Belum ada data. <a href="{{ route('admin.galeri.jahit.create') }}" class="underline">Tambah sekarang</a> .
  </div>
@endif
</section>
@endsection

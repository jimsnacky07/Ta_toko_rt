@extends('admin.layouts.admin')
@section('title','Edit Pakaian')

@section('content')
@php
  use Illuminate\Support\Str;
  $img = $item->image
      ? (Str::startsWith($item->image, ['http://','https://']) ? $item->image
         : asset('storage/'.$item->image))
      : null;
@endphp

<style>
  /* hilangkan panah number */
  input[type=number]::-webkit-outer-spin-button,
  input[type=number]::-webkit-inner-spin-button { -webkit-appearance:none; margin:0; }
  input[type=number] { -moz-appearance:textfield; appearance:textfield; }

  /* input/textarea abu-abu muda */
  input, textarea, select {
    background-color:#f3f4f6; border:1px solid #d1d5db; border-radius:.5rem;
    padding:.5rem .75rem; width:100%; outline:none;
  }
  input:focus, textarea:focus, select:focus { border-color:#2563eb; background-color:#f3f4f6; }
</style>

<section class="mx-auto max-w-4xl">
  <h2 class="text-2xl font-semibold mb-6 text-center">Edit Pakaian</h2>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    {{-- Preview --}}
    <div class="rounded-xl border bg-white shadow-sm overflow-hidden">
      <div class="w-full aspect-[4/3] bg-gray-100 flex items-center justify-center">
        @if($img)
          <img src="{{ $img }}" alt="{{ $item->name }}" class="h-full w-full object-contain">
        @else
          <span class="text-gray-400">Image</span>
        @endif
      </div>
      @if($img)
        <div class="p-3 text-center text-sm text-gray-600 truncate">{{ $item->name }}</div>
      @endif
    </div>

    {{-- Form --}}
    <form action="{{ route('admin.galeri.jahit.update', $item) }}" method="POST" enctype="multipart/form-data"
          class="card p-6 space-y-4 border rounded-xl bg-white shadow-sm">
      @csrf
      @method('PUT')

      {{-- Nama --}}
      <div>
        <label class="block text-sm font-medium mb-1">Nama</label>
        <input name="nama" required value="{{ old('nama', $item->name) }}">
        @error('nama')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
      </div>

      {{-- Harga (prefix Rp) --}}
      <div>
        <label class="block text-sm font-medium mb-1">Harga</label>
        <div class="relative">
          <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-600 select-none">Rp</span>
          <input name="harga" type="number" min="0" step="1" class="pl-10"
                 required value="{{ old('harga', $item->price) }}">
        </div>
        <p class="text-xs text-gray-500 mt-1">Tulis angka saja, tanpa titik/koma. Contoh: <span class="font-medium">150000</span></p>
        @error('harga')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
      </div>

      {{-- Spesifikasi Produk --}}
      <div class="mt-4">
        <div class="text-sm font-semibold mb-2">Spesifikasi Produk</div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="block text-sm text-gray-700 mb-1">Kategori</label>
            <input name="kategori" value="{{ old('kategori', $item->kategori ?? '') }}" placeholder="Contoh: Gaun, Kemeja">
          </div>
          <div>
            <label class="block text-sm text-gray-700 mb-1">Bahan</label>
            <input name="bahan" value="{{ old('bahan', $item->bahan ?? '') }}" placeholder="Contoh: Katun, Linen">
          </div>
          <div>
            <label class="block text-sm text-gray-700 mb-1">Motif</label>
            <input name="motif" value="{{ old('motif', $item->motif ?? '') }}" placeholder="Contoh: Polos, Bunga">
          </div>
          <div>
            <label class="block text-sm text-gray-700 mb-1">Dikirim Dari</label>
            <input name="dikirim_dari" value="{{ old('dikirim_dari', $item->dikirim_dari ?? '') }}" placeholder="Contoh: Bandung">
          </div>
        </div>
      </div>

      {{-- Ukuran / Deskripsi --}}
      <div>
        <label class="block text-sm font-medium mb-1">Deskripsi Ukuran</label>
        <textarea name="deskripsi" rows="5"
                  placeholder="Tuliskan ukuran/size chart dan detail penting lainnya (panjang, lingkar dada, panduan perawatan, dll).">{{ old('deskripsi', $item->deskripsi) }}</textarea>
        <p class="text-xs text-gray-500 mt-1">Contoh: Size S–XXL (lihat tabel), lingkar dada 86–102 cm, panjang 110 cm, cuci gentle.</p>
        @error('deskripsi')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
      </div>

      {{-- Gambar --}}
      <div>
        <label class="block text-sm font-medium mb-1">Gambar (opsional, untuk ganti)</label>
        <input type="file" name="gambar" accept="image/*" style="background-color:#fff">
        @error('gambar')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
      </div>

      <div class="flex items-center gap-3">
        <a href="{{ route('admin.galeri.jahit.index') }}" class="px-4 py-2 rounded-lg bg-gray-200">Batal</a>
        <button 
    type="submit" 
    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-1"
    >
        Update
    </button>
      </div>
    </form>
  </div>
</section>
@endsection

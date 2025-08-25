@extends('admin.layouts.admin')
@section('title','Create Pakaian')

@section('content')
<div class="mx-auto max-w-2xl">
  <h2 class="text-2xl font-semibold mb-6">Create Pakaian</h2>

  {{-- Notifikasi error umum (opsional) --}}
  @if ($errors->any())
    <div class="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-700">
      <strong>Terjadi kesalahan:</strong>
      <ul class="list-disc pl-5">
        @foreach ($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form action="{{ route('admin.galeri.jahit.store') }}" method="POST" enctype="multipart/form-data" class="card p-6 space-y-4">
    @csrf

    <div>
      <label class="block text-sm font-medium mb-1" for="nama">Nama</label>
      <input id="nama" name="nama" class="input w-full" required value="{{ old('nama') }}">
      @error('nama')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="relative mb-4">
    <label for="harga" class="block text-sm font-medium text-gray-700">Harga</label>
    <div class="relative mt-1 flex items-center">
        <!-- Simbol Rp -->
        <span class="absolute left-3 text-gray-600">Rp</span>
        
        <!-- Input Harga -->
        <input
            type="text"
            name="harga"
            id="harga"
            value="{{ old('harga') }}"
            class="block w-full pl-10 pr-4 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm appearance-none -moz-appearance-none"
            placeholder="Masukkan harga"
        />
    </div>
</div>

    <div>
      <label class="block text-sm font-medium mb-1" for="deskripsi">Deskripsi</label>
      <textarea id="deskripsi" name="deskripsi" rows="4" class="input w-full">{{ old('deskripsi') }}</textarea>
      @error('deskripsi')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
    </div>


    <!-- Spesifikasi Produk -->
    <div>
      <h3 class="text-lg font-semibold mt-6">Spesifikasi Produk</h3>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1" for="kategori">Kategori</label>
          <input id="kategori" name="kategori" class="input w-full" value="{{ old('kategori') }}">
        </div>

        <div>
          <label class="block text-sm font-medium mb-1" for="bahan">Bahan</label>
          <input id="bahan" name="bahan" class="input w-full" value="{{ old('bahan') }}">
        </div>

        <div>
          <label class="block text-sm font-medium mb-1" for="motif">Motif</label>
          <input id="motif" name="motif" class="input w-full" value="{{ old('motif') }}">
        </div>

        <div>
          <label class="block text-sm font-medium mb-1" for="dikirim_dari">Dikirim Dari</label>
          <input id="dikirim_dari" name="dikirim_dari" class="input w-full" value="{{ old('dikirim_dari') }}">
        </div>
      </div>
    </div>

    <div>
      <label class="block text-sm font-medium mb-1" for="gambar">Gambar</label>
      <input id="gambar" type="file" name="gambar" accept="image/*" class="block">
      @error('gambar')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="flex items-center gap-3">
      <a href="{{ route('admin.galeri.jahit.index') }}" class="px-4 py-2 rounded-lg bg-gray-200">Batal</a>
      <button type="submit" class="px-4 py-2 rounded-lg bg-blue-500 text-white hover:bg-blue-600">Simpan</button>
    </div>
  </form>
</div>
@endsection

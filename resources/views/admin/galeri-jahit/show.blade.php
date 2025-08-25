@extends('admin.layouts.admin')
@section('title','Detail Pakaian')

@section('content')
@php
  use Illuminate\Support\Str;
  $img = $item->image
    ? (Str::startsWith($item->image, ['http://','https://']) ? $item->image
       : asset('storage/'.$item->image))
    : null;
@endphp

<section class="mx-auto max-w-5xl">
  <h2 class="text-2xl font-semibold mb-6">Spesifikasi dan Deskripsi Produk</h2>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Gambar besar --}}
    <div class="rounded-xl border bg-white shadow-sm p-4 flex items-center justify-center">
      @if($img)
        <img src="{{ $img }}" alt="{{ $item->name }}" class="max-h-[480px] w-full object-contain">
      @else
        <div class="h-[320px] w-full bg-gray-100 flex items-center justify-center text-gray-400">Image</div>
      @endif
    </div>

    {{-- Ringkasan --}}
    <div class="rounded-xl border bg-white shadow-sm p-5 space-y-4">
      <h3 class="text-xl font-semibold">{{ $item->name }}</h3>
      <div class="text-lg font-bold">Rp {{ number_format($item->price ?? 0, 0, ',', '.') }}</div>

      <div class="flex gap-3">
        <a href="{{ route('admin.galeri.jahit.edit', $item) }}" class="px-4 py-2 rounded border hover:bg-gray-50">Edit</a>
        <a href="{{ route('admin.galeri.jahit.index') }}" class="px-4 py-2 rounded bg-gray-100">Kembali</a>
      </div>
    </div>
  </div>

  {{-- Spesifikasi Produk (opsional: tampil jika ada field-nya) --}}
  <div class="mt-8 rounded-xl border bg-white shadow-sm">
    <div class="border-b px-5 py-3 font-semibold">Spesifikasi Produk</div>
    <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-3">
      @if(!empty($item->kategory))
        <div class="flex justify-between"><span class="text-gray-600">Kategori</span><span>{{ $item->kategory }}</span></div>
      @endif
      @if(!empty($item->bahan))
        <div class="flex justify-between"><span class="text-gray-600">Bahan</span><span>{{ $item->bahan }}</span></div>
      @endif
      @if(!empty($item->motif))
        <div class="flex justify-between"><span class="text-gray-600">Motif</span><span>{{ $item->motif }}</span></div>
      @endif
      @if(!empty($item->dikirim_dari))
        <div class="flex justify-between"><span class="text-gray-600">Dikirim Dari</span><span>{{ $item->dikirim_dari }}</span></div>
      @endif
      @if(empty($item->kategory) && empty($item->bahan) && empty($item->motif) && empty($item->dikirim_dari))
        <div class="text-gray-500">Belum ada spesifikasi.</div>
      @endif
    </div>
  </div>

  {{-- Deskripsi Produk --}}
  <div class="mt-6 rounded-xl border bg-white shadow-sm">
    <div class="border-b px-5 py-3 font-semibold">Deskripsi Produk</div>
    <div class="p-5 prose max-w-none">
      {!! nl2br(e($item->deskripsi ?? '')) ?: '<span class="text-gray-500">Belum ada deskripsi.</span>' !!}
    </div>
  </div>

  {{-- (Opsional) Size Chart --}}
  @if(!empty($item->size_chart))
    <div class="mt-6 rounded-xl border bg-white shadow-sm">
      <div class="border-b px-5 py-3 font-semibold">Size Chart</div>
      <div class="p-5">{!! nl2br(e($item->size_chart)) !!}</div>
    </div>
  @endif
</section>
@endsection

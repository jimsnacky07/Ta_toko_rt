@extends('user.layouts.app')

@section('title', 'Pesanan Berhasil')

@section('content')
<div class="text-center mt-10">
    <h1 class="text-2xl font-bold text-green-600">Pesanan Berhasil!</h1>
    <p class="mt-2">Terima kasih, pesanan kamu sudah berhasil dibuat 🎉</p>

   <a href="{{ route('pusat.pesanan') }}">Lihat Pesanan Saya</a>
</div>
@endsection

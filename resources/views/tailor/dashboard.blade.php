@extends('tailor.layouts.app')

@section('title', 'Dashboard Tailor')

@section('content')
    <div class="max-w-7xl mx-auto">
        <!-- Background Image with Blurred Effect -->
        <div class="relative w-full h-96 bg-cover bg-center" style="background-image: url('{{ asset('images/gambar1.jpg') }}');">
            <div class="absolute inset-0 bg-black bg-opacity-50"></div>
            <div class="absolute inset-0 flex items-center justify-center">
                <h1 class="text-white text-4xl font-semibold">HAII SELAMAT DATANG DI TAILOR</h1>
            </div>
        </div>

        <!-- Link ke Riwayat Pesanan -->
        <div class="text-center mt-6">
            <a href="{{ route('tailor.riwayat.pesanan') }}" class="bg-[#A38C6C] text-white py-2 px-6 rounded-lg hover:bg-[#EAE0C8] transition">
                Lihat Riwayat Pesanan
            </a>
        </div>
    </div>
@endsection

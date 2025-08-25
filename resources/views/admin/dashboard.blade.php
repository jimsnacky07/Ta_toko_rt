@extends('admin.layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
    <div class="max-w-7xl mx-auto">
        <h1 class="text-2xl font-semibold text-gray-800 mb-6 text-center">
            Ringkasan Pesanan Bulan Ini
        </h1>

        <!-- Ringkasan Pesanan Bulan Ini -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <!-- Jumlah Pesanan -->
            <div class="bg-white rounded-xl shadow p-5">
                <div class="text-sm text-gray-500">Jumlah Pesanan (Bulan Ini)</div>
                <div class="mt-2 text-3xl font-bold">{{ $ordersThisMonth }}</div>
            </div>

            <!-- Jumlah Pelanggan -->
            <div class="bg-white rounded-xl shadow p-5">
                <div class="text-sm text-gray-500">Jumlah Pelanggan</div>
                <div class="mt-2 text-3xl font-bold">{{ $customers }}</div>
            </div>

            <!-- Pesanan Proses -->
            <div class="bg-white rounded-xl shadow p-5">
                <div class="text-sm text-gray-500">Pesanan Proses</div>
                <div class="mt-2 text-3xl font-bold">{{ $inProcess }}</div>
            </div>
        </div>

        <!-- Status Pesanan -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-6 mb-8">
            <!-- Pesanan Selesai Dikerjakan -->
            <div class="bg-white rounded-xl shadow p-5">
                <div class="text-sm text-gray-500">Pesanan Selesai Dikerjakan</div>
                <div class="mt-2 text-3xl font-bold">{{ $completed }}</div>
            </div>

            <!-- Pesanan Menunggu -->
            <div class="bg-white rounded-xl shadow p-5">
                <div class="text-sm text-gray-500">Pesanan Menunggu</div>
                <div class="mt-2 text-3xl font-bold">{{ $pending }}</div>
            </div>
        </div>
    </div>
@endsection

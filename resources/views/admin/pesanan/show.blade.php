@extends('layouts.admin')

@section('content')
<div class="px-6 py-4">
    <a href="{{ route('admin.pesanan.index') }}" class="btn mb-3">← Kembali</a>

    <div class="card">
        <div class="card-header">
            <h3>Detail Pesanan #{{ $pesanan->id }}</h3>
        </div>
        <div class="card-body">
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <dt class="font-semibold">Nama Pemesan</dt>
                    <dd>{{ $pesanan->user?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="font-semibold">Tanggal/Hari</dt>
                    <dd>{{ $pesanan->order_date->translatedFormat('l, d-m-Y') }}</dd>
                </div>
                <div>
                    <dt class="font-semibold">Status</dt>
                    <dd>{{ ucfirst(str_replace('-', ' ', $pesanan->status)) }}</dd>
                </div>
                <div>
                    <dt class="font-semibold">Total Harga</dt>
                    <dd>Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</dd>
                </div>
            </dl>
        </div>
    </div>
</div>
@endsection

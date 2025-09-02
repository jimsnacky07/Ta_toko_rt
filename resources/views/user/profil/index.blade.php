@extends('user.layouts.app')

@section('content')
<div class="flex justify-center mt-4">
    <div class="w-full max-w-md p-6 bg-white rounded-lg shadow-md">
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                {{ session('success') }}
            </div>
        @endif
        <div class="flex items-center mb-6">
            <div class="bg-red-600 text-white rounded-full w-12 h-12 flex items-center justify-center text-lg font-semibold">
                {{ strtoupper(substr($user->nama, 0, 1)) }}
            </div>
            <div class="ml-4">
                <h2 class="text-xl font-semibold">{{ $user->nama }}</h2>
                <p class="text-gray-500 capitalize">{{ $user->level }}</p>
            </div>
        </div>

        <div class="space-y-3 text-sm">
            <div>
                <p class="text-gray-500">Email</p>
                <p>{{ $user->email }}</p>
            </div>
            <div>
                <p class="text-gray-500">No. Telepon</p>
                <p>{{ $user->no_telp }}</p>
            </div>
            <div>
                <p class="text-gray-500">Alamat</p>
                <p>{{ $user->alamat ?? '-' }}</p>
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <a href="{{ route('profil.edit') }}"
               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700">
                Ubah Profil
            </a>
        </div>
    </div>
</div>
@endsection

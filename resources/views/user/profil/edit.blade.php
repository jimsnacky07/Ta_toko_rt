@extends('user.layouts.app')

@section('content')
<div class="flex justify-center mt-4">
    <div class="w-full max-w-md p-6 bg-white rounded-lg shadow-md">
        <h2 class="text-xl font-semibold mb-4">Ubah Profil</h2>

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('profil.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="block text-gray-700 text-sm mb-1">Nama</label>
                <input type="text" name="nama" value="{{ old('nama', $user->nama) }}" required
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-300">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-300">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm mb-1">No. Telepon</label>
                <input type="text" name="no_telp" value="{{ old('no_telp', $user->no_telp) }}" required
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-300"
                    placeholder="Contoh: 081234567890">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm mb-1">Alamat</label>
                <textarea name="alamat" rows="3" required
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-300"
                    placeholder="Masukkan alamat lengkap Anda">{{ old('alamat', $user->alamat) }}</textarea>
            </div>

            <div class="flex justify-between mt-6">
                <a href="{{ route('profil.index') }}"
                   class="px-4 py-2 bg-gray-500 text-white text-sm font-medium rounded hover:bg-gray-600">
                    Batal
                </a>
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

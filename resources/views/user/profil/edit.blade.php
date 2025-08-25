@extends('user.layouts.app')

@section('content')
<div class="flex justify-center mt-4">
    <div class="w-full max-w-md p-6 bg-white rounded-lg shadow-md">
        <h2 class="text-xl font-semibold mb-4">Edit Profil</h2>

        <form action="{{ route('profil.update', $user->id) }}" method="POST">
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
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-300">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm mb-1">Alamat</label>
                <textarea name="alamat" rows="3" required
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-300">{{ old('alamat', $user->alamat) }}</textarea>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm mb-1">Level</label>
                <input type="text" name="level" value="{{ old('level', $user->level) }}" readonly
                    class="w-full px-4 py-2 border bg-gray-100 rounded-lg cursor-not-allowed">
            </div>

            <div class="flex justify-end mt-6">
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

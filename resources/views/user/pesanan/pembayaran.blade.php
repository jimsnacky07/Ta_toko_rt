<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pembayaran</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-4xl mx-auto bg-white p-6 rounded shadow-md">
        <h2 class="text-xl font-bold text-center mb-6">Pembayaran</h2>

        <!-- Detail Pesanan -->
        <div class="mb-4 border p-4 rounded">
            <h3 class="text-lg font-semibold border-b pb-1 mb-2">Detail Pesanan</h3>
            <p class="mb-2"><strong>Jenis Pakaian:</strong> {{ request('jenis_pakaian') ?: '-' }}</p>
            <p class="mb-2"><strong>Jenis Kain:</strong> {{ request('jenis_kain') ?: '-' }}</p>
            <p class="mb-2"><strong>Request Customer:</strong> {{ request('request') ?: '-' }}</p>
            <p class="mb-2"><strong>Gambar Tambahan:</strong> 
                {{ request('gambar_custom') ? request('gambar_custom') : 'Tidak ada gambar diunggah' }}
            </p>
        </div>


        <!-- Profil Pengguna -->
        <div class="mb-4 border p-4 rounded">
            <h3 class="text-lg font-semibold border-b pb-1 mb-2">Profil Pengguna</h3>
            <p>Informasi pengguna akan ditampilkan di sini.</p>
        </div>

        <!-- Tombol -->
        <div class="flex justify-end">
            <button class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                Lanjut Pembayaran
            </button>
        </div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <title>Beranda | Romansa Tailor</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    :root {
      --main-tan: #A38C6C;
    }

    .bg-tan {
      background-color: var(--main-tan);
    }

    .text-tan {
      color: var(--main-tan);
    }

    .hover\:bg-tan-hover:hover {
      background-color: #8c785b; /* warna tan sedikit lebih gelap */
    }
  </style>
    </head>

        <body class="bg-gray-100 min-h-screen">
        <!-- Header -->
        <header class="bg-tan text-white p-4 flex justify-between items-center h-16">
  <div class="flex items-center space-x-2">
    <img src="{{ asset('images/logo%20romansa.png') }}" alt="Logo Romansa" class="h-16 object-contain" />
    <span class="text-xl font-bold">Romansa Tailor</span>
  </div>
  <div class="flex items-center space-x-2">
    <a href="{{ route('login') }}" 
      class="px-4 py-1 rounded-full bg-white text-[var(--main-tan)] text-sm font-semibold hover:bg-gray-200 transition">
        Masuk
    </a>
  <a href="{{ url('/register') }}" class="px-4 py-1 rounded-full bg-white text-[var(--main-tan)] text-sm font-semibold hover:bg-gray-200 transition">Daftar</a>


</header>


  <!-- Judul Section -->
  <div class="bg-white py-6 shadow text-center">
    <h1 class="text-3xl font-bold mb-4 text-[var(--main-tan)]">Hai! Selamat Datang di Romansa Tailor</h1>
    <p class="text-lg text-gray-800">Pilih style kamu sendiri, kami jahitkan dengan rapi</p>
  </div>

 <main class="px-4 py-6">
  <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">

    <!-- Produk 1 -->
    <div class="bg-white rounded-lg shadow hover:shadow-md transition overflow-hidden">
      <img src="{{ asset('images/baju kemeja cowok 2.jpg') }}" alt="Produk 1" class="w-full h-64 object-contain p-2">
      <div class="p-3">
        <h3 class="text-gray-800 font-semibold text-base mb-1">Kemeja Cowok Polos Lengan Panjang </h3>
        <p class="text-tan font-bold text-sm">Rp 85.000</p>
      </div>
    </div>

    <!-- Produk 2 -->
    <div class="bg-white rounded-lg shadow hover:shadow-md transition overflow-hidden">
      <img src="{{ asset('images/rok 4.jpg') }}" alt="Produk 2" class="w-full h-64 object-contain p-2">
      <div class="p-3">
        <h3 class="text-gray-800 font-semibold text-base mb-1">Rok Ungu Shimmer</h3>
        <p class="text-tan font-bold text-sm">Rp 160.000</p>
      </div>
    </div>

    <!-- Produk 3 -->
    <div class="bg-white rounded-lg shadow hover:shadow-md transition overflow-hidden">
      <img src="{{ asset('images/rok 6.jpg') }}" alt="Produk 3" class="w-full h-64 object-contain p-2">
      <div class="p-3">
        <h3 class="text-gray-800 font-semibold text-base mb-1">Rok Beige Shimmer</h3>
        <p class="text-tan font-bold text-sm">Rp 120.000</p>
      </div>
    </div>

    <!-- Produk 4 -->
    <div class="bg-white rounded-lg shadow hover:shadow-md transition overflow-hidden">
      <img src="{{ asset('images/rok 5 (1).jpg') }}" alt="Produk 4" class="w-full h-64 object-contain p-2">
      <div class="p-3">
        <h3 class="text-gray-800 font-semibold text-base mb-1">Rok Brukat Maron </h3>
        <p class="text-tan font-bold text-sm">Rp 198.000</p>
      </div>
    </div>

    <!-- Produk 5 -->
    <div class="bg-white rounded-lg shadow hover:shadow-md transition overflow-hidden">
      <img src="{{ asset('images/baju kemeja cowok 4.jpg') }}" alt="Produk 5" class="w-full h-64 object-contain p-2">
      <div class="p-3">
        <h3 class="text-gray-800 font-semibold text-base mb-1">Workshirt kemeja lengan pendek </h3>
        <p class="text-tan font-bold text-sm">Rp 180.000</p>
      </div>
    </div>

    <!-- Produk 6 -->
    <div class="bg-white rounded-lg shadow hover:shadow-md transition overflow-hidden">
      <img src="{{ asset('images/rok 3.jpg') }}" alt="Produk 6" class="w-full h-64 object-contain p-2">
      <div class="p-3">
        <h3 class="text-gray-800 font-semibold text-base mb-1">Rok Susun Ruffel</h3>
        <p class="text-tan font-bold text-sm">Rp 180.000</p>
      </div>
    </div>

    <!-- Produk 7 -->
    <div class="bg-white rounded-lg shadow hover:shadow-md transition overflow-hidden">
      <img src="{{ asset('images/rok 2.jpg') }}" alt="Produk 7" class="w-full h-64 object-contain p-2">
      <div class="p-3">
        <h3 class="text-gray-800 font-semibold text-base mb-1">Rok Jenne Susun </h3>
        <p class="text-tan font-bold text-sm">Rp 60.000</p>
      </div>
    </div>

    <!-- Produk 8 -->
    <div class="bg-white rounded-lg shadow hover:shadow-md transition overflow-hidden">
      <img src="{{ asset('images/baju kemeja cowok 1.jpg') }}" alt="Produk 8" class="w-full h-64 object-contain p-2">
      <div class="p-3">
        <h3 class="text-gray-800 font-semibold text-base mb-1">Kemeja Garis Cowok linen</h3>
        <p class="text-tan font-bold text-sm">Rp 90.000</p>
      </div>
    </div>

    <!-- Produk 9 -->
    <div class="bg-white rounded-lg shadow hover:shadow-md transition overflow-hidden">
      <img src="{{ asset('images/rok 8.jpg') }}" alt="Produk 9" class="w-full h-64 object-contain p-2">
      <div class="p-3">
        <h3 class="text-gray-800 font-semibold text-base mb-1">Rok Coqueete</h3>
        <p class="text-tan font-bold text-sm">Rp 150.000</p>
      </div>
    </div>

    <!-- Produk 10 -->
    <div class="bg-white rounded-lg shadow hover:shadow-md transition overflow-hidden">
      <img src="{{ asset('images/baju kemeja cowok 3.jpg') }}" alt="Produk 10" class="w-full h-64 object-contain p-2">
      <div class="p-3">
        <h3 class="text-gray-800 font-semibold text-base mb-1">Kemeja Levis Cowok</h3>
        <p class="text-tan font-bold text-sm">Rp 220.000</p>
      </div>
    </div>

    <!-- Produk 11 -->
    <div class="bg-white rounded-lg shadow hover:shadow-md transition overflow-hidden">
      <img src="{{ asset('images/rok 7.jpg') }}" alt="Produk 11" class="w-full h-64 object-contain p-2">
      <div class="p-3">
        <h3 class="text-gray-800 font-semibold text-base mb-1">Rok liner</h3>
        <p class="text-tan font-bold text-sm">Rp 57.000</p>
      </div>
    </div>

    <!-- Produk 12 -->
    <div class="bg-white rounded-lg shadow hover:shadow-md transition overflow-hidden">
      <img src="{{ asset('images/rok 5 (3).jpg') }}" alt="Produk 12" class="w-full h-64 object-contain p-2">
      <div class="p-3">
        <h3 class="text-gray-800 font-semibold text-base mb-1">Rok Brukat Hijau</h3>
        <p class="text-tan font-bold text-sm">Rp 198.000</p>
      </div>
    </div>

    <!-- Produk 13 -->
    <div class="bg-white rounded-lg shadow hover:shadow-md transition overflow-hidden">
      <img src="{{ asset('images/rok 5(2).jpg') }}" alt="Produk 13" class="w-full h-64 object-contain p-2">
      <div class="p-3">
        <h3 class="text-gray-800 font-semibold text-base mb-1">Rok Brukat Hitam</h3>
        <p class="text-tan font-bold text-sm">Rp 198.000</p>
      </div>
    </div>

    <!-- Produk 14 -->
    <div class="bg-white rounded-lg shadow hover:shadow-md transition overflow-hidden">
      <img src="{{ asset('images/rok 5 (4).jpg') }}" alt="Produk 14" class="w-full h-64 object-contain p-2">
      <div class="p-3">
        <h3 class="text-gray-800 font-semibold text-base mb-1">Rok Brukat cream</h3>
        <p class="text-tan font-bold text-sm">Rp 198.000</p>
      </div>
    </div>

      <!-- Produk 16 -->
    <div class="bg-white rounded-lg shadow hover:shadow-md transition overflow-hidden">
      <img src="{{ asset('images/celana 1.jpg') }}" alt="Produk 16" class="w-full h-64 object-contain p-2">
      <div class="p-3">
        <h3 class="text-gray-800 font-semibold text-base mb-1">Celana Coklat Formal</h3>
        <p class="text-tan font-bold text-sm">Rp 170.000</p>
      </div>
    </div>

    <!-- Produk 17 -->
    <div class="bg-white rounded-lg shadow hover:shadow-md transition overflow-hidden">
      <img src="{{ asset('images/celana 2.jpg') }}" alt="Produk 17" class="w-full h-64 object-contain p-2">
      <div class="p-3">
        <h3 class="text-gray-800 font-semibold text-base mb-1">Celana Cream Casual</h3>
        <p class="text-tan font-bold text-sm">Rp 160.000</p>
      </div>
    </div>

    <!-- Produk 18 -->
    <div class="bg-white rounded-lg shadow hover:shadow-md transition overflow-hidden">
      <img src="{{ asset('images/celana 3.jpg') }}" alt="Produk 18" class="w-full h-64 object-contain p-2">
      <div class="p-3">
        <h3 class="text-gray-800 font-semibold text-base mb-1">Celana Linen </h3>
        <p class="text-tan font-bold text-sm">Rp 78.000</p>
      </div>
    </div>

    <!-- Produk 19 -->
    <div class="bg-white rounded-lg shadow hover:shadow-md transition overflow-hidden">
      <img src="{{ asset('images/celana 4.jpg') }}" alt="Produk 19" class="w-full h-64 object-contain p-2">
      <div class="p-3">
        <h3 class="text-gray-800 font-semibold text-base mb-1">Celana Slim Fit Hitam</h3>
        <p class="text-tan font-bold text-sm">Rp 179.000</p>
      </div>
    </div>

    <!-- Produk 20 -->
    <div class="bg-white rounded-lg shadow hover:shadow-md transition overflow-hidden">
      <img src="{{ asset('images/celana 5.jpg') }}" alt="Produk 20" class="w-full h-64 object-contain p-2">
      <div class="p-3">
        <h3 class="text-gray-800 font-semibold text-base mb-1">Celana Chino Light</h3>
        <p class="text-tan font-bold text-sm">Rp 174.000</p>
      </div>
    </div>

    <!-- Produk 21 -->
    <div class="bg-white rounded-lg shadow hover:shadow-md transition overflow-hidden">
      <img src="{{ asset('images/celana 6.jpg') }}" alt="Produk 21" class="w-full h-64 object-contain p-2">
      <div class="p-3">
        <h3 class="text-gray-800 font-semibold text-base mb-1">Celana Wide Black</h3>
        <p class="text-tan font-bold text-sm">Rp 180.000</p>
      </div>
    </div>

       <!-- Produk 22 -->
    <div class="bg-white rounded-lg shadow hover:shadow-md transition overflow-hidden">
      <img src="{{ asset('images/jas.jpg') }}" alt="Produk 22" class="w-full h-64 object-contain p-2">
      <div class="p-3">
        <h3 class="text-gray-800 font-semibold text-base mb-1">Jas Wanita navy</h3>
        <p class="text-tan font-bold text-sm">Rp 310.000</p>
      </div>
    </div>
 
      <!-- Produk 23 -->
    <div class="bg-white rounded-lg shadow hover:shadow-md transition overflow-hidden">
      <img src="{{ asset('images/jas cewek 2.jpg') }}" alt="Produk 23" class="w-full h-64 object-contain p-2">
      <div class="p-3">
        <h3 class="text-gray-800 font-semibold text-base mb-1">Jas Wanita Abu-Abu Elegan</h3>
        <p class="text-tan font-bold text-sm">Rp 350.000</p>
      </div>
    </div>

    <!-- Produk 24 -->
    <div class="bg-white rounded-lg shadow hover:shadow-md transition overflow-hidden">
      <img src="{{ asset('images/kebaya.jpg') }}" alt="Produk 24" class="w-full h-64 object-contain p-2">
      <div class="p-3">
        <h3 class="text-gray-800 font-semibold text-base mb-1">kebaya Taro</h3>
        <p class="text-tan font-bold text-sm">Rp 350.000</p>
      </div>
    </div>

       <!-- Produk 25 -->
    <div class="bg-white rounded-lg shadow hover:shadow-md transition overflow-hidden">
      <img src="{{ asset('images/jas cewek 3.jpg') }}" alt="Produk 25" class="w-full h-64 object-contain p-2">
      <div class="p-3">
        <h3 class="text-gray-800 font-semibold text-base mb-1">Jas Wanita Abu-Abu Gelap</h3>
        <p class="text-tan font-bold text-sm">Rp 380.000</p>
      </div>
    </div>

    <!-- Produk 26 -->
    <div class="bg-white rounded-lg shadow hover:shadow-md transition overflow-hidden">
      <img src="{{ asset('images/jas cewek 4.jpg') }}" alt="Produk 26" class="w-full h-64 object-contain p-2">
      <div class="p-3">
        <h3 class="text-gray-800 font-semibold text-base mb-1">Jas Wanita Simpel Mahogany</h3>
        <p class="text-tan font-bold text-sm">Rp 350.000</p>
      </div>
    </div>

     <!-- Produk 27 -->
    <div class="bg-white rounded-lg shadow hover:shadow-md transition overflow-hidden">
      <img src="{{ asset('images/jas cewek 5.jpg') }}" alt="Produk 27" class="w-full h-64 object-contain p-2">
      <div class="p-3">
        <h3 class="text-gray-800 font-semibold text-base mb-1">Jas Wanita Mahogany</h3>
        <p class="text-tan font-bold text-sm">Rp 350.000</p>
      </div>
    </div>

    <!-- Produk 28 -->
    <div class="bg-white rounded-lg shadow hover:shadow-md transition overflow-hidden">
      <img src="{{ asset('images/jas cewek 6.jpg') }}" alt="Produk 26" class="w-full h-64 object-contain p-2">
      <div class="p-3">
        <h3 class="text-gray-800 font-semibold text-base mb-1">Jas Wanita Hijau Zaitun</h3>
        <p class="text-tan font-bold text-sm">Rp 350.000</p>
      </div>
    </div>

    <!-- Produk 29 -->
    <div class="bg-white rounded-lg shadow hover:shadow-md transition overflow-hidden">
      <img src="{{ asset('images/jas cewek 7.jpg') }}" alt="Produk 29" class="w-full h-64 object-contain p-2">
      <div class="p-3">
        <h3 class="text-gray-800 font-semibold text-base mb-1">Jas Wanita Army</h3>
        <p class="text-tan font-bold text-sm">Rp 450.000</p>
      </div>
    </div>

     <!-- Produk 30 -->
    <div class="bg-white rounded-lg shadow hover:shadow-md transition overflow-hidden">
      <img src="{{ asset('images/jas cewek 8.jpg') }}" alt="Produk 30" class="w-full h-64 object-contain p-2">
      <div class="p-3">
        <h3 class="text-gray-800 font-semibold text-base mb-1">Jas Wanita Cream Elegan</h3>
        <p class="text-tan font-bold text-sm">Rp 400.000</p>
      </div>
    </div>

      <!-- Produk 31 -->
    <div class="bg-white rounded-lg shadow hover:shadow-md transition overflow-hidden">
      <img src="{{ asset('images/jas cewek 9.jpg') }}" alt="Produk 31" class="w-full h-64 object-contain p-2">
      <div class="p-3">
        <h3 class="text-gray-800 font-semibold text-base mb-1">Jas Wanita Biru Navy</h3>
        <p class="text-tan font-bold text-sm">Rp 400.000</p>
      </div>
    </div>

    <!-- Produk 32 -->
    <div class="bg-white rounded-lg shadow hover:shadow-md transition overflow-hidden">
      <img src="{{ asset('images/jas cewek 10.jpg') }}" alt="Produk 32" class="w-full h-64 object-contain p-2">
      <div class="p-3">
        <h3 class="text-gray-800 font-semibold text-base mb-1">Jas Wanita Hitam Modern</h3>
        <p class="text-tan font-bold text-sm">Rp 410.000</p>
      </div>
    </div>

    <!-- Produk 33 -->
    <div class="bg-white rounded-lg shadow hover:shadow-md transition overflow-hidden">
      <img src="{{ asset('images/baju kemeja cowok 2.jpg') }}" alt="Produk 33" class="w-full h-64 object-contain p-2">
      <div class="p-3">
        <h3 class="text-gray-800 font-semibold text-base mb-1">Baju Kemeja Cowok Cream</h3>
        <p class="text-tan font-bold text-sm">Rp 100.000</p>
      </div>
    </div>

    <!-- Produk 34 -->
    <div class="bg-white rounded-lg shadow hover:shadow-md transition overflow-hidden">
      <img src="{{ asset('images/baju kemeja cowok 5.jpg') }}" alt="Produk 34" class="w-full h-64 object-contain p-2">
      <div class="p-3">
        <h3 class="text-gray-800 font-semibold text-base mb-1">Baju Kemeja Cowok Lengan Panjang Abu-Abu</h3>
        <p class="text-tan font-bold text-sm">Rp 100.000</p>
      </div>
    </div>

    <!-- Produk 35 -->
    <div class="bg-white rounded-lg shadow hover:shadow-md transition overflow-hidden">
      <img src="{{ asset('images/kemeja cowok.jpg') }}" alt="Produk 35" class="w-full h-64 object-contain p-2">
      <div class="p-3">
        <h3 class="text-gray-800 font-semibold text-base mb-1">Baju Kemeja Cowok Lengan Pendek Navy</h3>
        <p class="text-tan font-bold text-sm">Rp 100.000</p>
      </div>
    </div>

    <!-- Produk 36 -->
    <div class="bg-white rounded-lg shadow hover:shadow-md transition overflow-hidden">
      <img src="{{ asset('images/baju kemeja  cowok 5.jpg') }}" alt="Produk 36" class="w-full h-64 object-contain p-2">
      <div class="p-3">
        <h3 class="text-gray-800 font-semibold text-base mb-1">Baju Kemeja Cowok Navy</h3>
        <p class="text-tan font-bold text-sm">Rp 100.000</p>
      </div>
    </div>

  </div>
</main>
  </div>
</main> 
    </div>
  </main>
</body>
</html>

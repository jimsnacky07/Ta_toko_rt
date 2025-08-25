@extends('user.layouts.app')

@section('content')
   <!-- Judul di luar gambar -->
<h1 class="text-3xl font-bold mb-4 text-center text-[#A38C6C]">Tentang Kami</h1>

<!-- Gambar spanduk -->
<div class="relative h-[600px] rounded-md shadow-md mb-6 overflow-hidden">
    <img src="{{ asset('images/spanduk jahit.png') }}" alt="Tentang Kami" class="w-full h-full object-cover">
    <!-- Layer hitam di atas gambar tanpa teks -->
    <div class="absolute inset-0 bg-black bg-opacity-30"></div>
</div>

<!-- Konten Tentang Toko -->
<div class="bg-white p-6 rounded shadow-md text-gray-800 leading-relaxed mb-10">
    <div class="flex justify-center">
  <h2 class="text-2xl font-semibold mb-4 text-center">Selamat datang di Romansa Tailor</h2>
</div>
<div class="p-4">
  <p class="mb-4 indent-8 text-justify">
    Romansa Tailor adalah tempat jahit terpercaya yang hadir untuk memenuhi kebutuhan fashion Anda dengan hasil jahitan yang rapi, nyaman dipakai, dan sesuai keinginan. Kami percaya bahwa pakaian bukan hanya soal penampilan, tapi juga tentang rasa percaya diri. Karena itu, setiap jahitan kami kerjakan dengan teliti dan sepenuh hati agar menghasilkan pakaian yang benar-benar pas di badan dan nyaman digunakan dalam berbagai aktivitas.
  </p>

  <p class="mb-4 indent-8 text-justify">
    Kami melayani berbagai jenis jahitan, mulai dari pakaian harian, seragam kerja, kebaya, jas, hingga baju pesta dan permak pakaian. Selain itu, kami juga menerima jahitan custom sesuai model yang Anda inginkan. Anda bisa membawa desain sendiri atau berkonsultasi langsung dengan tim kami untuk mendapatkan hasil terbaik.
  </p>

  <p class="mb-4 indent-8 text-justify">
    Romansa Tailor berlokasi di <span class="italic text-gray-600 font-bold">Perumahan Griya Alifa Al-Madani blok B no 27</span>, dan buka setiap hari jam<span class="italic text-gray-600"> 08:00 WIB - 22:00 WIB</span>.
  </p>

  <p class="indent-8 text-justify">
     Anda bisa konsultasi terlebih dahulu melalui WhatsApp. Silakan hubungi kami di nomor: <span class="font-semibold text-blue-600">082169878747</span>
    Kami siap melayani Anda dengan sepenuh hati dan hasil jahitan terbaik.
  </p>
</div>

<!-- Fitur WhatsApp dan Google Maps -->
<div class="flex flex-col sm:flex-row justify-center items-center gap-6 mb-10">
  <!-- WhatsApp Button -->
  <a href="https://wa.me/6282169878747?text=Halo%20Romansa%20Tailor%2C%20saya%20ingin%20bertanya%20tentang%20layanan%20jahit"
   target="_blank"
   class="flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white font-semibold px-6 py-3 rounded-full shadow-md transition">
  <img src="{{ asset('images/wa.png') }}" alt="WhatsApp" class="w-5 h-5">
  Hubungi via WhatsApp
</a>

  <!-- Google Maps Button -->
  <a href="https://www.google.com/maps/search/?api=1&query=Jl.+Karet+Dusun+2+No.1,+RT.01+RW.02,+Desa+Rimbo+Panjang,+Kec.+Marpoyan+Damai,+Kota+Pekanbaru,+Riau+28289"
     target="_blank"
     class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-full shadow-md transition">
    
    <!-- Pakai Gambar Maps -->
    <img src="{{ asset('images/maps.png') }}" alt="Maps" class="w-5 h-5">

    Lihat Lokasi Toko
  </a>
</div>



@endsection

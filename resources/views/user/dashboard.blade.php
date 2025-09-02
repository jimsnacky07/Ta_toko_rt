@extends('user.layouts.app')

@section('title', 'Dashboard')

@section('content')
  <div class="relative h-[400px] rounded-md shadow-md mb-6 overflow-hidden">
    <img src="{{ asset('images/gambar1.jpg') }}" alt="Banner" class="w-full h-full object-cover">
    <div class="absolute inset-0 bg-black/40 flex flex-col items-center justify-center">
      <h1 class="text-white text-3xl md:text-4xl font-bold">Selamat Datang di Aplikasi Pemesanan Jahit</h1>
      <p class="text-white mt-2 text-lg">Pilih style sendiri, kami jahitkan dengan rapi</p>
    </div>
  </div>

  <!-- Search & Filter -->
  <div class="bg-white rounded-lg shadow p-4 mb-6">
    <div class="flex flex-col md:flex-row gap-4">
      <div class="flex-1">
        <input type="text" 
               placeholder="Cari produk..." 
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
               id="searchInput">
      </div>
      <div class="flex gap-2">
        <select class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" id="categoryFilter">
          <option value="">Semua Kategori</option>
          <option value="kemeja">Kemeja</option>
          <option value="blus">Blus</option>
          <option value="dress">Dress</option>
          <option value="celana">Celana</option>
        </select>
        <select class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" id="sortFilter">
          <option value="">Urutkan</option>
          <option value="name_asc">Nama A-Z</option>
          <option value="name_desc">Nama Z-A</option>
          <option value="price_asc">Harga Terendah</option>
          <option value="price_desc">Harga Tertinggi</option>
        </select>
      </div>
    </div>
  </div>

  <div class="mb-8">
    <h2 class="text-xl font-semibold text-gray-800 mb-3">Semua Produk</h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6" id="productsGrid">
      @forelse ($products ?? [] as $product)
        <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow duration-300 overflow-hidden product-card" 
             data-name="{{ strtolower($product->name) }}" 
             data-category="{{ strtolower($product->kategory ?? '') }}"
             data-price="{{ $product->price }}">
          <a href="{{ route('user.products.show', $product->id) }}" class="block">
            <!-- Product Image -->
            <div class="aspect-square bg-gray-50 flex items-center justify-center p-4">
              <img src="{{ asset($product->image) }}" 
                   alt="{{ $product->name }}" 
                   class="w-full h-full object-contain hover:scale-105 transition-transform duration-300">
            </div>
            
            <!-- Product Info -->
            <div class="p-4">
              <h3 class="font-semibold text-gray-800 mb-2 line-clamp-2 min-h-[3rem]">
                {{ $product->name }}
              </h3>
              
              <!-- Category -->
              @if($product->kategory)
                <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full mb-2">
                  {{ $product->kategory }}
                </span>
              @endif
              
              <!-- Price -->
              <div class="flex items-center justify-between">
                <span class="text-lg font-bold text-[#D2B48C]">
                  Rp {{ number_format((int)$product->price, 0, ',', '.') }}
                </span>
                
                <!-- Preorder Badge -->
                @if($product->is_preorder)
                  <span class="bg-orange-100 text-orange-800 text-xs px-2 py-1 rounded-full">
                    Pre-order
                  </span>
                @endif
              </div>
              
              <!-- Available Sizes -->
              @if($product->sizes)
                <div class="mt-2 flex flex-wrap gap-1">
                  @foreach(explode(',', $product->sizes) as $size)
                    <span class="bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded">
                      {{ trim($size) }}
                    </span>
                  @endforeach
                </div>
              @endif
              
              <!-- Material Info -->
              @if($product->bahan)
                <p class="text-sm text-gray-600 mt-2">
                  <span class="font-medium">Bahan:</span> {{ $product->bahan }}
                </p>
              @endif
            </div>
          </a>
        </div>
      @empty
        <div class="col-span-full text-center py-12">
          <div class="text-gray-400 mb-4">
            <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2 2v-5m16 0h-2M4 13h2m13-8V4a1 1 0 00-1-1H7a1 1 0 00-1 1v1m8 0V4.5" />
            </svg>
          </div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada produk</h3>
          <p class="text-gray-600">Produk akan segera tersedia. Silakan cek kembali nanti.</p>
        </div>
      @endforelse
    </div>
  </div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const sortFilter = document.getElementById('sortFilter');
    const productCards = document.querySelectorAll('.product-card');
    
    // Search functionality
    searchInput.addEventListener('input', filterProducts);
    categoryFilter.addEventListener('change', filterProducts);
    sortFilter.addEventListener('change', sortProducts);
    
    function filterProducts() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedCategory = categoryFilter.value.toLowerCase();
        
        productCards.forEach(card => {
            const productName = card.dataset.name;
            const productCategory = card.dataset.category;
            
            const matchesSearch = productName.includes(searchTerm);
            const matchesCategory = !selectedCategory || productCategory.includes(selectedCategory);
            
            if (matchesSearch && matchesCategory) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }
    
    function sortProducts() {
        const sortValue = sortFilter.value;
        const grid = document.getElementById('productsGrid');
        const cards = Array.from(productCards);
        
        if (!sortValue) return;
        
        cards.sort((a, b) => {
            const nameA = a.dataset.name;
            const nameB = b.dataset.name;
            const priceA = parseInt(a.dataset.price);
            const priceB = parseInt(b.dataset.price);
            
            switch(sortValue) {
                case 'name_asc':
                    return nameA.localeCompare(nameB);
                case 'name_desc':
                    return nameB.localeCompare(nameA);
                case 'price_asc':
                    return priceA - priceB;
                case 'price_desc':
                    return priceB - priceA;
                default:
                    return 0;
            }
        });
        
        // Re-append sorted cards
        cards.forEach(card => grid.appendChild(card));
    }
});
</script>
@endpush

@endsection

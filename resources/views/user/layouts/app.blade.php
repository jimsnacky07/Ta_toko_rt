<!DOCTYPE html>
<html lang="en" x-data="{ collapsed: false }" class="h-full">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'App')</title>

    {{-- Tailwind & Alpine --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    {{-- CSRF token untuk AJAX/fetch (penting agar POST tidak 419) --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        :root { --main-tan: #A38C6C; }
    </style>

    @php $route = Route::currentRouteName(); @endphp
</head>

<body class="bg-gray-100 h-full text-base">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside :class="collapsed ? 'w-20' : 'w-64'"
               class="flex-shrink-0 transition-all duration-300"
               style="background-color: var(--main-tan); color: white;">
            <div class="h-16 px-4 flex items-center justify-between" style="background-color: #A38C6C;">
                <div x-show="!collapsed" class="bg-[#EAE0C8] text-black px-4 py-1 rounded-full text-sm font-bold">User</div>
                <button @click="collapsed = !collapsed"
                        class="bg-gray-800 text-white w-8 h-8 rounded-full flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>

            <nav class="mt-4">
                <ul>
                    <!-- HOME -->
                    <li
                    class="py-3 px-5 flex items-center text-lg font-medium text-black hover:bg-[#EAE0C8] {{ request()->routeIs('user.dashboard') ? 'bg-[#EAE0C8]' : '' }}"
                    :class="collapsed ? 'justify-center' : 'space-x-4'">
                    <a href="{{ route('user.dashboard') }}" class="flex items-center gap-3 w-full">
                        <img src="{{ asset('images/home1 .png') }}" alt="Home" class="w-7 h-7">
                        <span x-show="!collapsed">Home</span>
                    </a>
                    </li>

                    <!-- ORDERS CUSTOM -->
                    <li
                        class="py-3 px-5 flex items-center text-lg font-medium text-black hover:bg-[#EAE0C8] {{ $route == 'buat.pesanan' ? 'bg-[#EAE0C8]' : '' }}"
                        :class="collapsed ? 'justify-center' : 'space-x-4'">
                        <a href="{{ route('buat.pesanan') }}" class="flex items-center gap-3 w-full">
                            <img src="{{ asset('images/buat pesanan.png') }}" alt="Buat Pesanan" class="w-7 h-7">
                            <span x-show="!collapsed">Orders Custom</span>
                        </a>
                    </li>

                    <!-- PESANAN SAYA -->
                    <li
                        class="py-3 px-5 flex items-center text-lg font-medium text-black hover:bg-[#EAE0C8] {{ $route == 'user.keranjang' ? 'bg-[#EAE0C8]' : '' }}"
                        :class="collapsed ? 'justify-center' : 'space-x-4'">
                        <a href="{{ route('pusat.pesanan') }}" class="flex items-center gap-3 w-full">
                            <img src="{{ asset('images/pusat pesanan.png') }}" alt="Pusat Pesanan" class="w-7 h-7">
                            <span x-show="!collapsed">Pesanan Saya</span>
                        </a>
                    </li>

                    <!-- TENTANG KAMI -->
                    <li
                        class="py-3 px-5 flex items-center text-lg font-medium text-black hover:bg-[#EAE0C8] {{ $route == 'user.orders' ? 'bg-[#EAE0C8]' : '' }}"
                        :class="collapsed ? 'justify-center' : 'space-x-4'">
                        <a href="{{ route('tentangkami') }}" class="flex items-center gap-3 w-full">
                            <img src="{{ asset('images/tentang kami.png') }}" alt="Tentang Kami" class="w-7 h-7">
                            <span x-show="!collapsed">Tentang Kami </span>
                        </a>
                    </li>

                    <!-- KERANJANG -->
                    <li
                        class="py-3 px-5 flex items-center text-lg font-medium text-black hover:bg-[#EAE0C8] {{ $route == 'keranjang.index' ? 'bg-[#EAE0C8]' : '' }}"
                        :class="collapsed ? 'justify-center' : 'space-x-4'">
                        <a href="{{ route('keranjang.index') }}" class="flex items-center gap-3 w-full">
                            <img src="{{ asset('images/keranjang.png') }}" alt="Keranjang" class="w-7 h-7">
                            <span x-show="!collapsed">Keranjang</span>
                        </a>
                    </li>

                    <!-- PROFIL -->
                    <li class="py-3 px-5 flex items-center text-base font-medium text-black hover:bg-[#EAE0C8]"
                        :class="collapsed ? 'justify-center' : 'space-x-4'">
                        <a href="{{ route('profil.index') }}" class="flex items-center gap-3 w-full">
                            <img src="{{ asset('images/profil.png') }}" alt="Profil" class="w-7 h-7">
                            <span x-show="!collapsed">Profil</span>
                        </a>
                    </li>

                    <!-- LOGOUT -->
                    <li class="py-3 px-5 flex items-center text-base font-medium text-black hover:bg-[#EAE0C8]"
                        :class="collapsed ? 'justify-center' : 'space-x-4'">
                        <form method="POST" action="{{ route('logout') }}" class="flex items-center gap-3 w-full">
                            @csrf
                            <button type="submit" class="flex items-center gap-3 w-full text-left">
                                <img src="{{ asset('images/logout.png') }}" alt="Logout" class="w-7 h-7">
                                <span x-show="!collapsed">Logout</span>
                            </button>
                        </form>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex flex-col flex-1 min-h-screen">
            <!-- Top Bar -->
            <header class="h-16 flex items-center justify-end px-4" style="background-color: var(--main-tan);">
            </header>

            <!-- elemen melayang seperti tombol WA -->
            @stack('floating')

            <!-- Page Content -->
            <main class="p-6 flex-1 overflow-y-auto">
                @yield('content')
            </main>
        </div>
    </div>

    {{-- tempat injeksi script halaman (pakai @push('scripts') di view) --}}
    @stack('scripts')
</body>
</html>

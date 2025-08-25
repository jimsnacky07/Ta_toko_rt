<!DOCTYPE html>
<html lang="en" x-data="{ collapsed: false }" class="h-full">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Admin • Romansa Tailor')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>:root{--main-tan:#A38C6C}</style>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="bg-gray-100 h-full">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside :class="collapsed ? 'w-20' : 'w-64'" class="flex-shrink-0 transition-all duration-300"
            style="background-color: var(--main-tan); color: white;">
            <div class="h-16 px-4 flex items-center justify-between" style="background-color: #A38C6C;">
                <div x-show="!collapsed" class="bg-[#EAE0C8] text-black px-4 py-1 rounded-full text-sm font-bold">Admin
                </div>
                <button @click="collapsed = !collapsed"
                    class="bg-gray-800 text-white w-8 h-8 rounded-full flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>

            <nav class="mt-4 text-sm">
                <ul>
                    <li
                        class="py-3 px-5 flex items-center text-base font-medium text-black hover:bg-[#EAE0C8] {{ request()->routeIs('admin.dashboard') ? 'bg-[#EAE0C8]' : '' }}">
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 w-full">
                            <img src="{{ asset('images/diagram_admin.png') }}" class="w-7 h-7" alt="Dashboard">
                            <span x-show="!collapsed">Dashboard</span>
                        </a>
                    </li>

                    <li
                        class="py-3 px-5 flex items-center text-base font-medium text-black hover:bg-[#EAE0C8] {{ request()->routeIs('admin.daftar.pesanan*') ? 'bg-[#EAE0C8]' : '' }}">
                        <a href="{{ route('admin.daftar.pesanan') }}" class="flex items-center gap-3 w-full">
                            <img src="{{ asset('images/daftar pesanan.png') }}" class="w-7 h-7" alt="Daftar Pesanan">
                            <span x-show="!collapsed">Daftar Pesanan</span>
                        </a>
                    </li>

                    <li
                        class="py-3 px-5 flex items-center text-base font-medium text-black hover:bg-[#EAE0C8] {{ request()->routeIs('admin.galeri.jahit*') ? 'bg-[#EAE0C8]' : '' }}">
                        <a href="{{ route('admin.galeri.jahit.index') }}" class="flex items-center gap-3 w-full">
                            <img src="{{ asset('images/galeri.png') }}" class="w-7 h-7" alt="Galeri Jahit">
                            <span x-show="!collapsed">Galeri Jahit</span>
                        </a>
                    </li>

                    <li
                        class="py-3 px-5 flex items-center text-base font-medium text-black hover:bg-[#EAE0C8] {{ request()->routeIs('admin.pelanggan*') ? 'bg-[#EAE0C8]' : '' }}">
                        <a href="{{ route('admin.pelanggan') }}" class="flex items-center gap-3 w-full">
                            <img src="{{ asset('images/pelanggan.png') }}" class="w-7 h-7" alt="Pelanggan">
                            <span x-show="!collapsed">Pelanggan</span>
                        </a>
                    </li>

                    <li class="py-3 px-5 flex items-center text-base font-medium text-black hover:bg-[#EAE0C8]">
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
                <!-- Removed "IB" section -->
            </header>

            @stack('floating')

            <main class="p-6 flex-1 overflow-y-auto">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>

</html>

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LevelMiddleware
{
    public function handle(Request $request, Closure $next, $level)
    {
        // Cek apakah pengguna sudah login
        if (Auth::check()) {
            // Cek apakah level pengguna sesuai dengan yang dibutuhkan
            if (Auth::user()->level == $level) {
                return $next($request);  // Lanjutkan ke halaman yang diminta
            }

            // Jika level tidak sesuai, alihkan ke halaman utama dengan pesan error
            return redirect('/')->with('error', 'Akses Ditolak. Anda tidak memiliki hak akses.');
        }

        // Jika pengguna belum login, arahkan ke halaman login dengan pesan error
        return redirect('/login')->with('error', 'Anda perlu login untuk mengakses halaman ini.');
    }
}

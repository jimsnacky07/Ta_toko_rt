<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Proses login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Debug: Check if user exists in database
        $user = \App\Models\User::where('email', $request->email)->first();
        if (!$user) {
            return back()->withErrors([
                'email' => 'Email tidak ditemukan.',
            ])->withInput();
        }

        // Debug: Check password
        if (!\Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'email' => 'Password salah.',
            ])->withInput();
        }

        // Try to authenticate with different field names
        $credentials1 = ['email' => $request->email, 'password' => $request->password];
        
        if (Auth::attempt($credentials1)) {
            $request->session()->regenerate();

            // Redirect berdasarkan level
            $userLevel = Auth::user()->level ?? 'customer';
            switch ($userLevel) {
                case 'admin':
                    return redirect()->intended('/admin/dashboard');
                case 'tailor':
                    return redirect()->intended('/tailor/dashboard');
                case 'customer':
                case 'user':
                default:
                    return redirect()->intended('/dashboard');
            }
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->withInput();
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}

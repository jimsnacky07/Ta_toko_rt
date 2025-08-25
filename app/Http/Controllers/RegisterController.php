<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function show()
    {
        return view('auth.register'); 
    }

    public function register(Request $request)
    {
        $request->validate([
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'nama'     => 'required',
            'no_telp'  => 'required',
            'alamat'   => 'required',
            'level'    => 'required'
        ]);

        User::create([
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'nama'     => $request->nama,
            'no_telp'  => $request->no_telp,
            'alamat'   => $request->alamat,
            'level'    => $request->level
        ]);

        return redirect('/login')->with('success', 'Registrasi berhasil!');
    }
}

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
            'name'     => 'required',
            'no_telp'  => 'nullable',
            'alamat'   => 'required',
        ]);

        dd($request->all());

        User::create([
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'name'     => $request->name,
            'no_telp'  => $request->no_telp,
            'alamat'   => $request->alamat,
            'level'    => 'user', // default level user
        ]);

        return redirect('/login')->with('success', 'Registrasi berhasil!');
    }
}

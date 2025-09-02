<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfilController extends Controller
{
    public function index()
    {
        $user = Auth::user(); // Ambil user yang sedang login
        return view('user.profil.index', compact('user'));
    }

    /**
     * Tampilkan form edit profil user yang sedang login
     */
    public function edit()
    {
        $user = Auth::user();
        return view('user.profil.edit', compact('user'));
    }

    /**
     * Update profil user yang sedang login
     */
    public function update(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . Auth::id(),
            'no_telp' => 'required|string|max:20',
            'alamat' => 'required|string|max:500',
        ]);

        $user = Auth::user();
        $user->update([
            'nama' => $request->nama,
            'email' => $request->email,
            'no_telp' => $request->no_telp,
            'alamat' => $request->alamat,
        ]);

        return redirect()->route('profil.index')->with('success', 'Profil berhasil diperbarui!');
    }

    /**
     * Hapus user
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('profil.index')->with('success', 'User berhasil dihapus.');
    }

    /**
     * Form ubah password (sama dengan blade yang Anda kirim)
     */
    public function showChangePasswordForm()
    {
        return view('profil.change-password');
    }

    /**
     * Proses ubah password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password sekarang salah']);
        }

        $user->password = Hash::make($request->new_password);
        //$user->save();

        return back()->with('success', 'Password berhasil diubah');
    }
}

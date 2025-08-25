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
     * Tampilkan form edit user
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('profil.edit', compact('user'));
    }

    /**
     * Update data user
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'email' => 'required|email',
            'nama' => 'required',
            'no_telp' => 'required',
            'alamat' => 'required',
            'level' => 'required'
        ]);

        $user = User::findOrFail($id);
        $user->update($request->all());

        return redirect()->route('profil.index')->with('success', 'Data user berhasil diupdate.');
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

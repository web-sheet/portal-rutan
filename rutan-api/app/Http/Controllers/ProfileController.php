<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    // Mengambil data user aktif
    public function show(Request $request)
    {
        return response()->json($request->user());
    }

    // Memperbarui informasi dasar
 public function update(Request $request)
{
    // Ambil data user yang sedang login via JWT
    $user = auth('api')->user();

    if (!$user) {
        return response()->json(['message' => 'User tidak ditemukan'], 401);
    }
    
    // Validasi data masukan
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        // Validasi email agar unik, kecuali untuk email user ini sendiri
        'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        // Validasi username agar unik, wajib diisi, tanpa spasi (alpha_dash), kecuali untuk username user ini sendiri
        'username' => 'required|string|alpha_dash|max:50|unique:users,username,' . $user->id,
    ]);

    // Jalankan update ke database
    $user->update($validated);

    return response()->json([
        'message' => 'Profil berhasil diperbarui', 
        'user' => [
            'name' => $user->name,
            'email' => $user->email,
            'username' => $user->username,
            'role' => $user->role
        ]
    ]);
}

    // Memperbarui password
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|current_password',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json(['message' => 'Password berhasil diubah']);
    }
}
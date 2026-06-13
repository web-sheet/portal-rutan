<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
 

    public function login(Request $request)
    {
        // 1. Validasi input: field 'login' wajib diisi
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        // 2. Deteksi apakah input berupa email atau username
        $loginType = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // 3. Susun credentials untuk JWT attempt
        $credentials = [
            $loginType => $request->login,
            'password' => $request->password,
        ];

        // 4. Lakukan autentikasi menggunakan guard JWT (api)
        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json(['message' => 'Email/Username atau password salah'], 401);
        }

        // 5. Ambil data user yang sedang login untuk dikirim ke Vue
        $user = auth('api')->user();

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'role' => $user->role, // Pastikan kolom 'role' sudah ada di tabel users
            ]
        ]);
    }

    public function me()
    {
        $user = auth('api')->user();

        // Jika user tidak ditemukan (token tidak valid / kedaluwarsa)
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Mengembalikan data user beserta role-nya ke Pinia
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'username' => $user->username, // Pastikan ada di database
            'role' => $user->role,         // Pastikan ada di database ('admin', 'kasi', 'kaur', 'karutan')
        ]);
    }

    public function logout()
    {
        // Melakukan invalidate pada token JWT agar tidak bisa digunakan lagi
        auth('api')->logout();

        return response()->json(['message' => 'Berhasil keluar dari sistem rutan']);
    }

   
}

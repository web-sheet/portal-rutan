<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // Memastikan hanya admin yang bisa mengakses seluruh method di controller ini
    public function __construct()
    {
        if (auth('api')->user() && auth('api')->user()->role !== 'admin') {
            return response()->json(['message' => 'Forbidden: Hanya Admin yang diizinkan'], 403)->send();
        }
    }

    // Ambil semua daftar user rutan
    public function index()
    {
        $users = User::select('id', 'name', 'email', 'username', 'role')->get();
        return response()->json($users);
    }

    // Tambah Petugas/User Baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'username' => 'required|string|alpha_dash|max:50|unique:users',
            'password' => 'required|string|min:6',
            'role' => ['required', Rule::in(['admin', 'kasi', 'kepegawaian', 'perlengkapan', 'karutan','staf_perlengkapan'])],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        return response()->json(['message' => 'Petugas baru berhasil ditambahkan', 'user' => $user], 201);
    }

    // Edit User & Perubahan Role
    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'username' => 'required|string|alpha_dash|max:50|unique:users,username,' . $id,
            'role' => ['required', Rule::in(['admin', 'kasi', 'kepegawaian', 'perlengkapan', 'karutan'])],
            'password' => 'nullable|string|min:6', // Password opsional saat edit
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->username = $validated['username'];
        $user->role = $validated['role'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return response()->json(['message' => 'Data petugas berhasil diperbarui']);
    }
}

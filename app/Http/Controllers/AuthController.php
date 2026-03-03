<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; // Pastikan model User diimport

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. Validasi diubah: menerima 'username' bukan 'user_id'
        $this->validate($request, [
            'username' => 'required',
            'pin'      => 'required'
        ]);

        // 2. Cari user berdasarkan NAMA persis seperti yang diketik
        $user = User::where('name', $request->username)->first();

        // 3. Jika nama tidak ada di database
        if (!$user) {
            return response()->json(['message' => 'Nama pengguna tidak terdaftar'], 404);
        }

        // 4. Jika PIN salah
        if ($request->pin !== $user->pin) {
            return response()->json(['message' => 'PIN yang Anda masukkan salah'], 401);
        }

        // 5. Jika sukses
        return response()->json([
            'message' => 'Login berhasil', 
            'user' => $user
        ], 200);
    }
}
<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller {

    // Login Sederhana (Cek Nama & PIN)
    public function login(Request $request) {
        $this->validate($request, [
            'user_id' => 'required|exists:users,id',
            'pin'     => 'required|string'
        ]);

        $user = User::find($request->user_id);

        // Cek PIN
        if ($user->pin !== $request->pin) {
            return response()->json(['message' => '⛔ PIN Salah! Akses ditolak.'], 401);
        }

        // Cek Status Aktif
        if (!$user->is_active) {
            return response()->json(['message' => '⛔ Akun ini sudah tidak aktif.'], 403);
        }

        return response()->json([
            'message' => 'Login Berhasil',
            'user' => $user
        ]);
    }
}
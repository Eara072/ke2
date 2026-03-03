<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    // 1. FUNGSI UNTUK MENAMBAH USER BARU (Dari fitur Tambah Akun)
    public function store(Request $request)
{
    // 1. Tambahkan validasi phone
    $this->validate($request, [
        'name'  => 'required',
        'phone' => 'required', // <-- Tambahkan ini
        'role'  => 'required',
        'pin'   => 'required'
    ]);

    $user = new User();
    $user->name = $request->name;
    $user->phone = $request->phone; // <-- PASTIKAN BARIS INI ADA!
    $user->role = $request->role;
    $user->pin = $request->pin;
    $user->is_active = 1;
    $user->save();

    return response()->json(['message' => 'User berhasil ditambahkan', 'user' => $user], 201);
}

    // 2. FUNGSI UNTUK MENGUPDATE USER (Yang tadi salah tempat)
    public function update(Request $request, $id)
    {
        $user = User::find($id);
        
        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        // Update nama dan role jika dikirim dari frontend
        if ($request->has('name')) {
            $user->name = $request->input('name');
        }
        
        if ($request->has('role')) {
            $user->role = $request->input('role');
        }
        
        // Update PIN HANYA jika form PIN tidak kosong
        if ($request->has('pin') && !empty($request->input('pin'))) {
            $user->pin = $request->input('pin');
        }

        $user->save();

        return response()->json(['message' => 'User berhasil diupdate', 'user' => $user], 200);
    }
}
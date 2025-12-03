<?php
namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityController extends Controller {

    // 1. Ambil daftar user (untuk dropdown di frontend)
    public function getUsers() {
        return response()->json(User::all());
    }

    // 2. Simpan laporan kegiatan
    public function store(Request $request) {
        $this->validate($request, [
            'user_id' => 'required|exists:users,id',
            'description' => 'required|string'
        ]);

        $activity = Activity::create([
            'user_id' => $request->user_id,
            'description' => $request->description
        ]);

        return response()->json([
            'message' => 'Kegiatan berhasil dicatat! Timer 10 menit di-reset.',
            'data' => $activity
        ]);
    }
}
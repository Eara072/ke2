<?php
namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ActivityController extends Controller {

    // 1. Ambil list user untuk dropdown
    public function getUsers() {
        // Hanya ambil user yang AKTIF
        return response()->json(User::where('is_active', true)->get());
    }

    // 2. Simpan Laporan (DENGAN CEK PIN)
    public function store(Request $request) {
        // Validasi Input
        $this->validate($request, [
            'user_id' => 'required|exists:users,id',
            'description' => 'required|string',
            'pin' => 'required|string' // Wajib kirim PIN
        ]);

        // Ambil data user dari database
        $user = User::find($request->user_id);

        // --- LOGIC PENGECEKAN PIN ---
        // Cek apakah PIN yang dikirim user SAMA DENGAN PIN di database?
        if ($user->pin !== $request->pin) {
            return response()->json([
                'message' => '⛔ PIN Salah! Laporan ditolak.'
            ], 401); // 401 = Unauthorized
        }
        // -----------------------------

        // Jika PIN Benar, Simpan Kegiatan
        $activity = Activity::create([
            'user_id' => $request->user_id,
            'description' => $request->description
        ]);

        // Update waktu terakhir lapor di tabel user (untuk CronJob)
        $user->last_activity_at = Carbon::now();
        $user->save();

        return response()->json([
            'message' => '✅ PIN Benar! Laporan diterima.',
            'data' => $activity
        ]);
    }
}
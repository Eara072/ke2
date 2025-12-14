<?php
namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\User; // <--- Pastikan baris ini ada!
use Illuminate\Http\Request;
use Carbon\Carbon;

class ActivityController extends Controller {

    // 1. Ambil List User (Ini yang dipanggil saat error terjadi)
    public function getUsers() {
        // Mengambil user yang aktif saja
        // Pastikan kolom 'is_active' sudah ada di database
        try {
            $users = User::where('is_active', true)->get();
            return response()->json($users);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 2. Simpan Laporan
    public function store(Request $request) {
        $this->validate($request, [
            'user_id' => 'required|exists:users,id',
            'description' => 'required|string',
            'pin' => 'required|string'
        ]);

        // Cek PIN (Bypass check di sini karena sudah dicek di frontend/login, 
        // tapi sebaiknya tetap dicek jika ingin strict)
        $user = User::find($request->user_id);
        
        if ((string)$user->pin !== (string)$request->pin) {
            return response()->json(['message' => '⛔ PIN Salah!'], 401);
        }

        $activity = Activity::create([
            'user_id' => $request->user_id,
            'description' => $request->description
        ]);

        // Update timestamp untuk CronJob
        $user->last_activity_at = Carbon::now();
        $user->save();

        return response()->json([
            'message' => 'Laporan diterima.',
            'data' => $activity
        ]);
    }
}
<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller {

    public function getDashboardStats() {
        // 1. Ambil semua karyawan aktif (Kecuali si Admin sendiri)
        // Kita filter role 'employee' agar admin tidak muncul di list absen
        $employees = User::where('role', 'employee')
                         ->where('is_active', true)
                         ->get();

        $today = Carbon::today();
        $recap = [];

        foreach ($employees as $emp) {
            // 2. Hitung jumlah laporan hari ini untuk karyawan tersebut
            $reportCount = DB::table('activities')
                ->where('user_id', $emp->id)
                ->whereDate('created_at', $today)
                ->count();

            // 3. Tentukan Status (Sesuai Logika Pagi & Sore)
            // - Pagi: Minimal 1x lapor (Check-in)
            // - Sore: Minimal 2x lapor (Check-in + Check-out)
            
            $recap[] = [
                'id' => $emp->id,
                'name' => $emp->name,
                // Ambil jam shift saja (08:00 - 17:00)
                'shift' => substr($emp->start_time, 0, 5) . ' - ' . substr($emp->end_time, 0, 5),
                'total_laporan' => $reportCount,
                'status_pagi' => $reportCount >= 1, // True jika sudah lapor minimal 1x
                'status_sore' => $reportCount >= 2  // True jika sudah lapor minimal 2x
            ];
        }

        // Kirim data ke Frontend
        return response()->json([
            'date' => $today->format('d M Y'), // Kirim tanggal hari ini (e.g. 08 Dec 2025)
            'data' => $recap
        ]);
    }
}
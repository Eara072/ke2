<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\WablasService;
use Carbon\Carbon;

class RemindEmployeeCommand extends Command
{
    // Nama command untuk dipanggil nanti
    protected $signature = 'remind:employee';

    protected $description = 'Cek karyawan yang belum update kegiatan 10 menit terakhir';

    public function handle()
{
    $this->info('--- MULAI PENGECEKAN ---');

    // 1. Waktu server sekarang
    $now = Carbon::now();
    $this->info("Waktu Server (Lumen): " . $now->format('Y-m-d H:i:s'));

    // 2. Ambil semua karyawan
    $employees = DB::table('users')->get();

    foreach ($employees as $employee) {

        // 3. Cek aktivitas terakhir
        $lastActivity = DB::table('activities')
            ->where('user_id', $employee->id)
            ->latest('created_at')
            ->first();

        // === A. Jika belum pernah input sama sekali ===
        if (!$lastActivity) {
            $this->error("User {$employee->name}: Belum ada aktivitas sama sekali. Kirim WA!");

            $message = "Halo *{$employee->name}*,\n\n";
            $message .= "System mendeteksi Anda belum menginput kegiatan hari ini.\n";
            $message .= "Mohon segera update aktivitas Anda.";

            WablasService::sendMessage($employee->phone, $message);
            continue;
        }

        // === B. Sudah pernah input → cek selisih menit ===
        $lastActivityTime = Carbon::parse($lastActivity->created_at);
        $diffInMinutes = $lastActivityTime->diffInMinutes($now);

        // Debug info
        $this->info("User: {$employee->name}");
        $this->info(" - Terakhir Lapor: " . $lastActivityTime->format('H:i:s'));
        $this->info(" - Selisih Waktu: {$diffInMinutes} menit");

        // === C. Jika lebih dari 10 menit, kirim WA ===
        if ($now->greaterThan($lastActivityTime) && $diffInMinutes >= 10) {
            $this->warn(" -> LEBIH DARI 10 MENIT! MENGIRIM WA...");

            $message = "Halo *{$employee->name}*,\n\n";
            $message .= "⚠️ Anda belum update kegiatan selama {$diffInMinutes} menit.\n";
            $message .= "Mohon segera input laporan Anda.";

            WablasService::sendMessage($employee->phone, $message);
        } else {
            // === D. Masih aman (< 10 menit) ===
            $this->info(" -> Aman. Baru update {$diffInMinutes} menit lalu.");
        }
    }

    $this->info('--- SELESAI ---'); 
    return 0;
}
}
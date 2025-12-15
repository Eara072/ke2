<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\WablasService;
use Carbon\Carbon;

class RemindEmployeeCommand extends Command
{
    // Hapus parameter {session}, kita buat simpel
    protected $signature = 'remind:employee';
    protected $description = 'Pengingat Harian (1x Sehari)';

    public function handle()
    {
        $this->info("--- JALANKAN PENGINGAT HARIAN (17:00) ---");

        $employees = DB::table('users')->where('is_active', true)->get();
        $today = Carbon::today();
        
        // Ganti dengan IP/Domain Anda untuk Magic Link
        $frontendUrl = "http://localhost:5173"; 

        foreach ($employees as $employee) {
            
            // Cek jumlah laporan hari ini
            $reportCount = DB::table('activities')
                ->where('user_id', $employee->id)
                ->whereDate('created_at', $today)
                ->count();

            $this->info("User: {$employee->name} | Total Lapor: {$reportCount}");
            
           // Jangan jalan kalau weekend
           if (Carbon::now()->isWeekend()) {

            $this->info("Hari Libur (Weekend). Skip.");

            return;

        }
            // LOGIKA: Jika laporan hari ini masih KOSONG (0), kirim WA
            if ($reportCount == 0) {
                $this->warn(" -> Belum lapor! Kirim WA...");
                
                $magicLink = "{$frontendUrl}?uid={$employee->id}";
                
                $msg = "Halo *{$employee->name}*,\n\n";
                $msg .= "Sistem mendeteksi Anda belum mengisi *Laporan Project Control* hari ini.\n";
                $msg .= "Mohon segera isi sebelum pulang kerja.\n\n";
                $msg .= "👇 *KLIK UNTUK LAPOR:* \n$magicLink";

                WablasService::sendMessage($employee->phone, $msg);
            } else {
                $this->info(" -> Aman (Sudah lapor).");
            }
        }
    }
}
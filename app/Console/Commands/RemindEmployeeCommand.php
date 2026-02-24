<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\WablasService;
use Carbon\Carbon;

class RemindEmployeeCommand extends Command
{
    // Tambah parameter {mode} untuk membedakan Shift Normal / Malam
    protected $signature = 'remind:employee {mode}'; 
    protected $description = 'Pengingat Laporan (Mode: normal/night)';

    public function handle()
    {
        $mode = $this->argument('mode'); // Ambil mode dari Kernel
        
        $frontendUrl = "http://localhost:5173"; 
        
        // --- TENTUKAN TANGGAL YANG DICEK ---
        if ($mode == 'night') {
            // Jika Mode Malam (Dijalankan Pagi), cek data KEMARIN
            $checkDate = Carbon::yesterday();
            $dateLabel = "Kemarin (" . $checkDate->format('d M') . ")";
            $this->info("--- CEK SHIFT MALAM (Target: $dateLabel) ---");
        } else {
            // Jika Mode Normal (Dijalankan Sore), cek data HARI INI
            $checkDate = Carbon::today();
            $dateLabel = "Hari Ini";
            $this->info("--- CEK SHIFT NORMAL (Target: $dateLabel) ---");
        }

        // Ambil Karyawan Aktif
        $employees = DB::table('users')
            ->where('is_active', true)
            ->where('role', 'employee')
            ->get();

        foreach ($employees as $employee) {
            
            // Query ke Database berdasarkan tanggal $checkDate
            $reportCount = DB::table('activities')
                ->where('user_id', $employee->id)
                ->whereDate('created_at', $checkDate) // Cek tanggal sesuai mode
                ->count();

            $this->info("User: {$employee->name} | Laporan $dateLabel: {$reportCount}");

            // --- LOGIKA PENGIRIMAN WA ---
            
            if ($reportCount == 0) {
                // Hanya kirim pesan ini kalau laporannya kosong
                $this->warn(" -> Belum lapor! Kirim WA...");
                
                $magicLink = "{$frontendUrl}?uid={$employee->id}";
                $msg = "";

                if ($mode == 'night') {
                    // Pesan Khusus Shift Malam (Dikirim Pagi)
                    $msg = "Selamat Pagi *{$employee->name}*,\n\n";
                    $msg .= "Sistem mendeteksi Anda belum mengisi laporan untuk Shift Malam **{$dateLabel}**.\n";
                    $msg .= "Sebelum meninggalkan pabrik/istirahat, mohon isi laporan dulu ya.\n\n";
                } else {
                    // Pesan Shift Normal (Dikirim Sore)
                    $msg = "Halo *{$employee->name}*,\n\n";
                    $msg .= "Reminder: Anda belum mengisi *Report Produksi* hari ini.\n";
                    $msg .= "Mohon segera isi laporan sebelum jam pulang.\n\n";
                }

                $msg .= "👇 *KLIK DISINI:* \n$magicLink";

                WablasService::sendMessage($employee->phone, $msg);
            } else {
                $this->info(" -> Aman.");
            }
        }
        
        $this->info("--- SELESAI ---");
    }
}
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\WablasService;
use Carbon\Carbon;

class RemindEmployeeCommand extends Command
{
    protected $signature = 'remind:employee';
    protected $description = 'Cek karyawan yang belum update kegiatan (Versi Teroptimasi)';

    public function handle()
    {
        $this->info('--- MULAI PENGECEKAN (OPTIMIZED) ---');
        
        $now = Carbon::now();
        $this->info("Waktu Server: " . $now->format('H:i'));

        // OPTIMASI 1: Hanya ambil karyawan yang AKTIF (is_active = 1)
        // Kita tidak perlu mengecek karyawan yang sudah resign/cuti.
        $employees = DB::table('users')->where('is_active', true)->get();

        foreach ($employees as $employee) {
            
            // --- LOGIKA 1: CEK JAM KERJA (SHIFT) ---
            
            $jamMasuk = Carbon::parse($employee->start_time);
            $jamPulang = Carbon::parse($employee->end_time);

            // Jika sekarang diluar jam kerja, SKIP.
            if ($now->lessThan($jamMasuk) || $now->greaterThan($jamPulang)) {
                $this->info("Skip {$employee->name}: Diluar jam kerja.");
                continue; 
            }

            // --- LOGIKA 2: CEK AKTIVITAS (TANPA QUERY TABEL ACTIVITIES) ---

            // Ambil waktu terakhir lapor langsung dari tabel users
            $lastActivityTime = $employee->last_activity_at ? Carbon::parse($employee->last_activity_at) : null;

            // KASUS A: Belum pernah lapor sama sekali (Kolom NULL)
            if (!$lastActivityTime) {
                
                // Beri toleransi 10 menit pertama saat baru masuk kerja
                if ($now->diffInMinutes($jamMasuk) < 10) {
                    $this->info("User {$employee->name}: Baru absen masuk, aman.");
                    continue;
                }

                // Jika sudah lewat 10 menit dari jam masuk & belum lapor
                $this->warn("User {$employee->name}: BELUM ADA DATA! Kirim WA...");
                WablasService::sendMessage($employee->phone, "Halo {$employee->name}, Anda belum input kegiatan pertama Anda hari ini!");
                continue;
                // Simpan bukti pengiriman ke database
                DB::table('notification_logs')->insert([
                    'user_id' => $employee->id,
                    'message' => $msg,
                    'created_at' => Carbon::now()
]);
            }

            // KASUS B: Sudah pernah lapor, cek selisih waktu
            $diffInMinutes = $lastActivityTime->diffInMinutes($now);

            // Validasi: Pastikan waktu sekarang > waktu lapor (mencegah bug timezone)
            if ($now->greaterThan($lastActivityTime) && $diffInMinutes >= 1) {
                
                $this->warn(" -> ALARM: {$employee->name} telat {$diffInMinutes} menit.");
                
                $msg = "Halo *{$employee->name}*, Sistem mendeteksi Anda diam selama *{$diffInMinutes} menit*.\nMohon segera update laporan kegiatan Anda.";
                
                $response = WablasService::sendMessage($employee->phone, $msg);
                $this->info(" -> Status WA: " . $response);

            } else {
                $this->info("User {$employee->name}: Aman (Update {$diffInMinutes} menit lalu).");
            }
        }
        
        $this->info('--- SELESAI ---');
    }
}
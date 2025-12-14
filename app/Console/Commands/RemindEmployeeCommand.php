<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\WablasService;
use Carbon\Carbon;

class RemindEmployeeCommand extends Command
{
    protected $signature = 'remind:employee {session}';
    protected $description = 'Cek kelengkapan laporan harian (2x Sehari) + Magic Link';

    public function handle()
    {
        $session = $this->argument('session');
        $this->info("--- CEK LAPORAN SESI: " . strtoupper($session) . " ---");

        $employees = DB::table('users')->where('is_active', true)->get();
        $today = Carbon::today();

        // GANTI INI DENGAN IP LAPTOP KAMU ATAU DOMAIN ASLI
        // Jangan localhost kalau mau dibuka di HP, tapi kalau tes di laptop pakai localhost gapapa.
        $frontendUrl = "http://localhost:5173"; 

        foreach ($employees as $employee) {
            
            $reportCount = DB::table('activities')
                ->where('user_id', $employee->id)
                ->whereDate('created_at', $today)
                ->count();

            $this->info("User: {$employee->name} | Total: {$reportCount}");

            $msg = "";
            $shouldSend = false;

            // Buat Magic Link (Menempelkan ID user di URL)
            $magicLink = "{$frontendUrl}?uid={$employee->id}";

            if ($session == 'morning' && $reportCount < 1) {
                $shouldSend = true;
                $msg = "Halo *{$employee->name}*, Anda belum Check-in Pagi.\n\n👇 *KLIK UNTUK LAPOR CEPAT:*\n$magicLink";
            }

            if ($session == 'afternoon' && $reportCount < 2) {
                $shouldSend = true;
                $msg = "Halo *{$employee->name}*, Jangan lupa Check-out Sore.\n\n👇 *KLIK UNTUK LAPOR CEPAT:*\n$magicLink";
            }

            if ($shouldSend) {
                $this->warn(" -> Kirim Link ke {$employee->phone}...");
                WablasService::sendMessage($employee->phone, $msg);
            }
        }
    }
}
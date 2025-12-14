<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Daftar Command yang aktif
     */
    protected $commands = [
        \App\Console\Commands\RemindEmployeeCommand::class,
    ];

    /**
     * Jadwal Eksekusi Robot
     */
    protected function schedule(Schedule $schedule)
    {
        // 1. CEK PAGI (Jalankan jam 10:00)
        // Mengecek siapa yang belum Check-in (Jumlah lapor < 1)
        $schedule->command('remind:employee morning')
                 ->everyMinute();
                

        // 2. CEK SORE (Jalankan jam 16:00)
        // Mengecek siapa yang belum Check-out (Jumlah lapor < 2)
        $schedule->command('remind:employee afternoon')
                 ->dailyAt('16:00')
                 ->timezone('Asia/Jakarta');
    }
}
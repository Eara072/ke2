<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\RemindEmployeeCommand::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        // 1. JADWAL NORMAL (SHIFT 1)
        // Senin-Kamis: Cek jam 15:45
        $schedule->command('remind:employee normal')
                 ->days([1, 2, 3, 4]) 
                 ->at('15:45')
                 ->timezone('Asia/Jakarta');

        // Jumat: Cek jam 16:15
        $schedule->command('remind:employee normal')
                 ->fridays() 
                 ->at('16:15')
                 ->timezone('Asia/Jakarta');

        // 2. JADWAL SHIFT MALAM (SHIFT 2)
        // Jalan setiap Selasa (2) sampai Sabtu (6) jam 07:30 Pagi.
        // Tujuannya: Mengecek laporan "HARI KEMARIN" yang belum diisi.
        $schedule->command('remind:employee night')
                 ->days([2, 3, 4, 5, 6]) 
                 ->at('07:30')
                 ->timezone('Asia/Jakarta');
    }
}
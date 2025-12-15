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
        // REVISI: Jalan CUMA 1X SEHARI (Jam 17:00 WIB)
        // Tujuannya mengingatkan karyawan mengisi laporan sebelum pulang
        $schedule->command('remind:employee')
                 ->dailyAt('17:00')
                 ->timezone('Asia/Jakarta');
    }
}
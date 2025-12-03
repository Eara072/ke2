<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     */
    protected $commands = [
        // DAFTARKAN COMMAND DISINI
        \App\Console\Commands\RemindEmployeeCommand::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        // JALANKAN SETIAP 10 MENIT
        $schedule->command('remind:employee')->everyTenMinutes();
        
        // Atau jika ingin testing cepat, pakai ->everyMinute();
    }
}
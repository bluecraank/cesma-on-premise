<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // API abfragen
        $schedule->command('switch:refresh')
        ->everyFifteenMinutes()
        ->between('6:00', '20:00')
        ->timezone('Europe/Berlin')
        ->appendOutputTo(storage_path('logs/switch-refresh.log'));
        
        // Daten aus der DB verarbeiten und Clients finden / updaten
        $schedule->command('clients:update')
        ->everyThirtyMinutes()
        ->between('6:00', '20:00')
        ->timezone('Europe/Berlin')
        ->appendOutputTo(storage_path('logs/clients-update.log'));

        // Clients anpingen, um den Status anzuzeigen
        $schedule->command('clients:ping')
        ->everyTenMinutes()
        ->timezone('Europe/Berlin')
        ->appendOutputTo(storage_path('logs/client-ping.log'));

        $schedule->command('client:vendor')
        ->daily()
        ->at('04:00')
        ->appendOutputTo(storage_path('logs/mac-vendors.log'));
        
        // Backups erstellen
        $schedule->command('switch:backup')
        ->dailyAt('08:00')
        ->appendOutputTo(storage_path('logs/backup.log'));

        // Backups per Mail versenden
        $schedule->command('switch:backup:mail')
        ->weekly()
        ->sundays()
        ->at('22:00')
        ->appendOutputTo(storage_path('logs/backup.log'));
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

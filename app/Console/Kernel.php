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
        // $schedule->command('switch:refresh')->everyFifteenMinutes()->timezone('Europe/Berlin')->between('07:15', '17:00');
        // $schedule->command('clients:update')->everyThirtyMinutes()->timezone('Europe/Berlin')->between('07:15', '17:00');
        // $schedule->command('clients:ping')->everyTenMinutes()->timezone('Europe/Berlin')->between('07:15', '17:00');
        
        // API abfragen
        $schedule->command('switch:refresh')->everyFifteenMinutes()->timezone('Europe/Berlin');
        
        // Daten aus der DB verarbeiten und Clients finden / updaten
        $schedule->command('clients:update')->everyThirtyMinutes()->timezone('Europe/Berlin');

        // Clients anpingen, um den Status anzuzeigen
        $schedule->command('clients:ping')->everyTenMinutes()->timezone('Europe/Berlin');
        
        // Backups erstellen
        $schedule->command('switch:backup')->dailyAt('08:00');

        // Backups per Mail versenden
        $schedule->command('switch:backup:mail')->weekly()->sundays()->at('22:00');
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

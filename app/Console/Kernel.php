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
        $schedule->command('device:refresh-all')
        ->everyFiveMinutes()
        ->appendOutputTo(storage_path('logs/device-refresh-all.log'))
        ->runInBackground();

        $schedule->command('clients:update')
        ->everyFifteenMinutes()
        ->appendOutputTo(storage_path('logs/clients-update.log'))
        ->runInBackground();

        $schedule->command('clients:ping')
        ->everyFifteenMinutes()
        ->between('05:00', '21:00')
        ->runInBackground();

        $schedule->command('clients:resolve-mac-vendors')
        ->daily()
        ->at('04:00')
        ->appendOutputTo(storage_path('logs/clients-resolve-mac-vendors.log'))
        ->runInBackground();
        
        // Backups erstellen
        $schedule->command('device:backup')
        ->dailyAt('08:00')
        ->appendOutputTo(storage_path('logs/device-backup.log'))
        ->runInBackground();

        // Backups per Mail versenden
        $schedule->command('backup:mail')
        ->weekly()
        ->sundays()
        ->at('22:00')
        ->appendOutputTo(storage_path('logs/backup-mail.log'));
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

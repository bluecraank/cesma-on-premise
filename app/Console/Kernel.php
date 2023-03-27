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
        ->runInBackground();
        
        $schedule->command('clients:query-providers')
        ->everyMinute();

        $schedule->command('clients:update')
        ->everyFiveMinutes()
        ->runInBackground();

        $schedule->command('clients:dns-lookup')
        ->everyFiveMinutes()
        ->runInBackground();

        $schedule->command('clients:resolve-mac-vendors')
        ->daily()
        ->at('05:00')
        ->runInBackground();
        
        // Backups erstellen
        $schedule->command('device:backup-all')
        ->dailyAt('10:30')
        ->runInBackground();

        $schedule->command('database:cleanup')
        ->dailyAt('04:00');

        // Backups per Mail versenden
        $schedule->command('backup:mail')
        ->weekly()
        ->sundays()
        ->at('22:00');
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

    protected function shortSchedule(\Spatie\ShortSchedule\ShortSchedule $shortSchedule)
{
    // this artisan command will run every second
    $shortSchedule->command('check:job-queue')->everySecond()->withoutOverlapping();
}
}

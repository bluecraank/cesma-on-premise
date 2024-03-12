<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // MOVED TO REFRESH DEVICES LOOP
        // API refresh
        // $schedule->command('device:refresh-all-api')
        //     ->everyFiveMinutes()
        //     ->runInBackground()
        //     ->withoutOverlapping();

        // Scan for new devices
        $schedule->command('device:arp-scan')
            ->everyFiveMinutes()
            ->runInBackground();

        // MOVED TO REFRESH DEVICES LOOP
        // SNMP refresh every minute cause it's fast
        // $schedule->command('device:refresh-all')->everyMinute();

        // Get clients from providers
        $schedule->command('clients:query-providers')
            ->everyMinute();

        // Update clients
        $schedule->command('clients:update')
            ->everyFiveMinutes()
            ->runInBackground();

        // Resolve dns hostnames of clients
        $schedule->command('clients:dns-lookup')
            ->everyFiveMinutes()
            ->runInBackground();

        // Resolve mac vendors of clients
        $schedule->command('clients:resolve-mac-vendors')
            ->daily()
            ->at('05:00')
            ->runInBackground();

        $schedule->command('device:resolve-topology')
            ->hourly()
            ->runInBackground();

        // Create backups of every device
        $schedule->command('device:backup-all')
            ->dailyAt('10:30')
            ->runInBackground();

        // Clean up database
        $schedule->command('database:cleanup')
            ->dailyAt('04:00');

        // Sent backup status weekly
        $schedule->command('backup:mail')
            ->weekly()
            ->sundays()
            ->at('22:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}

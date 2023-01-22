<?php

namespace App\Console\Commands;

use App\Models\Backup;
use App\Models\Client;
use App\Models\Log;
use App\Models\MacAddress;
use App\Models\PortStat;
use Illuminate\Console\Command;

class DatabaseCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up the database';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        MacAddress::whereDate('created_at', '<=', now()->subWeek(4))->delete();
        Client::whereDate('updated_at', '<=', now()->subWeek(4))->delete();
        Backup::whereDate('created_at', '<=', now()->subYear(2))->delete();
        PortStat::whereDate('created_at', '<=', now()->subWeek(2))->delete();
        Log::whereDate('created_at', '<=', now()->subWeek(8))->delete();
        
        \Illuminate\Support\Facades\Log::info('Database cleaned up');
    }
}

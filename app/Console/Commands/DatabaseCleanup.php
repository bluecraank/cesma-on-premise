<?php

namespace App\Console\Commands;

use App\Models\Backup;
use App\Models\Client;
use App\Models\Log;
use App\Models\MacAddress;
use App\Models\PortStat;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log as FacadesLog;

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
        
        $devices = \App\Models\Device::all();
        foreach($devices as $device) {
            $uplinks = json_decode($device->uplinks, true) ?? [];
            Client::where('switch_id', $device->id)->where(function ($query) use ($uplinks) {
                foreach($uplinks as $uplink) {
                    $query->orWhere('port_id', $uplink);
                }
            })->delete();
        }

        \Illuminate\Support\Facades\Log::info('Database cleaned up');
    }
}

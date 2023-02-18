<?php

namespace App\Console\Commands;

use App\Http\Controllers\ClientController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PingClients extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clients:ping';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ping all clients';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        ClientController::checkOnlineStatus();
        Log::info('[Clients] Ping finished');
    }
}

<?php

namespace App\Console\Commands;

use App\Http\Controllers\ClientController;
use App\Services\ClientService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GetClients extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clients:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process switch data and get snmp data to update clients';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        ClientService::getClients();
    }
}

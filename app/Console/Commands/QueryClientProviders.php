<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ClientService;
use Illuminate\Support\Facades\Log;

class QueryClientProviders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clients:query-providers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieve client data from routers (and or baramundi)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if(ClientService::getClientDataFromProviders()) {
            Log::info('[Clients] Client data retrieved from providers');
        }
    }
}

<?php

namespace App\Console\Commands;

use App\ClientProviders\Baramundi;
use App\ClientProviders\Router;
use Illuminate\Console\Command;
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
        $providers = [
            Baramundi::class,
            Router::class
        ];

        $queriedAtLeastOneProvider = 0;


        $start = microtime(true);
        foreach($providers as $provider) {
            $queriedAtLeastOneProvider++;
            $provider::queryClientData();
        }

        if($queriedAtLeastOneProvider != 0) {
            Log::info('[Clients] Client data retrieved from ' . $queriedAtLeastOneProvider . ' providers in' . (microtime(true) - $start) . ' seconds');
        }
    }
}

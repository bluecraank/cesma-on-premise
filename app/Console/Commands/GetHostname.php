<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;
use Illuminate\Support\Facades\Log;

class GetHostname extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clients:dns-lookup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get the hostname of all clients';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $clients = Client::whereNotNull('ip_address')->get();

        foreach ($clients as $client) {

            // DNS lookup for ip address of client
            $output = "";
            exec('timeout 0.35 host '.$client->ip_address, $output, $errors);
            $result = $output[0] ?? "";
            $found = strstr($result, "pointer");
            $hostname = str_replace(["pointer", " "], "", $found);

            // Skip if no hostname was found
            if(!$hostname or $hostname == null or $hostname == "") {
                continue;
            }
            
            // Do not update stale clients (last updated older than 24 hours ago)
            if($client->updated_at < now()->subDay()) {
                continue;
            }
            
            $client->hostname = rtrim($hostname, ".");

            // Dont touch updated_at
            $client->timestamps = false;
            
            $client->save();
        }

        Log::info('[Clients] Hostname lookup finished');
    }
}

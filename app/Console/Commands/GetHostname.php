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
        $clients = Client::all();

        foreach ($clients as $client) {

            if($client->ip_address == "") {
                continue;
            }

            $output = "";
            $get = exec('timeout 0.35 host '.$client->ip_address, $output, $errors);
            $result = $output[0] ?? "";
            $found = strstr($result, "pointer");
            $hostname = str_replace(["pointer", " "], "", $found);

            if(!$hostname or $hostname == null or $hostname == "") {
                continue;
            }

            $client->timestamps = false;
            $client->hostname = rtrim($hostname, ".");
            $client->save();
        }

        Log::info('[Clients] Hostname lookup finished');
    }
}

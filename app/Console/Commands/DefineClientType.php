<?php

namespace App\Console\Commands;

use App\Models\MacType;
use Illuminate\Console\Command;

class DefineClientType extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'client:define-type';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $types = MacType::all()->keyBy('mac_prefix')->toArray();
        $clients = \App\Models\Client::all();
        foreach($clients as $client) {
            $client->type = \App\Services\ClientService::getClientType($client->mac_address, $types);
            $client->save();
        }
    }
}

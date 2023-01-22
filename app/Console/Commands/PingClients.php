<?php

namespace App\Console\Commands;

use App\Http\Controllers\ClientController;
use Illuminate\Console\Command;

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
        return $this->comment(ClientController::checkOnlineStatus());
    }
}

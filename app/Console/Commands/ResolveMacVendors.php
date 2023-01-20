<?php

namespace App\Console\Commands;

use App\Http\Controllers\MacAddressController;
use Illuminate\Console\Command;

class ResolveMacVendors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clients:resolve-mac-vendors';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Search for Vendor name with mac prefix';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        return $this->comment(MacAddressController::getMacVendor());
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CreateSite extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'site:create {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $site = new \App\Models\Site();
        $site->name = $this->argument('name');
        $site->save();

        $this->info("Site {$site->name} created");
        return 0;
    }
}

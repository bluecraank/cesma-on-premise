<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class SetUserRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:role {guid : The guid of ldap user object} {role : user / admin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set a user role (guid required)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $user = User::where('guid', $this->argument('guid'))->first();
        if(!$user) {
            $this->comment('User not found');
            return;
        }

        $user->role = $this->argument('role') == 'admin' ? 'admin' : 'user';
        $user->save();
        
        $this->comment('User role set to "' . $user->role . '" for ' . $user->guid);
    }
}

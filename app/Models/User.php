<?php

namespace App\Models;


use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cookie;
use LdapRecord\Laravel\Auth\AuthenticatesWithLdap;
use LdapRecord\Laravel\Auth\LdapAuthenticatable;

class User extends Authenticatable implements LdapAuthenticatable
{
    use Notifiable, AuthenticatesWithLdap;

    protected $fillable = [
        'role'
    ];

    public function getRoleName() {
        switch ($this->role) {
            case 0:
                return 'User';
            case 1:
                return 'Admin';
            case 2:
                return 'Super Admin';
            default:
                return 'Unknown';
        }
    }

    public function availableSites() {
        $sites = Site::all();
        return $sites;
    }

    public function currentSite() {

        $cookie = Cookie::get('currentSite');

        if($cookie) {
            $site = Site::where('id', $cookie)->first();

            if(!$site)
                $site = Site::first();
            
        } else {
            $site = Site::first();
        }

        if(!$site)
            exit('No site found');
        

        return $site;
    }
}

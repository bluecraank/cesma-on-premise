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
        $sites = Permission::where('guid', $this->guid)->get()->pluck('site_id')->toArray();
        $sites = Site::whereIn('id', $sites)->get();

        if($this->role == 2)
            return Site::all();

        return $sites;
    }

    public function currentSite() {

        $cookie = Cookie::get('currentSite');

        if($cookie) {
            $site = Site::where('id', $cookie)->first();
        } else {
            $site = Site::first();
        }
        
        $permission = Permission::where('guid', $this->guid)->where('site_id', $site->id)->first();
        
        if(!$permission && $this->role != 2) {
            $default_allowed_site = Permission::where('guid', $this->guid)->first()?->site_id;
            $site = Site::where('id', $default_allowed_site)->first() ?? null;
        }


        // Abort if user does not have permission to access this site
        if(!$site && $this->role != 2)
            abort(403);
        

        return $site;
    }

    public function getAllowedSitesAttribute() {
        $permissions = Permission::where('guid', $this->guid)->get()->pluck('site_id')->toArray();
        return json_encode($permissions);
    }
}

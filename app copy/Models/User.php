<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cookie;
use LdapRecord\Laravel\Auth\LdapAuthenticatable;
use LdapRecord\Laravel\Auth\AuthenticatesWithLdap;

class User extends Authenticatable
{
    use Notifiable, AuthenticatesWithLdap;

    protected $fillable = [
        'role'
    ];

    public function getRoleName() {
        switch ($this->role) {
            case 0:
                return 'Read-only user';
            case 1:
                return 'Administrator';
            case 2:
                return 'Super Administrator';
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

    public function initials() {
        $name = $this->name;
        $name = explode(" ", $name);
        $initials = "";
        foreach ($name as $n) {
            $initials .= $n[0];
        }
        return $initials;
    }
}

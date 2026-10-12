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

        $site = $cookie ? Site::where('id', $cookie)->first() : null;

        // Fall back to the first site if the cookie points to a deleted site
        $site = $site ?? Site::first();

        if(!$site) {
            // No site exists yet
            abort(403, __('No site exists yet, create one with "php artisan site:create <name>"'));
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
            if ($n !== "") {
                $initials .= mb_substr($n, 0, 1);
            }
        }
        return $initials;
    }
}

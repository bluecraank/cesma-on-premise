<?php

namespace App\Models;


use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use LdapRecord\Laravel\Auth\AuthenticatesWithLdap;
use LdapRecord\Laravel\Auth\LdapAuthenticatable;

class User extends Authenticatable implements LdapAuthenticatable
{
    use Notifiable, AuthenticatesWithLdap;

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
}

<?php

namespace App\Ldap\Rules;

use LdapRecord\Laravel\Auth\Rule;
use LdapRecord\Models\ActiveDirectory\Group;

class Administrator extends Rule
{
    /**
     * Check if the rule passes validation.
     *
     * @return bool
     */
    public function isValid()
    {
        $administrators = Group::find(config('app.ldap_admin_group'));
        $user = Group::find(config('app.ldap_user_group'));

        return $this->user->groups()->recursive()->exists($administrators) || $this->user->groups()->recursive()->exists($user);
    }
}

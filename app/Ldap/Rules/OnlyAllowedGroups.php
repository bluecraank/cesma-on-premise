<?php

namespace App\Ldap\Rules;

use Illuminate\Database\Eloquent\Model as Eloquent;
use LdapRecord\Laravel\Auth\Rule;
use LdapRecord\Models\Model as LdapRecord;

class OnlyAllowedGroups implements Rule
{
    /**
     * Check if the rule passes validation.
     */
    public function passes(LdapRecord $user, Eloquent $model = null): bool
    {
        $administrators = Group::find(config('app.ldap_admin_group'));

        return $this->user->groups()->recursive()->exists($administrators);
    }
}

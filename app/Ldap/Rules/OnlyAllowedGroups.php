<?php

namespace App\Ldap\Rules;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Facades\Log;
use LdapRecord\Laravel\Auth\Rule;
use LdapRecord\Models\ActiveDirectory\Group;
use LdapRecord\Models\Model as LdapRecord;

class OnlyAllowedGroups implements Rule
{
    /**
     * Only members of LDAP_ADMIN_GROUP (also nested) may log in.
     * Without LDAP_ADMIN_GROUP every directory user is allowed.
     */
    public function passes(LdapRecord $user, Eloquent $model = null): bool
    {
        $groupDn = config('app.ldap_admin_group');

        if (empty($groupDn)) {
            return true;
        }

        $allowedGroup = Group::find($groupDn);

        if (!$allowedGroup) {
            Log::error('[LDAP] Login denied, group from LDAP_ADMIN_GROUP not found: ' . $groupDn);
            return false;
        }

        return $user->groups()->recursive()->exists($allowedGroup);
    }
}

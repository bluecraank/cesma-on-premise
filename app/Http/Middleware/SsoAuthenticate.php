<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use LdapRecord\Laravel\Middleware\WindowsAuthenticate;

class SsoAuthenticate extends WindowsAuthenticate
{
    /**
     * Only authenticate by the SSO header if SSO is enabled (SSO_ENABLED=true).
     * Otherwise any client could log in as any user by sending the header itself.
     */
    public function handle($request, Closure $next, ...$guards)
    {
        if (!config('app.sso_enabled')) {
            return $next($request);
        }

        return parent::handle($request, $next, ...$guards);
    }
}

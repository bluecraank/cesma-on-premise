<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use LdapRecord\Laravel\Middleware\WindowsAuthenticate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::define('is-superadmin', function (User $user) {
            return $user->role === 2;
        });

        Gate::define('is-admin', function (User $user) {
            return $user->role === 1;
        });

        Gate::define('is-user', function (User $user) {
            return $user->role === 0;
        });

        WindowsAuthenticate::serverKey(config('app.sso_http_header_user_key'));
        WindowsAuthenticate::bypassDomainVerification();

    }
}

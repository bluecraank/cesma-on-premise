<?php

namespace App\Providers;

use App\Helper\CLog;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        // Audit log for authentication (the user is passed explicitly, it is not set in the guard yet)
        Event::listen(\Illuminate\Auth\Events\Login::class, function ($event) {
            CLog::write("Auth", "info", "User {$event->user->name} logged in", null, [
                'guid' => $event->user->guid ?? null,
                'sso' => request()->server(config('app.sso_http_header_user_key')) !== null,
            ], $event->user->name);
        });

        Event::listen(\Illuminate\Auth\Events\Logout::class, function ($event) {
            if ($event->user) {
                CLog::write("Auth", "info", "User {$event->user->name} logged out", null, null, $event->user->name);
            }
        });

        Event::listen(\Illuminate\Auth\Events\Failed::class, function ($event) {
            // Never log the password
            $username = $event->credentials['samaccountname'] ?? $event->credentials['username'] ?? 'unknown';
            CLog::write("Auth", "warning", "Failed login for {$username}", null, null, $username);
        });

        Event::listen(\LdapRecord\Laravel\Events\Auth\Rejected::class, function ($event) {
            $username = $event->object->getName();
            CLog::write("Auth", "warning", "Login of {$username} denied, not member of LDAP_ADMIN_GROUP", null, null, $username);
        });
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}

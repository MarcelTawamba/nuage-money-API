<?php

namespace App\Providers;

use App\Events\PayInFailureEvent;
use App\Events\PayInSuccessEvent;
use App\Events\PayOutFailureEvent;
use App\Events\PayOutSuccessEvent;
use App\Listeners\PayInFailureHandler;
use App\Listeners\PayInSuccessHandler;
use App\Listeners\PayOutFailureHandler;
use App\Listeners\PayOutSuccessHandler;
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
        PayInSuccessEvent::class => [
            PayInSuccessHandler::class,
        ],
        PayOutFailureEvent::class => [
            PayOutFailureHandler::class,
        ],
        PayOutSuccessEvent::class => [
            PayOutSuccessHandler::class,
        ],
        PayInFailureEvent::class => [
            PayInFailureHandler::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
